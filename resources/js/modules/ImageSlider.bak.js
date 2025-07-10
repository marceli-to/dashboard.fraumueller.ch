import { gsap } from "gsap";

export default class ImageSlider {
  constructor(container) {
    this.container = container;
    this.track = container.querySelector('[data-slider-track]');
    this.originalSlides = [...this.track.children];
    this.slides = [];
    this.actualIndex = 0;
    this.x = 0;
    // this.speed = this.container.clientWidth * 0.04;
    // this.speed = 40;
    this.speed = window.innerWidth < 768 ? 40 : 60;
    this.isTransitioning = false;
    this.lastTime = null;
    this.isPaused = false;
    this.lastContainerWidth = this.container.clientWidth;
    this.hasInitialized = false;
  }

  init() {

    this.setContainerHeight();
    gsap.set(this.track, {
      transform: 'translate3d(0, 0, 0)',
      transformOrigin: "0% 0%",
      backfaceVisibility: "hidden",
      perspective: 1000
    });

    this.cloneSlides();
    this.measureSlides();
    this.setup();
    this.setupResizeObserver();
    this.setupTouch();
    this.setupAccessibility();

    this.actualIndex = this.originalSlides.length;
    const targetSlide = this.slides[this.actualIndex];
    const offset = this.getSlideOffset(targetSlide);
    this.x = offset;

    gsap.set(this.track, {
      transform: `translate3d(${-this.x}px, 0, 0)`
    });

    this.hasInitialized = true; // ✅ Set flag after init
    requestAnimationFrame((t) => this.animate(t));
  }

  cloneSlides() {
    const fragment = document.createDocumentFragment();
    this.originalSlides.forEach(slide => {
      fragment.appendChild(slide.cloneNode(true));
    });
    this.track.prepend(fragment.cloneNode(true));
    this.track.append(fragment.cloneNode(true));
    this.slides = [...this.track.children];
  }

  measureSlides() {
    this.slideWidths = this.slides.map(slide => slide.getBoundingClientRect().width);
    this.totalWidth = this.slideWidths.reduce((sum, w) => sum + w, 0);
    this.sectionWidth = this.totalWidth / 3;
  }

  setContainerHeight() {
    const viewportHeight = window.innerHeight;
    const viewportWidth = window.innerWidth;
    const windowWidth = window.innerWidth;
    
    let offset = 0;
    
    // Match Tailwind breakpoints
    if (windowWidth >= 1280) { // xl
      offset = 72;
      const calculatedHeight = viewportHeight - offset;
      this.container.style.height = `${calculatedHeight}px`;
      this.container.style.aspectRatio = ''; // Clear aspect ratio
    } 
    else if (windowWidth >= 1024) { // lg
      offset = 60;
      const calculatedHeight = viewportHeight - offset;
      this.container.style.height = `${calculatedHeight}px`;
      this.container.style.aspectRatio = ''; // Clear aspect ratio
    } 
    else if (windowWidth >= 700) {
      // For smaller screens and tablets, use aspect ratio
      this.container.style.height = ''; // Clear fixed height
      this.container.style.aspectRatio = '16 / 9';
      
      // Optionally set max-height to prevent overflow
      this.container.style.maxHeight = '100vh';
    } else {
      // For smaller screens and tablets, use aspect ratio
      this.container.style.height = ''; // Clear fixed height
      this.container.style.aspectRatio = '4 / 3';
      
      // Optionally set max-height to prevent overflow
      this.container.style.maxHeight = '100vh';
    }
  }

  setupResizeObserver() {
    let resizeTimeout;
    let lastWidth = window.innerWidth;
    let lastOrientation = window.innerHeight > window.innerWidth;
  
    const handleResize = () => {
      if (!this.hasInitialized) return;
  
      const currentWidth = window.innerWidth;
      const currentOrientation = window.innerHeight > window.innerWidth;
      
      // Check if width or orientation changed
      if (currentWidth === lastWidth && currentOrientation === lastOrientation) return;
      
      lastWidth = currentWidth;
      lastOrientation = currentOrientation;
  
      clearTimeout(resizeTimeout);
      this.isPaused = true;
  
      gsap.to(this.container, { opacity: 0, duration: 0.2 });
  
      resizeTimeout = setTimeout(() => {
        this.setContainerHeight();
        this.rebuildSlider();
      }, 300);
    };
  
    window.addEventListener('resize', handleResize);
    window.addEventListener('orientationchange', () => {
      setTimeout(handleResize, 100);
    });
  }

  rebuildSlider() {
    const currentIndex = this.actualIndex - this.originalSlides.length;
    gsap.killTweensOf(this.track);
    this.isTransitioning = false;

    this.track.innerHTML = '';
    this.originalSlides.forEach(slide => {
      this.track.appendChild(slide);
    });

    this.cloneSlides();
    this.measureSlides();

    this.actualIndex = this.originalSlides.length + Math.max(0, Math.min(currentIndex, this.originalSlides.length - 1));
    const targetSlide = this.slides[this.actualIndex];

    if (targetSlide) {
      const offset = this.getSlideOffset(targetSlide) - (this.container.clientWidth - targetSlide.clientWidth) / 2;
      this.x = offset;
      gsap.set(this.track, {
        transform: `translate3d(${-this.x}px, 0, 0)`,
        transformOrigin: "0% 0%"
      });
    }

    // this.speed = this.container.clientWidth * 0.04;
    // this.speed = 40;
    this.speed = window.innerWidth < 768 ? 40 : 60;
    this.lastContainerWidth = this.container.clientWidth;
    this.isPaused = false;
    gsap.to(this.container, { opacity: 1, duration: 0.3 });
  }

  setup() {
    this.container.querySelector('#nextBtn').addEventListener('click', () => this.next());
    this.container.querySelector('#prevBtn').addEventListener('click', () => this.prev());
    this.container.querySelector('#scrollBtn').addEventListener('click', () => this.scroll());

    this.container.setAttribute('tabindex', 0);
    this.container.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowRight') this.next();
      if (e.key === 'ArrowLeft') this.prev();
    });
  }

  setupTouch() {
    let startX = 0;

    this.container.addEventListener('touchstart', e => {
      startX = e.touches[0].clientX;
    });

    this.container.addEventListener('touchend', e => {
      const endX = e.changedTouches[0].clientX;
      const dx = endX - startX;

      if (Math.abs(dx) > 50) {
        dx > 0 ? this.prev() : this.next();
      }
    });
  }

  setupAccessibility() {
    this.track.setAttribute('aria-live', 'polite');
    this.slides.forEach((slide, index) => {
      slide.setAttribute('aria-label', `Slide ${index + 1}`);
    });
  }

  next() {
    if (this.isTransitioning) return;
    const centered = this.getCurrentCenteredSlideIndex();
    if (centered !== -1) {
      this.actualIndex = centered + 1;
      this.goToActualSlide(this.actualIndex, true, true);
    }
  }

  prev() {
    if (this.isTransitioning) return;
    const centered = this.getCurrentCenteredSlideIndex();
    if (centered !== -1) {
      this.actualIndex = centered - 1;
      this.goToActualSlide(this.actualIndex, true, true);
    }
  }

  goToActualSlide(slideIndex, animate = true, center = false) {
    const targetSlide = this.slides[slideIndex];
    if (!targetSlide) return;

    let offset = this.getSlideOffset(targetSlide);
    if (center) {
      offset -= (this.container.clientWidth - targetSlide.clientWidth) / 2;
    }

    if (animate) {
      this.isTransitioning = true;
      const proxy = { val: this.x };
      gsap.to(proxy, {
        val: offset,
        duration: 1,
        ease: 'power1.inOut',
        roundProps: 'val',
        onUpdate: () => {
          this.x = proxy.val;
          gsap.set(this.track, {
            transform: `translate3d(${-this.x}px, 0, 0)`,
            transformOrigin: "0% 0%"
          });
        },
        onComplete: () => {
          this.checkAndReposition();
          this.isTransitioning = false;
        }
      });
    } else {
      this.x = offset;
      gsap.set(this.track, {
        transform: `translate3d(${-this.x}px, 0, 0)`,
        transformOrigin: "0% 0%"
      });
    }
  }

  checkAndReposition() {
    const count = this.originalSlides.length;
    if (this.actualIndex >= count * 2) {
      this.actualIndex -= count;
      this.repositionToSlide(this.actualIndex);
    } else if (this.actualIndex < count) {
      this.actualIndex += count;
      this.repositionToSlide(this.actualIndex);
    }
  }

  repositionToSlide(index) {
    const target = this.slides[index];
    const offset = this.getSlideOffset(target) - (this.container.clientWidth - target.clientWidth) / 2;
    this.x = offset;
    gsap.set(this.track, {
      transform: `translate3d(${-this.x}px, 0, 0)`,
      transformOrigin: "0% 0%"
    });
  }

  getCurrentCenteredSlideIndex() {
    const containerRect = this.container.getBoundingClientRect();

    for (let i = 0; i < this.slides.length; i++) {
      const rect = this.slides[i].getBoundingClientRect();
      const visibleWidth = Math.max(0, Math.min(rect.right, containerRect.right) - Math.max(rect.left, containerRect.left));
      const visibilityRatio = visibleWidth / rect.width;

      if (visibilityRatio >= 0.8) {
        return i;
      }
    }

    return -1;
  }

  animate(timestamp) {
    if (!this.lastTime) this.lastTime = timestamp;
    const delta = timestamp - this.lastTime;
    this.lastTime = timestamp;

    if (!this.isTransitioning && !this.isPaused) {
      this.x += (this.speed * delta / 1000);
      if (this.x >= this.sectionWidth * 2) this.x -= this.sectionWidth;
      if (this.x < 0) this.x += this.sectionWidth;
    }

    gsap.set(this.track, {
      transform: `translate3d(${-this.x}px, 0, 0)`,
      transformOrigin: "0% 0%"
    });

    requestAnimationFrame((t) => this.animate(t));
  }

  getSlideOffset(slide) {
    return slide.offsetLeft;
  }

  scroll() {
    window.scrollTo({
      top: document.body.scrollHeight,
      behavior: 'smooth'
    });
  }
}
