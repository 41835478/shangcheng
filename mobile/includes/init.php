<?php

/**
 * QQ120029121 前台公用文件
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: prince $
 * $Id: init.php 17217 2017-04-01 06:29:08Z prince $
*/

if (!defined('IN_PRINCE'))
{
    die('Hacking attempt');
}

error_reporting(E_ALL);

if (__FILE__ == '')
{
    die('Fatal error code: 0');
}

/* 取得当前jtypmall所在的根目录 */
define('ROOT_PATH', str_replace('includes/init.php', '', str_replace('\\', '/', __FILE__)));
define('TOKEN', "leileiceshi");
define('PC_ROOT_PATH', str_replace('/mobile','',ROOT_PATH));
if (!file_exists(ROOT_PATH . '../data/install.lock') && !file_exists(ROOT_PATH . '../includes/install.lock')
    && !defined('NO_CHECK_INSTALL'))
{
    header("Location: ./../install/index.php\n");

    exit;
}



/* 初始化设置 */
@ini_set('memory_limit',          '64M');
@ini_set('session.cache_expire',  180);
@ini_set('session.use_trans_sid', 0);
@ini_set('session.use_cookies',   1);
@ini_set('session.auto_start',    0);
@ini_set('display_errors',        0);

if (DIRECTORY_SEPARATOR == '\\')
{
    @ini_set('include_path', '.;' . ROOT_PATH);
}
else
{
    @ini_set('include_path', '.:' . ROOT_PATH);
}

require(ROOT_PATH . '../data/config.php');

if (defined('DEBUG_MODE') == false)
{
    define('DEBUG_MODE', 0);
}

if (PHP_VERSION >= '5.1' && !empty($timezone))
{
    date_default_timezone_set($timezone);
}

$php_self = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
if ('/' == substr($php_self, -1))
{
    $php_self .= 'index.php';
}
define('PHP_SELF', $php_self);

require(ROOT_PATH . 'includes/inc_constant.php');
require(ROOT_PATH . 'includes/cls_qq120029121.php');
require(ROOT_PATH . 'includes/cls_error.php');
require(ROOT_PATH . 'includes/lib_time.php');
require(ROOT_PATH . 'includes/lib_base.php');
require(ROOT_PATH . 'includes/lib_common.php');
require(ROOT_PATH . 'includes/lib_main.php');
require(ROOT_PATH . 'includes/lib_insert.php');
require(ROOT_PATH . 'includes/lib_goods.php');
require(ROOT_PATH . 'includes/lib_article.php');

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

/* 创建 QQ120029121 对象 */
$yp = new PRINCE($db_name, $prefix);
define('DATA_DIR', $yp->data_dir());
define('IMAGE_DIR', $yp->image_dir());

/* 初始化数据库类 */
require(ROOT_PATH . 'includes/cls_mysql.php');
$db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);
$db->set_disable_cache_tables(array($yp->table('sessions'), $yp->table('sessions_data'), $yp->table('cart')));
$db_host = $db_user = $db_pass = $db_name = NULL;

/* 创建错误处理对象 */
$err = new yp_error('message.dwt');

/* 载入系统参数 */
$_CFG = load_config();

/* 载入语言文件 */
require(ROOT_PATH . 'languages/' . $_CFG['lang'] . '/common.php');

if ($_CFG['shop_closed'] == 1)
{
    /* 商店关闭了，输出关闭的消息 */
    header('Content-type: text/html; charset='.YP_CHARSET);

    die('<div style="margin: 150px; text-align: center; font-size: 14px"><p>' . $_LANG['shop_closed'] . '</p><p>' . $_CFG['close_comment'] . '</p></div>');
}
/*20170119  寒冰   qq   309 4855 52  商家自定义二级域名 */
if ($_CFG['supplier_url_open'] == 1)//商家自定义二级域名开启
{
  $str = $_SERVER['SERVER_NAME'] ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST'];
  $n = strpos($str,'.');//寻找位置
   if ($n) 
    {
     $str = substr($str,0,$n);//获取访问域名前缀
	 $sql = "SELECT supplier_id FROM " . $GLOBALS['yp']->table('supplier_shop_config') . " WHERE value = '$str' AND code = 'use_supplier_url'";
     $supplier_id = $GLOBALS['db']->getOne($sql);//获取店铺  id
	 if ($supplier_id){
	  header("Location: supplier.php?suppId=$supplier_id");//跳转到指定店铺
      exit;
	 }
    }
}
//end
if (is_spider())
{
    /* 如果是蜘蛛的访问，那么默认为访客方式，并且不记录到日志中 */
    if (!defined('INIT_NO_USERS'))
    {
        define('INIT_NO_USERS', true);
        /* 整合UC后，如果是蜘蛛访问，初始化UC需要的常量 */
        if($_CFG['integrate_code'] == 'ucenter')
        {
             $user = & init_users();
        }
    }
    $_SESSION = array();
    $_SESSION['user_id']     = 0;
    $_SESSION['user_name']   = '';
    $_SESSION['email']       = '';
    $_SESSION['user_rank']   = 0;
    $_SESSION['discount']    = 1.00;
}


if (!defined('INIT_NO_USERS'))
{
    /* 初始化session */
    include(ROOT_PATH . 'includes/cls_session.php');

    $sess = new cls_session($db, $yp->table('sessions'), $yp->table('sessions_data'));

    define('SESS_ID', $sess->get_session_id());

}

if(isset($_SERVER['PHP_SELF']))
{
    $_SERVER['PHP_SELF']=htmlspecialchars($_SERVER['PHP_SELF']);
}
if (!defined('INIT_NO_USERS'))
{
    /* 会员信息 */
    $user =& init_users();

    if (!isset($_SESSION['user_id']))
    {
        /* 获取投放站点的名称 */
        $site_name = isset($_GET['from'])   ? htmlspecialchars($_GET['from']) : addslashes($_LANG['self_site']);
        $from_ad   = !empty($_GET['ad_id']) ? intval($_GET['ad_id']) : 0;

        $_SESSION['from_ad'] = $from_ad; // 用户点击的广告ID
        $_SESSION['referer'] = stripslashes($site_name); // 用户来源

        unset($site_name);

        if (!defined('INGORE_VISIT_STATS'))
        {
            visit_stats();
        }
    }

    if (empty($_SESSION['user_id']))
    {
        if ($user->get_cookie())
        {
            /* 如果会员已经登录并且还没有获得会员的帐户余额、积分以及优惠券 */
            if ($_SESSION['user_id'] > 0)
            {
                update_user_info();
            }
        }
        else
        {
            $_SESSION['user_id']     = 0;
            $_SESSION['user_name']   = '';
            $_SESSION['email']       = '';
            $_SESSION['user_rank']   = 0;
            $_SESSION['discount']    = 1.00;
            if (!isset($_SESSION['login_fail']))
            {
                $_SESSION['login_fail'] = 0;
            }
        }
    }

    /* 设置推荐会员 */
    if (isset($_GET['u']))
    {
        set_affiliate();
    }

    /* session 不存在，检查cookie */
    if (!empty($_COOKIE['YP']['user_id']) && !empty($_COOKIE['YP']['password']))
    {
        // 找到了cookie, 验证cookie信息
        $sql = 'SELECT user_id, user_name, password,is_bind,isfollow ' .
                ' FROM ' .$yp->table('users') .
                " WHERE user_id = '" . intval($_COOKIE['YP']['user_id']) . "' AND password = '" .$_COOKIE['YP']['password']. "'";

        $row = $db->GetRow($sql);

        if (!$row ||($row['is_bind']==0 && $row['isfollow'] ))
        {
            // 没有找到这个记录
           $time = time() - 3600;
           setcookie("YP[user_id]",  '', $time, '/');
           setcookie("YP[password]", '', $time, '/');
        }
        else
        {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_name'] = $row['user_name'];
            update_user_info();
        }
    }

    if (isset($smarty))
    {
        $smarty->assign('yp_session', $_SESSION);
    }
}

if ((DEBUG_MODE & 1) == 1)
{
    error_reporting(E_ALL);
}
else
{
    error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
}
if ((DEBUG_MODE & 4) == 4)
{
    include(ROOT_PATH . 'includes/lib.debug.php');
}

/* 判断是否支持 Gzip 模式 */
if (!defined('INIT_NO_SMARTY') && gzip_enabled())
{
    ob_start('ob_gzhandler');
}
else
{
    ob_start();
}


/*demo.coolhong.com 今-天-优-品-多-商-户-系-统 Q-Q：12 00 29 12 1 mobile start*/
if (!defined('INIT_NO_SMARTY'))
{
    header('Cache-control: private');
    header('Content-type: text/html; charset='.YP_CHARSET);

    /* 创建 Smarty 对象。*/
    require(ROOT_PATH . 'includes/cls_template.php');
    $smarty = new cls_template;

    $smarty->cache_lifetime = $_CFG['cache_time'];
$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
$uachar = "/(nokia|sony|ericsson|mot|samsung|sgh|lg|philips|panasonic|alcatel|lenovo|cldc|midp|mobile)/i";
if(($ua == '' || preg_match($uachar, $ua))&& !strpos(strtolower($_SERVER['REQUEST_URI']),'wap'))
{
$smarty->template_dir   = ROOT_PATH . 'themesmobile/prince_jtypmall_mobile';
$smarty->cache_dir      = ROOT_PATH . 'temp/caches';
$smarty->compile_dir    = ROOT_PATH . 'temp/compiled';
}
else
{
    $smarty->template_dir   = ROOT_PATH . 'themesmobile/prince_jtypmall_mobile';
    $smarty->cache_dir      = ROOT_PATH . 'temp/caches';
    $smarty->compile_dir    = ROOT_PATH . 'temp/compiled';
}

    if ((DEBUG_MODE & 2) == 2)
    {
        $smarty->direct_output = true;
        $smarty->force_compile = true;
    }
    else
    {
        $smarty->direct_output = false;
        $smarty->force_compile = false;
    }

    $smarty->assign('lang', $_LANG);
    $smarty->assign('yp_charset', YP_CHARSET);
if(($ua == '' || preg_match($uachar, $ua))&& !strpos(strtolower($_SERVER['REQUEST_URI']),'wap'))
{
    if (!empty($_CFG['stylename']))
    {
$smarty->assign('yp_css_path', 'themesmobile/' . $_CFG['template'] . '/style_' . $_CFG['stylename'] . '.css');
}
else
{
 $smarty->assign('yp_css_path', 'themesmobile/' . $_CFG['template'] . '/style.css');
}
}else
{
if (!empty($_CFG['stylename']))
{
         $smarty->assign('yp_css_path', 'themesmobile/' . $_CFG['template'] . '/style_' . $_CFG['stylename'] . '.css');
    }
    else
    {
         $smarty->assign('yp_css_path', 'themesmobile/' . $_CFG['template'] . '/style.css');
    }

}
}

//prince 120029121  20161227
$chk_exist=$db -> getOne("SELECT count(*) FROM ". $GLOBALS['yp']->table('users') ." WHERE `user_id` = '".$_SESSION['user_id']."' ");
if($chk_exist==0){
	unset($_SESSION['user_id']);
}
//prince 120029121 20161227

//prince 120029121 
if(0){ 
		$testurl=$_SESSION['user_id'].'-'.$_SESSION['user_name'].'-'.$GLOBALS['yp']->url().'   http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $output= strftime("%Y%m%d %H:%M:%S", time()) . "\n" ;
        $output .= $testurl."\n" ;
        $output.="\n";
        $log_path=ROOT_PATH . "/data/log/";
        if(!is_dir($log_path)){
            @mkdir($log_path, 0777, true);
        }
        $output_date= strftime("%Y%m%d", time());
        file_put_contents($log_path.$output_date."_int.txt", $output, FILE_APPEND | LOCK_EX);
}

$is_weixin_browser = is_weixin_browser();
if($is_weixin_browser){
	if((!preg_match('/region.php/i', $_SERVER['REQUEST_URI'])) && (!preg_match('/user.php/i', $_SERVER['REQUEST_URI'])) && (!preg_match('/register.php/i', $_SERVER['REQUEST_URI']))){//20161130 prince 
		include_once(ROOT_PATH . 'weixin/weixin_oauth.php');//寒冰  20161117   qq   309485552     处理微信环境退出登录后又自动登陆
	}
}

function is_weixin_browser()
{
	$useragent = addslashes($_SERVER['HTTP_USER_AGENT']);
	if(strpos($useragent, 'MicroMessenger') === false && strpos($useragent, 'Windows Phone') === false )
	{
		return false;
	}
	else
	{
		return true;
	}
}

?>