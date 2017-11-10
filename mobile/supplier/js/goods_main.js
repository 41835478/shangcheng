


function toggle_menu()
  {
	  
    if($('#con_order_manage_1').css('display') != 'none')
    {
      $('#menu_list').slideToggle(200);
	  $('#menu_list_marker').toggleClass('on off');
    }
    else
    {
		$('#menu_list_marker').removeClass();
		$('#menu_list_marker').addClass('on');
    }
	  
	  
	  
	$('#con_order_manage_1').css('display','block');
	$('#con_order_manage_2').css('display','none');
	
	
	
	
  }
  
function toggle_search()
  {
	$('#con_order_manage_1').css('display','none');
	$('#con_order_manage_2').css('display','block');

  }
  
  
  
