<?php
/*今天优品 
© 2005-2016 今天优品多商户系统 
 QQ 120029121 QQ 309485552*/

$db = $GLOBALS['db'];
$code = !empty($_GET['code']) ? $_GET['code'] : '';
$up_uid = trim($_REQUEST['u'])?intval(trim($_REQUEST['u'])):intval($_GET['u']) ;


$is_wechat_browser=oauth_is_wechat_browser();

if(1){ 
		$testurl=$_SESSION['user_id'].'-'.$_SESSION['user_name'].'-'.$_COOKIE["openid"].'-'.$code.' http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $output= strftime("%Y%m%d %H:%M:%S", time()) . "\n" ;
        $output .= $testurl."\n" ;
        $output.="\n";
        $log_path=PC_ROOT_PATH . "/data/log/";
        if(!is_dir($log_path)){
            @mkdir($log_path, 0777, true);
        }
        $output_date= strftime("%Y%m%d", time());
        file_put_contents($log_path.$output_date."_oauth.txt", $output, FILE_APPEND | LOCK_EX);
}



if(empty($_SESSION['user_id'])&& (empty($code) )&& $is_wechat_browser){
	$appid = $db -> getOne("SELECT appid FROM ".$GLOBALS['yp']->table('weixin_config')." WHERE `id` = 1");
	$backurl=HTTP_TYPE.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$redirect_uri = urlencode($backurl);
	$state = 1;
	$scope = 'snsapi_userinfo';
	$oauth_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $appid . '&redirect_uri=' . $redirect_uri . '&response_type=code&scope=' . $scope . '&state=' . $state . '#wechat_redirect';
	header("Location: $oauth_url");
	exit;
}






if (empty($_SESSION['user_id'])){

    if(!empty($code)){

		$wxch_config = $db->getRow("SELECT * FROM ".$GLOBALS['yp']->table('weixin_config')." WHERE `id` = 1");
		$appid = $wxch_config['appid'];
		$appsecret = $wxch_config['appsecret'];
		$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$appsecret.'&code='.$code.'&grant_type=authorization_code';
		$ret_json = wx_curl_get_contents($url);
		$ret = json_decode($ret_json);
		$openid = $ret->openid;
		
        $output .= 'code:'.$code.' openid:'.$openid.' setcookie:'.$_COOKIE["openid"]."\n" ;
        $log_path=ROOT_PATH . "/data/log/";
        file_put_contents($log_path."first.txt", $output, FILE_APPEND | LOCK_EX);
		
        setcookie("openid",$openid ,time()+3600*24*7);
		if(empty($_COOKIE["openid"])){
			$backurl=HTTP_TYPE.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$redirect_uri = urlencode($backurl);
			$state = 1;
			$scope = 'snsapi_userinfo';
			$oauth_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $appid . '&redirect_uri=' . $redirect_uri . '&response_type=code&scope=' . $scope . '&state=' . $state . '#wechat_redirect';
			header("Location: $oauth_url");
			exit;
		}

		$access_token = !empty($ret->access_token) ? $ret->access_token : '';
		$url='https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid;
		$ret_json = wx_curl_get_contents($url);
		$ret = json_decode($ret_json);
		$fromUsername=$ret->openid;
		$nickname=$ret->nickname;
		$headimg=$ret->headimgurl;
		$openid=$ret->openid;

		$ret = $GLOBALS['db'] -> getOne("SELECT count(*) FROM " . $GLOBALS['yp']->table('users') . " WHERE `fake_id` = '$openid'");
		$createtime = time();
		$createymd = date('Y-m-d');

		if ($ret==0) {
			if (!empty($openid)) {
				$sql = "insert into ".$GLOBALS['yp']->table('users')." 				(`fake_id`,`parent_id`,`is_bind`,`email`,`reg_time`,`createymd`,`isfollow`,`nickname`,`access_token`,`expire_in`,`headimg`,`from_id`,`froms`) 
					value ('{$openid}','$up_uid',1,'','{$createtime}','{$createymd}',0,'$nickname','','0','$headimg',1,'mobile')";
				$GLOBALS['db']->query($sql);//注册粉丝
				
				$user_info = $GLOBALS['db'] ->getRow("SELECT * FROM ". $GLOBALS['yp']->table('users') ." WHERE `fake_id` = '$openid'");
				$autoreg = $GLOBALS['db']->getRow("SELECT * FROM " . $GLOBALS['yp']->table('weixin_autoreg') . " WHERE `autoreg_id` = 1");//获取微信自动注册配置
				$userpwd = $autoreg['userpwd'];//密码前缀
				$autoreg_rand = $autoreg['autoreg_rand'];//随机密码长度
				$s_mima = randomkeys($autoreg_rand);
				$pwd = $userpwd.$s_mima;
				$md5password=md5($pwd);
				$aite_id = 'weixin_'.$openid;
				$time=time();	
				$user_name=$autoreg['autoreg_name'].date('ym').$user_info['user_id'];
				$user_id=$user_info['user_id'];
				
				$GLOBALS['db']->query("UPDATE ".$GLOBALS['yp']->table('users')." SET `is_fenxiao`=1,`aite_id`='$aite_id' ,`password`='$md5password',`ec_salt`='', `passwd_weixin` =  '$pwd',`user_name`='$user_name',`froms`='mobile',`is_bind`=1 WHERE `user_id` = '$user_id'");
				
				if (!empty($GLOBALS['_CFG']['register_points'])){
					log_account_change($user_info['user_id'], 0, 0, $GLOBALS['_CFG']['register_points'], $GLOBALS['_CFG']['register_points'], '注册赠送');
				}
				
				 if($up_uid){
					 $wap_url_sql = "SELECT `wap_url` FROM " . $GLOBALS['yp']->table('weixin_config') . " WHERE `id`=1";
					 $wap_url = $db -> getOne($wap_url_sql);
                     @file_get_contents($wap_url."weixin/weixin_remind.php?notice=5&up_uid=".$up_uid."&my_name=".$user_name);
				 }
				
			} 
		}
	}
}






if(!empty($openid) && strlen($openid) == 28){		
		$w_res = $db->getRow("SELECT * FROM  ". $GLOBALS['yp']->table('users') ." WHERE  `fake_id` = '$openid'");
		$_SESSION['wxid'] = $openid;	
		if ($user->login($w_res['user_name'], null, true)) {
			update_user_info();
			recalculate_price();
		}
}

function wx_curl_get_contents($url) 
{
	if(isset($_SERVER['HTTP_USER_AGENT'])) {
		$agent = $_SERVER['HTTP_USER_AGENT'];
	} else {
		$agent = '';
	}

	if(isset($_SERVER['HTTP_REFERER'])) {
		$referer = $_SERVER['HTTP_REFERER'];
	} else {
		$referer = '';
	}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_TIMEOUT,1);
	curl_setopt($ch, CURLOPT_USERAGENT, $agent);
	curl_setopt($ch, CURLOPT_REFERER,$referer);
	curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	$r = curl_exec($ch);
	curl_close($ch);
	return $r;
}

/* 检查是否是微信浏览器访问 */
function oauth_is_wechat_browser(){
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    if (strpos($user_agent, 'MicroMessenger') === false){
      return false;
    } else {
      return true;
    }
}

function randomkeys($length)//随机密码
	{
		$pattern='1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
		for($i=0;$i<$length;$i++)
		{
			$key .= $pattern{mt_rand(0,35)};    //生成php随机数
		}
		return $key;
	}	
	
?>



