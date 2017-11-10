<?php

/**
 * QQ120029121 定期删除未付款订单
 * ===========================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ==========================================================
 * $Author: PRINCE $
 * $Id: ipdel.php 17217 2017-04-01 06:29:08Z PRINCE $
 */

if (!defined('IN_PRINCE'))
{
    die('Hacking attempt');
}
$cron_lang_qq_wx_120029121 = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/cron/order_del_qq_120029121.php';
if (file_exists($cron_lang_qq_wx_120029121))
{
    global $_LANG;
    include_once($cron_lang_qq_wx_120029121);
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc']    = 'order_del_qq_120029121_desc';

    /* 作者 */
    $modules[$i]['author']  = '今-天-优-品-研-发-团-队';

    /* 网址 */
    $modules[$i]['website'] = 'http://demo.coolhong.com';

    /* 版本号 */
    $modules[$i]['version'] = '1.0.0';

    /* 配置信息 */
    $modules[$i]['config']  = array(
        array('name' => 'order_del_qq_120029121_day', 'type' => 'select', 'value' => '1'),
		array('name' => 'order_del_qq_120029121_action', 'type' => 'select', 'value' => '2'),
    );

    return;
}

$cron['order_del_qq_120029121_day'] = !empty($cron['order_del_qq_120029121_day'])  ?  $cron['order_del_qq_120029121_day'] : 1 ;
$deltime = gmtime() - $cron['order_del_qq_120029121_day'] * 3600 * 24;

$cron['order_del_qq_120029121_action'] = !empty($cron['order_del_qq_120029121_action'])  ?  $cron['order_del_qq_120029121_action'] : 'invalid' ;
//echo $cron['order_del_qq_120029121_action'];

$sql_qq_wx_120029121 = "select order_id FROM " . $yp->table('order_info') .
           " WHERE pay_status ='0' and add_time < '$deltime'";
$res_qq_wx_120029121=$db->query($sql_qq_wx_120029121);

while ($row_qq_wx_120029121=$db->fetchRow($res_qq_wx_120029121))
{
  if ($cron['order_del_qq_120029121_action'] == 'cancel' || $cron['order_del_qq_120029121_action'] == 'invalid')
  {
	  /* 设置订单为取消 */
	  if ($cron['order_del_qq_120029121_action'] == 'cancel')
	  {
	  
		    $order_cancel_qq_wx_120029121 = array('order_status' => OS_CANCELED, 'to_buyer' => '超过一定时间未付款，订单自动取消');
			$GLOBALS['db']->autoExecute($GLOBALS['yp']->table('order_info'),
											$order_cancel_qq_wx_120029121, 'UPDATE', "order_id = '$row_qq_wx_120029121[order_id]' ");
	  }
	  /* 设置订单未无效 */
	  elseif($cron['order_del_qq_120029121_action'] == 'invalid')
	  {
			$order_invalid_qq_wx_120029121 = array('order_status' => OS_INVALID, 'to_buyer' => ' ');
			$GLOBALS['db']->autoExecute($GLOBALS['yp']->table('order_info'),
											$order_invalid_qq_wx_120029121, 'UPDATE', "order_id = '$row_qq_wx_120029121[order_id]' ");
	  }
  }
  elseif ($cron['order_del_qq_120029121_action'] == 'remove')
  {
	  /* 删除订单 */
	  $db->query("DELETE FROM ".$yp->table('order_info'). " WHERE order_id = '$row_qq_wx_120029121[order_id]' ");
	  $db->query("DELETE FROM ".$yp->table('order_goods'). " WHERE order_id = '$row_qq_wx_120029121[order_id]' ");
	  $db->query("DELETE FROM ".$yp->table('order_action'). " WHERE order_id = '$row_qq_wx_120029121[order_id]' ");
	  $action_array = array('delivery', 'back');
	  del_delivery_qq_wx_120029121($row_qq_wx_120029121['order_id'], $action_array);
  }

}
 

function del_delivery_qq_wx_120029121($order_id, $action_array)
{
    $return_res = 0;

    if (empty($order_id) || empty($action_array))
    {
        return $return_res;
    }

    $query_delivery = 1;
    $query_back = 1;
    if (in_array('delivery', $action_array))
    {
        $sql = 'DELETE O, G
                FROM ' . $GLOBALS['yp']->table('delivery_order') . ' AS O, ' . $GLOBALS['yp']->table('delivery_goods') . ' AS G
                WHERE O.order_id = \'' . $order_id . '\'
                AND O.delivery_id = G.delivery_id';
        $query_delivery = $GLOBALS['db']->query($sql, 'SILENT');
    }
    if (in_array('back', $action_array))
    {
        $sql = 'DELETE O, G
                FROM ' . $GLOBALS['yp']->table('back_order') . ' AS O, ' . $GLOBALS['yp']->table('back_goods') . ' AS G
                WHERE O.order_id = \'' . $order_id . '\'
                AND O.back_id = G.back_id';
        $query_back = $GLOBALS['db']->query($sql, 'SILENT');
    }

    if ($query_delivery && $query_back)
    {
        $return_res = 1;
    }

    return $return_res;
}
?>