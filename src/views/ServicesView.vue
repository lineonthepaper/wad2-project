<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import serviceCatalogue from '/json/serviceCatalogue.json'

const router = useRouter()

const searchQuery = ref("")
const selectedServiceType = ref("")
const selectedTracking = ref("")
const selectedWeight = ref("")
const services = ref([])
const loading = ref(false)
const error = ref(null)
const loadedImages = ref(new Set())

const hasActiveFilters = computed(() => {
  return selectedServiceType.value || selectedTracking.value || selectedWeight.value
})

const availableWeights = computed(() => {
  const weights = [...new Set(services.value.map(service => service.max_weight))]
  return weights.sort((a, b) => a - b)
})

const filteredServices = computed(() => {
  return services.value.filter(service => {
    const matchesSearch = !searchQuery.value ||
      service.service_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      service.service_type.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      service.service_description.toLowerCase().includes(searchQuery.value.toLowerCase())

    const matchesServiceType = !selectedServiceType.value ||
      service.service_type === selectedServiceType.value

    const matchesTracking = !selectedTracking.value ||
      (selectedTracking.value === 'Yes' && service.is_tracked) ||
      (selectedTracking.value === 'No' && !service.is_tracked)

    const matchesWeight = !selectedWeight.value ||
      service.max_weight == selectedWeight.value

    return matchesSearch && matchesServiceType && matchesTracking && matchesWeight
  })
})


const preloadImage = (url) => {
  return new Promise((resolve, reject) => {
    const img = new Image()
    img.onload = () => resolve(url)
    img.onerror = () => reject(url)
    img.src = url
  })
}

const fetchServices = async () => {
  loading.value = true
  error.value = null

  try {
    services.value = serviceCatalogue.map((service, index) => ({
      id: index + 1,
      service_name: service.service_name,
      service_type: service.service_type,
      is_tracked: service.is_tracked,
      max_weight: service.max_weight,
      max_height: service.max_height,
      max_width: service.max_width,
      max_length: service.max_length,
      img_url: service.img_url,
      service_description: service.service_description
    }))

    // Preload all images
    const imageUrls = services.value.map(service => service.img_url)
    const preloadPromises = imageUrls.map(url => preloadImage(url).catch(() => null))

    await Promise.all(preloadPromises)

  } catch (err) {
    console.error('Error loading services:', err)
    error.value = err.message
  } finally {
    loading.value = false
  }
}

const handleImageError = (event, serviceId) => {

  if (!event.target.classList.contains('image-failed')) {
    event.target.src = "https://via.placeholder.com/300x200/CCCCCC/FFFFFF?text=Service+Image"
    event.target.classList.add('image-failed')
    loadedImages.value.add(serviceId)
  }
}

const handleImageLoad = (event, serviceId) => {
  event.target.classList.add('image-loaded')
  loadedImages.value.add(serviceId)
}

const getServiceTypeBadgeClass = (serviceType) => {
  return serviceType === 'Documents' ? 'bg-info' : 'bg-success'
}

const goToDetail = (serviceId) => {
  router.push(`/service/${serviceId}`)
}

const clearFilters = () => {
  selectedServiceType.value = ""
  selectedTracking.value = ""
  selectedWeight.value = ""
  searchQuery.value = ""
}

onMounted(() => {
  fetchServices()
})
</script>

<template>
  <div class="catalogue-page">
    <hr />
<div class="row justify-content-center airplane-header">
  <div class="col-lg-4 col-md-6 col-sm-8 py-2 text-center">
    <h1 class="jua text-hot-pink">Our Services</h1>
  </div>
</div>
<hr />

    <div class="container mt-4">
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3 text-muted">Loading services...</p>
      </div>

      <div v-else-if="error" class="text-center py-5">
        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
        <h4 class="text-danger">Failed to load services</h4>
        <p class="text-muted">{{ error }}</p>
        <button class="btn btn-primary" @click="fetchServices">
          <i class="fas fa-redo"></i> Try Again
        </button>
      </div>

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
                Clear
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

              <div class="card-img-container">
                <img
                  :src="service.img_url"
                  class="card-img-top service-image"
                  :alt="service.service_name"
                  @error="(event) => handleImageError(event, service.id)"
                  @load="(event) => handleImageLoad(event, service.id)"
                  :class="{ 'image-loaded': loadedImages.has(service.id) }"
                >
                <div class="image-placeholder" v-if="!loadedImages.has(service.id)">
                  <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                </div>
              </div>

              <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <h5 class="card-title mb-0">{{ service.service_name }}</h5>
                </div>

                <p class="card-text text-muted flex-grow-1">{{ service.service_description }}</p>

                <div class="service-details mb-2">
                  <small class="text-muted d-block">

                    Max Weight: {{ service.max_weight }}kg
                  </small>
                  <small class="text-muted d-block">

                    Dimensions: {{ service.max_length }} × {{ service.max_width }} × {{ service.max_height }} cm
                  </small>
                  <small class="text-muted d-block">

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
                <button class="buttonView w-100" @click.stop="goToDetail(service.id)">View Details</button>
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

<style scoped>


.buttonView {
  display: inline-block;
  padding: 12px 24px;
  background-color: #ff6297;
  color: white !important;
  text-decoration: none;
  border: 2px solid #ff6297;
  border-radius: 6px;
  font-weight: bold;
  transition: all 0.3s ease;
  cursor: pointer;
}

.buttonView:hover {
  background-color: white;
  color: #ff6297 !important;
}

.service-card:hover {
  transform: translateY(-5px);
}


.text-hot-pink {
  color: var(--hot-pink) !important;
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


.card-img-container {
  height: 200px;
  overflow: hidden;
  position: relative;
  background-color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  border-bottom: 1px solid #e9ecef;
}

.service-image {
  max-height: 100%;
  max-width: 100%;
  width: auto;
  height: auto;
  object-fit: contain;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.service-image.image-loaded {
  opacity: 1;
}

.image-placeholder {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: white;
}

.image-failed {
  opacity: 1 !important;
}

@media (max-width: 768px) {
  .dropdown-toggle {
    min-width: 100px;
    font-size: 0.9rem;
  }

  .d-flex.gap-2 {
    gap: 0.5rem !important;
  }

  .card-img-container {
    height: 180px;
    padding: 15px;
  }
}
</style>
