<?php
// company22.php — serveur DataTables pour COMPANY (7 colonnes, sans RATING)
session_start();
include_once "conf.php";
include_once "page_titles.php";
if (!isset($conn) && isset($link)) { $conn = $link; }

/* ====================== Helpers Local Time ====================== */
$ACI_TZ_BY_COUNTRY = [
  'israel'=>'Asia/Jerusalem','france'=>'Europe/Paris','united kingdom'=>'Europe/London','uk'=>'Europe/London',
  'germany'=>'Europe/Berlin','switzerland'=>'Europe/Zurich','italy'=>'Europe/Rome','spain'=>'Europe/Madrid',
  'portugal'=>'Europe/Lisbon','belgium'=>'Europe/Brussels','netherlands'=>'Europe/Amsterdam','poland'=>'Europe/Warsaw',
  'greece'=>'Europe/Athens','canada'=>'America/Toronto','united states'=>'America/New_York','usa'=>'America/New_York',
  'mexico'=>'America/Mexico_City','brazil'=>'America/Sao_Paulo','uae'=>'Asia/Dubai','qatar'=>'Asia/Qatar',
  'saudi arabia'=>'Asia/Riyadh','morocco'=>'Africa/Casablanca','south africa'=>'Africa/Johannesburg',
  'india'=>'Asia/Kolkata','china'=>'Asia/Shanghai','japan'=>'Asia/Tokyo','singapore'=>'Asia/Singapore','australia'=>'Australia/Sydney',
];
$US_STATE_ABBR = [
  'ALABAMA'=>'AL','ALASKA'=>'AK','ARIZONA'=>'AZ','ARKANSAS'=>'AR','CALIFORNIA'=>'CA','COLORADO'=>'CO','CONNECTICUT'=>'CT',
  'DELAWARE'=>'DE','FLORIDA'=>'FL','GEORGIA'=>'GA','HAWAII'=>'HI','IDAHO'=>'ID','ILLINOIS'=>'IL','INDIANA'=>'IN',
  'IOWA'=>'IA','KANSAS'=>'KS','KENTUCKY'=>'KY','LOUISIANA'=>'LA','MAINE'=>'ME','MARYLAND'=>'MD','MASSACHUSETTS'=>'MA',
  'MICHIGAN'=>'MI','MINNESOTA'=>'MN','MISSISSIPPI'=>'MS','MISSOURI'=>'MO','MONTANA'=>'MT','NEBRASKA'=>'NE','NEVADA'=>'NV',
  'NEW HAMPSHIRE'=>'NH','NEW JERSEY'=>'NJ','NEW MEXICO'=>'NM','NEW YORK'=>'NY','NORTH CAROLINA'=>'NC','NORTH DAKOTA'=>'ND',
  'OHIO'=>'OH','OKLAHOMA'=>'OK','OREGON'=>'OR','PENNSYLVANIA'=>'PA','RHODE ISLAND'=>'RI','SOUTH CAROLINA'=>'SC',
  'SOUTH DAKOTA'=>'SD','TENNESSEE'=>'TN','TEXAS'=>'TX','UTAH'=>'UT','VERMONT'=>'VT','VIRGINIA'=>'VA','WASHINGTON'=>'WA',
  'WEST VIRGINIA'=>'WV','WISCONSIN'=>'WI','WYOMING'=>'WY','DISTRICT OF COLUMBIA'=>'DC','PUERTO RICO'=>'PR',
  'GUAM'=>'GU','VIRGIN ISLANDS'=>'VI','AMERICAN SAMOA'=>'AS'
];
$ACI_TZ_US_BY_STATE = [
  'AL'=>'America/Chicago','AK'=>'America/Anchorage','AZ'=>'America/Phoenix','AR'=>'America/Chicago',
  'CA'=>'America/Los_Angeles','CO'=>'America/Denver','CT'=>'America/New_York','DC'=>'America/New_York',
  'DE'=>'America/New_York','FL'=>'America/New_York','GA'=>'America/New_York','HI'=>'Pacific/Honolulu','IA'=>'America/Chicago',
  'ID'=>'America/Boise','IL'=>'America/Chicago','IN'=>'America/Indiana/Indianapolis','KS'=>'America/Chicago',
  'KY'=>'America/New_York','LA'=>'America/Chicago','MA'=>'America/New_York','MD'=>'America/New_York','ME'=>'America/New_York',
  'MI'=>'America/Detroit','MN'=>'America/Chicago','MO'=>'America/Chicago','MS'=>'America/Chicago',
  'MT'=>'America/Denver','NC'=>'America/New_York','ND'=>'America/Chicago','NE'=>'America/Chicago',
  'NH'=>'America/New_York','NJ'=>'America/New_York','NM'=>'America/Denver',
  'NV'=>'America/Los_Angeles','NY'=>'America/New_York','OH'=>'America/New_York','OK'=>'America/Chicago',
  'OR'=>'America/Los_Angeles','PA'=>'America/New_York','RI'=>'America/New_York','SC'=>'America/New_York',
  'SD'=>'America/Chicago','TN'=>'America/Chicago','TX'=>'America/Chicago',
  'UT'=>'America/Denver','VA'=>'America/New_York','VT'=>'America/New_York',
  'WA'=>'America/Los_Angeles','WI'=>'America/Chicago','WV'=>'America/New_York','WY'=>'America/Denver',
  'PR'=>'America/Puerto_Rico','GU'=>'Pacific/Guam','VI'=>'America/St_Thomas','AS'=>'Pacific/Pago_Pago'
];
$CA_PROV_ABBR = [
  'ALBERTA'=>'AB','BRITISH COLUMBIA'=>'BC','MANITOBA'=>'MB','NEW BRUNSWICK'=>'NB','NEWFOUNDLAND AND LABRADOR'=>'NL',
  'NEWFOUNDLAND'=>'NL','NOVA SCOTIA'=>'NS','ONTARIO'=>'ON','PRINCE EDWARD ISLAND'=>'PE','QUEBEC'=>'QC',
  'SASKATCHEWAN'=>'SK','NORTHWEST TERRITORIES'=>'NT','NUNAVUT'=>'NU','YUKON'=>'YT'
];
$ACI_TZ_CA_BY_PROVINCE = [
  'AB'=>'America/Edmonton','BC'=>'America/Vancouver','MB'=>'America/Winnipeg','NB'=>'America/Moncton',
  'NL'=>'America/St_Johns','NS'=>'America/Halifax','ON'=>'America/Toronto','PE'=>'America/Halifax',
  'QC'=>'America/Toronto','SK'=>'America/Regina','NT'=>'America/Yellowknife','NU'=>'America/Iqaluit',
  'YT'=>'America/Whitehorse'
];

function aci_norm($s){ return strtoupper(trim((string)$s)); }
function aci_country_canonical(string $c): string {
    $k = strtolower(trim($c));
    $k = preg_replace('/[^\p{L}\p{Nd}]+/u', ' ', $k);
    $k = trim(preg_replace('/\s+/', ' ', $k));
    $map = [
        'united states of america' => 'united states','u s a' => 'usa','u s' => 'us','us' => 'us','usa' => 'usa',
        'great britain' => 'united kingdom','g b' => 'united kingdom','gb' => 'united kingdom',
    ];
    return $map[$k] ?? $k;
}
function aci_pick_timezone_from_fields(?string $utc_timezone, ?string $country_raw, ?string $state_raw, array $countryMap): string {
  global $US_STATE_ABBR, $ACI_TZ_US_BY_STATE, $CA_PROV_ABBR, $ACI_TZ_CA_BY_PROVINCE;
  if (!empty($utc_timezone)) return $utc_timezone;
  $country = aci_country_canonical((string)$country_raw);
  $state   = aci_norm($state_raw ?? '');
  if (in_array($country, ['united states','usa','us'], true) && $state !== '') {
    if (strlen($state) > 2 && isset($US_STATE_ABBR[$state])) $state = $US_STATE_ABBR[$state];
    if (isset($ACI_TZ_US_BY_STATE[$state])) return $ACI_TZ_US_BY_STATE[$state];
  }
  if (in_array($country, ['canada','ca'], true) && $state !== '') {
    if (strlen($state) > 2 && isset($CA_PROV_ABBR[$state])) $state = $CA_PROV_ABBR[$state];
    if (isset($ACI_TZ_CA_BY_PROVINCE[$state])) return $ACI_TZ_CA_BY_PROVINCE[$state];
  }
  if ($country !== '' && isset($countryMap[$country])) return $countryMap[$country];
  return 'UTC';
}
function aci_compute_local_time_from_fields(?string $utc_timezone, ?string $country_raw, ?string $state_raw, array $countryMap): array {
  try {
    $tz = aci_pick_timezone_from_fields($utc_timezone, $country_raw, $state_raw, $countryMap);
    $dt = new DateTime('now', new DateTimeZone($tz));
    return [$dt->format('H:i d/m/Y'), $dt->format('H:i')];
  } catch (Throwable $e) {
    return ['N/A', 'N/A'];
  }
}
/* ====================== /Helpers Local Time ====================== */

// Paramètres DataTables
$requestData   = $_REQUEST;
$companyrating = isset($_GET['companyrating']) ? trim($_GET['companyrating']) : 'all';

/* ====================== Filtres ====================== */
$filters = [];

$contactId = (isset($requestData['contact_id']) && $requestData['contact_id'] !== '')
           ? (int)$requestData['contact_id'] : 0;
if ($contactId > 0) {
  $filters[] = "c.aci_contact = {$contactId}";
}

if ($companyrating !== '' && $companyrating !== 'all') {
  $filters[] = "c.companyrating = '" . mysqli_real_escape_string($conn, $companyrating) . "'";
}

if (!empty($requestData['search']['value'])) {
  $sv = mysqli_real_escape_string($conn, $requestData['search']['value']);
  $filters[] = "("
    . " c.Fld_Company_ID LIKE '%{$sv}%' "
    . " OR c.Fld_Company_Name LIKE '%{$sv}%' "
    . " OR c.internet LIKE '%{$sv}%' "
    . " OR c.aci_contact LIKE '%{$sv}%' "
    . " OR c.logocompany LIKE '%{$sv}%' "
    . " OR c.cage_code LIKE '%{$sv}%' "
    . " OR d.Fld_Remark LIKE '%{$sv}%' "
    . " OR e.Employee_Name LIKE '%{$sv}%' "
    . ")";
}

$where = "WHERE c.status = 'Available'" . (count($filters) ? " AND " . implode(" AND ", $filters) : "");

/* ====================== Counts ====================== */
$sqlCountTotal = "
  SELECT COUNT(DISTINCT c.Fld_Company_ID) AS cnt
  FROM tb_company c
  WHERE c.status = 'Available'
";
$resTotal  = mysqli_query($conn, $sqlCountTotal);
$rowTotal  = mysqli_fetch_assoc($resTotal);
$totalData = (int)($rowTotal['cnt'] ?? 0);

$sqlCountFiltered = "
  SELECT COUNT(DISTINCT c.Fld_Company_ID) AS cnt
  FROM tb_company c
  LEFT JOIN tbl_Company_Details d ON d.Fld_Company_ID = c.Fld_Company_ID
  LEFT JOIN tbl_Employee        e ON e.Employee_ID     = c.aci_contact
  {$where}
";
$resFiltered   = mysqli_query($conn, $sqlCountFiltered);
$rowFiltered   = mysqli_fetch_assoc($resFiltered);
$totalFiltered = (int)($rowFiltered['cnt'] ?? 0);

/* ====================== Pagination & Tri ====================== */
$start  = isset($requestData['start'])  ? (int)$requestData['start']  : 0;
$length = isset($requestData['length']) ? (int)$requestData['length'] : 10;

$orderColumnIndex = isset($requestData['order'][0]['column']) ? (int)$requestData['order'][0]['column'] : 0;
$orderDir = (isset($requestData['order'][0]['dir']) && in_array(strtolower($requestData['order'][0]['dir']), ['asc','desc'], true))
            ? strtoupper($requestData['order'][0]['dir']) : 'ASC';

/* Map colonnes -> SQL (7 colonnes) */
$ORDERABLE = [
  0 => 'c.Fld_Company_ID',      // ID
  1 => 'c.logocompany',         // Logo
  2 => 'c.Fld_Company_Name',    // Company
  3 => 'c.cage_code',           // Cage
  4 => 'c.Fld_Company_Name',    // Local time (calculée) -> fallback
  5 => 'e.Employee_Name',       // Contact
  6 => 'c.Fld_Company_Name',    // Actions -> fallback
];
$orderColumnSql = $ORDERABLE[$orderColumnIndex] ?? 'c.Fld_Company_Name';

/* ====================== Data ====================== */
$sqlData = "
  SELECT
    c.Fld_Company_ID,
    c.Fld_Company_Name,
    c.Company_Old_Id,
    c.Fld_Company_Rating_ID,
    c.`delete`,
    c.companyrating,
    c.aci_contact,
    c.logocompany,
    c.status,
    c.internet,
    c.cage_code,
    e.Employee_Name,
    MAX(d.UTC_timezone)         AS UTC_timezone,
    MAX(d.Fld_Company_Country)  AS Fld_Company_Country,
    MAX(d.Fld_Company_State)    AS Fld_Company_State
  FROM tb_company c
  LEFT JOIN tbl_Company_Details d ON d.Fld_Company_ID = c.Fld_Company_ID
  LEFT JOIN tbl_Employee        e ON e.Employee_ID     = c.aci_contact
  {$where}
  GROUP BY c.Fld_Company_ID
  ORDER BY {$orderColumnSql} {$orderDir}
  LIMIT {$start}, {$length}
";
$q = mysqli_query($conn, $sqlData);
if (!$q) {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode([
    "draw"            => isset($requestData['draw']) ? intval($requestData['draw']) : 0,
    "recordsTotal"    => 0,
    "recordsFiltered" => 0,
    "data"            => [],
    "error"           => "SQL error: " . mysqli_error($conn)
  ]);
  exit;
}

/* ====================== Construction des lignes (7 colonnes) ====================== */
$data = [];
while ($row = mysqli_fetch_assoc($q)) {
  $nested = [];

  // 0) ID
  $nested[] = $row['Fld_Company_ID'];

  // 1) Logo
  if (!empty($row['logocompany'])) {
    $nested[] = "<a href='javascript:detailcompany(".$row['Fld_Company_ID'].")' title='company details'><img src='../logo_company/".htmlspecialchars($row['logocompany'], ENT_QUOTES, 'UTF-8')."' width='200'></a>";
  } else {
    $nested[] = "<div align='center'><span style='color:#be0831;font-family:\"Times New Roman\",Times,serif;font-style:oblique;font-weight:bold;'>No Logo Available</span></div>";
  }

  // 2) COMPANY
  $company_maj = strtoupper($row['Fld_Company_Name']);
  $nested[] = "<a href='javascript:detailcompany(".$row['Fld_Company_ID'].")' title='company details'>".htmlspecialchars($company_maj, ENT_QUOTES, 'UTF-8')."</a>";

  // 3) CAGE
  $nested[] = htmlspecialchars($row['cage_code'] ?? '', ENT_QUOTES, 'UTF-8');

  // 4) LOCAL TIME (calcul)
  list($displayTime, $hhmm) = aci_compute_local_time_from_fields(
    $row['UTC_timezone'] ?? null,
    $row['Fld_Company_Country'] ?? null,
    $row['Fld_Company_State'] ?? null,
    $ACI_TZ_BY_COUNTRY
  );
  if ($hhmm !== 'N/A') {
    $h_cur = (int)substr($hhmm, 0, 2);
    $color = ($h_cur >= 8 && $h_cur < 20) ? 'green' : 'red';
  } else {
    $color = 'gray';
  }
  $nested[] = "<span style='font-weight:bold;color:{$color}'>".htmlspecialchars($displayTime, ENT_QUOTES, 'UTF-8')."</span>";

  // 5) ACI 770 CONTACT
  $nested[] = htmlspecialchars($row['Employee_Name'] ?? '', ENT_QUOTES, 'UTF-8');

  // 6) ACTIONS
  $id = (int)$row['Fld_Company_ID'];
  $nested[] =
    "<a href='javascript:detailcompany({$id})' title='company details'>
       <i style='margin-left:10px;position:relative;top:4px;font-size:23px;' class='fa fa-plus-square'></i>
     </a>
     <a href='modif_company.php?Fld_Company_ID={$id}' title='Modification Company'>
       <i style='margin-left:10px;position:relative;top:4px;font-size:23px;' class='fa fa-pencil-square-o'></i>
     </a>
     <a href='ajout_contact_company.php?Fld_Company_ID={$id}' title='Add Contact Company'>
       <img src='images/add_contact2.png' width='35' alt='Add contact'>
     </a>
     <a href='archive_company.php?Fld_Company_ID={$id}' onClick=\"return(confirm('Etes-vous sûr ?'));\" title='Archive Company'>
       <i style='margin-left:10px;position:relative;top:4px;font-size:23px;' class='fa fa-archive'></i>
     </a>";

  $data[] = $nested;
}

/* ====================== JSON ====================== */
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
  "draw"            => isset($requestData['draw']) ? intval($requestData['draw']) : 0,
  "recordsTotal"    => $totalData,
  "recordsFiltered" => $totalFiltered,
  "data"            => $data
]);
exit;
