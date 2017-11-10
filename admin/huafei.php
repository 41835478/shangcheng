<?php

/**
 * QQ120029121 会员帐目管理(包括预付款，余额)
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: PRINCE $
 * $Id: huafei.php 17217 2017-04-01 06:29:08Z PRINCE $
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
    //admin_priv('huafei');

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
	if (isset($_REQUEST['recharge_status']))
    {
        $smarty->assign('recharge_status_' . intval($_REQUEST['recharge_status']), 'selected="selected"');
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
    $smarty->display('huafei_log.htm');
}
/*------------------------------------------------------ */
//-- 话费充值设置  寒冰  qq  3094  85552  20160825
/*------------------------- ----------------------------- */
if ($_REQUEST['act'] == 'setting')
{
    /* 权限判断 */
    //admin_priv('surplus_manage');

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
   
   
			

			$appid 		= $_POST ['appid'];
			$appkey 	= $_POST ['appkey'];
			$banlance = floatval($_POST ['banlance']);
			$account_zhekou 	= $_POST ['account_zhekou'];
			$account_cycle = intval($_POST ['account_cycle']);
			$account_money 		= $_POST ['account_money'];
			
		   $old_account_money = $db->getOne ( "SELECT account_money FROM " . $GLOBALS['yp']->table('user_account_huafei_config') . " WHERE `id` = 1" );//取得原有设置金额
		   
		   if($old_account_money != $account_money){//设置的金额有变化
		   $time = gmtime();
		   $difference_money = $account_money - $old_account_money;
		   $GLOBALS['db']->query("UPDATE " . $GLOBALS['yp']->table('users') . " SET account_money = account_money + $difference_money   WHERE account_cycle <  $time  ");//充值周期内的老用户  将补足差额
	
		   }
			
			
			$ret = $db->query (
					"UPDATE " . $GLOBALS['yp']->table('user_account_huafei_config') . " SET
					`appid`		='$appid',
					`appkey`		='$appkey',
					`banlance`		='$banlance',
					`account_zhekou`	='$account_zhekou',
					`account_cycle`	='$account_cycle',
					`account_money`	='$account_money'
					WHERE `id`=1;" );
					$link [] = array ('href' => 'huafei.php?act=setting','text' => '手机话费充值设置');
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
			$smarty->assign ( 'banlance', $ret ['banlance'] );
			$smarty->assign ( 'account_zhekou', $ret ['account_zhekou'] );
			$smarty->assign ( 'account_cycle', $ret ['account_cycle'] );
			$smarty->assign ( 'account_money', $ret ['account_money'] );
			
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
    $smarty->display('huafei_setting.html');
}
}


/*------------------------------------------------------ */
//-- ajax帐户信息列表
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $list = account_huafei_list();
    $smarty->assign('list',         $list['list']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('huafei_log.htm'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
/*------------------------------------------------------ */
//-- ajax删除一条信息
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
    /* 检查权限 */
    //check_authz_json('surplus_manage');
	
	
    $id = @intval($_REQUEST['id']);
    $sql = "SELECT u.user_name FROM " . $yp->table('users') . " AS u, " .
           $yp->table('user_account') . " AS ua " .
           " WHERE u.user_id = ua.user_id AND ua.id = '$id' ";
    $user_name = $db->getOne($sql);
	
    $sql = "DELETE FROM " . $yp->table('user_account') . " WHERE id = '$id'";
    if ($db->query($sql, 'SILENT'))
    {
     //  admin_log(addslashes($user_name), 'remove', 'user_surplus');
       $url = 'huafei.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
       yp_header("Location: $url\n");
       exit;
    }
    else
    {
        make_json_error($db->error());
    }
}
elseif ($_REQUEST['act'] == 'recharge')
{
    /* 检查权限 */
    //check_authz_json('surplus_manage');
    $id = intval($_REQUEST['id']);
	$sql = "SELECT * FROM " . $GLOBALS['yp']->table('user_account') .
           " WHERE id = '$id'";
    $arr = $GLOBALS['db']->getRow($sql);
	if ($arr['is_paid'] == 0){
		  $link [] = array ('href' => 'huafei.php?act=list','text' => '充值记录');
		  sys_msg ( '未付款！！', 0, $link );
    }	
	
    //这里加入充值api
    include_once(ROOT_PATH. 'mobile/api/class.juhe.recharge.php');
				 	$appkey = $GLOBALS['db']->getOne("SELECT appkey FROM " . $GLOBALS['yp']->table('user_account_huafei_config') . " limit 1 ");
					$openid = $GLOBALS['db']->getOne("SELECT appid FROM " . $GLOBALS['yp']->table('user_account_huafei_config') . " limit 1 ");
					$recharge = new recharge($appkey,$openid);

					$orderid =local_date("Ymd", $arr['paid_time']).$arr['id']; //自己定义一个订单号，需要保证唯一
					$telRechargeRes = $recharge->telcz($arr['mobile_phone'],intval($arr['amount']),$orderid); #可以选择的面额5、10、20、30、50、100、300
					if($telRechargeRes['error_code'] =='0'){//充值成功
						$sql = 'UPDATE ' . $GLOBALS['yp']->table('user_account') .
							   " SET recharge_status= 1" .
							   " WHERE id = '$arr[id]' LIMIT 1";
						$GLOBALS['db']->query($sql);
						$sql = 'UPDATE ' . $GLOBALS['yp']->table('user_account_huafei_config') .
							   " SET banlance= banlance-'$arr[chongzhifei]' " .
							   " LIMIT 1";
						$GLOBALS['db']->query($sql);
						  $link [] = array ('href' => 'huafei.php?act=list','text' => '充值记录');
						  sys_msg ( '充值成功！！', 0, $link );
					}else{//充值失败
						$sql = 'UPDATE ' . $GLOBALS['yp']->table('user_account') .
							   " SET recharge_status= 2, resmsg='$telRechargeRes[reason]' " .
							   " WHERE id = '$arr[id]' LIMIT 1";
						$GLOBALS['db']->query($sql);
						 $link [] = array ('href' => 'huafei.php?act=list','text' => '充值记录');
						 sys_msg ( '充值失败！！', 0, $link );
					}
	//$link [] = array ('href' => 'huafei.php?act=list','text' => '充值记录');
	//sys_msg ( '充值失败！！', 0, $link );
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
        $filter['recharge_status'] = isset($_REQUEST['recharge_status']) ? intval($_REQUEST['recharge_status']) : -1;
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
         if ($filter['recharge_status'] != -1)
        {
            $where .= " AND ua.recharge_status = '$filter[recharge_status]' ";
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