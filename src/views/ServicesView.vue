<template>
  <div class="catalogue-page">
    <hr />
    <div class="row bg-light-pink justify-content-center airplane-header">
      <div class="col-lg-4 col-md-6 col-sm-8 py-2 text-center">
        <h1 class="jua text-hot-pink">Our Services</h1>
      </div>
    </div>
    <hr />

    <div class="container mt-4">
      <div class="row mb-4">
        <div class="col-md-6">
          <div class="input-group">
            <input
              type="text"
              class="form-control"
              placeholder="Search services..."
              v-model="searchQuery"
            >
          </div>
        </div>
        <div class="col-md-6">
          <div class="d-flex flex-wrap gap-2">
            <div class="dropdown">
              <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                {{ selectedServiceType || 'Service Type' }}
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" @click.prevent="selectedServiceType = ''">All Services</a></li>
                <li><a class="dropdown-item" href="#" @click.prevent="selectedServiceType = 'Documents'">Documents</a></li>
                <li><a class="dropdown-item" href="#" @click.prevent="selectedServiceType = 'Packets'">Packets</a></li>
              </ul>
            </div>
            <div class="dropdown">
              <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                {{ selectedTracking || 'Tracking' }}
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" @click.prevent="selectedTracking = ''">All</a></li>
                <li><a class="dropdown-item" href="#" @click.prevent="selectedTracking = 'Yes'">Yes</a></li>
                <li><a class="dropdown-item" href="#" @click.prevent="selectedTracking = 'No'">No</a></li>
              </ul>
            </div>
            <div class="dropdown">
              <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                {{ selectedWeight || 'Weight' }}
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" @click.prevent="selectedWeight = ''">All Weights</a></li>
                <li v-for="weight in availableWeights" :key="weight">
                  <a class="dropdown-item" href="#" @click.prevent="selectedWeight = weight">{{ weight }}kg</a>
                </li>
              </ul>
            </div>
            <button
              class="btn btn-outline-danger"
              @click="clearFilters"
              :disabled="!hasActiveFilters"
            >
              <i class="fas fa-times"></i> Clear
            </button>
          </div>
        </div>
      </div>
      <div class="row mb-3" v-if="hasActiveFilters">
        <div class="col-12">
          <div class="d-flex flex-wrap gap-2 align-items-center">
            <small class="text-muted">Active filters:</small>
            <span
              v-if="selectedServiceType"
              class="badge bg-primary"
              @click="selectedServiceType = ''"
              style="cursor: pointer;"
            >
              Service: {{ selectedServiceType }} <i class="fas fa-times ms-1"></i>
            </span>
            <span
              v-if="selectedTracking"
              class="badge bg-success"
              @click="selectedTracking = ''"
              style="cursor: pointer;"
            >
              Tracking: {{ selectedTracking }} <i class="fas fa-times ms-1"></i>
            </span>
            <span
              v-if="selectedWeight"
              class="badge bg-warning text-dark"
              @click="selectedWeight = ''"
              style="cursor: pointer;"
            >
              Weight: {{ selectedWeight }}kg <i class="fas fa-times ms-1"></i>
            </span>
          </div>
        </div>
      </div>
      <div class="row">
        <div
          class="col-lg-4 col-md-6 mb-4"
          v-for="service in filteredServices"
          :key="service.id"
        >
          <div
            class="card h-100 service-card shadow-sm"
            @click="goToDetail(service.id)"
            style="cursor: pointer; transition: transform 0.2s;"
          >
            <img
              :src="getServiceImage(service.service_name)"
              class="card-img-top"
              :alt="service.service_name"
              style="height: 200px; object-fit: cover;"
            >
            <div class="card-body d-flex flex-column">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <h5 class="card-title mb-0">{{ service.service_name }}</h5>
                <span class="badge bg-primary">{{ service.service_type }}</span>
              </div>

              <p class="card-text text-muted flex-grow-1">{{ getServiceDescription(service.service_name) }}</p>

              <div class="service-details mb-2">
                <small class="text-muted d-block">
                  <i class="fas fa-weight-hanging me-1"></i>
                  Max Weight: {{ service.max_weight }}kg
                </small>
                <small class="text-muted d-block">
                  <i class="fas fa-ruler-combined me-1"></i>
                  Dimensions: {{ service.max_length }} × {{ service.max_width }} × {{ service.max_height }} cm
                </small>
                <small class="text-muted d-block">
                  <i class="fas fa-shipping-fast me-1"></i>
                  Tracking: {{ service.id_tracked ? 'Yes' : 'No' }}
                </small>
              </div>

              <div class="mt-auto">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="badge" :class="getServiceTypeBadgeClass(service.service_type)">
                    {{ service.service_type }}
                  </span>
                  <div class="text-warning">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="far fa-star"></i>
                    <small class="text-muted">({{ getRatingCount(service.service_name) }})</small>
                  </div>
                </div>
              </div>
            </div>
            <div class="card-footer bg-transparent">
              <button class="btn btn-outline-primary w-100" @click.stop="goToDetail(service.id)">View Details</button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="filteredServices.length === 0" class="text-center py-5">
        <i class="fas fa-search fa-3x text-muted mb-3"></i>
        <h4 class="text-muted">No services found</h4>
        <p class="text-muted">Try adjusting your search terms or filters</p>
        <button class="btn btn-primary" @click="clearFilters">
          Clear All Filters
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "ServicesCatalogue",
  data() {
    return {
      searchQuery: "",
      selectedServiceType: "",
      selectedTracking: "",
      selectedWeight: "",
      services: [
        {
          id: 1,
          service_name: "Basic Mail",
          service_type: "Documents",
          id_tracked: false,
          max_weight: 2,
          max_height: 2,
          max_width: 30.5,
          max_length: 38.1
        },
        {
          id: 2,
          service_name: "Basic Package",
          service_type: "Packets",
          id_tracked: false,
          max_weight: 2,
          max_height: 60,
          max_width: 60,
          max_length: 60
        },
        {
          id: 3,
          service_name: "Registered Mail",
          service_type: "Documents",
          id_tracked: true,
          max_weight: 2,
          max_height: 2,
          max_width: 30.5,
          max_length: 38.1
        },
        {
          id: 4,
          service_name: "Registered Package",
          service_type: "Packets",
          id_tracked: true,
          max_weight: 2,
          max_height: 60,
          max_width: 60,
          max_length: 60
        },
        {
          id: 5,
          service_name: "Standard Large",
          service_type: "Documents",
          id_tracked: false,
          max_weight: 0.5,
          max_height: 1.4,
          max_width: 22.9,
          max_length: 32.4
        },
        {
          id: 6,
          service_name: "Standard Regular",
          service_type: "Documents",
          id_tracked: false,
          max_weight: 0.5,
          max_height: 0.6,
          max_width: 16.2,
          max_length: 24
        },
        {
          id: 7,
          service_name: "Tracked Letterbox",
          service_type: "Packets",
          id_tracked: true,
          max_weight: 2,
          max_height: 6.5,
          max_width: 22.9,
          max_length: 32.4
        }
      ]
    };
  },
  computed: {
    hasActiveFilters() {
      return this.selectedServiceType || this.selectedTracking || this.selectedWeight;
    },
    availableWeights() {
      const weights = [...new Set(this.services.map(service => service.max_weight))];
      return weights.sort((a, b) => a - b);
    },
    filteredServices() {
      return this.services.filter(service => {
        // Text search
        const matchesSearch = !this.searchQuery ||
          service.service_name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          service.service_type.toLowerCase().includes(this.searchQuery.toLowerCase());

        // Service type filter
        const matchesServiceType = !this.selectedServiceType ||
          service.service_type === this.selectedServiceType;

        // Tracking filter
        const matchesTracking = !this.selectedTracking ||
          (this.selectedTracking === 'Yes' && service.id_tracked) ||
          (this.selectedTracking === 'No' && !service.id_tracked);

        // Weight filter
        const matchesWeight = !this.selectedWeight ||
          service.max_weight == this.selectedWeight;

        return matchesSearch && matchesServiceType && matchesTracking && matchesWeight;
      });
    }
  },
  methods: {
    getServiceImage(serviceName) {
      const imageMap = {
        "Basic Mail": "https://via.placeholder.com/300x200/4A90E2/FFFFFF?text=Basic+Mail",
        "Basic Package": "https://via.placeholder.com/300x200/50E3C2/FFFFFF?text=Basic+Package",
        "Registered Mail": "https://via.placeholder.com/300x200/9013FE/FFFFFF?text=Registered+Mail",
        "Registered Package": "https://via.placeholder.com/300x200/F5A623/FFFFFF?text=Registered+Package",
        "Standard Large": "https://via.placeholder.com/300x200/417505/FFFFFF?text=Standard+Large",
        "Standard Regular": "https://via.placeholder.com/300x200/B8E986/FFFFFF?text=Standard+Regular",
        "Tracked Letterbox": "https://via.placeholder.com/300x200/D0021B/FFFFFF?text=Tracked+Letterbox"
      };
      return imageMap[serviceName] || "https://via.placeholder.com/300x200/CCCCCC/FFFFFF?text=Service";
    },

    getServiceDescription(serviceName) {
      const descriptionMap = {
        "Basic Mail": "Economical document mailing service for standard letters and documents",
        "Basic Package": "Basic parcel service for small packages without tracking",
        "Registered Mail": "Secure document service with tracking and proof of delivery",
        "Registered Package": "Tracked parcel service with delivery confirmation",
        "Standard Large": "Service for larger documents with standard delivery",
        "Standard Regular": "Regular document service for standard sized mail",
        "Tracked Letterbox": "Tracked service for packets with letterbox delivery"
      };
      return descriptionMap[serviceName] || "Reliable postal service for your shipping needs";
    },

    getServiceTypeBadgeClass(serviceType) {
      return serviceType === 'Documents' ? 'bg-info' : 'bg-success';
    },

    getRatingCount(serviceName) {
      const ratingMap = {
        "Basic Mail": 132,
        "Basic Package": 88,
        "Registered Mail": 150,
        "Registered Package": 95,
        "Standard Large": 117,
        "Standard Regular": 73,
        "Tracked Letterbox": 200
      };
      return ratingMap[serviceName] || 100;
    },

    goToDetail(serviceId) {
      this.$router.push(`/service/${serviceId}`);
    },

    clearFilters() {
      this.selectedServiceType = "";
      this.selectedTracking = "";
      this.selectedWeight = "";
      this.searchQuery = "";
    }
  }
};
</script>

<style scoped>
.service-card:hover {
  transform: translateY(-5px);
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

.badge {
  font-size: 0.75rem;
}

.input-group-text {
  background-color: #f8f9fa;
  border: 1px solid #ced4da;
}

.service-details {
  font-size: 0.85rem;
}

.dropdown-toggle {
  min-width: 120px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .dropdown-toggle {
    min-width: 100px;
    font-size: 0.9rem;
  }

  .d-flex.gap-2 {
    gap: 0.5rem !important;
  }
}
</style>
