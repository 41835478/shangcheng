<?php

/**
 * QQ120029121 支付响应页面
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: prince $
 * $Id: respond.php 17217 2017-04-01 06:29:08Z prince $
 */

define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_payment.php');
require(ROOT_PATH . 'includes/lib_order.php');
/* 支付方式代码 */
$pay_code = !empty($_REQUEST['code']) ? trim($_REQUEST['code']) : 'weixin';

//获取首信支付方式
if (empty($pay_code) && !empty($_REQUEST['v_pmode']) && !empty($_REQUEST['v_pstring']))
{
    $pay_code = 'cappay';
}

//获取快钱神州行支付方式
if (empty($pay_code) && ($_REQUEST['ext1'] == 'shenzhou') && ($_REQUEST['ext2'] == 'jtypmall'))
{
    $pay_code = 'shenzhou';
}

/* 参数是否为空 */
if (empty($pay_code))
{
    $msg = $_LANG['pay_not_exist'];
}
else
{
    /* 检查code里面有没有问号 */
    if (strpos($pay_code, '?') !== false)
    {
        $arr1 = explode('?', $pay_code);
        $arr2 = explode('=', $arr1[1]);

        $_REQUEST['code']   = $arr1[0];
        $_REQUEST[$arr2[0]] = $arr2[1];
        $_GET['code']       = $arr1[0];
        $_GET[$arr2[0]]     = $arr2[1];
        $pay_code           = $arr1[0];
    }

    /* 判断是否启用 */
    $sql = "SELECT COUNT(*) FROM " . $yp->table('payment') . " WHERE pay_code = '$pay_code' AND enabled = 1";
    if ($db->getOne($sql) == 0)
    {
        $msg = $_LANG['pay_disabled'];
    }
    else
    {
        $plugin_file = 'includes/modules/payment/' . $pay_code . '.php';

        /* 检查插件文件是否存在，如果存在则验证支付是否成功，否则则返回失败信息 */
        if (file_exists($plugin_file))
        {
            /* 根据支付方式代码创建支付类的对象并调用其响应操作方法 */
            include_once($plugin_file);

            $payment = new $pay_code();
            $msg     = ($payment->respond()) ? $_LANG['pay_success'] : $_LANG['pay_fail'];
            if($_GET['code'] == 'weixin' && $_GET['from'] == 'notify'){
            	echo 'success';exit;
            }
        }
        else
        {
            $msg = $_LANG['pay_not_exist'];
        }
    }
}

//新版拼团新增 S PRINCE
$user_id=$_SESSION['user_id']?$_SESSION['user_id']:0;
$order = $db->getRow("SELECT * FROM " . $yp->table('order_info') . " WHERE `user_id` = '$user_id' and pay_time>unix_timestamp(now())-3600*8-120 ORDER BY order_id DESC LIMIT 1");
if($order &&  $order['extension_code']=='extpintuan'){
	$order_id=$order['order_id'];
	$sql = "SELECT * FROM " . $yp->table('extpintuan_orders') . " WHERE order_id = '$order_id' ";
	$extpintuan = $db->getRow($sql);
	if($extpintuan){
	$pt_id=$extpintuan['pt_id'];
	$follow_user=$extpintuan['follow_user'];
	$url = 'extpintuan.php?act=pt_view&pt_id='.$pt_id.'&u='.$follow_user;
	yp_header("Location: $url\n");
    }
}
//新版拼团新增 E PRINCE


//云购新增 S PRINCE
$user_id=$_SESSION['user_id']?$_SESSION['user_id']:0;
$order = $db->getRow("SELECT * FROM " . $yp->table('order_info') . " WHERE `user_id` = '$user_id' and pay_time>unix_timestamp(now())-3600*8-120 ORDER BY order_id DESC LIMIT 1");
if($order &&  $order['extension_code']=='lucky_buy'){
	$url = 'lucky_buy.php?act=userlist';
	yp_header("Location: $url\n");
}
//云购新增 E PRINCE


//订单抽奖

$user_id=$_SESSION['user_id']?$_SESSION['user_id']:0;
$order = $db->getRow("SELECT * FROM " . $yp->table('order_info') . " WHERE `user_id` = '$user_id' and pay_time>unix_timestamp(now())-3600*8-120 and `order_draw_status` = '0' ORDER BY order_id DESC LIMIT 1");
if($order && $GLOBALS['_CFG']['order_draw'] && $order['goods_amount'] >= $GLOBALS['_CFG']['min_order_draw'] &&  empty($order['extension_code'])){
$url = 'weixin/act.php?aid='.$GLOBALS['_CFG']['draw_form'].'&order_id='.$order['order_id'];

echo "<script>alert('\u8ba2\u5355\u652f\u4ed8\u6210\u529f\uff01\u60a8\u5c06\u83b7\u5f97\u4e00\u6b21\u62bd\u5956\u673a\u4f1a\uff0c\u70b9\u51fb\u3010\u786e\u5b9a\u3011\u8fdb\u884c\u62bd\u5956\uff01\uff01');window.location.href=\"{$url}\";</script>";
exit;

}




//end   寒冰qq  30  9485  552   20161112

assign_template();
$position = assign_ur_here();
$smarty->assign('page_title', $position['title']);   // 页面标题
$smarty->assign('ur_here',    $position['ur_here']); // 当前位置
$smarty->assign('page_title', $position['title']);   // 页面标题
$smarty->assign('ur_here',    $position['ur_here']); // 当前位置
$smarty->assign('helps',      get_shop_help());      // 网店帮助

$smarty->assign('message',    $msg);
$smarty->assign('shop_url',   $yp->url());

$smarty->display('respond.dwt');

?>