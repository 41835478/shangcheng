<?php
define('IN_PRINCE', true);
require(dirname(__FILE__) . '/includes/init.php');
require(dirname(__FILE__) . '/weixin/wechat.class.php');

$weixinconfig = $GLOBALS['db']->getRow ( "SELECT * FROM " . $GLOBALS['yp']->table('weixin_config') . " WHERE `id` = 1" );

$weixin = new core_lib_wechat($weixinconfig);
if($_GET['code']){
	$json = $weixin->getOauthAccessToken();
	if($json['openid']){
		$rows = $GLOBALS['db']->getRow("SELECT * FROM " . $GLOBALS['yp']->table('users') . " WHERE fake_id='{$json['openid']}'");
		if($rows){
			$GLOBALS['user']->set_session($rows['username']);
			$GLOBALS['user']->set_cookie($rows['username'],1);
			update_user_info();  //更新用户信息
			recalculate_price(); //重新计算购物车中的商品价格
			header("Location:user.php");exit; 		
	}else{
			$createtime = gmtime();
			$createymd = date('Y-m-d',gmtime());
			$GLOBALS['db']->query("INSERT INTO ".$GLOBALS['yp']->table('users')." (`fake_id`,`createtime`,`createymd`,`isfollow`) 
				value ('" . $json['openid'] . "','{$createtime}','{$createymd}',0)"); 
				
			$user_info = $GLOBALS['db'] ->getRow("SELECT * FROM ". $GLOBALS['yp']->table('users') ." WHERE `fake_id` = '$openid'");
			$autoreg = $GLOBALS['db']->getRow("SELECT * FROM " . $GLOBALS['yp']->table('weixin_autoreg') . " WHERE `autoreg_id` = 1");//获取微信自动注册配置
			$userpwd = $autoreg['userpwd'];//密码前缀
			$autoreg_rand = $autoreg['autoreg_rand'];//随机密码长度
			$s_mima = random_pwdkeys($autoreg_rand);
			$pwd = $userpwd.$s_mima;
			$md5password=md5($pwd);
			$aite_id = 'weixin_'.$openid;
			$time=time();	
			$user_name=$autoreg['autoreg_name'].date('ym').$user_info['user_id'];
			$user_id=$user_info['user_id'];
			$GLOBALS['db']->query("UPDATE ".$GLOBALS['yp']->table('users')." SET `is_fenxiao`=1,`aite_id`='$aite_id' ,`password`='$md5password',`ec_salt`='', `passwd_weixin` =  '$pwd',`user_name`='$user_name',`froms`='mobile',`is_bind`=1 WHERE `user_id` = '$user_id'");
		}
	}
	$url = $GLOBALS['yp']->url()."user.php";
	header("Location:$url");exit;
}
$url = $GLOBALS['yp']->url()."weixin_login.php";
$url = $weixin->getOauthRedirect($url,1,'snsapi_userinfo');
header("Location:$url");exit;
?>