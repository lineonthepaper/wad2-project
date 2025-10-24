<template>
  <div class="container-fluid">
    <div class="row py-2">
      <iframe
        style="width: 100vw; height: 75vh"
        type="application/pdf"
        id="pdfViewer"
        src="about:blank"
      ></iframe>
      <div ref="view" class="col overflow-hidden" style="height: 0"></div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    const labels = useLabelsStore()
    const htmlPage = document.createElement('div')

    const view = useTemplateRef('view')
    // htmlPage.style.width = '297mm'

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
    const labelPdf = new jsPDF({
      orientation: 'l',
      unit: 'mm',
      format: 'a4',
      compress: true,
    })

    for (let s of this.labels.shipments) {
      let page = document.createElement('div')

      page.className = 'border rounded p-2 row page my-2'

      for (let person of this.sendToOrFrom) {
        let addressDetails = page.appendChild(document.createElement('div'))
        addressDetails.className = 'col-5'

        let sendToOrFromHeader = addressDetails.appendChild(document.createElement('h3'))
        sendToOrFromHeader.innerText = person.title

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
      postageInfoP.className = 'p-3 fw-bold border rounded border-secondary'
      postageInfoP.innerText = 'Postage Paid\n' + s.service.price + ' SGD'

      let barCode = postageInfoDiv.appendChild(document.createElement('canvas'))
      barCode.id = 'barCode' + s.mailId
      barCode.className = 'w-100'
      // console.log(barCode.id)

      let weightInfo = postageInfoDiv.appendChild(document.createElement('p'))
      weightInfo.innerText = 'Weight: ' + s.dimensions.weight + ' kg'

      let itemDetails = page.appendChild(document.createElement('div'))
      itemDetails.className = 'col'

      let itemDetailsHeader = itemDetails.appendChild(document.createElement('h3'))
      itemDetailsHeader.innerText = 'Item Details'

      let itemDetailsTable = itemDetails.appendChild(document.createElement('table'))
      itemDetailsTable.className = 'table'

      let theadTr = itemDetailsTable.appendChild(document.createElement('tr'))

      const itemDetailsTableHeaders = ['Description', 'Price', 'Weight', 'HS code']

      for (let title of itemDetailsTableHeaders) {
        let th = theadTr.appendChild(document.createElement('th'))
        th.innerText = title
      }

      theadTr.className = 'border-bottom'

      for (let rowId in s.items) {
        let tr = itemDetailsTable.appendChild(document.createElement('tr'))

        let desc = tr.appendChild(document.createElement('td'))
        desc.innerText = s.items[rowId].itemDescription

        let price = tr.appendChild(document.createElement('td'))
        price.innerText = s.items[rowId].costSGD + ' SGD'

        let weight = tr.appendChild(document.createElement('td'))
        weight.innerText = s.items[rowId].itemWeight + ' kg'

        let hsCode = tr.appendChild(document.createElement('td'))
        if (
          s.items[rowId].hsCode !== undefined &&
          s.items[rowId].hsCode !== null &&
          s.items[rowId].hsCode !== ''
        ) {
          hsCode.innerText = s.items[rowId].hsCode
        }
      }

      this.htmlPage.append(page)
    }

    this.view.append(this.htmlPage)

    for (let s of this.labels.shipments) {
      JsBarcode('#barCode' + s.mailId, 'SGP' + s.mailId, {
        // lineColor: '#ff759e',
        height: 80,
        width: 4,
      })
      // console.log('added barcode')
    }

    console.log(this.htmlPage.getElementsByClassName('page').length)

    function addPageToPdf(pages, index = 0) {
      if (pages.length == 0) {
        labelPdf.deletePage(index + 1)
        // window.open(labelPdf.output('bloburl'))
        document.getElementById('pdfViewer').src = labelPdf.output('datauristring')
        return
      }

      let pageWidth = 297
      let margin = 10
      let srcWidth = pages[0].scrollWidth
      let scale = (pageWidth - margin * 2) / srcWidth

      labelPdf.html(pages[0], {
        x: margin,
        y: labelPdf.internal.pageSize.getHeight() * index + margin,
        callback(labelPdf) {
          labelPdf.addPage()
          addPageToPdf(pages.slice(1), ++index)
        },
        html2canvas: { scale: scale, letterRendering: true },
      })
    }

    let pagesArr = Array.from(this.htmlPage.getElementsByClassName('page'))

    addPageToPdf(pagesArr)
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
import JsBarcode from 'jsbarcode'

import countryData from '/json/countryData'
</script>
