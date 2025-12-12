<template>
    <div class="ai-answer" :dir="direction">
        <!-- Formatted text -->
        <div class="answer-text" v-html="html"></div>

        <!-- TTS -->
        <div v-if="canTTS" class="tts-actions">
            <button type="button" class="btn" @click="play" :disabled="speaking || loading || !textTrim">
                <span v-if="loading">⏳ {{ t('common.loading') }}</span>
                <span v-else>▶️ {{ t('tts.play') }}</span>
            </button>
            <button type="button" class="btn" @click="stop" :disabled="!speaking && !loading">
                ⏹️ {{ t('tts.stop') }}
            </button>
        </div>
        <small v-if="error" class="error-text">{{ error }}</small>
        <small v-else-if="!canTTS" class="muted">
            {{ t('tts.notSupported') }}
        </small>
    </div>
</template>

<script setup>
import { ref, computed, onBeforeUnmount, defineProps } from 'vue';
import { apiFetch } from '../lib/http';
import { useLanguage } from '../i18n';

const { direction, t } = useLanguage();

const props = defineProps({
    text:  { type: String, default: '' },
    rate:  { type: Number, default: 0.95 }, // کمی آهسته‌تر برای فارسی
    pitch: { type: Number, default: 1 },
    lang:  { type: String, default: 'fa' } // Language code: fa, en, ar
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

        // Call backend TTS API with language parameter
        const response = await apiFetch('/text-to-speech', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                text: textTrim.value,
                chunk_index: 0,
                lang: props.lang || 'fa'
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

        // Convert data URL to Blob URL (more reliable for browser security)
        let audioUrl = data.audio;
        let blobUrlCreated = false;
        
        // If it's a data URL, convert to Blob URL
        if (audioUrl.startsWith('data:')) {
            try {
                // Extract MIME type and base64 data
                const matches = audioUrl.match(/^data:([^;]+);base64,(.+)$/);
                if (!matches || matches.length < 3) {
                    throw new Error('فرمت data URL نامعتبر است');
                }
                
                let mimeType = matches[1];
                const base64Data = matches[2];
                
                console.log('Original MIME type:', mimeType);
                console.log('Base64 data length:', base64Data.length);
                
                // Convert base64 to binary
                let binaryString;
                try {
                    binaryString = atob(base64Data);
                } catch (e) {
                    throw new Error('خطا در تبدیل base64: ' + e.message);
                }
                
                const bytes = new Uint8Array(binaryString.length);
                for (let i = 0; i < binaryString.length; i++) {
                    bytes[i] = binaryString.charCodeAt(i);
                }
                
                console.log('Created binary data, length:', bytes.length);
                
                // Detect actual format from magic bytes
                if (bytes.length >= 4) {
                    // WebM starts with: 1a 45 df a3
                    if (bytes[0] === 0x1a && bytes[1] === 0x45 && bytes[2] === 0xdf && bytes[3] === 0xa3) {
                        console.log('Detected WebM format from magic bytes');
                        mimeType = 'audio/webm';
                    }
                    // MP3 starts with: FF FB or FF F3
                    else if (bytes[0] === 0xFF && (bytes[1] === 0xFB || bytes[1] === 0xF3)) {
                        console.log('Detected MP3 format from magic bytes');
                        mimeType = 'audio/mpeg';
                    }
                    // OGG starts with: OggS
                    else if (bytes[0] === 0x4F && bytes[1] === 0x67 && bytes[2] === 0x67 && bytes[3] === 0x53) {
                        console.log('Detected OGG format from magic bytes');
                        mimeType = 'audio/ogg';
                    }
                }
                
                // Validate audio data before creating Blob
                if (bytes.length === 0) {
                    throw new Error('داده صوتی خالی است');
                }
                
                // Create Blob with detected/correct MIME type
                const blob = new Blob([bytes], { type: mimeType });
                
                // Verify blob was created
                if (blob.size === 0) {
                    throw new Error('Blob ایجاد نشد');
                }
                
                audioUrl = URL.createObjectURL(blob);
                blobUrlCreated = true;
                console.log('Created Blob URL:', {
                    mimeType: mimeType,
                    blobSize: blob.size,
                    urlPreview: audioUrl.substring(0, 50) + '...',
                    firstBytes: Array.from(bytes.slice(0, 8)).map(b => '0x' + b.toString(16).padStart(2, '0')).join(' ')
                });
            } catch (blobError) {
                console.error('Error creating Blob URL:', blobError);
                throw new Error('خطا در تبدیل صوت: ' + blobError.message);
            }
        } else {
            console.log('Audio URL is not a data URL, using as-is:', audioUrl.substring(0, 50));
        }

        // Verify we have a valid URL
        if (!audioUrl || (!audioUrl.startsWith('blob:') && !audioUrl.startsWith('data:'))) {
            throw new Error('URL صوتی نامعتبر است');
        }
        
        console.log('Using audio URL type:', audioUrl.startsWith('blob:') ? 'Blob URL' : 'Data URL');
        
        // Store the original URL for cleanup
        const originalAudioUrl = audioUrl;
        
        // Clean up Blob URL when audio ends or errors
        const cleanupBlobUrl = () => {
            if (originalAudioUrl && originalAudioUrl.startsWith('blob:')) {
                console.log('Cleaning up Blob URL');
                try {
                    URL.revokeObjectURL(originalAudioUrl);
                } catch (e) {
                    console.warn('Error revoking Blob URL:', e);
                }
            }
        };
        
        // Create audio element with preload
        const audio = new Audio();
        audio.preload = 'auto';
        audio.src = audioUrl;
        currentAudio = audio;
        
        console.log('Audio element created, src set to:', audioUrl.substring(0, 50) + '...');

        // Set up playback event handlers
        audio.onplay = () => {
            console.log('Audio started playing');
            speaking.value = true;
            loading.value = false;
        };

        audio.onended = () => {
            console.log('Audio finished playing');
            speaking.value = false;
            loading.value = false;
            cleanupBlobUrl();
            currentAudio = null;
        };

        audio.onerror = (e) => {
            console.error('Audio error:', e, audio.error);
            const errorMsg = audio.error ? 
                `خطا ${audio.error.code}: ${audio.error.message}` : 
                'خطا در بارگذاری صوت';
            error.value = errorMsg;
            speaking.value = false;
            loading.value = false;
            currentAudio = null;
        };

        // Try to play immediately, or wait for it to be ready
        try {
            // Try playing immediately (might work if audio is small)
            await audio.play();
            console.log('Audio play() called successfully');
        } catch (playError) {
            console.log('Immediate play failed, waiting for audio to load:', playError);
            
            // Wait for audio to be ready
            await new Promise((resolve, reject) => {
                let resolved = false;

                const cleanup = () => {
                    if (resolved) return;
                    resolved = true;
                    clearTimeout(timeout);
                };

                audio.oncanplaythrough = async () => {
                    console.log('Audio can play through, starting playback');
                    cleanup();
                    
                    try {
                        await audio.play();
                        console.log('Audio play() called successfully');
                        resolve();
                    } catch (playError2) {
                        console.error('Error calling audio.play():', playError2);
                        if (playError2.name === 'NotAllowedError') {
                            error.value = 'لطفا ابتدا روی دکمه پخش کلیک کنید';
                        } else {
                            error.value = 'خطا در پخش صوت: ' + playError2.message;
                        }
                        speaking.value = false;
                        loading.value = false;
                        currentAudio = null;
                        reject(playError2);
                    }
                };

                audio.onerror = (e) => {
                    console.error('Audio loading error:', e, audio.error);
                    cleanup();
                    
                    const errorMsg = audio.error ? 
                        `خطا ${audio.error.code}: ${audio.error.message}` : 
                        'خطا در بارگذاری صوت';
                    error.value = errorMsg;
                    speaking.value = false;
                    loading.value = false;
                    cleanupBlobUrl();
                    currentAudio = null;
                    reject(new Error(errorMsg));
                };

                // Set a timeout
                const timeout = setTimeout(() => {
                    if (!resolved && audio.readyState < 2) {
                        console.error('Audio loading timeout, readyState:', audio.readyState);
                        cleanup();
                        error.value = 'زمان بارگذاری صوت به پایان رسید';
                        speaking.value = false;
                        loading.value = false;
                        cleanupBlobUrl();
                        currentAudio = null;
                        reject(new Error('Audio loading timeout'));
                    }
                }, 10000);

                // Start loading
                audio.load();
            });
        }

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
        // Clean up Blob URL if it exists
        if (currentAudio.src && currentAudio.src.startsWith('blob:')) {
            URL.revokeObjectURL(currentAudio.src);
        }
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
    margin: 6px 0 6px 18px;
    padding: 0 0 0 16px;
}

[dir="rtl"] .answer-text ul, [dir="rtl"] .answer-text ol {
    margin: 6px 18px 6px 0;
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
