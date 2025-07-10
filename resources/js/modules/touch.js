(function () {
  document.addEventListener('DOMContentLoaded', function () {
    const html = document.documentElement;

    function isTouchDevice() {
      return 'ontouchstart' in window || navigator.maxTouchPoints > 0 || navigator.msMaxTouchPoints > 0;
    }

    // Add device class to <html>
    html.classList.add(isTouchDevice() ? 'is-touch' : 'no-touch');

    // Handle touch feedback
    function handleTouchStart(event) {
      const target = event.target.closest('[data-touch]');
      if (target) {
        target.classList.add('has-touch');
      }
    }

    function handleTouchEnd(event) {
      const target = event.target.closest('[data-touch]');
      if (target) {
        target.classList.remove('has-touch');
      }
    }

    // Attach global event listeners
    document.addEventListener('touchstart', handleTouchStart, { passive: true });
    document.addEventListener('touchend', handleTouchEnd);
  });
})();
