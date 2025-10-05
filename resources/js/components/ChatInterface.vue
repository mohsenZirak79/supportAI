<template>
    <HandoffModal
        :is-open="isHandoffModalOpen"
        :roles="availableRoles"
        @close="isHandoffModalOpen = false"
        @submit="handleHandoffSubmit"
    />
    <div class="chat-app" dir="rtl">
        <!-- نوار بالا -->
        <header class="chat-header">
            <div class="header-content">
                <h1 @click="editCurrentTitle">{{ activeChat?.title || 'چت با هوش مصنوعی' }}</h1>
                <button @click="goToTickets" class="nav-btn">تیکت‌ها</button>
            </div>
        </header>

        <div class="chat-container">
            <!-- سایدبار چت‌ها -->
            <aside class="sidebar">
                <div class="new-chat-btn" @click="startNewChat">
                    + چت جدید
                </div>
                <div class="chat-list">
                    <div
                        v-for="chat in chats"
                        :key="chat.id"
                        class="chat-item"
                        :class="{ active: chat.id === activeChatId }"
                        @click="setActiveChat(chat.id)"
                    >
                        <span @click.stop="editTitle(chat)">{{ chat.title }}</span>
                        <button @click.stop="deleteChat(chat.id)" class="delete-btn">×</button>
                    </div>
                </div>
            </aside>

            <!-- ناحیه چت اصلی -->
            <main class="chat-main" v-if="activeChatId">
                <div class="messages-container" ref="messagesContainer">
                    <div
                        v-for="(message, index) in activeChat?.messages || []"
                        :key="index"
                        class="message"
                        :class="{ 'user-message': message.sender === 'user', 'bot-message': message.sender === 'bot' }"
                    >
                        <div class="message-bubble" @click="onBubbleClick(message)">
                            {{ message.text }}

                            <div v-if="message.voiceUrl" class="voice-player" @click.stop="playVoice(message.id)">
                                <audio :ref="el => registerAudioRef(message.id, el)" :src="message.voiceUrl" preload="none" controls></audio>
                            </div>

                            <div class="message-meta">
                                <span class="timestamp">{{ formatDate(message.created_at) }}</span>
                                <div class="message-actions">
                                    <button @click="copyText(message.text)" title="کپی متن">📋</button>
                                    <button @click="showHandoffModal(message)" title="ارجاع به پشتیبانی">📤</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="loading" class="message bot-message">
                        <div class="message-bubble loading">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </div>

                <!-- فرم ارسال پیام -->
                <form @submit.prevent="sendMessage" class="input-form">
                    <!-- حالت ضبط صدا -->
                    <div v-if="isRecording" class="recording-ui">
                        <div class="waveform">
                            <div v-for="n in 20" :key="n" class="bar" :style="{ height: getBarHeight(n) + 'px' }"></div>
                        </div>
                        <div class="recording-controls">
                            <button type="button" @click="cancelRecording" class="cancel-btn">✕</button>
                            <button type="button" @click="sendRecording" class="send-btn">✓</button>
                        </div>
                        <div class="recording-timer">{{ formatTimer(recordingTime) }}</div>
                    </div>

                    <!-- حالت متنی -->
                    <div v-else class="text-input-area">
                    <textarea
                        v-model="inputMessage"
                        placeholder="پیام خود را بنویسید..."
                        rows="1"
                        @input="autoResize"
                        ref="textarea"
                        :disabled="loading"
                    ></textarea>
                                        <div class="input-actions">
                                            <button type="button" @click="startRecording" class="mic-btn" :disabled="loading">🎤</button>
                                            <button type="submit" :disabled="!inputMessage.trim() || loading">ارسال</button>
                                        </div>
                                    </div>
                                </form>
                <!--          <textarea-->
                <!--              v-model="inputMessage"-->
                <!--              placeholder="پیام خود را بنویسید..."-->
                <!--              rows="1"-->
                <!--              @input="autoResize"-->
                <!--              ref="textarea"-->
                <!--              :disabled="loading"-->
                <!--          ></textarea>-->
                <!--                    <button type="submit" :disabled="!inputMessage.trim() || loading">-->
                <!--                        ارسال-->
                <!--                    </button>-->
                <!--                </form>-->
            </main>

            <main v-else class="chat-main empty-state">
                <div class="empty-content">
                    <h2>چت جدیدی شروع کنید</h2>
                    <p>برای شروع گفت‌وگو، روی «چت جدید» کلیک کنید.</p>
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import {ref, computed, nextTick, onMounted, onUnmounted} from 'vue';
import HandoffModal from './HandoffModal.vue';
const isHandoffModalOpen = ref(false);
const selectedMessageForHandoff = ref(null);
// --- State ---
const isRecording = ref(false);
const recordingTime = ref(0);
const recordingInterval = ref(null);
const mediaRecorder = ref(null);
const audioChunks = ref([]);
const availableRoles = ref([]);
const fetchDepartments = async () => {  // ← این تابع رو کامل اضافه کن
    try {
        const response = await fetch('/api/v1/support-roles', {
            headers: { 'Accept': 'application/json' }
        });
        if (response.ok) {
            const data = await response.json();  // یا data.data اگر API فرق داره
            availableRoles.value = data;  // array objects مثل [{id: "...", name: "..."}]
            console.log('Roles loaded:', availableRoles.value);  // برای debug
        } else {
            console.error('خطا در بارگذاری roles');
        }
    } catch (error) {
        console.error('خطا در fetch departments:', error);
        // اختیاری: alert('خطا در بارگذاری بخش‌ها');
    }
};
const formatDate = (isoString) => {
    if (!isoString) return '';
    const date = new Date(isoString);
    return date.toLocaleString('fa-IR', {
        hour: '2-digit',
        minute: '2-digit',
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};
// --- State ---
const chats = ref([]); // لیست چت‌ها از API
const activeChatId = ref(null);
const inputMessage = ref('');
const loading = ref(false);
const textarea = ref(null);
const messagesContainer = ref(null);

// فرض می‌کنیم AI User با این ایمیل ثبت شده
const AI_EMAIL = 'ai@system.local';
let aiUserId = null;

// --- Methods ---
const startRecording = async () => {
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

        // شروع تایمر (هر 100ms یک بار آپدیت می‌شه برای smooth بودن)
        recordingInterval.value = setInterval(() => {
            recordingTime.value += 100;
            // محدودیت 10 دقیقه (600,000 میلی‌ثانیه)
            if (recordingTime.value >= 600000) {
                sendRecording();
            }
        }, 100);
    } catch (error) {
        alert('دسترسی به میکروفون رد شد یا پشتیبانی نمی‌شود.');
        console.error('Recording error:', error);
    }
};

const cancelRecording = () => {
    if (mediaRecorder.value && mediaRecorder.value.state !== 'inactive') {
        mediaRecorder.value.stop();
        mediaRecorder.value.stream.getTracks().forEach(track => track.stop());
    }
    cleanupRecording();
};

const sendRecording = () => {
    if (mediaRecorder.value && mediaRecorder.value.state !== 'inactive') {
        mediaRecorder.value.stop();
    }
    cleanupRecording();
};

const cleanupRecording = () => {
    if (recordingInterval.value) {
        clearInterval(recordingInterval.value);
        recordingInterval.value = null;
    }
    isRecording.value = false;
    recordingTime.value = 0;
};

const uploadVoice = async (blob) => {
    try {
        // 1) آپلود فایل
        const formData = new FormData();
        formData.append('file', blob, 'recording.webm');
        formData.append('collection', 'message_voices');
        const uploadRes = await fetch('/api/v1/files', { method: 'POST', body: formData });
        if (!uploadRes.ok) throw new Error('آپلود فایل شکست خورد');
        const { file_id } = await uploadRes.json();

        // 2) ارسال پیام با media_ids
        const messageRes = await fetch(`/api/v1/conversations/${activeChatId.value}/messages`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ content: '', media_ids: [file_id], media_kind: 'voice' })
        });
        if (!messageRes.ok) throw new Error('ارسال پیام شکست خورد');

        const { user_message } = await messageRes.json(); // ← این مهم است
        const chat = chats.value.find(c => c.id === activeChatId.value);
        if (!chat) return;

        // 3) پیام را به UI اضافه کن
        chat.messages.push({
            id: user_message.id,
            sender: 'user',
            text: user_message.content || '',
            created_at: user_message.created_at
        });

        // 4) مدیای همین پیام را بگیر و voiceUrl ست کن
        const r = await fetch(`/api/v1/messages/${user_message.id}/media`, { headers: { 'Accept': 'application/json' } });
        if (r.ok) {
            const { data: media } = await r.json();
            const voice = (media || []).find(m => m.collection === 'message_voices' || (m.mime || '').startsWith('audio/'));
            if (voice) {
                // پیدا کردن پیام تازه اضافه‌شده و تزریق voiceUrl
                const msg = chat.messages.find(m => m.id === user_message.id);
                if (msg) msg.voiceUrl = voice.url;
            }
            await nextTick(); // تا <audio> رندر شود و ref ثبت شود
        }
    } catch (error) {
        alert('خطا در ارسال صدا');
        console.error('Upload error:', error);
    }
};


// برای waveform پویا
const getBarHeight = (index) => {
    if (!isRecording.value) return 4;
    // شبیه‌سازی ارتفاع تصادفی برای نمایش
    return 10 + Math.random() * 30;
};

// فرمت تایمر: mm:ss.ms
const formatTimer = (ms) => {
    const totalSeconds = Math.floor(ms / 1000);
    const minutes = Math.floor(totalSeconds / 60).toString().padStart(2, '0');
    const seconds = (totalSeconds % 60).toString().padStart(2, '0');
    const centiseconds = Math.floor((ms % 1000) / 10).toString().padStart(2, '0');
    return `${minutes}:${seconds}.${centiseconds}`;
};

// پاک کردن منابع هنگام خروج
onUnmounted(() => {
    if (recordingInterval.value) {
        clearInterval(recordingInterval.value);
    }
    if (mediaRecorder.value) {
        mediaRecorder.value.stream?.getTracks().forEach(track => track.stop());
    }
});
// const scrollToBottom = () => {
//     if (messagesContainer.value) {
//         messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
//     }
// };
const scrollToBottom = () => {
    if (messagesContainer.value) {
        messagesContainer.value.scrollTo({
            top: messagesContainer.value.scrollHeight,
            behavior: 'smooth'
        });
    }
};


const activeChat = computed(() => {
    return chats.value.find(chat => chat.id === activeChatId.value) || null;
});
const autoResize = () => {
    const el = textarea.value;
    if (el) {
        el.style.height = 'auto';
        el.style.height = Math.min(el.scrollHeight, 150) + 'px';
    }
};

// لود چت‌ها از API
const loadChats = async () => {
    try {
        const res = await fetch('/api/v1/conversations', {
            headers: {'Accept': 'application/json'}
        });
        if (res.ok) {
            const {data} = await res.json();
            chats.value = data.map(chat => ({
                id: chat.id,
                title: chat.title,
                messages: []
            }));
            if (chats.value.length > 0 && !activeChatId.value) {
                setActiveChat(chats.value[0].id);
            }
        }
    } catch (e) {
        console.error('Failed to load chats', e);
    }
};

// لود پیام‌ها
const loadMessages = async (chatId) => {
    try {
        const res = await fetch(`/api/v1/conversations/${chatId}/messages`);
        if (res.ok) {
            const { data } = await res.json();
            const chat = chats.value.find(c => c.id === chatId);
            if (chat) {
                chat.messages = data.map(msg => ({
                    id: msg.id,
                    sender: msg.sender_type === 'ai' ? 'bot' : 'user',
                    text: msg.content,
                    created_at: msg.created_at
                }));

                // ⬇️ همین بلاک را «اینجا» اضافه کن:
                await Promise.all(
                    (chat.messages || []).map(async (msg) => {
                        try {
                            const r = await fetch(`/api/v1/messages/${msg.id}/media`, { headers: { 'Accept': 'application/json' } });
                            if (r.ok) {
                                const { data: media } = await r.json();
                                msg.media = media || [];
                                const voice = msg.media.find(m => m.collection === 'message_voices' || (m.mime || '').startsWith('audio/'));
                                if (voice) {
                                    msg.voiceUrl = voice.url;
                                }
                            } else {
                                msg.media = [];
                            }
                        } catch {
                            msg.media = [];
                        }
                    })
                );
            }
        }
    } catch (e) {
        console.error('Failed to load messages', e);
    }
};

// ایجاد چت جدید
const startNewChat = async () => {
    try {
        const res = await fetch('/api/v1/conversations', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({title: 'چت جدید'})
        });
        if (res.ok) {
            const newChat = await res.json();
            chats.value.unshift({
                id: newChat.id,
                title: newChat.title,
                messages: []
            });
            setActiveChat(newChat.id);
        }
    } catch (e) {
        alert('خطا در ایجاد چت جدید');
    }
};

// فعال‌سازی چت
const setActiveChat = async (id) => {
    activeChatId.value = id;
    await loadMessages(id);
    await nextTick();
    scrollToBottom();
};

// ارسال پیام
const sendMessage = async () => {
    if (!inputMessage.value.trim() || loading.value) return;

    const userMsg = {
        sender: 'user',
        text: inputMessage.value.trim()
    };

    const activeChat = chats.value.find(c => c.id === activeChatId.value);
    if (!activeChat) return;

    // اضافه کردن پیام کاربر به UI
    activeChat.messages.push(userMsg);
    inputMessage.value = '';
    await nextTick();
    scrollToBottom();

    loading.value = true;

    try {
        const res = await fetch(`/api/v1/conversations/${activeChatId.value}/messages`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({content: userMsg.text})
        });

        if (res.ok) {
            const {ai_message, conversation} = await res.json();

            // آپدیت عنوان چت اگر تغییر کرده
            if (conversation.title && conversation.title !== activeChat.title) {
                activeChat.title = conversation.title;
            }

            // اضافه کردن پاسخ AI
            activeChat.messages.push({
                sender: 'bot',
                text: ai_message.content
            });

            // ذخیره aiUserId برای تشخیص آینده
            if (!aiUserId) aiUserId = ai_message.sender_id;
        } else {
            throw new Error('خطا در ارسال پیام');
        }
    } catch (error) {
        activeChat.messages.push({
            sender: 'bot',
            text: '❌ خطایی رخ داد. لطفاً دوباره تلاش کنید.'
        });
    } finally {
        loading.value = false;
        await nextTick();
        scrollToBottom();
    }
};

// ویرایش عنوان
const editTitle = (chat) => {
    const newTitle = prompt('عنوان جدید را وارد کنید:', chat.title);
    if (newTitle && newTitle.trim() && newTitle !== chat.title) {
        updateChatTitle(chat.id, newTitle.trim());
    }
};

const editCurrentTitle = () => {
    const chat = chats.value.find(c => c.id === activeChatId.value);
    if (chat) editTitle(chat);
};

const updateChatTitle = async (chatId, title) => {
    try {
        const res = await fetch(`/api/v1/conversations/${chatId}/title`, {
            method: 'PATCH',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({title})
        });
        if (res.ok) {
            const updated = await res.json();
            const chat = chats.value.find(c => c.id === chatId);
            if (chat) chat.title = updated.title;
        }
    } catch (e) {
        alert('خطا در به‌روزرسانی عنوان');
    }
};

// حذف چت
const deleteChat = async (chatId) => {
    if (!confirm('آیا مطمئنید می‌خواهید این چت را حذف کنید؟')) return;

    try {
        const res = await fetch(`/api/v1/conversations/${chatId}`, {method: 'DELETE'});
        if (res.ok) {
            chats.value = chats.value.filter(c => c.id !== chatId);
            if (activeChatId.value === chatId) {
                activeChatId.value = chats.value.length ? chats.value[0].id : null;
            }
        }
    } catch (e) {
        alert('خطا در حذف چت');
    }
};
const copyText = (text) => {
    navigator.clipboard.writeText(text).then(() => {
        // نمایش toast موفقیت (اختیاری)
    });
};
const goToTickets = () => {
    window.location.href = '/ticket';
};
const showHandoffModal = (message) => {
    selectedMessageForHandoff.value = message;
    isHandoffModalOpen.value = true;
};
const handleHandoffSubmit = async (data) => {
    try {
        if (!selectedMessageForHandoff.value?.id) {
            alert('پیام انتخاب‌شده نامعتبر است.');
            return;
        }

        const res = await fetch(
            `/api/v1/conversations/${selectedMessageForHandoff.value.id}/handoff`,
            {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data) // { target_role, reason }
            }
        );

        if (!res.ok) throw new Error('خطا در ارجاع');

        // موفق
        alert('ارجاع با موفقیت انجام شد!');
        isHandoffModalOpen.value = false;
        selectedMessageForHandoff.value = null;
    } catch (e) {
        alert('خطا در ارجاع: ' + e.message);
    }
};
const audioRefs = ref({});
let currentlyPlayingId = null;
const registerAudioRef = (id, el) => {
    if (el) audioRefs.value[id] = el;
};
const playVoice = async (id) => {
    const el = audioRefs.value[id];
    if (!el) return;

    // توقف صدای قبلی
    if (currentlyPlayingId && currentlyPlayingId !== id) {
        const prev = audioRefs.value[currentlyPlayingId];
        if (prev && !prev.paused) prev.pause();
    }
    currentlyPlayingId = id;

    // آماده‌سازی برای پخش
    if (el.readyState < 2) { // HAVE_CURRENT_DATA
        el.load();
        await new Promise(res => {
            const onReady = () => { el.removeEventListener('canplay', onReady); res(); };
            el.addEventListener('canplay', onReady, { once: true });
        });
    }
    el.currentTime = 0;
    try {
        await el.play();
    } catch (e) {
        // بعضی مرورگرها سخت‌گیرند: اگر از روی bubble کلیک کردی و باز هم خطا داد،
        // یک fallback: simulate click on the control
        console.debug('play() failed, user gesture required?', e);
    }
};

const onBubbleClick = (message) => {
    if (message.voiceUrl) playVoice(message.id);
};


// --- Lifecycle ---
onMounted(() => {
    loadChats();
    fetchDepartments();
});
</script>

<style scoped>
/* همان استایل قبلی شما — بدون تغییر */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.chat-app {
    font-family: 'Vazirmatn', 'Segoe UI', Tahoma, sans-serif;
    background-color: #f9fafb;
    height: 100vh;
    display: flex;
    flex-direction: column;
}

.chat-header {
    background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
    color: white;
    padding: 16px 24px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.chat-header h1 {
    font-size: 1.5rem;
    font-weight: 600;
    cursor: pointer;
}

.chat-container {
    display: flex;
    flex: 1;
    overflow: hidden;
}

.sidebar {
    width: 260px;
    background-color: white;
    border-left: 1px solid #eaeaea;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}

.new-chat-btn {
    padding: 14px 20px;
    background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
    color: white;
    font-weight: 600;
    cursor: pointer;
    text-align: center;
    margin: 12px;
    border-radius: 8px;
    transition: opacity 0.2s;
}

.new-chat-btn:hover {
    opacity: 0.9;
}

.chat-list {
    padding: 8px 0;
}

.chat-item {
    padding: 12px 20px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    color: #333;
    transition: background 0.2s;
    font-size: 0.95rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-item:hover {
    background-color: #f5f7ff;
}

/*.chat-item.active {
    background-color: #eef2ff;
    border-right: 3px solid #2575fc;
    font-weight: 600;
}*/
.chat-item.active {
    background-color: #eef2ff;
    border-right: 3px solid transparent;
    border-image: linear-gradient(180deg, #6a11cb, #2575fc) 1;
}

.delete-btn {
    background: none;
    border: none;
    color: #ff4d4f;
    font-size: 1.2rem;
    cursor: pointer;
    margin-right: 8px;
    opacity: 0;
    transition: opacity 0.2s;
}

.chat-item:hover .delete-btn {
    opacity: 1;
}

.chat-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    background-color: #ffffff;
}

.empty-state {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #666;
}

.empty-content h2 {
    font-size: 1.4rem;
    margin-bottom: 12px;
}

.empty-content p {
    color: #888;
}

.messages-container {
    flex: 1;
    padding: 24px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.message {
    display: flex;
    justify-content: flex-end;
}

.message.user-message {
    justify-content: flex-start;
}

.message.bot-message {
    justify-content: flex-end;
}

.message-bubble {
    padding: 12px 16px;
    border-radius: 18px;
    max-width: 80%;
    word-break: break-word;
    line-height: 1.5;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    animation: fadeInUp 0.3s ease;
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}


.user-message .message-bubble {
    /*background: linear-gradient(135deg, #2575fc 0%, #6a11cb 100%);*/
    background-color: #f1f5f9;
    color: #333;
    /*color: white;*/
    border-bottom-right-radius: 4px;
}

.bot-message .message-bubble {
    background-color: #f1f5f9;
    color: #333;
    border-bottom-left-radius: 4px;
}

.loading {
    display: flex;
    align-items: center;
    gap: 4px;
    background: #f1f5f9 !important;
    color: #666;
}

.loading span {
    width: 8px;
    height: 8px;
    background-color: #94a3b8;
    border-radius: 50%;
    display: inline-block;
    animation: bounce 1.4s infinite ease-in-out both;
}

.loading span:nth-child(1) {
    animation-delay: -0.32s;
}

.loading span:nth-child(2) {
    animation-delay: -0.16s;
}

@keyframes bounce {
    0%, 80%, 100% {
        transform: scale(0);
    }
    40% {
        transform: scale(1);
    }
}

.input-form {
    display: flex;
    padding: 16px;
    background: white;
    border-top: 1px solid #eaeaea;
    gap: 12px;
    flex-direction: column;
}

.text-input-area {
    display: flex;
    gap: 12px;
    align-items: flex-end;
}

.text-input-area textarea {
    flex: 1;
    padding: 12px 16px;
    border: 1px solid #d1d5db;
    border-radius: 24px;
    resize: none;
    font-size: 1rem;
    font-family: inherit;
    outline: none;
    max-height: 150px;
}

.input-actions {
    display: flex;
    gap: 8px;
}

.input-form textarea:focus {
    border-color: #2575fc;
    box-shadow: 0 0 0 3px rgba(37, 117, 252, 0.2);
}

.input-actions button {
    padding: 12px 20px;
    border: none;
    border-radius: 24px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s;
}

.input-actions .mic-btn {
    background: #f1f5f9;
    color: #4b5563;
}

.input-actions button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* --- حالت ضبط صدا --- */
.recording-ui {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    gap: 12px;
}

.waveform {
    display: flex;
    gap: 2px;
    height: 40px;
    align-items: flex-end;
    justify-content: center;
    width: 100%;
}

/*.bar {
    width: 6px;
    background: linear-gradient(to top, #2575fc, #6a11cb);
    border-radius: 3px;
    transition: height 0.1s ease;
}*/
.bar {
    background: linear-gradient(to top, #2575fc, #6a11cb);
    box-shadow: 0 0 8px rgba(37,117,252,0.4);
}

.recording-controls {
    display: flex;
    gap: 20px;
}

.recording-controls button {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid #e2e8f0;
    background: white;
    font-size: 1.2rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cancel-btn {
    color: #ef4444;
    border-color: #fecaca;
}

.send-btn {
    color: #10b981;
    border-color: #bbf7d0;
}

.recording-timer {
    font-size: 0.875rem;
    color: #64748b;
}
.input-form button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .sidebar {
        width: 80px;
    }

    .chat-item span {
        display: none;
    }

    .chat-item::before {
        content: "💬";
        font-size: 1.2rem;
    }

    .new-chat-btn span {
        display: none;
    }

    .new-chat-btn::before {
        content: "+";
        font-size: 1.5rem;
    }
}

.message-meta {
    display: flex;
    justify-content: space-between;
    margin-top: 6px;
    font-size: 0.75rem;
    color: #666;
}

.message-actions button {
    background: none;
    border: none;
    cursor: pointer;
    opacity: 0;
    transition: opacity 0.2s;
}

.message-bubble:hover .message-actions button {
    opacity: 1;
}


/* --- فرم ارسال --- */
.input-form {
    display: flex;
    padding: 16px;
    background: white;
    border-top: 1px solid #eaeaea;
    gap: 12px;
    align-items: flex-end;
}

.text-input-area {
    display: flex;
    gap: 12px;
    align-items: center;
    width: 100%;
}

.text-input-area textarea {
    flex: 1;
    padding: 12px 16px;
    border: 1px solid #d1d5db;
    border-radius: 24px;
    resize: none;
    font-size: 1rem;
    font-family: inherit;
    outline: none;
    max-height: 150px;
    min-height: 40px;
    transition: border-color 0.2s;
}

.text-input-area textarea:focus {
    border-color: #2575fc;
    box-shadow: 0 0 0 3px rgba(37, 117, 252, 0.2);
}

.input-actions {
    display: flex;
    gap: 8px;
}

.input-actions button {
    padding: 12px 20px;
    border: none;
    border-radius: 24px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s;
}

.input-actions .mic-btn {
    background: #f1f5f9;
    color: #4b5563;
}

.input-actions button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* --- حالت ضبط صدا --- */
.recording-ui {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    gap: 12px;
}

.waveform {
    display: flex;
    gap: 2px;
    height: 40px;
    align-items: flex-end;
    justify-content: center;
    width: 100%;
}

.bar {
    width: 6px;
    background: linear-gradient(to top, #2575fc, #6a11cb);
    border-radius: 3px;
    transition: height 0.1s ease;
}

.recording-controls {
    display: flex;
    gap: 20px;
}

.recording-controls button {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid #e2e8f0;
    background: white;
    font-size: 1.2rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cancel-btn {
    color: #ef4444;
    border-color: #fecaca;
}

.send-btn {
    color: #10b981;
    border-color: #bbf7d0;
}

.recording-timer {
    font-size: 0.875rem;
    color: #64748b;
}
.bot-message .message-bubble::before {
    content: "🤖";
    margin-left: 6px;
}
.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 16px;
}

.nav-btn {
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
    padding: 6px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.nav-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-1px);
}
</style>
