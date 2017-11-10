<?php

/**
 * QQ120029121 首页文件
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: prince $
 * $Id: index.php 17217 2017-04-01 06:29:08Z prince $
*/

define('IN_PRINCE', true);



require(dirname(__FILE__) . '/includes/init.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}
if (isset($_REQUEST['is_c']))
{
    $is_c = intval($_REQUEST['is_c']);
	if($is_c == 1){

    header("Location:../index.php?is_c=1"); 
	}
}




/*------------------------------------------------------ */
//-- 
/*------------------------------------------------------ */
if (!empty($_GET['gOo']))
{
    if (!empty($_GET['gcat']))
    {
        /* 商品分类。*/
        $Loaction = 'category.php?id=' . $_GET['gcat'];
    }
    elseif (!empty($_GET['acat']))
    {
        /* 文章分类。*/
        $Loaction = 'article_cat.php?id=' . $_GET['acat'];
    }
    elseif (!empty($_GET['goodsid']))
    {
        /* 商品详情。*/
        $Loaction = 'goods.php?id=' . $_GET['goodsid'];
    }
    elseif (!empty($_GET['articleid']))
    {
        /* 文章详情。*/
        $Loaction = 'article.php?id=' . $_GET['articleid'];
    }

    if (!empty($Loaction))
    {
        yp_header("Location: $Loaction\n");

        exit;
    }
}

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
$cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-' . $_CFG['lang']));

if (!$smarty->is_cached('index.dwt', $cache_id))
{
    assign_template();

	
	
    $position = assign_ur_here();
    $smarty->assign('page_title',      $position['title']);    // 页面标题
    $smarty->assign('ur_here',         $position['ur_here']);  // 当前位置

    /* meta information */
    $smarty->assign('keywords',        htmlspecialchars($_CFG['shop_keywords']));
    $smarty->assign('description',     htmlspecialchars($_CFG['shop_desc']));
    $smarty->assign('flash_theme',     $_CFG['flash_theme']);  // Flash轮播图片模板

    $smarty->assign('feed_url',        ($_CFG['rewrite'] == 1) ? 'feed.xml' : 'feed.php'); // RSS URL

    //$smarty->assign('categories',      get_categories_tree()); // 分类树
    //$smarty->assign('helps',           get_shop_help());       // 网店帮助
    //$smarty->assign('top_goods',       get_top10());           // 销售排行


	
	if($_REQUEST['cat_id']){
    	$smarty->assign('cat_id',       intval($_REQUEST['cat_id']));   
    	$children = get_children($_REQUEST['cat_id']);
   		$goodslist = category_get_goods($children, $other_has, $other_youhuo, $brand, $price_min, $price_max, '', $size, $page, $sort='sort_order', $order='ASC', $limit = 'limit 200' );
    	$smarty->assign('cat_goods',       $goodslist );   
	}else{
    	$smarty->assign('cat_id',       0);     
		$smarty->assign('best_goods',      get_recommend_goods('best'));    // 推荐商品
		$smarty->assign('new_goods',       get_recommend_goods('new'));     // 最新商品
		$smarty->assign('hot_goods',       get_recommend_goods('hot'));     // 热点商品
	}
	
	
	
    $smarty->assign('promotion_goods', get_promote_goods()); // 促销商品
    $smarty->assign('brand_list',      get_brands());
    //$smarty->assign('promotion_info',  get_promotion_info()); // 增加一个动态显示所有促销信息的标签栏

    //$smarty->assign('invoice_list',    index_get_invoice_query());  // 发货查询
    //$smarty->assign('new_articles',    index_get_new_articles());   // 最新文章
    //$smarty->assign('group_buy_goods', index_get_group_buy());      // 团购商品
    //$smarty->assign('auction_list',    index_get_auction());        // 拍卖活动
    //$smarty->assign('shop_notice',     $_CFG['shop_notice']);       // 商店公告
    $smarty->assign('extpintuan_goods',   index_get_extpintuan());      // 拼团商品 QQ 120029121
    $smarty->assign('cut_goods',   index_get_cut());      // 砍价商品
	$smarty->assign('lucky_buy_goods',   index_get_lucky_buy());
	$smarty->assign("ROOTPATH", "");
    $smarty->assign('supplier_list', get_supplier_city());//附近商家

	$smarty->assign('wap_index_ad',get_wap_advlist('wap首页幻灯广告', 5));  //wap首页幻灯广告位
	
    $smarty->assign('wap_index_img',get_wap_advlist('手机端首页精品推荐广告', 5));  //wap首页幻灯广告位

    $category = $GLOBALS['db']->getAll("SELECT * FROM ".$GLOBALS['yp']->table('category')." WHERE 	is_show=1 AND parent_id=0 ORDER BY sort_order ASC,cat_id ASC ");
    $smarty->assign('category', $category);   

	 
	 $smarty->assign('menu_list',get_menu());
	

    $guide_qrcode = $GLOBALS['db']->getOne("SELECT guide_qrcode FROM ".$GLOBALS['yp']->table('weixin_config'));
    $web_url =HTTP_TYPE.'://'.$_SERVER['HTTP_HOST'];
    $smarty->assign('guide_qrcode',      $web_url.'/'.$guide_qrcode );
	
    /* links */
    //$links = index_get_links();
    //$smarty->assign('img_links',       $links['img']);
    //$smarty->assign('txt_links',       $links['txt']);
    //$smarty->assign('data_dir',        DATA_DIR);       // 数据目录
	
	
	/*添加首页幻灯插件*/	
	$smarty->assign("flash",get_flash_xml());
	$smarty->assign('flash_count',count(get_flash_xml()));


    /* 页面中的动态内容 */
    assign_dynamic('index');
}

$smarty->display('index.dwt', $cache_id);




/*------------------------------------------------------ */
//-- PRIVATE FUNCTIONS
/*------------------------------------------------------ */

/**
 * 调用发货单查询
 *
 * @access  private
 * @return  array
 */
function index_get_invoice_query()
{
    $sql = 'SELECT o.order_sn, o.invoice_no, s.shipping_code FROM ' . $GLOBALS['yp']->table('order_info') . ' AS o' .
            ' LEFT JOIN ' . $GLOBALS['yp']->table('shipping') . ' AS s ON s.shipping_id = o.shipping_id' .
            " WHERE invoice_no > '' AND shipping_status = " . SS_SHIPPED .
            ' ORDER BY shipping_time DESC LIMIT 10';
    $all = $GLOBALS['db']->getAll($sql);

    foreach ($all AS $key => $row)
    {
        $plugin = ROOT_PATH . 'includes/modules/shipping/' . $row['shipping_code'] . '.php';

        if (file_exists($plugin))
        {
            include_once($plugin);

            $shipping = new $row['shipping_code'];
            $all[$key]['invoice_no'] = $shipping->query((string)$row['invoice_no']);
        }
    }

    clearstatcache();

    return $all;
}

/**
 * 获得最新的文章列表。
 *
 * @access  private
 * @return  array
 */
function index_get_new_articles()
{
    $sql = 'SELECT a.article_id, a.title, ac.cat_name, a.add_time, a.file_url, a.open_type, ac.cat_id, ac.cat_name ' .
            ' FROM ' . $GLOBALS['yp']->table('article') . ' AS a, ' .
                $GLOBALS['yp']->table('article_cat') . ' AS ac' .
            ' WHERE a.is_open = 1 AND a.cat_id = ac.cat_id AND ac.cat_type = 1' .
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
                                        build_uri('article', array('aid' => $row['article_id']), $row['title']) : trim($row['file_url']);
        $arr[$idx]['cat_url']     = build_uri('article_cat', array('acid' => $row['cat_id']), $row['cat_name']);
    }

    return $arr;
}

/**
 * 获得最新的团购活动
 *
 * @access  private
 * @return  array
 */
function index_get_group_buy()
{
    $time = gmtime();
    $limit = get_library_number('group_buy', 'index');
	
    $group_buy_list = array();
    if ($limit > 0)
    {
        $sql = 'SELECT gb.*,g.*,gb.act_id AS group_buy_id, gb.goods_id, gb.ext_info, gb.goods_name, g.goods_thumb, g.goods_img ' .
                'FROM ' . $GLOBALS['yp']->table('goods_activity') . ' AS gb, ' .
                    $GLOBALS['yp']->table('goods') . ' AS g ' .
                "WHERE gb.act_type = '" . GAT_GROUP_BUY . "' " .
                "AND g.goods_id = gb.goods_id " .
                "AND gb.start_time <= '" . $time . "' " .
                "AND gb.end_time >= '" . $time . "' " .
                "AND g.is_delete = 0 " .
                "ORDER BY gb.act_id DESC " .
                "LIMIT $limit" ;
				
        $res = $GLOBALS['db']->query($sql);

        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            /* 如果缩略图为空，使用默认图片 */
            $row['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
            $row['thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);

            /* 根据价格阶梯，计算最低价 */
            $ext_info = unserialize($row['ext_info']);
            $price_ladder = $ext_info['price_ladder'];
            if (!is_array($price_ladder) || empty($price_ladder))
            {
                $row['last_price'] = price_format(0);
            }
            else
            {
                foreach ($price_ladder AS $amount_price)
                {
                    $price_ladder[$amount_price['amount']] = $amount_price['price'];
                }
            }
            ksort($price_ladder);
            $row['last_price'] = price_format(end($price_ladder));
            $row['url'] = build_uri('group_buy', array('gbid' => $row['group_buy_id']));
            $row['short_name']   = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                                           sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
            $row['short_style_name']   = add_style($row['short_name'],'');
			
			$stat = group_buy_stat($row['act_id'], $row['deposit']);
			$row['valid_goods'] = $stat['valid_goods'];
            $group_buy_list[] = $row;
        }
    }

    return $group_buy_list;
}

/**
 * 取得拍卖活动列表
 * @return  array
 */
function index_get_auction()
{
    $now = gmtime();
    $limit = get_library_number('auction', 'index');
    $sql = "SELECT a.act_id, a.goods_id, a.goods_name, a.ext_info, g.goods_thumb ".
            "FROM " . $GLOBALS['yp']->table('goods_activity') . " AS a," .
                      $GLOBALS['yp']->table('goods') . " AS g" .
            " WHERE a.goods_id = g.goods_id" .
            " AND a.act_type = '" . GAT_AUCTION . "'" .
            " AND a.is_finished = 0" .
            " AND a.start_time <= '$now'" .
            " AND a.end_time >= '$now'" .
            " AND g.is_delete = 0" .
            " ORDER BY a.start_time DESC" .
            " LIMIT $limit";
    $res = $GLOBALS['db']->query($sql);

    $list = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $ext_info = unserialize($row['ext_info']);
        $arr = array_merge($row, $ext_info);
        $arr['formated_start_price'] = price_format($arr['start_price']);
        $arr['formated_end_price'] = price_format($arr['end_price']);
        $arr['thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $arr['url'] = build_uri('auction', array('auid' => $arr['act_id']));
        $arr['short_name']   = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                                           sub_str($arr['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $arr['goods_name'];
        $arr['short_style_name']   = add_style($arr['short_name'],'');
        $list[] = $arr;
    }

    return $list;
}

/**
 * 获得所有的友情链接
 *
 * @access  private
 * @return  array
 */
function index_get_links()
{
    $sql = 'SELECT link_logo, link_name, link_url FROM ' . $GLOBALS['yp']->table('friend_link') . ' ORDER BY show_order';
    $res = $GLOBALS['db']->getAll($sql);

    $links['img'] = $links['txt'] = array();

    foreach ($res AS $row)
    {
        if (!empty($row['link_logo']))
        {
            $links['img'][] = array('name' => $row['link_name'],
                                    'url'  => $row['link_url'],
                                    'logo' => $row['link_logo']);
        }
        else
        {
            $links['txt'][] = array('name' => $row['link_name'],
                                    'url'  => $row['link_url']);
        }
    }

    return $links;
}

function get_flash_xml()
{
    $flashdb = array();
    if (file_exists(ROOT_PATH . DATA_DIR . '/flash_data.xml'))
    {

        // 兼容v2.7.0及以前版本
        if (!preg_match_all('/item_url="([^"]+)"\slink="([^"]+)"\stext="([^"]*)"\ssort="([^"]*)"/', file_get_contents(ROOT_PATH . DATA_DIR . '/flash_data.xml'), $t, PREG_SET_ORDER))
        {
            preg_match_all('/item_url="([^"]+)"\slink="([^"]+)"\stext="([^"]*)"/', file_get_contents(ROOT_PATH . DATA_DIR . '/flash_data.xml'), $t, PREG_SET_ORDER);
        }

        if (!empty($t))
        {
            foreach ($t as $key => $val)
            {
                $val[4] = isset($val[4]) ? $val[4] : 0;
                $flashdb[] = array('src'=>$val[1],'url'=>$val[2],'text'=>$val[3],'sort'=>$val[4]);
				
				//print_r($flashdb);
            }
        }
    }
    return $flashdb;
}

function get_wap_advlist( $position, $num )
{
		$arr = array( );
		$sql = "select ap.ad_width,ap.ad_height,ad.ad_id,ad.ad_name,ad.ad_code,ad.ad_link,ad.ad_id from ".$GLOBALS['yp']->table( "ypmart_ad_position" )." as ap left join ".$GLOBALS['yp']->table( "ypmart_ad" )." as ad on ad.position_id = ap.position_id where ap.position_name='".$position.( "' and UNIX_TIMESTAMP()>ad.start_time and UNIX_TIMESTAMP()<ad.end_time and ad.enabled=1 limit ".$num );
		$res = $GLOBALS['db']->getAll( $sql );
		foreach ( $res as $idx => $row )
		{
				$arr[$row['ad_id']]['name'] = $row['ad_name'];
				$arr[$row['ad_id']]['url'] = "affiche.php?ad_id=".$row['ad_id']."&uri=".$row['ad_link'];
				if(strpos($row['ad_code'],'http') === false){
					$arr[$row['ad_id']]['image'] = "../../data/afficheimg/".$row['ad_code'];
				}else{
					$arr[$row['ad_id']]['image'] =$row['ad_code'];
				}// 20170603 q120029121
				$arr[$row['ad_id']]['content'] = "<a href='".$arr[$row['ad_id']]['url']."' target='_blank'><img src='../../data/afficheimg/".$row['ad_code']."' width='".$row['ad_width']."' height='".$row['ad_height']."' /></a>";
				$arr[$row['ad_id']]['ad_code'] = $row['ad_code'];
		}
		return $arr;
}

function get_is_computer(){
$is_computer=$_REQUEST['is_computer'];
return $is_computer;
}

function get_menu()
{
	$sql = "select * from ".$GLOBALS['yp']->table('ypmart_menu')." order by sort";
	$list = $GLOBALS['db']->getAll($sql);
	$arr = array();
	foreach($list as $key => $rows)
	{
		$arr[$key]['id'] = $rows['id'];
		$arr[$key]['menu_name'] = $rows['menu_name'];
		$arr[$key]['menu_img'] = $rows['menu_img'];
		$arr[$key]['menu_url'] = $rows['menu_url']; 
	} 
	return $arr;
}

function index_get_extpintuan()
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

function index_get_cut()
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
function index_get_lucky_buy()
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



function get_address($lat, $lng){
    // 逆地址解析
    
    $key = "75LBZ-H4IWQ-DHP5D-GD7SQ-HTDIQ-67BNZ"; // 腾讯地图开发密钥

    $location = $lat.",".$lng;

    $url = "http://apis.map.qq.com/ws/geocoder/v1/?location=$location&key=$key&get_poi=1";

    $res = file_get_contents($url);

    $arr = json_decode($res, true);

    $address = $arr['result']['address'];

    $_SESSION['latitude'] = $lat;
    $_SESSION['longitude'] = $lng;
    $_SESSION['location_address'] = $address;
        
    return $address;
}


function get_supplier_city(){
    $limit = get_library_number('supplier_city', 'index');
	
	$supplier_list = array();

	if($limit > 0 && is_weixin_browser()){
		$user_id=$_SESSION['user_id']?$_SESSION['user_id']:0;
		$user_info = $GLOBALS['db']->getRow( "SELECT * FROM " . $GLOBALS['yp']->table('users') . " WHERE `user_id` = '$user_id'" );       
		$translate=txmap_translate($user_info['Latitude'], $user_info['Longitude']);
		$latitude = $translate['lat'];
		$longitude = $translate['lng'];
		if($latitude && $longitude){
			require_once(dirname(__FILE__) . '/includes/Geohash.php');
			
			// 获取所有店铺
			$sql="SELECT supplier_id,user_id,supplier_name,wx_latitude,wx_longitude FROM ". $GLOBALS['yp']->table("supplier") ." WHERE status=1 AND wx_latitude<>'' AND wx_longitude<>''";//20170101
			$supplier_list = $GLOBALS['db']->GetAll($sql);
		
			$geohash = new Geohash();
		
			foreach ($supplier_list as $key => $supplier) {
				// 获取距离
				$distance = $geohash->getDistance($latitude, $longitude, $supplier['wx_latitude'], $supplier['wx_longitude']);//20170101
				$supplier_list[$key]['distance'] = $distance;

				$unit = "m";
				if ($distance > 1000) {
					$unit = 'km';
					$distance = number_format($distance/1000, 1);
				}
				$supplier_list[$key]['distance_'] = $distance.$unit;
			}
		
			// 按距离排序
			usort($supplier_list, function($a, $b){
				return $a['distance'] > $b['distance'] ? 1 : -1;
			});
		
			$supplier_list = array_slice($supplier_list, 0, $limit);
		
			foreach ($supplier_list as $key => $supplier) {
				// 获取店铺配置
				$sql = "SELECT code,value FROM ". $GLOBALS['yp']->table("supplier_shop_config") ." WHERE supplier_id=".$supplier['supplier_id'];
					$supplier_config_list = $GLOBALS['db']->GetAll($sql);
		
				foreach ($supplier_config_list as $cof) {
					$code = $cof['code'];
					if ($code == 'shop_address' || $code == 'shop_name' || $code == 'shop_logo' || $code == "service_phone") {
						$supplier_list[$key][$code] = $cof['value'];
					}
					if ($code == 'shop_country' && $cof['value']){
						$supplier_list[$key]['shop_country'] = $GLOBALS['db']->getOne("SELECT region_name FROM ".$GLOBALS['yp']->table('region')." WHERE region_id=".$cof['value']);
					}
					if ($code == 'shop_province' && $cof['value']){
						$supplier_list[$key]['shop_province'] = $GLOBALS['db']->getOne("SELECT region_name FROM ".$GLOBALS['yp']->table('region')." WHERE region_id=".$cof['value']);
					}
					if ($code == 'shop_city' && $cof['value']){
						$supplier_list[$key]['shop_city'] = $GLOBALS['db']->getOne("SELECT region_name FROM ".$GLOBALS['yp']->table('region')." WHERE region_id=".$cof['value']);
					}
				}
		
				// 查询店铺下的商品
				$sql2 = "SELECT goods_id,goods_name,shop_price,market_price,goods_img,goods_thumb FROM ". $GLOBALS['yp']->table("goods") ." WHERE supplier_status = 1 and is_on_sale=1 and is_delete=0 and supplier_id='". $supplier['supplier_id'] ."' LIMIT 0,3";
				$goods_list = $GLOBALS['db']->GetAll($sql2);
				
				if(count($goods_list)<3){
					unset($supplier_list[$key]);
				}else{
					$supplier_list[$key]['goods_list'] = $goods_list;
				}
			}
		}
	}
	return $supplier_list;
}


/**
 * 获得分类下的商品
 *
 * @access  public
 * @param   string  $children
 * @return  array
 */
function category_get_goods($children, $other_has, $other_youhuo, $brand, $min, $max, $ext, $size, $page, $sort='sort order', $order='ASC', $limit = 'limit 20' ) {
    $display = $GLOBALS['display'];
    $where = "g.is_on_sale = 1 AND g.is_alone_sale = 1 AND " .
            "g.is_delete = 0 AND ($children OR " . get_extension_goods($children) . ')';

    if ($brand > 0) {
        $where .= "AND g.brand_id=$brand ";
    }

    if ($min >= 0 && $max > $min) {
        $where .= " AND (g.shop_price between $min AND $max) ";
    }

    if ($other_has > 0) {
        switch ($other_has) {
            case 1:
                break;
            case 2:
                $where .= " AND g.supplier_id = 0 ";
                break;
            case 3:
                $where .= " AND g.supplier_id > 0 ";
                break;
        }
    }
    if ($other_youhuo > 0) {
        $where .= " AND g.goods_number > 0 ";
    }
    /* 获得商品列表 */
    $sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, g.market_price, g.is_new, g.is_best, g.is_hot, g.shop_price AS org_price, ' .
            "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, g.promote_price, g.goods_type, g.exclusive, g.ghost_count," .
            'g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb , g.goods_img ' .
            'FROM ' . $GLOBALS['yp']->table('goods') . ' AS g ' .
            'LEFT JOIN ' . $GLOBALS['yp']->table('member_price') . ' AS mp ' .
            "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " .
            "WHERE $where $ext ORDER BY $sort $order,g.goods_id DESC $limit";
    /* 代码增加_start  By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 */
    if ($sort == 'salenum') {
        $sql = 'SELECT SUM(o.goods_number) as salenum, g.goods_id, g.goods_name, g.goods_name_style, g.market_price, g.is_new, g.is_best, g.is_hot, g.shop_price AS org_price, ' .
                "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, g.promote_price, g.goods_type, g.exclusive,g.ghost_count, " .
                'g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb , g.goods_img ' .
                'FROM ' . $GLOBALS['yp']->table('goods') . ' AS g ' .
                'LEFT JOIN ' . $GLOBALS['yp']->table('member_price') . ' AS mp ' .
                "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " .
                "LEFT JOIN " . $GLOBALS['yp']->table('order_goods') . " as o ON o.goods_id = g.goods_id " . 
                "WHERE $where $ext group by g.goods_id ORDER BY $sort $order $limit";
    }
    /* 根据真是价格排序 */
        if($sort=="final_price"){
            /* 获得商品列表 */
        $shop_price_sql =  "if(g.promote_start_date < ".gmtime()."  and g.promote_end_date >  ".gmtime()."  and g.promote_price > 0 and g.promote_price < IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]'), promote_price, IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]'))";
        $sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, g.market_price, g.is_new, g.is_best, g.is_hot, g.shop_price AS org_price,g.ghost_count, ' .
                " IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, ".
                "IF(g.exclusive > 0 and g.exclusive < $shop_price_sql , g.exclusive, $shop_price_sql ) as final_price, g.promote_price, g.goods_type, g.exclusive, " .
                'g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb , g.goods_img ' .
            'FROM ' . $GLOBALS['yp']->table('goods') . ' AS g ' .
            'LEFT JOIN ' . $GLOBALS['yp']->table('member_price') . ' AS mp ' .
            "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " .
            "WHERE $where $ext ORDER BY  cast($sort as DECIMAL( 10,2)) $order $limit";
        }
    /* 代码增加_end  By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 */

    if (empty($limit)) {
        $res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);
    } else {
        $res = $GLOBALS['db']->query($sql);
    }

    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res)) {
        if ($row['promote_price'] > 0) {
            $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
        } else {
            $promote_price = 0;
        }
        $final_price  = get_final_price($row['goods_id'], 1, false);
        /* 处理商品水印图片 */
        $watermark_img = '';

        if ($promote_price != 0) {
            $watermark_img = "watermark_promote_small";
        } elseif ($row['is_new'] != 0) {
            $watermark_img = "watermark_new_small";
        } elseif ($row['is_best'] != 0) {
            $watermark_img = "watermark_best_small";
        } elseif ($row['is_hot'] != 0) {
            $watermark_img = 'watermark_hot_small';
        }

        if ($watermark_img != '') {
            $arr[$row['goods_id']]['watermark_img'] = $watermark_img;
        }

        $arr[$row['goods_id']]['goods_id'] = $row['goods_id'];
        if ($display == 'grid') {
            $arr[$row['goods_id']]['goods_name'] = $GLOBALS['_CFG']['goods_name_length'] > 0 ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
        } else {
            $arr[$row['goods_id']]['goods_name'] = $row['goods_name'];
        }
        $arr[$row['goods_id']]['name'] = $row['goods_name'];
        $arr[$row['goods_id']]['goods_brief'] = $row['goods_brief'];
        $arr[$row['goods_id']]['goods_style_name'] = add_style($row['goods_name'], $row['goods_name_style']);
        $arr[$row['goods_id']]['market_price'] = price_format($row['market_price']);
        $arr[$row['goods_id']]['shop_price'] = price_format($row['shop_price']);
        $arr[$row['goods_id']]['type'] = $row['goods_type'];
        $arr[$row['goods_id']]['is_best'] = $row['is_best'];
        $arr[$row['goods_id']]['is_hot'] = $row['is_hot'];
        $arr[$row['goods_id']]['is_new'] = $row['is_new'];
        $arr[$row['goods_id']]['ghost_count'] = $row['ghost_count'];
        $arr[$row['goods_id']]['promote_price'] = ($promote_price > 0) ? price_format($promote_price) : '';
        $arr[$row['goods_id']]['goods_thumb'] =   get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $arr[$row['goods_id']]['goods_img'] =   get_image_path($row['goods_id'], $row['goods_img']);
        $arr[$row['goods_id']]['url'] = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
        $arr[$row['goods_id']]['wap_count'] = selled_wap_count($row['goods_id']);
        $arr[$row['goods_id']]['wap_pingjia'] = get_evaluation_sum($row['goods_id']);
        $arr[$row['goods_id']]['is_exclusive']  = is_exclusive($row['exclusive'],$final_price);
        $arr[$row['goods_id']]['final_price'] = price_format($final_price);
    }
    return $arr;
}

?>