<?php


//移动设置路径

define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');

require(ROOT_PATH . 'includes/cls_json.php');


if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}




$file_url = $GLOBALS['yp']->url().'themesmobile/prince_jtypmall_mobile';

$smarty->assign('file_url',$file_url);


$act = $_REQUEST['act'];

if($act == 'insert'){

    $url= trim($_POST['url']);

    if(check_exist_url()){

        ajax_update_pc_url($url);

    }

    else{

        ajax_insert_pc_url($url);



    }





}else{

    $sql = "select value from ".$GLOBALS['yp']->table('ypmart_shop_config')." where code = 'pc_url' ";

    $pc_url = $GLOBALS['db']->getOne($sql);

    $smarty->assign('pc_url',$pc_url);
    $smarty->assign('mobile-url',$GLOBALS['yp']->url());
    $smarty->display('setting.dwt');

}


//插入pc_url数据信息

function ajax_insert_pc_url($url) {

    $sql = "insert into ".$GLOBALS['yp']->table('ypmart_shop_config',1)." (code ,value) value('pc_url', '$url') ";

    $GLOBALS['db']->query($sql);

    $id = $GLOBALS['db']->insert_id();

    if($id>0){

        $info = 'ok';

        $sql = "select value from ".$GLOBALS['yp']->table('ypmart_shop_config')." where id = '$id' ";

        $pc_url = $GLOBALS['db']->getOne($sql);


    }

    else{

        $info = 'no';

        $pc_url = '';

    }


    $json = new JSON();

    $row = array();

    $row['info'] = $info;
    $row['pc_url'] = $pc_url;

    $val = $json->encode($row);

    exit($val);
}

//更新pc_url信息

function ajax_update_pc_url($url) {

    $db = $GLOBALS['db'];
    $yp = $GLOBALS['yp'];

    $sql = "update ".$yp->table('ypmart_shop_config',1)." set value = '$url'  where code = 'pc_url' ";

    $db->query($sql);

    if($db->affected_rows() == 1){

        $info = 'ok';
    }

    else{

        $info = 'no;';

    }

    $sql = "select value from ".$yp->table('ypmart_shop_config')." where code = 'pc_url' ";

    $pc_url = $db->getOne($sql);


    $json = new JSON();

    $row = array();

    $row['info'] = $info;
    $row['pc_url'] = $pc_url;

    $val = $json->encode($row);

    exit($val);
}


function check_exist_url(){

    $sql = "select id from ".$GLOBALS['yp']->table('ypmart_shop_config')." where code = 'pc_url' ";

    $id = $GLOBALS['db']->getOne($sql);

    if($id > 0 ){

        return true;
    }

    else{

        return false;

    }

}


?>