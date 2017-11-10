var server = require('../../../utils/server');
Page({
  data:{},
  onLoad:function(options){
    // 页面初始化 options为页面跳转所带来的参数
    var that = this;
    var app = getApp();
    var order_id = options.order_id;
    var user_id = app.globalData.userInfo.user_id

    server.getJSON('/Kuaidi/getKuaidi_List?user_id=' + user_id + "&id=" + order_id,function(res){
    var kuaidi = res.data.result.kuaidi_list
    var shipping_name = res.data.result.shipping_name
    var invoice_no = res.data.result.invoice_no
        that.setData({
          kuaidi:kuaidi,
          shipping_name:shipping_name,
          invoice_no: invoice_no,
          });
    });
    
  },
 
  onReady:function(){
    // 页面渲染完成
  },
  onShow:function(){
    // 页面显示
  },
  onHide:function(){
    // 页面隐藏
  },
  onUnload:function(){
    // 页面关闭
  }
})