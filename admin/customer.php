<?php

/**
 * SZY QQ120029121 在线聊天客服管理
 * ============================================================================
 * 演示地址: http://demo.coolhong.com；
 * ============================================================================
 * $Author: jtypmall $
 * $Id: customer.php 17217 2015-07-07 06:29:08Z niqingyang $
 */
define('IN_PRINCE', true);
require (dirname(__FILE__) . '/includes/init.php');
require_once (ROOT_PATH . 'includes/lib_goods.php');
require_once (ROOT_PATH . 'includes/lib_order.php');
require_once (ROOT_PATH . 'includes/lib_chat.php');

/* 检查权限 */
admin_priv('customer');

// 检查php扩展项是否开启
if(! function_exists("curl_init"))
{
	sys_msg($_LANG['error_php_ext_curl_invalid']);
}

/* act操作项的初始化 */
$action = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'list';

/* 路由 */

$function_name = 'action_' . $action;

if(! function_exists($function_name))
{
	$function_name = "action_list";
}

call_user_func($function_name);

return;

/* 路由 */

function action_list ()
{
	$user = $GLOBALS['user'];
	$_CFG = $GLOBALS['_CFG'];
	$_LANG = $GLOBALS['_LANG'];
	$smarty = $GLOBALS['smarty'];
	$db = $GLOBALS['db'];
	$yp = $GLOBALS['yp'];
	$user_id = $_SESSION['user_id'];
	
	$sql = "SELECT count(*) " . "FROM " . $GLOBALS['yp']->table('chat_customer') . " WHERE supp_id = '0' ";
	$count=$GLOBALS['db']->getOne($sql);

	if($count>1){
		$limit=$count-1;
		$sql = "delete from " . $yp->table('chat_customer') . " where supp_id=0 limit $limit ";
		$GLOBALS['db']->query($sql);
	}elseif($count==0){
		$customer['add_time'] = gmtime();
		$customer['supp_id'] = 0;
		$GLOBALS['db']->autoExecute($yp->table('chat_customer'), $customer, 'INSERT');
	}
	$sql = "SELECT * " . "FROM " . $GLOBALS['yp']->table('chat_customer') . " WHERE supp_id = '0' ";
	$customer = $GLOBALS['db']->getRow($sql);
	
	$smarty->assign('customer', $customer);
	
	/* 模板赋值 */
	$smarty->assign('ur_here', '设置IM客服');
	
	/* 显示模板 */
	assign_query_info();
	
	$smarty->display('customer_info.htm');
}

/**
 * 编辑客服信息的提交
 */
function action_update ()
{
	$user = $GLOBALS['user'];
	$_CFG = $GLOBALS['_CFG'];
	$_LANG = $GLOBALS['_LANG'];
	$smarty = $GLOBALS['smarty'];
	$db = $GLOBALS['db'];
	$yp = $GLOBALS['yp'];
	$cus_id=intval($_POST['cus_id']);
	
	require_once(ROOT_PATH . 'includes/cls_image.php');
	$image = new cls_image($_CFG['bgcolor']);
	if(!empty($_FILES['cus_degree']['name'])){
		if(strpos($_FILES['cus_degree']['name'], 'php')!==false){
			$link[] = array('href' =>'customer.php?act=list', 'text' =>$_LANG['back_list']);
			sys_msg('您的图片含有非法参数,请重新上传！',0,$link);
		}
		$file_url= $image->upload_image($_FILES['cus_degree'], 'headimg');
		$customer = array(
			'cus_degree' => $file_url
		);
		$db->autoExecute($yp->table('chat_customer'), $customer, 'UPDATE', "cus_id = '$cus_id'");
	}
		
	$customer = array(
		// 管理员编号
		'user_id' => trim(addslashes($_POST['user_id'])), 
		// 聊天系统用户名
		'of_username' => trim(addslashes($_POST['of_username'])), 
		// 客服名称
		'cus_name' => trim(addslashes($_POST['cus_name'])), 
		// 描述
		'cus_desc' => trim(addslashes($_POST['cus_desc']))
	);
	$db->autoExecute($yp->table('chat_customer'), $customer, 'UPDATE', "cus_id = '$cus_id'");

	/* log */
	admin_log('客服IM：'.addslashes($customer['cus_name']) . '[' . $cus_id . ']', 'edit', 'chat_customer');
		
	/* 提示信息 */
	$links = array(
		array(
			'href' => 'customer.php?act=list', 'text' => $_LANG['back_list']
		)
	);
	sys_msg($_LANG['edit_success'], 0, $links);

	/* 显示客服列表页面 */
	assign_query_info();
	$smarty->display('customer_info.htm');
}



?>
