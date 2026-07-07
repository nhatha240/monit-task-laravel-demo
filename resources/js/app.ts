import { createApp } from 'vue'
import './style.css'
import { createVuetify } from 'vuetify';
import App from './App.vue'
import router from './router'
// Register Vuetify as plugin
const vuetify = createVuetify();

createApp(App).use(vuetify).use(router).mount('#app')
