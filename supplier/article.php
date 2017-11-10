<?php

/**
 * QQ120029121 管理中心文章处理程序文件
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: PRINCE $
 * $Id: article.php 17217 2017-04-01 06:29:08Z PRINCE $
*/

define('IN_PRINCE', true);

require(dirname(__FILE__) . '/includes/init.php');
/* 修改 by demo.coolhong.com 今 天 优 品 多 商 户 系 统 q q 1 2 0 0 2 9 1 2 1 百度编辑器 begin */
//require_once(ROOT_PATH . "includes/fckeditor/fckeditor.php");
/* 修改 by demo.coolhong.com 今 天 优 品 多 商 户 系 统 q q 1 2 0 0 2 9 1 2 1 百度编辑器 end */
require_once(ROOT_PATH . 'includes/cls_image.php');

/*初始化数据交换对象 */
$exc   = new exchange($yp->table("supplier_article"), $db, 'article_id', 'title');
//$image = new cls_image();

/* 允许上传的文件类型 */
$allow_file_types = '|GIF|JPG|PNG|BMP|SWF|DOC|XLS|PPT|MID|WAV|ZIP|RAR|PDF|CHM|RM|TXT|';

/*------------------------------------------------------ */
//-- 文章列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{

    /* 取得过滤条件 */
    $filter = array();
    $smarty->assign('cat_select',  supplier_article_cat_list(0));
    $smarty->assign('ur_here',      $_LANG['03_article_list']);
    $smarty->assign('action_link',  array('text' => $_LANG['article_add'], 'href' => 'article.php?act=add'));
    $smarty->assign('full_page',    1);
    $smarty->assign('filter',       $filter);
 
  $article_list = get_articleslist();

    $smarty->assign('article_list',    $article_list['arr']);
    $smarty->assign('filter',          $article_list['filter']);
    $smarty->assign('record_count',    $article_list['record_count']);
    $smarty->assign('page_count',      $article_list['page_count']);

    $sort_flag  = sort_flag($article_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    assign_query_info();
    $smarty->display('article_list.htm');
}

/*------------------------------------------------------ */
//-- 翻页，排序
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    check_authz_json('article_manage');

    $article_list = get_articleslist();

    $smarty->assign('article_list',    $article_list['arr']);
    $smarty->assign('filter',          $article_list['filter']);
    $smarty->assign('record_count',    $article_list['record_count']);
    $smarty->assign('page_count',      $article_list['page_count']);

    $sort_flag  = sort_flag($article_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('article_list.htm'), '',
        array('filter' => $article_list['filter'], 'page_count' => $article_list['page_count']));
}

/*------------------------------------------------------ */
//-- 添加文章
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'add')
{
    /* 权限判断 */
    admin_priv('article_manage');

    /* 创建 html editor */
    create_html_editor('FCKeditor1');

    /*初始化*/
    $article = array();
    $article['is_open'] = 1;

    // /* 取得分类、品牌 */
    // $smarty->assign('goods_cat_list', cat_list());
    // $smarty->assign('brand_list',     get_brand_list());

    // /* 清理关联商品 */
    // $sql = "DELETE FROM " . $yp->table('goods_article') . " WHERE article_id = 0";
    // $db->query($sql);

    if (isset($_GET['id']))
    {
        $smarty->assign('cur_id',  $_GET['id']);
    }
    $smarty->assign('article',     $article);
    $smarty->assign('cat_select',  supplier_article_cat_list(0));
    $smarty->assign('ur_here',     $_LANG['article_add']);
    $smarty->assign('action_link', array('text' => $_LANG['03_article_list'], 'href' => 'article.php?act=list'));
    $smarty->assign('form_action', 'insert');

    assign_query_info();
    $smarty->display('article_info.htm');
}

/*------------------------------------------------------ */
//-- 添加文章
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'insert')
{
    /* 权限判断 */
    admin_priv('article_manage');

    /*检查是否重复*/
    $is_only = $exc->is_only('title', $_POST['title'],0, " cat_id ='$_POST[article_cat]'");

    if (!$is_only)
    {
        sys_msg(sprintf($_LANG['title_exist'], stripslashes($_POST['title'])), 1);
    }

    /* 取得文件地址 */
    $file_url = '';
    if ((isset($_FILES['file']['error']) && $_FILES['file']['error'] == 0) || (!isset($_FILES['file']['error']) && isset($_FILES['file']['tmp_name']) && $_FILES['file']['tmp_name'] != 'none'))
    {
        // 检查文件格式
        if (!check_file_type($_FILES['file']['tmp_name'], $_FILES['file']['name'], $allow_file_types))
        {
            sys_msg($_LANG['invalid_file']);
        }

        // 复制文件
        $res = upload_article_file($_FILES['file']);
        if ($res != false)
        {
            $file_url = $res;
        }
    }

    if ($file_url == '')
    {
        $file_url = $_POST['file_url'];
    }

    /* 计算文章打开方式 */
    if ($file_url == '')
    {
        $open_type = 0;
    }
    else
    {
        $open_type = $_POST['FCKeditor1'] == '' ? 1 : 2;
    }

    /*插入数据*/
    $add_time = gmtime();
    if (empty($_POST['cat_id']))
    {
        $_POST['cat_id'] = 0;
    }
    $sql = "INSERT INTO ".$yp->table('supplier_article')."(title, cat_id, article_type, is_open, author, ".
                "author_email, keywords, content, add_time, file_url, open_type, link, description,supplier_id) ".
            "VALUES ('$_POST[title]', '$_POST[article_cat]', '$_POST[article_type]', '$_POST[is_open]', ".
                "'$_POST[author]', '$_POST[author_email]', '$_POST[keywords]', '$_POST[FCKeditor1]', ".
                "'$add_time', '$file_url', '$open_type', '$_POST[link_url]', '$_POST[description]','$_SESSION[supplier_id]')";
    $db->query($sql);

    /* 处理关联商品 */
    // $article_id = $db->insert_id();
    // $sql = "UPDATE " . $yp->table('goods_article') . " SET article_id = '$article_id' WHERE article_id = 0";
    // $db->query($sql);

    $link[0]['text'] = $_LANG['continue_add'];
    $link[0]['href'] = 'article.php?act=add';

    $link[1]['text'] = $_LANG['back_list'];
    $link[1]['href'] = 'article.php?act=list';

    admin_log($_POST['title'],'add','article');

    clear_cache_files(); // 清除相关的缓存文件

    sys_msg($_LANG['articleadd_succeed'],0, $link);
}

/*------------------------------------------------------ */
//-- 编辑
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'edit')
{
    /* 权限判断 */
    admin_priv('article_manage');
  check_authz_json('article_manage');
    /* 取文章数据 */
    $sql = "SELECT * FROM " .$yp->table('supplier_article'). " WHERE article_id='$_REQUEST[id]'";
    $article = $db->GetRow($sql);


    if ($article['supplier_id'] != $_SESSION['supplier_id'])
    {
        /* F非自己保留分类，不能删除 */
        make_json_error('非法操作');
    }


    /* 创建 html editor */
   create_html_editor('FCKeditor1',htmlspecialchars($article['content'])); /* 修改 by demo.coolhong.com 今 天 优 品 多 商 户 系 统 q q 1 2 0 0 2 9 1 2 1 百度编辑器 */


    /* 取得分类、品牌 */
   // $smarty->assign('goods_cat_list', cat_list());
    //$smarty->assign('brand_list', get_brand_list());

    /* 取得关联商品 */
   // $goods_list = get_article_goods($_REQUEST['id']);
   // $smarty->assign('goods_list', $goods_list);

    $smarty->assign('article',     $article);
    $smarty->assign('cat_select',  supplier_article_cat_list(0, $article['cat_id']));
    $smarty->assign('ur_here',     $_LANG['article_edit']);
    $smarty->assign('action_link', array('text' => $_LANG['03_article_list'], 'href' => 'article.php?act=list&' . list_link_postfix()));
    $smarty->assign('form_action', 'update');

    assign_query_info();
    $smarty->display('article_info.htm');
}

if ($_REQUEST['act'] =='update')
{
    /* 权限判断 */
    admin_priv('article_manage');

    /*检查文章名是否相同*/
    $is_only = $exc->is_only('title', $_POST['title'], $_POST['id'], "cat_id = '$_POST[article_cat]'");

    if (!$is_only)
    {
        sys_msg(sprintf($_LANG['title_exist'], stripslashes($_POST['title'])), 1);
    }


    if (empty($_POST['cat_id']))
    {
        $_POST['cat_id'] = 0;
    }

    /* 取得文件地址 */
    $file_url = '';
    if (empty($_FILES['file']['error']) || (!isset($_FILES['file']['error']) && isset($_FILES['file']['tmp_name']) && $_FILES['file']['tmp_name'] != 'none'))
    {
        // 检查文件格式
        if (!check_file_type($_FILES['file']['tmp_name'], $_FILES['file']['name'], $allow_file_types))
        {
            sys_msg($_LANG['invalid_file']);
        }

        // 复制文件
        $res = upload_article_file($_FILES['file']);
        if ($res != false)
        {
            $file_url = $res;
        }
    }

    if ($file_url == '')
    {
        $file_url = $_POST['file_url'];
    }

    /* 计算文章打开方式 */
    if ($file_url == '')
    {
        $open_type = 0;
    }
    else
    {
        $open_type = $_POST['FCKeditor1'] == '' ? 1 : 2;
    }

    /* 如果 file_url 跟以前不一样，且原来的文件是本地文件，删除原来的文件 */
    $sql = "SELECT file_url FROM " . $yp->table('supplier_article') . " WHERE article_id = '$_POST[id]'";
    $old_url = $db->getOne($sql);
    if ($old_url != '' && $old_url != $file_url && strpos($old_url, 'http://') === false && strpos($old_url, 'https://') === false)
    {
        @unlink(ROOT_PATH . $old_url);
    }

    if ($exc->edit("title='$_POST[title]', cat_id='$_POST[article_cat]', article_type='$_POST[article_type]', is_open='$_POST[is_open]', author='$_POST[author]', author_email='$_POST[author_email]', keywords ='$_POST[keywords]', file_url ='$file_url', open_type='$open_type', content='$_POST[FCKeditor1]', link='$_POST[link_url]', description = '$_POST[description]'", $_POST['id']))
    {
        $link[0]['text'] = $_LANG['back_list'];
        $link[0]['href'] = 'article.php?act=list&' . list_link_postfix();

        $note = sprintf($_LANG['articleedit_succeed'], stripslashes($_POST['title']));
        admin_log($_POST['title'], 'edit', 'article');

        clear_cache_files();
		
		/* 代码增加_start  By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 */
	    clearhtml_file('article', $_POST['article_cat'], $_POST['id']);		
		/* 代码增加_end  By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 */
		
        sys_msg($note, 0, $link);
    }
    else
    {
        die($db->error());
    }
}

/*------------------------------------------------------ */
//-- 编辑文章主题
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_title')
{
    check_authz_json('article_manage');

    $id    = intval($_POST['id']);
    $title = json_str_iconv(trim($_POST['val']));

    /* 检查文章标题是否重复 */
    if ($exc->num("title", $title, $id) != 0)
    {
        make_json_error(sprintf($_LANG['title_exist'], $title));
    }
    else
    {
        if ($exc->edit("title = '$title'", $id))
        {
            clear_cache_files();
            admin_log($title, 'edit', 'article');
            make_json_result(stripslashes($title));
        }
        else
        {
            make_json_error($db->error());
        }
    }
}

/*------------------------------------------------------ */
//-- 切换是否显示
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'toggle_show')
{
    check_authz_json('article_manage');

    $id     = intval($_POST['id']);
    $val    = intval($_POST['val']);

    $exc->edit("is_open = '$val'", $id);
    clear_cache_files();

    make_json_result($val);
}

/*------------------------------------------------------ */
//-- 切换文章重要性
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'toggle_type')
{
    check_authz_json('article_manage');

    $id     = intval($_POST['id']);
    $val    = intval($_POST['val']);

    $exc->edit("article_type = '$val'", $id);
    clear_cache_files();

    make_json_result($val);
}



/*------------------------------------------------------ */
//-- 删除文章主题
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
    check_authz_json('article_manage');

    $id = intval($_GET['id']);

    $sql = "SELECT supplier_id FROM " . $yp->table('supplier_article') . " WHERE article_id = '$id' ";
    $supplier_id = $db->getOne($sql);
    if ($supplier_id != $_SESSION['supplier_id'])
    {
        /* F非自己保留分类，不能删除 */
        make_json_error('非法操作');
    }

    /* 删除原来的文件 */
    $sql = "SELECT file_url FROM " . $yp->table('supplier_article') . " WHERE article_id = '$id'";
    $old_url = $db->getOne($sql);
    if ($old_url != '' && strpos($old_url, 'http://') === false && strpos($old_url, 'https://') === false)
    {
        @unlink(ROOT_PATH . $old_url);
    }

    $name = $exc->get_name($id);
    if ($exc->drop($id))
    {
        $db->query("DELETE FROM " . $yp->table('comment') . " WHERE " . "comment_type = 1 AND id_value = $id");
        
        admin_log(addslashes($name),'remove','article');
        clear_cache_files();
    }

    $url = 'article.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    yp_header("Location: $url\n");
    exit;
}

/*------------------------------------------------------ */
//-- 将商品加入关联
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add_link_goods')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;

    check_authz_json('article_manage');

    $add_ids = $json->decode($_GET['add_ids']);
    $args = $json->decode($_GET['JSON']);
    $article_id = $args[0];

    if ($article_id == 0)
    {
        $article_id = $db->getOne('SELECT MAX(article_id)+1 AS article_id FROM ' .$yp->table('article'));
    }

    foreach ($add_ids AS $key => $val)
    {
        $sql = 'INSERT INTO ' . $yp->table('goods_article') . ' (goods_id, article_id) '.
               "VALUES ('$val', '$article_id')";
        $db->query($sql, 'SILENT') or make_json_error($db->error());
    }

    /* 重新载入 */
    $arr = get_article_goods($article_id);
    $opt = array();

    foreach ($arr AS $key => $val)
    {
        $opt[] = array('value'  => $val['goods_id'],
                        'text'  => $val['goods_name'],
                        'data'  => '');
    }

    make_json_result($opt);
}

/*------------------------------------------------------ */
//-- 将商品删除关联
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'drop_link_goods')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;

    check_authz_json('article_manage');

    $drop_goods     = $json->decode($_GET['drop_ids']);
    $arguments      = $json->decode($_GET['JSON']);
    $article_id     = $arguments[0];

    if ($article_id == 0)
    {
        $article_id = $db->getOne('SELECT MAX(article_id)+1 AS article_id FROM ' .$yp->table('article'));
    }

    $sql = "DELETE FROM " . $yp->table('goods_article').
            " WHERE article_id = '$article_id' AND goods_id " .db_create_in($drop_goods);
    $db->query($sql, 'SILENT') or make_json_error($db->error());

    /* 重新载入 */
    $arr = get_article_goods($article_id);
    $opt = array();

    foreach ($arr AS $key => $val)
    {
        $opt[] = array('value'  => $val['goods_id'],
                        'text'  => $val['goods_name'],
                        'data'  => '');
    }

    make_json_result($opt);
}

/*------------------------------------------------------ */
//-- 搜索商品
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'get_goods_list')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;

    $filters = $json->decode($_GET['JSON']);

    $arr = get_goods_list($filters);
    $opt = array();

    foreach ($arr AS $key => $val)
    {
        $opt[] = array('value' => $val['goods_id'],
                        'text' => $val['goods_name'],
                        'data' => $val['shop_price']);
    }

    make_json_result($opt);
}
/*------------------------------------------------------ */
//-- 批量操作
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'batch')
{
    /* 批量删除 */
    if (isset($_POST['type']))
    {
        if ($_POST['type'] == 'button_remove')
        {
            admin_priv('article_manage');

            if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']))
            {
                sys_msg($_LANG['no_select_article'], 1);
            }

            /* 删除原来的文件 */
            $sql = "SELECT file_url FROM " . $yp->table('article') .
                    " WHERE article_id " . db_create_in(join(',', $_POST['checkboxes'])) .
                    " AND file_url <> ''";

            $res = $db->query($sql);
            while ($row = $db->fetchRow($res))
            {
                $old_url = $row['file_url'];
                if (strpos($old_url, 'http://') === false && strpos($old_url, 'https://') === false)
                {
                    @unlink(ROOT_PATH . $old_url);
                }
            }

            foreach ($_POST['checkboxes'] AS $key => $id)
            {
                if ($exc->drop($id))
                {
                    $name = $exc->get_name($id);
                    admin_log(addslashes($name),'remove','article');
                }
            }

        }

        /* 批量隐藏 */
        if ($_POST['type'] == 'button_hide')
        {
            check_authz_json('article_manage');
            if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']))
            {
                sys_msg($_LANG['no_select_article'], 1);
            }

            foreach ($_POST['checkboxes'] AS $key => $id)
            {
              $exc->edit("is_open = '0'", $id);
            }
        }

        /* 批量显示 */
        if ($_POST['type'] == 'button_show')
        {
            check_authz_json('article_manage');
            if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']))
            {
                sys_msg($_LANG['no_select_article'], 1);
            }

            foreach ($_POST['checkboxes'] AS $key => $id)
            {
              $exc->edit("is_open = '1'", $id);
            }
        }

        /* 批量移动分类 */
        if ($_POST['type'] == 'move_to')
        {
            check_authz_json('article_manage');
            if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']) )
            {
                sys_msg($_LANG['no_select_article'], 1);
            }

            if(!$_POST['target_cat'])
            {
                sys_msg($_LANG['no_select_act'], 1);
            }
            
            foreach ($_POST['checkboxes'] AS $key => $id)
            {
              $exc->edit("cat_id = '".$_POST['target_cat']."'", $id);
            }
        }
    }

    /* 清除缓存 */
    clear_cache_files();
    $lnk[] = array('text' => $_LANG['back_list'], 'href' => 'article.php?act=list');
    sys_msg($_LANG['batch_handle_ok'], 0, $lnk);
}

/* 把商品删除关联 */
function drop_link_goods($goods_id, $article_id)
{
    $sql = "DELETE FROM " . $GLOBALS['yp']->table('goods_article') .
            " WHERE goods_id = '$goods_id' AND article_id = '$article_id' LIMIT 1";
    $GLOBALS['db']->query($sql);
    create_result(true, '', $goods_id);
}

/* 取得文章关联商品 */
function get_article_goods($article_id)
{
    $list = array();
    $sql  = 'SELECT g.goods_id, g.goods_name'.
            ' FROM ' . $GLOBALS['yp']->table('goods_article') . ' AS ga'.
            ' LEFT JOIN ' . $GLOBALS['yp']->table('goods') . ' AS g ON g.goods_id = ga.goods_id'.
            " WHERE ga.article_id = '$article_id'";
    $list = $GLOBALS['db']->getAll($sql);

    return $list;
}

/* 获得文章列表 */
function get_articleslist()
{
    $result = get_filter();
    if ($result === false)
    {
        $filter = array();
        $filter['keyword']    = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)
        {
            $filter['keyword'] = json_str_iconv($filter['keyword']);
        }
        
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'a.article_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $where = "AND a.supplier_id='$_SESSION[supplier_id]' ";
        if (!empty($filter['keyword']))
        {
            $where = " AND a.title LIKE '%" . mysql_like_quote($filter['keyword']) . "%'";
        }
       

        /* 文章总数 */
        $sql = 'SELECT COUNT(*) FROM ' .$GLOBALS['yp']->table('supplier_article'). ' AS a '.
               'LEFT JOIN ' .$GLOBALS['yp']->table('supplier_article_cat'). ' AS ac ON ac.cat_id = a.cat_id '.
               'WHERE 1 ' .$where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        $filter = page_and_size($filter);

        /* 获取文章数据 */
        $sql = 'SELECT a.* , ac.cat_name '.
               'FROM ' .$GLOBALS['yp']->table('supplier_article'). ' AS a '.
               'LEFT JOIN ' .$GLOBALS['yp']->table('supplier_article_cat'). ' AS ac ON ac.cat_id = a.cat_id '.
               'WHERE 1 ' .$where. ' ORDER by '.$filter['sort_by'].' '.$filter['sort_order'];

        $filter['keyword'] = stripslashes($filter['keyword']);
        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }
    $arr = array();
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    while ($rows = $GLOBALS['db']->fetchRow($res))
    {
        $rows['date'] = local_date($GLOBALS['_CFG']['time_format'], $rows['add_time']);

        $arr[] = $rows;
    }
    return array('arr' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

/* 上传文件 */
function upload_article_file($upload)
{
    if (!make_dir("../" . DATA_DIR . "/article"))
    {
        /* 创建目录失败 */
        return false;
    }

    $filename = cls_image::random_filename() . substr($upload['name'], strpos($upload['name'], '.'));
    $path     = ROOT_PATH. DATA_DIR . "/article/" . $filename;

    if (move_upload_file($upload['tmp_name'], $path))
    {
        return DATA_DIR . "/article/" . $filename;
    }
    else
    {
        return false;
    }
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