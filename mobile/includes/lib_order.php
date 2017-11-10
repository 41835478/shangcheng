<?php

/**
 * QQ120029121 购物流程函数库
 * ============================================================================
 * 演示地址: http://demo.coolhong.com  开发QQ:120029121    309485552
 * ============================================================================
 * $Author: prince $
 * $Id: lib_order.php 17217 2017-04-01 06:29:08Z prince $
 */

if (!defined('IN_PRINCE'))
{
    die('Hacking attempt');
}

/**
 * 处理序列化的支付、配送的配置参数
 * 返回一个以name为索引的数组
 *
 * @access  public
 * @param   string       $cfg
 * @return  void
 */
function unserialize_config($cfg)
{
    if (is_string($cfg) && ($arr = unserialize($cfg)) !== false)
    {
        $config = array();

        foreach ($arr AS $key => $val)
        {
            $config[$val['name']] = $val['value'];
        }

        return $config;
    }
    else
    {
        return false;
    }
}
/**
 * 取得已安装的配送方式
 * @return  array   已安装的配送方式
 */
function shipping_list()
{
    $sql = 'SELECT shipping_id, shipping_name ' .
            'FROM ' . $GLOBALS['yp']->table('shipping') .
            ' WHERE enabled = 1';

    return $GLOBALS['db']->getAll($sql);
}

/**
 * 取得配送方式信息
 * @param   int     $shipping_id    配送方式id
 * @return  array   配送方式信息
 */
function shipping_info($shipping_id)
{
    $sql = 'SELECT * FROM ' . $GLOBALS['yp']->table('shipping') .
            " WHERE shipping_id = '$shipping_id' " .
            'AND enabled = 1';

    return $GLOBALS['db']->getRow($sql);
}

/**
 * 取得可用的配送方式列表
 * @param   array   $region_id_list     收货人地区id数组（包括国家、省、市、区）
 * @return  array   配送方式数组
 * @return  int   是否只显示默认配送方式：0-非默认配送方式 1-默认配送方式 其他-全部
 */
function available_shipping_list($region_id_list,$suppid=0,$is_default_show=1,$shipping_code=null)
{
	$where = ' AND s.supplier_id='.$suppid.' and r.region_id ' . db_create_in($region_id_list);
	
	if($is_default_show == 0 || $is_default_show == 1){
		$where .= ' and s.is_default_show=' . $is_default_show;
	}
	
	if(!empty($shipping_code)){
		if($shipping_code == 'pups')
		{
			$where .= ' AND shipping_code = "' . $shipping_code . '" ';
		}
		else if($shipping_code == 'tc_express')
		{
			$where .= ' AND shipping_code = "' . $shipping_code . '" ';
		}
		else
		{
			$where .= ' AND shipping_code != "pups" AND shipping_code != "tc_express"  ';
		}
	}
	
    $sql = 'SELECT DISTINCT s.shipping_id, s.shipping_code, s.shipping_name, ' .
                's.shipping_desc, s.insure, s.support_cod, a.configure , s.support_pickup ' .
            'FROM ' . $GLOBALS['yp']->table('shipping') . ' AS s, ' .
                $GLOBALS['yp']->table('shipping_area') . ' AS a, ' .
                $GLOBALS['yp']->table('area_region') . ' AS r ' .
            'WHERE r.shipping_area_id = a.shipping_area_id AND a.shipping_id = s.shipping_id AND s.enabled = 1 ' . $where . ' ORDER BY s.shipping_order desc';
    
    return $GLOBALS['db']->getAll($sql);
}
/**
 * 取得某配送方式对应于某收货地址的区域信息
 * @param   int     $shipping_id        配送方式id
 * @param   array   $region_id_list     收货人地区id数组
 * @return  array   配送区域信息（config 对应着反序列化的 configure）
 */
function shipping_area_info($shipping_id, $region_id_list)
{
    $sql = 'SELECT s.shipping_code, s.shipping_name, ' .
                's.shipping_desc, s.insure, s.support_cod, a.configure ' .
            'FROM ' . $GLOBALS['yp']->table('shipping') . ' AS s, ' .
                $GLOBALS['yp']->table('shipping_area') . ' AS a, ' .
                $GLOBALS['yp']->table('area_region') . ' AS r ' .
            "WHERE s.shipping_id = '$shipping_id' " .
            'AND r.region_id ' . db_create_in($region_id_list) .
            ' AND r.shipping_area_id = a.shipping_area_id AND a.shipping_id = s.shipping_id AND s.enabled = 1';
    $row = $GLOBALS['db']->getRow($sql);

    if (!empty($row))
    {
        $shipping_config = unserialize_config($row['configure']);
        if (isset($shipping_config['pay_fee']))
        {
            if (strpos($shipping_config['pay_fee'], '%') !== false)
            {
                $row['pay_fee'] = floatval($shipping_config['pay_fee']) . '%';
            }
            else
            {
                 $row['pay_fee'] = floatval($shipping_config['pay_fee']);
            }
        }
        else
        {
            $row['pay_fee'] = 0.00;
        }
    }

    return $row;
}

/**
 * 计算运费
 * @param   string  $shipping_code      配送方式代码
 * @param   mix     $shipping_config    配送方式配置信息
 * @param   float   $goods_weight       商品重量
 * @param   float   $goods_amount       商品金额
 * @param   float   $goods_number       商品数量
 * @return  float   运费
 */
function shipping_fee($shipping_code, $shipping_config, $goods_weight, $goods_amount, $goods_number='')
{    
    if (!is_array($shipping_config))
    {
        $shipping_config = unserialize($shipping_config);
    }

    $filename = ROOT_PATH . '../includes/modules/shipping/' . $shipping_code . '.php';
    if (file_exists($filename))
    {
        include_once($filename);

        $obj = new $shipping_code($shipping_config);

        return $obj->calculate($goods_weight, $goods_amount, $goods_number);
    }
    else
    {
        return 0;
    }
}

/**
 * 获取指定配送的保价费用
 *
 * @access  public
 * @param   string      $shipping_code  配送方式的code
 * @param   float       $goods_amount   保价金额
 * @param   mix         $insure         保价比例
 * @return  float
 */
function shipping_insure_fee($shipping_code, $goods_amount, $insure)
{
    if (strpos($insure, '%') === false)
    {
        /* 如果保价费用不是百分比则直接返回该数值 */
        return floatval($insure);
    }
    else
    {
        $path = ROOT_PATH . '../includes/modules/shipping/' . $shipping_code . '.php';

        if (file_exists($path))
        {
            include_once($path);

            $shipping = new $shipping_code;
            $insure   = floatval($insure) / 100;

            if (method_exists($shipping, 'calculate_insure'))
            {
                return $shipping->calculate_insure($goods_amount, $insure);
            }
            else
            {
                return ceil($goods_amount * $insure);
            }
        }
        else
        {
            return false;
        }
    }
}

/**
 * 取得已安装的支付方式列表
 * @return  array   已安装的配送方式列表
 */
function payment_list()
{
    $sql = 'SELECT pay_id, pay_name ' .
            'FROM ' . $GLOBALS['yp']->table('payment') .
            ' WHERE enabled = 1';

    return $GLOBALS['db']->getAll($sql);
}

/**
 * 取得支付方式信息
 * @param   int     $pay_id     支付方式id
 * @return  array   支付方式信息
 */
function payment_info($pay_id)
{
    $sql = 'SELECT * FROM ' . $GLOBALS['yp']->table('payment') .
            " WHERE pay_id = '$pay_id' AND enabled = 1";

    return $GLOBALS['db']->getRow($sql);
}

/**
 * 获得订单需要支付的支付费用
 *
 * @access  public
 * @param   integer $payment_id
 * @param   float   $order_amount
 * @param   mix     $cod_fee
 * @return  float
 */
function pay_fee($payment_id, $order_amount, $cod_fee=null)
{
    $pay_fee = 0;
    $payment = payment_info($payment_id);
    $rate    = ($payment['is_cod'] && !is_null($cod_fee)) ? $cod_fee : $payment['pay_fee'];

    if (strpos($rate, '%') !== false)
    {
        /* 支付费用是一个比例 */
        $val     = floatval($rate) / 100;
        $pay_fee = $val > 0 ? $order_amount * $val /(1- $val) : 0;
    }
    else
    {
        $pay_fee = floatval($rate);
    }

    return round($pay_fee, 2);
}

/**
 * 取得可用的支付方式列表
 * @param   bool    $support_cod        配送方式是否支持货到付款
 * @param   int     $cod_fee            货到付款手续费（当配送方式支持货到付款时才传此参数）
 * @param   int     $is_online          是否支持在线支付
 * @return  array   配送方式数组
 */
function available_payment_list($support_cod, $cod_fee = 0, $is_online = false)
{
    $sql = 'SELECT pay_id, pay_code, pay_name, pay_fee, pay_desc, pay_config, is_cod' .
            ' FROM ' . $GLOBALS['yp']->table('payment') .
            " WHERE enabled = 1  ";

    if (!$support_cod)
    {
        $sql .= 'AND is_cod = 0 '; // 如果不支持货到付款
    }
    if ($is_online)
    {
        $sql .= "AND is_online = '1' ";
    }
    $sql .= 'ORDER BY pay_order'; // 排序
    $res = $GLOBALS['db']->query($sql);

    $pay_list = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        if ($row['is_cod'] == '1')
        {
            $row['pay_fee'] = $cod_fee;
        }

        $row['format_pay_fee'] = strpos($row['pay_fee'], '%') !== false ? $row['pay_fee'] : floatval($row['pay_fee'])>0? 
        price_format($row['pay_fee'], false):'';
        $modules[] = $row;
    }

    include_once(ROOT_PATH.'includes/lib_compositor.php');

    if(isset($modules))
    {
        return $modules;
    }
}

/**
 * 取得包装列表
 * @return  array   包装列表
 */
function pack_list()
{
    $sql = 'SELECT * FROM ' . $GLOBALS['yp']->table('pack');
    $res = $GLOBALS['db']->query($sql);

    $list = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['format_pack_fee'] = price_format($row['pack_fee'], false);
        $row['format_free_money'] = price_format($row['free_money'], false);
        $list[] = $row;
    }

    return $list;
}

/**
 * 取得包装信息
 * @param   int     $pack_id    包装id
 * @return  array   包装信息
 */
function pack_info($pack_id)
{
    $sql = "SELECT * FROM " . $GLOBALS['yp']->table('pack') .
            " WHERE pack_id = '$pack_id'";

    return $GLOBALS['db']->getRow($sql);
}

/**
 * 根据订单中的商品总额来获得包装的费用
 *
 * @access  public
 * @param   integer $pack_id
 * @param   float   $goods_amount
 * @return  float
 */
function pack_fee($pack_id, $goods_amount)
{
    $pack = pack_info($pack_id);

    $val = (floatval($pack['free_money']) <= $goods_amount && $pack['free_money'] > 0) ? 0 : floatval($pack['pack_fee']);

    return $val;
}

/**
 * 取得贺卡列表
 * @return  array   贺卡列表
 */
function card_list()
{
    $sql = "SELECT * FROM " . $GLOBALS['yp']->table('card');
    $res = $GLOBALS['db']->query($sql);

    $list = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['format_card_fee'] = price_format($row['card_fee'], false);
        $row['format_free_money'] = price_format($row['free_money'], false);
        $list[] = $row;
    }

    return $list;
}

/**
 * 取得贺卡信息
 * @param   int     $card_id    贺卡id
 * @return  array   贺卡信息
 */
function card_info($card_id)
{
    $sql = "SELECT * FROM " . $GLOBALS['yp']->table('card') .
            " WHERE card_id = '$card_id'";

    return $GLOBALS['db']->getRow($sql);
}

/**
 * 根据订单中商品总额获得需要支付的贺卡费用
 *
 * @access  public
 * @param   integer $card_id
 * @param   float   $goods_amount
 * @return  float
 */
function card_fee($card_id, $goods_amount)
{
    $card = card_info($card_id);

    return ($card['free_money'] <= $goods_amount && $card['free_money'] > 0) ? 0 : $card['card_fee'];
}

/**
 * 取得订单信息
 * @param   int     $order_id   订单id（如果order_id > 0 就按id查，否则按sn查）
 * @param   string  $order_sn   订单号
 * @return  array   订单信息（金额都有相应格式化的字段，前缀是formated_）
 */
function order_info($order_id, $order_sn = '')
{
    /* 计算订单各种费用之和的语句 */
    $total_fee = " (goods_amount - discount + tax + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee) AS total_fee ";
    $order_id = intval($order_id);
    if ($order_id > 0)
    {
        $sql = "SELECT *, " . $total_fee . " FROM " . $GLOBALS['yp']->table('order_info') .
                " WHERE order_id = '$order_id'";
    }
    else
    {
        $sql = "SELECT *, " . $total_fee . "  FROM " . $GLOBALS['yp']->table('order_info') .
                " WHERE order_sn = '$order_sn'";
    }
    $order = $GLOBALS['db']->getRow($sql);

    /* 格式化金额字段 */
    if ($order)
    {
        $order['formated_goods_amount']   = price_format($order['goods_amount'], false);
        $order['formated_discount']       = price_format($order['discount'], false);
        $order['formated_tax']            = price_format($order['tax'], false);
        $order['formated_shipping_fee']   = price_format($order['shipping_fee'], false);
        $order['formated_insure_fee']     = price_format($order['insure_fee'], false);
        $order['formated_pay_fee']        = price_format($order['pay_fee'], false);
        $order['formated_pack_fee']       = price_format($order['pack_fee'], false);
        $order['formated_card_fee']       = price_format($order['card_fee'], false);
        $order['formated_total_fee']      = price_format($order['total_fee'], false);
        $order['formated_money_paid']     = price_format($order['money_paid'], false);
        $order['formated_bonus']          = price_format($order['bonus'], false);
        $order['formated_integral_money'] = price_format($order['integral_money'], false);
        $order['formated_surplus']        = price_format($order['surplus'], false);
        $order['formated_order_amount']   = price_format(abs($order['order_amount']), false);
	$order['formated_order_amount_wap']   = $order['order_amount'];
        $order['formated_add_time']       = local_date($GLOBALS['_CFG']['time_format'], $order['add_time']);
    }

    return $order;
}

/**
 * 判断订单是否已完成
 * @param   array   $order  订单信息
 * @return  bool
 */
function order_finished($order)
{
    return $order['order_status']  == OS_CONFIRMED &&
        ($order['shipping_status'] == SS_SHIPPED || $order['shipping_status'] == SS_RECEIVED) &&
        ($order['pay_status']      == PS_PAYED   || $order['pay_status'] == PS_PAYING);
}

/**
 * 取得订单商品
 * @param   int     $order_id   订单id
 * @return  array   订单商品数组
 */
function order_goods($order_id)
{
    $sql = "SELECT rec_id, goods_id, goods_name, goods_sn, market_price, goods_number, " .
            "goods_price, goods_attr, is_real, parent_id, is_gift, " .
            "goods_price * goods_number AS subtotal, extension_code, package_attr_id   " .
            "FROM " . $GLOBALS['yp']->table('order_goods') .
            " WHERE order_id = '$order_id'";

    $res = $GLOBALS['db']->query($sql);

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        if ($row['extension_code'] == 'package_buy')
        {
            $row['package_goods_list'] = get_package_goods($row['goods_id'], $row['package_attr_id']);
        }
       // $row['goods_thumb'] = $GLOBALS['db']->getOne("select goods_thumb from ". $GLOBALS['yp']->table('goods') ." where goods_id = ".$row['goods_id']); 
        $row['goods_thumb'] = get_image_path($row['goods_id'], $GLOBALS['db']->getOne("select goods_thumb from ". $GLOBALS['yp']->table('goods') ." where goods_id = ".$row['goods_id']));
        $goods_list[] = $row;
    }

    //return $GLOBALS['db']->getAll($sql);
    return $goods_list;
}

/**
 * 取得订单总金额
 * @param   int     $order_id   订单id
 * @param   bool    $include_gift   是否包括赠品
 * @return  float   订单总金额
 */
function order_amount($order_id, $include_gift = true)
{
    $sql = "SELECT SUM(goods_price * goods_number) " .
            "FROM " . $GLOBALS['yp']->table('order_goods') .
            " WHERE order_id = '$order_id'";
    if (!$include_gift)
    {
        $sql .= " AND is_gift = 0";
    }

    return floatval($GLOBALS['db']->getOne($sql));
}

/**
 * 取得某订单商品总重量和总金额（对应 cart_weight_price）
 * @param   int     $order_id   订单id
 * @return  array   ('weight' => **, 'amount' => **, 'formated_weight' => **)
 */
function order_weight_price($order_id)
{
    $sql = "SELECT SUM(g.goods_weight * o.goods_number) AS weight, " .
                "SUM(o.goods_price * o.goods_number) AS amount ," .
                "SUM(o.goods_number) AS number " .
            "FROM " . $GLOBALS['yp']->table('order_goods') . " AS o, " .
                $GLOBALS['yp']->table('goods') . " AS g " .
            "WHERE o.order_id = '$order_id' " .
            "AND o.goods_id = g.goods_id";

    $row = $GLOBALS['db']->getRow($sql);
    $row['weight'] = floatval($row['weight']);
    $row['amount'] = floatval($row['amount']);
    $row['number'] = intval($row['number']);

    /* 格式化重量 */
    $row['formated_weight'] = formated_weight($row['weight']);

    return $row;
}

/**
 * 获得订单中的费用信息
 *
 * @access  public
 * @param   array   $order
 * @param   array   $goods
 * @param   array   $consignee
 * @param   bool    $is_gb_deposit  是否团购保证金（如果是，应付款金额只计算商品总额和支付费用，可以获得的积分取 $gift_integral）
 * @return  array
 */
function order_fee($order, $goods, $consignee)
{
    /* 初始化订单的扩展code */
    if (!isset($order['extension_code']))
    {
        $order['extension_code'] = '';
    }

    if ($order['extension_code'] == 'group_buy')
    {
        $group_buy = group_buy_info($order['extension_id']);
    }
    
    /* 预售活动 */
    if ($order['extension_code'] == 6)
    {
    	$pre_sale = pre_sale_info($order['extension_id']);
    }

    $total  = array('real_goods_count' => 0,
                    'gift_amount'      => 0,
                    'goods_price'      => 0,
                    'market_price'     => 0,
                    'discount'         => 0,
                    'pack_fee'         => 0,
                    'card_fee'         => 0,
                    'shipping_fee'     => 0,
                    'shipping_insure'  => 0,
                    'integral_money'   => 0,
                    'bonus'            => 0,
                    'surplus'          => 0,
                    'cod_fee'          => 0,
                    'pay_fee'          => 0,
                    'tax'              => 0);
    $weight = 0;

    /* 商品总价 */
    foreach ($goods AS $val)
    {
        /* 统计实体商品的个数 */
        if ($val['is_real'])
        {
            $total['real_goods_count']++;
        }

        $total['goods_price']  += $val['goods_price'] * $val['goods_number'];
        $total['market_price'] += $val['market_price'] * $val['goods_number'];
    }

    $total['saving']    = $total['market_price'] - $total['goods_price'];
    $total['save_rate'] = $total['market_price'] ? round($total['saving'] * 100 / $total['market_price']) . '%' : 0;

    $total['goods_price_formated']  = price_format($total['goods_price'], false);
    $total['market_price_formated'] = price_format($total['market_price'], false);
    $total['saving_formated']       = price_format($total['saving'], false);

    /* 折扣 */
    if ($order['extension_code'] != GROUP_BUY_CODE && $order['extension_code'] != 6)
    {
        $discount = compute_discount(isset($order['supplier_id']) ? $order['supplier_id'] : -1);
        $total['discount'] = $discount['discount'];
        if ($total['discount'] > $total['goods_price'])
        {
            $total['discount'] = $total['goods_price'];
        }
    }
    $total['discount_formated'] = price_format($total['discount'], false);

    /* 税额 */
    if (!empty($order['need_inv']) && $order['inv_type'] != '')
    {
        /* 查税率 */
        $rate = 0;
        foreach ($GLOBALS['_CFG']['invoice_type']['type'] as $key => $type)
        {
            if ($type == $order['inv_type'])
            {
                $rate = floatval($GLOBALS['_CFG']['invoice_type']['rate'][$key]) / 100;
                break;
            }
        }
        if ($rate > 0)
        {
            $total['tax'] = $rate * $total['goods_price'];
        }
    }
    $total['tax_formated'] = price_format($total['tax'], false);

    /* 包装费用 */
    if (!empty($order['pack_id']))
    {
        $total['pack_fee']      = pack_fee($order['pack_id'], $total['goods_price']);
    }
    $total['pack_fee_formated'] = price_format($total['pack_fee'], false);

    /* 贺卡费用 */
    if (!empty($order['card_id']))
    {
        $total['card_fee']      = card_fee($order['card_id'], $total['goods_price']);
    }
    $total['card_fee_formated'] = price_format($total['card_fee'], false);

    /* 红包 */
    $total['bonus'] = 0;
	
    if (!empty($order['bonus_id']))
    {
        $bonus          = bonus_info($order['bonus_id']);
        $total['bonus'] = $bonus['type_money'];
    }
    

    /* 线下红包 */
     if (!empty($order['bonus_sn']))
    {
        $bonus          = bonus_info(0,$order['bonus_sn']);
        $total['bonus'] += $bonus['type_money'];
        //$total['bonus_kill'] = $order['bonus_kill'];
        //$total['bonus_kill_formated'] = price_format($total['bonus_kill'], false);
    }
    $total['bonus_formated'] = price_format($total['bonus'], false);



    /* 配送费用 */
    $shipping_cod_fee = NULL;
    
    $sql_where = $_SESSION['user_id']>0 ? "user_id='". $_SESSION['user_id'] ."' " : "session_id = '" . SESS_ID . "' AND user_id=0 ";
	/*
    if ($order['shipping_id'] > 0 && $total['real_goods_count'] > 0)
    {
        $region['country']  = $consignee['country'];
        $region['province'] = $consignee['province'];
        $region['city']     = $consignee['city'];
        $region['district'] = $consignee['district'];
        $shipping_info = shipping_area_info($order['shipping_id'], $region);

        if (!empty($shipping_info))
        {
        	if ($order['extension_code'] == GROUP_BUY_CODE)
            {
                $weight_price = cart_weight_price(CART_GROUP_BUY_GOODS);
            }
            else if ($order['extension_code'] == 6)
            {
                $weight_price = cart_weight_price(CART_PRE_SALE_GOODS);
            }
            else
            {
                $weight_price = cart_weight_price();
            }

            // 查看购物车中是否全为免运费商品，若是则把运费赋为零
            $sql = 'SELECT count(*) FROM ' . $GLOBALS['yp']->table('cart') . " WHERE  $sql_where AND `extension_code` != 'package_buy' AND `is_shipping` = 0 AND rec_id in (".$_SESSION['sel_cartgoods'].")";  //jx
            $shipping_count = $GLOBALS['db']->getOne($sql);

            $total['shipping_fee'] = ($shipping_count == 0 AND $weight_price['free_shipping'] == 1) ?0 :  shipping_fee($shipping_info['shipping_code'],$shipping_info['configure'], $weight_price['weight'], $total['goods_price'], $weight_price['number']);

            if (!empty($order['need_insure']) && $shipping_info['insure'] > 0)
            {
                $total['shipping_insure'] = shipping_insure_fee($shipping_info['shipping_code'],
                    $total['goods_price'], $shipping_info['insure']);
            }
            else
            {
                $total['shipping_insure'] = 0;
            }

            if ($shipping_info['support_cod'])
            {
                $shipping_cod_fee = $shipping_info['pay_fee'];
            }
        }
    }

    $total['shipping_fee_formated']    = price_format($total['shipping_fee'], false);
    $total['shipping_insure_formated'] = price_format($total['shipping_insure'], false);*/

	/* 代码增加_start  By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 */	
	if (count($order['shipping_pay']) > 0 && $total['real_goods_count'] > 0){

		

		foreach ($goods AS $val)
		{
			$sql_supp = "select g.supplier_id, IF(g.supplier_id='0', '本网站', s.supplier_name) AS supplier_name2 from ".$GLOBALS['yp']->table('goods').
								  " AS g left join ".$GLOBALS['yp']->table('supplier')." AS s on g.supplier_id=s.supplier_id where g.goods_id='". $val['goods_id'] ."' ";
			$row_supp = $GLOBALS['db']->getRow($sql_supp);
			$row_supp['supplier_id'] = $row_supp['supplier_id'] ? intval($row_supp['supplier_id']) :0;

			$region['country']  = $consignee['country'];
			$region['province'] = $consignee['province'];
			$region['city']     = $consignee['city'];
			$region['district'] = $consignee['district'];
			$shipping_info = shipping_area_info($order['shipping_pay'][$row_supp['supplier_id']], $region);

			$total['supplier_shipping'][$row_supp['supplier_id']]['supplier_name'] =$row_supp['supplier_name2'];
			$total['supplier_shipping'][$row_supp['supplier_id']]['goods_number'] += $val['goods_number'];

			$total['supplier_goodsnumber'][$row_supp['supplier_id']] += $val['goods_number'];

			$total['goods_price_supplier'][$row_supp['supplier_id']]  += $val['goods_price'] * $val['goods_number'];

			if ($order['extension_code'] == 'group_buy')
			{
					$weight_price2 = cart_weight_price2(CART_GROUP_BUY_GOODS, $row_supp['supplier_id']);
			}
			else
			{
					$weight_price2 = cart_weight_price2(CART_GENERAL_GOODS, $row_supp['supplier_id']);
			}

			// 查看购物车中是否全为免运费商品，若是则把运费赋为零
		   $sql_where = $_SESSION['user_id']>0 ? "c.user_id='". $_SESSION['user_id'] ."' " : "c.session_id = '" . SESS_ID . "' AND c.user_id=0 ";
		   if($_SESSION['sel_cartgoods']){
            $sql_plus = " AND c.rec_id in (".$_SESSION['sel_cartgoods'].") ";
           }
           $sql = 'SELECT count(*) FROM ' . $GLOBALS['yp']->table('cart') . " AS c left join ". $GLOBALS['yp']->table('goods') ." AS g on c.goods_id=g.goods_id WHERE g.supplier_id = '". $row_supp['supplier_id'] ."' AND $sql_where AND c.extension_code != 'package_buy' AND c.is_shipping = 0 ".$sql_plus;  //jx
		   $shipping_count_supp = $GLOBALS['db']->getOne($sql);

		   $total['supplier_shipping'][$row_supp['supplier_id']]['shipping_fee'] = ($shipping_count_supp == 0 AND $weight_price2['free_shipping'] == 1) ?0 :  shipping_fee($shipping_info['shipping_code'],$shipping_info['configure'], $weight_price2['weight'], $total['goods_price_supplier'][$row_supp['supplier_id']], $weight_price2['number']);
		   $total['supplier_shipping'][$row_supp['supplier_id']]['formated_shipping_fee'] = price_format($total['supplier_shipping'][$row_supp['supplier_id']]['shipping_fee'], false);
		}
	
		krsort($total['supplier_shipping']);
		
		$total['shipping_fee']    = 0;
		foreach($total['supplier_shipping'] AS $supp_shipping)
		{
			$total['shipping_fee'] += $supp_shipping['shipping_fee'];
		}
		$total['shipping_fee_formated']    = price_format($total['shipping_fee'], false);
	}
	
	/* 代码增加_end  By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121 */

    // 购物车中的商品能享受红包支付的总额
    $bonus_amount = compute_discount_amount();
    // 红包和积分最多能支付的金额为商品总额
    //$max_amount = $total['goods_price'] == 0 ? $total['goods_price'] : $total['goods_price'] - $bonus_amount;
	$max_amount = $total['goods_price'] == 0 ? $total['goods_price'] : ($total['goods_price'] - $bonus_amount) > 0 ? $total['goods_price'] - $bonus_amount : 0 ;
    
	/* 计算订单总额 */
	if ($order['extension_code'] == GROUP_BUY_CODE && $group_buy['deposit'] > 0)
    {
        $total['amount'] = $total['goods_price'];
    }
    else if($order['extension_code'] == 6 && $pre_sale['deposit'] > 0)
    {
        $total['amount'] = $total['goods_price'];
    }
    else
    {
        $total['amount'] = $total['goods_price'] - $total['discount'] + $total['tax'] + $total['pack_fee'] + $total['card_fee'] +
            $total['shipping_fee'] + $total['shipping_insure'] + $total['cod_fee'];

        // 减去红包金额
		
        $use_bonus        = min($total['bonus'], $max_amount); // 实际减去的红包金额
        if(isset($total['bonus_kill']))
        {
            $use_bonus_kill   = min($total['bonus_kill'], $max_amount);
            $total['amount'] -=  $price = ($total['bonus_kill'] > 0 ? number_format($total['bonus_kill'], 2, '.', '') : 0); // 还需要支付的订单金额
        }

        $total['bonus']   = $use_bonus;
        $total['bonus_formated'] = price_format($total['bonus'], false);

        $total['amount'] -= $use_bonus; // 还需要支付的订单金额
        $max_amount      -= $use_bonus; // 积分最多还能支付的金额

    }

    /* 余额 */
    $order['surplus'] = $order['surplus'] > 0 ? $order['surplus'] : 0;
    if ($total['amount'] > 0)
    {
        if (isset($order['surplus']) && $order['surplus'] > $total['amount'])
        {
            $order['surplus'] = $total['amount'];
            $total['amount']  = 0;
        }
        else
        {
            $total['amount'] -= floatval($order['surplus']);
        }
    }
    else
    {
        $order['surplus'] = 0;
        $total['amount']  = 0;
    }
    $total['surplus'] = $order['surplus'];
    $total['surplus_formated'] = price_format($order['surplus'], false);
	
    /* 积分 */
    $order['integral'] = $order['integral'] > 0 ? $order['integral'] : 0;
    if ($total['amount'] > 0 && $max_amount > 0 && $order['integral'] > 0)
    {
        $integral_money = value_of_integral($order['integral']);

        // 使用积分支付
        $use_integral            = min($total['amount'], $max_amount, $integral_money); // 实际使用积分支付的金额
        $total['amount']        -= $use_integral;
        $total['integral_money'] = $use_integral;
        $order['integral']       = integral_of_value($use_integral);
    }
    else
    {
        $total['integral_money'] = 0;
        $order['integral']       = 0;
    }
    $total['integral'] = $order['integral'];
    $total['integral_formated'] = price_format($total['integral_money'], false);

    /* 保存订单信息 */
    $_SESSION['flow_order'] = $order;

    $se_flow_type = isset($_SESSION['flow_type']) ? $_SESSION['flow_type'] : '';
    
    /* 支付费用 */
    if (!empty($order['pay_id']) && ($total['real_goods_count'] > 0 || $se_flow_type != CART_EXCHANGE_GOODS))
    {
        $total['pay_fee']      = pay_fee($order['pay_id'], $total['amount'], $shipping_cod_fee);
    }

    $total['pay_fee_formated'] = price_format($total['pay_fee'], false);

    $total['amount']           += $total['pay_fee']; // 订单总额累加上支付费用
    $total['amount_formated']  = price_format($total['amount'], false);

    /* 取得可以得到的积分和红包 */
    if ($order['extension_code'] == GROUP_BUY_CODE)
    {
    	$total['will_get_integral'] = $group_buy['gift_integral'];
    }
    else if($order['extension_code'] == 6)
    {
    	$total['will_get_integral'] = $pre_sale['gift_integral'];
    }
    elseif ($order['extension_code'] == 'exchange_goods')
    {
        $total['will_get_integral'] = 0;
    }
    else
    {
        $total['will_get_integral'] = get_give_integral($goods);
    }
    //$total['will_get_bonus']        = $order['extension_code'] == 'exchange_goods' ? 0 : price_format(get_total_bonus(), false);
    $total['will_get_bonus']        = $order['extension_code'] == 'exchange_goods' ? 0 : price_format(get_total_bonus($total['goods_price_supplier']), false);
	$total['formated_goods_price']  = price_format($total['goods_price'], false);
    $total['formated_market_price'] = price_format($total['market_price'], false);
    $total['formated_saving']       = price_format($total['saving'], false);

    if ($order['extension_code'] == 'exchange_goods')
    {
    	$sql_exchange = $_SESSION['user_id']>0 ? "c.user_id='". $_SESSION['user_id'] ."' " : "c.session_id = '" . SESS_ID . "' AND c.user_id=0 ";
        $sql = 'SELECT SUM(eg.exchange_integral) '.
               'FROM ' . $GLOBALS['yp']->table('cart') . ' AS c,' . $GLOBALS['yp']->table('exchange_goods') . 'AS eg '.
               "WHERE c.goods_id = eg.goods_id AND " . $sql_exchange .
               "  AND c.rec_type = '" . CART_EXCHANGE_GOODS . "' " .
               '  AND c.is_gift = 0 AND c.goods_id > 0 ' .
               'GROUP BY eg.goods_id';
        $exchange_integral = $GLOBALS['db']->getOne($sql);
        $total['exchange_integral'] = $exchange_integral;
    }

    return $total;
}

/**
* 是否是货到付款的订单
*/
function get_cod_id($order_id){
	global $db,$yp;
	$pay_id = $db->getOne("select pay_id from ".$yp->table('payment')." where pay_code='cod' limit 1");
	if($pay_id){
		$num = $db->getOne("select count(order_id) from ".$yp->table('order_info')." where order_id=".$order_id." and pay_id=".$pay_id);
		return $num ? false : true;
	}else{
		return true;
	}
}


/**
 * 修改订单
 * @param   int     $order_id   订单id
 * @param   array   $order      key => value
 * @return  bool
 */
function update_order($order_id, $order)
{
	if(isset($order['shipping_status']) && $order['shipping_status'] == SS_RECEIVED && get_cod_id($order_id)){
		//收货确认的订单有可能发生佣金操作
		get_pingtai_rebate_from_supplier($order_id);
		$order['rebate_ispay'] = 2;
	}
    return $GLOBALS['db']->autoExecute($GLOBALS['yp']->table('order_info'),
        $order, 'UPDATE', "order_id = '$order_id'");
}

/**
 * 入驻商订单佣金计算(订单收货确认后触发)
 */
 function get_pingtai_rebate_from_supplier($order_id)
 {
    global $db,$yp;

    $sql = "select *,sum(money_paid + surplus) as jisuan_money from ".$yp->table('order_info')." where order_id=".$order_id;

    $info = $db->getRow($sql);

    $supplier_id = 0;
    $supplier_user_id = 0;
    $rebate = 0;




    if($info['supplier_id']>0){
        //如果是入驻商的订单
        $supp_sql = "select supplier_id,user_id,supplier_rebate from ".$yp->table('supplier')." where supplier_id=".$info['supplier_id']." and status=1";
        $supp_info = $db->getRow($supp_sql);
        if(!$supp_info){
            return true;
        }
    }else{
        // 寒冰   qq  30948555   2    20170911 平台订单直接分成
       order_affi_received($order_id , $info['user_id'] , $info['supplier_id']);
       return true;
    }

    if($info['pay_status'] == PS_PAYED){
        $order_user_id = $info['user_id'];//下单人user_id
        $num = 5;
        for ($i=0; $i < $num; $i++){
        $sql = "SELECT parent_id FROM " . $GLOBALS['yp']->table('users') . " WHERE user_id = '$order_user_id'";
        $parent_id = $db -> getOne($sql);
        if($parent_id == $supp_info['user_id']){//上级id等于商家uid
        $rebate = $supp_info['my_supplier_rebate'];
        $i=5;
        }else{
        $rebate = $supp_info['supplier_rebate'];
        $order_user_id = $parent_id;
        $i=$i+1;
        }
        }
        $supplier_id = $supp_info['supplier_id'];
        $supplier_user_id = $supp_info['user_id'];
        //$rebate = $supp_info['supplier_rebate'];
        $order_id = $info['order_id'];
        $order_sn = $info['order_sn'];
        $pay_id = $info['pay_id'];
        $pay_name = $info['pay_name'];
        //收货确认的订单
        
        $split_money = get_split_money_by_fencheng($order_id , $info['supplier_id']);//订单产生的分成佣金
        $money = $info['jisuan_money'];//要计算的价钱
        $rebate_money = round(($money * $rebate)/100, 2);//返给平台方的价钱
       if ($info['supplier_id']>0 && $info['froms'] != 's_xcx') {//订单来源不是商家小程序
          $result_money = $money - $rebate_money;//入驻商获取的价钱
          $result_money = $money - $rebate_money - $split_money;//入驻商获取的价钱
          $texts = "订单支付";
          $add_time = gmtime();

          order_affi_received($order_id , $info['user_id'] , $info['supplier_id']);//分成  寒冰  20170911 
          //佣金订单日志
          $sql = "INSERT INTO ".$yp->table('supplier_rebate_log')." (order_id,order_sn,supplier_id,all_money,rebate_money,result_money,split_money,pay_id,pay_name,texts,add_time) VALUES ({$order_id},'{$order_sn}',{$supplier_id},'{$money}','{$rebate_money}','{$result_money}','{$split_money}',{$pay_id},'{$pay_name}','{$texts}',{$add_time})";
          $db->query($sql);
 
          //入驻商绑定的会员帐户日志变动
          $change_desc = "订单:".$order_sn."返入驻商会员可用资金";
          log_account_change($supplier_user_id, $result_money, 0, 0, 0, $change_desc, ACT_ADJUSTING);

       }else{//如果是商家小程序自主收款
          $sql = "SELECT user_money FROM " . $GLOBALS['yp']->table('users') . " WHERE user_id = '$supplier_user_id'";
          $supplier_user_money = $db -> getOne($sql);//商家账户余额
          $koukuan = $split_money + $rebate_money;//平台提成与分销佣金
        if ($supplier_user_money > $koukuan ) {//商家余额足以支付平台提成与分销佣金
          $result_money = '-'.$koukuan;//入驻商获取的价钱
          $texts = "商家小程序订单支付(自主收款，只显示记录)";
          $add_time = gmtime();

          order_affi_received($order_id , $info['user_id'] , $info['supplier_id']);//分成  寒冰  20170911 
          //佣金订单日志
          $sql = "INSERT INTO ".$yp->table('supplier_rebate_log')." (order_id,order_sn,supplier_id,all_money,rebate_money,result_money,split_money,pay_id,pay_name,texts,add_time) VALUES ({$order_id},'{$order_sn}',{$supplier_id},'{$money}','{$rebate_money}','{$money}','{$split_money}',{$pay_id},'{$pay_name}','{$texts}',{$add_time})";
          $db->query($sql);
 
          //入驻商绑定的会员帐户日志变动
          $change_desc = "订单:".$order_sn."支付（商家自主收款，扣除平台提成与分销佣金）";
          log_account_change($supplier_user_id, $result_money, 0, 0, 0, $change_desc, ACT_ADJUSTING);

         } else {
            return true;
        }
        
       }
    }
    return true;
 }
/* 自动确认收货 佣金结算 */
function order_affi_received($order_id , $user_id , $supplier_id)
{
    $db = $GLOBALS['db'];
    $yp = $GLOBALS['yp'];
    if ($supplier_id > 0) {//取出商家分成方式配置
         $distrib_style = $db->getOne("SELECT value FROM " . $GLOBALS['yp']->table('supplier_shop_config') ." WHERE `code` = 'distrib_style' AND supplier_id = '$supplier_id'");
        } else {

       $distrib_style = $db->getOne("SELECT value FROM " . $GLOBALS['yp']->table('ypmart_shop_config') ." WHERE `code` = 'distrib_style'");
     } 
    
    if($distrib_style == 0){
       include_once(ROOT_PATH . 'includes/lib_fencheng.php');
       do_fencheng($order_id , $supplier_id);
    }
}


/**
 * 得到新订单号
 * @return  string
 */
function get_order_sn()
{
    /* 选择一个随机的方案 */
    mt_srand((double) microtime() * 1000000);

    return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

/**
 * 取得购物车商品
 * @param   int     $type   类型：默认普通商品
 * @return  array   购物车商品数组
 */
function cart_goods($type = CART_GENERAL_GOODS)
{
	$id_ext = "";
	if ($_SESSION['sel_cartgoods'])
	{
		$id_ext = " AND c.rec_id in (". $_SESSION['sel_cartgoods'] .") ";
	}
$sql_where = $_SESSION['user_id']>0 ? "c.user_id='". $_SESSION['user_id'] ."' " : "c.session_id = '" . SESS_ID . "' AND c.user_id=0 ";
    $sql = "SELECT c.rec_id, c.user_id, c.goods_id, c.goods_name, c.goods_sn, c.goods_number, c.market_price, " .
			" c.goods_price, c.goods_attr, c.is_real, c.extension_code, c.parent_id, c.is_gift, c.is_shipping, " .
			" package_attr_id, c.goods_price * c.goods_number AS subtotal, " .
			" IF(ga.act_id, ga.supplier_id, g.supplier_id) as supplier_id, " .
			" IF(ga.act_id, IFNULL(ss.supplier_name, '网站自营'), IFNULL(s.supplier_name, '网站自营')) as seller " .
            " FROM " . $GLOBALS['yp']->table('cart') .
            " as c LEFT JOIN " . $GLOBALS['yp']->table('goods') . " as g ON c.goods_id = g.goods_id LEFT JOIN ". $GLOBALS['yp']->table('supplier') .
            " as s ON s.supplier_id = g.supplier_id " .
			" left join " . $GLOBALS['yp']->table('goods_activity') . " as ga " .
			" on ga.act_id = c.goods_id and c.extension_code = 'package_buy'" .
			" left join " . $GLOBALS['yp']->table('supplier') . " as ss on ss.supplier_id = ga.supplier_id " .
			" WHERE $sql_where " .
            " AND c.rec_type = '$type' $id_ext ";  //代码修改 By  demo.coolhong.com 今天优品 多商户系统 QQ 120-029-121  增加一个 $id_ext , package_attr_id	

    $arr = $GLOBALS['db']->getAll($sql);

    /* 格式化价格及礼包商品 */
    foreach ($arr as $key => $value)
    {
        $arr[$key]['formated_market_price'] = price_format($value['market_price'], false);
        $arr[$key]['formated_goods_price']  = price_format($value['goods_price'], false);
        $arr[$key]['formated_subtotal']     = price_format($value['subtotal'], false);

	$arr[$key]['goods_thumb']  = $GLOBALS['db']->getOne("SELECT `goods_thumb` FROM " . $GLOBALS['yp']->table('goods') . " WHERE `goods_id`='{$value['goods_id']}'");
        $arr[$key]['goods_thumb'] = get_image_path($value['goods_id'], $arr[$key]['goods_thumb'], true);
        if ($value['extension_code'] == 'package_buy')
        {
            $arr[$key]['package_goods_list'] = get_package_goods($value['goods_id'], $value['package_attr_id']);
        }
    }

    return $arr;
}

/**
 * 取得购物车总金额
 * @params  boolean $include_gift   是否包括赠品
 * @param   int     $type           类型：默认普通商品
 * @return  float   购物车总金额
 */
function cart_amount($include_gift = true, $type = CART_GENERAL_GOODS)
{
	$sql_where = $_SESSION['user_id']>0 ? "user_id='". $_SESSION['user_id'] ."' " : "session_id = '" . SESS_ID . "' AND user_id=0 ";
    $sql = "SELECT SUM(goods_price * goods_number) " .
            " FROM " . $GLOBALS['yp']->table('cart') .
            " WHERE $sql_where " .
            "AND rec_type = '$type' ";

    if (!$include_gift)
    {
        $sql .= ' AND is_gift = 0 AND goods_id > 0';
    }

    return floatval($GLOBALS['db']->getOne($sql));
}

/**
 * 取得购物车总金额根据购物车中的id
 * @params  array $cartids   购物车中的id数组
 * @params  boolean $include_gift   是否包括赠品
 * @param   int     $type           类型：默认普通商品
 * @return  float   购物车总金额
 */
function cart_amount_new($cartids='', $include_gift = true, $type = CART_GENERAL_GOODS)
{
	$sql_where = $_SESSION['user_id']>0 ? "user_id='". $_SESSION['user_id'] ."' " : "session_id = '" . SESS_ID . "' AND user_id=0 ";
    $sql = "SELECT SUM(goods_price * goods_number) " .
            " FROM " . $GLOBALS['yp']->table('cart') .
            " WHERE $sql_where " .
            "AND rec_type = '$type' ";
    if (is_array($cartids)){
    	$idinfo = array_filter($cartids);
        if($idinfo){
        	$sql .= ' AND rec_id in('.implode(',',$idinfo).')';
        }
    }
    if (!$include_gift)
    {
        $sql .= ' AND is_gift = 0 AND goods_id > 0';
    }

    return floatval($GLOBALS['db']->getOne($sql));
}

/**
 * 检查某商品是否已经存在于购物车
 *
 * @access  public
 * @param   integer     $id
 * @param   array       $spec
 * @param   int         $type   类型：默认普通商品
 * @return  boolean
 */
function cart_goods_exists($id, $spec, $type = CART_GENERAL_GOODS)
{
    /* 检查该商品是否已经存在在购物车中 */
    $sql = "SELECT COUNT(*) FROM " .$GLOBALS['yp']->table('cart').
            "WHERE session_id = '" .SESS_ID. "' AND goods_id = '$id' ".
            "AND parent_id = 0 AND goods_attr = '" .get_goods_attr_info($spec). "' " .
            "AND rec_type = '$type'";

    return ($GLOBALS['db']->getOne($sql) > 0);
}

/**
 * 获得购物车中商品的总重量、总价格、总数量
 *
 * @access  public
 * @param   int     $type   类型：默认普通商品
 * @return  array
 */
function cart_weight_price($type = CART_GENERAL_GOODS)
{
    $package_row['weight'] = 0;
    $package_row['amount'] = 0;
    $package_row['number'] = 0;

    $packages_row['free_shipping'] = 1;

    /* 计算超值礼包内商品的相关配送参数 */
    $sql = 'SELECT goods_id, goods_number, goods_price FROM ' . $GLOBALS['yp']->table('cart') . " WHERE extension_code = 'package_buy' AND session_id = '" . SESS_ID . "'";
    $row = $GLOBALS['db']->getAll($sql);

    if ($row)
    {
        $packages_row['free_shipping'] = 0;
        $free_shipping_count = 0;

        foreach ($row as $val)
        {
            // 如果商品全为免运费商品，设置一个标识变量
            $sql = 'SELECT count(*) FROM ' .
                    $GLOBALS['yp']->table('package_goods') . ' AS pg, ' .
                    $GLOBALS['yp']->table('goods') . ' AS g ' .
                    "WHERE g.goods_id = pg.goods_id AND g.is_shipping = 0 AND pg.package_id = '"  . $val['goods_id'] . "'";
            $shipping_count = $GLOBALS['db']->getOne($sql);

            if ($shipping_count > 0)
            {
                // 循环计算每个超值礼包商品的重量和数量，注意一个礼包中可能包换若干个同一商品
                $sql = 'SELECT SUM(g.goods_weight * pg.goods_number) AS weight, ' .
                    'SUM(pg.goods_number) AS number FROM ' .
                    $GLOBALS['yp']->table('package_goods') . ' AS pg, ' .
                    $GLOBALS['yp']->table('goods') . ' AS g ' .
                    "WHERE g.goods_id = pg.goods_id AND g.is_shipping = 0 AND pg.package_id = '"  . $val['goods_id'] . "'";

                $goods_row = $GLOBALS['db']->getRow($sql);
                $package_row['weight'] += floatval($goods_row['weight']) * $val['goods_number'];
                $package_row['amount'] += floatval($val['goods_price']) * $val['goods_number'];
                $package_row['number'] += intval($goods_row['number']) * $val['goods_number'];
            }
            else
            {
                $free_shipping_count++;
            }
        }

        $packages_row['free_shipping'] = $free_shipping_count == count($row) ? 1 : 0;
    }

    /* 获得购物车中非超值礼包商品的总重量 */
    $sql    = 'SELECT SUM(g.goods_weight * c.goods_number) AS weight, ' .
                    'SUM(c.goods_price * c.goods_number) AS amount, ' .
                    'SUM(c.goods_number) AS number '.
                'FROM ' . $GLOBALS['yp']->table('cart') . ' AS c '.
                'LEFT JOIN ' . $GLOBALS['yp']->table('goods') . ' AS g ON g.goods_id = c.goods_id '.
                "WHERE c.session_id = '" . SESS_ID . "' " .
                "AND rec_type = '$type' AND g.is_shipping = 0 AND c.extension_code != 'package_buy'";
    $row = $GLOBALS['db']->getRow($sql);

    $packages_row['weight'] = floatval($row['weight']) + $package_row['weight'];
    $packages_row['amount'] = floatval($row['amount']) + $package_row['amount'];
    $packages_row['number'] = intval($row['number']) + $package_row['number'];
    /* 格式化重量 */
    $packages_row['formated_weight'] = formated_weight($packages_row['weight']);

    return $packages_row;
}

/**
 * 添加商品到购物车
 *
 * @access  public
 * @param   integer $goods_id   商品编号
 * @param   integer $num        商品数量
 * @param   array   $spec       规格值对应的id数组
 * @param   integer $parent     基本件
 * @return  boolean
 */
function addto_cart($goods_id, $num = 1, $spec = array(), $parent = 0)
{
    $GLOBALS['err']->clean();
    $_parent_id = $parent;

    /* 取得商品信息 */
    $sql = "SELECT g.goods_name, g.goods_sn, g.is_on_sale, g.is_real, ".
                "g.market_price, g.cost_price, g.shop_price AS org_price, g.promote_price, g.promote_start_date, ".
                "g.promote_end_date, g.goods_weight, g.integral, g.extension_code, ".
                "g.goods_number, g.is_alone_sale, g.is_shipping,".
                "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price ".
            " FROM " .$GLOBALS['yp']->table('goods'). " AS g ".
            " LEFT JOIN " . $GLOBALS['yp']->table('member_price') . " AS mp ".
                    "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ".
            " WHERE g.goods_id = '$goods_id'" .
            " AND g.is_delete = 0";
    $goods = $GLOBALS['db']->getRow($sql);

    if (empty($goods))
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['goods_not_exists'], ERR_NOT_EXISTS);

        return false;
    }

    /* 如果是作为配件添加到购物车的，需要先检查购物车里面是否已经有基本件 */
    if ($parent > 0)
    {
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['yp']->table('cart') .
                " WHERE goods_id='$parent' AND session_id='" . SESS_ID . "' AND extension_code <> 'package_buy'";
        if ($GLOBALS['db']->getOne($sql) == 0)
        {
            $GLOBALS['err']->add($GLOBALS['_LANG']['no_basic_goods'], ERR_NO_BASIC_GOODS);

            return false;
        }
    }

    /* 是否正在销售 */
    if ($goods['is_on_sale'] == 0)
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['not_on_sale'], ERR_NOT_ON_SALE);

        return false;
    }

    /* 不是配件时检查是否允许单独销售 */
    if (empty($parent) && $goods['is_alone_sale'] == 0)
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['cannt_alone_sale'], ERR_CANNT_ALONE_SALE);

        return false;
    }

    /* 如果商品有规格则取规格商品信息 配件除外 */
    $sql = "SELECT * FROM " .$GLOBALS['yp']->table('products'). " WHERE goods_id = '$goods_id' LIMIT 0, 1";
    $prod = $GLOBALS['db']->getRow($sql);

    if (is_spec($spec) && !empty($prod))
    {
        $product_info = get_products_info($goods_id, $spec);
    }
    if (empty($product_info))
    {
        $product_info = array('product_number' => '', 'product_id' => 0);
    }

    /* 检查：库存 */
    if ($GLOBALS['_CFG']['use_storage'] == 1)
    {
        //检查：商品购买数量是否大于总库存
        if ($num > $goods['goods_number'])
        {
            $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $goods['goods_number']), ERR_OUT_OF_STOCK);

            return false;
        }

        //商品存在规格 是货品 检查该货品库存
        if (is_spec($spec) && !empty($prod))
        {
            if (!empty($spec))
            {
                /* 取规格的货品库存 */
                if ($num > $product_info['product_number'])
                {
                    $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $product_info['product_number']), ERR_OUT_OF_STOCK);

                    return false;
                }
            }
        }
    }

    /* 计算商品的促销价格 */
    $spec_price             = spec_price($spec);
    $goods_price            = get_final_price($goods_id, $num, true, $spec);
    $goods['market_price'] += $spec_price;
    $goods_attr             = get_goods_attr_info($spec);
    $goods_attr_id          = join(',', $spec);

    /* 初始化要插入购物车的基本件数据 */
    $parent = array(
        'user_id'       => $_SESSION['user_id'],
        'session_id'    => SESS_ID,
        'goods_id'      => $goods_id,
        'goods_sn'      => addslashes($goods['goods_sn']),
        'product_id'    => $product_info['product_id'],
        'goods_name'    => addslashes($goods['goods_name']),
    	'cost_price'    => $goods['cost_price'],
    	'promote_price' => $goods['promote_price'],
        'market_price'  => $goods['market_price'],
		'split_money'	=> get_split_money_by_id($goods_id,$num,$spec), /*微分销新增$num.$spec*/
        'goods_attr'    => addslashes($goods_attr),
        'goods_attr_id' => $goods_attr_id,
        'is_real'       => $goods['is_real'],
        'extension_code'=> $goods['extension_code'],
        'is_gift'       => 0,
        'is_shipping'   => $goods['is_shipping'],
	'add_time'   => gmtime(),
        'rec_type'      => CART_GENERAL_GOODS
    );

    /* 如果该配件在添加为基本件的配件时，所设置的“配件价格”比原价低，即此配件在价格上提供了优惠， */
    /* 则按照该配件的优惠价格卖，但是每一个基本件只能购买一个优惠价格的“该配件”，多买的“该配件”不享 */
    /* 受此优惠 */
    $basic_list = array();
    $sql = "SELECT parent_id, goods_price " .
            "FROM " . $GLOBALS['yp']->table('group_goods') .
            " WHERE goods_id = '$goods_id'" .
            " AND goods_price < '$goods_price'" .
            " AND parent_id = '$_parent_id'" .
            " ORDER BY goods_price";
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $basic_list[$row['parent_id']] = $row['goods_price'];
    }

    /* 取得购物车中该商品每个基本件的数量 */
    $basic_count_list = array();
    if ($basic_list)
    {
        $sql = "SELECT goods_id, SUM(goods_number) AS count " .
                "FROM " . $GLOBALS['yp']->table('cart') .
                " WHERE session_id = '" . SESS_ID . "'" .
                " AND parent_id = 0" .
                " AND extension_code <> 'package_buy' " .
                " AND goods_id " . db_create_in(array_keys($basic_list)) .
                " GROUP BY goods_id";
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $basic_count_list[$row['goods_id']] = $row['count'];
        }
    }

    /* 取得购物车中该商品每个基本件已有该商品配件数量，计算出每个基本件还能有几个该商品配件 */
    /* 一个基本件对应一个该商品配件 */
    if ($basic_count_list)
    {
        $sql = "SELECT parent_id, SUM(goods_number) AS count " .
                "FROM " . $GLOBALS['yp']->table('cart') .
                " WHERE session_id = '" . SESS_ID . "'" .
                " AND goods_id = '$goods_id'" .
                " AND extension_code <> 'package_buy' " .
                " AND parent_id " . db_create_in(array_keys($basic_count_list)) .
                " GROUP BY parent_id";
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $basic_count_list[$row['parent_id']] -= $row['count'];
        }
    }

    /* 循环插入配件 如果是配件则用其添加数量依次为购物车中所有属于其的基本件添加足够数量的该配件 */
    foreach ($basic_list as $parent_id => $fitting_price)
    {
        /* 如果已全部插入，退出 */
        if ($num <= 0)
        {
            break;
        }

        /* 如果该基本件不再购物车中，执行下一个 */
        if (!isset($basic_count_list[$parent_id]))
        {
            continue;
        }

        /* 如果该基本件的配件数量已满，执行下一个基本件 */
        if ($basic_count_list[$parent_id] <= 0)
        {
            continue;
        }

        /* 作为该基本件的配件插入 */
        $parent['goods_price']  = max($fitting_price, 0) + $spec_price; //允许该配件优惠价格为0
        $parent['goods_number'] = min($num, $basic_count_list[$parent_id]);
        $parent['parent_id']    = $parent_id;

        /* 添加 */
        $GLOBALS['db']->autoExecute($GLOBALS['yp']->table('cart'), $parent, 'INSERT');

        /* 改变数量 */
        $num -= $parent['goods_number'];
    }

    /* 如果数量不为0，作为基本件插入 */
    if ($num > 0)
    {
        /* 检查该商品是否已经存在在购物车中 */
        $sql = "SELECT goods_number FROM " .$GLOBALS['yp']->table('cart').
                " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$goods_id' ".
                " AND parent_id = 0 AND goods_attr = '" .get_goods_attr_info($spec). "' " .
                " AND extension_code <> 'package_buy' " .
		" AND user_id= '".$_SESSION['user_id']."'".
                " AND rec_type = 'CART_GENERAL_GOODS'";

        $row = $GLOBALS['db']->getRow($sql);

        if($row) //如果购物车已经有此物品，则更新
        {
            $num += $row['goods_number'];
            if(is_spec($spec) && !empty($prod) )
            {
             $goods_storage=$product_info['product_number'];
            }
            else
            {
                $goods_storage=$goods['goods_number'];
            }
            if ($GLOBALS['_CFG']['use_storage'] == 0 || $num <= $goods_storage)
            {
                $goods_price = get_final_price($goods_id, $num, true, $spec);
                $sql = "UPDATE " . $GLOBALS['yp']->table('cart') . " SET goods_number = '$num'" .
                       " , goods_price = '$goods_price'".
                       " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$goods_id' ".
                       " AND parent_id = 0 AND goods_attr = '" .get_goods_attr_info($spec). "' " .
                       " AND extension_code <> 'package_buy' " .
                       "AND rec_type = 'CART_GENERAL_GOODS'";
                $GLOBALS['db']->query($sql);
            }
            else
            {
               $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);

                return false;
            }
        }
        else //购物车没有此物品，则插入
        {
            $goods_price = get_final_price($goods_id, $num, true, $spec);
            $parent['goods_price']  = max($goods_price, 0);
            $parent['goods_number'] = $num;
            $parent['parent_id']    = 0;
            $GLOBALS['db']->autoExecute($GLOBALS['yp']->table('cart'), $parent, 'INSERT');
        }
		
        /**
        * 判断是否为虚拟团购商品
        */
        $virtual_sale_id  = is_virtual_sale_goods($goods_id);
        if(!empty($virtual_sale_id))
        {
            /* 更新：记录购物流程类型：预售 */
            $_SESSION['flow_type'] = CART_VIRTUAL_GROUP_GOODS;
            $_SESSION['extension_code'] = VIRTUAL_SALE_CODE;
            $_SESSION['extension_id'] = $virtual_sale_id;
        	$GLOBALS['db']->query("update ".$GLOBALS['yp']->table('cart')." set rec_type=".CART_VIRTUAL_GROUP_GOODS." WHERE session_id = '" .SESS_ID. "' AND goods_id = '$goods_id' ");   
		}
		
    }

    /* 把赠品删除 */
    $sql = "DELETE FROM " . $GLOBALS['yp']->table('cart') . " WHERE session_id = '" . SESS_ID . "' AND is_gift <> 0";
    $GLOBALS['db']->query($sql);

    return true;
}

//获取商品分成金额
function get_split_money_by_id($goods_id,$num,$spec)/*微分销方法调整，全部替换即可*/
{
	$sql = "SELECT * FROM " . $GLOBALS['yp']->table('ypmart_distrib_goods') . " WHERE goods_id = '$goods_id' AND (distrib_time = 0 OR (start_time <= " . gmtime() . " AND end_time >= " .gmtime()."))";
	$rows = $GLOBALS['db']->getRow($sql);
	if($rows)
	{
		if($rows['distrib_type'] == 1 && $rows['distrib_money'] > 0)
		{
			 $split_money = $rows['distrib_money']; 
		}
		
		if($rows['distrib_type'] == 2 && $rows['distrib_money'] > 0)
		{
			$shop_price = get_final_price($goods_id, $num, true, $spec);
			$split_money = $shop_price*$rows['distrib_money']/100;
		}
		return $split_money;
	}
	else
	{
		return 0; 
	}
}

/**
 * 清空购物车
 * @param   int     $type   类型：默认普通商品
 * @param   string  $other  需要扩展的条件字符串
 */
function clear_cart($type = CART_GENERAL_GOODS,$other='')
{
	$sql_where = $_SESSION['user_id']>0 ? "user_id='". $_SESSION['user_id'] ."' " : "session_id = '" . SESS_ID . "' ";
	$sql = "DELETE FROM " . $GLOBALS['yp']->table('cart') .
            " WHERE $sql_where  AND rec_type = '$type' $other";
    $GLOBALS['db']->query($sql);
}

/**
 * 获得指定的商品属性
 *
 * @access      public
 * @param       array       $arr        规格、属性ID数组
 * @param       type        $type       设置返回结果类型：pice，显示价格，默认；no，不显示价格
 *
 * @return      string
 */
function get_goods_attr_info($arr, $type = 'pice')
{
    $attr   = '';

    if (!empty($arr))
    {
        $fmt = "%s:%s[%s] \n";

        $sql = "SELECT a.attr_name, ga.attr_value, ga.attr_price ".
                "FROM ".$GLOBALS['yp']->table('goods_attr')." AS ga, ".
                    $GLOBALS['yp']->table('attribute')." AS a ".
                "WHERE " .db_create_in($arr, 'ga.goods_attr_id')." AND a.attr_id = ga.attr_id";
        $res = $GLOBALS['db']->query($sql);

        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $attr_price = round(floatval($row['attr_price']), 2);
            $attr .= sprintf($fmt, $row['attr_name'], $row['attr_value'], $attr_price);
        }

        $attr = str_replace('[0]', '', $attr);
    }

    return $attr;
}

/**
 * 取得用户信息
 * @param   int     $user_id    用户id
 * @return  array   用户信息
 */
function user_info($user_id)
{
    $sql = "SELECT * FROM " . $GLOBALS['yp']->table('users') .
            " WHERE user_id = '$user_id'";
    $user = $GLOBALS['db']->getRow($sql);

    unset($user['question']);
    unset($user['answer']);

    /* 格式化帐户余额 */
    if ($user)
    {
//        if ($user['user_money'] < 0)
//        {
//            $user['user_money'] = 0;
//        }
        $user['formated_user_money'] = price_format($user['user_money'], false);
        $user['formated_frozen_money'] = price_format($user['frozen_money'], false);
    }

    return $user;
}

/**
 * 修改用户
 * @param   int     $user_id   订单id
 * @param   array   $user      key => value
 * @return  bool
 */
function update_user($user_id, $user)
{
    return $GLOBALS['db']->autoExecute($GLOBALS['yp']->table('users'),
        $user, 'UPDATE', "user_id = '$user_id'");
}

/**
 * 取得用户地址列表
 * @param   int     $user_id    用户id
 * @return  array
 */
function address_list($user_id)
{
    $sql = "SELECT * FROM " . $GLOBALS['yp']->table('user_address') .
            " WHERE user_id = '$user_id'";

    return $GLOBALS['db']->getAll($sql);
}

/**
 * 取得用户地址信息
 * @param   int     $address_id     地址id
 * @return  array
 */
function address_info($address_id)
{
    $sql = "SELECT * FROM " . $GLOBALS['yp']->table('user_address') .
            " WHERE address_id = '$address_id'";

    return $GLOBALS['db']->getRow($sql);
}

/**
 * 取得用户当前可用红包
 * @param   int     $user_id        用户id
 * @param   array   $goods_amount   订单商品金额(0=>自营总金,1=>店铺一的总金)
 * @return  array   红包数组
 */
function user_bonus($user_id, $goods_amount = 0)
	{
    	$day    = getdate();
    	$today  = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);
	
	foreach($goods_amount as $key=>$val)
	{
    	$sql = "SELECT t.type_id, t.type_name, t.type_money, b.bonus_id, t.supplier_id " .
            "FROM " . $GLOBALS['yp']->table('bonus_type') . " AS t," .
                $GLOBALS['yp']->table('user_bonus') . " AS b " .
            "WHERE t.type_id = b.bonus_type_id " .
            "AND t.use_start_date <= '$today' " .
            "AND t.use_end_date >= '$today' " .
            "AND t.min_goods_amount <= '$val' " .
            "AND b.user_id<>0 " .
            "AND b.user_id = '$user_id' " .
            "AND b.order_id = 0 ".
			"AND t.supplier_id = $key";
			$res[$key]=$GLOBALS['db']->getAll($sql);
			
	}
    	return $res;
	}

/**
 * 取得红包信息
 * @param   int     $bonus_id   红包id
 * @param   string  $bonus_sn   红包序列号
 * @param   array   红包信息
 */
function bonus_info($bonus_id, $bonus_sn = '')
{
    $sql = "SELECT t.*, sum(t.type_money) as type_money, b.* " .
            "FROM " . $GLOBALS['yp']->table('bonus_type') . " AS t," .
                $GLOBALS['yp']->table('user_bonus') . " AS b " .
            "WHERE t.type_id = b.bonus_type_id ";
   
    if (!empty($bonus_id))
    {
        $sql .= "AND b.bonus_id in(".$bonus_id.")";
    }
    if(!empty($bonus_sn))
    {
        $sql .= "AND b.bonus_sn in(".$bonus_sn.")";
    }

    return $GLOBALS['db']->getRow($sql);
}

/**
 * 检查红包是否已使用
 * @param   int $bonus_id   红包id
 * @return  bool
 */
function bonus_used($bonus_id)
{
    $sql = "SELECT order_id FROM " . $GLOBALS['yp']->table('user_bonus') .
            " WHERE bonus_id = '$bonus_id'";

    return  $GLOBALS['db']->getOne($sql) > 0;
}

/**
 * 设置红包为已使用
 * @param   int     $bonus_id   红包id
 * @param   int     $order_id   订单id
 * @return  bool
 */
function use_bonus($bonus_id, $order_id)
{
    $sql = "UPDATE " . $GLOBALS['yp']->table('user_bonus') .
            " SET order_id = '$order_id', used_time = '" . gmtime() . "' " .
            "WHERE bonus_id = '$bonus_id' LIMIT 1";

    return  $GLOBALS['db']->query($sql);
}

/**
 * 设置红包为未使用
 * @param   int     $bonus_id   红包id
 * @param   int     $order_id   订单id
 * @return  bool
 */
function unuse_bonus($bonus_id)
{
    $sql = "UPDATE " . $GLOBALS['yp']->table('user_bonus') .
            " SET order_id = 0, used_time = 0 " .
            "WHERE bonus_id = '$bonus_id' LIMIT 1";

    return  $GLOBALS['db']->query($sql);
}

/**
 * 计算积分的价值（能抵多少钱）
 * @param   int     $integral   积分
 * @return  float   积分价值
 */
function value_of_integral($integral)
{
    $scale = floatval($GLOBALS['_CFG']['integral_scale']);

    return $scale > 0 ? round(($integral / 100) * $scale, 2) : 0;
}

/**
 * 计算指定的金额需要多少积分
 *
 * @access  public
 * @param   integer $value  金额
 * @return  void
 */
function integral_of_value($value)
{
    $scale = floatval($GLOBALS['_CFG']['integral_scale']);

    return $scale > 0 ? round($value / $scale * 100) : 0;
}

/**
 * 订单退款
 * @param   array   $order          订单
 * @param   int     $refund_type    退款方式 1 到帐户余额 2 到退款申请（先到余额，再申请提款） 3 不处理
 * @param   string  $refund_note    退款说明
 * @param   float   $refund_amount  退款金额（如果为0，取订单已付款金额）
 * @return  bool
 */
function order_refund($order, $refund_type, $refund_note, $refund_amount = 0)
{
    /* 检查参数 */
    $user_id = $order['user_id'];
    if ($user_id == 0 && $refund_type == 1)
    {
        die('anonymous, cannot return to account balance');
    }

    $amount = $refund_amount > 0 ? $refund_amount : $order['money_paid'];
    if ($amount <= 0)
    {
        return true;
    }

    if (!in_array($refund_type, array(1, 2, 3)))
    {
        die('invalid params');
    }

    /* 备注信息 */
    if ($refund_note)
    {
        $change_desc = $refund_note;
    }
    else
    {
        include_once(ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/admin/order.php');
        $change_desc = sprintf($GLOBALS['_LANG']['order_refund'], $order['order_sn']);
    }

    /* 处理退款 */
    if (1 == $refund_type)
    {
        log_account_change($user_id, $amount, 0, 0, 0, $change_desc);

        return true;
    }
    elseif (2 == $refund_type)
    {
        /* 如果非匿名，退回余额 */
        if ($user_id > 0)
        {
            log_account_change($user_id, $amount, 0, 0, 0, $change_desc);
        }

        /* user_account 表增加提款申请记录 */
        $account = array(
            'user_id'      => $user_id,
            'amount'       => (-1) * $amount,
            'add_time'     => gmtime(),
            'user_note'    => $refund_note,
            'process_type' => SURPLUS_RETURN,
            'admin_user'   => $_SESSION['admin_name'],
            'admin_note'   => sprintf($GLOBALS['_LANG']['order_refund'], $order['order_sn']),
            'is_paid'      => 0
        );
        $GLOBALS['db']->autoExecute($GLOBALS['yp']->table('user_account'), $account, 'INSERT');

        return true;
    }
    else
    {
        return true;
    }
}

/**
 * 获得购物车中的商品
 *
 * @access  public
 * @return  array
 */
function get_cart_goods($other='')
{
    /* 初始化 */
    $goods_list = array();
    $total = array(
        'goods_price'  => 0, // 本店售价合计（有格式）
        'market_price' => 0, // 市场售价合计（有格式）
        'saving'       => 0, // 节省金额（有格式）
        'save_rate'    => 0, // 节省百分比
        'goods_amount' => 0, // 本店售价合计（无格式）
    );

    /* 循环、统计 */
	$sql_where = $_SESSION['user_id']>0 ? "c.user_id='". $_SESSION['user_id'] ."' " : "c.session_id = '" . SESS_ID . "' AND c.user_id=0 ";
	$sql = "SELECT c.*, g.cat_id, g.brand_id, c.extension_code, IF(ga.act_id, ga.supplier_id, g.supplier_id) as supplier_id, IF(c.parent_id, c.parent_id, c.goods_id) AS pid  " .
            " FROM " . $GLOBALS['yp']->table('cart') . " AS c left join " .$GLOBALS['yp']->table('goods')." AS g ".
			" on c.goods_id=g.goods_id ".
			" left join " . $GLOBALS['yp']->table('goods_activity') . " as ga " .
			" on ga.act_id = c.goods_id and c.extension_code = 'package_buy'" .
			" WHERE $sql_where AND c.rec_type = '" . CART_GENERAL_GOODS . "' $other " .
            " ORDER BY pid, c.parent_id";
			
    $res = $GLOBALS['db']->query($sql);

    /* 用于统计购物车中实体商品和虚拟商品的个数 */
    $virtual_goods_count = 0;
    $real_goods_count    = 0;

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $total['goods_price']  += $row['goods_price'] * $row['goods_number'];
        $total['market_price'] += $row['market_price'] * $row['goods_number'];

        $row['subtotal']     = price_format($row['goods_price'] * $row['goods_number'], false);
        $row['goods_price']  = price_format($row['goods_price'], false);
        $row['market_price'] = price_format($row['market_price'], false);

        /* 统计实体商品和虚拟商品的个数 */
        if ($row['is_real'])
        {
            $real_goods_count++;
        }
        else
        {
            $virtual_goods_count++;
        }

        /* 查询规格 */
        if (trim($row['goods_attr']) != '')
        {
            $row['goods_attr']=addslashes($row['goods_attr']);
            $sql = "SELECT attr_value FROM " . $GLOBALS['yp']->table('goods_attr') . " WHERE goods_attr_id " .
            db_create_in($row['goods_attr']);
            $attr_list = $GLOBALS['db']->getCol($sql);
            foreach ($attr_list AS $attr)
            {
                $row['goods_name'] .= ' [' . $attr . '] ';
            }
        }
        /* 增加是否在购物车里显示商品图 */
        if (($GLOBALS['_CFG']['show_goods_in_cart'] == "2" || $GLOBALS['_CFG']['show_goods_in_cart'] == "3") && $row['extension_code'] != 'package_buy')
        {
            $goods_thumb = $GLOBALS['db']->getOne("SELECT `goods_thumb` FROM " . $GLOBALS['yp']->table('goods') . " WHERE `goods_id`='{$row['goods_id']}'");
            $row['goods_thumb'] = get_image_path($row['goods_id'], $goods_thumb, true);
        }
        if ($row['extension_code'] == 'package_buy')
        {
            $row['package_goods_list'] = get_package_goods($row['goods_id'], $row['package_attr_id'] ); //修改 by demo.coolhong.com 今天优品多商户系统 Q Q 1200 2912 1 增加一个变量
        }
	$row['is_cansel'] = is_cansel($row['goods_id'], $row['product_id'], $row['package_buy']);

	if($row['supplier_id'])
	{
		$supplier_name = $GLOBALS['db']->getOne("select supplier_name from ". $GLOBALS['yp']->table('supplier') ." where supplier_id='". $row['supplier_id']."' ");
		$supplier_name = $supplier_name;
	}
	else
	{
		$supplier_name = '网站自营';
	}

	$keyname = $row['supplier_id'] ? $row['supplier_id'] : '0' ;
	$goods_list[$keyname]['goods_list'][] = $row;
	$goods_list[$keyname]['supplier_name'] = $supplier_name;
	ksort($goods_list);
		
	//$goods_list[] = $row;
    }
    $total['goods_amount'] = $total['goods_price'];
    $total['saving']       = price_format($total['market_price'] - $total['goods_price'], false);
    if ($total['market_price'] > 0)
    {
        $total['save_rate'] = $total['market_price'] ? round(($total['market_price'] - $total['goods_price']) *
        100 / $total['market_price']).'%' : 0;
    }

    $total['goods_price']  = price_format($total['goods_price'], false);
    $total['market_price'] = price_format($total['market_price'], false);
    $total['real_goods_count']    = $real_goods_count;
    $total['virtual_goods_count'] = $virtual_goods_count;
    return array('goods_list' => $goods_list, 'total' => $total);
}

/**
 * 取得收货人信息
 * @param   int     $user_id    用户编号
 * @return  array
 */
function get_consignee($user_id)
{
    if (isset($_SESSION['flow_consignee']))
    {
        /* 如果存在session，则直接返回session中的收货人信息 */

        return $_SESSION['flow_consignee'];
    }
    else
    {
        /* 如果不存在，则取得用户的默认收货人信息 */
        $arr = array();

        if ($user_id > 0)
        {
            /* 取默认地址 */
            $sql = "SELECT ua.*".
                    " FROM " . $GLOBALS['yp']->table('user_address') . "AS ua, ".$GLOBALS['yp']->table('users').' AS u '.
                    " WHERE u.user_id='$user_id' AND ua.address_id = u.address_id";

            $arr = $GLOBALS['db']->getRow($sql);
        }

        return $arr;
    }
}

/**
 * 查询购物车（订单id为0）或订单中是否有实体商品
 * @param   int     $order_id   订单id
 * @param   int     $flow_type  购物流程类型
 * @return  bool
 */
function exist_real_goods($order_id = 0, $flow_type = CART_GENERAL_GOODS)
{
    if ($order_id <= 0)
    {
		$sql_where = $_SESSION['user_id']>0 ? "user_id='". $_SESSION['user_id'] ."' " : "session_id = '" . SESS_ID . "' AND user_id=0 ";
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['yp']->table('cart') .
                " WHERE " . $sql_where . " AND is_real = 1 " .
                "AND rec_type = '$flow_type'";
    }
    else
    {
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['yp']->table('order_goods') .
                " WHERE order_id = '$order_id' AND is_real = 1";
    }

    return $GLOBALS['db']->getOne($sql) > 0;
}

/**
 * 检查收货人信息是否完整
 * @param   array   $consignee  收货人信息
 * @param   int     $flow_type  购物流程类型
 * @return  bool    true 完整 false 不完整
 */
function check_consignee_info($consignee, $flow_type)
{
    if (exist_real_goods(0, $flow_type))
    {
        /* 如果存在实体商品 */
        $res = !empty($consignee['consignee']) &&
            !empty($consignee['country']) ;

        if ($res)
        {
            if (empty($consignee['province']))
            {
                /* 没有设置省份，检查当前国家下面有没有设置省份 */
                $pro = get_regions(1, $consignee['country']);
                $res = empty($pro);
            }
            elseif (empty($consignee['city']))
            {
                /* 没有设置城市，检查当前省下面有没有城市 */
                $city = get_regions(2, $consignee['province']);
                $res = empty($city);
            }
            elseif (empty($consignee['district']))
            {
                $dist = get_regions(3, $consignee['city']);
                $res = empty($dist);
            }
        }

        return $res;
    }
    else
    {
        /* 如果不存在实体商品 */
        return !empty($consignee['consignee']) ;
    }
}

/**
 * 获得上一次用户采用的支付和配送方式
 *
 * @access  public
 * @return  void
 */
function last_shipping_and_payment()
{
    $sql = "SELECT shipping_id, pay_id " .
            " FROM " . $GLOBALS['yp']->table('order_info') .
            " WHERE user_id = '$_SESSION[user_id]' " .
            " ORDER BY order_id DESC LIMIT 1";
    $row = $GLOBALS['db']->getRow($sql);

    if (empty($row))
    {
        /* 如果获得是一个空数组，则返回默认值 */
        $row = array('shipping_id' => 0, 'pay_id' => 0);
    }

    return $row;
}

/**
 * 取得当前用户应该得到的红包总额
 * @param array $supplier_money_info 各个店铺对应的商品的总钱信息
 */
function get_total_bonus($supplier_money_info='')
{
    $day    = getdate();
    $today  = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);

	$sql_where = $_SESSION['user_id']>0 ? "c.user_id='". $_SESSION['user_id'] ."' " : "c.session_id = '" . SESS_ID . "' AND c.user_id=0 ";
    

	$sql_where1 = $_SESSION['user_id']>0 ? "user_id='". $_SESSION['user_id'] ."' " : "session_id = '" . SESS_ID . "' AND user_id=0 ";

    /* 取得购物车中非赠品总金额 */
	if(!is_array($supplier_money_info)){
		/* 按商品发的红包 */
        if($_SESSION['sel_cartgoods']){
            $sql_plus = "AND c.rec_id in (".$_SESSION['sel_cartgoods'].") ";
        }
		$sql = "SELECT SUM(c.goods_number * t.type_money)" .
				"FROM " . $GLOBALS['yp']->table('cart') . " AS c, "
						. $GLOBALS['yp']->table('bonus_type') . " AS t, "
						. $GLOBALS['yp']->table('goods') . " AS g " .
				"WHERE $sql_where " .
				"AND c.is_gift = 0 " .
				"AND c.goods_id = g.goods_id " .
				"AND g.bonus_type_id = t.type_id " .
				"AND t.send_type = '" . SEND_BY_GOODS . "' " .
				"AND t.send_start_date <= '$today' " .
				"AND t.send_end_date >= '$today' " .
				$sql_plus.
				" AND c.rec_type = '" . CART_GENERAL_GOODS . "'";
		$goods_total = floatval($GLOBALS['db']->getOne($sql));

        if($_SESSION['sel_cartgoods']){
            $sql_plus = " AND rec_id in (".$_SESSION['sel_cartgoods'].") ";
        }
		$sql = "SELECT SUM(goods_price * goods_number) " .
				"FROM " . $GLOBALS['yp']->table('cart') .
				" WHERE $sql_where1 " .
				" AND is_gift = 0 " .
				$sql_plus .
				" AND rec_type = '" . CART_GENERAL_GOODS . "'";
		$amount = floatval($GLOBALS['db']->getOne($sql));

		/* 按订单发的红包 */
		$sql = "SELECT FLOOR('$amount' / min_amount) * type_money " .
				"FROM " . $GLOBALS['yp']->table('bonus_type') .
				" WHERE send_type = '" . SEND_BY_ORDER . "' " .
				" AND send_start_date <= '$today' " .
				"AND send_end_date >= '$today' " .
				"AND min_amount > 0 ";
		$order_total = floatval($GLOBALS['db']->getOne($sql));
	}else{
		$order_total = $goods_total = 0;
        if($_SESSION['sel_cartgoods']){
            $sql_plus = " AND c.rec_id in (".$_SESSION['sel_cartgoods'].") ";
        }
		foreach($supplier_money_info as $key => $val){

			/* 按商品发的红包 */
			$sql = "SELECT SUM(c.goods_number * t.type_money)" .
					"FROM " . $GLOBALS['yp']->table('cart') . " AS c, "
							. $GLOBALS['yp']->table('bonus_type') . " AS t, "
							. $GLOBALS['yp']->table('goods') . " AS g " .
					"WHERE $sql_where " .
					"AND c.is_gift = 0 " .
					"AND c.goods_id = g.goods_id " .
					"AND t.supplier_id = g.supplier_id " .
					"AND g.bonus_type_id = t.type_id " .
					"AND t.send_type = '" . SEND_BY_GOODS . "' " .
					"AND t.send_start_date <= '$today' " .
					"AND t.send_end_date >= '$today' " .
					"AND g.supplier_id = ".$key.
					$sql_plus.
					" AND c.rec_type = '" . CART_GENERAL_GOODS . "'";
			$goods_total += $GLOBALS['db']->getOne($sql);

			$sql = "SELECT FLOOR('$val' / min_amount) * type_money " .
				"FROM " . $GLOBALS['yp']->table('bonus_type') .
				" WHERE send_type = '" . SEND_BY_ORDER . "' " .
				" AND send_start_date <= '$today' " .
				"AND send_end_date >= '$today' " .
				" AND supplier_id = ".$key.
				" AND min_amount > 0 ";
			$order_total += $GLOBALS['db']->getOne($sql);
		}
		$goods_total = floatval($goods_total);
		$order_total = floatval($order_total);
	}
	

    return $goods_total + $order_total;
}

/**
 * 处理红包（下订单时设为使用，取消（无效，退货）订单时设为未使用
 * @param   int     $bonus_id   红包编号
 * @param   int     $order_id   订单号
 * @param   int     $is_used    是否使用了
 */
function change_user_bonus($bonus_id, $order_id, $is_used = true)
{
    if ($is_used)
    {
        $sql = 'UPDATE ' . $GLOBALS['yp']->table('user_bonus') . ' SET ' .
                'used_time = ' . gmtime() . ', ' .
                "order_id = '$order_id' " .
                "WHERE bonus_id = '$bonus_id'";
    }
    else
    {
        $sql = 'UPDATE ' . $GLOBALS['yp']->table('user_bonus') . ' SET ' .
                'used_time = 0, ' .
                'order_id = 0 ' .
                "WHERE bonus_id = '$bonus_id'";
    }
    $GLOBALS['db']->query($sql);
}

/**
 * 获得订单信息
 *
 * @access  private
 * @return  array
 */
function flow_order_info()
{
    $order = isset($_SESSION['flow_order']) ? $_SESSION['flow_order'] : array();

    /* 初始化配送和支付方式 */
    if (!isset($order['shipping_id']) || !isset($order['pay_id']))
    {
        /* 如果还没有设置配送和支付 */
        if ($_SESSION['user_id'] > 0)
        {
            /* 用户已经登录了，则获得上次使用的配送和支付 */
            $arr = last_shipping_and_payment();

            if (!isset($order['shipping_id']))
            {
                $order['shipping_id'] = $arr['shipping_id'];
            }
            if (!isset($order['pay_id']))
            {
                $order['pay_id'] = $arr['pay_id'];
            }
        }
        else
        {
            if (!isset($order['shipping_id']))
            {
                $order['shipping_id'] = 0;
            }
            if (!isset($order['pay_id']))
            {
                $order['pay_id'] = 0;
            }
        }
    }

    if (!isset($order['pack_id']))
    {
        $order['pack_id'] = 0;  // 初始化包装
    }
    if (!isset($order['card_id']))
    {
        $order['card_id'] = 0;  // 初始化贺卡
    }
    if (!isset($order['bonus']))
    {
        $order['bonus'] = 0;    // 初始化红包
    }
    if (!isset($order['integral']))
    {
        $order['integral'] = 0; // 初始化积分
    }
    if (!isset($order['surplus']))
    {
        $order['surplus'] = 0;  // 初始化余额
    }

    /* 扩展信息 */
    if (isset($_SESSION['flow_type']) && intval($_SESSION['flow_type']) != CART_GENERAL_GOODS)
    {
        $order['extension_code'] = $_SESSION['extension_code'];
        $order['extension_id'] = $_SESSION['extension_id'];
    }

    return $order;
}

/**
 * 合并订单
 * @param   string  $from_order_sn  从订单号
 * @param   string  $to_order_sn    主订单号
 * @return  成功返回true，失败返回错误信息
 */
function merge_order($from_order_sn, $to_order_sn)
{
    /* 订单号不能为空 */
    if (trim($from_order_sn) == '' || trim($to_order_sn) == '')
    {
        return $GLOBALS['_LANG']['order_sn_not_null'];
    }

    /* 订单号不能相同 */
    if ($from_order_sn == $to_order_sn)
    {
        return $GLOBALS['_LANG']['two_order_sn_same'];
    }

    /* 取得订单信息 */
    $from_order = order_info(0, $from_order_sn);
    $to_order   = order_info(0, $to_order_sn);

    /* 检查订单是否存在 */
    if (!$from_order)
    {
        return sprintf($GLOBALS['_LANG']['order_not_exist'], $from_order_sn);
    }
    elseif (!$to_order)
    {
        return sprintf($GLOBALS['_LANG']['order_not_exist'], $to_order_sn);
    }

    /* 检查合并的订单是否为普通订单，非普通订单不允许合并 */
    if ($from_order['extension_code'] != '' || $to_order['extension_code'] != 0)
    {
        return $GLOBALS['_LANG']['merge_invalid_order'];
    }

    /* 检查订单状态是否是已确认或未确认、未付款、未发货 */
    if ($from_order['order_status'] != OS_UNCONFIRMED && $from_order['order_status'] != OS_CONFIRMED)
    {
        return sprintf($GLOBALS['_LANG']['os_not_unconfirmed_or_confirmed'], $from_order_sn);
    }
    elseif ($from_order['pay_status'] != PS_UNPAYED)
    {
        return sprintf($GLOBALS['_LANG']['ps_not_unpayed'], $from_order_sn);
    }
    elseif ($from_order['shipping_status'] != SS_UNSHIPPED)
    {
        return sprintf($GLOBALS['_LANG']['ss_not_unshipped'], $from_order_sn);
    }

    if ($to_order['order_status'] != OS_UNCONFIRMED && $to_order['order_status'] != OS_CONFIRMED)
    {
        return sprintf($GLOBALS['_LANG']['os_not_unconfirmed_or_confirmed'], $to_order_sn);
    }
    elseif ($to_order['pay_status'] != PS_UNPAYED)
    {
        return sprintf($GLOBALS['_LANG']['ps_not_unpayed'], $to_order_sn);
    }
    elseif ($to_order['shipping_status'] != SS_UNSHIPPED)
    {
        return sprintf($GLOBALS['_LANG']['ss_not_unshipped'], $to_order_sn);
    }

    /* 检查订单用户是否相同 */
    if ($from_order['user_id'] != $to_order['user_id'])
    {
        return $GLOBALS['_LANG']['order_user_not_same'];
    }

    /* 合并订单 */
    $order = $to_order;
    $order['order_id']  = '';
    $order['add_time']  = gmtime();

    // 合并商品总额
    $order['goods_amount'] += $from_order['goods_amount'];

    // 合并折扣
    $order['discount'] += $from_order['discount'];

    if ($order['shipping_id'] > 0)
    {
        // 重新计算配送费用
        $weight_price       = order_weight_price($to_order['order_id']);
        $from_weight_price  = order_weight_price($from_order['order_id']);
        $weight_price['weight'] += $from_weight_price['weight'];
        $weight_price['amount'] += $from_weight_price['amount'];
        $weight_price['number'] += $from_weight_price['number'];

        $region_id_list = array($order['country'], $order['province'], $order['city'], $order['district']);
        $shipping_area = shipping_area_info($order['shipping_id'], $region_id_list);

        $order['shipping_fee'] = shipping_fee($shipping_area['shipping_code'],
            unserialize($shipping_area['configure']), $weight_price['weight'], $weight_price['amount'], $weight_price['number']);

        // 如果保价了，重新计算保价费
        if ($order['insure_fee'] > 0)
        {
            $order['insure_fee'] = shipping_insure_fee($shipping_area['shipping_code'], $order['goods_amount'], $shipping_area['insure']);
        }
    }

    // 重新计算包装费、贺卡费
    if ($order['pack_id'] > 0)
    {
        $pack = pack_info($order['pack_id']);
        $order['pack_fee'] = $pack['free_money'] > $order['goods_amount'] ? $pack['pack_fee'] : 0;
    }
    if ($order['card_id'] > 0)
    {
        $card = card_info($order['card_id']);
        $order['card_fee'] = $card['free_money'] > $order['goods_amount'] ? $card['card_fee'] : 0;
    }

    // 红包不变，合并积分、余额、已付款金额
    $order['integral']      += $from_order['integral'];
    $order['integral_money'] = value_of_integral($order['integral']);
    $order['surplus']       += $from_order['surplus'];
    $order['money_paid']    += $from_order['money_paid'];

    // 计算应付款金额（不包括支付费用）
    $order['order_amount'] = $order['goods_amount'] - $order['discount']
                           + $order['shipping_fee']
                           + $order['insure_fee']
                           + $order['pack_fee']
                           + $order['card_fee']
                           - $order['bonus']
                           - $order['integral_money']
                           - $order['surplus']
                           - $order['money_paid'];

    // 重新计算支付费
    if ($order['pay_id'] > 0)
    {
        // 货到付款手续费
        $cod_fee          = $shipping_area ? $shipping_area['pay_fee'] : 0;
        $order['pay_fee'] = pay_fee($order['pay_id'], $order['order_amount'], $cod_fee);

        // 应付款金额加上支付费
        $order['order_amount'] += $order['pay_fee'];
    }

    /* 插入订单表 */
    do
    {
        $order['order_sn'] = get_order_sn();
        if ($GLOBALS['db']->autoExecute($GLOBALS['yp']->table('order_info'), addslashes_deep($order), 'INSERT'))
        {
            break;
        }
        else
        {
            if ($GLOBALS['db']->errno() != 1062)
            {
                die($GLOBALS['db']->errorMsg());
            }
        }
    }
    while (true); // 防止订单号重复

    /* 订单号 */
    $order_id = $GLOBALS['db']->insert_id();

    /* 更新订单商品 */
    $sql = 'UPDATE ' . $GLOBALS['yp']->table('order_goods') .
            " SET order_id = '$order_id' " .
            "WHERE order_id " . db_create_in(array($from_order['order_id'], $to_order['order_id']));
    $GLOBALS['db']->query($sql);

    include_once(ROOT_PATH . 'includes/lib_clips.php');
    /* 插入支付日志 */
    insert_pay_log($order_id, $order['order_amount'], PAY_ORDER);

    /* 删除原订单 */
    $sql = 'DELETE FROM ' . $GLOBALS['yp']->table('order_info') .
            " WHERE order_id " . db_create_in(array($from_order['order_id'], $to_order['order_id']));
    $GLOBALS['db']->query($sql);

    /* 删除原订单支付日志 */
    $sql = 'DELETE FROM ' . $GLOBALS['yp']->table('pay_log') .
            " WHERE order_id " . db_create_in(array($from_order['order_id'], $to_order['order_id']));
    $GLOBALS['db']->query($sql);

    /* 返还 from_order 的红包，因为只使用 to_order 的红包 */
    if ($from_order['bonus_id'] > 0)
    {
        unuse_bonus($from_order['bonus_id']);
    }

    /* 返回成功 */
    return true;
}

/**
 * 查询配送区域属于哪个办事处管辖
 * @param   array   $regions    配送区域（1、2、3、4级按顺序）
 * @return  int     办事处id，可能为0
 */
function get_agency_by_regions($regions)
{
    if (!is_array($regions) || empty($regions))
    {
        return 0;
    }

    $arr = array();
    $sql = "SELECT region_id, agency_id " .
            "FROM " . $GLOBALS['yp']->table('region') .
            " WHERE region_id " . db_create_in($regions) .
            " AND region_id > 0 AND agency_id > 0";
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $arr[$row['region_id']] = $row['agency_id'];
    }
    if (empty($arr))
    {
        return 0;
    }

    $agency_id = 0;
    for ($i = count($regions) - 1; $i >= 0; $i--)
    {
        if (isset($arr[$regions[$i]]))
        {
            return $arr[$regions[$i]];
        }
    }
}

/**
 * 获取配送插件的实例
 * @param   int   $shipping_id    配送插件ID
 * @return  object     配送插件对象实例
 */
function &get_shipping_object($shipping_id)
{
    $shipping  = shipping_info($shipping_id);
    if (!$shipping)
    {
        $object = new stdClass();
        return $object;
    }

    $file_path = ROOT_PATH.'../includes/modules/shipping/' . $shipping['shipping_code'] . '.php';

    include_once($file_path);

    $object = new $shipping['shipping_code'];
    return $object;
}

/**
 * 改变订单中商品库存
 * @param   int     $order_id   订单号
 * @param   bool    $is_dec     是否减少库存
 * @param   bool    $storage     减库存的时机，1，下订单时；0，发货时；
 */
function change_order_goods_storage($order_id, $is_dec = true, $storage = 0)
{
    /* 查询订单商品信息 */
    switch ($storage)
    {
        case 0 :
            $sql = "SELECT goods_id, SUM(send_number) AS num, MAX(extension_code) AS extension_code, product_id FROM " . $GLOBALS['yp']->table('order_goods') .
                    " WHERE order_id = '$order_id' AND is_real = 1 GROUP BY goods_id, product_id";
        break;

        case 1 :
            $sql = "SELECT goods_id, SUM(goods_number) AS num, MAX(extension_code) AS extension_code, product_id FROM " . $GLOBALS['yp']->table('order_goods') .
                    " WHERE order_id = '$order_id' AND is_real = 1 GROUP BY goods_id, product_id";
        break;
    }

    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        if ($row['extension_code'] != "package_buy")
        {
            if ($is_dec)
            {
                change_goods_storage($row['goods_id'], $row['product_id'], - $row['num']);
            }
            else
            {
                change_goods_storage($row['goods_id'], $row['product_id'], $row['num']);
            }
            $GLOBALS['db']->query($sql);
        }
        else
        {
            $sql = "SELECT goods_id, goods_number" .
                   " FROM " . $GLOBALS['yp']->table('package_goods') .
                   " WHERE package_id = '" . $row['goods_id'] . "'";
            $res_goods = $GLOBALS['db']->query($sql);
            while ($row_goods = $GLOBALS['db']->fetchRow($res_goods))
            {
                $sql = "SELECT is_real" .
                   " FROM " . $GLOBALS['yp']->table('goods') .
                   " WHERE goods_id = '" . $row_goods['goods_id'] . "'";
                $real_goods = $GLOBALS['db']->query($sql);
                $is_goods = $GLOBALS['db']->fetchRow($real_goods);

                if ($is_dec)
                {
                    change_goods_storage($row_goods['goods_id'], $row['product_id'], - ($row['num'] * $row_goods['goods_number']));
                }
                elseif ($is_goods['is_real'])
                {
                    change_goods_storage($row_goods['goods_id'], $row['product_id'], ($row['num'] * $row_goods['goods_number']));
                }
            }
        }
    }

}

/**
 * 商品库存增与减 货品库存增与减
 *
 * @param   int    $good_id         商品ID
 * @param   int    $product_id      货品ID
 * @param   int    $number          增减数量，默认0；
 *
 * @return  bool               true，成功；false，失败；
 */
function change_goods_storage($good_id, $product_id, $number = 0)
{
    if ($number == 0)
    {
        return true; // 值为0即不做、增减操作，返回true
    }

    if (empty($good_id) || empty($number))
    {
        return false;
    }

    $number = ($number > 0) ? '+ ' . $number : $number;

    /* 处理货品库存 */
    $products_query = true;
    if (!empty($product_id))
    {
        $sql = "UPDATE " . $GLOBALS['yp']->table('products') ."
                SET product_number = product_number $number
                WHERE goods_id = '$good_id'
                AND product_id = '$product_id'
                LIMIT 1";
        $products_query = $GLOBALS['db']->query($sql);
    }

    /* 处理商品库存 */
    $sql = "UPDATE " . $GLOBALS['yp']->table('goods') ."
            SET goods_number = goods_number $number
            WHERE goods_id = '$good_id'
            LIMIT 1";
    $query = $GLOBALS['db']->query($sql);

    if ($query && $products_query)
    {
        return true;
    }
    else
    {
        return false;
    }
}

/**
 * 取得支付方式id列表
 * @param   bool    $is_cod 是否货到付款
 * @return  array
 */
function payment_id_list($is_cod)
{
    $sql = "SELECT pay_id FROM " . $GLOBALS['yp']->table('payment');
    if ($is_cod)
    {
        $sql .= " WHERE is_cod = 1";
    }
    else
    {
        $sql .= " WHERE is_cod = 0";
    }

    return $GLOBALS['db']->getCol($sql);
}

/**
 * 生成查询订单的sql
 * @param   string  $type   类型
 * @param   string  $alias  order表的别名（包括.例如 o.）
 * @return  string
 */
function order_query_sql($type = 'finished', $alias = '')
{
  /* 已完成订单 */
    if ($type == 'finished')
    {
        return " AND {$alias}order_status " . db_create_in(array(OS_CONFIRMED, OS_SPLITED)) .
               " AND {$alias}shipping_status " . db_create_in(array(SS_RECEIVED)) .
               " AND {$alias}pay_status " . db_create_in(array(PS_PAYED, PS_PAYING)) . " ";
    }
    /* 待发货订单 */
    elseif ($type == 'await_ship')
    {
        return " AND   {$alias}order_status " .
                 db_create_in(array(OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART)) .
               " AND   {$alias}shipping_status " .
                 db_create_in(array(SS_UNSHIPPED, SS_PREPARING, SS_SHIPPED_ING)) .
               " AND ( {$alias}pay_status " . db_create_in(array(PS_PAYED, PS_PAYING)) . " OR {$alias}pay_id " . db_create_in(payment_id_list(true)) . ") ";
    }
    /* 待付款订单 */
    elseif ($type == 'await_pay')
    {
        return " AND   {$alias}order_status " . db_create_in(array(OS_CONFIRMED, OS_UNCONFIRMED)) .
               " AND   {$alias}pay_status = '" . PS_UNPAYED . "'" .
               " AND ( {$alias}shipping_status " . db_create_in(array(SS_SHIPPED, SS_RECEIVED)) . " OR {$alias}pay_id " . db_create_in(payment_id_list(false)) . ") ";
    }
    /* 未确认订单 */
    elseif ($type == 'unconfirmed')
    {
        return " AND {$alias}order_status = '" . OS_UNCONFIRMED . "' ";
    }
    /* 未处理订单：用户可操作 */
    elseif ($type == 'unprocessed')
    {
        return " AND {$alias}order_status " . db_create_in(array(OS_UNCONFIRMED, OS_CONFIRMED)) .
               " AND {$alias}shipping_status = '" . SS_UNSHIPPED . "'" .
               " AND {$alias}pay_status = '" . PS_UNPAYED . "' ";
    }
    /* 未付款未发货订单：管理员可操作 */
    elseif ($type == 'unpay_unship')
    {
        return " AND {$alias}order_status " . db_create_in(array(OS_UNCONFIRMED, OS_CONFIRMED)) .
               " AND {$alias}shipping_status " . db_create_in(array(SS_UNSHIPPED, SS_PREPARING)) .
               " AND {$alias}pay_status = '" . PS_UNPAYED . "' ";
    }
    /* 已发货订单：不论是否付款 */
    elseif ($type == 'shipped')
    {
        return " AND {$alias}order_status = '" . OS_CONFIRMED . "'" .
               " AND {$alias}shipping_status " . db_create_in(array(SS_SHIPPED, SS_RECEIVED)) . " ";
    }
    /* 已取消订单 */
    elseif ($type == 'canceled')
    {
        return " AND {$alias}order_status " . db_create_in(array(OS_CANCELED, OS_INVALID)) . " ";
    }
    /* 退款中订单 */
    elseif ($type == 'payback')
    {
        return " AND {$alias}pay_status = '" . PS_PAYBACK . "' ";
    }	
    /* 待评论订单 */
    elseif ($type == 'await_comment')
    {
		$a_comment = implode(',',$GLOBALS['db']->getRow("select distinct(order_id) from " . $GLOBALS['yp']->table('order_goods') . " where comment_state = 0 or shaidan_state = 0"));
		if (!empty($a_comment))
		{
			return " AND {$alias}shipping_status = '" . SS_RECEIVED . "' " .
				   " AND {$alias}order_id in (" . $a_comment . ") ";
		}
    }
    /* 待收货订单 */
    elseif ($type == 'await_receipt'){
        return " AND {$alias}order_status ". db_create_in(array(OS_CONFIRMED, OS_SPLITED)) .
               " AND {$alias}shipping_status = '" . SS_SHIPPED . "' ";
    }
    else
    {
        die('函数 order_query_sql 参数错误');
    }
}

/**
 * 生成查询订单总金额的字段
 * @param   string  $alias  order表的别名（包括.例如 o.）
 * @return  string
 */
function order_amount_field($alias = '')
{
    return "   {$alias}goods_amount + {$alias}tax + {$alias}shipping_fee" .
           " + {$alias}insure_fee + {$alias}pay_fee + {$alias}pack_fee" .
           " + {$alias}card_fee ";
}

/**
 * 生成计算应付款金额的字段
 * @param   string  $alias  order表的别名（包括.例如 o.）
 * @return  string
 */
function order_due_field($alias = '')
{
    return order_amount_field($alias) .
            " - {$alias}money_paid - {$alias}surplus - {$alias}integral_money" .
            " - {$alias}bonus - {$alias}discount ";
}

/**
 * 计算折扣：根据购物车和优惠活动
 * @param int $supplierid  店铺id
 * @return  float   折扣
 */
function compute_discount($supplierid=-1)
{
    /* 查询优惠活动 */
    $now = gmtime();
    $user_rank = ',' . $_SESSION['user_rank'] . ',';
    $sql = "SELECT *" .
            "FROM " . $GLOBALS['yp']->table('favourable_activity') .
            " WHERE start_time <= '$now'" .
            " AND end_time >= '$now'" .
            " AND CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'" .
            " AND act_type " . db_create_in(array(FAT_DISCOUNT, FAT_PRICE));
    $sql .= ($supplierid>=0) ? " AND supplier_id=".$supplierid : "";
    $favourable_list = $GLOBALS['db']->getAll($sql);
    if (!$favourable_list)
    {
        return 0;
    }

    /* 查询购物车商品 */
    $sql_where = $_SESSION['user_id']>0 ? "c.user_id='". $_SESSION['user_id'] ."' " : "c.session_id = '" . SESS_ID . "' AND c.user_id=0 ";
	//20170814 start prince qq 120029121 
    /*$sql = "SELECT c.goods_id, c.goods_price * c.goods_number AS subtotal, g.cat_id, g.brand_id, g.supplier_id " .
            "FROM " . $GLOBALS['yp']->table('cart') . " AS c, " . $GLOBALS['yp']->table('goods') . " AS g " .
            "WHERE c.goods_id = g.goods_id " .
            "AND " .$sql_where.
            "AND c.parent_id = 0 " .
            "AND c.is_gift = 0 " .
            "AND rec_type = '" . CART_GENERAL_GOODS . "'";
    $sql .= (isset($_REQUEST['sel_goods']) && !empty($_REQUEST['sel_goods'])) ? " AND c.rec_id in (". $_REQUEST['sel_goods'] .") " : "";
    $sql .= ($supplierid>=0) ? " AND g.supplier_id=".$supplierid : "";*/
	if ($supplierid >= 0)
	{
		$sql = "SELECT c.goods_id, c.goods_price * c.goods_number AS subtotal, g.cat_id, g.brand_id, " .
			" IF(c.extension_code = 'package_buy', ga.supplier_id, g.supplier_id) AS supplier_id " .
            " FROM " . $GLOBALS['yp']->table('cart') . " AS c " .
			" LEFT JOIN " . $GLOBALS['yp']->table('goods') . " AS g " .
            " ON c.goods_id = g.goods_id AND g.supplier_id = " . $supplierid .
			" LEFT JOIN " . $GLOBALS['yp']->table('goods_activity') . " AS ga " .
			" ON c.goods_id = ga.act_id AND ga.supplier_id = " . $supplierid .
            " WHERE " .$sql_where.
            " AND c.parent_id = 0 " .
            " AND c.is_gift = 0 " .
            " AND rec_type = '" . CART_GENERAL_GOODS . "'";
	}
	else
	{
		$sql = "SELECT c.goods_id, c.goods_price * c.goods_number AS subtotal, g.cat_id, g.brand_id, " .
			" IF(c.extension_code = 'package_buy', ga.supplier_id, g.supplier_id) AS supplier_id " .
            " FROM " . $GLOBALS['yp']->table('cart') . " AS c " .
			" LEFT JOIN " . $GLOBALS['yp']->table('goods') . " AS g " .
            " ON c.goods_id = g.goods_id " .
			" LEFT JOIN " . $GLOBALS['yp']->table('goods_activity') . " AS ga " .
			" ON c.goods_id = ga.act_id " .
            " WHERE " .$sql_where.
            " AND c.parent_id = 0 " .
            " AND c.is_gift = 0 " .
            " AND rec_type = '" . CART_GENERAL_GOODS . "'";
	}
    $sql .= (isset($_SESSION['sel_cartgoods']) && !empty($_SESSION['sel_cartgoods'])) ? " AND c.rec_id in (". $_SESSION['sel_cartgoods'] .") " : "";
	//20170814 end prince qq 120029121 
    $goods_list = $GLOBALS['db']->getAll($sql);
    if (!$goods_list)
    {
        return 0;
    }

    /* 初始化折扣 */
    $discount = 0;
    $favourable_name = array();

    /* 循环计算每个优惠活动的折扣 */
    foreach ($favourable_list as $favourable)
    {
        $total_amount = 0;
        if ($favourable['act_range'] == FAR_ALL)
        {
            foreach ($goods_list as $goods)
            {
            	if($favourable['supplier_id'] == $goods['supplier_id']){
                	$total_amount += $goods['subtotal'];
            	}
            }
        }
        elseif ($favourable['act_range'] == FAR_CATEGORY)
        {
            /* 找出分类id的子分类id */
            $id_list = array();
            $raw_id_list = explode(',', $favourable['act_range_ext']);
            foreach ($raw_id_list as $id)
            {
                $id_list = array_merge($id_list, array_keys(cat_list($id, 0, false)));
            }
            $ids = join(',', array_unique($id_list));

            foreach ($goods_list as $goods)
            {
                if (strpos(',' . $ids . ',', ',' . $goods['cat_id'] . ',') !== false && $favourable['supplier_id'] == $goods['supplier_id'])
                {
                    $total_amount += $goods['subtotal'];
                }
            }
        }
        elseif ($favourable['act_range'] == FAR_BRAND)
        {
            foreach ($goods_list as $goods)
            {
                if (strpos(',' . $favourable['act_range_ext'] . ',', ',' . $goods['brand_id'] . ',') !== false && $favourable['supplier_id'] == $goods['supplier_id'])
                {
                    $total_amount += $goods['subtotal'];
                }
            }
        }
        elseif ($favourable['act_range'] == FAR_GOODS)
        {
            foreach ($goods_list as $goods)
            {
                if (strpos(',' . $favourable['act_range_ext'] . ',', ',' . $goods['goods_id'] . ',') !== false && $favourable['supplier_id'] == $goods['supplier_id'])
                {
                    $total_amount += $goods['subtotal'];
                }
            }
        }
        else
        {
            continue;
        }

        /* 如果金额满足条件，累计折扣 */
        if ($total_amount > 0 && $total_amount >= $favourable['min_amount'] && ($total_amount <= $favourable['max_amount'] || $favourable['max_amount'] == 0))
        {
            if ($favourable['act_type'] == FAT_DISCOUNT)
            {
                $discount += $total_amount * (1 - $favourable['act_type_ext'] / 100);

                $favourable_name[] = $favourable['act_name'];
            }
            elseif ($favourable['act_type'] == FAT_PRICE)
            {
                $discount += $favourable['act_type_ext'];

                $favourable_name[] = $favourable['act_name'];
            }
        }
    }

    return array('discount' => $discount, 'name' => $favourable_name);
}

/**
 * 取得购物车该赠送的积分数
 * @return  int     积分数
 */
function get_give_integral()
{
        $sql = "SELECT SUM(c.goods_number * IF(g.give_integral > -1, g.give_integral, c.goods_price))" .
                "FROM " . $GLOBALS['yp']->table('cart') . " AS c, " .
                          $GLOBALS['yp']->table('goods') . " AS g " .
                "WHERE c.goods_id = g.goods_id " .
                "AND c.session_id = '" . SESS_ID . "' " .
                "AND c.goods_id > 0 " .
                "AND c.parent_id = 0 " .
                "AND c.rec_type = 0 " .
                "AND c.is_gift = 0";

        return intval($GLOBALS['db']->getOne($sql));
}

/**
 * 取得某订单应该赠送的积分数
 * @param   array   $order  订单
 * @return  int     积分数
 */
function integral_to_give($order)
{
    /* 判断是否团购 */
    if ($order['extension_code'] == 'group_buy')
    {
        include_once(ROOT_PATH . 'includes/lib_goods.php');
        $group_buy = group_buy_info(intval($order['extension_id']));

        return array('custom_points' => $group_buy['gift_integral'], 'rank_points' => $order['goods_amount']);
    }
    else
    {
        $sql = "SELECT SUM(og.goods_number * IF(g.give_integral > -1, g.give_integral, og.goods_price)) AS custom_points, SUM(og.goods_number * IF(g.rank_integral > -1, g.rank_integral, og.goods_price)) AS rank_points " .
                "FROM " . $GLOBALS['yp']->table('order_goods') . " AS og, " .
                          $GLOBALS['yp']->table('goods') . " AS g " .
                "WHERE og.goods_id = g.goods_id " .
                "AND og.order_id = '$order[order_id]' " .
                "AND og.goods_id > 0 " .
                "AND og.parent_id = 0 " .
                "AND og.is_gift = 0 AND og.extension_code != 'package_buy'";

        return $GLOBALS['db']->getRow($sql);
    }
}

/**
 * 发红包：发货时发红包
 * @param   int     $order_id   订单号
 * @return  bool
 */
function send_order_bonus($order_id,$supplier_id)
{
   	/* 取得订单应该发放的红包 */
   	$bonus_list = order_bonus($order_id,$supplier_id);
    /* 如果有红包，统计并发送 */
    if ($bonus_list)
    {
        /* 用户信息 */
        $sql = "SELECT u.user_id, u.user_name, u.email " .
                "FROM " . $GLOBALS['yp']->table('order_info') . " AS o, " .
                          $GLOBALS['yp']->table('users') . " AS u " .
                "WHERE o.order_id = '$order_id' " .
                "AND o.user_id = u.user_id ";
        $user = $GLOBALS['db']->getRow($sql);

        /* 统计 */
        $count = 0;
        $money = '';
        foreach ($bonus_list AS $bonus)
        {
            $count += $bonus['number'];
            $money .= price_format($bonus['type_money']) . ' [' . $bonus['number'] . '], ';

            /* 修改用户红包 */
             $sql = "INSERT INTO " . $GLOBALS['yp']->table('user_bonus') . " (bonus_type_id, user_id, supplier_id ) " ."VALUES('$bonus[type_id]', '$user[user_id]', '$supplier_id')";

            for ($i = 0; $i < $bonus['number']; $i++)
            {
                if (!$GLOBALS['db']->query($sql))
                {
                    return $GLOBALS['db']->errorMsg();
                }
            }
        }

        /* 如果有红包，发送邮件 */
        if ($count > 0)
        {
            $tpl = get_mail_template('send_bonus');
            $GLOBALS['smarty']->assign('user_name', $user['user_name']);
            $GLOBALS['smarty']->assign('count', $count);
            $GLOBALS['smarty']->assign('money', $money);
            $GLOBALS['smarty']->assign('shop_name', $GLOBALS['_CFG']['shop_name']);
            $GLOBALS['smarty']->assign('send_date', local_date($GLOBALS['_CFG']['date_format']));
            $GLOBALS['smarty']->assign('sent_date', local_date($GLOBALS['_CFG']['date_format']));
            $content = $GLOBALS['smarty']->fetch('str:' . $tpl['template_content']);
            send_mail($user['user_name'], $user['email'], $tpl['template_subject'], $content, $tpl['is_html']);
        }
    }

    return true;
}

/**
 * 返回订单发放的红包
 * @param   int     $order_id   订单id
 */
function return_order_bonus($order_id)
{
    /* 取得订单应该发放的红包 */
    $bonus_list = order_bonus($order_id);

    /* 删除 */
    if ($bonus_list)
    {
        /* 取得订单信息 */
        $order = order_info($order_id);
        $user_id = $order['user_id'];

        foreach ($bonus_list AS $bonus)
        {
            $sql = "DELETE FROM " . $GLOBALS['yp']->table('user_bonus') .
                    " WHERE bonus_type_id = '$bonus[type_id]' " .
                    "AND user_id = '$user_id' " .
                    "AND order_id = '0' LIMIT " . $bonus['number'];
            $GLOBALS['db']->query($sql);
        }
    }
}

/**
 * 取得订单应该发放的红包
 * @param   int     $order_id   订单id
 * @return  array
 */
function order_bonus($order_id,$supplier_id='')
{	
    /* 查询按商品发的红包 */
    $day    = getdate();
    $today  = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);

    $sql = "SELECT b.type_id, b.type_money, SUM(o.goods_number) AS number " .
            "FROM " . $GLOBALS['yp']->table('order_goods') . " AS o, " .
                      $GLOBALS['yp']->table('goods') . " AS g, " .
                      $GLOBALS['yp']->table('bonus_type') . " AS b " .
            " WHERE o.order_id = '$order_id' " .
            " AND o.is_gift = 0 " .
            " AND o.goods_id = g.goods_id " .
            " AND g.bonus_type_id = b.type_id " .
            " AND b.send_type = '" . SEND_BY_GOODS . "' " .
            " AND b.send_start_date <= '$today' " .
            " AND b.send_end_date >= '$today' " .
            " GROUP BY b.type_id ";
    $list = $GLOBALS['db']->getAll($sql);

    /* 查询定单中非赠品总金额 */
    $amount = order_amount($order_id, false);

    /* 查询订单日期 */
    $sql = "SELECT add_time " .
            " FROM " . $GLOBALS['yp']->table('order_info') .
            " WHERE order_id = '$order_id' LIMIT 1";
    $order_time = $GLOBALS['db']->getOne($sql);

    /* 查询按订单发的红包 */
	 $sql = "SELECT type_id, type_money, IFNULL(FLOOR('$amount' / min_amount), 1) AS number " .
            "FROM " . $GLOBALS['yp']->table('bonus_type') .
            "WHERE send_type = '" . SEND_BY_ORDER . "' " .
            "AND send_start_date <= '$order_time' " .
            "AND send_end_date >= '$order_time' ";
			if($supplier_id!=''){
				$sql.="AND supplier_id = '$supplier_id' ";
			}
    $list = array_merge($list, $GLOBALS['db']->getAll($sql));

    return $list;
}

/**
 * 计算购物车中的商品能享受红包支付的总额
 * @param  int  $suppid  店铺id
 * @return  float   享受红包支付的总额
 */
function compute_discount_amount($suppid=-1)
{
    /* 查询优惠活动 */
    $now = gmtime();
    $user_rank = ',' . $_SESSION['user_rank'] . ',';
    $where_suppid = '';
    if($suppid>-1){
    	$where_suppid = " AND supplier_id=".$suppid;
    }
    $sql = "SELECT *" .
            "FROM " . $GLOBALS['yp']->table('favourable_activity') .
            " WHERE start_time <= '$now'" .
            " AND end_time >= '$now'" .$where_suppid.
            " AND CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'" .
            " AND act_type " . db_create_in(array(FAT_DISCOUNT, FAT_PRICE));
    $favourable_list = $GLOBALS['db']->getAll($sql);
    if (!$favourable_list)
    {
        return 0;
    }

    /* 查询购物车商品 */
    $sql_where = $_SESSION['user_id']>0 ? "c.user_id='". $_SESSION['user_id'] ."' " : "c.session_id = '" . SESS_ID . "' AND c.user_id=0 ";
	if($suppid>-1){
    	$where_suppid = " AND g.supplier_id=".$suppid." ";
    }
    $sql = "SELECT c.goods_id, c.goods_price * c.goods_number AS subtotal, g.cat_id, g.brand_id, g.supplier_id " .
            "FROM " . $GLOBALS['yp']->table('cart') . " AS c, " . $GLOBALS['yp']->table('goods') . " AS g " .
            "WHERE c.goods_id = g.goods_id " .
            "AND $sql_where " .$where_suppid.
            "AND c.parent_id = 0 " .
            "AND c.is_gift = 0 " .
            "AND rec_type = '" . CART_GENERAL_GOODS . "'";
    $goods_list = $GLOBALS['db']->getAll($sql);
    if (!$goods_list)
    {
        return 0;
    }

    /* 初始化折扣 */
    $discount = 0;
    $favourable_name = array();

    /* 循环计算每个优惠活动的折扣 */
    foreach ($favourable_list as $favourable)
    {
        $total_amount = 0;
        if ($favourable['act_range'] == FAR_ALL)
        {
            foreach ($goods_list as $goods)
            {
            	if($favourable['supplier_id'] == $goods['supplier_id']){
                	$total_amount += $goods['subtotal'];
            	}
            }
        }
        elseif ($favourable['act_range'] == FAR_CATEGORY)
        {
            /* 找出分类id的子分类id */
            $id_list = array();
            $raw_id_list = explode(',', $favourable['act_range_ext']);
            foreach ($raw_id_list as $id)
            {
                $id_list = array_merge($id_list, array_keys(cat_list($id, 0, false)));
            }
            $ids = join(',', array_unique($id_list));

            foreach ($goods_list as $goods)
            {
                if (strpos(',' . $ids . ',', ',' . $goods['cat_id'] . ',') !== false && $favourable['supplier_id'] == $goods['supplier_id'])
                {
                    $total_amount += $goods['subtotal'];
                }
            }
        }
        elseif ($favourable['act_range'] == FAR_BRAND)
        {
            foreach ($goods_list as $goods)
            {
                if (strpos(',' . $favourable['act_range_ext'] . ',', ',' . $goods['brand_id'] . ',') !== false && $favourable['supplier_id'] == $goods['supplier_id'])
                {
                    $total_amount += $goods['subtotal'];
                }
            }
        }
        elseif ($favourable['act_range'] == FAR_GOODS)
        {
            foreach ($goods_list as $goods)
            {
                if (strpos(',' . $favourable['act_range_ext'] . ',', ',' . $goods['goods_id'] . ',') !== false && $favourable['supplier_id'] == $goods['supplier_id'])
                {
                    $total_amount += $goods['subtotal'];
                }
            }
        }
        else
        {
            continue;
        }
        if ($total_amount > 0 && $total_amount >= $favourable['min_amount'] && ($total_amount <= $favourable['max_amount'] || $favourable['max_amount'] == 0))
        {
            if ($favourable['act_type'] == FAT_DISCOUNT)
            {
                $discount += $total_amount * (1 - $favourable['act_type_ext'] / 100);
            }
            elseif ($favourable['act_type'] == FAT_PRICE)
            {
                $discount += $favourable['act_type_ext'];
            }
        }
    }


    return $discount;
}

/**
 * 添加礼包到购物车
 *
 * @access  public
 * @param   integer $package_id   礼包编号
 * @param   integer $num          礼包数量
 * @return  boolean
 */
function add_package_to_cart($package_id, $num = 1, $package_attr_id='', $package_prices='')
{
    $GLOBALS['err']->clean();
    if($package_prices)
    {
	$package_pricea=explode("-", $package_prices);
    }

    /* 取得礼包信息 */
    $package = get_package_info($package_id);

    if (empty($package))
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['goods_not_exists'], ERR_NOT_EXISTS);

        return false;
    }

    /* 是否正在销售 */
    if ($package['is_on_sale'] == 0)
    {
        $GLOBALS['err']->add($GLOBALS['_LANG']['not_on_sale'], ERR_NOT_ON_SALE);

        return false;
    }

    /* 现有库存是否还能凑齐一个礼包 */
    if ($GLOBALS['_CFG']['use_storage'] == '1' && judge_package_stock($package_id))
    {
        $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], 1), ERR_OUT_OF_STOCK);

        return false;
    }

    /* 检查库存
    if ($GLOBALS['_CFG']['use_storage'] == 1 && $num > $package['goods_number'])
    {
        $num = $goods['goods_number'];
        $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);

        return false;
    }*/

    /* 初始化要插入购物车的基本件数据 */
    $parent = array(
        'user_id'       => $_SESSION['user_id'],
        'session_id'    => SESS_ID,
        'goods_id'      => $package_id,
        'goods_sn'      => '',
        'goods_name'    => addslashes($package['package_name']),
        'market_price'  => $package_pricea[0] ? $package_pricea[0] :  $package['market_package'],
        'goods_price'   => $package_pricea[1] ? $package_pricea[1] :  $package['package_price'],
	'package_attr_id' =>$package_attr_id,
        'goods_number'  => $num,
        'goods_attr'    => '',
        'goods_attr_id' => '',
        'is_real'       => $package['is_real'],
        'extension_code'=> 'package_buy',
        'is_gift'       => 0,
        'rec_type'      => CART_GENERAL_GOODS
    );

    /* 如果数量不为0，作为基本件插入 */
    if ($num > 0)
    {
         /* 检查该商品是否已经存在在购物车中 */
        $sql = "SELECT goods_number FROM " .$GLOBALS['yp']->table('cart').
                " WHERE session_id = '" .SESS_ID. "' AND goods_id = '" . $package_id . "' ".
                " AND parent_id = 0 AND extension_code = 'package_buy' " .
                " AND package_attr_id = '$package_attr_id'  AND rec_type = '" . CART_GENERAL_GOODS . "'";

        $row = $GLOBALS['db']->getRow($sql);

        if($row) //如果购物车已经有此物品，则更新
        {
            $num += $row['goods_number'];
            if ($GLOBALS['_CFG']['use_storage'] == 0 || $num > 0)
            {
                $sql = "UPDATE " . $GLOBALS['yp']->table('cart') . " SET goods_number = '" . $num . "'" .
                       " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$package_id' ".
                       " AND parent_id = 0 AND extension_code = 'package_buy' " .
                       " AND package_attr_id = '$package_attr_id' AND rec_type = '" . CART_GENERAL_GOODS . "'";
                $GLOBALS['db']->query($sql);
            }
            else
            {
                $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['shortage'], $num), ERR_OUT_OF_STOCK);
                return false;
            }
        }
        else //购物车没有此物品，则插入
        {
            $GLOBALS['db']->autoExecute($GLOBALS['yp']->table('cart'), $parent, 'INSERT');
        }
    }

    /* 把赠品删除 */
    $sql = "DELETE FROM " . $GLOBALS['yp']->table('cart') . " WHERE session_id = '" . SESS_ID . "' AND is_gift <> 0";
    $GLOBALS['db']->query($sql);

    return true;
}

/**
 * 得到新发货单号
 * @return  string
 */
function get_delivery_sn()
{
    /* 选择一个随机的方案 */
    mt_srand((double) microtime() * 1000000);

    return date('YmdHi') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

/**
 * 检查礼包内商品的库存
 * @return  boolen
 */
function judge_package_stock($package_id, $package_num = 1)
{
    $sql = "SELECT goods_id, product_id, goods_number
            FROM " . $GLOBALS['yp']->table('package_goods') . "
            WHERE package_id = '" . $package_id . "'";
    $row = $GLOBALS['db']->getAll($sql);
    if (empty($row))
    {
        return true;
    }

    /* 分离货品与商品 */
    $goods = array('product_ids' => '', 'goods_ids' => '');
    foreach ($row as $value)
    {
        if ($value['product_id'] > 0)
        {
            $goods['product_ids'] .= ',' . $value['product_id'];
            continue;
        }

        $goods['goods_ids'] .= ',' . $value['goods_id'];
    }

    /* 检查货品库存 */
    if ($goods['product_ids'] != '')
    {
        $sql = "SELECT p.product_id
                FROM " . $GLOBALS['yp']->table('products') . " AS p, " . $GLOBALS['yp']->table('package_goods') . " AS pg
                WHERE pg.product_id = p.product_id
                AND pg.package_id = '$package_id'
                AND pg.goods_number * $package_num > p.product_number
                AND p.product_id IN (" . trim($goods['product_ids'], ',') . ")";
        $row = $GLOBALS['db']->getAll($sql);

        if (!empty($row))
        {
            return true;
        }
    }

    /* 检查商品库存 */
    if ($goods['goods_ids'] != '')
    {
        $sql = "SELECT g.goods_id
                FROM " . $GLOBALS['yp']->table('goods') . "AS g, " . $GLOBALS['yp']->table('package_goods') . " AS pg
                WHERE pg.goods_id = g.goods_id
                AND pg.goods_number * $package_num > g.goods_number
                AND pg.package_id = '" . $package_id . "'
                AND pg.goods_id IN (" . trim($goods['goods_ids'], ',') . ")";
        $row = $GLOBALS['db']->getAll($sql);

        if (!empty($row))
        {
            return true;
        }
    }

    return false;
}

function cart_weight_price2($type = CART_GENERAL_GOODS, $supplier_id)
{
	$package_row['weight'] = 0;
    $package_row['amount'] = 0;
    $package_row['number'] = 0;

    $packages_row['free_shipping'] = 1;

    /* 计算超值礼包内商品的相关配送参数 */
    $sql = 'SELECT goods_id, goods_number, goods_price FROM ' . $GLOBALS['yp']->table('cart') . " WHERE extension_code = 'package_buy' AND session_id = '" . SESS_ID . "'";
    $row = $GLOBALS['db']->getAll($sql);

    if ($row)
    {
        $packages_row['free_shipping'] = 0;
        $free_shipping_count = 0;

        foreach ($row as $val)
        {
            // 如果商品全为免运费商品，设置一个标识变量
            $sql = 'SELECT count(*) FROM ' .
                    $GLOBALS['yp']->table('package_goods') . ' AS pg, ' .
                    $GLOBALS['yp']->table('goods') . ' AS g ' .
                    "WHERE g.supplier_id='". $supplier_id ."' and g.goods_id = pg.goods_id AND g.is_shipping = 0 AND pg.package_id = '"  . $val['goods_id'] . "'";
            $shipping_count = $GLOBALS['db']->getOne($sql);

            if ($shipping_count > 0)
            {
                // 循环计算每个超值礼包商品的重量和数量，注意一个礼包中可能包换若干个同一商品
                $sql = 'SELECT SUM(g.goods_weight * pg.goods_number) AS weight, ' .
                    'SUM(pg.goods_number) AS number FROM ' .
                    $GLOBALS['yp']->table('package_goods') . ' AS pg, ' .
                    $GLOBALS['yp']->table('goods') . ' AS g ' .
                    "WHERE g.supplier_id='". $supplier_id ."' and g.goods_id = pg.goods_id AND g.is_shipping = 0 AND pg.package_id = '"  . $val['goods_id'] . "'";

                $goods_row = $GLOBALS['db']->getRow($sql);
                $package_row['weight'] += floatval($goods_row['weight']) * $val['goods_number'];
                $package_row['amount'] += floatval($val['goods_price']) * $val['goods_number'];
                $package_row['number'] += intval($goods_row['number']) * $val['goods_number'];
            }
            else
            {
                $free_shipping_count++;
            }
        }

        $packages_row['free_shipping'] = $free_shipping_count == count($row) ? 1 : 0;
    }

    /* 获得购物车中非超值礼包商品的总重量 */
    $sql    = 'SELECT SUM(g.goods_weight * c.goods_number) AS weight, ' .
                    'SUM(c.goods_price * c.goods_number) AS amount, ' .
                    'SUM(c.goods_number) AS number '.
                'FROM ' . $GLOBALS['yp']->table('cart') . ' AS c '.
                'LEFT JOIN ' . $GLOBALS['yp']->table('goods') . ' AS g ON g.goods_id = c.goods_id '.
                "WHERE g.supplier_id='". $supplier_id ."' and c.session_id = '" . SESS_ID . "' " .
                "AND rec_type = '$type' AND g.is_shipping = 0 AND c.extension_code != 'package_buy'";
    $row = $GLOBALS['db']->getRow($sql);

    $packages_row['weight'] = floatval($row['weight']) + $package_row['weight'];
    $packages_row['amount'] = floatval($row['amount']) + $package_row['amount'];
    $packages_row['number'] = intval($row['number']) + $package_row['number'];

    /* 格式化重量 */
    $packages_row['formated_weight'] = formated_weight($packages_row['weight']);

    return $packages_row;
}

/*
 * 获取订单对应的佣金记录id(只有店铺才计算)
 * @param int $suppid  店铺id
 */
function get_order_rebate($suppid){
	$spkey = intval($suppid);
	if($spkey<=0){
		return 0;
	}
	$sql = "select rebate_id, rebate_paytime_start, rebate_paytime_end from ". $GLOBALS['yp']->table('supplier_rebate') ." where supplier_id='$spkey' and is_pay_ok=0 order by rebate_id desc limit 0,1";
	$row = $GLOBALS['db']->getRow($sql);
	$nowtime = gmtime();
	if (  $nowtime >=  $row['rebate_paytime_start']  && $nowtime <= $row['rebate_paytime_end'] )
	{
		$rebate_id= $row['rebate_id'];
	}
	else
	{
		$kkk='yes';
		while($kkk=='yes')
		{
			insert_id_rebate($spkey);
			$sql2 = "select rebate_id, rebate_paytime_start, rebate_paytime_end from ". $GLOBALS['yp']->table('supplier_rebate') ." where supplier_id='$spkey' and is_pay_ok=0 order by rebate_id desc limit 0,1";
			$row2 = $GLOBALS['db']->getRow($sql2);
			if (  $nowtime >=  $row2['rebate_paytime_start']  && $nowtime <= $row2['rebate_paytime_end'] )
			{
				$rebate_id= $row2['rebate_id'];
				$kkk='no';
			}
		}
	  }
	  return $rebate_id;
}

function split_order($new_order_id)
{
	$sql = "select IF(g.supplier_id, g.supplier_id,0) AS supplier_id, og.rec_id, og.goods_number, og.goods_price from ". $GLOBALS['yp']->table("order_goods") .
				" AS og left join ". $GLOBALS['yp']->table("goods") ." AS g on og.goods_id=g.goods_id ".
				" where og.order_id = '$new_order_id' ";
	$res = $GLOBALS['db']->query($sql);
	$split_orders = array();
	$all_amount = 0;
	while ($row=$GLOBALS['db']->fetchRow($res))
	{
		$split_orders[$row['supplier_id']]['goods_amount'] += $row['goods_number'] * $row['goods_price'];
		$split_orders[$row['supplier_id']]['goods_reclist'][] = $row['rec_id'];
		$split_orders[$row['supplier_id']]['order_sn'] =  $split_orders[$row['supplier_id']]['order_sn'] ? $split_orders[$row['supplier_id']]['order_sn'] : get_order_sn();
		$split_orders[$row['supplier_id']]['shipping_fee'] = $GLOBALS['total']['supplier_shipping'][$row['supplier_id']]['shipping_fee'];
		$split_orders[$row['supplier_id']]['order_amount'] = $split_orders[$row['supplier_id']]['goods_amount'] + $split_orders[$row['supplier_id']]['shipping_fee'];
		$split_orders[$row['supplier_id']]['order_amount_formated'] = price_format($split_orders[$row['supplier_id']]['order_amount']);
		$all_amount += $split_orders[$row['supplier_id']]['order_amount'];
	}
	
	//下单来源
	$order_from = WEB_FROM;

	$count_split_orders =count($split_orders);

	foreach ($split_orders AS $spkey => $split)
	{
		//获的返佣ID
		if($spkey>0)
		{
			$sql = "select rebate_id, rebate_paytime_start, rebate_paytime_end from ". $GLOBALS['yp']->table('supplier_rebate') ." where supplier_id='$spkey' and is_pay_ok=0 order by rebate_id desc limit 0,1";
			$row = $GLOBALS['db']->getRow($sql);
			$nowtime = gmtime();
			if (  $nowtime >=  $row['rebate_paytime_start']  && $nowtime <= $row['rebate_paytime_end'] )
			{
				$rebate_id= $row['rebate_id'];
			}
			else
			{
				$kkk='yes';
				while($kkk=='yes')
				{
					insert_id_rebate($spkey);
					$sql2 = "select rebate_id, rebate_paytime_start, rebate_paytime_end from ". $GLOBALS['yp']->table('supplier_rebate') ." where supplier_id='$spkey' and is_pay_ok=0 order by rebate_id desc limit 0,1";
					$row2 = $GLOBALS['db']->getRow($sql2);
					if (  $nowtime >=  $row2['rebate_paytime_start']  && $nowtime <= $row2['rebate_paytime_end'] )
					{
						$rebate_id= $row2['rebate_id'];
						$kkk='no';
					}
				}
			  }
	  }
	  else
	  {
		   $rebate_id=0;
	  }

	  $order_sn = $split['order_sn'];
	  if ($count_split_orders ==1)
	  {
		  $sql = "update ". $GLOBALS['yp']->table('order_info') .
					  " set order_sn='$order_sn', supplier_id='$spkey', parent_order_id='0', rebate_id='$rebate_id', froms='$order_from'   where order_id='$new_order_id' ";
		  $GLOBALS['db']->query($sql);
	  }
	  else
	  {
		$sql = "insert into ".$GLOBALS['yp']->table('order_info') . "( ".
					" order_sn, user_id,	order_status,	shipping_status, pay_status, consignee, country,	province,	city,	district, address, zipcode, tel,	mobile	, email, best_time	,sign_building,	postscript,	shipping_id,	shipping_name,	 pay_id,	pay_name,	how_oos, how_surplus, pack_name,	card_name,	card_message,	inv_payee,	inv_content, goods_amount,	shipping_fee,	insure_fee,	pay_fee,	pack_fee,	card_fee, money_paid,	surplus,	integral,	integral_money,	bonus,	order_amount,	from_ad,	referer,	add_time,	confirm_time,	pay_time,	shipping_time,	pack_id,	card_id,	bonus_id,	invoice_no,	extension_code,	extension_id,	to_buyer,	pay_note,	agency_id,	inv_type,	tax,	is_separate,	parent_id,	discount,	 supplier_id,	parent_order_id, rebate_id, froms, pickup_point, is_pickup) ".					
					"select '$order_sn', user_id,	order_status,	shipping_status, pay_status, consignee, country,	province,	city,	district, address, zipcode, tel,	mobile	, email, best_time	,sign_building,	postscript,	shipping_id,	shipping_name,	 pay_id,	pay_name,	how_oos, how_surplus, pack_name,	card_name,	card_message,	inv_payee,	inv_content	, '". $split['goods_amount']. "',	'" . $split['shipping_fee'] ."',	insure_fee,	pay_fee,	pack_fee,	card_fee,	money_paid,	surplus,	integral,	integral_money,	bonus,	'". $split['order_amount'] ."',	from_ad,	referer,	add_time,	confirm_time,	pay_time,	shipping_time,	pack_id,	card_id,	bonus_id,	invoice_no,	extension_code,	extension_id,	to_buyer,	pay_note,	agency_id,	inv_type,	tax,	is_separate,	parent_id,	discount,	'$spkey',	'$new_order_id', '$rebate_id', '$order_from', pickup_point, is_pickup from ".$GLOBALS['yp']->table('order_info')." where order_id= '$new_order_id' ";
			$GLOBALS['db']->query($sql);
			$order_id_new = $GLOBALS['db']->insert_id();
			foreach ($split['goods_reclist'] AS $rec)
			{
					$sql= "update ". $GLOBALS['yp']->table('order_goods') ." set order_id='$order_id_new' where rec_id='$rec' ";
					$GLOBALS['db']->query($sql);
			}			

	   }

	}
    
	if ($count_split_orders>1)
	{
		$sql="delete from ".$GLOBALS['yp']->table('order_info')." where order_id='$new_order_id' ";
		$GLOBALS['db']->query($sql);
	}

	$arr=array();
	$arr['suborder_list'] = $split_orders;
	$arr['all_amount'] = $all_amount;
	$arr['sub_order_count'] = $count_split_orders;
	return  $arr;
}

function  insert_id_rebate($supplier_id)
{
		$sql="select supplier_rebate_paytime from ". $GLOBALS['yp']->table('supplier') ." where supplier_id='$supplier_id'";
		$supplier_rebate_paytime = $GLOBALS['db']->getOne($sql);

		$sql = "select rebate_paytime_start, rebate_paytime_end from ". $GLOBALS['yp']->table('supplier_rebate') ." where supplier_id= '$supplier_id' and is_pay_ok=0 order by rebate_id DESC LIMIT 0,1";
		$row = $GLOBALS['db']->getRow($sql);
		if (!$row['rebate_paytime_start'])
		{
			$rebate_paytime_start = local_mktime(0,0,0,local_date('m'), local_date('d'), local_date('Y'));
		}
		if (!$row['rebate_paytime_end'])
		{
			switch($supplier_rebate_paytime)
			{
				case '1':
					$rebate_paytime_end= local_strtotime("this Sunday") + 24*60*60-1;
					break;
				case '2':
					$rebate_paytime_end= local_mktime(23,59,59,local_date("m"),local_date("t"),local_date("Y"));
					break;
				case '3':
					if (local_date("m")=='1' || local_date("m")=='2' || local_date("m")=='3')
					{
						$rebate_paytime_end= local_mktime(23,59,59,3,31,local_date("Y"));
					}
					elseif (local_date("m")=='4' || local_date("m")=='5' || local_date("m")=='6')
					{
						$rebate_paytime_end= local_mktime(23,59,59, 6,30,local_date("Y"));
					}
					elseif(local_date("m")=='7' || local_date("m")=='8' || local_date("m")=='9')
					{
						$rebate_paytime_end= local_mktime(23,59,59, 9, 30,local_date("Y"));
					}
					elseif(local_date("m")=='10' || local_date("m")=='11' || local_date("m")=='12')
					{
						$rebate_paytime_end= local_mktime(23,59,59, 12,31,local_date("Y"));
					}
					break;
				case '4':
					$rebate_paytime_end= local_mktime(23,59,59,12,31,local_date("Y"));
					break;
			}
		}
		if ( $row['rebate_paytime_start']  &&  $row['rebate_paytime_end'] )
		{
			$rebate_paytime_start = $row['rebate_paytime_end'] + 1;
			switch($supplier_rebate_paytime)
			{
				case '1':
					$rebate_paytime_end= $row['rebate_paytime_end'] + 24*60*60*7;
					break;
				case '2':
					$rebate_paytime_end= local_mktime(23,59,59,local_date("m",$rebate_paytime_start),local_date("t",$rebate_paytime_start),local_date("Y",$rebate_paytime_start));
					break;
				case '3':
					if (local_date("m",$rebate_paytime_start)=='1' || local_date("m")=='2' || local_date("m")=='3')
					{
						$rebate_paytime_end= local_mktime(23,59,59,3,31,local_date("Y"));
					}
					elseif (local_date("m")=='4' || local_date("m")=='5' || local_date("m")=='6')
					{
						$rebate_paytime_end= local_mktime(23,59,59, 6,30,local_date("Y"));
					}
					elseif(local_date("m")=='7' || local_date("m")=='8' || local_date("m")=='9')
					{
						$rebate_paytime_end= local_mktime(23,59,59, 9, 30,local_date("Y"));
					}
					elseif(local_date("m")=='10' || local_date("m")=='11' || local_date("m")=='12')
					{
						$rebate_paytime_end= local_mktime(23,59,59, 12,31,local_date("Y"));
					}
					break;
				case '4':
					$rebate_paytime_end= local_mktime(23,59,59,12,31,local_date("Y"));
					break;
			}
		}

		$sql="insert into ". $GLOBALS['yp']->table('supplier_rebate') ."(rebate_paytime_start, rebate_paytime_end, supplier_id) value('$rebate_paytime_start', '$rebate_paytime_end', '$supplier_id') ";
		$GLOBALS['db']->query($sql);
}

function is_cansel($goods_id, $product_id, $package_buy)
{
	if($package_buy=='package_buy') 
	{
		return '1';
	}
	$sql = "select is_on_sale, goods_number from ". $GLOBALS['yp']->table('goods') ." where goods_id='$goods_id' ";
	$row = $GLOBALS['db']->getRow($sql);
	if (!$row['is_on_sale'])
	{
		return '0';
	}
	else
	{
		if ($product_id>0)
		{
			$sql2 = "select product_number from ". $GLOBALS['yp']->table('products') ." where product_id='$product_id' ";
			$row2 = $GLOBALS['db']->getRow($sql2);
			if (!$row2['product_number'])
			{
				return '0';
			}
		}
		else
		{
			if (!$row['goods_number'])
			{
				return '0';
			}
		}
	}
	return '1';
}

function getWeek($unixTime='')
{
	$unixTime=is_numeric($unixTime) ? $unixTime : time();
	$weekarray=array('日','一','二','三','四','五','六');
	return '周'.$weekarray[date('w',$unixTime)];
}

function get_region_info($region_id)
{
    $sql = 'SELECT region_name FROM ' . $GLOBALS['yp']->table('region') .
            " WHERE region_id = '$region_id' ";

    return $GLOBALS['db']->getOne($sql);
}
/**
判断是否是门店自提配送方式
@param int $id 配送方式id
@return bool
*/
function is_pups($id){
	global $db,$yp;
	$sql = "select shipping_code,support_pickup from ".$yp->table('shipping')." where shipping_id=".$id;
	$info = $db->getRow($sql);
	if($info){
		if($info['shipping_code'] == 'pups' && $info['support_pickup'] == 1){
			return true;
		}else{
			return false;
		}
	}else{
		return false;
	}
}
/**
 * 根据自提点的城市获取自提点列表
 * @param int $cityid  城市id
 * @param int $suppid  店铺id
 * @retrun array 自提点信息
*/
function get_pickup_info($cityid,$suppid=0){
	global $db,$yp;
	$sql = "select pp.*,r.region_name from ".$yp->table('pickup_point')." as pp left join ".$yp->table('region')." as r on pp.district_id=r.region_id where pp.city_id=".$cityid." and pp.supplier_id=".$suppid." order by pp.id";
	$info = $db->getAll($sql);
	return $info;
}
/**
 * 根据自提点的主键获取
 * @param int $id 主键
 * @retrun array 自提点信息
*/
function get_pickup_one_info($id){
	global $db,$yp;
	$sql = "select * from ".$yp->table('pickup_point')." where id=".$id;
	$info = $db->getRow($sql);
	return $info;
}

function update_order_shipping($order_id){
    $sql = "update ".$GLOBALS['yp']->table('order_info')." set shipping_status = ".SS_SHIPPED." where order_id = $order_id";
    $GLOBALS['db']->query($sql);
}

//根据商品id获取购物车中的商品信息
function get_cart_goods_info($rec_id){
    $sql = "select * from ". $GLOBALS['yp']->table('cart') ." where rec_id = $rec_id";
    $cart_goods = $GLOBALS['db']->getRow($sql);
    $cart_goods['goods_attr_arr'] = explode(",",$cart_goods['goods_attr_id']);
    return $cart_goods; 
}
/**
 * 根据goodsid获取商品(属性-产品)数量
 * @param type $goods_id
 * @param type $attr_id
 * @return int
 */
function get_goods_attr_number($goods_id,$attr_id=array()){
    $product_number  = $GLOBALS['db']->getOne("select goods_number from ".$GLOBALS['yp']->table('goods')." where goods_id = $goods_id");
    if(!empty($attr_id)){
        $attr_id_s =  is_array($attr_id)?implode('|',$attr_id):$attr_id;
        $sql = "select product_number from ".$GLOBALS['yp']->table('products')." where goods_id = $goods_id and goods_attr = '$attr_id_s'";
        $product_number = $GLOBALS['db']->getOne($sql);
        $is_products = $GLOBALS['db']->getOne("select count(*) from ".$GLOBALS['yp']->table('products')." where goods_id = $goods_id");
        if($product_number>0 && $is_products>0){
                return $product_number;
        }else{
             return 0;
        }
    }
    return $product_number;
}

/**
 * 同城快递
 * @param type $invoice_no
 * @return type
 */
function getkosorder($invoice_no){
    $kos_order_id = $GLOBALS['db']->getOne("select order_id from " . $GLOBALS['yp']->table('kuaidi_order') . " where order_sn='$invoice_no'");
    $sql = "select * from " . $GLOBALS['yp']->table('kuaidi_order_status') . " where order_id='" . $kos_order_id . "'  order by status_id desc";
    $res_status = $GLOBALS['db']->query($sql);
    $have_shipping_info = 0;
    $result = array('message'=>'ok','nu'=>$invoice_no,'ischeck'=>'1','com'=>'tongcheng','data'=>array());//初始化数据
    while($row_status = $GLOBALS['db']->fetchRow($res_status)){
	if($row_status['status_display'] == 1){
            switch($row_status['status_id']){
		case 1:
		$row_status['context'] = "您提交了订单，请等待确认。";
                $row_status['time']   = local_date('Y-m-d H:i:s', $row_status['status_time']);
                $row_status['ftime']   = local_date('Y-m-d H:i:s', $row_status['status_time']);
		break;
		case 2:
		$row_status['context'] = "您的快件已经确认，等待快递员揽收。";
                $row_status['time']   = local_date('Y-m-d H:i:s', $row_status['status_time']);
                $row_status['ftime']   = local_date('Y-m-d H:i:s', $row_status['status_time']);
                break;
		case 3:
		$postman_id = $GLOBALS['db']->getOne("select postman_id from " . $GLOBALS['yp']->table('kuaidi_order') . " where order_sn='$invoice_no'");
		$postman_info = $GLOBALS['db']->getRow("select postman_name, mobile from " . $GLOBALS['yp']->table('postman') . " where postman_id=" . $postman_id);
		$row_status['context'] = "您的快件正在派送，快递员：" . $postman_info['postman_name'] . "，电话：" . $postman_info['mobile'];
                $row_status['time']   = local_date('Y-m-d H:i:s', $row_status['status_time']);
                $row_status['ftime']   = local_date('Y-m-d H:i:s', $row_status['status_time']);          
		break;
                case 4:
		$row_status['context'] = "您的快件已经签收。";
                $row_status['time']   = local_date('Y-m-d H:i:s', $row_status['status_time']);
                $row_status['ftime']   = local_date('Y-m-d H:i:s', $row_status['status_time']);
		break;
		case 5:
		$row_status['context'] = "您的快件已被拒收。";
                $row_status['time']   = local_date('Y-m-d H:i:s', $row_status['status_time']);
                $row_status['ftime']   = local_date('Y-m-d H:i:s', $row_status['status_time']);
		break;
		case 6:
		$row_status['context'] = "您拒收的快件已被退回。";
                $row_status['time']   = local_date('Y-m-d H:i:s', $row_status['status_time']);
                $row_status['ftime']   = local_date('Y-m-d H:i:s', $row_status['status_time']);
		break;
		case 7:
		$row_status['context'] = "您的快件已经取消。";
                $row_status['time']   = local_date('Y-m-d H:i:s', $row_status['status_time']);
                $row_status['ftime']   = local_date('Y-m-d H:i:s', $row_status['status_time']);
		break;
            }
              $result['data'][] = $row_status;
        }

    }
    return $result;
}




//获取某一个订单的分成金额  寒冰
function get_split_money_by_fencheng($order_id , $supplier_id)
{
    

    if ($supplier_id > 0) {
        $sql = 'SELECT value FROM ' . $GLOBALS['yp']->table('supplier_shop_config')." WHERE code = 'distrib_type' AND supplier_id = 'supplier_id'";
    
        $distrib_type = $GLOBALS['db']->getOne($sql);
    }else{

      $sql = 'SELECT value FROM ' . $GLOBALS['yp']->table('ypmart_shop_config')." WHERE code = 'distrib_type'";
    
      $distrib_type = $GLOBALS['db']->getOne($sql);

    }
     
	 if($distrib_type == 0)
	 {
		 $total_fee = " (goods_amount - discount + tax + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee) AS total_money";
		 //按订单分成
		 $sql = "SELECT " . $total_fee . " FROM " . $GLOBALS['yp']->table('order_info') . " WHERE order_id = '$order_id'";
		 $total_fee = $GLOBALS['db']->getOne($sql);
		 $split_money = $total_fee*($GLOBALS['_CFG']['distrib_percent']/100);
	 }
	 else
	 {
		//按商品分成
	 	$sql = "SELECT sum(split_money*goods_number) FROM " . $GLOBALS['yp']->table('order_goods') . " WHERE order_id = '$order_id'";
	 	$split_money = $GLOBALS['db']->getOne($sql);
	 }
	 if($split_money > 0)
	 {
		 return $split_money; 
	 }
	 else
	 {
		 return 0; 
	 }
}

?>