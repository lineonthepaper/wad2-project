<template>
  <header>
      <hr />
      <div class="row bg-light-pink justify-content-center airplane-header">
        <div class="col-lg-4 col-md-6 col-sm-8 py-2 text-center">
          <h1 class="jua text-hot-pink">Frequently Asked Questions</h1>
        </div>
      </div>
      <hr />
  </header>
  <div class="faq-container">
    <div class="container py-4">
      <div class="row">
        <div class="col-12">
          <h2 class="text-center mb-4">Enter Your Questions</h2>
        </div>
      </div>

      <div class="row justify-content-center mb-4">
        <div class="col-12 col-md-8 col-lg-6">
          <div class="faq-search">
            <input
              type="text"
              v-model="searchQuery"
              class="form-control form-control-lg"
              placeholder="Type your question..."
              @input="handleSearch"
            />
          </div>
        </div>
      </div>

      <!-- Mobile View (Dropdown) -->
      <div class="row d-lg-none mb-3">
        <div class="col-12">
          <select v-model="activeTab" class="form-select form-select-lg" @change="setActiveTab(activeTab)">
            <option v-for="tab in tabs" :key="tab.id" :value="tab.id">{{ tab.name }}</option>
          </select>
        </div>
      </div>

      <!-- Desktop View (Tabs) -->
      <div class="row d-none d-lg-block">
        <!-- First Row of Tabs -->
        <div class="col-12 mb-2">
          <ul class="nav nav-tabs nav-justified" role="tablist">
            <li class="nav-item" v-for="tab in firstRowTabs" :key="tab.id">
              <button
                class="nav-link text-nowrap"
                :class="{ active: activeTab === tab.id }"
                @click="setActiveTab(tab.id)"
                type="button"
                role="tab"
              >
                {{ tab.name }}
              </button>
            </li>
          </ul>
        </div>
        
        <!-- Second Row of Tabs -->
        <div class="col-12">
          <ul class="nav nav-tabs nav-justified" role="tablist">
            <li class="nav-item" v-for="tab in secondRowTabs" :key="tab.id">
              <button
                class="nav-link text-nowrap"
                :class="{ active: activeTab === tab.id }"
                @click="setActiveTab(tab.id)"
                type="button"
                role="tab"
              >
                {{ tab.name }}
              </button>
            </li>
          </ul>
        </div>
      </div>

      <div class="row mt-3">
        <div class="col-12">
          <div class="tab-content">
            <div
              v-for="tab in tabs"
              :key="tab.id"
              v-show="activeTab === tab.id"
              class="tab-pane fade show active"
            >
              <div class="accordion" :id="`accordion${tab.id}`">
                <div v-for="item in filteredItems(tab.id)" :key="item.id" class="accordion-item">
                  <h2 class="accordion-header">
                    <button
                      class="accordion-button"
                      :class="{ collapsed: !isOpen(item.id) }"
                      type="button"
                      @click="toggleAccordion(item.id)"
                    >
                      <span class="me-auto">{{ item.question }}</span>
                    </button>
                  </h2>
                  <div
                    v-show="isOpen(item.id)"
                    class="accordion-collapse"
                    :class="{ show: isOpen(item.id) }"
                  >
                    <div class="accordion-body" v-html="item.answer"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="fixed-bottom text-end p-3">
      <a href="quickguide.pdf" download class="text-decoration-none">
        <button class="btn btn-primary btn-lg shadow">
          <i class="bi bi-download me-2"></i>Download Quick Guide
        </button>
      </a>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FAQ',
  data() {
    return {
      searchQuery: '',
      activeTab: 'gettingStarted',
      openItems: new Set(),
      tabs: [
        { id: 'gettingStarted', name: 'Getting Started' },
        { id: 'payment', name: 'Payment' },
        { id: 'services', name: 'Selecting a Service' },
        { id: 'labels', name: 'Printing Labels' },
        { id: 'tracking', name: 'Track Your Parcels' },
        { id: 'registration', name: 'Registration Process' },
        { id: 'shipment', name: 'Shipment Details' },
        { id: 'packaging', name: 'Ordering Packaging Materials' }
      ],
      faqItems: [
        { id: 'gs1', category: 'gettingStarted', question: 'What are the requirements to use fluffy shipping?', answer: 'You need a working PC with internet connection and a printer.' },
        { id: 'gs2', category: 'gettingStarted', question: 'Which operating systems and browsers do fluffy shipping support?', answer: 'fluffy shipping is designed to work on the following operating systems and browsers:<ol><li><u>Windows XP (32 bit)</u><br> Internet explorer 8, Firefox FF10/FF11, Google chrome v17/v18</li><li><u>Windows 7 (32 bit)</u><br>Internet explorer 8 and above, Firefox FF10/FF11, Google chrome v17/v18</li><li><u>Windows 7 (64 bit)</u><br>Internet explorer 8, Firefox FF10/FF11, Google chrome v17/v18</li><li><u>MAC OS X 10.6 and 10.7</u><br>Safari 5 and 5.1</li><li><u>Adobe Acrobat 6 or later versions</u></li><li><u>Active scripting needs to be enabled</u></li><li><u>Pop-up blocker needs to be disabled</u></li></ol>' },
        { id: 'gs3', category: 'gettingStarted', question: 'How do I ship as a non-registered consumer?', answer: 'Please click on "Ship as Guest" in the login page' },
        { id: 'gs4', category: 'gettingStarted', question: 'What is the difference between "Sign Up" and "Ship as Guest"?', answer: 'Signed up accounts will enjoy additional features such as Address book, Bulk-upload of shipments and past shipments tracking functions.' },

        { id: 'p1', category: 'payment', question: 'How do I proceed with my transaction after I completed the payment for my shipment?', answer: 'Please do not close the vPOST payment browser after you have completed your payment. Click "Done" on the payment browser and you will be redirected to the website.' },
        { id: 'p2', category: 'payment', question: 'If I made a payment but decided not to send the item, can I get a refund?', answer: 'Please note that no refund would be allowed once a booking has been confirmed and paid.' },
        { id: 'p3', category: 'payment', question: 'If the package I weighed at home is at a lower weight than your final lodgement weight, why must I top up the difference?', answer: 'Different weighing scales and location may register difference in weight. Please be assured that our weighing scales are calibrated and certified by the Office of Weights and Measures every year.' },
        { id: 'p4', category: 'payment', question: 'If the weight of the item is at a lower weight than I have paid for, can I get a refund?', answer: 'Please note that there will be no refund once a booking has been confirmed and paid. Do ensure your declared package weight corresponds to the actual weight of your item before making payment.' },
        { id: 'p5', category: 'payment', question: 'What happens if there is a short payment for the item that I am sending?', answer: 'If your item is collected by our courier, our customer service staff will contact you to top-up the difference. If your item is submitted at our post office, our counter staff will collect the short payment when you submit your item. Please take note that your item would be on hold until payment has been received.' },

        { id: 's1', category: 'services', question: 'What are the services available in fluffy shipping?', answer: '<ul><li>For Registered Article service, please click <a href="https://www.singpost.com/business/mail-solutions/mailing/registered-service" target="_blank">here</a></li><li>For Speedpost International services available, please click <a href="https://www.speedpost.com.sg/what-we-offer/international-delivery.html" target="_blank">here</a></li><li>For Speedpost Domestic services services available, please click <a href="https://www.speedpost.com.sg/what-we-offer/local-delivery.html" target="_blank">here</a></li></ul>' },
        { id: 's2', category: 'services', question: 'Why do I sometimes get fewer service options to choose from?', answer: '<ul><li>The service options available are determined by the origin, destination, weight, dimension and/or category of your parcel.</li><li>If no services are displayed under "Enter a shipment" in Step 2, please return to Step 1 and check your destination country, weight, dimension and/or category of shipment entered.</li></ul>' },

        { id: 'l1', category: 'labels', question: 'What do I do with the shipping labels and documents once they are printed?', answer: '<ol><li><u>For Speedpost International Shipments</u><br><br>Affix the shipping label securely onto the parcel. The addresses and barcodes on the shipping label must be in clear view to facilitate scanning of details for shipping purposes.<br><br>All supporting documents e.g. 1 copy of the CN23/CP71 are to be placed inside the clear plastic pocket provided for international shipments. Please remember to sign-off the Customs Declaration (CN23) and commercial invoice.<br><br>1 copy of tax invoice will be generated once your shipment is confirmed. The tax invoice is for your reference and retention.</li><li><u>For Speedpost Domestic shipments</u><br><br>Affix the shipping label securely onto the parcel. The addresses and barcodes on the shipping label must be in clear view to facilitate scanning of details for shipping purposes.<br><br>1 copy of tax invoice will be generated once your shipment is confirmed. The tax invoice is for your reference and retention.</li><li><u>Mail shipments</u><br><br>Affix the mail shipping label securely onto the letter / document / small packets. The addresses and barcodes on the mail label must be in clear view to facilitate machine reading of details for posting purposes.<br><br>For mail items, there would only be 1 mail label generated for Domestic and International mail items.<br><br>1 copy of tax invoice will be generated once your shipment is confirmed. The tax invoice is for your reference and retention.</li></ol>' },
        { id: 'l2', category: 'labels', question: 'What should I do if the shipping label printed on white A4 size paper is too big for my package?', answer: 'You may cut the shipping label to size before affixing onto your package with adhesive tape (preferably). The delivery address must be visible to facilitate delivery. The rest of the supporting documents (eg. commercial invoice) must be placed inside the clear plastic pocket' },
        { id: 'l3', category: 'labels', question: 'What kind of printer and paper should I use to print fluffy shipping labels?', answer: 'Use only white paper printed on a laser or high quality ink jet printer. Self-adhesive labels with thermal / label printer are also acceptable.' },
        { id: 'l4', category: 'labels', question: 'Would I be able to change the delivery address or other details of my package after printing the labels for my parcel?', answer: 'For Speedpost International and Domestic Delivery Services, online changes cannot be made once you have confirmed and paid for your shipments. If you need to edit/cancel your shipment, please call our Speedpost hotline: +65 62225777. <br>For mail items, you are not allowed to make any changes or other details once labels are printed. <br>Please note that no refund is allowed for Consumers once a booking in Fluffy Shipping has been confirmed and paid.' },
        { id: 'l5', category: 'labels', question: 'Can I print the labels in fluffy shipping and send the package to the nearest post office?', answer: 'You can assign to drop off your package at a post office under Step 4 - "Collection" screen. <br>For Registered Service items, you will need to hand over the items to our counter staff at any post office for posting. <br>All Fluffy Shipping items not handed over to our counter staff (for example: if the items are dropped into our posting boxes) will be processed as ordinary mail.' },

        { id: 't1', category: 'tracking', question: 'What should I do if no one picks up my package after I requested for a collection pick-up?', answer: 'A collection request is considered successful when there is a Job Number generated for your package. Please ensure that you see a "Collection scheduled" under your Shipment history. Please call our Speedpost hotline: +65 62225777 and quote the Job Number (eg. EZYxxxxxxxxxx) if your package is not picked up within the requested collection time.' },
        { id: 't2', category: 'tracking', question: 'How do I track my shipment from fluffy shipping?', answer: 'If you have a registered account, you can track your shipments under History/Tracking – Shipments history screen. <br>Alternatively, tracking is also available: <br>Speedpost items – <a href="https://www.speedpost.com.sg/" target="_blank">www.speedpost.com.sg</a> or SingPost mobile app <br>Mail items – <a href="https://www.singpost.com/" target="_blank"> www.singpost.com</a> or SingPost mobile app' },
        { id: 't3', category: 'tracking', question: 'Can I obtain proof of delivery from fluffy shipping?', answer: 'If you wish to obtain a copy of the proof of delivery for Speedpost items and Registered Service items (subject to additional charges), please call our SingPost customer service hotline at 1605.' },

        { id: 'r1', category: 'registration', question: 'How do I register for fluffy shipping?', answer: 'You may register as a user by clicking on "Sign Up" on the login page' },
        { id: 'r2', category: 'registration', question: 'What happens if I forget my username and/or password?', answer: 'You may click on "Forgot your password" link and enter your username and e-mail address. An email will be sent to you with your username and new password details. If you forget your username (for Consumers), please register for a new account using another email account.' },

        { id: 'sh1', category: 'shipment', question: 'Why do I get an error message on my address input?', answer: 'Please refrain from keying your address into one single address line. It is recommended to always key in your address in the following format: <br>Name Line 1 – Recipient Name (35 characters limit) <br>Name Line 2 – Company name (if any, 35 characters limit) <br>Address line 1 – Block number (35 characters limit)<br>Address line 2 – Street name and unit number (35 characters limit)<br>Address line 3 – Building name (35 characters limit)' },
        { id: 'sh2', category: 'shipment', question: 'What if there is a difference between my recorded weight and the actual weight of the item after lodgement?', answer: 'Should there be a difference between your recorded weight and the actual weight after lodgement, we will contact you to top-up the cost difference. If your item is submitted at our post offices, our post office counter staff will collect the short payment at the counter when you submit your item.' },
        { id: 'sh3', category: 'shipment', question: 'Why is my item not accepted when I make a slight change to the shipment details on the shipping label? Will I get a refund?', answer: 'Shipment details (i.e. destination, collection and delivery address, weight, etc) determine the price of your postage. Please ensure your shipment details are correct before you proceed to make payment for your shipment. There will be no refund on your shipment once a booking has been confirmed and paid.' },
        { id: 'sh4', category: 'shipment', question: 'Why do I keep getting an error message in the dimensions that I enter?', answer: 'Your item could be too large. We are unable to process shipments beyond the maximum dimension. Please click <a href="https://www.speedpost.com.sg/images/pdf/country_max_wtg_dim.pdf" target="_blank">here</a> for Package Maximum Weight and Dimension by Country for Speedpost services. Please click <a href="https://www.singpost.com/support-centre/mailing-and-packaging-tips" target="_blank">here</a> for Maximum Weight and Dimension for Mail services.' },
        { id: 'sh5', category: 'shipment', question: 'What happens if I do not know the postcode of the "send to" address?', answer: 'Postcodes are mandatory when shipping to major international destinations such as China, South Korea, Japan, Spain, France, Australia, UK and USA. <br>Without a postcode, deliveries may be subjected to failed deliveries or delays. <br>For Singapore addresses, a postcode is always required.' },

        { id: 'pack1', category: 'packaging', question: 'How can I get packaging supplies?', answer: 'To purchase packaging materials, please visit: <a href="https://shop.singpost.com/materials-packaging/packing-materials.html" target="_blank">https://shop.singpost.com/materials-packaging/packing-materials.html</a>' }
      ]
    }
  },
  computed: {
    firstRowTabs() {
      // First row: first 4 tabs
      return this.tabs.slice(0, 4);
    },
    secondRowTabs() {
      // Second row: remaining 4 tabs
      return this.tabs.slice(4);
    }
  },
  methods: {
    setActiveTab(tabId) {
      this.activeTab = tabId;
      this.openItems.clear();
    },
    toggleAccordion(itemId) {
      if (this.openItems.has(itemId)) {
        this.openItems.delete(itemId);
      } else {
        this.openItems.add(itemId);
      }
    },
    isOpen(itemId) {
      return this.openItems.has(itemId);
    },
    filteredItems(category) {
      if (!this.searchQuery) {
        return this.faqItems.filter(item => item.category === category);
      }

      const query = this.searchQuery.toLowerCase();
      return this.faqItems.filter(item =>
        item.category === category &&
        item.question.toLowerCase().includes(query)
      );
    },
    handleSearch() {
      if (this.searchQuery.trim() === '') {
        return;
      }

      const query = this.searchQuery.toLowerCase();

      const tabMatches = {};

      this.tabs.forEach(tab => {
        const matches = this.faqItems.filter(item =>
          item.category === tab.id &&
          item.question.toLowerCase().includes(query)
        ).length;
        tabMatches[tab.id] = matches;
      });

      let bestTab = this.activeTab;
      let maxMatches = 0;

      for (const [tabId, matches] of Object.entries(tabMatches)) {
        if (matches > maxMatches) {
          maxMatches = matches;
          bestTab = tabId;
        }
      }

      if (maxMatches > 0 && bestTab !== this.activeTab) {
        this.setActiveTab(bestTab);
      }
    }
  }
}
</script>

<style scoped>
:root {
  --hot-pink: #ff4275;
  --dark-pink: #ff759e;
  --pink: #ff9096;
  --light-pink: #fff9fb;
  --lighter-pink: #ffeef2;
}

.faq-container {
  font-family: Arial, sans-serif;
  background-color: var(--light-pink);
  min-height: 100vh;
}

li {
  font-size: 15px;
}

.faq-search {
  margin: 20px auto;
}

.faq-search .form-control {
  border-color: var(--pink);
  box-shadow: 0 2px 4px rgba(255, 66, 117, 0.1);
}

.faq-search .form-control:focus {
  border-color: var(--hot-pink);
  box-shadow: 0 2px 8px rgba(255, 66, 117, 0.2);
}

.nav-tabs {
  border-bottom: 2px solid var(--pink);
}

.nav-tabs .nav-link {
  color: #495057;
  border: none;
  border-bottom: 3px solid transparent;
  padding: 1rem 1.5rem;
  transition: all 0.3s ease;
}

.nav-tabs .nav-link:hover {
  color: var(--hot-pink);
  background-color: var(--lighter-pink);
  border-bottom-color: var(--dark-pink);
}

.nav-tabs .nav-link.active {
  color: var(--hot-pink);
  background-color: white;
  border-bottom-color: var(--hot-pink);
  font-weight: 600;
}

.form-select {
  border-color: var(--pink);
  box-shadow: 0 2px 4px rgba(255, 66, 117, 0.1);
}

.form-select:focus {
  border-color: var(--hot-pink);
  box-shadow: 0 2px 8px rgba(255, 66, 117, 0.2);
}

.accordion-item {
  border: 1px solid var(--pink);
  border-radius: 8px !important;
  margin-bottom: 0.75rem;
  background: white;
  box-shadow: 0 2px 4px rgba(255, 66, 117, 0.1);
}

.accordion-button {
  background-color: white;
  color: #2c3e50;
  font-weight: 500;
  border: none;
  padding: 1.25rem 3rem 1.25rem 1.5rem;
  border-radius: 8px !important;
  transition: all 0.3s ease;
}

.accordion-button:not(.collapsed) {
  background-color: var(--lighter-pink);
  color: var(--hot-pink);
  box-shadow: none;
}

.accordion-button:hover {
  background-color: var(--lighter-pink);
}

.accordion-button::after {
  display: none;
}

.accordion-button::before {
  content: '+';
  position: absolute;
  right: 1.5rem;
  font-size: 1.5rem;
  color: var(--hot-pink);
  font-weight: 300;
  transition: all 0.2s ease;
}

.accordion-button:not(.collapsed)::before {
  content: '–';
  color: var(--hot-pink);
}

.accordion-body {
  background-color: white;
  border-top: 1px solid var(--pink);
  padding: 1.5rem;
  border-radius: 0 0 8px 8px;
}

.accordion-body ol,
.accordion-body ul {
  padding-left: 1.5rem;
}

.accordion-body a {
  color: var(--hot-pink);
  text-decoration: none;
}

.accordion-body a:hover {
  text-decoration: underline;
}

.btn-primary {
  background-color: var(--hot-pink);
  border-color: var(--hot-pink);
  padding: 0.75rem 1.5rem;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  background-color: var(--dark-pink);
  border-color: var(--dark-pink);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 66, 117, 0.3);
}

h2.text-center {
  color: var(--hot-pink);
  font-weight: 600;
  margin-bottom: 2rem;
}

.accordion-body {
  font-size: 0.95rem;
}

@media (max-width: 768px) {
  .accordion-button {
    padding: 1rem 3rem 1rem 1rem;
    font-size: 0.9rem;
  }

  .accordion-body {
    padding: 1rem;
    font-size: 0.9rem;
  }

  .btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 0.9rem;
  }

  .nav-tabs .nav-link {
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
  }
}

@media (max-width: 576px) {
  .container.py-4 {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  h2.text-center {
    font-size: 1.5rem;
  }

  .accordion-button {
    padding: 0.875rem 2.5rem 0.875rem 0.875rem;
    font-size: 0.85rem;
  }

  .accordion-body {
    padding: 0.875rem;
    font-size: 0.85rem;
  }

  .fixed-bottom .btn {
    width: 100%;
    margin: 0.5rem;
  }
}

@media print {
  .faq-container {
    background: white;
  }

  .accordion-item {
    box-shadow: none;
    border: 1px solid #dee2e6;
    page-break-inside: avoid;
  }

  .fixed-bottom {
    display: none;
  }
}
</style>