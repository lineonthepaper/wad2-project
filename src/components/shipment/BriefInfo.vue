<script setup>
import { onMounted, ref } from 'vue'

import countryData from '/json/countryData.json'

import Choices from 'choices.js'

import '/node_modules/choices.js/public/assets/styles/choices.css'

onMounted(() => {
  const sendFrom = document.querySelector('#sendFrom')

  const fromChoices = new Choices(sendFrom, {
    placeholder: false,
    itemSelectText: '',
  })

  sendFrom.parentElement.className = sendFrom.className

  const sendTo = document.querySelector('#sendTo')

  const toChoices = new Choices(sendTo, {
    placeholder: false,
    itemSelectText: '',
  })

  sendTo.parentElement.className = sendTo.className
})

const sendForms = ref([
  { title: 'Send From', id: 'sendFrom' },
  { title: 'Send To', id: 'sendTo' },
])

const shipmentTypes = ref([
  { name: 'Document', imgUrl: '/shipment/document.png', imgAlt: 'Document icon' },
  { name: 'Package', imgUrl: '/shipment/package.png', imgAlt: 'Package icon' },
])
</script>

<script>
export default {
  data() {
    return {
      countries: countryData,
      shipmentType: null,
    }
  },
  methods: {
    toggleShipmentType(name) {
      if (this.shipmentType == name) {
        this.shipmentType = null
      } else {
        this.shipmentType = name
      }
    },
  },
}
</script>

<template>
  <div class="container-fluid">
    <div class="row py-2">
      <div class="col-6" v-for="sendForm in sendForms" :key="sendForm.id">
        <h4 class="text-dark-slate-blue">
          {{ sendForm.title }}
        </h4>
        <select
          v-if="sendForm.id == 'sendTo'"
          :id="sendForm.id"
          class="form-control mb-1 p-2 rounded"
          tabindex="0"
        >
          <option
            :selected="country.code2 == 'SG'"
            v-for="country of countries"
            :key="country.code2"
            :value="country.name"
          >
            {{ country.name }}
          </option>
        </select>

        <select v-else :id="sendForm.id" class="form-control mb-1 p-2 rounded" tabindex="0">
          <option value="Singapore">Singapore</option>
        </select>
      </div>
    </div>

    <div class="row py-2">
      <div class="col-md-6">
        <h4 class="text-dark-slate-blue">Type of Shipment</h4>
        <div class="row py-2 justify-content-center" v-for="type in shipmentTypes" :key="type.name">
          <button
            type="button"
            class="btn w-75 fs-5"
            :class="[shipmentType == type.name ? 'btn-pink-no-hover' : 'btn-white-no-hover']"
            @click="toggleShipmentType(type.name)"
            @keydown.enter="(toggleShipmentType(type.name), $event.preventDefault())"
          >
            <img
              :src="type.imgUrl"
              :alt="type.imgAlt"
              :class="{ invert: shipmentType == type.name }"
            />
            <br />
            {{ type.name }}
          </button>
        </div>
      </div>
      <div class="col-md-6">
        <h4 class="text-dark-slate-blue">Measurements</h4>
        <div class="mb-3">
          <label for="parcelWeightInput" class="text-dark-slate-blue">Weight</label>
          <input type="text" class="form-control" id="parcelWeightInput" />
        </div>

        <div class="mb-3">
          <label for="parcelLengthInput" class="text-dark-slate-blue">Length</label>
          <input type="text" class="form-control" id="parcelLengthInput" />
        </div>

        <div class="mb-3">
          <label for="parcelWidthInput" class="text-dark-slate-blue">Width</label>
          <input type="text" class="form-control" id="parcelWidthInput" />
        </div>

        <div class="mb-3">
          <label for="parcelHeightInput" class="text-dark-slate-blue">Height</label>
          <input type="text" class="form-control" id="parcelHeightInput" />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
button img {
  height: 50px;
  padding: 16px;
  box-sizing: content-box;
}

.invert {
  filter: invert(100%);
}
</style>
