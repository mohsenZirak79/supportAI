<template>
    <section
        ref="panelRef"
        class="floating-chat-panel"
        role="dialog"
        aria-modal="false"
        :aria-label="t('floating.dialogLabel')"
        tabindex="-1"
    >
        <header class="widget-header">
            <div class="widget-header__titles">
                <strong>{{ assistantName }}</strong>
                <small>{{ t('floating.onlineStatus') }}</small>
            </div>
            <button class="close-btn" type="button" :aria-label="t('floating.closeChat')" @click="$emit('close')">
                ✕
            </button>
        </header>

        <div class="widget-messages">
            <div v-for="(msg, idx) in messages" :key="msg.id || idx" class="msg" :class="msg.sender">
                <div class="bubble" :dir="direction">
                    <AiAnswer v-if="msg.sender === 'bot' && msg.text" :text="msg.text" />
                    <template v-else>{{ msg.text || t('chat.voiceMessage') }}</template>
                </div>
                <div
                    v-if="msg.sender === 'bot' && showPhoneCallbackOption && !chatLocked && msg.aiMessageId && conversationId && !msg.callbackRegistered"
                    class="callback-cta"
                >
                    <label class="callback-cta__row">
                        <input v-model="consentByMessageId[msg.aiMessageId]" type="checkbox" />
                        <span>{{ t('floating.callbackConsentLabel') }}</span>
                    </label>
                    <button
                        type="button"
                        class="callback-cta__btn"
                        :disabled="!consentByMessageId[msg.aiMessageId]"
                        @click="requestCallback(msg)"
                    >
                        {{ t('floating.callbackSubmit') }}
                    </button>
                </div>
                <div v-else-if="msg.sender === 'bot' && msg.callbackRegistered && !chatLocked" class="callback-done">
                    {{ t('floating.callbackRegistered') }}
                </div>
            </div>
            <div v-if="loading" class="typing">{{ t('floating.processing') }}</div>
        </div>

        <div v-if="chatLocked" class="chat-lock-banner" role="status">
            {{ t('floating.chatLockedBanner') }}
        </div>

        <FloatingChatInput
            :model-value="draft"
            :disabled="loading || chatLocked"
            :is-recording="isRecording"
            :recording-time="recordingTime"
            :dir="direction"
            :placeholder="t('chat.inputPlaceholder')"
            :send-label="t('chat.send')"
            :mic-label="t('chat.recordVoice')"
            @update:modelValue="$emit('update:draft', $event)"
            @send-text="$emit('send-text')"
            @start-recording="$emit('start-recording')"
            @cancel-recording="$emit('cancel-recording')"
            @send-recording="$emit('send-recording')"
        />

        <footer v-if="showOpenFullChat" class="widget-footer widget-footer--row">
            <button type="button" class="open-full-btn open-full-btn--secondary" @click="$emit('new-chat')">
                {{ t('floating.newChat') }}
            </button>
            <button type="button" class="open-full-btn" @click="$emit('open-full-chat')">
                {{ t('floating.openFullChat') }}
            </button>
        </footer>
    </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import AiAnswer from '../AiAnswer.vue';
import FloatingChatInput from './FloatingChatInput.vue';
import { useLanguage } from '../../i18n';

defineProps({
    messages: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    draft: { type: String, default: '' },
    assistantName: { type: String, default: 'دستیار هوشمند' },
    /** فقط برای کاربر لاگین‌شده؛ مهمان دکمهٔ «چت کامل» ندارد */
    showOpenFullChat: { type: Boolean, default: true },
    /** درخواست تماس پس از پیام دستیار (فقط کاربر لاگین) */
    showPhoneCallbackOption: { type: Boolean, default: false },
    /** شناسهٔ گفتگوی ویجت روی سرور */
    conversationId: { type: [String, Number], default: null },
    /** پس از ثبت درخواست تماس برای این گفتگو */
    chatLocked: { type: Boolean, default: false },
    isRecording: { type: Boolean, default: false },
    recordingTime: { type: Number, default: 0 },
});

const emit = defineEmits([
    'update:draft',
    'send-text',
    'close',
    'open-full-chat',
    'start-recording',
    'cancel-recording',
    'send-recording',
    'phone-callback-request',
    'new-chat',
]);

const consentByMessageId = reactive({});

function requestCallback(msg) {
    const id = msg.aiMessageId;
    if (!id || !consentByMessageId[id]) return;
    emit('phone-callback-request', { aiMessageId: id, consent: true });
}

const { t, initLocale, direction } = useLanguage();

const panelRef = ref(null);
const focusPanel = () => panelRef.value?.focus();

onMounted(() => {
    initLocale();
});

defineExpose({ focusPanel });
</script>

<style scoped>
.floating-chat-panel {
    width: min(380px, calc(100vw - 20px));
    height: min(620px, calc(100vh - 120px));
    border-radius: 18px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    background: rgba(255, 255, 255, 0.94);
    border: 1px solid rgba(148, 163, 184, 0.28);
    box-shadow:
        0 20px 50px rgba(15, 23, 42, 0.14),
        0 0 0 1px rgba(255, 255, 255, 0.75) inset;
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
}

.widget-header {
    flex-shrink: 0;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    border-bottom: 1px solid rgba(15, 118, 110, 0.35);
    background: linear-gradient(135deg, #0f766e 0%, #0d9488 45%, #0891b2 100%);
}

.widget-header__titles {
    min-width: 0;
    flex: 1;
    padding-inline-end: 4px;
}

.widget-header strong {
    display: block;
    font-size: 15px;
    font-weight: 700;
    color: #f8fafc;
    letter-spacing: -0.02em;
    line-height: 1.3;
    text-shadow: 0 1px 2px rgba(15, 23, 42, 0.12);
}

.widget-header small {
    display: block;
    margin-top: 3px;
    font-size: 11px;
    font-weight: 600;
    color: rgba(167, 243, 208, 0.95);
    letter-spacing: 0.02em;
}

.close-btn {
    border: 0;
    width: 36px;
    height: 36px;
    flex-shrink: 0;
    border-radius: 11px;
    background: rgba(255, 255, 255, 0.18);
    color: #f1f5f9;
    font-size: 15px;
    line-height: 1;
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease;
}

.close-btn:hover {
    background: rgba(255, 255, 255, 0.28);
    color: #fff;
}

.widget-messages {
    flex: 1;
    overflow: auto;
    padding: 14px 14px 12px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    /* تراز فیزیکی: کاربر راست، ربات چپ — مستقل از direction صفحه (فارسی/عربی/انگلیسی) */
    direction: ltr;
}
.msg {
    display: flex;
    width: 100%;
}
.msg.user {
    justify-content: flex-end;
}
.msg.bot {
    justify-content: flex-start;
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
}
.callback-cta {
    max-width: 85%;
    padding: 8px 10px;
    border-radius: 12px;
    background: rgba(240, 253, 250, 0.95);
    border: 1px solid rgba(13, 148, 136, 0.25);
    font-size: 11px;
    color: #0f172a;
}
.callback-cta__row {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    cursor: pointer;
    margin-bottom: 8px;
    line-height: 1.45;
}
.callback-cta__row input {
    margin-top: 2px;
    flex-shrink: 0;
}
.callback-cta__btn {
    width: 100%;
    border: 0;
    border-radius: 10px;
    padding: 8px 10px;
    font-size: 12px;
    font-weight: 600;
    background: linear-gradient(135deg, #0f766e, #0d9488);
    color: #fff;
    cursor: pointer;
}
.callback-cta__btn:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}
.callback-done {
    max-width: 85%;
    font-size: 11px;
    color: #047857;
    font-weight: 600;
    padding-inline-start: 2px;
}
.chat-lock-banner {
    flex-shrink: 0;
    margin: 0 14px 8px;
    padding: 10px 12px;
    border-radius: 12px;
    font-size: 12px;
    line-height: 1.45;
    color: #92400e;
    background: #fffbeb;
    border: 1px solid rgba(245, 158, 11, 0.35);
}
.bubble {
    max-width: 85%;
    border-radius: 14px;
    padding: 8px 10px;
    font-size: 13px;
}
.msg.user .bubble {
    background: linear-gradient(135deg, #0f766e, #06b6d4);
    color: #fff;
}
.msg.bot .bubble {
    background: rgba(226, 232, 240, 0.8);
    color: #0f172a;
}
.typing {
    font-size: 12px;
    color: #64748b;
    padding: 4px 8px;
}

.widget-footer {
    flex-shrink: 0;
    padding: 6px 14px 14px;
    border-top: 1px solid rgba(148, 163, 184, 0.15);
    background: rgba(248, 250, 252, 0.55);
}

.widget-footer--row {
    display: flex;
    flex-direction: row;
    gap: 8px;
    align-items: stretch;
}

.open-full-btn {
    flex: 1;
    min-width: 0;
    border: 0;
    border-radius: 12px;
    padding: 10px 12px;
    font-size: 13px;
    background: rgba(15, 118, 110, 0.1);
    color: #0f766e;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s ease, transform 0.12s ease;
}

.open-full-btn:hover {
    background: rgba(15, 118, 110, 0.16);
}

.open-full-btn--secondary {
    background: rgba(148, 163, 184, 0.2);
    color: #334155;
}

.open-full-btn--secondary:hover {
    background: rgba(148, 163, 184, 0.3);
}

.open-full-btn:active {
    transform: scale(0.99);
}

@media (max-width: 768px) {
    .floating-chat-panel {
        width: calc(100vw - 12px);
        height: min(72vh, 620px);
        border-radius: 18px 18px 0 0;
    }
}
</style>
