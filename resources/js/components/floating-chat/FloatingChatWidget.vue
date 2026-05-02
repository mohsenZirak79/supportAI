<template>
    <div v-if="config.enableFloatingChatWidget" class="floating-chat-root" :class="{ 'is-open': isOpen }">
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

            <div class="floating-chat-launcher-anchor">
                <FloatingChatGreeting
                    :visible="greetingVisible"
                    :text="t('floating.greeting')"
                    :text-dir="direction"
                />
                <FloatingChatLauncher
                    :aria-label="isOpen ? 'بستن چت شناور' : 'باز کردن چت شناور'"
                    @toggle="toggleWidget"
                />
            </div>
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
const FLOATING_WIDGET_CONVERSATION_KEY = 'supportAI:floating-widget-conversation-id';
const FLOATING_IMPORT_STORAGE_KEY = 'supportAI:floating-import-v1';
const GUEST_FLOATING_SESSION_KEY = 'supportAI:floating-guest-thread-v1';

const { t, initLocale, direction } = useLanguage();

const config = floatingChatWidgetConfig;
const isOpen = ref(false);
/** پیام «نیاز به کمک…» همیشه وقتی پنل بسته است نمایش داده می‌شود */
const greetingVisible = computed(() => !isOpen.value);
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
    if (event.key === 'Escape' && isOpen.value) {
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
});

onUnmounted(() => {
    document.removeEventListener('keydown', onEsc);
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
    /* LTR تا لبهٔ چپ فیزیکی ثابت بماند؛ با center لانچر زیر پنل عریض به وسط می‌رفت */
    direction: ltr;
    align-items: flex-start;
    gap: 12px;
    width: max-content;
    max-width: min(380px, calc(100vw - 40px));
}

/* لانچر همیشه زیر لبهٔ چپ پنل؛ اندازه ثابت */
.floating-chat-launcher-anchor {
    position: relative;
    width: 62px;
    height: 62px;
    flex-shrink: 0;
    align-self: flex-start;
    pointer-events: auto;
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
