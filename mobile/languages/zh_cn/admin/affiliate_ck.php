<?php

/**
 * QQ120029121 程序说明
 * ===========================================================
 * * 版权所有 2005-2012 热风科技，并保留所有权利。
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ----------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ==========================================================
 * $Author: liubo $
 * $Id: affiliate_ck.php 17217 2011-01-19 06:29:08Z liubo $
 */


$_LANG['order_id'] = '订单号';
$_LANG['affiliate_separate'] = '分成';
$_LANG['affiliate_cancel'] = '取消';
$_LANG['affiliate_rollback'] = '撤销';
$_LANG['log_info'] = '操作信息';
$_LANG['edit_ok'] = '操作成功';
$_LANG['edit_fail'] = '操作失败';
$_LANG['separate_info'] = '订单号 %s, 分成:金钱 %s 积分 %s';
$_LANG['separate_info2'] = '用户ID %s ( %s ), 获得的分成金额： %s元,%s成长值';
$_LANG['sch_order'] = '搜索订单号';
$_LANG['add_time'] = '下单时间';
$_LANG['order_user_name'] = '下单人';

$_LANG['sch_stats']['name'] = '操作状态';
$_LANG['sch_stats']['info'] = '按操作状态查找:';
$_LANG['sch_stats']['all'] = '全部';
$_LANG['sch_stats'][0] = '未分成';
$_LANG['sch_stats'][1] = '已分成';
$_LANG['sch_stats'][2] = '取消分成';
$_LANG['sch_stats'][3] = '已撤销';
$_LANG['order_stats']['name'] = '订单状态';
$_LANG['order_stats'][0] = '未确认';
$_LANG['order_stats'][1] = '已确认';
$_LANG['order_stats'][2] = '已取消';
$_LANG['order_stats'][3] = '无效';
$_LANG['order_stats'][4] = '退货';
$_LANG['order_stats'][5] = '确认收货';

$_LANG['ss'][SS_UNSHIPPED] = '未发货';
$_LANG['ss'][SS_PREPARING] = '配货中';
$_LANG['ss'][SS_SHIPPED] = '已发货';
$_LANG['ss'][SS_RECEIVED] = '收货确认';
$_LANG['ss'][SS_SHIPPED_PART] = '已发货(部分商品)';
$_LANG['ss'][SS_SHIPPED_ING] = '发货中';

$_LANG['ps'][PS_UNPAYED] = '未付款';
$_LANG['ps'][PS_PAYING] = '付款中';
$_LANG['ps'][PS_PAYED] = '已付款';


$_LANG['js_languages']['cancel_confirm'] = '您确定要取消分成吗？此操作不能撤销。';
$_LANG['js_languages']['rollback_confirm'] = '您确定要撤销此次分成吗？';
$_LANG['js_languages']['separate_confirm'] = '您确定要分成吗？';
$_LANG['loginfo'][0] = '用户id:';
$_LANG['loginfo'][1] = '现金:';
$_LANG['loginfo'][2] = '积分:';
$_LANG['loginfo']['cancel'] = '分成被管理员取消！';

$_LANG['separate_type'] = '分成类型';
$_LANG['separate_by'][0] = '推荐注册分成';
$_LANG['separate_by'][1] = '推荐订单分成';
$_LANG['separate_by'][-1] = '推荐注册分成';
$_LANG['separate_by'][-2] = '推荐订单分成';

$_LANG['show_affiliate_orders'] = '此列表显示此用户推荐的订单信息。';
$_LANG['back_note'] = '返回会员编辑页面';

$_LANG['money_low'] = '余额不足，无法分成！';
$_LANG['supplier'] = '供货商家';
$_LANG['self_sale'] = '网站自营';

$_LANG['order_separate'] = '订单分成';
$_LANG['order_cancel_separate'] = '订单撤销分成';
?>