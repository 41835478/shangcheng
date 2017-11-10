<?php
define('IN_PRINCE', true);
require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/cls_json.php');
$id=$_SESSION['user_id']?$_SESSION['user_id']:0 ;
$sql  = 'SELECT isfollow FROM ' .$GLOBALS['yp']->table('users'). "  WHERE user_id = '$id'";
$isfollow = $GLOBALS['db']->getOne($sql);

$guide_qrcode = $GLOBALS['db']->getOne("SELECT guide_qrcode FROM ".$GLOBALS['yp']->table('weixin_config'));
$web_url =HTTP_TYPE.'://'.$_SERVER['HTTP_HOST'];

$arr['user_id']=$_SESSION['user_id']?$_SESSION['user_id']:0 ;
$arr['isfollow']=$isfollow ?$isfollow :0 ;
$arr['got_u']=intval($_GET['u'])?intval($_GET['u']) :0 ;

$arr['guide_qrcode']=$web_url.'/'.$guide_qrcode ;

$json = new JSON;
echo $json->encode($arr);

?>