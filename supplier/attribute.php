<?php

/**
 * QQ120029121 属性规格管理
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: PRINCE $
 * $Id: attribute.php 17217 2017-04-01 06:29:08Z PRINCE $
*/

define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');

/* act操作项的初始化 */
$_REQUEST['act'] = trim($_REQUEST['act']);
if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'list';
}

$exc = new exchange($yp->table("attribute"), $db, 'attr_id', 'attr_name');
$supplier_id = $_SESSION['supplier_id'];

/*------------------------------------------------------ */
//-- 属性列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    $goods_type = isset($_GET['goods_type']) ? intval($_GET['goods_type']) : 0;

    $smarty->assign('ur_here',          $_LANG['09_attribute_list']);
    $smarty->assign('action_link',      array('href' => 'attribute.php?act=add&goods_type='.$goods_type , 'text' => $_LANG['10_attribute_add']));
    $smarty->assign('goods_type_list',  goods_typelist($goods_type)); // 取得商品类型
    $smarty->assign('full_page',        1);

    $list = get_attrlist();

    $smarty->assign('attr_list',    $list['item']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    /* 显示模板 */
    assign_query_info();
    $smarty->display('attribute_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、翻页
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'query')
{
    $list = get_attrlist();

    $smarty->assign('attr_list',    $list['item']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('attribute_list.htm'), '',
        array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}

/*------------------------------------------------------ */
//-- 添加/编辑属性
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit')
{
    /* 检查权限 */
    admin_priv('attr_manage');

    /* 添加还是编辑的标识 */
    $is_add = $_REQUEST['act'] == 'add';
    $smarty->assign('form_act', $is_add ? 'insert' : 'update');

    /* 取得属性信息 */
    if ($is_add)
    {
        $goods_type = isset($_GET['goods_type']) ? intval($_GET['goods_type']) : 0;
        $attr = array(
            'attr_id' => 0,
            'cat_id' => $goods_type,
            'attr_name' => '',
            'attr_input_type' => 0,
            'attr_index'  => 0,
            'attr_values' => '',
            'attr_type' => 0,
            'is_linked' => 0,
			 'attr_txm' => 0,
        );
    }
    else
    {
        $sql = "SELECT * FROM " .$GLOBALS['yp']->table('attribute') ." as a inner join " . $GLOBALS['yp']->table('goods_type') . " as g on a.cat_id = g.cat_id where g.supplier_id='$supplier_id' AND a.attr_id=" . $_REQUEST['attr_id'];

       // $sql = "SELECT * FROM " . $yp->table('attribute') . " WHERE attr_id = '$_REQUEST[attr_id]'";
        $attr = $db->getRow($sql);

        if (empty($attr)) {
            $links[] = array('text' => $_LANG['09_attribute_list'], 'href' => 'attribute.php?act=list');
           sys_msg('非法操作！', 1 ,$links);

       }
      }
    $smarty->assign('attr', $attr);
    $smarty->assign('attr_groups', get_attr_groups($attr['cat_id']));

    /* 取得商品分类列表 */
    $smarty->assign('goods_type_list', goods_typelist($attr['cat_id']));

    /* 模板赋值 */
    $smarty->assign('ur_here', $is_add ?$_LANG['10_attribute_add']:$_LANG['52_attribute_add']);
    $smarty->assign('action_link', array('href' => 'attribute.php?act=list', 'text' => $_LANG['09_attribute_list']));

    /* 显示模板 */
    assign_query_info();
    $smarty->display('attribute_info.htm');
}

/*------------------------------------------------------ */
//-- 插入/更新属性
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update')
{
    /* 检查权限 */
    admin_priv('attr_manage');

    /* 插入还是更新的标识 */
    $is_insert = $_REQUEST['act'] == 'insert';

    /* 检查名称是否重复 */
    $exclude = empty($_POST['attr_id']) ? 0 : intval($_POST['attr_id']);
    if (!$exc->is_only('attr_name', $_POST['attr_name'], $exclude, " cat_id = '$_POST[cat_id]'"))
    {
        sys_msg($_LANG['name_exist'], 1);
    }

    $cat_id = $_REQUEST['cat_id'];

    /* 取得属性信息 */
    $attr = array(
        'cat_id'          => $_POST['cat_id'],
        'attr_name'       => $_POST['attr_name'],
        'attr_index'      => $_POST['attr_index'],
        'attr_input_type' => $_POST['attr_input_type'],
        'is_linked'       => $_POST['is_linked'],
		'attr_txm'		  => $_POST['attr_txm'],
        'attr_values'     => isset($_POST['attr_values']) ? $_POST['attr_values'] : '',
        'attr_type'       => empty($_POST['attr_type']) ? '0' : intval($_POST['attr_type']),
		
        'attr_group'      => isset($_POST['attr_group']) ? intval($_POST['attr_group']) : 0
    );

    /* 入库、记录日志、提示信息 */
    if ($is_insert)
    {
        $db->autoExecute($yp->table('attribute'), $attr, 'INSERT');
      	$insert_id=$db->insert_id();
        admin_log($_POST['attr_name'], 'add', 'attribute');
        $links = array(
            array('text' => $_LANG['add_next'], 'href' => '?act=add&goods_type=' . $_POST['cat_id']),
            array('text' => $_LANG['back_list'], 'href' => '?act=list'),
        );
        //将下面代码注释掉  By demo.coolhong.com 今天优品多商户系统 Q Q 1200 2912 1
        //sys_msg(sprintf($_LANG['add_ok'], $attr['attr_name']), 0, $links);
    }
    else
    {
        $db->autoExecute($yp->table('attribute'), $attr, 'UPDATE', "attr_id = '$_POST[attr_id]'");
        admin_log($_POST['attr_name'], 'edit', 'attribute');
        $links = array(
            array('text' => $_LANG['back_list'], 'href' => '?act=list&amp;goods_type='.$_POST['cat_id'].''),
        );
        //将下面代码注释掉  By demo.coolhong.com 今天优品多商户系统 Q Q 1200 2912 1
        //sys_msg(sprintf($_LANG['edit_ok'], $attr['attr_name']), 0, $links);
    }

	/* 增加代码_start By demo.coolhong.com 今天优品多商户系统 Q Q 1200 2912 1 */
	$attr_id_qq_wx_120029121 = $is_insert ? $insert_id : $_POST['attr_id'];

	$msg_attr_qq_wx_120029121 = $is_insert ?   $_LANG['add_ok']  : $_LANG['edit_ok'];
	if($_POST['is_attr_gallery'] == '1')
	{
		$sql_qq_wx_120029121="update " .$yp->table("attribute"). " set  is_attr_gallery=0 where cat_id='".$_POST['cat_id']."' ";
		$db->query($sql_qq_wx_120029121);
	}
	$sql_qq_wx_120029121="update " .$yp->table("attribute"). " set  is_attr_gallery='$_POST[is_attr_gallery]' where attr_id='$attr_id_qq_wx_120029121' ";
	$db->query($sql_qq_wx_120029121);
	sys_msg(sprintf($msg_attr_qq_wx_120029121, $attr['attr_name']), 0, $links);
	/* 增加代码_end By demo.coolhong.com 今天优品多商户系统 Q Q 1200 2912 1 */

}

/*------------------------------------------------------ */
//-- 删除属性(一个或多个)
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'batch')
{
    /* 检查权限 */
    admin_priv('attr_manage');

    /* 取得要操作的编号 */
    if (isset($_POST['checkboxes']))
    {
        $count = count($_POST['checkboxes']);
        $ids   = isset($_POST['checkboxes']) ? join(',', $_POST['checkboxes']) : 0;
        $sql = "DELETE FROM " . $yp->table('attribute') . " WHERE attr_id " . db_create_in($ids);
        $db->query($sql);

        $sql = "DELETE FROM " . $yp->table('goods_attr') . " WHERE attr_id " . db_create_in($ids);
        $db->query($sql);

        /* 记录日志 */
        admin_log('', 'batch_remove', 'attribute');
        clear_cache_files();

        $link[] = array('text' => $_LANG['back_list'], 'href' => 'attribute.php?act=list');
        sys_msg(sprintf($_LANG['drop_ok'], $count), 0, $link);
    }
    else
    {
        $link[] = array('text' => $_LANG['back_list'], 'href' => 'attribute.php?act=list');
        sys_msg($_LANG['no_select_arrt'], 0, $link);
    }
}

/*------------------------------------------------------ */
//-- 编辑属性名称
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'edit_attr_name')
{
    check_authz_json('attr_manage');

    $id = intval($_POST['id']);
    $val = json_str_iconv(trim($_POST['val']));

    /* 取得该属性所属商品类型id */
    $cat_id = $exc->get_name($id, 'cat_id');

    /* 检查属性名称是否重复 */
    if (!$exc->is_only('attr_name', $val, $id, " cat_id = '$cat_id'"))
    {
        make_json_error($_LANG['name_exist']);
    }

    $exc->edit("attr_name='$val'", $id);

    admin_log($val, 'edit', 'attribute');

    make_json_result(stripslashes($val));
}

/*------------------------------------------------------ */
//-- 编辑排序序号
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'edit_sort_order')
{
    check_authz_json('attr_manage');

    $id = intval($_POST['id']);
    $val = intval($_POST['val']);

    $exc->edit("sort_order='$val'", $id);

    admin_log(addslashes($exc->get_name($id)), 'edit', 'attribute');

    make_json_result(stripslashes($val));
}

/*------------------------------------------------------ */
//-- 删除商品属性
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
    check_authz_json('attr_manage');

    $id = intval($_GET['id']);
      
     $sql = "SELECT * FROM " .$GLOBALS['yp']->table('attribute') ." as a inner join " . $GLOBALS['yp']->table('goods_type') . " as g on a.cat_id = g.cat_id where g.supplier_id='$supplier_id' AND a.attr_id=" . $id;

       // $sql = "SELECT * FROM " . $yp->table('attribute') . " WHERE attr_id = '$_REQUEST[attr_id]'";
        $attr = $db->getRow($sql);

        if (empty($attr)) {
          make_json_error('非法操作！');

       } 
    
    $db->query("DELETE FROM " .$yp->table('attribute'). " WHERE attr_id='$id'");
    $db->query("DELETE FROM " .$yp->table('goods_attr'). " WHERE attr_id='$id'");

    $url = 'attribute.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    yp_header("Location: $url\n");
    exit;
}

/*------------------------------------------------------ */
//-- 获取某属性商品数量
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'get_attr_num')
{
    check_authz_json('attr_manage');

    $id = intval($_GET['attr_id']);

    $sql = "SELECT COUNT(*) ".
           " FROM " . $yp->table('goods_attr') . " AS a, ".
           $yp->table('goods') . " AS g ".
           " WHERE g.goods_id = a.goods_id AND g.is_delete = 0 AND attr_id = '$id' ";

    $goods_num = $db->getOne($sql);

    if ($goods_num > 0)
    {
        $drop_confirm = sprintf($_LANG['notice_drop_confirm'], $goods_num);
    }
    else
    {
        $drop_confirm = $_LANG['drop_confirm'];
    }

    make_json_result(array('attr_id'=>$id, 'drop_confirm'=>$drop_confirm));
}

/*------------------------------------------------------ */
//-- 获得指定商品类型下的所有属性分组
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'get_attr_groups')
{
    check_authz_json('attr_manage');

    $cat_id = intval($_GET['cat_id']);
    $groups = get_attr_groups($cat_id);

    make_json_result($groups);
}

/* 代码增加_start   By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 */
/*------------------------------------------------------ */
//-- 设置颜色
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'savecolor')
{
	$sql = "delete from ". $yp->table('attribute_color') ." where attr_id= '$_REQUEST[attr_id]' ";
	$db->query($sql);
	foreach ($_REQUEST['color_name'] AS $color_key=> $color_name)
	{
		if ($_REQUEST['color_code'][$color_key])
		{
			$sql="insert into ". $yp->table('attribute_color') ."(attr_id, color_name, color_code) values('$_REQUEST[attr_id]', '$color_name', '". $_REQUEST['color_code'][$color_key] ."' )";
			$db->query($sql);
		}
	}
	$link[] = array('text' => '返回设置页面', 'href' => 'attribute.php?act=setcolor&attr_id='.$_REQUEST['attr_id']);
    sys_msg('恭喜，您已成功设置了颜色代码！', 0, $link);
}
elseif ($_REQUEST['act'] == 'setcolor')
{
    /* 检查权限 */
    admin_priv('attr_manage');

    $sql = "SELECT * FROM " .$GLOBALS['yp']->table('attribute') ." as a inner join " . $GLOBALS['yp']->table('goods_type') . " as g on a.cat_id = g.cat_id where g.supplier_id='$supplier_id' AND a.attr_id=" . $_REQUEST['attr_id'];

       // $sql = "SELECT * FROM " . $yp->table('attribute') . " WHERE attr_id = '$_REQUEST[attr_id]'";
        $attr = $db->getRow($sql);

        if (empty($attr)) {
            $links[] = array('text' => $_LANG['09_attribute_list'], 'href' => 'attribute.php?act=list');
           sys_msg('非法操作！', 1 ,$links);

       }
	
    $smarty->assign('attr', $attr);
	
	$colors_code=array();
	$sql="select * from ". $yp->table('attribute_color') . " where attr_id = '$_REQUEST[attr_id]'";
	$res_color = $db->query($sql);
	while ($row_color = $db->fetchRow($res_color))
	{
		$colors_code[$row_color['color_name']] = $row_color['color_code'];
	}
	
	if($attr['attr_values'])
	{
		$color_list= str_replace("\r\n", "\n", $attr['attr_values']);
		$color_array = explode("\n", $color_list);
		$color_list=array();
		foreach ($color_array as $ckey=>$cval)
		{
			$color_list[$ckey]['color_name'] = $cval;
			$color_list[$ckey]['color_code'] = $colors_code[$cval] ? $colors_code[$cval] : '';
		}
	}
	
    $smarty->assign('color_list', $color_list);
	$smarty->assign('ur_here', '设置颜色');
    $smarty->assign('action_link', array('href' => 'attribute.php?act=list&goods_type='.$attr['cat_id'], 'text' => $_LANG['09_attribute_list']));
	 /* 显示模板 */
    assign_query_info();
    $smarty->display('attribute_setcolor.htm');

}
/* 代码增加_end  添加    今-天-优-品-多-商-户-系-统 Q-Q 120 029 121 */

/*------------------------------------------------------ */
//-- PRIVATE FUNCTIONS
/*------------------------------------------------------ */

/**
 * 获取属性列表
 *
 * @return  array
 */
function get_attrlist()
{
     $supplier_id = $_SESSION['supplier_id'];

    /* 查询条件 */
    $filter = array();
    $filter['goods_type'] = empty($_REQUEST['goods_type']) ? 0 : intval($_REQUEST['goods_type']);
    $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'sort_order' : trim($_REQUEST['sort_by']);
    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
    $sql = "SELECT * FROM " . $GLOBALS['yp']->table('goods_type') . " WHERE supplier_id= '$supplier_id' AND enabled = 1";
    $row= $GLOBALS['db']->getAll($sql);
    $all_goods_type='';
      foreach($row as $key => $list){
          $row[$key]['cat_id'] = $list['cat_id'];

          $all_goods_type = $all_goods_type.$row[$key]['cat_id'].',';
         //var_dump($list['cat_id']);
    }

      // echo ($all_goods_type);
    $where = (!empty($filter['goods_type'])) ? " WHERE a.cat_id = '$filter[goods_type]' " : " WHERE a.cat_id in (".$all_goods_type."'')";

    $sql = "SELECT COUNT(*) FROM " . $GLOBALS['yp']->table('attribute') . " AS a $where";
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);

    /* 分页大小 */
    $filter = page_and_size($filter);

    /* 查询 */
    $sql = "SELECT a.*, t.cat_name,t.supplier_id" .
            " FROM " . $GLOBALS['yp']->table('attribute') . " AS a ".
            " LEFT JOIN " . $GLOBALS['yp']->table('goods_type') . " AS t ON a.cat_id = t.cat_id " . $where .
            " ORDER BY $filter[sort_by] $filter[sort_order] ".
            " LIMIT " . $filter['start'] .", $filter[page_size]";
    $row = $GLOBALS['db']->getAll($sql);

    foreach ($row AS $key => $val)
    {
        $row[$key]['attr_input_type_desc'] = $GLOBALS['_LANG']['value_attr_input_type'][$val['attr_input_type']];
        $row[$key]['attr_values']      = str_replace("\n", ", ", $val['attr_values']);
    }

    $arr = array('item' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

/**
 * 获得商品类型的列表
 *
 * @access  public
 * @param   integer     $selected   选定的类型编号
 * @return  string
 */
function goods_typelist($selected)
{
    $supplier_id = $_SESSION['supplier_id'];
    $sql = "SELECT cat_id, cat_name FROM " . $GLOBALS['yp']->table('goods_type') . " WHERE supplier_id = '$supplier_id' AND enabled = 1";
    $res = $GLOBALS['db']->query($sql);

    $lst = '';
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $lst .= "<option value='$row[cat_id]'";
        $lst .= ($selected == $row['cat_id']) ? ' selected="true"' : '';
        $lst .= '>' . htmlspecialchars($row['cat_name']). '</option>';
    }

    return $lst;
}
?>
