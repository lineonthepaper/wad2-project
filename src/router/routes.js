import { createWebHistory, createRouter } from 'vue-router'

import HomeView from '../components/HomeView.vue'
import FAQView from '../components/FAQView.vue'
import ServicesView from '../components/ServicesView.vue'
import ShipmentView from '../components/ShipmentView.vue'

const routes = [
  { path: '/', name: 'home', component: HomeView },
  { path: '/faq', name: 'faq', component: FAQView },
  { path: '/services', name: 'services', component: ServicesView },
  { path: '/shipment', name: 'shipment', component: ShipmentView },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
