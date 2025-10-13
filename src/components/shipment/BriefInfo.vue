<script setup>
import { onMounted, ref } from 'vue'

import countryData from '/json/countryData.json'

import Choices from 'choices.js'

import '/node_modules/choices.js/public/assets/styles/choices.css'

onMounted(() => {
  const sendFrom = document.querySelector('#sendFrom')

  const fromChoices = new Choices(sendFrom, {
    placeholder: true,
    placeholderValue: 'Country',
    itemSelectText: '',
  })

  sendFrom.parentElement.className = sendFrom.className

  const sendTo = document.querySelector('#sendTo')

  const toChoices = new Choices(sendTo, {
    placeholder: true,
    placeholderValue: 'Country',
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
    let weight = null
    let length = null
    let width = null
    let height = null
    const dimensions = ref([
      { title: 'Weight', name: 'weight', textInput: weight, placeholder: 'in kg' },
      { title: 'Length', name: 'length', textInput: length, placeholder: 'in cm' },
      { title: 'Width', name: 'width', textInput: width, placeholder: 'in cm' },
      { title: 'Height', name: 'height', textInput: height, placeholder: 'in cm' },
    ])
    return {
      countries: countryData,
      shipmentType: null,
      dimensions,
    }
  },
  emits: ['update-country', 'update-shipment-type', 'update-dimensions'],
  methods: {
    toggleShipmentType(name, event = null) {
      if (event) {
        if (event.key != ' ' && event.key != 'Enter') {
          return
        }
        event.preventDefault()
      }
      if (this.shipmentType == name) {
        this.shipmentType = null
      } else {
        this.shipmentType = name
      }

      this.$emit('update-shipment-type', this.shipmentType)
    },
    updateCountry(sendFormId, event) {
      // console.log(event.target.value)
      this.$emit('update-country', sendFormId, event.target.value)
    },
    updateDimensions(dimension, value) {
      this.$emit('update-dimensions', dimension, value)
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
          @change="updateCountry(sendForm.id, $event)"
          required
        >
          <option v-for="country of countries" :key="country.code2" :value="country.code2">
            {{ country.name }}
          </option>
        </select>

        <select
          v-else
          :id="sendForm.id"
          class="form-control mb-1 p-2 rounded"
          tabindex="0"
          @change="updateCountry(sendForm.id, $event)"
          required
        >
          <option value="SG">Singapore</option>
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
            @keydown="toggleShipmentType(type.name, $event)"
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
        <div class="mb-3" v-for="dimension in dimensions" :key="dimension.name">
          <label :for="'parcel' + dimension.title + 'Input'" class="text-dark-slate-blue">
            {{ dimension.title }}
          </label>
          <input
            type="number"
            class="form-control"
            :id="'parcel' + dimension.title + 'Input'"
            @change="updateDimensions(dimension.name, dimension.textInput)"
            v-model.number="dimension.textInput"
            inputmode="numeric"
            :placeholder="dimension.placeholder"
            required
          />
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
