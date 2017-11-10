<?php


define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');
require(dirname(__FILE__) . '/includes/lib_v_user.php');
/* 载入语言文件 */
require_once (ROOT_PATH . 'languages/' . $_CFG['lang'] . '/user.php');


if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}

if($_CFG['is_distrib'] == 0)
{
	show_message('没有开启微信分销服务！','返回首页','index.php'); 
}

if($_SESSION['user_id'] == 0)
{
	yp_header("Location: ./\n");
    exit;	 
}

$is_distribor = is_distribor($_SESSION['user_id']);
if($is_distribor != 1)
{
	show_message('您还不是分销商！','去首页','index.php');
	exit;
}


if (!$smarty->is_cached('v_user_news.dwt', $cache_id))
{
    assign_template();

    $position = assign_ur_here();
    $smarty->assign('page_title',      $position['title']);    // 页面标题
    $smarty->assign('ur_here',         $position['ur_here']);  // 当前位置

    /* meta information */
    $smarty->assign('keywords',        htmlspecialchars($_CFG['shop_keywords']));
    $smarty->assign('description',     htmlspecialchars($_CFG['shop_desc']));
	
	//$smarty->assign('boss_info',get_boss_by_user_id($_SESSION['user_id'])); //获取上司信息
	
		
	$user = $GLOBALS['user'];
	$_CFG = $GLOBALS['_CFG'];
	$_LANG = $GLOBALS['_LANG'];
	$smarty = $GLOBALS['smarty'];
	$db = $GLOBALS['db'];
	$yp = $GLOBALS['yp'];
	$user_id = $GLOBALS['user_id'];

	$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
	$smarty->assign('affiliate', $affiliate);

			$affdb = array();
			$num = count($affiliate['item']);
			$up_uid = "'$user_id'";
			$all_uid = "'$user_id'";
			for($i = 1; $i <= $num; $i ++)
			{
				$count = 0;
				if($up_uid)
				{
					$sql = "SELECT user_id FROM " . $yp->table('users') . " WHERE parent_id IN($up_uid)";
					$query = $db->query($sql);
					$up_uid = '';
					while($rt = $db->fetch_array($query))
					{
						$up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
						if($i < $num)
						{
							$all_uid .= ", '$rt[user_id]'";
						}
						$count ++;
					}
				}
				$affdb[$i]['num'] = $count;
				$affdb[$i]['point'] = $affiliate['item'][$i - 1]['level_point'];
				$affdb[$i]['money'] = $affiliate['item'][$i - 1]['level_money'];
			}
			$smarty->assign('affdb', $affdb);
			
	$affiliate_intro = nl2br(sprintf($_LANG['affiliate_intro'][$affiliate['config']['separate_by']]));

		

		
	$smarty->assign('affiliate_intro', $affiliate_intro);
	$smarty->assign('affiliate_type', $affiliate['config']['separate_by']);
		
	$smarty->assign('logdb', $logdb);

	
	$smarty->assign('userid', $user_id);
	$smarty->assign('lang', $_LANG);
		
	$smarty->assign('user_id',$_SESSION['user_id']);
    /* 页面中的动态内容 */
    assign_dynamic('v_user_news');
}

$smarty->display('v_user_news.dwt', $cache_id);

?>