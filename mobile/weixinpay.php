<?php
define('IN_PRINCE', true);
require(dirname(__FILE__) . '/includes/init.php');
require('../includes/lib_order.php');
include_once('../includes/lib_payment.php');
error_reporting(E_ALL ^ E_NOTICE);
$out_trade_no = intval($_GET['out_trade_no']);

//根据支付id获取订单id
$order_id = $GLOBALS['db']->getOne("SELECT order_id FROM ".$GLOBALS['yp']->table('pay_log')." WHERE log_id = '$out_trade_no'");

//获取订单信息
$order = $GLOBALS['db']->getRow("SELECT * FROM " . $GLOBALS['yp']->table('order_info') . " WHERE order_id = '$order_id' OR parent_order_id = '$order_id' limit 1");

if($order)
{
	if ($order['order_amount'] > 0){
		//防止商户订单号重复
		$order['order_id'] = $out_trade_no.'-'.$order['order_amount']*100; 
		$payment = payment_info($order['pay_id']);
		include_once('../includes/modules/payment/' . $payment['pay_code'] . '.php');
		$pay_obj    = new $payment['pay_code'];
		$code = $pay_obj->get_code($order, unserialize_config($payment['pay_config']));
	}
	else
	{
		show_message('此订单已支付！'); 
	}
}
else
{
	echo 1;exit; 
}
?>
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <?php
    if ($is_weixin_browser){//如果是微信内优先使用 微信公众号js 支付
    ?>
    <title>微信安全支付</title>
	<script type="text/javascript">
		function jsApiCall()
		{
			WeixinJSBridge.invoke(
				'getBrandWCPayRequest',
				<?php echo $code;?>,
				function(res){
					//WeixinJSBridge.log(res.err_msg);
					if(res.err_msg == "get_brand_wcpay_request:ok" ) {
						window.location.href = "<?php echo return_url('weixin');?>";
					} else {
						alert("交易取消");
						window.location.href = "./index.php";
					}
				}
			);
		}
		//function callpay()
		window.onload = function ()

		{
			if (typeof WeixinJSBridge == "undefined"){
			    if( document.addEventListener ){
			        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
			    }else if (document.attachEvent){
			        document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
			        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
			    }
			}else{
			    jsApiCall();
			}
		}
	</script>

	<?php 

 exit;
 } 

 ?>

     <title>微信H5安全支付</title>
     
  <?php 
   $payment = unserialize_config($payment['pay_config']);
   
   define ( APPID, $payment ['appId'] ); // appid
   define ( APPSECRET, $payment ['appSecret'] ); // appSecret
   define ( MCHID, $payment ['partnerId'] );//商户号
   define ( KEY, $payment ['partnerKey'] ); // 通加密串
   
 include_once ("weixin/WxPayPubHelper.php");
//$data['appid'] = APPID ;//公众账号ID

//$data['mch_id'] = MCHID;//商户hao
$data['appid'] = 'wx7c29b7a3a395882f';//公众账号ID

$data['mch_id'] = '1468781602';//商户hao
$data['out_trade_no'] = $order['order_id'];//订单号  order_sn改为order_id
$data['nonce_str'] = createNoncestr();//随机字符
$data['total_fee'] = $order['order_amount']*100;//金额
$data['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];//终端ip
$data['notify_url'] = HTTP_TYPE.'://'.$_SERVER['HTTP_HOST']."/mobile/respond.php";//异步通知地址
$data['trade_type'] = 'MWEB';//交易类型
$data['body'] = '订单号'.$order['order_sn'].'支付';//描述
//$data['scene_info'] = '{"h5_info":{"type":"Wap","wap_url":"http://demo.coolhong.com","wap_name":"测试支付"}}';//场景

$data['sign'] = getSign($data);//签名

$xml = arrayToXml($data);

$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";//微信传参地址
$re = postXmlCurl($xml,$url);

$objectxml = xmlToArray($re);//将微信返回的XML 转换成数组



//var_dump($objectxml);
if($objectxml['return_code'] == 'SUCCESS')  {

	
            if($objectxml['result_code'] == 'SUCCESS'){//如果这两个都为此状态则返回mweb_url，详情看‘统一下单’接口文档
           //echo  $objectxml['mweb_url']; //mweb_url是微信返回的支付连接要把这个连接分配到前台
              //拼接 mweb_url  唤起支付
	         $redirect_url = urlencode($data['notify_url']);//对redirect_url进行urlencode处理
             $objectxml['mweb_url'] = $objectxml['mweb_url']."&redirect_url=".$redirect_url;//
            //在MWEB_URL后拼接上redirect_url参数，来指定回调页面。
            $mweb_url = $objectxml['mweb_url'];
	         header("Location: $mweb_url");//内部跳转唤起微信支付
	         exit;

            }
           if($objectxml['result_code'] == 'FAIL'){

           	$err_code_des = $objectxml['err_code_des'];

           show_message('支付出错！'.$err_code_des.',请联系商家', $_LANG['back_home'], './', 'warning'); 
          }
       }else{

           show_message('支付出错！'.$objectxml['return_msg'].',请联系商家处理', $_LANG['back_home'], './', 'warning'); 
     }


	function trimString($value)
	{
		$ret = null;
		if (null != $value) 
		{
			$ret = $value;
			if (strlen($ret) == 0) 
			{
				$ret = null;
			}
		}
		return $ret;
	}
	
	/**
	 * 	作用：产生随机字符串，不长于32位
	 */
	function createNoncestr( $length = 32 ) 
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {  
			$str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
		}  
		return $str;
	}
	
	/**
	 * 	作用：格式化参数，签名过程需要使用
	 */
	function formatBizQueryParaMap($paraMap, $urlencode)
	{
		$buff = "";
		ksort($paraMap);
		foreach ($paraMap as $k => $v)
		{
		    if($urlencode)
		    {
			   $v = urlencode($v);
			}
			//$buff .= strtolower($k) . "=" . $v . "&";
			$buff .= $k . "=" . $v . "&";
		}
		$reqPar;
		if (strlen($buff) > 0) 
		{
			$reqPar = substr($buff, 0, strlen($buff)-1);
		}
		return $reqPar;
	}
	
	/**
	 * 	作用：生成签名
	 */
 function getSign($Obj)
	{
	 //$weixinconfig = $GLOBALS['db']->getRow ( "SELECT * FROM " . $GLOBALS['yp']->table('weixin_config') . " WHERE `id` = 1" );	
		foreach ($Obj as $k => $v)
		{
			$Parameters[$k] = $v;
		}
		//签名步骤一：按字典序排序参数
		ksort($Parameters);
		$String = formatBizQueryParaMap($Parameters, false);
		//echo '【string1】'.$String.'</br>';
		//签名步骤二：在string后加入KEY
		//$String = $String."&key=".KEY ;
        $String = $String."&key=12312312312312312312312312312312";
		//echo "【string2】".$String."</br>";
		//签名步骤三：MD5加密
		$String = md5($String);
		//echo "【string3】 ".$String."</br>";
		//签名步骤四：所有字符转为大写
		$result_ = strtoupper($String);
		//echo "【result】 ".$result_."</br>";
		return $result_;
	}
	
	/**
	 * 	作用：array转xml
	 */
function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
        	 if (is_numeric($val))
        	 {
        	 	$xml.="<".$key.">".$val."</".$key.">"; 

        	 }
        	 else
        	 	$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";  
        }
        $xml.="</xml>";
        return $xml; 
    }
	
	/**
	 * 	作用：将xml转为array
	 */
function xmlToArray($xml)
	{		
        //将XML转为array        
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);		
		return $array_data;
	}



	
	
	

	
/**
	 * 	作用：以post方式提交xml到对应的接口url
	 */
	 function postXmlCurl($xml,$url,$second=30)
	{		
        //初始化curl        
       	$ch = curl_init();
		//设置超时
	//	curl_setopt($ch, CURLOP_TIMEOUT, $second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		//运行curl
        $data = curl_exec($ch);
		//curl_close($ch);
		//返回结果
		if($data)
		{
			curl_close($ch);
			return $data;
		}
		else 
		{ 
			$error = curl_errno($ch);
			echo "curl出错，错误码:$error"."<br>"; 
			echo "<a href='https://pay.weixin.qq.com/wiki/doc/api/H5.php?chapter=9_1'>错误原因查询</a></br>";
			curl_close($ch);
			return false;
		}
	}	
	

?>
</head>
<body>
<!--	</br></br></br></br>
	<div align="center">
		<button style="width:400px; height:100px; background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:28px;" type="button" onclick="callpay()" >微信支付</button>
	</div>
	-->
</body>
</html>