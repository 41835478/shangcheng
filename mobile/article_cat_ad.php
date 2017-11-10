<?php





/**
 * QQ120029121 文章分类
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552






 * ============================================================================


 * $Author: prince $
 * $Id: article_cat.php 17217 2017-04-01 06:29:08Z prince $
*/








define('IN_PRINCE', true);





require(dirname(__FILE__) . '/includes/init.php');





if ((DEBUG_MODE & 2) != 2)


{


    $smarty->caching = true;


}





/* 清除缓存 */


clear_cache_files();





/*------------------------------------------------------ */


//-- INPUT


/*------------------------------------------------------ */


$ad = $db->getRow ( "SELECT * FROM " . $GLOBALS['yp']->table('weixin_ad_config') . " WHERE `id` = 1" );//广告页面设置


/* 获得指定的分类ID */


if (!empty($_GET['id']))


{


    $cat_id = intval($_GET['id']);


}


elseif (!empty($_GET['category']))


{


    $cat_id = intval($_GET['category']);


}


else


{


    yp_header("Location: ./\n");





    exit;


}

/*if ($cat_id != $ad['article_catid'])

{


    yp_header("Location: article_cat.php?id=$cat_id\n");

    exit;


}*/

/* 获得当前页码 */


$page   = !empty($_REQUEST['page'])  && intval($_REQUEST['page'])  > 0 ? intval($_REQUEST['page'])  : 1;





/*------------------------------------------------------ */


//-- PROCESSOR


/*------------------------------------------------------ */





/* 获得页面的缓存ID */


$cache_id = sprintf('%X', crc32($cat_id . '-' . $page . '-' . $_CFG['lang']));





if (!$smarty->is_cached('article_cat_ad.dwt', $cache_id))


{


    /* 如果页面没有被缓存则重新获得页面的内容 */



    assign_template('a', array($cat_id));


    $position = assign_ur_here($cat_id);


    $smarty->assign('page_title',           $position['title']);     // 页面标题


    $smarty->assign('ur_here',              $position['ur_here']);   // 当前位置





    $smarty->assign('categories',           get_categories_tree(0)); // 分类树


    $smarty->assign('article_categories',   article_categories_tree($cat_id)); //文章分类树


    $smarty->assign('helps',                get_shop_help());        // 网店帮助


    $smarty->assign('top_goods',            get_top10());            // 销售排行





    $smarty->assign('best_goods',           get_recommend_goods('best'));


    $smarty->assign('new_goods',            get_recommend_goods('new'));


    $smarty->assign('hot_goods',            get_recommend_goods('hot'));


    $smarty->assign('promotion_goods',      get_promote_goods());


    $smarty->assign('promotion_info', get_promotion_info());





    /* Meta */
    $meta = $db->getRow("SELECT keywords, cat_desc FROM " . $yp->table('article_cat') . " WHERE cat_id = '$cat_id'");





    if ($meta === false || empty($meta))


    {


        /* 如果没有找到任何记录则返回首页 */


        yp_header("Location: ./\n");


        exit;


    }





    $smarty->assign('keywords',    htmlspecialchars($meta['keywords']));


    $smarty->assign('description', htmlspecialchars($meta['cat_desc']));


	
	 

    /* 获得文章总数 */


    $size   = isset($_CFG['article_page_size']) && intval($_CFG['article_page_size']) > 0 ? intval($_CFG['article_page_size']) : 20;


    $count  = get_article_count($cat_id);


    $pages  = ($count > 0) ? ceil($count / $size) : 1;





    if ($page > $pages)


    {


        $page = $pages;


    }


    $pager['search']['id'] = $cat_id;


    $keywords = '';


    $goon_keywords = ''; //继续传递的搜索关键词





    /* 获得文章列表 */


    if (isset($_REQUEST['keywords']))


    {


        $keywords = addslashes(htmlspecialchars(urldecode(trim($_REQUEST['keywords']))));


        $pager['search']['keywords'] = $keywords;


        $search_url = substr(strrchr($_POST['cur_url'], '/'), 1);





        $smarty->assign('search_value',    stripslashes(stripslashes($keywords)));


        $smarty->assign('search_url',       $search_url);


        $count  = get_article_count($cat_id, $keywords);


        $pages  = ($count > 0) ? ceil($count / $size) : 1;


        if ($page > $pages)


        {


            $page = $pages;


        }





        $goon_keywords = urlencode($_REQUEST['keywords']);


    }


   $smarty->assign('artciles_list',    get_cat_articles($cat_id, $page, $size ,$keywords));


    $smarty->assign('cat_id',    $cat_id);


    /* 分页 */


    assign_pager('article_cat_ad', $cat_id, $count, $size, '', '', $page, $goon_keywords);


    assign_dynamic('article_cat_ad');


}





$smarty->assign('feed_url',         ($_CFG['rewrite'] == 1) ? "feed-typearticle_cat" . $cat_id . ".xml" : 'feed.php?type=article_cat' . $cat_id); // RSS URL

          $article_catid = $ad['article_catid'];
        $contents = array();  
         $sql = "select * from " . $GLOBALS['yp']->table('article_cat') . " where parent_id='$article_catid' order by cat_id limit 0,3";//查询出所需要的条数   

  
          $res = $GLOBALS['db']->query($sql);
          while ($row = $GLOBALS['db']->fetchRow($res)){   
        $contents[] = $row;
           }


          $smarty->assign('contents',    $contents); 

     $content = array();  
         $sql = "select * from " . $GLOBALS['yp']->table('article_cat') . " where parent_id='$article_catid' order by cat_id limit 3,30";//查询出所需要的条数   

  
          $res = $GLOBALS['db']->query($sql);
          while ($rew = $GLOBALS['db']->fetchRow($res)){   
        $content[] = $rew;
           }

          $smarty->assign('article_catid',    $article_catid); 
          $smarty->assign('content',    $content); 

$smarty->display('article_cat_ad.dwt', $cache_id);





?>