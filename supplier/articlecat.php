<?php

/**
 * QQ120029121 文章分类管理程序
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: PRINCE $
 * $Id: articlecat.php 17217 2017-04-01 06:29:08Z PRINCE $
*/

define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');
$exc = new exchange($yp->table("supplier_article_cat"), $db, 'cat_id', 'cat_name');
/* act操作项的初始化 */
$_REQUEST['act'] = trim($_REQUEST['act']);
if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'list';
}

/*------------------------------------------------------ */
//-- 分类列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    $articlecat = supplier_article_cat_list(0, 0, false);
    foreach ($articlecat as $key => $cat)
    {
        $articlecat[$key]['type_name'] = $_LANG['type_name'][$cat['cat_type']];
    }
    $smarty->assign('ur_here',     $_LANG['02_articlecat_list']);
    $smarty->assign('action_link', array('text' => $_LANG['articlecat_add'], 'href' => 'articlecat.php?act=add'));
    $smarty->assign('full_page',   1);
    $smarty->assign('articlecat',        $articlecat);

    assign_query_info();
    $smarty->display('articlecat_list.htm');
}

/*------------------------------------------------------ */
//-- 查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $articlecat = supplier_article_cat_list(0, 0, false);
    foreach ($articlecat as $key => $cat)
    {
        $articlecat[$key]['type_name'] = $_LANG['type_name'][$cat['cat_type']];
    }
    $smarty->assign('articlecat',        $articlecat);

    make_json_result($smarty->fetch('articlecat_list.htm'));
}

/*------------------------------------------------------ */
//-- 添加分类
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add')
{
    /* 权限判断 */
    admin_priv('article_cat');

    $smarty->assign('cat_select',  supplier_article_cat_list(0));
    $smarty->assign('ur_here',     $_LANG['articlecat_add']);
    $smarty->assign('action_link', array('text' => $_LANG['02_articlecat_list'], 'href' => 'articlecat.php?act=list'));
    $smarty->assign('form_action', 'insert');

    assign_query_info();
    $smarty->display('articlecat_info.htm');
}
elseif ($_REQUEST['act'] == 'insert')
{
    /* 权限判断 */
    admin_priv('article_cat');

    /*检查分类名是否重复*/
    $where = "supplier_id = '".$_SESSION['supplier_id']."'";
    $is_only = $exc->is_only('cat_name', $_POST['cat_name'], 0, $where);

    if (!$is_only)
    {
        sys_msg(sprintf($_LANG['catname_exist'], stripslashes($_POST['cat_name'])), 1);
    }
	/* 代码增加_start By demo.coolhong.com 今天优品多商户系统 qq 120029121 */
	if($_POST['path_name'] != '')
	{
		$is_only = $exc->is_only('path_name', $_POST['path_name'], 0, $where);

    	if (!$is_only)
    	{
        	sys_msg(sprintf('对不起，已经存在相同目录名', stripslashes($_POST['path_name'])), 1);
    	}
	}
	/* 代码增加_end  By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121  */
	
    $cat_type = 1;
    if ($_POST['parent_id'] > 0)
    {
        $sql = "SELECT cat_type FROM " . $yp->table('supplier_article_cat') . " WHERE cat_id = '$_POST[parent_id]'";
        $p_cat_type = $db->getOne($sql);
        if ($p_cat_type == 2 || $p_cat_type == 3 || $p_cat_type == 5)
        {
            sys_msg($_LANG['not_allow_add'], 0);
        }
        else if ($p_cat_type == 4)
        {
            $cat_type = 5;
        }
    }


    $sql = "INSERT INTO ".$yp->table('supplier_article_cat')."(cat_name, cat_type, cat_desc,keywords, parent_id, sort_order,supplier_id)
           VALUES ('$_POST[cat_name]', '$cat_type',  '$_POST[cat_desc]','$_POST[keywords]', '$_POST[parent_id]', '$_POST[sort_order]',$_SESSION[supplier_id])";//代码修改 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 增加新字段 path_name 和  $_POST[path_name]
    $db->query($sql);

   /* if($_POST['show_in_nav'] == 1)
    {
        $vieworder = $db->getOne("SELECT max(vieworder) FROM ". $yp->table('nav') . " WHERE type = 'middle'");
        $vieworder += 2;
        //显示在自定义导航栏中
        $sql = "INSERT INTO " . $yp->table('nav') . " (name,ctype,cid,ifshow,vieworder,opennew,url,type) VALUES('" . $_POST['cat_name'] . "', 'a', '" . $db->insert_id() . "','1','$vieworder','0', '" . build_uri('article_cat', array('acid'=> $db->insert_id()), $_POST['cat_name']) . "','middle')";
        $db->query($sql);
    }*/

    admin_log($_POST['cat_name'],'add','articlecat');

    $link[0]['text'] = $_LANG['continue_add'];
    $link[0]['href'] = 'articlecat.php?act=add';

    $link[1]['text'] = $_LANG['back_list'];
    $link[1]['href'] = 'articlecat.php?act=list';
    clear_cache_files();
    sys_msg($_POST['cat_name'].$_LANG['catadd_succed'],0, $link);
}

/*------------------------------------------------------ */
//-- 编辑文章分类
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit')
{
    /* 权限判断 */
    admin_priv('article_cat');
   
     $sql = "SELECT supplier_id FROM " . $yp->table('supplier_article_cat') . " WHERE cat_id = '$_REQUEST[id]' AND supplier_id = " . $_SESSION['supplier_id'];
    $supplier_id = $db->getOne($sql);
    if (empty($supplier_id))
    {
        /* F非自己保留分类，不能删除 */
        make_json_error('非法操作');
    }

    $sql = "SELECT cat_id, cat_name, cat_type, cat_desc, show_in_nav, keywords, parent_id,sort_order FROM ".
           $yp->table('supplier_article_cat'). " WHERE cat_id='$_REQUEST[id]'";//代码修改 By demo.coolhong.com 今天优品多商户系统 qq 120029121 增加一个path_name 字段
    $cat = $db->GetRow($sql);

    if ($cat['cat_type'] == 2 || $cat['cat_type'] == 3 || $cat['cat_type'] ==4)
    {
        $smarty->assign('disabled', 1);
    }
    $options    =   supplier_article_cat_list(0, $cat['parent_id'], false);
    $select     =   '';
    $selected   =   $cat['parent_id'];
    foreach ($options as $var)
    {
        if ($var['cat_id'] == $_REQUEST['id'])
        {
            continue;
        }
        $select .= '<option value="' . $var['cat_id'] . '" ';
        $select .= ' cat_type="' . $var['cat_type'] . '" ';
        $select .= ($selected == $var['cat_id']) ? "selected='ture'" : '';
        $select .= '>';
        if ($var['level'] > 0)
        {
            $select .= str_repeat('&nbsp;', $var['level'] * 4);
        }
        $select .= htmlspecialchars($var['cat_name']) . '</option>';
    }
    unset($options);
    $smarty->assign('cat',         $cat);
    $smarty->assign('cat_select',  $select);
    $smarty->assign('ur_here',     $_LANG['articlecat_edit']);
    $smarty->assign('action_link', array('text' => $_LANG['02_articlecat_list'], 'href' => 'articlecat.php?act=list'));
    $smarty->assign('form_action', 'update');

    assign_query_info();
    $smarty->display('articlecat_info.htm');
}
elseif ($_REQUEST['act'] == 'update')
{
    /* 权限判断 */
    admin_priv('article_cat');
    /*检查重名*/
    $where = "supplier_id = '".$_SESSION['supplier_id']."'";
    if ($_POST['cat_name'] != $_POST['old_catname'])
    {
        $is_only = $exc->is_only('cat_name', $_POST['cat_name'], $_POST['id'], $where);

        if (!$is_only)
        {
            sys_msg(sprintf($_LANG['catname_exist'], stripslashes($_POST['cat_name'])), 1);
        }
    }
	/* 代码增加_start  By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 */
	/*if($_POST['path_name'] != '')
	{
		$is_only = $exc->is_only('path_name', $_POST['path_name'], $_POST['id'], $where);
    	 if (!$is_only)
     	{
            sys_msg(sprintf('对不起，已经存在相同的目录名', stripslashes($_POST['path_name'])), 1);
     	}
	}*/
	/* 代码增加_start  By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 */
    if(!isset($_POST['parent_id']))
    {
        $_POST['parent_id'] = 0;
    }

    $row = $db->getRow("SELECT cat_type, parent_id FROM " . $yp->table('supplier_article_cat') . " WHERE cat_id='$_POST[id]'");
    $cat_type = $row['cat_type'];
    if ($cat_type == 3 || $cat_type ==4)
    {
        $_POST['parent_id'] = $row['parent_id'];
    }

    /* 检查设定的分类的父分类是否合法 */
    $child_cat = supplier_article_cat_list($_POST['id'], 0, false);
    if (!empty($child_cat))
    {
        foreach ($child_cat as $child_data)
        {
            $catid_array[] = $child_data['cat_id'];
        }
    }
    if (in_array($_POST['parent_id'], $catid_array))
    {
        sys_msg(sprintf($_LANG['parent_id_err'], stripslashes($_POST['cat_name'])), 1);
    }

    if ($cat_type == 1 || $cat_type == 5)
    {
        if ($_POST['parent_id'] > 0)
        {
            $sql = "SELECT cat_type FROM " . $yp->table('supplier_article_cat') . " WHERE cat_id = '$_POST[parent_id]'";
            $p_cat_type = $db->getOne($sql);
            if ($p_cat_type == 4)
            {
                $cat_type = 5;
            }
            else
            {
                $cat_type = 1;
            }
        }
        else
        {
            $cat_type = 1;
        }
    }

    $dat = $db->getOne("SELECT cat_name, show_in_nav FROM ". $yp->table('supplier_article_cat') . " WHERE cat_id = '" . $_POST['id'] . "'");
    if ($exc->edit("cat_name = '$_POST[cat_name]', cat_desc ='$_POST[cat_desc]', keywords='$_POST[keywords]',parent_id = '$_POST[parent_id]', cat_type='$cat_type', sort_order='$_POST[sort_order]', show_in_nav = '$_POST[show_in_nav]'",  $_POST['id']))
    {
		/* 代码增加_start  By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 */
		/*if($_POST['path_name'])
        {
			$sql = "UPDATE " . $yp->table('article_cat') . " SET path_name = '" . $_POST['path_name'] . "' WHERE cat_id = '" . $_POST['id'] . "' ";
            $db->query($sql);
		}*/
		/* 代码增加_end  By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 */
      
        $link[0]['text'] = $_LANG['back_list'];
        $link[0]['href'] = 'articlecat.php?act=list';
        $note = sprintf($_LANG['catedit_succed'], $_POST['cat_name']);
        admin_log($_POST['cat_name'], 'edit', 'articlecat');
        clear_cache_files();
        sys_msg($note, 0, $link);

    }
    else
    {
        die($db->error());
    }
}



/*------------------------------------------------------ */
//-- 编辑文章分类的排序
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_sort_order')
{
    check_authz_json('article_cat');

    $id    = intval($_POST['id']);
    $order = json_str_iconv(trim($_POST['val']));

    /* 检查输入的值是否合法 */
    if (!preg_match("/^[0-9]+$/", $order))
    {
        make_json_error(sprintf($_LANG['enter_int'], $order));
    }
    else
    {
        if ($exc->edit("sort_order = '$order'", $id))
        {
            clear_cache_files();
            make_json_result(stripslashes($order));
        }
        else
        {
            make_json_error($db->error());
        }
    }
}

/*------------------------------------------------------ */
//-- 删除文章分类
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
    check_authz_json('supplier_article_cat');

    $id = intval($_GET['id']);
     $sql = "SELECT supplier_id FROM " . $yp->table('supplier_article_cat') . " WHERE cat_id = '$id' AND supplier_id = " . $_SESSION['supplier_id'];
    $supplier_id = $db->getOne($sql);
    if (empty($supplier_id))
    {
        /* F非自己保留分类，不能删除 */
        make_json_error('非法操作');
    }

    $sql = "SELECT cat_type FROM " . $yp->table('supplier_article_cat') . " WHERE cat_id = '$id'";
    $cat_type = $db->getOne($sql);
    if ($cat_type == 2 || $cat_type == 3 || $cat_type ==4)
    {
        /* 系统保留分类，不能删除 */
        make_json_error($_LANG['not_allow_remove']);
    }

    $sql = "SELECT COUNT(*) FROM " . $yp->table('supplier_article_cat') . " WHERE parent_id = '$id'";
    if ($db->getOne($sql) > 0)
    {
        /* 还有子分类，不能删除 */
        make_json_error($_LANG['is_fullcat']);
    }

    /* 非空的分类不允许删除 */
    $sql = "SELECT COUNT(*) FROM ".$yp->table('supplier_article')." WHERE cat_id = '$id'";
    if ($db->getOne($sql) > 0)
    {
        make_json_error(sprintf($_LANG['not_emptycat']));
    }
    else
    {
        $exc->drop($id);
    
        clear_cache_files();
        admin_log($cat_name, 'remove', 'category');
    }

    $url = 'articlecat.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    yp_header("Location: $url\n");
    exit;
}
/*------------------------------------------------------ */
//-- 切换是否显示在导航栏
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'toggle_show_in_nav')
{
    check_authz_json('cat_manage');

    $id = intval($_POST['id']);
    $val = intval($_POST['val']);

    if (cat_update($id, array('show_in_nav' => $val)) != false)
    {
        if($val == 1)
        {
            //显示
            $nid = $db->getOne("SELECT id FROM ". $yp->table('nav') . " WHERE ctype='a' AND cid='$id' AND type = 'middle'");
            if(empty($nid))
            {
                //不存在
                $vieworder = $db->getOne("SELECT max(vieworder) FROM ". $yp->table('nav') . " WHERE type = 'middle'");
                $vieworder += 2;
                $catname = $db->getOne("SELECT cat_name FROM ". $yp->table('article_cat') . " WHERE cat_id = '$id'");
                $uri = build_uri('article_cat', array('acid'=> $id), $_POST['cat_name']);

                $sql = "INSERT INTO " . $yp->table('nav') . " (name,ctype,cid,ifshow,vieworder,opennew,url,type) ".
                    "VALUES('" . $catname . "', 'a', '$id','1','$vieworder','0', '" . $uri . "','middle')";
            }
            else
            {
                $sql = "UPDATE " . $yp->table('nav') . " SET ifshow = 1 WHERE ctype='a' AND cid='$id' AND type = 'middle'";
            }
            $db->query($sql);
        }
        else
        {
            //去除
            $db->query("UPDATE " . $yp->table('nav') . " SET ifshow = 0 WHERE ctype='a' AND cid='$id' AND type = 'middle'");
        }
        clear_cache_files();
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}

/**
 * 添加商品分类
 *
 * @param   integer $cat_id
 * @param   array   $args
 *
 * @return  mix
 */
function cat_update($cat_id, $args)
{
    if (empty($args) || empty($cat_id))
    {
        return false;
    }

    return $GLOBALS['db']->autoExecute($GLOBALS['yp']->table('supplier_article_cat'), $args, 'update', "cat_id='$cat_id'");
}

/**
 * 获得指定分类下的子分类的数组
 *
 * @access  public
 * @param   int     $cat_id     分类的ID
 * @param   int     $selected   当前选中分类的ID
 * @param   boolean $re_type    返回的类型: 值为真时返回下拉列表,否则返回数组
 * @param   int     $level      限定返回的级数。为0时返回所有级数
 * @return  mix
 */
function supplier_article_cat_list($cat_id = 0, $selected = 0, $re_type = true, $level = 0)
{
    static $res = NULL;
  $supplier_id = $_SESSION['supplier_id'];
    if ($res === NULL)
    {
        $data = read_static_cache('art_cat_pid_releate');
        if ($data === false)
        {
            $sql = "SELECT c.*, COUNT(s.cat_id) AS has_children, COUNT(a.article_id) AS aricle_num ".
               ' FROM ' . $GLOBALS['yp']->table('supplier_article_cat') . " AS c".
               " LEFT JOIN " . $GLOBALS['yp']->table('supplier_article_cat') . " AS s ON s.parent_id=c.cat_id".
               " LEFT JOIN " . $GLOBALS['yp']->table('supplier_article') . " AS a ON a.cat_id=c.cat_id".
               " WHERE c.supplier_id = '$supplier_id'".
               " GROUP BY c.cat_id ".
               " ORDER BY parent_id, sort_order ASC";
            $res = $GLOBALS['db']->getAll($sql);
            write_static_cache('art_cat_pid_releate', $res);
        }
        else
        {
            $res = $data;
        }
    }

    if (empty($res) == true)
    {
        return $re_type ? '' : array();
    }

    $options = supplier_article_cat_options($cat_id, $res); // 获得指定分类下的子分类的数组

    /* 截取到指定的缩减级别 */
    if ($level > 0)
    {
        if ($cat_id == 0)
        {
            $end_level = $level;
        }
        else
        {
            $first_item = reset($options); // 获取第一个元素
            $end_level  = $first_item['level'] + $level;
        }

        /* 保留level小于end_level的部分 */
        foreach ($options AS $key => $val)
        {
            if ($val['level'] >= $end_level)
            {
                unset($options[$key]);
            }
        }
    }

    $pre_key = 0;
    foreach ($options AS $key => $value)
    {
        //$options[$key]['has_children'] = 1;
        if ($pre_key > 0)
        {
            if ($options[$pre_key]['cat_id'] == $options[$key]['parent_id'])
            {
                $options[$pre_key]['has_children'] = 1;
            }
        }
        $pre_key = $key;
    }

    if ($re_type == true)
    {
        $select = '';
        foreach ($options AS $var)
        {
            $select .= '<option value="' . $var['cat_id'] . '" ';
            $select .= ' cat_type="' . $var['cat_type'] . '" ';
            $select .= ($selected == $var['cat_id']) ? "selected='ture'" : '';
            $select .= '>';
            if ($var['level'] > 0)
            {
                $select .= str_repeat('&nbsp;', $var['level'] * 4);
            }
            $select .= htmlspecialchars(addslashes($var['cat_name'])) . '</option>';
        }

        return $select;
    }
    else
    {
        foreach ($options AS $key => $value)
        {
            $options[$key]['url'] = build_uri('article_cat', array('acid' => $value['cat_id']), $value['cat_name']);
        }
        return $options;
    }
}

/**
 * 过滤和排序所有文章分类，返回一个带有缩进级别的数组
 *
 * @access  private
 * @param   int     $cat_id     上级分类ID
 * @param   array   $arr        含有所有分类的数组
 * @param   int     $level      级别
 * @return  void
 */
function supplier_article_cat_options($spec_cat_id, $arr)
{
    static $cat_options = array();

    if (isset($cat_options[$spec_cat_id]))
    {
        return $cat_options[$spec_cat_id];
    }

    if (!isset($cat_options[0]))
    {
        $level = $last_cat_id = 0;
        $options = $cat_id_array = $level_array = array();
        while (!empty($arr))
        {
            foreach ($arr AS $key => $value)
            {
                $cat_id = $value['cat_id'];
                if ($level == 0 && $last_cat_id == 0)
                {
                    if ($value['parent_id'] > 0)
                    {
                        break;
                    }

                    $options[$cat_id]          = $value;
                    $options[$cat_id]['level'] = $level;
                    $options[$cat_id]['id']    = $cat_id;
                    $options[$cat_id]['name']  = $value['cat_name'];
                    unset($arr[$key]);

                    if ($value['has_children'] == 0)
                    {
                        continue;
                    }
                    $last_cat_id  = $cat_id;
                    $cat_id_array = array($cat_id);
                    $level_array[$last_cat_id] = ++$level;
                    continue;
                }

                if ($value['parent_id'] == $last_cat_id)
                {
                    $options[$cat_id]          = $value;
                    $options[$cat_id]['level'] = $level;
                    $options[$cat_id]['id']    = $cat_id;
                    $options[$cat_id]['name']  = $value['cat_name'];
                    unset($arr[$key]);

                    if ($value['has_children'] > 0)
                    {
                        if (end($cat_id_array) != $last_cat_id)
                        {
                            $cat_id_array[] = $last_cat_id;
                        }
                        $last_cat_id    = $cat_id;
                        $cat_id_array[] = $cat_id;
                        $level_array[$last_cat_id] = ++$level;
                    }
                }
                elseif ($value['parent_id'] > $last_cat_id)
                {
                    break;
                }
            }

            $count = count($cat_id_array);
            if ($count > 1)
            {
                $last_cat_id = array_pop($cat_id_array);
            }
            elseif ($count == 1)
            {
                if ($last_cat_id != end($cat_id_array))
                {
                    $last_cat_id = end($cat_id_array);
                }
                else
                {
                    $level = 0;
                    $last_cat_id = 0;
                    $cat_id_array = array();
                    continue;
                }
            }

            if ($last_cat_id && isset($level_array[$last_cat_id]))
            {
                $level = $level_array[$last_cat_id];
            }
            else
            {
                $level = 0;
            }
        }
        $cat_options[0] = $options;
    }
    else
    {
        $options = $cat_options[0];
    }

    if (!$spec_cat_id)
    {
        return $options;
    }
    else
    {
        if (empty($options[$spec_cat_id]))
        {
            return array();
        }

        $spec_cat_id_level = $options[$spec_cat_id]['level'];

        foreach ($options AS $key => $value)
        {
            if ($key != $spec_cat_id)
            {
                unset($options[$key]);
            }
            else
            {
                break;
            }
        }

        $spec_cat_id_array = array();
        foreach ($options AS $key => $value)
        {
            if (($spec_cat_id_level == $value['level'] && $value['cat_id'] != $spec_cat_id) ||
                ($spec_cat_id_level > $value['level']))
            {
                break;
            }
            else
            {
                $spec_cat_id_array[$key] = $value;
            }
        }
        $cat_options[$spec_cat_id] = $spec_cat_id_array;

        return $spec_cat_id_array;
    }
}

?>
