<?php

/**
 * QQ120029121 积分商城
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: PRINCE $
 * $Id: exchange.php 17217 2017-04-01 06:29:08Z PRINCE $
*/

define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}

/*------------------------------------------------------ */
//-- act 操作项的初始化
/*------------------------------------------------------ */
if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'list';
}

/*------------------------------------------------------ */
//-- PROCESSOR
/*------------------------------------------------------ */

/*------------------------------------------------------ */
//-- 积分兑换商品列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    /* 初始化分页信息 */
    $page         = isset($_REQUEST['page'])   && intval($_REQUEST['page'])  > 0 ? intval($_REQUEST['page'])  : 1;
    $size         = isset($_CFG['page_size'])  && intval($_CFG['page_size']) > 0 ? intval($_CFG['page_size']) : 10;
    $cat_id       = isset($_REQUEST['cat_id']) && intval($_REQUEST['cat_id']) > 0 ? intval($_REQUEST['cat_id']) : 0;
    $integral_max = isset($_REQUEST['integral_max']) && intval($_REQUEST['integral_max']) > 0 ? intval($_REQUEST['integral_max']) : 0;
    $integral_min = isset($_REQUEST['integral_min']) && intval($_REQUEST['integral_min']) > 0 ? intval($_REQUEST['integral_min']) : 0;

    /* 排序、显示方式以及类型 */
    $default_display_type      = $_CFG['show_order_type'] == '0' ? 'list' : ($_CFG['show_order_type'] == '1' ? 'grid' : 'text');
    $default_sort_order_method = $_CFG['sort_order_method'] == '0' ? 'DESC' : 'ASC';
    $default_sort_order_type   = $_CFG['sort_order_type'] == '0' ? 'goods_id' : ($_CFG['sort_order_type'] == '1' ? 'exchange_integral' : 'last_update');

    $sort    = (isset($_REQUEST['sort'])  && in_array(trim(strtolower($_REQUEST['sort'])), array('goods_id', 'exchange_integral', 'last_update','click_count'))) ? trim($_REQUEST['sort'])  : $default_sort_order_type;
    $order   = (isset($_REQUEST['order']) && in_array(trim(strtoupper($_REQUEST['order'])), array('ASC', 'DESC')))                              ? trim($_REQUEST['order']) : $default_sort_order_method;
    $display = (isset($_REQUEST['display']) && in_array(trim(strtolower($_REQUEST['display'])), array('list', 'grid', 'text'))) ? trim($_REQUEST['display'])  : (isset($_COOKIE['YP']['display']) ? $_COOKIE['YP']['display'] : $default_display_type);
    $display  = in_array($display, array('list', 'grid', 'text')) ? $display : 'text';
    setcookie('YP[display]', $display, gmtime() + 86400 * 7);

    /* 页面的缓存ID */
    $cache_id = sprintf('%X', crc32($cat_id . '-' . $display . '-' . $sort  .'-' . $order  .'-' . $page . '-' . $size . '-' . $_SESSION['user_rank'] . '-' .
        $_CFG['lang'] . '-' . $integral_max . '-' .$integral_min));

    if (!$smarty->is_cached('exchange_list.dwt', $cache_id))
    {
        /* 如果页面没有被缓存则重新获取页面的内容 */

        $children = get_children($cat_id);

        $cat = get_cat_info($cat_id);   // 获得分类的相关信息

        if (!empty($cat))
        {
            $smarty->assign('keywords',    htmlspecialchars($cat['keywords']));
            $smarty->assign('description', htmlspecialchars($cat['cat_desc']));
        }

        assign_template();

        $position = assign_ur_here('exchange');
        $smarty->assign('page_title',       $position['title']);    // 页面标题
        $smarty->assign('ur_here',          $position['ur_here']);  // 当前位置

        $smarty->assign('categories',       get_categories_tree());        // 分类树
        $smarty->assign('helps',            get_shop_help());              // 网店帮助
        $smarty->assign('top_goods',        get_top10());                  // 销售排行
        $smarty->assign('promotion_info',   get_promotion_info());         // 促销活动信息
		

        /* 调查 */
        $vote = get_vote();
        if (!empty($vote))
        {
            $smarty->assign('vote_id',     $vote['id']);
            $smarty->assign('vote',        $vote['content']);
        }

        $ext = ''; //商品查询条件扩展

        //$smarty->assign('best_goods',      get_exchange_recommend_goods('best', $children, $integral_min, $integral_max));
        //$smarty->assign('new_goods',       get_exchange_recommend_goods('new',  $children, $integral_min, $integral_max));
        $smarty->assign('hot_goods',       get_exchange_recommend_goods('hot',  $children, $integral_min, $integral_max));


        $count = get_exchange_goods_count($children, $integral_min, $integral_max);
        $max_page = ($count> 0) ? ceil($count / $size) : 1;
        if ($page > $max_page)
        {
            $page = $max_page;
        }
        $goodslist = exchange_get_goods($children, $integral_min, $integral_max, $ext, $size, $page, $sort, $order);
        if($display == 'grid')
        {
            if(count($goodslist) % 2 != 0)
            {
                $goodslist[] = array();
            }
        }
        $smarty->assign('goods_list',       $goodslist);
        $smarty->assign('category',         $cat_id);
        $smarty->assign('integral_max',     $integral_max);
        $smarty->assign('integral_min',     $integral_min);


        assign_pager('exchange',            $cat_id, $count, $size, $sort, $order, $page, '', '', $integral_min, $integral_max, $display); // 分页
        assign_dynamic('exchange_list'); // 动态内容
    }

    $smarty->assign('feed_url',         ($_CFG['rewrite'] == 1) ? "feed-typeexchange.xml" : 'feed.php?type=exchange'); // RSS URL
    $smarty->display('exchange_list.dwt', $cache_id);
}

/*------------------------------------------------------ */
//-- 积分兑换商品详情
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'view')
{
    $goods_id = isset($_REQUEST['id'])  ? intval($_REQUEST['id']) : 0;

    $cache_id = $goods_id . '-' . $_SESSION['user_rank'] . '-' . $_CFG['lang'] . '-exchange';
    $cache_id = sprintf('%X', crc32($cache_id));

    if (!$smarty->is_cached('exchange_goods.dwt', $cache_id))
    {
        $smarty->assign('image_width',  $_CFG['image_width']);
        $smarty->assign('image_height', $_CFG['image_height']);
        $smarty->assign('helps',        get_shop_help()); // 网店帮助
        $smarty->assign('id',           $goods_id);
        $smarty->assign('type',         0);
        $smarty->assign('cfg',          $_CFG);
		$sql_attr_qq_wx_120029121="SELECT a.attr_id, ga.goods_attr_id FROM ". $GLOBALS['yp']->table('attribute') . " AS a left join ". $GLOBALS['yp']->table('goods_attr') . "  AS ga on a.attr_id=ga.attr_id  WHERE a.is_attr_gallery=1 and ga.goods_id='" . $goods_id. "' order by ga.goods_attr_id ";
$goods_attr=$GLOBALS['db']->getRow($sql_attr_qq_wx_120029121);
if($goods_attr){
	$goods_attr_id=$goods_attr['goods_attr_id'];
	$smarty->assign('attr_id', $goods_attr['attr_id']);
}else{
	$smarty->assign('attr_id', 0);
}//20161223 prince qq-12-00-29-12-1

if (!empty($_REQUEST['act']) && $_REQUEST['act'] == 'get_gallery_attr')
{
	include('includes/cls_json.php');
	$json = new JSON;

	$goods_attr_id=$_REQUEST['goods_attr_id'];
	$gallery_list_qq_wx_120029121=get_goods_gallery_attr_qq_wx_120029121($goods_id, $goods_attr_id);
	$gallery_content=array();
	$gallery_content['thumblist'] ='<ul>';
	foreach($gallery_list_qq_wx_120029121 as $gkey=>$gval)
	{
		$gallery_content['thumblist'] .= '<li>';
		$gallery_content['thumblist'] .= '<a  href="'. $gval['img_original'] . '" rel="zoom-id: zoom; zoom-height: 360px;zoom-width:400px;"  rev="'.$gval['img_url'].'" name="'.$gval['img_url'].'" rev="'. $gval['img_original'] . '" ';

		$gallery_content['thumblist'] .= '><img src="'. ($gval['thumb_url'] ? $gval['thumb_url'] : $gval['img_url']) . '" class="B_blue" ';
		$gallery_content['thumblist'] .= '  /></a></li>';
		if ($gkey==0)
		{
			$gallery_content['bigimg'] = $gval['img_original'] ;
			$gallery_content['middimg'] .= $gval['img_url'] ;
		}
	}
	$gallery_content['thumblist'] .='</ul>';

	die ($json->encode($gallery_content));

}


if (!empty($_REQUEST['act']) && $_REQUEST['act'] == 'get_products_info')
{
include('includes/cls_json.php');

$json = new JSON;
// $res = array('err_msg' => '', 'result' => '', 'qty' => 1);

$spce_id = $_GET['id'];
$goods_id = $_GET['goods_id'];
$row = get_products_info($goods_id,explode(",",$spce_id));
//$res = array('err_msg'=>$goods_id,'id'=>$spce_id);
die($json->encode($row));

}

        /* 获得商品的信息 */
        $goods = get_exchange_goods_info($goods_id);

        if ($goods === false)
        {
            /* 如果没有找到任何记录则跳回到首页 */
            yp_header("Location: ./\n");
            exit;
        }
        else
        {
            if ($goods['brand_id'] > 0)
            {
                $goods['goods_brand_url'] = build_uri('brand', array('bid'=>$goods['brand_id']), $goods['goods_brand']);
            }
			
			/* 代码增加_start  By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 20161223 */
			$goods['supplier_name'] ="网站自营";
			 if ($goods['supplier_id'] > 0)
			 {
				 $sql_supplier = "SELECT s.supplier_id,s.supplier_name,s.add_time,sr.rank_name FROM ". $yp->table("supplier") . " as s left join ". $yp->table("supplier_rank") ." as sr ON s.rank_id=sr.rank_id
	 WHERE s.supplier_id=".$goods[supplier_id]." AND s.status=1";
				 $shopuserinfo = $db->getRow($sql_supplier);
				 $goods['supplier_name']= $shopuserinfo['supplier_name'];
				 get_dianpu_baseinfo($goods['supplier_id'],$shopuserinfo);
			 }
			/* 代码增加_end  By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 20161223 */

            $goods['goods_style_name'] = add_style($goods['goods_name'], $goods['goods_name_style']);

            $smarty->assign('goods',              $goods);
            $smarty->assign('goods_id',           $goods['goods_id']);
            $smarty->assign('categories',         get_categories_tree());  // 分类树

            /* meta */
            $smarty->assign('keywords',           htmlspecialchars($goods['keywords']));
            $smarty->assign('description',        htmlspecialchars($goods['goods_brief']));

			$count1 = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " . $GLOBALS['yp']->table('comment') . " where comment_type=0 and id_value ='$goods_id' and status=1");
			$smarty->assign('review_count',       $count1); 

			//评价晒单 增加 by demo.coolhong.com 今 天 优 品 多 商 户 系 统 q q 1 2 0 0 2 9 1 2 1
			$rank_num['rank_a'] = $db->getOne("SELECT COUNT(*) AS num FROM ".$yp->table('comment')." WHERE id_value = '$goods_id' AND status = 1 AND comment_rank in (5,4)");
			$rank_num['rank_b'] = $db->getOne("SELECT COUNT(*) AS num FROM ".$yp->table('comment')." WHERE id_value = '$goods_id' AND status = 1 AND comment_rank in (3,2)");
			$rank_num['rank_c'] = $db->getOne("SELECT COUNT(*) AS num FROM ".$yp->table('comment')." WHERE id_value = '$goods_id' AND status = 1 AND comment_rank = 1");
			$rank_num['rank_total'] = $rank_num['rank_a'] + $rank_num['rank_b'] + $rank_num['rank_c'];
			$rank_num['rank_pa'] = ($rank_num['rank_a'] > 0) ? round(($rank_num['rank_a'] / $rank_num['rank_total']) * 100,1) : 0;
			$rank_num['rank_pb'] = ($rank_num['rank_b'] > 0) ? round(($rank_num['rank_b'] / $rank_num['rank_total']) * 100,1) : 0;
			$rank_num['rank_pc'] = ($rank_num['rank_c'] > 0) ? round(($rank_num['rank_c'] / $rank_num['rank_total']) * 100,1) : 0;
			$rank_num['shaidan_num'] = $db->getOne("SELECT COUNT(*) AS num FROM ".$yp->table('shaidan')." WHERE goods_id = '$goods_id' AND status = 1");
			$smarty->assign('rank_num',$rank_num);

            assign_template();

            /* 上一个商品下一个商品 */
            $sql = "SELECT eg.goods_id FROM " .$yp->table('exchange_goods'). " AS eg," . $GLOBALS['yp']->table('goods') . " AS g WHERE eg.goods_id = g.goods_id AND eg.goods_id > " . $goods['goods_id'] . " AND eg.is_exchange = 1 AND g.is_delete = 0 LIMIT 1";
            $prev_gid = $db->getOne($sql);
            if (!empty($prev_gid))
            {
                $prev_good['url'] = build_uri('exchange_goods', array('gid' => $prev_gid), $goods['goods_name']);
                $smarty->assign('prev_good', $prev_good);//上一个商品
            }

            $sql = "SELECT max(eg.goods_id) FROM " . $yp->table('exchange_goods') . " AS eg," . $GLOBALS['yp']->table('goods') . " AS g WHERE eg.goods_id = g.goods_id AND eg.goods_id < ".$goods['goods_id'] . " AND eg.is_exchange = 1 AND g.is_delete = 0";
            $next_gid = $db->getOne($sql);
            if (!empty($next_gid))
            {
                $next_good['url'] = build_uri('exchange_goods', array('gid' => $next_gid), $goods['goods_name']);
                $smarty->assign('next_good', $next_good);//下一个商品
            }

            /* current position */
            $position = assign_ur_here('exchange', $goods['goods_name']);
            $smarty->assign('page_title',          $position['title']);                    // 页面标题
            $smarty->assign('ur_here',             $position['ur_here']);                  // 当前位置

            $properties = get_goods_properties($goods_id);  // 获得商品的规格和属性
            $smarty->assign('properties',          $properties['pro']);                              // 商品属性
			$goodstype = "select goods_type from ".$yp->table('goods')." where goods_id='". $goods_id ."'";
		$goodstype_one = $db->getOne($goodstype);
		
				/* 代码增加_start  By  demo.coolhong.com 今天优品多商户系统 Q Q 1200 2912 1 */	
		$sql_zhyh_qq2211707 = "select attr_id from ".$yp->table('attribute')." where cat_id='". $goodstype_one ."' and is_attr_gallery='1' ";
		$attr_id_gallery = $db->getOne($sql_zhyh_qq2211707);
		
		$sql = "SELECT goods_attr_id, attr_value FROM " . $GLOBALS['yp']->table('goods_attr') . " WHERE goods_id = '$goods_id'";
		$results_qq_wx_120029121 = $GLOBALS['db']->getAll($sql);
		$return_arr = array();
		foreach ($results_qq_wx_120029121 as $value_wx_120029121)
		{
			$return_arr[$value_wx_120029121['goods_attr_id']] = $value_wx_120029121['attr_value'];
		}
		$prod_options_arr=array();
		
		$prod_exist_arr = array();
		$sql_prod  = "select goods_attr from ". $GLOBALS['yp']->table('products') ." where product_number>0 and goods_id='$goods_id' order by goods_attr";
		$res_prod = $db->query($sql_prod);
		while ($row_prod = $GLOBALS['db']->fetchRow($res_prod))
		{
			$prod_exist_arr[] = "|". $row_prod['goods_attr'] ."|";			
		}
		$GLOBALS['smarty']->assign('prod_exist_arr', $prod_exist_arr);

		$selected_first = array();

		foreach ($properties['spe'] AS $skey_wx_120029121=>$sval_wx_120029121)
		{
			$hahaha_zhyh = 0;
			$sskey_qq_wx_120029121 = '-1';
			foreach ($sval_wx_120029121['values'] AS $sskey_wx_120029121=>$ssval_wx_120029121)
			{				
				if ( is_exist_prod($selected_first, $ssval_wx_120029121['id'], $prod_exist_arr))
				{ 
					$hahaha_zhyh = $hahaha_zhyh ? $hahaha_zhyh : $ssval_wx_120029121['id'];
					$sskey_qq_wx_120029121 = ($sskey_qq_wx_120029121 != '-1') ? $sskey_qq_wx_120029121 : $sskey_wx_120029121;
				}
				else
				{
					$properties['spe'][$skey_wx_120029121]['values'][$sskey_wx_120029121]['disabled'] = "disabled";
				}

				if ($skey_wx_120029121==$attr_id_gallery)
				{
					$goods_attr_id_qq2211707 = $ssval_wx_120029121['id'] ;
					$sql_qq2211707_qq87139667 = "select  thumb_url from ". $yp->table('goods_gallery'). " where goods_id='$goods_id' and goods_attr_id='$goods_attr_id_qq2211707' and is_attr_image='1' ";
					$properties['spe'][$skey_wx_120029121]['values'][$sskey_wx_120029121]['goods_attr_thumb'] = $db->getOne($sql_qq2211707_qq87139667);
				}
			}
			if ($hahaha_zhyh)
			{
				$selected_first[$skey_wx_120029121] =  $hahaha_zhyh;
			}
			if ($sskey_qq_wx_120029121!='-1')
			{
				$properties['spe'][$skey_wx_120029121]['values'][$sskey_qq_wx_120029121]['selected_key_wx_120029121'] = "1";
			}
		}
		$smarty->assign('is_goods_page', 1);
		//echo '<pre>';
		//print_r($properties['spe']);
		//echo '</pre>';
		/* 代码增加_end  By  demo.coolhong.com 今天优品多商户系统 Q Q 1200 2912 1 */
            $smarty->assign('specification',       $properties['spe']);                              // 商品规格

            $smarty->assign('pictures',            get_goods_gallery_attr_qq_wx_120029121($goods_id, $goods_attr_id)); // 商品相册_修改 By demo.coolhong.com 今天优品多商户系统 Q Q 1200 2912 1
			$smarty->assign('new_goods',           get_recommend_goods('new'));     // 最新商品  改 By demo.coolhong.com 今天优品多商户系统 Q Q 1200 2912 1
			$smarty->assign('url',              $_SERVER["REQUEST_URI"]);
            assign_dynamic('exchange_goods');
        }
    }

    $smarty->display('exchange_goods.dwt',      $cache_id);
}

/*------------------------------------------------------ */
//--  兑换
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'buy')
{
    /* 查询：判断是否登录 */
    if (!isset($back_act) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
    {
        $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'exchange') ? $GLOBALS['_SERVER']['HTTP_REFERER'] : './index.php';
    }

    /* 查询：判断是否登录 */
    if ($_SESSION['user_id'] <= 0)
    {
        show_message($_LANG['eg_error_login'], array($_LANG['back_up_page']), array($back_act), 'error');
    }

    /* 查询：取得参数：商品id */
    $goods_id = isset($_POST['goods_id']) ? intval($_POST['goods_id']) : 0;
    if ($goods_id <= 0)
    {
        yp_header("Location: ./\n");
        exit;
    }

    /* 查询：取得兑换商品信息 */
    $goods = get_exchange_goods_info($goods_id);
    if (empty($goods))
    {
        yp_header("Location: ./\n");
        exit;
    }
    /* 查询：检查兑换商品是否有库存 */
    if($goods['goods_number'] == 0 && $_CFG['use_storage'] == 1)
    {
        show_message($_LANG['eg_error_number'], array($_LANG['back_up_page']), array($back_act), 'error');
    }
    /* 查询：检查兑换商品是否是取消 */
    if ($goods['is_exchange'] == 0)
    {
        show_message($_LANG['eg_error_status'], array($_LANG['back_up_page']), array($back_act), 'error');
    }

    $user_info   = get_user_info($_SESSION['user_id']);
    $user_points = $user_info['pay_points']; // 用户的积分总数
    if ($goods['exchange_integral'] > $user_points)
    {
        show_message($_LANG['eg_error_integral'], array($_LANG['back_up_page']), array($back_act), 'error');
    }

    /* 查询：取得规格 */
    $specs = '';
    foreach ($_POST as $key => $value)
    {
        if (strpos($key, 'value_spec_') !== false)
        {
            $specs .= ',' . intval($value);
        }
    }
    $specs = trim($specs, ',');
    /* 查询：如果商品有规格则取规格商品信息 配件除外 */
    if (!empty($specs))
    {
        $_specs = explode(',', $specs);

        $product_info = get_products_info($goods_id, $_specs);
    }
    if (empty($product_info))
    {
        $product_info = array('product_number' => '', 'product_id' => 0);
    }

    //查询：商品存在规格 是货品 检查该货品库存
    if((!empty($specs)) && ($product_info['product_number'] == 0) && ($_CFG['use_storage'] == 1))
    {
        show_message($_LANG['eg_error_number'], array($_LANG['back_up_page']), array($back_act), 'error');
    }

    /* 查询：查询规格名称和值，不考虑价格 */
    $attr_list = array();
    $sql = "SELECT a.attr_name, g.attr_value " .
            "FROM " . $yp->table('goods_attr') . " AS g, " .
                $yp->table('attribute') . " AS a " .
            "WHERE g.attr_id = a.attr_id " .
            "AND g.goods_attr_id " . db_create_in($specs);
    $res = $db->query($sql);
    while ($row = $db->fetchRow($res))
    {
        $attr_list[] = $row['attr_name'] . ': ' . $row['attr_value'];
    }
    $goods_attr = join(chr(13) . chr(10), $attr_list);

    /* 更新：清空购物车中所有团购商品 */
    include_once(ROOT_PATH . 'includes/lib_order.php');
    clear_cart(CART_EXCHANGE_GOODS);

    /* 更新：加入购物车 */
    $number = 1;
    $cart = array(
        'user_id'        => $_SESSION['user_id'],
        'session_id'     => SESS_ID,
        'goods_id'       => $goods['goods_id'],
        'product_id'     => $product_info['product_id'],
        'goods_sn'       => addslashes($goods['goods_sn']),
        'goods_name'     => addslashes($goods['goods_name']),
        'market_price'   => $goods['market_price'],
        'goods_price'    => 0,//$goods['exchange_integral']
        'goods_number'   => $number,
        'goods_attr'     => addslashes($goods_attr),
        'goods_attr_id'  => $specs,
        'is_real'        => $goods['is_real'],
        'extension_code' => addslashes($goods['extension_code']),
        'parent_id'      => 0,
        'rec_type'       => CART_EXCHANGE_GOODS,
        'is_gift'        => 0
    );
    $db->autoExecute($yp->table('cart'), $cart, 'INSERT');
	$_SESSION['sel_cartgoods'] = $db->insert_id();
    /* 记录购物流程类型：团购 */
    $_SESSION['flow_type'] = CART_EXCHANGE_GOODS;
    $_SESSION['extension_code'] = 'exchange_goods';
    $_SESSION['extension_id'] = $goods_id;
	

    /* 进入收货人页面 */
    yp_header("Location: ./flow.php?step=checkout\n");
    exit;
}

/*------------------------------------------------------ */
//-- PRIVATE FUNCTION
/*------------------------------------------------------ */

/**
 * 获得分类的信息
 *
 * @param   integer $cat_id
 *
 * @return  void
 */
function get_cat_info($cat_id)
{
    return $GLOBALS['db']->getRow('SELECT keywords, cat_desc, style, grade, filter_attr, parent_id FROM ' . $GLOBALS['yp']->table('category') .
        " WHERE cat_id = '$cat_id'");
}

/**
 * 获得分类下的商品
 *
 * @access  public
 * @param   string  $children
 * @return  array
 */
function exchange_get_goods($children, $min, $max, $ext, $size, $page, $sort, $order)
{
    $display = $GLOBALS['display'];
    $where = "eg.is_exchange = 1 AND g.is_delete = 0 AND ".
             "($children OR " . get_extension_goods($children) . ')';

    if ($min > 0)
    {
        $where .= " AND eg.exchange_integral >= $min ";
    }

    if ($max > 0)
    {
        $where .= " AND eg.exchange_integral <= $max ";
    }

    /* 获得商品列表 */
    $sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, eg.exchange_integral, ' .
                'g.goods_type, g.goods_brief, g.goods_thumb , g.goods_img, eg.is_hot ' .
            'FROM ' . $GLOBALS['yp']->table('exchange_goods') . ' AS eg, ' .$GLOBALS['yp']->table('goods') . ' AS g ' .
            "WHERE eg.goods_id = g.goods_id AND $where $ext ORDER BY $sort $order";

	$res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);

    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        /* 处理商品水印图片 */
        $watermark_img = '';

//        if ($row['is_new'] != 0)
//        {
//            $watermark_img = "watermark_new_small";
//        }
//        elseif ($row['is_best'] != 0)
//        {
//            $watermark_img = "watermark_best_small";
//        }
//        else
        if ($row['is_hot'] != 0)
        {
            $watermark_img = 'watermark_hot_small';
        }

        if ($watermark_img != '')
        {
            $arr[$row['goods_id']]['watermark_img'] =  $watermark_img;
        }

        $arr[$row['goods_id']]['goods_id']          = $row['goods_id'];
        if($display == 'grid')
        {
            $arr[$row['goods_id']]['goods_name']    = $GLOBALS['_CFG']['goods_name_length'] > 0 ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
        }
        else
        {
            $arr[$row['goods_id']]['goods_name']    = $row['goods_name'];
        }
        $arr[$row['goods_id']]['name']              = $row['goods_name'];
        $arr[$row['goods_id']]['goods_brief']       = $row['goods_brief'];
        $arr[$row['goods_id']]['goods_style_name']  = add_style($row['goods_name'],$row['goods_name_style']);
        $arr[$row['goods_id']]['exchange_integral'] = $row['exchange_integral'];
        $arr[$row['goods_id']]['type']              = $row['goods_type'];
        $arr[$row['goods_id']]['goods_thumb']       = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $arr[$row['goods_id']]['goods_img']         = get_image_path($row['goods_id'], $row['goods_img']);
        $arr[$row['goods_id']]['url']               = build_uri('exchange_goods', array('gid'=>$row['goods_id']), $row['goods_name']);
    }

    return $arr;
}

/**
 * 获得分类下的商品总数
 *
 * @access  public
 * @param   string     $cat_id
 * @return  integer
 */
function get_exchange_goods_count($children, $min = 0, $max = 0, $ext='')
{
    $where  = "eg.is_exchange = 1 AND g.is_delete = 0 AND ($children OR " . get_extension_goods($children) . ')';


    if ($min > 0)
    {
        $where .= " AND eg.exchange_integral >= $min ";
    }

    if ($max > 0)
    {
        $where .= " AND eg.exchange_integral <= $max ";
    }

    $sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['yp']->table('exchange_goods') . ' AS eg, ' .
           $GLOBALS['yp']->table('goods') . " AS g WHERE eg.goods_id = g.goods_id AND $where $ext";

    /* 返回商品总数 */
    return $GLOBALS['db']->getOne($sql);
}

/**
 * 获得指定分类下的推荐商品
 *
 * @access  public
 * @param   string      $type       推荐类型，可以是 best, new, hot, promote
 * @param   string      $cats       分类的ID
 * @param   integer     $min        商品积分下限
 * @param   integer     $max        商品积分上限
 * @param   string      $ext        商品扩展查询
 * @return  array
 */
function get_exchange_recommend_goods($type = '', $cats = '', $min =0,  $max = 0, $ext='')
{
    $price_where = ($min > 0) ? " AND g.shop_price >= $min " : '';
    $price_where .= ($max > 0) ? " AND g.shop_price <= $max " : '';

    $sql =  'SELECT g.goods_id, g.goods_name, g.goods_name_style, eg.exchange_integral, ' .
                'g.goods_brief, g.goods_thumb, goods_img, b.brand_name ' .
            'FROM ' . $GLOBALS['yp']->table('exchange_goods') . ' AS eg ' .
            'LEFT JOIN ' . $GLOBALS['yp']->table('goods') . ' AS g ON g.goods_id = eg.goods_id ' .
            'LEFT JOIN ' . $GLOBALS['yp']->table('brand') . ' AS b ON b.brand_id = g.brand_id ' .
            'WHERE eg.is_exchange = 1 AND g.is_delete = 0 ' . $price_where . $ext;
    $num = 0;
    $type2lib = array('best'=>'exchange_best', 'new'=>'exchange_new', 'hot'=>'exchange_hot');
    $num = get_library_number($type2lib[$type], 'exchange_list');

    switch ($type)
    {
        case 'best':
            $sql .= ' AND eg.is_best = 1';
            break;
        case 'new':
            $sql .= ' AND eg.is_new = 1';
            break;
        case 'hot':
            $sql .= ' AND eg.is_hot = 1';
            break;
    }

    if (!empty($cats))
    {
        $sql .= " AND (" . $cats . " OR " . get_extension_goods($cats) .")";
    }
    $order_type = $GLOBALS['_CFG']['recommend_order'];
    $sql .= ($order_type == 0) ? ' ORDER BY g.sort_order, g.last_update DESC' : ' ORDER BY RAND()';
    $res = $GLOBALS['db']->selectLimit($sql, $num);

    $idx = 0;
    $goods = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $goods[$idx]['id']                = $row['goods_id'];
        $goods[$idx]['name']              = $row['goods_name'];
        $goods[$idx]['brief']             = $row['goods_brief'];
        $goods[$idx]['brand_name']        = $row['brand_name'];
        $goods[$idx]['short_name']        = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                                                sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
        $goods[$idx]['exchange_integral'] = $row['exchange_integral'];
        $goods[$idx]['thumb']             = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $goods[$idx]['goods_img']         = get_image_path($row['goods_id'], $row['goods_img']);
        $goods[$idx]['url']               = build_uri('exchange_goods', array('gid' => $row['goods_id']), $row['goods_name']);

        $goods[$idx]['short_style_name']  = add_style($goods[$idx]['short_name'], $row['goods_name_style']);
        $idx++;
    }

    return $goods;
}

/**
 * 获得积分兑换商品的详细信息
 *
 * @access  public
 * @param   integer     $goods_id
 * @return  void
 */
function get_exchange_goods_info($goods_id)
{
    $time = gmtime();
    $sql = 'SELECT g.*, c.measure_unit, b.brand_id, b.brand_name AS goods_brand, eg.exchange_integral, eg.is_exchange ' .
            'FROM ' . $GLOBALS['yp']->table('goods') . ' AS g ' .
            'LEFT JOIN ' . $GLOBALS['yp']->table('exchange_goods') . ' AS eg ON g.goods_id = eg.goods_id ' .
            'LEFT JOIN ' . $GLOBALS['yp']->table('category') . ' AS c ON g.cat_id = c.cat_id ' .
            'LEFT JOIN ' . $GLOBALS['yp']->table('brand') . ' AS b ON g.brand_id = b.brand_id ' .
            "WHERE g.goods_id = '$goods_id' AND g.is_delete = 0 " .
            'GROUP BY g.goods_id';

    $row = $GLOBALS['db']->getRow($sql);

    if ($row !== false)
    {
        /* 处理商品水印图片 */
        $watermark_img = '';

        if ($row['is_new'] != 0)
        {
            $watermark_img = "watermark_new";
        }
        elseif ($row['is_best'] != 0)
        {
            $watermark_img = "watermark_best";
        }
        elseif ($row['is_hot'] != 0)
        {
            $watermark_img = 'watermark_hot';
        }

        if ($watermark_img != '')
        {
            $row['watermark_img'] =  $watermark_img;
        }

        /* 修正重量显示 */
        $row['goods_weight']  = (intval($row['goods_weight']) > 0) ?
            $row['goods_weight'] . $GLOBALS['_LANG']['kilogram'] :
            ($row['goods_weight'] * 1000) . $GLOBALS['_LANG']['gram'];

        /* 修正上架时间显示 */
        $row['add_time']      = local_date($GLOBALS['_CFG']['date_format'], $row['add_time']);

        /* 修正商品图片 */
        $row['goods_img']   = get_image_path($goods_id, $row['goods_img']);
        $row['goods_thumb'] = get_image_path($goods_id, $row['goods_thumb'], true);

        return $row;
    }
    else
    {
        return false;
    }
}

/* 代码增加_start By demo.coolhong.com 今天优品多商户系统 Q Q 1200 2912 1 */
/**
 * 获得指定商品的相册
 *
 * @access  public
 * @param   integer     $goods_id
 * @return  array
 */
function get_goods_gallery_attr_qq_wx_120029121($goods_id, $goods_attr_id)
{

    $sql = 'SELECT img_id, img_original, img_url, thumb_url, img_desc' .
        ' FROM ' . $GLOBALS['yp']->table('goods_gallery') .
        " WHERE goods_id = '$goods_id' and goods_attr_id='$goods_attr_id' LIMIT " . $GLOBALS['_CFG']['goods_gallery_number'];
    $row = $GLOBALS['db']->getAll($sql);
	if (count($row)==0)
	{
		$sql = 'SELECT img_id, img_original, img_url, thumb_url, img_desc' .
        ' FROM ' . $GLOBALS['yp']->table('goods_gallery') .
        " WHERE goods_id = '$goods_id' and goods_attr_id='0' LIMIT " . $GLOBALS['_CFG']['goods_gallery_number'];
		$row = $GLOBALS['db']->getAll($sql);
	}
    /* 格式化相册图片路径 */
    foreach($row as $key => $gallery_img)
    {
        $row[$key]['img_url'] = get_image_path($goods_id, $gallery_img['img_url'], false, 'gallery');
        $row[$key]['thumb_url'] = get_image_path($goods_id, $gallery_img['thumb_url'], true, 'gallery');
		$row[$key]['img_original'] = get_image_path($goods_id, $gallery_img['img_original'], true, 'gallery');
    }
    return $row;
}

/* 代码增加_end By demo.coolhong.com 今天优品多商户系统 Q Q 1200 2912 1 */
/* 20161223
 * 获取商品所对应店铺的店铺基本信息
 * @param int $suppid 店铺id
 * @param int $suppinfo 入驻商的信息
 */
function get_dianpu_baseinfo($suppid=0,$suppinfo){
	if(intval($suppid) <= 0){
		return ;
	}
	global $smarty;
	$sql = "SELECT * FROM " .$GLOBALS['yp']->table('supplier_shop_config'). " WHERE supplier_id = " . $suppid;
        $shopinfo = $GLOBALS['db']->getAll($sql);

        $_goods_attr = array();
        foreach ($shopinfo as $value)
        {
            $_goods_attr[$value['code']] = $value['value'];
        }
//代码增加 
		$sql1 = "SELECT AVG(comment_rank) FROM " . $GLOBALS['yp']->table('comment') . " c" . " LEFT JOIN " . $GLOBALS['yp']->table('order_info') . " o"." ON o.order_id = c.order_id"." WHERE c.status > 0 AND  o.supplier_id = " . $suppid;
		$avg_comment = $GLOBALS['db']->getOne($sql1);
		$avg_comment = round($avg_comment,1);		
		
		$sql2 = "SELECT AVG(server), AVG(shipping) FROM " . $GLOBALS['yp']->table('shop_grade') . " s" . " LEFT JOIN " . $GLOBALS['yp']->table('order_info') . " o"." ON o.order_id = s.order_id"." WHERE s.is_comment > 0 AND  s.server >0 AND o.supplier_id = " . $suppid;
		$row = $GLOBALS['db']->getRow($sql2);

		$avg_server = round($row['AVG(server)'],1);
		$avg_shipping = round($row['AVG(shipping)'],1);
		
		$sql3 = " SELECT c.comment_rank,s.send,s.shipping FROM ".$GLOBALS['yp']->table('shop_grade') ." AS s ".
				" LEFT JOIN ". $GLOBALS['yp']->table('comment') ." AS c ON c.order_id = s.order_id " .
				" LEFT JOIN ". $GLOBALS['yp']->table('order_info') ." AS o ON o.order_id = s.order_id".
				" WHERE s.is_comment >0 AND  s.server >0 AND o.supplier_id = " . $suppid;
		
		$h = $GLOBALS['db']->getAll($sql3);
		foreach($h as $key=>$value)
		{
			$count += array_sum($value);
		}

		$haoping = (($count/3)/count($h))/5*100;
		$haoping = round($haoping,1);

//代码增加 

    $smarty->assign('ghs_css_path',        'themes/'.$_goods_attr['template'].'/images/ghs/css/ghs_style.css');//入驻商所选模板样式路径
    $shoplogo = empty($_goods_attr['shop_logo']) ? 'themes/'.$_goods_attr['template'].'/images/dianpu.jpg' : $_goods_attr['shop_logo'];
    $smarty->assign('shoplogo',        $shoplogo);//商家logo
    $smarty->assign('shopname',        htmlspecialchars($_goods_attr['shop_name']));//店铺名称
    $smarty->assign('suppid',        $suppinfo['supplier_id']);//商家名称
    $smarty->assign('suppliername',        htmlspecialchars($suppinfo['supplier_name']));//商家名称
    $smarty->assign('userrank',        htmlspecialchars($suppinfo['rank_name']));//商家等级
   	$smarty->assign('region', get_province_city($_goods_attr['shop_province'],$_goods_attr['shop_city']));
	$smarty->assign('address', $_goods_attr['shop_address']);
    /* 代码修改 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 Start */
    $_goods_attr['qq'] = explode(',', $_goods_attr['qq']);
    $_goods_attr['ww'] = explode(',', $_goods_attr['ww']);
    
    //zhouhui 判断是否入住商品再分析显示联系QQ
    if($suppinfo['supplier_name']){
	    $smarty->assign('serviceqq', $_goods_attr['qq']);
	}
	else
	{
		$qq = $GLOBALS['db']->getAll("SELECT cus_no FROM " . $GLOBALS['yp']->table('chat_third_customer') . " WHERE is_master = 1 AND cus_type = 0 AND supplier_id = $suppid");
	    $arr_qq = array();
	    foreach ($qq as $v)
	    {
	        $arr_qq[] = $v['cus_no'];
	    }
		$smarty->assign('serviceqq', $arr_qq);
	}
	//zhouhui 判断是否入住商品再分析显示联系旺旺
	if($suppinfo['supplier_name']){
	    $smarty->assign('serviceww', $_goods_attr['ww']);
	}
	else
	{
		$ww = $GLOBALS['db']->getAll("SELECT cus_no FROM " . $GLOBALS['yp']->table('chat_third_customer') . " WHERE is_master = 1 AND cus_type = 1 AND supplier_id = $suppid");
	    $arr_ww = array();
	    foreach ($ww as $v)
	    {
	        $arr_ww[] = $v['cus_no'];
	    }
		$smarty->assign('serviceww', $arr_ww);
	}
    /* 代码修改 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 End */
	$smarty->assign('serviceemail', $_goods_attr['service_email']);
	$smarty->assign('servicephone', $_goods_attr['service_phone']);
    $smarty->assign('createtime',      gmdate('Y-m-d',$suppinfo['add_time']));//商家创建时间

	//代码增加 
	$smarty->assign('c_rank', $avg_comment);
	$smarty->assign('serv_rank', $avg_server);
	$smarty->assign('shipp_rank', $avg_shipping);
	$smarty->assign('haoping', $haoping);
	//代码增加 
    $suppid = (intval($suppid)>0) ? intval($suppid) : intval($_GET['suppId']);
}
?>