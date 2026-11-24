<?php
declare(strict_types=1);

require __DIR__ . '/erp_gateway.php';
require __DIR__ . '/erp_mail.php';
require __DIR__ . '/erp_workflows.php';
$documentOperations = require __DIR__ . '/erp_documents.php';

function mail_check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new LogicException($message);
    }
}

final class MailDocumentStatement extends PDOStatement
{
    private array $bound = [];
    public function __construct(private MailDocumentFixture $db, private array $quote, private string $sql) {}
    public function bindValue(int|string $param, mixed $value, int $type = PDO::PARAM_STR): bool
    {
        $this->bound[$param] = $value;
        return true;
    }
    public function execute(?array $params = null): bool
    {
        mail_check(substr_count($this->sql, '?') === count($params ?? $this->bound), 'Invalid PDO binding');
        if (preg_match('/^INSERT INTO `?tbl_RFQ(?:`|_)/D', $this->sql)) {
            $this->db->lastId++;
            $this->db->created = true;
        }
        return true;
    }
    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed
    {
        return $this->quote + ['Fld_Part_Nbr' => 'SYNTH', 'Fld_Part_Desc' => 'Synthetic part'];
    }
    public function closeCursor(): bool { return true; }
    public function fetchColumn(int $column = 0): mixed
    {
        if (str_starts_with(ltrim($this->sql), 'SELECT Fld_Contact_Email')) {
            return 'contact@example.test';
        }
        if (str_starts_with(ltrim($this->sql), 'SELECT ID FROM tbl_RFQ')) {
            return $this->db->created ? 1 : false;
        }
        return 'fixture.pdf';
    }
    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        return $mode === PDO::FETCH_COLUMN ? [1] : [['document_id' => 1, 'title' => 'Synthetic fixture']];
    }
}

final class MailDocumentFixture extends PDO
{
    public int $lastId = 0;
    public bool $created = false;
    public function __construct(private array $quote) {}
    public function lastInsertId(?string $name = null): string|false { return (string) $this->lastId; }
    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        return new MailDocumentStatement($this, $this->quote, $query);
    }
}

$pages = getenv('AEROCANADA_YOYAMIC_PAGES');
if (!$pages) {
    throw new LogicException('AEROCANADA_YOYAMIC_PAGES must point to the read-only application PDF library');
}
$temp = sys_get_temp_dir() . '/erp-doc-test-' . bin2hex(random_bytes(8));
mkdir($temp, 0700);
try {
    $tokenFile = $temp . '/token';
    file_put_contents($tokenFile, 'synthetic-google-token-abc');
    chmod($tokenFile, 0600);
    $binding = ['employeeEmail' => 'employee@example.test',
        'gmail' => ['employee' => ['email' => 'employee@example.test', 'readTokenFile' => $tokenFile, 'draftTokenFile' => $tokenFile, 'sendTokenFile' => $tokenFile]]];
    $args = ['mailbox' => 'employee'];
    $calls = [];
    $fixtureMime = "From: employee@example.test\r\nTo: contact@example.test\r\nSubject: Synthetic\r\nMessage-ID: <msg@example.test>\r\n\r\nSynthetic body";
    $fixtureRaw = rtrim(strtr(base64_encode($fixtureMime), '+/', '-_'), '=');
    $transport = static function (string $path, array $query, string $token, ?array $body = null) use (&$calls, $fixtureRaw): array {
        mail_check($token === 'synthetic-google-token-abc', 'Bad scoped token');
        $calls[] = [$path, $query, $body];
        return match ($path) {
            '/profile' => ['emailAddress' => 'employee@example.test'],
            '/messages' => ($query['labelIds'] ?? null) === 'SENT'
                ? ['messages' => [['id' => 'sent-synthetic']]]
                : ['messages' => [['id' => 'synthetic', 'threadId' => 'thread']], 'nextPageToken' => 'page'],
            '/messages/synthetic' => ['threadId' => 'thread', 'payload' => [
                'headers' => [['name' => 'Subject', 'value' => 'Synthetic RFQ'],
                    ['name' => 'From', 'value' => 'Fixture <contact@example.test>']],
                'parts' => [
                    ['mimeType' => 'text/html', 'body' => ['data' => base64_encode('<script>ignored</script>')]],
                    ['mimeType' => 'text/plain', 'body' => ['data' => base64_encode('Synthetic RFQ body')]],
                ],
            ]],
            '/messages/source-rfq' => ['payload' => ['mimeType' => 'text/plain',
                'headers' => [['name' => 'From', 'value' => 'Fixture <contact@example.test>']],
                'body' => ['data' => base64_encode("PN: SYNTH-1; QTY: 3\nP/N: SYNTH-2\nQTY: 5")]]],
            '/drafts/draft-approved' => ['id' => 'draft-approved', 'message' => ['raw' => $fixtureRaw]],
            '/drafts/send' => ($body['message']['raw'] ?? null) === $fixtureRaw
                ? ['id' => 'sent-synthetic'] : throw new LogicException('Send MIME differs from approved payload'),
            '/drafts' => $body ? ['id' => 'draft-new', 'message' => ['id' => 'new']]
                : ['drafts' => [['id' => 'draft-1', 'message' => ['id' => 'synthetic']]]],
            default => throw new LogicException('Unexpected Gmail URL'),
        };
    };

    $client = erp_gmail_client($binding, $args, $transport);
    mail_check(erp_gmail_list($client, $args + ['query' => 'subject:RFQ'], false)['items'][0]['message_id'] === 'synthetic', 'Gmail list failed');
    mail_check(erp_gmail_message($client, $args + ['message_id' => 'synthetic'])['plain_text'] === 'Synthetic RFQ body', 'MIME filtering failed');
    mail_check(erp_gmail_list($client, $args, true)['items'][0]['draft_id'] === 'draft-1', 'Draft list failed');

    try {
        erp_gmail_client($binding, ['mailbox' => 'sales'], $transport);
        throw new LogicException('Unassigned Gmail box accepted');
    } catch (ErpAccessDenied) {
    }
    try {
        erp_gmail_client(array_replace($binding, ['employeeEmail' => 'other@example.test']), $args, $transport);
        throw new LogicException('Mismatched Google identity accepted');
    } catch (ErpAccessDenied) {
    }

    $quote = array_fill_keys(['rfq', 'quote_date', 'customer', 'contact', 'part_number', 'description',
        'quantity', 'part_condition', 'price', 'currency', 'certification', 'lead_time', 'remarks', 'sender', 'sender_email'], 'Synthétique');
    $quote['quote_id'] = 42;
    $attachment = erp_quote_render_pdf($quote);
    $pdf = base64_decode($attachment['data_base64'], true);
    mail_check(str_starts_with($pdf, '%PDF-') && str_contains($pdf, '%%EOF'), 'Invalid PDF output');

    $messageId = str_repeat('a', 64);
    $mime = erp_gmail_quote_mime('employee@example.test', 'contact@example.test', 42, $messageId, $attachment);
    $raw = base64_decode(strtr($mime['message']['raw'], '-_', '+/'), true);
    mail_check(str_contains($raw, 'application/pdf') && str_contains($raw, 'ACI770-Quote-42.pdf'), 'MIME PDF missing');
    mail_check(erp_gmail_draft_result($client('/drafts', [], $mime))['draft_id'] === 'draft-new', 'Gmail persistence response rejected');
    mail_check(erp_gmail_draft_reconcile($client, $messageId)['draft_id'] === 'draft-1', 'Gmail reconciliation failed');
    mail_check(count(array_filter($calls, fn ($call) => $call[2] !== null)) === 1, 'Unexpected Gmail write');

    file_put_contents($temp . '/fixture.pdf', $pdf);
    $result = erp_document_read_chunk($temp, 'fixture.pdf', 5);
    mail_check(base64_decode($result['data_base64'], true) === substr($pdf, 5), 'Attachment read failed');
    symlink($temp . '/fixture.pdf', $temp . '/linked.pdf');
    foreach (['../fixture.pdf', '..' . chr(92) . 'fixture.pdf', "line\nbreak.pdf", 'linked.pdf'] as $filename) {
        try {
            erp_document_read_chunk($temp, $filename, 0);
            throw new LogicException('Unsafe document access accepted');
        } catch (InvalidArgumentException | OutOfBoundsException) {
        }
    }
    $quote['recipient'] = 'contact@example.test';
    $db = new MailDocumentFixture($quote);
    $mailOperations = erp_mail_operations($transport);
    $mailLists = ['search_gmail_messages' => [], 'list_gmail_drafts' => [],
        'get_gmail_message' => ['message_id' => 'synthetic'],
        'preview_gmail_rfq' => ['message_id' => 'source-rfq']];
    foreach ($mailLists as $name => $extra) {
        mail_check((bool) $mailOperations[$name]['handler']($db, $args + $extra, 4, $binding), 'Unreachable tool ' . $name);
    }
    $draft = $mailOperations['create_quote_gmail_draft']['handler']($db, $args + ['quote_id' => 42], $binding, $messageId);
    mail_check($draft['execute']()['draft_id'] === 'draft-new', 'Draft handler failed');
    mail_check($documentOperations['get_customer_quote_pdf']['handler']($db, ['quote_id' => 42], 4)['mime_type'] === 'application/pdf', 'PDF handler failed');
    mail_check($documentOperations['prepare_quote_email_draft']['handler']($db, ['quote_id' => 42], 4)['to'] === 'contact@example.test', 'Draft preview handler failed');
    mail_check(count($documentOperations['list_company_documents']['handler']($db, ['company_id' => 4], 4)['items']) === 1, 'Document list handler failed');
    mkdir($temp . '/docsattachment', 0700);
    rename($temp . '/fixture.pdf', $temp . '/docsattachment/fixture.pdf');
    try {
        putenv('AEROCANADA_YOYAMIC_PAGES=' . $temp . '/pages');
        $chunk = $documentOperations['download_yoyamic_document']['handler']($db,
            ['kind' => 'part', 'parent_id' => 4, 'document_id' => 1], 4);
        mail_check(base64_decode($chunk['data_base64'], true) === $pdf, 'Document download handler failed');
    } finally {
        putenv('AEROCANADA_YOYAMIC_PAGES=' . $pages);
        unlink($temp . '/docsattachment/fixture.pdf');
        rmdir($temp . '/docsattachment');
    }
    $sendArgs = ['mailbox' => 'employee', 'draft_id' => 'draft-approved', 'approval_id' => 'synthetic-approval-00001'];
    $preview = $mailOperations['preview_gmail_draft_for_approval']['handler']($db, $sendArgs, 4, $binding);
    mail_check($preview['sha256'] === hash('sha256', $fixtureMime) && !isset($preview['encoded']), 'Draft preview digest');
    $approval = ['mailbox' => 'employee', 'draftId' => 'draft-approved',
        'sha256' => $preview['sha256'], 'expiresAt' => time() + 60];
    $send = $mailOperations['send_approved_gmail_draft'];
    foreach ([[], array_replace($approval, ['sha256' => str_repeat('0', 64)]),
        array_replace($approval, ['expiresAt' => time() - 1])] as $badApproval) {
        try {
            $send['handler']($db, $sendArgs, $binding + ['gmailSendApprovals' => [
                'synthetic-approval-00001' => $badApproval]], $messageId);
            throw new LogicException('Unapproved send accepted');
        } catch (ErpAccessDenied) {
        }
    }
    $approvedBinding = $binding + ['gmailSendApprovals' => ['synthetic-approval-00001' => $approval]];
    $effect = $send['handler']($db, $sendArgs, $approvedBinding, $messageId);
    mail_check($effect['execute']()['message_id'] === 'sent-synthetic', 'Simulated SEND failed');
    mail_check($send['reconcile']($sendArgs, $approvedBinding, $messageId, $effect['reconciliation'])['sent'], 'Simulated SEND reconciliation');
    mail_check(count(array_filter($calls, fn ($call) => $call[0] === '/drafts/send')) === 1, 'Unexpected Gmail SEND retry');

    $rfqArgs = $args + ['message_id' => 'source-rfq'] + array_fill_keys(
        ['company_id', 'contact_id', 'priority_id', 'payment_term_id', 'rfq_type_id', 'condition_id'], 1);
    $rfq = $mailOperations['create_rfq_from_gmail']['handler']($db, $rfqArgs, 4, $binding);
    mail_check(count($rfq['line_ids']) === 2 && $rfq['source_message_id'] === 'source-rfq', 'Gmail multiline import failed');
    try {
        $mailOperations['create_rfq_from_gmail']['handler']($db, $rfqArgs, 4, $binding);
        throw new LogicException('Duplicate Gmail RFQ accepted');
    } catch (DomainException) {
    }

    echo "12 outils exercés. Gmail simulé et PDF FPDF natif : boîtes, MIME, brouillon, réconciliation, téléchargement et refus vérifiés.\n";
} finally {
    foreach (glob($temp . '/*') as $path) {
        unlink($path);
    }
    rmdir($temp);
}
