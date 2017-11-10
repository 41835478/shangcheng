<?php

/**
 * QQ120029121 分成管理
 * ===========================================================
 * 演示地址: http://demo.coolhong.com；
 * ==========================================================
 * $Author: prince $
 * $Id: affiliate_ck.php 17217 2017-04-01 06:29:08Z prince $
 */

define('IN_PRINCE', true);
require(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH .ADMIN_PATH. '/includes/lib_fencheng.php');
admin_priv('affiliate_ck');
$sql = "SELECT s.*,sr.*".
				"FROM " . $GLOBALS['yp']->table("supplier") . " as s left join " . $GLOBALS['yp']->table("supplier_rank") . " as sr on s.rank_id = sr.rank_id
					WHERE s.supplier_id = ".$_SESSION['supplier_id'];
$supp = $db->getRow($sql);
if ($supp['fenxiao_status'] == 0 ) {
	echo "<script>alert('您当前的【".$supp['rank_name']."】套餐无法使用分销功能，请升级套餐');window.location.href=\"supplier_rank.php?act=list\";</script>";
	exit;
}

$timestamp = time();
$supplier_id = $_SESSION['supplier_id'];

$sql = "SELECT value FROM " . $GLOBALS['yp']->table('supplier_shop_config') . " WHERE code = 'affiliate' AND supplier_id = '$supplier_id'";

    $config = $GLOBALS['db']->getOne($sql);
     $affiliate = unserialize($config);
empty($affiliate) && $affiliate = array();
$separate_on = $affiliate['on'];

/*------------------------------------------------------ */
//-- 分成页
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    $_GET[auid] = intval($_GET[auid]); 
	$logdb = get_affiliate_ck();
    $smarty->assign('full_page',  1);
    $smarty->assign('ur_here', $_LANG['affiliate_ck']);
    $smarty->assign('on', $separate_on);
    $smarty->assign('logdb',        $logdb['logdb']);
    $smarty->assign('filter',       $logdb['filter']);
    $smarty->assign('record_count', $logdb['record_count']);
    $smarty->assign('page_count',   $logdb['page_count']);

	$smarty->assign('supplier_list',get_supplier_list());
    assign_query_info();
    $smarty->display('affiliate_ck_list.htm');
}
/*------------------------------------------------------ */
//-- 分页
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
     $_GET[auid] = intval($_GET[auid]); $logdb = get_affiliate_ck() ;
    $smarty->assign('logdb',        $logdb['logdb']);
    $smarty->assign('on', $separate_on);
    $smarty->assign('filter',       $logdb['filter']);
    $smarty->assign('record_count', $logdb['record_count']);
    $smarty->assign('page_count',   $logdb['page_count']);

    $sort_flag  = sort_flag($logdb['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('affiliate_ck_list.htm'), '', array('filter' => $logdb['filter'], 'page_count' => $logdb['page_count']));
}
/*
    取消分成，不再能对该订单进行分成
*/
elseif ($_REQUEST['act'] == 'del')
{
    $oid =  intval($_REQUEST['oid']);
      $order_sn = $db->getOne("SELECT order_sn FROM " . $GLOBALS['yp']->table('order_info') . " WHERE order_id = '$oid' AND supplier_id = '$supplier_id'");

    if(empty($order_sn)){
    
    $links[] = array('text' => $_LANG['affiliate_ck'], 'href' => 'affiliate_ck.php?act=list');
	sys_msg('非法操作！', 1 ,$links);

   }
    $stat = $db->getOne("SELECT is_separate FROM " . $GLOBALS['yp']->table('order_info') . " WHERE order_id = '$oid'");
    if (empty($stat))
    {
        $sql = "UPDATE " . $GLOBALS['yp']->table('order_info') .
               " SET is_separate = 2" .
               " WHERE order_id = '$oid'";
        $db->query($sql);
    }
    $links[] = array('text' => $_LANG['affiliate_ck'], 'href' => 'affiliate_ck.php?act=list');
    sys_msg($_LANG['edit_ok'], 0 ,$links);
}
/*
    撤销某次分成，将已分成的收回来
*/
elseif ($_REQUEST['act'] == 'rollback')
{
    $order_id = intval($_REQUEST['order_id']);
     $order_sn = $db->getOne("SELECT order_sn FROM " . $GLOBALS['yp']->table('order_info') . " WHERE order_id = '$order_id' AND supplier_id = '$supplier_id'");

    if(empty($order_sn)){
    
    $links[] = array('text' => $_LANG['affiliate_ck'], 'href' => 'affiliate_ck.php?act=list');
	sys_msg('非法操作！', 1 ,$links);

   }
    $loginfo = $db->getAll("SELECT * FROM " . $GLOBALS['yp']->table('affiliate_log') . " WHERE order_id = '$order_id' and separate_type>=0 ");
	
    $order_sn = $db->getOne("SELECT order_sn FROM " . $GLOBALS['yp']->table('order_info') . " WHERE order_id = '$order_id'");
	
	foreach($loginfo as $log){
		$logid=$log['log_id'];
        $stat = $db->getRow("SELECT * FROM " . $GLOBALS['yp']->table('affiliate_log') . " WHERE log_id = '$logid'");
		
        if($stat['separate_type'] == 1) {//推荐订单分成??
            $flag = -2;
        }else{//推荐注册分成   $stat['separate_type'] == 0
            $flag = -1;
        }

		//撤销分成，记录日志
		$change_desc='订单'.$order_sn.'分成撤销';
		write_affiliate_log($stat['order_id'], $stat['user_id'], $stat['user_name'], -$stat['money'], $flag,-$stat['point'],$change_desc);
		
	}
	
	$sql = "UPDATE " . $GLOBALS['yp']->table('order_info') . " SET is_separate = 3 WHERE order_id = '" . $order_id . "'";
	$db->query($sql);
    $links[] = array('text' => $_LANG['affiliate_ck'], 'href' => 'affiliate_ck.php?act=list');
    sys_msg($_LANG['edit_ok'], 0 ,$links);
}
/*
    分成
*/
elseif ($_REQUEST['act'] == 'separate')
{
    include_once(ROOT_PATH . 'includes/lib_order.php');
    $oid = intval($_REQUEST['oid']);
    $order_sn = $db->getOne("SELECT order_sn FROM " . $GLOBALS['yp']->table('order_info') . " WHERE order_id = '$oid' AND supplier_id = '$supplier_id'");

    if(empty($order_sn)){
    
    $links[] = array('text' => $_LANG['affiliate_ck'], 'href' => 'affiliate_ck.php?act=list');
	sys_msg('非法操作！', 1 ,$links);

   }
    $sql = "SELECT value FROM " . $GLOBALS['yp']->table('supplier_shop_config') . " WHERE code = 'affiliate' AND supplier_id = '$supplier_id'";

    $config = $GLOBALS['db']->getOne($sql);
     $affiliate = unserialize($config);
   
    empty($affiliate) && $affiliate = array();
	$separate_by = $affiliate['config']['separate_by'];
    
	do_fencheng($oid,$supplier_id);
	$links[] = array('text' => $_LANG['affiliate_ck'], 'href' => 'affiliate_ck.php?act=list');
	sys_msg($_LANG['edit_ok'], 0 ,$links);
}
function get_affiliate_ck()
{
	$supplier_id = $_SESSION['supplier_id'];
    $sql = "SELECT value FROM " . $GLOBALS['yp']->table('supplier_shop_config') . " WHERE code = 'affiliate' AND supplier_id = '$supplier_id'";

    $config = $GLOBALS['db']->getOne($sql);
     $affiliate = unserialize($config);
   
    empty($affiliate) && $affiliate = array();
    $separate_by = $affiliate['config']['separate_by'];

    $sqladd = "AND o.supplier_id = '$supplier_id'";

    if (isset($_REQUEST['order_sn']))
    {
        $sqladd .= ' AND o.order_sn LIKE \'%' . trim($_REQUEST['order_sn']) . '%\'';
        $filter['order_sn'] = $_REQUEST['order_sn'];
    }
    

   
	

	$sql = "SELECT value FROM " . $GLOBALS['yp']->table('supplier_shop_config') . " WHERE code = 'distrib_type' AND supplier_id = '$supplier_id'";

    $distrib_type = $GLOBALS['db']->getOne($sql);
	
	if($distrib_type == 0)
	{
		//按订单分成
		$sql = "SELECT COUNT(*) FROM ".$GLOBALS['yp']->table('order_info')." AS o," . $GLOBALS['yp']->table('users') . " as u WHERE o.pay_status = 2 and o.is_separate=0 AND o.user_id = u.user_id $sqladd ";

	}
	else
	{
		//按商品分成
		$sql = "select count(*) from ".
		"(select o.order_id,o.user_id,o.add_time,o.order_status,o.pay_status,o.shipping_status,".
		"sum(b.split_money*goods_number) as total_money,u.user_name,o.is_separate ".
		"from " . $GLOBALS['yp']->table('order_info') . " as o ," . 
		$GLOBALS['yp']->table('order_goods') . " as b," . 
		$GLOBALS['yp']->table('users') . 
		" as u where o.pay_status = 2 and o.is_separate=0  and o.order_id = b.order_id ".
		"and o.user_id = u.user_id $sqladd group by o.order_id ) as ab" ;
		//" where total_money > 0";

	}
	





	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
    $logdb = array();
    /* 分页大小 */
    $filter = page_and_size($filter);
	
	if($distrib_type == 0)
	{
		$sql = "SELECT order_sn,is_separate,order_id,o.user_id,add_time,order_status,o.pay_status,o.shipping_status,u.user_name FROM ".$GLOBALS['yp']->table('order_info')." AS o," . $GLOBALS['yp']->table('users') . " as u WHERE o.pay_status = 2 and o.is_separate=0  AND o.user_id = u.user_id $sqladd ORDER BY order_id DESC LIMIT " . $filter['start'] . ",$filter[page_size]";
	}
	else
	{
		
		$sql = "select order_sn,is_separate,order_id,user_id,add_time,
		order_status,pay_status,shipping_status,user_name from " .
		"(select o.order_id,o.order_sn,o.user_id,o.add_time,o.order_status,o.pay_status,o.shipping_status,".
		"sum(b.split_money*goods_number) as total_money," .
		"o.is_separate,u.user_name from " . 
		$GLOBALS['yp']->table('order_info') . " as o ," . 
		$GLOBALS['yp']->table('order_goods') . " as b," . 
		$GLOBALS['yp']->table('users') . " as u ".
		" where o.pay_status = 2 and o.is_separate=0 and o.order_id = b.order_id " . 
		"and o.user_id = u.user_id $sqladd group by o.order_id ) as ab " . 
		//" where total_money > 0  ORDER BY order_id DESC" . 
		" ORDER BY order_id DESC" . 
		" LIMIT " . $filter['start'] . ",$filter[page_size]";
	}
	
	$query = $GLOBALS['db']->query($sql);
	
    while ($rt = $GLOBALS['db']->fetch_array($query))
    {
    	
		$info = get_all_affiliate_log($rt['order_id']);
		$rt['add_time'] = local_date("Y-m-d",$rt['add_time']);
		$rt['info'] = $info['info'];
		$rt['log_id'] = $info['log_id'];
       
		//$rt['supplier'] = get_supplier($rt['supplier_id']);
		$rt['split_money'] = get_split_money_by_orderid($rt['order_id']);
		$logdb[] = $rt;
	}
	
    $arr = array('logdb' => $logdb, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);


    return $arr;
}


//获取供货商名称
function get_supplier($supplier_id)
{
	$sql = "SELECT supplier_name FROM " . $GLOBALS['yp']->table('supplier') . " WHERE supplier_id = '$supplier_id'";
	return $GLOBALS['db']->getOne($sql); 
}

//获取供货商列表
function get_supplier_list()
{
    $sql = 'SELECT supplier_id,supplier_name 
            FROM ' . $GLOBALS['yp']->table('supplier') . '
            WHERE status=1 
            ORDER BY supplier_name ASC';
    $res = $GLOBALS['db']->getAll($sql);

    if (!is_array($res))
    {
        $res = array();
    }

    return $res;
}




?>