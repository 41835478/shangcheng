<?php

/**
 * QQ120029121 生成验证码
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: PRINCE $
 * $Id: goods_comment.php 17217 2017-04-01 06:29:08Z PRINCE $
*/

define('IN_PRINCE', true);

require_once(dirname(__FILE__) . '/includes/init.php');
include_once(dirname(__FILE__) . '/includes/lib_comment.php');


if ($_REQUEST['act'] == 'view')
{
	$shaidan_id = intval($_REQUEST['id']);

	$cache_id = sprintf('%X', crc32($_REQUEST['id'] . 'goods_shaidan_view'));
	if (!$smarty->is_cached('goods_comment_view.dwt', $cache_id))
	{
		$shaidan = $db->getRow("SELECT * FROM ".$yp->table('shaidan')." WHERE shaidan_id = '$shaidan_id'");
		$shaidan_imgs = $GLOBALS['db']->getAll("SELECT * FROM ".$GLOBALS['yp']->table('shaidan_img')." WHERE shaidan_id = '$shaidan_id'");	
		$goods_id = $shaidan['goods_id'];

		$smarty->assign('shaidan',               $shaidan);
		$smarty->assign('shaidan_imgs',          $shaidan_imgs);
		
		/* 获得商品的信息 */
		$goods = get_goods_info($goods_id);
		if ($goods === false)
		{
			/* 如果没有找到任何记录则跳回到首页 */
			yp_header("Location: ./\n");
			exit;
		}
		
        $catlist = array();
        foreach(get_parent_cats($goods['cat_id']) as $k=>$v)
        {
            $catlist[] = $v['cat_id'];
        }
        assign_template('c', $catlist);
		
        /* meta */
        $smarty->assign('keywords',           htmlspecialchars($goods['keywords']));
        $smarty->assign('description',        htmlspecialchars($goods['goods_brief']));
		
        $position = assign_ur_here($goods['cat_id'], $goods['goods_name']);
		$position['ur_here'] .= ' <span class="crumbs-arrow">&gt;</span><span class="last">晒单</span>';
		
        /* current position */
        $smarty->assign('page_title',          $position['title']);                    // 页面标题
        $smarty->assign('ur_here',             $position['ur_here']);                  // 当前位置
		
        $smarty->assign('goods',              $goods);
        $smarty->assign('goods_id',           $goods['goods_id']);
		$smarty->assign('categories',       get_categories_tree());  // 分类树
		$smarty->assign('helps',            get_shop_help()); // 网店帮助
		$smarty->assign('page_title',   $position['title']);    // 页面标题
		$smarty->assign('ur_here',      $position['ur_here']);  // 当前位置
		
		assign_dynamic('goods');
	}
	$smarty->display('goods_shaidan_view.dwt');
}
?>