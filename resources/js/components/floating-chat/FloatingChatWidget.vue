<template>
    <div
        v-if="config.enableFloatingChatWidget"
        class="floating-chat-root"
        :class="{ 'is-open': isOpen, 'has-proactive-nudge': proactiveNudgeVisible }"
    >
        <div class="floating-chat-stack">
            <transition name="nudge-pop">
                <div
                    v-if="proactiveNudgeVisible"
                    class="floating-help-nudge"
                    role="dialog"
                    aria-live="polite"
                    :aria-label="t('floating.proactiveAria')"
                >
                    <button type="button" class="floating-help-nudge__close" :aria-label="t('floating.proactiveDismiss')" @click="dismissProactiveNudge">
                        ×
                    </button>
                    <p class="floating-help-nudge__title">{{ t('floating.proactiveTitle') }}</p>
                    <p class="floating-help-nudge__body">{{ t('floating.proactiveBody') }}</p>
                    <button type="button" class="floating-help-nudge__cta" @click="openFromProactiveNudge">
                        {{ t('floating.proactiveOpen') }}
                    </button>
                </div>
            </transition>
            <transition name="widget-pop">
                <FloatingChatPanel
                    v-if="isOpen"
                    ref="panelRef"
                    :messages="messages"
                    :loading="loading"
                    :draft="draft"
                    :assistant-name="config.assistantName"
                    :show-open-full-chat="!isGuest"
                    :show-phone-callback-option="!isGuest"
                    :conversation-id="activeConversationId"
                    :chat-locked="conversationLocked"
                    :is-recording="isRecording"
                    :recording-time="recordingTime"
                    @update:draft="draft = $event"
                    @send-text="sendText"
                    @close="closeWidget"
                    @open-full-chat="openFullChat"
                    @new-chat="startNewWidgetChat"
                    @start-recording="startRecording"
                    @cancel-recording="cancelRecording"
                    @send-recording="sendRecording"
                    @phone-callback-request="onPhoneCallbackRequest"
                />
            </transition>

            <div class="floating-chat-launcher-anchor">
                <FloatingChatLauncher
                    :aria-label="isOpen ? 'بستن چت شناور' : 'باز کردن چت شناور'"
                    @toggle="toggleWidget"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { apiFetch } from '../../lib/http';
import { collectPageContextForAi } from '../../lib/collectPageContextForAi';
import { floatingChatWidgetConfig } from '../../config/floatingChatWidget';
import { useLanguage } from '../../i18n';
import FloatingChatLauncher from './FloatingChatLauncher.vue';
import FloatingChatPanel from './FloatingChatPanel.vue';

const ACTIVE_CHAT_STORAGE_KEY = 'supportAI:active-conversation-id';
const FLOATING_WIDGET_CONVERSATION_KEY = 'supportAI:floating-widget-conversation-id';
const GUEST_FLOATING_SESSION_KEY = 'supportAI:floating-guest-thread-v1';

const { t, initLocale } = useLanguage();

const config = floatingChatWidgetConfig;
const isOpen = ref(false);
const draft = ref('');
const loading = ref(false);
const messages = ref([]);
const activeConversationId = ref(null);
const panelRef = ref(null);

const isGuest = ref(false);
const authChecked = ref(false);
/** پس از ثبت درخواست تماس برای همین گفتگو، ارسال پیام در ویجت بسته می‌شود تا «چت جدید». */
const conversationLocked = ref(false);

const isRecording = ref(false);
const recordingTime = ref(0);
const recordingInterval = ref(null);
const mediaRecorder = ref(null);
const audioChunks = ref([]);

const proactiveNudgeVisible = ref(false);
let proactiveAutoHideTimer = null;

const OFFER_HELP_EVENT = 'supportai:offer-help';

function playSupportAiChime() {
    try {
        const Ctx = window.AudioContext || window.webkitAudioContext;
        if (!Ctx) return;
        const ctx = new Ctx();
        const gain = ctx.createGain();
        gain.connect(ctx.destination);
        const playTone = (freq, t0, dur) => {
            const osc = ctx.createOscillator();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(freq, t0);
            osc.connect(gain);
            osc.start(t0);
            osc.stop(t0 + dur);
        };
        const now = ctx.currentTime;
        gain.gain.setValueAtTime(0.0001, now);
        gain.gain.exponentialRampToValueAtTime(0.11, now + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.38);
        playTone(880, now, 0.1);
        playTone(1174, now + 0.1, 0.12);
        ctx.resume?.().catch(() => {});
        window.setTimeout(() => {
            try {
                ctx.close();
            } catch {
                /* ignore */
            }
        }, 500);
    } catch {
        /* ignore */
    }
    try {
        if (typeof navigator !== 'undefined' && navigator.vibrate) {
            navigator.vibrate([18, 40, 22]);
        }
    } catch {
        /* ignore */
    }
}

function dismissProactiveNudge() {
    proactiveNudgeVisible.value = false;
    if (proactiveAutoHideTimer) {
        window.clearTimeout(proactiveAutoHideTimer);
        proactiveAutoHideTimer = null;
    }
}


const getStorage = () => {
    if (typeof window === 'undefined') return null;
    try {
        return window.sessionStorage;
    } catch {
        return null;
    }
};

/** فقط گفتگوی ویجت؛ بدون localStorage اصلی اپ و بدون sync با صفحهٔ چت کامل */
const readWidgetStoredConversationId = () => {
    if (typeof window === 'undefined' || isGuest.value) return null;
    const raw = getStorage()?.getItem(FLOATING_WIDGET_CONVERSATION_KEY);
    if (!raw) return null;
    return Number(raw) || raw;
};

const persistWidgetConversationId = (id) => {
    if (typeof window === 'undefined' || !id || isGuest.value) return;
    try {
        getStorage()?.setItem(FLOATING_WIDGET_CONVERSATION_KEY, String(id));
    } catch {
        /* ignore */
    }
};

const checkAuth = async () => {
    if (authChecked.value) return;
    try {
        const res = await apiFetch('/conversations');
        authChecked.value = true;
        isGuest.value = !res.ok;
        if (isGuest.value) {
            try {
                window.localStorage?.removeItem(ACTIVE_CHAT_STORAGE_KEY);
            } catch {
                /* ignore */
            }
            activeConversationId.value = null;
        }
    } catch {
        authChecked.value = true;
        isGuest.value = true;
        try {
            window.localStorage?.removeItem(ACTIVE_CHAT_STORAGE_KEY);
        } catch {
            /* ignore */
        }
        activeConversationId.value = null;
    }
};

const loadGuestThreadFromStorage = () => {
    if (!isGuest.value) return;
    const raw = getStorage()?.getItem(GUEST_FLOATING_SESSION_KEY);
    if (!raw) return;
    try {
        const data = JSON.parse(raw);
        if (Array.isArray(data.messages) && data.messages.length) {
            messages.value = data.messages;
        }
    } catch {
        /* ignore */
    }
};

const persistGuestThread = () => {
    if (!isGuest.value || typeof window === 'undefined') return;
    try {
        window.sessionStorage.setItem(
            GUEST_FLOATING_SESSION_KEY,
            JSON.stringify({
                title: 'چت جدید',
                messages: messages.value.map((m) => ({
                    id: m.id,
                    sender: m.sender,
                    text: m.text || '',
                    created_at: m.created_at,
                })),
            })
        );
    } catch {
        /* ignore */
    }
};

const refreshConversationLock = async () => {
    if (isGuest.value || !activeConversationId.value) {
        conversationLocked.value = false;
        return;
    }
    try {
        const res = await apiFetch(`/conversations/${activeConversationId.value}/widget-callback-lock`);
        if (!res.ok) {
            conversationLocked.value = false;
            return;
        }
        const data = await res.json();
        conversationLocked.value = Boolean(data.locked);
    } catch {
        conversationLocked.value = false;
    }
};

const ensureConversation = async () => {
    if (isGuest.value) {
        throw new Error('guest has no server conversation');
    }
    if (activeConversationId.value) return activeConversationId.value;

    const storedId = readWidgetStoredConversationId();
    if (storedId) {
        activeConversationId.value = storedId;
        return storedId;
    }

    const createRes = await apiFetch('/conversations', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title: 'چت جدید' }),
    });
    if (!createRes.ok) {
        throw new Error('Unable to create conversation');
    }
    const newConversation = await createRes.json();
    activeConversationId.value = newConversation.id;
    persistWidgetConversationId(newConversation.id);
    return newConversation.id;
};

const appendUserMessage = (text) => {
    const msg = {
        id: `widget-u-${Date.now()}`,
        sender: 'user',
        text,
        created_at: new Date().toISOString(),
    };
    messages.value.push(msg);
    return msg;
};

const appendBotMessage = (payload) => {
    const serverId = payload?.id ?? null;
    messages.value.push({
        id: serverId ?? `widget-ai-${Date.now()}`,
        aiMessageId: serverId,
        sender: 'bot',
        text: payload?.content || 'پاسخی دریافت نشد.',
        created_at: payload?.created_at ?? new Date().toISOString(),
        callbackRegistered: false,
    });
};

const onPhoneCallbackRequest = async ({ aiMessageId, consent }) => {
    if (!aiMessageId || !consent || conversationLocked.value) return;
    let conversationId = activeConversationId.value;
    if (!conversationId) {
        try {
            conversationId = await ensureConversation();
        } catch {
            window.toast?.error?.('ابتدا یک پیام ارسال کنید.');
            return;
        }
    }
    const res = await apiFetch('/widget-callback-requests', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            conversation_id: String(conversationId),
            trigger_message_id: String(aiMessageId),
            consent: true,
        }),
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
        window.toast?.error?.(typeof data.message === 'string' ? data.message : 'ثبت درخواست ممکن نشد.');
        return;
    }
    const idx = messages.value.findIndex((m) => m.sender === 'bot' && m.aiMessageId === aiMessageId);
    if (idx >= 0) {
        const prev = messages.value[idx];
        messages.value.splice(idx, 1, { ...prev, callbackRegistered: true });
    }
    window.toast?.success?.(typeof data.message === 'string' ? data.message : 'درخواست تماس ثبت شد.');
    conversationLocked.value = true;
};

const startNewWidgetChat = async () => {
    if (isGuest.value || loading.value) return;
    loading.value = true;
    try {
        try {
            getStorage()?.removeItem(FLOATING_WIDGET_CONVERSATION_KEY);
        } catch {
            /* ignore */
        }
        activeConversationId.value = null;
        messages.value = [];
        draft.value = '';
        conversationLocked.value = false;

        const createRes = await apiFetch('/conversations', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title: 'چت جدید' }),
        });
        if (!createRes.ok) throw new Error('create failed');
        const newConversation = await createRes.json();
        activeConversationId.value = newConversation.id;
        persistWidgetConversationId(newConversation.id);
        await refreshConversationLock();
        window.toast?.success?.(t('floating.newChatStarted'));
    } catch {
        window.toast?.error?.(t('floating.newChatFailed'));
    } finally {
        loading.value = false;
    }
};

const sendText = async () => {
    const text = draft.value.trim();
    if (!text || loading.value) return;

    if (!isGuest.value && conversationLocked.value) {
        window.toast?.error?.(t('floating.chatLockedHint'));
        return;
    }

    appendUserMessage(text);
    draft.value = '';

    if (isGuest.value) {
        loading.value = true;
        try {
            const res = await fetch('/api/v1/guest-floating-chat', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    question: text,
                    lang: document.documentElement.lang || 'fa',
                    page_context: collectPageContextForAi({ source: 'floating-widget' }),
                }),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                appendBotMessage({
                    content:
                        (typeof data.message === 'string' && data.message) ||
                        'موقتاً پاسخ در دسترس نیست. بعداً دوباره تلاش کنید.',
                });
            } else {
                appendBotMessage({ content: data.answer || 'پاسخی دریافت نشد.' });
            }
        } catch {
            appendBotMessage({ content: 'خطا در ارسال پیام. لطفا دوباره تلاش کنید.' });
        } finally {
            loading.value = false;
        }
        return;
    }

    loading.value = true;

    try {
        const conversationId = await ensureConversation();
        const res = await apiFetch(`/conversations/${conversationId}/messages`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                content: text,
                lang: document.documentElement.lang || 'fa',
                page_context: collectPageContextForAi({ source: 'floating-widget' }),
            }),
        });
        const data = await res.json().catch(() => ({}));
        if (res.status === 423) {
            conversationLocked.value = true;
            const last = messages.value[messages.value.length - 1];
            if (last && last.sender === 'user') {
                messages.value.pop();
            }
            draft.value = text;
            window.toast?.error?.(typeof data.message === 'string' ? data.message : t('floating.chatLockedHint'));
            return;
        }
        if (!res.ok) throw new Error('send failed');
        if (data?.conversation?.id) {
            activeConversationId.value = data.conversation.id;
            persistWidgetConversationId(data.conversation.id);
        }
        appendBotMessage(data?.ai_message);
    } catch {
        appendBotMessage({ content: 'خطا در ارسال پیام. لطفا دوباره تلاش کنید.' });
    } finally {
        loading.value = false;
    }
};

const formatMicrophoneError = (error) => {
    if (!error) return 'خطا در دسترسی به میکروفون.';
    if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') return 'مجوز میکروفون مسدود است.';
    if (error.name === 'NotFoundError') return 'میکروفون یافت نشد.';
    return 'ضبط صدا در این مرورگر قابل استفاده نیست.';
};

const startRecording = async () => {
    if (isGuest.value) {
        appendBotMessage({ content: t('floating.guestVoiceDisabled') });
        return;
    }
    if (conversationLocked.value) {
        window.toast?.error?.(t('floating.chatLockedHint'));
        return;
    }
    if (loading.value || isRecording.value) return;
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        mediaRecorder.value = new MediaRecorder(stream);
        audioChunks.value = [];
        mediaRecorder.value.ondataavailable = (event) => {
            audioChunks.value.push(event.data);
        };
        mediaRecorder.value.onstop = async () => {
            const audioBlob = new Blob(audioChunks.value, { type: 'audio/webm' });
            await uploadVoice(audioBlob);
        };
        mediaRecorder.value.start();
        isRecording.value = true;
        recordingTime.value = 0;
        recordingInterval.value = window.setInterval(() => {
            recordingTime.value += 100;
            if (recordingTime.value >= 600000) sendRecording();
        }, 100);
    } catch (error) {
        appendBotMessage({ content: formatMicrophoneError(error) });
    }
};

const cleanupRecording = () => {
    if (recordingInterval.value) {
        window.clearInterval(recordingInterval.value);
        recordingInterval.value = null;
    }
    isRecording.value = false;
    recordingTime.value = 0;
};

const cancelRecording = () => {
    if (mediaRecorder.value && mediaRecorder.value.state !== 'inactive') {
        mediaRecorder.value.stop();
        mediaRecorder.value.stream?.getTracks().forEach((track) => track.stop());
    }
    cleanupRecording();
};

const sendRecording = () => {
    if (mediaRecorder.value && mediaRecorder.value.state !== 'inactive') {
        mediaRecorder.value.stop();
    }
    cleanupRecording();
};

const uploadVoice = async (blob) => {
    if (conversationLocked.value) {
        window.toast?.error?.(t('floating.chatLockedHint'));
        cleanupRecording();
        return;
    }
    loading.value = true;
    const tempVoiceUrl = URL.createObjectURL(blob);
    messages.value.push({
        id: `widget-voice-${Date.now()}`,
        sender: 'user',
        text: 'پیام صوتی',
        voiceUrl: tempVoiceUrl,
        created_at: new Date().toISOString(),
    });

    try {
        const conversationId = await ensureConversation();

        const formData = new FormData();
        formData.append('file', blob, 'recording.webm');
        formData.append('collection', 'message_voices');
        const uploadRes = await fetch('/api/v1/files', { method: 'POST', body: formData, credentials: 'include' });
        if (!uploadRes.ok) throw new Error('upload failed');
        const { file_id } = await uploadRes.json();

        const messageRes = await apiFetch(`/conversations/${conversationId}/messages`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                content: '',
                media_ids: [file_id],
                media_kind: 'voice',
                lang: document.documentElement.lang || 'fa',
                page_context: collectPageContextForAi({ source: 'floating-widget-voice' }),
            }),
        });
        const data = await messageRes.json().catch(() => ({}));
        if (messageRes.status === 423) {
            conversationLocked.value = true;
            messages.value.pop();
            window.toast?.error?.(typeof data.message === 'string' ? data.message : t('floating.chatLockedHint'));
            return;
        }
        if (!messageRes.ok) throw new Error('voice send failed');
        if (data?.conversation?.id) {
            activeConversationId.value = data.conversation.id;
            persistWidgetConversationId(data.conversation.id);
        }
        appendBotMessage(data?.ai_message);
    } catch {
        appendBotMessage({ content: 'خطا در ارسال پیام صوتی.' });
    } finally {
        loading.value = false;
        URL.revokeObjectURL(tempVoiceUrl);
    }
};

const openWidget = async () => {
    if (!authChecked.value) {
        await checkAuth();
    }
    isOpen.value = true;
    if (!isGuest.value) {
        try {
            await ensureConversation();
            await refreshConversationLock();
        } catch {
            /* ignore */
        }
    } else if (messages.value.length === 0) {
        loadGuestThreadFromStorage();
    }
    await nextTick();
    panelRef.value?.focusPanel?.();
};

const closeWidget = () => {
    isOpen.value = false;
};

function onOfferHelpEvent(ev) {
    const d = (ev && ev.detail) || {};
    if (isOpen.value) return;
    proactiveNudgeVisible.value = true;
    if (!d.silentSound) {
        playSupportAiChime();
    }
    if (proactiveAutoHideTimer) window.clearTimeout(proactiveAutoHideTimer);
    proactiveAutoHideTimer = window.setTimeout(() => {
        dismissProactiveNudge();
    }, 28000);
}

async function openFromProactiveNudge() {
    dismissProactiveNudge();
    await openWidget();
}

const toggleWidget = async () => {
    if (isOpen.value) {
        closeWidget();
        return;
    }
    await openWidget();
};

const openFullChat = async () => {
    if (isGuest.value) {
        return;
    }

    /** همیشه جریان «از ویجت» تا صفحهٔ چت، آخرین چت اصلی اپ را باز نکند */
    let conversationId = activeConversationId.value;
    if (!conversationId) {
        try {
            conversationId = await ensureConversation();
        } catch {
            conversationId = null;
        }
    }
    try {
        window.localStorage?.removeItem(ACTIVE_CHAT_STORAGE_KEY);
    } catch {
        /* ignore */
    }
    try {
        getStorage()?.removeItem(FLOATING_WIDGET_CONVERSATION_KEY);
    } catch {
        /* ignore */
    }
    const base = config.openChatPath || '/chat';
    const params = new URLSearchParams();
    params.set('newFromFloating', '1');
    if (conversationId) {
        params.set('conversation', String(conversationId));
    }
    window.location.href = `${base}?${params.toString()}`;
};

const onEsc = (event) => {
    if (event.key !== 'Escape') return;
    if (proactiveNudgeVisible.value) {
        dismissProactiveNudge();
        return;
    }
    if (isOpen.value) {
        closeWidget();
    }
};

watch(
    messages,
    () => {
        if (isGuest.value) persistGuestThread();
    },
    { deep: true }
);

watch([activeConversationId, isGuest], async ([id, guest]) => {
    if (guest || !id) {
        conversationLocked.value = false;
        return;
    }
    await refreshConversationLock();
});

onMounted(async () => {
    initLocale();
    await checkAuth();
    if (isGuest.value) {
        loadGuestThreadFromStorage();
    } else {
        const wid = readWidgetStoredConversationId();
        if (wid) activeConversationId.value = wid;
    }

    document.addEventListener('keydown', onEsc);
    document.addEventListener(OFFER_HELP_EVENT, onOfferHelpEvent);
});

onUnmounted(() => {
    document.removeEventListener('keydown', onEsc);
    document.removeEventListener(OFFER_HELP_EVENT, onOfferHelpEvent);
    dismissProactiveNudge();
    if (recordingInterval.value) window.clearInterval(recordingInterval.value);
    if (mediaRecorder.value) {
        mediaRecorder.value.stream?.getTracks().forEach((track) => track.stop());
    }
});
</script>

<style scoped>
.floating-chat-root {
    position: fixed;
    z-index: 2140;
    left: max(20px, env(safe-area-inset-left, 0px));
    right: auto;
    bottom: max(20px, env(safe-area-inset-bottom, 0px));
    pointer-events: none;
    overflow: visible;
}

.floating-chat-stack {
    display: flex;
    flex-direction: column;
    /* LTR تا لبهٔ چپ فیزیکی ثابت بماند؛ با center لانچر زیر پنل عریض به وسط می‌رفت */
    direction: ltr;
    align-items: flex-start;
    gap: 12px;
    width: max-content;
    max-width: min(380px, calc(100vw - 40px));
    overflow: visible;
}

/* لانچر همیشه زیر لبهٔ چپ پنل؛ اندازه ثابت */
.floating-help-nudge {
    pointer-events: auto;
    width: 100%;
    max-width: 300px;
    padding: 0.75rem 0.85rem 0.65rem;
    border-radius: 14px;
    background: linear-gradient(145deg, #fffefb, #f0fdfa);
    border: 1px solid rgba(13, 148, 136, 0.35);
    box-shadow:
        0 14px 36px rgba(15, 118, 110, 0.22),
        0 0 0 1px rgba(255, 255, 255, 0.8) inset;
    position: relative;
    text-align: start;
    direction: rtl;
}

.floating-help-nudge__close {
    position: absolute;
    top: 4px;
    left: 6px;
    width: 28px;
    height: 28px;
    border: none;
    border-radius: 8px;
    background: transparent;
    color: #64748b;
    font-size: 1.25rem;
    line-height: 1;
    cursor: pointer;
}
.floating-help-nudge__close:hover {
    background: rgba(15, 23, 42, 0.06);
    color: #0f172a;
}

.floating-help-nudge__title {
    margin: 0 0 0.35rem;
    padding-inline-end: 1.5rem;
    font-size: 0.95rem;
    font-weight: 700;
    color: #0f766e;
}

.floating-help-nudge__body {
    margin: 0 0 0.65rem;
    font-size: 0.82rem;
    line-height: 1.45;
    color: #334155;
}

.floating-help-nudge__cta {
    width: 100%;
    padding: 0.45rem 0.65rem;
    border: none;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    color: #fff;
    background: linear-gradient(135deg, #0f766e, #0891b2);
    box-shadow: 0 4px 14px rgba(8, 145, 178, 0.35);
}
.floating-help-nudge__cta:hover {
    filter: brightness(1.05);
}

.nudge-pop-enter-active {
    transition: all 0.42s cubic-bezier(0.22, 1, 0.36, 1);
}
.nudge-pop-leave-active {
    transition: opacity 0.22s ease;
}
.nudge-pop-enter-from {
    opacity: 0;
    transform: translateY(18px) scale(0.94);
}
.nudge-pop-leave-to {
    opacity: 0;
}

.floating-chat-root.has-proactive-nudge .floating-chat-launcher-anchor :deep(.floating-chat-launcher) {
    animation: launcher-nudge-ring 1.1s ease-in-out 2;
}

@keyframes launcher-nudge-ring {
    0%,
    100% {
        box-shadow:
            0 18px 42px rgba(14, 116, 144, 0.34),
            inset 0 1px 0 rgba(255, 255, 255, 0.5);
    }
    50% {
        box-shadow:
            0 10px 28px rgba(234, 179, 8, 0.45),
            0 0 0 4px rgba(250, 204, 21, 0.35),
            inset 0 1px 0 rgba(255, 255, 255, 0.5);
    }
}

.floating-chat-launcher-anchor {
    position: relative;
    width: 62px;
    height: 62px;
    flex-shrink: 0;
    align-self: flex-start;
    pointer-events: auto;
    overflow: visible;
}

.floating-chat-root :deep(button),
.floating-chat-root :deep(input),
.floating-chat-root :deep(a),
.floating-chat-root :deep(section),
.floating-chat-root :deep(div) {
    pointer-events: auto;
}

.widget-pop-enter-active,
.widget-pop-leave-active {
    transition: all 0.2s ease;
}

.widget-pop-enter-from,
.widget-pop-leave-to {
    transform: translateY(6px);
    opacity: 0;
}

@media (max-width: 768px) {
    .floating-chat-root {
        left: max(8px, env(safe-area-inset-left, 0px));
        bottom: max(8px, env(safe-area-inset-bottom, 0px));
    }

    .floating-chat-stack {
        max-width: calc(100vw - 16px);
    }
}
</style>
