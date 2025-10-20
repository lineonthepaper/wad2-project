import { defineStore } from 'pinia'

export const useCartStore = defineStore('cart', {
  state: () => ({
    shipments: [],
  }),
  getters: {
    numSelectedShipments() {
      let count = 0
      for (let s of this.shipments) {
        if (s.selected) {
          count++
        }
      }
      return count
    },
    selectedShipments() {
      let selectedShipments = []
      for (let s of this.shipments) {
        if (s.selected) {
          selectedShipments.push(s)
        }
      }
      return selectedShipments
    },
  },
  actions: {
    removeSelected() {
      let unselectedShipments = []
      for (let s of this.shipments) {
        if (!s.selected) {
          unselectedShipments.push(s)
        }
      }
      this.shipments = unselectedShipments
    },
  },
})
