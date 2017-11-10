<?php

/**
 * 分成核心执行文件
 * ===========================================================
 * 演示地址: http://demo.coolhong.com
 * ==========================================================
 * $Author: prince $
 * $Id: lib_fencheng.php 17217 2017-04-01 06:29:08Z prince $
 */

define('IN_PRINCE', true);

function do_fencheng($order_id,$supplier_id)
{
	//var_dump($order_id);
    $supplier_id = intval($supplier_id);
 $sql = "SELECT value FROM " . $GLOBALS['yp']->table('supplier_shop_config') . " WHERE code = 'affiliate' AND supplier_id = '$supplier_id'";
    $config = $GLOBALS['db']->getOne($sql);
    $affiliate = unserialize($config);
    empty($affiliate) && $affiliate = array();
	$separate_by = $affiliate['config']['separate_by'];
    $oid = intval($order_id);
	//获取订单分成金额
	$split_money = get_split_money_by_orderid($oid);

    $row = $GLOBALS['db']->getRow("SELECT o.order_sn,u.parent_id, o.is_separate, o.froms,(o.goods_amount - o.discount) AS goods_amount, o.user_id FROM " . $GLOBALS['yp']->table('order_info') . " o"." LEFT JOIN " . $GLOBALS['yp']->table('users') . " u ON o.user_id = u.user_id"." WHERE order_id = '$oid'");


    $order_sn = $row['order_sn'];
	$change_desc='订单'.$order_sn.'分成';
    $num = count($affiliate['item']);


	if($row['is_separate'] == '0'){//寒冰  20161118  增加   避免订单重复分成

		for ($i=0; $i < $num; $i++)
		{
			$affiliate['item'][$i]['level_money'] = (float)$affiliate['item'][$i]['level_money'];
			if($affiliate['config']['level_money_all']==100 )//所有比例之和
			{
				$setmoney = $split_money;
			}
			else 
			{
				if ($affiliate['item'][$i]['level_money'])
				{
					$affiliate['item'][$i]['level_money'] /= 100;
				}
				$setmoney = round($split_money * $affiliate['item'][$i]['level_money'], 2);
			}
			$row = $GLOBALS['db']->getRow("SELECT o.parent_id as user_id,u.user_name FROM " . $GLOBALS['yp']->table('users') . " o" .
							" LEFT JOIN" . $GLOBALS['yp']->table('users') . " u ON o.parent_id = u.user_id".
							" WHERE o.user_id = '$row[user_id]'");
			$up_uid = $row['user_id'];
			if ($up_uid && $row['user_name']){
				$info = sprintf($_LANG['separate_info'], $order_sn, $setmoney, 0);
				
				if($setmoney>0){
					push_user_msg($up_uid,$order_sn,$setmoney);
				}
				$point=round($setmoney);
                
				write_affiliate_log($oid, $up_uid, $row['user_name'], $setmoney, $separate_by,$point,$change_desc);
			}

		}
		  
		//个人购买增加分成
		$separate_personal = $affiliate['config']['ex_fenxiao_personal'];
		$personal_lever_money = $affiliate['config']['personal_lever_money'];
		$level_register_up = (float)$affiliate['config']['level_register_up'];
		if ($separate_personal > 0){
			$personal_data = $GLOBALS['db']->getRow("SELECT o.user_id,u.user_name,u.rank_points,u.is_fenxiao FROM " . $GLOBALS['yp']->table('order_info') . " o".
							" LEFT JOIN " . $GLOBALS['yp']->table('users') . " u ON o.user_id = u.user_id".
							" WHERE order_id = '$oid'");
			$personal_pay_money = $GLOBALS['db']->getOne("SELECT sum(goods_amount) FROM " . $GLOBALS['yp']->table('order_info')." where user_id = ".$personal_data['user_id']);
			//消费金额小于设置的最少消费金额时，个人分成 0
			if ($personal_pay_money < $personal_lever_money){
				$affiliate['config']['level_money_personal'] = 0;
				$affiliate['config']['level_point_personal'] = 0;
			}
			if($personal_data['is_fenxiao'] == 1){
				$personalMoney = round($split_money * $affiliate['config']['level_money_personal']*0.01, 2);
				$personalPoint = round($split_money * $affiliate['config']['level_point_personal']*0.01, 0);
				$info = sprintf($_LANG['separate_info'], $order_sn, $personalMoney, $personalPoint);
				if($personalMoney>0){
					push_user_msg($personal_data['user_id'],$order_sn,$personalMoney);
				}
				$change_desc='本人'.$change_desc;
				write_affiliate_log($oid, $personal_data['user_id'] , $personal_data['user_name'], $personalMoney,  $separate_by,$personalPoint,$change_desc);
			}

					   
		}
		
		$sql = "UPDATE " . $GLOBALS['yp']->table('order_info') .
				   " SET is_separate = 1,split_money='$split_money' WHERE order_id = '$oid'";
		$GLOBALS['db']->query($sql); 
		
		if ($row['froms'] == 's_xcx') {//来自商家小程序  分成时要扣除佣金
		  $sql = "SELECT user_id FROM " . $GLOBALS['yp']->table('supplier') . " WHERE supplier_id = '$supplier_id'";
          $supplier_user_id = $GLOBALS['db']->getOne($sql); 
        //echo $supplier_user_id;
		  $change_desc = "订单:".$order_sn."分成（商家自主收款，扣除平台提成与分销佣金）";
          log_account_change($supplier_user_id, '-'.$split_money, 0, 0, 0, $change_desc);
		}	
		$wap_url_sql = "SELECT `wap_url` FROM " . $GLOBALS['yp']->table('weixin_config') . " WHERE `id`=1";
	    $wap_url =  $GLOBALS['db'] -> getOne($wap_url_sql);
		$var = trim($wap_url);
		$len = strlen($var)-1;
		$lastword=$var{$len};
		if($lastword=='/'){
			$wap_url= substr($wap_url,0,-1);
		}
		@file_get_contents($wap_url."/weixin/auto_do.php?type=1&is_affiliate=1");
	}
}

function write_affiliate_log($oid, $uid, $username, $money, $separate_by,$point,$change_desc)
{
    $time = gmtime();
    $sql = "INSERT INTO " . $GLOBALS['yp']->table('affiliate_log') . "( order_id, user_id, user_name, time, money, separate_type,point,change_desc)".
     " VALUES ( '$oid', '$uid', '$username', '$time', '$money', '$separate_by','$point','$change_desc')";
    if ($oid){
        $GLOBALS['db']->query($sql);
    }
	log_account_change($uid, 0, 0, $point, 0, $change_desc); 
}


//获取某一个订单的分成金额
function get_split_money_by_orderid($order_id)
{
	$supplier_id = $_SESSION['supplier_id'];
    $sql = "SELECT value FROM " . $GLOBALS['yp']->table('supplier_shop_config') . " WHERE code = 'distrib_type' AND supplier_id = '$supplier_id'";

    $distrib_type = $GLOBALS['db']->getOne($sql);
	 if($distrib_type == 0){ //按订单分成
		 $total_fee = " (goods_amount - discount + tax + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee) AS total_money";
		 $sql = "SELECT " . $total_fee . " FROM " . $GLOBALS['yp']->table('order_info') . " WHERE order_id = '$order_id'";
		 $total_fee = $GLOBALS['db']->getOne($sql);
		 $sql = 'SELECT value FROM ' . $GLOBALS['yp']->table('ypmart_shop_config')." WHERE code = 'distrib_percent'";
         $distrib_percent = $GLOBALS['db']->getOne($sql);
		 $split_money = $total_fee*($distrib_percent/100);
	 }
	 else{//按商品分成
	 	$sql = "SELECT sum(split_money*goods_number) FROM " . $GLOBALS['yp']->table('order_goods') . " WHERE order_id = '$order_id'";
	 	$split_money = $GLOBALS['db']->getOne($sql);
	 }
	 if($split_money > 0){
		 return $split_money; 
	 }else{
		 return 0; 
	 }
}

//分成后，推送到各个上级分销商微信
function push_user_msg($user_id,$order_sn,$split_money){
	$type = 1;
	$text = "订单".$order_sn."分成，您得到的佣金为".$split_money;
	$user = $GLOBALS['db']->getRow("select * from " . $GLOBALS['yp']->table('users') . " where user_id='{$user_id}'");
	if($user && $user['fake_id']){
		$content = array(
			'touser'=>$user['fake_id'],
			'msgtype'=>'text',
			'text'=>array('content'=>$text)
		);
		$content = serialize($content);
		$sendtime = $sendtime ? $sendtime : time();
		$createtime = time();
		$sql = "insert into ".$GLOBALS['yp']->table('weixin_corn').     	
		"(`ecuid`,`content`,`createtime`,`sendtime`,`issend`,`sendtype`) 
		value ({$user_id},'{$content}','{$createtime}','{$sendtime}','0',{$type})";
		$GLOBALS['db']->query($sql);
		return true;
	}else{
		return false;
	}
}

//根据订单号获取分成日志信息
function get_all_affiliate_log($order_id)
{
	$sql = "SELECT * FROM " . $GLOBALS['yp']->table('affiliate_log') . " WHERE order_id = '$order_id' order by log_id desc";
	$list = $GLOBALS['db']->getAll($sql);
	$arr = array();
	$str = '';
	foreach($list as $val)
	{
		 $str .= sprintf($GLOBALS['_LANG']['separate_info2'], $val['user_id'], $val['user_name'], $val['money'], $val['point'])."<br />";
		 $arr['log_id'] = $val['log_id'];
		 $arr['separate_type'] = $val['separate_type'];
		 if($arr['separate_type']< 0){//已被撤销
            $str  = "<s>撤销" . $str . "</s>";
        }
	}
	$arr['info'] = $str;
	return $arr;
}

?>