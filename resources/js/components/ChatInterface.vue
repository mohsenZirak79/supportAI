<template>
    <HandoffModal
        :is-open="isHandoffModalOpen"
        :roles="availableRoles"
        @close="isHandoffModalOpen = false"
        @submit="handleHandoffSubmit"
    />

    <div class="cg-app" :dir="direction">
        <ChatHeader
            :title="activeChat?.title || $t('chat.title')"
            :show-menu-toggle="isMobile"
            :sidebar-open="isSidebarOpen"
            :menu-label="$t('chat.openSidebar')"
            :settings-label="$t('chat.settingsTitle')"
            @toggle-sidebar="toggleSidebar"
            @open-settings="settingsModalOpen = true"
        >
            <template #notifications>
                <NotificationBell @select="handleNotificationSelect" />
            </template>
        </ChatHeader>

        <div class="cg-body">
            <ChatSidebar
                v-model:search-query="sidebarSearch"
                :chats="filteredChats"
                :active-chat-id="activeChatId"
                :is-open="isSidebarOpen"
                :is-mobile="isMobile"
                :new-chat-text="$t('chat.newChat')"
                :new-chat-label="$t('chat.startNewChat')"
                :search-placeholder="$t('chat.searchConversations')"
                :search-label="$t('common.search')"
                :actions-label="$t('chat.chatSettings')"
                :user-name="currentUser.name || $t('nav.profile')"
                :user-avatar="currentUser.avatar"
                :account-label="$t('chat.settingsTitle')"
                :referral-dot="hasPublicReferralResponses"
                @new-chat="startNewChat"
                @select="setActiveChat($event)"
                @open-actions="openChatActionsModal"
                @open-settings="settingsModalOpen = true"
            />

            <div
                v-if="isMobile && isSidebarOpen"
                class="cg-drawer-backdrop"
                role="presentation"
                aria-hidden="true"
                @click="closeSidebar"
            />

            <div class="cg-main">
                <template v-if="activeChatId">
                    <div ref="messagesContainer" class="cg-thread-scroll">
                        <div class="cg-thread">
                            <div
                                v-for="(message, index) in activeChat?.messages || []"
                                :key="message.id || message._tmpKey || `msg-${index}`"
                                class="cg-msg"
                                :class="{ 'cg-msg--user': message.sender === 'user', 'cg-msg--bot': message.sender === 'bot' }"
                                :data-msg-id="message.id || ''"
                            >
                                <div class="cg-msg-inner">
                                    <div class="cg-msg-bubble" @click="onBubbleClick(message)">
                                        <template v-if="message.sender === 'bot' && message.text">
                                            <AiAnswer :text="message.text" :lang="locale" :gender="userVoiceGender" />
                                        </template>
                                        <template v-else>
                                            <div v-if="message.isSending" class="cg-msg-sending">
                                                <span class="cg-msg-dot" aria-hidden="true" />
                                                {{ $t('chat.sendingVoice') }}
                                            </div>
                                            <div v-if="message.text && message.text.trim()" class="cg-msg-transcript">
                                                <span>{{ message.text }}</span>
                                            </div>
                                            <div
                                                v-else-if="(message.has_voice || message.has_media) && !message.voiceUrl"
                                                class="cg-voice-placeholder"
                                                @click.stop="onBubbleClick(message)"
                                            >
                                                {{ $t('chat.loadAndPlay') }}
                                            </div>
                                            <span v-else-if="!message.voiceUrl">‌</span>
                                        </template>
                                        <div v-if="message.voiceUrl" class="cg-voice-wrap" @click.stop="playVoice(message.id)">
                                            <audio :ref="(el) => registerAudioRef(message.id, el)" :src="message.voiceUrl" preload="none" controls />
                                        </div>
                                        <div class="cg-msg-toolbar">
                                            <span class="cg-msg-time">{{ formatDate(message.created_at) }}</span>
                                            <div class="cg-msg-actions">
                                                <button
                                                    type="button"
                                                    class="cg-icon-btn"
                                                    :aria-label="$t('chat.copyText')"
                                                    :title="$t('chat.copyText')"
                                                    @click.stop="copyText(message.text)"
                                                >
                                                    <svg viewBox="0 0 24 24" class="cg-icon" aria-hidden="true">
                                                        <path
                                                            d="M16 1H4c-1.1 0-2 .9-2 2v12h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"
                                                        />
                                                    </svg>
                                                </button>
                                                <button
                                                    type="button"
                                                    class="cg-icon-btn"
                                                    :aria-label="$t('chat.handoff')"
                                                    :title="$t('chat.handoff')"
                                                    @click.stop="showHandoffModal(message)"
                                                >
                                                    <svg viewBox="0 0 24 24" class="cg-icon" aria-hidden="true">
                                                        <path d="M4 12v8h16v-8h2v10H2V12h2zm8-9 6 6h-4v6h-4V9H6l6-6z" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="loading" class="cg-msg cg-msg--bot">
                                <div class="cg-msg-inner">
                                    <div class="cg-msg-bubble cg-msg-bubble--typing">
                                        <ChatTypingIndicator :label="$t('chat.thinking')" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button
                        v-if="showScrollButton"
                        type="button"
                        class="cg-scroll-to-bottom"
                        :aria-label="$t('chat.scrollToBottom')"
                        @click="scrollToBottom"
                    >
                        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                            <path
                                d="M12 16.5a1 1 0 0 1-.7-.29l-6-6a1 1 0 0 1 1.4-1.42L12 14.09l5.3-5.3a1 1 0 1 1 1.4 1.42l-6 6a1 1 0 0 1-.7.29Z"
                            />
                        </svg>
                    </button>
                    <ChatComposer
                        ref="composerRef"
                        v-model="inputMessage"
                        :disabled="loading"
                        :is-recording="isRecording"
                        :recording-time="recordingTime"
                        :placeholder="$t('chat.inputPlaceholder')"
                        :mic-label="$t('chat.recordVoice')"
                        :send-label="$t('chat.send')"
                        :send-voice-label="$t('chat.send')"
                        :cancel-label="$t('common.cancel')"
                        :bar-height-fn="getBarHeight"
                        @submit="sendMessage"
                        @start-recording="startRecording"
                        @cancel-recording="cancelRecording"
                        @send-recording="sendRecording"
                        @keydown="onKeydown"
                    />
                </template>
                <div
                    v-else
                    class="cg-empty"
                    role="button"
                    tabindex="0"
                    @click="startNewChat"
                    @keydown.enter.prevent="startNewChat"
                >
                    <h2>{{ $t('chat.startNewChat') }}</h2>
                    <p>{{ $t('chat.startNewChatDesc') }}</p>
                </div>
            </div>
        </div>

        <BaseModal v-model:open="referralPanelOpen" :title="activeChat?.title || $t('referral.currentChat')" size="xl">
            <p class="cg-modal-eyebrow">{{ $t('referral.title') }}</p>
            <div class="cg-referral-toolbar">
                <button type="button" class="cg-btn-secondary" :disabled="referralsLoading" @click="refreshCurrentReferrals">
                    {{ $t('referral.refresh') }}
                </button>
            </div>
            <div v-if="referralsLoading" class="cg-referral-placeholder">
                <div class="cg-spinner" />
                <p>{{ $t('referral.loading') }}</p>
            </div>
            <div v-else-if="referralsError" class="cg-referral-placeholder cg-referral-placeholder--error">
                <p>{{ referralsError }}</p>
                <button type="button" class="cg-btn-secondary" @click="refreshCurrentReferrals">{{ $t('common.retry') }}</button>
            </div>
            <div v-else-if="!currentReferrals.length" class="cg-referral-placeholder">
                <p>{{ $t('referral.noReferrals') }}</p>
                <small class="cg-muted">{{ $t('referral.noReferralsHint') }}</small>
            </div>
            <div v-else class="cg-referral-list">
                <article v-for="referral in currentReferrals" :key="referral.id" class="cg-referral-card">
                    <div class="cg-referral-card-head">
                        <div>
                            <p class="cg-modal-eyebrow">{{ $t('referral.referTo') }} {{ referral.assigned_role || $t('referral.support') }}</p>
                            <h4>{{ activeChat?.title || $t('referral.currentChat') }}</h4>
                        </div>
                        <span class="cg-referral-status" :class="'cg-referral-status--' + referral.status">
                            {{ referralStatusLabel(referral.status) }}
                        </span>
                    </div>
                    <div class="cg-referral-block">
                        <div class="cg-referral-label">{{ $t('referral.referredMessage') }}</div>
                        <p v-if="referral.trigger_message?.content" class="cg-referral-text">{{ referral.trigger_message.content }}</p>
                        <p v-else class="cg-referral-text cg-muted">{{ $t('referral.messageVoiceOrFile') }}</p>
                        <div class="cg-referral-foot">
                            <span>{{ formatDate(referral.trigger_message?.created_at) }}</span>
                            <button type="button" class="cg-link-btn" @click="scrollToReferredMessage(referral.trigger_message_id)">
                                {{ $t('referral.viewInChat') }}
                            </button>
                        </div>
                    </div>
                    <div v-if="referral.description" class="cg-referral-block">
                        <div class="cg-referral-label">{{ $t('referral.yourNote') }}</div>
                        <p class="cg-referral-text">{{ referral.description }}</p>
                    </div>
                    <div v-if="referral.response" class="cg-referral-block cg-referral-block--response">
                        <div class="cg-referral-label">{{ $t('referral.supportResponse') }}</div>
                        <p class="cg-referral-text">{{ referral.response.text }}</p>
                        <div class="cg-referral-foot">
                            <span>{{ formatDate(referral.response.created_at) }}</span>
                        </div>
                        <div v-if="referral.response.files?.length" class="cg-referral-files">
                            <a
                                v-for="file in referral.response.files"
                                :key="file.id"
                                :href="file.url"
                                target="_blank"
                                rel="noopener"
                                class="cg-file-chip"
                            >
                                <span class="truncate">{{ file.name || $t('common.file') }}</span>
                            </a>
                        </div>
                    </div>
                    <div v-else class="cg-referral-block cg-muted">
                        <div class="cg-referral-label">{{ $t('referral.supportResponse') }}</div>
                        <p class="cg-referral-text">{{ $t('referral.noResponse') }}</p>
                    </div>
                </article>
            </div>
        </BaseModal>

        <BaseModal
            :open="renameModal.open"
            :title="$t('chat.renameChatTitle')"
            size="md"
            @update:open="(v) => { if (!v) closeRenameModal(); }"
        >
            <p class="cg-muted">{{ $t('chat.renameChatDesc') }}</p>
            <form class="cg-rename-form" @submit.prevent="submitRename">
                <input
                    ref="renameInputRef"
                    v-model="renameModal.title"
                    type="text"
                    class="cg-input"
                    maxlength="100"
                    :placeholder="$t('chat.newTitlePlaceholder')"
                    :disabled="renameModal.loading"
                />
                <div class="cg-modal-actions">
                    <button type="button" class="cg-btn-secondary" :disabled="renameModal.loading" @click="closeRenameModal">
                        {{ $t('common.cancel') }}
                    </button>
                    <button type="submit" class="cg-btn-primary" :disabled="renameModal.loading">
                        {{ renameModal.loading ? $t('chat.savingTitle') : $t('chat.saveTitle') }}
                    </button>
                </div>
            </form>
        </BaseModal>

        <BaseModal v-model:open="chatActionsOpen" :title="$t('chat.chatSettings')" size="sm">
            <div v-if="chatActionsChat" class="cg-actions-stack">
                <button type="button" class="cg-actions-row" @click="fromChatActionsRename">
                    {{ $t('chat.renameChat') }}
                </button>
                <button type="button" class="cg-actions-row" @click="fromChatActionsReferrals">
                    {{ $t('nav.referrals') }}
                </button>
                <button
                    type="button"
                    class="cg-actions-row cg-actions-row--danger"
                    :disabled="deletingChatId === chatActionsChat.id"
                    @click="fromChatActionsDelete"
                >
                    {{ $t('chat.deleteChat') }}
                </button>
            </div>
        </BaseModal>

        <BaseModal
            v-model:open="deleteConfirm.open"
            :title="$t('chat.deleteChatConfirmTitle')"
            variant="danger"
            size="sm"
            :close-on-backdrop="false"
        >
            <p>{{ $t('chat.confirmDelete') }}</p>
            <template #footer>
                <button type="button" class="cg-btn-secondary" @click="deleteConfirm.open = false">
                    {{ $t('common.cancel') }}
                </button>
                <button type="button" class="cg-btn-danger" :disabled="deletingChatId !== null" @click="executeDeleteChat">
                    {{ $t('common.delete') }}
                </button>
            </template>
        </BaseModal>

        <BaseModal v-model:open="settingsModalOpen" :title="$t('chat.settingsTitle')" size="lg">
            <div class="cg-settings">
                <section class="cg-settings-block">
                    <h3 class="cg-settings-heading">{{ $t('chat.languageSection') }}</h3>
                    <p class="cg-muted">{{ $t('chat.languageHint') }}</p>
                    <label class="cg-label" for="cg-settings-lang-select">{{ $t('chat.interfaceLanguage') }}</label>
                    <select id="cg-settings-lang-select" class="cg-select" :value="locale" @change="onLanguageChange">
                        <option value="fa">فارسی</option>
                        <option value="en">English</option>
                        <option value="ar">العربية</option>
                    </select>
                </section>
                <section class="cg-settings-block">
                    <h3 class="cg-settings-heading">{{ $t('chat.appearanceSection') }}</h3>
                    <p class="cg-muted">{{ $t('chat.appearanceHint') }}</p>
                </section>
                <section class="cg-settings-block">
                    <h3 class="cg-settings-heading">{{ $t('nav.referrals') }}</h3>
                    <button
                        type="button"
                        class="cg-btn-secondary cg-btn-block"
                        :disabled="!activeChatId"
                        @click="settingsModalOpen = false; toggleReferralPanel()"
                    >
                        {{ $t('referral.title') }}
                    </button>
                </section>
                <section class="cg-settings-block">
                    <h3 class="cg-settings-heading">{{ $t('nav.tickets') }}</h3>
                    <button type="button" class="cg-btn-secondary cg-btn-block" @click="settingsModalOpen = false; goToTickets()">
                        {{ $t('nav.tickets') }}
                    </button>
                </section>
                <section class="cg-settings-block">
                    <h3 class="cg-settings-heading">{{ $t('nav.profile') }}</h3>
                    <button type="button" class="cg-btn-secondary cg-btn-block" @click="settingsModalOpen = false; goToProfile()">
                        {{ $t('nav.profile') }}
                    </button>
                </section>
                <section class="cg-settings-block">
                    <button
                        type="button"
                        class="cg-btn-danger cg-btn-block"
                        :disabled="loggingOut"
                        @click="settingsModalOpen = false; logout()"
                    >
                        {{ loggingOut ? '...' : $t('nav.logout') }}
                    </button>
                </section>
            </div>
        </BaseModal>
    </div>
</template>
<script setup>
import {ref, computed, nextTick, onMounted, onUnmounted, reactive, watch} from 'vue';
import HandoffModal from './HandoffModal.vue';
import NotificationBell from './NotificationBell.vue';
import AiAnswer from './AiAnswer.vue';
import BaseModal from './chat/BaseModal.vue';
import ChatHeader from './chat/ChatHeader.vue';
import ChatSidebar from './chat/ChatSidebar.vue';
import ChatComposer from './chat/ChatComposer.vue';
import ChatTypingIndicator from './chat/ChatTypingIndicator.vue';
import {useToast} from 'vue-toast-notification'
import {apiFetch} from '../lib/http';
import { useLanguage } from '../i18n';

// i18n setup - CSP-safe, no vue-i18n
const { locale, setLocale, direction, isRtl, initLocale, t } = useLanguage();

const toast = useToast();
const logoutUrl = window?.AppConfig?.logoutUrl || '/logout';
const csrfToken = window?.AppConfig?.csrfToken
    || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    || '';
const loggingOut = ref(false);
const logout = async () => {
    if (loggingOut.value) {
        return;
    }
    loggingOut.value = true;
    try {
        const response = await fetch(logoutUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        });
        if (!response.ok) {
            throw new Error('Logout failed');
        }
        const storage = getWelcomeStorage();
        if (storage) {
            storage.removeItem(WELCOME_STORAGE_KEY);
        }
        window.location.href = '/login';
    } catch (error) {
        console.error('logout failed', error);
        toast.error(t('auth.logoutError'));
    } finally {
        loggingOut.value = false;
    }
};
const isHandoffModalOpen = ref(false);
const selectedMessageForHandoff = ref(null);
// --- State ---
const isRecording = ref(false);
const recordingTime = ref(0);
const recordingInterval = ref(null);
const mediaRecorder = ref(null);
const audioChunks = ref([]);
const availableRoles = ref([]);
const deletingChatId = ref(null);
const currentUser = ref({ name: '', avatar: null });
const sidebarSearch = ref('');
const settingsModalOpen = ref(false);
const chatActionsOpen = ref(false);
const chatActionsChat = ref(null);
const deleteConfirm = reactive({ open: false, chatId: null });
const composerRef = ref(null);
const renameModal = reactive({
    open: false,
    chatId: null,
    title: '',
    loading: false
});
const renameInputRef = ref(null);
const WELCOME_STORAGE_KEY = 'supportAI:welcome-session';
const ACTIVE_CHAT_STORAGE_KEY = 'supportAI:active-conversation-id';
const EVENT_ACTIVE_CHAT_CHANGED = 'supportAI:active-chat-changed';
const getPreferredConversationId = () => {
    if (typeof window === 'undefined') return null;
    const fromUrl = new URLSearchParams(window.location.search).get('conversation');
    if (fromUrl) return Number(fromUrl) || fromUrl;
    const saved = window.localStorage?.getItem(ACTIVE_CHAT_STORAGE_KEY);
    if (!saved) return null;
    return Number(saved) || saved;
};
const persistActiveConversationId = (id) => {
    if (typeof window === 'undefined' || !id) return;
    window.localStorage?.setItem(ACTIVE_CHAT_STORAGE_KEY, String(id));
    window.dispatchEvent(new CustomEvent(EVENT_ACTIVE_CHAT_CHANGED, { detail: { id } }));
};
const fetchDepartments = async () => {  // ← این تابع رو کامل اضافه کن
    try {
        const response = await apiFetch('/support-roles');
        if (response.ok) {
            const data = await response.json();  // یا data.data اگر API فرق داره
            availableRoles.value = data;  // array objects مثل [{id: "...", name: "..."}]
            // console.log('Roles loaded:', availableRoles.value);  // برای debug
        } else {
            console.error('خطا در بارگذاری roles');
        }
    } catch (error) {
        console.error('خطا در fetch departments:', error);
        // اختیاری: alert('خطا در بارگذاری بخش‌ها');
    }
};

const getDisplayName = (user) => {
    if (!user) return '';
    const parts = [user?.name, user?.family].filter(Boolean);
    return parts.join(' ').trim();
};

// Store user's voice preference globally
const userVoiceGender = ref('female');

const fetchCurrentUserName = async () => {
    try {
        const res = await apiFetch('/user/profile');
        if (!res.ok) return '';
        const payload = await res.json();
        // Also store voice preference
        if (payload?.user?.voice_gender) {
            userVoiceGender.value = payload.user.voice_gender;
        }
        return getDisplayName(payload?.user);
    } catch (error) {
        return '';
    }
};

const fetchCurrentUser = async () => {
    try {
        const res = await apiFetch('/user/profile');
        if (!res.ok) return;
        const payload = await res.json();
        const u = payload?.user;
        if (u) {
            currentUser.value = {
                name: getDisplayName(u),
                avatar: u.avatar || null
            };
        }
        if (u?.voice_gender) {
            userVoiceGender.value = u.voice_gender;
        }
    } catch (error) {
        console.debug('Failed to fetch current user', error);
    }
};

// Fetch user preferences on mount
const fetchUserPreferences = async () => {
    try {
        const res = await apiFetch('/user/profile');
        if (!res.ok) return;
        const payload = await res.json();
        if (payload?.user?.voice_gender) {
            userVoiceGender.value = payload.user.voice_gender;
        }
    } catch (error) {
        console.debug('Failed to fetch user preferences', error);
    }
};

const getWelcomeStorage = () => {
    if (typeof window === 'undefined') return null;
    try {
        return window.sessionStorage;
    } catch {
        return window.localStorage;
    }
};

const showWelcomeToast = async () => {
    if (typeof window === 'undefined') return;
    const storage = getWelcomeStorage();
    if (storage && storage.getItem(WELCOME_STORAGE_KEY)) return;

    const fetchedName = await fetchCurrentUserName();
    const fallbackName = t('chat.welcomeNameFallback');
    const displayName = fetchedName || fallbackName;
    const template = t('chat.welcomeToast');
    const message = template.replace('{name}', displayName);

    toast.open({
        message,
        type: 'default',
        position: isRtl.value ? 'top-left' : 'top-right',
        duration: 3600,
        dismissible: false,
        className: 'welcome-toast'
    });

    if (storage) {
        storage.setItem(WELCOME_STORAGE_KEY, String(Date.now()));
    }
};
const formatDate = (isoString) => {
    if (!isoString) return '';
    const date = new Date(isoString);
    // Use locale for date formatting
    const localeMap = { 'fa': 'fa-IR', 'en': 'en-US', 'ar': 'ar-SA' };
    const dateLocale = localeMap[locale.value] || 'fa-IR';
    return date.toLocaleString(dateLocale, {
        hour: '2-digit',
        minute: '2-digit',
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};
const referralStatusLabel = (status) => {
    const key = `referral.status.${status}`;
    const translated = t(key);
    // If translation exists, return it; otherwise return the status itself
    return translated !== key ? translated : (status || '-');
};
const getFileEmoji = (mimeOrType = '') => {
    const type = String(mimeOrType || '').toLowerCase();
    if (type.startsWith('image/')) return '';
    if (type.includes('pdf')) return '';
    if (type.includes('word') || type.includes('doc')) return '';
    if (type.includes('zip') || type.includes('rar')) return '';
    if (type.includes('sheet') || type.includes('excel') || type.includes('csv')) return '';
    if (type.startsWith('audio/')) return '';
    return '';
};
// --- State ---
const chats = ref([]); // لیست چت‌ها از API
const activeChatId = ref(null);
const inputMessage = ref('');
const loading = ref(false);
const messagesContainer = ref(null);
const showScrollButton = ref(false);
const SCROLL_OFFSET_THRESHOLD = 120;
const mediaFetchedFor = new Set();
const MOBILE_BREAKPOINT = 768;
const isMobile = ref(false);
const isSidebarOpen = ref(true);
const referralPanelOpen = ref(false);
const referralStore = reactive({});
// Language is managed by useLanguage() composable

const currentReferrals = computed(() => {
    const chatId = activeChatId.value;
    if (!chatId || !referralStore[chatId]) return [];
    return referralStore[chatId].items || [];
});
const referralsLoading = computed(() => {
    const chatId = activeChatId.value;
    if (!chatId || !referralStore[chatId]) return false;
    return referralStore[chatId].loading || false;
});
const referralsError = computed(() => {
    const chatId = activeChatId.value;
    if (!chatId || !referralStore[chatId]) return '';
    return referralStore[chatId].error || '';
});
const hasPublicReferralResponses = computed(() =>
    currentReferrals.value.some((item) => !!item.response)
);

const filteredChats = computed(() => {
    const q = sidebarSearch.value.trim().toLowerCase();
    if (!q) return chats.value;
    return chats.value.filter((c) => (c.title || '').toLowerCase().includes(q));
});

const updateLayoutFlags = () => {
    if (typeof window === 'undefined') return;
    const mobile = window.innerWidth <= MOBILE_BREAKPOINT;
    isMobile.value = mobile;
    isSidebarOpen.value = mobile ? false : true;
};

const toggleSidebar = () => {
    if (!isMobile.value) return;
    isSidebarOpen.value = !isSidebarOpen.value;
};

const closeSidebar = () => {
    if (!isMobile.value) return;
    isSidebarOpen.value = false;
};

const ensureReferralState = (chatId) => {
    if (!chatId) return null;
    if (!referralStore[chatId]) {
        referralStore[chatId] = {
            items: [],
            loading: false,
            loaded: false,
            error: ''
        };
    }
    return referralStore[chatId];
};

async function loadReferrals(chatId, {force = false} = {}) {
    if (!chatId) return;
    const state = ensureReferralState(chatId);
    if (!state) return;
    if (state.loading) return;
    if (state.loaded && !force) return;
    state.loading = true;
    state.error = '';
    try {
        const res = await apiFetch(`/conversations/${chatId}/referrals`);
        if (!res.ok) throw new Error('failed');
        const {data} = await res.json();
        state.items = data || [];
        state.loaded = true;
    } catch (err) {
        console.error('Failed to load referrals', err);
        state.error = t('referral.loadError');
    } finally {
        state.loading = false;
    }
}

const refreshCurrentReferrals = async () => {
    const chatId = activeChatId.value;
    if (!chatId) return;
    const state = ensureReferralState(chatId);
    if (!state) return;
    state.loaded = false;
    await loadReferrals(chatId, {force: true});
};

const closeReferralPanel = () => {
    referralPanelOpen.value = false;
};

const openChatActionsModal = (chat) => {
    chatActionsChat.value = chat;
    chatActionsOpen.value = true;
};

const fromChatActionsRename = () => {
    const c = chatActionsChat.value;
    chatActionsOpen.value = false;
    if (c) openRenameModal(c);
};

const fromChatActionsReferrals = () => {
    const c = chatActionsChat.value;
    chatActionsOpen.value = false;
    if (c) openReferralPanelForChat(c);
};

const fromChatActionsDelete = () => {
    const c = chatActionsChat.value;
    chatActionsOpen.value = false;
    if (c) requestDeleteChat(c.id);
};

const requestDeleteChat = (chatId) => {
    deleteConfirm.chatId = chatId;
    deleteConfirm.open = true;
};

const toggleReferralPanel = async () => {
    if (!activeChatId.value) return;
    if (referralPanelOpen.value) {
        referralPanelOpen.value = false;
        return;
    }
    referralPanelOpen.value = true;
    await loadReferrals(activeChatId.value);
};

const openReferralPanelForChat = async (chat) => {
    if (activeChatId.value !== chat.id) {
        await setActiveChat(chat.id);
    }
    referralPanelOpen.value = true;
    await loadReferrals(chat.id);
};

const openRenameModal = (chat) => {
    if (!chat) return;
    renameModal.open = true;
    renameModal.chatId = chat.id;
    renameModal.title = chat.title;
    renameModal.loading = false;
    nextTick(() => {
        renameInputRef.value?.focus();
        renameInputRef.value?.select();
    });
};

const closeRenameModal = () => {
    renameModal.open = false;
    renameModal.chatId = null;
    renameModal.title = '';
    renameModal.loading = false;
};

const submitRename = async () => {
    const chatId = renameModal.chatId;
    const newTitle = (renameModal.title || '').trim();
    if (!chatId) return;
    if (!newTitle) {
        toast.error(t('chat.titleEmpty'));
        return;
    }
    renameModal.loading = true;
    try {
        await renameChat(chatId, newTitle);
        closeRenameModal();
        toast.success(t('chat.titleUpdated'));
    } catch (e) {
        console.error('rename failed', e);
        toast.error(t('chat.titleError'));
    } finally {
        renameModal.loading = false;
    }
};

const handleScroll = () => {
    const el = messagesContainer.value;
    if (!el) return;
    const distanceFromBottom = el.scrollHeight - (el.scrollTop + el.clientHeight);
    showScrollButton.value = distanceFromBottom > SCROLL_OFFSET_THRESHOLD;
};

async function ensureMediaLoaded(msg) {
    if (!msg?.id) return;
    if (msg.voiceUrl) return;     // قبلاً ست شده
    if (mediaFetchedFor.has(msg.id)) return; // یکبار درخواست دادیم

    mediaFetchedFor.add(msg.id);
    try {
        const r = await apiFetch(`/messages/${msg.id}/media`);
        if (!r.ok) {
            mediaFetchedFor.delete(msg.id); // امکان تلاش مجدد
            return;
        }
        const {data: media} = await r.json();
        msg.media = media || [];
        const voice = msg.media.find(m => m.collection === 'message_voices' || (m.mime || '').startsWith('audio/'));
        if (voice) msg.voiceUrl = voice.url;
        else mediaFetchedFor.delete(msg.id);
    } catch (e) {
        mediaFetchedFor.delete(msg.id); // امکان تلاش مجدد
    }
}

// فرض می‌کنیم AI User با این ایمیل ثبت شده
const AI_EMAIL = 'ai@system.local';
let aiUserId = null;

const getMicrophonePermissionState = async () => {
    if (typeof navigator === 'undefined' || !navigator.permissions) return null;
    try {
        const status = await navigator.permissions.query({name: 'microphone'});
        return status.state;
    } catch {
        return null;
    }
};

const formatMicrophoneError = (error) => {
    if (!error) return t('chat.micUnknownError');
    switch (error.name) {
        case 'NotAllowedError':
        case 'PermissionDeniedError':
            return t('chat.micPermissionError');
        case 'NotFoundError':
        case 'DevicesNotFoundError':
            return t('chat.micNotFound');
        case 'NotReadableError':
            return t('chat.micNotReadable');
        case 'NotSupportedError':
            return t('chat.micNotSupported');
        default:
            return t('chat.micUnknownError');
    }
};

// --- Methods ---
const startRecording = async () => {
    try {
        const permissionState = await getMicrophonePermissionState();
        if (permissionState === 'denied') {
            toast.error(t('chat.micPermissionBlocked'));
            return;
        }
        const stream = await navigator.mediaDevices.getUserMedia({audio: true});
        mediaRecorder.value = new MediaRecorder(stream);
        audioChunks.value = [];

        mediaRecorder.value.ondataavailable = (event) => {
            audioChunks.value.push(event.data);
        };

        mediaRecorder.value.onstop = async () => {
            const audioBlob = new Blob(audioChunks.value, {type: 'audio/webm'});
            await uploadVoice(audioBlob);
        };

        mediaRecorder.value.start();
        isRecording.value = true;
        recordingTime.value = 0;

        // شروع تایمر (هر 100ms یک بار آپدیت می‌شه برای smooth بودن)
        recordingInterval.value = setInterval(() => {
            recordingTime.value += 100;
            // محدودیت 10 دقیقه (600,000 میلی‌ثانیه)
            if (recordingTime.value >= 600000) {
                sendRecording();
            }
        }, 100);
    } catch (error) {
        const message = formatMicrophoneError(error);
        toast.error(message);
        console.error('Recording error:', error);
    }
};

const cancelRecording = () => {
    if (mediaRecorder.value && mediaRecorder.value.state !== 'inactive') {
        mediaRecorder.value.stop();
        mediaRecorder.value.stream.getTracks().forEach(track => track.stop());
    }
    cleanupRecording();
};

const sendRecording = () => {
    if (mediaRecorder.value && mediaRecorder.value.state !== 'inactive') {
        mediaRecorder.value.stop();
    }
    cleanupRecording();
};

const cleanupRecording = () => {
    if (recordingInterval.value) {
        clearInterval(recordingInterval.value);
        recordingInterval.value = null;
    }
    isRecording.value = false;
    recordingTime.value = 0;
};

const uploadVoice = async (blob) => {
    const chat = chats.value.find(c => c.id === activeChatId.value);
    if (!chat) return;

    // ایجاد URL موقت برای پخش فوری صدا
    const tempVoiceUrl = URL.createObjectURL(blob);
    const tempMsgId = 'voice-temp-' + Date.now();

    // 1) فوری: پیام صوتی کاربر را نمایش بده (قبل از ارسال)
    chat.messages.push({
        id: tempMsgId,
        sender: 'user',
        text: '',
        voiceUrl: tempVoiceUrl,
        isSending: true,  // نشان می‌دهد در حال ارسال است
        created_at: new Date().toISOString()
    });

    await nextTick();
    scrollToBottom();

    try {
        // 2) آپلود فایل
        const formData = new FormData();
        formData.append('file', blob, 'recording.webm');
        formData.append('collection', 'message_voices');

        const uploadRes = await fetch('/api/v1/files', { method: 'POST', body: formData });
        if (!uploadRes.ok) throw new Error('upload failed');
        const { file_id } = await uploadRes.json();

        // 3) ارسال پیام ویسی به گفتگو (از apiFetch برای credentials و 401-handling)
        const messageRes = await apiFetch(`/conversations/${activeChatId.value}/messages`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                content: '',
                media_ids: [file_id],
                media_kind: 'voice',
                lang: locale.value
            })
        });
        if (!messageRes.ok) {
            const errText = await messageRes.text().catch(() => '');
            console.error('send voice failed', messageRes.status, errText);
            throw new Error('send failed');
        }

        const { user_message, ai_message, conversation } = await messageRes.json();

        // 4) اگر عنوان گفتگو آپدیت شده بود
        if (conversation?.title && conversation.title !== chat.title) {
            chat.title = conversation.title;
        }

        // 5) پیام موقت را با پیام واقعی جایگزین کن
        const tempMsgIndex = chat.messages.findIndex(m => m.id === tempMsgId);
        if (tempMsgIndex !== -1) {
            // متن transcript را از پاسخ بگیر (اگر وجود داشت)
            const transcriptText = user_message.content || '';

            chat.messages[tempMsgIndex] = {
                id: user_message.id,
                sender: 'user',
                text: transcriptText,  // متن ترنسکریپت
                voiceUrl: tempVoiceUrl,  // فعلاً همان URL موقت
                isSending: false,
                created_at: user_message.created_at
            };

            // آپدیت voiceUrl از سرور
            try {
                const r = await fetch(`/api/v1/messages/${user_message.id}/media`, { headers: { 'Accept': 'application/json' }});
                if (r.ok) {
                    const { data: media } = await r.json();
                    const voice = (media || []).find(m => m.collection === 'message_voices' || (m.mime || '').startsWith('audio/'));
                    if (voice) {
                        const msg = chat.messages.find(m => m.id === user_message.id);
                        if (msg) {
                            msg.voiceUrl = voice.url;
                            URL.revokeObjectURL(tempVoiceUrl); // آزاد کردن حافظه
                        }
                    }
                }
            } catch (_) {}
        }

        // 6) پیام AI را با متن ثابت به UI اضافه کن (پاسخ واقعی نمایش/ذخیره نمی‌شود)
        if (ai_message) {
            chat.messages.push({
                id: ai_message.id,
                sender: 'bot',
                text: ai_message.content || '',
                created_at: ai_message.created_at
            });

            // اگر AI ویس هم داده بود، مدیاش را بگیر و voiceUrl ست کن
            try {
                const r2 = await fetch(`/api/v1/messages/${ai_message.id}/media`, { headers: { 'Accept': 'application/json' }});
                if (r2.ok) {
                    const { data: media2 } = await r2.json();
                    const voice2 = (media2 || []).find(m => m.collection === 'message_voices' || (m.mime || '').startsWith('audio/'));
                    if (voice2) {
                        const aimsg = chat.messages.find(m => m.id === ai_message.id);
                        if (aimsg) aimsg.voiceUrl = voice2.url;
                    }
                }
            } catch (_) {}
        } else {
            chat.messages.push({
                id: 'ai-fallback-' + Date.now(),
                sender: 'bot',
                text: t('chat.voiceProcessError'),
                created_at: new Date().toISOString()
            });
        }

        await nextTick();
        scrollToBottom();
    } catch (error) {
        console.error('Upload voice error:', error);

        // حذف پیام موقت در صورت خطا
        const tempMsgIndex = chat.messages.findIndex(m => m.id === tempMsgId);
        if (tempMsgIndex !== -1) {
            chat.messages.splice(tempMsgIndex, 1);
        }
        URL.revokeObjectURL(tempVoiceUrl);

        toast.error(t('chat.uploadVoiceError'));
    }
};


// برای waveform پویا
const getBarHeight = (index) => {
    if (!isRecording.value) return 4;
    // شبیه‌سازی ارتفاع تصادفی برای نمایش
    return 10 + Math.random() * 30;
};

// فرمت تایمر: mm:ss.ms
const formatTimer = (ms) => {
    const totalSeconds = Math.floor(ms / 1000);
    const minutes = Math.floor(totalSeconds / 60).toString().padStart(2, '0');
    const seconds = (totalSeconds % 60).toString().padStart(2, '0');
    const centiseconds = Math.floor((ms % 1000) / 10).toString().padStart(2, '0');
    return `${minutes}:${seconds}.${centiseconds}`;
};

// پاک کردن منابع هنگام خروج
onUnmounted(() => {
    if (recordingInterval.value) {
        clearInterval(recordingInterval.value);
    }
    if (mediaRecorder.value) {
        mediaRecorder.value.stream?.getTracks().forEach(track => track.stop());
    }
    if (typeof window !== 'undefined') {
        window.removeEventListener('resize', updateLayoutFlags);
        window.removeEventListener(EVENT_ACTIVE_CHAT_CHANGED, handleExternalConversationChange);
    }
    if (messagesContainer.value) {
        messagesContainer.value.removeEventListener('scroll', handleScroll);
    }
    if (typeof document !== 'undefined') {
        document.body.style.overflow = '';
    }
    highlightTimers.forEach(timeout => clearTimeout(timeout));
    highlightTimers.clear();
});
// const scrollToBottom = () => {
//     if (messagesContainer.value) {
//         messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
//     }
// };
const scrollToBottom = () => {
    if (messagesContainer.value) {
        messagesContainer.value.scrollTo({
            top: messagesContainer.value.scrollHeight,
            behavior: 'smooth'
        });
        requestAnimationFrame(() => handleScroll());
    }
};


const activeChat = computed(() => {
    return chats.value.find(chat => chat.id === activeChatId.value) || null;
});
watch(activeChatId, (newId) => {
    if (isMobile.value) {
        closeSidebar();
    }
    if (newId && referralPanelOpen.value) {
        loadReferrals(newId, {force: false});
    }
});

watch(
    () => activeChat.value?.messages?.length,
    (newVal, oldVal) => {
        if (!newVal) return;
        nextTick(() => {
            if (!showScrollButton.value) {
                scrollToBottom();
            } else {
                handleScroll();
            }
        });
    }
);
// لود چت‌ها از API
const loadChats = async () => {
    try {
        const res = await apiFetch('/conversations');
        if (res.ok) {
            const {data} = await res.json();
            chats.value = data.map(chat => ({
                id: chat.id,
                title: chat.title,
                messages: []
            }));
            if (chats.value.length > 0 && !activeChatId.value) {
                const preferred = getPreferredConversationId();
                const matched = preferred ? chats.value.find((chat) => String(chat.id) === String(preferred)) : null;
                setActiveChat(matched?.id || chats.value[0].id);
            }
        }
    } catch (e) {
        console.error('Failed to load chats', e);
    }
};

// لود پیام‌ها
const loadMessages = async (chatId) => {
    try {
        const res = await apiFetch(`/conversations/${chatId}/messages`);
        if (res.ok) {
            const {data} = await res.json();
            const chat = chats.value.find(c => c.id === chatId);
            if (chat) {
                chat.messages = data.map(msg => ({
                    id: msg.id,
                    sender: msg.sender_type === 'ai' ? 'bot' : 'user',
                    text: msg.content,
                    created_at: msg.created_at,
                    type: msg.type,
                    has_media: !!msg.has_media,
                    has_voice: !!msg.has_voice,
                }));
                const recent = (chat.messages || []).slice(-12);
                recent.forEach(m => {
                    const maybeHasMedia =
                        m.has_media === true ||
                        m.has_voice === true ||
                        m.type === 'voice' ||
                        !(m.text && m.text.trim()); // متن خالی = احتمالاً ویس/فایل
                    if (maybeHasMedia) ensureMediaLoaded(m); // بدون await
                });

                // await Promise.all(
                //     (chat.messages || []).map(async (msg) => {
                //         try {
                //             const r = await apiFetch(`/messages/${msg.id}/media`);
                //             if (r.ok) {
                //                 const {data: media} = await r.json();
                //                 msg.media = media || [];
                //                 const voice = msg.media.find(m => m.collection === 'message_voices' || (m.mime || '').startsWith('audio/'));
                //                 if (voice) {
                //                     msg.voiceUrl = voice.url;
                //                 }
                //             } else {
                //                 msg.media = [];
                //             }
                //         } catch {
                //             msg.media = [];
                //         }
                //     })
                // );
            }
        }
    } catch (e) {
        console.error('Failed to load messages', e);
    }
};

// ایجاد چت جدید
const startNewChat = async () => {
    try {
        const res = await apiFetch('/conversations', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({title: 'چت جدید'})
        });
        if (res.ok) {
            const newChat = await res.json();
            chats.value.unshift({
                id: newChat.id,
                title: newChat.title,
                messages: []
            });
            setActiveChat(newChat.id);
        }
    } catch (e) {
        toast.error(t('chat.newChatError'));
    }
};

// فعال‌سازی چت
const setActiveChat = async (id) => {
    chatActionsOpen.value = false;
    activeChatId.value = id;
    persistActiveConversationId(id);
    await loadMessages(id);
    await nextTick();
    scrollToBottom();
};

const handleExternalConversationChange = async (event) => {
    const id = event?.detail?.id;
    if (!id || String(id) === String(activeChatId.value)) return;
    const exists = chats.value.some((chat) => String(chat.id) === String(id));
    if (!exists) {
        await loadChats();
    }
    const found = chats.value.find((chat) => String(chat.id) === String(id));
    if (found) {
        await setActiveChat(found.id);
    }
};

// ارسال پیام
const sendMessage = async () => {
    if (!inputMessage.value.trim() || loading.value) return;

    const userMsg = {
        sender: 'user',
        text: inputMessage.value.trim(),
        _tmpKey: 'u-' + Date.now()
    };

    const activeChat = chats.value.find(c => c.id === activeChatId.value);
    if (!activeChat) return;

    // اضافه کردن پیام کاربر به UI
    activeChat.messages.push(userMsg);
    inputMessage.value = '';
    await nextTick();
    scrollToBottom();
    composerRef.value?.resetHeight?.();
    loading.value = true;
    await nextTick();
    scrollToBottom();
    try {
        const res = await apiFetch(`/conversations/${activeChatId.value}/messages`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                content: userMsg.text,
                lang: locale.value
            })
        });

        if (res.ok) {
            let data;
            try {
                data = await res.json();
            } catch (parseErr) {
                console.error('Failed to parse response', parseErr);
                throw new Error('Invalid response');
            }
            const ai_message = data?.ai_message;
            const user_message = data?.user_message;
            const conversation = data?.conversation;

            const chatLocal = chats.value.find(c => c.id === activeChatId.value);
            if (!chatLocal) return;

            // آپدیت پیام کاربر با id از سرور (برای key پایدار و جلوگیری از flicker)
            const lastMsg = chatLocal.messages[chatLocal.messages.length - 1];
            if (lastMsg?.sender === 'user' && user_message) {
                lastMsg.id = user_message.id;
                lastMsg.created_at = user_message.created_at;
                delete lastMsg._tmpKey;
            }

            // آپدیت عنوان چت اگر تغییر کرده (اولین پیام: chat_topic از AI برمی‌گرده)
            if (conversation?.title && conversation.title !== chatLocal.title) {
                chatLocal.title = conversation.title;
            }

            // پاسخ AI با متن ثابت (محتوا در UI/state/inspect نمایش داده نمی‌شود)
            const botMsg = {
                id: ai_message?.id ?? 'ai-fallback-' + Date.now(),
                sender: 'bot',
                text: ai_message?.content ?? t('chat.sendError'),
                created_at: ai_message?.created_at ?? new Date().toISOString(),
                has_media: false,
                has_voice: false,
            };
            chatLocal.messages = [...chatLocal.messages, botMsg];

            await nextTick();
            scrollToBottom();
        } else {
            let errMsg = t('chat.sendError');
            try {
                const errData = await res.json();
                errMsg = errData?.error ?? errData?.message ?? errMsg;
            } catch (_) {}
            throw new Error(errMsg);
        }
    } catch (error) {
        const chatLocal = chats.value.find(c => c.id === activeChatId.value);
        if (chatLocal) {
            chatLocal.messages.push({
                sender: 'bot',
                text: typeof error?.message === 'string' ? error.message : t('chat.sendError')
            });
        }
        toast.error(t('chat.sendError'));
    } finally {
        loading.value = false;
        await nextTick();
        scrollToBottom();
    }
};

const renameChat = async (chatId, title) => {
    try {
        const res = await apiFetch(`/conversations/${chatId}/title`, {
            method: 'PATCH',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({title})
        });
        if (!res.ok) throw new Error('rename failed');
        const updated = await res.json();
        const chat = chats.value.find(c => c.id === chatId);
        if (chat) chat.title = updated.title;
    } catch (e) {
        console.error('renameChat', e);
        throw e;
    }
};
// 2-3) Enter = ارسال / Shift+Enter = خط جدید
function onKeydown(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault()
        sendMessage()
    }
}

const executeDeleteChat = async () => {
    const chatId = deleteConfirm.chatId;
    if (!chatId) {
        deleteConfirm.open = false;
        return;
    }
    const chat = chats.value.find((c) => c.id === chatId);
    if (!chat) {
        deleteConfirm.open = false;
        deleteConfirm.chatId = null;
        return;
    }

    deletingChatId.value = chatId;
    try {
        const res = await apiFetch(`/conversations/${chatId}`, { method: 'DELETE' });
        if (!res.ok) throw new Error('delete failed');

        const index = chats.value.findIndex((c) => c.id === chatId);
        chats.value = chats.value.filter((c) => c.id !== chatId);

        if (activeChatId.value === chatId) {
            const next = chats.value[index] || chats.value[index - 1] || chats.value[0];
            if (next) {
                await setActiveChat(next.id);
            } else {
                activeChatId.value = null;
            }
        }
        toast.success(t('chat.chatDeleted'));
    } catch (e) {
        console.error('delete chat failed', e);
        toast.error(t('chat.deleteError'));
    } finally {
        deletingChatId.value = null;
        deleteConfirm.open = false;
        deleteConfirm.chatId = null;
    }
};
const copyText = (text) => {
    navigator.clipboard.writeText(text).then(() => {
        toast.info(t('chat.textCopied'))
    });
};
const goToTickets = () => {
    window.location.href = '/ticket';
};
const handleNotificationSelect = async (notification) => {
    if (!notification) return;
    if (notification.category === 'referral' && notification.conversation_id) {
        await setActiveChat(notification.conversation_id);
    } else if (notification.category === 'ticket') {
        goToTickets();
    }
};
const goToProfile = () => {
    window.location.href = '/user/profile';
};
const showHandoffModal = (message) => {
    selectedMessageForHandoff.value = message;
    isHandoffModalOpen.value = true;
};
const handleHandoffSubmit = async (data) => {
    try {
        if (!selectedMessageForHandoff.value?.id) {
            toast.error(t('chat.handoffError'));
            return
        }

        const res = await apiFetch(`/messages/${selectedMessageForHandoff.value.id}/handoff`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
                body: JSON.stringify(data)
            }
        )

        if (!res.ok) {
            let msg = t('chat.handoffError')
            try {
                const j = await res.json();
                msg = j?.message || j?.error || msg
            } catch {
            }
            toast.error(msg);
            return
        }
        toast.success(t('chat.handoffSuccess'));
        isHandoffModalOpen.value = false
        selectedMessageForHandoff.value = null
    } catch (e) {
        toast.error(t('chat.handoffError') + ': ' + (e?.message || ''));
    }
}
const audioRefs = ref({});
let currentlyPlayingId = null;
const registerAudioRef = (id, el) => {
    if (el) {
        audioRefs.value[id] = el;
    } else {
        delete audioRefs.value[id];
    }
};
const playVoice = async (id) => {
    const el = audioRefs.value[id];
    if (!el) return;

    // توقف صدای قبلی
    if (currentlyPlayingId && currentlyPlayingId !== id) {
        const prev = audioRefs.value[currentlyPlayingId];
        if (prev && !prev.paused) prev.pause();
    }
    currentlyPlayingId = id;

    // آماده‌سازی برای پخش
    if (el.readyState < 2) { // HAVE_CURRENT_DATA
        el.load();
        await new Promise(res => {
            const onReady = () => {
                el.removeEventListener('canplay', onReady);
                res();
            };
            el.addEventListener('canplay', onReady, {once: true});
        });
    }
    el.currentTime = 0;
    try {
        await el.play();
    } catch (e) {
        // بعضی مرورگرها سخت‌گیرند: اگر از روی bubble کلیک کردی و باز هم خطا داد،
        // یک fallback: simulate click on the control
        console.debug('play() failed, user gesture required?', e);
    }
};

const highlightTimers = new Map();
const focusMessageById = (messageId) => {
    if (!messageId || !messagesContainer.value) return;
    const target = messagesContainer.value.querySelector(`[data-msg-id="${messageId}"]`);
    if (!target) return;
    target.scrollIntoView({behavior: 'smooth', block: 'center'});
    target.classList.add('cg-msg--highlight');
    if (highlightTimers.has(messageId)) {
        clearTimeout(highlightTimers.get(messageId));
    }
    const timer = setTimeout(() => {
        target.classList.remove('cg-msg--highlight');
        highlightTimers.delete(messageId);
    }, 2200);
    highlightTimers.set(messageId, timer);
};

const scrollToReferredMessage = (messageId) => {
    if (!messageId) return;
    const runScroll = () => focusMessageById(messageId);
    if (isMobile.value && referralPanelOpen.value) {
        referralPanelOpen.value = false;
        setTimeout(runScroll, 280);
    } else {
        runScroll();
    }
};

const onBubbleClick = async (message) => {
    if (!message) return;

    if (!message.voiceUrl && (message.has_voice || message.has_media)) {
        await ensureMediaLoaded(message);
        // بعد از ست شدن voiceUrl، المان audio در DOM رندر می‌شود؛ ref بعد از nextTick ثبت می‌شود
        await nextTick();
    }

    if (message.voiceUrl) {
        playVoice(message.id);
    }
};

const onLanguageChange = (event) => {
    // Update language using the i18n system
    const newLocale = event.target.value;
    setLocale(newLocale);
};

// --- Lifecycle ---
onMounted(() => {
    // Initialize i18n and apply direction to document
    initLocale();

    // Load voices for browser TTS (if used)
    if (typeof speechSynthesis !== 'undefined') {
        loadVoices();
        speechSynthesis.onvoiceschanged = loadVoices;
    }

    loadChats();
    fetchDepartments();
    fetchUserPreferences();
    fetchCurrentUser();
    nextTick(() => {
        showWelcomeToast();
    });
    if (typeof window !== 'undefined') {
        updateLayoutFlags();
        window.addEventListener('resize', updateLayoutFlags);
        window.addEventListener(EVENT_ACTIVE_CHAT_CHANGED, handleExternalConversationChange);
    }
});


watch(() => messagesContainer.value, (el, prev) => {
    if (prev) {
        prev.removeEventListener('scroll', handleScroll);
    }
    if (el) {
        el.addEventListener('scroll', handleScroll, {passive: true});
        nextTick(() => {
            handleScroll();
            scrollToBottom();
        });
    }
});


const synth = window.speechSynthesis;
const isSpeaking = ref(false);
let currentUtter = null;
let voices = [];

const loadVoices = () => {
    voices = synth.getVoices();
};
// loadVoices is called in the main onMounted hook above

// انتخاب بهترین صدای فارسی موجود
const pickFaVoice = () => {
    if (!voices || voices.length === 0) return null;
    // اولویت با fa-IR
    let v = voices.find(v => (v.lang || '').toLowerCase().startsWith('fa'));
    if (v) return v;
    // بعضی سیستم‌ها اسم فارسی رو متفاوت میارن (مثلاً Google فارسی)
    v = voices.find(v => /fa|farsi|فارسی/i.test(v.name));
    return v || voices[0]; // اگر نبود، هرچی هست
};

const chunkText = (text, size = 200) => {
    // تقسیم متن بلند به تکه‌های کوچک‌تر (مرورگرها برای تکه‌های خیلی بزرگ اذیت می‌شن)
    const parts = [];
    let t = text.replace(/\s+/g, ' ').trim();
    while (t.length) {
        let cut = t.slice(0, size);
        // سعی کن روی فاصله یا نقطه ببُری
        const lastSpace = cut.lastIndexOf(' ');
        if (lastSpace > size * 0.6) cut = cut.slice(0, lastSpace);
        parts.push(cut);
        t = t.slice(cut.length).trim();
    }
    return parts;
};

const speak = (text) => {
    stopSpeak(); // هر چیزی هست متوقف کن
    const faVoice = pickFaVoice();
    const parts = chunkText(text, 220);

    const playPart = (i) => {
        if (i >= parts.length) {
            isSpeaking.value = false;
            currentUtter = null;
            return;
        }
        const u = new SpeechSynthesisUtterance(parts[i]);
        if (faVoice) u.voice = faVoice;
        u.lang = faVoice?.lang || 'fa-IR';  // مهم برای جهت/تلفظ
        u.rate = 1;    // سرعت (0.1 تا 10) — قابل تنظیم
        u.pitch = 1;   // زیروبمی (0 تا 2)
        u.volume = 1;  // بلندی (0 تا 1)

        u.onend = () => playPart(i + 1);
        u.onerror = () => playPart(i + 1);

        currentUtter = u;
        isSpeaking.value = true;
        synth.speak(u);
    };

    playPart(0);
};

const stopSpeak = () => {
    if (synth.speaking || synth.pending) synth.cancel();
    isSpeaking.value = false;
    currentUtter = null;
};

</script>

<style scoped>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.cg-app {
    --cg-brand: #0f766e;
    --cg-brand-2: #0ea5e9;
    --cg-brand-muted: rgba(15, 118, 110, 0.12);
    --cg-ink: #0f172a;
    --cg-ink-muted: #64748b;
    --cg-surface-0: #ffffff;
    --cg-surface-1: #f8fafc;
    --cg-surface-2: #f1f5f9;
    --cg-surface-elevated: #ffffff;
    --cg-border: rgba(148, 163, 184, 0.22);
    --cg-shadow-soft: 0 8px 32px rgba(15, 23, 42, 0.06);
    --cg-shadow-modal: 0 24px 64px rgba(15, 23, 42, 0.16);
    --cg-header-h: 56px;
    --cg-header-total: calc(var(--cg-header-h) + env(safe-area-inset-top, 0px));
    --cg-thread-max: 768px;
    font-family: 'Vazirmatn', 'Inter', system-ui, sans-serif;
    color: var(--cg-ink);
    background: var(--cg-surface-1);
    height: 100vh;
    height: 100dvh;
    min-height: -webkit-fill-available;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: fixed;
    inset: 0;
    width: 100%;
    max-width: 100vw;
}

.cg-body {
    flex: 1;
    display: flex;
    min-height: 0;
    position: relative;
}

.cg-drawer-backdrop {
    position: fixed;
    inset: 0;
    z-index: 35;
    background: rgba(15, 23, 42, 0.35);
    backdrop-filter: blur(2px);
}

.cg-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;
    min-height: 0;
    background: var(--cg-surface-0);
}

.cg-thread-scroll {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    scroll-behavior: smooth;
}

.cg-thread {
    width: 100%;
    max-width: var(--cg-thread-max);
    margin-inline: auto;
    padding: 24px 16px 120px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.cg-msg {
    display: flex;
    width: 100%;
}

.cg-msg--user {
    justify-content: flex-end;
}

.cg-msg--bot {
    justify-content: flex-start;
}

.cg-msg-inner {
    max-width: min(70%, 640px);
    min-width: 0;
}

.cg-msg-bubble {
    border-radius: 16px;
    padding: 14px 16px;
    border: 1px solid transparent;
    box-shadow: var(--cg-shadow-soft);
    animation: cg-msg-in 0.22s ease;
}

@keyframes cg-msg-in {
    from {
        opacity: 0;
        transform: translateY(6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.cg-msg--user .cg-msg-bubble {
    background: linear-gradient(135deg, rgba(15, 118, 110, 0.12), rgba(14, 165, 233, 0.1));
    border-color: rgba(15, 118, 110, 0.18);
    color: var(--cg-ink);
}

.cg-msg--bot .cg-msg-bubble {
    background: var(--cg-surface-2);
    border-color: var(--cg-border);
}

.cg-msg-bubble--typing {
    padding-block: 12px;
}

.cg-msg-sending {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.875rem;
    color: var(--cg-ink-muted);
}

.cg-msg-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--cg-brand);
    animation: cg-pulse 1s ease infinite;
}

@keyframes cg-pulse {
    50% {
        opacity: 0.4;
    }
}

.cg-msg-transcript {
    font-size: 0.95rem;
    line-height: 1.65;
}

.cg-voice-placeholder {
    font-size: 0.875rem;
    color: var(--cg-brand);
    cursor: pointer;
    margin-top: 8px;
}

.cg-voice-wrap audio {
    width: 100%;
    margin-top: 10px;
    max-height: 40px;
}

.cg-msg-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 10px;
    padding-top: 8px;
    border-top: 1px solid rgba(148, 163, 184, 0.15);
}

.cg-msg-time {
    font-size: 0.72rem;
    color: var(--cg-ink-muted);
}

.cg-msg-actions {
    display: flex;
    gap: 4px;
}

.cg-icon-btn {
    width: 32px;
    height: 32px;
    border: 0;
    border-radius: 8px;
    background: transparent;
    color: var(--cg-ink-muted);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.12s ease, color 0.12s ease;
}

.cg-icon-btn:hover {
    background: rgba(255, 255, 255, 0.65);
    color: var(--cg-brand);
}

.cg-icon-btn:focus-visible {
    outline: 2px solid var(--cg-brand);
    outline-offset: 2px;
}

.cg-icon {
    width: 16px;
    height: 16px;
    fill: currentColor;
}

.cg-scroll-to-bottom {
    position: fixed;
    bottom: calc(112px + env(safe-area-inset-bottom, 0px));
    inset-inline-end: max(20px, env(safe-area-inset-inline-end));
    z-index: 15;
    width: 44px;
    height: 44px;
    border-radius: 12px;
    border: 1px solid var(--cg-border);
    background: var(--cg-surface-elevated);
    box-shadow: var(--cg-shadow-soft);
    color: var(--cg-brand);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.12s ease, box-shadow 0.15s ease;
}

.cg-scroll-to-bottom:hover {
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.1);
}

.cg-scroll-to-bottom:active {
    transform: scale(0.96);
}

.cg-empty {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 32px;
    text-align: center;
    cursor: pointer;
    color: var(--cg-ink-muted);
}

.cg-empty h2 {
    font-size: 1.15rem;
    color: var(--cg-ink);
}

.cg-empty p {
    max-width: 320px;
    font-size: 0.9rem;
    line-height: 1.6;
}

.cg-modal-eyebrow {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--cg-ink-muted);
    margin-bottom: 6px;
}

.cg-muted {
    color: var(--cg-ink-muted);
    font-size: 0.875rem;
    line-height: 1.55;
    margin-bottom: 12px;
}

.cg-referral-toolbar {
    margin-bottom: 16px;
}

.cg-referral-placeholder {
    text-align: center;
    padding: 32px 16px;
    color: var(--cg-ink-muted);
}

.cg-referral-placeholder--error {
    color: #b91c1c;
}

.cg-spinner {
    width: 32px;
    height: 32px;
    margin: 0 auto 12px;
    border: 3px solid var(--cg-border);
    border-top-color: var(--cg-brand);
    border-radius: 50%;
    animation: cg-spin 0.8s linear infinite;
}

@keyframes cg-spin {
    to {
        transform: rotate(360deg);
    }
}

.cg-referral-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.cg-referral-card {
    border: 1px solid var(--cg-border);
    border-radius: 14px;
    padding: 14px;
    background: var(--cg-surface-1);
}

.cg-referral-card-head {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 12px;
}

.cg-referral-status {
    font-size: 0.75rem;
    padding: 4px 8px;
    border-radius: 8px;
    background: var(--cg-surface-2);
}

.cg-referral-block {
    margin-top: 10px;
}

.cg-referral-block--response {
    border-top: 1px solid var(--cg-border);
    padding-top: 10px;
}

.cg-referral-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--cg-ink-muted);
    margin-bottom: 4px;
}

.cg-referral-text {
    font-size: 0.9rem;
    line-height: 1.55;
}

.cg-referral-foot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 8px;
    font-size: 0.75rem;
    color: var(--cg-ink-muted);
}

.cg-link-btn {
    border: 0;
    background: none;
    color: var(--cg-brand);
    font: inherit;
    font-weight: 600;
    cursor: pointer;
    text-decoration: underline;
    text-underline-offset: 2px;
}

.cg-referral-files {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 8px;
}

.cg-file-chip {
    padding: 6px 10px;
    border-radius: 8px;
    background: var(--cg-surface-0);
    border: 1px solid var(--cg-border);
    font-size: 0.8rem;
    color: var(--cg-brand);
    text-decoration: none;
    max-width: 100%;
}

.truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: inline-block;
    max-width: 220px;
}

.cg-input {
    width: 100%;
    padding: 12px 14px;
    border-radius: 12px;
    border: 1px solid var(--cg-border);
    font: inherit;
    margin-bottom: 16px;
}

.cg-input:focus {
    outline: none;
    border-color: var(--cg-brand);
    box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.12);
}

.cg-modal-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: flex-end;
}

.cg-btn-primary,
.cg-btn-secondary,
.cg-btn-danger {
    padding: 10px 18px;
    border-radius: 10px;
    font: inherit;
    font-weight: 600;
    cursor: pointer;
    border: 0;
    transition: opacity 0.15s ease, transform 0.12s ease;
}

.cg-btn-primary {
    background: linear-gradient(135deg, var(--cg-brand), var(--cg-brand-2));
    color: #fff;
}

.cg-btn-secondary {
    background: var(--cg-surface-2);
    color: var(--cg-ink);
    border: 1px solid var(--cg-border);
}

.cg-btn-danger {
    background: #dc2626;
    color: #fff;
}

.cg-btn-block {
    width: 100%;
    justify-content: center;
}

.cg-actions-stack {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.cg-actions-row {
    width: 100%;
    text-align: inherit;
    padding: 14px 16px;
    border: 0;
    border-radius: 12px;
    background: var(--cg-surface-1);
    font: inherit;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.12s ease;
}

.cg-actions-row:hover {
    background: var(--cg-surface-2);
}

.cg-actions-row--danger {
    color: #b91c1c;
}

.cg-settings {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.cg-settings-block {
    padding-bottom: 16px;
    border-bottom: 1px solid var(--cg-border);
}

.cg-settings-block:last-child {
    border-bottom: 0;
}

.cg-settings-heading {
    font-size: 0.95rem;
    margin-bottom: 10px;
    color: var(--cg-ink);
}

.cg-label {
    display: block;
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 6px;
    color: var(--cg-ink-muted);
}

.cg-select {
    width: 100%;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid var(--cg-border);
    font: inherit;
    background: var(--cg-surface-0);
}

:deep(.cg-app) {
    --cg-surface-elevated: #fff;
}

:deep([class^='cg-modal']) {
    --cg-surface-elevated: #fff;
    --cg-ink: #0f172a;
    --cg-border: rgba(148, 163, 184, 0.22);
    --cg-brand: #0f766e;
    --cg-brand-2: #0ea5e9;
}

.cg-msg.cg-msg--highlight .cg-msg-bubble {
    box-shadow: 0 0 0 2px var(--cg-brand), var(--cg-shadow-soft);
    transition: box-shadow 0.2s ease;
}

@media (max-width: 768px) {
    .cg-thread {
        padding-inline: 12px;
        padding-bottom: 100px;
    }

    .cg-msg-inner {
        max-width: 92%;
    }

    .cg-scroll-to-bottom {
        bottom: calc(100px + env(safe-area-inset-bottom, 0px));
    }
}
</style>
