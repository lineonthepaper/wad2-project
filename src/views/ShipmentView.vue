<script setup>
import { ref, shallowRef } from 'vue'
import BriefInfo from '@/components/shipment/BriefInfo.vue'
import ServicesSelection from '@/components/shipment/ServicesSelection.vue'
import ShipmentDetails from '@/components/shipment/ShipmentDetails.vue'
import DeliveryDetails from '@/components/shipment/DeliveryDetails.vue'

import { RouterLink, RouterView } from 'vue-router'

import zoneData from '/json/zoneData.json'
import serviceData from '/json/serviceData.json'

import { useShipmentStore } from '@/stores/shipment'
import { useCartStore } from '@/stores/cart'

import '/src/assets/sectionViews.css'
</script>

<script>
export default {
  data() {
    const shipment = useShipmentStore()
    const cart = useCartStore()

    const briefInfo = shallowRef(BriefInfo)
    const servicesSelection = shallowRef(ServicesSelection)
    const shipmentDetails = shallowRef(ShipmentDetails)
    const deliveryDetails = shallowRef(DeliveryDetails)

    const sections = ref([
      {
        title: 'Brief Information',
        id: 'briefInfo',
        info: briefInfo,
        eventEmit: {
          'update-country': this.receiveUpdateCountry,
          'update-shipment-type': this.receiveUpdateShipmentType,
          'update-dimensions': this.receiveUpdateDimensions,
        },
        data: {},
        props: {},
      },
      {
        title: 'Select Services',
        id: 'services',
        info: servicesSelection,
        eventEmit: {
          'update-select': this.receiveUpdateSelect,
        },
        data: {},
        props: {},
      },
      {
        title: 'Shipment Details',
        id: 'shipment',
        info: shipmentDetails,
        eventEmit: {
          'update-ref-numbers': this.receiveUpdateRefNumbers,
          'update-all-items': this.receiveUpdateAllItems,
        },
        data: {},
        props: {},
      },
      {
        title: 'Delivery Details',
        id: 'delivery',
        info: deliveryDetails,
        eventEmit: {
          'update-delivery-details': this.receiveUpdateDeliveryDetails,
        },
        data: {},
        props: {},
      },
    ])

    const objectsEqual = (o1, o2) =>
      Object.keys(o1).length === Object.keys(o2).length &&
      Object.keys(o1).every((p) => o1[p] === o2[p])

    const arraysEqual = (a1, a2) =>
      a1.length === a2.length && a1.every((o, index) => objectsEqual(o, a2[index]))
    return {
      currentElement: 'briefInfo',
      sections,
      componentKey: 0,
      objectsEqual,
      arraysEqual,
      shipment,
      cart,
    }
  },
  methods: {
    forceRerender() {
      this.componentKey++
      console.log('rerendering...')
    },
    toggle(id, index) {
      if (this.currentElement == id) {
        this.currentElement = null
      } else {
        this.currentElement = id
      }

      // compute all previous sections
      for (let i = 0; i < index; i++) {
        // console.log(this.sections[i])
        if (i == 0) {
          this.processBriefInfo()
        }
      }
    },
    receiveUpdateCountry(sendFormId, country) {
      // console.log('received ' + sendFormId + ' ' + country)
      this.sections[0].data[sendFormId] = country
      this.sections[3].props[sendFormId] = country
      if (sendFormId == 'sendFrom') {
        this.shipment.sender.country = country
      } else if (sendFormId == 'sendTo') {
        this.shipment.recipient.country = country
      }
    },
    receiveUpdateShipmentType(shipmentType) {
      // console.log('received ' + shipmentType)
      this.sections[0].data['shipmentType'] = shipmentType
      this.shipment.type = shipmentType
    },
    receiveUpdateDimensions(dimension, value) {
      // console.log('received ' + dimension + ' ' + value)
      this.sections[0].data[dimension] = value
      // console.log(this.sections[0])
      this.shipment.dimensions[dimension] = value
    },
    receiveUpdateSelect(foundService) {
      this.sections[1].data['selectedService'] = foundService
      if (foundService != undefined) {
        for (let service of this.sections[1].props.services) {
          if (
            this.objectsEqual(
              Object.fromEntries(Object.entries(service).filter((e) => e[0] != 'selected')),
              Object.fromEntries(Object.entries(foundService).filter((e) => e[0] != 'selected')),
            )
          ) {
            // console.log('same')
            service.selected = true
          }
        }
        this.sections[3].props['isTracked'] = foundService.isTracked
      } else {
        delete this.sections[3].props['isTracked']
      }
      // console.log(this.sections[1].data)

      this.shipment.service = foundService
    },
    receiveUpdateRefNumbers(refInputs) {
      this.sections[2].data['refNumbers'] = refInputs
      // console.log(this.sections[2].data['refNumbers'])
      this.shipment.refNumbers = refInputs
    },
    receiveUpdateAllItems(items) {
      this.sections[2].data['items'] = items
      // console.log(this.sections[2].data['items'])
      this.registerCompleteItems()
    },
    registerCompleteItems() {
      this.sections[2].data['completeItems'] = {}
      for (let rowId in this.sections[2].data['items']) {
        if ('hsCode' in this.sections[2].data['items'][rowId]) {
          if (Object.keys(this.sections[2].data['items'][rowId]).length == 7) {
            this.sections[2].data['completeItems'][rowId] = this.sections[2].data['items'][rowId]
          }
        } else {
          if (Object.keys(this.sections[2].data['items'][rowId]).length == 6) {
            this.sections[2].data['completeItems'][rowId] = this.sections[2].data['items'][rowId]
          }
        }
      }
      // console.log(this.sections[2].data['completeItems'])
      this.shipment.items = this.sections[2].data['completeItems']

      this.sections[2].props['totalSGD'] = this.totalSGD
    },
    receiveUpdateDeliveryDetails(details) {
      this.sections[3].data = details
      // console.log(this.sections[3].data)

      let properties = [
        'name',
        'phoneCode',
        'phoneNumber',
        'email',
        'line1',
        'line2',
        'line3',
        'city',
        'state',
        'postalCode',
      ]

      for (let property of properties) {
        if (details.sender[property] !== null && details.sender[property] !== '') {
          this.shipment.sender[property] = details.sender[property]
        }
      }

      for (let property of properties) {
        if (details.recipient[property] !== null && details.recipient[property] !== '') {
          this.shipment.recipient[property] = details.recipient[property]
        }
      }
    },
    processBriefInfo() {
      console.log('processing brief info')
      let newProps = {}

      if (
        'sendFrom' in this.sections[0].data &&
        'sendTo' in this.sections[0].data &&
        'shipmentType' in this.sections[0].data &&
        'weight' in this.sections[0].data &&
        'length' in this.sections[0].data &&
        'width' in this.sections[0].data &&
        'height' in this.sections[0].data
      ) {
        // continue

        let zone = null

        for (let obj of zoneData) {
          if (obj.country_code == this.sections[0].data.sendTo) {
            zone = obj.zone_id
            break
          }
        }

        newProps.services = []

        const addToNewProps = function (obj, sections) {
          let price = Number(obj.service_base_cost)

          let curWeight = Number(sections[0].data.weight) - Number(obj.service_base_weight)

          while (curWeight > 0) {
            curWeight -= Number(obj.service_add_weight)
            price += Number(obj.service_add_cost)
          }

          newProps.services.push({
            name: obj.service_name,
            min: obj.service_minimum_days,
            max: obj.service_maximum_days,
            price: price.toFixed(2),
            isTracked: obj.is_tracked,
            selected: false,
          })
        }

        for (let obj of serviceData) {
          if (
            obj.service_zone == zone &&
            obj.max_weight >= this.sections[0].data.weight &&
            obj.max_height >= this.sections[0].data.height &&
            obj.max_width >= this.sections[0].data.width &&
            obj.max_length >= this.sections[0].data.length &&
            obj.service_type == this.sections[0].data.shipmentType
          ) {
            addToNewProps(obj, this.sections)
          }
        }

        if (newProps.services.length == 0 && this.sections[0].data.shipmentType == 'Documents') {
          for (let obj of serviceData) {
            if (
              obj.service_zone == zone &&
              obj.max_weight >= this.sections[0].data.weight &&
              obj.max_height >= this.sections[0].data.height &&
              obj.max_width >= this.sections[0].data.width &&
              obj.max_length >= this.sections[0].data.length
            ) {
              addToNewProps(obj, this.sections)
            }
          }
        }

        // check if newProps and this.sections[1].props are the same
        // console.log(this.sections[1].props)
        if (Object.keys(this.sections[1].props).length === 0) {
          this.sections[1].props = newProps
        } else {
          let temp = this.sections[1].props.services
          if (
            !this.arraysEqual(
              newProps.services.map((service) =>
                Object.fromEntries(Object.entries(service).filter((e) => e[0] != 'selected')),
              ),
              temp.map((service) =>
                Object.fromEntries(Object.entries(service).filter((e) => e[0] != 'selected')),
              ),
            )
          ) {
            this.sections[1].props = newProps
          }
        }
      } else {
        // abort and display no services
        this.sections[1].props.services = []
      }
    },
    addToCart() {
      if (this.shipment.complete == true) {
        this.shipment.totalCostSGD = this.totalSGD
        this.cart.shipments.push(JSON.parse(JSON.stringify(this.shipment.$state)))
        console.log(this.cart.shipments)
        this.shipment.$reset()
        console.log('added to cart')
      }
    },
  },
  computed: {
    briefInfoCompletion() {
      let count = 0
      for (let property in this.sections[0].data) {
        if (this.sections[0].data[property] !== '' && this.sections[0].data[property] !== null) {
          count++
          // console.log(this.sections[0].data[property])
        }
      }
      return count / 7
      // return Object.keys(this.sections[0].data).length / 7
    },
    servicesCompletion() {
      return Object.keys(this.sections[1].data).length / 1
    },
    shipmentDetailsCompletion() {
      if (!('completeItems' in this.sections[2].data)) {
        return 0
      }
      return Object.keys(this.sections[2].data['completeItems']).length > 0 ? 1 : 0
    },
    deliveryDetailsCompletion() {
      if (!('sender' in this.sections[3].data) || !('recipient' in this.sections[3].data)) {
        return 0
      }
      let senderCount = 0
      let recipientCount = 0
      let requiredProperties = ['name', 'email', 'line1', 'city', 'state', 'postalCode']
      for (let r of requiredProperties) {
        if (this.sections[3].data.sender[r] !== null && this.sections[3].data.sender[r] !== '') {
          senderCount++
        }
        if (
          this.sections[3].data.recipient[r] !== null &&
          this.sections[3].data.recipient[r] !== ''
        ) {
          recipientCount++
        }
      }
      return (senderCount + recipientCount) / 12
    },
    overallCompletion() {
      return (
        ((this.briefInfoCompletion +
          this.servicesCompletion +
          this.shipmentDetailsCompletion +
          this.deliveryDetailsCompletion) /
          4) *
        100
      )
    },
    totalSGD() {
      let total = 0
      for (let itemId in this.shipment.items) {
        // console.log('adding ' + this.shipment.items[itemId].costSGD)
        total += this.shipment.items[itemId].costSGD
      }
      // console.log('total: ' + total)
      return total
    },
  },
  watch: {
    overallCompletion(value) {
      if (value === 100) {
        this.shipment.complete = true
      } else {
        this.shipment.complete = false
      }
    },
  },
}
</script>

<template>
  <div class="progress fixed-bottom bg-light border-danger-subtle border" role="progressbar" aria-label="Completion Percentage">
    <div class="progress-bar bg-pink" :style="'width:' + overallCompletion + '%'"></div>
  </div>
  <div class="container-fluid">
    <header>
      <hr />
      <div class="row bg-light-pink justify-content-center airplane-header">
        <div class="col-lg-4 col-md-6 col-sm-8 py-2 text-center">
          <h1 class="jua text-hot-pink">Create Shipment</h1>
        </div>
      </div>
      <hr />
    </header>

    <div
      class="row justify-content-center"
      v-for="(section, index) in sections"
      :key="section.id"
      :id="section.id"
    >
      <div class="col-1 d-flex justify-content-center">
        <div
          :class="[
            'section-toggler',
            'number',
            { 'number-selected': currentElement == section.id },
          ]"
          @keydown.enter="toggle(section.id, index)"
          @click="toggle(section.id, index)"
        >
          {{ index + 1 }}
        </div>
      </div>
      <div class="col-lg-10 col-md-11">
        <div class="section">
          <div
            @click="toggle(section.id, index)"
            class="section-toggler"
            tabindex="0"
            @keydown.enter="toggle(section.id, index)"
          >
            <div class="text-dark-slate-blue justify-content-between d-flex">
              <h2 class="d-inline-block">{{ section.title }}</h2>
              <h2 class="d-inline-block downarrow" v-if="currentElement != section.id">+</h2>
              <h2 class="d-inline-block uparrow" v-else>–</h2>
            </div>
          </div>
          <div class="section-info" :class="{ 'section-info-show': currentElement == section.id }">
            <component
              :is="section.info"
              v-on="section.eventEmit"
              :props="section.props"
              :key="section.id === 'services' ? componentKey : null"
            />
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col text-center">
        <component :to="{ name: 'cart' }" :is="overallCompletion == 100 ? 'RouterLink' : 'span'">
          <button
            type="button"
            class="btn btn-pink next-btn"
            :disabled="overallCompletion != 100"
            @click="overallCompletion == 100 ? addToCart() : null"
          >
            Add to Cart
          </button>
        </component>
      </div>
    </div>
  </div>
  <!-- <button @click="console.log(shipment.$state)">View state</button> -->
  <RouterView />
</template>

<style scoped>
#briefInfo ::v-deep(.choices__list) {
  padding: 4px;
  word-break: normal;
}

#delivery ::v-deep(.choices__input) {
  padding: 0px;
  word-break: normal;
}

#delivery ::v-deep(.choices__list) {
  padding: 6px 6px 6px 4px;
}

#delivery ::v-deep(.choices__item) {
  padding: 0px;
}

#delivery ::v-deep(.choices__item--choice) {
  padding: 4px;
}

#delivery ::v-deep(.choices__placeholder) {
  padding-left: 4px;
}

#shipment ::v-deep(.choices__list) {
  padding: 4px;
}

#shipment ::v-deep(.choices__input) {
  padding: 6px !important;
}

#shipment ::v-deep(.choices__placeholder) {
  padding: 1px;
}
</style>
