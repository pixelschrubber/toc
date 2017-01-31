$(document).ready(function() {
  $('#togglelink').click(function(event) {
    event.preventDefault();
    $(this).find('span').toggleClass("hide");
    $('#toc ol.toclist').toggle("fast");
  });
  
  // Jump to top
  if ($('#toc').hasClass('toplink')) {
    var el = $('#toctop');
    el.hide();
    $(window).scroll(function () {
      if ($(window).scrollTop() >= 400) {
        el.fadeIn(500);
      } else {
        el.fadeOut(500);
      }
    });
    el.click(function (e) {
      e.preventDefault();
      $('html,body').animate({scrollTop: 0}, 300);
    });
  }
  
  // speaking URL hashes
  if ($('#toc').hasClass('speaking-urls')) {
    $('#toc li a').each(function (index) {
      var slug = $(this).find("span").data(slug);
      $('div'+$(this.hash).selector).attr('id',slug.slug);     
      this.hash = slug.slug;
    });
  }  
  
  // Jumplinks
  $('#toc.smoothscroll ol.toclist a[href*="#"]').click(function() {
    var $target = $(this.hash);
    if ($target.length) {
      var targetOffset = $target.offset().top;
      $('html,body').animate({scrollTop: targetOffset}, 1000);
      
      if($('#toc').hasClass('browserhistory')) {
        var stateObj = {state: $(this.hash)};
        history.pushState(stateObj,'', $(this.hash));
      }      
      return false;
    }
  });
  

});