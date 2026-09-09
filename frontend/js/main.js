document.addEventListener('DOMContentLoaded', function () {
  const navbar = document.querySelector('.navbar');
  if (navbar) {
    const handleScroll = () => {
      if (window.scrollY > 10) navbar.classList.add('scrolled');
      else navbar.classList.remove('scrolled');
    };
    handleScroll();
    window.addEventListener('scroll', handleScroll);
  }

  const toggle = document.querySelector('.mobile-toggle');
  const menu = document.querySelector('.nav-menu');
  if (toggle && menu) {
    toggle.addEventListener('click', () => {
      menu.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', menu.classList.contains('is-open'));
    });
  }

  const pageLinks = document.querySelectorAll('.nav-menu a');
  const currentPath = window.location.pathname.split('/').pop() || 'index.html';
  pageLinks.forEach((link) => {
    const href = link.getAttribute('href');
    if (href === currentPath || (currentPath === '' && href === 'index.html')) {
      link.classList.add('active');
    }
  });

  const counters = document.querySelectorAll('[data-count]');
  const animateCounter = (element) => {
    const target = Number(element.dataset.count) || 0;
    const suffix = element.dataset.suffix || '';
    const duration = 1400;
    let start = 0;
    const increment = Math.max(1, Math.ceil(target / (duration / 16)));
    const tick = () => {
      start += increment;
      if (start >= target) {
        element.textContent = target + suffix;
        return;
      }
      element.textContent = start + suffix;
      requestAnimationFrame(tick);
    };
    tick();
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        if (entry.target.classList.contains('count-up')) {
          animateCounter(entry.target);
          observer.unobserve(entry.target);
        } else {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      }
    });
  }, { threshold: 0.2 });

  document.querySelectorAll('.reveal, .count-up').forEach((el) => observer.observe(el));

  const testimonialSwiper = document.querySelector('.testimonial-swiper');
  if (testimonialSwiper && typeof Swiper !== 'undefined') {
    new Swiper('.testimonial-swiper', {
      slidesPerView: 1,
      spaceBetween: 24,
      loop: true,
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
      },
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      breakpoints: {
        768: {
          slidesPerView: 2,
        },
        1024: {
          slidesPerView: 3,
        },
      },
    });
  }

  const galleryItems = document.querySelectorAll('.gallery-item');
  const galleryModal = document.querySelector('.gallery-modal');
  const modalImage = document.querySelector('.gallery-modal img');
  const modalClose = document.querySelector('.gallery-modal-close');

  if (galleryItems.length && galleryModal && modalImage) {
    galleryItems.forEach((item) => {
      item.addEventListener('click', () => {
        const img = item.querySelector('img');
        if (img) {
          modalImage.src = img.src;
          modalImage.alt = img.alt || 'Gallery image';
          galleryModal.classList.add('show');
          document.body.style.overflow = 'hidden';
        }
      });
    });

    const closeModal = () => {
      galleryModal.classList.remove('show');
      document.body.style.overflow = '';
    };

    if (modalClose) modalClose.addEventListener('click', closeModal);
    galleryModal.addEventListener('click', (e) => {
      if (e.target === galleryModal) closeModal();
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && galleryModal.classList.contains('show')) closeModal();
    });
  }
});
