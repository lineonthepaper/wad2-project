import { createWebHistory, createRouter } from 'vue-router'

import HomeView from '../views/HomeView.vue'
import FAQView from '../views/FAQView.vue'
import ShipmentView from '../views/ShipmentView.vue'
import LoginView from '../views/LoginView.vue'
import CartView from '../views/CartView.vue'
import HelpView from '../views/HelpView.vue'
import SignUpView from '@/views/SignUpView.vue'
import ServicesView from '../views/ServicesView.vue'
import ServiceDetail from '../views/ServiceDetail.vue'
import customerDB from '../views/customerDB.vue'
import ConfirmationView from '@/views/ConfirmationView.vue'
import settings from  '@/views/settings.vue'
import changePassword from '@/views/changePassword.vue'
import history from '@/views/history.vue'
import HistoryDetail from '@/views/HistoryDetail.vue'

export const routes = [
  { path: '/', name: 'home', title: 'Home', component: HomeView },
  { path: '/faq', name: 'faq', title: 'FAQ', component: FAQView },
  { path: '/services', name: 'services', title: 'Our services', component: ServicesView },
  { path: '/shipment', name: 'shipment', title: 'Shipment', component: ShipmentView },
  { path: '/login', name: 'login', title: 'Login', component: LoginView }, // single /login route
  { path: '/cart', name: 'cart', title: 'Cart', component: CartView },
  { path: '/help', name: 'help', title: 'Help', component: HelpView },
  { path: '/signup', name: 'signup', title: 'Sign Up', component: SignUpView }, // named signup
  { path: '/service/:id', name: 'ServiceDetail', component: ServiceDetail, props: true },
  { path: '/CDB',name: 'dashboard', title: 'Dashboard', component: customerDB },
  { path: '/confirmation', name: 'confirmation', title: 'Confirmed!', component: ConfirmationView },
  {path: '/settings',name: 'settings', title: 'settings', component: settings},
  {path: '/changePassword',name: 'changepw', title: 'changePassword', component: changePassword},
  {path: '/history',name: 'history', title: 'History', component: history},
  { path: '/history/:id', name: 'HistoryDetail', component: HistoryDetail, props: true },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
