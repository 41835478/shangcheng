<?php
 $GLOBALS['smarty']->assign('thiscid1',get_wap_parent_id_tree($GLOBALS['smarty']->_var['goods_cat']['id']));
?>
<section class="floor_body">         
      <h4><span><?php echo htmlspecialchars($this->_var['goods_cat']['name']); ?></span><i><a href="<?php echo $this->_var['goods_cat']['url']; ?>">更多</a></i></h4>
        <div style=" background:#eeeeee">
        <ul>
            <?php $_from = $this->_var['cat_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods_0_93870200_1510283506');$this->_foreach['name'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['name']['total'] > 0):
    foreach ($_from AS $this->_var['goods_0_93870200_1510283506']):
        $this->_foreach['name']['iteration']++;
?>
            <li>
               
                <div class="index_pro"> 
                    <div class="products_kuang" style="width: 100%;height: 0; padding-bottom: 100%;overflow: hidden;">
                       <a href="<?php echo $this->_var['goods_0_93870200_1510283506']['url']; ?>" title="<?php echo htmlspecialchars($this->_var['goods_0_93870200_1510283506']['name']); ?>"><img src="<?php echo $this->_var['option']['static_path']; ?><?php echo $this->_var['goods_0_93870200_1510283506']['thumb']; ?>"></a></div>
                    <div class="goods_name"> <a href="<?php echo $this->_var['goods_0_93870200_1510283506']['url']; ?>" title="<?php echo htmlspecialchars($this->_var['goods_0_93870200_1510283506']['name']); ?>"><?php echo $this->_var['goods_0_93870200_1510283506']['name']; ?></a></div>
                    <div class="price">
                            <a href="javascript:addToCart(<?php echo $this->_var['goods_0_93870200_1510283506']['id']; ?>)" class="btns">
                            <img src="themesmobile/prince_jtypmall_mobile/images/index_flow.png"></a>
                      <?php if ($this->_var['goods_0_93870200_1510283506']['promote_price']): ?>
                <span> <?php echo $this->_var['goods_0_93870200_1510283506']['promote_price']; ?></span><em><?php echo $this->_var['goods_0_93870200_1510283506']['shop_price']; ?></em>
               <?php else: ?>
                <span> <?php echo $this->_var['goods_0_93870200_1510283506']['shop_price']; ?></span><em><?php echo $this->_var['goods_0_93870200_1510283506']['market_price']; ?></em>
               <?php endif; ?>
                    </div>
                    </div>
 
            </li>
            
                <?php if ($this->_foreach['name']['iteration'] % 3 == 0 && $this->_foreach['name']['iteration'] != $this->_foreach['name']['total']): ?> </ul>
        <ul>
          <?php endif; ?>
          
          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
 </div>      
</section>




