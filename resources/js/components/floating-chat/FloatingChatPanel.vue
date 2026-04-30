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
            <div>
                <strong>{{ assistantName }}</strong>
                <small>{{ t('floating.onlineStatus') }}</small>
            </div>
            <button class="close-btn" type="button" :aria-label="t('floating.closeChat')" @click="$emit('close')">
                ✕
            </button>
        </header>

        <div class="widget-messages">
            <div v-for="(msg, idx) in messages" :key="msg.id || idx" class="msg" :class="msg.sender">
                <div class="bubble">
                    <AiAnswer v-if="msg.sender === 'bot' && msg.text" :text="msg.text" />
                    <template v-else>{{ msg.text || t('chat.voiceMessage') }}</template>
                </div>
            </div>
            <div v-if="loading" class="typing">{{ t('floating.processing') }}</div>
        </div>

        <FloatingChatInput
            :model-value="draft"
            :disabled="loading"
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

        <footer class="widget-footer">
            <button type="button" class="open-full-btn" @click="$emit('open-full-chat')">
                {{ t('floating.openFullChat') }}
            </button>
        </footer>
    </section>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import AiAnswer from '../AiAnswer.vue';
import FloatingChatInput from './FloatingChatInput.vue';
import { useLanguage } from '../../i18n';

defineProps({
    messages: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    draft: { type: String, default: '' },
    assistantName: { type: String, default: 'دستیار هوشمند' },
    isRecording: { type: Boolean, default: false },
    recordingTime: { type: Number, default: 0 },
});

defineEmits([
    'update:draft',
    'send-text',
    'close',
    'open-full-chat',
    'start-recording',
    'cancel-recording',
    'send-recording',
]);

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
    border-radius: 20px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    background: rgba(255, 255, 255, 0.82);
    border: 1px solid rgba(148, 163, 184, 0.32);
    box-shadow: 0 22px 52px rgba(15, 23, 42, 0.3);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
}

.widget-header {
    padding: 12px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(148, 163, 184, 0.25);
}
.widget-header strong {
    display: block;
    font-size: 14px;
    color: #0f172a;
}
.widget-header small {
    font-size: 11px;
    color: #16a34a;
}

.close-btn {
    border: 0;
    width: 32px;
    height: 32px;
    border-radius: 10px;
    background: rgba(226, 232, 240, 0.65);
    cursor: pointer;
}

.widget-messages {
    flex: 1;
    overflow: auto;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.msg {
    display: flex;
}
.msg.user {
    justify-content: flex-end;
}
.msg.bot {
    justify-content: flex-start;
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
    padding: 8px 12px 12px;
}
.open-full-btn {
    width: 100%;
    border: 0;
    border-radius: 11px;
    padding: 9px 10px;
    background: rgba(15, 118, 110, 0.1);
    color: #0f766e;
    font-weight: 600;
    cursor: pointer;
}

@media (max-width: 768px) {
    .floating-chat-panel {
        width: calc(100vw - 12px);
        height: min(72vh, 620px);
        border-radius: 18px 18px 0 0;
    }
}
</style>
