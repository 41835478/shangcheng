<?php
//By demo.coolhong.com 今天优品多商户系统 qq 120029121
//session_start();
//error_reporting(0); //关闭错误

header("Content-type:text/html; charset=UTF-8");
require_once ROOT_PATH.'sms/qq_120029121_dy/api_sdk/vendor/autoload.php';
use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Api\Sms\Request\V20170525\QuerySendDetailsRequest;
Config::load();




if($_GET['act'] == 'check'){
	/* 代码修改_start BY demo.coolhong.com 今天优品多商户系统 Q Q 1200 2912 1 */
	$mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
	$mobile_code = isset($_POST['mobile_code']) ? trim($_POST['mobile_code']) : '';
	/* 代码修改_end BY demo.coolhong.com 今天优品多商户系统 Q Q 1200 2912 1 */
	
	if(time() - $_SESSION['time'] > 30 * 60){
		unset($_SESSION['mobile_code']);
		exit(json_encode(array(
			'msg' => '验证码超过30分钟。'
		)));
	}
	else{
		if($mobile != $_SESSION['mobile'] or $mobile_code != $_SESSION['mobile_code']){
			exit(json_encode(array(
				'msg' => '手机验证码输入错误。'
			)));
		}
		else{
			exit(json_encode(array(
				'code' => '2'
			)));
		}
	}
 
}

if($_GET['act'] == 'send'){
	/* 代码修改_start BY demo.coolhong.com 今天优品多商户系统 Q Q 1200 2912 1 */
	$mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
	$mobile_code = isset($_POST['mobile_code']) ? trim($_POST['mobile_code']) : '';
	/* 代码修改_end BY demo.coolhong.com 今天优品多商户系统 Q Q 1200 2912 1 */
	
	//session_start();
	if(empty($mobile)){
		exit(json_encode(array(
			'msg' => '手机号码不能为空'
		)));
	}
	
	$preg = '/^1[0-9]{10}$/'; // 简单的方法
	if(! preg_match($preg, $mobile)){
		exit(json_encode(array(
			'msg' => '手机号码格式不正确'
		)));
	}
	
	$mobile_code = random(6, 1);
	$content = sprintf($GLOBALS['_CFG']['sms_register_tpl'],$mobile_code,$GLOBALS['_CFG']['sms_sign']);
	
	if($_SESSION['mobile']){
		if(strtotime(read_file($mobile)) > (time() - 60)){
			exit(json_encode(array(
				'msg' => '获取验证码太过频繁，一分钟之内只能获取一次。'
			)));
		}
	}
	
	$templateParam=Array("code"=>$mobile_code);
	$templateCode=$GLOBALS['_CFG']['sms_register_dayu'];
	$num = sendSMS($mobile, $content,'', '',$templateParam , $templateCode);//已支持阿里大于 QQ 120029121
	if($num == true){
		$_SESSION['mobile'] = $mobile;
		$_SESSION['mobile_code'] = $mobile_code;
		$_SESSION['time'] = time();
		exit(json_encode(array(
			'code' => 2
		)));
	}else{
		exit(json_encode(array(
			'msg' => '手机验证码发送失败。'
		)));
	}
}

//file_put_contents(ROOT_PATH."test.txt", "POST:".$mobile.$content.$time.$mid.$templateParam.$templateCode."\n", FILE_APPEND | LOCK_EX);

//发送短信核心方法
function sendSMS($mobile, $content, $time = '', $mid = '',$templateParam='' , $templateCode=''){//已支持阿里大于
	$key=$GLOBALS['_CFG']['dx_user_name'];
	$pwd=$GLOBALS['_CFG']['dx_pass_word'];
	$what_sms=$GLOBALS['_CFG']['what_sms'];
	$sms_sign=$GLOBALS['_CFG']['sms_sign'];
	if($what_sms==2){//阿里云短信服务 必须使用通过审核的模板

		if($templateCode){
			$accessKeyId = $key;
			$accessKeySecret = $pwd;
			// 短信API产品名
			$product = "Dysmsapi";
			// 短信API产品域名
			$domain = "dysmsapi.aliyuncs.com";
			// 暂时不支持多Region
			$region = "cn-hangzhou";
			// 服务结点
			$endPointName = "cn-hangzhou";
			// 初始化用户Profile实例
			$profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
			// 增加服务结点
			DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);
			// 初始化AcsClient用于发起请求
			$acsClient = new DefaultAcsClient($profile);
			// 初始化SendSmsRequest实例用于设置发送短信的参数
			$request = new SendSmsRequest();
			// 必填，设置雉短信接收号码
			$request->setPhoneNumbers($mobile);
			// 必填，设置签名名称
			$request->setSignName($sms_sign);
			// 必填，设置模板CODE
			$request->setTemplateCode($templateCode);
			// 可选，设置模板参数
			if($templateParam) {
				$request->setTemplateParam(json_encode($templateParam));
			}
			// 可选，设置流水号
			if($outId) {
				$request->setOutId($outId);
			}
			// 发起访问请求
			$acsResponse = $acsClient->getAcsResponse($request);
			// 打印请求结果
			//var_dump($acsResponse);
			$resp= (array)$acsResponse;
			if ($resp['Code']=='OK'){
			  return true;
			}else{
				file_put_contents(ROOT_PATH."test.txt", "POST:".$mobile.$content.$time.$mid.json_encode($templateParam).$templateCode.json_encode($resp)."\n", FILE_APPEND | LOCK_EX);			  
				return false; 
			}
		}else{
		  file_put_contents(ROOT_PATH."test.txt", "POST:".$mobile.$content.$time.$mid.json_encode($templateParam).$templateCode.'没传入模板'."\n", FILE_APPEND | LOCK_EX);	
		  return false; //没传入模板id
		}
	}elseif($what_sms==1){ //短信宝
		$http = 'http://api.smsbao.com/sms?'; // 短信接口
		$uid = $key;// 用户账号
		$pwd = $pwd;  // 密码
	
		$data = array(
			'u' => $uid, // 用户账号
			'p' => strtolower(md5($pwd)), // MD5位32密码,密码和用户名拼接字符
			'm' => $mobile, // 号码
			'c' => $content, // 内容
			't' => $time // 定时发送
		);
		$re = smsbaoPost($http, $data); // POST方式提交
	    //var_dump($re);						 
		if(trim($re) == '0'){
			return true;
		}else{
			file_put_contents(ROOT_PATH."test.txt", "POST:".$mobile.$content.$time.$mid.json_encode($templateParam).$templateCode.$re."\n", FILE_APPEND | LOCK_EX);			  
			return false;
		}
	}else{//云短信  所有短信均须添加后缀签名
		$content = iconv('utf-8', 'gbk', $content);
		$http = 'http://http.yunsms.cn/tx/'; // 短信接口
		$uid = $key;// 用户账号
		$pwd = $pwd;  // 密码
		
		$data = array(
			'uid' => $uid, // 用户账号
			'pwd' => strtolower(md5($pwd)), // MD5位密码
			'mobile' => $mobile, // 号码
			'content' => $content, // 内容
			'time' => $time, // 定时发送 可选
			'mid' => $mid //子扩展号 可选
		);
		$re = yunsmsPost($http, $data); // POST方式提交
	    //var_dump($re);						 
		$re_t = substr(trim($re), 3, 3);
		if(trim($re) == '100' || $re_t == '100'){
			return true;
		}else{
			file_put_contents(ROOT_PATH."test.txt", "POST:".$mobile.$content.$time.$mid.json_encode($templateParam).$templateCode.$re."\n", FILE_APPEND | LOCK_EX);			  
		
			return false;
		}
	}
}


//将XML转为array
function xmlToArray($xml){
	$array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
	return $array_data;
}


//云短信提交
function yunsmsPost($url, $data = '')
{
	$row = parse_url($url);
	$host = $row['host'];
	$port = $row['port'] ? $row['port'] : 80;
	$file = $row['path'];
	while(list($k, $v) = each($data))
	{
		$post .= rawurlencode($k) . "=" . rawurlencode($v) . "&"; // 转URL标准码
	}
	$post = substr($post, 0, - 1);
	$len = strlen($post);
	$fp = @fsockopen($host, $port, $errno, $errstr, 10);
	if(! $fp)
	{
		return "$errstr ($errno)\n";
	}
	else
	{
		$receive = '';
		$out = "POST $file HTTP/1.1\r\n";
		$out .= "Host: $host\r\n";
		$out .= "Content-type: application/x-www-form-urlencoded\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Content-Length: $len\r\n\r\n";
		$out .= $post;
		fwrite($fp, $out);
		while(! feof($fp))
		{
			$receive .= fgets($fp, 128);
		}
		fclose($fp);
		$receive = explode("\r\n\r\n", $receive);
		unset($receive[0]);
		return implode("", $receive);
	}
}

//短信宝信息提交
function smsbaoPost ($url, $data = '')
{
	return file_get_contents($url.http_build_query($data));
}

//checkSMS
function checkSMS ($mobile, $mobile_code)
{
	$arr = array(
		'error' => 0,'msg' => ''
	);
	if(time() - $_SESSION['time'] > 30 * 60)
	{
		unset($_SESSION['mobile_code']);
		$arr['error'] = 1;
		$arr['msg'] = '验证码超过30分钟。';
	}
	else
	{
		if($mobile != $_SESSION['mobile'] or $mobile_code != $_SESSION['mobile_code'])
		{
			$arr['error'] = 1;
			$arr['msg'] = '手机验证码输入错误。';
		}
		else
		{
			$arr['error'] = 2;
		}
	}
	return $arr;
}

//random
function random ($length = 6, $numeric = 0)
{
	PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	if($numeric)
	{
		$hash = sprintf('%0' . $length . 'd', mt_rand(0, pow(10, $length) - 1));
	}
	else
	{
		$hash = '';
		$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
		$max = strlen($chars) - 1;
		for($i = 0; $i < $length; $i ++)
		{
			$hash .= $chars[mt_rand(0, $max)];
		}
	}
	return $hash;
}

//read_file
function read_file ($file_name)
{
	$content = '';
	$filename = date('Ymd') . '/' . $file_name . '.log';
	if(function_exists('file_get_contents'))
	{
		@$content = file_get_contents($filename);
	}
	else
	{
		if(@$fp = fopen($filename, 'r'))
		{
			@$content = fread($fp, filesize($filename));
			@fclose($fp);
		}
	}
	$content = explode("\r\n",$content);
	return end($content);
}
?>
