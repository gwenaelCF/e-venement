$(document).ready(function(){
  LI.seatedPlanInitializationFunctions.push(function()
  {
    var click;
    $('.seated-plan .seat.txt').click(click = function(){
      if ( $('#todo .ticket').length == 0 || $(this).is('.printed') || $(this).is('.ordered') )
        return false;
      
      var seat = this;
      $('#done form [name="ticket[numerotation]"]').val($(this).find('input').val());
      $('#done form [name="ticket[id]"]').val($('#todo .ticket:first input').val());
      $.ajax({
        url: $('#done form').prop('action'),
        data: $('#done form').serialize(),
        success: function(){
          $('#done form input[name="ticket[numerotation]"], #done form input[name="ticket[id]"]').val('');
          var id = $(seat).clone(true).removeClass('seat').removeClass('txt').attr('class');
          $('#todo .ticket:first').prependTo('#done');
          $('#todo .total').html(parseInt($('#todo .total').html())-1);
          $('#done .total').html(parseInt($('#todo .total').html())+1);
          $('.seated-plan .'+id).addClass('ordered');
          $(seat).addClass('in-progress').dblclick(LI.seatedPlanUnallocatedSeat);
          
          // if there is no more ticket, go to the next step, including editting the order
          if ( $('#todo .ticket').length == 0 && $('#next a').hasClass('auto-click') )
            window.location = $('#next a').prop('href');
        },
        error: function(){
          $('#done form input').val('');
          alert($('#done form .error_msg').html());
        }
      });
    });
    
    $('#menu li').unbind().addClass('disabled');
    $('#banner a, #footer a').prop('href','#').unbind().click(function(){ return false; });
  });
});
