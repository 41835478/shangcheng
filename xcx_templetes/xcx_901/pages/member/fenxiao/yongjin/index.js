// pages/member/fenxiao/yongjin/index.js
var server = require('../../../../utils/server');
var app = getApp()
Page({
  data: {
   
  },
 
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    var user_id = getApp().globalData.userInfo.user_id;
    server.getJSON('/Distrib/getDistribInfo/user_id/' + user_id, function (res) {
      var result = res.data.result;

      that.setData({
        result: result

      });
    });

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
    var that = this;

    var login = app.globalData.login;
    var that = this;
    this.setData({ login: login });
    // 调用小程序 API，得到用户信息
    wx.getUserInfo({
      success: ({ userInfo }) => {
        that.setData({
          userInfo: userInfo
        });
        app.globalData.nickName = userInfo.nickName;
      }
    });

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
  
  },
  

  formSubmit: function (e) {
    var that = this;
    var money = e.detail.value.money;
    var user_id = getApp().globalData.userInfo.user_id;
    server.getJSON("/Distrib/getMoneytixian", {money: money,user_id:user_id }, function (res) {
      var result = res.data.result;
      wx.showModal({
        title: '提示:',
        showCancel: false,
        confirmText: '朕知道了',
        content: result,
      })
     
    })
  }
 
})