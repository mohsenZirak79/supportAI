<template>
    <div v-if="config.enableFloatingChatWidget" class="floating-chat-root" :class="{ 'is-open': isOpen }">
        <FloatingChatGreeting :visible="showGreeting" />

        <div class="floating-chat-stack">
            <transition name="widget-pop">
                <FloatingChatPanel
                    v-if="isOpen"
                    ref="panelRef"
                    :messages="messages"
                    :loading="loading"
                    :draft="draft"
                    :assistant-name="config.assistantName"
                    :is-recording="isRecording"
                    :recording-time="recordingTime"
                    @update:draft="draft = $event"
                    @send-text="sendText"
                    @close="closeWidget"
                    @open-full-chat="openFullChat"
                    @start-recording="startRecording"
                    @cancel-recording="cancelRecording"
                    @send-recording="sendRecording"
                />
            </transition>

            <FloatingChatLauncher
                :pulse="launcherPulse"
                :aria-label="isOpen ? 'بستن چت شناور' : 'باز کردن چت شناور'"
                @toggle="toggleWidget"
            />
        </div>
    </div>
</template>

<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { apiFetch } from '../../lib/http';
import { floatingChatWidgetConfig } from '../../config/floatingChatWidget';
import { useLanguage } from '../../i18n';
import FloatingChatLauncher from './FloatingChatLauncher.vue';
import FloatingChatPanel from './FloatingChatPanel.vue';
import FloatingChatGreeting from './FloatingChatGreeting.vue';

const ACTIVE_CHAT_STORAGE_KEY = 'supportAI:active-conversation-id';
const FLOATING_IMPORT_STORAGE_KEY = 'supportAI:floating-import-v1';
const GUEST_FLOATING_SESSION_KEY = 'supportAI:floating-guest-thread-v1';
const GREETING_SHOWN_STORAGE_KEY = 'supportAI:floating-widget:greeting-shown';
const GREETING_SOUND_STORAGE_KEY = 'supportAI:floating-widget:greeting-sound-played';
const EVENT_ACTIVE_CHAT_CHANGED = 'supportAI:active-chat-changed';

const { t, initLocale } = useLanguage();

const config = floatingChatWidgetConfig;
const isOpen = ref(false);
const launcherPulse = ref(false);
const showGreeting = ref(false);
const draft = ref('');
const loading = ref(false);
const messages = ref([]);
const activeConversationId = ref(null);
const panelRef = ref(null);

const isGuest = ref(false);
const authChecked = ref(false);

const isRecording = ref(false);
const recordingTime = ref(0);
const recordingInterval = ref(null);
const mediaRecorder = ref(null);
const audioChunks = ref([]);

const greetingDelayMs = computed(() => Number(config.greetingDelayMs || 0));
const greetingVisibleMs = computed(() => Number(config.greetingVisibleMs || 0));

let greetingOpenTimer;
let greetingCloseTimer;

const getStorage = () => {
    if (typeof window === 'undefined') return null;
    try {
        return window.sessionStorage;
    } catch {
        return null;
    }
};

const getPreferredConversationId = () => {
    if (typeof window === 'undefined' || isGuest.value) return null;
    const urlConversation = new URLSearchParams(window.location.search).get('conversation');
    if (urlConversation) return Number(urlConversation) || urlConversation;
    const saved = window.localStorage?.getItem(ACTIVE_CHAT_STORAGE_KEY);
    if (!saved) return null;
    return Number(saved) || saved;
};

const persistActiveConversationId = (id) => {
    if (typeof window === 'undefined' || !id || isGuest.value) return;
    window.localStorage?.setItem(ACTIVE_CHAT_STORAGE_KEY, String(id));
    window.dispatchEvent(new CustomEvent(EVENT_ACTIVE_CHAT_CHANGED, { detail: { id } }));
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

const ensureConversation = async () => {
    if (isGuest.value) {
        throw new Error('guest has no server conversation');
    }
    if (activeConversationId.value) return activeConversationId.value;

    const preferredId = getPreferredConversationId();
    if (preferredId) {
        activeConversationId.value = preferredId;
        return preferredId;
    }

    const listRes = await apiFetch('/conversations');
    if (listRes.ok) {
        const payload = await listRes.json();
        const first = payload?.data?.[0];
        if (first?.id) {
            activeConversationId.value = first.id;
            persistActiveConversationId(first.id);
            return first.id;
        }
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
    persistActiveConversationId(newConversation.id);
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
    messages.value.push({
        id: payload?.id ?? `widget-ai-${Date.now()}`,
        sender: 'bot',
        text: payload?.content || 'پاسخی دریافت نشد.',
        created_at: payload?.created_at ?? new Date().toISOString(),
    });
};

const sendText = async () => {
    const text = draft.value.trim();
    if (!text || loading.value) return;

    appendUserMessage(text);
    draft.value = '';

    if (isGuest.value) {
        appendBotMessage({ content: t('floating.guestNeedLoginForAi') });
        return;
    }

    loading.value = true;

    try {
        const conversationId = await ensureConversation();
        const res = await apiFetch(`/conversations/${conversationId}/messages`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ content: text, lang: document.documentElement.lang || 'fa' }),
        });
        if (!res.ok) throw new Error('send failed');
        const data = await res.json();
        if (data?.conversation?.id) {
            activeConversationId.value = data.conversation.id;
            persistActiveConversationId(data.conversation.id);
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
            }),
        });
        if (!messageRes.ok) throw new Error('voice send failed');
        const data = await messageRes.json();
        if (data?.conversation?.id) {
            activeConversationId.value = data.conversation.id;
            persistActiveConversationId(data.conversation.id);
        }
        appendBotMessage(data?.ai_message);
    } catch {
        appendBotMessage({ content: 'خطا در ارسال پیام صوتی.' });
    } finally {
        loading.value = false;
        URL.revokeObjectURL(tempVoiceUrl);
    }
};

const hideGreeting = () => {
    showGreeting.value = false;
    const storage = getStorage();
    storage?.setItem(GREETING_SHOWN_STORAGE_KEY, '1');
};

const maybePlayGreetingSound = async () => {
    if (!config.enableGreetingSound) return;
    const storage = getStorage();
    if (storage?.getItem(GREETING_SOUND_STORAGE_KEY)) return;
    if (!navigator.userActivation?.hasBeenActive) return;
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = ctx.createOscillator();
        const gain = ctx.createGain();
        oscillator.type = 'sine';
        oscillator.frequency.value = 740;
        gain.gain.value = 0.01;
        oscillator.connect(gain);
        gain.connect(ctx.destination);
        oscillator.start();
        oscillator.stop(ctx.currentTime + 0.07);
        storage?.setItem(GREETING_SOUND_STORAGE_KEY, '1');
    } catch {
        // Respect autoplay restrictions; silently fail.
    }
};

const openGreeting = async () => {
    const storage = getStorage();
    if (storage?.getItem(GREETING_SHOWN_STORAGE_KEY)) return;
    showGreeting.value = true;
    launcherPulse.value = true;
    await maybePlayGreetingSound();
    greetingCloseTimer = window.setTimeout(() => {
        hideGreeting();
    }, greetingVisibleMs.value);
};

const openWidget = async () => {
    if (!authChecked.value) {
        await checkAuth();
    }
    isOpen.value = true;
    hideGreeting();
    launcherPulse.value = false;
    if (!isGuest.value) {
        try {
            await ensureConversation();
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

const toggleWidget = async () => {
    if (isOpen.value) {
        closeWidget();
        return;
    }
    await openWidget();
};

const openFullChat = async () => {
    const loginBase = config.loginPath || '/login';

    if (isGuest.value) {
        const rows = messages.value
            .map((m) => ({
                sender: m.sender === 'user' ? 'user' : 'bot',
                text: (m.text || '').trim(),
            }))
            .filter((r) => r.text !== '');
        const userHasContent = rows.some((r) => r.sender === 'user');
        if (userHasContent) {
            try {
                getStorage()?.setItem(
                    FLOATING_IMPORT_STORAGE_KEY,
                    JSON.stringify({
                        title: 'چت جدید',
                        messages: rows,
                    })
                );
            } catch {
                /* ignore */
            }
        }
        try {
            getStorage()?.removeItem(GUEST_FLOATING_SESSION_KEY);
        } catch {
            /* ignore */
        }
        try {
            window.localStorage?.removeItem(ACTIVE_CHAT_STORAGE_KEY);
        } catch {
            /* ignore */
        }
        window.location.href = loginBase;
        return;
    }

    /** همان گفتگوی ویجت روی سرور؛ بدون ادامهٔ چت قدیمیِ ذخیره‌شده در localStorage */
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
    const base = config.openChatPath || '/chat';
    const params = new URLSearchParams();
    if (conversationId) {
        params.set('conversation', String(conversationId));
    } else {
        params.set('newFromFloating', '1');
    }
    window.location.href = `${base}?${params.toString()}`;
};

const onEsc = (event) => {
    if (event.key === 'Escape' && isOpen.value) {
        closeWidget();
    }
};

const onExternalActiveConversationChange = (event) => {
    if (isGuest.value) return;
    const id = event?.detail?.id;
    if (!id) return;
    activeConversationId.value = id;
};

watch(isOpen, (opened) => {
    if (opened) {
        hideGreeting();
    }
});

watch(
    messages,
    () => {
        if (isGuest.value) persistGuestThread();
    },
    { deep: true }
);

onMounted(async () => {
    initLocale();
    await checkAuth();
    if (isGuest.value) {
        loadGuestThreadFromStorage();
    } else {
        const preferred = getPreferredConversationId();
        if (preferred) activeConversationId.value = preferred;
    }

    greetingOpenTimer = window.setTimeout(() => {
        openGreeting();
    }, greetingDelayMs.value);

    document.addEventListener('keydown', onEsc);
    window.addEventListener(EVENT_ACTIVE_CHAT_CHANGED, onExternalActiveConversationChange);
});

onUnmounted(() => {
    window.clearTimeout(greetingOpenTimer);
    window.clearTimeout(greetingCloseTimer);
    document.removeEventListener('keydown', onEsc);
    window.removeEventListener(EVENT_ACTIVE_CHAT_CHANGED, onExternalActiveConversationChange);
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
}

.floating-chat-stack {
    display: flex;
    flex-direction: column;
    /* وسط‌چین تا با باز شدن پنل عریض‌تر، دکمهٔ لانچر در صفحات RTL جابه‌جا نشود */
    align-items: center;
    gap: 10px;
    width: max-content;
    max-width: min(380px, calc(100vw - 40px));
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
    transform: translateY(10px) scale(0.97);
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
