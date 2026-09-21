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
  const heroCard = document.querySelector('.hero-card');
  const closeMenu = () => {
    if (!menu) return;
    menu.classList.remove('is-open');
    if (toggle) toggle.setAttribute('aria-expanded', 'false');
  };
  if (toggle && menu) {
    toggle.addEventListener('click', () => {
      menu.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', menu.classList.contains('is-open'));
    });
    menu.addEventListener('click', (event) => {
      if (event.target.closest('a')) closeMenu();
    });
  }

  if (heroCard) {
    heroCard.addEventListener('pointerenter', () => {
      heroCard.classList.add('is-hovered');
      const compact = window.matchMedia('(max-width: 768px)').matches;
      heroCard.style.setProperty('transform', compact ? 'translateY(-4px) translateZ(20px) scale(1.03)' : 'translate3d(-12px, -6px, 32px) scale(1.06)', 'important');
      heroCard.style.setProperty('box-shadow', '0 42px 90px rgba(2, 12, 35, 0.38), 0 18px 34px rgba(37, 99, 235, 0.2), 0 0 52px rgba(43, 181, 255, 0.18)', 'important');
    });
    heroCard.addEventListener('pointerleave', () => {
      heroCard.classList.remove('is-hovered');
      heroCard.style.removeProperty('transform');
      heroCard.style.removeProperty('box-shadow');
    });
  }

  const pageLinks = document.querySelectorAll('.nav-menu a');
  const currentPath = window.location.pathname.split('/').pop() || 'index.html';
  const hashLinks = [...pageLinks].filter((link) => link.getAttribute('href')?.startsWith('#'));

  if (hashLinks.length) {
    const sections = hashLinks
      .map((link) => document.querySelector(link.getAttribute('href')))
      .filter(Boolean);
    const setActiveSection = (id) => {
      hashLinks.forEach((link) => {
        link.classList.toggle('active', link.getAttribute('href') === `#${id}`);
      });
    };

    const initialId = window.location.hash.slice(1);
    setActiveSection(initialId && sections.some((section) => section.id === initialId) ? initialId : sections[0]?.id);

    hashLinks.forEach((link) => {
      link.addEventListener('click', () => {
        setActiveSection(link.getAttribute('href').slice(1));
        closeMenu();
      });
    });

    const updateActiveFromScroll = () => {
      const anchor = (navbar?.offsetHeight || 0) + 24;
      const visibleSection = sections.reduce((current, section) => (
        Math.abs(section.getBoundingClientRect().top - anchor) <
          Math.abs(current.getBoundingClientRect().top - anchor) ? section : current
      ), sections[0]);
      if (visibleSection) setActiveSection(visibleSection.id);
    };

    window.addEventListener('scroll', updateActiveFromScroll, { passive: true });
    window.addEventListener('hashchange', updateActiveFromScroll);
    updateActiveFromScroll();
  } else {
    pageLinks.forEach((link) => {
      const href = link.getAttribute('href');
      link.classList.toggle('active', href === currentPath || (currentPath === '' && href === 'index.html'));
    });
  }

  document.querySelectorAll('.footer-grid > div:nth-child(2) ul').forEach((quickLinks) => {
    if ([...quickLinks.querySelectorAll('a')].some((link) => link.getAttribute('href') === 'gallery.html')) return;
    const galleryItem = document.createElement('li');
    galleryItem.innerHTML = '<a href="gallery.html">Gallery</a>';
    const admissionsLink = [...quickLinks.querySelectorAll('a')].find((link) => link.getAttribute('href') === 'admission.html');
    admissionsLink?.parentElement.before(galleryItem);
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
    const testimonialSlides = testimonialSwiper.querySelectorAll('.swiper-slide').length;
    new Swiper('.testimonial-swiper', {
      slidesPerView: 1,
      spaceBetween: 24,
      loop: testimonialSlides > 3,
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
  const modalPrevious = document.querySelector('.gallery-modal-prev');
  const modalNext = document.querySelector('.gallery-modal-next');
  const galleryFilters = document.querySelectorAll('[data-gallery-filter]');

  if (galleryItems.length && galleryModal && modalImage) {
    let selectedItemIndex = 0;

    const visibleItems = () => [...galleryItems].filter((item) => !item.hidden);

    const showGalleryItem = (item) => {
      const img = item?.querySelector('img');
      if (!img) return;
      modalImage.src = img.currentSrc || img.src;
      modalImage.alt = img.alt || 'Master Computer Institute gallery image';
      selectedItemIndex = visibleItems().indexOf(item);
    };

    galleryItems.forEach((item) => {
      item.addEventListener('click', () => {
        showGalleryItem(item);
        galleryModal.classList.add('show');
        galleryModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
      });
    });

    galleryFilters.forEach((filter) => {
      filter.addEventListener('click', () => {
        const category = filter.dataset.galleryFilter;
        galleryFilters.forEach((button) => button.classList.toggle('is-active', button === filter));
        galleryItems.forEach((item) => {
          item.hidden = category !== 'all' && item.dataset.galleryCategory !== category;
        });
      });
    });

    const moveGalleryItem = (direction) => {
      const items = visibleItems();
      if (!items.length) return;
      selectedItemIndex = (selectedItemIndex + direction + items.length) % items.length;
      showGalleryItem(items[selectedItemIndex]);
    };

    const closeModal = () => {
      galleryModal.classList.remove('show');
      galleryModal.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
    };

    if (modalClose) modalClose.addEventListener('click', closeModal);
    if (modalPrevious) modalPrevious.addEventListener('click', () => moveGalleryItem(-1));
    if (modalNext) modalNext.addEventListener('click', () => moveGalleryItem(1));
    galleryModal.addEventListener('click', (e) => {
      if (e.target === galleryModal) closeModal();
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && galleryModal.classList.contains('show')) closeModal();
      if (galleryModal.classList.contains('show') && e.key === 'ArrowLeft') moveGalleryItem(-1);
      if (galleryModal.classList.contains('show') && e.key === 'ArrowRight') moveGalleryItem(1);
    });
  }
});
