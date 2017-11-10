<?php


define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');
require(dirname(__FILE__) . '/includes/lib_v_user.php');

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

if(isset($_REQUEST['act']) && $_REQUEST['act'] == 'act_tixian')
{
	$tixian = array(
           'deposit_money' => $_POST['deposit_money'] > 0 ? $_POST['deposit_money'] : 0,
           'add_time'      => gmtime(),
           'user_id'       => $_SESSION['user_id']
    );
	
	if($tixian['deposit_money'] <= 0)
	{
		show_message('您输入的金额不正确！'); 
	}
	
	$user_money = get_sum_split_money($_SESSION['user_id']); 
	if($tixian['deposit_money'] > $user_money)
	{
		show_message('您的余额不足，请重新输入！');exit;
	}
	
	if(!empty($_CFG['fenxiao_deposit_least_money']))
	{
		if($tixian['deposit_money'] < $_CFG['fenxiao_deposit_least_money'])
		{
			show_message('每次结算金额不能少于'.$_CFG['fenxiao_deposit_least_money'].'元');exit;
		}
	}
	
	$reserve_money = $_CFG['reserve_money'] > 0 ? $_CFG['reserve_money'] : 0;
	
	if($user_money - $tixian['deposit_money'] < $reserve_money)
	{
		show_message('结算后，账户预留金额不能少于'.$reserve_money.'元');exit; 
	}
	
	$GLOBALS['db']->autoExecute($GLOBALS['yp']->table('deposit'), $tixian, 'INSERT');
	$error_no = $GLOBALS['db']->errno();
    if ($error_no > 0)
    {
        show_message($GLOBALS['db']->errorMsg());
    }
	else
	{
		log_account_change($tixian['user_id'], $tixian['deposit_money'], 0, 0, 0,'佣金结算到余额');
		$uid=$tixian['user_id'];
		$username=$_SESSION['user_name'];
		$money=-$tixian['deposit_money'];
		$change_desc='结算到余额';
		$time = gmtime();
        $sql = "INSERT INTO " . $GLOBALS['yp']->table('affiliate_log') . "(user_id, user_name, time, money, separate_type,change_desc)"." VALUES ('$uid', '$username', '$time', '$money', '4','$change_desc')";
        $GLOBALS['db']->query($sql);
		show_message('结算成功！请查看账户余额！','返回分销中心','v_user.php');
	}
}


if (!$smarty->is_cached('v_user_tixian.dwt', $cache_id))
{
    assign_template();

    $position = assign_ur_here();
    $smarty->assign('page_title',      $position['title']);    // 页面标题
    $smarty->assign('ur_here',         $position['ur_here']);  // 当前位置

    /* meta information */
    $smarty->assign('keywords',        htmlspecialchars($_CFG['shop_keywords']));
    $smarty->assign('description',     htmlspecialchars($_CFG['shop_desc']));
	$smarty->assign('user_info',get_user_info_by_user_id($_SESSION['user_id']));
	$smarty->assign('info',get_user_info($_SESSION['user_id']));
	$smarty->assign('split_money',get_sum_split_money($_SESSION['user_id']));
	$smarty->assign('user_id',$_SESSION['user_id']);
}
$smarty->display('v_user_tixian.dwt', $cache_id);



?>