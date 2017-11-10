// pages/member/fenxiao/liushui/index.js
var server = require('../../../../utils/server');
var cPage = 0;
//var ctype = "1";
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
    cPage = 0;
    this.getMoneyInfoList(0);

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
    cPage = 0;
    this.data.accounts = [];
    this.getMoneyInfoList(0);
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    this.getMoneyInfoList(++cPage);
    wx.showToast({
      title: '加载中',
      icon: 'loading'
    })
  },


  getMoneyInfoList(page) {
    console.log(page);

    var that = this;
    var user_id = getApp().globalData.userInfo.user_id
    //var moneys = app.globalData.userInfo.user_money

    var that = this;
    var user_id = getApp().globalData.userInfo.user_id
    server.getJSON('/Distrib/getUsernotes/user_id/' + user_id + "/page/" + page, function (res) {
      var result = res.data.result;
      var summoney = res.data.summoney;
      
      that.setData({
        result: result,
        summoney: summoney,

      });
    });


  },
  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})