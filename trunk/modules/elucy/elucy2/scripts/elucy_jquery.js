$(document).ready(function() {

   
      $('div.activities').hover(function() {
   
       $('div.teacher').css("background","#CE5913");
   
      }, function() {
   
        $('div.teacher').css("background","#A13C00");
   
      });



    $('div.activities2').hover(function() {
   
       $('div.student').css("background","#556A94");
   
      }, function() {
   
        $('div.student').css("background","#263A62");
   
      });


  $('div.activities3').hover(function() {
   
       $('div.comparison').css("background","#8C6A41");
   
      }, function() {
   
        $('div.comparison').css("background","#654B2B");
   
      });



$('div.list1> div').hide(); 

  $('div.list1> h3').click(function() {

    $(this).next('div').slideToggle('fast')

    .siblings('div:visible').slideUp('fast');
return false;
  });










 });