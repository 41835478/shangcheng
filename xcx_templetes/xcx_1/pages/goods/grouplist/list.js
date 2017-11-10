var server = require('../../../utils/server');
var categoryId
var keywords
var cPage = 0;
var gsort = "shop_price";
var asc = "desc";
var ctype = "0";
// 使用function初始化array，相比var initSubMenuDisplay = [] 既避免的引用复制的，同时方式更灵活，将来可以是多种方式实现，个数也不定的
function initSubMenuDisplay() {
  return ['hidden', 'hidden', 'hidden', 'hidden'];
}

//定义初始化数据，用于运行时保存
var initSubMenuHighLight = [
  ['highlight', '', '', '', ''],
  ['', ''],
  ['', '', ''], []
];
Page({

  tabClick: function (e) {
    var index = e.currentTarget.dataset.index
    var types = ["0", "1"]


    var classs = ["text-normal", "text-normal"]
    classs[index] = "text-select"
    this.setData({ tabClasss: classs, tab: index })
    cPage = 0;
    ctype = types[index];
    //var ctype;  201708026 prince
    this.getGoods(categoryId, 0, this.data.sort[0][0], ctype);
  },


	data:{
    tabClasss: ["text-select", "text-normal"],
    menu: ["highlight", "", "", ""],
    subMenuDisplay: initSubMenuDisplay(),
    subMenuHighLight: initSubMenuHighLight,
    sort: [['shop_price-desc', 'shop_price-asc'], ['sales_sum-desc', 'sales_sum-asc'], ['is_new-desc', 'is_new-asc'], 'comment_count-asc'],
    goods: [],
    empty: false
	},

	onLoad: function(options){
   
    // 页面显示
    if (ctype == "0") {
      var tabClasss = ["text-select", "text-normal"];
      this.setData({ tabClasss: tabClasss });
      this.getGoods(categoryId, 0, this.data.sort[0][0], ctype); 
    }  
  
	},
	

  getGoods: function (category, pageIndex, sort, ctype){
		var that = this;
    var syskey = getApp().globalData.syskey;//20170811
		var sortArray = sort.split('-');
		gsort = sortArray[0];
		asc = sortArray[1];
    var ctype = ctype;
    server.getJSON('/Activity/group_list/' + "p/" + pageIndex + "/syskey/" + syskey + "/ctype/" + ctype,function(res){

    var newgoods = res.data.result
  
    that.setData({
      goods: newgoods
    });



    });
	},


	tapGoods: function(e) {
		var objectId = e.currentTarget.dataset.objectId;
		wx.navigateTo({
			url:"../../../../../groupDetail/detail?objectId="+objectId
		});
	},
  onReachBottom: function () {
   
    this.getGoods(categoryId, ++cPage, gsort + "-" + asc, ctype);
   
    wx.showToast({
      title: '加载中',
      icon: 'loading'
    })
  },
  onPullDownRefresh: function () {
    this.setData({
      goods: []
    });
    cPage = 0;
   
    this.getGoods(categoryId, cPage, gsort + "-" + asc, ctype);
    
  }


});