<?php


define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');
require(dirname(__FILE__) . '/includes/lib_v_user.php');
require(dirname(__FILE__) . '/weixin/wechat.class.php');
if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}

if($_CFG['is_distrib'] == 0)
{
	show_message('没有开启微信分销服务！','返回首页','index.php'); 
}

if(isset($_GET['user_id']) && intval($_GET['user_id']) > 0)
{
	$user_id = intval($_GET['user_id']);
}
else
{
	 yp_header("Location:./\n");
	 exit;
}

if($_SESSION['user_id'] != $user_id && $user_id > 0)
{
	$weixinconfig = $GLOBALS['db']->getRow( "SELECT * FROM " . $GLOBALS['yp']->table('weixin_config') . " WHERE `id` = 1" );
	$weixin = new core_lib_wechat($weixinconfig);
	$openid = '';
	if($_GET['code'])
	{
		$json = $weixin->getOauthAccessToken();
		$openid = $json['openid'];
		if($openid)
		{

			$user_info = $GLOBALS['db']->getRow("SELECT * FROM " . $GLOBALS['yp']->table('users') . " WHERE fake_id='{$openid}'");
			if(empty($user_info))
			{
				 yp_header("Location:register.php?u={$user_id}\n");
				 exit;
			}
		}
	}
	if(empty($openid) || $openid == '')
	{
		$url = $GLOBALS['yp']->url()."v_user_erweima.php?user_id=" . $user_id;  // prince qq 1 2 00 29121
		$url = $weixin->getOauthRedirect($url,1,'snsapi_userinfo');
	}
}

//是否生成过二维码
if(is_erweima($_SESSION['user_id']) == 0)
{
	$config = $GLOBALS['db']->getRow ( "SELECT * FROM " . $GLOBALS['yp']->table('weixin_config') . " WHERE `id` = 1" );
	$weixin = new core_lib_wechat($config);
	$scene_id = $db->getOne("select id from " . $GLOBALS['yp']->table('weixin_qcode') . " order by id desc");
	$scene_id = $scene_id ? $scene_id+1 : 1;
	$qcode = $weixin->getQRCode($scene_id,1,$_SESSION['user_id']);
	$GLOBALS['db']->query("insert into " . $GLOBALS['yp']->table('weixin_qcode') . " (`id`,`type`,`content`,`qcode`) value ($scene_id,4,'" . $_SESSION['user_id'] . "','{$qcode['ticket']}')");
}


if (!$smarty->is_cached('v_user_erweima.dwt', $cache_id))
{
    assign_template();

    $position = assign_ur_here();
    $smarty->assign('page_title',      $position['title']);    // 页面标题
    $smarty->assign('ur_here',         $position['ur_here']);  // 当前位置

    /* meta information */
    $smarty->assign('keywords',        htmlspecialchars($_CFG['shop_keywords']));
    $smarty->assign('description',     htmlspecialchars($_CFG['shop_desc']));
    //zhouhui 如果是获取的uid 则读取它.
    if($user_id){
    	$smarty->assign('user_info',	   get_user_info_by_user_id($user_id)); 
    	$smarty->assign('erweima',get_erweima_by_user_id($user_id));
		$smarty->assign('user_id',$user_id);
    }else
    {
		$smarty->assign('user_info',	   get_user_info_by_user_id($_SESSION['user_id'])); 
		$smarty->assign('erweima',get_erweima_by_user_id($_SESSION['user_id']));
		$smarty->assign('user_id',$_SESSION['user_id']);
	}
	$sql = "select headimg from " . $GLOBALS['yp']->table('users') . " where user_id = '$user_id'";
	$headimg = $GLOBALS['db']->getOne($sql);
	if(strpos($headimg, 'http') === false ){
		if($headimg){//20170923 prince
         	$smarty->assign('headimg', get_pc_url().'/'.$headimg);
		}
    }else{
         $smarty->assign('headimg', $headimg);
	}
    /* 页面中的动态内容 */
    assign_dynamic('v_user_erweima');
}

$smarty->display('v_user_erweima.dwt', $cache_id);

?>