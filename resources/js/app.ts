import { createApp } from 'vue';
import './style.css';
import App from './App.vue';
import vuetify from './plugins/vuetify';
import router from './router';
import { loadUser } from './stores/auth';

// Hydrate the current user from a stored token before the first navigation
// runs, so route guards see the correct auth state on a hard refresh.
loadUser().finally(() => {
    createApp(App).use(vuetify).use(router).mount('#app');
});
