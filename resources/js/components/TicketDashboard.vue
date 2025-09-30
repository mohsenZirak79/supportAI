<template>
    <div class="ticket-app" dir="rtl">
        <!-- Toast Container -->
        <div class="toast-container">
            <div
                v-for="toast in toasts"
                :key="toast.id"
                :class="`toast toast-${toast.type}`"
                @click="removeToast(toast.id)"
            >
                <svg v-if="toast.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <svg v-else-if="toast.type === 'error'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <span>{{ toast.message }}</span>
            </div>
        </div>

        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center space-x-4 space-x-reverse">
                        <div class="bg-blue-600 p-2 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">سیستم تیکتینگ</h1>
                            <p class="text-sm text-gray-500">پنل مدیریت تیکت‌های شما</p>
                        </div>
                    </div>
                    <button
                        @click="showNewTicketForm = true"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2 space-x-reverse"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>تیکت جدید</span>
                    </button>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center">
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="mr-4">
                            <p class="text-sm font-medium text-gray-600">کل تیکت‌ها</p>
                            <p class="text-2xl font-bold text-gray-900">{{ tickets.length }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center">
                        <div class="bg-yellow-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="mr-4">
                            <p class="text-sm font-medium text-gray-600">در انتظار پاسخ</p>
                            <p class="text-2xl font-bold text-gray-900">{{ pendingTickets }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center">
                        <div class="bg-green-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="mr-4">
                            <p class="text-sm font-medium text-gray-600">پاسخ داده شده</p>
                            <p class="text-2xl font-bold text-gray-900">{{ answeredTickets }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center">
                        <div class="bg-red-100 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        <div class="mr-4">
                            <p class="text-sm font-medium text-gray-600">بسته شده</p>
                            <p class="text-2xl font-bold text-gray-900">{{ closedTickets }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8 border border-gray-100">
                <div class="flex flex-wrap gap-4 items-center">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">فیلتر بر اساس وضعیت</label>
                        <select v-model="statusFilter" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">همه</option>
                            <option value="pending">در انتظار پاسخ</option>
                            <option value="answered">پاسخ داده شده</option>
                            <option value="closed">بسته شده</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">فیلتر بر اساس بخش</label>
                        <select v-model="departmentFilter" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">همه بخش‌ها</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                {{ dept.name }}
                            </option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">جستجو</label>
                        <input
                            type="text"
                            v-model="searchQuery"
                            placeholder="جستجو در عنوان یا متن تیکت..."
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>
                </div>
            </div>

            <!-- Tickets List -->
            <div class="space-y-6">
                <div v-if="loading" class="text-center py-12">
                    <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-blue-600 border-t-transparent"></div>
                    <p class="mt-4 text-gray-600">در حال بارگذاری...</p>
                </div>
                <div v-else-if="filteredTickets.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">هیچ تیکتی یافت نشد</h3>
                    <p class="text-gray-500">تیکت جدیدی ایجاد کنید یا فیلترها را تغییر دهید</p>
                </div>

                <div
                    v-for="ticket in filteredTickets"
                    :key="ticket.id"
                    class="ticket-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
                >
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 space-x-reverse mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ ticket.title }}</h3>
                                    <span class="text-sm text-gray-500">#{{ ticket.id }}</span>
                                </div>
                                <div class="flex items-center space-x-4 space-x-reverse text-sm text-gray-600">
                  <span class="flex items-center">
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    {{ getDepartmentName(ticket.department) }}
                  </span>
                                    <span class="flex items-center">
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ formatDate(ticket.created_at) }}
                  </span>
                                    <span :class="getPriorityClass(ticket.priority)">
                    {{ getPriorityLabel(ticket.priority) }}
                  </span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3 space-x-reverse">
                <span :class="getStatusClass(ticket.status)" class="status-badge">
                  {{ getStatusLabel(ticket.status) }}
                </span>
                                <button
                                    @click="viewThread(ticket.id)"
                                    class="text-blue-600 hover:text-blue-800 transition-colors"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p class="text-gray-600 mb-4">{{ ticket.message.substring(0, 150) }}...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thread Modal -->
        <transition name="fade">
            <div v-if="selectedThread" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
                <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="p-6 border-b border-gray-200 relative">
                        <h2 class="text-xl font-bold text-gray-900">گفتگوی تیکت: {{ selectedThread.title }}</h2>
                        <button @click="closeThreadModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="p-6 space-y-4 max-h-96 overflow-y-auto">
                        <div v-for="msg in threadMessages" :key="msg.id" class="flex">
                            <div class="flex-shrink-0">
                                <div :class="`w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-medium ${
                  msg.sender_type === 'agent' ? 'bg-green-500' : 'bg-blue-500'
                }`">
                                    {{ msg.sender_type === 'agent' ? 'پشتیبان' : 'شما' }}
                                </div>
                            </div>
                            <div class="mr-3 flex-1">
                                <div :class="msg.sender_type === 'agent' ? 'bg-green-50 border border-green-200' : 'bg-white shadow-sm'" class="rounded-lg p-4">
                                    <p>{{ msg.message }}</p>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">{{ formatDate(msg.created_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Reply Form -->
                    <div v-if="canReplyToThread" class="p-6 border-t">
                        <form @submit.prevent="submitThreadReply" class="space-y-4">
              <textarea
                  v-model="threadReplyMessage"
                  rows="3"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="پاسخ خود را بنویسید..."
                  required
              ></textarea>
                            <button
                                type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors"
                            >
                                ارسال پاسخ
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </transition>

        <!-- New Ticket Modal -->
        <transition name="fade">
            <div v-if="showNewTicketForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
                <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h2 class="text-xl font-bold text-gray-900">ثبت تیکت جدید</h2>
                            <button @click="closeNewTicketForm" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <form @submit.prevent="submitNewTicket" class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">🏢 بخش پشتیبانی</label>
                            <select
                                v-model="newTicket.department"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none transition-colors"
                                required
                            >
                                <option value="">انتخاب بخش...</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.value">
                                    {{ dept.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">📝 عنوان تیکت</label>
                            <input
                                type="text"
                                v-model="newTicket.title"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none transition-colors"
                                placeholder="عنوان مشکل یا درخواست خود را وارد کنید..."
                                required
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">💬 توضیحات</label>
                            <textarea
                                v-model="newTicket.message"
                                rows="6"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none transition-colors resize-none"
                                placeholder="توضیحات کاملی از مشکل یا درخواست خود ارائه دهید..."
                                required
                            ></textarea>
                        </div>
                        <div class="flex justify-end space-x-3 space-x-reverse pt-4">
                            <button
                                type="button"
                                @click="closeNewTicketForm"
                                class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors"
                            >
                                انصراف
                            </button>
                            <button
                                type="submit"
                                :disabled="isSubmittingTicket"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors disabled:opacity-50"
                            >
                                <span v-if="!isSubmittingTicket">ثبت تیکت</span>
                                <span v-else>در حال ثبت...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

// --- State ---
const toasts = ref([]);
const showNewTicketForm = ref(false);
const loading = ref(true);
const selectedThread = ref(null);
const threadMessages = ref([]);
const threadReplyMessage = ref('');
const expandedTickets = ref([]);
const isSubmittingTicket = ref(false);
const isSubmittingReply = ref(false);
const statusFilter = ref('');
const departmentFilter = ref('');
const searchQuery = ref('');
const newTicket = ref({
    title: '',
    message: '',
    department: '',
    priority: 'normal'
});
const replyMessages = ref({});
const departments = ref([
    { id: 1, value: 'support_website', name: 'پشتیبانی وب سایت' },
    { id: 2, value: 'support_sales', name: 'پشتیبانی فروش' },
    { id: 3, value: 'support_admin', name: 'پشتیبانی اداری' },
    { id: 4, value: 'support_finance', name: 'پشتیبانی مالی' }
]);
const tickets = ref([]);

// --- Computed ---
const filteredTickets = computed(() => {
    let filtered = tickets.value;
    if (statusFilter.value) {
        filtered = filtered.filter(ticket => ticket.status === statusFilter.value);
    }
    if (departmentFilter.value) {
        filtered = filtered.filter(ticket => ticket.department === departmentFilter.value);
    }
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(ticket =>
            ticket.title.toLowerCase().includes(query) ||
            ticket.message.toLowerCase().includes(query)
        );
    }
    return filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
});

const pendingTickets = computed(() => tickets.value.filter(t => t.status === 'pending').length);
const answeredTickets = computed(() => tickets.value.filter(t => t.status === 'answered').length);
const closedTickets = computed(() => tickets.value.filter(t => t.status === 'closed').length);

// --- Methods ---
const addToast = (message, type = 'success') => {
    const id = Date.now();
    toasts.value.push({ id, message, type });
    setTimeout(() => removeToast(id), 5000);
};
const removeToast = (id) => {
    toasts.value = toasts.value.filter(t => t.id !== id);
};

const fetchTickets = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/v1/tickets');
        tickets.value = response.data.data;
    } catch (error) {
        addToast('خطا در بارگذاری تیکت‌ها', 'error');
    } finally {
        loading.value = false;
    }
};

const viewThread = async (rootId) => {
    try {
        const response = await axios.get(`/api/v1/tickets/${rootId}`);
        selectedThread.value = response.data.ticket;
        threadMessages.value = response.data.messages;
    } catch (error) {
        addToast('خطا در بارگذاری گفتگو', 'error');
    }
};

const closeThreadModal = () => {
    selectedThread.value = null;
    threadMessages.value = [];
    threadReplyMessage.value = '';
};

const canReplyToThread = computed(() => {
    if (threadMessages.value.length === 0) return false;
    const lastMessage = threadMessages.value[threadMessages.value.length - 1];
    return lastMessage.sender_type === 'agent' && lastMessage.status !== 'closed';
});

const submitThreadReply = async () => {
    if (!selectedThread.value) return;
    try {
        await axios.post(`/api/v1/tickets/${selectedThread.value.id}/messages`, {
            message: threadReplyMessage.value
        });
        addToast('پاسخ شما ارسال شد');
        await viewThread(selectedThread.value.id);
        threadReplyMessage.value = '';
    } catch (error) {
        addToast('خطا در ارسال پاسخ', 'error');
    }
};

const submitNewTicket = async () => {
    isSubmittingTicket.value = true;
    try {
        const response = await axios.post('/api/v1/tickets', newTicket.value);
        tickets.value.unshift(response.data);
        closeNewTicketForm();
        addToast(`تیکت شما با موفقیت ثبت شد. شماره پیگیری: #${response.data.id}`);
    } catch (error) {
        addToast('خطا در ثبت تیکت', 'error');
    } finally {
        isSubmittingTicket.value = false;
    }
};

const closeNewTicketForm = () => {
    showNewTicketForm.value = false;
    newTicket.value = { title: '', message: '', department: '', priority: 'normal' };
};

const getDepartmentName = (deptValue) => {
    const dept = departments.value.find(d => d.value === deptValue);
    return dept ? dept.name : 'نامشخص';
};

const getStatusLabel = (status) => {
    const labels = { pending: 'در انتظار پاسخ', answered: 'پاسخ داده شده', closed: 'بسته شده' };
    return labels[status] || status;
};

const getStatusClass = (status) => {
    const classes = { pending: 'bg-yellow-100 text-yellow-800', answered: 'bg-green-100 text-green-800', closed: 'bg-gray-100 text-gray-800' };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getPriorityLabel = (priority) => {
    const labels = { low: 'کم', normal: 'معمولی', high: 'بالا', urgent: 'فوری' };
    return labels[priority] || priority;
};

const getPriorityClass = (priority) => {
    const classes = { low: 'text-green-600', normal: 'text-gray-600', high: 'text-yellow-600', urgent: 'text-red-600' };
    return classes[priority] || 'text-gray-600';
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('fa-IR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// --- Lifecycle ---
onMounted(() => {
    fetchTickets();
});
</script>

<style scoped>
.ticket-app {
    font-family: 'Vazirmatn', sans-serif;
    background-color: #f9fafb;
    min-height: 100vh;
}
.toast-container {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 10000;
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: 100%;
    max-width: 500px;
    padding: 0 16px;
}
.toast {
    padding: 12px 16px;
    border-radius: 8px;
    color: white;
    font-weight: 500;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 8px;
    animation: toastSlideIn 0.3s ease-out;
}
@keyframes toastSlideIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
.toast-success { background-color: #10b981; }
.toast-error { background-color: #ef4444; }
.toast-warning { background-color: #f59e0b; }
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
.ticket-card {
    transition: all 0.3s ease;
}
.ticket-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}
</style>
