<?php

/**
 * QQ120029121 用户相关函数库
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: PRINCE $
 * $Id: lib_clips.php 17217 2017-04-01 06:29:08Z PRINCE $
 */

if (!defined('IN_PRINCE'))
{
    die('Hacking attempt');
}

/**
 *  获取指定用户的收藏商品列表
 *
 * @access  public
 * @param   int     $user_id        用户ID
 * @param   int     $num            列表最大数量
 * @param   int     $start          列表其实位置
 *
 * @return  array   $arr
 */
function get_collection_goods($user_id, $num = 10, $start = 0)
{
    $sql = 'SELECT g.goods_id, g.goods_name, g.goods_thumb, g.market_price, g.shop_price AS org_price, '.
                "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, ".
                'g.promote_price, g.promote_start_date,g.promote_end_date, c.rec_id, c.is_attention' .
            ' FROM ' . $GLOBALS['yp']->table('collect_goods') . ' AS c' .
            " LEFT JOIN " . $GLOBALS['yp']->table('goods') . " AS g ".
                "ON g.goods_id = c.goods_id ".
            " LEFT JOIN " . $GLOBALS['yp']->table('member_price') . " AS mp ".
                "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ".
            " WHERE c.user_id = '$user_id' ORDER BY c.rec_id DESC";
    $res = $GLOBALS['db'] -> selectLimit($sql, $num, $start);

    $goods_list = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        if ($row['promote_price'] > 0)
        {
            $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
        }
        else
        {
            $promote_price = 0;
        }

		/* 判断是否为正在预售的商品 */
		$pre_sale_id = is_pre_sale_goods($row['goods_id']);
		if($pre_sale_id != null)
		{
			$goods_list[$row['goods_id']]['is_pre_sale']        = 1;
			$goods_list[$row['goods_id']]['pre_sale_id']        = $pre_sale_id;
		}
		else 
		{
			$goods_list[$row['goods_id']]['is_pre_sale']        = 0;
		}
        
        $goods_list[$row['goods_id']]['rec_id']        = $row['rec_id'];
        $goods_list[$row['goods_id']]['is_attention']  = $row['is_attention'];
        $goods_list[$row['goods_id']]['goods_id']      = $row['goods_id'];
        $goods_list[$row['goods_id']]['goods_name']    = $row['goods_name'];
	$goods_list[$row['goods_id']]['short_goods_name'] = sub_str($row['goods_name'], 20) ;
        $goods_list[$row['goods_id']]['market_price']  = price_format($row['market_price']);
        $goods_list[$row['goods_id']]['shop_price']    = price_format($row['shop_price']);
        $goods_list[$row['goods_id']]['promote_price'] = ($promote_price > 0) ? price_format($promote_price) : '';
        $goods_list[$row['goods_id']]['url']           = build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']);
        $goods_list[$row['goods_id']]['thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'],true);
    }

    return $goods_list;
}

/**
 *  获取指定用户的关注店铺
 *
 * @access  public
 * @param   int     $user_id        用户ID
 * @param   int     $num            列表最大数量
 * @param   int     $start          列表其实位置
 *
 * @return  array   $arr
 */
function get_follow_shops($user_id, $num = 10, $start = 0)
{
    $sql = 'SELECT sg.id,sg.supplierid,s.supplier_name,s.tel,s.company_name ' .
            ' FROM ' . $GLOBALS['yp']->table('supplier_guanzhu') . ' AS sg' .
            " LEFT JOIN " . $GLOBALS['yp']->table('supplier') . " AS s ".
                "ON sg.supplierid = s.supplier_id ".
            " WHERE sg.userid = '$user_id' ORDER BY sg.addtime DESC";
    $res = $GLOBALS['db'] -> selectLimit($sql, $num, $start);

    $supp_list = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
    	$supp_list[$row['supplierid']]['id']        = $row['id'];
        $supp_list[$row['supplierid']]['supplierid']        = $row['supplierid'];
        $supp_list[$row['supplierid']]['supplier_name']        = $row['supplier_name'];
        $supp_list[$row['supplierid']]['tel']        = $row['tel'];
        $supp_list[$row['supplierid']]['company_name']        = $row['company_name'];
        
        $supp_list[$row['supplierid']]['url']           = build_uri('supplier', array('suppid'=>$row['supplierid']));
        $suppinfo = $GLOBALS['db'] -> query("select value,code from " . $GLOBALS['yp']->table('supplier_shop_config') ." where supplier_id = ".$row['supplierid']." AND code in('shop_name','shop_logo','qq','ww')");
        while ($r = $GLOBALS['db']->fetchRow($suppinfo)){
        	$supp_list[$row['supplierid']][$r['code']]        = $r['value'];
        }
    }
    return $supp_list;
}

/**
 *  查看此商品是否已进行过缺货登记
 *
 * @access  public
 * @param   int     $user_id        用户ID
 * @param   int     $goods_id       商品ID
 *
 * @return  int
 */
function get_booking_rec($user_id, $goods_id)
{
    $sql = 'SELECT COUNT(*) '.
           'FROM ' .$GLOBALS['yp']->table('booking_goods').
           "WHERE user_id = '$user_id' AND goods_id = '$goods_id' AND is_dispose = 0";

    return $GLOBALS['db']->getOne($sql);
}

/**
 *  获取指定用户的留言
 *
 * @access  public
 * @param   int     $user_id        用户ID
 * @param   int     $user_name      用户名
 * @param   int     $num            列表最大数量
 * @param   int     $start          列表其实位置
 * @return  array   $msg            留言及回复列表
 * @return  string  $order_id       订单ID
 */
function get_message_list($user_id, $user_name, $num, $start, $order_id = 0)
{
    /* 获取留言数据 */
    $msg = array();
    $sql = "SELECT * FROM " .$GLOBALS['yp']->table('feedback');
    if ($order_id)
    {
        $sql .= " WHERE parent_id = 0 AND order_id = '$order_id' AND user_id = '$user_id' ORDER BY msg_time DESC";
    }
    else
    {
        $sql .= " WHERE parent_id = 0 AND user_id = '$user_id' AND user_name = '" . $_SESSION['user_name'] . "' AND order_id=0 ORDER BY msg_time DESC";
    }

    $res = $GLOBALS['db']->SelectLimit($sql, $num, $start);

    while ($rows = $GLOBALS['db']->fetchRow($res))
    {
        /* 取得留言的回复 */
        //if (empty($order_id))
        //{
            $reply = array();
            $sql   = "SELECT user_name, user_email, msg_time, msg_content".
                     " FROM " .$GLOBALS['yp']->table('feedback') .
                     " WHERE parent_id = '" . $rows['msg_id'] . "'";
            $reply = $GLOBALS['db']->getRow($sql);

            if ($reply)
            {
                $msg[$rows['msg_id']]['re_user_name']   = $reply['user_name'];
                $msg[$rows['msg_id']]['re_user_email']  = $reply['user_email'];
                $msg[$rows['msg_id']]['re_msg_time']    = local_date($GLOBALS['_CFG']['time_format'], $reply['msg_time']);
                $msg[$rows['msg_id']]['re_msg_content'] = nl2br(htmlspecialchars($reply['msg_content']));
            }
        //}

        $msg[$rows['msg_id']]['msg_content'] = nl2br(htmlspecialchars($rows['msg_content']));
        $msg[$rows['msg_id']]['msg_time']    = local_date($GLOBALS['_CFG']['time_format'], $rows['msg_time']);
        $msg[$rows['msg_id']]['msg_type']    = $order_id ? $rows['user_name'] : $GLOBALS['_LANG']['type'][$rows['msg_type']];
        $msg[$rows['msg_id']]['msg_title']   = nl2br(htmlspecialchars($rows['msg_title']));
        $msg[$rows['msg_id']]['message_img'] = $rows['message_img'];
        $msg[$rows['msg_id']]['order_id'] = $rows['order_id'];
    }

    return $msg;
}

/**
 *  添加留言函数
 *
 * @access  public
 * @param   array       $message
 *
 * @return  boolen      $bool
 */
function add_message($message)
{
    $upload_size_limit = $GLOBALS['_CFG']['upload_size_limit'] == '-1' ? ini_get('upload_max_filesize') : $GLOBALS['_CFG']['upload_size_limit'];
    $status = 1 - $GLOBALS['_CFG']['message_check'];

    $last_char = strtolower($upload_size_limit{strlen($upload_size_limit)-1});

    switch ($last_char)
    {
        case 'm':
            $upload_size_limit *= 1024*1024;
            break;
        case 'k':
            $upload_size_limit *= 1024;
            break;
    }

    if ($message['upload'])
    {
        if($_FILES['message_img']['size'] / 1024 > $upload_size_limit)
        {
            $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['upload_file_limit'], $upload_size_limit));
            return false;
        }
        $img_name = upload_file($_FILES['message_img'], 'feedbackimg');

        if ($img_name === false)
        {
            return false;
        }
    }
    else
    {
        $img_name = '';
    }

    if (empty($message['msg_title']))
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['msg_title_empty']);

        return false;
    }

    $message['msg_area'] = isset($message['msg_area']) ? intval($message['msg_area']) : 0;
    $sql = "INSERT INTO " . $GLOBALS['yp']->table('feedback') .
            " (msg_id, parent_id, user_id, user_name, user_email, msg_title, msg_type, msg_status,  msg_content, msg_time, message_img, order_id, msg_area)".
            " VALUES (NULL, 0, '$message[user_id]', '$message[user_name]', '$message[user_email]', ".
            " '$message[msg_title]', '$message[msg_type]', '$status', '$message[msg_content]', '".gmtime()."', '$img_name', '$message[order_id]', '$message[msg_area]')";
    $GLOBALS['db']->query($sql);

    return true;
}

/**
 *  获取用户的tags
 *
 * @access  public
 * @param   int         $user_id        用户ID
 *
 * @return array        $arr            tags列表
 */
function get_user_tags($user_id = 0)
{
    if (empty($user_id))
    {
        $GLOBALS['error_no'] = 1;

        return false;
    }

    $tags = get_tags(0, $user_id);

    if (!empty($tags))
    {
        color_tag($tags);
    }

    return $tags;
}

/**
 *  验证性的删除某个tag
 *
 * @access  public
 * @param   int         $tag_words      tag的ID
 * @param   int         $user_id        用户的ID
 *
 * @return  boolen      bool
 */
function delete_tag($tag_words, $user_id)
{
     $sql = "DELETE FROM ".$GLOBALS['yp']->table('tag').
            " WHERE tag_words = '$tag_words' AND user_id = '$user_id'";

     return $GLOBALS['db']->query($sql);
}

/**
 *  获取某用户的缺货登记列表
 *
 * @access  public
 * @param   int     $user_id        用户ID
 * @param   int     $num            列表最大数量
 * @param   int     $start          列表其实位置
 *
 * @return  array   $booking
 */
function get_booking_list($user_id, $num, $start)
{
    $booking = array();
    $sql = "SELECT bg.rec_id, bg.goods_id, bg.goods_number, bg.booking_time, bg.dispose_note, g.goods_name,g.goods_thumb,g.supplier_id ".
           "FROM " .$GLOBALS['yp']->table('booking_goods')." AS bg , " .$GLOBALS['yp']->table('goods')." AS g". " WHERE bg.goods_id = g.goods_id AND bg.user_id = '$user_id' ORDER BY bg.booking_time DESC";
    $res = $GLOBALS['db']->SelectLimit($sql, $num, $start);

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        if (empty($row['dispose_note']))
        {
            $row['dispose_note'] = 'N/A';
        }
        $booking[] = array('rec_id'       => $row['rec_id'],
                           'goods_name'   => $row['goods_name'],
                           'goods_number' => $row['goods_number'],//商品图片   jx
						   'goods_thumb'  => $row['goods_thumb'],
						   'supplier_id'  => $row['supplier_id'],//  商家id     jx
                           'booking_time' => local_date($GLOBALS['_CFG']['date_format'], $row['booking_time']),
                           'dispose_note' => $row['dispose_note'],
                           'url'          => build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']),
						   'goods_id'=>$row['goods_id']);
    }

    return $booking;
}

/**
 *  获取某用户的缺货登记列表
 *
 * @access  public
 * @param   int     $goods_id    商品ID
 *
 * @return  array   $info
 */
function get_goodsinfo($goods_id)
{
    $info = array();
    $sql  = "SELECT goods_name FROM " .$GLOBALS['yp']->table('goods'). " WHERE goods_id = '$goods_id'";

    $info['goods_name']   = $GLOBALS['db']->getOne($sql);
    $info['goods_number'] = 1;
    $info['id']           = $goods_id;

    if (!empty($_SESSION['user_id']))
    {
        $row = array();
        $sql = "SELECT ua.consignee, ua.email, ua.tel, ua.mobile ".
               "FROM ".$GLOBALS['yp']->table('user_address')." AS ua, ".$GLOBALS['yp']->table('users')." AS u".
               " WHERE u.address_id = ua.address_id AND u.user_id = '$_SESSION[user_id]'";
        $row = $GLOBALS['db']->getRow($sql) ;
        $info['consignee'] = empty($row['consignee']) ? '' : $row['consignee'];
        $info['email']     = empty($row['email'])     ? '' : $row['email'];
        $info['tel']       = empty($row['mobile'])    ? (empty($row['tel']) ? '' : $row['tel']) : $row['mobile'];
    }

    return $info;
}

/**
 *  验证删除某个收藏商品
 *
 * @access  public
 * @param   int         $booking_id     缺货登记的ID
 * @param   int         $user_id        会员的ID
 * @return  boolen      $bool
 */
function delete_booking($booking_id, $user_id)
{
    $sql = 'DELETE FROM ' .$GLOBALS['yp']->table('booking_goods').
           " WHERE rec_id = '$booking_id' AND user_id = '$user_id'";

    return $GLOBALS['db']->query($sql);
}

/**
 * 添加缺货登记记录到数据表
 * @access  public
 * @param   array $booking
 *
 * @return void
 */
function add_booking($booking)
{
    $sql = "INSERT INTO " .$GLOBALS['yp']->table('booking_goods').
            " VALUES ('', '$_SESSION[user_id]', '$booking[email]', '$booking[linkman]', ".
                "'$booking[tel]', '$booking[goods_id]', '$booking[desc]', ".
                "'$booking[goods_amount]', '".gmtime()."', 0, '', 0, '')";
    $GLOBALS['db']->query($sql) or die ($GLOBALS['db']->errorMsg());

    return $GLOBALS['db']->insert_id();
}

/**
 * 插入会员账目明细
 *
 * @access  public
 * @param   array     $surplus  会员余额信息
 * @param   string    $amount   余额
 *
 * @return  int
 */
function insert_user_account($surplus, $amount)
{
    $sql = 'INSERT INTO ' .$GLOBALS['yp']->table('user_account').
           ' (user_id, admin_user, amount, add_time, paid_time, admin_note, user_note, process_type, payment, is_paid,real_name,account_type,account,mobile_phone)'.
            " VALUES ('$surplus[user_id]', '', '$amount', '".gmtime()."', 0, '', '$surplus[user_note]', '$surplus[process_type]', '$surplus[payment]', 0, '$surplus[real_name]', '$surplus[account_type]', '$surplus[account]', '$surplus[mobile_phone]')";  // mod by prince 20161123
    $GLOBALS['db']->query($sql);

    return $GLOBALS['db']->insert_id();
}

/**
 * 更新会员账目明细
 *
 * @access  public
 * @param   array     $surplus  会员余额信息
 *
 * @return  int
 */
function update_user_account($surplus)
{
    $sql = 'UPDATE ' .$GLOBALS['yp']->table('user_account'). ' SET '.
           "amount     = '$surplus[amount]', ".
           "user_note  = '$surplus[user_note]', ".
           "payment    = '$surplus[payment]' ".
           "WHERE id   = '$surplus[rec_id]'";
    $GLOBALS['db']->query($sql);

    return $surplus['rec_id'];
}

/**
 * 将支付LOG插入数据表
 *
 * @access  public
 * @param   integer     $id         订单编号
 * @param   float       $amount     订单金额
 * @param   integer     $type       支付类型
 * @param   integer     $is_paid    是否已支付
 *
 * @return  int
 */
function insert_pay_log($id, $amount, $type = PAY_SURPLUS, $is_paid = 0)
{
    $sql = 'INSERT INTO ' .$GLOBALS['yp']->table('pay_log')." (order_id, order_amount, order_type, is_paid)".
            " VALUES  ('$id', '$amount', '$type', '$is_paid')";
    $GLOBALS['db']->query($sql);

     return $GLOBALS['db']->insert_id();
}

/**
 * 取得上次未支付的pay_lig_id
 *
 * @access  public
 * @param   array     $surplus_id  余额记录的ID
 * @param   array     $pay_type    支付的类型：预付款/订单支付
 *
 * @return  int
 */
function get_paylog_id($surplus_id, $pay_type = PAY_SURPLUS)
{
    $sql = 'SELECT log_id FROM' .$GLOBALS['yp']->table('pay_log').
           " WHERE order_id = '$surplus_id' AND order_type = '$pay_type' AND is_paid = 0";

    return $GLOBALS['db']->getOne($sql);
}

/**
 * 根据ID获取当前余额操作信息
 *
 * @access  public
 * @param   int     $surplus_id  会员余额的ID
 *
 * @return  int
 */
function get_surplus_info($surplus_id)
{
    $sql = 'SELECT * FROM ' .$GLOBALS['yp']->table('user_account').
           " WHERE id = '$surplus_id'";

    return $GLOBALS['db']->getRow($sql);
}

/**
 * 取得已安装的支付方式(其中不包括线下支付的)
 * @param   bool    $include_balance    是否包含余额支付（冲值时不应包括）
 * @return  array   已安装的配送方式列表
 */
function get_online_payment_list($include_balance = true)
{
    $sql = 'SELECT pay_id, pay_code, pay_name, pay_fee, pay_desc ' .
            'FROM ' . $GLOBALS['yp']->table('payment') .
            " WHERE enabled = 1 AND is_cod <> 1";
    if (!$include_balance)
    {
        $sql .= " AND pay_code <> 'balance' ";
    }

    $modules = $GLOBALS['db']->getAll($sql);

    include_once(ROOT_PATH.'includes/lib_compositor.php');

    return $modules;
}

/**
 * 查询会员余额的操作记录
 *
 * @access  public
 * @param   int     $user_id    会员ID
 * @param   int     $num        每页显示数量
 * @param   int     $start      开始显示的条数
 * @return  array
 */
function get_account_log($user_id, $num, $start)
{
    $account_log = array();
    $sql = 'SELECT * FROM ' .$GLOBALS['yp']->table('user_account').
           " WHERE user_id = '$user_id'" .
           " AND process_type " . db_create_in(array(SURPLUS_SAVE, SURPLUS_RETURN)) .
           " ORDER BY add_time DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $num, $start);

    if ($res)
    {
        while ($rows = $GLOBALS['db']->fetchRow($res))
        {
            $rows['add_time']         = local_date($GLOBALS['_CFG']['date_format'], $rows['add_time']);
            $rows['admin_note']       = nl2br(htmlspecialchars($rows['admin_note']));
            $rows['short_admin_note'] = ($rows['admin_note'] > '') ? sub_str($rows['admin_note'], 30) : 'N/A';
            $rows['user_note']        = nl2br(htmlspecialchars($rows['user_note']));
            $rows['short_user_note']  = ($rows['user_note'] > '') ? sub_str($rows['user_note'], 30) : 'N/A';
            $rows['pay_status']       = ($rows['is_paid'] == 0) ? $GLOBALS['_LANG']['un_confirm'] :(($rows['is_paid'] == 2) ? '已取消' :$GLOBALS['_LANG']['is_confirm']);//  20161126
            $rows['amount']           = price_format(abs($rows['amount']), false);
            $rows['real_name']       = $rows['real_name'];//qq 120 029 121 20161123
            $rows['account_type']       = $rows['account_type'];//qq 120 029 121 20161123
            $rows['account']       = $rows['account'];//qq 120 029 121 20161123
            $rows['mobile_phone']       = $rows['mobile_phone'];//qq 120 029 121 20161123

            /* 会员的操作类型： 冲值，提现 */
            if ($rows['process_type'] == 0)
            {
                $rows['type'] = $GLOBALS['_LANG']['surplus_type_0'];
            }
            else
            {
                $rows['type'] = $GLOBALS['_LANG']['surplus_type_1'];
            }

            /* 支付方式的ID */
            $sql = 'SELECT pay_id FROM ' .$GLOBALS['yp']->table('payment').
                   " WHERE pay_name = '$rows[payment]' AND enabled = 1";
            $pid = $GLOBALS['db']->getOne($sql);

            /* 如果是预付款而且还没有付款, 允许付款 */
            if (($rows['is_paid'] == 0) && ($rows['process_type'] == 0))
            {
                $rows['handle'] = '<a href="user.php?act=pay&id='.$rows['id'].'&pid='.$pid.'">'.$GLOBALS['_LANG']['pay'].'</a>';
				$rows['pay_id'] = $pid;
            }

            $account_log[] = $rows;
        }

        return $account_log;
    }
    else
    {
         return false;
    }
}

/**
 *  删除未确认的会员帐目信息
 *
 * @access  public
 * @param   int         $rec_id     会员余额记录的ID
 * @param   int         $user_id    会员的ID
 * @return  boolen
 */
function del_user_account($rec_id, $user_id)
{

    $process_type = $GLOBALS['db']->getOne("SELECT  process_type FROM " .$GLOBALS['yp']->table('user_account'). " WHERE id = '$rec_id' AND user_id = '$user_id'");
	
	if($process_type){
	
    $return_amount = $GLOBALS['db']->getOne("SELECT amount FROM " .$GLOBALS['yp']->table('user_account'). " WHERE id = '$rec_id' AND user_id = '$user_id'");
	
	$r_amount   = str_replace('-', '', $return_amount);
	 
	 $info = date('Y-m-d H:i:s')."取消余额提现，解除资金冻结".$r_amount."元";
	 
	 log_account_change($_SESSION['user_id'], $r_amount, $return_amount, 0, 0, $info);//还原用户金额
	 
    }
	
    $sql = 'DELETE FROM ' .$GLOBALS['yp']->table('user_account').
           " WHERE is_paid = 0 AND id = '$rec_id' AND user_id = '$user_id'";

    return $GLOBALS['db']->query($sql);
}

/**
 * 统计会员账户
 * @access  public
 * @param   int     $user_id        会员ID
 * @return  int
 */
function get_user_surplus($user_id)
{
    $sql = "SELECT SUM(user_money) as user_money ,SUM(frozen_money) as frozen_money,SUM(rank_points) as rank_points,SUM(pay_points) as pay_points FROM " .$GLOBALS['yp']->table('account_log').
           " WHERE user_id = '$user_id'";

    return $GLOBALS['db']->getRow($sql);
}

/**
 * 获取用户中心默认页面所需的数据
 *
 * @access  public
 * @param   int         $user_id            用户ID
 *
 * @return  array       $info               默认页面所需资料数组
 */
function get_user_default($user_id)
{
    $user_bonus = get_user_bonus();
/*代码修改2014-12-23 by demo.coolhong.com 今 天 优 品 多 商 户 系 统 q q 1 2 0 0 2 9 1 2 1  _star */
    $sql = "SELECT * FROM " .$GLOBALS['yp']->table('users'). " WHERE user_id = '$user_id'";
/*代码修改2014-12-23 by demo.coolhong.com 今 天 优 品 多 商 户 系 统 q q 1 2 0 0 2 9 1 2 1  _end */
    $row = $GLOBALS['db']->getRow($sql);
    /* 代码增加_start By demo.coolhong.com 今天优品多商户系统 qq 120029121 */
    $_SESSION['user_name'] =$row['user_name'];
    /* 代码增加_end By demo.coolhong.com 今天优品多商户系统 qq 120029121 */
    $info = array();
    $info['username']  = stripslashes($_SESSION['user_name']);
    $info['shop_name'] = $GLOBALS['_CFG']['shop_name'];
    $info['integral']  = $row['pay_points'] . $GLOBALS['_CFG']['integral_name'];
    /* 增加是否开启会员邮件验证开关 */
    $info['is_validate'] = ($GLOBALS['_CFG']['member_email_validate'] && !$row['is_validated'])?0:1;
    $info['credit_line'] = $row['credit_line'];
    $info['formated_credit_line'] = price_format($info['credit_line'], false);
	/*代码增加2014-12-23 by demo.coolhong.com 今 天 优 品 多 商 户 系 统 q q 1 2 0 0 2 9 1 2 1  _star */
	$info['mobile_phone'] = $row['mobile_phone'];
	$info['email']	= $row['email'];
	$info['status'] = $row['status'];
	$info['is_validated'] = $row['is_validated'];
	$info['validated'] = $row['validated'];
	$info['is_surplus_open'] = $row['is_surplus_open'];
	$info['status'] = $row['status'];
    /*代码增加2014-12-23 by demo.coolhong.com 今 天 优 品 多 商 户 系 统 q q 1 2 0 0 2 9 1 2 1  _end */
    //如果$_SESSION中时间无效说明用户是第一次登录。取当前登录时间。
    $last_time = !isset($_SESSION['last_time']) ? $row['last_login'] : $_SESSION['last_time'];

    if ($last_time == 0)
    {
        $_SESSION['last_time'] = $last_time = gmtime();
    }

    $info['last_time'] = local_date($GLOBALS['_CFG']['time_format'], $last_time);
    $info['surplus']   = price_format($row['user_money'], false);
    $info['bonus']     = sprintf($GLOBALS['_LANG']['user_bonus_info'], $user_bonus['bonus_count'], price_format($user_bonus['bonus_value'], false));

    $sql = "SELECT COUNT(*) FROM " .$GLOBALS['yp']->table('order_info').
            " WHERE user_id = '" .$user_id. "' AND add_time > '" .local_strtotime('-1 months'). "'";
    $info['order_count'] = $GLOBALS['db']->getOne($sql);

    include_once(ROOT_PATH . 'includes/lib_order.php');
    $sql = "SELECT order_id, order_sn ".
            " FROM " .$GLOBALS['yp']->table('order_info').
            " WHERE user_id = '" .$user_id. "' AND shipping_time > '" .$last_time. "'". order_query_sql('shipped');
    $info['shipped_order'] = $GLOBALS['db']->getAll($sql);

    return $info;
}

/**
 * 添加商品标签
 *
 * @access  public
 * @param   integer     $id
 * @param   string      $tag
 * @return  void
 */
function add_tag($id, $tag)
{
    if (empty($tag))
    {
        return;
    }

    $arr = explode(',', $tag);

    foreach ($arr AS $val)
    {
        /* 检查是否重复 */
        $sql = "SELECT COUNT(*) FROM ". $GLOBALS['yp']->table("tag").
                " WHERE user_id = '".$_SESSION['user_id']."' AND goods_id = '$id' AND tag_words = '$val'";

        if ($GLOBALS['db']->getOne($sql) == 0)
        {
            $sql = "INSERT INTO ".$GLOBALS['yp']->table("tag")." (user_id, goods_id, tag_words) ".
                    "VALUES ('".$_SESSION['user_id']."', '$id', '$val')";
            $GLOBALS['db']->query($sql);
        }
    }
}

/**
 * 标签着色
 *
 * @access   public
 * @param    array
 * @author   Xuan Yan
 *
 * @return   none
 */
function color_tag(&$tags)
{
    $tagmark = array(
        array('color'=>'#666666','size'=>'0.8em','ifbold'=>1),
        array('color'=>'#333333','size'=>'0.9em','ifbold'=>0),
        array('color'=>'#006699','size'=>'1.0em','ifbold'=>1),
        array('color'=>'#CC9900','size'=>'1.1em','ifbold'=>0),
        array('color'=>'#666633','size'=>'1.2em','ifbold'=>1),
        array('color'=>'#993300','size'=>'1.3em','ifbold'=>0),
        array('color'=>'#669933','size'=>'1.4em','ifbold'=>1),
        array('color'=>'#3366FF','size'=>'1.5em','ifbold'=>0),
        array('color'=>'#197B30','size'=>'1.6em','ifbold'=>1),
    );

    $maxlevel = count($tagmark);
    $tcount = $scount = array();

    foreach($tags AS $val)
    {
        $tcount[] = $val['tag_count']; // 获得tag个数数组
    }
    $tcount = array_unique($tcount); // 去除相同个数的tag

    sort($tcount); // 从小到大排序

    $tempcount = count($tcount); // 真正的tag级数
    $per = $maxlevel >= $tempcount ? 1 : $maxlevel / ($tempcount - 1);

    foreach ($tcount AS $key => $val)
    {
        $lvl = floor($per * $key);
        $scount[$val] = $lvl; // 计算不同个数的tag相对应的着色数组key
    }

    $rewrite = intval($GLOBALS['_CFG']['rewrite']) > 0;

    /* 遍历所有标签，根据引用次数设定字体大小 */
    foreach ($tags AS $key => $val)
    {
        $lvl = $scount[$val['tag_count']]; // 着色数组key

        $tags[$key]['color'] = $tagmark[$lvl]['color'];
        $tags[$key]['size']  = $tagmark[$lvl]['size'];
        $tags[$key]['bold']  = $tagmark[$lvl]['ifbold'];
        if ($rewrite)
        {
            if (strtolower(YP_CHARSET) !== 'utf-8')
            {
                $tags[$key]['url'] = 'tag-' . urlencode(urlencode($val['tag_words'])) . '.html';
            }
            else
            {
                $tags[$key]['url'] = 'tag-' . urlencode($val['tag_words']) . '.html';
            }
        }
        else
        {
            $tags[$key]['url'] = 'search.php?keywords=' . urlencode($val['tag_words']);
        }
    }
    shuffle($tags);
}

/**
 * 取得用户等级信息
 * @access   public
 * @author   Xuan Yan
 *
 * @return array
 */
function get_rank_info()
{
    global $db,$yp;

    if (!empty($_SESSION['user_rank']))
    {
        $sql = "SELECT rank_name, special_rank FROM " . $yp->table('user_rank') . " WHERE rank_id = '$_SESSION[user_rank]'";
        $row = $db->getRow($sql);
        if (empty($row))
        {
            return array();
        }
        $rank_name = $row['rank_name'];
        if ($row['special_rank'])
        {
            return array('rank_name'=>$rank_name);
        }
        else
        {
            $user_rank = $db->getOne("SELECT rank_points FROM " . $yp->table('users') . " WHERE user_id = '$_SESSION[user_id]'");
            $sql = "SELECT rank_name,min_points FROM " . $yp->table('user_rank') . " WHERE min_points > '$user_rank' ORDER BY min_points ASC LIMIT 1";
            $rt  = $db->getRow($sql);
            $next_rank_name = $rt['rank_name'];
            $next_rank = $rt['min_points'] - $user_rank;
            return array('rank_name'=>$rank_name,'next_rank_name'=>$next_rank_name,'next_rank'=>$next_rank);
        }
    }
    else
    {
        return array();
    }
}

/**
 *  获取用户参与活动信息
 *
 * @access  public
 * @param   int     $user_id        用户id
 *
 * @return  array
 */
function get_user_prompt ($user_id)
{
    $prompt = array();
    $now = gmtime();
    /* 夺宝奇兵 */
    $sql = "SELECT act_id, goods_name, end_time " .
            "FROM " . $GLOBALS['yp']->table('goods_activity') .
            " WHERE act_type = '" . GAT_SNATCH . "'" .
            " AND (is_finished = 1 OR (is_finished = 0 AND end_time <= '$now'))";
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $act_id = $row['act_id'];
        $result = get_snatch_result($act_id);
        if (isset($result['order_count']) && $result['order_count'] == 0 && $result['user_id'] == $user_id)
        {
            $prompt[] = array(
                   'text'=>sprintf($GLOBALS['_LANG']['your_snatch'],$row['goods_name'], $row['act_id']),
                   'add_time'=> $row['end_time']
            );
        }
        if (isset($auction['last_bid']) && $auction['last_bid']['bid_user'] == $user_id && $auction['order_count'] == 0)
        {
            $prompt[] = array(
                'text' => sprintf($GLOBALS['_LANG']['your_auction'], $row['goods_name'], $row['act_id']),
                'add_time' => $row['end_time']
            );
        }
    }


    /* 竞拍 */

    $sql = "SELECT act_id, goods_name, end_time " .
            "FROM " . $GLOBALS['yp']->table('goods_activity') .
            " WHERE act_type = '" . GAT_AUCTION . "'" .
            " AND (is_finished = 1 OR (is_finished = 0 AND end_time <= '$now'))";
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $act_id = $row['act_id'];
        $auction = auction_info($act_id);
        if (isset($auction['last_bid']) && $auction['last_bid']['bid_user'] == $user_id && $auction['order_count'] == 0)
        {
            $prompt[] = array(
                'text' => sprintf($GLOBALS['_LANG']['your_auction'], $row['goods_name'], $row['act_id']),
                'add_time' => $row['end_time']
            );
        }
    }

    /* 排序 */
    $cmp = create_function('$a, $b', 'if($a["add_time"] == $b["add_time"]){return 0;};return $a["add_time"] < $b["add_time"] ? 1 : -1;');
    usort($prompt, $cmp);

    /* 格式化时间 */
    foreach ($prompt as $key => $val)
    {
        $prompt[$key]['formated_time'] = local_date($GLOBALS['_CFG']['time_format'], $val['add_time']);
    }

    return $prompt;
}

/**
 *  获取用户评论
 *
 * @access  public
 * @param   int     $user_id        用户id
 * @param   int     $page_size      列表最大数量
 * @param   int     $start          列表起始页
 * @return  array
 */
function get_comment_list($user_id, $page_size, $start)
{
    $sql = "SELECT c.*, g.goods_name AS cmt_name, r.content AS reply_content, r.add_time AS reply_time ".
           " FROM " . $GLOBALS['yp']->table('comment') . " AS c ".
           " LEFT JOIN " . $GLOBALS['yp']->table('comment') . " AS r ".
           " ON r.parent_id = c.comment_id AND r.parent_id > 0 ".
           " LEFT JOIN " . $GLOBALS['yp']->table('goods') . " AS g ".
           " ON c.comment_type=0 AND c.id_value = g.goods_id ".
           " WHERE c.user_id='$user_id'";
    $res = $GLOBALS['db']->SelectLimit($sql, $page_size, $start);

    $comments = array();
    $to_article = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['formated_add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
        if ($row['reply_time'])
        {
            $row['formated_reply_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['reply_time']);
        }
        if ($row['comment_type'] == 1)
        {
            $to_article[] = $row["id_value"];
        }
        $comments[] = $row;
    }

    if ($to_article)
    {
        $sql = "SELECT article_id , title FROM " . $GLOBALS['yp']->table('article') . " WHERE " . db_create_in($to_article, 'article_id');
        $arr = $GLOBALS['db']->getAll($sql);
        $to_cmt_name = array();
        foreach ($arr as $row)
        {
            $to_cmt_name[$row['article_id']] = $row['title'];
        }

        foreach ($comments as $key=>$row)
        {
            if ($row['comment_type'] == 1)
            {
                $comments[$key]['cmt_name'] = isset($to_cmt_name[$row['id_value']]) ? $to_cmt_name[$row['id_value']] : '';
            }
        }
    }

    return $comments;
}
/*
 *
 * 获取当前用户购物车里面的商品
 *  jx   2014/12/12
 *
 */
function get_user_gouwuche($user_id)
{
	$sql = "SELECT c.goods_price,c.goods_id,g.* FROM ".$GLOBALS['yp']->table('goods')."as g,".$GLOBALS['yp']->table('cart')."as c WHERE c.user_id='$user_id' AND g.goods_id=c.goods_id";
	$res =  $GLOBALS['db']->getAll($sql);
	return $res;
}
/*
 *
 *
 *获取当前积分商城的数据
 *jx   2014/12/12
 *
 */
function get_user_jifen()
{
	$sql = "SELECT e.*,g.* FROM".$GLOBALS['yp']->table('goods')."as g,".$GLOBALS['yp']->table('exchange_goods')."as e WHERE g.goods_id=e.goods_id AND e.is_exchange='1'";
	$res = $GLOBALS['db']->getAll($sql);
	return $res;
}
/*
 *
 *获取当前用户收藏的商品
 *jx  2014/12/15
 *
 */
function get_user_collection($user_id)
{
	$sql = "SELECT c.user_id,c.goods_id,g.* FROM ".$GLOBALS['yp']->table('goods')."as g,".$GLOBALS['yp']->table('collect_goods')." as c WHERE c.user_id = '$user_id' AND g.goods_id = c.goods_id";
	$res = $GLOBALS['db']->getAll($sql);
	foreach($res as $key=>$value)
	{
		if($value['is_promote'] == 1 )
		{
			$res[$key]['shop_price'] = $res[$key]['promote_price'];
		}
	}
	return $res;
}

/*
 *
 *获取当前用户关注的商铺
 *jx  2014/12/15
 *
 */
function get_user_guanzhu($user_id)
{
	$sql = "SELECT * FROM ".$GLOBALS['yp']->table('supplier_guanzhu')."as g,".$GLOBALS['yp']->table('supplier_street')." as s WHERE g.userid='$user_id' AND s.supplier_id=g.supplierid";
	$res = $GLOBALS['db']->getAll($sql);
	return $res;		
}
/*
 *
 *获取当前用户购买过得商品
 *jx 2014/12/15
 *
 */
function get_user_mai($user_id)
{
	$sql = "SELECT g.*,i.*,o.* FROM ".$GLOBALS['yp']->table('goods')."as g,".$GLOBALS['yp']->table('order_info')."as i,".$GLOBALS['yp']->table('order_goods')."as o WHERE i.user_id ='$user_id' AND i.shipping_status=2 AND i.order_id=o.order_id AND o.goods_id = g.goods_id";
    /* 代码增加 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 Start */
    $sql .= ' GROUP BY g.goods_id';
    /* 代码增加 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 End */
	$res = $GLOBALS['db']->getAll($sql);
	foreach($res as $key=>$value)
	{
		$res[$key]['url'] = build_uri('goods', array('gid'=>$value['goods_id']), $value['goods_name']);
	}
	return $res;
}
/*
 *
 *获取当前用户的交易记录
 *jx	2014/12/16
 *
 */
function get_user_reminding($user_id)
{
	//查找代付款的详情
	$sql = "SELECT * FROM ".$GLOBALS['yp']->table('order_info')."WHERE user_id = '$user_id' AND pay_status=0 order by order_sn desc limit 4";
	$res = $GLOBALS['db']->getAll($sql);
	foreach($res as $key=>$value)
	{
		$rea[$key] = $value['order_id'];
		$sql = "SELECT g.goods_thumb,o.* FROM ".$GLOBALS['yp']->table('goods')."as g,".$GLOBALS['yp']->table('order_goods')."as o WHERE o.order_id='$rea[$key]' AND o.goods_id = g.goods_id ";
		$resu[$key] = $GLOBALS['db']->getRow($sql);
		$sqls = "SELECT COUNT(*) FROM ".$GLOBALS['yp']->table('goods')."as g,".$GLOBALS['yp']->table('order_goods')."as o WHERE o.order_id='$rea[$key]' AND o.goods_id = g.goods_id ";
		$resu[$key]['shu'] = $GLOBALS['db']->getOne($sqls);
		
		if ($value['order_status'] == OS_UNCONFIRMED)
        {
            $value['handler'] = "<a class='none' href='user.php?act=cancel_order&order_id=".$value['order_id']."'>".$GLOBALS['_LANG']['cancel']."</a><a class='cancel-order' href='havascript:;'>".$GLOBALS['_LANG']['cancel']."</a>";
        }
        else if ($value['order_status'] == OS_SPLITED)
        {
            /* 对配送状态的处理 */
            if ($value['shipping_status'] == SS_SHIPPED)
            {
                @$value['handler'] = $GLOBALS['_LANG']['received'];
            }
            elseif ($value['shipping_status'] == SS_RECEIVED)
            {
                @$value['handler'] = '<span style="color:#E31939">'.$GLOBALS['_LANG']['ss_received'] .'</span>';
            }
            else
            {
                if ($value['pay_status'] == PS_UNPAYED)
                {
                    @$value['handler'] = "<a href='user.php?act=order_detail&order_id=".$value['order_id']."'>".$GLOBALS['_LANG']['pay_money']."</a>";
                }
                else
                {
                    @$value['handler'] = "<a href='user.php?act=order_detail&order_id=".$value['order_id']."'>".$GLOBALS['_LANG']['view_order']."</a>";
                }

            }
        }
        else
        {
            $value['handler'] = '<span style="color:#E31939">'.$GLOBALS['_LANG']['os'][$value['order_status']] .'</span>';
        }
        $resu[$key]['handler'] = $value['handler'];
	}
	return $resu;
	//$sql = "SELECT g.*,i.*,o.* FROM ".$GLOBALS['yp']->table('goods')."as g,".$GLOBALS['yp']->table('order_info')."as i,".$GLOBALS['yp']->table('order_goods')."as o WHERE i.user_id ='$user_id' AND i.shipping_status=0 AND i.order_id=o.order_id AND o.goods_id = g.goods_id limit 4";
	//$res = $GLOBALS['db']->getAll($sql);
	
}
function get_user_shu($user_id)
{
	//查询待付款的
	$sql = "SELECT COUNT(*) FROM ".$GLOBALS['yp']->table('order_info')."WHERE user_id ='$user_id' AND order_status=0";
	$res['daif'] = $GLOBALS['db']->getOne($sql);
	//查询待收货的数量
	$sql = "SELECT COUNT(*) FROM ".$GLOBALS['yp']->table('order_info')."WHERE user_id='$user_id' AND pay_status=0 AND order_status=1";
	$res['dais'] = $GLOBALS['db']->getOne($sql);
	//查询待评价的数量
	$sql = "SELECT count(*) FROM ".$GLOBALS['yp']->table('goods')."as g,".$GLOBALS['yp']->table('order_goods')."as o,".$GLOBALS['yp']->table('order_info')."as i WHERE i.user_id = '$user_id' AND  i.order_id=o.order_id AND o.goods_id=g.goods_id";
	$res['quan'] = $GLOBALS['db']->getOne($sql);
	return $res;
}
/*$sql = "SELECT g.goods_thumb,o.*,i.* FROM ".$GLOBALS['yp']->table('goods')."as g,".$GLOBALS('ecs')->table('order_info')."as i,".$GLOBALS('ecs')->table('order_goods')."as  o  WHERE i.user_id = '$user_id' AND i.order_id=o.order_id  AND	i.shipping_status='2' AND o.goods_id = g.goods_id";
	*/
?>