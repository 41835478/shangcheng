<?php
define('IN_PRINCE', true);
require_once('../includes/init.php');

$weburl = $_SERVER['SERVER_NAME'] ? "http://".$_SERVER['SERVER_NAME']."/" : "http://".$_SERVER['HTTP_HOST']."/";
$token = $_GET['token'];
$t = $_GET['t'];
$wxid = $_GET['wxid'];
$url = $_GET['url'] ? $_GET['url'] : $weburl;
$url = strpos($url,"http") == false ? $weburl.$url : $url;
if($token == md5($wxid.TOKEN.$t) && $t+86400>time() && !$_SESSION['user_id']){
	$user_info= $GLOBALS['db']->getRow("select * from " . $GLOBALS['yp']->table('users') . " where fake_id='{$wxid}'");
	if($user_info){
		$username = $user_info['user_name'];
		$GLOBALS['user']->set_session($username);
        $GLOBALS['user']->set_cookie($username,1);
		update_user_info();  //更新用户信息
        recalculate_price(); // 重新计算购物车中的商品价格
	}
}
header("Location: {$url}");
exit;