<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="target-densitydpi=device-dpi, width=device-width, initial-scale=1, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">

	<style type="text/css">
#page{
	width: 98%;
	height: 10em;
	margin:1em auto;
	
	font-size:1em;
	line-height:1.5em;
}
#page2{
	width: 98%;
	height: 10em;
	margin:1em auto;
	;
	font-size:1em;
	line-height:1.5em;
}
</style>
	<title>支付宝即时到账交易接口接口</title>
</head>
<?php
/* *
 * 功能：即时到账交易接口接入页
 * 版本：3.3
 * 修改日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 *************************注意*************************
 * 如果您在接口集成过程中遇到问题，可以按照下面的途径来解决
 * 1、商户服务中心（https://b.alipay.com/support/helperApply.htm?action=consultationApply），提交申请集成协助，我们会有专业的技术工程师主动联系您协助解决
 * 2、商户帮助中心（http://help.alipay.com/support/232511-16307/0-16307.htm?sh=Y&info_type=9）
 * 3、支付宝论坛（http://club.alipay.com/read-htm-tid-8681712.html）
 * 如果不想使用扩展功能请把扩展功能参数赋空值。
 */
define('IN_PRINCE', true); 
if(strpos($_SERVER['HTTP_USER_AGENT'],'MicroMessenger'))
{
		//define('IN_PRINCE', true);
		require_once("../includes/init.php");
		$smarty->display('new.dwt');
		exit;
}
require_once("../includes/init.php");
require_once("alipay.config.php");
require_once("lib/alipay_submit.class.php");

//初始化配置
$alipay_config = array(
				"partner" => PARTNER,
				"key" => KEY,
				"account" => ACCOUNT,
				"private_key_path" => PRIVATE_KEY_PATH,
				"ali_public_key_path" => ALI_PUBLIC_KEY_PATH,
				"sign_type" => SIGN_TYPE,
				"input_charset" => INPUT_CHARSET,
				"cacert" => CACERT,
				"transport" => TRANSPORT 
				);

/**************************调用授权接口alipay.wap.trade.create.direct获取授权码token**************************/
	
//返回格式
$format = "xml";
//必填，不需要修改

//返回格式
$v = "2.0";
//必填，不需要修改

//请求号
$req_id = date('Ymdhis');
//必填，须保证每次请求都是唯一

//**req_data详细信息**

//服务器异步通知页面路径
$notify_url =  HTTP_TYPE ."://".$_SERVER['HTTP_HOST']."/mobile/pay/ajax_url.php";
//$notify_url = "http://www.duudoo.com/test.asp";
//需http://格式的完整路径，不允许加?id=123这类自定义参数

//页面跳转同步通知页面路径
$call_back_url = HTTP_TYPE ."://".$_SERVER['HTTP_HOST']."/mobile/pay/result_url.php";
//需http://格式的完整路径，不允许加?id=123这类自定义参数

//卖家支付宝帐户
$seller_email = $alipay_config['account'];
//必填

//商户订单号
$out_trade_no = $_GET['out_trade_no'];
//商户网站订单系统中唯一订单号，必填

//订单名称
$subject = $_GET['out_trade_no'];
//必填

//付款金额
$total_fee = $_GET['total_fee'];
//必填
/* 取得要修改的支付记录信息 */
 $sql = "SELECT * FROM " . $GLOBALS['yp']->table('pay_log') .
                " WHERE log_id = '$out_trade_no'";
  $pay_log = $GLOBALS['db']->getRow($sql);
			
if ($pay_log['order_amount'] && $pay_log['order_amount'] != $total_fee)
{
  ?>
	
<div id='page2'>
	<div style="text-align:center;color:#666;font-size:14px;font-weight:normal; line-height:20px;">
	<br />
	<br />
	<br />
	非法操作！3秒后跳转到订单页，请重新发起支付！！
	</div>
</div>
<script type="text/javascript">
		var url = window.location.host;
		window.setTimeout("window.location='http://'+url+'/mobile/user.php?act=order_list&composite_status=100'",3000); 
	</script>
<?php
exit;
 } 
//请求业务参数详细
$req_data = '<direct_trade_create_req><notify_url>' . $notify_url . '</notify_url><call_back_url>' . $call_back_url . '</call_back_url><seller_account_name>' . $seller_email . '</seller_account_name><out_trade_no>' . $out_trade_no . '</out_trade_no><subject>' . $subject . '</subject><total_fee>' . $total_fee . '</total_fee></direct_trade_create_req>';
//必填

/************************************************************/

//构造要请求的参数数组，无需改动
$para_token = array(
		"service" => "alipay.wap.trade.create.direct",
		"partner" => trim($alipay_config['partner']),
		"sec_id" => trim($alipay_config['sign_type']),
		"format"	=> $format,
		"v"	=> $v,
		"req_id"	=> $req_id,
		"req_data"	=> $req_data,
		"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
);

//建立请求
$alipaySubmit = new AlipaySubmit($alipay_config);
$html_text = $alipaySubmit->buildRequestHttp($para_token);

//URLDECODE返回的信息
$html_text = urldecode($html_text);

//解析远程模拟提交后返回的信息
$para_html_text = $alipaySubmit->parseResponse($html_text);

//获取request_token
$request_token = $para_html_text['request_token'];


/**************************根据授权码token调用交易接口alipay.wap.auth.authAndExecute**************************/

//业务详细
$req_data = '<auth_and_execute_req><request_token>' . $request_token . '</request_token></auth_and_execute_req>';
//必填

//构造要请求的参数数组，无需改动
$parameter = array(
		"service" => "alipay.wap.auth.authAndExecute",
		"partner" => trim($alipay_config['partner']),
		"v"	=> $v,
		"sec_id" => trim($alipay_config['sign_type']),
		"format"	=> $format,
		"req_id"	=> $req_id,
		"req_data"	=> $req_data,
		"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
);

//建立请求
$alipaySubmit = new AlipaySubmit($alipay_config);
$html_text = $alipaySubmit->buildRequestForm($parameter, 'get', '确认');
echo $html_text;
?>

</body>
</html>