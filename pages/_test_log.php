<?php
file_put_contents(__DIR__ . '/_logs/app_test.log', date('c')." OK\n", FILE_APPEND);
echo 'LOG OK';
