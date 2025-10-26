<template>
  <tr>
    <!-- Item Description -->
    <td>
      <input
        type="text"
        class="form-control form-control-lg"
        v-model="inputs[0].input"
        @change="updateItemRow(rowId, inputs[0].name, inputs[0].input)"
        required
        style="max-width: 20em"
      />
    </td>

    <!-- Declared Currency -->
    <td>
      <div style="max-width: 8em">
        <select
          :id="'declaredCurrency' + rowId"
          class="form-control p-2 rounded"
          tabindex="0"
          v-model="inputs[1].input"
          @change="updateItemRow(rowId, inputs[1].name, inputs[1].input)"
          required
        >
          <option v-for="currency of currencyData" :key="currency.code" :value="currency.code">
            {{ currency.code }}
          </option>
        </select>
      </div>
    </td>

    <!-- Declared Value -->
    <td>
      <input
        type="number"
        class="form-control form-control-lg"
        style="max-width: 6em"
        v-model="inputs[2].input"
        @change="updateItemRow(rowId, inputs[2].name, inputs[2].input)"
        required
        @keypress="blockNonNumericInput($event)"
      />
    </td>

    <!-- Item Weight -->
    <td>
      <input
        type="number"
        class="form-control form-control-lg"
        style="max-width: 5em"
        placeholder="in kg"
        v-model="inputs[3].input"
        @change="updateItemRow(rowId, inputs[3].name, inputs[3].input)"
        required
        @keypress="blockNonNumericInput($event)"
        max="2"
        min="0"
      />
    </td>

    <!-- Item Quantity -->
    <td>
      <input
        type="number"
        class="form-control form-control-lg"
        style="max-width: 5em"
        v-model="inputs[4].input"
        @change="updateItemRow(rowId, inputs[4].name, inputs[4].input)"
        required
        @keypress="blockNonNumericInput($event)"
      />
    </td>

    <!-- HS Code -->
    <td>
      <input
        type="text"
        class="form-control form-control-lg"
        style="max-width: 5em"
        v-model="inputs[5].input"
        @change="updateItemRow(rowId, inputs[5].name, inputs[5].input)"
        placeholder="opt."
      />
    </td>
  </tr>
</template>

<style scoped>
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  display: none;
  margin: 0;
}

input[type='number'] {
  -moz-appearance: textfield;
}
</style>

<script setup>
import currencyData from '/json/currencyData.json'

import Choices from 'choices.js'

import '/node_modules/choices.js/public/assets/styles/choices.css'

import { onMounted, ref } from 'vue'

import { blockNonNumericInput } from './utils'

import axios from 'axios'

const props = defineProps({
  rowId: Number,
})

onMounted(() => {
  const declaredCurrency = document.querySelector('#declaredCurrency' + props.rowId)

  const currencyChoices = new Choices(declaredCurrency, {
    placeholder: true,
    placeholderValue: 'Currency',
    itemSelectText: '',
  })

  declaredCurrency.parentElement.className = declaredCurrency.className
})
</script>

<script>
export default {
  emits: ['update-item-row'],
  data() {
    let itemDescription = null
    let itemCurrency = null
    let itemValue = null
    let itemWeight = null
    let itemQuantity = null
    let hsCode = null
    const inputs = ref([
      { name: 'itemDescription', input: itemDescription },
      { name: 'itemCurrency', input: itemCurrency },
      { name: 'itemValue', input: itemValue },
      { name: 'itemWeight', input: itemWeight },
      { name: 'itemQuantity', input: itemQuantity },
      { name: 'hsCode', input: hsCode },
    ])
    return {
      inputs,
    }
  },
  methods: {
    updateItemRow(rowId, inputName, value) {
      console.log('emitting ' + inputName + ' ' + value + ' on row ' + rowId)
      if (inputName == 'itemCurrency' || inputName == 'itemValue') {
        if (
          this.inputs[1].input !== null &&
          this.inputs[2].input !== null &&
          this.inputs[2].input !== ''
        ) {
          this.emitSGD(rowId, this.inputs[1].input, Number(this.inputs[2].input))
        }
      }
      this.$emit('update-item-row', rowId, inputName, value)
    },
    emitSGD(rowId, currency, price) {
      if (currency == 'SGD') {
        this.$emit('update-item-row', rowId, 'costSGD', price)
      } else {
        axios
          .get('https://api.fxratesapi.com/latest', {
            params: {
              api_key: import.meta.env.VITE_FXRATESAPI_KEY,
              base: currency,
              places: 2,
              currencies: 'SGD',
              resolution: '1d',
              amount: price,
            },
          })
          .then((response) => {
            // console.log(response.data.rates.SGD)
            // console.log('converted: ' + price / response.data.rates[currency])
            // return Math.round((price / response.data.rates[currency]) * 100) / 100

            this.$emit('update-item-row', rowId, 'costSGD', response.data.rates.SGD)
            console.log('emitted ' + response.data.rates.SGD)
          })
          .catch((error) => {
            console.error(error)
            return null
          })
      }
    },
  },
}
</script>
