<template>
  <div class="container-fluid">
    <hr />
    <div class="row bg-light-pink justify-content-center airplane-header">
      <div class="col-lg-3 col-md-6 col-sm-6 py-2 text-center">
        <h1 class="jua text-hot-pink">Cart</h1>
      </div>
    </div>
    <hr />

    <div class="row">
      <button @click="console.log(cart.$state)">Cart state</button>
      <button @click="console.log(shipment.$state)">Shipment state</button>
    </div>

    <div
      class="row justify-content-center text-dark-slate-blue py-2"
      v-for="(s, index) in cart.shipments"
      :key="index"
    >
      <div class="col-lg-10 border rounded p-4">
        <div class="row">
          <h2>{{ s.service.name }}</h2>
        </div>
        <div class="row">
          <div class="col-md-4 py-2" v-for="send in sendFromOrTo" :key="send.id">
            <h3>Send From</h3>
            <p>
              <input
                type="text"
                v-model="s[send.id].name"
                class="form-control"
                placeholder="Name"
              />
            </p>
            <p>
              <!-- {{ s[send.id].line1 }} -->
              <template v-for="i in 3" :key="i">
                <input
                  type="text"
                  v-model="s[send.id]['line' + i]"
                  class="form-control d-inline-block my-1"
                  :placeholder="'Address Line ' + i"
                />
              </template>
            </p>
            <p class="my-2">
              <input
                type="text"
                v-model="s[send.id].city"
                class="form-control d-inline-block w-50"
                placeholder="City"
              />
              <input
                type="text"
                v-model="s[send.id].state"
                class="form-control d-inline-block w-50"
                placeholder="State"
              />
            </p>
            <p class="my-2">
              <input
                type="text"
                disabled
                :value="s[send.id].country"
                class="form-control w-50 d-inline"
              />
              <input
                type="text"
                v-model="s[send.id].postalCode"
                class="form-control w-50 d-inline"
                placeholder="Postal Code"
              />
            </p>
          </div>

          <div class="col order-first order-md-last py-2 justify-content-center text-center">
            stamp
          </div>
        </div>

        <div class="row">
          <div class="col-md-8">
            <h3>Weight: {{ s.dimensions.weight }} kg</h3>
            <h3>Declared Value:</h3>
          </div>
          <div class="col-md-4 text-center">
            <button type="button" class="btn btn-pink next-btn">Check Out</button>
          </div>
        </div>
        <!-- {{ s.sender }}
        {{ s.recipient }}
        {{ s.service }} -->
        <!-- {{ s.dimensions }} -->
      </div>
    </div>
  </div>
</template>

<script setup>
import { useShipmentStore } from '@/stores/shipment'
import { useCartStore } from '@/stores/cart'
import { ref } from 'vue'
</script>

<script>
export default {
  data() {
    const shipment = useShipmentStore()
    const cart = useCartStore()
    const sendFromOrTo = ref([
      { title: 'Send From', id: 'sender' },
      { title: 'Send To', id: 'recipient' },
    ])
    return {
      shipment,
      cart,
      sendFromOrTo,
    }
  },
}
</script>
