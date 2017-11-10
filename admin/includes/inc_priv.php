<?php

/**
 * QQ120029121 权限对照表
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: sunxiaodong $
 * $Id: inc_priv.php 15503 2008-12-24 09:22:45Z sunxiaodong $
 */
if(! defined('IN_PRINCE'))
{
	die('Hacking attempt');
}

// 商品管理权限
$purview['01_goods_list'] = array(
	'goods_manage', 'remove_back'
);
/* 代码增加_start By demo.coolhong.com */
$purview['02_supplier_goods_list'] = array(
	'goods_manage', 'remove_back'
);
$purview['03_goods_add'] = 'goods_manage';
$purview['04_category_list'] = array(
	'cat_manage', 'cat_drop'
); // 分类添加、分类转移和删除
/* 代码增加_end By demo.coolhong.com */
$purview['05_comment_manage'] = 'comment_priv';
$purview['05_order_comment'] = 'order_comment_priv'; // 代码增加 --订单评论
$purview['06_goods_brand_list'] = 'brand_manage';
$purview['08_goods_type'] = 'attr_manage'; // 商品属性
$purview['11_goods_trash'] = array(
	'goods_manage', 'remove_back'
);
$purview['12_batch_pic'] = 'picture_batch';
$purview['13_batch_add'] = 'goods_batch';
$purview['14_goods_export'] = 'goods_export';
$purview['15_batch_edit'] = 'goods_batch';
$purview['16_goods_script'] = 'gen_goods_script';
$purview['17_tag_manage'] = 'tag_manage';
/* 代码删除 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 Start */
//$purview['50_virtual_card_list'] = 'virualcard';
//$purview['51_virtual_card_add'] = 'virualcard';
//$purview['52_virtual_card_change'] = 'virualcard';
/* 代码删除 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 End */
$purview['goods_auto'] = 'goods_auto';
$purview['scan_store'] = 'scan_store';
$purview['05_shaidan_manage'] = 'shaidan_manage';
$purview['05_question_manage'] = 'question_manage';
$purview['05_goods_tags'] = 'tag_manage';

// 促销管理权限
/* 代码删除 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 Start */
//$purview['02_snatch_list'] = 'snatch_manage';
/* 代码删除 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 End */
$purview['04_bonustype_list'] = 'bonus_manage';
/* 代码删除 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 Start */
//$purview['06_pack_list'] = 'pack';
//$purview['07_card_list'] = 'card_manage';
//$purview['08_group_buy'] = 'group_by';
/* 代码删除 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 End */
$purview['09_topic'] = 'topic_manage';
$purview['10_auction'] = 'auction';
$purview['12_favourable'] = 'favourable';
$purview['13_wholesale'] = 'whole_sale';
$purview['14_package_list'] = 'package_manage';
// $purview['02_snatch_list'] = 'gift_manage'; //赠品管理
$purview['04_back_order'] = 'back_view';
$purview['15_exchange_goods'] = 'exchange_goods'; // 赠品管理
$purview['11_kuaidi_order'] = 'order_view';
$purview['12_kuaidi_order2'] = 'order_view';
$purview['25_pre_sale_list'] = 'pre_sale'; // 预售管理


$purview['26_cut'] = 'cut';
$purview['28_extpintuan'] = 'extpintuan';
$purview['30_lucky_buy'] = 'lucky_buy';

                                            
// 文章管理权限
$purview['02_articlecat_list'] = 'article_cat';
$purview['03_article_list'] = 'article_manage';
$purview['article_auto'] = 'article_auto';
$purview['vote_list'] = 'vote_priv';

// 会员管理权限
$purview['03_users_list'] = 'users_manage';

$purview['04_users_export'] = 'users_manage'; // 代码增加 By demo.coolhong.com 今天优品多商户系统 qq 120029121

$purview['04_users_add'] = 'users_manage';
$purview['05_user_rank_list'] = 'user_rank';
$purview['09_user_account'] = 'surplus_manage';
$purview['06_list_integrate'] = 'integrate_users';
$purview['08_unreply_msg'] = 'feedback_priv';
$purview['10_user_account_manage'] = 'account_manage';
$purview['09_postman_list'] = 'users_manage';

// 权限管理
$purview['admin_logs'] = array(
	'logs_manage', 'logs_drop'
);
$purview['admin_list'] = array(
	'admin_manage', 'admin_drop', 'allot_priv'
);
$purview['agency_list'] = 'agency_manage';
$purview['suppliers_list'] = 'suppliers_manage'; // 供货商
$purview['admin_role'] = 'role_manage';
$purview['customer'] = 'customer'; // 在线聊天客服管理
$purview['third_customer'] = 'third_customer'; // 三方客服
                                                     
// 商店设置权限
$purview['01_shop_config'] = 'shop_config';
$purview['shop_authorized'] = 'shop_authorized';
$purview['shp_webcollect'] = 'webcollect_manage';
$purview['02_payment_list'] = 'payment';
$purview['03_shipping_list'] = array(
	'ship_manage', 'shiparea_manage'
);
$purview['04_mail_settings'] = 'shop_config';
$purview['05_area_list'] = 'area_manage';
$purview['07_cron_schcron'] = 'cron';
$purview['08_friendlink_list'] = 'friendlink';
$purview['sitemap'] = 'sitemap';
$purview['check_file_priv'] = 'file_priv';
$purview['captcha_manage'] = 'shop_config';
$purview['file_check'] = 'file_check';
$purview['navigator'] = 'navigator';
$purview['flashplay'] = 'flash_manage';
$purview['ucenter_setup'] = 'integrate_users';
$purview['021_reg_fields'] = 'reg_fields';
$purview['website'] = 'website_login';

// 广告管理
$purview['z_clicks_stats'] = 'ad_manage';
$purview['ad_position'] = 'ad_manage';
$purview['ad_list'] = 'ad_manage';

// 订单管理权限
/* 代码增加_start By demo.coolhong.com */
$purview['01_order_list'] = 'order_view';
$purview['02_supplier_order'] = 'order_view';
/* 代码增加_end By demo.coolhong.com */
$purview['03_order_query'] = 'order_view';
$purview['04_merge_order'] = 'order_os_edit';
$purview['05_edit_order_print'] = 'order_os_edit';
$purview['06_undispose_booking'] = 'booking';
$purview['08_add_order'] = 'order_edit';
$purview['09_delivery_order'] = 'delivery_view';
$purview['10_back_order'] = 'back_view';
$purview['11_supplier_back_order'] = 'back_view';
$purview['12_order_excel'] = 'order_view';
$purview['02_supplier_order'] = 'order_view';
$purview['12_invoice_list'] = 'invoice_manage';

// 报表统计权限
$purview['flow_stats'] = 'client_flow_stats';
/* 代码添加_START By demo.coolhong.com 今天优品多商户系统 qq 120029121 */
$purview['keyword'] = 'client_flow_stats';
/* 代码添加_SEND By demo.coolhong.com 今天优品多商户系统 qq 120029121 */
$purview['report_guest'] = 'client_flow_stats';
$purview['report_users'] = 'client_flow_stats';
$purview['visit_buy_per'] = 'client_flow_stats';
$purview['searchengine_stats'] = 'client_flow_stats';
$purview['report_order'] = 'sale_order_stats';
$purview['report_sell'] = 'sale_order_stats';
$purview['sale_list'] = 'sale_order_stats';
$purview['sell_stats'] = 'sale_order_stats';
/* 代码增加 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 Start */
$purview['industry_stats'] = 'industry_stats'; // 行业分析
$purview['users_stats'] = 'users_stats'; // 会员统计
$purview['shops_stats'] = 'shops_stats'; // 店铺统计
$purview['orders_stats'] = 'orders_stats'; // 订单统计
$purview['goods_stats'] = 'goods_stats'; // 商品分析
$purview['sells_stats'] = 'sells_stats'; // 销售报告
$purview['after_sells_stats'] = 'after_sells_stats'; // 售后统计
$purview['client_keyword_stats'] = 'client_keyword_stats'; // 客户搜索记录
$purview['client_flow_stats'] = 'client_flow_stats'; // 客户统计
/* 代码增加 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 End */

// 模板管理
$purview['02_template_select'] = 'template_select';
$purview['03_template_setup'] = 'template_setup';
$purview['04_template_library'] = 'library_manage';
$purview['05_edit_languages'] = 'lang_edit';
$purview['06_template_backup'] = 'backup_setting';
$purview['mail_template_manage'] = 'mail_template';

// 数据库管理
$purview['02_db_manage'] = array(
	'db_backup', 'db_renew'
);
$purview['03_db_optimize'] = 'db_optimize';
$purview['04_sql_query'] = 'sql_query';
$purview['convert'] = 'convert';
$purview['clear_demo'] = 'clear_demo';

// 短信管理
$purview['02_sms_my_info'] = 'my_info';
$purview['03_sms_send'] = 'sms_send';
$purview['04_sms_charge'] = 'sms_charge';
$purview['05_sms_send_history'] = 'send_history';
$purview['06_sms_charge_history'] = 'charge_history';

// 推荐管理
$purview['affiliate'] = 'affiliate';
$purview['affiliate_ck'] = 'affiliate_ck';

// 邮件群发管理
$purview['attention_list'] = 'attention_list';
$purview['email_list'] = 'email_list';
$purview['magazine_list'] = 'magazine_list';
$purview['view_sendlist'] = 'view_sendlist';
$purview['sendmail'] = 'send_mail';

/* 代码增加_start By demo.coolhong.com */
$purview['05_supplier_rank'] = 'supplier_rank';
$purview['01_supplier_reg'] = 'supplier_manage';
$purview['02_supplier_list'] = 'supplier_manage';
$purview['03_rebate_nopay'] = 'supplier_rebate';
//$purview['03_rebate_pay'] = 'supplier_rebate';
$purview['04_shop_category'] = 'supplier_manage';
$purview['05_shop_street'] = 'supplier_manage';
$purview['06_supplier_tag'] = 'supplier_tag';
/* 代码增加_end By demo.coolhong.com */
// 微信权限
// $purview['weixin_config']= 'weixin_config';
// $purview['weixin_addconfig']= 'weixin_addconfig';
// $purview['weixin_menu']= 'weixin_menu';
// $purview['weixin_notice']= 'weixin_notice';
// $purview['weixin_keywords']= 'weixin_keywords';
// $purview['weixin_fans']= 'weixin_fans';
// $purview['weixin_news']= 'weixin_news';
// $purview['weixin_addqcode']= 'weixin_addqcode';
// $purview['weixin_qcode']= 'weixin_qcode';
// $purview['weixin_reg']= 'weixin_reg';
// 活动管理
// $purview['weixin_act']= 'weixin_act';
// $purview['weixin_award']= 'weixin_award';
// $purview['weixin_oauth']= 'weixin_oauth';
// $purview['weixin_qiandao']= 'weixin_qiandao';
// $purview['weixin_addkey']= 'weixin_addkey';
$purview['website'] = 'website';
/* 代码增加_start By demo.coolhong.com 今天优品多商户系统 qq 120029121 */
$purview['16_takegoods_list'] = 'takegoods_list';
$purview['16_takegoods_order'] = 'takegoods_order';
$purview['website'] = 'website';
/* 代码增加_end By demo.coolhong.com 今天优品多商户系统 qq 120029121 */

// 自提点管理权限
$purview['pickup_point_list'] = 'pickup_point_manage';
$purview['pickup_point_add'] = 'pickup_point_manage';
$purview['pickup_point_batch_add'] = 'pickup_point_batch';
// 即时通信
$purview['chat'] = 'chat';
$purview['chat_settings'] = 'chat_settings';
$purview['customer'] = 'customer';
$purview['third_customer'] = 'third_customer';

 //虚拟团购管理
$purview['virtual'] = 'virtual';  
$purview['virtual_goods_add'] = 'virtual_goods_add';
$purview['virtual_goods_sup'] = 'virtual_goods_sup';
$purview['virtual_goods_list'] = 'virtual_goods_list';
$purview['virtual_card_list'] = 'virtual_card_list';
$purview['virtual_validate'] = 'virtual_validate';
$purview['virtual_category'] = 'virtual_category';
$purview['virtual_district'] = 'virtual_district';



//分销管理
$purview['001_29_fenxiao'] = '001_29_fenxiao';
$purview['01_29_fenxiao'] = 'affiliate';
$purview['02_29_fenxiao'] = '02_29_fenxiao';
$purview['03_29_fenxiao'] = '03_29_fenxiao';
$purview['04_29_fenxiao'] = 'affiliate_ck';
$purview['04_29_fenxiao2'] = '04_29_fenxiao2';
$purview['05_29_fenxiao'] = '05_29_fenxiao';
$purview['06_29_fenxiao'] = '06_29_fenxiao';



//微信设置
$purview['01_30_wx_manage'] = '01_30_wx_manage';
$purview['02_30_wx_manage'] = '02_30_wx_manage';
$purview['03_30_wx_manage'] = '03_30_wx_manage';
$purview['04_30_wx_manage'] = '04_30_wx_manage';
$purview['05_30_wx_manage'] = '05_30_wx_manage';
$purview['06_30_wx_manage'] = '06_30_wx_manage';
$purview['07_30_wx_manage'] = '07_30_wx_manage';
$purview['08_30_wx_manage'] = '08_30_wx_manage';
$purview['09_30_wx_manage'] = '09_30_wx_manage';
$purview['10_30_wx_manage'] = '10_30_wx_manage';
$purview['11_30_wx_manage'] = '11_30_wx_manage';
$purview['12_30_wx_manage'] = '12_30_wx_manage';
$purview['13_30_wx_manage'] = '13_30_wx_manage';
$purview['14_30_wx_manage'] = '14_30_wx_manage';
$purview['15_30_wx_manage'] = '15_30_wx_manage';
$purview['16_30_wx_manage'] = '16_30_wx_manage';
$purview['17_30_wx_manage'] = '17_30_wx_manage';

//手机端系统设置
$purview['01_31_m_system'] = 'shop_config';
$purview['02_31_m_system'] = '02_31_m_system';
$purview['03_31_m_system'] = '03_31_m_system';
$purview['04_31_m_system'] = '04_31_m_system';
$purview['05_31_m_system'] = 'shop_config';
$purview['06_31_m_system'] = 'mail_template';

//手机端广告管理
$purview['01_32_m_banner'] = 'ad_manage';
$purview['02_32_m_banner'] = 'ad_manage';

//手机端模板管理
$purview['01_33_m_template'] = 'template_setup';
$purview['02_33_m_template'] = 'library_manage';
$purview['03_33_m_template'] = 'lang_edit';

//APP管理
$purview['01_34_app'] = 'basic_setting';
$purview['02_34_app'] = 'app_setting';
$purview['03_34_app'] = 'push_message';
$purview['04_34_app'] = 'guide_picture';
$purview['05_34_app'] = 'menu_setting';
$purview['06_34_app'] = 'template_setting';

//小程序管理
$purview['01_35_xcx'] = 'xcx_settings';//小程序设置
$purview['02_35_xcx'] = 'xcx_menu';//小程序菜单
$purview['03_35_xcx'] = 'xcx_template';//小程序模板

?>