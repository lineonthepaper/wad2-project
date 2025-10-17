<template>
  <div class="container-fluid">
    <hr />
    <div class="row bg-light-pink justify-content-center airplane-header">
      <div class="col-lg-3 col-md-6 col-sm-6 py-2 text-center">
        <h1 class="jua text-hot-pink">Cart</h1>
      </div>
    </div>
    <hr />

    <!-- <div class="row">
      <button @click="console.log(cart.$state)">Cart state</button>
      <button @click="console.log(shipment.$state)">Shipment state</button>
    </div> -->

    <div
      class="row justify-content-center text-dark-slate-blue py-2"
      v-for="(s, index) in cart.shipments"
      :key="index"
    >
      <div class="col-lg-10 border rounded p-4">
        <div class="row">
          <h2>{{ s.service.name }}</h2>
        </div>
        <hr class="hr-slate-blue" />
        <div class="row">
          <div class="col-md-4 py-2" v-for="send in sendFromOrTo" :key="send.id">
            <h3>{{ send.title }}</h3>
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
                :value="getCountryName(s[send.id].country)"
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

          <div class="col order-first order-md-last py-sm-5 justify-content-center text-center">
            <!-- stamp -->
            <div
              class="checkbox mx-auto"
              role="checkbox"
              :aria-checked="s.selected"
              @click="s.selected = !s.selected"
            >
              <img src="/shipment/stamp.png" alt="Stamp" :class="{ 'img-selected': s.selected }" />
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col">
            <h3>Weight: {{ s.dimensions.weight }} kg</h3>
            <h3>Declared Value: {{ s.totalCostSGD }} SGD</h3>
          </div>
        </div>
        <!-- {{ s.sender }}
        {{ s.recipient }}
        {{ s.service }} -->
        <!-- {{ s.dimensions }} -->
        <!-- {{ s }} -->
      </div>
    </div>

    <div class="row justify-content-center py-2">
      <div class="col text-center">
        <button type="button" class="btn btn-pink next-btn">Check Out</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useShipmentStore } from '@/stores/shipment'
import { useCartStore } from '@/stores/cart'
import { ref } from 'vue'

import countryData from '/json/countryData.json'
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
  methods: {
    getCountryName(countryCode) {
      let countryName = null
      for (let obj of countryData) {
        if (obj.code2 == countryCode) {
          countryName = obj.name
        }
      }
      return countryName
    },
  },
}
</script>

<style scoped>
.checkbox {
  height: 7em;
  width: 7em;
  border: 1px solid black;
  border-radius: 2px;

  cursor: pointer;
  user-select: none;
}

.checkbox img {
  height: 0px;
  margin-left: -50%;
  margin-top: -50%;
  transform: rotate(-25deg);
}

.checkbox .img-selected {
  height: 200%;
  transform: rotate(0deg);
  transition: transform 0.25s ease-in;
}

@media screen and (max-width: 992px) {
  .checkbox {
    transform: scale(0.8);
  }
}

@media screen and (max-width: 768px) {
  .checkbox {
    transform: scale(0.7);
  }
}

@media screen and (max-width: 576px) {
  .checkbox {
    transform: scale(0.5);
  }
}
</style>
