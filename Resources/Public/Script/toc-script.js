$(document).ready(function() {
  $('#togglelink').click(function(event) {
    event.preventDefault();
    $(this).find('span').toggleClass("hide");
    $('#toc ol.toclist').toggle("fast");
  });
  
  $('#toc.smoothscroll ol.toclist a[href*="#"]').click(function() {
    var $target = $(this.hash);
    if ($target.length) {
      var targetOffset = $target.offset().top;
      $('html,body').animate({scrollTop: targetOffset}, 1000);
      return false;
    }
  });
  
  //@todo: Speaking URL Hash
  // for each link, take data-slug, append to link instead of hash, insert id in first element of each id

});