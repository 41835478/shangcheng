<?php

/**
 * QQ120029121 ajax
 * ============================================================================
 * 版权所有 2005-2016 热风科技，并保留所有权利。
 * 演示地址: http://demo.coolhong.com；
 * ============================================================================
 * $Author: demo.coolhong.com $
 * $Id: ajax.php 17063 2010-03-25 06:35:46Z $
*/

define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');

if ($_REQUEST['act'] == 'tipemail')
{
	require(ROOT_PATH . 'includes/cls_json.php');
	$word_qq_120029121 = json_str_iconv($_REQUEST['word']);
	$json_qq_120029121   = new JSON;
	$result_qq_120029121 = array('error' => 0, 'message' => '', 'content' => '');
	
	if(!$word_qq_120029121 ||  strlen($word_qq_120029121) > 30)
	{
        $result_qq_120029121['error']   = 1;
		die($json_qq_120029121->encode($result_qq_120029121));
	}
	$word_qq_120029121 = str_replace(array(' ','*', "\'"), array('', '', ''), $word_qq_120029121);

	$email_name_arr = explode("@", $word_qq_120029121);
	$email_name = $email_name_arr[0];
    
	$_CFG['email_domain'] =str_replace(" ", "",$_CFG['email_domain']);
	$email_domain_arr = explode(",", str_replace("，",",",$_CFG['email_domain']));

    $logdb=array();
	foreach($email_domain_arr AS $key=>$edomain)
	{
		$email_domain_arr[$key] = $email_name.'@'.$edomain;
	}

	foreach($email_domain_arr AS $email_domain)
    {
		if (stristr($email_domain, $word_qq_120029121))
		{
			$logdb[] = $email_domain;
		}
	}
	$smarty->assign('logdb', $logdb);	

	if(count($logdb)==0)
	{
		$result_qq_120029121['content'] = '';
	}
	else
	{		
		$result_qq_120029121['content'] = $smarty->fetch('library/email_tip.lbi');
	}
	

	die($json_qq_120029121->encode($result_qq_120029121));
}
?>