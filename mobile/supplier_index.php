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
$act = !empty($_GET['act']) ? $_GET['act'] : '';
if ($act == 'cat_rec')
{
    $rec_array = array(1 => 'best', 2 => 'new', 3 => 'hot');
    $rec_type = !empty($_REQUEST['rec_type']) ? intval($_REQUEST['rec_type']) : '1';
    $cat_id = !empty($_REQUEST['cid']) ? intval($_REQUEST['cid']) : '0';
    include_once('includes/cls_json.php');
    $json = new JSON;
    $result   = array('error' => 0, 'content' => '', 'type' => $rec_type, 'cat_id' => $cat_id);

    $children = get_children($cat_id);
    $smarty->assign($rec_array[$rec_type] . '_goods',      get_category_recommend_goods($rec_array[$rec_type], $children));    // 推荐商品
    $smarty->assign('cat_rec_sign', 1);
    $result['content'] = $smarty->fetch('library/recommend_' . $rec_array[$rec_type] . '.lbi');
    die($json->encode($result));
}

/*------------------------------------------------------ */
//-- 判断是否存在缓存，如果存在则调用缓存，反之读取相应内容
/*------------------------------------------------------ */
/* 缓存编号 */
$cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-' . $_CFG['lang'].'-'.$_GET['suppId']));
if (!$smarty->is_cached('mall.dwt', $cache_id))
{

	//echo "<pre>";
    //print_r($_CFG);
    assign_template();
    assign_template_supplier();
    $position = assign_ur_here();
    $smarty->assign('page_title',      $position['title']);    // 页面标题
    //$smarty->assign('ur_here',         $position['ur_here']);  // 当前位置
    //$smarty->assign('feed_url',        ($_CFG['rewrite'] == 1) ? 'feed.xml' : 'feed.php'); // RSS URL

    $smarty->assign('is_guanzhu',is_guanzhu($_GET['suppId']));
    $smarty->assign('categories',      get_categories_tree_supplier()); // 分类树
    
	$supplier_id=$_GET['suppId']?$_GET['suppId']:0;
	$row=$db->getRow("select * from" . $yp->table('supplier_shop_config') . " WHERE code = 'mobile_header_picture' AND supplier_id=".$supplier_id);//added on 20160925 by PRINCE  qq 1 2 0 0 2 9 1 2 1
	$mobile_header_picture=trim($row['value']);//added on 20160925 by PRINCE  qq 1 2 0 0 2 9 1 2 1
	$smarty->assign('mobile_header_picture',    $mobile_header_picture?'/'.$mobile_header_picture:'/mobile/store/images/1.jpg');//added on 20160925 by PRINCE  qq 1 2 0 0 2 9 1 2 1
	
	
	$row=$db->getRow("select * from" . $yp->table('supplier_shop_config') . " WHERE code = 'mobile_shop_header_color' AND supplier_id=".$supplier_id);//added on 20160925 by PRINCE  qq 1 2 0 0 2 9 1 2 1
	$mobile_shop_header_color=trim($row['value']);//added on 20160925 by PRINCE  qq 1 2 0 0 2 9 1 2 1
	$smarty->assign('mobile_color',    $mobile_shop_header_color?$mobile_shop_header_color:'#000000');
	
    //分解首页三类商品的显示数量
    $index_goods_num[0] = 6;
    $index_goods_num[1] = 6;
    $index_goods_num[2] = 6;// Mod by q q 1 200 29 121  20160922
    if(!empty($GLOBALS['_CFG']['shop_index_num'])){
    	$index_goods_info = explode("\r\n",$GLOBALS['_CFG']['shop_index_num']);
    	if(is_array($index_goods_info) && count($index_goods_info) >= 3){
    		$index_goods_num = $index_goods_info;
    	}
    }// 今天优品多商户系统 Mod by PRINCE QQ 120029121
	
    //1,2,3对应店铺商品分类中的精品,最新，热门
    $smarty->assign('best_goods',      get_supplier_goods(1,$index_goods_num[0]));    // 精品商品
    $smarty->assign('new_goods',       get_supplier_goods(2,$index_goods_num[1]));     // 最新商品
    $smarty->assign('hot_goods',       get_supplier_goods(3,$index_goods_num[2]));     // 热门商品
    //$smarty->assign('top_goods',       get_top10());           // 销售排行
    //$smarty->assign('new_articles',    index_get_new_articles());   // 最新文章
    $smarty->assign('category_goods',       get_supplier_category_info());     // 首页推荐分类商品
	 $smarty->assign('extpintuan_goods',   index_get_extpintuan($supplier_id));      // 拼团商品 QQ 120029121
    $smarty->assign('cut_goods',   index_get_cut($supplier_id));      // 砍价商品 QQ 30948   5552     寒  冰
	 $smarty->assign('lucky_buy_goods',   index_get_lucky_buy($supplier_id));
	
	$smarty->assign('supplier_id',        $supplier_id); 
    /* links */
    $smarty->assign('data_dir',        DATA_DIR);       // 数据目录
    //宝贝数量
    $goods_num = $db->getOne("select count(*) from ".$yp->table("goods")." where is_delete = 0 and is_on_sale= 1 and is_virtual = 0 and supplier_id = ".$_GET['suppId']);
    // 获取评分
    $sql1 = "SELECT AVG(comment_rank) FROM " . $GLOBALS['yp']->table('comment') . " c" . " LEFT JOIN " . $GLOBALS['yp']->table('order_info') . " o"." ON o.order_id = c.order_id"." WHERE c.status > 0 AND  o.supplier_id = " .$_GET['suppId'];
    $avg_comment = $GLOBALS['db']->getOne($sql1);
    $avg_comment = number_format(round($avg_comment),1);		

    $sql2 = "SELECT AVG(server), AVG(shipping) FROM " . $GLOBALS['yp']->table('shop_grade') . " s" . " LEFT JOIN " . $GLOBALS['yp']->table('order_info') . " o"." ON o.order_id = s.order_id"." WHERE s.is_comment > 0 AND  s.server >0 AND o.supplier_id = " .$_GET['suppId'];
    $row = $GLOBALS['db']->getRow($sql2);

    $avg_server = number_format(round($row['AVG(server)']),1);
    $avg_shipping = number_format(round($row['AVG(shipping)']),1);
    $haoping = round((($avg_comment+$avg_server+$avg_shipping)/3)/5,2)*100;
    $smarty->assign('goods_number',$goods_num);
    $smarty->assign('comment_rand',$avg_comment);
    $smarty->assign('server',$avg_server);
    $smarty->assign('pingfen',round((($avg_comment+$avg_server+$avg_shipping)/3),0));
    $smarty->assign('shipping',$avg_shipping);
    $smarty->assign('suppinfo',$suppinfo);
    $customers = is_customers(CUSTOMER_SERVICE, $_GET['suppId']);
    $smarty->assign('customers',        $customers);
    
    /* 首页推荐分类 */
    $cat_recommend_res = $db->getAll("SELECT c.cat_id, c.cat_name, cr.recommend_type FROM " . $yp->table("cat_recommend") . " AS cr INNER JOIN " . $yp->table("category") . " AS c ON cr.cat_id=c.cat_id");
    if (!empty($cat_recommend_res))
    {
        $cat_rec_array = array();
        foreach($cat_recommend_res as $cat_recommend_data)
        {
            $cat_rec[$cat_recommend_data['recommend_type']][] = array('cat_id' => $cat_recommend_data['cat_id'], 'cat_name' => $cat_recommend_data['cat_name']);
        }
        $smarty->assign('cat_rec', $cat_rec);
    }
  // 获取轮播图
    $playerdb = get_flash_xml();
    $smarty->assign('playerdb',$playerdb);
    /* 页面中的动态内容 */
    assign_dynamic('mall');
}
$smarty->display('mall.dwt', $cache_id);

/*------------------------------------------------------ */
//-- PRIVATE FUNCTIONS
/*------------------------------------------------------ */

/**
 * 获得最新的文章列表。
 *
 * @access  private
 * @return  array
 */
function index_get_new_articles()
{
    $sql = 'SELECT a.article_id, a.title, ac.cat_name, a.add_time, a.file_url, a.open_type, ac.cat_id, ac.cat_name ' .
            ' FROM ' . $GLOBALS['yp']->table('supplier_article') . ' AS a, ' .
                $GLOBALS['yp']->table('supplier_article_cat') . ' AS ac' .
            ' WHERE a.is_open = 1 AND a.cat_id = ac.cat_id AND ac.cat_id ='.$_GET['suppId'] .
            ' ORDER BY a.article_type DESC, a.add_time DESC LIMIT ' . $GLOBALS['_CFG']['article_number'];
    $res = $GLOBALS['db']->getAll($sql);

    $arr = array();
    foreach ($res AS $idx => $row)
    {
        $arr[$idx]['id']          = $row['article_id'];
        $arr[$idx]['title']       = $row['title'];
        $arr[$idx]['short_title'] = $GLOBALS['_CFG']['article_title_length'] > 0 ?
                                        sub_str($row['title'], $GLOBALS['_CFG']['article_title_length']) : $row['title'];
        $arr[$idx]['cat_name']    = $row['cat_name'];
        $arr[$idx]['add_time']    = local_date($GLOBALS['_CFG']['date_format'], $row['add_time']);
        $arr[$idx]['url']         = $row['open_type'] != 1 ?
                                        build_uri('supplier', array('go'=>'article','suppid'=>$_GET['suppId'],'aid' => $row['article_id']), $row['title']) : trim($row['file_url']);
        $arr[$idx]['cat_url']     = build_uri('article_cat', array('acid' => $row['cat_id']), $row['cat_name']);
    }

    return $arr;
}


function get_supplier_goods($gtype=0,$limit=6){
	$gtype = intval($gtype);
	if($gtype <= 0){
		return ;
	}elseif($gtype == 1){
		$gtype=" g.is_best=1 ";
	}elseif($gtype == 2){
		$gtype=" g.is_new=1 ";
	}elseif($gtype == 3){
		$gtype=" g.is_hot=1 ";
	}//// Mod by q q 1 200 29 121  20160922
	
	$sql = "SELECT DISTINCT g.goods_id,g.* FROM ". $GLOBALS['yp']->table('goods') ." AS g ".
	" WHERE ".$gtype." AND g.supplier_status = 1 AND g.supplier_id =".$_GET['suppId']."  
	 AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 
	 ORDER BY g.sort_order, g.last_update DESC LIMIT ".$limit; // Mod by q q 1 200 29 121  20160922
	//2017.02.27  寒冰  qq  309485552   修复  商家未审核 商品显示在mobile前台 bug
	$result = $GLOBALS['db']->getAll($sql);
	
	$goods = array();
	if($result){
		foreach ($result AS $idx => $row)
        {
            if ($row['promote_price'] > 0)
            {
                $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                $goods[$idx]['promote_price'] = $promote_price > 0 ? price_format($promote_price) : '';
            }
            else
            {
                $goods[$idx]['promote_price'] = '';
            }
            $final_price = get_final_price($row['goods_id'], 1, true, array());
            $goods[$idx]['final_price']     = price_format($final_price);
            $goods[$idx]['is_exclusive']  = is_exclusive($row['exclusive'],$final_price);
            $goods[$idx]['id']           = $row['goods_id'];
            $goods[$idx]['name']         = $row['goods_name'];
            $goods[$idx]['brief']        = $row['goods_brief'];
            $goods[$idx]['brand_name']   = isset($goods_data['brand'][$row['goods_id']]) ? $goods_data['brand'][$row['goods_id']] : '';
            $goods[$idx]['goods_style_name']   = add_style($row['goods_name'],$row['goods_name_style']);

            $goods[$idx]['short_name']   = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                                               sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
            $goods[$idx]['short_style_name']   = add_style($goods[$idx]['short_name'],$row['goods_name_style']);
            $goods[$idx]['market_price'] = price_format($row['market_price']);
            $goods[$idx]['shop_price']   = price_format($row['shop_price']);
            $goods[$idx]['thumb']        = get_image_path($row['goods_id'], $row['goods_thumb'], true);
            $goods[$idx]['goods_img']    = get_image_path($row['goods_id'], $row['goods_img']);
            $goods[$idx]['url']          = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
        }
	}
	return $goods;
	
}

/*商家首页砍价*/
function index_get_cut($supplier_id)
{
 
    $time = gmtime();
    $limit = get_library_number('cut', 'index');

    $cut_list = array();
    if ($limit > 0)
    {
        $sql = 'SELECT gb.*,g.goods_id, g.goods_name, g.shop_price, g.goods_brief, g.goods_thumb, goods_img, g.shop_price ,g.market_price ' .
                'FROM ' . $GLOBALS['yp']->table('goods_activity') . ' AS gb, ' .
                    $GLOBALS['yp']->table('goods') . ' AS g ' .
                "WHERE gb.act_type = '" . GAT_CUT . "' " .
                "AND g.goods_id = gb.goods_id " .
                "AND gb.start_time <= '" . $time . "' " .
				"AND gb.supplier_id = '" . $supplier_id . "' " .
                "AND gb.end_time >= '" . $time . "' " .
                "AND g.is_delete = 0 " .
                "ORDER BY gb.act_id DESC " .
                "LIMIT $limit" ;
        $res = $GLOBALS['db']->query($sql);
		
		$userid=$_SESSION['user_id'];//  QQ  120029121 

        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            /* 如果缩略图为空，使用默认图片 */
            $row['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);//mod by qq120-029-121 20160815
            $row['thumb'] =  get_image_path($row['goods_id'], $row['goods_thumb'], true);//mod by qq120-029-121 20160815

            /* 根据价格阶梯，计算最低价 */
            $ext_info = unserialize($row['ext_info']);
            $price_ladder = $ext_info['price_ladder'];
            foreach ($price_ladder AS $amount_price){
                 $final_price = $amount_price['price'];
				 break;
            }
			$market_price=$ext_info['market_price']?$ext_info['market_price']:$row['market_price'];
            $row['final_price'] = price_format($final_price);
            $row['market_price'] = price_format($market_price);
            $row['url'] = 'cut.php?act=view&id='.$row['act_id'].'&u='.$userid; // QQ  120029121 
            $row['name']   = $row['act_name']?$row['act_name']:$row['goods_name'];
            $cut_list[] = $row;
        }
    }

    return $cut_list;
}
function index_get_extpintuan($supplier_id)
{
    $time = gmtime();
    $limit = get_library_number('extpintuan', 'index');

    $extpintuan_list = array();
    if ($limit > 0)
    {
        $sql = 'SELECT gb.*,g.goods_id, g.goods_name, g.shop_price, g.goods_brief, g.goods_thumb, goods_img, g.shop_price ,g.market_price ' .
                'FROM ' . $GLOBALS['yp']->table('goods_activity') . ' AS gb, ' .
                    $GLOBALS['yp']->table('goods') . ' AS g ' .
                "WHERE gb.act_type = '" . GAT_EXTPINTUAN . "' " .
                "AND g.goods_id = gb.goods_id " .
                "AND gb.start_time <= '" . $time . "' " .
                "AND gb.end_time >= '" . $time . "' " .
				"AND gb.supplier_id = '" . $supplier_id . "' " .
                "AND g.is_delete = 0 " .
                "ORDER BY gb.act_id DESC " .
                "LIMIT $limit" ;
        $res = $GLOBALS['db']->query($sql);
		
		$userid=$_SESSION['user_id'];//  QQ  120029121 

        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            /* 如果缩略图为空，使用默认图片 */
            $row['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);//mod by qq120-029-121 20160815
            $row['thumb'] =  get_image_path($row['goods_id'], $row['goods_thumb'], true);//mod by qq120-029-121 20160815

            /* 根据价格阶梯，计算最低价 */
            $ext_info = unserialize($row['ext_info']);
            $price_ladder = $ext_info['price_ladder'];
            foreach ($price_ladder AS $amount_price){
                 $final_price = $amount_price['price'];
				 break;
            }
			$market_price=$ext_info['market_price']?$ext_info['market_price']:$row['market_price'];
            $row['final_price'] = price_format($final_price);
            $row['market_price'] = price_format($market_price);
            $row['url'] = 'extpintuan.php?act=view&act_id='.$row['act_id'].'&u='.$userid; // QQ  120029121 
            $row['name']   = $row['act_name']?$row['act_name']:$row['goods_name'];
            $extpintuan_list[] = $row;
        }
    }

    return $extpintuan_list;
}
function index_get_lucky_buy($supplier_id)
{
    $time = gmtime();
    $limit = get_library_number('lucky_buy', 'index');

    $lucky_buy_list = array();
    if ($limit > 0)
    {
        $sql = 'SELECT gb.*,g.goods_id, g.goods_name, g.shop_price, g.goods_brief, g.goods_thumb, goods_img, g.shop_price ,g.market_price ' .
                'FROM ' . $GLOBALS['yp']->table('goods_activity') . ' AS gb, ' .
                    $GLOBALS['yp']->table('goods') . ' AS g ' .
                "WHERE gb.act_type = '" . GAT_LUCKY_BUY . "' " .
                "AND g.goods_id = gb.goods_id " .
                "AND gb.start_time <= '" . $time . "' " .
                "AND gb.end_time >= '" . $time . "' " .
				"AND gb.supplier_id = '" . $supplier_id . "' " .
                "AND g.is_delete = 0 " .
                "ORDER BY gb.act_id DESC " .
                "LIMIT $limit" ;
        $res = $GLOBALS['db']->query($sql);
		
		$userid=$_SESSION['user_id'];//  QQ  120029121 

        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            /* 如果缩略图为空，使用默认图片 */
            $row['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);//mod by qq120-029-121 20160815
            $row['thumb'] =  get_image_path($row['goods_id'], $row['goods_thumb'], true);//mod by qq120-029-121 20160815

            /* 根据价格阶梯，计算最低价 */
            $ext_info = unserialize($row['ext_info']);
            $price_ladder = $ext_info['price_ladder'];
            foreach ($price_ladder AS $amount_price){
                 $final_price = $amount_price['price'];
				 break;
            }
			$market_price=$ext_info['market_price']?$ext_info['market_price']:$row['market_price'];
            $row['final_price'] = price_format($final_price);
            $row['market_price'] = price_format($market_price);
            $row['url'] = 'lucky_buy.php?act=view&act_id='.$row['act_id'].'&u='.$userid; // QQ  120029121 
            $row['name']   = $row['act_name']?$row['act_name']:$row['goods_name'];
            $lucky_buy_list[] = $row;
        }
    }

    return $lucky_buy_list;
}


//获取轮播图 
function get_flash_xml()
{
    $flash_file = "flash_data_supplier".$_GET['suppId'].".xml";
    $flashdb = array();
    $pc_root_path = str_replace('/mobile','',ROOT_PATH);
    if (file_exists($pc_root_path . DATA_DIR . '/'.$flash_file))
    {

        // 兼容v2.7.0及以前版本
        if (!preg_match_all('/item_url="([^"]+)"\slink="([^"]+)"\stext="([^"]*)"\ssort="([^"]*)"/', file_get_contents($pc_root_path . DATA_DIR . '/'.$flash_file), $t, PREG_SET_ORDER))
        {
            preg_match_all('/item_url="([^"]+)"\slink="([^"]+)"\stext="([^"]*)"/', file_get_contents($pc_root_path . DATA_DIR . '/'.$flash_file), $t, PREG_SET_ORDER);
        }
        if (!empty($t))
        {
            foreach ($t as $key => $val)
            {
                $val[4] = isset($val[4]) ? $val[4] : 0;
                $flashdb[] = array('src'=>$val[1],'url'=>$val[2],'text'=>$val[3],'sort'=>$val[4]);
            }
        }
    }
    return $flashdb;
}
/**
 * 获取本店铺首页要显示的分类
 */
function get_supplier_category_info(){
	$sql = "select cat_id,cat_name,cat_pic,cat_pic_url,cat_goods_limit from ". $GLOBALS['yp']->table('supplier_category') ." where 
	supplier_id=".$_GET['suppId']." and is_show=1 and is_show_cat_pic=1 order by sort_order desc";
	$result = $GLOBALS['db']->getAll($sql);
	if($result){
		foreach($result as $key => $row){
			$result[$key]['goods'] = get_supplier_category_goods($row['cat_id'],$row['cat_goods_limit']);
		}
	}
	return $result;
}

/*
 * 首页推荐分类中商品显示
 * @param int $catid  分类id
 * @param int $limit  分类下首页显示的商品id
 */
function get_supplier_category_goods($catid=0,$limit=10){
	
	$sql = "SELECT DISTINCT g.goods_id,g.* FROM ". $GLOBALS['yp']->table('goods') ." AS g, ". $GLOBALS['yp']->table('supplier_goods_cat') ." AS gc  
	WHERE gc.cat_id =".$catid." AND gc.supplier_id =".$_GET['suppId']." AND gc.goods_id = g.goods_id 
	AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 
	ORDER BY g.goods_id DESC LIMIT ".$limit;
	
	$result = $GLOBALS['db']->getAll($sql);
	
	$goods = array();
	if($result){
		foreach ($result AS $idx => $row)
        {
            if ($row['promote_price'] > 0)
            {
                $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
                $goods[$idx]['promote_price'] = $promote_price > 0 ? price_format($promote_price) : '';
            }
            else
            {
                $goods[$idx]['promote_price'] = '';
            }

            $goods[$idx]['id']           = $row['goods_id'];
            $goods[$idx]['name']         = $row['goods_name'];
            $goods[$idx]['brief']        = $row['goods_brief'];
            $goods[$idx]['brand_name']   = isset($goods_data['brand'][$row['goods_id']]) ? $goods_data['brand'][$row['goods_id']] : '';
            $goods[$idx]['goods_style_name']   = add_style($row['goods_name'],$row['goods_name_style']);

            $goods[$idx]['short_name']   = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
            sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
            $goods[$idx]['short_style_name']   = add_style($goods[$idx]['short_name'],$row['goods_name_style']);
            $goods[$idx]['market_price'] = price_format($row['market_price']);
            $goods[$idx]['shop_price']   = price_format($row['shop_price']);
            $goods[$idx]['thumb']        = get_image_path($row['goods_id'], $row['goods_thumb'], true);
            $goods[$idx]['goods_img']    = get_image_path($row['goods_id'], $row['goods_img']);
            $goods[$idx]['original_img'] = get_image_path($row['goods_id'], $row['original_img']);
            $goods[$idx]['url']          = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
        }
	}
	
	return $goods;
	
}
?>