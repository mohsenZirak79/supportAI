<template>
    <div class="app-container" :dir="direction">
        <!-- Unified Header -->
        <header class="app-header">
            <div class="header-inner">
                <div class="header-brand">
                    <div class="brand-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <span class="brand-text">{{ t('profile.title') }}</span>
                </div>
                <nav class="header-nav">
                    <select :value="locale" class="lang-select" @change="onLanguageChange">
                        <option value="fa">فارسی</option>
                        <option value="en">EN</option>
                        <option value="ar">ع</option>
                    </select>
                    <button @click="goToChat" class="nav-link">{{ t('nav.chat') }}</button>
                    <button @click="goToTickets" class="nav-link">{{ t('nav.tickets') }}</button>
                    <button @click="logout" class="nav-link danger" :disabled="loggingOut">
                        {{ loggingOut ? '...' : t('nav.logout') }}
                    </button>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="app-main">
            <!-- Loading State -->
            <div v-if="loading" class="loading-container">
                <div class="loader"></div>
            </div>

            <!-- Profile Content -->
            <div v-else class="profile-layout">
                <!-- Profile Card -->
                <div class="profile-card">
                    <!-- Avatar Section -->
                    <div class="avatar-section">
                        <div class="avatar">
                            <img v-if="user.avatar" :src="user.avatar" :alt="fullName" />
                            <span v-else class="avatar-initials">{{ initials }}</span>
                        </div>
                        <h2 class="user-name">{{ fullName }}</h2>
                        <p class="user-email">{{ user.email }}</p>
                    </div>

                    <!-- Info Grid -->
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">{{ t('profile.phone') }}</span>
                            <span class="info-value">{{ user.phone || '—' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">{{ t('profile.nationalId') }}</span>
                            <span class="info-value">{{ user.national_id || '—' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">{{ t('profile.postalCode') }}</span>
                            <span class="info-value">{{ user.postal_code || '—' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">{{ t('profile.birthDate') }}</span>
                            <span class="info-value">{{ user.birth_date || '—' }}</span>
                        </div>
                        <div class="info-item full">
                            <span class="info-label">{{ t('profile.address') }}</span>
                            <span class="info-value">{{ user.address || '—' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">{{ t('profile.memberSince') }}</span>
                            <span class="info-value">{{ user.created_at }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toast-notification';
import { useLanguage } from '../i18n';

const toast = useToast();
const { locale, direction, setLocale, t } = useLanguage();

// State
const loading = ref(true);
const loggingOut = ref(false);

// User data
const user = reactive({
    id: null,
    name: '',
    family: '',
    email: '',
    phone: '',
    national_id: '',
    postal_code: '',
    birth_date: '',
    address: '',
    avatar: null,
    created_at: '',
});

// Computed
const fullName = computed(() => {
    return `${user.name || ''} ${user.family || ''}`.trim() || t('profile.guest');
});

const initials = computed(() => {
    const first = user.name?.charAt(0) || '';
    const last = user.family?.charAt(0) || '';
    return (first + last).toUpperCase() || '?';
});

// Methods
const fetchProfile = async () => {
    try {
        loading.value = true;
        const response = await axios.get('/api/v1/user/profile');
        if (response.data.success) {
            Object.assign(user, response.data.user);
        }
    } catch (error) {
        console.error('Failed to fetch profile:', error);
        toast.error(t('profile.fetchError'));
    } finally {
        loading.value = false;
    }
};

const onLanguageChange = (event) => {
    setLocale(event.target.value);
};

const goToChat = () => {
    window.location.href = '/chat';
};

const goToTickets = () => {
    window.location.href = '/ticket';
};

const logout = async () => {
    loggingOut.value = true;
    try {
        await axios.post('/logout');
        window.location.href = '/login';
    } catch (error) {
        console.error('Logout failed:', error);
        toast.error(t('auth.logoutError'));
    } finally {
        loggingOut.value = false;
    }
};

// Lifecycle
onMounted(() => {
    fetchProfile();
});
</script>

<style scoped>
/* ============================================
   UNIFIED APP STYLES
   ============================================ */
.app-container {
    min-height: 100vh;
    background: #f8fafc;
    font-family: 'Vazirmatn', 'Inter', system-ui, sans-serif;
}

/* Header */
.app-header {
    background: #0e7490;
    color: white;
    padding: 0 24px;
    height: 56px;
    position: sticky;
    top: 0;
    z-index: 100;
}

.header-inner {
    max-width: 1200px;
    margin: 0 auto;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.header-brand {
    display: flex;
    align-items: center;
    gap: 10px;
}

.brand-icon {
    width: 28px;
    height: 28px;
    background: rgba(255,255,255,0.15);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.brand-icon svg {
    width: 18px;
    height: 18px;
}

.brand-text {
    font-weight: 600;
    font-size: 1rem;
}

.header-nav {
    display: flex;
    align-items: center;
    gap: 6px;
}

.lang-select {
    background: rgba(255,255,255,0.15);
    border: none;
    color: white;
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 0.85rem;
    cursor: pointer;
}

.lang-select option {
    background: #0e7490;
    color: white;
}

.nav-link {
    background: transparent;
    border: none;
    color: rgba(255,255,255,0.85);
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.nav-link:hover {
    background: rgba(255,255,255,0.15);
    color: white;
}

.nav-link.danger {
    color: #fecaca;
}

.nav-link.danger:hover {
    background: rgba(239,68,68,0.2);
}

/* Main */
.app-main {
    max-width: 800px;
    margin: 0 auto;
    padding: 32px 24px;
}

/* Loading */
.loading-container {
    display: flex;
    justify-content: center;
    padding: 80px 0;
}

.loader {
    width: 36px;
    height: 36px;
    border: 3px solid #e2e8f0;
    border-top-color: #0e7490;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Profile Card */
.profile-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    overflow: hidden;
}

.avatar-section {
    background: linear-gradient(135deg, #0e7490, #06b6d4);
    padding: 32px 24px;
    text-align: center;
}

.avatar {
    width: 88px;
    height: 88px;
    border-radius: 50%;
    background: white;
    margin: 0 auto 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border: 3px solid rgba(255,255,255,0.3);
}

.avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-initials {
    font-size: 1.75rem;
    font-weight: 700;
    color: #0e7490;
}

.user-name {
    font-size: 1.25rem;
    font-weight: 600;
    color: white;
    margin: 0 0 4px;
}

.user-email {
    font-size: 0.9rem;
    color: rgba(255,255,255,0.8);
    margin: 0;
}

/* Info Grid */
.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1px;
    background: #f1f5f9;
}

.info-item {
    background: white;
    padding: 16px 20px;
}

.info-item.full {
    grid-column: 1 / -1;
}

.info-label {
    display: block;
    font-size: 0.75rem;
    color: #64748b;
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value {
    font-size: 0.95rem;
    color: #1e293b;
    font-weight: 500;
}

/* Responsive */
@media (max-width: 600px) {
    .app-header {
        padding: 0 16px;
    }
    
    .header-nav {
        gap: 4px;
    }
    
    .nav-link {
        padding: 6px 8px;
        font-size: 0.8rem;
    }
    
    .app-main {
        padding: 20px 16px;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .info-item.full {
        grid-column: 1;
    }
}

/* RTL/LTR */
[dir="ltr"] .app-container {
    text-align: left;
}

[dir="rtl"] .app-container {
    text-align: right;
}
</style>
