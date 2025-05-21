import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

// PrimeVue
import PrimeVue from 'primevue/config'
import ToastService from 'primevue/toastservice'
import 'primevue/resources/themes/aura-dark-green/theme.css'
import 'primeicons/primeicons.css'

// Componentes
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Toast from 'primevue/toast'
import Dropdown from 'primevue/dropdown'
import Card from 'primevue/card'

// Swiper
import 'swiper/css'
import 'swiper/css/navigation'

const app = createApp(App)

app.use(router)
app.use(PrimeVue)
app.use(ToastService)

// Componentes globais
app.component('Button', Button)
app.component('InputText', InputText)
app.component('Toast', Toast)
app.component('Dropdown', Dropdown)
app.component('Card', Card)

app.mount('#app')