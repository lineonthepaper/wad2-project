<template>
  <div class="container-fluid">
    <div class="row py-2">
      <div class="col-lg-4" v-for="ref in refNumbers" :key="ref.name">
        <label :for="ref.name + 'Input'">
          <h4 class="text-dark-slate-blue pt-2">
            {{ ref.title }}
          </h4>
        </label>
        <input
          type="text"
          :id="ref.name + 'Input'"
          class="form-control"
          v-model="ref.textInput"
          :placeholder="ref.placeholder"
        />
      </div>
    </div>

    <div class="row py-2">
      <h4 class="text-dark-slate-blue pt-2">Item Details</h4>

      <table class="table">
        <thead>
          <tr>
            <th>Item Description</th>
            <th>Declared Currency</th>
            <th>Declared Value</th>
            <th>Item Weight</th>
            <th>Item Quantity</th>
          </tr>
        </thead>
        <tbody>
          <ItemRow v-for="i in numRows" :key="i" :row-id="i" />
        </tbody>
      </table>
    </div>

    <div class="row py-2 justify-content-center">
      <div class="col text-center">
        <button type="button" class="btn btn-pink" @click="numRows++">Add Item</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import ItemRow from './ItemRow.vue'
</script>

<script>
export default {
  data() {
    let senderRef = null
    let senderVAT = null
    let receiverVAT = null
    const refNumbers = ref([
      {
        title: 'Sender Reference',
        name: 'senderRef',
        textInput: senderRef,
        placeholder: 'optional',
      },
      {
        title: 'Sender VAT Number',
        name: 'senderVAT',
        textInput: senderVAT,
        placeholder: 'optional',
      },
      {
        title: 'Receiver VAT Number',
        name: 'receiverVAT',
        textInput: receiverVAT,
        placeholder: 'optional',
      },
    ])
    return {
      refNumbers,
      numRows: 1,
    }
  },
}
</script>
