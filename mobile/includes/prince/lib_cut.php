<?php
//今天优品 多商户系统    
// 插件开发者:PRINCE
// QQ:120029121
/**
 * 取得砍价活动信息
 * @param   int     $act_id     活动id
 * @return  array
 */
function cut_info($act_id, $config = false)
{
    $sql = "SELECT * FROM " . $GLOBALS['yp']->table('goods_activity') . " WHERE act_id = '$act_id'";
    $cut = $GLOBALS['db']->getRow($sql);
    if ($cut['act_type'] != GAT_CUT)
    {
        return array();
    }
    $cut['status_no'] = cut_status($cut);
    if ($config == true)
    {

        $cut['start_time'] = local_date('Y-m-d H:i', $cut['start_time']);
        $cut['end_time'] = local_date('Y-m-d H:i', $cut['end_time']);
    }
    else
    {
        $cut['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $cut['start_time']);
        $cut['end_time'] = local_date($GLOBALS['_CFG']['time_format'], $cut['end_time']);
    }
    $ext_info = unserialize($cut['ext_info']);
    $cut = array_merge($cut, $ext_info);
    $cut['formated_start_price'] = price_format($cut['start_price']);
    $cut['formated_end_price'] = price_format($cut['end_price']);
    $cut['formated_max_price'] = price_format($cut['max_price']);
    $cut['formated_deposit'] = price_format($cut['deposit']);
    $cut['goods_name'] = $cut['act_name']?$cut['act_name']:$cut['goods_name'];


    return $cut;
}



/**
 * 取得砍价活动出价记录
 * @param   int     $act_id     活动id
 * @return  array
 */
function cut_log($act_id)
{
    $log = array();
    $sql = "SELECT a.* " .
            "FROM " . $GLOBALS['yp']->table('cut') . " AS a " .
            "WHERE act_id = '$act_id' " .
            "ORDER BY a.new_price ASC LIMIT 10";
    $res = $GLOBALS['db']->query($sql);
	$rownum=1;
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['cut_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['cut_time']);
        $row['user_nickname'] = $row['user_nickname'];
        $row['shop_price'] = price_format($row['shop_price'], false);
        $row['new_price'] = price_format($row['new_price'], false);
        $row['rownum'] = $rownum;
	    $rownum=$rownum+1;
        $log[] = $row;
    }

    return $log;
}

/**
 * 取得砍价活动出价记录
 * @param   int     $act_id     $user_id
 * @return  array
 */
function user_cut_log($user_id,$act_id, $page = 1)
{
	
    /* 取得砍价记录列表 */
    $count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' .$GLOBALS['yp']->table('cut_log').
           " WHERE act_user = '$user_id' AND act_id = '$act_id' ");
    $size  = 10;
    $page_count = ($count > 0) ? intval(ceil($count / $size)) : 1;
	
	
    $log = array();
    $sql = "SELECT c.* " .
            "FROM " . $GLOBALS['yp']->table('cut_log') . " AS c  " .
            "LEFT JOIN " .  $GLOBALS['yp']->table('cut') . " AS u ON (u.user_id = c.act_user and u.act_id=c.act_id) " .
            "WHERE u.user_id = '$user_id' " .
            "AND u.act_id = '$act_id' " .
            "ORDER BY c.log_id DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $size, ($page-1) * $size);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['cut_user_nickname'] = $row['cut_user_nickname'];
        $row['formated_cut_price'] = price_format($row['cut_price'], false);
        $row['formated_cut_price'] = price_format($row['cut_price'], false);
        $row['formated_after_cut_price'] = price_format($row['after_cut_price'], false);
        $log[] = $row;
    }
	
    $pager['page']         = $page;
    $pager['size']         = $size;
    $pager['record_count'] = $count;
    $pager['page_count']   = $page_count;
    $pager['page_first']   = "javascript:gotoPage(1,$id,$type)";
    $pager['page_prev']    = $page > 1 ? "cut.php?act=logpage&id=$act_id&actuid=$user_id&page=".($page-1): false;
    $pager['page_next']    = $page < $page_count ? "cut.php?act=logpage&id=$act_id&actuid=$user_id&page=".($page + 1): false;
    $pager['page_last']    = $page < $page_count ? 'javascript:gotoPage(' .$page_count. ",$id,$type)"  : 'javascript:;';

    $log = array('log' => $log, 'pager' => $pager);

    return $log;
}

/**
 * 计算砍价活动状态（注意参数一定是原始信息）
 * @param   array   $auction    砍价活动原始信息
 * @return  int
 */
function cut_status($cut)
{
    $now = gmtime();
    if ($cut['is_finished'] == 0)
    {
        if ($now < $cut['start_time'])
        {
            return PRE_START; // 未开始
        }
        elseif ($now > $cut['end_time'])
        {
            return FINISHED; // 已结束，未处理
        }
        else
        {
            return UNDER_WAY; // 进行中
        }
    }
    elseif ($cut['is_finished'] == 1)
    {
        return FINISHED; // 已结束，未处理
    }
    else
    {
        return SETTLED; // 已结束，已处理
    }
}



/**
 * 取得砍价活动数量
 * @return  int
 */
function cut_count()
{
    $now = gmtime();
    $sql = "SELECT COUNT(*) " .
            "FROM " . $GLOBALS['yp']->table('goods_activity') .
            "WHERE act_type = '" . GAT_CUT . "' " .
            "AND start_time <= '$now' AND end_time >= '$now' AND is_finished < 2";

    return $GLOBALS['db']->getOne($sql);
}

/**
 * 取得某页的砍价活动
 * @param   int     $size   每页记录数
 * @param   int     $page   当前页
 * @return  array
 */
function cut_list($size, $page)
{
    $cut_list = array();
    $cut_list['finished'] = $cut_list['finished'] = array();

    $now = gmtime();
    $sql = "SELECT a.*,g.*, IFNULL(g.goods_thumb, '') AS goods_thumb " .
            "FROM " . $GLOBALS['yp']->table('goods_activity') . " AS a " .
                "LEFT JOIN " . $GLOBALS['yp']->table('goods') . " AS g ON a.goods_id = g.goods_id " .
            "WHERE a.act_type = '" . GAT_CUT . "' " .
            "AND a.start_time <= '$now' AND a.end_time >= '$now' AND a.is_finished < 2 ORDER BY a.act_id DESC";
    //$res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);
    $res = $GLOBALS['db']->selectLimit($sql, 100000, ($page - 1) * $size);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $ext_info = unserialize($row['ext_info']);
        $cut = array_merge($row, $ext_info);
        $cut['status_no'] = cut_status($cut);

        $cut['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $cut['start_time']);
        $cut['end_time']   = local_date($GLOBALS['_CFG']['time_format'], $cut['end_time']);
        $cut['formated_start_price'] = price_format($cut['start_price']);
        $cut['formated_end_price'] = price_format($cut['end_price']);
        $cut['formated_deposit'] = price_format($cut['deposit']);
        $cut['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $cut['url'] = build_uri('cut', array('auid'=>$cut['act_id']));  //???
        $cut['shop_price'] = price_format($row['shop_price']);
        $cut['goods_name'] = $row['act_name']?$row['act_name']:$row['goods_name'];

        if($cut['status_no'] < 2)
        {
            $cut_list['under_way'][] = $cut;
        }
        else
        {
            $cut_list['finished'][] = $cut;
        }
    }

    $cut_list = @array_merge($cut_list['under_way'], $cut_list['finished']);

    return $cut_list;
}

/**
 * 取得某页的砍价活动
 * @param   int     $size   每页记录数
 * @param   int     $page   当前页
 * @return  array
 */
function cut_user_list($size, $page,$act_user)
{
    $cut_list = array();
    $cut_list['finished'] = $cut_list['finished'] = array();

    $now = gmtime();
    $sql = "SELECT u.*,a.*,g.*, IFNULL(g.goods_thumb, '') AS goods_thumb " .
            "FROM " . $GLOBALS['yp']->table('cut') . " AS u " .
            "LEFT JOIN " . $GLOBALS['yp']->table('goods_activity') . " AS a ON u.act_id  = a.act_id  " .
                "LEFT JOIN " . $GLOBALS['yp']->table('goods') . " AS g ON a.goods_id = g.goods_id " .
            "WHERE a.act_type = '" . GAT_CUT . "' " .
            "AND u.user_id='$act_user'  ORDER BY u.cut_id DESC";
    //$res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);
    $res = $GLOBALS['db']->selectLimit($sql, 100000, ($page - 1) * $size);

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $ext_info = unserialize($row['ext_info']);
        $cut = array_merge($row, $ext_info);
        $cut['status_no'] = cut_status($cut);

        $cut['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $cut['start_time']);
        $cut['end_time']   = local_date($GLOBALS['_CFG']['time_format'], $cut['end_time']);
        $cut['formated_start_price'] = price_format($cut['start_price']);
        $cut['formated_end_price'] = price_format($cut['end_price']);
        $cut['formated_deposit'] = price_format($cut['deposit']);
        $cut['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $cut['url'] = build_uri('cut', array('auid'=>$cut['act_id']));  //???
        $cut['shop_price'] = price_format($row['shop_price']);
        $cut['goods_name'] = $row['act_name']?$row['act_name']:$row['goods_name'];

        if($cut['status_no'] < 2)
        {
            $cut_list['under_way'][] = $cut;
        }
        else
        {
            $cut_list['finished'][] = $cut;
        }
    }

    $cut_list = @array_merge($cut_list['under_way'], $cut_list['finished']);

    return $cut_list;
}


/**
 * 获取活动列表
 *
 * @access  public
 *
 * @return void
 */
function get_cutlist()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 查询条件 */
        $filter['keywords']   = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)
        {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'act_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $where = (!empty($filter['keywords'])) ? " AND act_name like '%". mysql_like_quote($filter['keywords']) ."%'" : '';

        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['yp']->table('goods_activity') .
               " WHERE act_type =" . GAT_CUT . $where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        $filter = page_and_size($filter);

        /* 获活动数据 */
        $sql = "SELECT act_id, act_name AS cut_name, goods_name, start_time, end_time, is_finished, ext_info, product_id,free_shipping ".
               " FROM " . $GLOBALS['yp']->table('goods_activity') .
               " WHERE act_type = " . GAT_CUT . $where .
               " ORDER by $filter[sort_by] $filter[sort_order] LIMIT ". $filter['start'] .", " . $filter['page_size'];

        $filter['keywords'] = stripslashes($filter['keywords']);
        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $row = $GLOBALS['db']->getAll($sql);

    foreach ($row AS $key => $val)
    {
        $row[$key]['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $val['start_time']);
        $row[$key]['end_time']   = local_date($GLOBALS['_CFG']['time_format'], $val['end_time']);
        $info = unserialize($row[$key]['ext_info']);
        unset($row[$key]['ext_info']);
        if ($info)
        {
            foreach ($info as $info_key => $info_val)
            {
                $row[$key][$info_key] = $info_val;
            }
        }
    }

    $arr = array('cuts' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

/**
 * 获取指定id cut 的信息
 *
 * @access  public
 * @param   int         $id         act_id
 *
 * @return array       array(act_id, cut_name, goods_id,start_time, end_time, min_price, integral)
 */
function get_cut_info($id)
{
    global $yp, $db,$_CFG;

    $sql = "SELECT act_id, act_name AS cut_name, goods_id, product_id, goods_name, start_time, end_time, act_desc, ext_info,free_shipping" .
           " FROM " . $GLOBALS['yp']->table('goods_activity') .
           " WHERE act_id='$id' AND act_type = " . GAT_CUT;

    $cut = $db->GetRow($sql);

    /* 将时间转成可阅读格式 */
    $cut['start_time'] = local_date('Y-m-d H:i', $cut['start_time']);
    $cut['end_time']   = local_date('Y-m-d H:i', $cut['end_time']);
    $row = unserialize($cut['ext_info']);
    unset($cut['ext_info']);
    if ($row)
    {
        foreach ($row as $key=>$val)
        {
            $cut[$key] = $val;
        }
    }

    return $cut;
}

/**
 * 返回活动详细列表
 *
 * @access  public
 *
 * @return array
 */
function get_cut_log_detail()
{
    $filter['cut_id']  = empty($_REQUEST['cut_id']) ? 0 : intval($_REQUEST['cut_id']);
    $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'cut_time' : trim($_REQUEST['sort_by']);
    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
    $cut_id=$filter['cut_id'];

    $where = empty($filter['cut_id']) ? '' : " WHERE  cut_id='$cut_id' ";

    /* 获得记录总数以及总页数 */
    $sql = "SELECT count(*) FROM ".$GLOBALS['yp']->table('cut_log'). $where;
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);

    $filter = page_and_size($filter);

    /* 获得活动数据 */
    $sql = "SELECT s.* ".
            " FROM ".$GLOBALS['yp']->table('cut_log')." AS s ".
            $where.
            " ORDER by ".$filter['sort_by']." ".$filter['sort_order'].
            " LIMIT ". $filter['start'] .", " . $filter['page_size'];
    $row = $GLOBALS['db']->getAll($sql);

    foreach ($row AS $key => $val)
    {
        //$row[$key]['cut_time'] = date($GLOBALS['_CFG']['time_format'], $val['cut_time']);
		 $row[$key]['cut_time'] =  local_date('Y-m-d H:i', $val['cut_time']);
		 $row[$key]['end_cut_time'] =  local_date('Y-m-d H:i', $val['end_cut_time']);
		 $row[$key]['end_buy_time'] =  local_date('Y-m-d H:i', $val['end_buy_time']);
    }

    $arr = array('cut' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

/**
 * 返回活动详细列表
 *
 * @access  public
 *
 * @return array
 */
function get_cut_detail()
{
    $filter['act_id']  = empty($_REQUEST['act_id']) ? 0 : intval($_REQUEST['act_id']);
    $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'create_time' : trim($_REQUEST['sort_by']);
    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
	
	$act_id=$filter['act_id'] ;

    $where = empty($filter['act_id']) ? '' : " WHERE act_id='$act_id' ";

    /* 获得记录总数以及总页数 */
    $sql = "SELECT count(*) FROM ".$GLOBALS['yp']->table('cut'). $where;
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);

    $filter = page_and_size($filter);

    /* 获得活动数据 */
    $sql = "SELECT * ".
            " FROM ".$GLOBALS['yp']->table('cut'). $where.
            " ORDER by ".$filter['sort_by']." ".$filter['sort_order'].
            " LIMIT ". $filter['start'] .", " . $filter['page_size'];
    $row = $GLOBALS['db']->getAll($sql);

    foreach ($row AS $key => $val)
    {
        //$row[$key]['create_time'] = date($GLOBALS['_CFG']['time_format'], $val['create_time']);
		 $row[$key]['create_time'] =  local_date('Y-m-d H:i', $val['create_time']);
		 $row[$key]['end_cut_time'] =  local_date('Y-m-d H:i', $val['end_cut_time']);
		 $row[$key]['end_buy_time'] =  local_date('Y-m-d H:i', $val['end_buy_time']);

    }

    $arr = array('cut' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}










function cut_detail_info($cut_id)

{		

		$sql = "SELECT ga.*,IFNULL(g.goods_thumb, '') AS goods_thumb, c.*,g.* " .

				"FROM  " . $GLOBALS['yp']->table('cut') . " AS c  " .

					"LEFT JOIN " . $GLOBALS['yp']->table('goods_activity') . " AS ga ON c.act_id  = ga.act_id  " .

						"LEFT JOIN " . $GLOBALS['yp']->table('goods') . " AS g ON ga.goods_id = g.goods_id " .

				"WHERE c.cut_id=".$cut_id."  ";

		$cutinfo = $GLOBALS['db']->getRow($sql);

        $ext_info = unserialize($cutinfo['ext_info']);

        $cutinfo = array_merge($cutinfo, $ext_info);


        $cutinfo['now_time']   = gmtime();


        if (empty($cutinfo['goods_thumb'])){
            $cutinfo['goods_thumb'] = get_image_path($cutinfo['goods_id'], $cutinfo['goods_thumb'], true);
        }

        $cutinfo['url'] = 'extpintuan.php?act=view&act_id='.$cutinfo['act_id'].'&u='.$_SESSION['user_id'];


    return $cutinfo;

}




function is_wechat_browser_for_cut(){
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    if (strpos($user_agent, 'MicroMessenger') === false){
      return false;
    } else {
      return true;
    }
}


?>

