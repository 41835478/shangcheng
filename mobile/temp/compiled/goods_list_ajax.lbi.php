<?php if ($this->_var['goods']['goods_id']): ?>

<li>
<div class="item">
<div class="pic_box">

<?php if ($this->_var['goods']['is_best'] == 1): ?>
<span style=" background-position:0px -70px">精品</span>
<?php elseif ($this->_var['goods']['is_new'] == 1): ?>
<span style=" background-position:0px -35px">新品</span>
<?php elseif ($this->_var['goods']['is_hot'] == 1): ?>
<span style=" background-position:0px 0px">热卖</span>
<?php endif; ?>

</div>

<div class="goods_images">

<?php if ($this->_var['goods']['is_exclusive']): ?> <div class="best_phone">手机专享</div><?php endif; ?> 
<a href="<?php echo $this->_var['goods']['url']; ?>"><img src="<?php echo $this->_var['goods']['goods_thumb']; ?>"></a>
</div>
<dl>
<dt><a href="<?php echo $this->_var['goods']['url']; ?>"><?php echo $this->_var['goods']['goods_name']; ?></a></dt>
<dd><i><?php if ($this->_var['goods']['final_price']): ?><?php echo $this->_var['goods']['final_price']; ?><?php elseif ($this->_var['goods']['promote_price']): ?><?php echo $this->_var['goods']['promote_price']; ?><?php else: ?><?php echo $this->_var['goods']['shop_price']; ?><?php endif; ?></i>
<em><?php if ($this->_var['goods']['promote_price']): ?><?php echo $this->_var['goods']['shop_price']; ?><?php else: ?><?php echo $this->_var['goods']['market_price']; ?><?php endif; ?></em></dd>
</dl>
</div>

<div class="goods_number">
<div class="ui-number"> 
<a class="decrease" onclick="goods_cut(<?php echo $this->_var['goods']['goods_id']; ?>);">-</a>
<input class="num" id="number_<?php echo $this->_var['goods']['goods_id']; ?>" type="text" onblur="changePrice();" value="1" onfocus="if(value=='1') {value=''}" size="4" maxlength="5"  />
<a class="increase" onclick="goods_add(<?php echo $this->_var['goods']['goods_id']; ?>);">+</a> 
</div>
</div>
<span class="bug_car" onClick="addToCart(<?php echo $this->_var['goods']['goods_id']; ?>)">
<img src="themesmobile/prince_jtypmall_mobile/images/index_flow.png">
</span>

</li>

<?php endif; ?>
	
