<template>
    <div v-if="isOpen" class="modal-overlay" @click="close">
        <div class="modal-content" @click.stop>
            <h2>ارجاع به پشتیبانی</h2>
            <div class="form-group">
                <label>نقش پشتیبان:</label>
                <select v-model="selectedRole" class="select-input">
                    <option value="">انتخاب کنید...</option>
                    <option v-for="role in roles" :key="role" :value="role">
                        {{ roleLabel(role) }}
                    </option>
                </select>
            </div>
            <div class="form-group">
                <label>توضیحات (اختیاری):</label>
                <textarea v-model="reason" placeholder="دلیل ارجاع را بنویسید..." class="text-input"></textarea>
            </div>
            <div class="modal-actions">
                <button @click="close" class="btn-cancel">لغو</button>
                <button @click="submit" :disabled="!selectedRole" class="btn-submit">ارجاع</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits } from 'vue';

const props = defineProps({
    isOpen: Boolean,
    roles: Array
});

const emit = defineEmits(['close', 'submit']);

const selectedRole = ref('');
const reason = ref('');

const roleLabel = (role) => {
    const labels = {
        'support_sales': 'پشتیبانی فروش',
        'support_finance': 'پشتیبانی مالی',
        'support_technical': 'پشتیبانی فنی'
    };
    return labels[role] || role;
};

const close = () => emit('close');
const submit = () => {
    emit('submit', { target_role: selectedRole.value, reason: reason.value });
    close();
};
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    padding: 24px;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
}

.form-group {
    margin-bottom: 16px;
}

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
