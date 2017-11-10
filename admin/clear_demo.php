<?php

/**
 * QQ120029121 清除演示数据
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: PRINCE $
 * $Id: goods.php 17217 2017-04-01 06:29:08Z PRINCE $
*/
define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');
admin_priv('clear_demo');// prince 1060626

/*------------------------------------------------------ */
//-- 载入界面
/*------------------------------------------------------ */
if($_REQUEST['act'] == 'start')
{
    $smarty->assign('ur_here', $_LANG['clear_demo']);
    $smarty->display('clear_demo.htm');
}

/*------------------------------------------------------ */
//-- 清除数据
/*------------------------------------------------------ */
elseif($_REQUEST['act'] == 'clear')
{
    $_POST['username'] = isset($_POST['username']) ? trim($_POST['username']) : '';
    $_POST['password'] = isset($_POST['password']) ? trim($_POST['password']) : '';

    $sql="SELECT `ec_salt` FROM ". $yp->table('admin_user') ."WHERE user_name = '" . $_POST['username']."'";
    $ec_salt =$db->getOne($sql);
    if(!empty($ec_salt))
    {
        /* 检查密码是否正确 */
        $sql = "SELECT user_id, user_name, password, last_login, action_list, last_login,suppliers_id,ec_salt".
            " FROM " . $yp->table('admin_user') .
            " WHERE user_name = '" . $_POST['username']. "' AND password = '" . md5(md5($_POST['password']).$ec_salt) . "'";
    }
    else
    {
        /* 检查密码是否正确 */
        $sql = "SELECT user_id, user_name, password, last_login, action_list, last_login,suppliers_id,ec_salt".
            " FROM " . $yp->table('admin_user') .
            " WHERE user_name = '" . $_POST['username']. "' AND password = '" . md5($_POST['password']) . "'";
    }
    $row = $db->getRow($sql);

    if($row)
    {
        $sql="SELECT `action_list` FROM ". $yp->table('admin_user') ."WHERE user_name = '" . $_POST['username']."'";
        $action_list =$db->getOne($sql);

        if($action_list == 'all')
        {   
		    if (file_exists("../data/clear_demo.txt")) {
             sys_msg($_LANG['not_txt'], 1);
			 exit;
            }
            $tables = array(
                'account_log', 'admin_log', 'admin_message', 'adsense', 'affiliate_log', 'auction_log', 'agency','attribute',
                'back_action','back_goods','back_order','back_replay', 'bar_code','bind_record', 'bonus_type', 'booking_goods', 'brand',
                'cart', 'card','chat_customer', 'collect_goods','comment', 'cut','cut_log','category',
                'delivery_goods', 'delivery_order','deposit', 'dianpu',
                'email','email_list','email_sendlist','error_log','exchange_goods','extpintuan','extpintuan_orders','extpintuan_price',
                'favourable_activity', 'feedback', 'friend_link',
                'goods', 'goods_activity', 'goods_article', 'goods_attr', 'goods_cat', 'goods_gallery',  'group_goods',
                'keyword','keyword_area','keywords',
                'link_goods','lucky_buy','lucky_buy_calculate','lucky_buy_detail',
                'member_price',
				'ypmart_article_ad_info','ypmart_article_ad_user','ypmart_distrib_goods',
                'weixin_prince_qrcode',
                'order_action', 'order_goods', 'order_info',
                'pack', 'package_goods', 'payment', 'pay_log', 'products', 'pricecut',
				'question',
                'shipping', 'shipping_area', 'snatch_log', 'stats','sessions','sessions_data','shaidan','shaidan_img',
				'snatch_log',
                'supplier','suppliers',  'supplier_admin_user', 'supplier_article', 'supplier_article_cat', 'supplier_cat_recommend', 'supplier_goods_cat', 'supplier_guanzhu',
                'supplier_rebate', 'supplier_rebate_log', 'supplier_shop_config', 'supplier_street', 'supplier_tag', 'supplier_tag_map', 'suppliers',
                'tag', 'takegoods', 'takegoods_goods', 'takegoods_order', 'takegoods_type', 'takegoods_type_goods',
                'user_account', 'user_address', 'user_bonus', 'user_feed', 'user_address', 'users',
                'validate_record', 'valuecard', 'valuecard_type', 'verifycode',
                'virtual_card', 'virtual_district', 'virtual_goods_card', 'virtual_goods_district','volume_price', 'vote', 'vote_log', 'vote_option',
                 'wholesale', 'weixin_actlog', 'weixin_ad_log',     'weixin_corn', 'weixin_jflog',     'weixin_login',   'weixin_msg',   'weixin_paylog',   'weixin_qcode',   'weixin_reward_log',   'weixin_share',   'weixin_sign',   'weixin_user', 
				 'tag','takegoods','takegoods_goods','takegoods_order','users','user_account','user_account_huafei_config','user_address','user_bonus','user_feed','validate_record','valuecard','valuecard_type','verifycode','vote','vote_log','vote_option',
				 'cut','cut_log','extpintuan','extpintuan_orders','extpintuan_price',
				 'lucky_buy','lucky_buy_calculate','lucky_buy_detail'   //PRINCE 20160626
            );

            foreach ($tables AS $table)
            {
                $sql = "TRUNCATE `{$prefix}$table`";
                $db->query($sql);
            }
			$sql = "delete from `{$prefix}goods_type` where cat_id!=5";
			$db->query($sql);
            $file = fopen("../data/clear_demo.txt","w");
            fwrite($file,$_LANG['clear_txt']);
            clear_cache_files();
            sys_msg($_LANG['clear_success'], 0);
        }
        else
        {
            sys_msg($_LANG['not_permitted'], 1);
        }
    }
    else
    {
       
        sys_msg($_LANG['password_incorrect'], 1);
		
    }
}
?>