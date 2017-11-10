<?php
/**
 * QQ120029121 快钱联合注册接口
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: Prince $
 * $Id: send.php 15013 2008-10-23 09:31:42Z Prince $
*/

define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');
$backUrl=$yp->url() . ADMIN_PATH . '/receive.php';
header("location:http://cloud.demo.coolhong.com/payment_apply.php?mod=kuaiqian&par=$backUrl");
exit;
?>
