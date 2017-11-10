<?php
/**
 * QQ120029121 帮助信息接口
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: PRINCE $
 * $Id: respond.php 16220 2009-06-12 02:08:59Z PRINCE $
 */

define('IN_PRINCE', true);
require(dirname(__FILE__) . '/includes/init.php');

$get_keyword = trim($_GET['al']); // 获取关键字
header("location:http://help.demo.coolhong.com/do.php?k=".$get_keyword."&v=".$_CFG['yp_version']."&l=".$_CFG['lang']."&c=".YP_CHARSET);
?>