<template>
    <div class="app-container" :dir="direction">
        <!-- Unified Header - Same as Chat & Tickets -->
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
        <main class="profile-main">
            <div class="profile-container">
                <!-- Loading State -->
                <div v-if="loading" class="loading-container">
                    <div class="loader"></div>
                </div>

                <!-- Profile Content -->
                <div v-else class="profile-content">
                    <!-- Left Column - Avatar & Quick Info -->
                    <div class="profile-sidebar">
                        <div class="avatar-card">
                            <div class="avatar-wrapper">
                                <div class="avatar-container">
                                    <img 
                                        v-if="user.avatar" 
                                        :src="user.avatar" 
                                        :alt="fullName"
                                        class="avatar-image"
                                    />
                                    <div v-else class="avatar-placeholder">
                                        <span>{{ initials }}</span>
                                    </div>
                                </div>
                            </div>
                            <h2 class="user-name">{{ fullName }}</h2>
                            <p class="user-email">{{ user.email }}</p>
                            <div class="member-since">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                                <span>{{ t('profile.memberSince') }}: {{ user.created_at }}</span>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="quick-stats">
                            <h3>{{ t('profile.accountInfo') }}</h3>
                            <div class="stat-item">
                                <div class="stat-icon phone">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                    </svg>
                                </div>
                                <div class="stat-text">
                                    <span class="stat-label">{{ t('profile.phone') }}</span>
                                    <span class="stat-value">{{ user.phone || '—' }}</span>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-icon id">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="2" y="5" width="20" height="14" rx="2"/>
                                        <line x1="2" y1="10" x2="22" y2="10"/>
                                    </svg>
                                </div>
                                <div class="stat-text">
                                    <span class="stat-label">{{ t('profile.nationalId') }}</span>
                                    <span class="stat-value">{{ user.national_id || '—' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Edit Form -->
                    <div class="profile-forms">
                        <!-- Personal Information -->
                        <div class="form-card">
                            <div class="card-header">
                                <div class="header-icon">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                </div>
                                <h3>{{ t('profile.personalInfo') }}</h3>
                            </div>
                            <form @submit.prevent="updateProfile" class="profile-form">
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label>{{ t('profile.firstName') }}</label>
                                        <input 
                                            v-model="form.name" 
                                            type="text" 
                                            required
                                            :placeholder="t('profile.firstNamePlaceholder')"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label>{{ t('profile.lastName') }}</label>
                                        <input 
                                            v-model="form.family" 
                                            type="text" 
                                            required
                                            :placeholder="t('profile.lastNamePlaceholder')"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label>{{ t('profile.email') }}</label>
                                        <input 
                                            v-model="form.email" 
                                            type="email" 
                                            required
                                            :placeholder="t('profile.emailPlaceholder')"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label>{{ t('profile.phone') }}</label>
                                        <input 
                                            v-model="form.phone" 
                                            type="tel" 
                                            :placeholder="t('profile.phonePlaceholder')"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label>{{ t('profile.nationalId') }}</label>
                                        <input 
                                            v-model="form.national_id" 
                                            type="text"
                                            :placeholder="t('profile.nationalIdPlaceholder')"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label>{{ t('profile.postalCode') }}</label>
                                        <input 
                                            v-model="form.postal_code" 
                                            type="text"
                                            :placeholder="t('profile.postalCodePlaceholder')"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label>{{ t('profile.birthDate') }}</label>
                                        <input 
                                            v-model="form.birth_date" 
                                            type="date"
                                        />
                                    </div>
                                    <div class="form-group full-width">
                                        <label>{{ t('profile.address') }}</label>
                                        <textarea 
                                            v-model="form.address" 
                                            rows="3"
                                            :placeholder="t('profile.addressPlaceholder')"
                                        ></textarea>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn-primary" :disabled="savingProfile">
                                        <span v-if="savingProfile" class="btn-spinner"></span>
                                        {{ savingProfile ? t('common.saving') : t('profile.saveChanges') }}
                                    </button>
                                </div>
                            </form>
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
const savingProfile = ref(false);

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

// Form data
const form = reactive({
    name: '',
    family: '',
    email: '',
    phone: '',
    national_id: '',
    postal_code: '',
    birth_date: '',
    address: '',
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
            Object.assign(form, {
                name: user.name,
                family: user.family,
                email: user.email,
                phone: user.phone,
                national_id: user.national_id,
                postal_code: user.postal_code,
                birth_date: user.birth_date,
                address: user.address,
            });
        }
    } catch (error) {
        console.error('Failed to fetch profile:', error);
        toast.error(t('profile.fetchError'));
    } finally {
        loading.value = false;
    }
};

const updateProfile = async () => {
    try {
        savingProfile.value = true;
        const response = await axios.put('/api/v1/user/profile', form);
        if (response.data.success) {
            Object.assign(user, response.data.user);
            toast.success(t('profile.updateSuccess'));
        }
    } catch (error) {
        console.error('Failed to update profile:', error);
        const message = error.response?.data?.message || t('profile.updateError');
        toast.error(message);
    } finally {
        savingProfile.value = false;
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
   BASE STYLES
   ============================================ */
.app-container {
    min-height: 100vh;
    background: #f8fafc;
    font-family: 'Vazirmatn', 'Inter', system-ui, sans-serif;
}

/* Header - EXACTLY the same as Chat & Tickets */
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
    max-width: 1400px;
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

/* Main Content */
.profile-main {
    max-width: 1200px;
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

/* Profile Layout */
.profile-content {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 24px;
}

@media (max-width: 900px) {
    .profile-content {
        grid-template-columns: 1fr;
    }
}

/* Sidebar */
.profile-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.avatar-card, .quick-stats, .form-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

/* Avatar Section - Read Only */
.avatar-card {
    text-align: center;
}

.avatar-wrapper {
    position: relative;
    display: inline-block;
    margin-bottom: 16px;
}

.avatar-container {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    overflow: hidden;
    position: relative;
    border: 3px solid #e2e8f0;
}

.avatar-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #0e7490, #06b6d4);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 700;
    color: white;
}

.user-name {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 4px;
}

.user-email {
    font-size: 0.9rem;
    color: #64748b;
    margin: 0 0 16px;
}

.member-since {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 0.8rem;
    color: #94a3b8;
    padding-top: 16px;
    border-top: 1px solid #f1f5f9;
}

/* Quick Stats */
.quick-stats h3 {
    font-size: 0.9rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 16px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid #f1f5f9;
}

.stat-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.stat-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e0f2fe;
    color: #0284c7;
}

.stat-icon.phone {
    background: #dcfce7;
    color: #16a34a;
}

.stat-icon.id {
    background: #f3e8ff;
    color: #9333ea;
}

.stat-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.stat-label {
    font-size: 0.75rem;
    color: #94a3b8;
}

.stat-value {
    font-size: 0.9rem;
    font-weight: 500;
    color: #1e293b;
}

/* Form Cards */
.profile-forms {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #f1f5f9;
}

.header-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e0f2fe;
    color: #0284c7;
}

.card-header h3 {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.profile-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

@media (max-width: 600px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    font-size: 0.85rem;
    font-weight: 500;
    color: #475569;
}

.form-group input,
.form-group textarea {
    padding: 10px 14px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #f8fafc;
    color: #1e293b;
    font-size: 0.9rem;
    transition: all 0.2s ease;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #0e7490;
    background: white;
    box-shadow: 0 0 0 3px rgba(14, 116, 144, 0.1);
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: #94a3b8;
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

/* Form Actions */
.form-actions {
    display: flex;
    justify-content: flex-end;
    padding-top: 8px;
}

[dir="rtl"] .form-actions {
    justify-content: flex-start;
}

.btn-primary {
    padding: 10px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: none;
    background: #0e7490;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #0c6580;
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

/* ============================================
   RESPONSIVE STYLES
   ============================================ */
@media (max-width: 900px) {
    .profile-content {
        grid-template-columns: 1fr;
    }
    
    .profile-sidebar {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
}

@media (max-width: 768px) {
    .app-header {
        padding: 0 16px;
        height: 52px;
    }
    
    .header-inner {
        gap: 8px;
    }
    
    .header-nav {
        gap: 4px;
    }
    
    .nav-link {
        padding: 5px 10px;
        font-size: 0.8rem;
    }
    
    .lang-select {
        padding: 5px 8px;
        font-size: 0.8rem;
    }
    
    .profile-main {
        padding: 20px 16px;
    }
    
    .avatar-container {
        width: 80px;
        height: 80px;
    }
    
    .avatar-placeholder {
        font-size: 1.5rem;
    }
    
    .avatar-card, .quick-stats, .form-card {
        padding: 18px;
    }
    
    .form-grid {
        gap: 12px;
    }
}

@media (max-width: 640px) {
    .app-header {
        padding: 0 12px;
        height: 48px;
    }
    
    .brand-text {
        display: none;
    }
    
    .brand-icon {
        width: 32px;
        height: 32px;
    }
    
    .header-nav {
        gap: 3px;
    }
    
    .nav-link {
        padding: 4px 8px;
        font-size: 0.75rem;
    }
    
    .lang-select {
        padding: 4px 6px;
        font-size: 0.75rem;
    }
    
    .profile-sidebar {
        grid-template-columns: 1fr;
    }
    
    .profile-main {
        padding: 16px 12px;
    }
    
    .avatar-card, .quick-stats, .form-card {
        padding: 16px;
        border-radius: 12px;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .user-name {
        font-size: 1.1rem;
    }
    
    .user-email {
        font-size: 0.85rem;
    }
    
    .member-since {
        font-size: 0.75rem;
    }
}

@media (max-width: 480px) {
    .app-header {
        padding: 0 10px;
    }
    
    .nav-link {
        padding: 4px 6px;
        font-size: 0.7rem;
    }
    
    .profile-main {
        padding: 12px 10px;
    }
    
    .avatar-container {
        width: 70px;
        height: 70px;
    }
    
    .avatar-placeholder {
        font-size: 1.25rem;
    }
    
    .form-group label {
        font-size: 0.8rem;
    }
    
    .form-group input,
    .form-group textarea {
        padding: 8px 12px;
        font-size: 0.85rem;
    }
    
    .btn-primary {
        padding: 8px 20px;
        font-size: 0.85rem;
    }
    
    .card-header h3 {
        font-size: 0.9rem;
    }
    
    .header-icon {
        width: 34px;
        height: 34px;
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
