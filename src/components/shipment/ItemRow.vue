<template>
  <tr>
    <!-- Item Description -->
    <td>
      <input
        type="text"
        class="form-control form-control-lg"
        v-model="inputs.itemDescription"
        @change="updateItemRow(rowId, 'itemDescription', inputs.itemDescription)"
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
          v-model="inputs.itemCurrency"
          @change="updateItemRow(rowId, 'itemCurrency', inputs.itemCurrency)"
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
        v-model="inputs.itemValue"
        @change="updateItemRow(rowId, 'itemValue', inputs.itemValue)"
        required
      />
    </td>

    <!-- Item Weight -->
    <td>
      <input
        type="number"
        class="form-control form-control-lg"
        style="max-width: 5em"
        placeholder="in kg"
        v-model="inputs.itemWeight"
        @change="updateItemRow(rowId, 'itemWeight', inputs.itemWeight)"
        required
      />
    </td>

    <!-- Item Quantity -->
    <td>
      <input
        type="number"
        class="form-control form-control-lg"
        style="max-width: 5em"
        v-model="inputs.itemQuantity"
        @change="updateItemRow(rowId, 'itemQuantity', inputs.itemQuantity)"
        required
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
    const inputs = ref([
      { name: 'itemDescription', input: itemDescription },
      { name: 'itemCurrency', input: itemCurrency },
      { name: 'itemValue', input: itemValue },
      { name: 'itemWeight', input: itemWeight },
      { name: 'itemQuantity', input: itemQuantity },
    ])
    return {
      inputs,
    }
  },
  methods: {
    updateItemRow(rowId, inputName, value) {
      console.log('emitting ' + inputName + ' ' + value + ' on row ' + rowId)
      this.$emit('update-item-row', rowId, inputName, value)
    },
  },
}
</script>
