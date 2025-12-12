<template>
    <div class="lang-switcher" :class="{ 'lang-switcher--light': light }">
        <select 
            v-model="currentLocale" 
            class="lang-switcher__select"
            @change="handleChange"
            :aria-label="$t('language.select')"
        >
            <option 
                v-for="option in localeOptions" 
                :key="option.code" 
                :value="option.code"
            >
                {{ option.name }}
            </option>
        </select>
        <svg class="lang-switcher__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 5h12M9 3v2m1.048 3.5A4.5 4.5 0 0112 5.5M9 14l-4 4m0 0l4 4m-4-4h18"/>
        </svg>
    </div>
</template>

<script setup>
import { computed, watch, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useLanguage, SUPPORTED_LOCALES, RTL_LOCALES } from '../i18n';

const props = defineProps({
    light: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['change']);

const { locale: i18nLocale } = useI18n();
const { locale, setLocale, localeOptions, initLocale } = useLanguage();

// Sync with i18n locale
const currentLocale = computed({
    get: () => locale.value,
    set: (value) => {
        setLocale(value);
        i18nLocale.value = value;
    }
});

const handleChange = () => {
    emit('change', currentLocale.value);
};

onMounted(() => {
    initLocale();
});
</script>

<style scoped>
.lang-switcher {
    position: relative;
    display: inline-flex;
    align-items: center;
}

.lang-switcher__select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 8px;
    padding: 0.5rem 2rem 0.5rem 0.75rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: inherit;
    cursor: pointer;
    transition: all 0.2s ease;
    min-width: 100px;
    font-family: inherit;
}

[dir="ltr"] .lang-switcher__select {
    padding: 0.5rem 0.75rem 0.5rem 2rem;
}

.lang-switcher__select:hover {
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.4);
}

.lang-switcher__select:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.3);
}

.lang-switcher__select option {
    background: #1e293b;
    color: #f8fafc;
    padding: 0.5rem;
}

.lang-switcher__icon {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    width: 16px;
    height: 16px;
    opacity: 0.7;
}

[dir="rtl"] .lang-switcher__icon {
    left: 0.75rem;
}

[dir="ltr"] .lang-switcher__icon {
    right: 0.75rem;
}

/* Light theme variant */
.lang-switcher--light .lang-switcher__select {
    background: rgba(0, 0, 0, 0.05);
    border-color: rgba(0, 0, 0, 0.1);
    color: #1f2937;
}

.lang-switcher--light .lang-switcher__select:hover {
    background: rgba(0, 0, 0, 0.08);
    border-color: rgba(0, 0, 0, 0.15);
}

.lang-switcher--light .lang-switcher__select:focus {
    box-shadow: 0 0 0 2px rgba(14, 116, 144, 0.2);
}

.lang-switcher--light .lang-switcher__select option {
    background: #ffffff;
    color: #1f2937;
}
</style>

