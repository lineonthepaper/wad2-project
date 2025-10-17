<template>
  <div class="atalogue-page">
    <hr />
    <div class="row bg-light-pink justify-content-center airplane-header">
      <div class="col-lg-4 col-md-6 col-sm-8 py-2 text-center">
        <h1 class="jua text-hot-pink">Our Services</h1>
      </div>
    </div>
    <hr />

    <div class="container mt-4">
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3 text-muted">Loading services...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="text-center py-5">
        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
        <h4 class="text-danger">Failed to load services</h4>
        <p class="text-muted">{{ error }}</p>
        <button class="btn btn-primary" @click="fetchServices">
          <i class="fas fa-redo"></i> Try Again
        </button>
      </div>

      <!-- Content -->
      <div v-else>
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
                :src="service.img_url"
                class="card-img-top"
                :alt="service.service_name"
                style="height: 200px; object-fit: cover;"
                @error="handleImageError"
              >
              <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <h5 class="card-title mb-0">{{ service.service_name }}</h5>
                </div>

                <p class="card-text text-muted flex-grow-1">{{ service.service_description }}</p>

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
                    Tracking: {{ service.is_tracked ? 'Yes' : 'No' }}
                  </small>
                </div>

                <div class="mt-auto">
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="badge" :class="getServiceTypeBadgeClass(service.service_type)">
                      {{ service.service_type }}
                    </span>
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
      services: [],
      loading: false,
      error: null
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
          service.service_type.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          service.service_description.toLowerCase().includes(this.searchQuery.toLowerCase());

        // Service type filter
        const matchesServiceType = !this.selectedServiceType ||
          service.service_type === this.selectedServiceType;

        // Tracking filter
        const matchesTracking = !this.selectedTracking ||
          (this.selectedTracking === 'Yes' && service.is_tracked) ||
          (this.selectedTracking === 'No' && !service.is_tracked);

        // Weight filter
        const matchesWeight = !this.selectedWeight ||
          service.max_weight == this.selectedWeight;

        return matchesSearch && matchesServiceType && matchesTracking && matchesWeight;
      });
    }
  },
  methods: {
    async fetchServices() {
      this.loading = true;
      this.error = null;

      try {
        // Adjust the path based on where your JSON file is located
        const response = await fetch('/json/serviceCatalogue.json');

        if (!response.ok) {
          throw new Error(`Failed to fetch services: ${response.status} ${response.statusText}`);
        }

        const servicesData = await response.json();

        // Transform the JSON data to match our component structure
        this.services = servicesData.map((service, index) => ({
          id: index + 1, // Generate IDs since they're not in the JSON
          service_name: service.service_name,
          service_type: service.service_type,
          is_tracked: service.is_tracked,
          max_weight: service.max_weight,
          max_height: service.max_height,
          max_width: service.max_width,
          max_length: service.max_length,
          img_url: service.img_url,
          service_description: service.service_description
        }));

      } catch (err) {
        console.error('Error loading services:', err);
        this.error = err.message;
      } finally {
        this.loading = false;
      }
    },

    handleImageError(event) {
      // Fallback image if the img_url is broken or missing
      event.target.src = "https://via.placeholder.com/300x200/CCCCCC/FFFFFF?text=Service+Image";
    },

    getServiceTypeBadgeClass(serviceType) {
      return serviceType === 'Documents' ? 'bg-info' : 'bg-success';
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
  },
  mounted() {
    this.fetchServices();
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
