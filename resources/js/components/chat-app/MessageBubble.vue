<template>
    <div
        class="cg-msg-row"
        :class="message.sender === 'user' ? 'cg-msg-row--user' : 'cg-msg-row--bot'"
        :data-msg-id="message.id || ''"
    >
        <div
            class="message-bubble"
            :class="message.sender === 'user' ? 'cg-msg-bubble' : 'cg-msg-assistant'"
            @click="handleBubbleClick(message)"
        >
            <template v-if="message.sender === 'bot' && message.text">
                <AiAnswer :text="message.text" :lang="locale" :gender="userVoiceGender" />
            </template>
            <template v-else>
                <div v-if="message.isSending" class="cg-sending">
                    <span class="cg-sending-dot" />
                    {{ $t('chat.sendingVoice') }}
                </div>
                <div v-if="message.text && message.text.trim()" class="voice-transcript">
                    <span class="transcript-text">{{ message.text }}</span>
                </div>
                <div
                    v-else-if="(message.has_voice || message.has_media) && !message.voiceUrl"
                    class="cg-voice-load"
                    @click.stop="emit('bubble-click', message)"
                >
                    <span>{{ $t('chat.loadAndPlay') }}</span>
                </div>
                <span v-else-if="!message.voiceUrl">‌</span>
            </template>

            <div v-if="message.voiceUrl" class="voice-player" @click.stop="emit('play-voice', message.id)">
                <audio
                    :ref="(el) => registerAudioRef(message.id, el)"
                    :src="message.voiceUrl"
                    preload="none"
                    controls
                />
            </div>

            <div class="cg-msg-meta message-meta">
                <span class="timestamp">{{ formatDate(message.created_at) }}</span>
                <div class="msg-actions">
                    <button
                        v-if="message.text"
                        type="button"
                        class="msg-action copy"
                        :aria-label="$t('chat.copyText')"
                        :title="$t('chat.copyText')"
                        @click.stop="emit('copy', message.text)"
                    >
                        <svg viewBox="0 0 24 24" class="icon" aria-hidden="true">
                            <path
                                d="M16 1H4c-1.1 0-2 .9-2 2v12h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"
                            />
                        </svg>
                    </button>
                    <button
                        type="button"
                        class="msg-action handoff"
                        :aria-label="$t('chat.handoff')"
                        :title="$t('chat.handoff')"
                        @click.stop="emit('handoff', message)"
                    >
                        <svg viewBox="0 0 24 24" class="icon" aria-hidden="true">
                            <path d="M4 12v8h16v-8h2v10H2V12h2zm8-9 6 6h-4v6h-4V9H6l6-6z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import AiAnswer from '../AiAnswer.vue';

const props = defineProps({
    message: { type: Object, required: true },
    locale: { type: String, default: 'fa' },
    userVoiceGender: { type: String, default: 'female' },
    formatDate: { type: Function, required: true },
    registerAudioRef: { type: Function, required: true },
    onBubbleClick: { type: Function, required: true },
});

const emit = defineEmits(['copy', 'handoff', 'play-voice', 'bubble-click']);

const handleBubbleClick = (msg) => {
    props.onBubbleClick(msg);
};
</script>
