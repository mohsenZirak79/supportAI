<template>
    <div class="ai-answer" dir="rtl">
        <!-- متن قالب‌بندی‌شده -->
        <div class="answer-text" v-html="html"></div>

        <!-- TTS -->
        <div v-if="canTTS" class="tts-actions">
            <button type="button" class="btn" @click="play" :disabled="speaking || loading || !textTrim">
                <span v-if="loading">⏳ در حال تولید...</span>
                <span v-else>▶️ پخش</span>
            </button>
            <button type="button" class="btn" @click="stop" :disabled="!speaking && !loading">
                ⏹️ توقف
            </button>
        </div>
        <small v-if="error" class="error-text">{{ error }}</small>
        <small v-else-if="!canTTS" class="muted">
            مرورگر شما از پخش صوت پشتیبانی نمی‌کند.
        </small>
    </div>
</template>

<script setup>
import { ref, computed, onBeforeUnmount, defineProps } from 'vue';
import { apiFetch } from '../lib/http';

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

/* ------------------ TTS (Backend API) ------------------ */
const speaking = ref(false);
const canTTS = typeof window !== 'undefined' && 'Audio' in window;
let currentAudio = null;
const loading = ref(false);
const error = ref('');

async function play() {
    if (!canTTS || !textTrim.value) return;
    if (loading.value) return;
    
    stop();

    try {
        loading.value = true;
        error.value = '';

        // Call backend TTS API
        const response = await apiFetch('/text-to-speech', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                text: textTrim.value,
                chunk_index: 0
            })
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.error || 'خطا در تولید صوت');
        }

        const data = await response.json();
        
        if (!data.success || !data.audio) {
            throw new Error(data.error || 'خطا در تولید صوت');
        }

        // Create audio element and play
        const audio = new Audio(data.audio);
        currentAudio = audio;

        audio.onplay = () => {
            speaking.value = true;
            loading.value = false;
        };

        audio.onended = () => {
            speaking.value = false;
            loading.value = false;
            currentAudio = null;
        };

        audio.onerror = (e) => {
            error.value = 'خطا در پخش صوت';
            speaking.value = false;
            loading.value = false;
            currentAudio = null;
        };

        await audio.play();

    } catch (err) {
        console.error('TTS error:', err);
        error.value = err.message || 'خطا در تولید صوت';
        speaking.value = false;
        loading.value = false;
        currentAudio = null;
    }
}

function stop() {
    if (currentAudio) {
        currentAudio.pause();
        currentAudio.currentTime = 0;
        currentAudio = null;
    }
    speaking.value = false;
    loading.value = false;
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
.error-text { color: #ef4444; font-size: 13px; display: block; margin-top: 4px; }
</style>
