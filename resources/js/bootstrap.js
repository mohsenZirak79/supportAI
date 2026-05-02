import axios from 'axios'
import './echo'
import { installAxiosErrorInterceptor } from './lib/notifySupportAiOfferHelp'

window.axios = axios
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
axios.defaults.withCredentials = true

installAxiosErrorInterceptor()
