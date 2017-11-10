<?php if ($this->_var['hot_goods']): ?>
<section class="index_floor">

    <h4><span><?php echo $this->_var['lang']['hot_goods']; ?></span><i><a href="search.php?intro=hot">更多</a></i></h4>
   
    <div id="scroll_hot" class="scroll_hot">
      <div class="bd">
        <ul>
          <?php $_from = $this->_var['hot_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods_0_93570200_1510283506');$this->_foreach['hot_goods'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['hot_goods']['total'] > 0):
    foreach ($_from AS $this->_var['goods_0_93570200_1510283506']):
        $this->_foreach['hot_goods']['iteration']++;
?>
          <li>
           
             <div class="index_pro"> 
              <div class="products_kuang" style="width: 100%;height: 0; padding-bottom: 100%;overflow: hidden;">
            <?php if ($this->_var['goods_0_93570200_1510283506']['is_exclusive']): ?> <div class="best_phone">手机专享</div><?php endif; ?>
               <a href="<?php echo $this->_var['goods_0_93570200_1510283506']['url']; ?>" title="<?php echo htmlspecialchars($this->_var['goods_0_93570200_1510283506']['name']); ?>">
                <img src="<?php echo $this->_var['option']['static_path']; ?><?php echo $this->_var['goods_0_93570200_1510283506']['thumb']; ?>"> </a></div>
              <div class="goods_name"> <a href="<?php echo $this->_var['goods_0_93570200_1510283506']['url']; ?>" title="<?php echo htmlspecialchars($this->_var['goods_0_93570200_1510283506']['name']); ?>"><?php echo $this->_var['goods_0_93570200_1510283506']['name']; ?></a></div>
              <div class="price">
                   <a href="javascript:addToCart(<?php echo $this->_var['goods_0_93570200_1510283506']['id']; ?>)" class="btns">
                    <img src="themesmobile/prince_jtypmall_mobile/images/index_flow.png">
                </a>
                 <span><?php echo $this->_var['goods_0_93570200_1510283506']['final_price']; ?></span><em><?php if ($this->_var['goods_0_93570200_1510283506']['promote_price']): ?><?php echo $this->_var['goods_0_93570200_1510283506']['shop_price']; ?><?php else: ?><?php echo $this->_var['goods_0_93570200_1510283506']['market_price']; ?><?php endif; ?></em>
              </div>
              </div>
           
          </li>

          <?php if ($this->_foreach['hot_goods']['iteration'] % 3 == 0 && $this->_foreach['hot_goods']['iteration'] != $this->_foreach['hot_goods']['total']): ?> </ul>
        <ul>
          <?php endif; ?><?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?></div>
        <div class="hd">
          <ul></ul>
        </div>
      </div>

  </section>

  <script type="text/javascript">
    TouchSlide({ 
      slideCell:"#scroll_hot",
      titCell:".hd ul", //开启自动分页 autoPage:true ，此时设置 titCell 为导航元素包裹层
      effect:"leftLoop", 
      autoPage:true, //自动分页
      //switchLoad:"_src" //切换加载，真实图片路径为"_src" 
    });
  </script>
<?php endif; ?>