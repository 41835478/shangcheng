<?php

/**
 * 订单众筹页面
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: prince $
 * $Id: order_raise.php 17217 2017-01-10 06:29:08Z prince $
 */
define('IN_PRINCE', true);

require (dirname(__FILE__) . '/includes/init.php');
require(dirname(__FILE__) . '/includes/lib_v_user.php');
/* 载入语言文件 */
require_once (ROOT_PATH . 'languages/' . $_CFG['lang'] . '/user.php');


if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') == false )
{
		show_message("请在微信中使用此功能", '返回主页', 'index.php', 'info');
}

$act = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : '';

//全局变量
$user = $GLOBALS['user'];
$_CFG = $GLOBALS['_CFG'];
$_LANG = $GLOBALS['_LANG'];
$smarty = $GLOBALS['smarty'];
$user_id = $_SESSION['user_id'];
$db = $GLOBALS['db'];
$yp = $GLOBALS['yp'];
$weburl = $_SERVER['SERVER_NAME'] ? HTTP_TYPE."://".$_SERVER['SERVER_NAME']."/" : HTTP_TYPE."://".$_SERVER['HTTP_HOST']."/";
/*===================订单众筹支付=====================*/

$order_id = isset($_REQUEST['order_id']) ? trim($_REQUEST['order_id']) : 0;
$order_sn =  $GLOBALS['db']->getOne("SELECT order_sn FROM " . $GLOBALS['yp']->table('order_info')." where order_id = '$order_id'");
$key = ($order_id+$order_sn)*2;//支付密钥
$smarty->assign('key',               $key);
if($_GET['act']=='reward'){
	$reward_money = $_POST['reward_money']?$_POST['reward_money']*100:100;
	$or_amount =  $GLOBALS['db']->getOne("SELECT order_amount FROM " . $GLOBALS['yp']->table('order_info')." where order_id = '$order_id'");
  if($reward_money >= $or_amount*100){
	$reward_money = $or_amount*100;//如果用户输入金额大于应付金额则收取应付金额
	}
	$reward_meg = $_POST['reward_meg'];
	$anonymous_reward = '0';//是否匿名
	$timeStamp = time();
	$order_sn = $timeStamp.mt_rand(100000, 999999); 
	$wxuser =  $GLOBALS['db']->getRow("SELECT * FROM " . $GLOBALS['yp']->table('users')." where user_id=".$_SESSION["user_id"]);
	$openid =  $wxuser['fake_id'];
	$ecuid =  $wxuser['user_id'];
	$rewardtimeymd = date('Y-m-d H:i:s');
	$wxname = $wxuser['nickname'];
	$headimg = $wxuser['headimg'];
	$re_money = $reward_money/100;
	$title = '订单号：'.$order_sn.'众筹支付';
	$delsql="delete from " . $GLOBALS['yp']->table('order_raise_log')." where `ecuid`='$ecuid' and  `status` = 0 ";
	$GLOBALS['db'] -> query($delsql);//删除用户未支付订单
	$sql = "insert into " . $GLOBALS['yp']->table('order_raise_log')." values (0,'".$order_id."','".$ecuid."','".$order_sn."','".$re_money."','".$reward_meg."','".$anonymous_reward."','".$timeStamp."','".$rewardtimeymd."','".$wxname."','".$headimg."',0)";
	$GLOBALS['db'] -> query($sql);//插入打赏日志订单
	include_once(dirname(__FILE__) . '/wxzf/weixin_pay.php');//引入微信支付
	echo $jsApiParameters;
    exit;
	
}else{
    $reward_money ='100';
	include_once(dirname(__FILE__) . '/wxzf/weixin_pay.php');//引入微信支付
}

/*=====================支付成功回调==========================*/
if($_GET['act']=='payok'){
  $dokey = trim($_REQUEST['key'])?trim($_REQUEST['key']):trim($_GET['key']) ;
  $orderid = $dokey/2-$order_sn;//验证密钥正确性
  if($dokey == $key && $orderid == $order_id){
  $raise_id = $db -> getOne("SELECT `raise_id` FROM " . $GLOBALS['yp']->table('order_raise_log')." WHERE `ecuid`='$user_id' and `order_id` = '$orderid' and `status` = 0 ");
  $reward_money = $db -> getOne("SELECT `reward_money` FROM " . $GLOBALS['yp']->table('order_raise_log')." WHERE `ecuid`='$user_id' and `order_id` = '$orderid' and `status` = 0 ");
  $order2_amount =  $GLOBALS['db']->getOne("SELECT order_amount2 FROM " . $GLOBALS['yp']->table('order_info')." where order_id = '$order_id'");
  $order_amount =  $GLOBALS['db']->getOne("SELECT order_amount FROM " . $GLOBALS['yp']->table('order_info')." where order_id = '$order_id'");
  if($order2_amount == '0.00'){
  $db->query("UPDATE ".$GLOBALS['yp']->table('order_info') . " SET `order_amount2`= '$order_amount' ,`is_order_raise`='1' WHERE `order_id`= '$order_id'");
  }//标记订单为 众筹订单
  $db->query("UPDATE " . $GLOBALS['yp']->table('order_raise_log')." SET `status`='1' WHERE `raise_id`= '$raise_id'");//更改记录支付状态
  $db->query("UPDATE ".$GLOBALS['yp']->table('order_info') . " SET `order_amount`= order_amount-$reward_money ,`money_paid`= money_paid+$reward_money WHERE `order_id`= '$order_id'");//减少订单应付金额,
  $db->query("UPDATE " . $GLOBALS['yp']->table('pay_log')." SET `order_amount`= order_amount-$reward_money WHERE `order_id`= '$order_id'");//减少支付记录应付款
  $time = time();
  $contents = "亲爱的：\r\n     您的众筹订单（订单号：".$order_sn.")刚刚收到了一笔支持\r\n\r\n";
  $contents .= "支持金额：".$reward_money."\r\n";
  $contents .= "支持时间：".date("Y-m-d H:i:s", $time)."\r\n\r\n";
  $contents .= "还需支付：￥".$order_amount - $reward_money."元\r\n\r\n";
  $contents .= "<a href={$weburl}mobile/order_raise.php?order_id=".$order_id.">点击这里查看详情</a>";
  $sql = "select user_id from ".$GLOBALS['yp']->table('order_info')." where `order_id`= '$order_id'";
  $user_id = $GLOBALS['db']->getOne($sql);
  $sql = "select fake_id from ".$GLOBALS['yp']->table('users')." where user_id='$user_id'";
  $fake_id = $GLOBALS['db']->getOne($sql);
  $ret = ToUserMsg($fake_id,'text',array('text'=>$contents));
  if ($ret) {
	 @file_get_contents($weburl."/mobile/weixin/auto_do.php");
	}
  echo "<script>alert('多谢支持！土豪，我们下辈子还做朋友可好？！');window.location.href=\"order_raise.php?order_id=".$order_id."\";</script>";
  exit; 
   }else{
   echo "<script>alert('非法操作！！');window.location.href=\"order_raise.php?order_id=".$order_id."\";</script>";
   exit;
   }
}	

/* 订单众筹详情页 */
if (!$smarty->is_cached('order_raise.dwt'))
{
    assign_template();
    $order_id = isset($_REQUEST['order_id']) ? trim($_REQUEST['order_id']) : 0;
	$order_uid = $db->getOne("select user_id from ".$yp->table('order_info') ." where order_id = $order_id");
    include_once (ROOT_PATH . 'includes/lib_transaction.php');
	//获取订单用户信息
	$user_info = get_user_info_by_user_id($order_uid); //用户信息，包括昵称和头像
	$smarty->assign('user_info',$user_info);
	$smarty->assign('info',get_user_info($order_uid));
	
	if($order_uid == $_SESSION['user_id'])//定义导航菜单样式
	{
	$smarty->assign('menu',1);
	}

	/* 订单详情 */
	$order = get_order_detail_raise($order_id);
	

	if($order === false)
	{
		$GLOBALS['err']->show($_LANG['back_home_lnk'], './');
		
		exit();
	}
	
	if($order['order_amount'] == '0.00'){//订单应付金额为零
	  if($order['pay_status'] == '0'){
	 $time = time();
     $order2_amount =  $GLOBALS['db']->getOne("SELECT order_amount2 FROM " . $GLOBALS['yp']->table('order_info')." where order_id = '$order_id'");//取出订单总价
     $db->query("UPDATE ".$GLOBALS['yp']->table('order_info') . " SET `money_paid`= '$order_amount2' ,`pay_status`='2' ,`order_status`= '1' ,`confirm_time`= '$time' ,`pay_time`= '$time' WHERE `order_id`= '$order_id'");
	 $db->query("UPDATE " . $GLOBALS['yp']->table('pay_log')."  SET `is_paid`= '1' WHERE `order_id`= '$order_id'");//修改已付金额与 订单支付状态为已付款
	 //show_message("订单已完成支付，无需众筹", '查看订单详情', 'user.php?act=order_detail&order_id='.$order_id, 'info');
      $contents = "亲爱的：\r\n     您的众筹订单（订单号：".$order_sn.")众筹成功\r\n\r\n";
      $contents .= "<a href={$weburl}mobile/order_raise.php?order_id=".$order_id.">点击这里查看详情</a>";
      $sql = "select user_id from ".$GLOBALS['yp']->table('order_info')." where `order_id`= '$order_id'";
      $user_id = $GLOBALS['db']->getOne($sql);
      $sql = "select fake_id from ".$GLOBALS['yp']->table('users')." where user_id='$user_id'";
      $fake_id = $GLOBALS['db']->getOne($sql);
      $ret = ToUserMsg($fake_id,'text',array('text'=>$contents));
      if ($ret) {
	    @file_get_contents($weburl."/mobile/weixin/auto_do.php");
	   }
	 }
	 $smarty->assign('order_amount', '1');
    }
	/* 订单商品 */
	$goods_list = order_goods($order_id);
	foreach($goods_list as $key => $value)
	{
		$goods_list[$key]['market_price'] = price_format($value['market_price'], false);
		$goods_list[$key]['goods_price'] = price_format($value['goods_price'], false);
		$goods_list[$key]['subtotal'] = price_format($value['subtotal'], false);
	}
	
	$smarty->assign('order', $order);
	$smarty->assign('goods_list', $goods_list);
    /*统计已筹金额*/
     $money_pay = $db -> getOne("SELECT sum(reward_money) FROM " . $GLOBALS['yp']->table('order_raise_log')." WHERE order_id = '$order_id' and `status` = 1 ");
     $smarty->assign('money_pay', $money_pay ? $money_pay : '0.00');
	/*统计订单打赏次数*/
    // $total = mysql_fetch_array(mysql_query("select count(*) from " . $GLOBALS['yp']->table('order_raise_log')."  where order_id = '$order_id' and `status` = 1 "));
    $total = mysqli_fetch_array(mysqli_query("select count(*) from " . $GLOBALS['yp']->table('order_raise_log')."  where order_id = '$order_id' and `status` = 1 "));
    $Total = $total[0];
    $smarty->assign('Total',    $Total);
	
	/*查询单次打赏金额最多*/
	 $shafa = $db -> getRow("SELECT * FROM " . $GLOBALS['yp']->table('order_raise_log')." WHERE order_id = '$order_id' and `status` = 1  and `anonymous_reward` = 0 order by `reward_money` desc limit 1 ");
	$smarty->assign('shafa',    $shafa);
	$shafaid = $shafa['raise_id'];
	/*查询出其他打赏*/
	  $bandeng = array(); 
	  $sql = "select * from " . $GLOBALS['yp']->table('order_raise_log')." where `order_id` = '$order_id' and `status` = 1  and `raise_id` != '$shafaid'  and `anonymous_reward` = 0 order by `raise_id` limit 0,6";//查询出所需要的条数   

          $res = $GLOBALS['db']->query($sql);
          while ($rew = $GLOBALS['db']->fetchRow($res)){   
        $bandeng[] = $rew;
           }

          $smarty->assign('bandeng',    $bandeng); 

    $position = assign_ur_here(0, $_LANG['user_center']);
	$smarty->assign('page_title', $position['title']); // 页面标题
	$smarty->assign('ur_here', $position['ur_here']);
	$smarty->assign('jsApiParameters',    $jsApiParameters);
	$smarty->assign('helps', get_shop_help()); // 网店帮助
	$smarty->assign('data_dir', DATA_DIR); // 数据目录
	$smarty->assign('lang', $_LANG);
	$smarty->assign('order_id', $order_id);
	$smarty->display('order_raise.dwt', $cache_id);
}

function get_order_detail_raise($order_id)
{
    include_once(ROOT_PATH . 'includes/lib_order.php');
	 if ($order_id <= 0)
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['invalid_order_id']);

        return false;
    }
	
    $order = order_info($order_id);

    return $order;

}
  
// 今天优品多商户系统 Added by PRINCE QQ 120029121  2016年7月18日
function ToUserMsg($fake_id,$type="text",$msg=array(),$sendtime=0){//将消息推送至微信
	$user = $GLOBALS['db']->getRow("select * from " . $GLOBALS['yp']->table('users') . " where fake_id='{$fake_id}'");
	if($user && $user['fake_id'] && $user['isfollow'] == 1){
		if($type == 'text'){
			$content = array(
					'touser'=>$user['fake_id'],
					'msgtype'=>'text',
					'text'=>array('content'=>$msg['text'])
			);
		}
		if($type == 'news'){
			$content = array(
					'touser'=>$user['fake_id'],
					'msgtype'=>'news',
					'news'=>array('articles'=>$msg)
			);
		}
		$content = serialize($content);
		$sendtime = $sendtime ? $sendtime : time();
		$createtime = time();
		$sql = "insert into " . $GLOBALS['yp']->table('weixin_corn') . " (`ecuid`,`content`,`createtime`,`sendtime`,`issend`)
		value ({$user['user_id']},'{$content}','{$createtime}','{$sendtime}','0')";
		$GLOBALS['db']->query($sql);
		return true;
	}else{
		$GLOBALS['err']->add("用户未关注");
		return false;
	}
}
?>
