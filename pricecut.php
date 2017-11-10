<?php

/**
 * QQ120029121 降价通知
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: PRINCE $
 * $Id: pricecut.php 17217 2017-04-01 06:29:08Z PRINCE $
*/

define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');

$goods_id = $_POST['goods_id'] ? intval($_POST['goods_id']) : 0;
$price = $_POST['price'] ? $_POST['price'] : 0;
$mobile = $_POST['mobile'] ? $_POST['mobile'] : '';
$email = $_POST['email'] ? $_POST['email'] : '';
$nowtime= gmtime();

$sql = "insert into ".$yp->table('pricecut')." (goods_id, price, mobile, email, add_time) values('$goods_id', '$price', '$mobile', '$email', '$nowtime')";
$db->query($sql);

$goods_url =  build_uri('goods', array('gid'=>$goods_id), '');
show_message('恭喜，您的降价通知已经成功提交！', '返回上一页', $goods_url);

?>
