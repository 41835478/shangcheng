<?php

/**
 * QQ120029121 购物流程
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: prince $
 * $Id: delete_cart_goods.php 15632 2009-02-20 03:58:31Z prince $
 */

define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');

include_once('includes/cls_json.php');


$result = array('error' => 0, 'message' => '', 'content' => '', 'goods_id' => '');
$json  = new JSON;
if($_POST['id'])
{
$sql = 'DELETE FROM '.$GLOBALS['yp']->table('cart')." WHERE rec_id=".$_POST['id'];
$GLOBALS['db']->query($sql);
}
$sql = 'SELECT c.*,IF(c.extension_code = "package_buy",act_name,g.goods_name) as goods_name, '.
		' IF(c.extension_code = "package_buy","package_img",g.goods_thumb) as goods_thumb,g.goods_id,c.goods_number,c.goods_price' .
		' FROM ' . $GLOBALS['yp']->table('cart') ." AS c ".
		" LEFT JOIN ".$GLOBALS['yp']->table('goods')." AS g ON g.goods_id=c.goods_id ".
		" left join ".$GLOBALS['yp']->table('goods_activity')." as pa on pa.act_id=c.goods_id ".
		" WHERE session_id = '" . SESS_ID . "' AND rec_type = '" . CART_GENERAL_GOODS . "'";
$row = $GLOBALS['db']->GetAll($sql);
$arr = array();
foreach($row AS $k=>$v)
{
	
	$arr[$k]['goods_thumb']  = get_image_path($v['goods_id'], $v['goods_thumb'], true);
	$arr[$k]['short_name']   = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
																				 sub_str($v['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $v['goods_name'];
	$arr[$k]['url']          = ($v['extension_code'] == 'package_buy' ? '' : build_uri('goods', array('gid' => $v['goods_id']), $v['goods_name']));
	$arr[$k]['goods_number'] = $v['goods_number'];
	$arr[$k]['goods_name']   = $v['goods_name'];
	$arr[$k]['goods_price']  = price_format($v['goods_price']);
	$arr[$k]['goods_price2']  = $v['goods_price'];
	$arr[$k]['rec_id']       = $v['rec_id'];
}		
$sql = 'SELECT SUM(goods_number) AS number, SUM(goods_price * goods_number) AS amount' .
			 ' FROM ' . $GLOBALS['yp']->table('cart') .
			 " WHERE session_id = '" . SESS_ID . "' AND rec_type = '" . CART_GENERAL_GOODS . "'";
$row = $GLOBALS['db']->GetRow($sql);

if ($row)
{
		$number = intval($row['number']);
		$amount = floatval($row['amount']);
}
else
{
		$number = 0;
		$amount = 0;
}

foreach($arr as $val)
	{
		$zj['goods_number'] += $val['goods_number'];
		$zj['goods_price'] += $val['goods_price2']*$val['goods_number'];
	}

$GLOBALS['smarty']->assign('str',sprintf($GLOBALS['_LANG']['cart_info'], $number, price_format($amount, false)));
$GLOBALS['smarty']->assign('goods',$arr);
$GLOBALS['smarty']->assign('zj',$zj);

$result['content'] = $GLOBALS['smarty']->fetch('library/cart_info.lbi');
		
//$smarty->assign('order',$order);

die($json->encode($result));


?>