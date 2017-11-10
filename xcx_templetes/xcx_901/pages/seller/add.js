// pages/seller/add.js
var server = require('../../utils/server');
Page({
  data:{},
  onLoad:function(options){
    // 页面初始化 options为页面跳转所带来的参数
var self = this;
var openId = getApp().globalData.openid;
server.getJSON("/User/validateOpenid",{openid:openId},function(res){

				if (res.data.code == 200) {
						getApp().globalData.userInfo = res.data.data;
						getApp().globalData.login = true;
						var apply = getApp().globalData.userInfo.apply;
    self.setData({apply:apply});
					}
					
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
  },
  sellerName:function(e){
    var sellerName = e.detail.value;
    this.setData({sellerName:sellerName});
  },
  sellerXM:function(e){
    var sellerXM = e.detail.value;
    this.setData({sellerXM:sellerXM});
  },
  sellerDesc:function(e){
    var sellerDesc = e.detail.value;
    this.setData({sellerDesc:sellerDesc});
  },
  sellercContactName:function(e){
    var sellercContactName = e.detail.value;
    this.setData({sellercContactName:sellercContactName});
  },
  sellercContactPhone:function(e){
    var sellercContactPhone = e.detail.value;
    this.setData({sellercContactPhone:sellercContactPhone});
  },
  sumbit:function(e){
  
    var apply = this.data.apply;
    var id ;
    if(apply == undefined){
      id = 0;
    }else{
      id = apply.id;
    }
    var sellerName = this.data.sellerName;
    var sellerXM = this.data.sellerXM;
    var sellerDesc = this.data.sellerDesc;
    var sellercContactName = this.data.sellercContactName;
    var sellercContactPhone = this.data.sellercContactPhone;
    var user_id = getApp().globalData.userInfo.user_id
		
    if(apply != undefined){

    if(sellerName == undefined){
      sellerName = apply.sellername;
    }
    if(sellerXM == undefined){
      sellerXM = apply.sellerxm;
    }
    if(sellerDesc == undefined){
      sellerDesc = apply.sellerdesc;
    }
    if(sellercContactName == undefined){
     sellercContactName = apply.sellerccontactname;
    }
    if(sellercContactPhone == undefined){
      sellercContactPhone = apply.sellerccontactphone;
    }
    }

    if(sellerName == undefined){
      wx.showToast({title:'请输入商户名称'});
      return ;
    }
    if(sellerXM == undefined){
      wx.showToast({title:'请输入主营项目'});
      return ;
    }
    if(sellerDesc == undefined){
      wx.showToast({title:'请输入简单介绍'});
      return ;
    }
    if(sellercContactName == undefined){
      wx.showToast({title:'请输入联系人姓名'});
      return ;
    }
    if(sellercContactPhone == undefined){
      wx.showToast({title:'请输入您的电话'});
      return ;
    }
  
    /*sellerName = encodeURI(sellerName);
    sellerXM = encodeURI(sellerXM);
    sellerDesc = encodeURI(sellerDesc);
    sellercContactName = encodeURI(sellercContactName);
    sellercContactPhone = encodeURI(sellercContactPhone);
*/

    server.getJSON("/User/apply",{sellerName:sellerName,sellerXM:sellerXM,sellerDesc:sellerDesc,sellercContactName:sellercContactName,sellercContactPhone:sellercContactPhone,user_id:user_id,aid:id},function(res){
      wx.showToast({title:"成功"});
      setTimeout(function(res){
        wx.navigateBack({
          delta: 1, // 回退前 delta(默认为1) 页面
          success: function(res){
            // success
          },
          fail: function(res) {
            // fail
          },
          complete: function(res) {
            // complete
          }
        })
      },1000)
    });

  }
})