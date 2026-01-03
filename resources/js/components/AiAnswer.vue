<template>
    <div class="ai-answer" :dir="direction">
        <!-- Formatted text -->
        <div class="answer-text" v-html="html"></div>

        <!-- TTS - Beautiful minimal icons -->
        <div v-if="canTTS && textTrim" class="tts-container">
            <button 
                type="button" 
                class="tts-btn" 
                :class="{ 
                    'is-playing': speaking, 
                    'is-loading': loading 
                }"
                @click="togglePlayback"
                :disabled="loading && !speaking"
                :title="speaking ? t('tts.stop') : t('tts.play')"
            >
                <!-- Loading spinner -->
                <svg v-if="loading && !speaking" class="tts-icon spin" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" stroke-dasharray="31.4 31.4" stroke-linecap="round"/>
                </svg>
                
                <!-- Stop icon (when playing) -->
                <svg v-else-if="speaking" class="tts-icon" viewBox="0 0 24 24" fill="currentColor">
                    <rect x="6" y="6" width="12" height="12" rx="1"/>
                </svg>
                
                <!-- Play icon (default) -->
                <svg v-else class="tts-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M8 5.14v14l11-7-11-7z"/>
                </svg>
                
                <!-- Sound waves animation when playing -->
                <div v-if="speaking" class="sound-waves">
                    <span class="wave wave-1"></span>
                    <span class="wave wave-2"></span>
                    <span class="wave wave-3"></span>
                </div>
            </button>
        </div>
        <small v-if="error" class="error-text">{{ error }}</small>
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
    lang:  { type: String, default: 'fa' }, // Language code: fa, en, ar
    gender: { type: String, default: 'female' } // Voice gender: male, female
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

function togglePlayback() {
    if (speaking.value || loading.value) {
        stop();
    } else {
        play();
    }
}

async function play() {
    if (!canTTS || !textTrim.value) return;
    if (loading.value) return;
    
    stop();

    try {
        loading.value = true;
        error.value = '';

        // Call backend TTS API with language and gender parameters
        const response = await apiFetch('/text-to-speech', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                text: textTrim.value,
                chunk_index: 0,
                lang: props.lang || 'fa',
                gender: props.gender || 'female'
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
            // Don't show error if we're stopping manually
            if (isStoppingManually) return;
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
                    // Don't show error if we're stopping manually
                    if (isStoppingManually) {
                        cleanup();
                        return;
                    }
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
        // Don't show error if we're stopping manually
        if (isStoppingManually) return;
        console.error('TTS error:', err);
        error.value = err.message || 'خطا در تولید صوت';
        speaking.value = false;
        loading.value = false;
        currentAudio = null;
    }
}

let isStoppingManually = false;

function stop() {
    isStoppingManually = true;
    error.value = ''; // Clear any error when stopping manually
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
    // Reset flag after a short delay
    setTimeout(() => { isStoppingManually = false; }, 100);
}

onBeforeUnmount(() => stop());
</script>

<style scoped>
.ai-answer {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* متن قالب‌بندی‌شده */
.answer-text {
    white-space: normal;
    line-height: 1.85;
    font-size: 0.95rem;
    color: #1e293b;
}

/* Headings - زیباتر و کوچکتر */
.answer-text :deep(h3) {
    font-size: 0.95rem;
    font-weight: 600;
    color: #0f172a;
    margin: 14px 0 8px 0;
    padding-bottom: 6px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 6px;
}

.answer-text :deep(h3)::before {
    content: '';
    display: inline-block;
    width: 4px;
    height: 16px;
    background: linear-gradient(135deg, #0e7490, #06b6d4);
    border-radius: 2px;
    flex-shrink: 0;
}

/* پاراگراف‌ها */
.answer-text :deep(p) {
    margin: 8px 0;
    text-align: justify;
    color: #334155;
}

/* لیست‌ها */
.answer-text :deep(ul),
.answer-text :deep(ol) {
    margin: 10px 0;
    padding: 0;
    list-style: none;
}

.answer-text :deep(li) {
    position: relative;
    margin: 6px 0;
    padding-inline-start: 22px;
    line-height: 1.75;
    color: #475569;
}

/* Bullet برای ul */
.answer-text :deep(ul li)::before {
    content: '';
    position: absolute;
    inset-inline-start: 0;
    top: 9px;
    width: 6px;
    height: 6px;
    background: #0e7490;
    border-radius: 50%;
}

/* شماره برای ol */
.answer-text :deep(ol) {
    counter-reset: list-counter;
}

.answer-text :deep(ol li) {
    counter-increment: list-counter;
}

.answer-text :deep(ol li)::before {
    content: counter(list-counter);
    position: absolute;
    inset-inline-start: 0;
    top: 0;
    font-size: 0.8rem;
    font-weight: 600;
    color: #0e7490;
    background: #ecfeff;
    width: 18px;
    height: 18px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Bold text */
.answer-text :deep(strong) {
    font-weight: 600;
    color: #0f172a;
}

/* First paragraph after greeting */
.answer-text :deep(p:first-child) {
    font-size: 0.95rem;
    color: #334155;
}

/* TTS Button - Beautiful minimal design */
.tts-container {
    display: flex;
    align-items: center;
    margin-top: 8px;
}

.tts-btn {
    position: relative;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
}

.tts-btn:hover:not(:disabled) {
    background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
    transform: scale(1.08);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.tts-btn:active:not(:disabled) {
    transform: scale(0.95);
}

.tts-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.tts-btn.is-playing {
    background: linear-gradient(135deg, #0e7490 0%, #06b6d4 100%);
    box-shadow: 0 4px 14px rgba(14, 116, 144, 0.3);
}

.tts-btn.is-playing:hover {
    background: linear-gradient(135deg, #0c6580 0%, #0891b2 100%);
}

.tts-btn.is-loading {
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
}

.tts-icon {
    width: 16px;
    height: 16px;
    color: #475569;
    transition: color 0.2s ease;
}

.tts-btn.is-playing .tts-icon {
    color: white;
}

.tts-icon.spin {
    animation: spin 1s linear infinite;
    color: #0e7490;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Sound waves animation */
.sound-waves {
    position: absolute;
    display: flex;
    align-items: center;
    gap: 2px;
    inset-inline-end: -22px;
}

.wave {
    display: block;
    width: 3px;
    background: #0e7490;
    border-radius: 2px;
    animation: wave 0.5s ease-in-out infinite;
}

.wave-1 {
    height: 8px;
    animation-delay: 0s;
}

.wave-2 {
    height: 14px;
    animation-delay: 0.15s;
}

.wave-3 {
    height: 8px;
    animation-delay: 0.3s;
}

@keyframes wave {
    0%, 100% { transform: scaleY(0.5); opacity: 0.5; }
    50% { transform: scaleY(1); opacity: 1; }
}

.error-text { 
    color: #ef4444; 
    font-size: 12px; 
    display: block; 
    margin-top: 4px; 
}
</style>
