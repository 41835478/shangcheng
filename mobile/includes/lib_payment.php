<?php

/**
 * QQ120029121 支付接口函数库
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: prince $
 * $Id: lib_payment.php 17218 2011-01-24 04:10:41Z prince $
 */

if (!defined('IN_PRINCE'))
{
    die('Hacking attempt');
}

/**
 * 取得返回信息地址
 * @param   string  $code   支付方式代码
 */
function return_url($code)
{
    return $GLOBALS['yp']->url() . 'respond.php?code=' . $code;
}

/**
 *  取得某支付方式信息
 *  @param  string  $code   支付方式代码
 */
function get_payment($code)
{
    $sql = 'SELECT * FROM ' . $GLOBALS['yp']->table('payment').
           " WHERE pay_code = '$code' AND enabled = '1'";
    $payment = $GLOBALS['db']->getRow($sql);

    if ($payment)
    {
        $config_list = unserialize($payment['pay_config']);

        foreach ($config_list AS $config)
        {
            $payment[$config['name']] = $config['value'];
        }
    }

    return $payment;
}

/**
 *  通过订单sn取得订单ID
 *  @param  string  $order_sn   订单sn
 *  @param  blob    $voucher    是否为会员充值
 */
function get_order_id_by_sn($order_sn, $voucher = 'false')
{
    if ($voucher == 'true')
    {
        if(is_numeric($order_sn))
        {
              return $GLOBALS['db']->getOne("SELECT log_id FROM " . $GLOBALS['yp']->table('pay_log') . " WHERE order_id=" . $order_sn . ' AND order_type=1');
        }
        else
        {
            return "";
        }
    }
    else
    {
        if(is_numeric($order_sn))
        {
            $sql = 'SELECT order_id FROM ' . $GLOBALS['yp']->table('order_info'). " WHERE order_sn = '$order_sn'";
            $order_id = $GLOBALS['db']->getOne($sql);
        }
        if (!empty($order_id))
        {
            $pay_log_id = $GLOBALS['db']->getOne("SELECT log_id FROM " . $GLOBALS['yp']->table('pay_log') . " WHERE order_id='" . $order_id . "'");
            return $pay_log_id;
        }
        else
        {
            return "";
        }
    }
}

/**
 *  通过订单ID取得订单商品名称
 *  @param  string  $order_id   订单ID
 */
function get_goods_name_by_id($order_id)
{
    $sql = 'SELECT goods_name FROM ' . $GLOBALS['yp']->table('order_goods'). " WHERE order_id = '$order_id'";
    $goods_name = $GLOBALS['db']->getCol($sql);
    return implode(',', $goods_name);
}

/**
 * 检查支付的金额是否与订单相符
 *
 * @access  public
 * @param   string   $log_id      支付编号
 * @param   float    $money       支付接口返回的金额
 * @return  true
 */
function check_money($log_id, $money)
{
    if(is_numeric($log_id))
    {
        $sql = 'SELECT order_amount FROM ' . $GLOBALS['yp']->table('pay_log') .
              " WHERE log_id = '$log_id'";
        $amount = $GLOBALS['db']->getOne($sql);
    }
    else
    {
        return false;
    }
    if ($money == $amount)
    {
        return true;
    }
    else
    {
        return false;
    }
}

/**
 * 修改订单的支付状态
 *
 * @access  public
 * @param   string  $log_id     支付编号
 * @param   integer $pay_status 状态
 * @param   string  $note       备注
 * @return  void
 */
function order_paid($log_id, $pay_status = PS_PAYED, $note = '',$out_trade_no='',$transaction_id= '')
{
    /* 取得支付编号 */
    $log_id = intval($log_id);
    if ($log_id > 0)
    {
        /* 取得要修改的支付记录信息 */
        $sql = "SELECT * FROM " . $GLOBALS['yp']->table('pay_log') .
                " WHERE log_id = '$log_id'";
        $pay_log = $GLOBALS['db']->getRow($sql);
        if ($pay_log && $pay_log['is_paid'] == 0)
        {
            /* 修改此次支付操作的状态为已付款 */
            $sql = 'UPDATE ' . $GLOBALS['yp']->table('pay_log') .
                    " SET is_paid = '1' WHERE log_id = '$log_id'";
            $GLOBALS['db']->query($sql);

            /* 根据记录类型做相应处理 */
            if ($pay_log['order_type'] == PAY_ORDER)
            {
                /* 取得订单信息 */
                $sql = 'SELECT order_id, user_id,supplier_id, order_sn, consignee, address, tel, shipping_id, extension_code, extension_id, goods_amount ' .
                        'FROM ' . $GLOBALS['yp']->table('order_info') .
                       " WHERE order_id = '$pay_log[order_id]' OR parent_order_id = '$pay_log[order_id]' ";
                $orderinfo    = $GLOBALS['db']->getAll($sql);
		foreach($orderinfo as $key => $order)
		{
	                $order_id = $order['order_id'];
	                $order_sn = $order['order_sn'];
			$suppid = $order['supplier_id'];
			$supplier[$suppid]	 = $order_sn;
	
	                /* 修改订单状态为已付款 */
	                $sql = 'UPDATE ' . $GLOBALS['yp']->table('order_info') .
	                            " SET order_status = '" . OS_CONFIRMED . "', " .
	                                " confirm_time = '" . gmtime() . "', " .
	                                " pay_status = '$pay_status', " .
	                                " out_trade_no = '$out_trade_no', " .    //PRINCE 120029121
	                                " transaction_id = '$transaction_id', " . //PRINCE 120029121
	                                " pay_time = '".gmtime()."', " .
	                                " money_paid = order_amount," .
	                                " order_amount = 0 ".
	                       "WHERE order_id = '$order_id'";
	                $GLOBALS['db']->query($sql);
	
	                /* 记录订单操作记录 */
	                order_action($order_sn, OS_CONFIRMED, SS_UNSHIPPED, $pay_status, $note, $GLOBALS['_LANG']['buyer']);
					//微信消息 PRINCE 120029121
					if($order['extension_code']=='extpintuan'){
						include_once(ROOT_PATH.'wxm_extpintuan.php');
						send_order_message($order_id);
					}
	
	               /* 如果需要，发短信 */  
	               //include_once('send.php');
					include_once(ROOT_PATH. 'sms/sms.php');
					send_sms($supplier,$GLOBALS['_CFG']['sms_order_payed_tpl'],2);
				   //付款给客户发短信
				   if($GLOBALS['_CFG']['sms_order_pay'] == 1)
				   {
					   $content = sprintf($GLOBALS['_CFG']['sms_order_pay_tpl'],$order_sn,$GLOBALS['_CFG']['sms_sign']);
					   $templateParam=Array("code"=>$order_sn);
					   $templateCode=$GLOBALS['_CFG']['sms_order_pay_dayu'];
					   sendSMS($mobile,$content,'', '',$templateParam , $templateCode);//已支持阿里大于 QQ 120029121 
				   }
				  
				    $wap_url_sql = "SELECT `wap_url` FROM ".$GLOBALS['yp']->table('weixin_config')." WHERE `id`=1";
	
					$wap_url =  $GLOBALS['db'] -> getOne($wap_url_sql);//手机端网址
					
					
					@file_get_contents($wap_url."weixin/weixin_remind.php?notice=2&is_one_user=".$order['user_id']."&order_id=".$order['order_id']);
				  
	                /* 对虚拟商品的支持 */
	                $virtual_goods = get_virtual_goods($order_id);
	                if (!empty($virtual_goods))
	                {
	                    $msg = '';
	                    if (!virtual_goods_ship($virtual_goods, $msg, $order_sn, true))
	                    {
	                        $GLOBALS['_LANG']['pay_success'] .= '<div style="color:red;">'.$msg.'</div>'.$GLOBALS['_LANG']['virtual_goods_ship_fail'];
	                    }
	
	                    /* 如果订单没有配送方式，自动完成发货操作 */
	                    if ($order['shipping_id'] == -1)
	                    {
	                        /* 将订单标识为已发货状态，并记录发货记录 */
	                        $sql = 'UPDATE ' . $GLOBALS['yp']->table('order_info') .
	                               " SET shipping_status = '" . SS_SHIPPED . "', shipping_time = '" . gmtime() . "'" .
	                               " WHERE order_id = '$order_id'";
	                        $GLOBALS['db']->query($sql);
	
	                         /* 记录订单操作记录 */
	                        order_action($order_sn, OS_CONFIRMED, SS_SHIPPED, $pay_status, $note, $GLOBALS['_LANG']['buyer']);
	                        $integral = integral_to_give($order);
	                        log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($GLOBALS['_LANG']['order_gift_integral'], $order['order_sn']));
	                    }
	                }
               }

            }
            elseif ($pay_log['order_type'] == PAY_SURPLUS)
            {
                $sql = 'SELECT `id` FROM ' . $GLOBALS['yp']->table('user_account') .  " WHERE `id` = '$pay_log[order_id]' AND `is_paid` = 1  LIMIT 1";
                $res_id=$GLOBALS['db']->getOne($sql);
                if(empty($res_id))
                {
                    /* 更新会员预付款的到款状态 */
                    $sql = 'UPDATE ' . $GLOBALS['yp']->table('user_account') .
                           " SET paid_time = '" .gmtime(). "', is_paid = 1" .
                           " WHERE id = '$pay_log[order_id]' LIMIT 1";
                    $GLOBALS['db']->query($sql);

                    /* 取得添加预付款的用户以及金额 */
                    $sql = "SELECT user_id, amount FROM " . $GLOBALS['yp']->table('user_account') .
                            " WHERE id = '$pay_log[order_id]'";
                    $arr = $GLOBALS['db']->getRow($sql);

                    /* 修改会员帐户金额 */
                    $_LANG = array();
                    include_once(ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/user.php');
                    log_account_change($arr['user_id'], $arr['amount'], 0, 0, 0, $_LANG['surplus_type_0'], ACT_SAVING);
                }
            }
			
			 elseif ($pay_log['order_type'] == 3)//话费充值 start
            {
                $sql = 'SELECT `id` FROM ' . $GLOBALS['yp']->table('user_account') .  " WHERE `id` = '$pay_log[order_id]' AND `is_paid` = 1  LIMIT 1";
                $res_id=$GLOBALS['db']->getOne($sql);
                if(empty($res_id))
                {
                    /* 更新会员预付款的到款状态 */
                    $sql = 'UPDATE ' . $GLOBALS['yp']->table('user_account') .
                           " SET paid_time = '" .gmtime(). "', is_paid = 1" .
                           " WHERE id = '$pay_log[order_id]' LIMIT 1";
                    $GLOBALS['db']->query($sql);

                    /* 取得添加预付款的用户以及金额 */
                    $sql = "SELECT * FROM " . $GLOBALS['yp']->table('user_account') .
                            " WHERE id = '$pay_log[order_id]'";
                    $arr = $GLOBALS['db']->getRow($sql);

                    /* 修改会员帐户金额 */
					$user_id = $arr['user_id'];
					$sql = "SELECT * FROM " . $GLOBALS['yp']->table('users') . " WHERE user_id = '$user_id'";
                    $user_info = $GLOBALS['db']->getRow($sql);//用户信息
					$account_info = $GLOBALS['db']->getRow ( "SELECT * FROM " . $GLOBALS['yp']->table('user_account_huafei_config') . " WHERE `id` = 1" );//充值配置信息
					if($user_info['account_cycle']){ 
					 $account_money = $user_info['account_money'] - $arr['amount'];
					 $GLOBALS['db']->query("UPDATE " . $GLOBALS['yp']->table('users') . " SET  account_money = $account_money  WHERE  user_id = '$user_id'");
					}else{
					$account_cycle = gmtime() + $account_info['account_cycle'] * 24 *3600;//充值过期时间
					$account_money = $user_info['account_money'] - $arr['amount'];
					$GLOBALS['db']->query("UPDATE " . $GLOBALS['yp']->table('users') . " SET account_cycle = $account_cycle , account_money = $account_money  WHERE  user_id = '$user_id'");
			
					}
                   //这里加入充值api
                    include_once(ROOT_PATH. 'api/class.juhe.recharge.php');

				 	$appkey = $GLOBALS['db']->getOne("SELECT appkey FROM " . $GLOBALS['yp']->table('user_account_huafei_config') . " limit 1 ");
					$openid = $GLOBALS['db']->getOne("SELECT appid FROM " . $GLOBALS['yp']->table('user_account_huafei_config') . " limit 1 ");
					$recharge = new recharge($appkey,$openid);

					$orderid =local_date("Ymd", $arr['paid_time']).$pay_log['order_id']; //自己定义一个订单号，需要保证唯一
					$telRechargeRes = $recharge->telcz($arr['mobile_phone'],intval($arr['amount']),$orderid); #可以选择的面额5、10、20、30、50、100、300
					if($telRechargeRes['error_code'] =='0'){//充值成功
						$sql = 'UPDATE ' . $GLOBALS['yp']->table('user_account') .
							   " SET recharge_status= 1" .
							   " WHERE id = '$pay_log[order_id]' LIMIT 1";
						$GLOBALS['db']->query($sql);
						$sql = 'UPDATE ' . $GLOBALS['yp']->table('user_account_huafei_config') .
							   " SET banlance= banlance-'$arr[chongzhifei]' " .
							   " LIMIT 1";
						$GLOBALS['db']->query($sql);
					}else{//充值失败
						$sql = 'UPDATE ' . $GLOBALS['yp']->table('user_account') .
							   " SET recharge_status= 2, resmsg='$telRechargeRes[reason]' " .
							   " WHERE id = '$pay_log[order_id]' LIMIT 1";
						$GLOBALS['db']->query($sql);
					}

				 
                }
            }//话费充值end
     	
        }
        else
        {
            /* 取得已发货的虚拟商品信息 */
            $post_virtual_goods = get_virtual_goods($pay_log['order_id'], true);

            /* 有已发货的虚拟商品 */
            if (!empty($post_virtual_goods))
            {
                $msg = '';
                /* 检查两次刷新时间有无超过12小时 */
                $sql = 'SELECT pay_time, order_sn FROM ' . $GLOBALS['yp']->table('order_info') . " WHERE order_id = '$pay_log[order_id]'";
                $row = $GLOBALS['db']->getRow($sql);
                $intval_time = gmtime() - $row['pay_time'];
                if ($intval_time >= 0 && $intval_time < 3600 * 12)
                {
                    $virtual_card = array();
                    foreach ($post_virtual_goods as $code => $goods_list)
                    {
                        /* 只处理虚拟卡 */
                        if ($code == 'virtual_card')
                        {
                            foreach ($goods_list as $goods)
                            {
                                if ($info = virtual_card_result($row['order_sn'], $goods))
                                {
                                    $virtual_card[] = array('goods_id'=>$goods['goods_id'], 'goods_name'=>$goods['goods_name'], 'info'=>$info);
                                }
                            }

                            $GLOBALS['smarty']->assign('virtual_card',      $virtual_card);
                        }
                    }
                }
                else
                {
                    $msg = '<div>' .  $GLOBALS['_LANG']['please_view_order_detail'] . '</div>';
                }

                $GLOBALS['_LANG']['pay_success'] .= $msg;
            }

           /* 取得未发货虚拟商品 */
           $virtual_goods = get_virtual_goods($pay_log['order_id'], false);
           if (!empty($virtual_goods))
           {
               $GLOBALS['_LANG']['pay_success'] .= '<br />' . $GLOBALS['_LANG']['virtual_goods_ship_fail'];
           }
        }
    }
	
    $sql = 'SELECT value FROM ' . $GLOBALS['yp']->table('ypmart_shop_config')." WHERE code = 'pay_fencheng'";
    $pay_fencheng = $GLOBALS['db']->getOne($sql);
					 
	if ($pay_fencheng == 1){
       include_once(ROOT_PATH . 'includes/lib_fencheng.php');
	   do_fencheng($order_id);
	}	
}

?>