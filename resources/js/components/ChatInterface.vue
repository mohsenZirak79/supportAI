<template>

    <HandoffModal
        :is-open="isHandoffModalOpen"
        :roles="availableRoles"
        @close="isHandoffModalOpen = false"
        @submit="handleHandoffSubmit"
    />

    <div class="chat-app" dir="rtl">
        <!-- نوار بالا -->
        <header class="chat-header">
            <div class="header-content">
                <div class="header-left">
                    <button
                        v-if="isMobile"
                        class="mobile-sidebar-toggle"
                        type="button"
                        @click="toggleSidebar"
                        aria-label="باز کردن لیست گفتگوها"
                    >
                        ☰
                    </button>
                    <div class="chat-logo">
                        <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0,60 Q25,50 50,60 T100,60 L100,100 L0,100 Z" fill="rgba(255,255,255,0.3)"/>
                            <path d="M0,70 Q25,60 50,70 T100,70 L100,100 L0,100 Z" fill="rgba(255,255,255,0.2)"/>
                            <path d="M30,50 Q40,40 50,50 Q60,40 70,50 L70,100 L30,100 Z" fill="rgba(255,255,255,0.4)"/>
                            <circle cx="50" cy="35" r="12" fill="white" opacity="0.9"/>
                            <path d="M42,35 Q50,30 58,35 Q50,40 42,35" fill="white" opacity="0.9"/>
                        </svg>
                    </div>
                    <h1>{{ activeChat?.title || 'چت با هوش مصنوعی' }}</h1>
                </div>
                <div class="header-actions">
                    <!-- Language Selector -->
                    <select v-model="selectedLanguage" class="lang-selector" @change="onLanguageChange">
                        <option value="fa">فارسی</option>
                        <option value="en">English</option>
                        <option value="ar">العربية</option>
                    </select>
                    <button
                        class="nav-btn ghost"
                        type="button"
                        :disabled="!activeChatId"
                        @click="toggleReferralPanel"
                    >
                        <span class="nav-btn__dot" v-if="hasPublicReferralResponses"></span>
                        ارجاع‌ها
                    </button>
                    <button @click="goToTickets" class="nav-btn" type="button">تیکت‌ها</button>
                    <button
                        class="nav-btn danger"
                        type="button"
                        @click="logout"
                        :disabled="loggingOut"
                    >
                        {{ loggingOut ? 'در حال خروج…' : 'خروج' }}
                    </button>
                </div>
            </div>
        </header>

        <div class="chat-container">
            <!-- سایدبار چت‌ها -->
            <aside class="sidebar" :class="{ 'is-mobile': isMobile, 'is-open': isSidebarOpen }">
                <div class="new-chat-btn" @click="startNewChat">
                    + چت جدید
                </div>
                <div class="chat-list">
                    <div
                        v-for="chat in chats"
                        :key="chat.id"
                        class="chat-item"
                        :class="{ active: chat.id === activeChatId }"
                        @click="setActiveChat(chat.id)"
                    >
                        <span class="chat-item__title">{{ chat.title }}</span>
                        <button
                            class="chat-menu-btn"
                            type="button"
                            aria-label="تنظیمات چت"
                            @click.stop="toggleChatMenu(chat.id)"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true" class="chat-menu-icon">
                                <circle cx="12" cy="5" r="1.5" />
                                <circle cx="12" cy="12" r="1.5" />
                                <circle cx="12" cy="19" r="1.5" />
                            </svg>
                        </button>
                        <div v-if="chatMenuOpenId === chat.id" class="chat-menu">
                            <button type="button" @click.stop="openRenameModal(chat)">تغییر عنوان</button>
                            <button
                                type="button"
                                class="danger"
                                :disabled="deletingChatId === chat.id"
                                @click.stop="deleteChat(chat.id)"
                            >
                                حذف چت
                            </button>
                        </div>
                    </div>
                </div>
            </aside>
            <div
                v-if="isMobile && isSidebarOpen"
                class="sidebar-overlay"
                @click="closeSidebar"
            ></div>

            <!-- ناحیه چت اصلی -->
            <main class="chat-main" v-if="activeChatId">
                <div class="messages-container" ref="messagesContainer">
                    <div
                        v-for="(message, index) in activeChat?.messages || []"
                        :key="message.id || message._tmpKey || Math.random()"
                        class="message"
                        :class="{ 'user-message': message.sender === 'user', 'bot-message': message.sender === 'bot' }"
                        :data-msg-id="message.id || ''"
                    >
                        <div class="message-bubble" @click="onBubbleClick(message)">

                            <!-- بات: متن + دکمه‌های پخش -->
                            <template v-if="message.sender === 'bot' && message.text">
                                <AiAnswer :text="message.text" :lang="selectedLanguage"/>
                            </template>

                            <!-- کاربر: اگر متن دارد همان را، وگرنه اگر voice است یک برچسب نشان بده -->
                            <template v-else>
                                <span v-if="message.text && message.text.trim()">{{ message.text }}</span>
                                <span v-else-if="message.voiceUrl">🎤 پیام صوتی</span>
                                <span v-else>‌</span>
                            </template>

                            <!-- پخش صدا (همان قبلی) -->
                            <div v-if="message.voiceUrl" class="voice-player" @click.stop="playVoice(message.id)">
                                <audio :ref="el => registerAudioRef(message.id, el)" :src="message.voiceUrl"
                                       preload="none" controls></audio>
                            </div>

                            <div class="message-meta">
                                <span class="timestamp">{{ formatDate(message.created_at) }}</span>
                                <div class="msg-actions">
                                    <button
                                        class="msg-action copy"
                                        @click="copyText(message.text)"
                                        aria-label="کپی متن"
                                        title="کپی متن"
                                    >
                                        <!-- آیکن کپی -->
                                        <svg viewBox="0 0 24 24" class="icon">
                                            <path
                                                d="M16 1H4c-1.1 0-2 .9-2 2v12h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/>
                                        </svg>
                                    </button>

                                    <button
                                        class="msg-action handoff"
                                        @click="showHandoffModal(message)"
                                        aria-label="ارجاع به پشتیبانی"
                                        title="ارجاع به پشتیبانی"
                                    >
                                        <!-- آیکن ارجاع/ارسال -->
                                        <svg viewBox="0 0 24 24" class="icon">
                                            <path d="M4 12v8h16v-8h2v10H2V12h2zm8-9 6 6h-4v6h-4V9H6l6-6z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="loading" class="message bot-message">
                        <div class="message-bubble loading">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </div>
                <button
                    v-if="showScrollButton"
                    class="scroll-bottom-btn"
                    type="button"
                    aria-label="رفتن به آخرین پیام"
                    @click="scrollToBottom"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 16.5a1 1 0 0 1-.7-.29l-6-6a1 1 0 0 1 1.4-1.42L12 14.09l5.3-5.3a1 1 0 1 1 1.4 1.42l-6 6a1 1 0 0 1-.7.29Z"/>
                    </svg>
                </button>


                <!-- فرم ارسال پیام -->
                <form @submit.prevent="sendMessage" class="input-form">
                    <!-- حالت ضبط صدا -->
                    <div v-if="isRecording" class="recording-ui">
                        <div class="waveform">
                            <div v-for="n in 20" :key="n" class="bar" :style="{ height: getBarHeight(n) + 'px' }"></div>
                        </div>
                        <div class="recording-controls">
                            <button type="button" @click="cancelRecording" class="cancel-btn">✕</button>
                            <button type="button" @click="sendRecording" class="send-btn">✓</button>
                        </div>
                        <div class="recording-timer">{{ formatTimer(recordingTime) }}</div>
                    </div>

                    <!-- حالت متنی -->
                    <div v-else class="text-input-area">
                    <textarea
                        ref="msgInput"
                        v-model="inputMessage"
                        class="chat-input"
                        placeholder="پیام خود را بنویسید…"
                        rows="1"
                        @input="autoGrow"
                        @keydown="onKeydown"
                    />
                        <div class="input-actions">
                            <button type="button" @click="startRecording" class="mic-btn" :disabled="loading">🎤</button>
                            <button type="submit" class="btn btn-primary" :disabled="loading">ارسال</button>
                        </div>
                    </div>
                </form>
                <!--          <textarea-->
                <!--              v-model="inputMessage"-->
                <!--              placeholder="پیام خود را بنویسید..."-->
                <!--              rows="1"-->
                <!--              @input="autoResize"-->
                <!--              ref="textarea"-->
                <!--              :disabled="loading"-->
                <!--          ></textarea>-->
                <!--                    <button type="submit" :disabled="!inputMessage.trim() || loading">-->
                <!--                        ارسال-->
                <!--                    </button>-->
                <!--                </form>-->
            </main>

            <main v-else class="chat-main empty-state">
                <div class="empty-content">
                    <h2>چت جدیدی شروع کنید</h2>
                    <p>برای شروع گفت‌وگو، روی «چت جدید» کلیک کنید.</p>
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
                aria-label="پاسخ‌های ارجاع شده"
            >
                <div class="referral-panel__header">
                    <div>
                        <p class="referral-panel__eyebrow">ارجاعات فعال</p>
                        <h3>{{ activeChat?.title || 'چت جاری' }}</h3>
                    </div>
                    <div class="panel-actions">
                        <button
                            class="panel-icon-btn"
                            type="button"
                            :disabled="referralsLoading"
                            @click="refreshCurrentReferrals"
                            aria-label="به‌روزرسانی ارجاعات"
                        >
                            ↻
                        </button>
                        <button class="panel-icon-btn" type="button" @click="closeReferralPanel" aria-label="بستن پنل">
                            ✕
                        </button>
                    </div>
                </div>
                <div class="referral-panel__body">
                    <div v-if="referralsLoading" class="referral-panel__placeholder">
                        <div class="spinner"></div>
                        <p>در حال بارگذاری ارجاعات…</p>
                    </div>
                    <div v-else-if="referralsError" class="referral-panel__placeholder error">
                        <p>{{ referralsError }}</p>
                        <button type="button" class="panel-retry" @click="refreshCurrentReferrals">تلاش مجدد</button>
                    </div>
                    <div v-else-if="!currentReferrals.length" class="referral-panel__placeholder">
                        <p>هنوز ارجاعی برای نمایش وجود ندارد.</p>
                        <small class="text-muted">می‌توانید پیام موردنظر را به پشتیبان ارجاع دهید.</small>
                    </div>
                    <div v-else class="referral-card-list">
                        <article v-for="referral in currentReferrals" :key="referral.id" class="referral-card">
                            <div class="referral-card__header">
                                <div>
                                    <p class="referral-card__eyebrow">ارجاع به {{ referral.assigned_role || 'پشتیبان' }}</p>
                                    <h4>{{ activeChat?.title || 'چت جاری' }}</h4>
                                </div>
                                <span class="referral-status" :class="'referral-status--' + referral.status">
                                    {{ referralStatusLabel(referral.status) }}
                                </span>
                            </div>

                            <div class="referral-card__section">
                                <div class="section-title">پیام ارجاع‌شده</div>
                                <p class="section-body" v-if="referral.trigger_message?.content">
                                    {{ referral.trigger_message.content }}
                                </p>
                                <p class="section-body muted" v-else>
                                    این پیام شامل ویس یا فایل است.
                                </p>
                                <div class="section-footer">
                                    <span>{{ formatDate(referral.trigger_message?.created_at) }}</span>
                                    <button
                                        type="button"
                                        class="section-link"
                                        @click="scrollToReferredMessage(referral.trigger_message_id)"
                                    >
                                        مشاهده در گفتگو
                                    </button>
                                </div>
                            </div>

                            <div v-if="referral.description" class="referral-card__section">
                                <div class="section-title">توضیح شما</div>
                                <p class="section-body">{{ referral.description }}</p>
                            </div>

                            <div v-if="referral.response" class="referral-card__section response">
                                <div class="section-title">پاسخ پشتیبان</div>
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
                                        <span class="truncate">{{ file.name || 'فایل' }}</span>
                                    </a>
                                </div>
                            </div>
                            <div v-else class="referral-card__section muted">
                                <div class="section-title">پاسخ پشتیبان</div>
                                <p class="section-body">پاسخی برای نمایش ثبت نشده است.</p>
                            </div>
                        </article>
                    </div>
                </div>
            </section>
        </transition>

        <transition name="fade">
            <div v-if="renameModal.open" class="modal-backdrop" @click.self="closeRenameModal">
                <form class="rename-modal" @submit.prevent="submitRename">
                    <h3>تغییر عنوان گفتگو</h3>
                    <p class="modal-desc">برای مدیریت بهتر گفتگوها، عنوانی انتخاب کنید که محتوا را توصیف کند.</p>
                    <input
                        type="text"
                        ref="renameInputRef"
                        v-model="renameModal.title"
                        class="rename-input"
                        maxlength="100"
                        placeholder="عنوان جدید"
                        :disabled="renameModal.loading"
                    />
                    <div class="modal-actions">
                        <button type="button" class="modal-btn ghost" @click="closeRenameModal" :disabled="renameModal.loading">
                            انصراف
                        </button>
                        <button type="submit" class="modal-btn primary" :disabled="renameModal.loading">
                            {{ renameModal.loading ? 'در حال ذخیره…' : 'ذخیره عنوان' }}
                        </button>
                    </div>
                </form>
            </div>
        </transition>
    </div>
</template>


<script setup>
import {ref, computed, nextTick, onMounted, onUnmounted, reactive, watch} from 'vue';
import HandoffModal from './HandoffModal.vue';
import AiAnswer from './AiAnswer.vue'
import {useToast} from 'vue-toast-notification'
import {apiFetch} from '../lib/http';

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
        window.location.href = '/login';
    } catch (error) {
        console.error('logout failed', error);
        toast.error('خروج با خطا مواجه شد. لطفاً دوباره تلاش کنید.');
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
const renameModal = reactive({
    open: false,
    chatId: null,
    title: '',
    loading: false
});
const renameInputRef = ref(null);
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
const formatDate = (isoString) => {
    if (!isoString) return '';
    const date = new Date(isoString);
    return date.toLocaleString('fa-IR', {
        hour: '2-digit',
        minute: '2-digit',
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};
const referralStatusLabel = (status) => {
    switch (status) {
        case 'pending':
            return 'در انتظار';
        case 'assigned':
            return 'در حال بررسی';
        case 'responded':
            return 'پاسخ داده شد';
        case 'closed':
            return 'بسته شده';
        default:
            return status || '-';
    }
};
const getFileEmoji = (mimeOrType = '') => {
    const type = String(mimeOrType || '').toLowerCase();
    if (type.startsWith('image/')) return '🖼️';
    if (type.includes('pdf')) return '📄';
    if (type.includes('word') || type.includes('doc')) return '📝';
    if (type.includes('zip') || type.includes('rar')) return '📦';
    if (type.includes('sheet') || type.includes('excel') || type.includes('csv')) return '📊';
    if (type.startsWith('audio/')) return '🎧';
    return '📎';
};
// --- State ---
const chats = ref([]); // لیست چت‌ها از API
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
const selectedLanguage = ref('fa'); // Default to Persian

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
        state.error = 'خطا در بارگذاری ارجاع‌ها. دوباره تلاش کنید.';
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
        toast.error('عنوان نمی‌تواند خالی باشد.');
        return;
    }
    renameModal.loading = true;
    try {
        await renameChat(chatId, newTitle);
        closeRenameModal();
        toast.success('عنوان چت به‌روزرسانی شد.');
    } catch (e) {
        console.error('rename failed', e);
        toast.error('خطا در تغییر عنوان.');
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
        if (!r.ok) return;
        const {data: media} = await r.json();
        msg.media = media || [];
        const voice = msg.media.find(m => m.collection === 'message_voices' || (m.mime || '').startsWith('audio/'));
        if (voice) msg.voiceUrl = voice.url;
    } catch (e) {
        // بی‌صدا رد شو
    }
}

// فرض می‌کنیم AI User با این ایمیل ثبت شده
const AI_EMAIL = 'ai@system.local';
let aiUserId = null;

// --- Methods ---
const startRecording = async () => {
    try {
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
        alert('دسترسی به میکروفون رد شد یا پشتیبانی نمی‌شود.');
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

    try {
        // 1) آپلود فایل
        const formData = new FormData();
        formData.append('file', blob, 'recording.webm');
        formData.append('collection', 'message_voices');

        const uploadRes = await fetch('/api/v1/files', { method: 'POST', body: formData });
        if (!uploadRes.ok) throw new Error('آپلود فایل شکست خورد');
        const { file_id } = await uploadRes.json();

        // 2) ارسال پیام ویسی به گفتگو
        //    ⚠️ بک‌اند شما { user_message, ai_message, conversation } برمی‌گردونه
        const messageRes = await fetch(`/api/v1/conversations/${activeChatId.value}/messages`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ 
                content: '', 
                media_ids: [file_id], 
                media_kind: 'voice',
                lang: selectedLanguage.value
            })
        });
        if (!messageRes.ok) {
            const t = await messageRes.text().catch(() => '');
            console.error('send voice failed', messageRes.status, t);
            throw new Error('ارسال پیام شکست خورد');
        }

        const { user_message, ai_message, conversation } = await messageRes.json();

        // 3) اگر عنوان گفتگو آپدیت شده بود
        if (conversation?.title && conversation.title !== chat.title) {
            chat.title = conversation.title;
        }

        // 4) پیام کاربر (ویس) را به UI اضافه کن
        chat.messages.push({
            id: user_message.id,
            sender: 'user',
            text: user_message.content || '',
            created_at: user_message.created_at
        });

        // مدیای پیام کاربر را بگیر تا voiceUrl ست شود
        try {
            const r = await fetch(`/api/v1/messages/${user_message.id}/media`, { headers: { 'Accept': 'application/json' }});
            if (r.ok) {
                const { data: media } = await r.json();
                const voice = (media || []).find(m => m.collection === 'message_voices' || (m.mime || '').startsWith('audio/'));
                if (voice) {
                    const msg = chat.messages.find(m => m.id === user_message.id);
                    if (msg) msg.voiceUrl = voice.url;
                }
            }
        } catch (_) {}

        // 5) پیام AI را هم (متن + احتمالاً ویس) به UI اضافه کن
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
            // اگر به هر دلیلی ai_message نبود، حداقل یه پیام خطای ملایم نشون بده
            chat.messages.push({
                id: 'ai-fallback-' + Date.now(),
                sender: 'bot',
                text: 'نتوانستم پاسخ صوتی را پردازش کنم.',
                created_at: new Date().toISOString()
            });
        }

        await nextTick();
        scrollToBottom();
    } catch (error) {
        console.error('Upload voice error:', error);
        toast.error('خطا در ارسال/دریافت ویس');
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
                setActiveChat(chats.value[0].id);
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
                    type: msg.type,                // اگر خواستی نمایش بدهی
                    has_media: !!msg.has_media,    // ← از API جدید
                    has_voice: !!msg.has_voice,    // ← از API جدید
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
        alert('خطا در ایجاد چت جدید');
    }
};

// فعال‌سازی چت
const setActiveChat = async (id) => {
    closeChatMenu();
    activeChatId.value = id;
    await loadMessages(id);
    await nextTick();
    scrollToBottom();
};

// ارسال پیام
const sendMessage = async () => {
    if (!inputMessage.value.trim() || loading.value) return;

    const userMsg = {
        sender: 'user',
        text: inputMessage.value.trim()
    };

    const activeChat = chats.value.find(c => c.id === activeChatId.value);
    if (!activeChat) return;

    // اضافه کردن پیام کاربر به UI
    activeChat.messages.push(userMsg);
    inputMessage.value = '';
    await nextTick();
    scrollToBottom();
    inputMessage.value = ''
    if (msgInput.value) msgInput.value.style.height = 'auto'
    loading.value = true;
    await nextTick();
    scrollToBottom();
    try {
        const res = await apiFetch(`/conversations/${activeChatId.value}/messages`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                content: userMsg.text,
                lang: selectedLanguage.value
            })
        });

        if (res.ok) {
            const {ai_message, conversation} = await res.json();

            // آپدیت عنوان چت اگر تغییر کرده
            const chatLocal = chats.value.find(c => c.id === activeChatId.value);
            if (chatLocal) {
                if (conversation.title && conversation.title !== chatLocal.title) {
                    chatLocal.title = conversation.title;
                }

                // اضافه کردن پاسخ AI
                // activeChat.messages.push({
                //     id: ai_message.id,
                //     sender: 'bot',
                //     text: ai_message.content,
                //     created_at: ai_message.created_at,
                //     // فلگ‌های محافظه‌کارانه: بعداً اگر مدیا داشت lazy ست می‌کنیم
                //     has_media: false,
                //     has_voice: false,
                // });
                // await nextTick();
                // scrollToBottom();
                const botMsg = {
                    id: ai_message.id,
                    sender: 'bot',
                    text: ai_message.content || '',
                    created_at: ai_message.created_at,
                    has_media: false,
                    has_voice: false,
                };
                chatLocal.messages = [...chatLocal.messages, botMsg]; // ← به‌جای push

                await nextTick();
                scrollToBottom();
            }
        } else {
            throw new Error('خطا در ارسال پیام');
        }
    } catch (error) {
        activeChat.messages.push({
            sender: 'bot',
            text: '❌ خطایی رخ داد. لطفاً دوباره تلاش کنید.'
        });
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
const msgInput = ref(null)

// 2-2) رشد خودکار بدون اسکرول
function autoGrow() {
    const ta = msgInput.value
    if (!ta) return
    ta.style.height = 'auto'
    ta.style.height = Math.min(ta.scrollHeight, 220) + 'px'
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
    if (!confirm('آیا مطمئنید این گفتگو حذف شود؟')) return;

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
        toast.success('گفتگو حذف شد.');
    } catch (e) {
        console.error('delete chat failed', e);
        toast.error('حذف چت ناموفق بود.');
    } finally {
        deletingChatId.value = null;
    }
};
const copyText = (text) => {
    navigator.clipboard.writeText(text).then(() => {
        toast.info('متن کپی شد')
    });
};
const goToTickets = () => {
    window.location.href = '/ticket';
};
const showHandoffModal = (message) => {
    selectedMessageForHandoff.value = message;
    isHandoffModalOpen.value = true;
};
const handleHandoffSubmit = async (data) => {
    try {
        if (!selectedMessageForHandoff.value?.id) {
            toast.error('پیام انتخاب‌شده نامعتبر است.');
            return
        }

        const res = await apiFetch(`/messages/${selectedMessageForHandoff.value.id}/handoff`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
                body: JSON.stringify(data)
            }
        )

        if (!res.ok) {
            let msg = 'خطا در ارجاع'
            try {
                const j = await res.json();
                msg = j?.message || j?.error || msg
            } catch {
            }
            toast.error(msg);
            return
        }
        toast.success('ارجاع با موفقیت ثبت شد.');
        isHandoffModalOpen.value = false
        selectedMessageForHandoff.value = null
    } catch (e) {
        toast.error('خطا در ارجاع: ' + (e?.message || 'نامشخص'), 'error');
    }
}
const audioRefs = ref({});
let currentlyPlayingId = null;
const registerAudioRef = (id, el) => {
    if (el) audioRefs.value[id] = el;
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
    target.classList.add('message-highlight');
    if (highlightTimers.has(messageId)) {
        clearTimeout(highlightTimers.get(messageId));
    }
    const timer = setTimeout(() => {
        target.classList.remove('message-highlight');
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
    }

    if (message.voiceUrl) {
        playVoice(message.id);
    }
};

const onLanguageChange = () => {
    // Save language preference to localStorage
    localStorage.setItem('ai_language', selectedLanguage.value);
};

// --- Lifecycle ---
onMounted(() => {
    // Load language preference
    const savedLang = localStorage.getItem('ai_language');
    if (savedLang && ['fa', 'en', 'ar'].includes(savedLang)) {
        selectedLanguage.value = savedLang;
    }
    
    // Load voices for browser TTS (if used)
    if (typeof speechSynthesis !== 'undefined') {
        loadVoices();
        speechSynthesis.onvoiceschanged = loadVoices;
    }
    
    loadChats();
    fetchDepartments();
    if (typeof window !== 'undefined') {
        updateLayoutFlags();
        window.addEventListener('resize', updateLayoutFlags);
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
}
</script>

<style scoped>
/* همان استایل قبلی شما — بدون تغییر */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.chat-app {
    font-family: 'Vazirmatn', 'Segoe UI', Tahoma, sans-serif;
    background-color: #f9fafb;
    min-height: 100vh;
    height: 100vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.chat-header {
    background: linear-gradient(135deg, #0e7490 0%, #0891b2 100%);
    color: white;
    padding: 16px 24px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.chat-header h1 {
    font-size: 1.3rem;
    font-weight: 600;
    cursor: pointer;
    color: white;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.chat-logo {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.chat-logo svg {
    width: 100%;
    height: 100%;
}

.chat-container {
    display: flex;
    flex: 1;
    overflow: hidden;
    min-height: 0;
}

.sidebar {
    width: 260px;
    background-color: white;
    border-left: 1px solid #eaeaea;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    transition: transform .3s ease, box-shadow .3s ease;
}

.new-chat-btn {
    padding: 14px 20px;
    background: linear-gradient(135deg, #0e7490 0%, #0891b2 100%);
    color: white;
    font-weight: 600;
    cursor: pointer;
    text-align: center;
    margin: 12px;
    border-radius: 8px;
    transition: opacity 0.2s;
}

.new-chat-btn:hover {
    opacity: 0.9;
}

.chat-list {
    padding: 8px 0;
}

.chat-item {
    padding: 12px 20px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    color: #333;
    transition: background 0.2s;
    font-size: 0.95rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
}

.chat-item:hover {
    background-color: #f5f7ff;
}

/*.chat-item.active {
    background-color: #eef2ff;
    border-right: 3px solid #2575fc;
    font-weight: 600;
}*/
.chat-item.active {
    background-color: #eef2ff;
    border-right: 3px solid transparent;
    border-image: linear-gradient(180deg, #0e7490, #0891b2) 1;
}

.chat-item__title {
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding-inline-end: 8px;
}

.chat-menu-btn {
    width: 32px;
    height: 32px;
    border-radius: 999px;
    border: 1px solid rgba(148, 163, 184, 0.4);
    background: rgba(241, 245, 249, 0.8);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s ease, border-color 0.2s ease;
    position: relative;
    z-index: 2;
}

.chat-menu-btn:hover {
    background: rgba(226, 232, 240, 0.9);
    border-color: rgba(148, 163, 184, 0.8);
}

.chat-menu-icon {
    width: 16px;
    height: 16px;
    fill: #475569;
}

.chat-menu {
    position: absolute;
    top: 50%;
    inset-inline-end: 16px;
    transform: translateY(calc(-50% + 24px));
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.15);
    display: flex;
    flex-direction: column;
    min-width: 140px;
    z-index: 10;
    overflow: hidden;
}

.chat-menu button {
    text-align: right;
    padding: 10px 14px;
    background: none;
    border: none;
    font-size: 0.9rem;
    cursor: pointer;
    transition: background 0.15s ease;
}

.chat-menu button:hover {
    background: #f8fafc;
}

.chat-menu button.danger {
    color: #dc2626;
}

.chat-menu button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.chat-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    background-color: #ffffff;
    position: relative;
    min-height: 0;
}

.empty-state {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #666;
}

.empty-content h2 {
    font-size: 1.4rem;
    margin-bottom: 12px;
}

.empty-content p {
    color: #888;
}

.messages-container {
    flex: 1;
    padding: 24px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 16px;
    min-height: 0;
    padding-bottom: 96px;
}

.message {
    display: flex;
    justify-content: flex-end;
}

.message.user-message {
    justify-content: flex-start;
}

.message.bot-message {
    justify-content: flex-end;
}

.message-bubble {
    padding: 12px 16px;
    border-radius: 18px;
    max-width: 80%;
    word-break: break-word;
    line-height: 1.5;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    animation: fadeInUp 0.3s ease;
    transition: box-shadow .2s ease, transform .2s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}


.user-message .message-bubble {
    /*background: linear-gradient(135deg, #2575fc 0%, #6a11cb 100%);*/
    background-color: #f1f5f9;
    color: #333;
    /*color: white;*/
    border-bottom-right-radius: 4px;
}

.bot-message .message-bubble {
    background-color: #f1f5f9;
    color: #333;
    border-bottom-left-radius: 4px;
}

.loading {
    display: flex;
    align-items: center;
    gap: 4px;
    background: #f1f5f9 !important;
    color: #666;
}

.loading span {
    width: 8px;
    height: 8px;
    background-color: #94a3b8;
    border-radius: 50%;
    display: inline-block;
    animation: bounce 1.4s infinite ease-in-out both;
}

.loading span:nth-child(1) {
    animation-delay: -0.32s;
}

.loading span:nth-child(2) {
    animation-delay: -0.16s;
}

@keyframes bounce {
    0%, 80%, 100% {
        transform: scale(0);
    }
    40% {
        transform: scale(1);
    }
}

.input-form {
    display: flex;
    padding: 16px;
    background: white;
    border-top: 1px solid #eaeaea;
    gap: 12px;
    flex-direction: column;
    position: sticky;
    bottom: 0;
    z-index: 4;
    box-shadow: 0 -6px 18px rgba(15, 23, 42, 0.08);
}

.text-input-area {
    display: flex;
    gap: 12px;
    align-items: flex-end;
}

.text-input-area textarea {
    flex: 1;
    padding: 12px 16px;
    border: 1px solid #d1d5db;
    border-radius: 24px;
    resize: none;
    font-size: 1rem;
    font-family: inherit;
    outline: none;
    max-height: 150px;
}

.input-actions {
    display: flex;
    gap: 8px;
}

.input-form textarea:focus {
    border-color: #0891b2;
    box-shadow: 0 0 0 3px rgba(14, 116, 144, 0.2);
}

.input-actions button {
    padding: 12px 20px;
    border: none;
    border-radius: 24px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s;
}

.input-actions .mic-btn {
    background: #f1f5f9;
    color: #4b5563;
}

.input-actions button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.scroll-bottom-btn {
    position: absolute;
    inset-inline-end: 24px;
    bottom: 110px;
    width: 44px;
    height: 44px;
    border-radius: 999px;
    border: none;
    background: linear-gradient(135deg, #0e7490, #0891b2);
    box-shadow: 0 10px 25px rgba(14, 116, 144, 0.35);
    color: #fff;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    z-index: 5;
}

.scroll-bottom-btn svg {
    width: 22px;
    height: 22px;
    fill: currentColor;
}

.scroll-bottom-btn:hover {
    transform: translateY(1px);
    box-shadow: 0 6px 14px rgba(15, 23, 42, 0.25);
}

.scroll-bottom-btn:focus-visible {
    outline: 3px solid rgba(99, 102, 241, 0.4);
    outline-offset: 2px;
}

.mobile-sidebar-toggle {
    display: none;
    background: rgba(255, 255, 255, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.4);
    color: #fff;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 1rem;
    cursor: pointer;
}

.mobile-sidebar-toggle:focus-visible {
    outline: 2px solid rgba(255, 255, 255, 0.6);
    outline-offset: 2px;
}

.sidebar-overlay {
    display: none;
}

/* --- حالت ضبط صدا --- */
.recording-ui {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    gap: 12px;
}

.waveform {
    display: flex;
    gap: 2px;
    height: 40px;
    align-items: flex-end;
    justify-content: center;
    width: 100%;
}

/*.bar {
    width: 6px;
    background: linear-gradient(to top, #0891b2, #0e7490);
    border-radius: 3px;
    transition: height 0.1s ease;
}*/
.bar {
    background: linear-gradient(to top, #0891b2, #0e7490);
    box-shadow: 0 0 8px rgba(14, 116, 144, 0.4);
}

.recording-controls {
    display: flex;
    gap: 20px;
}

.recording-controls button {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid #e2e8f0;
    background: white;
    font-size: 1.2rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cancel-btn {
    color: #ef4444;
    border-color: #fecaca;
}

.send-btn {
    color: #10b981;
    border-color: #bbf7d0;
}

.recording-timer {
    font-size: 0.875rem;
    color: #64748b;
}

.input-form button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.message-meta {
    display: flex;
    justify-content: space-between;
    margin-top: 6px;
    font-size: 0.75rem;
    color: #666;
}

.message-actions button {
    background: none;
    border: none;
    cursor: pointer;
    opacity: 0;
    transition: opacity 0.2s;
}

.message-bubble:hover .message-actions button {
    opacity: 1;
}


/* --- حالت ضبط صدا --- */
.recording-ui {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    gap: 12px;
}

.waveform {
    display: flex;
    gap: 2px;
    height: 40px;
    align-items: flex-end;
    justify-content: center;
    width: 100%;
}

.bar {
    width: 6px;
    background: linear-gradient(to top, #0891b2, #0e7490);
    border-radius: 3px;
    transition: height 0.1s ease;
}

.recording-controls {
    display: flex;
    gap: 20px;
}

.recording-controls button {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid #e2e8f0;
    background: white;
    font-size: 1.2rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cancel-btn {
    color: #ef4444;
    border-color: #fecaca;
}

.send-btn {
    color: #10b981;
    border-color: #bbf7d0;
}

.recording-timer {
    font-size: 0.875rem;
    color: #64748b;
}

.bot-message .message-bubble::before {
    content: "🤖";
    margin-left: 6px;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 16px;
    flex-wrap: wrap;
    gap: 12px;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.nav-btn {
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
    padding: 6px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.nav-btn.ghost {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.2);
    color: #f8fafc;
    position: relative;
    padding-inline-start: 32px;
}

.nav-btn.ghost:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.nav-btn__dot {
    position: absolute;
    inset-inline-start: 12px;
    top: 50%;
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: #f97316;
    transform: translateY(-50%);
    box-shadow: 0 0 0 6px rgba(249, 115, 22, 0.25);
}

.nav-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-1px);
}

.nav-btn.danger {
    background: rgba(248, 113, 113, 0.2);
    border-color: rgba(248, 113, 113, 0.55);
    color: #fee2e2;
}

.nav-btn.danger:hover {
    background: rgba(248, 113, 113, 0.35);
}

.lang-selector {
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
    padding: 6px 12px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.2s ease;
    margin-left: 8px;
}

.lang-selector:hover {
    background: rgba(255, 255, 255, 0.3);
}

.lang-selector option {
    background: #0e7490;
    color: white;
}


.msg-actions {
    display: inline-flex;
    gap: 8px;
    opacity: 0;
    transform: translateY(2px);
    transition: opacity .18s ease, transform .18s ease;
}

/* نمایش هنگام هاور روی حباب پیام */
.message-bubble:hover .msg-actions {
    opacity: 1;
    transform: translateY(0);
}

/* دکمه‌ها: کپسولی با افکت شیشه‌ای سبک */
.msg-action {
    --bg: rgba(241, 245, 249, .9); /* slate-100/90 */
    --bd: rgba(203, 213, 225, .9); /* slate-300/90 */
    --fg: #334155; /* slate-700 */
    --hover: rgba(226, 232, 240, 1); /* slate-200 */
    --ring: rgba(99, 102, 241, .25); /* indigo ring */

    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 32px;
    border-radius: 999px;
    border: 1px solid var(--bd);
    background: var(--bg);
    color: var(--fg);
    cursor: pointer;
    transition: transform .12s ease, box-shadow .12s ease, background .12s ease, border-color .12s ease;
    box-shadow: 0 2px 6px rgba(15, 23, 42, .06);
    backdrop-filter: blur(4px);
}

.msg-action:hover {
    background: var(--hover);
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(15, 23, 42, .10);
}

.msg-action:active {
    transform: translateY(0);
    box-shadow: 0 2px 6px rgba(15, 23, 42, .06);
}

.msg-action:focus-visible {
    outline: none;
    box-shadow: 0 0 0 4px var(--ring);
}

/* آیکن‌ها */
.msg-action .icon {
    width: 18px;
    height: 18px;
    fill: currentColor;
    display: block;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity .2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-panel-enter-active,
.slide-panel-leave-active {
    transition: transform .3s ease;
}

.slide-panel-enter-from,
.slide-panel-leave-to {
    transform: translateX(110%);
}

.referral-panel-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    z-index: 60;
}

.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    z-index: 90;
}

.rename-modal {
    width: 100%;
    max-width: 420px;
    background: #fff;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 30px 60px rgba(15, 23, 42, 0.2);
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.rename-input {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 12px;
    padding: 12px 14px;
    font-size: 0.95rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.rename-input:focus {
    border-color: #0891b2;
    box-shadow: 0 0 0 3px rgba(14, 116, 144, 0.25);
    outline: none;
}

.modal-desc {
    color: #64748b;
    font-size: 0.9rem;
    line-height: 1.7;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 8px;
}

.modal-btn {
    border-radius: 999px;
    padding: 10px 20px;
    border: 1px solid transparent;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.modal-btn.primary {
    background: linear-gradient(135deg, #0e7490, #0891b2);
    color: #fff;
    box-shadow: 0 10px 20px rgba(14, 116, 144, 0.25);
}

.modal-btn.primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    box-shadow: none;
}

.modal-btn.ghost {
    background: transparent;
    border-color: #cbd5f5;
    color: #475569;
}

.modal-btn:hover:not(:disabled) {
    transform: translateY(-1px);
}

.referral-panel {
    position: fixed;
    top: 0;
    bottom: 0;
    right: 0;
    width: min(420px, 92vw);
    background: #fff;
    box-shadow: -10px 0 35px rgba(15, 23, 42, 0.25);
    border-top-left-radius: 18px;
    border-bottom-left-radius: 18px;
    z-index: 70;
    display: flex;
    flex-direction: column;
    padding: 20px;
}

.referral-panel.is-mobile {
    width: 100%;
    right: 0;
    left: 0;
    border-radius: 0;
}

.referral-panel.is-mobile.slide-panel-enter-from,
.referral-panel.is-mobile.slide-panel-leave-to {
    transform: translateY(100%);
}

.referral-panel__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 16px;
}

.referral-panel__eyebrow {
    font-size: 0.8rem;
    color: #94a3b8;
    margin-bottom: 2px;
}

.panel-actions {
    display: flex;
    gap: 8px;
}

.panel-icon-btn {
    width: 36px;
    height: 36px;
    border-radius: 999px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #475569;
    cursor: pointer;
    transition: all .2s ease;
}

.panel-icon-btn:hover:not(:disabled) {
    background: #e2e8f0;
}

.panel-icon-btn:disabled {
    opacity: .5;
    cursor: not-allowed;
}

.referral-panel__body {
    overflow-y: auto;
    flex: 1;
}

.referral-panel__placeholder {
    text-align: center;
    color: #475569;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 40px 12px;
}

.referral-panel__placeholder.error {
    color: #dc2626;
}

.spinner {
    width: 32px;
    height: 32px;
    border-radius: 999px;
    border: 3px solid #e2e8f0;
    border-top-color: #0891b2;
    animation: spin .8s linear infinite;
}

.referral-card-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.referral-card {
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 16px;
    background: #fff;
    box-shadow: 0 5px 18px rgba(15, 23, 42, 0.06);
}

.referral-card__header {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 12px;
}

.referral-card__eyebrow {
    font-size: 0.8rem;
    color: #94a3b8;
    margin-bottom: 4px;
}

.referral-status {
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    background: #e2e8f0;
    color: #475569;
}

.referral-status--pending { background: #fef3c7; color: #92400e; }
.referral-status--assigned { background: #dbeafe; color: #1d4ed8; }
.referral-status--responded { background: #dcfce7; color: #15803d; }
.referral-status--closed { background: #e2e8f0; color: #475569; }

.referral-card__section {
    border: 1px solid #f1f5f9;
    border-radius: 12px;
    padding: 12px;
    margin-bottom: 10px;
    background: #f8fafc;
}

.referral-card__section.response {
    border-color: #c7d2fe;
    background: #eef2ff;
}

.referral-card__section.muted {
    background: #fef2f2;
    border-color: #fecaca;
}

.section-title {
    font-size: 0.85rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 6px;
}

.section-body {
    color: #0f172a;
    line-height: 1.6;
    white-space: pre-wrap;
}

.section-body.muted {
    color: #94a3b8;
}

.section-footer {
    margin-top: 8px;
    display: flex;
    justify-content: space-between;
    font-size: 0.78rem;
    color: #94a3b8;
    flex-wrap: wrap;
    gap: 8px;
}

.section-link {
    background: none;
    border: none;
    color: #2563eb;
    cursor: pointer;
    font-weight: 600;
}

.referral-files {
    margin-top: 10px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.file-chip {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    max-width: 220px;
    padding: .35rem .6rem;
    border-radius: 9999px;
    font-size: .78rem;
    line-height: 1;
    background: #f1f5f9;
    color: #334155;
    border: 1px solid #e2e8f0;
}

.file-chip-link {
    background: #eef2ff;
    color: #4338ca;
    border-color: #c7d2fe;
}

.file-chip .truncate {
    max-width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.panel-retry {
    border: 1px solid #e2e8f0;
    border-radius: 999px;
    padding: 6px 18px;
    background: #fff;
    color: #1d4ed8;
    cursor: pointer;
}

.message.message-highlight .message-bubble {
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
    transform: translateY(-2px);
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* واریانت‌ها (در صورت نیاز به تفاوت رنگی) */
.msg-action.copy {
    --fg: #0f172a; /* تیره‌تر برای کپی */
}

.msg-action.handoff {
    --fg: #4338ca; /* ایندیگو */
    --bd: rgba(165, 180, 252, .7);
    --bg: rgba(238, 242, 255, .85);
    --hover: rgba(224, 231, 255, 1);
}

.chat-input {
    width: 100%;
    min-height: 56px; /* اندازه اولیه */
    max-height: 220px; /* محدودیت رشد */
    overflow: hidden; /* بدون اسکرول عمودی */
    resize: none; /* کاربر نتواند دستی تغییر دهد */
    line-height: 1.6;
    border-radius: 14px;
    padding: 12px 14px;
}

/* ——— تبلت و پایین‌تر (تا ۱۰۲۴) ——— */
@media (max-width: 1024px) {
    .chat-container {
        flex-direction: column;
    }

    /* فقط وقتی موبایل نیست (تبلت افقی و…)
       سایدبار بالای صفحه قرار بگیرد */
    .sidebar:not(.is-mobile) {
        width: 100%;
        max-height: 240px;
        border-left: none;
        border-bottom: 1px solid #eaeaea;
        flex-direction: row;
    }

    .chat-list {
        flex: 1;
        max-height: 200px;
        overflow-y: auto;
    }

    .messages-container {
        padding: 20px;
    }
}

/* ——— موبایل (زیر ۷۶۸) drawer از راست ——— */
@media (max-width: 768px) {
    .chat-header .header-content {
        flex-wrap: wrap;
        gap: 8px;
    }

    .mobile-sidebar-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .chat-container {
        position: relative;
        overflow: visible;
        flex-direction: column;
    }

    /* سایدبار به صورت drawer ثابت از راست */
    .sidebar.is-mobile {
        position: fixed;
        top: 0;
        bottom: 0;
        left: auto;
        right: 0;
        width: min(85vw, 320px);
        transform: translateX(110%);
        border: none;
        box-shadow: -8px 0 24px rgba(15, 23, 42, 0.25);
        z-index: 40;
        background: #fff;
        max-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .sidebar.is-mobile.is-open {
        transform: translateX(0);
    }

    .sidebar-overlay {
        display: block;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        z-index: 30;
    }

    .messages-container {
        padding: 16px 12px;
    }

    .message-bubble {
        max-width: 100%;
    }

    .input-form {
        padding: 12px;
    }

    .text-input-area {
        flex-direction: column;
        align-items: stretch;
    }

    .input-actions {
        width: 100%;
        justify-content: space-between;
    }

    .input-actions button {
        flex: 1;
    }

    .nav-btn {
        width: 100%;
        text-align: center;
    }

    .sidebar.is-mobile .chat-item {
        flex-direction: row;
        align-items: center;
        gap: 6px;
    }

    .sidebar.is-mobile .delete-btn {
        opacity: 1;
        margin-right: 0;
        margin-left: 8px;
    }

    .scroll-bottom-btn {
        inset-inline-end: 12px;
        bottom: 95px;
    }
}


</style>
