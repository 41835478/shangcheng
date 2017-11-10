<?php
define('IN_PRINCE', true);
require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/cls_json.php');

$output = $GLOBALS['smarty']->fetch('library/rs_member_info.lbi');

$arr['rs_memberinfo']=$output;

$json = new JSON;
echo $json->encode($arr);

?>