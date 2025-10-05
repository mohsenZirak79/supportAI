import { ref } from 'vue'

const list = ref([])

function add(message, type = 'success') {
    const id = Date.now() + Math.random()
    list.value.push({ id, message, type })
    setTimeout(() => {
        list.value = list.value.filter(t => t.id !== id)
    }, 5000)
}

export const toast = { list, add }
