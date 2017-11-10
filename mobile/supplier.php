<?php

/**
 * 店铺的控制器文件
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: PRINCE $
 * $Id: index.php 17217 2017-04-01 06:29:08Z PRINCE $
*/

define('IN_PRINCE', true);


require(dirname(__FILE__) . '/includes/init_supplier.php');


if($_GET['suppId']<=0){
	
	yp_header("Location: index.php");
    exit;
}
$sql="SELECT s.*,sr.rank_name FROM ". $yp->table("supplier") . " as s left join ". $yp->table("supplier_rank") ." as sr ON s.rank_id=sr.rank_id
 WHERE s.supplier_id=".$_GET['suppId']." AND s.status=1";
$suppinfo=$db->getRow($sql);
$smarty->assign('suppid', $suppinfo['supplier_id']);
if(empty($suppinfo['supplier_id']) || $_GET['suppId'] != $suppinfo['supplier_id'])
{
	 yp_header("Location: index.php");
     exit;
}

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}
$typeinfo = array('index','category','search','article','other','about','activity');
$go = (isset($_GET['go']) && !empty($_GET['go'])) ? $_GET['go'] : 'index';
if(!in_array($go,$typeinfo)){
	yp_header("Location: index.php");
    exit;
}else{
	require(dirname(__FILE__) . '/supplier_'.$go.'.php');
}

?>