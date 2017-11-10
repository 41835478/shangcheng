<?php
/**
 * 订单打印接口文件
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: PRINCE $
 * $Id: order_api.php 17217 2011-01-19 06:29:08Z  $
*/
define('IN_PRINCE', true);
require(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . 'includes/lib_order.php');
include_once(ROOT_PATH . 'includes/HttpClient.class.php');
$oid = $_GET['order_id'];

$sql = "SELECT supplier_id,referer FROM " . $GLOBALS['yp']->table('order_info') . " WHERE order_id = '" . $_GET['order_id'] . "'";
$supplier = $GLOBALS['db']->getRow($sql); 
if($supplier['supplier_id'] > 0){
    $sql = "SELECT value FROM " . $GLOBALS['yp']->table('supplier_shop_config') . " WHERE code = 'printer_open' AND supplier_id = '" . $supplier['supplier_id'] . "'";
    $printer_open = $GLOBALS['db']->getOne($sql); 
    $sql = "SELECT value FROM " . $GLOBALS['yp']->table('supplier_shop_config') . " WHERE code = 'printer_user' AND supplier_id = '" . $supplier['supplier_id'] . "'";
    $printer_user = $GLOBALS['db']->getOne($sql); 
    $sql = "SELECT value FROM " . $GLOBALS['yp']->table('supplier_shop_config') . " WHERE code = 'printer_key' AND supplier_id = '" . $supplier['supplier_id'] . "'";
    $printer_key = $GLOBALS['db']->getOne($sql); 
    $sql = "SELECT value FROM " . $GLOBALS['yp']->table('supplier_shop_config') . " WHERE code = 'printer_sn' AND supplier_id = '" . $supplier['supplier_id'] . "'";
    $printer_sn = $GLOBALS['db']->getOne($sql); //打印机编号
    $sql = "SELECT value FROM " . $GLOBALS['yp']->table('supplier_shop_config') . " WHERE code = 'custom_lang' AND supplier_id = '" . $supplier['supplier_id'] . "'";
    $custom_lang = $GLOBALS['db']->getOne($sql); //自定义打印语
    define('USER', $printer_user);	//*用户填写*：云后台注册账号
    define('UKEY', $printer_key);	//*用户填写*: 云注册账号后生成的UKEY
}else{
    define('USER', $GLOBALS['_CFG']['printer_user']);	//*用户填写*：云后台注册账号
    define('UKEY', $GLOBALS['_CFG']['printer_key']);	//*用户填写*: 云注册账号后生成的UKEY
    $printer_sn = $GLOBALS['_CFG']['printer_sn'];//打印机编号
    $custom_lang = $GLOBALS['_CFG']['custom_lang'];//自定义打印语
    $printer_open = $GLOBALS['_CFG']['printer_open'];
}


if($printer_open == '0'){//打印机关闭
    exit;
  }


//API URL
define('IP','api.feieyun.cn');		//接口IP或域名
define('PORT',80);					//接口IP端口
define('HOSTNAME','/Api/Open/');	//接口路径
define('STIME', time());			//公共参数，请求时间
define('SIG', sha1(USER.UKEY.STIME)); //公共参数，请求公钥


//==================方法1.打印订单==================
		//***接口返回值说明***
		//正确例子：{"msg":"ok","ret":0,"data":"316500004_20160823165104_1853029628","serverExecutedTime":6}
		//错误：{"msg":"错误信息.","ret":非零错误码,"data":null,"serverExecutedTime":5}
				
		
		//标签说明：
		//"<BR>"为换行符
		//"<CUT>"为切刀指令(主动切纸,仅限切刀打印机使用才有效果)
		//"<LOGO>"为打印LOGO指令(前提是预先在机器内置LOGO图片)
		//"<PLUGIN>"为钱箱或者外置音响指令
		//"<CB></CB>"为居中放大
		//"<B></B>"为放大一倍
		//"<C></C>"为居中
		//"<L></L>"为字体变高一倍
	    //"<W></W>"为字体变宽一倍
	    //"<QR></QR>"为二维码
		//"<RIGHT></RIGHT>"为右对齐
	    //拼凑订单内容时可参考如下格式
		//根据打印纸张的宽度，自行调整内容的格式，可参考下面的样例格式
		
            $order_status = array("未确认","已确认","已取消","无效","退货"); //订单状态
			$pay_status = array("未付款","付款中","已付款");  //支付状态
			
			//商品订单信息
			$order = order_info($oid);
			//订单中包含的商品
			$ordergoods = order_goods($oid);
			
			$sql = "SELECT concat(IFNULL(c.region_name, ''), '  ', IFNULL(p.region_name, ''), " .
                "'  ', IFNULL(t.region_name, ''), '  ', IFNULL(d.region_name, '')) AS region " .
            "FROM " . $GLOBALS['yp']->table('order_info') . " AS o " .
                "LEFT JOIN " . $GLOBALS['yp']->table('region') . " AS c ON o.country = c.region_id " .
                "LEFT JOIN " . $GLOBALS['yp']->table('region') . " AS p ON o.province = p.region_id " .
                "LEFT JOIN " . $GLOBALS['yp']->table('region') . " AS t ON o.city = t.region_id " .
                "LEFT JOIN " . $GLOBALS['yp']->table('region') . " AS d ON o.district = d.region_id " .
            "WHERE o.order_id = '$order[order_id]'";
    		$order['region'] = $GLOBALS['db']->getOne($sql);
			
			
			$orderInfo = '<CB>订单信息</CB><BR>';//标题字体如需居中放大,就需要用标签套上
			$orderInfo .= '订单号:'.$order['order_sn'].'<BR>';
			$orderInfo .= '所属商家:'.$supplier['referer'].'<BR>';
			//$orderInfo .= '订单状态:'.$order_status[$order['order_status']].'<BR>';
			$orderInfo .= '支付状态:'.$pay_status[$order['pay_status']].'<BR>';
			$orderInfo .= '收货人:'.$order['consignee'].'<BR>';
			$orderInfo .= '收货地址:'.$order['region'].$order['address'].'<BR>';
			$orderInfo .= '邮编:'.$order['zipcode'].'<BR>';
			if ($order['tel']){
			$orderInfo .= '电话:'.$order['tel'].'<BR>';
			}
			if ($order['mobile']){
			$orderInfo .= '手机:'.$order['mobile'].'<BR>';
			}
			$orderInfo .= '配送方式:'.$order['shipping_name'].'<BR>';
			$orderInfo .='------------------------------------<BR>';
			if ($order['money_paid']){
			$orderInfo .= '已付款金额:'.$order['money_paid'].'<BR>';
			}
			$orderInfo .= '支付方式:'.$order['pay_name'].'<BR>';
			$orderInfo .='------------------------------------<BR>';
			$orderInfo .= '<CB>商品明细</CB><BR>';
			$orderInfo .='------------------------------------<BR>';
			
			foreach ($ordergoods as $v){
				$orderInfo .= $v['goods_name'].'('.$v['goods_sn'].')'.'【'.$v['goods_attr'].'】×'.$v['goods_number'].'(件)×'.$v['goods_price'].'='.$v['subtotal'].'<BR>';	
			}
			$orderInfo .='------------------------------------<BR>';
			
			$orderInfo .= '商品总价:'.$order['goods_amount'].'<BR>';
			$orderInfo .= '支付费用:'.$order['pay_fee'].'<BR>';
			$orderInfo .= '使用余额:'.$order['surplus'].'<BR>';
			if ($order['discount']){
			$orderInfo .= '折扣金额:'.$order['discount'].'<BR>';
			}
			if ($order['pay_time']){
			$orderInfo .= '支付时间:'.date('Y-m-d H:i:s',$order['pay_time'] +28800).'<BR>';
			}
			if ($order['to_buyer']){
			$orderInfo .= '客户留言:'.$order['to_buyer'].'<BR>';
			}
			$orderInfo .='------------------------------------<BR>';
			$orderInfo .= '打印时间:'.date('Y-m-d H:i:s',time()).'<BR>';
			$orderInfo .= '<C>扫描下方二维码，查看订单详情</C><BR>';
		    $orderInfo .= '<QR>'.$GLOBALS['_CFG']['shop_url'].'mobile/user.php?act=order_detail&order_id='.$oid.'</QR>';//把二维码字符串用标签套上即可自动生成二维码
			$orderInfo .='------------------------------------<BR>';
		    $orderInfo .= '<BR><C>'.$custom_lang.'</C><BR>';//自定义语
		
		//打开注释可测试
		wp_print($printer_sn,$orderInfo,1);
		

		
//===========方法2.查询某订单是否打印成功=============
		//***接口返回值说明***
		//正确例子：
		//已打印：{"msg":"ok","ret":0,"data":true,"serverExecutedTime":6}
		//未打印：{"msg":"ok","ret":0,"data":false,"serverExecutedTime":6}
		
		//打开注释可测试
		//$orderindex = "xxxxxxxxxxxxxx";//订单索引，从方法1返回值中获取
		//queryOrderState($orderindex);
		

		
	
//===========方法3.查询指定打印机某天的订单详情============
		//***接口返回值说明***
		//正确例子：{"msg":"ok","ret":0,"data":{"print":6,"waiting":1},"serverExecutedTime":9}
		
		//打开注释可测试
		//$sn = "xxxxxxxxx";//打印机编号
		//$date = "2016-08-27";//注意时间格式为"yyyy-MM-dd",如2016-08-27
		//queryOrderInfoByDate($sn,$date);
		



//===========方法4.查询打印机的状态==========================
		//***接口返回值说明***
		//正确例子：
		//{"msg":"ok","ret":0,"data":"离线","serverExecutedTime":9}
		//{"msg":"ok","ret":0,"data":"在线，工作状态正常","serverExecutedTime":9}
		//{"msg":"ok","ret":0,"data":"在线，工作状态不正常","serverExecutedTime":9}
		
		//打开注释可测试
		//queryPrinterStatus("打印机编号");
		




/*
 *  方法1
	拼凑订单内容时可参考如下格式
	根据打印纸张的宽度，自行调整内容的格式，可参考下面的样例格式
*/
function wp_print($printer_sn,$orderInfo,$times){
	
		$content = array(			
			'user'=>USER,
			'stime'=>STIME,
			'sig'=>SIG,
			'apiname'=>'Open_printMsg',

			'sn'=>$printer_sn,
			'content'=>$orderInfo,
		    'times'=>$times//打印次数
		);
	$client = new HttpClient(IP,PORT);
	if(!$client->post(HOSTNAME,$content)){
		echo 'error';
	}
	else{
		echo $client->getContent();
	}
	
}





/*
 *  方法2
	根据订单索引,去查询订单是否打印成功,订单索引由方法1返回
*/
function queryOrderState($index){
		$msgInfo = array(
			'user'=>USER,
			'stime'=>STIME,
			'sig'=>SIG,	 
			'apiname'=>'Open_queryOrderState',
			
			'orderid'=>$index
		);
	
	$client = new HttpClient(IP,PORT);
	if(!$client->post(HOSTNAME,$msgInfo)){
		echo 'error';
	}
	else{
		$result = $client->getContent();
		echo $result;
	}
	
}




/*
 *  方法3
	查询指定打印机某天的订单详情
*/
function queryOrderInfoByDate($printer_sn,$date){
		$msgInfo = array(
			'user'=>USER,
			'stime'=>STIME,
			'sig'=>SIG,			
			'apiname'=>'Open_queryOrderInfoByDate',
			
	        'sn'=>$printer_sn,
			'date'=>$date
		);
	
	$client = new HttpClient(IP,PORT);
	if(!$client->post(HOSTNAME,$msgInfo)){ 
		echo 'error';
	}
	else{
		$result = $client->getContent();
		echo $result;
	}
	
}



/*
 *  方法4
	查询打印机的状态
*/
function queryPrinterStatus($printer_sn){
		
	    $msgInfo = array(
	    	'user'=>USER,
			'stime'=>STIME,
			'sig'=>SIG,	
			'debug'=>'nojson',				
			'apiname'=>'Open_queryPrinterStatus',
			
	        'sn'=>$printer_sn
		);
	
	$client = new HttpClient(IP,PORT);
	if(!$client->post(HOSTNAME,$msgInfo)){
		echo 'error';
	}
	else{
		$result = $client->getContent();
		echo $result;
	}
}


?>
