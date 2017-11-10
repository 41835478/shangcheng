<?php
$order_id = isset($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : 0;
require(dirname(__FILE__) . '/api.class.php');
if(!$_SESSION['user_id']){
	//$uid = 12;
	exit("您还没有绑定会员，请绑定后再来吧！");
}
if($order_id){
 $order_user = $GLOBALS['db']->getOne("SELECT user_id FROM " . $GLOBALS['yp']->table('order_info') . " WHERE `order_id` = '$order_id'");
 if($order_user != $_SESSION['user_id']){
  exit("非法操作！该订单不属于您！！");
 }
 $order_pay = $GLOBALS['db']->getOne("SELECT pay_status FROM " . $GLOBALS['yp']->table('order_info') . " WHERE `order_id` = '$order_id'");
 if($order_pay != '2'){
  exit("非法操作！该订单还未付款！！");
 }
 $order_draw_status = $GLOBALS['db']->getOne("SELECT order_draw_status FROM " . $GLOBALS['yp']->table('order_info') . " WHERE `order_id` = '$order_id'");
  if($order_draw_status){
  exit("该订单已参加过抽奖！！");
  }
  $order_draw_name = '订单抽奖-';
}
$aid = intval($_GET['aid']);
$act = $db->getRow ( "SELECT * FROM " . $GLOBALS['yp']->table('weixin_act') . " WHERE `aid` = $aid and isopen=1" );
if(!$act) exit("活动已经结束");
$actList = (array)$db->getAll ( "SELECT * FROM " . $GLOBALS['yp']->table('weixin_actlist') . " where aid=$aid and isopen=1" );
if(!$actList) exit("活动未设置奖项");
$sql = "SELECT " . $GLOBALS['yp']->table('weixin_actlog') . ".*," . $GLOBALS['yp']->table('users') . ".nickname FROM " . $GLOBALS['yp']->table('weixin_actlog') . " 
		left join " . $GLOBALS['yp']->table('users') . " on " . $GLOBALS['yp']->table('weixin_actlog') . ".uid=" . $GLOBALS['yp']->table('users') . ".user_id 
		where code!='' and aid=$aid order by lid desc";
$award = $db->getAll ( $sql );
$uid = intval($_SESSION['user_id']);
$api = new weixinapi();
$awardNum = intval($api->getAwardNum($aid , $order_id));
require("award_{$act['tpl']}.php");

?>