import { defineStore } from 'pinia'

export const useCartStore = defineStore('cart', {
  state: () => ({
    shipments: [],
  }),
  getters: {},
  actions: {},
})
