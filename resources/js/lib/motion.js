/**
 * Motion System - Premium Animation Library
 * Reusable animation utilities with consistent timing, easing, and accessibility
 */

// ============================================
// MOTION CONFIGURATION
// ============================================

export const MOTION_CONFIG = {
    // Durations (in ms)
    duration: {
        instant: 100,
        fast: 200,
        normal: 400,
        slow: 600,
        slower: 800,
        slowest: 1000,
    },
    
    // Custom easing curves
    easing: {
        // Premium smooth easing (similar to Framer Motion)
        smooth: 'cubic-bezier(0.16, 1, 0.3, 1)',
        // Bouncy spring-like feel
        spring: 'cubic-bezier(0.34, 1.56, 0.64, 1)',
        // Ease out expo - great for entrances
        easeOutExpo: 'cubic-bezier(0.19, 1, 0.22, 1)',
        // Ease in out sine - subtle and elegant
        easeInOutSine: 'cubic-bezier(0.37, 0, 0.63, 1)',
        // Sharp deceleration
        decelerate: 'cubic-bezier(0, 0, 0.2, 1)',
        // Acceleration
        accelerate: 'cubic-bezier(0.4, 0, 1, 1)',
    },
    
    // Stagger delays (in ms)
    stagger: {
        fast: 50,
        normal: 100,
        slow: 150,
    },
    
    // Transform presets
    transform: {
        slideUp: { from: 'translateY(40px)', to: 'translateY(0)' },
        slideDown: { from: 'translateY(-40px)', to: 'translateY(0)' },
        slideRight: { from: 'translateX(-40px)', to: 'translateX(0)' },
        slideLeft: { from: 'translateX(40px)', to: 'translateX(0)' },
        scale: { from: 'scale(0.9)', to: 'scale(1)' },
        scaleUp: { from: 'scale(0.8)', to: 'scale(1)' },
        fadeSlideUp: { from: 'translateY(30px)', to: 'translateY(0)' },
    },
    
    // Viewport intersection options
    viewport: {
        threshold: 0.2,
        rootMargin: '0px 0px -80px 0px',
    }
};

// ============================================
// REDUCED MOTION DETECTION
// ============================================

export const prefersReducedMotion = () => {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
};

export const getMotionDuration = (duration) => {
    return prefersReducedMotion() ? 0 : duration;
};

export const getMotionDelay = (delay) => {
    return prefersReducedMotion() ? 0 : delay;
};

// ============================================
// INTERSECTION OBSERVER FACTORY
// ============================================

export const createRevealObserver = (options = {}) => {
    const config = {
        threshold: options.threshold ?? MOTION_CONFIG.viewport.threshold,
        rootMargin: options.rootMargin ?? MOTION_CONFIG.viewport.rootMargin,
        once: options.once ?? true,
    };
    
    const callback = (entries, observer) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                const element = entry.target;
                const staggerIndex = parseInt(element.dataset.staggerIndex || index);
                const delay = getMotionDelay(staggerIndex * (options.staggerDelay || MOTION_CONFIG.stagger.normal));
                
                setTimeout(() => {
                    element.classList.add('motion-revealed');
                    element.style.transitionDelay = '0ms';
                    
                    // Trigger custom event
                    element.dispatchEvent(new CustomEvent('motionReveal', { bubbles: true }));
                }, delay);
                
                if (config.once) {
                    observer.unobserve(element);
                }
            } else if (!config.once) {
                entry.target.classList.remove('motion-revealed');
            }
        });
    };
    
    return new IntersectionObserver(callback, {
        threshold: config.threshold,
        rootMargin: config.rootMargin,
    });
};

// ============================================
// ANIMATION UTILITIES
// ============================================

export const animate = (element, keyframes, options = {}) => {
    if (prefersReducedMotion()) {
        // Apply final state immediately
        const lastFrame = keyframes[keyframes.length - 1];
        Object.assign(element.style, lastFrame);
        return Promise.resolve();
    }
    
    const defaultOptions = {
        duration: MOTION_CONFIG.duration.normal,
        easing: MOTION_CONFIG.easing.smooth,
        fill: 'forwards',
    };
    
    const animation = element.animate(keyframes, { ...defaultOptions, ...options });
    return animation.finished;
};

export const staggerAnimate = async (elements, keyframes, options = {}) => {
    const staggerDelay = options.staggerDelay || MOTION_CONFIG.stagger.normal;
    
    const promises = Array.from(elements).map((element, index) => {
        return animate(element, keyframes, {
            ...options,
            delay: getMotionDelay(index * staggerDelay),
        });
    });
    
    return Promise.all(promises);
};

// ============================================
// SCROLL PROGRESS TRACKING
// ============================================

export const createScrollProgress = (element, callback) => {
    if (prefersReducedMotion()) return () => {};
    
    const updateProgress = () => {
        const rect = element.getBoundingClientRect();
        const windowHeight = window.innerHeight;
        const elementHeight = rect.height;
        
        // Calculate progress from 0 (element enters viewport) to 1 (element exits viewport)
        const progress = Math.max(0, Math.min(1, 
            (windowHeight - rect.top) / (windowHeight + elementHeight)
        ));
        
        callback(progress);
    };
    
    const throttledUpdate = throttle(updateProgress, 16); // ~60fps
    
    window.addEventListener('scroll', throttledUpdate, { passive: true });
    updateProgress();
    
    return () => window.removeEventListener('scroll', throttledUpdate);
};

// ============================================
// PARALLAX EFFECT
// ============================================

export const createParallax = (element, options = {}) => {
    if (prefersReducedMotion()) return () => {};
    
    const speed = options.speed ?? 0.5;
    const direction = options.direction ?? 'y';
    
    return createScrollProgress(element, (progress) => {
        const offset = (progress - 0.5) * 100 * speed;
        
        if (direction === 'y') {
            element.style.transform = `translateY(${offset}px)`;
        } else {
            element.style.transform = `translateX(${offset}px)`;
        }
    });
};

// ============================================
// MAGNETIC EFFECT (for buttons/interactive elements)
// ============================================

export const createMagneticEffect = (element, options = {}) => {
    if (prefersReducedMotion()) return () => {};
    
    const strength = options.strength ?? 0.3;
    const ease = options.ease ?? 0.15;
    
    let currentX = 0;
    let currentY = 0;
    let targetX = 0;
    let targetY = 0;
    let rafId = null;
    
    const lerp = (start, end, factor) => start + (end - start) * factor;
    
    const updatePosition = () => {
        currentX = lerp(currentX, targetX, ease);
        currentY = lerp(currentY, targetY, ease);
        
        element.style.transform = `translate(${currentX}px, ${currentY}px)`;
        
        if (Math.abs(currentX - targetX) > 0.1 || Math.abs(currentY - targetY) > 0.1) {
            rafId = requestAnimationFrame(updatePosition);
        }
    };
    
    const handleMouseMove = (e) => {
        const rect = element.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;
        
        targetX = (e.clientX - centerX) * strength;
        targetY = (e.clientY - centerY) * strength;
        
        if (!rafId) {
            rafId = requestAnimationFrame(updatePosition);
        }
    };
    
    const handleMouseLeave = () => {
        targetX = 0;
        targetY = 0;
        
        if (!rafId) {
            rafId = requestAnimationFrame(updatePosition);
        }
    };
    
    element.addEventListener('mousemove', handleMouseMove);
    element.addEventListener('mouseleave', handleMouseLeave);
    
    return () => {
        element.removeEventListener('mousemove', handleMouseMove);
        element.removeEventListener('mouseleave', handleMouseLeave);
        if (rafId) cancelAnimationFrame(rafId);
    };
};

// ============================================
// TILT EFFECT (for cards)
// ============================================

export const createTiltEffect = (element, options = {}) => {
    if (prefersReducedMotion()) return () => {};
    
    const maxTilt = options.maxTilt ?? 10;
    const perspective = options.perspective ?? 1000;
    const scale = options.scale ?? 1.02;
    const speed = options.speed ?? 400;
    
    element.style.transformStyle = 'preserve-3d';
    element.style.transition = `transform ${speed}ms ${MOTION_CONFIG.easing.smooth}`;
    
    const handleMouseMove = (e) => {
        const rect = element.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        
        const rotateX = ((y - centerY) / centerY) * -maxTilt;
        const rotateY = ((x - centerX) / centerX) * maxTilt;
        
        element.style.transform = `
            perspective(${perspective}px)
            rotateX(${rotateX}deg)
            rotateY(${rotateY}deg)
            scale(${scale})
        `;
    };
    
    const handleMouseLeave = () => {
        element.style.transform = `
            perspective(${perspective}px)
            rotateX(0deg)
            rotateY(0deg)
            scale(1)
        `;
    };
    
    element.addEventListener('mousemove', handleMouseMove);
    element.addEventListener('mouseleave', handleMouseLeave);
    
    return () => {
        element.removeEventListener('mousemove', handleMouseMove);
        element.removeEventListener('mouseleave', handleMouseLeave);
    };
};

// ============================================
// GLOW FOLLOW EFFECT
// ============================================

export const createGlowFollow = (element, options = {}) => {
    if (prefersReducedMotion()) return () => {};
    
    const glowColor = options.color ?? 'rgba(14, 116, 144, 0.4)';
    const glowSize = options.size ?? 200;
    
    // Create glow element
    const glow = document.createElement('div');
    glow.className = 'glow-follow';
    glow.style.cssText = `
        position: absolute;
        width: ${glowSize}px;
        height: ${glowSize}px;
        background: radial-gradient(circle, ${glowColor} 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
        opacity: 0;
        transform: translate(-50%, -50%);
        transition: opacity 0.3s ease;
        z-index: 0;
    `;
    
    element.style.position = 'relative';
    element.style.overflow = 'hidden';
    element.insertBefore(glow, element.firstChild);
    
    const handleMouseMove = (e) => {
        const rect = element.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        glow.style.left = `${x}px`;
        glow.style.top = `${y}px`;
        glow.style.opacity = '1';
    };
    
    const handleMouseLeave = () => {
        glow.style.opacity = '0';
    };
    
    element.addEventListener('mousemove', handleMouseMove);
    element.addEventListener('mouseleave', handleMouseLeave);
    
    return () => {
        element.removeEventListener('mousemove', handleMouseMove);
        element.removeEventListener('mouseleave', handleMouseLeave);
        glow.remove();
    };
};

// ============================================
// UTILITY FUNCTIONS
// ============================================

function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// ============================================
// INITIALIZATION HELPER
// ============================================

export const initMotionSystem = () => {
    // Add CSS custom properties to document
    const style = document.createElement('style');
    style.textContent = `
        :root {
            --motion-duration-instant: ${MOTION_CONFIG.duration.instant}ms;
            --motion-duration-fast: ${MOTION_CONFIG.duration.fast}ms;
            --motion-duration-normal: ${MOTION_CONFIG.duration.normal}ms;
            --motion-duration-slow: ${MOTION_CONFIG.duration.slow}ms;
            --motion-duration-slower: ${MOTION_CONFIG.duration.slower}ms;
            --motion-duration-slowest: ${MOTION_CONFIG.duration.slowest}ms;
            
            --motion-ease-smooth: ${MOTION_CONFIG.easing.smooth};
            --motion-ease-spring: ${MOTION_CONFIG.easing.spring};
            --motion-ease-expo: ${MOTION_CONFIG.easing.easeOutExpo};
            --motion-ease-sine: ${MOTION_CONFIG.easing.easeInOutSine};
            --motion-ease-decelerate: ${MOTION_CONFIG.easing.decelerate};
            --motion-ease-accelerate: ${MOTION_CONFIG.easing.accelerate};
        }
        
        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }
        
        /* Base reveal animation styles */
        [data-motion-reveal] {
            opacity: 0;
            transform: translateY(30px);
            transition: 
                opacity var(--motion-duration-slow) var(--motion-ease-smooth),
                transform var(--motion-duration-slow) var(--motion-ease-smooth);
        }
        
        [data-motion-reveal].motion-revealed {
            opacity: 1;
            transform: translateY(0);
        }
        
        [data-motion-reveal="slide-right"] {
            transform: translateX(-30px);
        }
        
        [data-motion-reveal="slide-right"].motion-revealed {
            transform: translateX(0);
        }
        
        [data-motion-reveal="slide-left"] {
            transform: translateX(30px);
        }
        
        [data-motion-reveal="slide-left"].motion-revealed {
            transform: translateX(0);
        }
        
        [data-motion-reveal="scale"] {
            transform: scale(0.9);
        }
        
        [data-motion-reveal="scale"].motion-revealed {
            transform: scale(1);
        }
        
        [data-motion-reveal="fade"] {
            transform: none;
        }
    `;
    document.head.appendChild(style);
    
    // Auto-initialize reveal observers
    const revealElements = document.querySelectorAll('[data-motion-reveal]');
    if (revealElements.length > 0) {
        const observer = createRevealObserver();
        revealElements.forEach((el, index) => {
            if (!el.dataset.staggerIndex) {
                el.dataset.staggerIndex = index;
            }
            observer.observe(el);
        });
    }
    
    console.log('🎬 Motion system initialized');
};

// Auto-initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMotionSystem);
} else {
    initMotionSystem();
}

export default {
    MOTION_CONFIG,
    prefersReducedMotion,
    getMotionDuration,
    getMotionDelay,
    createRevealObserver,
    animate,
    staggerAnimate,
    createScrollProgress,
    createParallax,
    createMagneticEffect,
    createTiltEffect,
    createGlowFollow,
    initMotionSystem,
};

