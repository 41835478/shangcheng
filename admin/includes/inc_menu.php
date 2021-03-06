<?php

/**
 * QQ120029121 管理中心菜单数组
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: PRINCE $
 * $Id: inc_menu.php 17217 2017-04-01 06:29:08Z PRINCE $
 */
if(! defined('IN_PRINCE'))
{
	die('Hacking attempt');
}
								//商品管理
//--------------------------------------------------------------------------------
$modules['02_cat_and_goods']['01_goods_list'] = 'goods.php?act=list'; // 商品列表
$modules['02_cat_and_goods']['02_supplier_goods_list'] = 'goods.php?act=list&supp=1'; // 供货商商品列表
$modules['02_cat_and_goods']['03_goods_add'] = 'goods.php?act=add'; // 添加商品
$modules['02_cat_and_goods']['04_category_list'] = 'category.php?act=list';//商品分类
$modules['02_cat_and_goods']['05_comment_manage'] = 'comment_manage.php?act=list';//用户评论
// 代码增加 评论详情
$modules['02_cat_and_goods']['05_order_comment'] = 'order_comment.php?act=list';//订单评论
/* 晒单插件 增加 by demo.coolhong.com 今 天 优 品 多 商 户 系 统 q q 1 2 0 0 2 9 1 2 1 */
$modules['02_cat_and_goods']['05_shaidan_manage'] = 'shaidan.php?act=list';//用户晒单
$modules['02_cat_and_goods']['05_goods_tags'] = 'goods_tags.php?act=list';//标签审核
/* 晒单插件 增加 by demo.coolhong.com 今 天 优 品 多 商 户 系 统 q q 1 2 0 0 2 9 1 2 1 */
// $modules['02_cat_and_goods']['05_question_manage'] =
// 'question_manage.php?act=list';
$modules['02_cat_and_goods']['06_goods_brand_list'] = 'brand.php?act=list';//商品品牌
$modules['02_cat_and_goods']['08_goods_type'] = 'goods_type.php?act=manage';//商品类型/规格/属性
$modules['02_cat_and_goods']['11_goods_trash'] = 'goods.php?act=trash'; // 商品回收站
$modules['02_cat_and_goods']['12_batch_pic'] = 'picture_batch.php';//图片批量处理
$modules['02_cat_and_goods']['13_batch_add'] = 'goods_batch.php?act=add'; // 商品批量上传
$modules['02_cat_and_goods']['14_goods_export'] = 'goods_export.php?act=goods_export';//商品批量导出
$modules['02_cat_and_goods']['15_batch_edit'] = 'goods_batch.php?act=select'; // 商品批量修改
$modules['02_cat_and_goods']['16_goods_script'] = 'gen_goods_script.php?act=setup';	//生成商品代码
$modules['02_cat_and_goods']['17_tag_manage'] = 'tag_manage.php?act=list';//标签管理
/*
 * $modules['02_cat_and_goods']['50_virtual_card_list'] =
 * 'goods.php?act=list&extension_code=virtual_card';
 * $modules['02_cat_and_goods']['51_virtual_card_add'] =
 * 'goods.php?act=add&extension_code=virtual_card';
 * $modules['02_cat_and_goods']['52_virtual_card_change'] =
 * 'virtual_card.php?act=change';
 */
$modules['02_cat_and_goods']['goods_auto'] = 'goods_auto.php?act=list';//商品自动上下加
$modules['02_cat_and_goods']['pricecut'] = 'pricecut.php?act=list&status=-1';//降价通知列表
$modules['02_cat_and_goods']['scan_store'] = 'scan.php?act=insert';//出入库管理
$purview['pricecut'] = 'goods_manage';
$_LANG['pricecut'] = '降价通知列表';
//-------------------------------------------------------------------------
//入驻商管理
//--------------------------------------------------------
/* 代码增加_start By demo.coolhong.com 今天优品多商户系统 qq 120029121 */
$modules['02_supplier']['01_supplier_reg'] = 'supplier.php?act=list';//入驻商申请列表
$modules['02_supplier']['02_supplier_list'] = 'supplier.php?act=list&status=1';//入驻商列表
$modules['02_supplier']['03_rebate_nopay'] = 'supplier_rebate.php?act=list';//平台交易统计
//$modules['02_supplier']['03_rebate_pay'] = 'supplier_rebate.php?act=list&is_pay_ok=1';
$modules['02_supplier']['04_shop_category'] = 'supplier_street_category.php?act=list';//店铺街分类
$modules['02_supplier']['05_shop_street'] = 'supplier_street.php?act=list';//店铺街列表
$modules['02_supplier']['05_supplier_rank'] = 'supplier_rank.php?act=list';//入驻商等级
$modules['02_supplier']['06_supplier_tag'] = 'supplier_tag.php?act=list';//店铺标签
//---------------------------------------------------------------------------------------
//促销管理
//------------------------------------------------------
/* $modules['03_promotion']['02_snatch_list'] = 'snatch.php?act=list'; */
$modules['03_promotion']['04_bonustype_list'] = 'bonus.php?act=list';//优惠卷类型
// $modules['03_promotion']['06_pack_list'] = 'pack.php?act=list';
// $modules['03_promotion']['07_card_list'] = 'card.php?act=list';
// $modules['03_promotion']['08_group_buy'] = 'group_buy.php?act=list';
$modules['03_promotion']['09_topic'] = 'topic.php?act=list';//专题管理
$modules['03_promotion']['10_auction'] = 'auction.php?act=list';//拍卖活动
$modules['03_promotion']['12_favourable'] = 'favourable.php?act=list';//优惠活动
// $modules['03_promotion']['13_wholesale'] = 'wholesale.php?act=list';
$modules['03_promotion']['14_package_list'] = 'package.php?act=list';//超值礼包
// $modules['03_promotion']['ebao_commend'] = 'ebao_commend.php?act=list';
$modules['03_promotion']['15_exchange_goods'] = 'exchange_goods.php?act=list';//积分商城商品
$modules['03_promotion']['16_takegoods_list'] = 'takegoods.php?act=list';//提货券管理
$modules['03_promotion']['16_takegoods_order'] = 'takegoods.php?act=order_list';//提货券提货列表
$modules['03_promotion']['19_valuecard_list'] = 'valuecard.php?act=list';
$_LANG['19_valuecard_list'] = '储值卡管理';
$purview['19_valuecard_list'] = 'bonus_manage';
$modules['03_promotion']['25_pre_sale_list'] = 'pre_sale.php?act=list'; // 预售
$modules['03_promotion']['26_cut']       = 'cut.php?act=list';//砍价活动
$modules['03_promotion']['28_extpintuan']       = 'extpintuan.php?act=list';//新版拼团
$modules['03_promotion']['30_lucky_buy']       = 'lucky_buy.php?act=list';//云购
//----------------------------------------------------------
//订单管理
//-------------------------------------------------------------
$modules['04_order']['01_order_list'] = 'order.php?act=list';//订单列表
$modules['04_order']['02_supplier_order'] = 'order.php?act=list&supp=1';//入驻商订单列表
$modules['04_order']['03_order_query'] = 'order.php?act=order_query';//订单查询
$modules['04_order']['04_merge_order'] = 'order.php?act=merge';//合并订单
$modules['04_order']['05_edit_order_print'] = 'order.php?act=templates';//订单打印模板
$modules['04_order']['06_undispose_booking'] = 'goods_booking.php?act=list_all';//缺货登记
// $modules['04_order']['07_repay_application'] = 'repay.php?act=list_all';
$modules['04_order']['08_add_order'] = 'order.php?act=add';//添加订单
$modules['04_order']['09_delivery_order'] = 'order.php?act=delivery_list';//发货单列表
$modules['04_order']['10_back_order'] = 'back.php?act=back_list'; // 代码修改 退款/退货
$modules['04_order']['11_kuaidi_order'] = 'kuaidi_order.php?act=list';//快递单列表
$modules['04_order']['11_supplier_back_order'] = 'back.php?act=back_list&supp=1'; // 代码修改 入驻商退换货列表
$modules['04_order']['12_invoice_list'] = 'order.php?act=invoice_list';//发票列表
$modules['04_order']['12_kuaidi_order2'] = 'kuaidi_order.php?act=list&order_status=4&is_finish=1';//快递单历史
$modules['04_order']['12_order_excel'] = 'excel.php?act=order_excel';//订单历史
// $modules['04_order']['10_back_order'] = 'order.php?act=back_list';
// By
// demo.coolhong.com
// By
// demo.coolhong.com
// jtypmall
// add
// start
// jtypmall add end
/* 增值税发票_添加_START_demo.coolhong.com */

//--------------------------------------------------------------
/* 增值税发票_添加_START_demo.coolhong.com */

/* 虚拟卷订单列表_添加_START_demo.coolhong.com */
//-------------------------------------------------------------------------
$modules['18_virtual']['virtual_goods_add'] = 'virtual_goods.php?act=add&extension_code=virtual_good'; // 添加虚拟商品
$modules['18_virtual']['virtual_goods_sup'] = 'virtual_goods.php?act=list&extension_code=virtual_good&supp=1'; // 虚拟商品列表
$modules['18_virtual']['virtual_goods_list'] = 'virtual_goods.php?act=list&extension_code=virtual_good'; // 虚拟商品列表
$modules['18_virtual']['virtual_card_list'] = 'virtual_goods_card.php?act=all_card';
$modules['18_virtual']['virtual_validate'] = 'virtual_goods_card.php?act=verification_info';
$modules['18_virtual']['virtual_category'] = 'category.php?act=virtual_list'; // 虚拟商品分类管理
$modules['18_virtual']['virtual_district'] = 'virtual_goods.php?act=district'; // 虚拟商品商圈管理
/* 虚拟卷订单列表_添加_START_demo.coolhong.com */
//------------------------------------------------------------------------

//广告管理
//---------------------------------------------
$modules['05_banner']['ad_position'] = 'ad_position.php?act=list';//广告列表
$modules['05_banner']['ad_list'] = 'ads.php?act=list';//广告位置
//----------------------------------------------------

//报表统计
//$modules['06_stats']['flow_stats'] = 'flow_stats.php?act=view';
/* 代码添加_START By demo.coolhong.com 今天优品多商户系统 qq 120029121 */
$modules['06_stats']['keyword'] = 'keyword.php?act=list'; // 客户搜索记录
/* 代码添加_SEND By demo.coolhong.com 今天优品多商户系统 qq 120029121 */
//$modules['06_stats']['searchengine_stats'] = 'searchengine_stats.php?act=view';
//$modules['06_stats']['z_clicks_stats'] = 'adsense.php?act=list';
$modules['06_stats']['report_guest'] = 'guest_stats.php?act=list'; // 客户统计
//$modules['06_stats']['report_order'] = 'order_stats.php?act=list';
//$modules['06_stats']['report_sell'] = 'sale_general.php?act=list';
//$modules['06_stats']['sale_list'] = 'sale_list.php?act=list';
//$modules['06_stats']['sell_stats'] = 'sale_order.php?act=goods_num';
//$modules['06_stats']['report_users'] = 'users_order.php?act=order_num';
//$modules['06_stats']['visit_buy_per'] = 'visit_sold.php?act=list';
/* 代码增加 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 Start */
$modules['06_stats']['industry_stats'] = 'industry_scale_stats.php?act=list'; // 行业分析
$modules['06_stats']['users_stats'] = 'user_added_stats.php?act=list'; // 会员统计
$modules['06_stats']['shops_stats'] = 'shop_added_stats.php?act=list'; // 店铺统计
$modules['06_stats']['orders_stats'] = 'order_stats.php?act=list'; // 订单统计
$modules['06_stats']['goods_stats'] = 'goods_stats.php?act=list'; // 商品分析
$modules['06_stats']['sells_stats'] = 'sell_stats.php?act=list'; // 销售报告
$modules['06_stats']['after_sells_stats'] = 'refund_stats.php?act=list'; // 售后统计
/* 代码增加 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 End */
//--------------------------------------------------------------------------------------------
$modules['07_content']['03_article_list'] = 'article.php?act=list';
$modules['07_content']['02_articlecat_list'] = 'articlecat.php?act=list';
$modules['07_content']['vote_list'] = 'vote.php?act=list';
$modules['07_content']['article_auto'] = 'article_auto.php?act=list';
// $modules['07_content']['shop_help'] = 'shophelp.php?act=list_cat';
// $modules['07_content']['shop_info'] = 'shopinfo.php?act=list';

$modules['08_members']['03_users_list'] = 'users.php?act=list';

$modules['08_members']['04_users_export'] = 'users_export.php'; // 代码增加
// By
// demo.coolhong.com

$modules['08_members']['04_users_add'] = 'users.php?act=add';//添加会员
$modules['08_members']['05_user_rank_list'] = 'user_rank.php?act=list';//会员等级
//$modules['08_members']['06_list_integrate'] = 'integrate.php?act=list';
$modules['08_members']['08_unreply_msg'] = 'user_msg.php?act=list_all';
$modules['08_members']['09_user_account'] = 'user_account.php?act=list';
$modules['08_members']['10_user_account_manage'] = 'user_account_manage.php?act=list';
$modules['08_members']['09_postman_list'] = 'postman.php?act=list';



$modules['10_priv_admin']['admin_logs'] = 'admin_logs.php?act=list';
$modules['10_priv_admin']['admin_list'] = 'privilege.php?act=list';
$modules['10_priv_admin']['admin_role'] = 'role.php?act=list';
$modules['10_priv_admin']['agency_list'] = 'agency.php?act=list';
$modules['10_priv_admin']['suppliers_list'] = 'suppliers.php?act=list'; // 供货商

$modules['11_system']['01_shop_config'] = 'shop_config.php?act=list_edit';
//$modules['11_system']['shop_authorized'] = 'license.php?act=list_edit';//授权证书
$modules['11_system']['02_payment_list'] = 'payment.php?act=list';
$modules['11_system']['03_shipping_list'] = 'shipping.php?act=list';
$modules['11_system']['04_mail_settings'] = 'shop_config.php?act=mail_settings';
$modules['11_system']['05_area_list'] = 'area_manage.php?act=list';
// $modules['11_system']['06_plugins'] = 'plugins.php?act=list';
$modules['11_system']['07_cron_schcron'] = 'cron.php?act=list';
$modules['11_system']['08_friendlink_list'] = 'friend_link.php?act=list';
$modules['11_system']['sitemap'] = 'sitemap.php';
$modules['11_system']['check_file_priv'] = 'check_file_priv.php?act=check';
$modules['11_system']['captcha_manage'] = 'captcha_manage.php?act=main';
$modules['11_system']['ucenter_setup'] = 'integrate.php?act=setup&code=ucenter';
$modules['11_system']['flashplay'] = 'flashplay.php?act=list';
$modules['11_system']['navigator'] = 'navigator.php?act=list';
//$modules['11_system']['file_check'] = 'filecheck.php';
// $modules['11_system']['fckfile_manage'] = 'fckfile_manage.php?act=list';
$modules['11_system']['021_reg_fields'] = 'reg_fields.php?act=list';
//模板管理
$modules['12_template']['02_template_select'] = 'template.php?act=list';
$modules['12_template']['03_template_setup'] = 'template.php?act=setup';
$modules['12_template']['04_template_library'] = 'template.php?act=library';
$modules['12_template']['05_edit_languages'] = 'edit_languages.php?act=list';
$modules['12_template']['06_template_backup'] = 'template.php?act=backup_setting';
$modules['12_template']['mail_template_manage'] = 'mail_template.php?act=list';
//数据库管理
$modules['13_backup']['02_db_manage'] = 'database.php?act=backup';
$modules['13_backup']['03_db_optimize'] = 'database.php?act=optimize';
$modules['13_backup']['04_sql_query'] = 'sql.php?act=main';
$modules['13_backup']['clear_demo'] = 'clear_demo.php?act=start';
// $modules['13_backup']['05_synchronous'] = 'integrate.php?act=sync';
$modules['13_backup']['convert'] = 'convert.php?act=main';

// $modules['14_sms']['02_sms_my_info'] = 'sms.php?act=display_my_info';
//$modules['14_sms']['03_sms_send'] = 'sms.php?act=display_send_ui';  //prince    20170517  q q 1 2 0029121
// $modules['14_sms']['04_sms_charge'] = 'sms.php?act=display_charge_ui';
// $modules['14_sms']['05_sms_send_history'] =
// 'sms.php?act=display_send_history_ui';
// $modules['14_sms']['06_sms_charge_history'] =
// 'sms.php?act=display_charge_history_ui';

//$modules['15_rec']['affiliate'] = 'affiliate.php?act=list';  // del by prince
//$modules['15_rec']['affiliate_ck'] = 'affiliate_ck.php?act=list';// del by 1 2 0029121

$modules['16_email_manage']['email_list'] = 'email_list.php?act=list';
$modules['16_email_manage']['magazine_list'] = 'magazine_list.php?act=list';
$modules['16_email_manage']['attention_list'] = 'attention_list.php?act=list';
$modules['16_email_manage']['view_sendlist'] = 'view_sendlist.php?act=list';
/* 代码增加_end By demo.coolhong.com 今天优品多商户系统 qq 120029121 */

// 微信权限
// $modules['17_weixin_manage']['weixin_config'] = 'weixin.php?act=config';
// $modules['17_weixin_manage']['weixin_addconfig'] =
// 'weixin.php?act=addconfig';
// $modules['17_weixin_manage']['weixin_menu'] = 'weixin.php?act=menu';
// $modules['17_weixin_manage']['weixin_notice'] = 'weixin.php?act=notice';
// $modules['17_weixin_manage']['weixin_keywords'] = 'weixin.php?act=keywords';
// $modules['17_weixin_manage']['weixin_fans'] = 'weixin.php?act=fans';
// $modules['17_weixin_manage']['weixin_news'] = 'weixin.php?act=news';
// $modules['17_weixin_manage']['weixin_addqcode'] = 'weixin.php?act=addqcode';
// $modules['17_weixin_manage']['weixin_qcode'] = 'weixin.php?act=qcode';
// $modules['17_weixin_manage']['weixin_reg'] = 'weixin.php?act=reg';
// 活动管理
// $modules['17_weixin_manage']['weixin_act'] = 'weixin_egg.php?act=list';
// $modules['17_weixin_manage']['weixin_award'] = 'weixin_egg.php?act=log';
// $modules['17_weixin_manage']['weixin_oauth'] = 'weixin.php?act=oauth';
// $modules['17_weixin_manage']['weixin_qiandao'] = 'weixin.php?act=qiandao';
// $modules['17_weixin_manage']['weixin_addkey'] = 'weixin.php?act=addkey';
$modules['11_system']['website'] = 'website.php?act=list';
/* 代码增加_start By demo.coolhong.com 今天优品多商户系统 qq 120029121 */
/* 代码增加_end By demo.coolhong.com 今天优品多商户系统 qq 120029121 */
$modules['16_email_manage']['sendmail'] = 'sendmail.php?act=sendmail';
/* 代码增加_start By demo.coolhong.com 今天优品多商户系统 qq 120029121 */
/* 代码增加_end By demo.coolhong.com 今天优品多商户系统 qq 120029121 */
/* 代码增加_start By demo.coolhong.com 今天优品多商户系统 Q Q 1200 2912 1 */
$modules['17_pickup_point_manage']['pickup_point_list'] = 'pickup_point.php?act=list';
$modules['17_pickup_point_manage']['pickup_point_add'] = 'pickup_point.php?act=add';
$modules['17_pickup_point_manage']['pickup_point_batch_add'] = 'pickup_point.php?act=batch_add';
/* 代码增加_end By demo.coolhong.com 今天优品多商户系统 Q Q 1200 2912 1 */

/* 代码增加_start By demo.coolhong.com 今天优品多商户系统 qq 120029121 */
$modules['11_system']['website'] = 'website.php?act=list';
/* 代码增加_end By demo.coolhong.com 今天优品多商户系统 qq 120029121 */

//$modules['20_chat']['chat_settings'] = 'chat_settings.php'; // 聊天服务设置    //20170905 云旺修改 by prince
$modules['20_chat']['customer'] = 'customer.php?act=list'; // 客服管理
$modules['20_chat']['third_customer'] = 'third_customer.php?act=list'; // 三方客服
require('prince_menu.php');


//分销管理
$modules['29_fenxiao']['001_29_fenxiao'] = '../mobile/'.ADMIN_PATH.'/fenxiao_config.php?act=list_edit';
$modules['29_fenxiao']['01_29_fenxiao'] = '../mobile/'.ADMIN_PATH.'/affiliate.php?act=list';
$modules['29_fenxiao']['02_29_fenxiao'] = '../mobile/'.ADMIN_PATH.'/distributor.php?act=list';
$modules['29_fenxiao']['03_29_fenxiao'] = '../mobile/'.ADMIN_PATH.'/distrib_goods.php?act=list';
$modules['29_fenxiao']['04_29_fenxiao'] = '../mobile/'.ADMIN_PATH.'/affiliate_ck.php?act=list';
$modules['29_fenxiao']['04_29_fenxiao2'] = '../mobile/'.ADMIN_PATH.'/affiliate_done.php?act=list';
$modules['29_fenxiao']['05_29_fenxiao'] = '../mobile/'.ADMIN_PATH.'/distrib_sort.php?act=list';
$modules['29_fenxiao']['06_29_fenxiao'] = '../mobile/'.ADMIN_PATH.'/deposit_list.php?act=list';


//微信设置
$modules['30_wx_manage']['01_30_wx_manage'] = '../mobile/'.ADMIN_PATH.'/weixin.php?act=config';
$modules['30_wx_manage']['02_30_wx_manage'] = '../mobile/'.ADMIN_PATH.'/weixin.php?act=menu';
$modules['30_wx_manage']['03_30_wx_manage'] = '../mobile/'.ADMIN_PATH.'/weixin.php?act=keywords&t=1';
$modules['30_wx_manage']['04_30_wx_manage'] = '../mobile/'.ADMIN_PATH.'/weixin.php?act=keywords';
$modules['30_wx_manage']['05_30_wx_manage'] = '../mobile/'.ADMIN_PATH.'/article_danpin.php?act=list';
$modules['30_wx_manage']['06_30_wx_manage'] = '../mobile/'.ADMIN_PATH.'/weixin.php?act=autoreg';
$modules['30_wx_manage']['07_30_wx_manage'] = '../mobile/'.ADMIN_PATH.'/weixin.php?act=qiandao';
$modules['30_wx_manage']['08_30_wx_manage'] = '../mobile/'.ADMIN_PATH.'/weixin_egg.php?act=list';
$modules['30_wx_manage']['09_30_wx_manage'] = '../mobile/'.ADMIN_PATH.'/weixin_egg.php?act=log';
$modules['30_wx_manage']['10_30_wx_manage'] = '../mobile/'.ADMIN_PATH.'/weixin.php?act=qcode';
$modules['30_wx_manage']['11_30_wx_manage'] = '../mobile/'.ADMIN_PATH.'/weixin.php?act=addqcode';
$modules['30_wx_manage']['12_30_wx_manage'] = '../mobile/'.ADMIN_PATH.'/weixin.php?act=qrcode_config';
$modules['30_wx_manage']['13_30_wx_manage'] = '../mobile/'.ADMIN_PATH.'/weixin.php?act=remind';
$modules['30_wx_manage']['14_30_wx_manage'] = '../mobile/'.ADMIN_PATH.'/weixin.php?act=news';
$modules['30_wx_manage']['15_30_wx_manage'] = '../mobile/'.ADMIN_PATH.'/weixin.php?act=fans';
$modules['30_wx_manage']['16_30_wx_manage'] = '../mobile/'.ADMIN_PATH.'/weixin.php?act=notice';
$modules['30_wx_manage']['17_30_wx_manage'] = '../mobile/'.ADMIN_PATH.'/weixin_share.php?act=list';

//手机端系统设置
$modules['31_m_system']['01_31_m_system'] = '../mobile/'.ADMIN_PATH.'/shop_config.php?act=list_edit';
$modules['31_m_system']['02_31_m_system'] = '../mobile/'.ADMIN_PATH.'/menu.php?act=list';
$modules['31_m_system']['03_31_m_system'] = '../mobile/'.ADMIN_PATH.'/website.php?act=list';
$modules['31_m_system']['04_31_m_system'] = '../mobile/'.ADMIN_PATH.'/captcha_manage.php?act=main';
$modules['31_m_system']['05_31_m_system'] = '../mobile/'.ADMIN_PATH.'/shop_config.php?act=mail_settings';
$modules['31_m_system']['06_31_m_system'] = '../mobile/'.ADMIN_PATH.'/mail_template.php?act=list';

//手机端广告管理
$modules['32_m_banner']['01_32_m_banner'] = '../mobile/'.ADMIN_PATH.'/ad_position.php?act=list';
$modules['32_m_banner']['02_32_m_banner'] = '../mobile/'.ADMIN_PATH.'/ads.php?act=list';


//手机端模板管理
$modules['33_m_template']['01_33_m_template'] = '../mobile/'.ADMIN_PATH.'/template.php?act=setup';
$modules['33_m_template']['02_33_m_template'] = '../mobile/'.ADMIN_PATH.'/template.php?act=library';
$modules['33_m_template']['03_33_m_template'] = '../mobile/'.ADMIN_PATH.'/edit_languages.php?act=list';

//APP管理
$modules['34_app']['01_34_app'] = '../mobile/'.ADMIN_PATH.'/app_manage.php?act=basic_setting';
$modules['34_app']['02_34_app'] = '../mobile/'.ADMIN_PATH.'/app_push.php?act=setting';
$modules['34_app']['03_34_app'] = '../mobile/'.ADMIN_PATH.'/app_push.php?act=push_message';
$modules['34_app']['04_34_app'] = '../mobile/'.ADMIN_PATH.'/app_manage.php?act=guide_picture';
$modules['34_app']['05_34_app'] = '../mobile/'.ADMIN_PATH.'/app_manage.php?act=menu_setting';
$modules['34_app']['06_34_app'] = '../mobile/'.ADMIN_PATH.'/app_manage.php?act=template_setting';

//小程序管理
$modules['35_xcx']['01_35_xcx'] = 'xcx_settings.php?act=default'; //小程序设置 20170928
$modules['35_xcx']['02_35_xcx'] = 'xcx_menu.php?act=list'; //小程序菜单设置 20170928
$modules['35_xcx']['03_35_xcx'] = 'xcx_template.php?act=select'; //小程序菜单设置 20170928
$modules['35_xcx']['04_35_xcx'] = 'xcx_template.php?act=list'; //小程序模板设置 20170928
$modules['35_xcx']['05_35_xcx'] = 'xcx_make.php?act=default'; //小程序生成 20170928
$modules['35_xcx']['06_35_xcx'] = 'xcx_download.php?act=default'; //小程序下载 20170928

?>
