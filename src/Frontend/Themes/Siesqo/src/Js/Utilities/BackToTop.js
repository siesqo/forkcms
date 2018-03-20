export class BackToTop {
  constructor () {
    $(window).scroll(function () {
      if ($(this).scrollTop() > 50) {
        $('#backToTop').fadeIn()
      } else {
        $('#backToTop').fadeOut()
      }
    })

        // scroll body to 0px on click
    $('#backToTop').click(function () {
      $('body,html').animate({
        scrollTop: 0
      }, 800)
      return false
    })
  }
}
