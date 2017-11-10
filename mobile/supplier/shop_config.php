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
require_once(ROOT_PATH . 'includes/lib_supplier_common_wap.php');//add by prince

if($GLOBALS['_CFG']['certificate_id']  == '')
{
    $certi_id='error';
}
else
{
    $certi_id=$GLOBALS['_CFG']['certificate_id'];
}

$sess_id = $GLOBALS['sess']->get_session_id();

$auth = time();
$ac = md5($certi_id.'SHOP_SMS'.$auth);
//$url = 'http://demo.coolhong.com/sms/index.php?certificate_id='.$certi_id.'&sess_id='.$sess_id.'&auth='.$auth.'&ac='.$ac;

/*------------------------------------------------------ */
//-- 列表编辑 ?act=list_edit
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list_edit')
{
    /* 检查权限 */
    admin_priv('shop_base');
    /* 可选语言 */
    $dir = opendir('../languages');
    $lang_list = array();
    while (@$file = readdir($dir))
    {
        if ($file != '.' && $file != '..' &&  $file != '.svn' && $file != '_svn' && is_dir('../languages/' .$file))
        {
            $lang_list[] = $file;
        }
    }
    @closedir($dir);
	

   $group_list = get_settings(array('1'), array('shop_header_color','shop_header_text'));
	function get_street_type(){
	$sql = "select str_id,str_name from ".$GLOBALS['yp']->table('street_category')." where is_show = 1";
	$info = $GLOBALS['db']->getAll($sql);
	$ret = array();
	foreach($info as $k=>$v){
		$ret[$v['str_id']] = $v['str_name'];
	}
	return $ret;
}
	//$street_info = $db->getRow("select * from ".$yp->table('supplier_street')." where supplier_id=".$_SESSION['supplier_id']);
	$smarty->assign('stype',get_street_type());
	//$item_list[][9]['vars']=$street_info;
   // $group_list[9]['name']='店铺设置';
	//$group_list[9]['code']='shop_set';
	//$group_list[9]['vars']['0']['code']='shop';
	//$street_info = $db->getRow("select * from ".$yp->table('supplier_street')." where supplier_id=".$_SESSION['supplier_id']);
     //$group_list[9]['vars'][]=$street_info;
	//$group_list[1]['vars']['0']['store_dir']='shop';
//echo "<pre>";
	//print_r($street_info);
//print_r($group_list);
    $sql = "SELECT value FROM " . $GLOBALS['yp']->table('supplier_shop_config') . " WHERE  code = 'use_supplier_url' AND supplier_id=".$_SESSION['supplier_id'];
    $supplier_value = $GLOBALS['db']->getOne($sql);
	if ($supplier_value)
	{
    $str = $_SERVER['SERVER_NAME'] ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST'];
    $n = strpos($str,'.');//寻找位置
	$str_2 = substr($str,0,$n);
	$supplier_url = str_replace("$str_2","$supplier_value","$str");
	$smarty->assign('supplier_url',    '您的店铺二级域名是：'.$supplier_url);
	}
    $smarty->assign('lang_list',    $lang_list);
    $smarty->assign('ur_here',      $_LANG['01_shop_config']);
    $smarty->assign('group_list',   $group_list);
    $smarty->assign('countries',    get_regions());

    if (strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'iis') !== false)
    {
        $rewrite_confirm = $_LANG['rewrite_confirm_iis'];
    }
    else
    {
        $rewrite_confirm = $_LANG['rewrite_confirm_apache'];
    }
    $smarty->assign('rewrite_confirm', $rewrite_confirm);

    if ($_CFG['shop_country'] > 0)
    {
        $smarty->assign('provinces', get_regions(1, $_CFG['shop_country']));
        if ($_CFG['shop_province'])
        {
            $smarty->assign('cities', get_regions(2, $_CFG['shop_province']));
			if ($_CFG['shop_district'])
			{
				$smarty->assign('districts', get_regions(3, $_CFG['shop_city']));
			}
        }
    }
    $smarty->assign('cfg', $_CFG);

    //zhouhui
    $sql = "SELECT * FROM ".$yp->table('supplier')." where supplier_id=". $_SESSION['supplier_id'];//20170101 mod by qq120029121
    $supplier_info = $db->getRow($sql);
    $smarty->assign('supplier_info', $supplier_info);

    //assign_query_info();
    //$smarty->display('shop_config.htm');
	
    _wap_assign_header_info('店铺设置','店铺设置',0,1);
    _wap_assign_footer_order_info();
    _wap_display_page('shop_config.htm');
	
}

/*------------------------------------------------------ */
//-- 邮件服务器设置
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'mail_settings')
{
    /* 检查权限 */
    admin_priv('shop_config');

    $arr = get_settings(array(5));

    assign_query_info();

    $smarty->assign('ur_here',      $_LANG['mail_settings']);
    $smarty->assign('cfg', $arr[5]['vars']);
    $smarty->display('shop_config_mail_settings.htm');
}

/*------------------------------------------------------ */
//-- 提交   ?act=post
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'post')
{
    $type = empty($_POST['type']) ? '' : $_POST['type'];
	$jd=$_POST['jingdu'];
	$wd=$_POST['weidu'];
	
	
	//20170101 add by qq120029121 start
	$wx_jd=$_POST['wx_jingdu'];
	$wx_wd=$_POST['wx_weidu'];
    $db->query("UPDATE ".$yp->table('supplier')." set wx_latitude='$wx_wd',wx_longitude='$wx_jd' where supplier_id=".$_SESSION['supplier_id']);
	//20170101 add by qq120029121 end

    //zhouhui
    $db->query("UPDATE ".$yp->table('supplier')." set latitude='$wd',longitude='$jd' where supplier_id=".$_SESSION['supplier_id']);
	//$jd= substr($jd,7);
	//$wd= substr($wd,7);
	$sql="select * from " . $GLOBALS['yp']->table('supplier_shop_config') . " where code='jingdu' and supplier_id=".$_SESSION['supplier_id'];
	$jingdu=$db->getRow($sql);
	if(empty($jingdu['id']))
	{ 
		$sql="insert into " . $GLOBALS['yp']->table('supplier_shop_config') . " (parent_id, code,type,value,sort_order,supplier_id) VALUES ('1','jingdu','jwd','".$jd."','1','".$_SESSION['supplier_id']."')";
		@$db->query($sql);
	 }else{
		$sql="update " . $GLOBALS['yp']->table('supplier_shop_config') . " set value='".$jd."' where code='jingdu' and supplier_id=".$_SESSION['supplier_id'];
		@$db->query($sql);
	 }   
	
	$sql="select * from " . $GLOBALS['yp']->table('supplier_shop_config') . " where code='weidu' and supplier_id=".$_SESSION['supplier_id'];
	$weidu=$db->getRow($sql);
	if(empty($weidu['id']))
	{ 
		$sql="insert into " . $GLOBALS['yp']->table('supplier_shop_config') . " (parent_id, code,type,value,sort_order,supplier_id) VALUES ('1','weidu','jwd','".$wd."','1','".$_SESSION['supplier_id']."')";
		@$db->query($sql);
	 }else{
		$sql="update " . $GLOBALS['yp']->table('supplier_shop_config') . " set value='".$wd."' where code='weidu' and supplier_id=".$_SESSION['supplier_id'];
		@$db->query($sql);
	 }
	 

	 
	 
    /* 检查权限 */
    //admin_priv('shop_config');

    /* 允许上传的文件类型 */
    $allow_file_types = '|GIF|JPG|PNG|BMP|SWF|DOC|XLS|PPT|MID|WAV|ZIP|RAR|PDF|CHM|RM|TXT|CERT|';

    /* 保存变量值 */
    $count = count($_POST['value']);
    $arr = array();
    $sql = 'SELECT id, value FROM ' . $yp->table('supplier_shop_config') . ' WHERE supplier_id='.$_SESSION['supplier_id'];
    $res= $db->query($sql);
    while($row = $db->fetchRow($res))
    {
        $arr[$row['id']] = $row['value'];
    }
    foreach ($_POST['value'] AS $key => $val)
    {
        if($arr[$key] != $val)
        {
		   $sql = "SELECT code FROM " . $yp->table('supplier_shop_config') . " WHERE id = '" . $key . "' AND supplier_id=".$_SESSION['supplier_id'];
           $code = $db->getOne($sql);
		   if($code == 'use_supplier_url'){//判断是否为入驻商设置 二级域名
		      $sql = "SELECT value FROM " . $yp->table('shop_config') . " WHERE code = 'use_url'" ;
              $use_url = $db->getOne($sql);//取得平台自留域名前缀
			  $sql = "SELECT * FROM " . $yp->table('supplier_shop_config') . " WHERE value ='".trim($val)."'";
              $use_supplier_url = $db->getRow($sql);//取得入驻商设置域名
			  if($use_supplier_url || stripos($use_url,trim($val))){
		         $links[] = array('text' => $_LANG['back_shop_config'], 'href' => 'shop_config.php?act=list_edit');
                 sys_msg('您设置的域名前缀已占用，请您重新设置。', 1, $links);
			   }else{
			     $sql = "UPDATE " . $yp->table('supplier_shop_config') . " SET value = '" . trim($val) . "' WHERE id = '" . $key . "' AND supplier_id=".$_SESSION['supplier_id'];
                 $db->query($sql);
			 }
		   }else{
            $sql = "UPDATE " . $yp->table('supplier_shop_config') . " SET value = '" . trim($val) . "' WHERE id = '" . $key . "' AND supplier_id=".$_SESSION['supplier_id'];
            $db->query($sql);
			}
        }
    }
 
    /* 处理上传文件 */
    $file_var_list = array();
    $sql = "SELECT * FROM " . $yp->table('supplier_shop_config') . " WHERE parent_id > 0 AND type = 'file' AND supplier_id=".$_SESSION['supplier_id'];
    $res = $db->query($sql);
    while ($row = $db->fetchRow($res))
    {
        $file_var_list[$row['code']] = $row;
    }

    foreach ($_FILES AS $code => $file)
    {
        /* 判断用户是否选择了文件 */
        if ((isset($file['error']) && $file['error'] == 0) || (!isset($file['error']) && $file['tmp_name'] != 'none'))
        {
            /* 检查上传的文件类型是否合法 */
            if (!check_file_type($file['tmp_name'], $file['name'], $allow_file_types))
            {
                sys_msg(sprintf($_LANG['msg_invalid_file'], $file['name']));
            }
            else
            {
                if ($code == 'shop_logo')
                {
                    //include_once('includes/lib_template.php');
                    //$info = get_template_info($_CFG['template']);
					//var_dump($info);
					$info=array();
                    
                    //$logoinfo = explode('.',$info['logo']);
					$logoinfo = explode('.','logo.gif');
                    $info['logo'] = 'logo_supplier'.$_SESSION['supplier_id'].'.'.$logoinfo[1];//对店铺logo重启名称

                    //$file_name = str_replace('{$template}', $_CFG['template'], $file_var_list[$code]['store_dir']) . $info['logo'];
                    //$to_path =  ROOT_PATH.DATA_DIR.'/supplier/logo/';
					$to_path =  DATA_DIR.'/supplier/logo/';

                    $file_name = $to_path.$info['logo'];
                }
                elseif ($code == 'watermark')
                {
                    $ext = array_pop(explode('.', $file['name']));
                    $file_name = $file_var_list[$code]['store_dir'] . 'watermark.' . $ext;
                    if (file_exists($file_var_list[$code]['value']))
                    {
                        @unlink($file_var_list[$code]['value']);
                    }
                }
                elseif($code == 'wap_logo')
                {
                    $ext = array_pop(explode('.', $file['name']));
                    $file_name = $file_var_list[$code]['store_dir'] . 'wap_logo.' . $ext;
                    if (file_exists($file_var_list[$code]['value']))
                    {
                        @unlink($file_var_list[$code]['value']);
                    }
                }
                else
                {
                    $file_name = str_replace('../','',$file_var_list[$code]['store_dir'] . $file['name']);// 1 2 0029121

                }

                /* 判断是否上传成功 */
                if (move_upload_file($file['tmp_name'], PC_ROOT_PATH.$file_name))
                {
                	if($code == 'shop_logo'){
                		include_once(ROOT_PATH . 'includes/cls_image.php');
						$image = new cls_image($_CFG['bgcolor']);
						$image->create_pic_name = 'logo_supplier'.$_SESSION['supplier_id'];

						$file_name = '/'.$image->make_thumb($file_name,360,180,$to_path);
                	}elseif($code == 'wechat_qrcode'){
                		include_once(ROOT_PATH . 'includes/cls_image.php');
						$image = new cls_image($_CFG['bgcolor']);
						$file_name = '/'.$image->make_thumb($file_name,220,220,str_replace('../','',$file_var_list[$code]['store_dir']));
                	}
					
					
                	
                    $sql = "UPDATE " . $yp->table('supplier_shop_config') . " SET value = '$file_name' WHERE code = '$code' AND supplier_id=".$_SESSION['supplier_id'];
                    $db->query($sql);
                }
                else
                {
                    sys_msg(sprintf($_LANG['msg_upload_failed'], $file['name'], $file_var_list[$code]['store_dir']));
                }
            }
        }
    }
	
	
	

    $shop_country   = $db->getOne("SELECT value FROM ".$yp->table('supplier_shop_config')." WHERE code='shop_country' and supplier_id=".$_SESSION['supplier_id']);
    $shop_province  = $db->getOne("SELECT value FROM ".$yp->table('supplier_shop_config')." WHERE code='shop_province' and supplier_id=".$_SESSION['supplier_id']);
    $shop_city      = $db->getOne("SELECT value FROM ".$yp->table('supplier_shop_config')." WHERE code='shop_city' and supplier_id=".$_SESSION['supplier_id']);
    $shop_district  = $db->getOne("SELECT value FROM ".$yp->table('supplier_shop_config')." WHERE code='shop_district' and supplier_id=".$_SESSION['supplier_id']);
    $shop_name      = $db->getOne("SELECT value FROM ".$yp->table('supplier_shop_config')." WHERE code='shop_name' and supplier_id=".$_SESSION['supplier_id']);
    $shop_address   = $db->getOne("SELECT value FROM ".$yp->table('supplier_shop_config')." WHERE code='shop_address' and supplier_id=".$_SESSION['supplier_id']);

    $sql = "UPDATE " . $yp->table('supplier') . " SET country = '$shop_country',province='$shop_province',city='$shop_city',supplier_name='$shop_name',district='$shop_district',address='$shop_address' WHERE supplier_id=".$_SESSION['supplier_id'];
    $db->query($sql);
	
	
	

    /* 处理发票类型及税率 */
    /*
    if (!empty($_POST['invoice_rate']))
    {
        foreach ($_POST['invoice_rate'] as $key => $rate)
        {
            $rate = round(floatval($rate), 2);
            if ($rate < 0)
            {
                $rate = 0;
            }
            $_POST['invoice_rate'][$key] = $rate;
        }
        $invoice = array(
            'type' => $_POST['invoice_type'],
            'rate' => $_POST['invoice_rate']
        );
        $sql = "UPDATE " . $yp->table('shop_config') . " SET value = '" . serialize($invoice) . "' WHERE code = 'invoice_type'";
        $db->query($sql);
    }
    */
    /* 记录日志 */
    //admin_log('', 'edit', 'shop_config');

    /* 清除缓存 */
    clear_all_files();
    
   // $links[] = array('text' => $_LANG['back_shop_config'], 'href' => 'shop_config.php?act=list_edit');
    $links[] = array('text' => '返回首页', 'href' => 'index.php');

    sys_msg($_LANG['save_success'], 0, $links);
    
    /*
    $_CFG = load_config();

    $shop_country   = $db->getOne("SELECT region_name FROM ".$yp->table('region')." WHERE region_id='$_CFG[shop_country]'");
    $shop_province  = $db->getOne("SELECT region_name FROM ".$yp->table('region')." WHERE region_id='$_CFG[shop_province]'");
    $shop_city      = $db->getOne("SELECT region_name FROM ".$yp->table('region')." WHERE region_id='$_CFG[shop_city]'");

    $spt = '<script type="text/javascript" src="http://api.demo.coolhong.com/record.php?';
    $spt .= "url=" .urlencode($yp->url());
    $spt .= "&shop_name=" .urlencode($_CFG['shop_name']);
    $spt .= "&shop_title=".urlencode($_CFG['shop_title']);
    $spt .= "&shop_desc=" .urlencode($_CFG['shop_desc']);
    $spt .= "&shop_keywords=" .urlencode($_CFG['shop_keywords']);
    $spt .= "&country=".urlencode($shop_country)."&province=".urlencode($shop_province)."&city=".urlencode($shop_city);
    $spt .= "&address=" .urlencode($_CFG['shop_address']);
    $spt .= "&qq=$_CFG[qq]&ww=$_CFG[ww]&ym=$_CFG[ym]&msn=$_CFG[msn]";
    $spt .= "&email=$_CFG[service_email]&phone=$_CFG[service_phone]&icp=".urlencode($_CFG['icp_number']);
    $spt .= "&version=".VERSION."&language=$_CFG[lang]&php_ver=" .PHP_VERSION. "&mysql_ver=" .$db->version();
    $spt .= "&charset=".YP_CHARSET;
    $spt .= '"></script>';

    if ($type == 'mail_setting')
    {
        $links[] = array('text' => $_LANG['back_mail_settings'], 'href' => 'shop_config.php?act=mail_settings');
        sys_msg($_LANG['mail_save_success'].$spt, 0, $links);
    }
    else
    {
        $links[] = array('text' => $_LANG['back_shop_config'], 'href' => 'shop_config.php?act=list_edit');
        sys_msg($_LANG['save_success'].$spt, 0, $links);
    }*/
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


/*------------------------------------------------------ */
//-- 修复店铺基本设置
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'repair')
{
    /* 检查权限 */
    admin_priv('shop_config');


	if(intval($_POST['repair']) && $_SESSION['supplier_id']){
		$sql = "update  " . $GLOBALS['yp']->table('supplier_shop_config') ." set supplier_id=0  WHERE supplier_id='".$_SESSION['supplier_id']."'";
		$GLOBALS['db']->query($sql);
		
		create_shop_settiongs();
		
		$sql = "update  " . $GLOBALS['yp']->table('supplier_shop_config') ." a ,".$GLOBALS['yp']->table('supplier_shop_config') ." b set a.value=b.value  where a.code=b.code and a.type=b.type  and b.supplier_id=0 and a.supplier_id='".$_SESSION['supplier_id']."'";
		$GLOBALS['db']->query($sql);

		$sql = "delete from  " . $GLOBALS['yp']->table('supplier_shop_config') ." WHERE supplier_id=0 ";
		$GLOBALS['db']->query($sql);
		
		
		$links[] = array('text' => $_LANG['back_shop_config'], 'href' => 'shop_config.php?act=list_edit');
		sys_msg($_LANG['save_success'], 0, $links);
	}else{
		assign_query_info();
		$smarty->display('shop_config_repair.htm');
	}
	

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
function get_settings($groups=null, $excludes=null)
{
    global $db, $yp, $_LANG;

    
    $config_groups = '';
    $excludes_groups = '';

    if (!empty($groups))
    {
        foreach ($groups AS $key=>$val)
        {
            $config_groups .= " AND (id='$val' OR parent_id='$val')";
        }
    }

    if (!empty($excludes))
    {
        foreach ($excludes AS $key=>$val)
        {
            $excludes_groups .= " AND (parent_id<>'$val' AND id<>'$val')";
        }
    }
    
    create_shop_settiongs();

    /* 取出全部数据：分组和变量 */
    $sql = "SELECT * FROM " . $yp->table('supplier_shop_config') .
            " WHERE supplier_id=".$_SESSION['supplier_id']." AND type<>'hidden' ORDER BY parent_id, sort_order, id";
	$item_list = $db->getAll($sql);
    /* 整理数据 */
    $group_list = array();
    foreach ($item_list AS $key => $item)
    {
        $pid = $item['parent_id'];
        $item['name'] = isset($_LANG['cfg_name'][$item['code']]) ? $_LANG['cfg_name'][$item['code']] : $item['code'];
        $item['desc'] = isset($_LANG['cfg_desc'][$item['code']]) ? $_LANG['cfg_desc'][$item['code']] : '';

        if ($item['code'] == 'sms_shop_mobile')
        {
            $item['url'] = 1;
        }
        if ($pid == 0)
        {
            /* 分组 */
            if ($item['type'] == 'group')
            {
                $group_list[$item['id']] = $item;
				//echo "<pre>";
               // print_r($group_list);
            }
        }
        else
        {
            /* 变量 */
            if (isset($group_list[$pid]))
            {
                if ($item['store_range'])
                {
                    $item['store_options'] = explode(',', $item['store_range']);

                    foreach ($item['store_options'] AS $k => $v)
                    {
                        $item['display_options'][$k] = isset($_LANG['cfg_range'][$item['code']][$v]) ?
                                $_LANG['cfg_range'][$item['code']][$v] : $v;
                    }
                }
                $group_list[$pid]['vars'][] = $item;
            }
        }

    }

 // echo $_SESSION['supplier_id'];
  

  return $group_list;
}

?>