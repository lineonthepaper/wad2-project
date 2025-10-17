import { createWebHistory, createRouter } from 'vue-router'

import HomeView from '../views/HomeView.vue'
import FAQView from '../views/FAQView.vue'
import ShipmentView from '../views/ShipmentView.vue'
import LoginView from '../views/SignUpView.vue'
import CartView from '../views/CartView.vue'
import HelpView from '../views/HelpView.vue'
import SignupForm from '@/views/SignUpView.vue'
import LoginForm from '@/views/LoginView.vue'
import ServicesView from '../views/ServicesView.vue'
import ServiceDetail from '../views/ServiceDetail.vue'
import customerDB from '../views/customerDB.vue'


export const routes = [
  { path: '/', name: 'home', title: 'Home', component: HomeView },
  { path: '/faq', name: 'faq', title: 'FAQ', component: FAQView },
  { path: '/services', name: 'services', title: 'Our services', component: ServicesView },
  { path: '/shipment', name: 'shipment', title: 'Shipment', component: ShipmentView },
  { path: '/login', name: 'login', title: 'Login', component: LoginView },
  { path: '/cart', name: 'cart', title: 'Cart', component: CartView },
  { path: '/help', name: 'help', title: 'Help', component: HelpView },
  { path: '/signup', component: SignupForm },
  { path: '/login', component: LoginForm },
  { path: '/service/:id', name: 'ServiceDetail', component: ServiceDetail, props: true },
  { path: '/CDB', component: customerDB }

]


const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
