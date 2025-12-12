/**
 * i18n Setup - Internationalization with vue-i18n
 * Supports: fa (Persian/RTL), ar (Arabic/RTL), en (English/LTR)
 */

import { createI18n } from 'vue-i18n';
import { ref, watch, computed } from 'vue';

// Import translation files
import fa from './fa.json';
import en from './en.json';
import ar from './ar.json';

// ============================================
// CONSTANTS
// ============================================

export const SUPPORTED_LOCALES = ['fa', 'ar', 'en'];
export const RTL_LOCALES = ['fa', 'ar'];
export const DEFAULT_LOCALE = 'fa';
export const STORAGE_KEY = 'app_language';

// ============================================
// CREATE i18n INSTANCE
// ============================================

export const i18n = createI18n({
    legacy: true, // Use legacy mode to avoid CSP issues with new Function()
    locale: getStoredLocale(),
    fallbackLocale: 'fa',
    messages: {
        fa,
        en,
        ar,
    },
    // Suppress warnings for missing translations in dev
    silentTranslationWarn: true,
    silentFallbackWarn: true,
});

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Get stored locale from localStorage, with validation
 */
export function getStoredLocale() {
    if (typeof localStorage === 'undefined') return DEFAULT_LOCALE;
    
    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored && SUPPORTED_LOCALES.includes(stored)) {
        return stored;
    }
    return DEFAULT_LOCALE;
}

/**
 * Check if a locale is RTL
 */
export function isRtlLocale(locale) {
    return RTL_LOCALES.includes(locale);
}

/**
 * Get direction for a locale
 */
export function getLocaleDirection(locale) {
    return isRtlLocale(locale) ? 'rtl' : 'ltr';
}

/**
 * Apply direction and language to document
 */
export function applyLocaleToDocument(locale) {
    if (typeof document === 'undefined') return;
    
    const dir = getLocaleDirection(locale);
    const html = document.documentElement;
    
    // Set lang and dir attributes
    html.setAttribute('lang', locale);
    html.setAttribute('dir', dir);
    
    // Add/remove RTL/LTR classes
    html.classList.remove('rtl', 'ltr');
    html.classList.add(dir);
    
    // Update body classes as well for broader CSS targeting
    document.body.classList.remove('rtl', 'ltr');
    document.body.classList.add(dir);
    
    // Set CSS custom property for direction-aware styling
    html.style.setProperty('--dir', dir);
    html.style.setProperty('--dir-factor', dir === 'rtl' ? '-1' : '1');
}

/**
 * Save locale to localStorage
 */
export function saveLocale(locale) {
    if (typeof localStorage === 'undefined') return;
    localStorage.setItem(STORAGE_KEY, locale);
}

// ============================================
// REACTIVE LANGUAGE STATE (COMPOSABLE)
// ============================================

// Global reactive state
const currentLocale = ref(getStoredLocale());

/**
 * useLanguage composable - use this in Vue components
 * Provides reactive language state and methods
 */
export function useLanguage() {
    // Computed properties
    const locale = computed({
        get: () => currentLocale.value,
        set: (value) => setLocale(value)
    });
    
    const isRtl = computed(() => isRtlLocale(currentLocale.value));
    const direction = computed(() => getLocaleDirection(currentLocale.value));
    const localeOptions = computed(() => SUPPORTED_LOCALES.map(code => ({
        code,
        name: i18n.global.t(`language.${code}`),
        isRtl: isRtlLocale(code)
    })));
    
    /**
     * Set the current locale
     */
    function setLocale(newLocale) {
        if (!SUPPORTED_LOCALES.includes(newLocale)) {
            console.warn(`Unsupported locale: ${newLocale}`);
            return;
        }
        
        // Update reactive state
        currentLocale.value = newLocale;
        
        // Update vue-i18n (legacy mode uses direct assignment)
        i18n.global.locale = newLocale;
        
        // Persist to localStorage
        saveLocale(newLocale);
        
        // Apply to document
        applyLocaleToDocument(newLocale);
        
        // Emit custom event for non-Vue code
        if (typeof window !== 'undefined') {
            window.dispatchEvent(new CustomEvent('localechange', { 
                detail: { locale: newLocale, direction: getLocaleDirection(newLocale) } 
            }));
        }
    }
    
    /**
     * Toggle between LTR and RTL (useful for quick switch)
     */
    function toggleDirection() {
        const newLocale = isRtl.value ? 'en' : 'fa';
        setLocale(newLocale);
    }
    
    /**
     * Initialize on mount - apply current locale to document
     */
    function initLocale() {
        applyLocaleToDocument(currentLocale.value);
    }
    
    return {
        // State
        locale,
        isRtl,
        direction,
        localeOptions,
        
        // Methods
        setLocale,
        toggleDirection,
        initLocale,
        
        // Helpers
        t: i18n.global.t,
    };
}

// ============================================
// INITIALIZATION
// ============================================

// Apply locale to document on load
if (typeof document !== 'undefined') {
    // Run on DOMContentLoaded if document not ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            applyLocaleToDocument(getStoredLocale());
        });
    } else {
        applyLocaleToDocument(getStoredLocale());
    }
}

// ============================================
// VANILLA JS API (for non-Vue code)
// ============================================

/**
 * Global language API for use outside Vue components
 * Access via window.AppLanguage
 */
export const AppLanguage = {
    get locale() { return currentLocale.value; },
    set locale(value) { 
        if (SUPPORTED_LOCALES.includes(value)) {
            currentLocale.value = value;
            i18n.global.locale = value; // Legacy mode uses direct assignment
            saveLocale(value);
            applyLocaleToDocument(value);
        }
    },
    get isRtl() { return isRtlLocale(currentLocale.value); },
    get direction() { return getLocaleDirection(currentLocale.value); },
    setLocale(locale) { this.locale = locale; },
    t(key) { return i18n.global.t(key); },
    SUPPORTED_LOCALES,
    RTL_LOCALES,
};

// Expose to window for vanilla JS usage
if (typeof window !== 'undefined') {
    window.AppLanguage = AppLanguage;
}

export default i18n;

