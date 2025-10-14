<template>
  <div class="container-fluid">
    <div v-for="address in addresses" :key="address.id">
      <div class="row py-2">
        <div class="col-md-4">
          <h4 class="text-dark-slate-blue">{{ address.title }} Contact</h4>

          <label :for="address.id + 'Name'" class="text-dark-slate-blue">Name</label>
          <input :id="address.id + 'Name'" type="text" class="form-control mb-1" required />

          <label :for="address.id + 'PhoneNumber'" class="text-dark-slate-blue">Phone Number</label>
          <div class="input-group mb-1">
            <div class="d-inline" style="width: 40%">
              <select :id="address.id + 'PhoneCode'" class="form-control">
                <option
                  v-for="phoneCode in phoneCodesData"
                  :value="phoneCode.code"
                  :key="phoneCode.code"
                >
                  {{ phoneCode.code }}
                </option>
              </select>
            </div>
            <div class="d-inline" style="width: 60%">
              <input
                :id="address.id + 'PhoneNumber'"
                type="text"
                class="form-control"
                placeholder="optional"
              />
            </div>
          </div>

          <label :for="address.id + 'Email'" class="text-dark-slate-blue">Email</label>
          <input
            :id="address.id + 'Email'"
            type="text"
            class="form-control mb-1"
            placeholder="optional"
          />
        </div>
        <div class="col-md-8">
          <h4 class="text-dark-slate-blue">{{ address.title }} Address</h4>
          <div class="row">
            <div class="col-lg-8">
              <div v-for="i in 3" :key="i">
                <label :for="address.id + 'Line' + i" class="text-dark-slate-blue"
                  >Address Line {{ i }}</label
                >
                <input
                  :id="address.id + 'Line' + i"
                  type="text"
                  class="form-control mb-1"
                  :required="i === 1 ? true : null"
                />
              </div>
            </div>

            <div class="col-lg-4">
              <div class="row">
                <div class="col-sm-6">
                  <label :for="address.id + 'City'" class="text-dark-slate-blue">City</label>
                  <input :id="address.id + 'City'" type="text" class="form-control mb-1" />
                </div>

                <div class="col-sm-6">
                  <label :for="address.id + 'State'" class="text-dark-slate-blue">State</label>
                  <input :id="address.id + 'State'" type="text" class="form-control mb-1" />
                </div>
              </div>

              <label :for="address.id + 'Postal Code'" class="text-dark-slate-blue"
                >Postal Code</label
              >
              <input
                :id="address.id + 'Postal Code'"
                type="text"
                class="form-control mb-1"
                required
              />

              <label :for="address.id + 'Country'" class="text-dark-slate-blue">Country</label>
              <input :id="address.id + 'Country'" type="text" class="form-control mb-1" required />
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <h4 class="text-dark-slate-blue">Other Information</h4>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'

import Choices from 'choices.js'

import '/node_modules/choices.js/public/assets/styles/choices.css'

import phoneCodesData from '/json/phoneCodesData.json'

onMounted(() => {
  const senderPhoneCode = document.querySelector('#senderPhoneCode')

  const senderPhoneCodeChoices = new Choices(senderPhoneCode, {
    placeholder: true,
    placeholderValue: 'Code',
    itemSelectText: '',
  })

  const recipientPhoneCode = document.querySelector('#recipientPhoneCode')

  const recipientPhoneCodeChoices = new Choices(recipientPhoneCode, {
    placeholder: true,
    placeholderValue: 'Code',
    itemSelectText: '',
  })

  senderPhoneCode.parentElement.className = senderPhoneCode.className

  recipientPhoneCode.parentElement.className = recipientPhoneCode.className
})
</script>

<script>
export default {
  data() {
    const addresses = ref([
      { title: 'Sender', id: 'sender' },
      { title: 'Recipient', id: 'recipient' },
    ])
    return {
      addresses,
    }
  },
}
</script>

<style>
.choices__input {
  padding: 0px !important;
}
.choices__list {
  padding: 6px 16px 6px 10px;
}
</style>
