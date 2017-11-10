var server = require('../../../utils/server');
Page({
  data:{},
  onLoad:function(options){
    // 页面初始化 options为页面跳转所带来的参数
    var that = this;
    var app = getApp();
    var order_id = options.order_id;
    var user_id = app.globalData.userInfo.user_id

    server.getJSON('/User/getOrderDetail?user_id=' + user_id + "&id=" + order_id,function(res){
var result = res.data.result
        that.setData({result:result});
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