<template>
    <div class="toast-wrap" v-if="toasts.length">
        <div v-for="t in toasts" :key="t.id" class="toast" :class="t.type">
            {{ t.message }}
        </div>
    </div>
</template>

<script setup>
import {computed} from 'vue'
import {toast} from '@/lib/toast'

const toasts = computed(() => toast.list.value)
</script>

<style scoped>
.toast-wrap {
    position: fixed;
    bottom: 16px;
    left: 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    z-index: 2000
}

.toast {
    background: #111827;
    color: #fff;
    padding: 10px 14px;
    border-radius: 10px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, .2);
    max-width: 320px;
    font-size: .92rem
}

.toast.success {
    background: #065f46
}

.toast.error {
    background: #7f1d1d
}

.toast.info {
    background: #1f2937
}
</style>
