(function () {
  var hero = document.querySelector('.hero-slider');
  if (!hero) return;
  var slides = hero.querySelectorAll('.slide');
  var dotsBox = hero.querySelector('.slide-dots');
  var i = 0, timer = null;
  if (slides.length < 2) return;

  slides.forEach(function (_, n) {
    var d = document.createElement('button');
    d.type = 'button';
    d.setAttribute('aria-label', 'Banner ' + (n + 1));
    d.addEventListener('click', function () { go(n); restart(); });
    dotsBox.appendChild(d);
  });
  var dots = dotsBox.querySelectorAll('button');

  function go(n) {
    slides[i].classList.remove('active');
    dots[i].classList.remove('on');
    i = (n + slides.length) % slides.length;
    slides[i].classList.add('active');
    dots[i].classList.add('on');
  }
  function restart() { clearInterval(timer); timer = setInterval(function () { go(i + 1); }, 5000); }

  hero.querySelector('.prev').addEventListener('click', function () { go(i - 1); restart(); });
  hero.querySelector('.next').addEventListener('click', function () { go(i + 1); restart(); });
  hero.addEventListener('mouseenter', function () { clearInterval(timer); });   // 滑鼠停在上面時暫停
  hero.addEventListener('mouseleave', restart);

  dots[0].classList.add('on');
  restart();
})();