<?php
/*
　                              \\\|/// 
　                            \\  - -  // 
　                             (  @ @  ) 
　┏━━━━━━━━━━━━━oOOo-(_)-oOOo━━┓
　┃                                           ┃
　┃ 微智商盟                                  ┃
　┃                                           ┃
　┃ 网站地址: http://demo.coolhong.com/mobile/           ┃
　┃                                           ┃
　┃  广东热风科技有限公司，并保留所有权利。                                ┃
　┃                                           ┃
　┃                                  Oooo    ┃
　┗━━━━━━━━━━━━━ oooO━-(   )━━┛
　                             (   )   ) / 
　                              \ (   (_/ 
  　                             \_)
*/
define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');


if ((DEBUG_MODE & 2) != 2)


{


    $smarty->caching = true;


}

$user_id = !empty($_REQUEST['user_id']) ? trim($_REQUEST['user_id']) : '';

$user_agent = $_SERVER['HTTP_USER_AGENT'];




if(empty($_SESSION['user_id']))//未登陆处理

{

	if(! in_array($action, $not_login_arr))

	{

		if(in_array($action, $ui_arr))

		{

			
			if(! empty($_SERVER['QUERY_STRING']))

			{

				$back_act = 'user.php?' . strip_tags($_SERVER['QUERY_STRING']);

			}

			$action = 'login';

		}

		else

		{

			// 未登录提交数据。非正常途径提交数据！

// 			die($_LANG['require_login']);

			show_message($_LANG['require_login'], array('</br>请先登录', '</br>返回首页'), array('user.php?act=login', $yp->url()), 'error', false);

		}

	}

}



if ($act == 'cat_rec')
{
    $rec_array = array(1 => 'best', 2 => 'new', 3 => 'hot');
    $rec_type = !empty($_REQUEST['rec_type']) ? intval($_REQUEST['rec_type']) : '1';
    $cat_id = !empty($_REQUEST['cid']) ? intval($_REQUEST['cid']) : '0';
    include_once('include/cls_json.php');
    $json = new JSON;
    $result   = array('error' => 0, 'content' => '', 'type' => $rec_type, 'cat_id' => $cat_id);

    $children = get_children($cat_id);
    $smarty->assign($rec_array[$rec_type] . '_goods',      get_category_recommend_goods($rec_array[$rec_type], $children));    // 推荐商品
    $smarty->assign('cat_rec_sign', 1);
    $result['content'] = $smarty->fetch('library/recommend_' . $rec_array[$rec_type] . '.lbi');
    die($json->encode($result));
}

$cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-' . $_CFG['lang']));

$ad = $db->getRow ( "SELECT * FROM " . $GLOBALS['yp']->table('weixin_ad_config') . " WHERE `id` = 1" );//广告页面设置
 $smarty->assign('ad',         $ad);

if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'ad_list';
}
/*========================广告列表====================================*/
if ($_REQUEST['act'] == 'ad_list')
{  

if (!$smarty->is_cached('article_ad_list.dwt', $cache_id))
        $cache_id = $_CFG['lang'] . '-' . $size . '-' . $page;
        $cache_id = sprintf('%X', crc32($cache_id));
{
   if($_GET["key"]=="del"){//删除用户广告位
	 $sql="delete from  " . $GLOBALS['yp']->table('ypmart_article_ad_user') . "  where id='".$_GET['id']."'";
   // mysql_query($sql);
	 mysqli_query($sql);
   // mysql_close();
	 mysqli_close();
	 echo "<script type='text/javascript'>alert('\u6210\u529f\u5220\u9664\u0021');location.href='article_ad.php?act=ad_list&page=".$_GET["page"]."';      </script>";
	 exit;
}

$perpagenum = 10;//定义每页显示几条

$total = mysqli_fetch_array(mysqli_query("select count(*) from " . $GLOBALS['yp']->table('ypmart_article_ad_user') . "   where user_id='".$_SESSION['user_id']."'"));//查询数据库中一共有多少条数据
$Total = $total[0];
$Totalpage = ceil($Total/$perpagenum);//上舍，取整

 
if(!isset($_GET['page'])||!intval($_GET['page'])||$_GET['page']>$Totalpage)//page可能的四种状态   
{   
    $page=1;   
}   
else   
{   
    $page=$_GET['page'];//如果不满足以上四种情况，则page的值为$_GET['page']   
}   
$startnum     = ($page-1)*$perpagenum;//开始条数 

$contents = array();  
$sql = "select * from " . $GLOBALS['yp']->table('ypmart_article_ad_user') . "  where user_id='".$_SESSION['user_id']."' order by id limit $startnum,$perpagenum";//查询出所需要的条数   

//$rs = mysql_query($sql);   
//$contents = mysql_fetch_array($rs);  
$res = $GLOBALS['db']->query($sql);
while ($row = $GLOBALS['db']->fetchRow($res)){   
        $contents[] = $row;
}
	
	 


$smarty->assign('contents',    $contents); 


$per = $page - 1;//上一页   
$next = $page + 1;//下一页   

        $smarty->assign('Total',    $Total);
		$smarty->assign('perpagenum',    $perpagenum);
		$smarty->assign('Totalpage',    $Totalpage);
        $smarty->assign('page',    $page);
		$smarty->assign('per',    $per);
		$smarty->assign('next',    $next);

		$url = $_SERVER['PHP_SELF'];
		$smarty->assign('url',    $url);

		#$web_url ='http://'.$_SERVER['HTTP_HOST'].'/';
		$web_url =HTTP_TYPE.'://'.$_SERVER['HTTP_HOST'].'/';
        $smarty->assign('web_url',    $web_url);

		//$wap_url ='http://'.$_SERVER['HTTP_HOST'].'/mobile/';
		$wap_url =HTTP_TYPE.'://'.$_SERVER['HTTP_HOST'].'/mobile/';
        $smarty->assign('wap_url',    $wap_url);

       
   


      
		 //模板赋值
        $smarty->assign('cfg', $_CFG);
        assign_template();

    $position = assign_ur_here();
    $smarty->assign('page_title',      $position['title']);    // 页面标题
    $smarty->assign('ur_here',         $position['ur_here']);  // 当前位置
    $smarty->assign('best_goods',      get_recommend_goods('best'));    // 推荐商品
    $smarty->assign('new_goods',       get_recommend_goods('new'));     // 最新商品
    $smarty->assign('hot_goods',       get_recommend_goods('hot'));     // 热点文章
    $smarty->assign('promotion_goods', get_promote_goods()); // 特价商品
    $smarty->assign('brand_list',      get_brands());
    $smarty->assign('promotion_info',  get_promotion_info()); // 增加一个动态显示所有促销信息的标签栏
 
    assign_dynamic('article_ad_list');
	   
	  

}



$smarty->assign('now_time',  gmtime());           // 当前系统时间
 


$smarty->display('article_ad_list.dwt', $cache_id);





}
/*===============================我的发布=================================*/
 elseif ($_REQUEST['act'] == 'my_ad'){
        if (!$smarty->is_cached('article_ad_my.dwt', $cache_id))
        $cache_id = $_CFG['lang'] . '-' . $size . '-' . $page;
        $cache_id = sprintf('%X', crc32($cache_id));
          {

		  $perpagenum = 10;//定义每页显示几条
    // $total = mysql_fetch_array(mysql_query("select count(*) from " . $GLOBALS['yp']->table('ypmart_article_ad_info') . "  where user_id='".$_SESSION['user_id']."'"));//查询数据库中一共有多少条数据
    $total = mysqli_fetch_array(mysqli_query("select count(*) from " . $GLOBALS['yp']->table('ypmart_article_ad_info') . "   where user_id='".$_SESSION['user_id']."'"));//查询数据库中一共有多少条数据
    $Total = $total[0];
    $Totalpage = ceil($Total/$perpagenum);//上舍，取整

 
                if(!isset($_GET['page'])||!intval($_GET['page'])||$_GET['page']>$Totalpage)//page可能的四种状态   
                 {   
                    $page=1;   
                 }   
                 else   
                {   
                  $page=$_GET['page'];//如果不满足以上四种情况，则page的值为$_GET['page']   
              }   
             $startnum     = ($page-1)*$perpagenum;//开始条数 

           $contents = array();  
		   
           $sql = "select * from " . $GLOBALS['yp']->table('ypmart_article_ad_info') . "  where user_id='".$_SESSION['user_id']."' order by id limit $startnum,$perpagenum";//查询出所需要的条数   
 
         $res = $GLOBALS['db']->query($sql);
           while ($row = $GLOBALS['db']->fetchRow($res)){   
           $contents[] = $row;
      }


$smarty->assign('contents',    $contents); 


$per = $page - 1;//上一页   
$next = $page + 1;//下一页   

        $smarty->assign('Total',    $Total);
		$smarty->assign('perpagenum',    $perpagenum);
		$smarty->assign('Totalpage',    $Totalpage);
        $smarty->assign('page',    $page);
		$smarty->assign('per',    $per);
		$smarty->assign('next',    $next);
		
		$url_my = $_SERVER['PHP_SELF'];
		$smarty->assign('url_my',    $url_my);

		//$web_url ='http://'.$_SERVER['HTTP_HOST'].'/';
		$web_url =HTTP_TYPE.'://'.$_SERVER['HTTP_HOST'].'/';
        $smarty->assign('web_url',    $web_url);

		//$wap_url ='http://'.$_SERVER['HTTP_HOST'].'/mobile/';
		$wap_url =HTTP_TYPE.'://'.$_SERVER['HTTP_HOST'].'/mobile/';
        $smarty->assign('wap_url',    $wap_url);



		  }

$smarty->assign('now_time',  gmtime());           // 当前系统时间
$smarty->display('article_ad_my.dwt', $cache_id);

}
/*============================添加广告==============================*/

 elseif ($_REQUEST['act'] == 'ad_info')
{  




       
if (!$smarty->is_cached('article_ad_info.dwt'))


{
     
      if(!empty($_FILES["ad_img"])&&!empty($_POST['adtelno'])&&!empty($_POST['adtitle'])&&!empty($_POST['adlink'])){
	  
		if(is_uploaded_file($_FILES['ad_img']['tmp_name'])){ 
		$ad_img=$_FILES["ad_img"]; 
		//获取数组里面的值 
		//$name=time().$upfile["name"];//上传文件的文件名
		$string = strrev($_FILES['ad_img']['name']);
		$adarray = explode('.',$string);
		$type=$ad_img["type"];//上传文件的类型 
		$size=$ad_img["size"];//上传文件的大小 
		$tmp_name=$ad_img["tmp_name"];//上传文件的临时存放路径 
		//$adname = 'upload/'.time().'a.'.strrev($array[0]);
		$adname = 'images/upload/Image/'.time().rand(10,100).'a.'.strrev($adarray[0]);
		//判断是否为图片 
		switch ($type){ 
			case 'image/pjpeg':$okType=true; 
			break; 
			case 'image/jpeg':$okType=true; 
			break; 
			case 'image/gif':$okType=true; 
			break; 
			case 'image/png':$okType=true; 
			break; 
		}
		
		
		if($okType){ 
			$error=$ad_img["error"];//上传后系统返回的值 
			//把上传的临时文件移动到up目录下面 
			move_uploaded_file($tmp_name,$adname); 
		}else{ 
			qy_alert_back('no');
		} 
	}
	
	
	if(is_uploaded_file($_FILES['qrcode']['tmp_name'])){ 
		$qrcode=$_FILES["qrcode"]; 
		//获取数组里面的值 
		//$name=time().$qrcode["name"];//上传文件的文件名
		$ewstring = strrev($_FILES['qrcode']['name']);
		$ewarray = explode('.',$ewstring);
		$ewtype=$qrcode["type"];//上传文件的类型 
		$ewsize=$qrcode["size"];//上传文件的大小 
		$ewtmp_name=$qrcode["tmp_name"];//上传文件的临时存放路径 
		$ewname = 'images/upload/Image/'.time().rand(10,100).'b.'.strrev($ewarray[0]);
		//判断是否为图片 
		switch ($ewtype){ 
			case 'image/pjpeg':$okTypew=true; 
			break; 
			case 'image/jpeg':$okTypew=true; 
			break; 
			case 'image/gif':$okTypew=true; 
			break; 
			case 'image/png':$okTypew=true; 
			break; 
		}
		
           
		if($okTypew){ 
			$error=$qrcode["error"];//上传后系统返回的值 
			//把上传的临时文件移动到up目录下面 
			move_uploaded_file($ewtmp_name,$ewname); 
		}else{ 
			qy_alert_back('no');
		} 
	}
	
	 $adtelnumber=trim($_POST['adtelno']);//手机号
	 
	 $userid=$_SESSION['user_id'];
	
	 $nickname_sql = "SELECT `nickname` FROM " . $GLOBALS['yp']->table('users') . " WHERE `userid` = '$userid'";

    $nickname = $db -> getOne($nickname_sql);//登录会员微信昵称
	 
	
	$sql = "insert into " . $GLOBALS['yp']->table('ypmart_article_ad_user') . "  values (0,'".$_SESSION['user_id']."','".$_POST['adtitle']."','".$_POST['adlink']."','".$adname."','".$nickname."','".date('Y-m-d H:i:s')."','".$adtelnumber."','".$ewname."',0)";

  // mysql_query($sql);
	mysqli_query($sql);

	echo "<script>alert('\u63d0\u4ea4\u6210\u529f\uff01');window.location.href=' article_ad.php?act=ad_list';</script>";

	
	}
	
		
		
		
		
		
		
		
		
		
		
		
		

		#$web_url ='http://'.$_SERVER['HTTP_HOST'].'/';
		$web_url =HTTP_TYPE.'://'.$_SERVER['HTTP_HOST'].'/';
        $smarty->assign('web_url',    $web_url);

		#$wap_url ='http://'.$_SERVER['HTTP_HOST'].'/mobile/';
		$wap_url =HTTP_TYPE.'://'.$_SERVER['HTTP_HOST'].'/mobile/';
        $smarty->assign('wap_url',    $wap_url);

       
   


      
        
		 //模板赋值
     
        $smarty->assign('cfg', $_CFG);
        assign_template();

    $position = assign_ur_here();
    $smarty->assign('page_title',      $position['title']);    // 页面标题
    $smarty->assign('ur_here',         $position['ur_here']);  // 当前位置
    $smarty->assign('best_goods',      get_recommend_goods('best'));    // 推荐商品
    $smarty->assign('new_goods',       get_recommend_goods('new'));     // 最新商品
    $smarty->assign('hot_goods',       get_recommend_goods('hot'));     // 热点文章
    $smarty->assign('promotion_goods', get_promote_goods()); // 特价商品
    $smarty->assign('brand_list',      get_brands());
    $smarty->assign('promotion_info',  get_promotion_info()); // 增加一个动态显示所有促销信息的标签栏


	
        assign_dynamic('article_ad_info');
	   
	  

}



$smarty->assign('now_time',  gmtime());           // 当前系统时间
 


$smarty->display('article_ad_info.dwt', $cache_id);





}




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
				$arr[$row['ad_id']]['image'] = "data/afficheimg/".$row['ad_code'];
				$arr[$row['ad_id']]['content'] = "<a href='".$arr[$row['ad_id']]['url']."' target='_blank'><img src='data/afficheimg/".$row['ad_code']."' width='".$row['ad_width']."' height='".$row['ad_height']."' /></a>";
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












?>