document.addEventListener('DOMContentLoaded', () => {
  const revealItems = document.querySelectorAll('.reveal');
  const revealObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.18 });

  revealItems.forEach((item) => revealObserver.observe(item));

  const tiltScenes = document.querySelectorAll('[data-tilt-scene]');
  tiltScenes.forEach((scene) => {
    scene.addEventListener('pointermove', (event) => {
      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
      const bounds = scene.getBoundingClientRect();
      const x = (event.clientX - bounds.left) / bounds.width - 0.5;
      const y = (event.clientY - bounds.top) / bounds.height - 0.5;
      scene.style.setProperty('--tilt-x', `${y * -5}deg`);
      scene.style.setProperty('--tilt-y', `${x * 7}deg`);
      scene.style.setProperty('--shift-x', `${x * 10}px`);
      scene.style.setProperty('--shift-y', `${y * 8}px`);
    });

    scene.addEventListener('pointerleave', () => {
      scene.style.setProperty('--tilt-x', '0deg');
      scene.style.setProperty('--tilt-y', '0deg');
      scene.style.setProperty('--shift-x', '0px');
      scene.style.setProperty('--shift-y', '0px');
    });
  });
});
