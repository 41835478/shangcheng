<?php if ($this->_var['supplier_list']): ?>
    <link rel="stylesheet" href="themesmobile/prince_jtypmall_mobile/css/supplier.css" type="text/css" />
    <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
	<div class="index_floor">
    <h4><span>附近商家</span><i><a href="supplier_near.php?">更多</a></i></h4>
    <div ng-view="" class="page ng-scope" id="mainPage" style="height: 100%;line-height:normal">
       <!-- <div class="wrap ng-scope">-->
            <div class="shop-list-box">
                <ul class="shop-list">
                    <?php $_from = $this->_var['supplier_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'sup');if (count($_from)):
    foreach ($_from AS $this->_var['sup']):
?>
                    <li class="ng-scope">
                        <div>
                            <div class="shop-thumb">
                                <a href="supplier.php?suppId=<?php echo $this->_var['sup']['supplier_id']; ?>">
                                    <img src="<?php echo $this->_var['sup']['shop_logo']; ?>" style="display: inline;">
                                </a>
                            </div>
                            <div class="shop-info">
                                <div class="shop-name">
                                    <a class="ng-binding" href="supplier.php?suppId=<?php echo $this->_var['sup']['supplier_id']; ?>"><?php echo $this->_var['sup']['shop_name']; ?>(<font color="#2BB8AA">距离<?php echo $this->_var['sup']['distance_']; ?></font>)</a>
                                </div>
                                <div class="shop-address">
                                    <span class="l ng-binding" style="height:20px;overflow:hidden;"><?php echo $this->_var['sup']['shop_country']; ?>-<?php echo $this->_var['sup']['shop_province']; ?>-<?php echo $this->_var['sup']['shop_city']; ?>-<?php echo sub_str($this->_var['sup']['shop_address'],12); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="shop-products ng-scope">
                            <ul>
                            <?php $_from = $this->_var['sup']['goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');if (count($_from)):
    foreach ($_from AS $this->_var['goods']):
?>
                                <li class="ng-scope">
                                    <a href="goods.php?id=<?php echo $this->_var['goods']['goods_id']; ?>">
                                        <div class="product-thumb">
                                            <img src="<?php if (strpos ( $this->_var['goods']['goods_thumb'] , 'http' ) === 0): ?><?php else: ?><?php echo $this->_var['ROOTPATH']; ?>/<?php endif; ?><?php echo $this->_var['goods']['goods_thumb']; ?>" style="display: inline;">
                                        </div>
                                        <div class="product-name">
                                            <p class="name ng-binding"><?php echo sub_str($this->_var['goods']['goods_name'],12); ?></p>
                                            <p class="price">¥<span class="ng-binding"><?php echo $this->_var['goods']['shop_price']; ?></span></p>
                                        </div>
                                    </a>
                                </li>
                            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                            </ul>
                        </div>

                        <div class="shop-action">
                            <div class="item navigation">
                                <!--a href="supplier_near.php?act=map&supplier_id=<?php echo $this->_var['sup']['supplier_id']; ?>"><i class="icon"></i><?php echo $this->_var['sup']['distance_']; ?></a-->
                                <a href="supplier_near.php?act=map&supplier_id=<?php echo $this->_var['sup']['supplier_id']; ?>"><i class="icon"></i>查看位置</a>
                            </div>
                            <div class="item contact">
                                <a href="tel:<?php echo $this->_var['sup']['service_phone']; ?>"><i class="icon"></i>联系店家</a>
                            </div>
                            <div class="item shop">
                                <a href="supplier.php?suppId=<?php echo $this->_var['sup']['supplier_id']; ?>">
                                <i class="icon"></i>进入店铺</a>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </ul>
            </div>
        <!--</div>-->
    </div>
</div>
<?php endif; ?>