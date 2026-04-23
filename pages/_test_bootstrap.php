<?php
require_once __DIR__ . '/bootstrap.php';

$_SESSION['BOOTSTRAP_TEST'] = ($_SESSION['BOOTSTRAP_TEST'] ?? 0) + 1;
log_error('BOOTSTRAP_TEST_OK', ['counter' => $_SESSION['BOOTSTRAP_TEST']]);

echo "BOOTSTRAP OK - counter=" . (int)$_SESSION['BOOTSTRAP_TEST'];
