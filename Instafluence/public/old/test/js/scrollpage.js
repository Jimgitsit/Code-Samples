$(document).ready(function() {
$('.scrollPage').click(function() {
   var elementClicked = $(this).attr("href");
   var destination = $(elementClicked).offset().top;
   $("html:not(:animated),body:not(:animated)").animate({ scrollTop: destination-00}, 500 );
   return false;
});
});