<?php

/**
 * QQ120029121 供货商等级管理程序
 * ============================================================================
 * 演示地址: http://demo.coolhong.com；
 * ============================================================================
 * $Author: 今天优品 $
 * $Id: user_rank.php 17217 2017-04-01 06:29:08Z qq120029121 $
*/

define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');

require(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/supplier.php');
$smarty->assign('lang', $_LANG);

$exc = new exchange($yp->table("supplier_rank"), $db, 'rank_id', 'rank_name');
$exc_user = new exchange($yp->table("supplier"), $db, 'user_rank', 'user_rank');

/*------------------------------------------------------ */
//--套餐管理
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'list')
{
    $ranks = array();
    $ranks = $db->getAll("SELECT * FROM " .$yp->table('supplier_rank')." order by sort_order ");

    $smarty->assign('ur_here',      $_LANG['supplier_rank_list']);
    $smarty->assign('full_page',    1);
   
    $max_rank = $db->getRow("SELECT * FROM " .$yp->table('supplier_rank')." ORDER BY price DESC limit 1");//取出最高套餐等级
 
    $smarty->assign('user_ranks',   $ranks);
    $supplier_id = $_SESSION['supplier_id']; 
   $sql = "SELECT s.*,sr.*".
            "FROM " . $GLOBALS['yp']->table("supplier") . " as s left join " . $GLOBALS['yp']->table("supplier_rank") . " as sr on s.rank_id = sr.rank_id
                WHERE s.supplier_id = '$supplier_id' ";
    $supplier = $db->getRow($sql);
     if ($supplier['end_time'] < time()) {
         $is_overdue = '1';
    }
    $supplier['start_time'] = date("Y-m-d H:i:s",$supplier['start_time']);
    $supplier['end_time'] = date("Y-m-d H:i:s",$supplier['end_time']);

    
    if ($supplier['rank_id'] == $max_rank['rank_id']) {
        $is_max_rank = '1';
    }

	$kfqq=$db->GetOne('SELECT value  FROM ' .$yp->table('shop_config') . " WHERE  code='qq' and  parent_id=1");
    if(!empty($kfqq)){
        $qqinfo = explode(',',$kfqq);
        $smarty->assign('kfqq', $qqinfo);
    }
    $smarty->assign('is_max_rank',  $is_max_rank  ? $is_max_rank : 0 );
    $smarty->assign('is_overdue',  $is_overdue  ? $is_overdue : 0 );
    $smarty->assign('supplier',   $supplier);

    assign_query_info();
    $smarty->display('supplier_rank.htm');
}

/*------------------------------------------------------ */
//-- 翻页，排序
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $ranks = array();
    $ranks = $db->getAll("SELECT * FROM " .$yp->table('supplier_rank')." order by sort_order ");

    $smarty->assign('user_ranks',   $ranks);
    make_json_result($smarty->fetch('supplier_rank.htm'));
}



?>