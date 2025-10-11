<template>
    <div class="ticket-app" dir="rtl">
        <!-- Toast Container -->
        <!--        <div class="toast-container">
                    <div
                        v-for="toast in toasts"
                        :key="toast.id"
                        :class="`toast toast-${toast.type}`"
                        @click="removeToast(toast.id)"
                    >
                        <svg v-if="toast.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <svg v-else-if="toast.type === 'error'" class="w-5 h-5" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <span>{{ toast.message }}</span>
                    </div>
                </div>-->

        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center space-x-4 space-x-reverse">
                    <!-- می‌تونی آیکون/عنوان اضافه کنی -->
                </div>

                <!-- دکمه‌ها: نسخه براق‌شده -->
                <div class="flex items-center space-x-3 space-x-reverse py-3">
                    <!-- Chat -->
                    <button
                        @click="goToChat"
                        class="relative group px-4 py-2 rounded-xl font-medium transition-all
                   bg-gradient-to-r from-sky-50 via-blue-50 to-indigo-50
                   border border-blue-200 text-blue-700
                   hover:from-sky-100 hover:via-blue-100 hover:to-indigo-100
                   hover:border-blue-300 hover:text-blue-800
                   shadow-sm hover:shadow-md"
                    >
                        <span
                            class="absolute inset-0 rounded-xl blur-sm opacity-0 group-hover:opacity-100 transition-opacity bg-gradient-to-r from-blue-200/40 to-indigo-200/40"></span>
                        <span class="relative flex items-center space-x-2 space-x-reverse">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
              </svg>
              <span>چت</span>
            </span>
                    </button>

                    <!-- New Ticket -->
                    <button
                        @click="showNewTicketForm = true"
                        class="relative group px-4 py-2 rounded-xl font-semibold text-white transition-all
                   bg-gradient-to-r from-blue-600 to-indigo-600
                   hover:from-blue-700 hover:to-indigo-700
                   shadow-md hover:shadow-lg"
                    >
                        <span
                            class="absolute inset-0 rounded-xl blur-sm opacity-0 group-hover:opacity-100 transition-opacity bg-white/10"></span>
                        <span class="relative flex items-center space-x-2 space-x-reverse">
                          <svg class="w-10 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 4v16m8-8H4"></path>
                          </svg>
                          <span>تیکت جدید</span>
            </span>
                    </button>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-4">
                <div class="stat-card glossy p-6">
                    <div class="flex items-center">
                        <div
                            class="icon-wrap bg-gradient-to-br from-blue-100 to-cyan-100 p-3 rounded-xl ring-1 ring-blue-200/50">
                            <svg class="w-10 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="mr-4">
                            <p class="text-sm font-medium text-gray-600">کل تیکت‌ها</p>
                            <p class="text-2xl font-bold text-gray-900">{{ tickets.length }}</p>
                        </div>
                    </div>
                </div>

                <div class="stat-card glossy p-6">
                    <div class="flex items-center">
                        <div
                            class="icon-wrap bg-gradient-to-br from-amber-100 to-yellow-100 p-3 rounded-xl ring-1 ring-amber-200/50">
                            <svg class="w-10 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="mr-4">
                            <p class="text-sm font-medium text-gray-600">در انتظار پاسخ</p>
                            <p class="text-2xl font-bold text-gray-900">{{ pendingTickets }}</p>
                        </div>
                    </div>
                </div>

                <div class="stat-card glossy p-6">
                    <div class="flex items-center">
                        <div
                            class="icon-wrap bg-gradient-to-br from-emerald-100 to-green-100 p-3 rounded-xl ring-1 ring-emerald-200/50">
                            <svg class="w-10 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="mr-4">
                            <p class="text-sm font-medium text-gray-600">پاسخ داده شده</p>
                            <p class="text-2xl font-bold text-gray-900">{{ answeredTickets }}</p>
                        </div>
                    </div>
                </div>

                <div class="stat-card glossy p-6">
                    <div class="flex items-center">
                        <div
                            class="icon-wrap bg-gradient-to-br from-rose-100 to-red-100 p-3 rounded-xl ring-1 ring-rose-200/50">
                            <svg class="w-10 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"></path>
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
            <div class="glossy p-6 mb-4">
                <div class="flex flex-wrap gap-4 items-center">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">فیلتر بر اساس وضعیت</label>
                        <select v-model="statusFilter"
                                class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">همه</option>
                            <option value="pending">در انتظار پاسخ</option>
                            <option value="answered">پاسخ داده شده</option>
                            <option value="closed">بسته شده</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">فیلتر بر اساس بخش</label>
                        <select v-model="departmentFilter"
                                class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
                    <div
                        class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-blue-600 border-t-transparent"></div>
                    <p class="mt-4 text-gray-600">در حال بارگذاری...</p>
                </div>

                <div v-else-if="filteredTickets.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">هیچ تیکتی یافت نشد</h3>
                    <p class="text-gray-500">تیکت جدیدی ایجاد کنید یا فیلترها را تغییر دهید</p>
                </div>

                <div
                    v-for="ticket in filteredTickets"
                    :key="ticket.id"
                    class="ticket-card glossy borderless-gradient overflow-hidden"
                >
                    <div class="p-4">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 space-x-reverse mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ ticket.title }}</h3>
                                    <span class="text-sm text-gray-500"></span>
                                </div>
                                <div class="flex items-center space-x-4 space-x-reverse text-sm text-gray-600">
                  <span class="flex items-center">
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    {{ getDepartmentName(ticket.department) }}
                  </span>
                                    <span class="flex items-center">
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ formatDate(ticket.created_at) }}
                  </span>
                                    <span class="priority" :class="getPriorityClass(ticket.priority)">
                    {{ getPriorityLabel(ticket.priority) }}
                  </span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3 space-x-reverse">
                <span :class="['badge', getStatusClass(ticket.status)]">
                  {{ getStatusLabel(ticket.status) }}
                </span>
                                <button
                                    @click="viewThread(ticket.id)"
                                    class="relative group bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-1 rounded-lg font-medium transition-colors flex items-center space-x-2 space-x-reverse shadow-sm hover:shadow-md"
                                >
                                    <span
                                        class="absolute inset-0 rounded-lg blur-[2px] opacity-0 group-hover:opacity-100 transition-opacity bg-white/30"></span>
                                    <span class="relative flex items-center space-x-2 space-x-reverse">
                    <svg class="w-10 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <span>نمایش گفتگو</span>
                  </span>
                                </button>
                            </div>
                        </div>
                        <p class="text-gray-600 mb-4">{{ ticket.message.substring(0, 150) }}...</p>

                        <div v-if="(ticket.attachments_count ?? 0) > 0"
                             class="flex items-center text-sm text-gray-500 mb-4">
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                            </svg>
                            {{ ticket.attachments_count }} فایل پیوست
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thread Modal -->
        <!-- Thread Modal -->
        <transition name="fade">
            <div
                v-if="selectedThread"
                class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-50"
                @click.self="closeThreadModal"
            >
                <!-- ظرف مدال: سه ردیف -->
                <div
                    class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl h-[85vh] overflow-hidden grid grid-rows-[auto,1fr,auto] relative"
                    @click.stop
                >
                    <!-- Header -->
                    <div class="p-5 border-b border-gray-200 flex items-center justify-center relative">
                        <button
                            @click="closeThreadModal"
                            class="absolute top-4 left-4 close-btn text-red-500 hover:text-red-700"
                            aria-label="close"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                        <h2 class="text-lg md:text-xl font-extrabold text-gray-900 text-center">
                            گفتگوی تیکت: {{ selectedThread.title }}
                        </h2>
                    </div>

                    <!-- Messages Scroll Area -->
                    <div class="overflow-y-auto bg-gray-50 p-4 md:p-6" id="thread-scroll">
                        <div v-for="msg in threadMessages" :key="msg.id" class="mb-4">
                            <div class="flex items-start gap-3" :class="isSupport(msg.sender_type) ? 'flex-row' : 'flex-row-reverse'">
                                <!-- آواتار ساده: «پشتیبان» یا «شما» -->
                                <div
                                    class="w-9 h-9 shrink-0 rounded-full flex items-center justify-center text-xs font-bold text-white"
                                    :class="isSupport(msg.sender_type) ? 'bg-emerald-500' : 'bg-blue-500'"
                                >
                                    {{ isSupport(msg.sender_type) ? 'پشتیبان' : 'شما' }}
                                </div>

                                <!-- بابل پیام -->
                                <div
                                    class="max-w-[80%] rounded-lg border p-4"
                                    :class="isSupport(msg.sender_type)
                      ? 'bg-green-50 border-green-200 text-green-800'
                      : 'bg-blue-50 border-blue-200 text-blue-800'"
                                >
                                    <p class="leading-7 whitespace-pre-wrap break-words">{{ msg.message }}</p>

                                    <!-- پیوست‌ها (چیپ‌های جمع‌وجور) -->
                                    <div v-if="msg.attachments && msg.attachments.length" class="mt-3 flex flex-wrap gap-2">
                                        <template v-for="att in (msg.showAllAtt ? msg.attachments : msg.attachments.slice(0,6))" :key="att.id">
                                            <a :href="att.url" target="_blank" class="file-chip file-chip-link" :title="att.name">
                                                <span class="mr-1">{{ getFileEmoji(att.mime) }}</span>
                                                <span class="truncate max-w-[140px] inline-block align-middle">{{ att.name || 'فایل' }}</span>
                                            </a>
                                        </template>
                                        <button
                                            v-if="msg.attachments.length > 6"
                                            class="file-chip file-chip-muted"
                                            @click="msg.showAllAtt = !msg.showAllAtt"
                                        >
                                            {{ msg.showAllAtt ? 'کمتر' : `+${msg.attachments.length - 6} بیشتر` }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <p class="text-xs text-gray-400 mt-1" :class="isSupport(msg.sender_type) ? 'text-left' : 'text-right'">
                                {{ formatDate(msg.created_at) }}
                            </p>
                        </div>
                    </div>

                    <!-- Reply Composer (همیشه در دید) -->
                    <div v-if="canReplyToThread" class="border-t bg-white p-4 md:p-6">
                        <form @submit.prevent="submitThreadReply" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">پاسخ شما</label>
                                <textarea
                                    v-model="threadReplyMessage"
                                    rows="3"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="پاسخ خود را بنویسید..."
                                    @keydown.ctrl.enter.prevent="submitThreadReply"
                                    required
                                ></textarea>
                                <p class="text-xs text-gray-400 mt-1">Ctrl + Enter برای ارسال</p>
                            </div>

                            <!-- Dropzone -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">📎 فایل‌های پیوست</label>
                                <div
                                    class="file-drop-zone dropzone-fancy w-full p-4 rounded-lg text-center cursor-pointer border-2 border-dashed border-gray-300"
                                    :class="{'dragging': isDragOverReply}"
                                    @dragover.prevent="isDragOverReply = true"
                                    @dragleave.prevent="isDragOverReply = false"
                                    @drop.prevent="handleFileDropReply"
                                    @click="$refs.replyFileInput.click()"
                                >
                                    <p class="text-gray-600">فایل‌ها را اینجا بکشید یا کلیک کنید</p>
                                    <p class="text-xs text-gray-400 mt-1">حداکثر 10 فایل، هر کدام تا 5 مگابایت</p>
                                </div>
                                <input
                                    type="file"
                                    ref="replyFileInput"
                                    @change="handleFileSelectReply"
                                    multiple
                                    class="hidden"
                                    accept="image/*,.pdf,.doc,.docx,.txt,.zip,.rar"
                                >
                            </div>

                            <!-- فایل‌های انتخاب‌شده به‌صورت چیپ -->
                            <div v-if="replyFiles.length" class="flex flex-wrap gap-2 max-h-28 overflow-y-auto">
                                <template v-for="(file, idx) in replyFilesLimited" :key="file.name + idx">
              <span class="file-chip" :title="file.name">
                <span class="mr-1">{{ getFileEmoji(file.type) }}</span>
                <span class="truncate max-w-[160px] inline-block align-middle">{{ file.name }}</span>
                <button type="button" class="chip-x" @click="removeReplyFile(idx)" aria-label="remove">×</button>
              </span>
                                </template>
                                <span v-if="replyFilesMoreCount > 0" class="file-chip file-chip-muted cursor-pointer"
                                      @click="showAllReplyFiles = !showAllReplyFiles">
              +{{ replyFilesMoreCount }} فایل دیگر
            </span>
                            </div>

                            <div class="flex justify-end gap-3">
                                <button type="button" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50"
                                        @click="threadReplyMessage=''; replyFiles=[];">
                                    پاک‌سازی
                                </button>
                                <button
                                    type="submit"
                                    :disabled="isSubmittingReply || !threadReplyMessage.trim()"
                                    class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700
                     text-white px-5 py-2 rounded-lg font-medium disabled:opacity-50"
                                >
                                    {{ isSubmittingReply ? 'در حال ارسال...' : 'ارسال پاسخ' }}
                                </button>
                            </div>
                        </form>
                    </div>
                    <!-- /Composer -->
                </div>
            </div>
        </transition>


        <!-- New Ticket Modal -->
        <transition name="fade">
            <div v-if="showNewTicketForm"
                 class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
                 @click.self="closeNewTicketForm"
            >
                <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden"
                     @click.stop
                >
                    <div class="p-6 border-b border-gray-200 sticky top-0 bg-white z-10">
                        <div class="flex justify-between items-center">
                            <h2 class="text-xl font-bold text-gray-900">ثبت تیکت جدید</h2>
                            <button
                                @click="closeNewTicketForm"
                                class="absolute top-4 left-4 text-red-500 hover:text-red-700 transition-transform transform hover:scale-110 close-btn"
                                aria-label="close"
                                style="width: 36px; height: 36px;"
                            >
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="max-h-[75vh] overflow-y-auto">
                        <form @submit.prevent="submitNewTicket" class="p-6 space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">🏢 بخش پشتیبانی</label>
                                <select
                                    v-model="newTicket.department"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none transition-colors"
                                    required
                                >
                                    <option value="">انتخاب بخش...</option>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">
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
                            <!-- File Upload -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">📎 فایل‌های پیوست</label>
                                <div
                                    class="file-drop-zone dropzone-fancy w-full p-8 rounded-lg text-center cursor-pointer border-2 border-dashed border-gray-300"
                                    :class="{'dragging': isDragOver}"
                                    @dragover.prevent="isDragOver = true"
                                    @dragleave.prevent="isDragOver = false"
                                    @drop.prevent="handleFileDrop"
                                    @click="$refs.fileInput.click()"
                                >
                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor"
                                         viewBox="0 0 48 48">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"/>
                                    </svg>
                                    <p class="text-lg font-medium text-gray-600">فایل‌ها را اینجا بکشید یا کلیک کنید</p>
                                    <p class="text-sm text-gray-500 mt-1">حداکثر 10 فایل، هر کدام تا 5 مگابایت</p>
                                </div>
                                <input
                                    type="file"
                                    ref="fileInput"
                                    @change="handleFileSelect"
                                    multiple
                                    class="hidden"
                                    accept="image/*,.pdf,.doc,.docx,.txt,.zip,.rar"
                                >
                            </div>
                            <!-- Selected Files -->
                            <transition-group name="slide" tag="div" class="space-y-2">
                                <div
                                    v-for="(file, index) in selectedFiles"
                                    :key="file.name + index"
                                    class="flex items-center justify-between bg-gray-50 p-3 rounded-lg"
                                >
                                    <div class="flex items-center space-x-3 space-x-reverse">
                                        <div class="text-2xl">{{ getFileIcon(file.type) }}</div>
                                        <div>
                                            <p class="font-medium text-gray-800">{{ file.name }}</p>
                                            <p class="text-sm text-gray-500">{{ formatFileSize(file.size) }}</p>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="removeFile(index)"
                                        class="text-red-500 hover:text-red-700 transition-colors"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </transition-group>
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
                                    class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-3 rounded-lg font-medium transition-colors disabled:opacity-50"
                                >
                                    <span v-if="!isSubmittingTicket">ثبت تیکت</span>
                                    <span v-else>در حال ثبت...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import {ref, computed, onMounted, onUnmounted, watch} from 'vue';
import axios from 'axios';

// --- State ---
import {useToast} from 'vue-toast-notification'

const toast = useToast();
const showNewTicketForm = ref(false);
const loading = ref(true);
const selectedThread = ref(null);
const threadMessages = ref([]);
const threadReplyMessage = ref('');
const replyFiles = ref([]);
const isDragOverReply = ref(false);
const expandedTickets = ref([]);
const isSubmittingTicket = ref(false);
const isSubmittingReply = ref(false);
const statusFilter = ref('');
const departmentFilter = ref('');
const searchQuery = ref('');
const showAllReplyFiles = ref(false);
const replyPreviewLimit = 12; // حداکثر چیپ‌های اولیه
const newTicket = ref({
    title: '',
    message: '',
    department: '',
    priority: 'normal'
});
const selectedFiles = ref([]);
const isDragOver = ref(false);
const departments = ref([]);
const tickets = ref([]);

const anyModalOpen = computed(() => !!selectedThread.value || !!showNewTicketForm.value);
const replyFilesLimited = computed(() =>
    showAllReplyFiles.value ? replyFiles.value : replyFiles.value.slice(0, replyPreviewLimit)
);
const replyFilesMoreCount = computed(() =>
    Math.max(0, replyFiles.value.length - replyPreviewLimit)
);
const handleEsc = (e) => {
    if (e.key === 'Escape') {
        if (selectedThread.value) closeThreadModal();
        if (showNewTicketForm.value) closeNewTicketForm();
    }
};
const getFileEmoji = (mimeOrType = '') => {
    const t = String(mimeOrType).toLowerCase();
    if (t.startsWith('image/')) return '🖼️';
    if (t.includes('pdf')) return '📄';
    if (t.includes('word') || t.includes('doc')) return '📝';
    if (t.includes('zip') || t.includes('rar')) return '📦';
    if (t.includes('sheet') || t.includes('excel') || t.includes('csv')) return '📊';
    return '📎';
};
const prettySize = (bytes) => {
    if (!bytes) return '';
    const k = 1024, sizes = ['بایت', 'کیلوبایت', 'مگابایت', 'گیگابایت'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${(bytes / Math.pow(k, i)).toFixed(1)} ${sizes[i]}`;
};
// Helper: شمارش پیوست‌ بر اساس شکل‌های مختلف ریسپانس
const getAttachCount = (t) => {
    if (!t) return 0;
    // ترتیب fallback: attachments_count -> attachments.length -> media_count -> files_count -> 0
    if (typeof t.attachments_count === 'number') return t.attachments_count;
    if (Array.isArray(t.attachments)) return t.attachments.length;
    if (typeof t.media_count === 'number') return t.media_count;
    if (typeof t.files_count === 'number') return t.files_count;
    return 0;
};

const lockBodyScroll = (lock) => {
    if (lock) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
};

watch(anyModalOpen, (open) => lockBodyScroll(open), {immediate: true});

onMounted(() => {
    window.addEventListener('keydown', handleEsc);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleEsc);
    lockBodyScroll(false);
});

// --- Toasts ---

// --- Fetch Data ---
const fetchDepartments = async () => {
    try {
        const response = await axios.get('/api/v1/support-roles');
        departments.value = response.data;
    } catch (error) {
        toast.error('خطا در بارگذاری بخش‌ها');
    }
};

const fetchTickets = async () => {
    loading.value = true;
    try {
        const {data} = await axios.get('/api/v1/tickets');
        tickets.value = data?.data ?? data ?? [];
    } catch (e) {
        toast.error('خطا در بارگذاری تیکت‌ها');
    } finally {
        loading.value = false;
    }
};
const canUserReply = ref(false);

const viewThread = async (rootId) => {
    try {
        const { data } = await axios.get(`/api/v1/tickets/${rootId}`);
        selectedThread.value = data.ticket;
        threadMessages.value = (data.messages || []).map(m => ({ ...m, showAllAtt: false }));
        canUserReply.value   = !!data.can_user_reply;

        // اسکرول به بالا یا پایین؟ (اینجا اجازه می‌دهیم کاربر از ابتدا بخواند)
        // اگر خواستی پایین برود:
        // nextTick(()=>{ const el = document.getElementById('thread-scroll'); if(el) el.scrollTop = el.scrollHeight; });
    } catch (e) {
        toast.error('خطا در بارگذاری گفتگو');
    }
};
const canReplyToThread = computed(() =>
    !!canUserReply.value && selectedThread.value?.status !== 'closed'
);

// --- Thread Management ---
// const viewThread = async (rootId) => {
//     try {
//         const response = await axios.get(`/api/v1/tickets/${rootId}`);
//         const t = response?.data?.ticket ?? {};
//         const msgs = response?.data?.messages ?? [];
//
//         selectedThread.value = t;
//
//         // پیام ریشه (اولین تیکت) را بساز و به ابتدای لیست اضافه کن
//         const rootMessage = {
//             id: `root-${t.id}`,
//             message: t.message,
//             // اگر سمت بک‌اند نوع فرستنده را مشخص می‌کند از همان استفاده کن، در غیر اینصورت 'customer'
//             sender_type: t.sender_type || 'customer',
//             created_at: t.created_at,
//             // اگر بک‌اند attachments را برنمی‌گرداند، حداقل count داریم؛
//             // در UI نمایش نام فایل لازم نیست، پس آرایه خالی مشکلی ایجاد نمی‌کند
//             attachments: Array.isArray(t.attachments) ? t.attachments : [],
//         };
//
//         threadMessages.value = [rootMessage, ...msgs];
//
//         // اسکرول به ابتدای گفتگو
//         // (اختیاری: اگر container خاصی داری، می‌تونی با nextTick به بالا اسکرولش کنی)
//     } catch (error) {
//         toast.error('خطا در بارگذاری گفتگو');
//     }
// };


const closeThreadModal = () => {
    selectedThread.value = null;
    threadMessages.value = [];
    threadReplyMessage.value = '';
    replyFiles.value = [];
};

// const canReplyToThread = computed(() => {
//     if (threadMessages.value.length === 0) return false;
//     const lastMessage = threadMessages.value[threadMessages.value.length - 1];
//     return lastMessage.sender_type === 'user' && lastMessage.status !== 'closed';
// });

const submitThreadReply = async () => {
    if (!selectedThread.value) return;
    if (!canReplyToThread.value) {
        toast.error('بعد از دریافت پاسخ پشتیبانی می‌توانید جواب بدهید.');
        return;
    }

    isSubmittingReply.value = true;
    try {
        const formData = new FormData();
        formData.append('message', threadReplyMessage.value);
        replyFiles.value.forEach(f => formData.append('files[]', f)); // بک‌اند files[] را می‌پذیرد

        await axios.post(`/api/v1/tickets/${selectedThread.value.id}/messages`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        toast.success('پاسخ شما ارسال شد');
        threadReplyMessage.value = '';
        replyFiles.value = [];
        showAllReplyFiles.value = false;

        // رفرش گفتگو (آخرین پاسخ پشتیبان بعداً اجازه را تغییر می‌دهد)
        await viewThread(selectedThread.value.id);
    } catch (e) {
        const msg = e?.response?.data?.message || 'خطا در ارسال پاسخ';
        toast.error(msg);
    } finally {
        isSubmittingReply.value = false;
    }
};


// --- File Handling for Reply ---
const handleFileSelectReply = (event) => {
    const files = Array.from(event.target.files);
    addFiles(files, replyFiles);
};
const handleFileDropReply = (event) => {
    isDragOverReply.value = false;
    const files = Array.from(event.dataTransfer.files);
    addFiles(files, replyFiles);
};
const removeReplyFile = (index) => {
    replyFiles.value.splice(index, 1);
};

// --- New Ticket ---
const submitNewTicket = async () => {
    isSubmittingTicket.value = true;
    try {
        const formData = new FormData();
        formData.append('title', newTicket.value.title);
        formData.append('message', newTicket.value.message);
        formData.append('department', newTicket.value.department);
        if (newTicket.value.priority) formData.append('priority', newTicket.value.priority);
        selectedFiles.value.forEach(f => formData.append('files[]', f));

        const res = await axios.post('/api/v1/tickets', formData, {
            headers: {'Content-Type': 'multipart/form-data'}
        });

        const created = res?.data?.data ?? res?.data ?? {};
        tickets.value.unshift({
            ...created,
            attachments_count: created.attachments_count ?? (selectedFiles.value?.length || 0),
        });

        closeNewTicketForm();
        newTicket.value = {title: '', message: '', department: '', priority: 'normal'};
        selectedFiles.value = [];

        toast.success('تیکت شما با موفقیت ثبت شد.');
    } catch (error) {
        const msg =
            error?.response?.data?.message
            ?? (error?.response?.data?.errors && Object.values(error.response.data.errors)[0]?.[0])
            ?? 'خطا در ثبت تیکت';
        toast.error(msg);
    } finally {
        isSubmittingTicket.value = false;
    }
};


const closeNewTicketForm = () => {
    showNewTicketForm.value = false;
    newTicket.value = {title: '', message: '', department: ''};
    selectedFiles.value = [];
};

// --- File Utils ---
const addFiles = (files, targetRef) => {
    const maxFiles = 10;
    const maxSize = 5 * 1024 * 1024;
    files.forEach(file => {
        if (targetRef.value.length >= maxFiles) {
            toast.warning('حداکثر 10 فایل مجاز است');
            return;
        }
        if (file.size > maxSize) {
            toast.warning(`فایل ${file.name} بیش از 5 مگابایت است`);
            return;
        }
        targetRef.value.push(file);
    });
};

const handleFileSelect = (event) => {
    const files = Array.from(event.target.files);
    addFiles(files, selectedFiles);
};
const handleFileDrop = (event) => {
    isDragOver.value = false;
    const files = Array.from(event.dataTransfer.files);
    addFiles(files, selectedFiles);
};
const removeFile = (index) => {
    selectedFiles.value.splice(index, 1);
};

const getFileIcon = (type) => {
    if (type?.startsWith('image/')) return '🖼️';
    if (type?.includes('pdf')) return '📄';
    if (type?.includes('word') || type?.includes('doc')) return '📝';
    if (type?.includes('zip') || type?.includes('rar')) return '📦';
    return '📎';
};

const formatFileSize = (bytes) => {
    if (!bytes) return '0 بایت';
    const k = 1024;
    const sizes = ['بایت', 'کیلوبایت', 'مگابایت'];
    const i = Math.min(2, Math.floor(Math.log(bytes) / Math.log(k)));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

// --- Utils ---
const getDepartmentName = (deptId) => {
    const dept = departments.value.find(d => d.id === deptId);
    return dept ? dept.name : 'نامشخص';
};

const getStatusLabel = (status) => {
    const labels = {pending: 'در انتظار پاسخ', answered: 'پاسخ داده شده', closed: 'بسته شده'};
    return labels[status] || status;
};

const getStatusClass = (status) => {
    const map = {
        pending: 'badge-pending',
        answered: 'badge-answered',
        closed: 'badge-closed',
    };
    return map[status] || 'badge-muted';
};

const getPriorityLabel = (priority) => {
    const labels = {low: 'کم', normal: 'معمولی', high: 'بالا', urgent: 'فوری'};
    return labels[priority] || priority;
};

const getPriorityClass = (priority) => {
    const map = {
        low: 'prio-low',
        normal: 'prio-normal',
        high: 'prio-high',
        urgent: 'prio-urgent',
    };
    return map[priority] || 'prio-normal';
};
const isSupport = (t) => ['admin','support','agent','staff','operator']
    .includes(String(t || '').toLowerCase());

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

const pendingTickets = computed(() => tickets.value.filter(t => t.status === 'pending').length);
const answeredTickets = computed(() => tickets.value.filter(t => t.status === 'answered').length);
const closedTickets = computed(() => tickets.value.filter(t => t.status === 'closed').length);

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

const goToChat = () => {
    window.location.href = '/chat';
};

// --- Lifecycle ---
onMounted(() => {
    fetchDepartments();
    fetchTickets();
});
</script>

<style scoped>
.ticket-app {
    font-family: 'Vazirmatn', ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    background: radial-gradient(1200px 800px at 100% -200px, rgba(59, 130, 246, 0.06), transparent 60%),
    radial-gradient(1000px 600px at -200px 100%, rgba(99, 102, 241, 0.06), transparent 60%),
    #f7fafc;
    min-height: 100vh;
}

/* ---------- Toast ---------- */
/*.toast-container {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 10000;
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: 100%;
    max-width: 520px;
    padding: 0 16px;
}

.toast {
    padding: 12px 16px;
    border-radius: 12px;
    color: white;
    font-weight: 600;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    gap: 10px;
    animation: toastSlideIn .3s ease-out;
    backdrop-filter: saturate(150%) blur(6px);
}

@keyframes toastSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px)
    }
    to {
        opacity: 1;
        transform: translateY(0)
    }
}

.toast-success {
    background: linear-gradient(135deg, #10b981, #34d399);
}

.toast-error {
    background: linear-gradient(135deg, #ef4444, #f43f5e);
}

.toast-warning {
    background: linear-gradient(135deg, #f59e0b, #fbbf24);
}*/

/* ---------- Cards (glossy + gradient edge) ---------- */
.glossy {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.88), rgba(255, 255, 255, 0.92));
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.9);
    box-shadow: 0 10px 30px rgba(17, 24, 39, 0.05);
    position: relative;
}

.glossy::before {
    content: "";
    position: absolute;
    inset: -1px;
    border-radius: 16px;
    padding: 1px;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.35), rgba(99, 102, 241, 0.35), rgba(16, 185, 129, 0.35));
    -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    pointer-events: none;
    opacity: .0;
    transition: opacity .25s ease;
}

.glossy:hover::before {
    opacity: .7;
}

.borderless-gradient {
    border: 0 !important;
    transition: transform .25s ease, box-shadow .25s ease;
}

.ticket-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 35px rgba(17, 24, 39, 0.12);
}

/* ---------- Icon Wrap ---------- */
.icon-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* ---------- Badges ---------- */
.badge {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .35rem .8rem;
    border-radius: 9999px;
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: .01em;
    border: 1px solid transparent;
    backdrop-filter: saturate(140%) blur(6px);
    box-shadow: inset 0 0 0 9999px rgba(255, 255, 255, 0.28);
}

.badge-pending {
    color: #92400e;
    background: linear-gradient(135deg, #fff7ed, #fffbeb);
    border-color: #fcd34d99;
}

.badge-answered {
    color: #065f46;
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    border-color: #34d39999;
}

.badge-closed {
    color: #334155;
    background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
    border-color: #cbd5e199;
}

.badge-muted {
    color: #334155;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border-color: #e2e8f099;
}

/* ---------- Priority pill ---------- */
.priority {
    font-weight: 700;
    margin-inline-start: .5rem;
}

.prio-low {
    color: #059669;
}

/* emerald-600 */
.prio-normal {
    color: #475569;
}

/* slate-600   */
.prio-high {
    color: #b45309;
}

/* amber-700   */
.prio-urgent {
    color: #dc2626;
    text-shadow: 0 0 6px rgba(220, 38, 38, .25);
}

/* red-600 */

/* ---------- Dropzone ---------- */
.dropzone-fancy {
    position: relative;
    transition: all .3s ease;
    border-radius: 14px;
    background: linear-gradient(180deg, rgba(248, 250, 252, .6), rgba(241, 245, 249, .8));
}

.dropzone-fancy:hover {
    border-color: #3b82f6 !important;
    background: #eff6ff;
    box-shadow: 0 8px 22px rgba(59, 130, 246, .15);
}

.dropzone-fancy.dragging {
    border-color: #2563eb !important;
    background: linear-gradient(180deg, #eff6ff, #e0e7ff);
    box-shadow: 0 10px 28px rgba(37, 99, 235, .18);
}

/* ---------- Modal Close BTN ---------- */
.close-btn {
    transition: transform .15s ease, background .15s ease;
}

.close-btn:hover {
    transform: rotate(90deg) scale(1.05);
}

/* ---------- Transitions ---------- */
.fade-enter-active, .fade-leave-active {
    transition: opacity .25s ease;
}

.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

.file-drop-zone {
    transition: all .3s ease;
}

/* ---------- Typography tweaks ---------- */
.ticket-card .text-gray-600 {
    color: #4b5563;
}

.ticket-card .text-gray-500 {
    color: #64748b;
}

/* ---------- Header ---------- */
header.bg-white {
    background: linear-gradient(180deg, rgba(255, 255, 255, .92), rgba(255, 255, 255, .98));
    backdrop-filter: blur(6px);
    border-bottom: 1px solid rgba(226, 232, 240, .9);
    position: sticky;
    top: 0;
    z-index: 30;
}

.close-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: transparent;
    cursor: pointer;
    transition: transform 0.15s ease, color 0.15s ease;
}

.close-btn:hover {
    transform: rotate(90deg) scale(1.1);
}

.file-chip {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    max-width: 220px;
    padding: .35rem .6rem;
    border-radius: 9999px;
    font-size: .78rem;
    line-height: 1;
    background: #f1f5f9;       /* slate-100 */
    color: #334155;            /* slate-700 */
    border: 1px solid #e2e8f0; /* slate-200 */
}
.file-chip-link { background: #eef2ff; color: #4338ca; border-color: #c7d2fe; } /* indigo */
.file-chip-muted { background: #fafafa; color: #64748b; border-color: #e5e7eb; cursor: pointer; }

.file-chip .truncate { vertical-align: middle; }

.chip-x {
    margin-inline-start: .25rem;
    border: 0; background: transparent; color: #ef4444;
    font-weight: 700; line-height: 1; cursor: pointer;
}
.chip-x:hover { color: #b91c1c; }
.file-chip {
    display: inline-flex; align-items: center; gap: .35rem;
    max-width: 220px; padding: .35rem .6rem; border-radius: 9999px;
    font-size: .78rem; line-height: 1;
    background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0;
}
.file-chip-link { background:#eef2ff; color:#4338ca; border-color:#c7d2fe; }
.file-chip-muted { background:#fafafa; color:#64748b; border-color:#e5e7eb; cursor:pointer; }
.chip-x { margin-inline-start:.25rem; border:0; background:transparent; color:#ef4444; font-weight:700; cursor:pointer; }
.chip-x:hover { color:#b91c1c; }

/* رفع هرگونه لاین/بار رنگی کنار بابل‌ها */
.bg-green-50, .bg-blue-50 { position: relative; }
.bg-green-50::before, .bg-blue-50::before { content:none; }
/* اگر قبلاً pseudo-element داشتی که نوار ایجاد می‌کرد، این خط آن را حذف می‌کند */

.close-btn { display:flex; align-items:center; justify-content:center; background:transparent; }
</style>
