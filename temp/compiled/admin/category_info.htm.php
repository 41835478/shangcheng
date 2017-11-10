<!-- $Id: category_info.htm 16752 2009-10-20 09:59:38Z jtypmall $ -->

<?php echo $this->fetch('pageheader.htm'); ?> 
<script>
function change_link_type(obj)
{
	var id = 'value_' + obj.value
	<?php $_from = $this->_var['link_type']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
	document.getElementById('value_<?php echo $this->_var['key']; ?>').style.display = 'none'
	<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
	document.getElementById(id).style.display = ''
}
</script>
<!-- start add new category form -->
<div class="main-div">
  <form action="category.php" method="post" name="theForm" enctype="multipart/form-data" onsubmit="return validate()">
    <table width="100%" id="general-table">
      <tr>
        <td class="label"><?php echo $this->_var['lang']['cat_name']; ?>:</td>
        <td><textarea name='cat_name' rows="3" cols="48"><?php echo htmlspecialchars($this->_var['cat_info']['cat_name']); ?></textarea>
          <font color="red">*</font> <span class="notice-span" <?php if ($this->_var['help_open']): ?>style="display:block" <?php else: ?> style="display:none" <?php endif; ?> id="noticeCat_name"><?php echo $this->_var['lang']['notice_cat_name']; ?></span></td>
      </tr>
      <!--小程序新增 start-->
	  <tr>
        <td class="label">小程序简称(四词为佳):</td>
        <td>
          <input type='text' name='xcx_cat_name' maxlength="20" value='<?php echo htmlspecialchars($this->_var['cat_info']['xcx_cat_name']); ?>' size='27' />
		  <span class="notice-span" >该名称为分类的简称，用于显示在小程序中，四个汉字为佳。</span>
        </td>
      </tr>
	  <tr>
        <td class="label">是否在小程序中显示:</td>
        <td>
          <input type="radio" name="is_xcx_show" value="1" <?php if ($this->_var['cat_info']['is_xcx_show'] != 0): ?> checked="true"<?php endif; ?>/>
          <?php echo $this->_var['lang']['yes']; ?>
          <input type="radio" name="is_xcx_show" value="0" <?php if ($this->_var['cat_info']['is_xcx_show'] == 0): ?> checked="true"<?php endif; ?> />
          <?php echo $this->_var['lang']['no']; ?> 
        </td>
      </tr>
      <!--小程序新增 end-->

	 
	  <tr style="display:none;">
        <td class="label">目录名称:</td>
        <td>
          <input type='text' name='path_name' maxlength="20" value='<?php echo htmlspecialchars($this->_var['cat_info']['path_name']); ?>' size='27' />
		  <span class="notice-span" <?php if ($this->_var['help_open']): ?>style="display:block" <?php else: ?> style="display:none" <?php endif; ?> id="noticePathname">生成的【真静态HTML文件】将保存到该目录下<br>例如：在这里输入 jiaju，根目录下就会生成一个 category-jiaju 的二级目录用来保存纯静态HTML文件</span>
        </td>
      </tr>
   
      <tr>
        <td class="label">分类图标</td>
        <td><input type="file" name="type_img" id="thumb" size="45"><?php if ($this->_var['cat_info']['type_img'] != ""): ?><input type="button" value="删除" onclick="if (confirm('确认删除分类图标吗?'))location.href='category.php?act=drop_thumb&id=<?php echo $this->_var['cat_info']['cat_id']; ?>'"><?php endif; ?>
        <br />
        <?php if ($this->_var['cat_info']['type_img']): ?>
        <img src="/<?php echo $this->_var['cat_info']['type_img']; ?>"  style="width:100px; height:auto"/>
        <?php endif; ?>
        <p>
        <span style=" color:#F30;">*&nbsp;注:&nbsp;只需给二、三级分类添加分类图标，二级分类图标在小程序中显示，三级分类图标在手机端显示。</span>
        </p>
        </td>
      </tr>
      <tr>
        <td class="label">小程序中此类广告图</td>
        <td><input type="file" name="cat_adimg_1" id="cat_adimg_1" size="45"><?php if ($this->_var['cat_info']['cat_adimg_1'] != ""): ?><input type="button" value="删除" onclick="if (confirm('确认删除分类图标吗?'))location.href='category.php?act=drop_adimg_1&id=<?php echo $this->_var['cat_info']['cat_id']; ?>'"><?php endif; ?>
        <?php if ($this->_var['cat_info']['cat_adimg_1']): ?>
        <br />
        <img src="/<?php echo $this->_var['cat_info']['cat_adimg_1']; ?>" style="width:100px; height:auto"/>
        <?php endif; ?>
        <br />
        *仅支持顶级分类设置广告
        </td>
      </tr>
  <tr>
  	<td align="left" class="label">广告链接地址：</td>
    <td>
		<select name="type" onchange="change_link_type(this)">
		<?php $_from = $this->_var['link_type']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
		<option value="<?php echo $this->_var['key']; ?>" <?php if ($this->_var['menu']['type'] == $this->_var['key']): ?>selected<?php endif; ?> ><?php echo $this->_var['item']['name']; ?></option>
		<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
		</select>
		<?php $_from = $this->_var['link_type']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
		<?php if ($this->_var['item']['options']): ?>
		<select id="value_<?php echo $this->_var['key']; ?>" name="value[<?php echo $this->_var['key']; ?>]" <?php if ($this->_var['menu']['type'] != $this->_var['key']): ?>style="display:none;"<?php endif; ?>>
		<?php echo $this->_var['item']['options']; ?>
		</select>
		<?php else: ?>
		<input id="value_<?php echo $this->_var['key']; ?>" type="text" name="value[<?php echo $this->_var['key']; ?>]" <?php if ($this->_var['menu']['type'] == $this->_var['key']): ?>value="<?php echo $this->_var['menu']['link']; ?>"<?php else: ?>value="<?php echo $this->_var['item']['value']; ?>"<?php endif; ?> <?php if ($this->_var['menu']['type'] != $this->_var['key']): ?>style="display:none;"<?php endif; ?>/>
		<?php endif; ?>
		<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        <br /><span>小程序的页面链接非普通的链接，小程序的链接类似:pages/index/index (不带参数) 或pages/goods/detail/detail?objectId=1 (带参数)
        <br />若选择自定义，可以使用微信小程序开发工具打开某个页面进行获取页面路径及参数</span>
    </td>
  </tr>
      <tr>
        <td class="label"><?php echo $this->_var['lang']['parent_id']; ?>:</td>
        <td><select name="parent_id">
            <option value="0"><?php echo $this->_var['lang']['cat_top']; ?></option>
            
            <?php echo $this->_var['cat_select']; ?>
          
          </select></td>
      </tr>
      <tr id="measure_unit">
        <td class="label"><?php echo $this->_var['lang']['measure_unit']; ?>:</td>
        <td><input type="text" name='measure_unit' value='<?php echo $this->_var['cat_info']['measure_unit']; ?>' size="12" /></td>
      </tr>
      <tr>
        <td class="label"><?php echo $this->_var['lang']['sort_order']; ?>:</td>
        <td><input type="text" name='sort_order' <?php if ($this->_var['cat_info']['sort_order']): ?>value='<?php echo $this->_var['cat_info']['sort_order']; ?>'<?php else: ?> value="50"<?php endif; ?> size="15" /></td>
      </tr>
      <tr>
        <td class="label"><?php echo $this->_var['lang']['is_show']; ?>:</td>
        <td><input type="radio" name="is_show" value="1" <?php if ($this->_var['cat_info']['is_show'] != 0): ?> checked="true"<?php endif; ?>/>
          <?php echo $this->_var['lang']['yes']; ?>
          <input type="radio" name="is_show" value="0" <?php if ($this->_var['cat_info']['is_show'] == 0): ?> checked="true"<?php endif; ?> />
          <?php echo $this->_var['lang']['no']; ?> </td>
      </tr>
      <tr>
        <td class="label"><?php echo $this->_var['lang']['show_in_nav']; ?>:</td>
        <td><input type="radio" name="show_in_nav" value="1" <?php if ($this->_var['cat_info']['show_in_nav'] != 0): ?> checked="true"<?php endif; ?>/>
          <?php echo $this->_var['lang']['yes']; ?>
          <input type="radio" name="show_in_nav" value="0" <?php if ($this->_var['cat_info']['show_in_nav'] == 0): ?> checked="true"<?php endif; ?> />
          <?php echo $this->_var['lang']['no']; ?> </td>
      </tr>
      <tr>
        <td class="label"><?php echo $this->_var['lang']['show_in_index']; ?>:</td>
        <td><input type="checkbox" name="cat_recommend[]" value="1" <?php if ($this->_var['cat_recommend'] [ 1 ] == 1): ?> checked="true"<?php endif; ?>/>
          <?php echo $this->_var['lang']['index_best']; ?>
          <input type="checkbox" name="cat_recommend[]" value="2" <?php if ($this->_var['cat_recommend'] [ 2 ] == 1): ?> checked="true"<?php endif; ?> />
          <?php echo $this->_var['lang']['index_new']; ?>
          <input type="checkbox" name="cat_recommend[]" value="3" <?php if ($this->_var['cat_recommend'] [ 3 ] == 1): ?> checked="true"<?php endif; ?> />
          <?php echo $this->_var['lang']['index_hot']; ?> </td>
      </tr>
      <tr>
        <td class="label"><a href="javascript:showNotice('noticeFilterAttr');" title="<?php echo $this->_var['lang']['form_notice']; ?>"><img src="images/notice.gif" width="16" height="16" border="0" alt="<?php echo $this->_var['lang']['notice_style']; ?>"></a><?php echo $this->_var['lang']['filter_attr']; ?>:</td>
        <td><script type="text/javascript">
          var arr = new Array();
          var sel_filter_attr = "<?php echo $this->_var['lang']['sel_filter_attr']; ?>";
          <?php $_from = $this->_var['attr_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('att_cat_id', 'val');if (count($_from)):
    foreach ($_from AS $this->_var['att_cat_id'] => $this->_var['val']):
?>
            arr[<?php echo $this->_var['att_cat_id']; ?>] = new Array();
            <?php $_from = $this->_var['val']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('i', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['i'] => $this->_var['item']):
?>
              <?php $_from = $this->_var['item']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('attr_id', 'attr_val');if (count($_from)):
    foreach ($_from AS $this->_var['attr_id'] => $this->_var['attr_val']):
?>
                arr[<?php echo $this->_var['att_cat_id']; ?>][<?php echo $this->_var['i']; ?>] = ["<?php echo $this->_var['attr_val']; ?>", <?php echo $this->_var['attr_id']; ?>];
              <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

          function changeCat(obj)
          {
            var key = obj.value;
            var sel = window.ActiveXObject ? obj.parentNode.childNodes[4] : obj.parentNode.childNodes[5];
            sel.length = 0;
            sel.options[0] = new Option(sel_filter_attr, 0);
            if (arr[key] == undefined)
            {
              return;
            }
            for (var i= 0; i < arr[key].length ;i++ )
            {
              sel.options[i+1] = new Option(arr[key][i][0], arr[key][i][1]);
            }

          }

          </script>

         
          <table width="100%" id="tbody-attr" align="center" class="no_border">
            <?php if ($this->_var['attr_cat_id'] == 0): ?>
            <tr>
              <td>   
                   <a href="javascript:;" onclick="addFilterAttr(this)">[+]</a> 
                   <select onChange="changeCat(this)"><option value="0"><?php echo $this->_var['lang']['sel_goods_type']; ?></option><?php echo $this->_var['goods_type_list']; ?></select>&nbsp;&nbsp;
                   <select name="filter_attr[]"><option value="0"><?php echo $this->_var['lang']['sel_filter_attr']; ?></option></select><br />                   
              </td>
            </tr> 
            <?php endif; ?>           
            <?php $_from = $this->_var['filter_attr_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'filter_attr');$this->_foreach['filter_attr_tab'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['filter_attr_tab']['total'] > 0):
    foreach ($_from AS $this->_var['filter_attr']):
        $this->_foreach['filter_attr_tab']['iteration']++;
?>
            <tr>
              <td>
                 <?php if ($this->_foreach['filter_attr_tab']['iteration'] == 1): ?>
                   <a href="javascript:;" onclick="addFilterAttr(this)">[+]</a>
                 <?php else: ?>
                   <a href="javascript:;" onclick="removeFilterAttr(this)">[-]&nbsp;</a>
                 <?php endif; ?>
                 <select onChange="changeCat(this)"><option value="0"><?php echo $this->_var['lang']['sel_goods_type']; ?></option><?php echo $this->_var['filter_attr']['goods_type_list']; ?></select>&nbsp;&nbsp;
                 <select name="filter_attr[]"><option value="0"><?php echo $this->_var['lang']['sel_filter_attr']; ?></option><?php echo $this->html_options(array('options'=>$this->_var['filter_attr']['option'],'selected'=>$this->_var['filter_attr']['filter_attr'])); ?></select><br />
              </td>
            </tr>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
          </table>
          <span class="notice-span" <?php if ($this->_var['help_open']): ?>style="display:block" <?php else: ?> style="display:none" <?php endif; ?> id="noticeFilterAttr"><?php echo $this->_var['lang']['filter_attr_notic']; ?></span></td>
      </tr>
       
<!--隐藏lw	  <tr>
		<td class="label">品牌排序:</td>
        <td>
          <input type="text" name="brand_wwwwx_120029121com" size=60 value="<?php echo $this->_var['cat_info']['brand_qq']; ?>" /> (半角逗号间隔)
        </td>
	  </tr>
	   <tr>
		<td class="label">属性排序:</td>
        <td>
          <textarea name='attr_qq' rows="6" cols="68"><?php echo $this->_var['cat_info']['attr_qq120029121']; ?></textarea>
		  <br>
		  格式参考： 注意每个属性栏目占一行，如果一行乘不下，别管它，它会自动换行，只要没到下个属性栏目之前别按回车键就行<br><font color=#ff3300>颜色:红色,蓝色,白色,棕色,紫色<br>尺码:M,L,XL,XXL,XXL</font>
        </td>
	  </tr>-->
	  
      <tr>
        <td class="label"><a href="javascript:showNotice('noticeGrade');" title="<?php echo $this->_var['lang']['form_notice']; ?>"><img src="images/notice.gif" width="16" height="16" border="0" alt="<?php echo $this->_var['lang']['notice_style']; ?>"></a><?php echo $this->_var['lang']['grade']; ?>:</td>
        <td><input type="text" name="grade" value="<?php echo empty($this->_var['cat_info']['grade']) ? '0' : $this->_var['cat_info']['grade']; ?>" size="40" />
          <br />
          <span class="notice-span" <?php if ($this->_var['help_open']): ?>style="display:block" <?php else: ?> style="display:none" <?php endif; ?> id="noticeGrade"><?php echo $this->_var['lang']['notice_grade']; ?></span></td>
      </tr>
      <tr>
        <td class="label"><a href="javascript:showNotice('noticeGoodsSN');" title="<?php echo $this->_var['lang']['form_notice']; ?>"><img src="images/notice.gif" width="16" height="16" border="0" alt="<?php echo $this->_var['lang']['notice_style']; ?>"></a><?php echo $this->_var['lang']['cat_style']; ?>:</td>
        <td><input type="text" name="style" value="<?php echo htmlspecialchars($this->_var['cat_info']['style']); ?>" size="40" />
          <br />
          <span class="notice-span" <?php if ($this->_var['help_open']): ?>style="display:block" <?php else: ?> style="display:none" <?php endif; ?> id="noticeGoodsSN"><?php echo $this->_var['lang']['notice_style']; ?></span></td>
      </tr>
      <tr>
        <td class="label"><?php echo $this->_var['lang']['keywords']; ?>:</td>
        <td><input type="text" name="keywords" value='<?php echo $this->_var['cat_info']['keywords']; ?>' size="50"></td>
      </tr>
      <tr>
        <td class="label"><?php echo $this->_var['lang']['cat_desc']; ?>:</td>
        <td><textarea name='cat_desc' rows="6" cols="48"><?php echo $this->_var['cat_info']['cat_desc']; ?></textarea></td>
      </tr>
      
      <!--代码修改_start Byjdy--> 
      <?php if ($this->_var['is_topcat'] == '1'): ?>
      <tr>
        <td class="label"><font color=#ff3300>是否启用频道首页:</font></td>
        <td>
          <input type="radio" name="category_index" value="1" <?php if ($this->_var['cat_info']['category_index'] == '1'): ?>checked=checked<?php endif; ?> onclick="_index_dwt('index_dwt', 1)" >启用
		  <input type="radio" name="category_index" value="0" <?php if (! $this->_var['cat_info']['category_index']): ?>checked=checked<?php endif; ?> onclick="_index_dwt('index_dwt', 0); _index_dwt('index_dwt_file', 0); change_dwt()" >不启用
		   <br>
		  <span class="notice-span" <?php if ($this->_var['help_open']): ?>style="display:block" <?php else: ?> style="display:none" <?php endif; ?> id="noticeGoodsSN">注意：只有顶级分类才需要设置“是否启用”，如果您正在编辑的分类属于二级分类，请不用理会这个选项。</span>
        </td>
      </tr>
	  <tr id="index_dwt" style="display:<?php if ($this->_var['cat_info']['category_index'] == '1'): ?><?php else: ?>none<?php endif; ?>">
        <td class="label"><font color=#ff3300>是否更改频道首页默认模版:</font></td>
        <td>
          <input id="index_dwt_1" type="radio" name="category_index_dwt" value="1" <?php if ($this->_var['cat_info']['category_index_dwt'] == '1'): ?>checked=checked<?php endif; ?> onclick="_index_dwt('index_dwt_file', 1)" >更改
		  <input id="index_dwt_0" type="radio" name="category_index_dwt" value="0" <?php if (! $this->_var['cat_info']['category_index_dwt']): ?>checked=checked<?php endif; ?> onclick="_index_dwt('index_dwt_file', 0)" >使用默认
		   <br>
		  <span class="notice-span" <?php if ($this->_var['help_open']): ?>style="display:block" <?php else: ?> style="display:none" <?php endif; ?> id="noticeGoodsSN">注意：只有启用频道首页才需要设置“是否更改”。</span>
        </td>
      </tr>
	  <tr id="index_dwt_file" style="display:<?php if ($this->_var['cat_info']['category_index'] == '1' && $this->_var['cat_info']['category_index_dwt'] == '1'): ?><?php else: ?>none<?php endif; ?>">
        <td class="label"><font color=#ff3300>选择模版文件:</font></td>
        <td>
		  <input type="text" name="index_dwt_file" value="<?php echo htmlspecialchars($this->_var['cat_info']['index_dwt_file']); ?>" size="40" /><br />
          <span class="notice-span" <?php if ($this->_var['help_open']): ?>style="display:block" <?php else: ?> style="display:none" <?php endif; ?> id="noticeGoodsSN">
			您可以为频道首页指定模版文件，文件请存放在模版文件夹目录下，此处输入文件名，例如category-2.dwt
		  </span>
        </td>
      </tr>	  
	  <?php endif; ?>
	  <tr>
        <td class="label"><font color=#ff3300>在频道首页显示该二级分类:</font></td>
        <td><input type="radio" name="show_in_index" value="1" onclick="show_goods_num_area(1)" <?php if ($this->_var['cat_info']['show_in_index'] == '1'): ?>checked=checked<?php endif; ?> >
          显示
          <input type="radio" name="show_in_index" value="0" onclick="show_goods_num_area(0)" <?php if (! $this->_var['cat_info']['show_in_index']): ?>checked=checked<?php endif; ?>>
          不显示 <br>
          <span class="notice-span" <?php if ($this->_var['help_open']): ?>style="display:block" <?php else: ?> style="display:none" <?php endif; ?> id="noticeGoodsSN">注意：只有二级分类才需要设置“是否显示</span></td>
      </tr>
        <tr id="goods_num_area" <?php if ($this->_var['cat_info']['show_in_index'] != '1'): ?>style="display:none"<?php endif; ?>>
            <td class="label"><font color=#ff3300>频道首页该分类下显示商品数量:</font></td>
            <td><input type="text" name="show_goods_num" value='<?php echo $this->_var['cat_info']['show_goods_num']; ?>' size="15"><br />
            <span class="notice-span" <?php if ($this->_var['help_open']): ?>style="display:block" <?php else: ?> style="display:none" <?php endif; ?> id="noticeGoodsSN">例如：8，该分类在顶级频道以楼层显示时至多显示8个商品</span></td>
        </tr>
    </table>
    <div class="button-div">
      <input type="submit" class="button" value="<?php echo $this->_var['lang']['button_submit']; ?>" />
      <input type="reset" class="button" value="<?php echo $this->_var['lang']['button_reset']; ?>" />
    </div>
    <input type="hidden" name="act" value="<?php echo $this->_var['form_act']; ?>" />
    <input type="hidden" name="old_cat_name" value="<?php echo $this->_var['cat_info']['cat_name']; ?>" />
    <input type="hidden" name="cat_id" value="<?php echo $this->_var['cat_info']['cat_id']; ?>" />
    <input type="hidden" name="is_virtual" value="<?php echo $this->_var['is_virtual']; ?>" />
  </form>
</div>
<?php echo $this->smarty_insert_scripts(array('files'=>'../js/utils.js,validator.js')); ?>
 
<script language="JavaScript">
<!--
document.forms['theForm'].elements['cat_name'].focus();
/**
 * 检查表单输入的数据
 */
function validate()
{
  validator = new Validator("theForm");
  validator.required("cat_name",      catname_empty);
  if (parseInt(document.forms['theForm'].elements['grade'].value) >10 || parseInt(document.forms['theForm'].elements['grade'].value) < 0)
  {
    validator.addErrorMsg('<?php echo $this->_var['lang']['grade_error']; ?>');
  }
  return validator.passed();
}
onload = function()
{
  // 开始检查订单
  startCheckOrder();
}

/**
 * 新增一个筛选属性
 */
function addFilterAttr(obj)
{
  var src = obj.parentNode.parentNode;
  var tbl = document.getElementById('tbody-attr');

  var validator  = new Validator('theForm');
  var filterAttr = document.getElementsByName("filter_attr[]");

  if (filterAttr[filterAttr.length-1].selectedIndex == 0)
  {
    validator.addErrorMsg(filter_attr_not_selected);
  }
  
  for (i = 0; i < filterAttr.length; i++)
  {
    for (j = i + 1; j <filterAttr.length; j++)
    {
      if (filterAttr.item(i).value == filterAttr.item(j).value)
      {
        validator.addErrorMsg(filter_attr_not_repeated);
      } 
    } 
  }

  if (!validator.passed())
  {
    return false;
  }

  var row  = tbl.insertRow(tbl.rows.length);
  var cell = row.insertCell(-1);
  cell.innerHTML = src.cells[0].innerHTML.replace(/(.*)(addFilterAttr)(.*)(\[)(\+)/i, "$1removeFilterAttr$3$4-");
  filterAttr[filterAttr.length-1].selectedIndex = 0;
}

/**
 * 删除一个筛选属性
 */
function removeFilterAttr(obj)
{
  var row = rowindex(obj.parentNode.parentNode);
  var tbl = document.getElementById('tbody-attr');

  tbl.deleteRow(row);
}

function _index_dwt(id, type)
{
	if(type == 0){
		$("[name='style']").val("category_index.css");
	}
	document.getElementById(id).style.display = (type == 1 ? "" : "none");
}

function change_dwt()
{
	document.getElementById("index_dwt_0").checked = true;
	document.getElementById("index_dwt_1").checked = false;
}

function show_goods_num_area(show_in_index)
{
    if (show_in_index == 1)
    {
        document.getElementById('goods_num_area').style.display='';
    }
    else
    {
        document.getElementById('goods_num_area').style.display='none';
    }
}
//-->
</script> 

<?php echo $this->fetch('pagefooter.htm'); ?>