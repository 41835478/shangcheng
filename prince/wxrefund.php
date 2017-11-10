<?php
/**
 * 微信退款插件
 * $Author: PRINCE $
 * 2016-03-25 09:29:08Z PRINCE QQ 120029121 
 */


	
function do_wx_refund($order_id,$order_sn,$money_paid,$money_refund){	

    $sql = 'SELECT * FROM ' . $GLOBALS['yp']->table('weixin_config').
           " WHERE id = '1'";
    $payment = $GLOBALS['db']->getRow($sql);
	
	define("PRINCE_WXPAY_APPID", $payment['appid']);
	define("PRINCE_WXPAY_MCHID", $payment['partnerId']);
	define("PRINCE_WXPAY_KEY", $payment['partnerKey']);
	define("PRINCE_WXPAY_APPSECRET", $payment['appsecret']);
	define("PRINCE_WXPAY_SSLCERT_PATH", ROOT_PATH.'prince/cert/apiclient_cert.pem');
	define("PRINCE_WXPAY_SSLKEY_PATH", ROOT_PATH.'prince/cert/apiclient_key.pem');


	if(isset($order_sn) && $order_sn != ""){
		$out_trade_no = $order_sn;
		$total_fee = $money_paid*100;
		$refund_fee = $money_refund*100;
		$input = new WxPayRefund();
		$input->SetOut_trade_no($out_trade_no);
		$input->SetTotal_fee($total_fee);
		$input->SetRefund_fee($refund_fee);
		$input->SetOut_refund_no(WxPayConfig::MCHID.date("YmdHis").wx_getMillisecond().rand(1,10));
		$input->SetOp_user_id(WxPayConfig::MCHID);
		$return = WxPayApi::refund($input);
		if(is_array($return) && $return['result_code'] == 'SUCCESS'){
			$sql = "update " . $GLOBALS['yp']->table('order_info') . " set wx_refund_status=1 WHERE  order_id =".$order_id;
			$GLOBALS['db']->query($sql);
			//echo '成功处理订单:'.$order_sn.'<br />';
			return true;
		}elseif(is_array($return) && $return['result_code'] == 'FAIL'){
			$sql = "update " . $GLOBALS['yp']->table('order_info') . " set wx_refund_status=2 WHERE wx_refund_status=0 and order_id =".$order_id;
			$GLOBALS['db']->query($sql);
			echo '订单:'.$order_sn.' 处理失败<br />';
			echo '订单金额:'.$money_paid.'<br />';
			echo '退款金额:'.$money_refund.'<br />';
			echo '返回状态码:'.$return['return_code'].'<br />';
			echo '返回信息:'.$return['return_msg'].'<br />';
			echo '业务结果:'.$return['result_code'].'<br />';
			echo '错误代码:'.$return['err_code'].'<br />';
			echo '错误代码描述:'.$return['err_code_des'].'<br />';
			return false;
		}
	}
}

//获取毫秒
function wx_getMillisecond() {
	list($usec, $usec) = explode(' ', microtime());
	   $msec=round($usec*1000);
	   return $msec;
}


function prince_get_payment_by_code_pc($code)
{
    $sql = 'SELECT * FROM ' . $GLOBALS['yp']->table('payment').
           " WHERE pay_code = '$code' AND enabled = '1'";
    $payment = $GLOBALS['db']->getRow($sql);

    if ($payment)
    {
        $config_list = unserialize($payment['pay_config']);

        foreach ($config_list AS $config)
        {
            $payment[$config['name']] = $config['value'];
        }
    }

    return $payment;
}

function prince_get_payment_by_id_pc($id)
{
    $sql = 'SELECT * FROM ' . $GLOBALS['yp']->table('payment').
           " WHERE pay_id  = '$id' AND enabled = '1'";
    $payment = $GLOBALS['db']->getRow($sql);

    if ($payment)
    {
        $config_list = unserialize($payment['pay_config']);

        foreach ($config_list AS $config)
        {
            $payment[$config['name']] = $config['value'];
        }
    }

    return $payment;
}


function prince_get_payment_by_code_mobile($code)
{
    $sql = 'SELECT * FROM ' . $GLOBALS['yp']->table('ypmart_payment').
           " WHERE pay_code = '$code' AND enabled = '1'";
    $payment = $GLOBALS['db']->getRow($sql);

    if ($payment)
    {
        $config_list = unserialize($payment['pay_config']);

        foreach ($config_list AS $config)
        {
            $payment[$config['name']] = $config['value'];
        }
    }

    return $payment;
}

function prince_get_payment_by_id_mobile($id)
{
    $sql = 'SELECT * FROM ' . $GLOBALS['yp']->table('ypmart_payment').
           " WHERE pay_id = '$id' AND enabled = '1'";
    $payment = $GLOBALS['db']->getRow($sql);

    if ($payment)
    {
        $config_list = unserialize($payment['pay_config']);

        foreach ($config_list AS $config)
        {
            $payment[$config['name']] = $config['value'];
        }
    }

    return $payment;
}

/**
 * 微信退款插件
 * $Author: PRINCE $
 * 2016-03-25 09:29:08Z PRINCE QQ 120029121 
 */
?>
