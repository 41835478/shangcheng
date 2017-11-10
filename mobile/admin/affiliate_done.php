<?php

/**
 * QQ120029121 分成管理
 * ===========================================================
 * 演示地址: http://demo.coolhong.com；
 * ==========================================================
 * $Author: prince $
 * $Id: affiliate_done.php 17217 2017-04-01 06:29:08Z prince $
 */

define('IN_PRINCE', true);
require(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . 'includes/lib_fencheng.php');
admin_priv('affiliate_ck');
$timestamp = time();

$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
empty($affiliate) && $affiliate = array();
$separate_on = $affiliate['on'];

/*------------------------------------------------------ */
//-- 分成页
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
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
    $smarty->display('affiliate_done_list.htm');
}
/*------------------------------------------------------ */
//-- 分页
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $logdb = get_affiliate_ck();
    $smarty->assign('logdb',        $logdb['logdb']);
    $smarty->assign('on', $separate_on);
    $smarty->assign('filter',       $logdb['filter']);
    $smarty->assign('record_count', $logdb['record_count']);
    $smarty->assign('page_count',   $logdb['page_count']);

    $sort_flag  = sort_flag($logdb['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('affiliate_done_list.htm'), '', array('filter' => $logdb['filter'], 'page_count' => $logdb['page_count']));
}
/*
    取消分成，不再能对该订单进行分成
*/
elseif ($_REQUEST['act'] == 'del')
{
    $oid = (int)$_REQUEST['oid'];
    $stat = $db->getOne("SELECT is_separate FROM " . $GLOBALS['yp']->table('order_info') . " WHERE order_id = '$oid'");
    if (empty($stat))
    {
        $sql = "UPDATE " . $GLOBALS['yp']->table('order_info') .
               " SET is_separate = 2" .
               " WHERE order_id = '$oid'";
        $db->query($sql);
    }
    $links[] = array('text' => $_LANG['affiliate_ck'], 'href' => 'affiliate_done.php?act=list');
    sys_msg($_LANG['edit_ok'], 0 ,$links);
}
/*
    撤销某次分成，将已分成的收回来
*/
elseif ($_REQUEST['act'] == 'rollback')
{
    $order_id = intval($_REQUEST['order_id']);
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
    $links[] = array('text' => $_LANG['affiliate_ck'], 'href' => 'affiliate_done.php?act=list');
    sys_msg($_LANG['edit_ok'], 0 ,$links);
}
/*
    分成
*/
elseif ($_REQUEST['act'] == 'separate')
{
    include_once(ROOT_PATH . 'includes/lib_order.php');
    $affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
    empty($affiliate) && $affiliate = array();
	$separate_by = $affiliate['config']['separate_by'];
    $oid = intval($_REQUEST['oid']);
	do_fencheng($oid);
	$links[] = array('text' => $_LANG['affiliate_ck'], 'href' => 'affiliate_done.php?act=list');
	sys_msg($_LANG['edit_ok'], 0 ,$links);
}
function get_affiliate_ck()
{

    $affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
    empty($affiliate) && $affiliate = array();
    $separate_by = $affiliate['config']['separate_by'];

    $sqladd = '';
    if (isset($_REQUEST['status']))
    {   
	    if((int)$_REQUEST['status']>=1){
			$sqladd .= ' AND o.is_separate = ' . (int)$_REQUEST['status'];
			$filter['status'] = (int)$_REQUEST['status'];
		}
    }
    if (isset($_REQUEST['order_sn']))
    {
        $sqladd .= ' AND o.order_sn LIKE \'%' . trim($_REQUEST['order_sn']) . '%\'';
        $filter['order_sn'] = $_REQUEST['order_sn'];
    }

    if(isset($_REQUEST['supplier_id'])){   
		if($_REQUEST['supplier_id']>=0){
			$sqladd .= ' AND o.supplier_id = ' . $_REQUEST['supplier_id']; 
		}
	}
	

	$sql = "SELECT COUNT(*) FROM ".$GLOBALS['yp']->table('order_info')." AS o," . $GLOBALS['yp']->table('users') . " as u WHERE o.pay_status = 2 and o.is_separate>0 AND o.user_id = u.user_id $sqladd ";

	$filter['record_count'] = $GLOBALS['db']->getOne($sql);
    $logdb = array();
    /* 分页大小 */
    $filter = page_and_size($filter);
	
	$sql = "SELECT order_sn,is_separate,o.split_money,order_id,o.user_id,add_time,order_status,o.pay_status,o.shipping_status,o.supplier_id,u.user_name FROM ".$GLOBALS['yp']->table('order_info')." AS o," . $GLOBALS['yp']->table('users') . " as u WHERE o.pay_status = 2 and o.is_separate>0 AND o.user_id = u.user_id $sqladd ORDER BY order_id DESC LIMIT " . $filter['start'] . ",$filter[page_size]";

	$query = $GLOBALS['db']->query($sql);
    while ($rt = $GLOBALS['db']->fetch_array($query))
    {
		$info = get_all_affiliate_log($rt['order_id']);
		$rt['add_time'] = local_date("Y-m-d",$rt['add_time']);
		$rt['info'] = $info['info'];
		$rt['log_id'] = $info['log_id'];

		$rt['supplier'] = get_supplier($rt['supplier_id']);
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