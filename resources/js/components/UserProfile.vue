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
                                <div class="avatar-container" @click="triggerAvatarUpload">
                                    <img 
                                        v-if="user.avatar" 
                                        :src="user.avatar" 
                                        :alt="fullName"
                                        class="avatar-image"
                                    />
                                    <div v-else class="avatar-placeholder">
                                        <span>{{ initials }}</span>
                                    </div>
                                    <div class="avatar-overlay">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                            <circle cx="12" cy="13" r="4"/>
                                        </svg>
                                    </div>
                                </div>
                                <input 
                                    ref="avatarInput"
                                    type="file" 
                                    accept="image/*" 
                                    @change="handleAvatarUpload"
                                    style="display: none;"
                                />
                                <button 
                                    v-if="user.avatar" 
                                    @click.stop="deleteAvatar"
                                    class="delete-avatar-btn"
                                    :title="t('profile.deleteAvatar')"
                                >
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                </button>
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

                        <!-- Change Password -->
                        <div class="form-card">
                            <div class="card-header">
                                <div class="header-icon security">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                    </svg>
                                </div>
                                <h3>{{ t('profile.changePassword') }}</h3>
                            </div>
                            <form @submit.prevent="updatePassword" class="profile-form">
                                <div class="form-grid">
                                    <div class="form-group full-width">
                                        <label>{{ t('profile.currentPassword') }}</label>
                                        <div class="password-input">
                                            <input 
                                                v-model="passwordForm.current_password" 
                                                :type="showCurrentPassword ? 'text' : 'password'"
                                                required
                                                :placeholder="t('profile.currentPasswordPlaceholder')"
                                            />
                                            <button 
                                                type="button" 
                                                class="toggle-password"
                                                @click="showCurrentPassword = !showCurrentPassword"
                                            >
                                                <svg v-if="showCurrentPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                                </svg>
                                                <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                    <circle cx="12" cy="12" r="3"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ t('profile.newPassword') }}</label>
                                        <div class="password-input">
                                            <input 
                                                v-model="passwordForm.password" 
                                                :type="showNewPassword ? 'text' : 'password'"
                                                required
                                                minlength="6"
                                                :placeholder="t('profile.newPasswordPlaceholder')"
                                            />
                                            <button 
                                                type="button" 
                                                class="toggle-password"
                                                @click="showNewPassword = !showNewPassword"
                                            >
                                                <svg v-if="showNewPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                                </svg>
                                                <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                    <circle cx="12" cy="12" r="3"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ t('profile.confirmPassword') }}</label>
                                        <div class="password-input">
                                            <input 
                                                v-model="passwordForm.password_confirmation" 
                                                :type="showConfirmPassword ? 'text' : 'password'"
                                                required
                                                minlength="6"
                                                :placeholder="t('profile.confirmPasswordPlaceholder')"
                                            />
                                            <button 
                                                type="button" 
                                                class="toggle-password"
                                                @click="showConfirmPassword = !showConfirmPassword"
                                            >
                                                <svg v-if="showConfirmPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                                </svg>
                                                <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                    <circle cx="12" cy="12" r="3"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <p v-if="passwordMismatch" class="error-text">{{ t('profile.passwordMismatch') }}</p>
                                <div class="form-actions">
                                    <button type="submit" class="btn-secondary" :disabled="savingPassword || passwordMismatch">
                                        <span v-if="savingPassword" class="btn-spinner"></span>
                                        {{ savingPassword ? t('common.saving') : t('profile.updatePassword') }}
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
const savingPassword = ref(false);
const avatarInput = ref(null);

// Password visibility toggles
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

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

const passwordForm = reactive({
    current_password: '',
    password: '',
    password_confirmation: '',
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

const passwordMismatch = computed(() => {
    return passwordForm.password && passwordForm.password_confirmation && 
           passwordForm.password !== passwordForm.password_confirmation;
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

const updatePassword = async () => {
    if (passwordMismatch.value) return;
    
    try {
        savingPassword.value = true;
        const response = await axios.put('/api/v1/user/profile/password', passwordForm);
        if (response.data.success) {
            toast.success(t('profile.passwordSuccess'));
            // Clear password form
            passwordForm.current_password = '';
            passwordForm.password = '';
            passwordForm.password_confirmation = '';
        }
    } catch (error) {
        console.error('Failed to update password:', error);
        const message = error.response?.data?.message || t('profile.passwordError');
        toast.error(message);
    } finally {
        savingPassword.value = false;
    }
};

const triggerAvatarUpload = () => {
    avatarInput.value?.click();
};

const handleAvatarUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Validate file size (2MB)
    if (file.size > 2 * 1024 * 1024) {
        toast.error(t('profile.avatarTooLarge'));
        return;
    }

    const formData = new FormData();
    formData.append('avatar', file);

    try {
        const response = await axios.post('/api/v1/user/profile/avatar', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        if (response.data.success) {
            user.avatar = response.data.avatar;
            toast.success(t('profile.avatarSuccess'));
        }
    } catch (error) {
        console.error('Failed to upload avatar:', error);
        toast.error(t('profile.avatarError'));
    }
    
    // Reset input
    event.target.value = '';
};

const deleteAvatar = async () => {
    if (!confirm(t('profile.confirmDeleteAvatar'))) return;
    
    try {
        const response = await axios.delete('/api/v1/user/profile/avatar');
        if (response.data.success) {
            user.avatar = null;
            toast.success(t('profile.avatarDeleted'));
        }
    } catch (error) {
        console.error('Failed to delete avatar:', error);
        toast.error(t('profile.avatarDeleteError'));
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

/* Header - Consistent with other pages */
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

/* Avatar Section */
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
    cursor: pointer;
    position: relative;
    border: 3px solid #e2e8f0;
    transition: all 0.3s ease;
}

.avatar-container:hover {
    border-color: #0e7490;
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

.avatar-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    color: white;
}

.avatar-container:hover .avatar-overlay {
    opacity: 1;
}

.delete-avatar-btn {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #ef4444;
    border: 2px solid white;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.delete-avatar-btn:hover {
    background: #dc2626;
    transform: scale(1.1);
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

.header-icon.security {
    background: #f3e8ff;
    color: #9333ea;
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

/* Password Input */
.password-input {
    position: relative;
}

.password-input input {
    width: 100%;
    padding-inline-end: 44px;
}

.toggle-password {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    inset-inline-end: 12px;
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.2s ease;
}

.toggle-password:hover {
    color: #64748b;
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

.btn-primary,
.btn-secondary {
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
}

.btn-primary {
    background: #0e7490;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #0c6580;
}

.btn-secondary {
    background: #7c3aed;
    color: white;
}

.btn-secondary:hover:not(:disabled) {
    background: #6d28d9;
}

.btn-primary:disabled,
.btn-secondary:disabled {
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

/* Error Text */
.error-text {
    color: #ef4444;
    font-size: 0.85rem;
    margin: 0;
}

/* Responsive */
@media (max-width: 768px) {
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
}

/* RTL/LTR */
[dir="ltr"] .app-container {
    text-align: left;
}

[dir="rtl"] .app-container {
    text-align: right;
}
</style>
