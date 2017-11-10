<?php

/**
 * QQ120029121 定期删除
 * ===========================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ==========================================================
 * $Author: PRINCE $
 * $Id: pay_rem.php 17217 2017-04-01 06:29:08Z PRINCE $
 */

if (!defined('IN_PRINCE'))
{
    die('Hacking attempt');
}
$cron_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/cron/pay_rem_qq120029121.php';
if (file_exists($cron_lang))
{
    global $_LANG;

    include_once($cron_lang);
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc']    = 'pay_rem_desc';

    /* 作者 */
    $modules[$i]['author']  = '今-天-优-品-研-发-团-队';

    /* 网址 */
    $modules[$i]['website'] = 'http://demo.coolhong.com';

    /* 版本号 */
    $modules[$i]['version'] = '1.0.0';

    /* 配置信息 */
    $modules[$i]['config']  = array(
        array('name' => 'pay_rem_time', 'type' => 'select', 'value' => '6'),
		array('name' => 'pay_rem_sms', 'type' => 'select', 'value' => '1'),
		array('name' => 'pay_rem_email', 'type' => 'select', 'value' => '1'),
		array('name' => 'pay_rem_weixin', 'type' => 'select', 'value' => '1'),
    );

    return;
}

$cron['pay_rem_time'] = !empty($cron['pay_rem_time'])  ?  $cron['pay_rem_time'] : 1 ;
$cron['pay_rem_sms'] = !empty($cron['pay_rem_sms'])  ?  $cron['pay_rem_sms'] : 0 ;
$cron['pay_rem_email'] = !empty($cron['pay_rem_email'])  ?  $cron['pay_rem_email'] : 0 ;
$cron['pay_rem_weixin'] = !empty($cron['pay_rem_weixin'])  ?  $cron['pay_rem_weixin'] : 0 ;

$time = gmtime();
$rem_time = $time - $cron['pay_rem_time'] * 3600 ;//提醒时间  QQ   3094   8555   2
$sql = "SELECT * FROM " . $GLOBALS['yp']->table('order_info') . " WHERE pay_status ='0' AND order_status < '2' AND pay_id != '6' AND add_time < '$rem_time'";
$res_orders = $db->getAll($sql);


foreach ($res_orders as $key =>$val)
{

    $orders_user = $GLOBALS['db']->getRow("select * from ".$GLOBALS['yp']->table('users')." where user_id = $val[user_id]");//取得下单人信息

  

    if($cron['pay_rem_sms']){//如需短信提醒 QQ   3094   8555   2
 
                      include_once(ROOT_PATH. 'sms/sms.php');
					  
					  $user_mobile = !empty($orders_user['mobile_phone'])  ? $orders_user['mobile_phone'] : $val['mobile'] ;
					  $pay_url = $yp->url() . 'mobile/user.php?act=order_detail&order_id=' . $val['order_id'];
                     
				       //短信模板样式：亲爱的%s：您于%s在商城购买的商品，订单号：%s，请您及时付款。如有疑问，请联系商城客服。【%s】
					   $content = sprintf($GLOBALS['_CFG']['pay_rem_sms_qq309485552_tpl'],$orders_user['user_name'],local_date($_CFG['time_format'], $val['add_time']),$val['order_sn'],$_CFG['sms_sign']);
					   $templateParam=Array("name"=>$orders_user['user_name'],"order_sn"=>$val['order_sn']);
					   $templateCode=$GLOBALS['_CFG']['sms_register_dayu'];
					   sendSMS($user_mobile,$content,'', '',$templateParam , $templateCode);//已支持阿里大于 QQ 120029121
  
    }



    if($cron['pay_rem_email']){//如需邮件提醒 QQ   3094   8555   2
	
            $order_email = !empty($val['email'])  ?  $val['email'] : $orders_user['email'] ;
            $tpl = get_mail_template('pay_rem_qq309485552_email');
	        $smarty->assign('order_sn', $val['order_sn']);
			$smarty->assign('user_name', $orders_user['user_name']);
	        $smarty->assign('add_time', local_date($_CFG['time_format'], $val['add_time']));
	        $smarty->assign('shop_name', $_CFG['shop_name']);
	        $smarty->assign('send_date', date($_CFG['time_format']));
			$smarty->assign('send_msg_url',$yp->url() . 'user.php?act=order_detail&order_id=' . $val['order_id']);
	        $content = $smarty->fetch('str:' . $tpl['template_content']);
	        send_mail($_CFG['shop_name'], $order_email, $tpl['template_subject'], $content, $tpl['is_html']); 


    }




     if($cron['pay_rem_weixin']){//如需微信提 醒  热风  科技 开发QQ: 1 20029121    30 9485552
        if($orders_user['aite_id']){
        $wxid = str_replace('weixin_','',$orders_user['aite_id']);
       $wap_url_sql = "SELECT `wap_url` FROM ".$GLOBALS['yp']->table('weixin_config')." WHERE `id`=1";
       $wap_url = $db -> getOne($wap_url_sql);
       @file_get_contents($wap_url."weixin/weixin_remind.php?notice=6&is_one_user=".$wxid."&order_id=".$val['order_id']);
 
     }
  
   }




}

 

?>