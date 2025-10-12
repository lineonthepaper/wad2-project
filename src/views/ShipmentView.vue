<script setup>
import { ref, shallowRef } from 'vue'
import BriefInfo from '@/components/shipment/BriefInfo.vue'
import ServicesSelection from '@/components/shipment/ServicesSelection.vue'
import ShipmentDetails from '@/components/shipment/ShipmentDetails.vue'
import DeliveryDetails from '@/components/shipment/DeliveryDetails.vue'

import axios from 'axios'
</script>

<script>
export default {
  data() {
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
        data: {},
        props: {},
      },
      {
        title: 'Delivery Details',
        id: 'delivery',
        info: deliveryDetails,
        data: {},
        props: {},
      },
    ])
    return {
      currentElement: 'briefInfo',
      sections,
      componentKey: 0,
    }
  },
  methods: {
    forceRerender() {
      this.componentKey++
      console.log('rerendering...')
    },
    toggle(id, index, event = null) {
      if (event) {
        if (event.key != 'Enter' && event.key != ' ') {
          return
        }
      }
      if (this.currentElement == id) {
        this.currentElement = null
      } else {
        this.currentElement = id
      }

      // compute all previous sections
      for (let i = 0; i < index; i++) {
        console.log(this.sections[i])
        if (i == 0) {
          this.processBriefInfo()
        }
      }
    },
    receiveUpdateCountry(sendFormId, country) {
      // console.log('received ' + sendFormId + ' ' + country)
      this.sections[0].data[sendFormId] = country
    },
    receiveUpdateShipmentType(shipmentType) {
      // console.log('received ' + shipmentType)
      this.sections[0].data['shipmentType'] = shipmentType
    },
    receiveUpdateDimensions(dimension, value) {
      // console.log('received ' + dimension + ' ' + value)
      this.sections[0].data[dimension] = value
      // console.log(this.sections[0])
    },
    receiveUpdateSelect(foundService) {
      this.sections[1].data['selectedService'] = foundService
      // console.log(this.sections)
    },
    processBriefInfo() {
      console.log('processing brief info')
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
        this.sections[1].props = {}
        let zone = null
        axios
          .post('/api/mail.php', {
            method: 'getZone',
            countryCode: this.sections[0].data.sendTo,
          })
          .then((zoneResponse) => {
            console.log(zoneResponse.data)
            zone = zoneResponse.data.zone

            // console.log('searching for matching services...')
            axios
              .post('/api/mail.php', {
                method: 'getMatchingServices',
                zone: zone,
                type: this.sections[0].data.shipmentType,
                weight: this.sections[0].data.weight,
                height: this.sections[0].data.height,
                width: this.sections[0].data.width,
                length: this.sections[0].data.length,
              })
              .then((serviceResponse) => {
                console.log(serviceResponse.data.services)

                this.sections[1].props.services = []

                for (let s of serviceResponse.data.services) {
                  let price = Number(s.basecost)

                  let curWeight = Number(this.sections[0].data.weight) - Number(s.baseweight)

                  while (curWeight > 0) {
                    curWeight -= Number(s.addweight)
                    price += Number(s.addcost)
                  }

                  this.sections[1].props.services.push({
                    name: s.name,
                    min: s.min,
                    max: s.max,
                    price: price.toFixed(2),
                    selected: false,
                  })
                }

                this.forceRerender()
                // console.log(this.sections[1].props.services)
              })
              .catch((error) => {
                console.error(error)
              })
          })
          .catch((error) => {
            console.error(error)
          })
      } else {
        // abort and display no services
        this.sections[1].props.services = []
      }
    },
  },
}
</script>

<template>
  <div class="container-fluid">
    <hr />
    <div class="row bg-light-pink justify-content-center airplane-header">
      <div class="col-lg-4 col-md-6 col-sm-8 py-2 text-center">
        <h1 class="jua text-hot-pink">Create Shipment</h1>
      </div>
    </div>
    <hr />

    <div class="row justify-content-center" v-for="(section, index) in sections" :key="section.id">
      <div class="col-1 d-flex justify-content-center">
        <div class="number" :class="{ 'number-selected': currentElement == section.id }">
          {{ index + 1 }}
        </div>
      </div>
      <div class="col-md-8">
        <div class="section">
          <div
            @click="toggle(section.id, index)"
            class="section-toggler"
            tabindex="0"
            @keydown="toggle(section.id, index, $event)"
          >
            <div class="text-dark-slate-blue justify-content-between d-flex">
              <h2 class="d-inline-block">{{ section.title }}</h2>
              <h2 class="d-inline-block downarrow" v-if="currentElement != section.id">▼</h2>
              <h2 class="d-inline-block uparrow" v-else>▲</h2>
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
  </div>
</template>

<style scoped>
.section {
  border: 2px var(--dark-pink) solid;
  border-radius: 8px;
  padding: 20px;
  margin: 10px;
}

.section-toggler {
  cursor: pointer;
  font-weight: bold;
  user-select: none;
}

.section-info {
  max-height: 0px;
  overflow: hidden;
  transition: max-height 0.15s;
}

.section-info-show {
  max-height: calc(1px * infinity);
  overflow: visible;
  transition: max-height 0.35s;
}

.number {
  border: 2px var(--dark-pink) solid;
  border-radius: 50%;
  aspect-ratio: 1 / 1;
  height: 1.5em;
  text-align: center;
  line-height: 1.5em;
  padding: 10px;
  box-sizing: content-box;
  font-weight: bold;
  display: inline-block;
  background-color: white;
  margin: 10px;
  font-size: 2em;
  color: var(--dark-slate-blue);
}

.number-selected {
  background-color: var(--dark-pink);
  color: white;
}
</style>
