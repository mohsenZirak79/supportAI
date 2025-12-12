/**
 * Simple i18n Setup - CSP-Safe Internationalization
 * Does NOT use vue-i18n to avoid CSP 'unsafe-eval' issues
 * Supports: fa (Persian/RTL), ar (Arabic/RTL), en (English/LTR)
 */

import { ref, computed, reactive } from 'vue';

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

// All messages
const messages = { fa, en, ar };

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
    if (document.body) {
        document.body.classList.remove('rtl', 'ltr');
        document.body.classList.add(dir);
    }
    
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

/**
 * Get nested object value by dot notation path
 * e.g., getNestedValue(obj, 'chat.title') returns obj.chat.title
 */
function getNestedValue(obj, path) {
    if (!obj || !path) return undefined;
    
    const keys = path.split('.');
    let result = obj;
    
    for (const key of keys) {
        if (result && typeof result === 'object' && key in result) {
            result = result[key];
        } else {
            return undefined;
        }
    }
    
    return result;
}

/**
 * Simple translation function - NO runtime compilation
 * @param {string} key - Translation key (e.g., 'chat.title')
 * @param {string} locale - Locale code (optional, uses current locale if not provided)
 * @returns {string} - Translated string or key if not found
 */
export function translate(key, locale) {
    const currentLang = locale || currentLocale.value;
    const msg = messages[currentLang];
    
    if (!msg) return key;
    
    const value = getNestedValue(msg, key);
    
    if (value !== undefined && typeof value === 'string') {
        return value;
    }
    
    // Fallback to Persian
    if (currentLang !== 'fa') {
        const fallbackValue = getNestedValue(messages.fa, key);
        if (fallbackValue !== undefined && typeof fallbackValue === 'string') {
            return fallbackValue;
        }
    }
    
    return key;
}

// ============================================
// REACTIVE LANGUAGE STATE
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
    
    /**
     * Translation function bound to current locale
     */
    function t(key) {
        return translate(key, currentLocale.value);
    }
    
    return {
        // State
        locale,
        isRtl,
        direction,
        
        // Methods
        setLocale,
        toggleDirection,
        initLocale,
        t,
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
            saveLocale(value);
            applyLocaleToDocument(value);
        }
    },
    get isRtl() { return isRtlLocale(currentLocale.value); },
    get direction() { return getLocaleDirection(currentLocale.value); },
    setLocale(locale) { this.locale = locale; },
    t(key) { return translate(key); },
    SUPPORTED_LOCALES,
    RTL_LOCALES,
};

// Expose to window for vanilla JS usage
if (typeof window !== 'undefined') {
    window.AppLanguage = AppLanguage;
}

// ============================================
// VUE PLUGIN (Simple, CSP-Safe)
// ============================================

/**
 * Vue plugin that adds $t() method globally
 * This is CSP-safe because it doesn't use new Function()
 */
export const i18nPlugin = {
    install(app) {
        // Add global $t method
        app.config.globalProperties.$t = function(key) {
            return translate(key, currentLocale.value);
        };
        
        // Add global $locale property
        app.config.globalProperties.$locale = currentLocale;
        
        // Provide for composition API
        app.provide('i18n', {
            t: translate,
            locale: currentLocale,
            setLocale: (newLocale) => {
                if (SUPPORTED_LOCALES.includes(newLocale)) {
                    currentLocale.value = newLocale;
                    saveLocale(newLocale);
                    applyLocaleToDocument(newLocale);
                }
            }
        });
    }
};

// Default export is the plugin
export default i18nPlugin;
