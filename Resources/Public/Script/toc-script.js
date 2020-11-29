(function () {
  // Toggle-Visibility
  if (document.querySelectorAll('#togglelink').length) {
    document.querySelector('#togglelink').addEventListener('click', function (event) {
      event.preventDefault();
      var spans = document.querySelector("#togglelink").querySelectorAll('span');
      spans.forEach(element => {
        element.classList.toggle('hide');
      });

      if (document.querySelector('#toc ol.toclist').classList.contains('show')) {
        document.querySelector('#toc ol.toclist').classList.add('hide');
        document.querySelector('#toc ol.toclist').classList.remove('show');
      } else {
        document.querySelector('#toc ol.toclist').classList.add('show');
        document.querySelector('#toc ol.toclist').classList.remove('hide');
      }
    }, false);
  }

  // New Toggle Style
  if (document.querySelector('#toc').classList.contains('togglestyle')) {
    var deepLists = document.querySelectorAll('#toc ol ol');
    deepLists.forEach(element => {
      element.classList.add('hide');
    });

    document.querySelector('#togglelinkStyle').addEventListener('click', function (event) {
      event.preventDefault();
      document.querySelector('#togglelinkStyle').classList.toggle('active');
      var deepLists = document.querySelectorAll('#toc ol ol');
      deepLists.forEach(element => {
        element.classList.toggle('hide');
      });
    }, false);
  }

  // Jump to top
  if (document.querySelector('#toc').classList.contains('toplink')) {
    var scrollToTopBtn = document.querySelector("#toctop")
    var rootElement = document.documentElement

    function handleScroll() {
      var scrollTotal = rootElement.scrollHeight - rootElement.clientHeight
      if ((rootElement.scrollTop / scrollTotal) > 0.80) {
        scrollToTopBtn.style.display = "block"
      } else {
        scrollToTopBtn.style.display = "none"
      }
    }
    function scrollToTop() {
      rootElement.scrollTo({
        top: 0,
        behavior: "smooth"
      })
    }
    scrollToTopBtn.addEventListener("click", scrollToTop);
    document.addEventListener("scroll", handleScroll);
  }

  // Speaking URL hashes
  if (document.querySelector('#toc').classList.contains('speaking-urls')) {
    var links = document.querySelectorAll('#toc li a');
    links.forEach(element => {
      var slug = element.querySelector('span').getAttribute('data-slug');
      if (slug !== null) {
        var headline = document.querySelector('div' + element.getAttribute('href'));
        headline.setAttribute('id', slug);
      }
      element.setAttribute('href', '#' + slug);
    });
  }

  // Jumplinks
  var smoothLinks = document.querySelectorAll('#toc.smoothscroll ol.toclist a[href*="#"]');
  smoothLinks.forEach(element => {
    element.addEventListener('click', function (event) {
      event.preventDefault();

      const href = this.getAttribute("href");
      const offsetTop = document.querySelector(href).offsetTop;

      scroll({
        top: offsetTop,
        behavior: "smooth"
      });

    }, false);
  });

  // Browserhistory
  if (document.querySelector('#toc').classList.contains('browserhistory')) {
    var stateObj = { state: $(this.hash) };
    history.pushState(stateObj, '', $(this.hash));
  }
})();