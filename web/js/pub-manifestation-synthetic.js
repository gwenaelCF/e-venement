$(document).ready(function(){
  LI.pubNamedTicketsInitialization();
  
  $('#categories form').submit(function(){
    $.ajax({
      type: $(this).prop('method'),
      url:  $(this).prop('action'),
      data: $(this).serialize(),
      success: function(json){
        if ( json.error && json.error.message )
          LI.alert(json.error.message, 'error');
        if ( json.success && json.success.message )
          LI.alert(json.success.message, 'success');
        LI.pubNamedTicketsInitialization();
      }
    });
    return false;
  });
  
  // remove empty selects
  $('#categories select').each(function(){
    if ( $(this).find('option').length == 0 )
      $(this).closest('li').remove();
  });
  
  // drag-scroll from any device for seated-plans
  $('#plans .gauge').overscroll();
});
