<template>
    <div class="ai-answer" dir="rtl">
        <!-- متن قالب‌بندی‌شده -->
        <div class="answer-text" v-html="html"></div>

        <!-- TTS -->
        <div v-if="canTTS" class="tts-actions">
            <button type="button" class="btn" @click="play" :disabled="speaking || !textTrim">
                ▶️ پخش
            </button>
            <button type="button" class="btn" @click="stop" :disabled="!speaking">
                ⏹️ توقف
            </button>
        </div>
        <small v-else class="muted">
            مرورگر شما از خواندن خودکار متن (TTS) پشتیبانی نمی‌کند.
        </small>
    </div>
</template>

<script setup>
import { ref, computed, onBeforeUnmount, defineProps } from 'vue';

const props = defineProps({
    text:  { type: String, default: '' },
    rate:  { type: Number, default: 0.95 }, // کمی آهسته‌تر برای فارسی
    pitch: { type: Number, default: 1 }
});

const textTrim = computed(() => (props.text || '').trim());

/* --------- مینی‌مارک‌داون: ### Heading, * / - bullets, 1. numbered, **bold** --------- */
function escapeHtml(s) {
    return s.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;', "'":'&#39;'}[m]));
}
function inlineFormat(s) {
    // **bold**
    return s.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
}
function renderMiniMd(src) {
    const lines = src.split(/\r?\n/);
    let out = [];
    let inUL = false, inOL = false;
    let pBuffer = [];

    const flushP = () => {
        if (pBuffer.length) {
            const txt = inlineFormat(escapeHtml(pBuffer.join(' ')));
            out.push(`<p>${txt}</p>`);
            pBuffer = [];
        }
    };
    const closeLists = () => {
        if (inUL) { out.push('</ul>'); inUL = false; }
        if (inOL) { out.push('</ol>'); inOL = false; }
    };

    for (let raw of lines) {
        const line = raw.trim();

        // Heading ###
        if (/^###\s+/.test(line)) {
            flushP(); closeLists();
            const h = line.replace(/^###\s+/, '');
            out.push(`<h3>${inlineFormat(escapeHtml(h))}</h3>`);
            continue;
        }

        // Bulleted list
        const mUL = line.match(/^[*-]\s+(.+)/);
        if (mUL) {
            flushP();
            if (!inUL) { closeLists(); out.push('<ul>'); inUL = true; }
            out.push(`<li>${inlineFormat(escapeHtml(mUL[1]))}</li>`);
            continue;
        }

        // Numbered list
        const mOL = line.match(/^\d+\.\s+(.+)/);
        if (mOL) {
            flushP();
            if (!inOL) { closeLists(); out.push('<ol>'); inOL = true; }
            out.push(`<li>${inlineFormat(escapeHtml(mOL[1]))}</li>`);
            continue;
        }

        // Blank line => پاراگراف جدید
        if (line === '') {
            flushP(); closeLists();
            continue;
        }

        // Otherwise paragraph buffer
        pBuffer.push(line);
    }

    flushP(); closeLists();
    return out.join('\n');
}

const html = computed(() => renderMiniMd(textTrim.value));

/* ------------------ TTS (Web Speech API) ------------------ */
const speaking = ref(false);
const canTTS = typeof window !== 'undefined' && 'speechSynthesis' in window;
let currentUtterance = null;

function play() {
    if (!canTTS || !textTrim.value) return;
    stop();

    currentUtterance = new SpeechSynthesisUtterance(textTrim.value);
    currentUtterance.lang = 'fa-IR';  // برای فارسی
    currentUtterance.rate = props.rate;
    currentUtterance.pitch = props.pitch;

    currentUtterance.onstart = () => { speaking.value = true; };
    currentUtterance.onend   = () => { speaking.value = false; currentUtterance = null; };
    currentUtterance.onerror = () => { speaking.value = false; currentUtterance = null; };

    window.speechSynthesis.speak(currentUtterance);
}

function stop() {
    if (!canTTS) return;
    window.speechSynthesis.cancel();
    speaking.value = false;
    currentUtterance = null;
}

onBeforeUnmount(() => stop());
</script>

<style scoped>
.ai-answer {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

/* متن قالب‌بندی‌شده */
.answer-text {
    white-space: normal;
    line-height: 1.8;
}

/* Headings و لیست‌ها */
.answer-text h3 {
    font-size: 1.05rem;
    margin: 8px 0 6px;
    font-weight: 700;
}
.answer-text p {
    margin: 6px 0;
}
.answer-text ul, .answer-text ol {
    margin: 6px 18px 6px 0; /* راست‌به‌چپ */
    padding: 0 16px 0 0;
}
.answer-text li {
    margin: 4px 0;
    line-height: 1.8;
}

/* دکمه‌های TTS */
.tts-actions {
    display: flex;
    gap: 8px;
}
.btn {
    padding: 6px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #f8fafc;
    cursor: pointer;
    font-size: 14px;
}
.btn:disabled { opacity: .6; cursor: not-allowed; }

.muted { color: #94a3b8; font-size: 13px; }
</style>
