import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.css';
import { createVuetify } from 'vuetify';

export default createVuetify({
    theme: {
        defaultTheme: 'light',
        themes: {
            light: {
                colors: {
                    primary: '#4f46e5',
                    secondary: '#6366f1',
                    background: '#f4f5fb',
                    surface: '#ffffff',
                },
            },
        },
    },
    defaults: {
        VTextField: { variant: 'outlined', density: 'comfortable' },
        VTextarea: { variant: 'outlined', density: 'comfortable' },
        VSelect: { variant: 'outlined', density: 'comfortable' },
        VBtn: { variant: 'flat' },
    },
});
