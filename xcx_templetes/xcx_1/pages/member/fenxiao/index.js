// pages/member/fenxiao/index.js
var server = require('../../../utils/server');
var app = getApp()
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
    var that = this;
    var user_id = getApp().globalData.userInfo.user_id;
    server.getJSON('/Distrib/getDistribInfo/user_id/' + user_id, function (res) {
      var result = res.data.result;
     
     that.setData({
       result: result
  
      });
    });
  },

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

  navigateToshouyi: function () {
    wx.navigateTo({
      url: '../fenxiao/shouyi/index'
    });
  },
  navigateToliushui: function () {
    wx.navigateTo({
      url: '../fenxiao/liushui/index'
    });
  },
  navigateToyongjin: function () {
    wx.navigateTo({
      url: '../fenxiao/yongjin/index'
    });
  },
 
  navigateToxiaji: function () {
    wx.navigateTo({
      url: '../fenxiao/xiaji/index'
    });
  },
  navigateToerweima: function () {
    wx.navigateTo({
      url: '../fenxiao/erweima/index'
    });
  },
  navigateToxinshou: function () {
    wx.navigateTo({
      url: '../fenxiao/xinshou/index'
    });
  },
  navigateToshezhi: function () {
    wx.navigateTo({
      url: '../fenxiao/shezhi/index'
    });
  },
  navigateToshanghuo: function () {
    wx.navigateTo({
      url: '../fenxiao/shanghuo/index'
    });
  },
  navigateToweidian: function () {
    wx.navigateTo({
      url: '../fenxiao/weidian/index'
    });
  },
   
  navigateTomember: function () {
    wx.switchTab({
      url: '../index/index'
    });
  }
  
})