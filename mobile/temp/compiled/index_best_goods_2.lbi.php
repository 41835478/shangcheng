<li>
           
            <div class="index_pro">
              <div class="products_kuang" style="width: 100%;height: 0; padding-bottom: 100%;overflow: hidden;">
                <?php if ($this->_var['goods']['is_exclusive']): ?> <div class="best_phone">手机专享</div><?php endif; ?>
                <a href="<?php echo $this->_var['goods']['url']; ?>" title="<?php echo htmlspecialchars($this->_var['goods']['name']); ?>"> <img src="<?php echo $this->_var['goods']['thumb']; ?>"></a></div>
              <div class="goods_name"> <a href="<?php echo $this->_var['goods']['url']; ?>" title="<?php echo htmlspecialchars($this->_var['goods']['name']); ?>"><?php echo $this->_var['goods']['name']; ?></a></div>
              <div class="price">
                <a href="javascript:addToCart(<?php echo $this->_var['goods']['id']; ?>)" class="btns">
                    <img src="themesmobile/prince_jtypmall_mobile/images/index_flow.png">
                </a>
                <span><?php echo $this->_var['goods']['final_price']; ?></span><em><?php if ($this->_var['goods']['promote_price']): ?><?php echo $this->_var['goods']['shop_price']; ?><?php else: ?><?php echo $this->_var['goods']['market_price']; ?><?php endif; ?></em>
              </div>
              </div>
        
          </li>
