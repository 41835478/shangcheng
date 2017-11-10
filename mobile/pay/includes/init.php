<?php

/**
 * QQ120029121 管理中心公用文件
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: PRINCE $
 * $Id: init.php 17217 2017-04-01 06:29:08Z PRINCE $
*/

if (!defined('IN_PRINCE'))
{
    die('Hacking attempt');
}

define('YP_ADMIN', true);

error_reporting(E_ALL);

if (__FILE__ == '')
{
    die('Fatal error code: 0');
}

/* 初始化设置 */
@ini_set('memory_limit',          '64M');
@ini_set('session.cache_expire',  180);
@ini_set('session.use_trans_sid', 0);
@ini_set('session.use_cookies',   1);
@ini_set('session.auto_start',    0);
@ini_set('display_errors',        1);

if (DIRECTORY_SEPARATOR == '\\')
{
    @ini_set('include_path',      '.;' . ROOT_PATH);
}
else
{
    @ini_set('include_path',      '.:' . ROOT_PATH);
}

if (file_exists('../data/config.php'))
{
    include('../data/config.php');
}
else
{
    include('../includes/config.php');
}

/* 取得当前jtypmall所在的根目录 */
if(!defined('ADMIN_PATH'))
{
    define('ADMIN_PATH','json');
}
define('ROOT_PATH', str_replace(ADMIN_PATH . '/includes/init.php', '', str_replace('\\', '/', __FILE__)));

if (defined('DEBUG_MODE') == false)
{
    define('DEBUG_MODE', 0);
}

if (PHP_VERSION >= '5.1' && !empty($timezone))
{
    date_default_timezone_set($timezone);
}

if (isset($_SERVER['PHP_SELF']))
{
    define('PHP_SELF', $_SERVER['PHP_SELF']);
}
else
{
    define('PHP_SELF', $_SERVER['SCRIPT_NAME']);
}

require( '../includes/inc_constant.php');
require('../includes/cls_qq120029121.php');
require('../includes/cls_error.php');
require('../includes/lib_time.php');
require('../includes/lib_base.php');
require('../includes/lib_common.php');
require('./includes/lib_main.php');
require('./includes/cls_exchange.php');



/* 创建 QQ120029121 对象 */
$yp = new PRINCE($db_name, $prefix);
define('DATA_DIR', $yp->data_dir());
define('IMAGE_DIR', $yp->image_dir());

/* 初始化数据库类 */
require('../includes/cls_mysql.php');
$db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);
$db_host = $db_user = $db_pass = $db_name = NULL;



?>
