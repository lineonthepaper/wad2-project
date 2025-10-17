<template>
  <hr />
  <div class="row bg-light-pink justify-content-center airplane-header">
    <div class="col-lg-4 col-md-6 col-sm-8 py-2 text-center">
      <h1 class="jua text-hot-pink">Details</h1>
    </div>
  </div>
  <hr />
  <div class="service-detail container py-5">


    <div class="mb-3">
      <router-link to="/services" class="back-link">← BACK</router-link>
    </div>

    <div v-if="service" class="detail-content">

      <div class="row align-items-center mb-5">
        <div class="col-md-6 text-center">
          <img :src="service.image" :alt="service.service_name" class="service-image" />
          <p class="dimensions mt-3">
            Width: {{ service.max_width }}cm |
            Length: {{ service.max_length }}cm |
            Height: {{ service.max_height }}cm
          </p>
        </div>
        <div class="col-md-6">
          <p class="category">{{ service.service_type }}</p>
          <h1 class="title">{{ service.service_name }}</h1>
          <p class="description">{{ service.description }}</p>

          <!-- <div class="action-buttons mt-3">
            <button class="btn-outline">LOCATE US</button>
            <button class="btn-outline">CHECK DELIVERY RATES</button>
            <button class="btn-outline">CALCULATE RATES</button>
          </div> -->

          <p class="delivery-note mt-4">
            {{ service.deliveryNote }}
          </p>
        </div>
      </div>


      <div class="tabs mb-3">
        <button
          class="tab-btn"
          :class="{ active: activeTab === 'info' }"
          @click="activeTab = 'info'"
        >
          Additional Information
        </button>
        <button
          class="tab-btn"
          :class="{ active: activeTab === 'notes' }"
          @click="activeTab = 'notes'"
        >
          Notes
        </button>
      </div>


      <div v-if="activeTab === 'info'" class="info-section">
        <table class="info-table">
          <tr>
            <td><strong>Maximum Weight</strong></td>
            <td>{{ service.max_weight }}kg</td>
          </tr>
          <tr>
            <td><strong>Maximum Dimensions</strong></td>
            <td>
              {{ service.max_length }} × {{ service.max_width }} × {{ service.max_height }} cm
            </td>
          </tr>
          <tr>
            <td><strong>Tracking</strong></td>
            <td>{{ service.id_tracked ? 'Yes' : 'No' }}</td>
          </tr>
          <tr>
            <td><strong>Type of Delivery</strong></td>
            <td>{{ service.deliveryType || 'Standard' }}</td>
          </tr>
          <tr>
            <td><strong>Delivery Standard</strong></td>
            <td v-html="service.deliveryStandard"></td>
          </tr>
          <tr>
            <td><strong>Compensation</strong></td>
            <td>{{ service.compensation || 'Standard coverage applies' }}</td>
          </tr>
          <tr>
            <td><strong>Conditions</strong></td>
            <td>
              <ul>
                <li v-for="(cond, i) in service.conditions" :key="i">
                  {{ cond }}
                </li>
              </ul>
            </td>
          </tr>
        </table>
      </div>

      <div v-else class="notes-section">
        <p v-for="(note, i) in service.notes" :key="i">{{ note }}</p>
      </div>
    </div>

    <div v-else class="text-center">
      <p>Service not found.</p>
      <router-link to="/services" class="btn btn-primary">Return to Catalogue</router-link>
    </div>
  </div>
</template>

<script>
export default {
  name: "ServiceDetail",
  props: ["id"],
  data() {
    return {
      activeTab: "info",
      services: [
        {
          id: 1,
          service_name: "Basic Mail",
          service_type: "Documents",
          id_tracked: false,
          max_weight: 2,
          max_height: 2,
          max_width: 30.5,
          max_length: 38.1,
          description: "Economical document mailing service for standard letters and documents",
          image: "https://via.placeholder.com/300x200/4A90E2/FFFFFF?text=Basic+Mail",
          deliveryNote: "Standard delivery within 2-3 working days. No tracking included.",
          deliveryType: "Letterbox",
          deliveryStandard: "2-3 working days",
          compensation: "Not applicable",
          conditions: [
            "Maximum weight: 2kg",
            "Suitable for documents and standard letters",
            "No tracking available",
            "Standard delivery timeframe applies"
          ],
          notes: [
            "This service does not include tracking",
            "For important documents, consider Registered Mail",
            "Delivery times may vary during peak periods"
          ]
        },
        {
          id: 2,
          service_name: "Basic Package",
          service_type: "Packets",
          id_tracked: false,
          max_weight: 2,
          max_height: 60,
          max_width: 60,
          max_length: 60,
          description: "Basic parcel service for small packages without tracking",
          image: "https://via.placeholder.com/300x200/50E3C2/FFFFFF?text=Basic+Package",
          deliveryNote: "Parcel delivery within 3-5 working days. No tracking included.",
          deliveryType: "Doorstep",
          deliveryStandard: "3-5 working days",
          compensation: "Limited coverage",
          conditions: [
            "Maximum weight: 2kg",
            "Suitable for small packages",
            "No tracking available",
            "Signature not required"
          ],
          notes: [
            "Ideal for non-valuable items",
            "Consider tracked services for important packages",
            "Packaging must be secure and appropriate"
          ]
        },
        {
          id: 3,
          service_name: "Registered Mail",
          service_type: "Documents",
          id_tracked: true,
          max_weight: 2,
          max_height: 2,
          max_width: 30.5,
          max_length: 38.1,
          description: "Secure document service with tracking and proof of delivery",
          image: "https://via.placeholder.com/300x200/9013FE/FFFFFF?text=Registered+Mail",
          deliveryNote: "Tracked delivery with signature confirmation. Next working day delivery available.",
          deliveryType: "Registered",
          deliveryStandard: "1-2 working days",
          compensation: "Up to $100 coverage",
          conditions: [
            "Maximum weight: 2kg",
            "Tracking included",
            "Signature required upon delivery",
            "Proof of delivery available"
          ],
          notes: [
            "Recommended for important documents",
            "Tracking number provided",
            "Delivery confirmation available"
          ]
        },
        {
          id: 4,
          service_name: "Registered Package",
          service_type: "Packets",
          id_tracked: true,
          max_weight: 2,
          max_height: 60,
          max_width: 60,
          max_length: 60,
          description: "Tracked parcel service with delivery confirmation",
          image: "https://via.placeholder.com/300x200/F5A623/FFFFFF?text=Registered+Package",
          deliveryNote: "Fully tracked parcel service with delivery confirmation and insurance coverage.",
          deliveryType: "Doorstep with Signature",
          deliveryStandard: "2-3 working days",
          compensation: "Up to $200 coverage",
          conditions: [
            "Maximum weight: 2kg",
            "Full tracking included",
            "Signature required",
            "Insurance coverage included"
          ],
          notes: [
            "Ideal for valuable items",
            "Real-time tracking available",
            "Insurance coverage for peace of mind"
          ]
        },
        {
          id: 5,
          service_name: "Standard Large",
          service_type: "Documents",
          id_tracked: false,
          max_weight: 0.5,
          max_height: 1.4,
          max_width: 22.9,
          max_length: 32.4,
          description: "Service for larger documents with standard delivery",
          image: "https://via.placeholder.com/300x200/417505/FFFFFF?text=Standard+Large",
          deliveryNote: "Economical service for larger documents and flat items.",
          deliveryType: "Letterbox",
          deliveryStandard: "1-2 working days",
          compensation: "Not applicable",
          conditions: [
            "Maximum weight: 0.5kg",
            "Suitable for large envelopes and flat documents",
            "No tracking included",
            "Specific size requirements apply"
          ],
          notes: [
            "Perfect for large format documents",
            "Cost-effective for bulk mailing",
            "Standard delivery timeframe"
          ]
        },
        {
          id: 6,
          service_name: "Standard Regular",
          service_type: "Documents",
          id_tracked: false,
          max_weight: 0.5,
          max_height: 0.6,
          max_width: 16.2,
          max_length: 24,
          description: "Regular document service for standard sized mail",
          image: "https://via.placeholder.com/300x200/B8E986/FFFFFF?text=Standard+Regular",
          deliveryNote: "Standard mail service for regular sized documents and letters.",
          deliveryType: "Letterbox",
          deliveryStandard: "1-2 working days",
          compensation: "Not applicable",
          conditions: [
            "Maximum weight: 0.5kg",
            "Standard envelope sizes",
            "No tracking included",
            "Regular delivery schedule"
          ],
          notes: [
            "Most economical option",
            "Ideal for routine correspondence",
            "Standard delivery reliability"
          ]
        },
        {
          id: 7,
          service_name: "Tracked Letterbox",
          service_type: "Packets",
          id_tracked: true,
          max_weight: 2,
          max_height: 6.5,
          max_width: 22.9,
          max_length: 32.4,
          description: "Tracked service for packets with letterbox delivery",
          image: "https://via.placeholder.com/300x200/D0021B/FFFFFF?text=Tracked+Letterbox",
          deliveryNote: "Tracked packet service that fits through standard letterboxes.",
          deliveryType: "Letterbox",
          deliveryStandard: "1-2 working days",
          compensation: "Up to $50 coverage",
          conditions: [
            "Maximum weight: 2kg",
            "Must fit through standard letterbox",
            "Tracking included",
            "Specific dimension requirements"
          ],
          notes: [
            "Convenient letterbox delivery",
            "Tracking for peace of mind",
            "Ideal for small tracked items"
          ]
        }
      ]
    };
  },
  computed: {
    service() {
      return this.services.find(s => s.id === parseInt(this.id));
    },
  },
};
</script>

<style scoped>
.service-detail {
  max-width: 1000px;
  background-color: white;
}

.back-link {
  color: #004b8d;
  font-weight: bold;
  text-decoration: none;
}

.back-link:hover {
  text-decoration: underline;
}

.category {
  text-transform: uppercase;
  color: #b71c1c;
  font-weight: 600;
  font-size: 14px;
}

.title {
  font-size: 32px;
  font-weight: bold;
  color: #1c1c1c;
}

.description {
  font-size: 16px;
  color: #333;
}

.service-image {
  width: 100%;
  max-width: 320px;
}

.btn-outline {
  background: none;
  border: 1px solid #004b8d;
  color: #004b8d;
  padding: 6px 14px;
  border-radius: 4px;
  margin-right: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-outline:hover {
  background: #004b8d;
  color: white;
}

.delivery-note {
  font-size: 14px;
  color: #555;
}

.tabs {
  border-bottom: 2px solid #ddd;
}

.tab-btn {
  background: none;
  border: none;
  font-weight: 600;
  padding: 10px 20px;
  cursor: pointer;
  color: #555;
}

.tab-btn.active {
  color: #004b8d;
  border-bottom: 3px solid #004b8d;
}

.info-table {
  width: 100%;
  border-collapse: collapse;
}

.info-table td {
  border-bottom: 1px solid #eee;
  padding: 8px 12px;
  vertical-align: top;
}

ul {
  padding-left: 20px;
  margin: 0;
}

.notes-section p {
  background: #f8f9fa;
  padding: 10px 15px;
  margin-bottom: 8px;
  border-radius: 4px;
}
</style>
