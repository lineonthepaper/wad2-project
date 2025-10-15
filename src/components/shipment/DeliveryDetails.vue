<template>
  <div class="container-fluid">
    <div v-for="address in addresses" :key="address.id">
      <div class="row py-2">
        <div class="col-md-4">
          <h4 class="text-dark-slate-blue">{{ address.title }} Contact</h4>

          <label :for="address.id + 'Name'" class="text-dark-slate-blue">Name</label>
          <input
            :id="address.id + 'Name'"
            type="text"
            class="form-control mb-1"
            required
            v-model="inputs[address.id].name"
            @change="updateDeliveryDetails"
          />

          <label :for="address.id + 'PhoneNumber'" class="text-dark-slate-blue">Phone Number</label>
          <div class="input-group mb-1">
            <div class="d-inline" style="width: 40%">
              <select
                :id="address.id + 'PhoneCode'"
                class="form-control rounded"
                v-model="inputs[address.id].phoneCode"
                tabindex="0"
                :ref="address.id + 'phoneCode'"
                @change="updateDeliveryDetails"
              >
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
                v-model="inputs[address.id].phoneNumber"
                @change="updateDeliveryDetails"
              />
            </div>
          </div>

          <label :for="address.id + 'Email'" class="text-dark-slate-blue">Email</label>
          <input
            :id="address.id + 'Email'"
            type="text"
            class="form-control mb-1"
            placeholder="optional"
            v-model="inputs[address.id].email"
            @change="updateDeliveryDetails"
          />
        </div>
        <div class="col-md-8">
          <h4 class="text-dark-slate-blue">{{ address.title }} Address</h4>
          <div class="row">
            <div class="col-xl-8">
              <div v-for="i in 3" :key="i">
                <label :for="address.id + 'Line' + i" class="text-dark-slate-blue"
                  >Address Line {{ i }}</label
                >
                <input
                  :id="address.id + 'Line' + i"
                  type="text"
                  class="form-control mb-1"
                  :required="i === 1 ? true : null"
                  v-model="inputs[address.id]['line' + i]"
                  @change="updateDeliveryDetails"
                />
              </div>
            </div>

            <div class="col-xl-4">
              <div class="row">
                <div class="col-sm-6">
                  <label :for="address.id + 'City'" class="text-dark-slate-blue">City</label>
                  <input
                    :id="address.id + 'City'"
                    type="text"
                    class="form-control mb-1"
                    v-model="inputs[address.id].city"
                    @change="updateDeliveryDetails"
                  />
                </div>

                <div class="col-sm-6">
                  <label :for="address.id + 'State'" class="text-dark-slate-blue">State</label>
                  <input
                    :id="address.id + 'State'"
                    type="text"
                    class="form-control mb-1"
                    v-model="inputs[address.id].state"
                    @change="updateDeliveryDetails"
                  />
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
                v-model="inputs[address.id].postalCode"
                @change="updateDeliveryDetails"
              />

              <label :for="address.id + 'Country'" class="text-dark-slate-blue">Country</label>
              <input
                :id="address.id + 'Country'"
                type="text"
                class="form-control mb-1"
                required
                disabled
                :value="
                  address.id == 'sender'
                    ? senderCountry
                    : address.id == 'recipient'
                      ? recipientCountry
                      : ''
                "
              />
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row text-dark-slate-blue" v-if="'isTracked' in props">
      <h4>Other Information</h4>
      <div v-if="props.isTracked">
        Please drop off your mail at any
        <a href="https://www.singpost.com/locate-us">SingPost Post Office.</a>
      </div>
      <div v-else>
        You may drop off your mail at any
        <a href="https://www.singpost.com/locate-us">SingPost Posting Box.</a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'

import Choices from 'choices.js'

import '/node_modules/choices.js/public/assets/styles/choices.css'

import phoneCodesData from '/json/phoneCodesData.json'

import countryData from '/json/countryData.json'

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
    let senderName = null,
      senderPhoneCode = null,
      senderPhoneNumber = null,
      senderEmail = null,
      senderLine1 = null,
      senderLine2 = null,
      senderLine3 = null,
      senderCity = null,
      senderState = null,
      senderPostalCode = null
    let recipientName = null,
      recipientPhoneCode = null,
      recipientPhoneNumber = null,
      recipientEmail = null,
      recipientLine1 = null,
      recipientLine2 = null,
      recipientLine3 = null,
      recipientCity = null,
      recipientState = null,
      recipientPostalCode = null
    const inputs = ref({
      sender: {
        name: senderName,
        phoneCode: senderPhoneCode,
        phoneNumber: senderPhoneNumber,
        email: senderEmail,
        line1: senderLine1,
        line2: senderLine2,
        line3: senderLine3,
        city: senderCity,
        state: senderState,
        postalCode: senderPostalCode,
      },
      recipient: {
        name: recipientName,
        phoneCode: recipientPhoneCode,
        phoneNumber: recipientPhoneNumber,
        email: recipientEmail,
        line1: recipientLine1,
        line2: recipientLine2,
        line3: recipientLine3,
        city: recipientCity,
        state: recipientState,
        postalCode: recipientPostalCode,
      },
    })
    const addresses = ref([
      { title: 'Sender', id: 'sender', inputs: inputs.value.sender },
      { title: 'Recipient', id: 'recipient', inputs: inputs.value.recipient },
    ])
    return {
      addresses,
      inputs,
    }
  },
  props: ['props'],
  emits: ['update-delivery-details'],
  computed: {
    senderCountry() {
      if (this.props !== undefined) {
        if ('sendFrom' in this.props) {
          for (let obj of countryData) {
            if (obj.code2 == this.props.sendFrom) {
              return obj.name
            }
          }
        }
      }
      return ''
    },
    recipientCountry() {
      if (this.props !== undefined) {
        if ('sendTo' in this.props) {
          for (let obj of countryData) {
            if (obj.code2 == this.props.sendTo) {
              return obj.name
            }
          }
        }
      }
      return ''
    },
  },
  methods: {
    updateDeliveryDetails() {
      this.$emit('update-delivery-details', this.inputs)
    },
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
