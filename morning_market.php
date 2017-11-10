<?php

/**
 * QQ120029121 首页文件
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: Prince $
 * $Id: morning_market.php 17063 2010-03-25 06:35:46Z Prince $
*/

define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');

$cur_url = basename(PHP_SELF);
$filename = substr($cur_url, 0, -4);
$smarty->assign("filename",$filename);



	
$isd = local_date('G',gmtime());

if($isd >= 6 && $isd < 11){
	$endtime = local_date('Y-n-j',gmtime()).' 11:00:00';
	$sstart = 1;
}
elseif($isd>=11)
{
	$sstart = 2;
}
else
{
	$sstart = 3;
}

if($sstart < 3){

	$jstime = strtotime($endtime);
	$smarty->assign("jstime",$jstime-8*60*60);
	
}


$smarty->assign('sstart', $sstart);



if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}
/* 修改 by demo.coolhong.com 今 天 优 品 多 商 户 系 统 q q 1 2 0 0 2 9 1 2 1 start */

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
//-- 早市商品 --> 购买
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'buy')
{
    /* 查询：取得参数：早市商品id */
   
    $goods_id =  trim($_REQUEST['goods_id'])?trim($_REQUEST['goods_id']):0 ;
    $user_id=$_SESSION['user_id'];
  
	
  if ($goods_id <= 0){
        yp_header("Location: ./\n");
        exit;
    }

	//取得商品信息
	
	$sql = "select * from ".$GLOBALS['yp']->table('goods')." where is_morning_market=1  and is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0  AND goods_id = '$goods_id'";
	
	
	$row = $GLOBALS['db']->getRow($sql);

  

    /* 活动是否正在进行 */
	if (empty($row)){
        show_message('对不起！该商品没有加入早市销售！！', '', '', 'error');
    }
	
    if ($sstart != 1){
        show_message('对不起！活动今天的早市已结束，明天早点来哦！！', '', '', 'error');
    }
	


    /* 查询：是否登录 */
    if ($user_id <= 0){
		show_message($_LANG['au_buy_after_login'], "马上登陆", 'user.php', 'error');
    }

    /* 清空购物车中所有商品 */
    include_once(ROOT_PATH . 'includes/lib_order.php');
    clear_cart();

    /* 加入购物车 */
   $cart = array(
        'user_id'        => $user_id,
        'session_id'     => SESS_ID,
        'goods_id'       => $goods_id,
        'goods_sn'       => addslashes($row['goods_sn']),
        'goods_name'     => addslashes($row['goods_name']),
        'market_price'   => $row['market_price'],
        'goods_price'    => $row['morning_market_price'],
        'goods_number'   => 1,
		'is_real'        => $row['is_real'],
        'is_shipping'    =>$row['is_shipping'],
		'add_time'    => time(),
        'extension_code' => addslashes($row['extension_code']),
		 'rec_type'       => CART_MORNING_GOODS,
        'parent_id'      => 0,
        'is_gift'        => 0,
       
    );
    $db->autoExecute($yp->table('cart'), $cart, 'INSERT');
	$_SESSION['sel_cartgoods'] = $db->insert_id();
	
    /* 记录购物流程类型：早市 */
    $_SESSION['flow_type'] = CART_MORNING_GOODS;
    $_SESSION['extension_code'] = 'morning_market';
    $_SESSION['extension_id'] = $goods_id;
   
	
   
    /* 进入收货人页面 */
    yp_header("Location: ./flow.php?step=checkout\n");
    exit;
}



/*------------------------------------------------------ */
//-- 早市列表页面
/*------------------------------------------------------ */
  assign_template();

    $position = assign_ur_here('','早市');
    $smarty->assign('page_title',      $position['title']);    // 页面标题
    $smarty->assign('ur_here',         $position['ur_here']);  // 当前位置

    /* meta information */
    $smarty->assign('keywords',        htmlspecialchars($_CFG['shop_keywords']));
    $smarty->assign('description',     htmlspecialchars($_CFG['shop_desc']));
    $smarty->assign('flash_theme',     $_CFG['flash_theme']);  // Flash轮播图片模板

    $smarty->assign('feed_url',        ($_CFG['rewrite'] == 1) ? 'feed.xml' : 'feed.php'); // RSS URL

    $smarty->assign('categories',      get_categories_tree()); // 分类树
    $smarty->assign('helps',           get_shop_help());       // 网店帮助
    $smarty->assign('top_goods',       get_top10());           // 销售排行

    $smarty->assign('best_goods',      get_recommend_goods('best'));    // 推荐商品
    $smarty->assign('new_goods',       get_recommend_goods('new'));     // 最新商品
    $smarty->assign('hot_goods',       get_recommend_goods('hot'));     // 热点文章
    $smarty->assign('promotion_goods', get_promote_goods()); // 特价商品
    $smarty->assign('brand_list',      get_brands());
    $smarty->assign('promotion_info',  get_promotion_info()); // 增加一个动态显示所有促销信息的标签栏

    $smarty->assign('invoice_list',    index_get_invoice_query());  // 发货查询
    $smarty->assign('new_articles',    index_get_new_articles());   // 最新文章
    $smarty->assign('group_buy_goods', index_get_group_buy());      // 团购商品
    $smarty->assign('auction_list',    index_get_auction());        // 拍卖活动
    $smarty->assign('shop_notice',     $_CFG['shop_notice']);       // 商店公告
	$smarty->assign('cat_goods_list',  get_cat_goods_list());

    /* 首页主广告设置 */
    $smarty->assign('index_ad',     $_CFG['index_ad']);
    if ($_CFG['index_ad'] == 'cus')
    {
        $sql = 'SELECT ad_type, content, url FROM ' . $yp->table("ad_custom") . ' WHERE ad_status = 1';
        $ad = $db->getRow($sql, true);
        $smarty->assign('ad', $ad);
    }
	
	$index_goods_list = get_index_goods_list();
	$smarty->assign('index_goods_list',$index_goods_list);
    /* links */
    $links = index_get_links();
    $smarty->assign('img_links',       $links['img']);
    $smarty->assign('txt_links',       $links['txt']);
    $smarty->assign('data_dir',        DATA_DIR);       // 数据目录

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


$smarty->display('morning_market.dwt');

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
function get_index_goods_list()
{
	$city_id = $_SESSION['city_id'];
	$sql = "select goods_thumb,goods_img,goods_id,is_promote,is_best,is_new,goods_number,is_hot,morning_market_price,shop_price,market_price,goods_name,goods_brief from ".$GLOBALS['yp']->table('goods')." where is_morning_market=1  and is_on_sale = 1 AND is_alone_sale = 1 AND is_delete = 0  order by sort_order, goods_id desc";
	
	
	$row = $GLOBALS['db']->getAll($sql);
	$i =1;
	foreach($row as $idx=>$value)
	{
      $row[$idx]['short_name']   = sub_str($value['name'],46);
	  $row[$idx]['market_price'] = price_format($value['market_price']);
	  $row[$idx]['shop_price']   = price_format($value['shop_price']);
	  $row[$idx]['morning_market_price']   = price_format($value['morning_market_price']);
	  $row[$idx]['thumb']        = get_image_path($value['goods_id'], $value['goods_thumb'], true);
	  $row[$idx]['goods_img']    = get_image_path($value['goods_id'], $value['goods_img']);
	  $row[$idx]['url']          = build_uri('goods', array('gid' => $value['goods_id']), $value['goods_name']);
	  if($i%2 ==0)
	  {
	  	$row[$idx]['count'] =1;
	  }
	  else
	  {
	  	$row[$idx]['count'] =0;
	  }
	  $i++;
	  
	}

	return $row;

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

//LLX
function get_hot_cat_goods($type = '',$cat_id, $num = 7)
{
	$children = get_children($cat_id);

	$sql = 'SELECT g.goods_id,g.cat_id, g.goods_name, g.market_price, g.shop_price AS org_price, ' .
	"IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, ".
	'g.promote_price, promote_start_date, promote_end_date, g.goods_brief, g.goods_thumb, g.goods_img ' .
	"FROM " . $GLOBALS['yp']->table('goods') . ' AS g '.
	"LEFT JOIN " . $GLOBALS['yp']->table('member_price') . " AS mp ".
	"ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ".
	'WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND  g.is_delete = 0' ;

	switch ($type)
	{
		case 'best':
			$sql .= ' AND is_best = 1';
			break;
		case 'new':
			$sql .= ' AND is_new = 1';
			break;
		case 'hot':
			$sql .= ' AND is_hot = 1';
			break;
		case 'promote':
			$time = gmtime();
			$sql .= " AND is_promote = 1 AND promote_start_date <= '$time' AND promote_end_date >= '$time'";
			break;
	}

	$sql.=' AND (' . $children . 'OR ' . get_extension_goods($children) . ') ' .'ORDER BY g.sort_order, g.goods_id DESC';



	if ($num > 0)
	{
		$sql .= ' LIMIT ' . $num;
	}

	//echo $sql;

	$res = $GLOBALS['db']->getAll($sql);

	$goods = array();
	foreach ($res AS $idx => $row)
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

		$temp=$row['cat_id'];
		$cat_info=get_hot_cat_info($temp);

		$goods[$idx]['id']           = $row['goods_id'];
		$goods[$idx]['cat_id']       = $row['cat_id'];
		$goods[$idx]['cat_name']     = $cat_info['name'];
		$goods[$idx]['cat_url']     = $cat_info['url'];


		$goods[$idx]['name']         = $row['goods_name'];
		$goods[$idx]['brief']        = $row['goods_brief'];
		$goods[$idx]['market_price'] = price_format($row['market_price']);
		$goods[$idx]['short_name']   = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
		sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
		$goods[$idx]['shop_price']   = price_format($row['shop_price']);
		$goods[$idx]['thumb']        = empty($row['goods_thumb']) ? $GLOBALS['_CFG']['no_picture'] : $row['goods_thumb'];
		$goods[$idx]['goods_img']    = empty($row['goods_img']) ? $GLOBALS['_CFG']['no_picture'] : $row['goods_img'];
		$goods[$idx]['url']          = build_uri('goods', array('gid' => $row['goods_id']), $row['goods_name']);
	}

	return $goods;
}

function get_hot_cat_info($cat_id)
{
	/* 分类信息 */
	$sql = 'SELECT cat_name FROM ' . $GLOBALS['yp']->table('category') . " WHERE cat_id = '$cat_id'";
	$cat['name'] = $GLOBALS['db']->getOne($sql);
	$cat['url']  = build_uri('category', array('cid' => $cat_id), $cat['name']);
	$cat['id']   = $cat_id;

	return $cat;
}



?>