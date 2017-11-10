<?php

/**
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: PRINCE $
 * $Id: index.php 17217 2017-04-01 06:29:08Z PRINCE $
*/

define('IN_PRINCE', true);
//判断是否有ajax请求
$act = !empty($_GET['act']) ? $_GET['act'] : '';
if ($act == 'add_guanzhu')
{
	
	$user_id = intval($_SESSION['user_id']);
    
    include_once('includes/cls_json.php');
    $json = new JSON;
    $result   = array('error' => 0, 'info' => '', 'data'=>'');
    
	if(empty($user_id)){
		$result['info'] = '请先登陆！';
		die($json->encode($result));
	}
	try {
		$sql = 'INSERT INTO '. $yp->table('supplier_guanzhu') . ' (`userid`, `supplierid`, `addtime`) VALUES ('.$user_id.','.$_GET['suppId'].','.time().') ON DUPLICATE KEY UPDATE addtime='.time();
		$db->query($sql);
		if($db->affected_rows() > 1){
			$result['error'] = 2;
    		$result['info'] = '已经关注！';
		}else{
			$result['error'] = 1;
    		$result['info'] = '关注成功！';
		}
	} catch (Exception $e) {
		$result['error'] = 2;
    	$result['info'] = '已经关注！';
	}
    die($json->encode($result));
}


?>