<?php

/**
 * QQ120029121 列出所有分类及品牌
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: prince $
 * $Id: catalog.php 17217 2017-04-01 06:29:08Z prince $
*/

define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');
require(dirname(__FILE__) . '/includes/lib_getdata.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}

if (!$smarty->is_cached('article_cat_list.dwt'))
{
     /* 如果页面没有被缓存则重新获得页面的内容 */

    assign_template('a', array($cat_id));
    $position = assign_ur_here($cat_id);
    $smarty->assign('page_title',           $position['title']);     // 页面标题
    $smarty->assign('ur_here',              $position['ur_here']);   // 当前位置


    $smarty->assign('article_categories',   article_categories_tree($cat_id)); //文章分类树

    $meta = $db->getRow("SELECT keywords, cat_desc FROM " . $yp->table('article_cat') . " WHERE cat_id = '$cat_id'");
    $smarty->assign('keywords',    htmlspecialchars($meta['keywords']));
    $smarty->assign('description', htmlspecialchars($meta['cat_desc']));
    $smarty->assign( 'article_top1', get_article_new(array(16),'art_cat',5) );//第一栏，今日聚焦
    $smarty->assign( 'article_top2', get_article_new(array(14),'art_cat',5) );//第二栏，行业聚焦
    $smarty->assign( 'article_mid1', get_article_new(array(18),'art_cat',5) );//第三栏，生活百科
    $smarty->assign( 'article_mid2', get_article_new(array(12),'art_cat',5) );//第四栏，生活百科
    $smarty->assign( 'article_mid3', get_article_new(array(4),'art_cat',8) );//开店必备
    $smarty->assign( 'article_foott', get_article_new(array(11),'art_cat',1) );    //中间图片列表3标题
    $smarty->assign( 'article_foot', get_article_new(array(11),'art_cat',7) );       //中间图片列表3图片，这两id统一
    $smarty->assign( 'article_ad', getads(99,5));

}
$smarty->display('article_cat_list.dwt');
