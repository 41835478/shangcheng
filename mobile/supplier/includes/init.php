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

define('YP_ADMIN', false);

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

define('ROOT_PATH', str_replace('includes/init.php', '', str_replace('\\', '/', __FILE__)));
define('PC_ROOT_PATH', str_replace('/mobile/supplier','',ROOT_PATH));

if (DIRECTORY_SEPARATOR == '\\')
{
    @ini_set('include_path',      '.;' . ROOT_PATH);
}
else
{
    @ini_set('include_path',      '.:' . ROOT_PATH);
}

include('../../data/config.php');

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

require(ROOT_PATH . 'includes/cls_qq120029121.php');
require(ROOT_PATH . 'includes/cls_error.php');
require(ROOT_PATH . 'includes/cls_exchange.php');
require(ROOT_PATH . 'includes/inc_constant.php');
require(ROOT_PATH . 'includes/lib_base.php');
require(ROOT_PATH . 'includes/lib_common.php');
require(ROOT_PATH . 'includes/lib_main.php');
require(ROOT_PATH . 'includes/lib_supplier_common.php');
require(ROOT_PATH . 'includes/lib_time.php');

/* 对用户传入的变量进行转义操作。*/
if (!get_magic_quotes_gpc())
{
    if (!empty($_GET))
    {
        $_GET  = addslashes_deep($_GET);
    }
    if (!empty($_POST))
    {
        $_POST = addslashes_deep($_POST);
    }

    $_COOKIE   = addslashes_deep($_COOKIE);
    $_REQUEST  = addslashes_deep($_REQUEST);
}

/* 对路径进行安全处理 */
if (strpos(PHP_SELF, '.php/') !== false)
{
    yp_header("Location:" . substr(PHP_SELF, 0, strpos(PHP_SELF, '.php/') + 4) . "\n");
    exit();
}

/* 创建 QQ120029121 对象 */
$yp = new PRINCE($db_name, $prefix);
define('DATA_DIR', $yp->data_dir());
define('IMAGE_DIR', $yp->image_dir());

/* 初始化数据库类 */
require(ROOT_PATH . 'includes/cls_mysql.php');
$db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);
$db_host = $db_user = $db_pass = $db_name = NULL;

/* 创建错误处理对象 */
$err = new yp_error('message.htm');

/* 初始化session */
require(ROOT_PATH . 'includes/cls_session.php');

$sess = new cls_session($db, $yp->table('sessions'), $yp->table('sessions_data'), 'YPCP_ID');

/* 初始化 action */
if (!isset($_REQUEST['act']))
{
    $_REQUEST['act'] = '';
}
elseif (($_REQUEST['act'] == 'login' || $_REQUEST['act'] == 'logout' || $_REQUEST['act'] == 'signin') &&
    strpos(PHP_SELF, '/privilege.php') === false)
{
    $_REQUEST['act'] = '';
}
elseif (($_REQUEST['act'] == 'forget_pwd' || $_REQUEST['act'] == 'reset_pwd' || $_REQUEST['act'] == 'get_pwd') &&
    strpos(PHP_SELF, '/get_password.php') === false)
{
    $_REQUEST['act'] = '';
}

/* 载入系统参数 */
$_CFG = array_merge(load_config(),load_config_supplier());
// TODO : 登录部分准备拿出去做，到时候把以下操作一起挪过去
if ($_REQUEST['act'] == 'captcha')
{
    include(ROOT_PATH . 'includes/cls_captcha.php');

    $img = new captcha(ROOT_PATH.'data/captcha/');
    @ob_end_clean(); //清除之前出现的多余输入
    $img->generate_image();

    exit;
}
require(ROOT_PATH . 'languages/' .$_CFG['lang']. '/common.php');
require(ROOT_PATH . 'languages/' .$_CFG['lang']. '/log_action.php');

//prince 
if (file_exists(ROOT_PATH . 'languages/' . $_CFG['lang'] . '/admin/' . basename(PHP_SELF)))
{
    include(ROOT_PATH . 'languages/' . $_CFG['lang'] . '/admin/' . basename(PHP_SELF));
}

if (!file_exists('../temp/caches'))
{
    @mkdir('../temp/caches', 0777);
    @chmod('../temp/caches', 0777);
}

if (!file_exists('../temp/compiled'))
{
    @mkdir('../temp/compiled', 0777);
    @chmod('../temp/compiled', 0777);
}

clearstatcache();


/* 创建 Smarty 对象。*/
require(ROOT_PATH . 'includes/cls_template.php');
$smarty = new cls_template;

$smarty->template_dir  = ROOT_PATH . 'templates';
$smarty->compile_dir   = ROOT_PATH . 'temp/compiled';

if ((DEBUG_MODE & 2) == 2)
{
    $smarty->force_compile = true;
}


$smarty->assign('lang', $_LANG);
$smarty->assign('help_open', $_CFG['help_open']);

if(isset($_CFG['enable_order_check']))  // 为了从旧版本顺利升级到2.5.0
{
    $smarty->assign('enable_order_check', $_CFG['enable_order_check']);
}
else
{
    $smarty->assign('enable_order_check', 0);
}

/* 验证通行证信息 */
if(isset($_GET['ent_id']) && isset($_GET['ent_ac']) &&  isset($_GET['ent_sign']) && isset($_GET['ent_email']))
{
    $ent_id = trim($_GET['ent_id']);
    $ent_ac = trim($_GET['ent_ac']);
    $ent_sign = trim($_GET['ent_sign']);
    $ent_email = trim($_GET['ent_email']);
    $certificate_id = trim($_CFG['certificate_id']);
    $domain_url = $yp->url();
    $token=$_GET['token'];
    if($token==md5(md5($_CFG['token']).$domain_url))
    {
        require(ROOT_PATH . 'includes/cls_transport.php');
        $t = new transport('-1',5);
        $apiget = "act=ent_sign&ent_id= $ent_id & certificate_id=$certificate_id";

        $t->request('http://cloud.demo.coolhong.com/api.php', $apiget);
        $db->query('UPDATE '.$yp->table('shop_config') . ' SET value = "'. $ent_id .'" WHERE code = "ent_id"');
        $db->query('UPDATE '.$yp->table('shop_config') . ' SET value = "'. $ent_ac .'" WHERE code = "ent_ac"');
        $db->query('UPDATE '.$yp->table('shop_config') . ' SET value = "'. $ent_sign .'" WHERE code = "ent_sign"');
        $db->query('UPDATE '.$yp->table('shop_config') . ' SET value = "'. $ent_email .'" WHERE code = "ent_email"');
        clear_cache_files();
        yp_header("Location: ./index.php\n");
    }
}

/* 验证管理员身份 */
if ((!isset($_SESSION['supplier_id']) || intval($_SESSION['supplier_id']) <= 0) &&
    $_REQUEST['act'] != 'login' && $_REQUEST['act'] != 'signin' &&
    $_REQUEST['act'] != 'get_password')
{
    
    
	 /* if(!empty($_COOKIE['apply_user_name']) && !empty($_COOKIE['apply_password']) ){//自动登陆
	
	   $sql = "SELECT user_id, user_name, password, last_login, action_list, last_login,supplier_id,ec_salt".
				" FROM " . $yp->table('supplier_admin_user') .
				" WHERE user_name = '" .$_COOKIE['apply_user_name']. "' AND password = '" .$_COOKIE['apply_password']. "'";
		$row = $db->getRow($sql);
		
		if ($row)
		{
			// 登录成功
			$_SESSION['supplier_id'] = $row['supplier_id'];//店铺的id
			$_SESSION['supplier_user_id'] = $row['user_id'];//管理员id
			$_SESSION['supplier_name']  = $row['user_name'];//管理员名称
			$_SESSION['supplier_action_list'] = $row['action_list'];//管理员权限
			$_SESSION['supplier_last_check']  = $row['last_login']; // 用于保存最后一次检查订单的时间
			set_admin_session($row['user_id'], $row['user_name'], $row['action_list'], $row['last_login']);

	
			if($row['action_list'] == 'all')
			{
				$_SESSION['supplier_admin_id'] = $row['user_id'];//超级管理员的标识管理员id
				$_SESSION['supplier_shop_guide'] = true;//超级管理员标识
			}
			$db->query("UPDATE " .$yp->table('supplier_admin_user').
					 " SET last_login='" . gmtime() . "', last_ip='" . real_ip() . "'".
					 " WHERE user_id='$_SESSION[supplier_user_id]'");
					 
			    $time = gmtime() + 3600 * 24 * 365;
				setcookie('YPCP[supplier_id]',   $row['supplier_id'],                            $time);
				setcookie('YPCP[supplier_user_id]',   $row['user_id'],                            $time);
				setcookie('YPCP[supplier_pass]', md5($row['password'].$_CFG['hash_code']), $time);		 
					 
		} 
	 }
    else*/
	
	if (!empty($_COOKIE['YPCP']['supplier_id']) && !empty($_COOKIE['YPCP']['supplier_user_id']) && !empty($_COOKIE['YPCP']['supplier_pass']))
    {
        // 找到了cookie, 验证cookie信息
        $sql = 'SELECT user_id, user_name, password, action_list, last_login, supplier_id ' .
                ' FROM ' .$yp->table('supplier_admin_user') .
                " WHERE user_id = '" . intval($_COOKIE['YPCP']['supplier_user_id']) . "' AND supplier_id=".intval($_COOKIE['YPCP']['supplier_id']);
        $row = $db->GetRow($sql);
        
        if (!$row)
        {
            // 没有找到这个记录
            setcookie($_COOKIE['YPCP']['supplier_id'],   '', 1);
            setcookie($_COOKIE['YPCP']['supplier_pass'], '', 1);
			setcookie('YPCP[supplier_id]', '', 1);
			setcookie('YPCP[supplier_pass]', '', 1);
            
            if (!empty($_REQUEST['is_ajax']))
            {
                make_json_error($_LANG['priv_error']);
            }
            else
            {
                yp_header("Location: privilege.php?act=login&num=1\n");
            }

            exit;
        }
        else
        {   
            // 检查密码是否正确
            if (md5($row['password'] . $_CFG['hash_code']) == $_COOKIE['YPCP']['supplier_pass'])
            {
                if(!isset($row['last_time']))
                {
                    $row['last_time'] = '';
                };
				$_SESSION['supplier_id'] = $row['supplier_id'];
				$_SESSION['supplier_user_id'] = $row['user_id'];
				$_SESSION['supplier_name']  = $row['user_name'];
                $_SESSION['supplier_action_list'] = $row['action_list'];
                set_admin_session($row['user_id'], $row['user_name'], $row['action_list'], $row['last_time']);
                // 更新最后登录时间和IP
                $db->query('UPDATE ' . $yp->table('supplier_admin_user') .
                            " SET last_login = '" . gmtime() . "', last_ip = '" . real_ip() . "'" .
                            " WHERE user_id = '" . $_SESSION['supplier_user_id'] . "'");
				//yp_header("Location: index.php");
                //exit;
            }
            else
            {    //echo '1----'.$row['password'].'-'.$_CFG['hash_code'].'-'.md5($row['password'] . $_CFG['hash_code']).'-'.$_COOKIE['YPCP']['supplier_pass'];
                setcookie($_COOKIE['YPCP']['supplier_id'],   '', 1);
				setcookie($_COOKIE['YPCP']['supplier_user_id'],   '', 1);
                setcookie($_COOKIE['YPCP']['supplier_pass'], '', 1);
			
				setcookie('YPCP[supplier_id]', '', 1);
				setcookie('YPCP[supplier_user_id]', '', 1);
				setcookie('YPCP[supplier_pass]', '', 1);
					
				setcookie('YPCP[supplier_id]', '', 1,'/'.SUPPLIER_PATH.'/');
				setcookie('YPCP[supplier_user_id]', '', 1,'/'.SUPPLIER_PATH.'/');
				setcookie('YPCP[supplier_pass]', '', 1,'/'.SUPPLIER_PATH.'/');
				//echo '2----'.$row['password'].'-'.$_CFG['hash_code'].'-'.md5($row['password'] . $_CFG['hash_code']).'-'.$_COOKIE['YPCP']['supplier_pass'];exit;
                if (!empty($_REQUEST['is_ajax']))
                {
                    make_json_error($_LANG['priv_error']);
                }
                else
                {
                    yp_header("Location: privilege.php?act=login&num=2\n");
                }

                exit;
            }
        }
    }
    else
    {
        if (!empty($_REQUEST['is_ajax']))
        {
            make_json_error($_LANG['priv_error']);
        }
        else
        {
            yp_header("Location: privilege.php?act=login&num=3\n");
        }

        exit;
    }
}

$smarty->assign('token', $_CFG['token']);

if ($_REQUEST['act'] != 'login' && $_REQUEST['act'] != 'signin' &&
    $_REQUEST['act'] != 'get_password')
{
   
    $admin_path = preg_replace('/:\d+/', '', $yp->url()) ;
	$admin_path = str_replace('/supplier','',$admin_path );

    if (!empty($_SERVER['HTTP_REFERER']) &&
        strpos(preg_replace('/:\d+/', '', $_SERVER['HTTP_REFERER']), $admin_path) === false)
    {
        if (!empty($_REQUEST['is_ajax']))
        {
            make_json_error($_LANG['priv_error']);
        }
        else
        {
            yp_header("Location: privilege.php?act=login&num=4\n");
        }

        exit;
    }
}

/* 管理员登录后可在任何页面使用 act=phpinfo 显示 phpinfo() 信息 */
if ($_REQUEST['act'] == 'phpinfo' && function_exists('phpinfo'))
{
    phpinfo();

    exit;
}

//header('Cache-control: private');
header('content-type: text/html; charset=' . YP_CHARSET);
header('Expires: Fri, 14 Mar 1980 20:53:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

if ((DEBUG_MODE & 1) == 1)
{
    error_reporting(E_ALL);
}
else
{
    error_reporting(E_ALL ^ E_NOTICE);
}
if ((DEBUG_MODE & 4) == 4)
{
    include(ROOT_PATH . 'includes/lib.debug.php');
}

/* 判断是否支持gzip模式 */
if (gzip_enabled())
{
    ob_start('ob_gzhandler');
}
else
{
    ob_start();
}
create_shop_settiongs();
?>
