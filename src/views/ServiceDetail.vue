<template>
  <hr />
  <div class="row bg-light-pink justify-content-center airplane-header">
    <div class="col-lg-4 col-md-6 col-sm-8 py-2 text-center">
      <h1 class="jua text-hot-pink">Details - {{ service.service_name }}</h1>
    </div>
  </div>
  <hr />
  <div class="service-detail container py-5">


    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-3 text-muted">Loading service details...</p>
    </div>


    <div v-else-if="error" class="text-center py-5">
      <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
      <h4 class="text-danger">Failed to load service details</h4>
      <p class="text-muted">{{ error }}</p>
      <button class="btn btn-primary" @click="fetchService">
        <i class="fas fa-redo"></i> Try Again
      </button>
    </div>


    <div v-else>
      <div class="mb-3">
        <router-link to="/services" class="back-link">← BACK</router-link>
      </div>

      <div v-if="service" class="detail-content">

        <div class="row align-items-center mb-5">
          <div class="col-md-6 text-center">
            <img :src="service.img_url" :alt="service.service_name" class="service-image" @error="handleImageError" />
            <p class="dimensions mt-3">
              Width: {{ service.max_width }}cm |
              Length: {{ service.max_length }}cm |
              Height: {{ service.max_height }}cm
            </p>
          </div>
          <div class="col-md-6">
            <div class="title-with-badge d-flex align-items-center gap-3 mb-3">
              <h1 class="title mb-0">{{ service.service_name }}</h1>
              <span class="badge" :class="getServiceTypeBadgeClass(service.service_type)">
                {{ service.service_type }}
              </span>
            </div>
            <p class="description">{{ service.service_description }}</p>

            <!-- <div class="action-buttons mt-3">
              <button class="btn-outline">LOCATE US</button>
              <button class="btn-outline">CHECK DELIVERY RATES</button>
              <button class="btn-outline">CALCULATE RATES</button>
            </div> -->

            <p class="delivery-note mt-4">
              {{ service.delivery_note }}
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
              <td>{{ service.is_tracked ? 'Yes' : 'No' }}</td>
            </tr>
            <tr>
              <td><strong>Type of Delivery</strong></td>
              <td>{{ service.delivery_type }}</td>
            </tr>
            <tr>
              <td><strong>Delivery Standard</strong></td>
              <td>{{ service.delivery_standard }}</td>
            </tr>
            <tr>
              <td><strong>Compensation</strong></td>
              <td>{{ service.compensation }}</td>
            </tr>
            <tr>
              <td><strong>Conditions</strong></td>
              <td>
                <ul>
                  <li v-for="(condition, i) in service.conditions" :key="i">
                    {{ condition }}
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
        <i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i>
        <h4 class="text-muted">Service not found</h4>
        <p class="text-muted mb-4">The service you're looking for doesn't exist.</p>
        <router-link to="/services" class="btn btn-primary">Return to Catalogue</router-link>
      </div>
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
      service: null,
      services: [],
      loading: false,
      error: null
    };
  },
  computed: {
    serviceId() {
      return parseInt(this.id);
    }
  },
  methods: {
    async fetchService() {
      this.loading = true;
      this.error = null;
      this.service = null;

      try {
        
        const response = await fetch('/json/serviceCatalogue.json');

        if (!response.ok) {
          throw new Error(`Failed to fetch services: ${response.status} ${response.statusText}`);
        }

        const servicesData = await response.json();


        this.services = servicesData.map((service, index) => ({
          id: index + 1,
          service_name: service.service_name,
          service_type: service.service_type,
          is_tracked: service.is_tracked,
          max_weight: service.max_weight,
          max_height: service.max_height,
          max_width: service.max_width,
          max_length: service.max_length,
          img_url: service.img_url,
          service_description: service.service_description,
          delivery_note: service.delivery_note,
          delivery_type: service.delivery_type,
          delivery_standard: service.delivery_standard,
          compensation: service.compensation,
          conditions: service.conditions,
          notes: service.notes
        }));


        this.service = this.services.find(s => s.id === this.serviceId);

        if (!this.service) {
          throw new Error('Service not found');
        }

      } catch (err) {
        console.error('Error loading service:', err);
        this.error = err.message;
      } finally {
        this.loading = false;
      }
    },

    handleImageError(event) {

      event.target.src = "https://via.placeholder.com/300x200/CCCCCC/FFFFFF?text=Service+Image";
    },

    getServiceTypeBadgeClass(serviceType) {
      return serviceType === 'Documents' ? 'bg-info' : 'bg-success';
    }
  },
  watch: {
    id: {
      immediate: true,
      handler() {
        this.fetchService();
      }
    }
  },
  mounted() {
    this.fetchService();
  }
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

.title-with-badge {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.title {
  font-size: 32px;
  font-weight: bold;
  color: #1c1c1c;
  margin: 0;
}

.description {
  font-size: 16px;
  color: #333;
}

.service-image {
  width: 100%;
  max-width: 320px;
  height: 200px;
  object-fit: cover;
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

.bg-light-pink {
  background-color: #fff0f5;
}

.text-hot-pink {
  color: #ff69b4;
}

.jua {
  font-family: 'Jua', sans-serif;
}

.visually-hidden {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}


@media (max-width: 768px) {
  .title-with-badge {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .title {
    font-size: 24px;
  }
}
</style>
