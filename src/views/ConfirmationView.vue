<template>
  <div class="container-fluid">
    <header>
      <hr />
      <div class="row bg-light-pink justify-content-center airplane-header">
        <div class="col-lg-4 col-md-6 col-sm-8 py-2 text-center">
          <h1 class="jua text-hot-pink" v-if="labels.shipments.length != 0">
            Your shipment has been registered!
          </h1>
          <h1 class="jua text-hot-pink" v-else>???</h1>
        </div>
      </div>
      <hr />
    </header>

    <template v-if="labels.shipments.length != 0">
      <div class="row text-dark-slate-blue py-2">
        <div class="col">
          <h2 class="text-center">What's next?</h2>
        </div>
      </div>

      <div
        class="row justify-content-center"
        v-for="(section, index) in sections"
        :key="section.id"
        :id="section.id"
      >
        <div class="col-1 d-flex justify-content-center">
          <div class="section-no-toggle number">
            {{ index + 1 }}
          </div>
        </div>
        <div class="col-lg-10 col-md-11">
          <div class="section">
            <div class="section-no-toggle" tabindex="0" @keydown.enter="toggle(section.id)">
              <div class="text-dark-slate-blue justify-content-between d-flex">
                <h2 class="d-inline-block">{{ section.title }}</h2>
              </div>
            </div>
            <div class="section-info section-info-show">
              <component :is="section.info" />
            </div>
          </div>
        </div>
      </div>
    </template>

    <template v-else>
      <div class="row justify-content-center py-2 text-dark-slate-blue">
        <h3 class="text-center my-2">
          You have no shipments! Start shipping with Fluffy Shipping today!
        </h3>
        <RouterLink :to="{ name: 'shipment' }" class="text-center my-2">
          <button type="button" class="btn btn-pink next-btn">Create Shipment</button>
        </RouterLink>
      </div>
    </template>
  </div>
  <RouterView />
</template>

<script>
export default {
  data() {
    const labelsSection = shallowRef(LabelsSection)
    const dropOffSection = shallowRef(DropOffSection)
    const finalSection = shallowRef(FinalSection)
    const sections = ref([
      { title: 'Print your shipping label(s)', id: 'labelsSection', info: labelsSection },
      { title: 'Drop off your mail', id: 'dropOffSection', info: dropOffSection },
      { title: 'Sit back and relax!', id: 'finalSection', info: finalSection },
    ])
    const labels = useLabelsStore()
    return {
      sections,
      labels,
    }
  },
  mounted() {
    if (this.labels.shipments.length === 0) {
      this.$router.push({ name: 'shipment' })
    }
  },
}
</script>

<script setup>
import { ref, shallowRef } from 'vue'
import LabelsSection from '@/components/confirmation/LabelsSection.vue'
import DropOffSection from '@/components/confirmation/DropOffSection.vue'
import FinalSection from '@/components/confirmation/FinalSection.vue'

import { useLabelsStore } from '@/stores/labels'

import { RouterLink, RouterView } from 'vue-router'

import '/src/assets/sectionViews.css'
</script>
