var server = require('../../utils/server');
Page({
  data: {},
  onLoad: function (options) {
    // 页面初始化 options为页面跳转所带来的参数
    var that = this;
    server.getJSON("/Store/getStoreClass", function (res) {
      var store_class = res.data;
      for (var i = 0; i < store_class.length; i++) {
        if (i == 0) {
          store_class[i].select = 1;
          that.getStoreList(store_class[i].str_id);
        }
        else {
          store_class[i].select = 0;
        }
      }
      that.setData({ store_class: store_class });
    });
  },
  getStoreList: function (str_id) {
    var that = this;
    server.getJSON("/Store/getStores", { cid: str_id }, function (res) {
      var supplier = res.data;
      that.setData({
         supplier: supplier
      
         });
    });
  },
  onReady: function () {
    // 页面渲染完成
  },
  onShow: function () {
    // 页面显示
  },
  onHide: function () {
    // 页面隐藏
  },
  onUnload: function () {
    // 页面关闭
  },
  goods: function (e) {
    var id = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: 'goods?id=' + id,
      success: function (res) {
        // success
      },
      fail: function () {
        // fail
      },
      complete: function () {
        // complete
      }
    })
  },
  showDetail: function (e) {
    var goodsId = e.currentTarget.dataset.goodsId;
    wx.navigateTo({
      url: "../goods/detail/detail?objectId=" + goodsId
    });
  },
  take: function (e) {
    var phone = e.currentTarget.dataset.phone;
    wx.makePhoneCall({
      phoneNumber: phone //仅为示例，并非真实的电话号码
    })
  },
  onClickClass: function (e) {
    var class_id = e.currentTarget.dataset.id;
    var store_class = this.data.store_class;
    for (var i = 0; i < store_class.length; i++) {
      if (store_class[i].str_id == class_id) {
        store_class[i].select = 1;
        this.getStoreList(store_class[i].str_id);
      }
      else {
        store_class[i].select = 0;
      }
    }
    this.setData({ store_class: store_class });
  },

  onShareAppMessage: function (res) {

    return {
      title: '优品小程序系统',
      desc: '联系QQ:309485552 或 120029121',
      path: '/pages/index/index?uid=118',
      success: function (res) {
        var openId = getApp().globalData.openid; // 转发成功请求服务器
        server.getJSON("/Store/share", { openid: openId }, function (res) {
          var share_jf = res.data.share_jf;
          wx.showModal({
            title: '提示',
            showCancel: false,
            confirmText: '朕知道了',
            content: share_jf,
          })
        })
      },
      fail: function (res) {
        wx.showModal({
          title: '提示',
          showCancel: false,
          confirmText: '下次再说',
          content: '您取消了分享！！',

        })
        // 转发失败
      }
    }
  }

})