<script setup>
import { ref, shallowRef } from 'vue'
import BriefInfo from '@/components/shipment/BriefInfo.vue'
import ServicesSelection from '@/components/shipment/ServicesSelection.vue'
import ShipmentDetails from '@/components/shipment/ShipmentDetails.vue'
import DeliveryDetails from '@/components/shipment/DeliveryDetails.vue'

const briefInfo = shallowRef(BriefInfo)
const servicesSelection = shallowRef(ServicesSelection)
const shipmentDetails = shallowRef(ShipmentDetails)
const deliveryDetails = shallowRef(DeliveryDetails)

const sections = ref([
  { title: 'Brief Information', id: 'briefInfo', info: briefInfo },
  {
    title: 'Select Services',
    id: 'services',
    info: servicesSelection,
  },
  {
    title: 'Shipment Details',
    id: 'shipment',
    info: shipmentDetails,
  },
  {
    title: 'Delivery Details',
    id: 'delivery',
    info: deliveryDetails,
  },
])
</script>

<script>
export default {
  data() {
    return {
      currentElement: 'briefInfo',
    }
  },
  methods: {
    toggle(id, event = null) {
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
            @click="toggle(section.id)"
            class="section-toggler"
            tabindex="0"
            @keydown="toggle(section.id, $event)"
          >
            <div class="text-dark-slate-blue justify-content-between d-flex">
              <h2 class="d-inline-block">{{ section.title }}</h2>
              <h2 class="d-inline-block downarrow" v-if="currentElement != section.id">▼</h2>
              <h2 class="d-inline-block uparrow" v-else>▲</h2>
            </div>
          </div>
          <div class="section-info" :class="{ 'section-info-show': currentElement == section.id }">
            <component :is="section.info" />
          </div>
        </div>
      </div>
    </div>

    <!-- <div class="row justify-content-center">
      <div class="col-1 d-flex justify-content-center">
        <div class="number number-selected">1</div>
      </div>
      <div class="col-md-8">
        <div class="section">
          <div @click="toggle('briefInfo')" class="section-toggler">
            <div class="text-dark-slate-blue justify-content-between d-flex">
              <h2 class="d-inline-block">Brief Information</h2>
              <h2 class="d-inline-block downarrow" v-if="currentElement != 'briefInfo'">▼</h2>
              <h2 class="d-inline-block uparrow" v-else>▲</h2>
            </div>
          </div>
          <div class="section-info" :class="{ 'section-info-show': currentElement == 'briefInfo' }">
            blah blah blah
          </div>
        </div>
      </div>
    </div>

    <div class="row justify-content-center">
      <div class="col-1 d-flex justify-content-center">
        <div class="number">2</div>
      </div>
      <div class="col-md-8">
        <div class="section">
          <h2 class="text-dark-slate-blue">Select Service</h2>
        </div>
      </div>
    </div>

    <div class="row justify-content-center">
      <div class="col-1 d-flex justify-content-center">
        <div class="number">3</div>
      </div>
      <div class="col-md-8">
        <div class="section">
          <h2 class="text-dark-slate-blue">Shipment Details</h2>
        </div>
      </div>
    </div>

    <div class="row justify-content-center">
      <div class="col-1 d-flex justify-content-center">
        <div class="number">4</div>
      </div>
      <div class="col-md-8">
        <div class="section">
          <h2 class="text-dark-slate-blue">Delivery Details</h2>
        </div>
      </div>
    </div> -->
  </div>
</template>

<style scoped>
.section {
  border: 2px #ff759e solid;
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
  max-height: 100vh;
  overflow: visible;
  transition: max-height 0.35s;
}

.number {
  border: 2px #ff759e solid;
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
  color: #455a64;
}

.number-selected {
  background-color: #ff759e;
  color: white;
}
</style>
