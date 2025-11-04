import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createHead } from '@unhead/vue/client'
import App from './App.vue'
import router from './router/routes.js'
import SpeechToTextPlugin from './plugins/speech.js'

import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap'

import 'choices.js'

const app = createApp(App)

const head = createHead()

app.use(createPinia())

app.use(head).use(router)

app.use(SpeechToTextPlugin, {
  buttonColor: '#3c4247ff',
  buttonPosition: 'right',
  lang: 'en-US',
  enabledPages: ['/services','/history','/faq']
});

app.mount('#app')

