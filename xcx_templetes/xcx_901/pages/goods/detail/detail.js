var server = require('../../../utils/server');
var WxParse = require('../../../wxParse/wxParse.js');//20170807 prince

var objectId;
Page({
  data: {
    goods: {},
    current: 0,
    tabStates: [true, false, false],
    tabClasss: ["text-select", "text-normal", "text-normal"],
    galleryHeight: getApp().screenWidth,
    tab: 0,
    goods_num: 1,
    textStates: ["view-btns-text-normal", "view-btns-text-select"],
  },
  propClick: function (e) {
    var pos = e.currentTarget.dataset.pos;
    var index = e.currentTarget.dataset.index;
    var goods = this.data.goods
    for (var i = 0; i < goods.goods.goods_spec_list[index].length; i++) {

      if (i == pos)
        goods.goods.goods_spec_list[index][pos].isClick = 1;
      else
        goods.goods.goods_spec_list[index][i].isClick = 0;
    }

    this.setData({ goods: goods });
    this.checkPrice();
  },
  addCollect: function (e) {
    var goods_id = e.currentTarget.dataset.id;

    var user_id = getApp().globalData.userInfo.user_id
    var ctype = 0;

    server.getJSON('/Goods/collectGoods/user_id/' + user_id + "/goods_id/" + goods_id + "/type/" + ctype, function (res) {
      wx.showToast({ title: res.data.msg, icon: 'success', duration: 2000 })
    });




  },
  bindMinus: function (e) {

    var num = this.data.goods_num;
    if (num > 1) {
      num--;
    }

    this.setData({ goods_num: num });
  },
  bindManual: function (e) {
    var index = parseInt(e.currentTarget.dataset.index);
    var num = e.detail.value;
    this.setData({ goods_num: num });
  },
  bindPlus: function (e) {
    var num = this.data.goods_num;

    num++;

    this.setData({ goods_num: num });
  },
  onLoad: function (options) {
    var app = getApp();
    app.getInviteCode(options);

    var goodsId = options.objectId;
    objectId = goodsId;
    this.getGoodsById(goodsId);

  },

  tabClick: function (e) {
    var index = e.currentTarget.dataset.index
    var classs = ["text-normal", "text-normal", "text-normal"]
    classs[index] = "text-select"
    this.setData({ tabClasss: classs, tab: index })
  },
  getGoodsById: function (goodsId) {
		/*var that = this
		var query = new AV.Query('Goods');
        // 生成商品对象
		query.get(goodsId).then(function (goods) {
			// console.log(goods);
			that.setData({
				goods: goods
			});
		// 成功获得实例
		}, function (error) {
		// 异常处理
		});*/
    var that = this

    server.getJSON('/Goods/goodsInfo/id/' + goodsId, function (res) {
      var goodsInfo = res.data.result;

      that.setData({
        goods: goodsInfo
      });
      that.checkPrice();

      console.log(goodsInfo.goods.goods_desc);

      var goods_desc = goodsInfo.goods.goods_desc;


      WxParse.wxParse('goods_desc', 'html', goods_desc, that, 5);

    });

  },
  checkPrice: function () {
    var goods = this.data.goods;
    var spec = ""
    this.setData({ price: goods.goods.shop_price });
    for (var i = 0; i < goods.goods.goods_spec_list.length; i++) {

      for (var j = 0; j < goods.goods.goods_spec_list[i].length; j++) {
        if (goods.goods.goods_spec_list[i][j].isClick == 1) {
          if (spec == "")
            spec = goods.goods.goods_spec_list[i][j].item_id
          else
            spec = spec + "_" + goods.goods.goods_spec_list[i][j].item_id
        }
      }
    }
    //console.log(spec);//20170806 prince 

    /*var specs = spec.split("_");
    for (var i = 0; i < specs.length; i++) {
      specs[i] = parseInt(specs[i])
    }
    specs.sort(function (a, b) { return a - b });
    spec = ""
    for (var i = 0; i < specs.length; i++) {
      if (spec == "")
        spec = specs[i]
      else
        spec = spec + "_" + specs[i]
    }
    console.log(spec);*///屏蔽前端排序
    //console.log(goods['spec_goods_price']); //20170806 prince 
    var price = goods['spec_goods_price'][spec].price;
    //console.log(price);//20170806 prince 
    this.setData({ price: price });
  },

  bug: function () {
    var goods = this.data.goods;
    var spec = ""
    if (goods.goods.goods_spec_list != null)
      for (var i = 0; i < goods.goods.goods_spec_list.length; i++) {

        for (var j = 0; j < goods.goods.goods_spec_list[i].length; j++) {
          if (goods.goods.goods_spec_list[i][j].isClick == 1) {
            if (spec == "")
              spec = goods.goods.goods_spec_list[i][j].item_id
            else
              spec = spec + "_" + goods.goods.goods_spec_list[i][j].item_id
          }
        }
      }


    var app = getApp()
    var that = this;
    var goods_id = that.data.goods.goods.goods_id;
    var goods_spec = spec;
    var session_id = app.globalData.openid//that.data.goods.goods.spec_goods_price
    var goods_num = that.data.goods_num;

    var user_id = "0"
    if (app.globalData.login)
      user_id = app.globalData.userInfo.user_id



    server.getJSON('/Cart/addCart', { goods_id: goods_id, goods_spec: goods_spec, session_id: session_id, goods_num: goods_num, user_id: user_id }, function (res) {

      if (res.data.status == 1) {
        wx.showToast({
          title: '已加入购物车',
          icon: 'success',
          duration: 2000
        });
        wx.switchTab({
          url: '../../cart/cart'
        });
      }
      else
        wx.showToast({
          title: res.data.msg,
          icon: 'error',
          duration: 2000
        });


    });





    return;


  },
  addCart: function () {

    var goods = this.data.goods;
    var spec = ""
    if (goods.goods.goods_spec_list != null)
      for (var i = 0; i < goods.goods.goods_spec_list.length; i++) {

        for (var j = 0; j < goods.goods.goods_spec_list[i].length; j++) {
          if (goods.goods.goods_spec_list[i][j].isClick == 1) {
            if (spec == "")
              spec = goods.goods.goods_spec_list[i][j].item_id
            else
              spec = spec + "_" + goods.goods.goods_spec_list[i][j].item_id
          }
        }
      }




    var app = getApp()
    var that = this;
    var goods_id = that.data.goods.goods.goods_id;
    var goods_spec = spec;
    var session_id = app.globalData.openid//that.data.goods.goods.spec_goods_price
    var goods_num = that.data.goods_num;
    var user_id = "0"
    if (app.globalData.login)
      user_id = app.globalData.userInfo.user_id



    server.getJSON('/Cart/addCart', { goods_id: goods_id, goods_spec: goods_spec, session_id: session_id, goods_num: goods_num, user_id: user_id }, function (res) {
      if (res.data.status == 1)
        wx.showToast({
          title: '已加入购物车',
          icon: 'success',
          duration: 1000
        });
      else
        wx.showToast({
          title: res.data.msg,
          icon: 'error',
          duration: 1000
        });
    });


  },
  showCartToast: function () {
    wx.showToast({
      title: '已加入购物车',
      icon: 'success',
      duration: 1000
    });
    wx.navigateTo({
      url: '../../../../../../cart/cart'
    });

  },
  previewImage: function (e) {
    wx.previewImage({
      //从<image>的data-current取到current，得到String类型的url路径
      current: this.data.goods.get('images')[parseInt(e.currentTarget.dataset.current)],
      urls: this.data.goods.get('images') // 需要预览的图片http链接列表
    })
  },
  onShareAppMessage: function (res) {
    var that = this;
    var user_id = getApp().globalData.userInfo.user_id;
    var open_id = getApp().globalData.userInfo.open_id;
    var title = that.data.goods.goods.goods_name;
    var desc = that.data.goods.goods.goods_brief;
    var goods_id = that.data.goods.goods.goods_id;

    return {
      title: title,
      desc: desc,
      path: '/pages/goods/detail/detail?objectId=' + goods_id +'&uid=' + user_id,
      success: function (res) {
        server.postJSON('/Share/getShare/user_id/' + user_id + '/type/Goods', { user_id: user_id, open_id: open_id }, function (res) {
          var result = res.data.result;

          wx.showModal({
            title: '提示',
            showCancel: false,
            confirmText: '我知道了',
            content: result,
          })
        })
      },
      fail: function (res) {
        wx.showModal({
          title: '提示',
          showCancel: false,
          confirmText: '我知道了',
          content: '您取消了分享！',

        })
        // 转发失败
      }
    }
  }
});