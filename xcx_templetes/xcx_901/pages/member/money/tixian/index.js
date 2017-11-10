// pages/member/money/tixian/index.js
var server = require('../../../../utils/server');
var app = getApp()
Page({
 
  /**
   * 页面的初始数据
   */
  data: {
    array: ["微信", "支付宝", "银行卡"],
    toast1Hidden: true,
    modalHidden: true,
    modalHidden2: true,
    notice_str: '',
    index: 0  
  },
  toast1Change: function (e) {
    this.setData({ toast1Hidden: true });
  },
  //弹出确认框  
  modalTap: function (e) {
    this.setData({
      modalHidden: false
    })
  },

  cancel_one: function (e) {
    console.log(e);
    this.setData({
      modalHidden: true,
      toast1Hidden: false,
      notice_str: '取消成功'
    });
  },
  //弹出提示框  
  modalTap2: function (e) {
    this.setData({
      modalHidden2: false
    })
  },
  modalChange2: function (e) {
    this.setData({
      modalHidden2: true
    })
  },
  bindPickerChange: function (e) {
    console.log('picker发送选择改变，携带值为', e.detail.value)
    this.setData({
      index: e.detail.value
    })
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    var nickname = getApp().globalData.userInfo.nickname
    this.setData({
      nickname: nickname
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
  
  },
  formSubmit: function (e) {
    var that = this;
    var user_id = getApp().globalData.userInfo.user_id;
    var open_id = getApp().globalData.userInfo.open_id;
    var money = e.detail.value.money;
    var mobile_phone = e.detail.value.mobile_phone;
    var user_name = e.detail.value.user_name;
    var account_type = e.detail.value.account_type;
    var account = e.detail.value.account;
    var user_note = e.detail.value.user_note;
    console.log('携带值为', open_id)
    server.postJSON('/User/Userdeposit/user_id/' + user_id, { user_id: user_id, open_id: open_id, mobile_phone: mobile_phone, user_name: user_name, money: money, account_type: account_type, account: account, user_note: user_note }, function (res) {

      var result = res.data.result;
      wx.showModal({
        title: '提示',
        showCancel: false,
        confirmText: '我知道了',
        content: result,
      });
      if (res.data.status == 1) {
        wx.switchTab({
              url: '../../../member/index/index'
            });
      }

    });
  },
  
})