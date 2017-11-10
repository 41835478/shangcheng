<?php

/**
 * QQ120029121 前台公用文件
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: PRINCE $
 * $Id: init.php 17217 2017-04-01 06:29:08Z PRINCE $
*/

require('../data/config.php');

/* 初始化数据库类 */
require('../includes/cls_mysql.php');
$db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);
$db->set_disable_cache_tables(array($yp->table('sessions'), $yp->table('sessions_data'), $yp->table('cart')));
$db_host = $db_user = $db_pass = $db_name = NULL;


?>