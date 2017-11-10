<?php

/**
 * 店铺 首页文件
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: PRINCE $
 * $Id: index.php 17217 2017-04-01 06:29:08Z PRINCE $
*/

define('IN_PRINCE', true);
//判断是否有ajax请求
$act = !empty($_REQUEST['act']) ? $_REQUEST['act'] : 'list';
if($act == 'list'){
    // 数据准备
    $supplier_id = empty($_GET['suppId'])?0:intval($_GET['suppId']);
    /* 取得用户等级 */
    $user_rank_list = array();
    $user_rank_list[0] = $_LANG['not_user'];
    $sql = "SELECT rank_id, rank_name FROM " . $GLOBALS['yp']->table('user_rank');
    $res = $db->query($sql);
        while ($row = $db->fetchRow($res))
	{
            $user_rank_list[$row['rank_id']] = $row['rank_name'];
	}
    


// 开始工作

$sql = "SELECT * FROM " . $yp->table('favourable_activity'). " WHERE supplier_id = $supplier_id AND ORDER BY `sort_order` ASC,`end_time` DESC";
$nowtime = gmtime();
$sql = "SELECT fa.* FROM " . $yp->table('favourable_activity'). " AS fa ".
		"WHERE fa.supplier_id = $supplier_id AND fa.start_time<=".$nowtime." AND fa.end_time>=".$nowtime.
		" ORDER BY fa.`sort_order` ASC,fa.`end_time` DESC ";
$res = $db->query($sql);
$activity_list = array();
while ($row = $db->fetchRow($res))
{
    $row['start_time']  = local_date('Y-m-d H:i', $row['start_time']);
    $row['end_time']    = local_date('Y-m-d H:i', $row['end_time']);
    //享受优惠会员等级
    $user_rank = explode(',', $row['user_rank']);
    $row['user_rank'] = array();
    foreach($user_rank as $val)
    {
        if (isset($user_rank_list[$val]))
        {
            $row['user_rank'][] = $user_rank_list[$val];
        }
    }

    //优惠范围类型、内容
    if ($row['act_range'] != FAR_ALL && !empty($row['act_range_ext']))
    {
        if ($row['act_range'] == FAR_CATEGORY)
        {
            $row['act_range'] = $_LANG['far_category'];
            $row['program'] = 'category.php?id=';
            $sql = "SELECT cat_id AS id, cat_name AS name FROM " . $yp->table('supplier_category') .
                " WHERE cat_id " . db_create_in($row['act_range_ext']);
        }
        elseif ($row['act_range'] == FAR_BRAND)
        {
            $row['act_range'] = $_LANG['far_brand'];
            $row['program'] = 'brand.php?id=';
            $sql = "SELECT brand_id AS id, brand_name AS name FROM " . $yp->table('brand') .
                " WHERE brand_id " . db_create_in($row['act_range_ext']);
        }
        else
        {
            $row['act_range'] = $_LANG['far_goods'];
           $row['program'] = 'goods.php?id=';
            $sql = "SELECT goods_id AS id, goods_name AS name, goods_thumb AS thumb FROM " . $yp->table('goods') .
                " WHERE goods_id " . db_create_in($row['act_range_ext']);
        }
        $act_range_ext = $db->getAll($sql);
        foreach($act_range_ext as $key=>$value){
            $act_range_ext[$key]['thumb'] = get_image_path($value['goods_id'],$value['thumb']);//mod by qq120-029-121 20160815
        }
        $row['act_range_ext'] = $act_range_ext;
    }
    else
    {
        $row['act_range'] = $_LANG['far_all'];
    }

    //优惠方式
    $row['act_type_num'] = $row['act_type'];
    switch($row['act_type'])
    {
        case 0:
            $row['act_type'] = $_LANG['fat_goods'];
            $row['gift'] = unserialize($row['gift']);
            if(is_array($row['gift']))
            {
                foreach($row['gift'] as $k=>$v)
                {
                    $row['gift'][$k]['thumb'] = get_image_path($v['id'], $db->getOne("SELECT goods_thumb FROM " . $yp->table('goods') . " WHERE goods_id = '" . $v['id'] . "'"), true);//mod by qq120-029-121 20160815
                }
           }
           break;
        case 1:
            $row['act_type'] = $_LANG['fat_price'];
            $row['act_type_ext'] .= $_LANG['unit_yuan'];
            $row['gift'] = array();
            break;
        case 2:
            $row['act_type'] = $_LANG['fat_discount'];
            $row['act_type_ext'] .= "%";
           $row['gift'] = array();
            break;
    }
    
    if($row['supplier_id'] > 0){
	    $sql = "select code,value from " . $yp->table('supplier_shop_config'). 
	    		" where supplier_id=".$row['supplier_id'].
	    		" AND code in('shop_name','shop_logo')";
	    $r = $db->getAll($sql);
	    foreach($r as $k=>$v){
	    	$row[$v['code']] = $v['value'];
	    }
    }else{
        $row['shop_name'] = '网站自营';
    	$row['shop_logo'] = 'admin/'.$_CFG['shop_logo'];
    }
    

    $activity_list[] = $row;
}
// echo "<pre>";
// print_r($list);
    $smarty->assign('activity_list',             $activity_list);

    $smarty->assign('helps',            get_shop_help());       // 网店帮助
    $smarty->assign('lang',             $_LANG);

    $smarty->assign('feed_url',         ($_CFG['rewrite'] == 1) ? "feed-typeactivity.xml" : 'feed.php?type=activity'); // RSS URL
    $smarty->display('activity.dwt');
    }
?>