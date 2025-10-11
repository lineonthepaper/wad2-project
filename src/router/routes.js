import { createWebHistory, createRouter } from 'vue-router'

import HomeView from '../views/HomeView.vue'
import FAQView from '../views/FAQView.vue'
import ServicesView from '../views/ServicesView.vue'
import ShipmentView from '../views/ShipmentView.vue'
<<<<<<< HEAD
import LoginView from '../views/LoginView.vue'
import CartView from '../views/CartView.vue'
import HelpView from '../views/HelpView.vue'
=======
import LoginView from '../views/SignUpView.vue'
import CartView from '../views/CartView.vue'
import HelpView from '../views/HelpView.vue'
import SignupForm from '@/views/SignUpView.vue'
import LoginForm from '@/views/LoginView.vue'

>>>>>>> 8ea627c (B)

export const routes = [
  { path: '/', name: 'home', title: 'Home', component: HomeView },
  { path: '/faq', name: 'faq', title: 'FAQ', component: FAQView },
  { path: '/services', name: 'services', title: 'Our services', component: ServicesView },
  { path: '/shipment', name: 'shipment', title: 'Shipment', component: ShipmentView },
  { path: '/login', name: 'login', title: 'Login', component: LoginView },
  { path: '/cart', name: 'cart', title: 'Cart', component: CartView },
  { path: '/help', name: 'help', title: 'Help', component: HelpView },
<<<<<<< HEAD
=======
  { path: '/signup', component: SignupForm},
  { path: '/login', component: LoginForm},
  { path: '/', redirect: '/signup' }
>>>>>>> 8ea627c (B)
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
<<<<<<< HEAD
=======

>>>>>>> 8ea627c (B)
