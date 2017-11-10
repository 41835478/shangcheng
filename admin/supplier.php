<?php

/**
 * QQ120029121 管理中心供货商管理
 * ============================================================================
 * 演示地址: http://demo.coolhong.com；
 * ============================================================================
 * $Author: 今天优品 $
 * $Id: suppliers.php 15013 2016-05-13 09:31:42Z 今天优品 $
 */

define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/supplier.php');
$smarty->assign('lang', $_LANG);


/*------------------------------------------------------ */
//-- 供货商列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    /* 检查权限 */
    admin_priv('supplier_manage');



    /* 查询 */
    $result = suppliers_list();

    /* 模板赋值 */
    $ur_here_lang = $_REQUEST['status'] =='1' ? $_LANG['supplier_list'] : $_LANG['supplier_reg_list'];
    $smarty->assign('ur_here', $ur_here_lang); // 当前导航

    $smarty->assign('full_page',        1); // 翻页参数

    $smarty->assign('status',    $_REQUEST['status']);
    $smarty->assign('supplier_list',    $result['result']);
    $smarty->assign('filter',       $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count',   $result['page_count']);
    $smarty->assign('sort_suppliers_id', '<img src="images/sort_desc.gif">');
    $sql="select rank_id,rank_name from ". $yp->table('supplier_rank') ." order by sort_order";
    $supplier_rank=$db->getAll($sql);
    $smarty->assign('supplier_rank', $supplier_rank);

    /* 显示模板 */
    assign_query_info();
    $smarty->display('supplier_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    check_authz_json('supplier_manage');

    $result = suppliers_list();

    $smarty->assign('supplier_list',    $result['result']);
    $smarty->assign('filter',       $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count',   $result['page_count']);

    /* 排序标记 */
    $sort_flag  = sort_flag($result['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('supplier_list.htm'), '',
        array('filter' => $result['filter'], 'page_count' => $result['page_count']));
}


/*------------------------------------------------------ */
//-- 查看、编辑供货商
/*------------------------------------------------------ */
elseif ($_REQUEST['act']== 'edit')
{
    /* 检查权限 */
    admin_priv('supplier_manage');
    $suppliers = array();

    /* 取得供货商信息 */
    $id = $_REQUEST['id'];
// 	 $status = intval($_REQUEST['status']);
    $sql = "SELECT * FROM " . $yp->table('supplier') . " WHERE supplier_id = '$id'";
    $supplier = $db->getRow($sql);
    if (count($supplier) <= 0)
    {
        sys_msg('该供应商不存在！');
    }

    /* 省市县 */
    $supplier_country = $supplier['country'] ?  $supplier['country'] : $_CFG['shop_country'];
    $smarty->assign('country_list',       get_regions());
    $smarty->assign('province_list', get_regions(1, $supplier_country));
    $smarty->assign('city_list', get_regions(2, $supplier['province']));
    $smarty->assign('district_list', get_regions(3, $supplier['city']));
    $smarty->assign('supplier_country', $supplier_country);
    /* 供货商等级 */
    $sql="select rank_name from ". $yp->table('supplier_rank') ." where rank_id = ".$supplier['rank_id'];
    $rank_name=$db->getOne($sql);
    $supplier['rank_name'] = $rank_name;
    $supplier['start_time'] = date("Y-m-d H:i:s",$supplier['start_time']);
    $supplier['end_time'] = date("Y-m-d H:i:s",$supplier['end_time']);
    // $sql="select rank_id,rank_name from ". $yp->table('supplier_rank') ." order by sort_order";
    //$supplier_rank=$db->getAll($sql);
    //$smarty->assign('supplier_rank', $supplier_rank);

    /* 店铺类型 */
    $sql="select str_name from ". $yp->table('street_category') ." where str_id = ".$supplier['type_id'];
    $type_name=$db->getOne($sql);
    $supplier['type_name'] = $type_name;

    $smarty->assign('ur_here', $_LANG['edit_supplier']);
// 	 $lang_supplier_list = $status=='1' ? $_LANG['supplier_list'] :  $_LANG['supplier_reg_list'];
//      $smarty->assign('action_link', array('href' => 'supplier.php?act=list', 'text' =>$lang_supplier_list ));
    if ($_REQUEST['status'] == '1')
    {
        $lang_supplier_list = $_LANG['supplier_list'];
        $smarty->assign('action_link', array('href' => 'supplier.php?act=list&status=1', 'text' =>$lang_supplier_list ));
    }
    else
    {
        $lang_supplier_list = $_LANG['supplier_reg_list'];
        $smarty->assign('action_link', array('href' => 'supplier.php?act=list', 'text' =>$lang_supplier_list ));
    }

    $smarty->assign('form_action', 'update');
    $smarty->assign('supplier', $supplier);
    /* 代码增加 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 Start */
    // 商品等级
    $smarty->assign('rank_id', $supplier['rank_id']);
    $smarty->assign('supplier_rank_list', get_supplier_rank_list());
    /* 代码增加 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 End */

    assign_query_info();

    $smarty->display('supplier_info.htm');


}

/*------------------------------------------------------ */
//-- 查看供货商佣金日志
/*------------------------------------------------------ */
elseif ($_REQUEST['act']== 'view')
{
    /* 检查权限 */
    admin_priv('supplier_manage');

    /* 查询 */
    $result = rebate_log_list();

    /* 模板赋值 */
    $smarty->assign('ur_here', '佣金日志记录'); // 当前导航

    $smarty->assign('full_page',        1); // 翻页参数

    $smarty->assign('log_list',    $result['result']);
    $smarty->assign('filter',       $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count',   $result['page_count']);
    $smarty->assign('sort_suppliers_id', '<img src="images/sort_desc.gif">');

    /* 显示模板 */
    assign_query_info();
    $smarty->display('supplier_log_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query_log')
{
    check_authz_json('supplier_manage');

    $result = rebate_log_list();

    $smarty->assign('log_list',    $result['result']);
    $smarty->assign('filter',       $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count',   $result['page_count']);
    ;

    make_json_result($smarty->fetch('supplier_log_list.htm'), '',
        array('filter' => $result['filter'], 'page_count' => $result['page_count']));
}

/*------------------------------------------------------ */
//-- 提交添加、编辑供货商
/*------------------------------------------------------ */
elseif ($_REQUEST['act']=='update')
{
	if($_SESSION['admin_name']=='test'){
		sys_msg('警告，你的权限不足，请勿修改！');
	}
	
	/* 检查权限 */
    admin_priv('supplier_manage');

    //审核通过，必须要填写的项目
    /* 代码删除 By demo.coolhong.com 今天优品多商户系统 qq 120029121 Start */
//    if(intval($_POST['status']) == 1){
//        if(intval($_POST['supplier_rebate_paytime'])<=0){
//            sys_msg('结算类型必须选择！');
//        }
//    }
    /* 代码删除 By demo.coolhong.com 今天优品多商户系统 qq 120029121 End */

    /* 提交值 */
    $supplier_id =  intval($_POST['id']);
    $status_url = intval($_POST['status_url']);
    $supplier = array(
        //'rank_id'   => intval($_POST['rank_id']),
        //'address'   => trim($_POST['address']),
        //'tel'   => trim($_POST['tel']),
        //'email'   => trim($_POST['email']),
        //'contact_back'   => trim($_POST['contact_back']),
        //'contact_shop'   => trim($_POST['contact_shop']),
        //'contact_yunying'   => trim($_POST['contact_yunying']),
        //'contact_shouhou'   => trim($_POST['contact_shouhou']),
        //'contact_caiwu'   => trim($_POST['contact_caiwu']),
        //'contact_jishu'   => trim($_POST['contact_jishu']),
        'country'   => intval($_POST['country']),
        'province'   => intval($_POST['province']),
        'city'   => intval($_POST['city']),
        'district'   => intval($_POST['district']),
        'company_name'   => trim($_POST['supplier_name']),
        'address'   => trim($_POST['address']),
        'tel'   => trim($_POST['tel']),
        'guimo'   => trim($_POST['guimo']),
        'company_type'   => trim($_POST['company_type']),
        'email'   => trim($_POST['email']),
        'contacts_name'   => trim($_POST['contacts_name']),
        'contacts_phone'   => trim($_POST['contacts_phone']),
        'id_card_no'   => trim($_POST['id_card_no']),//prince 20161018 

		
        'bank_account_name'	=>	trim($_POST['bank_account_name']),
        'bank_account_number'	=>	trim($_POST['bank_account_number']),
        'bank_name'	=>	trim($_POST['bank_name']),
        'bank_code'	=>	trim($_POST['bank_code']),
        'settlement_bank_account_name'	=>	trim($_POST['settlement_bank_account_name']),
        'settlement_bank_account_number'	=>	trim($_POST['settlement_bank_account_number']),
        'settlement_bank_name'	=>	trim($_POST['settlement_bank_name']),
        'settlement_bank_code'	=>	trim($_POST['settlement_bank_code']),
        /* 代码增加 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 Start */
        'rank_id' => $_POST['rank_id'],
        /* 代码增加 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 End */

        //'system_fee'   => trim($_POST['system_fee']),
        'supplier_bond'   => trim($_POST['supplier_bond']),
        'supplier_rebate'   => trim($_POST['supplier_rebate']),
		'my_supplier_rebate'   => trim($_POST['my_supplier_rebate']),//寒冰  20161003   qq   3094  8555 2
        'supplier_rebate_paytime'   => intval($_POST['supplier_rebate_paytime']),
        'supplier_remark'   => trim($_POST['supplier_remark']),
	    'supplier_name'   => trim($_POST['supplier_name']),//2017.03.10 修复平台编辑商家店铺名称无效bug
        'status'   => intval($_POST['status']),
        'start_time' => strtotime($_POST['start_time']),
        'end_time'   => strtotime($_POST['end_time']),
        'need_approve'   => intval($_POST['need_approve']) // 20170724 新增审核控制
		
    );
   
    /* 代码增加_start  By  demo.coolhong.com */
    /* 取得供货商信息 */
    //$sql = "SELECT * FROM " . $yp->table('supplier') . " WHERE supplier_id = '" . $supplier_id ."' ";
    $sql = "select s.supplier_id,s.add_time,s.rank_id,s.status,s.start_time,s.end_time,u.user_name,u.headimg,u.nickname,u.user_id,u.email,u.password,u.ec_salt,u.last_login,u.last_ip from " . $yp->table('supplier') . " as s left join ". $yp->table('users') .
        " as u on s.user_id=u.user_id where s.supplier_id=".$supplier_id;
    $supplier_old = $db->getRow($sql);
    if (empty($supplier_old['supplier_id']))
    {
        sys_msg('该供货商信息不存在！');
    }

	/*$sql = "SELECT COUNT(*) FROM " . $GLOBALS['yp']->table('supplier') . "WHERE status = 1";
    $supplier_count   = $GLOBALS['db']->getOne($sql);
    if ($supplier_count > $_CFG['opening_account_number'] && $supplier['status'] == 1 ) {
      sys_msg('可开户数量超出上限，请联系技术客服！');   
    }
    */  //寒冰  20170910 
    if(empty($supplier_old['add_time']) && $supplier['status'] == 1 ){
        //审核通过时就是店铺创建成功的时间
        $supplier['add_time'] = time();
        $supplier['syskey'] = md5($supplier_id.'-'.$supplier['add_time']);
         //审核通过时就是店铺套餐开始时间
        $supplier['start_time'] = time();
        $sql="select available_days from ". $yp->table('supplier_rank') ." where rank_id = ".$supplier['rank_id'];
        $available_days = $db->getOne($sql);//取出套餐有效期天数
        $supplier['end_time'] = $supplier['start_time'] + $available_days * 86400;
        // 取出 用户选择的套餐等级
       $sql = "select * from ". $yp->table('supplier_rank') ." where rank_id = ".$supplier['rank_id'];
       $supplier_rank = $db->getRow($sql);
       $supplier['system_fee'] = $supplier_rank['price'] ? $supplier_rank['price']: '0.00'; //平台使用费按照套餐费用计算 
    }
  
    //更改用户套餐
   if($supplier['rank_id'] != $supplier_old['rank_id']){
    if ($supplier['start_time'] == $supplier_old['start_time']) {
        $supplier['start_time'] = time();
      }

    if ($supplier['end_time'] == $supplier_old['end_time']) {
        $sql="select available_days from ". $yp->table('supplier_rank') ." where rank_id = ".$supplier['rank_id'];
        $available_days = $db->getOne($sql);//取出套餐有效期天数
        $supplier['end_time'] = $supplier['start_time'] + $available_days * 86400;
      }
       // 取出 用户选择的套餐等级
       $sql = "select * from ". $yp->table('supplier_rank') ." where rank_id = ".$supplier['rank_id'];
       $supplier_rank = $db->getRow($sql);
       $supplier['system_fee'] = $supplier_rank['price'] ? $supplier_rank['price']: '0.00'; //平台使用费按照套餐费用计算 
       
     }   
    //操作店铺商品与店铺街信息
    if($supplier['status'] != $supplier_old['status'] && $supplier['status'] == -1){
        //审核不通过
        //店铺街信息失效
        $check_info = array(
            'is_groom' => 0,
            'is_show' => 0,
            'supplier_notice' => '',
            'status' => 0
        );
        $db->autoExecute($yp->table('supplier_street'), $check_info, 'UPDATE', "supplier_id = '" . $supplier_id . "'");
        //商品下架
        $good_info = array(
            'is_on_sale' => 0
        );
        $db->autoExecute($yp->table('goods'), $good_info, 'UPDATE', "supplier_id = '" . $supplier_id . "'");
        //删除店铺所在的标签
        $db->query('delete FROM '.$yp->table('supplier_tag_map').' WHERE supplier_id = '.$supplier_id);
    }

    //更新相关店铺的管理员状态
    $sql = "select * from ". $yp->table('supplier_admin_user') ." where supplier_id=".$supplier_old['supplier_id'];
    $info = $db->getAll($sql);

    if(count($info)>0){
		$supplier_old['user_id']=$supplier_old['user_id']?$supplier_old['user_id']:0;
        $sql = "UPDATE ". $yp->table('supplier_admin_user') ." SET user_name = '".$supplier_old['user_name']."',password = '".$supplier_old['password']."',email='".$supplier_old['email']."',ec_salt='".$supplier_old['ec_salt']."', checked = ".intval($_POST['status'])." WHERE supplier_id=".$supplier_old['supplier_id']." and uid='".$supplier_old['user_id']."' and uid>0";//prince 20170729
        $db->query($sql);
		if($supplier['status'] != $supplier_old['status'] && $supplier['status'] == 1){//店铺审核通过
		   $sql = "UPDATE ". $yp->table('supplier_admin_user') ." SET  action_list = 'all' WHERE supplier_id=".$supplier_old['supplier_id']." and uid='".$supplier_old['user_id']."'";
          $db->query($sql);//2017.03.12   寒冰  qq  309485552     修复   新申请商家 管理员权限不完整问题（如果已有店铺管理员权限不完整   请把店铺状态设为   未审核  然后  重新审核通过即可）
		}
    }else{
        $insql = "INSERT INTO " . $yp->table('supplier_admin_user') . " (`uid`, `user_name`, `email`, `password`, `ec_salt`, `add_time`, `last_login`, `last_ip`, `action_list`, `nav_list`, `lang_type`, `agency_id`, `supplier_id`, `todolist`, `role_id`, `checked`) ".
            "VALUES(".$supplier_old['user_id'].", '".$supplier_old['user_name']."', '".$supplier_old['email']."', '".$supplier_old['password']."', '".$supplier_old['ec_salt']."', ".$supplier_old['last_login'].", ".$supplier_old['last_login'].", '".$supplier_old['last_ip']."', 'all', '', '', 0, ".$supplier_old['supplier_id'].", NULL, NULL, ".intval($_POST['status']).")";
        $db->query($insql);
    }
    /* 代码增加_end  By  demo.coolhong.com */

    /* 保存供货商信息 */
    $db->autoExecute($yp->table('supplier'), $supplier, 'UPDATE', "supplier_id = '" . $supplier_id . "'");

    if ($_POST['status']!='1')
    {
        $sql="update ". $yp->table('goods') ." set is_on_sale=0 where supplier_id='$supplier_id' ";
        $db->query($sql);
    }

    /* 清除缓存 */
    clear_cache_files();

    /* 提示信息 */
    $links[] = array('href' => ($status_url >0 ? 'supplier.php?act=list&status=1' : 'supplier.php?act=list'), 'text' => ($status_url >0 ? $_LANG['back_supplier_list'] : $_LANG['back_supplier_reg']));
    sys_msg($_LANG['edit_supplier_ok'], 0, $links);

}

//删除店铺信息
elseif ($_REQUEST['act'] == 'delete'){
    
	if($_SESSION['admin_name']=='test'){
		sys_msg('警告，你的权限不足，请勿修改！');
	}
	
	/* 检查权限 */
    admin_priv('supplier_manage');
    $supplier_id =  intval($_GET['id']);

    $sql = "SELECT * FROM " . $yp->table('supplier') . " WHERE supplier_id = ".$supplier_id;
    $supplier = $db->getRow($sql);
    if (count($supplier) <= 0)
    {
        sys_msg('数据不存在！');
    }


    if($supplier_id > 0){
        $ret = array();
        //入驻商相关删除信息
        $supplier_info = array(
            'delete FROM '.$yp->table('supplier_admin_user').' WHERE supplier_id = '.$supplier_id,
            'delete FROM '.$yp->table('supplier_article').' WHERE supplier_id = '.$supplier_id,
            'delete FROM '.$yp->table('supplier_article_cat').' WHERE supplier_id = '.$supplier_id,
            'delete FROM '.$yp->table('supplier_category').' WHERE supplier_id = '.$supplier_id,
            'delete FROM '.$yp->table('supplier_cat_recommend').' WHERE supplier_id = '.$supplier_id,
            'delete FROM '.$yp->table('supplier_goods_cat').' WHERE supplier_id = '.$supplier_id,
            'delete FROM '.$yp->table('supplier_guanzhu').' WHERE supplierid = '.$supplier_id,
            'delete FROM '.$yp->table('supplier_money_log').' WHERE supplier_id = '.$supplier_id,
            'delete FROM '.$yp->table('supplier_nav').' WHERE supplier_id = '.$supplier_id,
            /* 代码删除 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 Start */
//            'delete FROM '.$yp->table('supplier_rebate_log').' WHERE rebateid in (SELECT rebate_id FROM '.$yp->table('supplier_rebate').' WHERE supplier_id ='.$supplier_id.')',
            /* 代码删除 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 End */
            'delete FROM '.$yp->table('supplier_shop_config').' WHERE supplier_id = '.$supplier_id,
            'delete FROM '.$yp->table('supplier_street').' WHERE supplier_id = '.$supplier_id,
            'delete FROM '.$yp->table('supplier_tag_map').' WHERE supplier_id = '.$supplier_id,
            'delete FROM '.$yp->table('ypmart_ad').' WHERE supplier_id = '.$supplier_id,
            'delete FROM '.$yp->table('ypmart_ad_position').' WHERE supplier_id = '.$supplier_id,
            'delete FROM '.$yp->table('weixin_xcx_config').' WHERE supplier_id = '.$supplier_id,
            'delete FROM '.$yp->table('weixin_xcx_menu').' WHERE supplier_id = '.$supplier_id,
            'delete FROM '.$yp->table('weixin_xcx_template').' WHERE supplier_id = '.$supplier_id
        );
        /* 代码增加 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 Start */
//        if($db->getOne('SELECT COUNT(*) FROM ' . $yp->table('supplier_rebate') . ' WHERE supplier_id = ' . $supplier_id))
//        {
//            array_push($supplier_info, 'delete FROM '.$yp->table('supplier_rebate_log').' WHERE rebateid in (SELECT rebate_id FROM '.$yp->table('supplier_rebate').' WHERE supplier_id ='.$supplier_id.')');
//        }
        /* 代码增加 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 End */
        foreach($supplier_info as $sk=>$sv){
            if($db->query($sv)){}else{
                $ret[] = $sv;
            }
        }
        delete_supplier_pic($supplier_id);
        //商品相关删除信息
        $goods_info = array(
            'delete FROM '.$yp->table('goods_activity').' WHERE goods_id in (SELECT goods_id FROM '.$yp->table('goods').' WHERE supplier_id ='.$supplier_id.')',
            'delete FROM '.$yp->table('goods_attr').' WHERE goods_id in (SELECT goods_id FROM '.$yp->table('goods').' WHERE supplier_id ='.$supplier_id.')',
            'delete FROM '.$yp->table('goods_cat').' WHERE goods_id in (SELECT goods_id FROM '.$yp->table('goods').' WHERE supplier_id ='.$supplier_id.')',
            'delete FROM '.$yp->table('goods_gallery').' WHERE goods_id in (SELECT goods_id FROM '.$yp->table('goods').' WHERE supplier_id ='.$supplier_id.')',
            'delete FROM '.$yp->table('goods_tag').' WHERE goods_id in (SELECT goods_id FROM '.$yp->table('goods').' WHERE supplier_id ='.$supplier_id.')',
            'delete FROM '.$yp->table('products').' WHERE goods_id in (SELECT goods_id FROM '.$yp->table('goods').' WHERE supplier_id ='.$supplier_id.')'
        );
        foreach($goods_info as $gk=>$gv){
            if($db->query($gv)){}else{
                $ret[] = $gv;
            }
        }
        //最后删除中间表信息
        $other_info = array(
            'delete FROM '.$yp->table('goods').' WHERE supplier_id = '.$supplier_id,
            'delete FROM '.$yp->table('supplier').' WHERE supplier_id = '.$supplier_id
//            'delete FROM '.$yp->table('supplier_rebate').' WHERE supplier_id = '.$supplier_id
        );
        foreach($other_info as $ok=>$ov){
            if($db->query($ov)){}else{
                $ret[] = $ov;
            }
        }

    }
    if(count($ret)>0){
        echo "如下删除语句执行失败:";
        echo "<pre>";
        print_r($ret);
        sleep(10);
    }

    /* 提示信息 */
    $links[0] = array('href' => 'supplier.php?act=list&status='.$supplier['status'], 'text' =>'返回上一页');
    sys_msg('删除成功!',0,$links);
}

/* 代码增加 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 Start */
/*------------------------------------------------------ */
//-- 批量导出供货商
/*------------------------------------------------------ */
elseif ($_REQUEST['act']=='export')
{
    $where = " WHERE s.applynum = 3 AND s.status = 1 ";

    // 入驻商名称
    if (isset($_REQUEST['supplier_name']) && !empty($_REQUEST['supplier_name']))
    {
        $where .= " AND s.supplier_name LIKE '%" . mysql_like_quote($_REQUEST['supplier_name']) . "%'";
    }
    // 入驻商等级
    if (isset($_REQUEST['rank_id']) && !empty($_REQUEST['rank_id']))
    {
        $where .= " AND s.rank_id = " . $_REQUEST['rank_id'];
    }

    /* 查询 */
    $sql = "SELECT "
        . "u.user_name, " // 会员名称
        . "s.supplier_name, " // 入驻商名称
        . "s.rank_id, " // 入驻商等级
        . "s.tel, " // 公司电话
        . "s.system_fee, " // 平台使用费
        . "s.supplier_bond, " // 商家保证金
        . "s.supplier_rebate, " // 分成利率
        . "s.supplier_remark, " // 入驻商备注
        . "s.status " // 状态
        . "FROM "
        . $GLOBALS['yp']->table("supplier") . " AS s LEFT JOIN "
        . $GLOBALS['yp']->table("users") . " AS u ON s.user_id = u.user_id "
        . $where;

    $res = $GLOBALS['db']->getAll($sql);

    // 引入phpexcel核心类文件
    require_once ROOT_PATH . '/includes/phpexcel/Classes/PHPExcel.php';
    // 实例化excel类
    $objPHPExcel = new PHPExcel();
    // 操作第一个工作表
    $objPHPExcel->setActiveSheetIndex(0);
    // 设置sheet名
    $objPHPExcel->getActiveSheet()->setTitle('入驻商列表');
    // 设置表格宽度
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
    // 列名表头文字加粗
    $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getFont()->setBold(true);
    // 列表头文字居中
    $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    // 列名赋值
    $objPHPExcel->getActiveSheet()->setCellValue('A1', '会员名称');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', '入驻商名称');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', '入驻商等级');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', '公司电话');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', '平台使用费');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', '商家保证金');
    $objPHPExcel->getActiveSheet()->setCellValue('G1', '分成利率');
    $objPHPExcel->getActiveSheet()->setCellValue('H1', '入驻商备注');
    $objPHPExcel->getActiveSheet()->setCellValue('I1', '状态');

    // 数据起始行
    $row_num = 2;
    // 向每行单元格插入数据
    foreach($res as $value)
    {
        // 入驻商等级
        switch ($value['rank_id'])
        {
            case 1:
                $rank_name = '初级店铺';
                break;
            case 2:
                $rank_name = '中级店铺';
                break;
            case 3:
                $rank_name = '高级店铺';
                break;
            default:
                $rank_name = '';
        }

        // 设置所有垂直居中
        $objPHPExcel->getActiveSheet()->getStyle('A' . $row_num . ':' . 'I' . $row_num)->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        // 设置平台使用费和商家保证金为数字格式
        $objPHPExcel->getActiveSheet()->getStyle('E' . $row_num . ':' . 'F' . $row_num)->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
        // 设置分成利率为数字格式
        $objPHPExcel->getActiveSheet()->getStyle('G' . $row_num)->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);

        // 设置单元格数值
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('A' . $row_num, $value['user_name'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $row_num, $value['supplier_name']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $row_num, $rank_name);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row_num, $value['tel'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $row_num, $value['system_fee']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $row_num, $value['supplier_bond']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $row_num, $value['supplier_rebate']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $row_num, $value['supplier_remark']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $row_num, $value['status'] ? '通过' : '');
        $row_num++;
    }
    $outputFileName = '入驻商_' . time() . '.xls';
    $xlsWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");
    header('Content-Disposition:inline;filename="' . $outputFileName . '"');
    header("Content-Transfer-Encoding: binary");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Pragma: no-cache");
    $xlsWriter->save("php://output");
    echo file_get_contents($outputFileName);
}
/* 代码增加 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 End */

/**
 *  获取供应商列表信息
 *
 * @access  public
 * @param
 *
 * @return void
 */
function suppliers_list()
{
    $result = get_filter();
    if ($result === false)
    {
        $aiax = isset($_GET['is_ajax']) ? $_GET['is_ajax'] : 0;

        /* 过滤信息 */
        $filter['supplier_name'] = empty($_REQUEST['supplier_name']) ? '' : trim($_REQUEST['supplier_name']);
        $filter['rank_name'] = empty($_REQUEST['rank_name']) ? '' : trim($_REQUEST['rank_name']);
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'supplier_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'ASC' : trim($_REQUEST['sort_order']);
        $filter['status'] = empty($_REQUEST['status']) ? '0' : intval($_REQUEST['status']);

        $where = 'WHERE applynum = 3 ';
        $where .= $filter['status'] ? " AND s.status = '". $filter['status']. "' " : " AND s.status in('0','-1') ";
        if ($filter['supplier_name'])
        {
            $where .= " AND supplier_name LIKE '%" . mysql_like_quote($filter['supplier_name']) . "%'";
        }
        if ($filter['rank_name'])
        {
            $where .= " AND rank_id = '$filter[rank_name]'";
        }

        /* 分页大小 */
        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)
        {
            $filter['page_size'] = intval($_REQUEST['page_size']);
        }
        elseif (isset($_COOKIE['YPCP']['page_size']) && intval($_COOKIE['YPCP']['page_size']) > 0)
        {
            $filter['page_size'] = intval($_COOKIE['YPCP']['page_size']);
        }
        else
        {
            $filter['page_size'] = 15;
        }

        /* 记录总数 */
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['yp']->table('supplier') ." as s ". $where;
        $filter['record_count']   = $GLOBALS['db']->getOne($sql);
        $filter['page_count']     = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        /* 查询 */
        $sql = "SELECT s.supplier_id,s.start_time,s.end_time,u.user_name,u.nickname,u.headimg, rank_id, supplier_name,s.company_name, tel, system_fee, supplier_bond, supplier_rebate, supplier_remark, need_approve, ".
            "s.status ".
            "FROM " . $GLOBALS['yp']->table("supplier") . " as s left join " . $GLOBALS['yp']->table("users") . " as u on s.user_id = u.user_id
                $where
                ORDER BY " . $filter['sort_by'] . " " . $filter['sort_order']. "
                LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ", " . $filter['page_size'] . " ";

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $rankname_list =array();
    $sql2 = "select * from ". $GLOBALS['yp']->table("supplier_rank") ;
    $res2 = $GLOBALS['db']->query($sql2);
    while ($row2=$GLOBALS['db']->fetchRow($res2))
    {
        $rankname_list[$row2['rank_id']] = $row2['rank_name'];
    }

    $list=array();
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['rank_name'] = $rankname_list[$row['rank_id']];
		
		if(!empty($row['headimg'])){
			if(strpos($row['headimg'],'http') === false){
				$row['headimg'] = './../'.$row['headimg'];
			}
		}else{
			$row['headimg']='';
		}
		
        $row['status_name'] = $row['status']=='1' ? '通过' : ($row['status']=='0' ? "未审核" : "未通过");
		$row['user_id'] = $GLOBALS['db']->getOne("select user_id from ".$GLOBALS['yp']->table("supplier_admin_user")." where supplier_id=".$row['supplier_id']);//prince
        $open = $GLOBALS['db']->getRow("select value from ".$GLOBALS['yp']->table("supplier_shop_config")." where supplier_id=".$row['supplier_id']." and code='shop_closed'");
        $row['start_time'] = date("Y-m-d H:i:s",$row['start_time']);
        $row['end_time'] = date("Y-m-d H:i:s",$row['end_time']);
        if($open && $open['value'] == 0){
            $row['open'] = 1;
        }else{
            $row['open'] = 0;
        }
        $list[]=$row;
    }

    $arr = array('result' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}
/*
* 入驻商的佣金记录
*/
function rebate_log_list()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 过滤信息 */
        $filter['id'] = intval($_REQUEST['id']);
        $filter['addtime_start'] = !empty($_REQUEST['addtime_start']) ? local_strtotime($_REQUEST['addtime_start']) : 0;
        $filter['addtime_end'] = !empty($_REQUEST['addtime_end']) ? local_strtotime($_REQUEST['addtime_end']." 23:59:59") : 0;
        $filter['status'] = empty($_REQUEST['status']) ? '0' : intval($_REQUEST['status']);

        $where = ' WHERE supplier_id = '.$filter['id'];
        $where .= $filter['addtime_start'] ? " AND addtime >= '". $filter['addtime_start']. "' " :  " ";
        $where .= $filter['addtime_end'] ? " AND addtime_end <= '". $filter['addtime_end']. "' " :  " ";
        $where .= $filter['status']>0 ? " AND status = '". $filter['status']. "' " : "";

        /* 分页大小 */
        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)
        {
            $filter['page_size'] = intval($_REQUEST['page_size']);
        }
        elseif (isset($_COOKIE['YPCP']['page_size']) && intval($_COOKIE['YPCP']['page_size']) > 0)
        {
            $filter['page_size'] = intval($_COOKIE['YPCP']['page_size']);
        }
        else
        {
            $filter['page_size'] = 15;
        }

        /* 记录总数 */
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['yp']->table('supplier_money_log') . $where;
        $filter['record_count']   = $GLOBALS['db']->getOne($sql);
        $filter['page_count']     = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        /* 查询 */
        $sql = "SELECT * ".
            "FROM " . $GLOBALS['yp']->table("supplier_money_log") . $where ."
                ORDER BY addtime desc 
                LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ", " . $filter['page_size'] . " ";

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $list=array();
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['add_time'] = date("Y-m-d H:i:s",$row['addtime']);
        $list[]=$row;
    }

    $arr = array('result' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

/*
删除店铺下所有商品的上传的图片
*/
function delete_supplier_pic($suppid){
    global $db,$yp;

    $sql = "select goods_thumb,goods_img,original_img from ".$yp->table('goods')." where supplier_id=".$suppid;

    $query = $db->query($sql);
    while($row = $db->fetchRow($query)){
        @unlink(ROOT_PATH.$row['goods_thumb']);
        @unlink(ROOT_PATH.$row['goods_img']);
        @unlink(ROOT_PATH.$row['original_img']);
    }

    $sql = "select gg.img_url,gg.thumb_url,gg.img_original from ".$yp->table('goods_gallery')." as gg,".$yp->table('goods')." as g where g.supplier_id=".$suppid." and g.goods_id=gg.goods_id";

    $query = $db->query($sql);
    while($row = $db->fetchRow($query)){
        @unlink(ROOT_PATH.$row['img_url']);
        @unlink(ROOT_PATH.$row['thumb_url']);
        @unlink(ROOT_PATH.$row['img_original']);
    }
}

/* 代码增加 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 Start */
/**
 * 取得店铺等级列表
 * @return array 店铺等级列表 id => name
 */
function get_supplier_rank_list()
{
    $sql = 'SELECT rank_id, rank_name FROM ' . $GLOBALS['yp']->table('supplier_rank') . ' ORDER BY sort_order';
    $res = $GLOBALS['db']->getAll($sql);

    $rank_list = array();
    foreach ($res AS $row)
    {
        $rank_list[$row['rank_id']] = addslashes($row['rank_name']);
    }

    return $rank_list;
}
/* 代码增加 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 End */
?>