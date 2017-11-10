<?php

/**
 * QQ120029121 管理中心商店设置
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: PRINCE $
 * $Id: shop_config.php 17217 2017-04-01 06:29:08Z PRINCE $
 */

define('IN_PRINCE', true);

/* 代码 */
require(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . '/includes/cls_image.php');
$image = new cls_image($_CFG['bgcolor']);

if($GLOBALS['_CFG']['certificate_id']  == '')
{
    $certi_id='error';
}
else
{
    $certi_id=$GLOBALS['_CFG']['certificate_id'];
}

$sess_id = $GLOBALS['sess']->get_session_id();


/*------------------------------------------------------ */
//-- 列表编辑 ?act=list_edit
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list_edit')
{
	//include_once(ROOT_PATH . 'includes/fckeditor/fckeditor.php'); // 包含 html editor 类文件
	
	admin_priv('shop_header');
	
	$group_list = get_shop_header();
	$row=$db->getRow("select * from" . $yp->table('supplier_shop_config') . " WHERE code = 'shop_header_text' AND supplier_id=".$_SESSION['supplier_id']);
	$picture=trim($row['value']);
	//$picture=$_REQUEST['picture'];
	//echo "<pre>";
	//print_r($group_list);
    /* 创建 html editor */
	create_html_editor('shop_header_text', htmlspecialchars($group_list['shop_header_text']));  
    assign_query_info();
	$smarty->assign('picture',    $picture);
	
	$row=$db->getRow("select * from" . $yp->table('supplier_shop_config') . " WHERE code = 'mobile_header_picture' AND supplier_id=".$_SESSION['supplier_id']);//added on 20160925 by PRINCE  qq 1 2 0 0 2 9 1 2 1
	$mobile_header_picture=trim($row['value']);//added on 20160925 by PRINCE  qq 1 2 0 0 2 9 1 2 1
	$smarty->assign('mobile_header_picture',    $mobile_header_picture);//added on 20160925 by PRINCE  qq 1 2 0 0 2 9 1 2 1
	
	$smarty->assign('color',    $group_list['shop_header_color']);
	
	
	$row=$db->getRow("select * from" . $yp->table('supplier_shop_config') . " WHERE code = 'mobile_shop_header_color' AND supplier_id=".$_SESSION['supplier_id']);//added on 20160925 by PRINCE  qq 1 2 0 0 2 9 1 2 1
	$mobile_shop_header_color=trim($row['value']);//added on 20160925 by PRINCE  qq 1 2 0 0 2 9 1 2 1
	$smarty->assign('mobile_color',    $mobile_shop_header_color?$mobile_shop_header_color:'#000000');

	$smarty->assign('ur_here','店铺头部自定义');
    $smarty->display('shop_header.htm');
}

/*------------------------------------------------------ */
//-- 提交   ?act=post
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'post')
{
	
 
    $sql = "UPDATE " . $yp->table('supplier_shop_config') . " SET value = '$_POST[shop_header_color]' WHERE code = 'shop_header_color' AND supplier_id=".$_SESSION['supplier_id'];
    $db->query($sql);
	
	$sql = "DELETE FROM " . $yp->table('supplier_shop_config') . "  WHERE code = 'mobile_shop_header_color' AND supplier_id=".$_SESSION['supplier_id'];
	$db->query($sql);
	$supplier_id=$_SESSION['supplier_id'];
	$sql = "INSERT INTO " . $yp->table('supplier_shop_config') . "(`parent_id`, `code`, `type`,  `value`, `sort_order`, `supplier_id`)VALUES ('2','mobile_shop_header_color','hidden', '$_POST[mobile_shop_header_color]','1', '$supplier_id')  ";
	$db->query($sql);
	
	if($_FILES['goods_img']['size']!=0){
		$original_img   = $image->upload_image($_FILES['goods_img']); // 原始图片
		$sql = "UPDATE " . $yp->table('supplier_shop_config') . " SET value = ' $original_img ' WHERE code = 'shop_header_text' AND supplier_id=".$_SESSION['supplier_id'];
		$db->query($sql);
    }
	if($_FILES['mobile_goods_img']['size']!=0){//added on 20160925 by PRINCE  qq 1 2 0 0 2 9 1 2 1
		$original_img   = $image->upload_image($_FILES['mobile_goods_img']); // 原始图片
		$sql = "DELETE FROM " . $yp->table('supplier_shop_config') . "  WHERE code = 'mobile_header_picture' AND supplier_id=".$_SESSION['supplier_id'];
		$db->query($sql);
		$supplier_id=$_SESSION['supplier_id'];
		$sql = "INSERT INTO " . $yp->table('supplier_shop_config') . "(`parent_id`, `code`, `type`,  `value`, `sort_order`, `supplier_id`)VALUES ('2','mobile_header_picture','hidden', '$original_img','1', '$supplier_id')  ";
		$db->query($sql);
    }//added on 20160925 by PRINCE  qq 1 2 0 0 2 9 1 2 1
	
    $links[] = array('text' => '返回头部自定义', 'href' => 'shop_header.php?act=list_edit');
    sys_msg('设置成功！', 0, $links);


    /* 清除缓存 */
    clear_all_files();

 
}

/*------------------------------------------------------ */
//-- 发送测试邮件
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'send_test_email')
{
    /* 检查权限 */
    check_authz_json('shop_config');

    /* 取得参数 */
    $email          = trim($_POST['email']);

    /* 更新配置 */
    $_CFG['mail_service'] = intval($_POST['mail_service']);
    $_CFG['smtp_host']    = trim($_POST['smtp_host']);
    $_CFG['smtp_port']    = trim($_POST['smtp_port']);
    $_CFG['smtp_user']    = json_str_iconv(trim($_POST['smtp_user']));
    $_CFG['smtp_pass']    = trim($_POST['smtp_pass']);
    $_CFG['smtp_mail']    = trim($_POST['reply_email']);
    $_CFG['mail_charset'] = trim($_POST['mail_charset']);

    if (send_mail('', $email, $_LANG['test_mail_title'], $_LANG['cfg_name']['email_content'], 0))
    {
        make_json_result('', $_LANG['sendemail_success'] . $email);
    }
    else
    {
        make_json_error(join("\n", $err->_message));
    }
}

/*------------------------------------------------------ */
//-- 删除上传文件
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'del')
{
    /* 检查权限 */
    //check_authz_json('shop_config');

    /* 取得参数 */
    $code          = trim($_GET['code']);

    $filename = $_CFG[$code];

    //删除文件
    @unlink($filename);

    //更新设置
    update_configure($code, '');

    /* 记录日志 */
    //admin_log('', 'edit', 'shop_config');

    /* 清除缓存 */
    clear_all_files();

    sys_msg($_LANG['save_success'], 0);

}

/**
 * 设置系统设置
 *
 * @param   string  $key
 * @param   string  $val
 *
 * @return  boolean
 */
function update_configure($key, $val='')
{
    if (!empty($key))
    {
        $sql = "UPDATE " . $GLOBALS['yp']->table('supplier_shop_config') . " SET value='$val' WHERE code='$key' AND supplier_id=".$_SESSION['supplier_id'];

        return $GLOBALS['db']->query($sql);
    }

    return true;
}



/**
 * 获得设置信息
 *
 * @param   array   $groups     需要获得的设置组
 * @param   array   $excludes   不需要获得的设置组
 *
 * @return  array
 */
function get_shop_header()
{
    global $db, $yp, $_LANG;
    
    create_shop_settiongs();

    /* 取出全部数据：分组和变量 */
    $sql = "SELECT * FROM " . $yp->table('supplier_shop_config') .
            " WHERE supplier_id=".$_SESSION['supplier_id']." AND code in('shop_header_color','shop_header_text')";
    $item_list = $db->getAll($sql);
    //return $item_list;
    $group_list = array();
    foreach ($item_list AS $key => $item)
    {
    	if(in_array($item['code'],array('shop_header_color','shop_header_text')))
    	{
    		$group_list[$item['code']] = $item['value'];
    	}
    }

    return $group_list;
}

?>