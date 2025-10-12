<template>
  <tr>
    <!-- Item Description -->
    <td>
      <input type="text" class="form-control form-control-lg" />
    </td>

    <!-- Declared Currency -->
    <td>
      <div style="width: 8em">
        <select :id="'declaredCurrency' + rowId" class="form-control p-2 rounded" tabindex="0">
          <option v-for="currency of currencyData" :key="currency.code" :value="currency.code">
            {{ currency.code }}
          </option>
        </select>
      </div>
    </td>

    <!-- Declared Value -->
    <td>
      <input type="number" class="form-control form-control-lg" style="width: 5em" />
    </td>

    <!-- Item Weight -->
    <td>
      <input
        type="number"
        class="form-control form-control-lg"
        style="width: 5em"
        placeholder="in kg"
      />
    </td>

    <!-- Item Quantity -->
    <td>
      <input type="number" class="form-control form-control-lg" style="width: 4em" />
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

import { onMounted } from 'vue'

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
  data() {
    return {}
  },
}
</script>
