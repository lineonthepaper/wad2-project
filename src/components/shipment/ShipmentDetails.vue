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
          v-model="refInputs[ref.name]"
          :placeholder="ref.placeholder"
          @change="updateRefNumbers"
        />
      </div>
    </div>

    <div class="row py-2 justify-content-center">
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
          <ItemRow
            v-for="i in numRows"
            :key="i"
            :row-id="i"
            @update-item-row="receiveUpdateItemRow"
          />
        </tbody>
      </table>
    </div>

    <div class="row py-2 justify-content-center">
      <div class="col-6 text-end">
        <button type="button" class="btn btn-pink" @click="numRows++">Add Item</button>
      </div>
      <div class="col-6 text-start">
        <button type="button" class="btn btn-pink" @click="removeRow">Remove Item</button>
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
    let recipientVAT = null
    const refNumbers = ref([
      {
        title: 'Sender Reference',
        name: 'senderRef',
        placeholder: 'optional',
      },
      {
        title: 'Sender VAT Number',
        name: 'senderVAT',
        placeholder: 'optional',
      },
      {
        title: 'Recipient VAT Number',
        name: 'recipientVAT',
        placeholder: 'optional',
      },
    ])
    const refInputs = ref({
      senderRef: this.senderRef,
      senderVAT: this.senderVAT,
      recipientVAT: this.recipientVAT,
    })
    return {
      refNumbers,
      numRows: 1,
      items: {},
      refInputs,
    }
  },
  methods: {
    removeRow() {
      if (this.numRows > 1) {
        this.numRows--
      }
      for (let rowNum in this.items) {
        if (rowNum > this.numRows) {
          delete this.items[rowNum]
        }
      }
    },
    receiveUpdateItemRow(rowId, inputName, value) {
      // console.log('received ' + inputName + ' ' + value + ' on row ' + rowId)
      if (!(rowId in this.items)) {
        this.items[rowId] = {}
      }
      this.items[rowId][inputName] = value
      // console.log(this.items)

      this.updateAllItems()
    },
    updateAllItems() {
      this.$emit('update-all-items', this.items)
    },
    updateRefNumbers() {
      // console.log(this.refInputs)
      this.$emit('update-ref-numbers', this.refInputs)
    },
  },
}
</script>

<style scoped>
@media screen and (max-width: 510px) {
  .table {
    transform: scale(0.8);
  }
}

@media screen and (max-width: 450px) {
  .table {
    transform: scale(0.7);
  }
}

@media screen and (max-width: 320px) {
  .table {
    transform: scale(0.55);
  }
}
</style>
