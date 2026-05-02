<template>

    <HandoffModal
        :is-open="isHandoffModalOpen"
        :roles="availableRoles"
        @close="isHandoffModalOpen = false"
        @submit="handleHandoffSubmit"
    />

    <div class="cg-root chat-app" :dir="direction">
        <div class="cg-corner-notif">
            <NotificationBell @select="handleNotificationSelect" />
        </div>
        <div class="cg-body chat-container">
            <aside
                class="cg-sidebar sidebar"
                :class="{ 'is-mobile': isMobile, 'is-open': isSidebarOpen }"
            >
                <div class="cg-sidebar__brand" role="banner">
                    <div class="cg-sidebar__brand-logo" aria-hidden="true">
                        <img
                            v-if="!sidebarBrandLogoFailed"
                            src="/images/logo.png"
                            alt=""
                            class="cg-sidebar__brand-img"
                            @error="sidebarBrandLogoFailed = true"
                        />
                        <svg
                            v-else
                            class="cg-sidebar__brand-logo-fallback"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.75"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M4 5.5A3.5 3.5 0 0 1 7.5 2h9A3.5 3.5 0 0 1 20 5.5v6A3.5 3.5 0 0 1 16.5 15H11l-4.25 3.45A1 1 0 0 1 5 17.67V15.9A3.5 3.5 0 0 1 4 13V5.5Z" />
                        </svg>
                    </div>
                    <div class="cg-sidebar__brand-text">
                        <strong class="cg-sidebar__brand-name">{{ $t('chat.sidebarBrandName') }}</strong>
                        <span class="cg-sidebar__brand-sub">{{ $t('chat.sidebarBrandSubtitle') }}</span>
                    </div>
                </div>
                <div class="cg-sidebar__search">
                    <input
                        v-model="chatSearchQuery"
                        type="search"
                        autocomplete="off"
                        :placeholder="$t('chat.searchChats')"
                    />
                </div>
                <button type="button" class="cg-new-chat new-chat-btn" @click="startNewChat">
                    {{ $t('chat.newChat') }}
                </button>
                <div class="cg-chat-list chat-list">
                    <div
                        v-for="chat in filteredChats"
                        :key="chat.id"
                        class="cg-chat-item chat-item"
                        :class="{ 'is-active': chat.id === activeChatId, active: chat.id === activeChatId }"
                        @click="setActiveChat(chat.id)"
                    >
                        <span class="cg-chat-item__title chat-item__title">{{ chat.title }}</span>
                        <button
                            type="button"
                            class="cg-chat-menu-btn chat-menu-btn"
                            :aria-label="$t('chat.chatSettings')"
                            @click.stop="toggleChatMenu(chat.id)"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true" class="chat-menu-icon">
                                <circle cx="12" cy="5" r="1.5" />
                                <circle cx="12" cy="12" r="1.5" />
                                <circle cx="12" cy="19" r="1.5" />
                            </svg>
                        </button>
                        <div v-if="chatMenuOpenId === chat.id" class="cg-chat-menu chat-menu">
                            <button type="button" @click.stop="openRenameModal(chat)">{{ $t('chat.renameChat') }}</button>
                            <button type="button" @click.stop="openReferralPanelForChat(chat)">
                                {{ $t('nav.referrals') }}
                            </button>
                            <button
                                type="button"
                                class="danger"
                                :disabled="deletingChatId === chat.id"
                                @click.stop="deleteChat(chat.id)"
                            >
                                {{ $t('chat.deleteChat') }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="cg-sidebar-footer sidebar-footer">
                    <button
                        type="button"
                        class="cg-user-trigger sidebar-user-trigger"
                        :aria-expanded="userMenuOpen"
                        aria-haspopup="true"
                        @click="userMenuOpen = !userMenuOpen"
                    >
                        <img
                            v-if="currentUser.avatar"
                            :src="currentUser.avatar"
                            :alt="currentUser.name"
                            class="cg-user-avatar sidebar-user-avatar"
                        />
                        <div v-else class="cg-user-avatar cg-user-avatar--ph sidebar-user-avatar sidebar-user-avatar--placeholder">
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </div>
                        <span class="cg-user-name sidebar-user-name">{{ currentUser.name || $t('nav.profile') }}</span>
                        <span
                            v-if="hasPublicReferralResponses && !userMenuOpen"
                            class="cg-dot sidebar-footer__dot sidebar-footer__dot--on-trigger"
                        />
                    </button>
                    <div v-if="userMenuOpen" ref="userMenuRef" class="cg-user-menu sidebar-user-menu">
                        <button
                            type="button"
                            class="sidebar-user-menu__item"
                            :disabled="!activeChatId"
                            @click="userMenuOpen = false; toggleReferralPanel()"
                        >
                            <span v-if="hasPublicReferralResponses" class="sidebar-footer__dot" />
                            {{ $t('nav.referrals') }}
                        </button>
                        <button type="button" class="sidebar-user-menu__item" @click="userMenuOpen = false; goToTickets()">
                            {{ $t('nav.tickets') }}
                        </button>
                        <button type="button" class="sidebar-user-menu__item" @click="userMenuOpen = false; goToProfile()">
                            {{ $t('nav.profile') }}
                        </button>
                        <button
                            type="button"
                            class="sidebar-user-menu__item sidebar-user-menu__item--danger danger"
                            :disabled="loggingOut"
                            @click="userMenuOpen = false; logout()"
                        >
                            {{ loggingOut ? '...' : $t('nav.logout') }}
                        </button>
                    </div>
                </div>
            </aside>

            <div
                v-if="isMobile && isSidebarOpen"
                class="cg-sidebar-overlay sidebar-overlay"
                role="presentation"
                @click="closeSidebar"
            />

            <main v-if="activeChatId" class="cg-main chat-main">
                <div v-if="isMobile" class="cg-mobile-chat-top">
                    <button
                        type="button"
                        class="cg-icon-btn mobile-menu-btn"
                        :aria-label="$t('chat.openSidebar')"
                        @click="toggleSidebar"
                    >
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <line x1="3" y1="12" x2="21" y2="12"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                    <span class="cg-mobile-chat-top__title">{{ activeChat?.title || $t('chat.title') }}</span>
                </div>
                <div ref="messagesContainer" class="cg-msg-scroll messages-container">
                    <div class="cg-msg-feed">
                        <MessageBubble
                            v-for="(message, index) in activeChat?.messages || []"
                            :key="message.id || message._tmpKey || `msg-${index}`"
                            :message="message"
                            :locale="locale"
                            :user-voice-gender="userVoiceGender"
                            :format-date="formatDate"
                            :register-audio-ref="registerAudioRef"
                            :on-bubble-click="onBubbleClick"
                            @copy="copyText"
                            @handoff="showHandoffModal"
                            @play-voice="playVoice"
                            @bubble-click="onBubbleClick"
                        />
                        <TypingIndicator v-if="loading" />
                    </div>
                </div>
                <button
                    v-if="showScrollButton"
                    type="button"
                    class="cg-scroll-down scroll-bottom-btn"
                    :aria-label="$t('chat.scrollToBottom')"
                    @click="scrollToBottom"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 16.5a1 1 0 0 1-.7-.29l-6-6a1 1 0 0 1 1.4-1.42L12 14.09l5.3-5.3a1 1 0 1 1 1.4 1.42l-6 6a1 1 0 0 1-.7.29Z"/>
                    </svg>
                </button>

                <form class="cg-composer input-form" @submit.prevent="sendMessage">
                    <div class="cg-composer-inner">
                        <div class="cg-composer-box">
                            <div v-if="isRecording" class="cg-recording recording-ui">
                                <div class="cg-waveform waveform">
                                    <div
                                        v-for="n in 20"
                                        :key="n"
                                        class="bar"
                                        :style="{ height: getBarHeight(n) + 'px' }"
                                    />
                                </div>
                                <div class="cg-rec-controls recording-controls">
                                    <button type="button" class="cancel-btn" :aria-label="$t('common.cancel')" @click="cancelRecording">✕</button>
                                    <button type="button" class="send-btn" :aria-label="$t('chat.send')" @click="sendRecording">✓</button>
                                </div>
                                <div class="cg-rec-timer recording-timer">{{ formatTimer(recordingTime) }}</div>
                            </div>
                            <div v-else class="cg-input-row text-input-area">
                                <textarea
                                    ref="msgInput"
                                    v-model="inputMessage"
                                    class="cg-textarea chat-input"
                                    rows="1"
                                    :placeholder="$t('chat.inputPlaceholder')"
                                    @input="autoGrow"
                                    @keydown="onKeydown"
                                />
                                <div class="cg-input-actions input-actions">
                                    <button
                                        type="button"
                                        class="cg-round-btn mic-btn"
                                        :disabled="loading"
                                        :aria-label="$t('chat.recordVoice')"
                                        :title="$t('chat.recordVoice')"
                                        @click="startRecording"
                                    >
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/>
                                            <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                                            <line x1="12" y1="19" x2="12" y2="23"/>
                                            <line x1="8" y1="23" x2="16" y2="23"/>
                                        </svg>
                                    </button>
                                    <button
                                        type="submit"
                                        class="cg-round-btn cg-round-btn--primary send-btn"
                                        :disabled="loading || !inputMessage.trim()"
                                        :aria-label="$t('chat.send')"
                                        :title="$t('chat.send')"
                                    >
                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </main>

            <main v-else class="cg-main cg-main--empty cg-main--landing chat-main empty-state">
                <div v-if="isMobile" class="cg-mobile-chat-top cg-mobile-chat-top--on-dark">
                    <button
                        type="button"
                        class="cg-icon-btn mobile-menu-btn cg-icon-btn--on-dark"
                        :aria-label="$t('chat.openSidebar')"
                        @click.stop="toggleSidebar"
                    >
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <line x1="3" y1="12" x2="21" y2="12"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                    <span class="cg-mobile-chat-top__title">{{ $t('chat.title') }}</span>
                </div>
                <div class="cg-landing">
                    <div class="cg-landing__body">
                        <h1 class="cg-landing__title">{{ $t('chat.emptyHeroTitle') }}</h1>
                        <form class="cg-landing-form" @submit.prevent="sendMessage">
                            <div class="cg-landing-pill">
                                <button
                                    type="button"
                                    class="cg-landing-pill__plus"
                                    :aria-label="$t('chat.landingAttachAria')"
                                    :title="$t('chat.landingAttachAria')"
                                    @click.stop
                                >
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M12 5v14M5 12h14"/>
                                    </svg>
                                </button>
                                <textarea
                                    ref="emptyMsgInput"
                                    v-model="inputMessage"
                                    class="cg-landing-pill__input"
                                    rows="1"
                                    :placeholder="$t('chat.emptyHeroPlaceholder')"
                                    @input="autoGrow"
                                    @keydown="onKeydown"
                                />
                                <div class="cg-landing-pill__trailing">
                                    <span class="cg-landing-pill__mode" aria-hidden="true">{{ $t('chat.emptyHeroMode') }}</span>
                                    <button
                                        type="button"
                                        class="cg-landing-pill__icon-btn"
                                        :disabled="loading"
                                        :aria-label="$t('chat.recordVoice')"
                                        :title="$t('chat.recordVoice')"
                                        @click="startRecordingWithActiveChat"
                                    >
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/>
                                            <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                                            <line x1="12" y1="19" x2="12" y2="23"/>
                                            <line x1="8" y1="23" x2="16" y2="23"/>
                                        </svg>
                                    </button>
                                    <button
                                        type="submit"
                                        class="cg-landing-pill__icon-btn cg-landing-pill__icon-btn--send"
                                        :disabled="loading || !inputMessage.trim()"
                                        :aria-label="$t('chat.send')"
                                        :title="$t('chat.send')"
                                    >
                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </form>
                        <button type="button" class="cg-landing-chip" @click="applyLandingChipPrompt">
                            {{ $t('chat.emptyHeroChip') }}
                        </button>
                    </div>
                    <p class="cg-landing__disclaimer">{{ $t('chat.emptyHeroDisclaimer') }}</p>
                </div>
            </main>
        </div>
        <transition name="fade">
            <div
                v-if="referralPanelOpen"
                class="referral-panel-backdrop"
                @click="closeReferralPanel"
            ></div>
        </transition>

        <transition name="slide-panel">
            <section
                v-if="referralPanelOpen"
                class="referral-panel"
                :class="{ 'is-mobile': isMobile }"
                :aria-label="$t('referral.title')"
            >
                <div class="referral-panel__header">
                    <div>
                        <p class="referral-panel__eyebrow">{{ $t('referral.title') }}</p>
                        <h3>{{ activeChat?.title || $t('referral.currentChat') }}</h3>
                    </div>
                    <div class="panel-actions">
                        <button
                            class="panel-icon-btn"
                            type="button"
                            :disabled="referralsLoading"
                            @click="refreshCurrentReferrals"
                            :aria-label="$t('referral.refresh')"
                        >
                            ↻
                        </button>
                        <button class="panel-icon-btn" type="button" @click="closeReferralPanel" :aria-label="$t('referral.closePanel')">
                            ✕
                        </button>
                    </div>
                </div>
                <div class="referral-panel__body">
                    <div v-if="referralsLoading" class="referral-panel__placeholder">
                        <div class="spinner"></div>
                        <p>{{ $t('referral.loading') }}</p>
                    </div>
                    <div v-else-if="referralsError" class="referral-panel__placeholder error">
                        <p>{{ referralsError }}</p>
                        <button type="button" class="panel-retry" @click="refreshCurrentReferrals">{{ $t('common.retry') }}</button>
                    </div>
                    <div v-else-if="!currentReferrals.length" class="referral-panel__placeholder">
                        <p>{{ $t('referral.noReferrals') }}</p>
                        <small class="text-muted">{{ $t('referral.noReferralsHint') }}</small>
                    </div>
                    <div v-else class="referral-card-list">
                        <article v-for="referral in currentReferrals" :key="referral.id" class="referral-card">
                            <div class="referral-card__header">
                                <div>
                                    <p class="referral-card__eyebrow">{{ $t('referral.referTo') }} {{ referral.assigned_role || $t('referral.support') }}</p>
                                    <h4>{{ activeChat?.title || $t('referral.currentChat') }}</h4>
                                </div>
                                <span class="referral-status" :class="'referral-status--' + referral.status">
                                    {{ referralStatusLabel(referral.status) }}
                                </span>
                            </div>

                            <div class="referral-card__section">
                                <div class="section-title">{{ $t('referral.referredMessage') }}</div>
                                <p class="section-body" v-if="referral.trigger_message?.content">
                                    {{ referral.trigger_message.content }}
                                </p>
                                <p class="section-body muted" v-else>
                                    {{ $t('referral.messageVoiceOrFile') }}
                                </p>
                                <div class="section-footer">
                                    <span>{{ formatDate(referral.trigger_message?.created_at) }}</span>
                                    <button
                                        type="button"
                                        class="section-link"
                                        @click="scrollToReferredMessage(referral.trigger_message_id)"
                                    >
                                        {{ $t('referral.viewInChat') }}
                                    </button>
                                </div>
                            </div>

                            <div v-if="referral.description" class="referral-card__section">
                                <div class="section-title">{{ $t('referral.yourNote') }}</div>
                                <p class="section-body">{{ referral.description }}</p>
                            </div>

                            <div v-if="referral.response" class="referral-card__section response">
                                <div class="section-title">{{ $t('referral.supportResponse') }}</div>
                                <p class="section-body">{{ referral.response.text }}</p>
                                <div class="section-footer">
                                    <span>{{ formatDate(referral.response.created_at) }}</span>
                                </div>
                                <div v-if="referral.response.files?.length" class="referral-files">
                                    <a
                                        v-for="file in referral.response.files"
                                        :key="file.id"
                                        :href="file.url"
                                        target="_blank"
                                        rel="noopener"
                                        class="file-chip file-chip-link"
                                    >
                                        <span>{{ getFileEmoji(file.mime) }}</span>
                                        <span class="truncate">{{ file.name || $t('common.file') }}</span>
                                    </a>
                                </div>
                            </div>
                            <div v-else class="referral-card__section muted">
                                <div class="section-title">{{ $t('referral.supportResponse') }}</div>
                                <p class="section-body">{{ $t('referral.noResponse') }}</p>
                            </div>
                        </article>
                    </div>
                </div>
            </section>
        </transition>

        <transition name="fade">
            <div v-if="renameModal.open" class="modal-backdrop" @click.self="closeRenameModal">
                <form class="rename-modal" @submit.prevent="submitRename">
                    <h3>{{ $t('chat.renameChatTitle') }}</h3>
                    <p class="modal-desc">{{ $t('chat.renameChatDesc') }}</p>
                    <input
                        type="text"
                        ref="renameInputRef"
                        v-model="renameModal.title"
                        class="rename-input"
                        maxlength="100"
                        :placeholder="$t('chat.newTitlePlaceholder')"
                        :disabled="renameModal.loading"
                    />
                    <div class="modal-actions">
                        <button type="button" class="modal-btn ghost" @click="closeRenameModal" :disabled="renameModal.loading">
                            {{ $t('common.cancel') }}
                        </button>
                        <button type="submit" class="modal-btn primary" :disabled="renameModal.loading">
                            {{ renameModal.loading ? $t('chat.savingTitle') : $t('chat.saveTitle') }}
                        </button>
                    </div>
                </form>
            </div>
        </transition>
    </div>
</template>


<script setup>
import {ref, computed, nextTick, onMounted, onUnmounted, reactive, watch} from 'vue';
import './chat-app/chat-layout.css';
import './chat-app/chat-overlays.css';
import HandoffModal from './HandoffModal.vue';
import NotificationBell from './NotificationBell.vue';
import MessageBubble from './chat-app/MessageBubble.vue';
import TypingIndicator from './chat-app/TypingIndicator.vue';
import {useToast} from 'vue-toast-notification'
import {apiFetch} from '../lib/http';
import { collectPageContextForAi } from '../lib/collectPageContextForAi';
import { aiUserSafeReply } from '../lib/aiUserSafeReply';
import { useLanguage } from '../i18n';

// i18n setup - CSP-safe, no vue-i18n
const { locale, direction, isRtl, initLocale, t } = useLanguage();

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
const chatMenuOpenId = ref(null);
const deletingChatId = ref(null);
const currentUser = ref({ name: '', avatar: null });
const userMenuOpen = ref(false);
const userMenuRef = ref(null);
const renameModal = reactive({
    open: false,
    chatId: null,
    title: '',
    loading: false
});
const renameInputRef = ref(null);
const sidebarBrandLogoFailed = ref(false);
const WELCOME_STORAGE_KEY = 'supportAI:welcome-session';
const ACTIVE_CHAT_STORAGE_KEY = 'supportAI:active-conversation-id';
const FLOATING_IMPORT_STORAGE_KEY = 'supportAI:floating-import-v1';
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
const chatSearchQuery = ref('');
const activeChatId = ref(null);
const inputMessage = ref('');
const loading = ref(false);
const textarea = ref(null);
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
    closeChatMenu();
    if (activeChatId.value !== chat.id) {
        await setActiveChat(chat.id);
    }
    referralPanelOpen.value = true;
    await loadReferrals(chat.id);
};

const handleReferralEsc = (event) => {
    if (event.key === 'Escape') {
        closeReferralPanel();
    }
};

const handleRenameEsc = (event) => {
    if (event.key === 'Escape' && renameModal.open) {
        closeRenameModal();
    }
};

const toggleChatMenu = (chatId) => {
    chatMenuOpenId.value = chatMenuOpenId.value === chatId ? null : chatId;
};

const closeChatMenu = () => {
    chatMenuOpenId.value = null;
};

const openRenameModal = (chat) => {
    closeChatMenu();
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

let previousBodyOverflow = '';
watch(referralPanelOpen, (open) => {
    if (typeof document === 'undefined') return;
    if (open) {
        previousBodyOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        document.addEventListener('keydown', handleReferralEsc);
    } else {
        document.body.style.overflow = previousBodyOverflow || '';
        document.removeEventListener('keydown', handleReferralEsc);
    }
});

watch(() => renameModal.open, (open) => {
    if (typeof document === 'undefined') return;
    if (open) {
        document.addEventListener('keydown', handleRenameEsc);
    } else {
        document.removeEventListener('keydown', handleRenameEsc);
    }
});

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
                lang: locale.value,
                page_context: collectPageContextForAi({ source: 'chat-full-voice' }),
            })
        });
        if (!messageRes.ok) {
            try {
                await messageRes.text();
            } catch (_) {}
            console.error('send voice failed', messageRes.status);
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

        if (ai_message) {
            chat.messages.push({
                id: ai_message.id,
                sender: 'bot',
                text: aiUserSafeReply(ai_message.content, t),
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
                text: t('chat.aiServiceUnavailable'),
                created_at: new Date().toISOString()
            });
        }

        await nextTick();
        scrollToBottom();
    } catch (error) {
        console.error('Upload voice error');

        // حذف پیام موقت در صورت خطا
        const tempMsgIndex = chat.messages.findIndex(m => m.id === tempMsgId);
        if (tempMsgIndex !== -1) {
            chat.messages.splice(tempMsgIndex, 1);
        }
        URL.revokeObjectURL(tempVoiceUrl);

        toast.error(t('chat.aiServiceUnavailable'));
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
        document.removeEventListener('keydown', handleReferralEsc);
        document.removeEventListener('keydown', handleRenameEsc);
        document.removeEventListener('click', handleMenuClickOutside);
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

const filteredChats = computed(() => {
    const q = chatSearchQuery.value.trim().toLowerCase();
    if (!q) return chats.value;
    return chats.value.filter((c) => String(c.title || '').toLowerCase().includes(q));
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
const autoResize = () => {
    const el = textarea.value;
    if (el) {
        el.style.height = 'auto';
        el.style.height = Math.min(el.scrollHeight, 150) + 'px';
    }
};

// لود چت‌ها از API
/** @param {{ skipAutoSelect?: boolean }} [opts] اگر true، چتی را خودکار فعال نکن (مثلاً قبل از import ویجت شناور) */
const loadChats = async (opts = {}) => {
    const skipAutoSelect = !!opts.skipAutoSelect;
    try {
        const res = await apiFetch('/conversations');
        if (res.ok) {
            const {data} = await res.json();
            chats.value = data.map(chat => ({
                id: chat.id,
                title: chat.title,
                messages: []
            }));
            if (!skipAutoSelect && chats.value.length > 0 && !activeChatId.value) {
                const preferred = getPreferredConversationId();
                const matched = preferred ? chats.value.find((chat) => String(chat.id) === String(preferred)) : null;
                setActiveChat(matched?.id || chats.value[0].id);
            }
        }
    } catch (e) {
        console.error('Failed to load chats', e);
    }
};

/** گفتگوی ویجت شناور (مهمان) پس از ورود به چت کامل وارد دیتابیس می‌شود */
/** @returns {Promise<boolean>} true if a conversation was imported and activated */
const tryImportFloatingTranscript = async () => {
    if (typeof window === 'undefined' || typeof sessionStorage === 'undefined') {
        return false;
    }
    const raw = sessionStorage.getItem(FLOATING_IMPORT_STORAGE_KEY);
    if (!raw) {
        return false;
    }
    let bundle;
    try {
        bundle = JSON.parse(raw);
    } catch {
        sessionStorage.removeItem(FLOATING_IMPORT_STORAGE_KEY);
        return false;
    }
    const rows = bundle?.messages;
    if (!Array.isArray(rows) || rows.length === 0) {
        sessionStorage.removeItem(FLOATING_IMPORT_STORAGE_KEY);
        return false;
    }
    sessionStorage.removeItem(FLOATING_IMPORT_STORAGE_KEY);
    try {
        const res = await apiFetch('/conversations/import-floating', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                title: typeof bundle.title === 'string' ? bundle.title : 'چت جدید',
                messages: rows,
            }),
        });
        if (!res.ok) {
            toast.error(t('chat.importFloatingError'));
            return false;
        }
        const data = await res.json();
        const conv = data?.conversation;
        if (!conv?.id) {
            toast.error(t('chat.importFloatingError'));
            return false;
        }
        await loadChats();
        await setActiveChat(conv.id);
        toast.success(t('chat.importFloatingSuccess'));
        if (typeof window.history?.replaceState === 'function') {
            const url = new URL(window.location.href);
            url.searchParams.delete('conversation');
            window.history.replaceState({}, '', url.pathname + url.search);
        }
        return true;
    } catch (e) {
        console.error('import floating transcript', e);
        toast.error(t('chat.importFloatingError'));
        return false;
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
    closeChatMenu();
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

const ensureChatBeforeSend = async () => {
    if (activeChatId.value) return true;
    await startNewChat();
    await nextTick();
    return !!activeChatId.value;
};

const startRecordingWithActiveChat = async () => {
    if (loading.value) return;
    const ok = await ensureChatBeforeSend();
    if (!ok) return;
    await nextTick();
    await startRecording();
};

const applyLandingChipPrompt = () => {
    inputMessage.value = t('chat.emptyHeroChipPrompt');
    nextTick(() => {
        autoGrow();
        emptyMsgInput.value?.focus?.();
    });
};

// ارسال پیام
const sendMessage = async () => {
    if (!inputMessage.value.trim() || loading.value) return;
    if (!(await ensureChatBeforeSend())) return;

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
    inputMessage.value = '';
    if (msgInput.value) msgInput.value.style.height = 'auto';
    if (emptyMsgInput.value) emptyMsgInput.value.style.height = 'auto';
    loading.value = true;
    await nextTick();
    scrollToBottom();
    try {
        const res = await apiFetch(`/conversations/${activeChatId.value}/messages`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                content: userMsg.text,
                lang: locale.value,
                page_context: collectPageContextForAi({ source: 'chat-full' }),
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

            const botMsg = {
                id: ai_message?.id ?? 'ai-fallback-' + Date.now(),
                sender: 'bot',
                text:
                    ai_message != null
                        ? aiUserSafeReply(ai_message.content, t)
                        : t('chat.aiServiceUnavailable'),
                created_at: ai_message?.created_at ?? new Date().toISOString(),
                has_media: false,
                has_voice: false,
            };
            chatLocal.messages = [...chatLocal.messages, botMsg];

            await nextTick();
            scrollToBottom();
        } else {
            try {
                await res.text();
            } catch (_) {}
            throw new Error('send failed');
        }
    } catch {
        const chatLocal = chats.value.find(c => c.id === activeChatId.value);
        if (chatLocal) {
            chatLocal.messages.push({
                sender: 'bot',
                text: t('chat.aiServiceUnavailable'),
            });
        }
        toast.error(t('chat.aiServiceUnavailable'));
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
const msgInput = ref(null);
const emptyMsgInput = ref(null);

// 2-2) رشد خودکار بدون اسکرول
function autoGrow() {
    const ta = msgInput.value || emptyMsgInput.value;
    if (!ta) return;
    ta.style.height = 'auto';
    ta.style.height = Math.min(ta.scrollHeight, 220) + 'px';
}

// 2-3) Enter = ارسال / Shift+Enter = خط جدید
function onKeydown(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault()
        sendMessage()
    }
}

// حذف چت
const deleteChat = async (chatId) => {
    closeChatMenu();
    const chat = chats.value.find(c => c.id === chatId);
    if (!chat) return;
    if (!confirm(t('chat.confirmDelete'))) return;

    deletingChatId.value = chatId;
    try {
        const res = await apiFetch(`/conversations/${chatId}`, {method: 'DELETE'});
        if (!res.ok) throw new Error('delete failed');

        const index = chats.value.findIndex(c => c.id === chatId);
        chats.value = chats.value.filter(c => c.id !== chatId);

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
    target.classList.add('cg-msg-highlight');
    if (highlightTimers.has(messageId)) {
        clearTimeout(highlightTimers.get(messageId));
    }
    const timer = setTimeout(() => {
        target.classList.remove('cg-msg-highlight');
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

// --- Lifecycle ---
onMounted(async () => {
    // Initialize i18n and apply direction to document
    initLocale();

    // Load voices for browser TTS (if used)
    if (typeof speechSynthesis !== 'undefined') {
        loadVoices();
        speechSynthesis.onvoiceschanged = loadVoices;
    }

    let newFromFloating = false;
    /** شناسهٔ گفتگوی ویجت از query؛ بعد از replaceState از URL حذف می‌شود */
    let floatingTargetConversationId = null;
    if (typeof window !== 'undefined' && typeof window.history?.replaceState === 'function') {
        const url = new URL(window.location.href);
        if (url.searchParams.get('newFromFloating') === '1') {
            newFromFloating = true;
            const convParam = url.searchParams.get('conversation');
            if (convParam) {
                floatingTargetConversationId = Number(convParam) || convParam;
            }
            url.searchParams.delete('newFromFloating');
            url.searchParams.delete('conversation');
            const qs = url.searchParams.toString();
            window.history.replaceState({}, '', url.pathname + (qs ? `?${qs}` : '') + url.hash);
        }
    }

    await loadChats({ skipAutoSelect: newFromFloating });

    const imported = await tryImportFloatingTranscript();
    if (newFromFloating && !imported) {
        if (floatingTargetConversationId) {
            const found = chats.value.find((chat) => String(chat.id) === String(floatingTargetConversationId));
            if (found) {
                await setActiveChat(found.id);
            } else {
                await startNewChat();
            }
        } else {
            await startNewChat();
        }
    }
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
    if (typeof document !== 'undefined') {
        document.addEventListener('click', handleMenuClickOutside);
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

function handleMenuClickOutside(event) {
    const target = event.target;
    if (!target) return;
    const inMenu = typeof target.closest === 'function' ? target.closest('.chat-menu') : null;
    const inButton = typeof target.closest === 'function' ? target.closest('.chat-menu-btn') : null;
    if (!inMenu && !inButton) {
        closeChatMenu();
    }
    const inUserTrigger = typeof target.closest === 'function' ? target.closest('.sidebar-user-trigger') : null;
    const inUserMenu = typeof target.closest === 'function' ? target.closest('.sidebar-user-menu') : null;
    if (!inUserTrigger && !inUserMenu) {
        userMenuOpen.value = false;
    }
}
</script>
