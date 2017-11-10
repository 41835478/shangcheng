var server = require('../../../../utils/server');
Page({
  data:{},
  blur:function(event){
      var value = event.detail.value;
      this.setData({content:value});
  },
  formSubmit:function(event){

    var that = this;
    var app = getApp();
    var timeout = setTimeout(function doHandler()
       {
            var content = that.data.content;
            var user_id = app.globalData.userInfo.user_id
            var goods_id = that.data.goods_id;
            var order_id = that.data.order_id;
            var service_rank = that.data.kfValue;
            var deliver_rank = that.data.shipperValue;
            var goods_rank = that.data.descValue;

            server.getJSON('/User/add_comment',{user_id:user_id,content:content,goods_id:goods_id,order_id:order_id,service_rank:service_rank,deliver_rank:deliver_rank,goods_rank:goods_rank},function(res){
              // success
                wx.showToast({ title: res.data.msg, icon: 'success', duration: 2000 })
                setTimeout(function doHandler(){
                  wx.navigateBack({
                    delta: 1, // 回退前 delta(默认为1) 页面
                    success: function(res){
                      // success
                    },
                    fail: function() {
                      // fail
                    },
                    complete: function() {
                      // complete
                    }
                  })
                },2000);
            });
            
			//console.log(that.data.content);
        },1000);//使用字符串执行方法


      
  },
  onLoad:function(options){
    var goods_name = options.goods_name;
    var goods_id = options.goods_id;
    var order_id = options.order_id;
    var image = options.image;
    var spec = options.spec;
    this.setData({goods_name:goods_name,image:image,spec:spec,goods_id:goods_id,order_id:order_id,starsDescIMG:"/images/stars1.gif",descValue:1,starsKFIMG:"/images/stars1.gif",kfValue:1,starsShipperIMG:"/images/stars1.gif",shipperValue:1});
    // 页面初始化 options为页面跳转所带来的参数
var that = this;
    wx.getSystemInfo({
  success: function(res) {
    that.setData({width:res.windowWidth})
  }
    })
  


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
  },
  starsDesc:function(e){
     var target = e.currentTarget.dataset;
     var width = this.data.width;
     var sWidth = width / 750.0 * 225.0;
     var offsetLeft = e.detail.x - e.currentTarget.offsetLeft;
     var clickPos = 0;
     var starsDesc = "/images/stars1.gif";
     if(sWidth / 5.0 > offsetLeft)
     {
     clickPos = 0;
     starsDesc = "/images/stars1.gif";
     }
     else if(sWidth / 5.0 * 2 > offsetLeft)
     {
       clickPos = 1;
       starsDesc = "/images/stars2.gif";
     }
     else if(sWidth / 5.0 * 3 > offsetLeft)
     {clickPos = 2;
     starsDesc = "/images/stars3.gif";
     }
     else if(sWidth / 5.0 * 4 > offsetLeft)
     {clickPos = 3;
     starsDesc = "/images/stars4.gif";}
     else if(sWidth / 5.0 * 5 > offsetLeft)
     {clickPos = 4;
     starsDesc = "/images/stars5.gif";}
     this.setData({descValue:++clickPos,starsDescIMG:starsDesc});
  },
  starsKF:function(e){
     var target = e.currentTarget.dataset;
     var width = this.data.width;
     var sWidth = width / 750.0 * 225.0;
     var offsetLeft = e.detail.x - e.currentTarget.offsetLeft;
     var clickPos = 0;
     var starsDesc = "/images/stars1.gif";
     if(sWidth / 5.0 > offsetLeft)
     {
     clickPos = 0;
     starsDesc = "/images/stars1.gif";
     }
     else if(sWidth / 5.0 * 2 > offsetLeft)
     {
       clickPos = 1;
       starsDesc = "/images/stars2.gif";
     }
     else if(sWidth / 5.0 * 3 > offsetLeft)
     {clickPos = 2;
     starsDesc = "/images/stars3.gif";
     }
     else if(sWidth / 5.0 * 4 > offsetLeft)
     {clickPos = 3;
     starsDesc = "/images/stars4.gif";}
     else if(sWidth / 5.0 * 5 > offsetLeft)
     {clickPos = 4;
     starsDesc = "/images/stars5.gif";}
     this.setData({kfValue:++clickPos,starsKFIMG:starsDesc});
  },
  starsShipper:function(e){
     var target = e.currentTarget.dataset;
     var width = this.data.width;
     var sWidth = width / 750.0 * 225.0;
     var offsetLeft = e.detail.x - e.currentTarget.offsetLeft;
     var clickPos = 0;
     var starsDesc = "/images/stars1.gif";
     if(sWidth / 5.0 > offsetLeft)
     {
     clickPos = 0;
     starsDesc = "/images/stars1.gif";
     }
     else if(sWidth / 5.0 * 2 > offsetLeft)
     {
       clickPos = 1;
       starsDesc = "/images/stars2.gif";
     }
     else if(sWidth / 5.0 * 3 > offsetLeft)
     {clickPos = 2;
     starsDesc = "/images/stars3.gif";
     }
     else if(sWidth / 5.0 * 4 > offsetLeft)
     {clickPos = 3;
     starsDesc = "/images/stars4.gif";}
     else if(sWidth / 5.0 * 5 > offsetLeft)
     {clickPos = 4;
     starsDesc = "/images/stars5.gif";}
     this.setData({shipperValue:++clickPos,starsShipperIMG:starsDesc});
  }
})