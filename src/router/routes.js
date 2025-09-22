import { createWebHistory, createRouter } from 'vue-router'

import HomeView from '../views/HomeView.vue'
import FAQView from '../views/FAQView.vue'
import ServicesView from '../views/ServicesView.vue'
import ShipmentView from '../views/ShipmentView.vue'

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
