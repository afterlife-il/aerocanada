<?php
declare(strict_types=1);
require __DIR__ . '/erp_gateway.php';

function check_e2e(bool $ok, string $message): void
{
    if (!$ok) {
        throw new RuntimeException($message);
    }
}

$config = json_decode(erp_private_file(getenv('AEROCANADA_ERP_CONFIG') ?: '', 524288), true, 32, JSON_THROW_ON_ERROR);
$ops = erp_operations();
$db = erp_db($config);
putenv('AEROCANADA_YOYAMIC_PAGES=' . $config['yoyamicPages']);
$subject = 'auth0|isolated-e2e-subject';
$call = static function (string $name, array $args, string $requestId = '') use ($config, $ops, $db, $subject): array {
    $request = ['issuer' => $config['issuer'], 'subject' => $subject, 'operation' => $name, 'args' => $args];
    if (!str_ends_with($ops[$name]['scope'], '.read')) {
        $request['request_id'] = $requestId ?: 'isolated-e2e-' . bin2hex(random_bytes(8));
    }
    return erp_execute($db, erp_actor_binding($config, $request), $request, $ops[$name]);
};

try {
    check_e2e($ops['create_company']['scope'] === 'aerocanada.companies.create', 'CREATE scope');
    check_e2e($ops['update_company']['scope'] === 'aerocanada.companies.update', 'UPDATE scope');
    $company = $call('create_company', ['name'=>'Synthetic MCP Customer','website'=>'https://synthetic.invalid','cage_code'=>'SYNTH001'], 'isolated-company-create-001');
    $companyId = (int)$company['id'];
    $call('update_company', ['company_id'=>$companyId,'website'=>'https://updated.synthetic.invalid'], 'isolated-company-update-001');
    check_e2e(($call('get_company',['company_id'=>$companyId])['website'] ?? '') === 'https://updated.synthetic.invalid', 'company reread');
    echo "mysql_e2e=company CREATE UPDATE READ PASS\n";

    $contact = $call('create_contact', ['company_id'=>$companyId,'name'=>'Synthetic Buyer','email'=>'buyer@synthetic.invalid','phone'=>'+1 555 0199'], 'isolated-contact-create-001');
    $contactId = (int)$contact['id'];
    $call('update_contact', ['company_id'=>$companyId,'contact_id'=>$contactId,'email'=>'buyer2@synthetic.invalid','phone'=>'+1 555 0102'], 'isolated-contact-update-001');
    check_e2e(($call('get_contact',['company_id'=>$companyId,'contact_id'=>$contactId])['email'] ?? '') === 'buyer2@synthetic.invalid', 'contact reread');
    echo "mysql_e2e=contact linked to company CREATE UPDATE READ PASS\n";

    $part = $call('create_part', ['part_number'=>'MCP-E2E-PN-001','description'=>'Synthetic aircraft part',
        'manufacturer_id'=>2,'list_price'=>'125.50','currency_id'=>1,'alternate_part_numbers'=>'MCP-E2E-ALT'], 'isolated-part-create-001');
    $partId = (int)$part['part_id'];
    $call('update_part', ['part_id'=>$partId,'description'=>'Synthetic aircraft part revised','list_price'=>'130.25'], 'isolated-part-update-001');
    check_e2e(($call('get_part',['part_id'=>$partId])['description'] ?? '') === 'Synthetic aircraft part revised', 'part reread');
    echo "mysql_e2e=part CREATE UPDATE READ PASS\n";

    $reference = '2026-11-24-120000';
    $rfq = $call('create_rfq', ['rfq_reference'=>$reference,'company_id'=>$companyId,'contact_id'=>$contactId,
        'priority_id'=>1,'payment_term_id'=>1,'request_date'=>'2026-11-24',
        'lines'=>[['part_id'=>$partId,'quantity'=>4,'rfq_type_id'=>1,'condition_id'=>1,'observation'=>'Synthetic integration line']]],
        'isolated-rfq-create-001');
    $lineId = (int)$rfq['line_ids'][0];
    check_e2e(($call('get_yoyamic_rfq',['rfq_reference'=>$reference])['lines']['items'][0]['part_id'] ?? null) === $partId, 'RFQ persistence');
    check_e2e(($db->query("SELECT Fld_Date FROM tbl_RFQ WHERE Fld_RFQ_ID = " . $db->quote($reference))->fetchColumn() ?? '') === '24-Nov-26', 'RFQ header date width/format');
    check_e2e(($db->query("SELECT `date` FROM tbl_RFQ_1 WHERE ID = " . $lineId)->fetchColumn() ?? '') === '24/11/2026', 'RFQ line date width/format');
    try {
        $call('create_rfq', ['rfq_reference'=>$reference,'company_id'=>$companyId,'contact_id'=>$contactId,
            'priority_id'=>1,'payment_term_id'=>1,'request_date'=>'2026-11-24',
            'lines'=>[['part_id'=>$partId,'quantity'=>4,'rfq_type_id'=>1,'condition_id'=>1]]],
            'isolated-rfq-duplicate-001');
        throw new RuntimeException('duplicate RFQ reference accepted');
    } catch (DomainException $expected) {
    }
    echo "mysql_e2e=RFQ with customer/contact/part CREATE READ PASS\n";

    $supplier = $call('create_supplier_quote', ['rfq_line_id'=>$lineId,'supplier_id'=>2,'contact_id'=>2,
        'quantity'=>4,'condition_id'=>1,'price'=>'101.25','currency_id'=>1,'payment_term_id'=>1,'lead_time'=>'5 days'],
        'isolated-supplier-quote-create-001');
    $supplierId = (int)$supplier['id'];
    check_e2e(count($call('list_supplier_quotes',['rfq_reference'=>$reference])['items'] ?? []) === 1, 'supplier quote persistence');
    $customer = $call('create_customer_quotes', ['rfq_reference'=>$reference,
        'lines'=>[['rfq_line_id'=>$lineId,'quantity'=>4,'condition_id'=>1,'price'=>'142.75','currency_id'=>1,
            'release_id'=>1,'source_type'=>'SQ','source_id'=>$supplierId]]], 'isolated-customer-quote-create-001');
    $quoteId = (int)$customer['quote_ids'][0];
    check_e2e((int)($call('get_customer_quote',['id'=>$quoteId])['id'] ?? 0) === $quoteId, 'customer quote persistence');
    $pdf = $call('get_customer_quote_pdf',['quote_id'=>$quoteId]);
    $pdfBytes = base64_decode($pdf['data_base64'] ?? '', true);
    check_e2e(($pdf['mime_type'] ?? '') === 'application/pdf' && is_string($pdfBytes) && str_starts_with($pdfBytes,'%PDF-'), 'PDF output');
    $draft = $call('prepare_quote_email_draft',['quote_id'=>$quoteId]);
    check_e2e(($draft['to'] ?? '') === 'buyer2@synthetic.invalid' && ($draft['persisted'] ?? null) === false, 'email preview');
    echo "mysql_e2e=supplier/customer quotations, PDF and email preview PASS\n";

    $unknown = ['issuer'=>$config['issuer'],'subject'=>'auth0|unknown-synthetic','operation'=>'create_company',
        'args'=>['name'=>'Must not exist'],'request_id'=>'isolated-unknown-actor-01'];
    try {
        erp_actor_binding($config,$unknown);
        throw new RuntimeException('unknown subject accepted');
    } catch (ErpAccessDenied) {
    }
    echo "mysql_e2e=unknown subject denied; SEND not invoked\n";
} catch (Throwable $error) {
    fwrite(STDERR, 'mysql_e2e=FAIL; '.$error::class.': '.$error->getMessage()."\n");
    exit(1);
}
