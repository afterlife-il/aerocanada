<?php
declare(strict_types=1);

function erp_gmail_http(string $path, array $parameters, string $token, ?array $body = null): array
{
    if (!preg_match('~^/(?:profile|messages(?:/[0-9A-Za-z_-]{1,128})?|drafts(?:/[0-9A-Za-z_-]{1,128})?)$~D', $path)) {
        throw new LogicException('Unsupported Gmail endpoint');
    }
    if ($body !== null && (!in_array($path, ['/drafts', '/drafts/send'], true) || $parameters)) {
        throw new LogicException('Unsupported Gmail write endpoint');
    }
    $url = 'https://gmail.googleapis.com/gmail/v1/users/me' . $path;
    if ($parameters) {
        $url .= '?' . http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
    }
    $curl = curl_init($url);
    $data = '';
    curl_setopt_array($curl, [
        CURLOPT_FOLLOWLOCATION => false, CURLOPT_PROTOCOLS => CURLPROTO_HTTPS, CURLOPT_PROXY => '',
        CURLOPT_CONNECTTIMEOUT => 2, CURLOPT_TIMEOUT => 4,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token, 'Accept: application/json'],
        CURLOPT_WRITEFUNCTION => static function ($handle, string $chunk) use (&$data): int {
            if (strlen($data) + strlen($chunk) > 524288) {
                return 0;
            }
            $data .= $chunk;
            return strlen($chunk);
        }
    ]);
    if ($body !== null) {
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body, JSON_THROW_ON_ERROR),
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token, 'Accept: application/json',
                'Content-Type: application/json'],
        ]);
    }
    try {
        $ok = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    } finally {
        curl_close($curl);
    }
    if ($status === 404) {
        throw new OutOfBoundsException('Élément Gmail introuvable');
    }
    if ($status === 401 || $status === 403) {
        throw new ErpAccessDenied();
    }
    if (!$ok || !in_array($status, [200, 201], true)) {
        throw new RuntimeException('Gmail unavailable');
    }
    $result = json_decode($data, true, 32, JSON_THROW_ON_ERROR);
    if (!is_array($result)) {
        throw new RuntimeException('Invalid Gmail response');
    }
    return $result;
}

function erp_gmail_client(array $binding, array $args, ?callable $transport = null, string $permission = 'read'): Closure
{
    $mailbox = $args['mailbox'] ?? null;
    if (!is_string($mailbox) || !in_array($mailbox, ['sales', 'employee'], true)) {
        throw new InvalidArgumentException('Boîte Gmail invalide');
    }
    $account = $binding['gmail'][$mailbox] ?? null;
    $tokenFile = match ($permission) {
        'write' => 'draftTokenFile', 'send' => 'sendTokenFile', default => 'readTokenFile',
    };
    if (!is_array($account) || !is_string($account[$tokenFile] ?? null)
        || !filter_var($account['email'] ?? null, FILTER_VALIDATE_EMAIL)) {
        throw new ErpAccessDenied();
    }
    $token = rtrim(erp_private_file($account[$tokenFile], 4096), "\r\n");
    if (!preg_match('/^[A-Za-z0-9._~-]{16,4096}$/D', $token)) {
        throw new ErpAccessDenied();
    }
    $http = $transport ?? 'erp_gmail_http';
    $profile = $http('/profile', [], $token);
    if (!is_string($profile['emailAddress'] ?? null)
        || strcasecmp($account['email'], $profile['emailAddress']) !== 0
        || ($mailbox === 'employee' && strcasecmp($binding['employeeEmail'], $account['email']) !== 0)) {
        throw new ErpAccessDenied();
    }
    return static fn (string $path, array $parameters, ?array $body = null): array => $http($path, $parameters, $token, $body);
}

function erp_gmail_text(array $mime, int $depth = 0): string
{
    if ($depth > 8 || !empty($mime['filename'])) {
        return '';
    }
    if (($mime['mimeType'] ?? null) === 'text/plain' && is_string($mime['body']['data'] ?? null)) {
        $decoded = base64_decode(strtr(substr($mime['body']['data'], 0, 32768), '-_', '+/'), true);
        return is_string($decoded) ? mb_scrub(substr($decoded, 0, 16000), 'UTF-8') : '';
    }
    foreach (array_slice($mime['parts'] ?? [], 0, 24) as $part) {
        $value = is_array($part) ? erp_gmail_text($part, $depth + 1) : '';
        if ($value !== '') {
            return $value;
        }
    }
    return '';
}

function erp_gmail_list(Closure $client, array $args, bool $drafts): array
{
    $maximum = $args['limit'] ?? 20;
    $query = $args['query'] ?? '';
    $page = $args['page_token'] ?? null;
    if (!is_int($maximum) || $maximum < 1 || $maximum > 50 || !is_string($query) || strlen($query) > 512
        || ($page !== null && (!is_string($page) || strlen($page) > 512))) {
        throw new InvalidArgumentException('Paramètres Gmail invalides');
    }
    $options = ['maxResults' => $maximum];
    if ($query !== '') {
        $options['q'] = $query;
    }
    if ($page !== null) {
        $options['pageToken'] = $page;
    }
    $response = $client($drafts ? '/drafts' : '/messages', $options);
    $items = [];
    foreach (array_slice($response[$drafts ? 'drafts' : 'messages'] ?? [], 0, $maximum) as $item) {
        $message = $drafts ? ($item['message'] ?? []) : $item;
        if (!is_string($message['id'] ?? null)) {
            continue;
        }
        $value = ['message_id' => $message['id'], 'thread_id' => $message['threadId'] ?? null];
        if ($drafts) {
            $value['draft_id'] = $item['id'] ?? null;
        }
        $items[] = $value;
    }
    return ['items' => $items, 'next_page_token' => $response['nextPageToken'] ?? null];
}

function erp_gmail_message(Closure $client, array $args): array
{
    $id = $args['message_id'] ?? null;
    if (!is_string($id) || !preg_match('/^[a-zA-Z0-9_-]{1,128}$/D', $id)) {
        throw new InvalidArgumentException('Identifiant Gmail invalide');
    }
    $message = $client('/messages/' . $id, ['format' => 'full']);
    $payload = $message['payload'] ?? [];
    $headers = [];
    foreach ($payload['headers'] ?? [] as $header) {
        $name = strtolower($header['name'] ?? '');
        if (in_array($name, ['from', 'to', 'subject', 'date', 'message-id'], true) && is_string($header['value'] ?? null)) {
            $headers[$name] = mb_strcut($header['value'], 0, 1024, 'UTF-8');
        }
    }
    return ['message_id' => $id, 'thread_id' => $message['threadId'] ?? null, 'headers' => $headers,
        'plain_text' => erp_gmail_text($payload), 'snippet' => mb_strcut($message['snippet'] ?? '', 0, 1024, 'UTF-8'),
        'content_from_external_sender' => true];
}

function erp_gmail_quote_mime(string $from, string $to, int $quoteId, string $messageId, array $attachment): array
{
    if (!filter_var($from, FILTER_VALIDATE_EMAIL) || !filter_var($to, FILTER_VALIDATE_EMAIL)
        || !preg_match('/^[a-f0-9]{64}$/D', $messageId)
        || $attachment['filename'] !== 'ACI770-Quote-' . $quoteId . '.pdf') {
        throw new InvalidArgumentException('Informations de brouillon invalides');
    }
    $boundary = 'quote-' . $messageId;
    $message = implode("\r\n", [
        'From: ' . $from, 'To: ' . $to, 'Subject: AeroCanada quotation ' . $quoteId,
        'Message-ID: <' . $messageId . '@mcp.invalid>', 'MIME-Version: 1.0',
        'Content-Type: multipart/mixed; boundary="' . $boundary . '"', '',
        '--' . $boundary, 'Content-Type: text/plain; charset=UTF-8', '',
        'Please find the AeroCanada quotation attached.', '',
        '--' . $boundary, 'Content-Type: application/pdf', 'Content-Transfer-Encoding: base64',
        'Content-Disposition: attachment; filename="' . $attachment['filename'] . '"', '',
        rtrim(chunk_split($attachment['data_base64'], 76, "\r\n")),
        '--' . $boundary . '--', '',
    ]);
    return ['message' => ['raw' => rtrim(strtr(base64_encode($message), '+/', '-_'), '=')]];
}

function erp_gmail_draft_result(array $draft): array
{
    if (!is_string($draft['id'] ?? null) || $draft['id'] === '') {
        throw new RuntimeException('Missing draft ID');
    }
    return ['draft_id' => $draft['id'], 'message_id' => $draft['message']['id'] ?? null, 'persisted' => true];
}

function erp_gmail_draft_write(PDO $db, array $args, array $binding, string $messageId, ?callable $transport = null): array
{
    $quote = erp_quote_document_data($db, ['quote_id' => $args['quote_id'] ?? null]);
    if (!filter_var($quote['recipient'] ?? null, FILTER_VALIDATE_EMAIL)) {
        throw new DomainException('Adresse du contact actif indisponible');
    }
    $client = erp_gmail_client($binding, $args, $transport, 'write');
    $from = $binding['gmail'][$args['mailbox']]['email'];
    $body = erp_gmail_quote_mime($from, $quote['recipient'], (int) $quote['quote_id'], $messageId, erp_quote_render_pdf($quote));
    return ['execute' => static fn (): array => erp_gmail_draft_result($client('/drafts', [], $body))];
}

function erp_gmail_draft_reconcile(Closure $client, string $messageId): array
{
    $response = $client('/drafts', ['q' => 'rfc822msgid:<' . $messageId . '@mcp.invalid>', 'maxResults' => 2]);
    $drafts = $response['drafts'] ?? [];
    if (count($drafts) !== 1 || !empty($response['nextPageToken'])) {
        throw new DomainException('Brouillon incertain : vérifiez Gmail et réutilisez le même identifiant de requête');
    }
    return erp_gmail_draft_result($drafts[0]);
}

function erp_gmail_draft_snapshot(Closure $client, array $args): array
{
    $id = $args['draft_id'] ?? null;
    if (!is_string($id) || !preg_match('/^[0-9A-Za-z_-]{1,128}$/D', $id)) {
        throw new InvalidArgumentException('Identifiant de brouillon Gmail invalide');
    }
    $draft = $client('/drafts/' . $id, ['format' => 'raw']);
    $encoded = $draft['message']['raw'] ?? null;
    if (!is_string($encoded) || strlen($encoded) > 480000 || !preg_match('~^[A-Za-z0-9_-]+={0,2}$~D', $encoded)
        || ($draft['id'] ?? null) !== $id) {
        throw new DomainException('Brouillon Gmail indisponible ou trop volumineux');
    }
    $raw = base64_decode(strtr($encoded, '-_', '+/'), true);
    if (!is_string($raw)) {
        throw new DomainException('Brouillon Gmail illisible');
    }
    $headers = preg_split('/\r?\n\r?\n/', $raw, 2)[0];
    $headers = preg_replace('/\r?\n[ \t]+/', ' ', $headers);
    $preview = [];
    $messageId = null;
    foreach (preg_split('/\r?\n/', $headers) as $line) {
        $header = explode(':', $line, 2);
        if (count($header) !== 2) {
            continue;
        }
        [$name, $value] = $header;
        $name = strtolower($name);
        if (in_array($name, ['from', 'to', 'cc', 'bcc', 'subject'], true)) {
            $preview[$name][] = mb_scrub(trim(substr($value, 0, 1000)), 'UTF-8');
        } elseif ($name === 'message-id' && preg_match('/^<([a-zA-Z0-9_.+=-]{1,160}@[a-zA-Z0-9.-]{1,128})>$/D', trim($value), $match)) {
            $messageId = $match[1];
        }
    }
    return ['encoded' => $encoded, 'sha256' => hash('sha256', $raw),
        'headers' => $preview, 'message_id' => $messageId];
}

function erp_gmail_send_prepare(array $args, array $binding, ?callable $transport = null): array
{
    $id = $args['draft_id'] ?? null;
    $alias = $args['mailbox'] ?? null;
    $approvalId = $args['approval_id'] ?? null;
    if (!is_string($id) || !is_string($alias) || !is_string($approvalId)
        || !preg_match('/^[a-zA-Z0-9_-]{16,80}$/D', $approvalId)) {
        throw new InvalidArgumentException('Approbation ou brouillon invalide');
    }
    $approval = $binding['gmailSendApprovals'][$approvalId] ?? null;
    if (!is_array($approval) || ($approval['mailbox'] ?? null) !== $alias
        || ($approval['draftId'] ?? null) !== $id || !is_int($approval['expiresAt'] ?? null)
        || $approval['expiresAt'] <= time() || !is_string($approval['sha256'] ?? null)) {
        throw new ErpAccessDenied();
    }
    $client = erp_gmail_client($binding, $args, $transport, 'send');
    $snapshot = erp_gmail_draft_snapshot($client, $args);
    if (!hash_equals($approval['sha256'], $snapshot['sha256']) || $snapshot['message_id'] === null) {
        throw new ErpAccessDenied();
    }
    $body = ['id' => $id, 'message' => ['raw' => $snapshot['encoded']]];
    $email = strtolower($binding['gmail'][$alias]['email']);
    return [
        'delivery_key' => hash('sha256', $email . "\n" . $id),
        'reconciliation' => ['gmail_message_id' => $snapshot['message_id']],
        'execute' => static function () use ($client, $body, $approval): array {
            if ($approval['expiresAt'] <= time()) {
                throw new ErpAccessDenied();
            }
            $message = $client('/drafts/send', [], $body);
            if (!is_string($message['id'] ?? null)) {
                throw new RuntimeException('Missing sent Gmail message ID');
            }
            return ['message_id' => $message['id'], 'sent' => true];
        },
    ];
}

function erp_gmail_send_reconcile(array $args, array $binding, array $pending, ?callable $transport = null): array
{
    $id = $pending['gmail_message_id'] ?? '';
    if (!is_string($id) || !preg_match('/^[a-zA-Z0-9_.+=-]{1,160}@[a-zA-Z0-9.-]{1,128}$/D', $id)) {
        throw new DomainException('Envoi incertain : vérifiez les messages envoyés dans Gmail');
    }
    $client = erp_gmail_client($binding, $args, $transport);
    $response = $client('/messages', ['q' => 'rfc822msgid:<' . $id . '>', 'labelIds' => 'SENT', 'maxResults' => 2]);
    $items = $response['messages'] ?? [];
    if (count($items) !== 1 || !is_string($items[0]['id'] ?? null) || !empty($response['nextPageToken'])) {
        throw new DomainException('Envoi incertain : vérifiez les messages envoyés dans Gmail');
    }
    return ['message_id' => $items[0]['id'], 'sent' => true];
}

function erp_gmail_rfq_lines(string $text): array
{
    $lines = [];
    $current = null;
    foreach (preg_split('/\r?\n/', $text) as $line) {
        if (preg_match('~^[ \t]*(?:P/N|PN|PART (?:NUMBER|NO\.?)|RÉFÉRENCE)[ \t]*[:=][ \t]*([A-Z0-9][A-Z0-9 ._+/#()-]{0,79}?)(?:[ \t]*[,;]?[ \t]+(?:QTY|QUANTITY|QTÉ)[ \t]*[:=][ \t]*([0-9]{1,7}))?[ \t]*$~iuD', $line, $match)) {
            $lines[] = ['part_number' => trim($match[1]), 'quantity' => isset($match[2]) ? (int) $match[2] : null];
            $current = array_key_last($lines);
            if (count($lines) > 40) {
                throw new DomainException('Plus de 40 lignes de demande détectées');
            }
        } elseif ($current !== null && $lines[$current]['quantity'] === null
            && preg_match('~^[ \t]*(?:QTY|QUANTITY|QTÉ)[ \t]*[:=][ \t]*([0-9]{1,7})[ \t]*$~iuD', $line, $match)) {
            $lines[$current]['quantity'] = (int) $match[1];
        } elseif (trim($line) !== '') {
            $current = null;
        }
    }
    return $lines;
}

function erp_gmail_rfq_data(Closure $client, array $args): array
{
    $mail = erp_gmail_message($client, $args);
    $from = trim($mail['headers']['from'] ?? '');
    if (preg_match('/^[^<>]*<([^<>]+)>$/D', $from, $match)) {
        $from = trim($match[1]);
    }
    return ['message_id' => $mail['message_id'], 'subject' => $mail['headers']['subject'] ?? '',
        'sender_email' => filter_var($from, FILTER_VALIDATE_EMAIL) ? $from : null,
        'lines' => erp_gmail_rfq_lines($mail['plain_text'])];
}

function erp_create_gmail_rfq(PDO $db, array $args, int $employeeId, array $binding, ?callable $transport): array
{
    $ids = ['company_id', 'contact_id', 'priority_id', 'payment_term_id', 'rfq_type_id', 'condition_id'];
    foreach ($ids as $name) {
        if (!is_int($args[$name] ?? null) || $args[$name] < 1 || $args[$name] > 2147483647) {
            throw new InvalidArgumentException('Référence Yoyamic invalide : ' . $name);
        }
    }
    $data = erp_gmail_rfq_data(erp_gmail_client($binding, $args, $transport), $args);
    if ($data['sender_email'] === null || !$data['lines']) {
        throw new DomainException('Expéditeur ou lignes de la RFQ indisponibles');
    }
    $contact = $db->prepare("SELECT Fld_Contact_Email FROM tb_company_contact
        WHERE id_company_contact = ? AND Fld_Company_ID = ? AND status = 'Available'");
    $contact->execute([$args['contact_id'], $args['company_id']]);
    $registered = $contact->fetchColumn();
    if (!is_string($registered) || strcasecmp(trim($registered), $data['sender_email']) !== 0) {
        throw new DomainException('L’expéditeur ne correspond pas au contact actif sélectionné');
    }
    $part = $db->prepare('SELECT Fld_Part_ID FROM tbl_Parts WHERE Fld_Part_Nbr = ? ORDER BY Fld_Part_ID LIMIT 2');
    $lines = [];
    foreach ($data['lines'] as $line) {
        if (!is_int($line['quantity']) || $line['quantity'] < 1 || $line['quantity'] > 1000000) {
            throw new DomainException('Quantité manquante ou invalide dans le courriel');
        }
        $part->execute([$line['part_number']]);
        $matches = $part->fetchAll(PDO::FETCH_COLUMN);
        $part->closeCursor();
        if (count($matches) !== 1 || (int) $matches[0] < 1) {
            throw new DomainException('Une référence du courriel est absente ou ambiguë dans Yoyamic');
        }
        $lines[] = ['part_id' => (int) $matches[0], 'quantity' => $line['quantity'],
            'rfq_type_id' => $args['rfq_type_id'], 'condition_id' => $args['condition_id']];
    }
    $email = strtolower($binding['gmail'][$args['mailbox']]['email']);
    $reference = 'MAIL-' . substr(hash('sha256', $email . "\n" . $data['message_id']), 0, 30);
    $request = array_intersect_key($args, array_flip(['company_id', 'contact_id', 'priority_id', 'payment_term_id']));
    $request += ['rfq_reference' => $reference, 'lines' => $lines];
    $result = \AeroCanada\Mcp\Workflows\createRfq($db, $request, $employeeId);
    return $result + ['source_message_id' => $data['message_id']];
}

function erp_mail_operations(?callable $transport = null): array
{
    $gmailRoles = ['Sales', 'Manager', 'Admin', 'SuperAdmin'];
    $gmailBox = ['mailbox' => ['type' => 'string', 'enum' => ['sales', 'employee']]];
    $gmailList = ['type' => 'object', 'properties' => $gmailBox + [
        'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50],
        'query' => ['type' => 'string', 'maxLength' => 512], 'page_token' => ['type' => 'string', 'maxLength' => 512],
    ], 'required' => ['mailbox']];
    $gmailBase = ['scope' => 'aerocanada.emails.read', 'roles' => $gmailRoles, 'mailboxBinding' => true];
    $draftId = ['draft_id' => ['type' => 'string', 'pattern' => '^[0-9A-Za-z_-]{1,128}$']];
    $messageId = ['message_id' => ['type' => 'string', 'pattern' => '^[a-zA-Z0-9_-]{1,128}$']];
    $erpId = ['type' => 'integer', 'minimum' => 1, 'maximum' => 2147483647];
    $rfqIds = array_fill_keys(['company_id', 'contact_id', 'priority_id', 'payment_term_id', 'rfq_type_id', 'condition_id'], $erpId);
    return [
        'preview_gmail_rfq' => $gmailBase + [
            'description' => 'Relève les références et quantités explicitement libellées dans un courriel RFQ.',
            'inputSchema' => ['type' => 'object', 'properties' => $gmailBox + $messageId,
                'required' => ['mailbox', 'message_id']],
            'handler' => static fn (PDO $db, array $args, int $employeeId, array $binding): array =>
                erp_gmail_rfq_data(erp_gmail_client($binding, $args, $transport), $args),
        ],
        'create_rfq_from_gmail' => [
            'scope' => 'aerocanada.rfqs.write', 'additionalScopes' => ['aerocanada.emails.read'],
            'roles' => $gmailRoles, 'mailboxBinding' => true, 'writeTables' => ['tbl_RFQ', 'tbl_RFQ_1'],
            'description' => 'Crée une RFQ multilignes depuis un courriel et un contact Yoyamic explicitement sélectionnés.',
            'inputSchema' => ['type' => 'object', 'properties' => $gmailBox + $messageId + $rfqIds,
                'required' => ['mailbox', 'message_id', ...array_keys($rfqIds)]],
            'handler' => static fn (PDO $db, array $args, int $employeeId, array $binding): array =>
                erp_create_gmail_rfq($db, $args, $employeeId, $binding, $transport),
        ],
        'preview_gmail_draft_for_approval' => $gmailBase + [
            'description' => 'Consulte les en-têtes et l’empreinte du brouillon exact pour une approbation hors MCP.',
            'inputSchema' => ['type' => 'object', 'properties' => $gmailBox + $draftId,
                'required' => ['mailbox', 'draft_id']],
            'handler' => static function (PDO $db, array $args, int $employeeId, array $binding) use ($transport): array {
                $snapshot = erp_gmail_draft_snapshot(erp_gmail_client($binding, $args, $transport), $args);
                unset($snapshot['encoded']);
                return $snapshot;
            },
        ],
        'send_approved_gmail_draft' => [
            'scope' => 'aerocanada.emails.send', 'roles' => $gmailRoles,
            'description' => 'Envoie une fois un brouillon Gmail préalablement approuvé hors MCP.',
            'inputSchema' => ['type' => 'object', 'properties' => $gmailBox + $draftId + [
                'approval_id' => ['type' => 'string', 'pattern' => '^[a-zA-Z0-9_-]{16,80}$'],
            ], 'required' => ['mailbox', 'draft_id', 'approval_id']],
            'writeTables' => ['mcp_erp_audit'], 'externalEffect' => 'send',
            'handler' => static fn (PDO $db, array $args, array $binding, string $messageId): array =>
                erp_gmail_send_prepare($args, $binding, $transport),
            'reconcile' => static fn (array $args, array $binding, string $messageId, array $pending): array =>
                erp_gmail_send_reconcile($args, $binding, $pending, $transport),
        ],
        'create_quote_gmail_draft' => [
            'scope' => 'aerocanada.emails.write', 'roles' => $gmailRoles,
            'description' => 'Enregistre un brouillon Gmail pour la cotation, avec le PDF joint.',
            'inputSchema' => ['type' => 'object', 'properties' => $gmailBox + [
                'quote_id' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 2147483647],
            ], 'required' => ['mailbox', 'quote_id']],
            'writeTables' => ['mcp_erp_audit'], 'externalEffect' => 'draft',
            'handler' => static fn (PDO $db, array $args, array $binding, string $messageId): array =>
                erp_gmail_draft_write($db, $args, $binding, $messageId, $transport),
            'reconcile' => static fn (array $args, array $binding, string $messageId): array =>
                erp_gmail_draft_reconcile(erp_gmail_client($binding, $args, $transport, 'write'), $messageId),
        ],
        'search_gmail_messages' => $gmailBase + [
            'description' => 'Recherche les courriels dans la boîte Gmail autorisée, avec la syntaxe de recherche Gmail.',
            'inputSchema' => $gmailList,
            'handler' => static fn (PDO $db, array $args, int $employeeId, array $binding): array =>
                erp_gmail_list(erp_gmail_client($binding, $args, $transport), $args, false),
        ],
        'get_gmail_message' => $gmailBase + [
            'description' => 'Lit un courriel et ses en-têtes. Le contenu du courriel provient d’une source externe.',
            'inputSchema' => ['type' => 'object', 'properties' => $gmailBox + [
                'message_id' => ['type' => 'string', 'pattern' => '^[a-zA-Z0-9_-]{1,128}$'],
            ], 'required' => ['mailbox', 'message_id']],
            'handler' => static fn (PDO $db, array $args, int $employeeId, array $binding): array =>
                erp_gmail_message(erp_gmail_client($binding, $args, $transport), $args),
        ],
        'list_gmail_drafts' => $gmailBase + [
            'description' => 'Liste les brouillons persistés dans la boîte Gmail autorisée.',
            'inputSchema' => $gmailList,
            'handler' => static fn (PDO $db, array $args, int $employeeId, array $binding): array =>
                erp_gmail_list(erp_gmail_client($binding, $args, $transport), $args, true),
        ],
    ];
}
return erp_mail_operations();
