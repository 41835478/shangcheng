<?php
//今天优品 多商户系统    
// 插件开发者:PRINCE
// QQ:120029121
/**
 * 插件公共函数
 * 原创作者：PRINCE
 * QQ: 120029121
 */


/**
 * 取得云购活动信息
 * @param   int     $act_id   云购活动id
 * @param   int     $current_num    本次购买数量（计算当前价时要加上的数量）
 * @return  array
 *                  status          状态：
 */
function lucky_buy_info($act_id, $current_num = 0)
{
    /* 取得云购活动信息 */
    $act_id = intval($act_id);
    $sql = "SELECT *, act_id AS lucky_buy_id, act_desc AS lucky_buy_desc, start_time AS start_date, end_time AS end_date " .
            "FROM " . $GLOBALS['yp']->table('goods_activity') .
            "WHERE act_id = '$act_id' " .
            "AND act_type = '" . GAT_LUCKY_BUY . "'";
    $lucky_buy = $GLOBALS['db']->getRow($sql);

    /* 如果为空，返回空数组 */
    if (empty($lucky_buy))
    {
        return array();
    }

    $ext_info = unserialize($lucky_buy['ext_info']);
    $lucky_buy = array_merge($lucky_buy, $ext_info);

    /* 格式化时间 */
    $lucky_buy['formated_start_date'] = local_date('Y-m-d H:i', $lucky_buy['start_time']);
    $lucky_buy['formated_end_date'] = local_date('Y-m-d H:i', $lucky_buy['end_time']);




    /* 统计信息 */
    $stat = lucky_buy_stat($act_id);
    $lucky_buy = array_merge($lucky_buy, $stat);



    /* 状态 */        
    $lucky_buy['status_no'] = lucky_buy_status($lucky_buy);
    if (isset($GLOBALS['_LANG']['gbs'][$lucky_buy['status']]))
    {
        $lucky_buy['status_desc'] = $GLOBALS['_LANG']['gbs'][$lucky_buy['status']];
    }

    $lucky_buy['start_time'] = $lucky_buy['formated_start_date'];
    $lucky_buy['end_time'] = $lucky_buy['formated_end_date'];

    return $lucky_buy;
}

/*
 * 取得某云购活动统计信息
 * @param   int     $act_id   云购活动id
 * @return  array   统计信息
 *                  total_order     总订单数
 *                  total_goods     总商品数
 *                  valid_order     有效订单数
 *                  valid_goods     有效商品数
 */
function lucky_buy_stat($act_id)
{
    $act_id = intval($act_id);

    /* 取得云购活动商品ID */
    $sql = "SELECT goods_id " .
           "FROM " . $GLOBALS['yp']->table('goods_activity') .
           "WHERE act_id = '$act_id' " .
           "AND act_type = '" . GAT_LUCKY_BUY . "'";
    $lucky_buy_goods_id = $GLOBALS['db']->getOne($sql);

    /* 取得总订单数和总商品数 */
    $sql = "SELECT COUNT(*) AS total_order, SUM(g.goods_number) AS total_goods " .
            "FROM " . $GLOBALS['yp']->table('order_info') . " AS o, " .
                $GLOBALS['yp']->table('order_goods') . " AS g " .
            " WHERE o.order_id = g.order_id " .
            "AND o.extension_code = 'lucky_buy' " .
            "AND o.extension_id = '$act_id' " .
            "AND g.goods_id = '$lucky_buy_goods_id' " .
            "AND (order_status = '" . OS_CONFIRMED . "' OR order_status = '" . OS_UNCONFIRMED . "')";
    $stat = $GLOBALS['db']->getRow($sql);
    if ($stat['total_order'] == 0)
    {
        $stat['total_goods'] = 0;
    }


        $stat['valid_order'] = $stat['total_order'];
        $stat['valid_goods'] = $stat['total_goods'];


	
    return $stat;
}

/**
 * 获得云购的状态
 *
 * @access  public
 * @param   array
 * @return  integer
 */
function lucky_buy_status($lucky_buy)
{
    $now = gmtime();
    if ($lucky_buy['is_finished'] == 0)
    {
        /* 未处理 */
        if ($now < $lucky_buy['start_time'])
        {
            $status = GBS_PRE_START;
        }
        elseif ($now > $lucky_buy['end_time'])
        {
            $status = GBS_FINISHED;
        }
        else
        {
                $status = GBS_UNDER_WAY;
        }
    }
    elseif ($lucky_buy['is_finished'] == 1)
    {
        /* 已结束 */
        $status = 2;
    }


    return $status;
}


/* 取得用户云购活动总数 */
function user_lucky_buy_count()
{
    $sql = "SELECT COUNT(DISTINCT order_id) " .
            "FROM " . $GLOBALS['yp']->table('lucky_buy_detail') .
            "WHERE user_id  = '" . $_SESSION['user_id'] . "' " ;

    return $GLOBALS['db']->getOne($sql);
}







function get_lucky_buy_detail()
{
    $filter['act_id']  = empty($_REQUEST['act_id']) ? 0 : intval($_REQUEST['act_id']);
    $filter['lucky_buy_id']  = empty($_REQUEST['lucky_buy_id']) ? 0 : intval($_REQUEST['lucky_buy_id']);
    $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'used_time' : trim($_REQUEST['sort_by']);
    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
	$act_id=$filter['act_id'] ;
	$lucky_buy_id=$filter['lucky_buy_id'] ;
	$order_id=$_REQUEST['order_id']?intval($_REQUEST['order_id']):0;

    $where = (empty($filter['lucky_buy_id']) )? '' : " WHERE lucky_buy_id='$lucky_buy_id'";

    //if($order_id){
    //	$where = " WHERE order_id='$order_id'";
	//}

    /* 获得记录总数以及总页数 */
    $sql = "SELECT count(*) FROM ".$GLOBALS['yp']->table('lucky_buy_detail'). $where;
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);

    $filter = page_and_size($filter);

    /* 获得活动数据 */
    $sql = "SELECT s.* ".
            " FROM ".$GLOBALS['yp']->table('lucky_buy_detail')." AS s ".
            $where.
            " ORDER by ".$filter['sort_by']." ".$filter['sort_order'].
            " LIMIT ". $filter['start'] .", " . $filter['page_size'];
    $row = $GLOBALS['db']->getAll($sql);

    foreach ($row AS $key => $val)
    {
		$row[$key]['create_time'] =  local_date('Y-m-d H:i', $val['create_time']);
		$row[$key]['used_time'] =  local_date('Y-m-d H:i', $val['used_time']);
    }

    $arr = array('info' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

/**
 * 返回活动详细列表
 *
 * @access  public
 *
 * @return array
 */
function get_lucky_buy()
{
    $filter['act_id']  = empty($_REQUEST['act_id']) ? 0 : intval($_REQUEST['act_id']);
    $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'lucky_buy_id' : trim($_REQUEST['sort_by']);
    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
	$act_id=$filter['act_id'] ;
	
    $where = (empty($filter['act_id']) )? '' : " WHERE act_id='$act_id' ";
	
    /* 获得记录总数以及总页数 */
    $sql = "SELECT count(*) FROM ".$GLOBALS['yp']->table('lucky_buy'). $where;
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);

    $filter = page_and_size($filter);

    /* 获得活动数据 */
    $sql = "SELECT * ".
            " FROM ".$GLOBALS['yp']->table('lucky_buy'). $where.
            " ORDER by ".$filter['sort_by']." ".$filter['sort_order'].
            " LIMIT ". $filter['start'] .", " . $filter['page_size'];
    $row = $GLOBALS['db']->getAll($sql);

    foreach ($row AS $key => $val)
    {

		$row[$key]['start_time'] =  local_date('Y-m-d H:i', $val['start_time']);
		$row[$key]['end_time'] =  local_date('Y-m-d H:i', $val['end_time']>0?$val['end_time']:0);

    }

    $arr = array('info' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}




/**
 * 取得云购活动数量
 * @return  int
 */
function lucky_buy_count()
{
    $now = gmtime();
    $sql = "SELECT COUNT(*) " .
            "FROM " . $GLOBALS['yp']->table('goods_activity') .
            "WHERE act_type = '" . GAT_LUCKY_BUY . "' " .
            "AND start_time <= '$now' AND end_time >= '$now' AND is_finished < 1";

    return $GLOBALS['db']->getOne($sql);
}

/**
 * 取得某页的云购活动
 * @param   int     $size   每页记录数
 * @param   int     $page   当前页
 * @return  array
 */
function lucky_buy_list($size, $page)
{   
    $lucky_buy_list = array();
    $lucky_buy_list['finished'] = $lucky_buy_list['finished'] = array();

    $now = gmtime();
    $sql = "SELECT a.*,g.*, IFNULL(g.goods_thumb, '') AS goods_thumb " .
            "FROM " . $GLOBALS['yp']->table('goods_activity') . " AS a " .
                "LEFT JOIN " . $GLOBALS['yp']->table('goods') . " AS g ON a.goods_id = g.goods_id " .
            "WHERE a.act_type = '" . GAT_LUCKY_BUY . "' " .
            "AND a.start_time <= '$now' AND a.end_time >= '$now' AND a.is_finished < 1 ORDER BY a.act_id DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size); 
    while ($row = $GLOBALS['db']->fetchRow($res))
    {   
        $ext_info = unserialize($row['ext_info']);
        $lucky_buy = array_merge($row, $ext_info);
        $lucky_buy['status_no'] = lucky_buy_status($lucky_buy);

        $lucky_buy['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $lucky_buy['start_time']);
        $lucky_buy['end_time']   = local_date($GLOBALS['_CFG']['time_format'], $lucky_buy['end_time']);
        $lucky_buy['formated_start_price'] = price_format($lucky_buy['start_price']);
        $lucky_buy['formated_end_price'] = price_format($lucky_buy['end_price']);
        $lucky_buy['formated_deposit'] = price_format($lucky_buy['deposit']);
        $lucky_buy['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $lucky_buy['url'] = 'lucky_buy.php?act=view&act_id='.$row['act_id'].'&u='.$_SESSION['user_id'];
        $lucky_buy['shop_price'] = price_format($row['shop_price']);

        if($lucky_buy['status_no'] < 2)
        {
            $lucky_buy_list['under_way'][] = $lucky_buy;
        }
        else
        {
            $lucky_buy_list['finished'][] = $lucky_buy;
        }
		
        if (empty($lucky_buy['goods_thumb'])){
            $lucky_buy['goods_thumb'] = get_image_path($lucky_buy['goods_id'], $lucky_buy['goods_thumb'], true);
        }
    }

    $lucky_buy_list = @array_merge($lucky_buy_list['under_way'], $lucky_buy_list['finished']);

    return $lucky_buy_list;
}

/**
 * 取得某页的云购活动
 * @param   int     $size   每页记录数
 * @param   int     $page   当前页
 * @return  array
 */
function lucky_buy_user_list($size, $page,$act_user)
{
    $lucky_buy_list = array();

    $now = gmtime();
    $sql = "SELECT DISTINCT lbd.order_id ,lb.status AS luck_buy_status,lb.*,ga.*,g.*, IFNULL(g.goods_thumb, '') AS goods_thumb  " .
            "FROM  " . $GLOBALS['yp']->table('lucky_buy_detail') . " AS lbd  " .
            "LEFT JOIN " . $GLOBALS['yp']->table('lucky_buy') . " AS lb ON lbd.lucky_buy_id   = lb.lucky_buy_id   " .
            	"LEFT JOIN " . $GLOBALS['yp']->table('goods_activity') . " AS ga ON lbd.act_id  = ga.act_id  " .
                	"LEFT JOIN " . $GLOBALS['yp']->table('goods') . " AS g ON ga.goods_id = g.goods_id " .
            "WHERE lbd.user_id='".$_SESSION['user_id']."' and lbd.user_id >0 ORDER BY lbd.lucky_buy_id DESC,lbd.order_id DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $ext_info = unserialize($row['ext_info']);
        $lucky_buy = array_merge($row, $ext_info);
        $lucky_buy['status_no'] = lucky_buy_status($lucky_buy);

        $lucky_buy['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $lucky_buy['start_time']);
        $lucky_buy['end_time']   = local_date($GLOBALS['_CFG']['time_format'], $lucky_buy['end_time']);
        $lucky_buy['formated_start_price'] = price_format($lucky_buy['start_price']);
        $lucky_buy['formated_end_price'] = price_format($lucky_buy['end_price']);
        $lucky_buy['formated_deposit'] = price_format($lucky_buy['deposit']);
        $lucky_buy['goods_thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $lucky_buy['shop_price'] = price_format($row['shop_price']);
        $lucky_buy['lucky_user_id'] = $row['lucky_user_id'];

        $lucky_buy['status'] = $row['luck_buy_status'];
		
		$lucky_buy_list[] = $lucky_buy;


    }

    return $lucky_buy_list;
}


/* 取得云购活动记录总数 */
function count_lucky_buy_detail($lucky_buy_id)
{
    $sql = "SELECT count(*) " .
            "  FROM  " . $GLOBALS['yp']->table('lucky_buy_detail') . 
            "  WHERE lucky_buy_id=".$lucky_buy_id." and user_id >0 GROUP BY user_id,order_id ORDER BY used_time DESC";

    return $GLOBALS['db']->getOne($sql);
}

/**
 * 取得云购活动记录
 * @param   int     $size   每页记录数
 * @param   int     $page   当前页
 * @return  array
 */
function lucky_buy_detail($size, $page,$lucky_buy_id)
{
    $lucky_buy_detail = array();

    $now = gmtime();
    $sql = "SELECT user_id ,used_time, user_name,user_head,used_time,used_time_millisecond,count(code) as total " .
            "  FROM  " . $GLOBALS['yp']->table('lucky_buy_detail') . 
            "  WHERE lucky_buy_id=".$lucky_buy_id." and user_id >0 GROUP BY user_id,order_id ORDER BY used_time DESC, used_time_millisecond DESC ";
    $res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);
    while ($row = $GLOBALS['db']->fetchRow($res)){
		$lucky_buy_detail[] = $row;
    }

    return $lucky_buy_detail;
}


/* 取得云购往期总数 */
function schedulelist_count($act_id)
{
    $sql = "SELECT COUNT(*) " .
            " FROM " . $GLOBALS['yp']->table('lucky_buy') .
            " WHERE act_id  = '" . $act_id . "' and status >0 ";

    return $GLOBALS['db']->getOne($sql);
}


/**
 * 取得某页的云购排期情况
 * @param   int     $size   每页记录数
 * @param   int     $page   当前页
 * @return  array
 */
function schedulelist_list($size, $page,$act_id)
{
    $lucky_buy_list = array();

    $now = gmtime();
    $sql = "SELECT *  " .
            " FROM  " . $GLOBALS['yp']->table('lucky_buy') . 
            " WHERE act_id=".$act_id." and status >0 ORDER BY lucky_buy_id DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $lucky_buy['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['start_time']);
        $lucky_buy['end_time']   = local_date($GLOBALS['_CFG']['time_format'], $row['end_time']);
        $lucky_buy['lucky_user_head'] = $row['lucky_user_head'];
        $lucky_buy['lucky_user_name'] = $row['lucky_user_name'];
        $lucky_buy['lucky_user_id'] = $row['lucky_user_id'];
        $lucky_buy['lucky_user_order_id'] = $row['lucky_user_order_id'];
        $lucky_buy['lucky_code'] = $row['lucky_code'];
        $lucky_buy['total'] = $row['total'];
        $lucky_buy['schedule_id'] = $row['schedule_id'];
        $lucky_buy['lucky_buy_id'] = $row['lucky_buy_id'];

        $lucky_buy['status'] = $row['status'];
		
		$lucky_buy_list[] = $lucky_buy;


    }

    return $lucky_buy_list;
}


//根据lucky_buy_id查询当期lucky_buy信息
function lucky_buy_by_lucky_buy_id($lucky_buy_id)
{
    $sql = "SELECT * " .
            "  FROM  " . $GLOBALS['yp']->table('lucky_buy') . 
            "  WHERE lucky_buy_id=".$lucky_buy_id." LIMIT 1";
    $res = $GLOBALS['db']->getRow($sql);
    return $res;
}


function ship_code($lucky_buy_id){
	
	//取出云购待发货订单数据  Start

    $sql = "SELECT o.*,og.goods_number " .
            " FROM " . $GLOBALS['yp']->table('order_info') .  " AS o " .
			"LEFT JOIN " . $GLOBALS['yp']->table('order_goods') . " AS og   ON o.order_id  = og.order_id   " .
            " WHERE o.order_status=1 AND o.pay_status=2  " .
            " AND o.shipping_status=0 AND o.extension_code='lucky_buy' AND o.extension_id>0 " ;
			
    $row = $GLOBALS['db']->getAll($sql);

	//云购发货 Start
    foreach ($row AS $key => $val)
    {
			$sql = "SELECT used_time,used_time_millisecond,calculate_number,count(1) as had_shipped  FROM  " . $GLOBALS['yp']->table('lucky_buy_detail') . 
						" WHERE order_id=".$val['order_id'].
						" AND order_id>0 ".
						" GROUP BY used_time,used_time_millisecond,calculate_number ";
			$had_shipped_info = $GLOBALS['db']->getRow($sql);
			
			$need_number=$val['goods_number'];
			if(empty($had_shipped_info)){
				$now = gmtime();
				$used_time_millisecond=getMillisecond();
				$the_format_time   = local_date($GLOBALS['_CFG']['time_format'], $now);
				$calculate_number=substr($the_format_time,11,2).substr($the_format_time,14,2).substr($the_format_time,17,2).$used_time_millisecond;
			}else{
				$now = $had_shipped_info['used_time'];
				$used_time_millisecond=$had_shipped_info['used_time_millisecond'];
				$calculate_number=$had_shipped_info['calculate_number'];
				$need_number=$val['goods_number']-$had_shipped_info['had_shipped'];
			}
			
			$sql = "SELECT lucky_buy_id " .
						" FROM  " . $GLOBALS['yp']->table('lucky_buy') . 
						" WHERE act_id=".$val['extension_id'].
						" AND available >0 ";
			$lucky_buy_info = $GLOBALS['db']->getAll($sql);
			if(!empty($lucky_buy_info)){
				foreach ($lucky_buy_info AS $key => $lucky_buy_info){  
					update_available($lucky_buy_info['lucky_buy_id']);
				}
			}

			$sql = "SELECT * " .
						" FROM  " . $GLOBALS['yp']->table('lucky_buy') . 
						" WHERE act_id=".$val['extension_id'].
						" AND available >0 LIMIT 1";
			$last_lucky_buy_info = $GLOBALS['db']->getRow($sql);
			$available=	$last_lucky_buy_info['available']?$last_lucky_buy_info['available']:0;	
			$lucky_buy_id=$last_lucky_buy_info['lucky_buy_id']?$last_lucky_buy_info['lucky_buy_id']:0;
			
			if($available >=$need_number ){   //当期可用数量足够
			
			    if($need_number<=49){
					ship_code_by_rand($val['extension_id'],$need_number,$val['user_id'],$now,$used_time_millisecond,$calculate_number,$val['order_id'],$val['order_sn'],$lucky_buy_id);
					
				}else{
					$not_rand_run_number=floor($need_number/49);
					$rand_run_number=floor($need_number%49);
					
					ship_code_by_rand($val['extension_id'],$rand_run_number,$val['user_id'],$now,$used_time_millisecond,$calculate_number,$val['order_id'],$val['order_sn'],$lucky_buy_id);
					
					for ($x=0; $x<$not_rand_run_number; $x++) {echo $x;
						ship_code_notby_rand($val['extension_id'],49,$val['user_id'],$now,$used_time_millisecond,$calculate_number,$val['order_id'],$val['order_sn'],$lucky_buy_id);
					} 
				}
				update_available($lucky_buy_id);
			}else{   //当期可用数量不够，先发货，再新开一期发货
					
					ship_code_notby_rand($val['extension_id'],$available,$val['user_id'],$now,$used_time_millisecond,$calculate_number,$val['order_id'],$val['order_sn'],$lucky_buy_id);
					update_available($lucky_buy_id);
					$lucky_buy_id=goto_next_schedule($val['extension_id'],$need_number);
					$need_number =$need_number-$available;	
					if($need_number<=49){
						ship_code_by_rand($val['extension_id'],$need_number,$val['user_id'],$now,$used_time_millisecond,$calculate_number,$val['order_id'],$val['order_sn'],$lucky_buy_id);
						
					}else{
						$not_rand_run_number=floor($need_number/49);
						$rand_run_number=floor($need_number%49);
						echo gmtime();
						ship_code_by_rand($val['extension_id'],$rand_run_number,$val['user_id'],$now,$used_time_millisecond,$calculate_number,$val['order_id'],$val['order_sn'],$lucky_buy_id);
						echo gmtime();
						for ($x=0; $x<$not_rand_run_number; $x++) {
							ship_code_notby_rand($val['extension_id'],49,$val['user_id'],$now,$used_time_millisecond,$calculate_number,$val['order_id'],$val['order_sn'],$lucky_buy_id);
						} echo gmtime();
					}
					update_available($lucky_buy_id);									
			}
			update_shipping_status($val['order_id'],$val['goods_number'],$now);

    }
}

function calculate_lucky_code(){
	
	//取出云购待计算幸运码数据  Start
    $sql = "SELECT * " .
            " FROM " . $GLOBALS['yp']->table('lucky_buy') . 
            " WHERE available=0 AND status=0  " .
            " AND lucky_code<=0 " ;
			
    $row = $GLOBALS['db']->getAll($sql);
	
    //云购开奖 Start
    foreach ($row AS $key => $val){
            $now = gmtime();
			$sql = "SELECT * " .
						" FROM  " . $GLOBALS['yp']->table('lucky_buy_calculate') . 
						" WHERE lucky_buy_id='".$val['lucky_buy_id']."' ";
			$chk_lucky_buy_calculate = $GLOBALS['db']->getRow($sql);

			if(empty($chk_lucky_buy_calculate )){
							
				$sql = "INSERT INTO " .$GLOBALS['yp']->table('lucky_buy_calculate'). " (lucky_buy_id,act_id, schedule_id, code,create_time,used_time,used_time_millisecond,calculate_number)" .
                    "SELECT lucky_buy_id, act_id, schedule_id,code,create_time,used_time,used_time_millisecond,calculate_number ".
						" FROM  " . $GLOBALS['yp']->table('lucky_buy_detail') . 
						" WHERE lucky_buy_id=".$val['lucky_buy_id'].
						" GROUP BY order_id ORDER BY used_time DESC, used_time_millisecond DESC LIMIT 50";
				$GLOBALS['db']->query($sql);
			}
			
			$sql = "SELECT SUM(calculate_number) " .
						" FROM  " . $GLOBALS['yp']->table('lucky_buy_calculate') . 
						" WHERE lucky_buy_id=".$val['lucky_buy_id'].
						" LIMIT 50";
			$sum_calculate_number = $GLOBALS['db']->getOne($sql);
			$mod_of_sum=$sum_calculate_number%$val['total'];
			$lucky_code=$mod_of_sum+10000001;
			
			$sql = "SELECT * " .
						" FROM  " . $GLOBALS['yp']->table('lucky_buy_detail') . 
						" WHERE lucky_buy_id=".$val['lucky_buy_id'].
						" AND code =$lucky_code LIMIT 1";
			$lucky_code_info = $GLOBALS['db']->getRow($sql);
			
			$sql = 'UPDATE ' . $GLOBALS['yp']->table('lucky_buy_detail') . " SET `is_lucky_user` =1 ".
					" WHERE lucky_buy_id=".$val['lucky_buy_id'].
					" AND code =$lucky_code " ;
			$GLOBALS['db']->query($sql);
			
			$sql = 'UPDATE ' . $GLOBALS['yp']->table('lucky_buy') . ' SET `status` =1 '.
					", end_time = '" . $now.
					"', sum_of_calculate_number = '" . $sum_calculate_number.
					"', lucky_code = " . $lucky_code.
					", lucky_user_id = '" . $lucky_code_info['user_id'].
					"', lucky_user_name = '" . $lucky_code_info['user_name'].
					"', lucky_user_head = '" . $lucky_code_info['user_head'].
					"', lucky_user_order_id = '". $lucky_code_info['order_id'].
					"', lucky_user_order_sn = '". $lucky_code_info['order_sn'].
					"' WHERE lucky_buy_id='".$val['lucky_buy_id'].
					"'  " ;
			$GLOBALS['db']->query($sql);
			$lucky_buy_id=$val['lucky_buy_id'];
			include_once(ROOT_PATH.'wxm_lucky_buy.php');
		
	}
	
}


function get_calculate_info($lucky_buy_id){
		$sql = "SELECT count(*) " .
						" FROM  " . $GLOBALS['yp']->table('lucky_buy_calculate') . 
						" WHERE lucky_buy_id='".$lucky_buy_id.
						"' LIMIT 50";
		$count_all = $GLOBALS['db']->getOne($sql);
	
		$sql = "SELECT * " .
						" FROM  " . $GLOBALS['yp']->table('lucky_buy_calculate') . 
						" WHERE lucky_buy_id='".$lucky_buy_id.
						"' ORDER BY used_time DESC, used_time_millisecond DESC LIMIT 50";
		$row = $GLOBALS['db']->getAll($sql);
		$i=0;
        foreach ($row AS $key => $val)
		{   
			$row[$key]['used_time']   = local_date($GLOBALS['_CFG']['time_format'], $val['used_time']);
			$row[$key]['this_index']=$i+1;
			$i=$i+1;
			$row[$key]['count_all']=$count_all;
		}

		return $row;
	
}

//进入下一期
function goto_next_schedule($act_id,$need_number=1) {
	        $lucky_buy = lucky_buy_info($act_id);

			$sql = "select * from ". $GLOBALS['yp']->table('lucky_buy') . " where  act_id='$act_id' order by schedule_id desc limit 1";
			$chk_info =$GLOBALS['db']->getRow($sql);
			if(empty($chk_info)){
				$schedule_id='8'.$act_id.'80001';
			}elseif($chk_info['available']<$need_number){
				$schedule_id=$chk_info['schedule_id']+1;
			}
			if((empty($chk_info)|| $chk_info['available']<$need_number) && $schedule_id){	
				$nowtime=gmtime();
				$total=$lucky_buy['number'];
			    $sql = "INSERT INTO " .$GLOBALS['yp']->table('lucky_buy'). " (act_id, schedule_id, total,available,start_time)" .
                    "VALUES ('$act_id', '$schedule_id', '$total','$total','$nowtime')";	
			    $GLOBALS['db']->query($sql);
				$lucky_buy_id = $GLOBALS['db']->insert_id();		

				//开始创建云购码 

				$sql = "INSERT INTO " .$GLOBALS['yp']->table('lucky_buy_detail'). " (lucky_buy_id,act_id, schedule_id, code,create_time)" .
                    "SELECT '$lucky_buy_id', '$act_id', '$schedule_id',code,'$nowtime' FROM " .$GLOBALS['yp']->table('lucky_buy_code'). " ORDER BY code ASC LIMIT $total ";
			    $GLOBALS['db']->query($sql);
			    return $lucky_buy_id;

			}
}


//获取毫秒
function getMillisecond() {
	list($usec, $usec) = explode(' ', microtime());
	   $msec=round($usec*1000);
	   return $msec;
}




//云购
function get_lucky_buy_by_id($lucky_buy_id)
{
	$sql = "SELECT lb.* " .
				" FROM  " . $GLOBALS['yp']->table('lucky_buy') . " AS lb  " .
				" WHERE lb.lucky_buy_id=".$lucky_buy_id."  ";
    return   $GLOBALS['db']->getRow($sql);
}

                              

/*
 * 取得云购活动列表
 * @return   array
 */
function lucky_buy_list_adm()
{
    $result = get_filter();   

    if ($result === false)
    {
        /* 过滤条件 */
        $filter['keyword']      = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)
        {
            $filter['keyword'] = json_str_iconv($filter['keyword']);
        }
        $filter['sort_by']      = empty($_REQUEST['sort_by']) ? 'act_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order']   = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $where = (!empty($filter['keyword'])) ? " AND goods_name LIKE '%" . mysql_like_quote($filter['keyword']) . "%'" : '';

        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['yp']->table('goods_activity') .
                " WHERE act_type = '" . GAT_LUCKY_BUY . "' $where";
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        /* 分页大小 */
        $filter = page_and_size($filter);

        /* 查询 */
        $sql = "SELECT * ".
                "FROM " . $GLOBALS['yp']->table('goods_activity') .
                " WHERE act_type = '" . GAT_LUCKY_BUY . "' $where ".
                " ORDER BY $filter[sort_by] $filter[sort_order] ".
                " LIMIT ". $filter['start'] .", $filter[page_size]";

        $filter['keyword'] = stripslashes($filter['keyword']);
        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }
    $res = $GLOBALS['db']->query($sql);

    $list = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $ext_info = unserialize($row['ext_info']);
        $arr = array_merge($row, $ext_info);

    	$stat = lucky_buy_stat($arr['act_id']);
		
        $arr['valid_order']  = $stat['valid_order'] ;
        $arr['start_time']  = local_date($GLOBALS['_CFG']['date_format'], $arr['start_time']);
        $arr['end_time']    = local_date($GLOBALS['_CFG']['date_format'], $arr['end_time']);

        $list[] = $arr;
    }
    $arr = array('item' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

/**
 * 取得某商品的云购活动
 * @param   int     $goods_id   商品id
 * @return  array
 */
function goods_lucky_buy($goods_id)
{
    $sql = "SELECT * FROM " . $GLOBALS['yp']->table('goods_activity') .
            " WHERE goods_id = '$goods_id' " .
            " AND act_type = '" . GAT_LUCKY_BUY . "'" .
            " AND start_time <= " . gmtime() .
            " AND end_time >= " . gmtime();

    return $GLOBALS['db']->getRow($sql);
}

/**
 * 列表链接
 * @param   bool    $is_add         是否添加（插入）
 * @return  array('href' => $href, 'text' => $text)
 */
function list_link($is_add = true)
{
    $href = 'lucky_buy.php?act=list';
    if (!$is_add)
    {
        $href .= '&' . list_link_postfix();
    }

    return array('href' => $href, 'text' => $GLOBALS['_LANG']['lucky_buy_list']);
}




function ship_code_by_rand($act_id,$number,$user_id,$now,$used_time_millisecond,$calculate_number,$order_id,$order_sn,$lucky_buy_id){
	$sql = "SELECT * " .
						" FROM  " . $GLOBALS['yp']->table('lucky_buy_detail') . 
						" WHERE act_id=".$act_id.
						" AND user_id=0 and used_time=0".
						" ORDER BY RAND() LIMIT ". $number ;
	$codes = $GLOBALS['db']->getAll($sql);
				   
	//获取用户昵称、头像
	$sql = "SELECT * FROM  " . $GLOBALS['yp']->table('users') . " WHERE  user_id=".$user_id;
	$user_info =$GLOBALS['db']->getRow($sql);
	$user_info['user_name']=$user_info['nickname']?$user_info['nickname']:$user_info['user_name'];
	$user_info['headimg']=$user_info['headimg'];

	foreach ($codes AS $key => $code){
			$sql = 'UPDATE ' . $GLOBALS['yp']->table('lucky_buy_detail') . ' SET `used_time` ='.$now.
											", used_time_millisecond = " . $used_time_millisecond.
											", calculate_number = " . $calculate_number.
											", user_id = " . $user_id .
											", user_name = '" . $user_info['user_name'] .
											"', user_head = '" . $user_info['headimg'] .
											"', order_id = " . $order_id. 
											", order_sn = '" . $order_sn . 
							                "' WHERE id = " . $code['id'] ;
			$GLOBALS['db']->query($sql);
				
	}
	
}

function ship_code_notby_rand($act_id,$number,$user_id,$now,$used_time_millisecond,$calculate_number,$order_id,$order_sn,$lucky_buy_id){
				   
	//获取用户昵称、头像
	$sql = "SELECT * FROM  " . $GLOBALS['yp']->table('users') . " WHERE  u.user_id=".$user_id;
	$user_info =$GLOBALS['db']->getRow($sql);
	$user_info['user_name']=$user_info['nickname']?$user_info['nickname']:$user_info['user_name'];
	$user_info['headimg']=$user_info['headimg'];

	$sql = 'UPDATE ' . $GLOBALS['yp']->table('lucky_buy_detail') . ' SET `used_time` ='.$now.
											", used_time_millisecond = " . $used_time_millisecond.
											", calculate_number = " . $calculate_number.
											", user_id = " . $user_id .
											", user_name = '" . $user_info['user_name'] .
											"', user_head = '" . $user_info['headimg'] .
											"', order_id = " . $order_id. 
											", order_sn = '" . $order_sn . 
											"' WHERE act_id<='$act_id' and order_id<=0 AND user_id <=0 LIMIT ". $number ;

	$GLOBALS['db']->query($sql);					
	
}

function update_available($lucky_buy_id){
	$sql = "SELECT count(*)  FROM  " . $GLOBALS['yp']->table('lucky_buy_detail') . 
						 " WHERE lucky_buy_id = " . $lucky_buy_id .
						 " AND order_id>0  ";

	$had_used = $GLOBALS['db']->getOne($sql);
	
	$sql = 'UPDATE ' . $GLOBALS['yp']->table('lucky_buy') . " SET `available` =`total`-".$had_used.
							                " WHERE lucky_buy_id = " . $lucky_buy_id ;
	$GLOBALS['db']->query($sql);
}

function update_shipping_status($order_id,$number,$now){
	$sql = "SELECT count(*)  FROM  " . $GLOBALS['yp']->table('lucky_buy_detail') . 
						" WHERE order_id=".$order_id.
						" AND order_id>0  ";
	$had_shipped_number = $GLOBALS['db']->getOne($sql);

	if($had_shipped_number>=$number){
		$sql = 'UPDATE ' . $GLOBALS['yp']->table('order_info') . " SET `shipping_time` =".$now.
												", shipping_status = 2 " .
												" WHERE order_id = " . $order_id ;
		$GLOBALS['db']->query($sql);
	}

}

function is_wechat_browser_for_lucky_buy(){
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    if (strpos($user_agent, 'MicroMessenger') === false){
      return false;
    } else {
      return true;
    }
}


/**
 * 插件公共函数
 * 原创作者： P R I N C E
 * Q Q: 1 2 0 0 2 9 1 2 1
 */




?>
