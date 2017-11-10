<?php

/**
 * QQ120029121 广告管理程序
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: prince $
 * $Id: ads.php 17217 2017-04-01 06:29:08Z prince $
*/

define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');

/* act操作项的初始化 */
if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'list';
}
else
{
    $_REQUEST['act'] = trim($_REQUEST['act']);
}

/*------------------------------------------------------ */
//-- 广告列表页面
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    $pid = !empty($_REQUEST['pid']) ? intval($_REQUEST['pid']) : 0;

    $smarty->assign('ur_here',     '微信文章广告列表');
    $smarty->assign('pid',         $pid);
    $smarty->assign('full_page',  1);
 if($_GET["key"]=="del"){//删除用户广告位
	 $sql="delete from " . $GLOBALS['yp']->table('ypmart_article_ad_user') . "  where id='".$_GET['id']."'";
   // mysql_query($sql);
   // mysql_close();
	 $db->query($sql);
	 echo "<script type='text/javascript'>alert('删除成功！！');location.href='article_ad.php?act=list&page=".$_GET["page"]."';      </script>";
	 exit;
}

    if($_GET["key"]=="add"){//通过审核用户广告位
	 $db->query("UPDATE " . $GLOBALS['yp']->table('ypmart_article_ad_user') . "  SET `ad_status`='1' WHERE `id`='".$_GET['id']."'");
   // mysql_query($sql);
   // mysql_close();
   $db->query($sql);
	 echo "<script type='text/javascript'>alert('审核成功！！');location.href='article_ad.php?act=list&page=".$_GET["page"]."';      </script>";
	 exit;
}


    /* 获得总记录数据 */
   $perpagenum = 15;//定义每页显示几条
//
   $total = mysqli_fetch_array($db->query("select count(*) from " . $GLOBALS['yp']->table('ypmart_article_ad_user') . "  "));//查询数据库中一共有多少条数据
   $Total = $total[0];
   $Totalpage = ceil($Total/$perpagenum);//上舍，取整


if(!isset($_GET['page'])||!intval($_GET['page'])||$_GET['page']>$Totalpage)//page可能的四种状态
{
    $page=1;
}
else
{
    $page=$_GET['page'];//如果不满足以上四种情况，则page的值为$_GET['page']
}
$startnum     = ($page-1)*$perpagenum;//开始条数

    /* 获得广告数据 */
    $ads_list = array();
      $sql = "select * from " . $GLOBALS['yp']->table('ypmart_article_ad_user') . "   order by id limit $startnum,$perpagenum";//查询出所需要的条数 
      $res = $GLOBALS['db']->query($sql);
      while ($rew = $GLOBALS['db']->fetchRow($res)){   
        $ads_list[] = $rew;
       }
    
	 $smarty->assign('ads_list',     $ads_list);
  
	$per = $page - 1;//上一页   
    $next = $page + 1;//下一页   

        $smarty->assign('Total',    $Total);
		$smarty->assign('perpagenum',    $perpagenum);
		$smarty->assign('Totalpage',    $Totalpage);
        $smarty->assign('page',    $page);
		$smarty->assign('per',    $per);
		$smarty->assign('next',    $next);
		
		$url = $_SERVER['PHP_SELF'];
		$smarty->assign('url',    $url);

    $smarty->display('weixin/article_ad_list.html');
}


/*------------------------------------------------------ */
//-- 文章系统设置页面告页面
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'config')
{
    admin_priv('ad_manage');

   if($_POST){
           
            if(!empty($_POST['all_rank']))
	{
		$sql = "UPDATE " . $yp->table('weixin_ad_config',1) . " SET user_rank = '" . $_POST['all_rank'][0] . "' WHERE `id` = 1";
        $db->query($sql);
	}
	else
	{
		if(!empty($_POST['user_rank']))
		{
			$user_ranks = $_POST['user_rank'];
			$user_rank = '';
			for($i = 0;$i < count($user_ranks); $i++) 
			{
				 $user_rank .= $user_ranks[$i].',';
			}
			$user_rank = rtrim($user_rank,",");
			$sql = "UPDATE " . $yp->table('weixin_ad_config',1) . " SET user_rank = '" . $user_rank . "' WHERE `id` = 1";
			$db->query($sql);
		}
        else
        {
            $sql = "UPDATE " . $yp->table('weixin_ad_config',1) . " SET user_rank = '0' WHERE `id` = 1";
            $db->query($sql);
        }
	} 
   
   
			

			$article_catid 		= intval($_POST ['article_catid']);
			$is_deduct_money 	= intval($_POST ['is_deduct_money']);
			$deduct_money 	= $_POST ['deduct_money'];
			$deduct_point = intval($_POST ['deduct_point']);
			$is_zanshang 	= intval($_POST ['is_zanshang']);
			$zan_money 		= $_POST ['zan_money'];
			$admin_ad 	= intval($_POST ['admin_ad']);
			$ad_text = $_POST['ad_text'];
			$ad_url = $_POST['ad_url'];
			$is_reward 	= intval($_POST ['is_reward']);
			$reward_point = intval($_POST['reward_point']);
			$reward_rank_point = intval($_POST['reward_rank_point']);
			$is_ad_reward = intval($_POST['is_ad_reward']);
			$earnings_times = intval($_POST['earnings_times']);
			$ad_reward_money = $_POST['ad_reward_money'];
			$ad_reward_point = intval($_POST['ad_reward_point']);
			
			$ret = $db->query (
					"UPDATE " . $GLOBALS['yp']->table('weixin_ad_config') . " SET
					`article_catid`		='$article_catid',
					`is_deduct_money`		='$is_deduct_money',
					`deduct_money`	='$deduct_money',
					`deduct_point`	='$deduct_point',
					`is_zanshang`	='$is_zanshang',
					`zan_money`	='$zan_money',
					`admin_ad`		='$admin_ad',
					`ad_text`		='$ad_text',
					`ad_url`	='$ad_url',
					`is_reward`		='$is_reward',
					`reward_point`		='$reward_point',
					`reward_rank_point`	='$reward_rank_point',
					`is_ad_reward`	='$is_ad_reward',
					`earnings_times`	='$earnings_times',
					`ad_reward_money`		='$ad_reward_money',
					`ad_reward_point`		='$ad_reward_point'
					WHERE `id`=1;" );
					$link [] = array ('href' => 'article_ad.php?act=config','text' => '文章广告系统设置');
			if ($ret) {
					sys_msg ( '设置成功', 0, $link );
			} else {
				sys_msg ( '设置失败，请重试', 0, $link );
			}
		}else{
			$smarty->assign('ur_here',      "文章广告系统设置");
			$ret = $db->getRow ( "SELECT * FROM " . $GLOBALS['yp']->table('weixin_ad_config') . " WHERE `id` = 1" );
			$smarty->assign ( 'article_catid', $ret ['article_catid'] );
			$smarty->assign ( 'is_deduct_money', $ret ['is_deduct_money'] );
			$smarty->assign ( 'deduct_money', $ret ['deduct_money'] );
			$smarty->assign ( 'deduct_point', $ret ['deduct_point'] );
            $smarty->assign ( 'is_zanshang', $ret ['is_zanshang'] );
			$smarty->assign ( 'zan_money', $ret ['zan_money'] );
			$smarty->assign ( 'admin_ad', $ret ['admin_ad'] );
			$smarty->assign ( 'ad_text', $ret ['ad_text'] );
			$smarty->assign ( 'ad_url', $ret ['ad_url'] );
			$smarty->assign ( 'is_reward', $ret ['is_reward'] );
			$smarty->assign ( 'reward_point', $ret ['reward_point'] );
			$smarty->assign ( 'reward_rank_point', $ret ['reward_rank_point'] );
			$smarty->assign ( 'is_ad_reward', $ret ['is_ad_reward'] );
			$smarty->assign ( 'earnings_times', $ret ['earnings_times'] );
			$smarty->assign ( 'ad_reward_money', $ret ['ad_reward_money'] );
			$smarty->assign ( 'ad_reward_point', $ret ['ad_reward_point'] );
				/* 取得用户等级 */
	$sql = "SELECT user_rank FROM " . $yp->table('weixin_ad_config') . " WHERE `id` = 1";
	$ranks = $db->getOne($sql);
    $user_rank_list = array();
    $sql = "SELECT rank_id, rank_name FROM " . $yp->table('user_rank');
    $res = $db->query($sql);

    while ($row = $db->fetchRow($res))
    {
        $row['checked'] = strpos(',' . $ranks . ',', ',' . $row['rank_id']. ',') !== false;
		
        $user_rank_list[] = $row;
    }
	if($ranks == '-1')
	{
		$smarty->assign('ranks',$ranks);
	}
    $smarty->assign('user_rank_list', $user_rank_list);
	
	
    $smarty->assign('cfg', $_CFG);

    assign_query_info();
			
			
           $smarty->display('weixin/article_ad_config.html');
		   
		   
}

}
?>