<?php
/**
 * QQ120029121 供货商文章页
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: PRINCE $
 * $Id: article.php 17217 2017-04-01 06:29:08Z PRINCE $
*/

define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');

$smarty->assign('ur_here', "通知文章");

/* 供货商文章 */
$article_id = intval($_REQUEST['id']);
$sql = "select * from ". $yp->table('article') ." where article_id = '$article_id' ";
$article = $db->getRow($sql);

$smarty->assign('article',  $article);
$smarty->display('start_article.htm');

?>
