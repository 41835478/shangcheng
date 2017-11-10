// pages/article/detail/index.js
var server = require('../../../utils/server');
var WxParse = require('../../../wxParse/wxParse.js');//20170807 prince

var objectId;
Page({

  /**
   * 页面的初始数据
   */
  data: {
  
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this
    var app = getApp();
    app.getInviteCode(options);
    syskey = getApp().globalData.syskey;
    var articleId = options.objectId;
    var syskey;
    //this.getGoodsById(goodsId);
    //console.log('picker发送选择改变，携带值为',syskey);

    server.getJSON('/Article/getArticleDetail/article_id/' + articleId + '/syskey/' + syskey, function (res) {
      var articleInfo = res.data.result;
      if (res.data.status == 1) {
        wx.showToast({
          title: res.data.msg,
          icon: 'success',
          duration: 2000
        })
      }
      that.setData({
        article: articleInfo
      });

      
      var article_desc = articleInfo.content;
      WxParse.wxParse('article_desc', 'html', article_desc, that, 5);
    })
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
  
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
  
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
  
  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {
  
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {
  
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
  
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
  
  }
})