<?php

/**
 * QQ120029121 会员帐目管理(包括预付款，余额)
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: PRINCE $
 * $Id: user_account.php 17217 2017-04-01 06:29:08Z PRINCE $
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
//-- 会员余额记录列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    /* 权限判断 */
    admin_priv('surplus_manage');

    /* 指定会员的ID为查询条件 */
    $user_id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

    /* 获得支付方式列表 */
    $payment = array();
    $sql = "SELECT pay_id, pay_name FROM ".$yp->table('payment').
           " WHERE enabled = 1 AND pay_code != 'cod' ORDER BY pay_id";
    $res = $db->query($sql);

    while ($row = $db->fetchRow($res))
    {
        $payment[$row['pay_name']] = $row['pay_name'];
    }

    /* 模板赋值 */
    if (isset($_REQUEST['process_type']))
    {
        $smarty->assign('process_type_' . intval($_REQUEST['process_type']), 'selected="selected"');
    }
    if (isset($_REQUEST['is_paid']))
    {
        $smarty->assign('is_paid_' . intval($_REQUEST['is_paid']), 'selected="selected"');
    }
    $smarty->assign('ur_here',       $_LANG['09_user_account']);
    $smarty->assign('id',            $user_id);
    $smarty->assign('payment_list',  $payment);
    $smarty->assign('action_link',   array('text' => $_LANG['surplus_add'], 'href'=>'user_account.php?act=add'));

    $list = account_list();
    $smarty->assign('list',         $list['list']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);
    $smarty->assign('full_page',    1);

    assign_query_info();
    $smarty->display('user_account_list.htm');
}
/*------------------------------------------------------ */
//-- 话费充值设置  寒冰  qq  3094  85552  20160825
/*------------------------- ----------------------------- */
if ($_REQUEST['act'] == 'huafei_add')
{
    /* 权限判断 */
    admin_priv('surplus_manage');

   if($_POST){
           
      if(!empty($_POST['all_rank']))
	{
		$sql = "UPDATE " . $yp->table('user_account_huafei_config',1) . " SET user_rank = '" . $_POST['all_rank'][0] . "' WHERE `id` = 1";
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
			$sql = "UPDATE " . $yp->table('user_account_huafei_config',1) . " SET user_rank = '" . $user_rank . "' WHERE `id` = 1";
			$db->query($sql);
		}
        else
        {
            $sql = "UPDATE " . $yp->table('user_account_huafei_config',1) . " SET user_rank = '0' WHERE `id` = 1";
            $db->query($sql);
        }
	} 
   
   
			

			$appid 		= intval($_POST ['appid']);
			$appkey 	= $_POST ['appkey'];
			$account_zhekou 	= $_POST ['account_zhekou'];
			$account_cycle = intval($_POST ['account_cycle']);
			$account_money 		= $_POST ['account_money'];
			$ad_url = $_POST['ad_url'];
			
			$ret = $db->query (
					"UPDATE " . $GLOBALS['yp']->table('user_account_huafei_config') . " SET
					`appid`		='$appid',
					`appkey`		='$appkey',
					`account_zhekou`	='$account_zhekou',
					`account_cycle`	='$account_cycle',
					`account_money`	='$account_money',
					`ad_url`	='$ad_url'
					WHERE `id`=1;" );
					$link [] = array ('href' => 'user_account.php?act=huafei_add','text' => '手机话费充值设置');
			if ($ret) {
					sys_msg ( '设置成功', 0, $link );
			} else {
				sys_msg ( '设置失败，请重试', 0, $link );
			}
		}else{
			$smarty->assign('ur_here',      "手机话费充值设置");
			$ret = $db->getRow ( "SELECT * FROM " . $GLOBALS['yp']->table('user_account_huafei_config') . " WHERE `id` = 1" );
			$smarty->assign ( 'appid', $ret ['appid'] );
			$smarty->assign ( 'appkey', $ret ['appkey'] );
			$smarty->assign ( 'account_zhekou', $ret ['account_zhekou'] );
			$smarty->assign ( 'account_cycle', $ret ['account_cycle'] );
			$smarty->assign ( 'account_money', $ret ['account_money'] );
			$smarty->assign ( 'ad_url', $ret ['ad_url'] );
				/* 取得用户等级 */
	$sql = "SELECT user_rank FROM " . $yp->table('user_account_huafei_config') . " WHERE `id` = 1";
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
    $smarty->display('user_account_huafei_add.html');
}
}
/*------------------------------------------------------ */
//-- 话费充值记录列表  寒冰  qq  3094  85552
/*------------------------- ----------------------------- */
if ($_REQUEST['act'] == 'huafei')
{
    /* 权限判断 */
    admin_priv('surplus_manage');

    /* 指定会员的ID为查询条件 */
    $user_id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

    /* 获得支付方式列表 */
    $payment = array();
    $sql = "SELECT pay_id, pay_name FROM ".$yp->table('payment').
           " WHERE enabled = 1 AND pay_code != 'cod' ORDER BY pay_id";
    $res = $db->query($sql);

    while ($row = $db->fetchRow($res))
    {
        $payment[$row['pay_name']] = $row['pay_name'];
    }

    /* 模板赋值 */
    if (isset($_REQUEST['process_type']))
    {
        $smarty->assign('process_type_' . intval($_REQUEST['process_type']), 'selected="selected"');
    }
    if (isset($_REQUEST['is_paid']))
    {
        $smarty->assign('is_paid_' . intval($_REQUEST['is_paid']), 'selected="selected"');
    }
    $smarty->assign('ur_here',       '手机话费充值记录');
    $smarty->assign('id',            $user_id);
    $smarty->assign('payment_list',  $payment);
   

    $list = account_huafei_list();
    $smarty->assign('list',         $list['list']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);
    $smarty->assign('full_page',    1);

    assign_query_info();
    $smarty->display('user_account_huafei_log.htm');
}
/*------------------------------------------------------ */
//-- 添加/编辑会员余额页面
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit')
{
    admin_priv('surplus_manage'); //权限判断

    $ur_here  = ($_REQUEST['act'] == 'add') ? $_LANG['surplus_add'] : $_LANG['surplus_edit'];
    $form_act = ($_REQUEST['act'] == 'add') ? 'insert' : 'update';
    $id       = isset($_GET['id']) ? intval($_GET['id']) : 0;

    /* 获得支付方式列表, 不包括“货到付款” */
    $user_account = array();
    $payment = array();
    $sql = "SELECT pay_id, pay_name FROM ".$yp->table('payment').
           " WHERE enabled = 1 AND pay_code != 'cod' ORDER BY pay_id";
    $res = $db->query($sql);

    while ($row = $db->fetchRow($res))
    {
        $payment[$row['pay_name']] = $row['pay_name'];
    }

    if ($_REQUEST['act'] == 'edit')
    {
        /* 取得余额信息 */
        $user_account = $db->getRow("SELECT * FROM " .$yp->table('user_account') . " WHERE id = '$id'");

        // 如果是负数，去掉前面的符号
        $user_account['amount'] = str_replace('-', '', $user_account['amount']);

        /* 取得会员名称 */
        $sql = "SELECT user_name FROM " .$yp->table('users'). " WHERE user_id = '$user_account[user_id]'";
        $user_name = $db->getOne($sql);
    }
    else
    {
        $surplus_type = '';
        $user_name    = '';
    }

    /* 模板赋值 */
    $smarty->assign('ur_here',          $ur_here);
    $smarty->assign('form_act',         $form_act);
    $smarty->assign('payment_list',     $payment);
    $smarty->assign('action',           $_REQUEST['act']);
    $smarty->assign('user_surplus',     $user_account);
    $smarty->assign('user_name',        $user_name);
    if ($_REQUEST['act'] == 'add')
    {
        $href = 'user_account.php?act=list';
    }
    else
    {
        $href = 'user_account.php?act=list&' . list_link_postfix();
    }
    $smarty->assign('action_link', array('href' => $href, 'text' => $_LANG['09_user_account']));

    assign_query_info();
    $smarty->display('user_account_info.htm');
}

/*------------------------------------------------------ */
//-- 添加/编辑会员余额的处理部分
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update')
{
    
    
	if($_SESSION['admin_name']=='test'){
		sys_msg('对不起，您的权限不足，请勿修改！');
	}
	/* 权限判断 */
    admin_priv('surplus_manage');

    /* 初始化变量 */
    $id           = isset($_POST['id'])            ? intval($_POST['id'])             : 0;
    $is_paid      = !empty($_POST['is_paid'])      ? intval($_POST['is_paid'])        : 0;
    $amount       = !empty($_POST['amount'])       ? floatval($_POST['amount'])       : 0;
    $process_type = !empty($_POST['process_type']) ? intval($_POST['process_type'])   : 0;
    $user_name    = !empty($_POST['user_id'])      ? trim($_POST['user_id'])          : '';
    $admin_note   = !empty($_POST['admin_note'])   ? trim($_POST['admin_note'])       : '';
    $user_note    = !empty($_POST['user_note'])    ? trim($_POST['user_note'])        : '';
    $payment      = !empty($_POST['payment'])      ? trim($_POST['payment'])          : '';

    $user_id = $db->getOne("SELECT user_id FROM " .$yp->table('users'). " WHERE user_name = '$user_name'");

    /* 此会员是否存在 */
    if ($user_id == 0)
    {
        $link[] = array('text' => $_LANG['go_back'], 'href'=>'javascript:history.back(-1)');
        sys_msg($_LANG['username_not_exist'], 0, $link);
    }

    /* 退款，检查余额是否足够 */
    if ($process_type == 1)
    {
		$account_sum = get_user_surplus($user_id);
		$user_account=$account_sum['user_money'];

        /* 如果扣除的余额多于此会员拥有的余额，提示 */
        if ($amount > $user_account)
        {
            $link[] = array('text' => $_LANG['go_back'], 'href'=>'javascript:history.back(-1)');
            sys_msg($_LANG['surplus_amount_error'], 0, $link);
        }
    }

    if ($_REQUEST['act'] == 'insert')
    {
        /* 入库的操作 */
        if ($process_type == 1)
        {
            $amount = (-1) * $amount;
        }
        $sql = "INSERT INTO " .$yp->table('user_account').
		       "(`id`,`user_id`,`admin_user`,`amount`,`add_time`,`paid_time`,`admin_note`,`user_note`,`process_type`,`payment`,`is_paid`) ".
               " VALUES ('', '$user_id', '$_SESSION[admin_name]', '$amount', '".gmtime()."', '".gmtime()."', '$admin_note', '$user_note', '$process_type', '$payment', '$is_paid')";
        $db->query($sql);
        $id = $db->insert_id();
    }
    else
    {
        /* 更新数据表 */
        $sql = "UPDATE " .$yp->table('user_account'). " SET ".
               "admin_note   = '$admin_note', ".
               "user_note    = '$user_note', ".
               "payment      = '$payment' ".
              "WHERE id      = '$id'";
        $db->query($sql);
    }

    // 更新会员余额数量
    if ($is_paid == 1)
    {
        $change_desc = $amount > 0 ? $_LANG['surplus_type_0'] : $_LANG['surplus_type_1'];
        $change_type = $amount > 0 ? ACT_SAVING : ACT_DRAWING;
        log_account_change($user_id, $amount, 0, 0, 0, $change_desc, $change_type);
    }

    //如果是预付款并且未确认，向pay_log插入一条记录
    if ($process_type == 0 && $is_paid == 0)
    {
        include_once(ROOT_PATH . 'includes/lib_order.php');

        /* 取支付方式信息 */
        $payment_info = array();
        $payment_info = $db->getRow('SELECT * FROM ' . $yp->table('payment').
                                    " WHERE pay_name = '$payment' AND enabled = '1'");
        //计算支付手续费用
        $pay_fee   = pay_fee($payment_info['pay_id'], $amount, 0);
        $total_fee = $pay_fee + $amount;

        /* 插入 pay_log */
        $sql = 'INSERT INTO ' . $yp->table('pay_log') . " (order_id, order_amount, order_type, is_paid)" .
                " VALUES ('$id', '$total_fee', '" .PAY_SURPLUS. "', 0)";
        $db->query($sql);
    }

    /* 记录管理员操作 */
    if ($_REQUEST['act'] == 'update')
    {
        admin_log($user_name, 'edit', 'user_surplus');
    }
    else
    {
        admin_log($user_name, 'add', 'user_surplus');
    }

    /* 提示信息 */
    if ($_REQUEST['act'] == 'insert')
    {
        $href = 'user_account.php?act=list';
    }
    else
    {
        $href = 'user_account.php?act=list&' . list_link_postfix();
    }
    $link[0]['text'] = $_LANG['back_list'];
    $link[0]['href'] = $href;

    $link[1]['text'] = $_LANG['continue_add'];
    $link[1]['href'] = 'user_account.php?act=add';

    sys_msg($_LANG['attradd_succed'], 0, $link);
}

/*------------------------------------------------------ */
//-- 审核会员余额页面
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'check')
{
    /* 检查权限 */
    admin_priv('surplus_manage');

    /* 初始化 */
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    /* 如果参数不合法，返回 */
    if ($id == 0)
    {
        yp_header("Location: user_account.php?act=list\n");
        exit;
    }

    /* 查询当前的预付款信息 */
    $account = array();
    $account = $db->getRow("SELECT * FROM " .$yp->table('user_account'). " WHERE id = '$id'");
    $account['add_time'] = local_date($_CFG['time_format'], $account['add_time']);

    //余额类型:预付款，退款申请，购买商品，取消订单
    if ($account['process_type'] == 0)
    {
        $process_type = $_LANG['surplus_type_0'];
    }
    elseif ($account['process_type'] == 1)
    {
        $process_type = $_LANG['surplus_type_1'];
    }
    elseif ($account['process_type'] == 2)
    {
        $process_type = $_LANG['surplus_type_2'];
    }
    else
    {
        $process_type = $_LANG['surplus_type_3'];
    }

    $sql = "SELECT user_name FROM " .$yp->table('users'). " WHERE user_id = '$account[user_id]'";
    $user_name = $db->getOne($sql);
    $sql = "SELECT supplier_id FROM " .$yp->table('users'). " WHERE user_id = '$account[user_id]'";
    $supplier_id = $db->getOne($sql);

    /* 模板赋值 */
    $smarty->assign('ur_here',      $_LANG['check']);
    $account['user_note'] = htmlspecialchars($account['user_note']);
    $smarty->assign('surplus',      $account);
    $smarty->assign('process_type', $process_type);
    $smarty->assign('user_name',    $user_name);
    $smarty->assign('supplier_id',    $supplier_id);
    $smarty->assign('id',           $id);
    $smarty->assign('action_link',  array('text' => $_LANG['09_user_account'],
    'href'=>'user_account.php?act=list&' . list_link_postfix()));

    /* 页面显示 */
    assign_query_info();
    $smarty->display('user_account_check.htm');
}

/*------------------------------------------------------ */
//-- 更新会员余额的状态
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'action')
{
    /* 检查权限 */
    admin_priv('surplus_manage');

    /* 初始化 */
    $id         = isset($_POST['id'])         ? intval($_POST['id'])             : 0;
    $is_paid    = isset($_POST['is_paid'])    ? intval($_POST['is_paid'])        : 0;
    $admin_note = isset($_POST['admin_note']) ? trim($_POST['admin_note'])       : '';

    /* 如果参数不合法，返回 */
    if ($id == 0 || empty($admin_note))
    {
        yp_header("Location: user_account.php?act=list\n");
        exit;
    }

    /* 查询当前的预付款信息 */
    $account = array();
    $account = $db->getRow("SELECT * FROM " .$yp->table('user_account'). " WHERE id = '$id'");
    $amount  = $account['amount'];

    //如果状态为未确认
    if ($account['is_paid'] == 0)
    {
        //如果是退款申请, 并且已完成,更新此条记录,扣除相应的余额
        if ($is_paid == '1' && $account['process_type'] == '1')
        {
			$account_sum = get_user_surplus($account['user_id']);
			$user_account=$account_sum['user_money'];
            $fmt_amount   = str_replace('-', '', $amount);
			$user_frozen_money = $account_sum['frozen_money'];//用户冻结金额
			

            //如果扣除的余额多于此会员拥有的余额，提示
            if ($fmt_amount > $user_account + $user_frozen_money)
            {
                $link[] = array('text' => $_LANG['go_back'], 'href'=>'javascript:history.back(-1)');
                sys_msg($_LANG['surplus_amount_error'], 0, $link);
            }
          
           //微信红包or企业转账支付用户提现金额
		   
		    $sql = "SELECT * FROM " .$yp->table('users'). " WHERE user_id = '$account[user_id]'";
       		$ret = $db->getRow($sql);//获取提现用户微信资料
			
			if(!empty($ret['fake_id']) && $account['account_type'] == "微信")// 20161126 prince 
			{//微信id不为空
				if($ret['isfollow'] ==  '1' && $fmt_amount >= 1 and $fmt_amount <= 200)//会员已关注 提现金额1到200之间
				{
					require("../wxhongbao.php");
					$arr['present_manner'] = 'hongbao';
					$arr['openid'] = $ret['fake_id'];
					$arr['hbname'] = $_CFG['shop_name'];
					$arr['body'] = "您的提现申请已经成功";
					$arr['fee'] = $fmt_amount;
					$comm = new Common_util_pub();        	
					$sendhb = $comm->sendhongbaoto($arr);
					//var_dump($sendhb);
					if($sendhb['result_code']!='SUCCESS'){
						sys_msg(var_dump($sendhb), 0, $link);
						exit;
					}
			    }else{
					require("../wxhongbao.php");
					$arr['openid'] = $ret['fake_id'];
					$arr['present_manner'] = 'lingqian';
					$arr['name'] = $ret['nickname'];
					$arr['hbname'] = $_CFG['shop_name'];
					$arr['body'] = "您的提现申请已经成功处理";
					$arr['fee'] = $fmt_amount;
					$comm = new Common_util_pub();        	
					$sendhb = $comm->sendhongbaoto($arr);
					//var_dump($sendhb);
					if($sendhb['result_code']!='SUCCESS'){
						sys_msg(var_dump($sendhb), 0, $link);
						exit;
					}
			    }
            }
			
            update_user_account($id, $amount, $admin_note, $is_paid);

            //更新会员余额数量
			 if ($fmt_amount > $user_frozen_money)//如果提现金额大于用户冻结金额
            {
			     $difference = $fmt_amount - $user_frozen_money;
                 log_account_change($account['user_id'], '-'.$difference, '-'.$user_frozen_money, 0, 0, $_LANG['surplus_type_1'], ACT_DRAWING);//多余部分从用户余额中扣除
            }else{
			
			 log_account_change($account['user_id'], 0, $amount, 0, 0, $_LANG['surplus_type_1'], ACT_DRAWING);
			
			}
         
			
			
			
			//是否开启余额变动给客户发短信 -提现
			if($_CFG['sms_user_money_change'] == 1)
			{
				$sql = "SELECT user_money,mobile_phone FROM " . $GLOBALS['yp']->table('users') . " WHERE user_id = '" . $account['user_id'] . "'";
				$users = $GLOBALS['db']->getRow($sql);
				$content = sprintf($_CFG['sms_deposit_balance_reduce_tpl'],date("Y-m-d H:i:s",gmtime()),$amount,$users['user_money'],$_CFG['sms_sign']);
				if($users['mobile_phone'])
				{
					include_once(ROOT_PATH. 'sms/sms.php');
					$templateParam=Array("code"=>$users['user_money']);
					$templateCode=$GLOBALS['_CFG']['sms_user_money_change_dayu'];
					sendSMS($users['mobile_phone'],$content,'', '',$templateParam , $templateCode);//已支持阿里大于 QQ 120029121
				}
			}
        }
        elseif ($is_paid == '1' && $account['process_type'] == '0')
        {
            //如果是预付款，并且已完成, 更新此条记录，增加相应的余额
            update_user_account($id, $amount, $admin_note, $is_paid);

            //更新会员余额数量
            log_account_change($account['user_id'], $amount, 0, 0, 0, $_LANG['surplus_type_0'], ACT_SAVING);

        }
        elseif ($is_paid == '0')
        {
            /* 否则更新信息 */
            $sql = "UPDATE " .$yp->table('user_account'). " SET ".
                   "admin_user    = '$_SESSION[admin_name]', ".
                   "admin_note    = '$admin_note', ".
                   "is_paid       = 0 WHERE id = '$id'";
            $db->query($sql);
        }
		elseif ($is_paid == '2')// add by prince 20161126 start
        {
            /* 更新信息 */
            $sql = "UPDATE " .$yp->table('user_account'). " SET ".
                   "admin_user    = '$_SESSION[admin_name]', ".
                   "admin_note    = '$admin_note', ".
                   "is_paid       = 2 WHERE id = '$id'";
            $db->query($sql);
			//如果是提现，则解冻
			if($account['process_type'] == '1'){
				$return_amount = $GLOBALS['db']->getOne("SELECT amount FROM " .$GLOBALS['yp']->table('user_account'). " WHERE id = '$id' ");
				$r_amount   = str_replace('-', '', $return_amount);
				$info = date('Y-m-d H:i:s')."取消余额提现，解除资金冻结".$r_amount."元";
				log_account_change($account['user_id'], $r_amount, $return_amount, 0, 0, $info);//还原用户金额
			}
        }// add by prince 20161126 end

        /* 记录管理员日志 */
        admin_log('(' . addslashes($_LANG['check']) . ')' . $admin_note, 'edit', 'user_surplus');

        /* 提示信息 */
        $link[0]['text'] = $_LANG['back_list'];
        $link[0]['href'] = 'user_account.php?act=list&' . list_link_postfix();

        sys_msg($_LANG['attradd_succed'], 0, $link);
    }
}

/*------------------------------------------------------ */
//-- ajax帐户信息列表
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $list = account_list();
    $smarty->assign('list',         $list['list']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('user_account_list.htm'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
/*------------------------------------------------------ */
//-- ajax删除一条信息
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
    
    
	if($_SESSION['admin_name']=='test'){
		make_json_error('对不起，您的权限不足，请勿修改！');
	}
	/* 检查权限 */
    check_authz_json('surplus_manage');
    $id = @intval($_REQUEST['id']);
    $sql = "SELECT u.user_name FROM " . $yp->table('users') . " AS u, " .
           $yp->table('user_account') . " AS ua " .
           " WHERE u.user_id = ua.user_id AND ua.id = '$id' ";
    $user_name = $db->getOne($sql);
	
	//20161126 q q 12 00 29 121  start 
    $account = $db->getRow("SELECT * FROM " .$yp->table('user_account'). " WHERE id = '$id'");
	if($account['is_paid'] == 0 && $account['process_type'] == '1'){
		$return_amount = $GLOBALS['db']->getOne("SELECT amount FROM " .$GLOBALS['yp']->table('user_account'). " WHERE id = '$id' ");
		$r_amount   = str_replace('-', '', $return_amount);
		$info = date('Y-m-d H:i:s')."取消余额提现，解除资金冻结".$r_amount."元";
		log_account_change($account['user_id'], $r_amount, $return_amount, 0, 0, $info);//还原用户金额
	}
	//20161126 q q 12 00 29 121  end 
		
    $sql = "DELETE FROM " . $yp->table('user_account') . " WHERE id = '$id'";
    if ($db->query($sql, 'SILENT'))
    {
       admin_log(addslashes($user_name), 'remove', 'user_surplus');
       $url = 'user_account.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
       yp_header("Location: $url\n");
       exit;
    }
    else
    {
        make_json_error($db->error());
    }
}

/*------------------------------------------------------ */
//-- 会员余额函数部分
/*------------------------------------------------------ */
/**
 * 统计会员账户
 * @access  public
 * @param   int     $user_id        会员ID
 * @return  int
 */
function get_user_surplus($user_id)
{
    $sql = "SELECT SUM(user_money) as user_money ,SUM(frozen_money) as frozen_money,SUM(rank_points) as rank_points,SUM(pay_points) as pay_points FROM " .$GLOBALS['yp']->table('account_log').
           " WHERE user_id = '$user_id'";

    return $GLOBALS['db']->getRow($sql);
}




/**
 * 更新会员账目明细
 *
 * @access  public
 * @param   array     $id          帐目ID
 * @param   array     $admin_note  管理员描述
 * @param   array     $amount      操作的金额
 * @param   array     $is_paid     是否已完成
 *
 * @return  int
 */
function update_user_account($id, $amount, $admin_note, $is_paid)
{
    $sql = "UPDATE " .$GLOBALS['yp']->table('user_account'). " SET ".
           "admin_user  = '$_SESSION[admin_name]', ".
           "amount      = '$amount', ".
           "paid_time   = '".gmtime()."', ".
           "admin_note  = '$admin_note', ".
           "is_paid     = '$is_paid' WHERE id = '$id'";
    return $GLOBALS['db']->query($sql);
}

/**
 *
 *
 * @access  public
 * @param
 *
 * @return void
 */
function account_list()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 过滤列表 */
        $filter['user_id'] = !empty($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)
        {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }

        $filter['process_type'] = isset($_REQUEST['process_type']) ? intval($_REQUEST['process_type']) : -1;
        $filter['payment'] = empty($_REQUEST['payment']) ? '' : trim($_REQUEST['payment']);
        $filter['is_paid'] = isset($_REQUEST['is_paid']) ? intval($_REQUEST['is_paid']) : -1;
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['start_date'] = empty($_REQUEST['start_date']) ? '' : local_strtotime($_REQUEST['start_date']);
        $filter['end_date'] = empty($_REQUEST['end_date']) ? '' : (local_strtotime($_REQUEST['end_date']) + 86400);

        $where = " WHERE 1 ";
        if ($filter['user_id'] > 0)
        {
            $where .= " AND ua.user_id = '$filter[user_id]' ";
        }
        if ($filter['process_type'] != -1)
        {
            $where .= " AND ua.process_type = '$filter[process_type]' ";
        }
        else
        {
            $where .= " AND ua.process_type " . db_create_in(array(SURPLUS_SAVE, SURPLUS_RETURN));
        }
        if ($filter['payment'])
        {
            $where .= " AND ua.payment = '$filter[payment]' ";
        }
        if ($filter['is_paid'] != -1)
        {
            $where .= " AND ua.is_paid = '$filter[is_paid]' ";
        }

        if ($filter['keywords'])
        {
            $where .= " AND u.user_name LIKE '%" . mysql_like_quote($filter['keywords']) . "%'";
            $sql = "SELECT COUNT(*) FROM " .$GLOBALS['yp']->table('user_account'). " AS ua, ".
                   $GLOBALS['yp']->table('users') . " AS u " . $where;
        }
        /*　时间过滤　*/
        if (!empty($filter['start_date']) && !empty($filter['end_date']))
        {
            $where .= "AND paid_time >= " . $filter['start_date']. " AND paid_time < '" . $filter['end_date'] . "'";
        }

        // 代码修改   By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 Start
//        $sql = "SELECT COUNT(*) FROM " .$GLOBALS['yp']->table('user_account'). " AS ua, ".
//                   $GLOBALS['yp']->table('users') . " AS u " . $where;
        $sql = "SELECT COUNT(*) FROM " .$GLOBALS['yp']->table('user_account'). " AS ua LEFT JOIN ".
            $GLOBALS['yp']->table('users') . " AS u ON ua.user_id = u.user_id " . $where;
        // 代码修改   By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 End
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        /* 分页大小 */
        $filter = page_and_size($filter);

        /* 查询数据 */
        $sql  = 'SELECT ua.*, u.user_name,u.supplier_id FROM ' .
            $GLOBALS['yp']->table('user_account'). ' AS ua LEFT JOIN ' .
            $GLOBALS['yp']->table('users'). ' AS u ON ua.user_id = u.user_id'.
            $where . "ORDER by " . $filter['sort_by'] . " " .$filter['sort_order']. " LIMIT ".$filter['start'].", ".$filter['page_size'];

        $filter['keywords'] = stripslashes($filter['keywords']);
        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $list = $GLOBALS['db']->getAll($sql);
    foreach ($list AS $key => $value)
    {
        $list[$key]['surplus_amount']       = price_format(abs($value['amount']), false);
        $list[$key]['add_date']             = local_date($GLOBALS['_CFG']['time_format'], $value['add_time']);
        $list[$key]['process_type_name']    = $GLOBALS['_LANG']['surplus_type_' . $value['process_type']];
       if($list[$key]['supplier_id']){
        $sql = "select supplier_name from " . $GLOBALS['yp']->table('supplier') . "WHERE supplier_id = ".$list[$key]['supplier_id'];
        $list[$key]['supplier_name'] = $GLOBALS['db']->getOne($sql);//会员归属商家
      }else{

        $list[$key]['supplier_name']= '平台自有';

      }
     }
    $arr = array('list' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}
/**
 *
 *
 * 
 *
 * @话费充值   20160825  寒冰  		QQ 3094  85552
 */
function account_huafei_list()
{
    $result = get_filter();
     if ($result === false)
    {
        /* 过滤列表 */
        $filter['user_id'] = !empty($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)
        {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }

        $filter['process_type'] = isset($_REQUEST['process_type']) ? intval($_REQUEST['process_type']) : 3;
        $filter['payment'] = empty($_REQUEST['payment']) ? '' : trim($_REQUEST['payment']);
        $filter['is_paid'] = isset($_REQUEST['is_paid']) ? intval($_REQUEST['is_paid']) : -1;
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['start_date'] = empty($_REQUEST['start_date']) ? '' : local_strtotime($_REQUEST['start_date']);
        $filter['end_date'] = empty($_REQUEST['end_date']) ? '' : (local_strtotime($_REQUEST['end_date']) + 86400);

        $where = " WHERE 1 ";
        if ($filter['user_id'] > 0)
        {
            $where .= " AND ua.user_id = '$filter[user_id]' ";
        }
        if ($filter['process_type'] != -1)
        {
            $where .= " AND ua.process_type = '$filter[process_type]' ";
        }
        else
        {
            $where .= " AND ua.process_type " . db_create_in(array(SURPLUS_SAVE, SURPLUS_RETURN));
        }
        if ($filter['payment'])
        {
            $where .= " AND ua.payment = '$filter[payment]' ";
        }
        if ($filter['is_paid'] != -1)
        {
            $where .= " AND ua.is_paid = '$filter[is_paid]' ";
        }

        if ($filter['keywords'])
        {
            $where .= " AND u.user_name LIKE '%" . mysql_like_quote($filter['keywords']) . "%'";
            $sql = "SELECT COUNT(*) FROM " .$GLOBALS['yp']->table('user_account'). " AS ua, ".
                   $GLOBALS['yp']->table('users') . " AS u " . $where;
        }
        /*　时间过滤　*/
        if (!empty($filter['start_date']) && !empty($filter['end_date']))
        {
            $where .= "AND paid_time >= " . $filter['start_date']. " AND paid_time < '" . $filter['end_date'] . "'";
        }

        // 代码修改   By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 Start
//        $sql = "SELECT COUNT(*) FROM " .$GLOBALS['yp']->table('user_account'). " AS ua, ".
//                   $GLOBALS['yp']->table('users') . " AS u " . $where;
        $sql = "SELECT COUNT(*) FROM " .$GLOBALS['yp']->table('user_account'). " AS ua LEFT JOIN ".
            $GLOBALS['yp']->table('users') . " AS u ON ua.user_id = u.user_id " . $where;
        // 代码修改   By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 End
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        /* 分页大小 */
        $filter = page_and_size($filter);

        /* 查询数据 */
        $sql  = 'SELECT ua.*, u.user_name FROM ' .
            $GLOBALS['yp']->table('user_account'). ' AS ua LEFT JOIN ' .
            $GLOBALS['yp']->table('users'). ' AS u ON ua.user_id = u.user_id'.
            $where . "ORDER by " . $filter['sort_by'] . " " .$filter['sort_order']. " LIMIT ".$filter['start'].", ".$filter['page_size'];

        $filter['keywords'] = stripslashes($filter['keywords']);
        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $list = $GLOBALS['db']->getAll($sql);
    foreach ($list AS $key => $value)
    {
        $list[$key]['surplus_amount']       = price_format(abs($value['amount']), false);
        $list[$key]['add_date']             = local_date($GLOBALS['_CFG']['time_format'], $value['add_time']);
        $list[$key]['process_type_name']    = $GLOBALS['_LANG']['surplus_type_' . $value['process_type']];
     }
    $arr = array('list' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}
?>