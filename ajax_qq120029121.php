<?php

/**
 * QQ120029121 ajax
 * ============================================================================
 * 版权所有 2005-2016 热风科技，并保留所有权利。
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: demo.coolhong.com $
 * $Id: ajax.php 17063 2010-03-25 06:35:46Z qq $
*/

define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');

if ($_REQUEST['act'] == 'tipword')
{
	require(ROOT_PATH . 'includes/cls_json.php');
	$word_qq_120029121 = json_str_iconv($_REQUEST['word']);
	$json_qq_120029121   = new JSON;
	$result_qq_120029121 = array('error' => 0, 'message' => '', 'content' => '');
	
	if(!$word_qq_120029121 || strlen($word_qq_120029121) < 2 || strlen($word_qq_120029121) > 30)
	{
        $result_qq_120029121['error']   = 1;
		die($json_qq_120029121->encode($result_qq_120029121));
	}
	$needle = $replace = array();
	$word_qq_120029121 = str_replace(array(' ','*', "\'"), array('%', '%', ''), $word_qq_120029121);
	$needle[] = $word_qq_120029121;
	$replace[] = '<strong style="color:cc0000;">'.$word_qq_120029121.'</strong>';
	$logdb = array();
	if(preg_match("/^[a-z0-9A-Z]+$/", $word_qq_120029121)) {	
    	$sql_qq = "SELECT * FROM " . $yp->table('keyword') ." WHERE searchengine='jtypmall' AND status='1' AND letter LIKE '%$word_qq_120029121%' ORDER BY total_search DESC";
	} else {
    	$sql_qq = "SELECT * FROM " . $yp->table('keyword') ." WHERE searchengine='jtypmall' AND status='1' AND word LIKE '%$word_qq_120029121%' ORDER BY total_search DESC";
	}
    $res_qq_120029121 = $db->SelectLimit($sql_qq, 10, 0);

	$iii=1; //demo.coolhong.com 今-天-优-品-多-商-户-系-统 Q-Q：12 00 29 12 1
	while ($rows_qq_120029121 = $db->fetchRow($res_qq_120029121))
    {
		$rows_qq_120029121['kword'] = str_ireplace($needle, $replace, $rows_qq_120029121['word']);

		/* start  By  demo.coolhong.com 今天优品多商户系统 Q-Q：12 00 29 12 1 */
		if($iii==1 && $rows_qq_120029121['keyword_cat_count'])
		{  
			$rows_qq_120029121['keyword_cat'] =  '<a href="' . $rows_qq_120029121['keyword_cat_url'] . '"><font color=#666>在<font color=#cc0000>'. $rows_qq_120029121['keyword_cat'] .'</font>分类中搜索</font></a>';
			$rows_qq_120029121['keyword_cat_count'] = intval($rows_qq_120029121['keyword_cat_count']);
		}
		$iii=$iii+1;  
		/* end  By  demo.coolhong.com 今天优品多商户系统 Q-Q：12 00 29 12 1 */

		$logdb[] = $rows_qq_120029121; 

		
	}
	$smarty->assign('logdb', $logdb);
	$result_qq_120029121['content'] = $smarty->fetch('library/search_tip.lbi');
	die($json_qq_120029121->encode($result_qq_120029121));
}
?>