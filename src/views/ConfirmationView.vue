<template>
  <div class="container-fluid">
    <header>
      <hr />
      <div class="row bg-light-pink justify-content-center airplane-header">
        <div class="col-lg-4 col-md-6 col-sm-8 py-2 text-center">
          <h1 class="jua text-hot-pink">Your shipment has been registered!</h1>
        </div>
      </div>
      <hr />
    </header>

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
        <div
          :class="[
            'section-toggler',
            'number',
            { 'number-selected': currentElement == section.id },
          ]"
          @keydown.enter="toggle(section.id)"
          @click="toggle(section.id)"
        >
          {{ index + 1 }}
        </div>
      </div>
      <div class="col-lg-10 col-md-11">
        <div class="section">
          <div
            @click="toggle(section.id)"
            class="section-toggler"
            tabindex="0"
            @keydown.enter="toggle(section.id)"
          >
            <div class="text-dark-slate-blue justify-content-between d-flex">
              <h2 class="d-inline-block">{{ section.title }}</h2>
              <h2 class="d-inline-block downarrow" v-if="currentElement != section.id">+</h2>
              <h2 class="d-inline-block uparrow" v-else>–</h2>
            </div>
          </div>
          <div class="section-info" :class="{ 'section-info-show': currentElement == section.id }">
            <component :is="section.info" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    const labelSection = shallowRef(LabelSection)
    const dropOffSection = shallowRef(DropOffSection)
    const finalSection = shallowRef(FinalSection)
    const sections = ref([
      { title: 'Print your shipping label(s)', id: 'labelSection', info: labelSection },
      { title: 'Drop off your mail', id: 'dropOffSection', info: dropOffSection },
      { title: 'Sit back and relax!', id: 'finalSection', info: finalSection },
    ])
    return {
      sections,
      currentElement: 'labelSection',
    }
  },
  methods: {
    toggle(id) {
      if (this.currentElement == id) {
        this.currentElement = null
      } else {
        this.currentElement = id
      }
    },
  },
}
</script>

<script setup>
import { ref, shallowRef } from 'vue'
import LabelSection from '@/components/confirmation/LabelSection.vue'
import DropOffSection from '@/components/confirmation/DropOffSection.vue'
import FinalSection from '@/components/confirmation/FinalSection.vue'

import '/src/assets/sectionViews.css'
</script>
