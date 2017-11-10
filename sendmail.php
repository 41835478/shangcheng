<?php



$fp = fsockopen("smtp.139.com", 465, $errno, $errstr, 10);
if (!$fp) {
    echo "$errstr ($errno)<br />\n";
} else {
    echo "Connected";
}
?>