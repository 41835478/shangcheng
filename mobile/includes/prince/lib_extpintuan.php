<?php
//今天优品 多商户系统    
// 插件开发者:PRINCE
// QQ:120029121
/**
 * 新版拼团文件
 * $Author: RINCE 120029121  $
 * $Id: extpintuan.php 17217 2016-04-20 09:29:08Z RINCE 120029121  $
 */




/**

 * 取得拼团活动信息

 * @param   int     $extpintuan_id   拼团活动id

 * @param   int     $current_num    本次购买数量（计算当前价时要加上的数量）

 * @return  array

 *                  status          状态：

 */

function extpintuan_info($extpintuan_id, $current_num = 0)

{

    /* 取得拼团活动信息 */

    $extpintuan_id = intval($extpintuan_id);

    $sql = "SELECT *, act_id AS extpintuan_id, act_desc AS extpintuan_desc, start_time AS start_date, end_time AS end_date " .

            "FROM " . $GLOBALS['yp']->table('goods_activity') .

            "WHERE act_id = '$extpintuan_id' " .

            "AND act_type = '" . GAT_EXTPINTUAN . "'";

    $extpintuan = $GLOBALS['db']->getRow($sql);



    /* 如果为空，返回空数组 */

    if (empty($extpintuan))

    {

        return array();

    }



    $ext_info = unserialize($extpintuan['ext_info']);

    $extpintuan = array_merge($extpintuan, $ext_info);



    /* 格式化时间 */

    $extpintuan['formated_start_date'] = local_date('Y-m-d H:i', $extpintuan['start_time']);

    $extpintuan['formated_end_date'] = local_date('Y-m-d H:i', $extpintuan['end_time']);



    /* 格式化保证金 */

    $extpintuan['formated_deposit'] = price_format($extpintuan['deposit'], false);





    /* 处理价格阶梯 */

    $extpintuan['org_price_ladder'] = $extpintuan['price_ladder'];

    $price_ladder = $extpintuan['price_ladder'];

	$i=0;

    if (!is_array($price_ladder) || empty($price_ladder))

    {

        $price_ladder = array(array('amount' => 0, 'minprice' => 0, 'maxprice' => 0));

    }

    else

    {

        foreach ($price_ladder as $key => $amount_price)

        {



			$i=$i+1;

        }

    }

    $extpintuan['price_ladder'] = $extpintuan['price_ladder'];

    $extpintuan['ladder_amount'] =$i ;



    /* 统计信息 */

    $stat = extpintuan_stat($extpintuan_id, $extpintuan['deposit']);

    $extpintuan = array_merge($extpintuan, $stat);



    /* 计算当前价 */

    $cur_price  = $price_ladder[0]['minprice']; // 初始化

    $cur_amount = $stat['valid_goods'] + $current_num; // 当前数量

    foreach ($price_ladder as $amount_price)

    {

        if ($cur_amount >= $amount_price['amount'])

        {

            $cur_price = $amount_price['minprice'];

        }

        else

        {

            break;

        }

    }

    $extpintuan['cur_price'] = $cur_price;

    $extpintuan['formated_cur_price'] = price_format($cur_price, false);



    /* 最终价 */

    $extpintuan['trans_price'] = $extpintuan['cur_price'];

    $extpintuan['formated_trans_price'] = $extpintuan['formated_cur_price'];

    $extpintuan['trans_amount'] = $extpintuan['valid_goods'];



    /* 状态 */

    $extpintuan['status'] = extpintuan_status($extpintuan);

    if (isset($GLOBALS['_LANG']['gbs'][$extpintuan['status']]))

    {

        $extpintuan['status_desc'] = $GLOBALS['_LANG']['gbs'][$extpintuan['status']];

    }




    return $extpintuan;

}



/*

 * 取得某拼团活动统计信息

 * @param   int     $extpintuan_id   拼团活动id

 * @param   float   $deposit        保证金

 * @return  array   统计信息

 *                  total_order     总订单数

 *                  total_goods     总商品数

 *                  valid_order     有效订单数

 *                  valid_goods     有效商品数

 */

function extpintuan_stat($extpintuan_id, $deposit)

{

    $extpintuan_id = intval($extpintuan_id);



    /* 取得拼团活动商品ID */

    $sql = "SELECT goods_id " .

           "FROM " . $GLOBALS['yp']->table('goods_activity') .

           "WHERE act_id = '$extpintuan_id' " .

           "AND act_type = '" . GAT_EXTPINTUAN . "'";

    $extpintuan_goods_id = $GLOBALS['db']->getOne($sql);



    /* 取得总订单数和总商品数 */

    $sql = "SELECT COUNT(*) AS total_order, SUM(g.goods_number) AS total_goods " .

            "FROM " . $GLOBALS['yp']->table('order_info') . " AS o, " .

                $GLOBALS['yp']->table('order_goods') . " AS g " .

            " WHERE o.order_id = g.order_id " .

            "AND o.extension_code = 'extpintuan' " .

            "AND o.extension_id = '$extpintuan_id' " .

            "AND g.goods_id = '$extpintuan_goods_id' " .

            "AND (order_status = '" . OS_CONFIRMED . "' OR order_status = '" . OS_UNCONFIRMED . "')";

    $stat = $GLOBALS['db']->getRow($sql);

    if ($stat['total_order'] == 0)

    {

        $stat['total_goods'] = 0;

    }



    /* 取得有效订单数和有效商品数 */

    $deposit = floatval($deposit);

    if ($deposit > 0 && $stat['total_order'] > 0)

    {

        $sql .= " AND (o.money_paid + o.surplus) >= '$deposit'";

        $row = $GLOBALS['db']->getRow($sql);

        $stat['valid_order'] = $row['total_order'];

        if ($stat['valid_order'] == 0)

        {

            $stat['valid_goods'] = 0;

        }

        else

        {

            $stat['valid_goods'] = $row['total_goods'];

        }

    }

    else

    {

        $stat['valid_order'] = $stat['total_order'];

        $stat['valid_goods'] = $stat['total_goods'];

    }



    return $stat;

}



/**

 * 获得拼团的状态

 *

 * @access  public

 * @param   array

 * @return  integer

 */

function extpintuan_status($extpintuan)

{

    $now = gmtime();

    if ($extpintuan['is_finished'] == 0)

    {

        /* 未处理 */

        if ($now < $extpintuan['start_time'])

        {

            $status = GBS_PRE_START;

        }

        elseif ($now > $extpintuan['end_time'])

        {

            $status = GBS_FINISHED;

        }

        else

        {

            if ($extpintuan['restrict_amount'] == 0 || $extpintuan['valid_goods'] < $extpintuan['restrict_amount'])

            {

                $status = GBS_UNDER_WAY;

            }

            else

            {

                $status = GBS_FINISHED;

            }

        }

    }

    elseif ($extpintuan['is_finished'] == GBS_SUCCEED)

    {

        /* 已处理，拼团成功 */

        $status = GBS_SUCCEED;

    }

    elseif ($extpintuan['is_finished'] == GBS_FAIL)

    {

        /* 已处理，拼团失败 */

        $status = GBS_FAIL;

    }



    return $status;

}



/**

 *

 * @access  public

 */

function update_extpintuan_info($pt_id){


	//处理拼团数据  Start

    $now = gmtime();

    $sql = "SELECT a.* " .

            "FROM " . $GLOBALS['yp']->table('extpintuan') . " AS a " .

            "WHERE status=0  " .

            "ORDER BY a.create_time asc ";

    $row = $GLOBALS['db']->getAll($sql);

	

    foreach ($row AS $key => $val)

    {

		 if($val['create_succeed']==1){//处理开团成功的拼团及订单

			 

			 if($val['available_people']==0){// 所需人数剩余0 开团成功
			        if($val['is_lucky_extpintuan']){
						$status=3;
					}else{
						$status=1;
					}

					$sql = 'UPDATE ' . $GLOBALS['yp']->table('extpintuan') . ' SET status ='.$status.' '.

							   "WHERE pt_id = '" . $val['pt_id'] . "'";

					$GLOBALS['db']->query($sql);	
					
					$sql = 'UPDATE ' . $GLOBALS['yp']->table('order_info') . ' AS o SET o.pt_status ='.$status.' '.

							   "WHERE exists (SELECT 1 FROM  " . $GLOBALS['yp']->table('extpintuan_orders') .
							                      " AS pto WHERE  o.order_id=pto.order_id and pto.pt_id= '" . $val['pt_id'] . "')";
                   
					$GLOBALS['db']->query($sql);
					
					send_extpintuan_wxm($val['pt_id'],$status);
	

			 }else{//所需人数大于零

				    $sql = "SELECT count(*) " .

						"FROM  " . $GLOBALS['yp']->table('extpintuan_orders') . " AS pto  " .

						"LEFT JOIN " . $GLOBALS['yp']->table('order_info') . " AS o ON pto.order_id    = o.order_id    " .

						"WHERE pto.pt_id=".$val['pt_id'].

						"  and o.pay_status =2 ";

					$valid_orders = $GLOBALS['db']->getOne($sql);

				  	$sql = 'UPDATE ' . $GLOBALS['yp']->table('extpintuan') . ' SET `available_people` =`need_people`-'. $valid_orders  .

							" WHERE pt_id = '" . $val['pt_id'] . "'";

					$GLOBALS['db']->query($sql);	

					if($val['need_people']<=$valid_orders){
						if($val['is_lucky_extpintuan']){
							$status=3;
						}else{
							$status=1;
						}

						$sql = 'UPDATE ' . $GLOBALS['yp']->table('extpintuan') . ' SET status ='.$status.' '.

								   "WHERE pt_id = '" . $val['pt_id'] . "'";

						$GLOBALS['db']->query($sql);	
						
						$sql = 'UPDATE ' . $GLOBALS['yp']->table('order_info') . ' AS o SET o.pt_status ='.$status.' '.
	
								   "WHERE exists (SELECT 1 FROM  " . $GLOBALS['yp']->table('extpintuan_orders') .
													  " AS pto WHERE  o.order_id=pto.order_id and pto.pt_id= '" . $val['pt_id'] . "')";
					   
						$GLOBALS['db']->query($sql);	
						
						send_extpintuan_wxm($val['pt_id'],$status);

					}else{
                        
						if($val['end_time']<$now ){

							$sql = 'UPDATE ' . $GLOBALS['yp']->table('extpintuan') . ' SET status =2 '.
	
									   "WHERE pt_id = '" . $val['pt_id'] . "' and end_time<$now ";
	
							$GLOBALS['db']->query($sql);	
							
							$sql = 'UPDATE ' . $GLOBALS['yp']->table('order_info') . ' AS o SET o.pt_status =2 '.
		
									   "WHERE exists (SELECT 1 FROM  " . $GLOBALS['yp']->table('extpintuan_orders') .
														  " AS pto WHERE  o.order_id=pto.order_id and pto.pt_id= '" . $val['pt_id'] . "')";
						   
							$GLOBALS['db']->query($sql);	
							send_extpintuan_wxm($val['pt_id'],2);

						}

					}

			 }

 

		 }else{//处理开团中的拼团及订单

			   if($val['end_time']>$now ){//未开团 未超时

				   $sql = "SELECT pto.*,o.order_status,o.shipping_status,o.pay_status " .

						"FROM  " . $GLOBALS['yp']->table('extpintuan_orders') . " AS pto  " .

						"LEFT JOIN " . $GLOBALS['yp']->table('order_info') . " AS o ON pto.order_id    = o.order_id    " .

						"WHERE pto.pt_id=".$val['pt_id']." and pto.follow_user=pto.act_user and o.pay_status =2";

				   $act_user_order = $GLOBALS['db']->getRow($sql);

				   if($act_user_order){

						$sql = 'UPDATE ' . $GLOBALS['yp']->table('extpintuan') . ' SET create_succeed =1 '.

							   "WHERE pt_id = '" . $val['pt_id'] . "'";

						$GLOBALS['db']->query($sql);

				   }

			   }else{//未开团，已超时

					$sql = 'UPDATE ' . $GLOBALS['yp']->table('extpintuan') . ' SET status =2 '.

							   "WHERE pt_id = '" . $val['pt_id'] . "'";

					$GLOBALS['db']->query($sql);	
					
					$sql = 'UPDATE ' . $GLOBALS['yp']->table('order_info') . ' AS o SET o.pt_status =2 '.

							   "WHERE exists (SELECT 1 FROM  " . $GLOBALS['yp']->table('extpintuan_orders') .
							                      " AS pto WHERE  o.order_id=pto.order_id and pto.pt_id= '" . $val['pt_id'] . "')";
                   
					$GLOBALS['db']->query($sql);
					
					send_extpintuan_wxm($val['pt_id'],2);	

			   }

		 }



    }

	//处理拼团数据  End

	

	//拼团订单数据  Start

	$sql = "SELECT pto.order_id " .

			"FROM  " . $GLOBALS['yp']->table('extpintuan') . " AS pt  " .

			"LEFT JOIN " . $GLOBALS['yp']->table('extpintuan_orders') . " AS pto ON pto.pt_id    = pt.pt_id    " .

			"LEFT JOIN " . $GLOBALS['yp']->table('order_info') . " AS o ON pto.order_id    = o.order_id    " .

			"WHERE pt.status!=0 AND o.pay_status <2 and order_status<2 ";

	$row = $GLOBALS['db']->getAll($sql);

    foreach ($row AS $key => $val){

		$sql = 'UPDATE ' . $GLOBALS['yp']->table('order_info') . ' SET order_status =2 '.

		"WHERE order_id = '" . $val['order_id'] . "'";

		$GLOBALS['db']->query($sql);

	}

	

	//拼团订单数据  End

	

	//释放抽取价格权利 Start

	$sql = "SELECT * " .

			" FROM  " . $GLOBALS['yp']->table('order_info') . 

			" WHERE extension_code='extpintuan' AND pay_status =2 and pt_price_status=0 ";

	$row = $GLOBALS['db']->getAll($sql);

    foreach ($row AS $key => $val){

		$sql = 'UPDATE ' . $GLOBALS['yp']->table('extpintuan_price') . ' SET status =1 '.

		" WHERE act_id = " . $val['extension_id'] . 

		" AND level = " . $val['pt_level'] . 

		" AND status =0 ";

		$GLOBALS['db']->query($sql);

		$sql = 'UPDATE ' . $GLOBALS['yp']->table('order_info') . ' SET pt_price_status =1 '.

		" WHERE order_id = " . $val['order_id'] ;

		$GLOBALS['db']->query($sql);

	}

	//释放抽取价格权利 End



	

}

/* 发送短信 */

function send_extpintuan_sms($pt_id,$status)
{
	
	$sql = "SELECT o.order_sn,o.mobile FROM  " . $GLOBALS['yp']->table('extpintuan_orders') . " AS pto  " .

						" LEFT JOIN " . $GLOBALS['yp']->table('order_info') . " AS o ON pto.order_id    = o.order_id    " .

						" WHERE pto.pt_id=".$pt_id.

						"  AND o.pay_status =2 ";

    $res = $GLOBALS['db']->query($sql);
	
    /*while ($row = $GLOBALS['db']->fetchRow($res))

    {   
           $order_sn=$row['order_sn'];
           $mobile=$row['mobile'];
	       include_once(ROOT_PATH.'sms/sms.php');
		   if($order_sn && $mobile && $status){
			   if($status==1){
				   $content = "{'OrderNo':'".$order_sn."'}，SMS_7496235"; //拼团成功短信
				   sendSMS($mobile,$content,'', '',$templateParam , $templateCode);//暂不支持阿里大于 QQ 120029121
			   }elseif($status==2){
				   $content = "{'OrderNo':'".$order_sn."'}，SMS_7451118";  //拼团失败短信
				   sendSMS($mobile,$content,'', '',$templateParam , $templateCode);//暂不支持阿里大于 QQ 120029121
			   }
		   }
    }*/
    
}



/* 发微信 */

function send_extpintuan_wxm($pt_id,$status){
	$sql = "SELECT pto.* FROM  " . $GLOBALS['yp']->table('extpintuan_orders') . " AS pto  " .
						" LEFT JOIN " . $GLOBALS['yp']->table('order_info') . " AS o ON pto.order_id    = o.order_id    " .
						" WHERE pto.pt_id=".$pt_id.
						"  AND o.pay_status =2 ";
    $res = $GLOBALS['db']->query($sql);
	
    while ($row = $GLOBALS['db']->fetchRow($res)){   
	       //include_once(ROOT_PATH.'mobile/wxm_extpintuan.php');
		   send_status_message($row['follow_user'],$row['order_id'],$row['order_sn'],$pt_id,$status);
    }  
}


/* 发微信 */

function send_lucky_extpintuan_wxm(){
	$sql = "SELECT po.* FROM  " . $GLOBALS['yp']->table('extpintuan_orders') . " AS po  " .
						" LEFT JOIN " . $GLOBALS['yp']->table('extpintuan') . " AS p ON p.pt_id    = po.pt_id    " .
						" WHERE p.is_lucky_extpintuan=1 and p.status=4 ".
						" AND po.notify =0 ";
    $res = $GLOBALS['db']->query($sql);
	
    while ($row = $GLOBALS['db']->fetchRow($res)){   
	       //include_once(ROOT_PATH.'wxm_extpintuan.php');
		   $notify = $GLOBALS['db'] -> getOne('SELECT `notify` FROM ' . $GLOBALS['yp']->table('extpintuan_orders'). ' WHERE order_id = '.$row['order_id']);
		   if(!$notify){
			   send_lucky_message($row['follow_user'],$row['order_id'],$row['order_sn'],$row['pt_id'],$row['lucky_order']);
			   $sql = 'UPDATE ' . $GLOBALS['yp']->table('extpintuan_orders') . ' SET notify =1 WHERE order_id = '.$row['order_id'];
			   $GLOBALS['db']->query($sql);
		   }
    }  
}



/* 取得拼团活动总数 */

function extpintuan_count()

{
    $type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;

    $now = gmtime();

	$where=" AND b.start_time <= '$now' AND b.end_time > '$now' ";
	if($type ==1){
		$where=" AND ext_act_type='$type' AND b.start_time <= '$now' AND b.end_time > '$now' ";
	}elseif($type ==2){
		$where=" AND ext_act_type='$type'  ";
	}

    $sql = "SELECT COUNT(*) " .

            "FROM " . $GLOBALS['yp']->table('goods_activity') .

            " b WHERE act_type = '" . GAT_EXTPINTUAN . "' " .
			$where.
            " ";



    return $GLOBALS['db']->getOne($sql);

}



/* 取得用户拼团活动总数 */

function user_extpintuan_count()

{

    $sql = "SELECT COUNT(*) " .

            "FROM " . $GLOBALS['yp']->table('extpintuan_orders') .

            "WHERE follow_user  = '" . $_SESSION['user_id'] . "' " ;



    return $GLOBALS['db']->getOne($sql);

}


/**

 * 取得某页的所有拼团活动

 * @param   int     $size   每页记录数

 * @param   int     $page   当前页

 * @return  array

 */

function extpintuan_list($size, $page)

{   
	
    $type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;

    $now = gmtime();

	$where=" AND b.start_time <= '$now' AND b.end_time > '$now' ";
	if($type ==1){
		$where=" AND ext_act_type='$type' AND b.start_time <= '$now' AND b.end_time > '$now' ";
	}elseif($type ==2){
		$where=" AND ext_act_type='$type'  ";
	}

	
    /* 取得拼团活动 */

    $pt_list = array();


    $sql = "SELECT b.*, IFNULL(g.goods_thumb, '') AS goods_thumb, g.*,b.act_id AS extpintuan_id, ".

                "b.start_time AS start_date, b.end_time AS end_date " .

            "FROM " . $GLOBALS['yp']->table('goods_activity') . " AS b " .

                "LEFT JOIN " . $GLOBALS['yp']->table('goods') . " AS g ON b.goods_id = g.goods_id " .

            "WHERE b.act_type = '" . GAT_EXTPINTUAN . "' " .
			$where.

            " ORDER BY b.act_id DESC";
			


    $res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);

    while ($extpintuan = $GLOBALS['db']->fetchRow($res))

    {

        $ext_info = unserialize($extpintuan['ext_info']);

        $extpintuan = array_merge($extpintuan, $ext_info);



        /* 格式化时间 */

        $extpintuan['formated_start_date']   = local_date($GLOBALS['_CFG']['time_format'], $extpintuan['start_date']);

        $extpintuan['formated_end_date']     = local_date($GLOBALS['_CFG']['time_format'], $extpintuan['end_date']);



        /* 格式化保证金 */

        $extpintuan['formated_deposit'] = price_format($extpintuan['deposit'], false);

        /* 处理价格阶梯 */

        $extpintuan['org_price_ladder'] = $extpintuan['price_ladder'];

		

        $price_ladder = $extpintuan['price_ladder'];

		$i=0;

        if (!is_array($price_ladder) || empty($price_ladder))

        {

            $price_ladder = array(array('amount' => 0, 'minprice' => 0, 'maxprice' => 0));

        }

        else

        {

            foreach ($price_ladder as $key => $amount_price)

            {

				$i=$i+1;

            }

        }

        $extpintuan['ladder_amount'] =$i ;

        $extpintuan['price_ladder'] = $extpintuan['price_ladder'];

        $extpintuan['lowest_amount'] = get_lowest_amount( $price_ladder );

        $extpintuan['min_price'] = get_min_price( $price_ladder );

        $extpintuan['max_price'] = get_max_price( $price_ladder );

        $extpintuan['single_buy'] = $extpintuan['single_buy'];

        $extpintuan['single_buy_price'] = $extpintuan['single_buy_price'];

        $extpintuan['act_id'] = $extpintuan['act_id'];



        /* 处理图片 */

        if (empty($extpintuan['goods_thumb']))

        {

            $extpintuan['goods_thumb'] = get_image_path($extpintuan['goods_id'], $extpintuan['goods_thumb'], true);

        }

        /* 处理链接 */

        $extpintuan['url'] = 'extpintuan.php?act=view&act_id='.$extpintuan['extpintuan_id'].'&u='.$_SESSION['user_id'];

        /* 加入数组 */

        $pt_list[] = $extpintuan;

    }



    return $pt_list;

}




/**

 * 取得某用户的所有拼团活动

 * @param   int     $size   每页记录数

 * @param   int     $page   当前页

 * @return  array

 */

function extpintuan_user_list($size, $page)

{

    /* 取得拼团活动 */

    $pt_list = array();

    $now = gmtime();

		

    $sql = "SELECT ga.*,g.*, IFNULL(g.goods_thumb, '') AS goods_thumb, pto.* ,pt.status,pt.need_people,pt.need_people AS this_need_people,pt.pt_id,pt.price as pt_price " .

            "FROM  " . $GLOBALS['yp']->table('extpintuan_orders') . " AS pto  " .

            "LEFT JOIN " . $GLOBALS['yp']->table('extpintuan') . " AS pt ON pto.pt_id   = pt.pt_id   " .

            	"LEFT JOIN " . $GLOBALS['yp']->table('goods_activity') . " AS ga ON pt.act_id  = ga.act_id  " .

                	"LEFT JOIN " . $GLOBALS['yp']->table('goods') . " AS g ON ga.goods_id = g.goods_id " .

            "WHERE pto.follow_user=".$_SESSION['user_id']."  ORDER BY pto.order_id DESC";

    $res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);

    while ($extpintuan = $GLOBALS['db']->fetchRow($res))

    {

        $ext_info = unserialize($extpintuan['ext_info']);

        $extpintuan = array_merge($extpintuan, $ext_info);



        /* 格式化时间 */

        $extpintuan['formated_start_date']   = local_date($GLOBALS['_CFG']['time_format'], $extpintuan['start_date']);

        $extpintuan['formated_end_date']     = local_date($GLOBALS['_CFG']['time_format'], $extpintuan['end_date']);

        $extpintuan['price'] = price_format($extpintuan['pt_price'], false);



        /* 格式化保证金 */

        $extpintuan['formated_deposit'] = price_format($extpintuan['deposit'], false);

        /* 处理价格阶梯 */

        $price_ladder = $extpintuan['price_ladder'];

		$i=0;

        if (!is_array($price_ladder) || empty($price_ladder))

        {

            $price_ladder = array(array('amount' => 0, 'minprice' => 0, 'maxprice' => 0));

        }

        else

        {

            foreach ($price_ladder as $key => $amount_price)

            {

				$i=$i+1;

            }

        }

        $extpintuan['price_ladder'] = $extpintuan['price_ladder'];

        $extpintuan['lowest_price'] = price_format(get_min_price( $price_ladder ));

        $extpintuan['lowest_amount'] = get_lowest_amount( $price_ladder );

        $extpintuan['ladder_amount'] =$i ;



        /* 处理图片 */

        if (empty($extpintuan['goods_thumb']))

        {

            $extpintuan['goods_thumb'] = get_image_path($extpintuan['goods_id'], $extpintuan['goods_thumb'], true);

        }

        /* 处理链接 */

        $extpintuan['url'] = 'extpintuan.php?act=view&act_id='.$extpintuan['extpintuan_id'].'&u='.$_SESSION['user_id'];

        /* 加入数组 */

        $pt_list[] = $extpintuan;

    }



    return $pt_list;

}





function extpintuan_detail_info($extpintuan_id)

{		

		$sql = "SELECT ga.*,IFNULL(g.goods_thumb, '') AS goods_thumb, pt.need_people AS this_need_people, pt.*,g.* " .

				"FROM  " . $GLOBALS['yp']->table('extpintuan') . " AS pt  " .

					"LEFT JOIN " . $GLOBALS['yp']->table('goods_activity') . " AS ga ON pt.act_id  = ga.act_id  " .

						"LEFT JOIN " . $GLOBALS['yp']->table('goods') . " AS g ON ga.goods_id = g.goods_id " .

				"WHERE pt.pt_id=".$extpintuan_id."  ";

		$extpintuan = $GLOBALS['db']->getRow($sql);

        $ext_info = unserialize($extpintuan['ext_info']);

        $extpintuan = array_merge($extpintuan, $ext_info);



        /* 格式化时间 */

        $extpintuan['create_time']   = local_date($GLOBALS['_CFG']['time_format'], $extpintuan['create_time']);

        //$extpintuan['price'] = price_format($extpintuan['price'], false);



        /* 处理图片 */

        if (empty($extpintuan['goods_thumb']))

        {

            $extpintuan['goods_thumb'] = get_image_path($extpintuan['goods_id'], $extpintuan['goods_thumb'], true);

        }

        /* 处理链接 */

        $extpintuan['url'] = 'extpintuan.php?act=view&act_id='.$extpintuan['act_id'].'&u='.$_SESSION['user_id'];

        /* 加入数组 */



    return $extpintuan;

}


function get_min_price( $price_ladder ){

    

   if(is_array( $price_ladder)){

       

      $aa = array();

      foreach( $price_ladder as $key => $value){

          

           $aa[] = $value['minprice'];

          

          

      }

      sort($aa);

    

      return $aa[0];

       

   }

    

}




function get_max_price( $price_ladder ){

    

   if(is_array( $price_ladder)){

       

      $aa = array();

      foreach( $price_ladder as $key => $value){

          

           $aa[] = $value['maxprice'];

          

          

      }

      sort($aa);

    

      return $aa[0];

       

   }

    

}



function get_lowest_amount( $price_ladder ){

    

   if(is_array( $price_ladder)){

       

      $aa = array();

      foreach( $price_ladder as $key => $value){

          

           $aa[] = $value['amount'];

          

          

      }

      sort($aa);

    

      return $aa[0];

       

   }

    

}



/**

 * @param   int     $act_id     活动id

 * @return  array

 */

function get_new_extpintuan($act_id,$level)

{

    $new_extpintuan = array();

    $sql = "SELECT a.* " .

            "FROM " . $GLOBALS['yp']->table('extpintuan') . " AS a " .

            //"WHERE act_id = '$act_id' and status=0 and create_succeed=1 and need_people='$level' " .
            "WHERE act_id = '$act_id' and status=0 and create_succeed=1  " .

            "ORDER BY a.create_time desc LIMIT 10";

    $res = $GLOBALS['db']->query($sql);

    while ($row = $GLOBALS['db']->fetchRow($res))

    {   

        $row['create_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['create_time']);

        $row['price'] = price_format($row['price'], false);

        $row['user_nickname'] = strlen($row['user_nickname'])>10?sub_str_for_extpt($row['user_nickname'], 10): $row['user_nickname'];

        $new_extpintuan[] = $row;

    }



    return $new_extpintuan;

}





/**

 * 返回活动详细列表

 *

 * @access  public

 *

 * @return array

 */

function get_extpintuan()

{

    $filter['act_id']  = empty($_REQUEST['act_id']) ? 0 : intval($_REQUEST['act_id']);

    $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'create_time' : trim($_REQUEST['sort_by']);

    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);



    $where = empty($filter['act_id']) ? '' : " WHERE act_id='$filter[act_id]' ";



    /* 获得记录总数以及总页数 */

    $sql = "SELECT count(*) FROM ".$GLOBALS['yp']->table('extpintuan'). $where;

    $filter['record_count'] = $GLOBALS['db']->getOne($sql);



    $filter = page_and_size($filter);



    /* 获得活动数据 */

    $sql = "SELECT * ".

            " FROM ".$GLOBALS['yp']->table('extpintuan'). $where.

            " ORDER by ".$filter['sort_by']." ".$filter['sort_order'].

            " LIMIT ". $filter['start'] .", " . $filter['page_size'];

    $row = $GLOBALS['db']->getAll($sql);



    foreach ($row AS $key => $val)

    {    

		 $row[$key]['create_time'] =  local_date('Y-m-d H:i', $val['create_time']);

		 $row[$key]['end_time'] =  local_date('Y-m-d H:i', $val['end_time']);



    }



    $arr = array('extpintuan' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);



    return $arr;

}







/**

 * 返回活动详细列表

 *

 * @access  public

 *

 * @return array

 */

function get_extpintuan_detail()

{

    $filter['pt_id']  = empty($_REQUEST['pt_id']) ? 0 : intval($_REQUEST['pt_id']);

    $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'follow_time' : trim($_REQUEST['sort_by']);

    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);



    $where = empty($filter['pt_id']) ? '' : " WHERE pt_id='$filter[pt_id]' ";



    /* 获得记录总数以及总页数 */

    $sql = "SELECT count(*) FROM ".$GLOBALS['yp']->table('extpintuan_orders'). $where;

    $filter['record_count'] = $GLOBALS['db']->getOne($sql);



    $filter = page_and_size($filter);



    /* 获得活动数据 */

    $sql = "SELECT s.* ".

            " FROM ".$GLOBALS['yp']->table('extpintuan_orders')." AS s ".

            $where.

            " ORDER by ".$filter['sort_by']." ".$filter['sort_order'].

            " LIMIT ". $filter['start'] .", " . $filter['page_size'];

    $row = $GLOBALS['db']->getAll($sql);



    foreach ($row AS $key => $val)

    {

		 $row[$key]['follow_time'] =  local_date('Y-m-d H:i', $val['follow_time']);



    }



    $arr = array('extpintuan' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);



    return $arr;

}



/**

 * 获取指定id extpintuan 的信息

 */

function get_extpintuan_info($id)

{

    global $yp, $db,$_CFG;



    $sql = "SELECT act_id, act_name AS cut_name, goods_id, product_id, goods_name, start_time, end_time, act_desc, ext_info" .

           " FROM " . $GLOBALS['yp']->table('goods_activity') .

           " WHERE act_id='$id' AND act_type = " . GAT_EXTPINTUAN;



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



//拼团

function get_extpintuan_by_ptid($pt_id)

{

	$sql = "SELECT pt.* " .

				" FROM  " . $GLOBALS['yp']->table('extpintuan') . " AS pt  " .

				" WHERE pt.pt_id=".$pt_id."  ";

    return   $GLOBALS['db']->getRow($sql);

}



//抽奖
function create_lucky_orders($act_id){
    $sql = "SELECT act_id,act_type,count(1) as total_extpintuan " .
            " FROM " . $GLOBALS['yp']->table('extpintuan') . " AS a " .
            " WHERE status=3  " .
            " GROUP BY act_id,act_type  " .
            " ORDER BY a.act_id asc ";
    $row = $GLOBALS['db']->getAll($sql);

    foreach ($row AS $key => $val){
		$sql = "SELECT count(*) FROM  " . $GLOBALS['yp']->table('extpintuan') . 
							" WHERE act_id='".$val['act_id']."' AND status=0 ";
		$not_finished = $GLOBALS['db']->getOne($sql);
		
		if($not_finished==0){
			$extpintuan_info=extpintuan_info($val['act_id']);
			if($extpintuan_info['end_time']<gmtime()){
				$sql = "SELECT count(*) FROM  " . $GLOBALS['yp']->table('order_info') . 
							" WHERE extension_code = 'extpintuan' AND extension_id='".$val['act_id']."' AND pt_status>=3 AND pay_status=2 ";
				$total_orders = $GLOBALS['db']->getOne($sql);
				
				if($extpintuan_info['lucky_limit']>=$total_orders){// 限量数比订单数还要多
					   $sql = 'UPDATE ' . $GLOBALS['yp']->table('order_info') . ' AS o SET o.pt_status =4,o.pt_lucky_order =1 '.
							   " WHERE extension_code = 'extpintuan' AND extension_id='".$val['act_id']."' AND pt_status=3 AND pay_status=2 ";
					   $GLOBALS['db']->query($sql);
				}else{
					create_lucky_orders_by_rand($val['act_id'],$extpintuan_info['lucky_limit']);
				}
				$sql = 'UPDATE ' . $GLOBALS['yp']->table('extpintuan_orders') . ' AS pto SET pto.lucky_order =1 '.
								   "WHERE exists (SELECT 1 FROM  " . $GLOBALS['yp']->table('order_info') .
													  " AS o WHERE  o.order_id=pto.order_id and extension_code = 'extpintuan' AND extension_id='".$val['act_id'].
													  "' AND o.pt_lucky_order= 1  )";
				$GLOBALS['db']->query($sql);
				update_pt_status_to_4($val['act_id'],$total_orders ,$extpintuan_info['lucky_limit']);
			}
		}	
	}
}

//
function update_pt_status_to_4($act_id,$total_orders,$lucky_limit){
			$sql = "SELECT count(*) FROM  " . $GLOBALS['yp']->table('order_info') . 
						" WHERE extension_code = 'extpintuan' AND extension_id='".$act_id."' AND pay_status=2 AND pt_lucky_order =1 ";
			$count_orders = $GLOBALS['db']->getOne($sql);

			$sql = "SELECT count(*) FROM  " . $GLOBALS['yp']->table('extpintuan_orders') . 
						" WHERE act_id='".$act_id."' AND lucky_order =1 ";
			$count_extpintuan_orders = $GLOBALS['db']->getOne($sql);

			if($lucky_limit >$total_orders ){
				if($count_orders >=$total_orders  && $count_extpintuan_orders>=$total_orders ){
					$sql = 'UPDATE ' . $GLOBALS['yp']->table('order_info') . ' AS o SET o.pt_status =4 '.
							   " WHERE extension_code = 'extpintuan' AND extension_id='".$act_id."' AND pt_status=3 ";
					$GLOBALS['db']->query($sql);
					
				    $sql = 'UPDATE ' . $GLOBALS['yp']->table('extpintuan') . '  SET status =4 '.
							" WHERE act_id='".$act_id."' AND status=3 ";
					$GLOBALS['db']->query($sql);
				}
			}else{
				if($count_orders >=$lucky_limit  && $count_extpintuan_orders>=$lucky_limit ){
					$sql = 'UPDATE ' . $GLOBALS['yp']->table('order_info') . ' AS o SET o.pt_status =4 '.
							   " WHERE extension_code = 'extpintuan' AND extension_id='".$act_id."' AND pt_status=3 ";
					$GLOBALS['db']->query($sql);
					
				    $sql = 'UPDATE ' . $GLOBALS['yp']->table('extpintuan') . '  SET status =4 '.
							" WHERE act_id='".$act_id."' AND status=3 ";
					$GLOBALS['db']->query($sql);
				}
			}
	
}
//
function create_lucky_orders_by_rand($act_id,$lucky_limit){
	$sql = "SELECT count(*) FROM  " . $GLOBALS['yp']->table('order_info') . 
				" WHERE extension_code = 'extpintuan' AND extension_id='".$act_id."' AND pay_status=2 AND pt_lucky_order =1 ";
	$total_lucky_orders = $GLOBALS['db']->getOne($sql);
	
	$need_number=$lucky_limit-$total_lucky_orders;
	if($need_number>0){
		$sql = "SELECT *  FROM  " . $GLOBALS['yp']->table('order_info') . 
					" WHERE extension_code = 'extpintuan' AND extension_id='".$act_id."' AND pt_status>=3 AND pay_status=2 AND pt_lucky_order !=1 ".
					" ORDER BY RAND() LIMIT ". $need_number ;
		$rows = $GLOBALS['db']->getAll($sql); 
		
		foreach ($rows AS $key => $val){
				 $sql = 'UPDATE ' . $GLOBALS['yp']->table('order_info') . ' AS o SET o.pt_status =4,o.pt_lucky_order =1 '.
							   " WHERE order_id = " . $val['order_id'].
							   " AND extension_code = 'extpintuan' AND extension_id='".$act_id."' AND pt_status=3 AND pay_status=2 ";	
				$GLOBALS['db']->query($sql);	
		}
	}
}




/* 取得某活动幸运订单总数 */
function extpintuan_lucky_list_count($act_id){
    $sql = "SELECT COUNT(*) " .
            "FROM " . $GLOBALS['yp']->table('extpintuan_orders') .
            "WHERE act_id='".$act_id."' AND lucky_order=1  ";
    return $GLOBALS['db']->getOne($sql);
}

/* 取得某活动幸运订单 */
function extpintuan_lucky_list($act_id,$size, $page){
    $lucky_list = array();
    $sql = "SELECT * " .
            "FROM " . $GLOBALS['yp']->table('extpintuan_orders') .
            "WHERE act_id='".$act_id."' AND lucky_order=1 ORDER BY order_id DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);

    while ($luckyorder = $GLOBALS['db']->fetchRow($res)){
        $lucky_list[] = $luckyorder;
    }
    return $lucky_list;
}




//update  ".$GLOBALS['yp']->table('order_info') . " set `pt_status`=3 ,`pt_lucky_order`=0 WHERE `pt_status`>=3;
//select  `pt_status` ,`pt_lucky_order` from ".$GLOBALS['yp']->table('order_info') . " WHERE `pt_status`>=3
//update   ". $GLOBALS['yp']->table('extpintuan_orders') . "  set `lucky_order`=0 WHERE 1

function is_wechat_browser_for_extpintuan(){
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    if (strpos($user_agent, 'MicroMessenger') === false){
      return false;
    } else {
      return true;
    }
}


function sub_str_for_extpt($str, $length = 0, $append = true)
{
    $str = trim($str);
    $strlength = strlen($str);

    if ($length == 0 || $length >= $strlength)
    {
        return $str;
    }
    elseif ($length < 0)
    {
        $length = $strlength + $length;
        if ($length < 0)
        {
            $length = $strlength;
        }
    }

    if (function_exists('mb_substr'))
    {
        $newstr = mb_substr($str, 0, $length, YP_CHARSET);
    }
    elseif (function_exists('iconv_substr'))
    {
        $newstr = iconv_substr($str, 0, $length, YP_CHARSET);
    }
    else
    {
        $newstr = substr($str, 0, $length);
    }

    if ($append && $str != $newstr)
    {
        $newstr .= '...';
    }

    return $newstr;
}

/**
 * 新版拼团文件
 * $Author: RINCE 120029121  $
 * $Id: extpintuan.php 17217 2016-04-20 09:29:08Z RINCE 120029121  $
 */




?>



