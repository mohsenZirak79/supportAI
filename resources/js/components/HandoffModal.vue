<!-- /var/www/supportAI/resources/js/components/HandoffModal.vue -->
<template>
    <div v-if="isOpen" class="modal-overlay" @click="close">
        <div class="modal-content" @click.stop>
            <h2>ارجاع به پشتیبانی</h2>

            <div class="form-group">
                <label>نقش پشتیبان:</label>
                <select
                    v-model="selectedRoleId"
                    class="select-input"
                    :disabled="labelsLoading"
                >
                    <option value="">انتخاب کنید...</option>
                    <option
                        v-for="role in roles"
                        :key="role?.id ?? role"
                        :value="role?.id ?? role"
                    >
                        {{ roleLabel(role) }}
                    </option>
                </select>
                <p v-if="labelsLoading" style="margin-top:8px;font-size:12px;color:#64748b">
                    در حال بارگذاری لیبل نقش‌ها...
                </p>
            </div>

            <div class="form-group">
                <label>توضیحات (اختیاری):</label>
                <textarea
                    v-model="reason"
                    placeholder="دلیل ارجاع را بنویسید..."
                    class="text-input"
                ></textarea>
            </div>

            <div class="modal-actions">
                <button @click="close" class="btn-cancel">لغو</button>
                <button
                    @click="submit"
                    :disabled="!selectedRoleId"
                    class="btn-submit"
                >
                    ارجاع
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, onMounted } from 'vue'
import axios from 'axios'

/**
 * props:
 * - isOpen: کنترل نمایش مودال
 * - roles: آرایه‌ای از نقش‌ها که توسط parent پاس داده می‌شود
 *   می‌تواند یکی از این دو فرمت باشد:
 *   1) [{ id: 'support_sales', name: 'پشتیبانی فروش' }, ...]
 *   2) ['support_sales', 'support_finance', ...]
 */
const props = defineProps({
    isOpen: { type: Boolean, default: false },
    roles: { type: Array, default: () => [] }
})

const emit = defineEmits(['close', 'submit'])

const selectedRoleId = ref('')
const reason = ref('')

// Map برای نگهداری id => name که از کنترلر می‌گیریم
const roleNameById = ref(new Map())
const labelsLoading = ref(false)

/**
 * گرفتن لیبل نقش‌ها از کنترلر
 * API expected: GET /api/v1/support-roles
 * response: [{ id: 'support_sales', name: 'پشتیبانی فروش' }, ...]
 */
const fetchRoleLabels = async () => {
    try {
        labelsLoading.value = true
        const { data } = await axios.get('/api/v1/support-roles')
        const map = new Map()
        if (Array.isArray(data)) {
            for (const r of data) {
                if (r && r.id && r.name) map.set(String(r.id), r.name)
            }
        }
        roleNameById.value = map
    } catch (e) {
        // اینجا می‌تونی به سیستم toast خودت وصل کنی
        console.error('خطا در بارگذاری لیبل نقش‌ها', e)
    } finally {
        labelsLoading.value = false
    }
}

// در mount یک بار لیبل‌ها را بگیر
onMounted(fetchRoleLabels)

/**
 * roleLabel:
 * - اگر role آبجکت باشد و name داشته باشد => همان name
 * - اگر role آبجکت با id یا فقط id/کلید باشد => از Map بخوان
 * - اگر در Map نبود => fallback قدیمی
 */
const roleLabel = (role) => {
    if (role && typeof role === 'object') {
        if (role.name) return role.name
        if (role.id && roleNameById.value.has(String(role.id))) {
            return roleNameById.value.get(String(role.id))
        }
        return String(role.id || '')
    }

    // role رشته/کلید است
    if (role && roleNameById.value.has(String(role))) {
        return roleNameById.value.get(String(role))
    }

    // fallback قدیمی
    // const labels = {
    //     support_sales: 'پشتیبانی فروش',
    //     support_finance: 'پشتیبانی مالی',
    //     support_technical: 'پشتیبانی فنی'
    // }
    return  String(role || '')
}

const close = () => emit('close')

const submit = () => {
    emit('submit', {
        target_role: selectedRoleId.value, // فقط id ارسال می‌شود
        reason: reason.value
    })
    // reset ساده
    selectedRoleId.value = ''
    reason.value = ''
    close()
}
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex; align-items: center; justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    padding: 24px;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
}

.form-group { margin-bottom: 16px; }

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
}

.select-input, .text-input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-family: inherit;
}

.text-input {
    min-height: 80px;
    resize: vertical;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

.btn-cancel, .btn-submit {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}

.btn-cancel {
    background: #f1f5f9;
    color: #4b5563;
}

.btn-submit {
    background: #2575fc;
    color: white;
}

.btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
