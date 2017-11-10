<?php

/**
 * QQ120029121 广告管理程序
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: prince $
 * $Id: ads.php 17217 2017-04-01 06:29:08Z prince $
*/

define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . 'includes/cls_image.php');
$image = new cls_image($_CFG['bgcolor']);
$exc   = new exchange($yp->table("ypmart_ad"), $db, 'ad_id', 'ad_name');
$allow_suffix = array('gif', 'jpg', 'png', 'jpeg', 'bmp','swf');
/* act操作项的初始化 */
if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'list';
}
else
{
    $_REQUEST['act'] = trim($_REQUEST['act']);
}
$supplier_id=0;

/*------------------------------------------------------ */
//-- 广告列表页面
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    $pid = !empty($_REQUEST['pid']) ? intval($_REQUEST['pid']) : 0;

    $smarty->assign('ur_here',     $_LANG['ad_list']);
    $smarty->assign('action_link', array('text' => $_LANG['ads_add'], 'href' => 'ads.php?act=add'));
    $smarty->assign('pid',         $pid);
     $smarty->assign('full_page',  1);

    $ads_list = get_adslist();

    $smarty->assign('ads_list',     $ads_list['ads']);
    $smarty->assign('filter',       $ads_list['filter']);
    $smarty->assign('record_count', $ads_list['record_count']);
    $smarty->assign('page_count',   $ads_list['page_count']);

    $sort_flag  = sort_flag($ads_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    assign_query_info();
    $smarty->display('ads_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $ads_list = get_adslist();

    $smarty->assign('ads_list',     $ads_list['ads']);
    $smarty->assign('filter',       $ads_list['filter']);
    $smarty->assign('record_count', $ads_list['record_count']);
    $smarty->assign('page_count',   $ads_list['page_count']);

    $sort_flag  = sort_flag($ads_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('ads_list.htm'), '',
        array('filter' => $ads_list['filter'], 'page_count' => $ads_list['page_count']));
}

/*------------------------------------------------------ */
//-- 添加新广告页面
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add')
{
    admin_priv('ad_manage');
	
	//菜单选择
	$menu = array();
	$menu['id'] = -1;
	$menu['type'] = 'custom';
	$smarty->assign('menu',$menu);

	$goods_list = get_goods_options(0,0);
	$cat_list = cat_list(0, 0, true);
	$page_list = get_page_options();
	$article_list = get_articles_options(0,0);

	$link_type = get_link_type();
	$link_type['goods']['options'] = $goods_list;
	$link_type['goods_cat']['options'] = $cat_list;
	$link_type['page']['options'] = $page_list;
	$link_type['article']['options'] = $article_list;
	$smarty->assign('link_type',$link_type);
	//菜单选择

    $ad_link = empty($_GET['ad_link']) ? '' : trim($_GET['ad_link']);
    $ad_name = empty($_GET['ad_name']) ? '' : trim($_GET['ad_name']);

    $start_time = local_date('Y-m-d');
    $end_time   = local_date('Y-m-d', gmtime() + 3600 * 24 * 30);  // 默认结束时间为1个月以后

    $smarty->assign('ads',
        array('ad_link' => $ad_link, 'ad_name' => $ad_name, 'start_time' => $start_time,
            'end_time' => $end_time, 'enabled' => 1));

    $smarty->assign('ur_here',       $_LANG['ads_add']);
    $smarty->assign('action_link',   array('href' => 'ads.php?act=list', 'text' => $_LANG['ad_list']));
    $smarty->assign('position_list', get_position_list($supplier_id));

    $smarty->assign('form_act', 'insert');
    $smarty->assign('action',   'add');
    $smarty->assign('cfg_lang', $_CFG['lang']);

    assign_query_info();
    $smarty->display('ads_info.htm');
}

/*------------------------------------------------------ */
//-- 新广告的处理
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'insert')
{
    admin_priv('ad_manage');

    /* 初始化变量 */
    $id      = !empty($_POST['id'])      ? intval($_POST['id'])    : 0;
    $type    = !empty($_POST['type'])    ? intval($_POST['type'])  : 0;
    $ad_name = !empty($_POST['ad_name']) ? trim($_POST['ad_name']) : '';

    if ($_POST['media_type'] == '0')
    {
        $ad_link = !empty($_POST['ad_link']) ? trim($_POST['ad_link']) : '';
    }
    else
    {
        $ad_link = !empty($_POST['ad_link2']) ? trim($_POST['ad_link2']) : '';
    }
	
	//菜单保存
	$type = empty($_REQUEST['type']) ? '' : trim($_REQUEST['type']);
	$value = empty($_REQUEST['value']) ? array() : $_REQUEST['value'];
	if($type=='goods_cat'){
		$menu_url="pages/goods/list/list?categoryId=".$value[$type];
	}else{
		$menu_url= $value[$type];
	}
	$ad_link=$menu_url;
	//菜单保存

    /* 获得广告的开始时期与结束日期 */
    $start_time = local_strtotime($_POST['start_time']);
    $end_time   = local_strtotime($_POST['end_time']);

    /* 查看广告名称是否有重复 */
    $sql = "SELECT COUNT(*) FROM " .$yp->table('ypmart_ad'). " WHERE ad_name = '$ad_name'";
    if ($db->getOne($sql) > 0)
    {
        $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
        sys_msg($_LANG['ad_name_exist'], 0, $link);
    }

    /* 添加图片类型的广告 */
    if ($_POST['media_type'] == '0')
    {
        if ((isset($_FILES['ad_img']['error']) && $_FILES['ad_img']['error'] == 0) || (!isset($_FILES['ad_img']['error']) && isset($_FILES['ad_img']['tmp_name'] ) &&$_FILES['ad_img']['tmp_name'] != 'none'))
        {
            $ad_code = basename($image->upload_image($_FILES['ad_img'], 'afficheimg'));
        }
        if (!empty($_POST['img_url']))
        {
            $ad_code = $_POST['img_url'];
        }
        if (((isset($_FILES['ad_img']['error']) && $_FILES['ad_img']['error'] > 0) || (!isset($_FILES['ad_img']['error']) && isset($_FILES['ad_img']['tmp_name']) && $_FILES['ad_img']['tmp_name'] == 'none')) && empty($_POST['img_url']))
        {
            $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
            sys_msg($_LANG['js_languages']['ad_photo_empty'], 0, $link);
        }
    }

    /* 如果添加的广告是Flash广告 */
    elseif ($_POST['media_type'] == '1')
    {
        if ((isset($_FILES['upfile_flash']['error']) && $_FILES['upfile_flash']['error'] == 0) || (!isset($_FILES['upfile_flash']['error']) && isset($_FILES['ad_img']['tmp_name']) && $_FILES['upfile_flash']['tmp_name'] != 'none'))
        {
            /* 检查文件类型 */
            if ($_FILES['upfile_flash']['type'] != "application/x-shockwave-flash")
            {
                $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
                sys_msg($_LANG['upfile_flash_type'], 0, $link);
            }

            /* 生成文件名 */
            $urlstr = date('Ymd');
            for ($i = 0; $i < 6; $i++)
            {
                $urlstr .= chr(mt_rand(97, 122));
            }

            $source_file = $_FILES['upfile_flash']['tmp_name'];
            $target      = ROOT_PATH . DATA_DIR . '/afficheimg/';
            $file_name   = $urlstr .'.swf';

            if (!move_upload_file($source_file, $target.$file_name))
            {
                $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
                sys_msg($_LANG['upfile_error'], 0, $link);
            }
            else
            {
                $ad_code = $file_name;
            }
        }
        elseif (!empty($_POST['flash_url']))
        {
            if (substr(strtolower($_POST['flash_url']), strlen($_POST['flash_url']) - 4) != '.swf')
            {
                $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
                sys_msg($_LANG['upfile_flash_type'], 0, $link);
            }
            $ad_code = $_POST['flash_url'];
        }

        if (((isset($_FILES['upfile_flash']['error']) && $_FILES['upfile_flash']['error'] > 0) || (!isset($_FILES['upfile_flash']['error']) && isset($_FILES['upfile_flash']['tmp_name']) && $_FILES['upfile_flash']['tmp_name'] == 'none')) && empty($_POST['flash_url']))
        {
            $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
            sys_msg($_LANG['js_languages']['ad_flash_empty'], 0, $link);
        }
    }
    /* 如果广告类型为代码广告 */
    elseif ($_POST['media_type'] == '2')
    {
        if (!empty($_POST['ad_code']))
        {
            $ad_code = $_POST['ad_code'];
        }
        else
        {
            $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
            sys_msg($_LANG['js_languages']['ad_code_empty'], 0, $link);
        }
    }

    /* 广告类型为文本广告 */
    elseif ($_POST['media_type'] == '3')
    {
        if (!empty($_POST['ad_text']))
        {
            $ad_code = $_POST['ad_text'];
        }
        else
        {
            $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
            sys_msg($_LANG['js_languages']['ad_text_empty'], 0, $link);
        }
    }
	
	$parent_position_id= $GLOBALS['db']->getOne('SELECT parent_position_id FROM ' . $GLOBALS['yp']->table('ypmart_ad_position'). " where position_id='".intval($_POST['position_id'])."' ");
	$parent_position_id=$parent_position_id?$parent_position_id:0;

    /* 插入数据 */
    $sql = "INSERT INTO ".$yp->table('ypmart_ad'). " (supplier_id,position_id,parent_position_id,media_type,ad_name,type,ad_link,ad_code,start_time,end_time,link_man,link_email,link_phone,click_count,enabled)
    VALUES ('$supplier_id',
			'$_POST[position_id]',
			'$parent_position_id',
            '$_POST[media_type]',
            '$ad_name',
			'$type',
            '$ad_link',
            '$ad_code',
            '$start_time',
            '$end_time',
            '$_POST[link_man]',
            '$_POST[link_email]',
            '$_POST[link_phone]',
            '0',
            '1')";

    $db->query($sql);
    /* 记录管理员操作 */
    admin_log($_POST['ad_name'], 'add', 'ads');

    clear_cache_files(); // 清除缓存文件

    /* 提示信息 */

    $link[0]['text'] = $_LANG['show_ads_template'];
    $link[0]['href'] = 'template.php?act=setup';

    $link[1]['text'] = $_LANG['back_ads_list'];
    $link[1]['href'] = 'ads.php?act=list';

    $link[2]['text'] = $_LANG['continue_add_ad'];
    $link[2]['href'] = 'ads.php?act=add';
    sys_msg($_LANG['add'] . "&nbsp;" .$_POST['ad_name'] . "&nbsp;" . $_LANG['attradd_succed'],0, $link);

}

/*------------------------------------------------------ */
//-- 广告编辑页面
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit')
{
    admin_priv('ad_manage');

    /* 获取广告数据 */
    $sql = "SELECT * FROM " .$yp->table('ypmart_ad'). " WHERE ad_id='".intval($_REQUEST['id'])."'";
    $ads_arr = $db->getRow($sql);
	
	//菜单信息
	$menu=$ads_arr;
	$menu['id'] = intval($_REQUEST['id']);
	$menu['link'] = $menu['ad_link'];

	$smarty->assign('menu',$menu);
	
	if($menu['type'] == 'goods_cat' || $menu['type'] == 'goods' || $menu['type'] == 'article'){
		$hello = explode('=',$menu['link']); 
		$selected = $hello[1];
	}else{
		$selected = $menu['link'];
	}

	$goods_list = get_goods_options(0,$selected);
	$cat_list = cat_list(0, $selected, true);
	$page_list = get_page_options($selected);
	$article_list = get_articles_options(0,$selected);

	$link_type = get_link_type();
	$link_type['goods']['options'] = $goods_list;
	$link_type['goods_cat']['options'] = $cat_list;
	$link_type['page']['options'] = $page_list;
	$link_type['article']['options'] = $article_list;
	$smarty->assign('link_type',$link_type);
	//菜单信息

    $ads_arr['ad_name'] = htmlspecialchars($ads_arr['ad_name']);
    /* 格式化广告的有效日期 */
    $ads_arr['start_time']  = local_date('Y-m-d', $ads_arr['start_time']);
    $ads_arr['end_time']    = local_date('Y-m-d', $ads_arr['end_time']);

    if ($ads_arr['media_type'] == '0')
    {
        if (strpos($ads_arr['ad_code'], 'http://') === false && strpos($ads_arr['ad_code'], 'https://') === false)
        {
            $src = '../' . DATA_DIR . '/afficheimg/'. $ads_arr['ad_code'];
            $smarty->assign('img_src', $src);
        }
        else
        {
            $src = $ads_arr['ad_code'];
            $smarty->assign('url_src', $src);
        }
    }
    if ($ads_arr['media_type'] == '1')
    {
        if (strpos($ads_arr['ad_code'], 'http://') === false && strpos($ads_arr['ad_code'], 'https://') === false)
        {
            $src = '../' . DATA_DIR . '/afficheimg/'. $ads_arr['ad_code'];
            $smarty->assign('flash_url', $src);
        }
        else
        {
            $src = $ads_arr['ad_code'];
            $smarty->assign('flash_url', $src);
        }
        $smarty->assign('src', $src);
    }
    if ($ads_arr['media_type'] == 0)
    {
        $smarty->assign('media_type', $_LANG['ad_img']);
    }
    elseif ($ads_arr['media_type'] == 1)
    {
        $smarty->assign('media_type', $_LANG['ad_flash']);
    }
    elseif ($ads_arr['media_type'] == 2)
    {
        $smarty->assign('media_type', $_LANG['ad_html']);
    }
    elseif ($ads_arr['media_type'] == 3)
    {
        $smarty->assign('media_type', $_LANG['ad_text']);
    }

    $smarty->assign('ur_here',       $_LANG['ads_edit']);
    $smarty->assign('action_link',   array('href' => 'ads.php?act=list', 'text' => $_LANG['ad_list']));
    $smarty->assign('form_act',      'update');
    $smarty->assign('action',        'edit');
    $smarty->assign('position_list', get_position_list($supplier_id));
    $smarty->assign('ads',           $ads_arr);

    assign_query_info();
    $smarty->display('ads_info.htm');
}

/*------------------------------------------------------ */
//-- 广告编辑的处理
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'update')
{
    admin_priv('ad_manage');

    /* 初始化变量 */
    $id   = !empty($_POST['id'])   ? intval($_POST['id'])   : 0;
    $type = !empty($_POST['media_type']) ? intval($_POST['media_type']) : 0;

    if ($_POST['media_type'] == '0')
    {
        $ad_link = !empty($_POST['ad_link']) ? trim($_POST['ad_link']) : '';
    }
    else
    {
        $ad_link = !empty($_POST['ad_link2']) ? trim($_POST['ad_link2']) : '';
    }
	
	//菜单修改
	$type = empty($_REQUEST['type']) ? '' : trim($_REQUEST['type']);
	$value = empty($_REQUEST['value']) ? array() : $_REQUEST['value'];
	if($type=='goods_cat'){
		$menu_url="pages/goods/list/list?categoryId=".$value[$type];
	}else{
		$menu_url= $value[$type];
	}
	$ad_link =$menu_url;
	//菜单修改

    /* 获得广告的开始时期与结束日期 */
    $start_time = local_strtotime($_POST['start_time']);
    $end_time   = local_strtotime($_POST['end_time']);

    /* 编辑图片类型的广告 */
    if ($type == 0)
    {
        if ((isset($_FILES['ad_img']['error']) && $_FILES['ad_img']['error'] == 0) || (!isset($_FILES['ad_img']['error']) && isset($_FILES['ad_img']['tmp_name']) && $_FILES['ad_img']['tmp_name'] != 'none'))
        {
            $img_up_info = basename($image->upload_image($_FILES['ad_img'], 'afficheimg'));
            $ad_code = "ad_code = '".$img_up_info."'".',';
        }
        else
        {
            $ad_code = '';
        }
        if (!empty($_POST['img_url']))
        {
            $ad_code = "ad_code = '$_POST[img_url]', ";
        }
    }

    /* 如果是编辑Flash广告 */
    elseif ($type == 1)
    {
        if ((isset($_FILES['upfile_flash']['error']) && $_FILES['upfile_flash']['error'] == 0) || (!isset($_FILES['upfile_flash']['error']) && isset($_FILES['upfile_flash']['tmp_name']) && $_FILES['upfile_flash']['tmp_name'] != 'none'))
        {
            /* 检查文件类型 */
            if ($_FILES['upfile_flash']['type'] != "application/x-shockwave-flash")
            {
                $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
                sys_msg($_LANG['upfile_flash_type'], 0, $link);
            }
            /* 生成文件名 */
            $urlstr = date('Ymd');
            for ($i = 0; $i < 6; $i++)
            {
                $urlstr .= chr(mt_rand(97, 122));
            }

            $source_file = $_FILES['upfile_flash']['tmp_name'];
            $target      = ROOT_PATH . DATA_DIR . '/afficheimg/';
            $file_name   = $urlstr .'.swf';

            if (!move_upload_file($source_file, $target.$file_name))
            {
                $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
                sys_msg($_LANG['upfile_error'], 0, $link);
            }
            else
            {
                $ad_code = "ad_code = '$file_name', ";
            }
        }
        elseif (!empty($_POST['flash_url']))
        {
            if (substr(strtolower($_POST['flash_url']), strlen($_POST['flash_url']) - 4) != '.swf')
            {
                $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
                sys_msg($_LANG['upfile_flash_type'], 0, $link);
            }
            $ad_code = "ad_code = '".$_POST['flash_url']."', ";
        }
        else
        {
            $ad_code = '';
        }

    }

    /* 编辑代码类型的广告 */
    elseif ($type == 2)
    {
        $ad_code = "ad_code = '$_POST[ad_code]', ";
    }

    /* 编辑文本类型的广告 */
    if ($type == 3)
    {
        $ad_code = "ad_code = '$_POST[ad_text]', ";
    }

    $ad_code = str_replace('../' . DATA_DIR . '/afficheimg/', '', $ad_code);
    /* 更新信息 */
    $sql = "UPDATE " .$yp->table('ypmart_ad'). " SET ".
            "supplier_id     = '$supplier_id', ".
            "position_id = '$_POST[position_id]', ".
            "ad_name     = '$_POST[ad_name]', ".
            "type     = '$type', ".
            "ad_link     = '$ad_link', ".
            $ad_code.
            "start_time  = '$start_time', ".
            "end_time    = '$end_time', ".
            "link_man    = '$_POST[link_man]', ".
            "link_email  = '$_POST[link_email]', ".
            "link_phone  = '$_POST[link_phone]', ".
            "enabled     = '$_POST[enabled]' ".
            "WHERE ad_id = '$id'";
    $db->query($sql);

   /* 记录管理员操作 */
   admin_log($_POST['ad_name'], 'edit', 'ads');

   clear_cache_files(); // 清除模版缓存

   /* 提示信息 */
   $href[] = array('text' => $_LANG['back_ads_list'], 'href' => 'ads.php?act=list');
   sys_msg($_LANG['edit'] .' '.$_POST['ad_name'].' '. $_LANG['attradd_succed'], 0, $href);

}

/*------------------------------------------------------ */
//--生成广告的JS代码
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add_js')
{
    admin_priv('ad_manage');

    /* 编码 */
    $lang_list = array(
        'UTF8'   => $_LANG['charset']['utf8'],
        'GB2312' => $_LANG['charset']['zh_cn'],
        'BIG5'   => $_LANG['charset']['zh_tw'],
    );

    $js_code  = "<script type=".'"'."text/javascript".'"';
    $js_code .= ' src='.'"'.$yp->url().'affiche.php?act=js&type='.$_REQUEST['type'].'&ad_id='.intval($_REQUEST['id']).'"'.'></script>';

    $site_url = $yp->url().'affiche.php?act=js&type='.$_REQUEST['type'].'&ad_id='.intval($_REQUEST['id']);

    $smarty->assign('ur_here',     $_LANG['add_js_code']);
    $smarty->assign('action_link', array('href' => 'ads.php?act=list', 'text' => $_LANG['ad_list']));
    $smarty->assign('url',         $site_url);
    $smarty->assign('js_code',     $js_code);
    $smarty->assign('lang_list',   $lang_list);

    assign_query_info();
    $smarty->display('ads_js.htm');
}

/*------------------------------------------------------ */
//-- 编辑广告名称
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_ad_name')
{
    check_authz_json('ad_manage');

    $id      = intval($_POST['id']);
    $ad_name = json_str_iconv(trim($_POST['val']));

    /* 检查广告名称是否重复 */
    if ($exc->num('ad_name', $ad_name, $id) != 0)
    {
        make_json_error(sprintf($_LANG['ad_name_exist'], $ad_name));
    }
    else
    {
        if ($exc->edit("ad_name = '$ad_name'", $id))
        {
            admin_log($ad_name,'edit','ads');
            make_json_result(stripslashes($ad_name));
        }
        else
        {
            make_json_error($db->error());
        }
    }
}

/*------------------------------------------------------ */
//-- 删除广告位置
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
	if($_SESSION['supplier_name']=='test'){
        make_json_result('对不起，您的权限不足，无法操作！');
	}
	check_authz_json('ad_manage');

    $id = intval($_GET['id']);
    $img = $exc->get_name($id, 'ad_code');

    $exc->drop($id);

    if ((strpos($img, 'http://') === false) && (strpos($img, 'https://') === false))
    {
        $img_name = basename($img);
        @unlink(ROOT_PATH. DATA_DIR . '/afficheimg/'.$img_name);
    }

    admin_log('', 'remove', 'ads');

    $url = 'ads.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    yp_header("Location: $url\n");
    exit;
}

/* 获取广告数据列表 */
function get_adslist()
{
    /* 过滤查询 */
    $pid = !empty($_REQUEST['pid']) ? intval($_REQUEST['pid']) : 0;
	$supplier_id=0;

    $filter = array();
    $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'ad.ad_name' : trim($_REQUEST['sort_by']);
    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

    $where = " WHERE ad.supplier_id='$supplier_id' ";
    if ($pid > 0)
    {
        $where .= " AND ad.position_id = '$pid' ";
    }

    /* 获得总记录数据 */
    $sql = 'SELECT COUNT(*) FROM ' .$GLOBALS['yp']->table('ypmart_ad'). ' AS ad ' . $where;
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);

    $filter = page_and_size($filter);

    /* 获得广告数据 */
    $arr = array();
    $sql = 'SELECT ad.*, COUNT(o.order_id) AS ad_stats, p.position_name '.
            'FROM ' .$GLOBALS['yp']->table('ypmart_ad'). 'AS ad ' .
            'LEFT JOIN ' . $GLOBALS['yp']->table('ypmart_ad_position'). ' AS p ON p.position_id = ad.position_id '.
            'LEFT JOIN ' . $GLOBALS['yp']->table('order_info'). " AS o ON o.from_ad = ad.ad_id $where " .
            'GROUP BY ad.ad_id '.
            'ORDER by '.$filter['sort_by'].' '.$filter['sort_order'];

    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    while ($rows = $GLOBALS['db']->fetchRow($res))
    {
         /* 广告类型的名称 */
         $rows['type']  = ($rows['media_type'] == 0) ? $GLOBALS['_LANG']['ad_img']   : '';
         $rows['type'] .= ($rows['media_type'] == 1) ? $GLOBALS['_LANG']['ad_flash'] : '';
         $rows['type'] .= ($rows['media_type'] == 2) ? $GLOBALS['_LANG']['ad_html']  : '';
         $rows['type'] .= ($rows['media_type'] == 3) ? $GLOBALS['_LANG']['ad_text']  : '';

         /* 格式化日期 */
         $rows['start_date']    = local_date($GLOBALS['_CFG']['date_format'], $rows['start_time']);
         $rows['end_date']      = local_date($GLOBALS['_CFG']['date_format'], $rows['end_time']);

		 if(strpos($rows['ad_code'],'http') === false){
        	 $rows['image']      = "../../data/afficheimg/".$rows['ad_code'];
		 }else{
        	 $rows['image']      = $rows['ad_code'];
		 }
		 
         $arr[] = $rows;
    }

    return array('ads' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

//菜单类型
function get_link_type(){
	return array(
	'custom'=>array('type'=>'custom','name'=>'自定义'),
	'page'=>array('type'=>'page','name'=>'页面(仅适用于小程序)'),
	'goods'=>array('type'=>'goods','name'=>'商品(仅适用于小程序)'),
	'goods_cat'=>array('type'=>'goods_cat','name'=>'商品分类(仅适用于小程序)'),
	'article'=>array('type'=>'article','name'=>'文章(仅适用于小程序)'));
}


function get_page_options($selected=''){
	$page_list = array(
			//array('title'=>'商品分类','page'=>'pages/category/category'),
			//array('title'=>'购物车','page'=>'pages/cart/cart'),
			//array('title'=>'个人中心','page'=>'pages/member/index/index'),
			array('title'=>'我的订单','page'=>'pages/order/list/list'),
			array('title'=>'我的收藏','page'=>'pages/member/collect/collect'),
			array('title'=>'疯狂秒杀','page'=>'pages/goods/grouplist/list'),
			array('title'=>'优惠券','page'=>'pages/member/coupon/index'),
			array('title'=>'我的钱包','page'=>'pages/member/money/money'),
			array('title'=>'我的积分','page'=>'pages/member/point/point'),
			array('title'=>'地址管理','page'=>'pages/address/list/list'),
			array('title'=>'我的评价','page'=>'pages/member/evaluate/evaluate'),
			array('title'=>'联系我们','page'=>'pages/member/aboutus/aboutus'));
	$options = '';
	foreach($page_list as $val)
	{
		$title = $val['title'];
		$page = $val['page'];
		if($val['page'] == $selected)
		{
			$options .= "<option value='$page' selected='selected'>$title</option>";
		}
		else{
			$options .= "<option value='$page'>$title</option>";
		}
	}
	return $options;
}

function get_goods_options($supplier_id,$selected=0){
	$sql="SELECT * FROM ".$GLOBALS['yp']->table('goods')."WHERE is_on_sale=1 AND is_delete=0  ORDER BY goods_id desc ";
	$result = $GLOBALS['db']->getAll($sql);
	$options = '';
	foreach($result as $val)
	{
		$title = $val['goods_name'];
		$page = "pages/goods/detail/detail?objectId=".$val['goods_id'];
		if($val['goods_id'] == $selected)
		{
			$options .= "<option value='$page' selected='selected'>$title</option>";
		}
		else{
			$options .= "<option value='$page'>$title</option>";
		}
	}
	return $options;
}
function get_articles_options($supplier_id,$selected=0){
	$sql="SELECT * FROM ".$GLOBALS['yp']->table('article')."WHERE is_open=1 ORDER BY article_id desc ";
	$result = $GLOBALS['db']->getAll($sql);
	$options = '';
	foreach($result as $val)
	{
		$title = $val['title'];
		$page = "pages/article/detail/index?objectId=".$val['article_id'];
		if($val['article_id'] == $selected)
		{
			$options .= "<option value='$page' selected='selected'>$title</option>";
		}
		else{
			$options .= "<option value='$page'>$title</option>";
		}
	}
	return $options;
}

?>