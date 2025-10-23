<template>
  <div class="container-fluid">
    <div class="row py-2">
      <div ref="view" class="col"></div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    const labels = useLabelsStore()
    const htmlPage = document.createElement('div')

    const view = useTemplateRef('view')

    const sendToOrFrom = ref([
      { title: 'Send From', id: 'sender' },
      { title: 'Send To', id: 'recipient' },
    ])

    return {
      labels,
      htmlPage,
      view,
      sendToOrFrom,
    }
  },
  mounted() {
    for (let s of this.labels.shipments) {
      let page = document.createElement('div')

      page.className = 'border rounded p-2 row'

      for (let person of this.sendToOrFrom) {
        let addressDetails = page.appendChild(document.createElement('div'))
        addressDetails.className = 'col-5'

        let sendFromHeader = addressDetails.appendChild(document.createElement('h3'))
        sendFromHeader.innerText = person.title

        let name = addressDetails.appendChild(document.createElement('p'))
        name.innerText = s[person.id].name

        let line1 = addressDetails.appendChild(document.createElement('p'))
        line1.innerText = s[person.id].line1

        if (s[person.id].line2 != '' && s[person.id].line2 != null) {
          let line2 = addressDetails.appendChild(document.createElement('p'))
          line2.innerText = s[person.id].line2
        }

        if (s[person.id].line3 != '' && s[person.id].line3 != null) {
          let line3 = addressDetails.appendChild(document.createElement('p'))
          line3.innerText = s[person.id].line3
        }

        let cityState = addressDetails.appendChild(document.createElement('p'))
        cityState.innerText = s[person.id].city + ', ' + s[person.id].state

        let countryPostalCode = addressDetails.appendChild(document.createElement('p'))
        countryPostalCode.innerText =
          this.getCountryName(s[person.id].country) + ' ' + s[person.id].postalCode
      }

      let postageInfoDiv = page.appendChild(document.createElement('div'))
      postageInfoDiv.className = 'col-2'

      let postageInfoP = postageInfoDiv.appendChild(document.createElement('p'))
      postageInfoP.className = 'p-3'
      postageInfoP.innerText = 'Postage Paid'

      // TODO: add postage amount, weight, item info

      this.htmlPage.append(page)
    }

    this.view.append(this.htmlPage)

    const labelPdf = new jsPDF()
    // TODO: convert to pdf

    // TODO: render pdf instead of putting html in view div
  },
  methods: {
    getCountryName(countryCode) {
      for (let obj of countryData) {
        if (obj.code2 == countryCode) {
          return obj.name
        }
      }
      return null
    },
  },
}
</script>

<script setup>
import { useLabelsStore } from '@/stores/labels'

import { ref, useTemplateRef } from 'vue'

import { jsPDF } from 'jspdf'

import countryData from '/json/countryData'
</script>
