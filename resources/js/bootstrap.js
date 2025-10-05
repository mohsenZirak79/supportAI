import axios from 'axios'
import './echo'
import { toast } from './lib/toast'   // لازم برای تست در کنسول

window.__app__toast = toast           // تست: __app__toast.add('msg','success')
window.axios = axios
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
axios.defaults.withCredentials = true
