<?php if ($this->_var['promotion_goods']): ?>

<script>
var Tday = new Array();
var daysms = 24 * 60 * 60 * 1000
var hoursms = 60 * 60 * 1000
var Secondms = 60 * 1000
var microsecond = 1000
var DifferHour = -1
var DifferMinute = -1
var DifferSecond = -1
function clock(key)
{
   var time = new Date()
   var hour = time.getHours()
   var minute = time.getMinutes()
   var second = time.getSeconds()
   var timevalue = ""+((hour > 12) ? hour-12:hour)
   timevalue +=((minute < 10) ? ":0":":")+minute
   timevalue +=((second < 10) ? ":0":":")+second
   timevalue +=((hour >12 ) ? " PM":" AM")
   var convertHour = DifferHour
   var convertMinute = DifferMinute
   var convertSecond = DifferSecond
   var Diffms = Tday[key].getTime() - time.getTime()
   DifferHour = Math.floor(Diffms / daysms)
   Diffms -= DifferHour * daysms
   DifferMinute = Math.floor(Diffms / hoursms)
   Diffms -= DifferMinute * hoursms
   DifferSecond = Math.floor(Diffms / Secondms)
   Diffms -= DifferSecond * Secondms
   var dSecs = Math.floor(Diffms / microsecond)
  
      if(convertHour != DifferHour) a=DifferHour+":";
   if(convertMinute != DifferMinute) b=DifferMinute+":";
   if(convertSecond != DifferSecond) c=DifferSecond+":";
     d=dSecs;
     if (DifferHour>0) {a=a}
     else {a=''}
   document.getElementById("jstimerBox"+key).innerHTML = a + b + c + d; //显示倒计时信息
}
</script>
<section class="floor_body">
  <h4><span><?php echo $this->_var['lang']['promotion_goods']; ?></span><i><a href="search.php?intro=promotion">更多</a></i></h4>
    <div id="scroll_promotion" style=" background:#eeeeee">
        <ul>
          <?php $_from = $this->_var['promotion_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'goods');$this->_foreach['promotion_goods'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['promotion_goods']['total'] > 0):
    foreach ($_from AS $this->_var['key'] => $this->_var['goods']):
        $this->_foreach['promotion_goods']['iteration']++;
?>
          <li>
            
             <div class="index_pro"> 
              <div class="products_kuang" style="width: 100%;height: 0; padding-bottom: 100%;overflow: hidden;">
               <?php if ($this->_var['goods']['is_exclusive']): ?>
               <div class="best_phone"> 手机专享</div>
               <?php endif; ?>
              <div class="timerBox" id="jstimerBox<?php echo $this->_var['key']; ?>">正在加载...</div>
              
               <a href="<?php echo $this->_var['goods']['url']; ?>" title="<?php echo htmlspecialchars($this->_var['goods']['name']); ?>"> <img src="<?php echo $this->_var['option']['static_path']; ?><?php echo $this->_var['goods']['thumb']; ?>"></a>
                </div>
              <div class="goods_name"><a href="<?php echo $this->_var['goods']['url']; ?>" title="<?php echo htmlspecialchars($this->_var['goods']['name']); ?>"><?php echo $this->_var['goods']['name']; ?></a></div>
              <div class="price">
              <a href="javascript:addToCart(<?php echo $this->_var['goods']['id']; ?>)" class="btns">
                  <img src="themesmobile/prince_jtypmall_mobile/images/index_flow.png"></a>
                  
                  <span><?php echo $this->_var['goods']['final_price']; ?></span><em><?php if ($this->_var['goods']['promote_price']): ?><?php echo $this->_var['goods']['shop_price']; ?><?php else: ?><?php echo $this->_var['goods']['market_price']; ?><?php endif; ?></em>
              </div>  
</div>
<script>
Tday[<?php echo $this->_var['key']; ?>] = new Date("<?php echo $this->_var['goods']['gmt_end_time']; ?>");  
window.setInterval(function()    
{clock(<?php echo $this->_var['key']; ?>);}, 1000);  
</script>
          </li>
      <?php if ($this->_foreach['promotion_goods']['iteration'] % 3 == 0 && $this->_foreach['promotion_goods']['iteration'] != $this->_foreach['promotion_goods']['total']): ?> </ul>
        <ul>
          <?php endif; ?>
          
          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</div>
  </section>
<?php endif; ?>