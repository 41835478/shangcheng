<?php

/**
 * 检查支付状态
*/
	define('IN_PRINCE', true);
	require('includes/init.php');
	$order_sn=$_GET['order_sn'];
	$sql="SELECT 
		pay_status
	FROM ".$yp->table('order_info')." WHERE order_sn='$order_sn'";
	$pay_status=$db ->getRow($sql);
	print_r(json_encode($pay_status));
?>