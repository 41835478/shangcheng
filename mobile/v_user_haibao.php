<?php


define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');
require(dirname(__FILE__) . '/includes/lib_v_user.php');
require(dirname(__FILE__) . '/includes/modules/payment/weixin/WxPayPubHelper.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}

if($_CFG['is_distrib'] == 0)
{
	show_message('没有开启微信分销服务！','返回首页','index.php'); 
}

$user_id = intval($_GET['user_id']);

if($_SESSION['user_id'] != $user_id && $user_id > 0)
{
	$weixinconfig = $GLOBALS['db']->getRow( "SELECT * FROM " . $GLOBALS['yp']->table('weixin_config') . " WHERE `id` = 1" );
	define ( APPID, $weixinconfig['appid']); // appid
	define ( APPSECRET, $weixinconfig['appsecret']); // appSecret
	$selfUrl = 'http://' . $_SERVER ['HTTP_HOST'] . $_SERVER ['PHP_SELF'] . '?' . $_SERVER ['QUERY_STRING'];
	
	$jsApi = new JsApi_pub();
	if (!isset($_GET['code'])) 
	{
		// 触发微信返回code码
		$url = $jsApi->createOauthUrlForCode($selfUrl);
		Header("Location: $url");exit;
	}
	else
	{
		// 获取code码，以获取openid
		$code = $_GET['code'];
		$jsApi->setCode($code);
		$openid = $jsApi->getOpenId();
	}

}

//是否生成过二维码
if(is_erweima($user_id) == 0)
{
	require('weixin/wechat.class.php');
	$config = $GLOBALS['db']->getRow ( "SELECT * FROM " . $GLOBALS['yp']->table('weixin_config') . " WHERE `id` = 1" );
	$weixin = new core_lib_wechat($config);
	$scene_id = $db->getOne("select id from " . $GLOBALS['yp']->table('weixin_qcode') . " order by id desc");
	$scene_id = $scene_id ? $scene_id+1 : 1;
	$qcode = $weixin->getQRCode($scene_id,1,$user_id);
	$GLOBALS['db']->query("insert into " . $GLOBALS['yp']->table('weixin_qcode') . " (`id`,`type`,`content`,`qcode`) value ($scene_id,4,'$user_id','{$qcode['ticket']}')");
}


if (!$smarty->is_cached('v_user_haibao.dwt', $cache_id))
{
    assign_template();

    $position = assign_ur_here();
    $smarty->assign('page_title',      $position['title']);    // 页面标题
    $smarty->assign('ur_here',         $position['ur_here']);  // 当前位置

    /* meta information */
    $smarty->assign('keywords',        htmlspecialchars($_CFG['shop_keywords']));
    $smarty->assign('description',     htmlspecialchars($_CFG['shop_desc']));
	$smarty->assign('user_info',get_user_info_by_user_id($user_id)); 
	$smarty->assign('erweima',get_erweima_by_user_id($user_id));
	$smarty->assign('user_id',$user_id);
	
    /* 页面中的动态内容 */
    assign_dynamic('v_user_haibao');
}

$smarty->display('v_user_haibao.dwt', $cache_id);

?>