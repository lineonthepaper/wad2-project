<template>
  <div class="container-fluid">
    <header>
      <hr />
      <div class="row bg-light-pink justify-content-center airplane-header">
        <div class="col-lg-3 col-md-6 col-sm-6 py-2 text-center">
          <h1 class="jua text-hot-pink">Cart</h1>
        </div>
      </div>
      <hr />
    </header>

    <!-- <div class="row">
      <button @click="console.log(cart.$state)">Cart state</button>
      <button @click="console.log(shipment.$state)">Shipment state</button>
    </div> -->

    <div class="row justify-content-center text-dark-slate-blue py-2" v-if="!noErrors">
      <div class="col-auto text-center bg-warning-subtle rounded border p-2">
        There were one or more errors with your shipment. <br />
        Please check your information and try again.
      </div>
    </div>

    <div v-if="cart.shipments.length != 0">
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
                <img
                  src="/shipment/stamp.png"
                  alt="Stamp"
                  :class="{ 'img-selected': s.selected }"
                />
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-8">
              <h3>Weight: {{ s.dimensions.weight }} kg</h3>
              <h3>Declared Value: {{ s.totalCostSGD }} SGD</h3>
            </div>
            <div class="col-md-4 text-center">
              <button type="button" class="btn btn-pink next-btn" @click="deleteShipment(index)">
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="row justify-content-center py-2">
        <div class="col text-center">
          <button
            type="button"
            class="btn btn-pink next-btn"
            @click="cart.numSelectedShipments > 0 ? checkOut() : null"
          >
            {{ loading ? 'Loading...' : 'Check Out' }}
          </button>
        </div>
      </div>
    </div>

    <div class="row justify-content-center py-2 text-dark-slate-blue" v-else>
      <h3 class="text-center my-2">
        You have no shipments! Start shipping with Fluffy Shipping today!
      </h3>
      <RouterLink :to="{ name: 'shipment' }" class="text-center my-2">
        <button type="button" class="btn btn-pink next-btn">Create Shipment</button>
      </RouterLink>
    </div>
  </div>
  <RouterView />
</template>

<script setup>
import { useShipmentStore } from '@/stores/shipment'
import { useCartStore } from '@/stores/cart'
import { useLabelsStore } from '@/stores/labels'
import { ref } from 'vue'

import { RouterLink, RouterView } from 'vue-router'

import countryData from '/json/countryData.json'
import zoneData from '/json/zoneData.json'

import axios from 'axios'
</script>

<script>
export default {
  data() {
    const shipment = useShipmentStore()
    const cart = useCartStore()
    const labels = useLabelsStore()
    const sendFromOrTo = ref([
      { title: 'Send From', id: 'sender' },
      { title: 'Send To', id: 'recipient' },
    ])
    return {
      shipment,
      cart,
      labels,
      sendFromOrTo,
      statuses: ref([]),
      loading: false,
      mailIds: ref([]),
    }
  },
  methods: {
    getZone(countryCode) {
      for (let obj of zoneData) {
        if (obj.country_code == countryCode) {
          return obj.zone_id
        }
      }
      return null
    },
    getCountryName(countryCode) {
      for (let obj of countryData) {
        if (obj.code2 == countryCode) {
          return obj.name
        }
      }
      return null
    },
    deleteShipment(index) {
      this.cart.shipments.splice(index, 1)
    },
    until(conditionFunction) {
      const poll = (resolve) => {
        if (conditionFunction()) resolve()
        else setTimeout(() => poll(resolve), 400)
      }

      return new Promise(poll)
    },
    checkOut() {
      this.loading = true
      for (let shipment of this.cart.selectedShipments) {
        let people = ['sender', 'recipient']
        let addressIds = {}

        // add to db
        for (let person of people) {
          axios
            .post('/api/addresses.php', {
              method: 'addAddress',
              name: shipment[person].name,
              email: shipment[person].email,
              phone: shipment[person].phoneNumber,
              phoneCountryCode: shipment[person].phoneCode,
              address: {
                addressLines: [
                  shipment[person].line1,
                  shipment[person].line2,
                  shipment[person].line3,
                ],
                postalCode: shipment[person].postalCode,
                countryCode: shipment[person].country,
              },
            })
            .then((response) => {
              // console.log(response.data)
              if (response.data.success) {
                addressIds[person] = response.data.addressId
              }

              // console.log(addressIds)
            })
            .catch((error) => {
              console.error(error)
            })
        }

        let zone = this.getZone(shipment.recipient.country)

        async function waitForAddressIds(statuses, until, mailIds, sendEmail) {
          await until(() => Object.keys(addressIds).length == 2)
          axios
            .post('/api/mail.php', {
              method: 'addMail',
              customerEmail: shipment.sender.email,
              senderAddressId: addressIds.sender,
              recipientAddressId: addressIds.recipient,
              mailItems: shipment.items,
              parcelLength: shipment.dimensions.length,
              parcelWidth: shipment.dimensions.width,
              parcelHeight: shipment.dimensions.height,
              service: {
                name: shipment.service.name,
                type: shipment.type,
                zone: zone,
              },
            })
            .then((response) => {
              // console.log(response.data)
              if (!response.data.success) {
                statuses.push(false)
                mailIds.push(null)
              } else {
                statuses.push(true)
                mailIds.push(response.data.mailId)
              }
              // then send email

              sendEmail(shipment, response.data.mailId)
            })
            .catch((error) => {
              console.error(error)
            })
        }

        waitForAddressIds(this.statuses, this.until, this.mailIds, this.sendEmail)
      }

      async function waitForAddMail(
        statuses,
        numSelectedShipments,
        noErrors,
        router,
        until,
        loading,
        cart,
        labels,
        mailIds,
      ) {
        // console.log(statuses)

        await until(() => statuses.length == numSelectedShipments)

        loading = false

        let remainingShipments = []

        labels.$reset()

        for (let i = 0; i < statuses.length; i++) {
          if (!statuses[i]) {
            remainingShipments.push(cart.shipments[i])
          } else {
            labels.shipments.push(JSON.parse(JSON.stringify(cart.shipments[i])))
            labels.shipments[labels.shipments.length - 1].mailId = mailIds[i]
          }
        }

        if (noErrors) {
          // only update cart if no errors in any of the shipments
          cart.shipments = remainingShipments

          router.push({ name: 'confirmation' })
        } else {
          // reset labels to default if there are any errors at all in any of the selected shipments
          labels.$reset()
        }
      }

      waitForAddMail(
        this.statuses,
        this.cart.numSelectedShipments,
        this.noErrors,
        this.$router,
        this.until,
        this.loading,
        this.cart,
        this.labels,
        this.mailIds,
      )
    },
    sendEmail(shipment, mailId) {
      axios
        .post('/api/sendEmail.php', {
          from: {
            name: 'Fluffy Shipping @ SingPost',
          },
          to: [
            {
              email: shipment.sender.email,
              name: shipment.sender.name,
            },
          ],
          subject: 'Your mail has been registered!',
          mailId: mailId,
          shipmentType: shipment.type,
        })
        .then((response) => {
          console.log(response.data)
        })
        .catch((error) => {
          console.error(error)
        })
    },
  },
  computed: {
    noErrors() {
      for (let status of this.statuses) {
        if (status == false) {
          return false
        }
      }
      return true
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
