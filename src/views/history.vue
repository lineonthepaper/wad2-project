<template>
  <div class="history-page">

    <div v-if="!isAuthenticated" class="login-required">
      <div class="login-message">
        <div class="message-icon">
          <i class="fas fa-lock"></i>
        </div>
        <h2>Authentication Required</h2>
        <p>Please log in to view your history of shipments</p>
        <div class="action-buttons">
          <button @click="redirectToLogin" class="btn btn-primary">
            <i class="fas fa-sign-in-alt"></i>
            Go to Login
          </button>
        </div>
      </div>
    </div>


    <div v-else class="dashboard-wrapper">
      <hr />
      <div class="row justify-content-center airplane-header">
        <div class="col-lg-4 col-md-6 col-sm-8 py-2 text-center">
          <h1 class="jua text-hot-pink">History of shipments</h1>
        </div>
      </div>
      <hr />

      <div class="container mt-4">

        <div v-if="loading" class="text-center py-5">
          <div class="loading-spinner-large"></div>
          <p class="mt-3 text-muted">Loading your shipments...</p>
        </div>


        <div v-else-if="error" class="text-center py-5">
          <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
          <h4 class="text-danger">Failed to load shipments</h4>
          <p class="text-muted">Unable to load your shipment history at this time.</p>
          <button class="btn btn-primary" @click="fetchShipments">
            <i class="fas fa-redo"></i> Try Again
          </button>
        </div>


        <div v-else>

          <div class="row mb-4">
            <div class="col-md-6">
              <div class="input-group">
                <input
                  type="text"
                  class="form-control search-input"
                  placeholder="Search by service, destination, or items..."
                  v-model="searchQuery"
                  @input="handleSearch"
                >
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex flex-wrap gap-2">
                <div class="dropdown">
                  <button class="btn btn-outline-grey dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    {{ selectedStatus || 'Status' }}
                  </button>
                  <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" @click.prevent="selectedStatus = ''">All Status</a></li>
                    <li><a class="dropdown-item" href="#" @click.prevent="selectedStatus = 'pending'">Pending</a></li>
                    <li><a class="dropdown-item" href="#" @click.prevent="selectedStatus = 'in_transit'">In Transit</a></li>
                    <li><a class="dropdown-item" href="#" @click.prevent="selectedStatus = 'delivered'">Delivered</a></li>
                  </ul>
                </div>
                <div class="dropdown">
                  <button class="btn btn-outline-grey dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    {{ selectedService || 'Service Type' }}
                  </button>
                  <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" @click.prevent="selectedService = ''">All Services</a></li>
                    <li><a class="dropdown-item" href="#" @click.prevent="selectedService = 'Registered'">Registered</a></li>
                    <li><a class="dropdown-item" href="#" @click.prevent="selectedService = 'Standard'">Standard</a></li>
                    <li><a class="dropdown-item" href="#" @click.prevent="selectedService = 'Basic'">Basic</a></li>
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
                  v-if="selectedStatus"
                  class="badge bg-primary"
                  @click="selectedStatus = ''"
                  style="cursor: pointer;"
                >
                  Status: {{ selectedStatus }} <i class="fas fa-times ms-1"></i>
                </span>
                <span
                  v-if="selectedService"
                  class="badge bg-success"
                  @click="selectedService = ''"
                  style="cursor: pointer;"
                >
                  Service: {{ selectedService }} <i class="fas fa-times ms-1"></i>
                </span>
                <span
                  v-if="searchQuery"
                  class="badge bg-info text-dark"
                  @click="searchQuery = ''"
                  style="cursor: pointer;"
                >
                  Search: "{{ searchQuery }}" <i class="fas fa-times ms-1"></i>
                </span>
              </div>
            </div>
          </div>


          <div class="row">
            <div class="col-12">
              <div v-if="shipments.length > 0" class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Found {{ filteredShipments.length }} shipments</h5>
                <div class="text-muted small">
                  Showing {{ Math.min(filteredShipments.length, itemsPerPage) }} of {{ filteredShipments.length }}
                </div>
              </div>


              <div v-if="shipments.length > 0" class="shipment-grid">
                <div
                  class="shipment-card card mb-3 shadow-sm"
                  v-for="shipment in paginatedShipments"
                  :key="shipment.mailId"
                  @click="viewShipmentDetails(shipment)"
                  style="cursor: pointer; transition: transform 0.2s;"
                >
                  <div class="card-body">
                    <div class="row">

                      <div class="col-md-4">
                        <div class="d-flex align-items-center mb-2">
                          <h6 class="mb-0 me-2">{{ getTrackingId(shipment) }}</h6>
                          <span class="badge" :class="getStatusBadgeClass(shipment.status)">
                            {{ formatStatus(shipment.status) }}
                          </span>
                        </div>
                        <p class="text-muted small mb-2">
                          <i class="fas fa-calendar me-1"></i>
                          Created: {{ formatDate(shipment.createdDate) }}
                        </p>
                        <p class="text-muted small mb-0">
                          <i class="fas fa-envelope me-1"></i>
                          {{ shipment.service.name }}
                        </p>
                      </div>


                      <div class="col-md-4">
                        <div class="route-info">
                          <div class="d-flex align-items-center mb-1">
                            <small class="text-muted">From: {{ shipment.senderAddress?.countryCode || 'SG' }}</small>
                          </div>
                          <div class="d-flex align-items-center">
                            <small class="text-muted">To: {{ shipment.recipientAddress?.countryCode || 'Unknown' }}</small>
                          </div>
                          <div class="progress mt-2" style="height: 4px;">
                            <div
                              class="progress-bar"
                              :class="getProgressBarClass(shipment.status)"
                              :style="{ width: getProgressWidth(shipment.status) }"
                            ></div>
                          </div>
                          <small class="text-muted d-block mt-1 text-center">
                            {{ getProgressText(shipment.status) }}
                          </small>
                        </div>
                      </div>


                      <div class="col-md-4">
                        <div class="shipment-details">
                          <div class="row text-center">
                            <div class="col-6">
                              <small class="text-muted d-block">Weight</small>
                              <strong>{{ shipment.totalWeight }}kg</strong>
                            </div>
                            <div class="col-6">
                              <small class="text-muted d-block">Value</small>
                              <strong>${{ shipment.totalValue }}</strong>
                            </div>
                          </div>
                          <div class="mt-2 text-center">
                            <small class="text-muted">
                              Expected: {{ formatDate(shipment.expectedDelivery) }}
                            </small>
                          </div>
                        </div>
                      </div>
                    </div>


                    <div class="mt-3 pt-3 border-top">
                      <div class="d-flex align-items-center">
                        <div>
                          <small class="text-muted">Items:</small>
                          <span class="ms-2">
                            {{ shipment.mailItems.map(item => item.itemDescription).join(', ') }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="filteredShipments.length === 0 && shipments.length > 0" class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No shipments match your search</h4>
                <p class="text-muted">Try adjusting your search terms or filters</p>
                <button class="btn btn-primary" @click="clearFilters">
                  Clear All Filters
                </button>
              </div>


              <div v-if="shipments.length === 0 && !loading" class="text-center py-5">
                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No shipments found</h4>
                <p class="text-muted">You haven't created any shipments yet.</p>
                <button class="btn btn-primary" @click="redirectToCreateShipment"  style="display: block; margin: 0 auto; text-align: center;>
                  Create Your First Shipment
                </button>
              </div>


              <div v-if="filteredShipments.length > itemsPerPage" class="d-flex justify-content-center mt-4">
                <nav>
                  <ul class="pagination">
                    <li class="page-item" :class="{ disabled: currentPage === 1 }">
                      <a class="page-link" href="#" @click.prevent="currentPage--">Previous</a>
                    </li>
                    <li
                      v-for="page in totalPages"
                      :key="page"
                      class="page-item"
                      :class="{ active: currentPage === page }"
                    >
                      <a class="page-link" href="#" @click.prevent="currentPage = page">{{ page }}</a>
                    </li>
                    <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                      <a class="page-link" href="#" @click.prevent="currentPage++">Next</a>
                    </li>
                  </ul>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

export default {
  name: 'History',
  setup() {
    const router = useRouter()

    const isAuthenticated = ref(false)
    const user = ref({ email: '' })
    const searchQuery = ref("")
    const selectedStatus = ref("")
    const selectedService = ref("")
    const shipments = ref([])
    const loading = ref(false)
    const error = ref(null)
    const currentPage = ref(1)
    const itemsPerPage = ref(10)

    const checkAuthentication = () => {
      const userData = sessionStorage.getItem('currentUser')
      if (userData) {
        try {
          const userObj = JSON.parse(userData)
          user.value.email = userObj.email || userObj.display_name || 'User'
          isAuthenticated.value = true
        } catch (error) {
          console.error('Error parsing user data:', error)
          isAuthenticated.value = false
        }
      } else {
        isAuthenticated.value = false
      }
    }

    const handleSearch = () => {
      currentPage.value = 1
    }

    const viewShipmentDetails = (shipment) => {
      sessionStorage.setItem('selectedShipment', JSON.stringify(shipment))
      router.push(`/history/${shipment.mailId}`)
    }

    const hasActiveFilters = computed(() => {
      return selectedStatus.value || selectedService.value || searchQuery.value
    })

    const filteredShipments = computed(() => {
      if (!shipments.value.length) return []

      return shipments.value.filter(shipment => {
        const query = searchQuery.value.toLowerCase().trim()

        if (!query) {
          const matchesStatus = !selectedStatus.value || shipment.status === selectedStatus.value
          const matchesService = !selectedService.value ||
            shipment.service?.name?.toLowerCase().includes(selectedService.value.toLowerCase())
          return matchesStatus && matchesService
        }

        const matchesSearch =
          (getTrackingId(shipment).toLowerCase().includes(query)) ||
          (shipment.service?.name?.toLowerCase().includes(query)) ||
          (shipment.recipientAddress?.countryCode?.toLowerCase().includes(query)) ||
          (shipment.recipientAddress?.name?.toLowerCase().includes(query)) ||
          (shipment.mailItems?.some(item =>
            item.itemDescription?.toLowerCase().includes(query)
          )) ||
          (formatStatus(shipment.status).toLowerCase().includes(query)) ||
          (shipment.senderAddress?.countryCode?.toLowerCase().includes(query))

        const matchesStatus = !selectedStatus.value || shipment.status === selectedStatus.value
        const matchesService = !selectedService.value ||
          shipment.service?.name?.toLowerCase().includes(selectedService.value.toLowerCase())

        return matchesSearch && matchesStatus && matchesService
      })
    })

    const totalPages = computed(() => {
      return Math.ceil(filteredShipments.value.length / itemsPerPage.value)
    })

    const paginatedShipments = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage.value
      const end = start + itemsPerPage.value
      return filteredShipments.value.slice(start, end)
    })

    const fetchShipments = async () => {
      if (!isAuthenticated.value) return

      loading.value = true
      error.value = null

      try {
        const response = await fetch('/api/dashboard.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            method: 'getAllMailByCustomerEmail',
            customerEmail: user.value.email
          })
        })

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`)
        }

        const text = await response.text()


        if (text.trim().startsWith('<')) {
          console.warn('Server returned HTML instead of JSON. This might be an error page.')
          shipments.value = []
          return
        }

        let data
        try {
          data = JSON.parse(text)
        } catch (parseError) {
          console.warn('Failed to parse response as JSON:', parseError)
          shipments.value = []
          return
        }

        if (data.success) {
          shipments.value = data.shipments || []
        } else {

          shipments.value = []
        }
      } catch (err) {
        console.error('Error loading shipments:', err)

        error.value = 'Unable to load your shipment history at this time.'
        shipments.value = []
      } finally {
        loading.value = false
      }
    }

    const clearFilters = () => {
      selectedStatus.value = ""
      selectedService.value = ""
      searchQuery.value = ""
      currentPage.value = 1
    }

    const getTrackingId = (shipment) => {
      let trackingId = `TRK-${shipment.mailId.toString().padStart(6, '0')}`;
      if (shipment.trackingNumber && shipment.trackingNumber !== 0) {
        trackingId = `TRK-${shipment.trackingNumber}`;
      }
      return trackingId;
    }

    const getStatusBadgeClass = (status) => {
      const statusClasses = {
        'pending': 'bg-warning text-dark',
        'in_transit': 'bg-info',
        'delivered': 'bg-success'
      }
      return statusClasses[status] || 'bg-secondary'
    }

    const formatStatus = (status) => {
      const statusMap = {
        'pending': 'Pending',
        'in_transit': 'In Transit',
        'delivered': 'Delivered'
      }
      return statusMap[status] || status
    }

    const formatDate = (dateString) => {
      if (!dateString) return 'N/A'
      try {
        return new Date(dateString).toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        })
      } catch (error) {
        return 'Invalid Date'
      }
    }

    const getProgressWidth = (status) => {
      const progressMap = {
        'pending': '25%',
        'in_transit': '60%',
        'delivered': '100%'
      }
      return progressMap[status] || '0%'
    }

    const getProgressBarClass = (status) => {
      const classMap = {
        'pending': 'bg-warning',
        'in_transit': 'bg-info',
        'delivered': 'bg-success'
      }
      return classMap[status] || 'bg-secondary'
    }

    const getProgressText = (status) => {
      const textMap = {
        'pending': 'Processing',
        'in_transit': 'In Transit',
        'delivered': 'Delivered'
      }
      return textMap[status] || 'Unknown'
    }

    const redirectToLogin = () => {
      router.push('/login')
    }

    const redirectToCreateShipment = () => {
      router.push('/shipment')
    }

    onMounted(() => {
      checkAuthentication()
      if (isAuthenticated.value) {
        fetchShipments()
      }
    })

    return {
      isAuthenticated,
      user,
      searchQuery,
      selectedStatus,
      selectedService,
      shipments,
      loading,
      error,
      currentPage,
      itemsPerPage,
      hasActiveFilters,
      filteredShipments,
      totalPages,
      paginatedShipments,
      fetchShipments,
      clearFilters,
      handleSearch,
      viewShipmentDetails,
      getTrackingId,
      getStatusBadgeClass,
      formatStatus,
      formatDate,
      getProgressWidth,
      getProgressBarClass,
      getProgressText,
      redirectToLogin,
      redirectToCreateShipment
    }
  }
}
</script>

<style scoped>

.history-page {
  min-height: 100vh;
  background: linear-gradient(135deg, var(--light-pink) 0%, var(--pink-grey) 100%);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.login-required {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, var(--light-pink) 0%, var(--pink-grey) 100%);
}

.login-message {
  text-align: center;
  background: white;
  padding: 3rem;
  border-radius: 20px;
  box-shadow: 0 8px 30px rgba(255, 66, 117, 0.2);
  max-width: 400px;
  width: 90%;
}

.message-icon {
  font-size: 4rem;
  color: var(--hot-pink);
  margin-bottom: 1.5rem;
}

.login-message h2 {
  color: var(--dark-slate-blue);
  margin-bottom: 1rem;
  font-size: 1.8rem;
}

.login-message p {
  color: var(--slate-blue);
  margin-bottom: 2rem;
  font-size: 1.1rem;
  line-height: 1.5;
}

.action-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-primary {
  background: var(--hot-pink);
  color: white;
}

.btn-primary:hover {
  background: var(--dark-pink);
  transform: translateY(-2px);
}

.text-hot-pink {
  color: var(--hot-pink) !important;
}

.jua {
  font-family: 'Jua', sans-serif;
}

.loading-spinner-large {
  width: 60px;
  height: 60px;
  border: 6px solid var(--light-pink);
  border-top: 6px solid var(--hot-pink);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.shipment-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
}

.route-info {
  border-left: 2px solid var(--pink-grey);
  padding-left: 15px;
}

.shipment-details {
  border-left: 2px solid var(--pink-grey);
  padding-left: 15px;
}

.progress {
  background-color: var(--pink-grey);
}

.badge {
  font-size: 0.75rem;
}

.input-group-text {
  background-color: #f8f9fa;
  border: 1px solid #ced4da;
}

.btn-outline-grey {
  border-color: var(--grey-outline);
  color: var(--grey-outline);
  background-color: white;
}

.btn-outline-grey:hover {
  background-color: var(--grey-outline);
  border-color: var(--grey-outline);
  color: white;
}

.btn-outline-danger {
  border-color: var(--dark-pink);
  color: var(--dark-pink);
  background-color: white;
}

.btn-outline-danger:hover {
  background-color: var(--dark-pink);
  color: white;
}

.pagination .page-link {
  color: var(--hot-pink);
  border-color: var(--pink-grey);
}

.pagination .page-item.active .page-link {
  background-color: var(--hot-pink);
  border-color: var(--hot-pink);
}

.pagination .page-link:hover {
  color: var(--dark-pink);
  background-color: var(--light-pink);
  border-color: var(--pink-grey);
}

@media (max-width: 768px) {
  .route-info,
  .shipment-details {
    border-left: none;
    border-top: 2px solid var(--pink-grey);
    padding-left: 0;
    padding-top: 15px;
    margin-top: 15px;
  }

  .shipment-card .card-body .row > div {
    margin-bottom: 15px;
  }

  .shipment-card .card-body .row > div:last-child {
    margin-bottom: 0;
  }

  .action-buttons {
    flex-direction: column;
  }

  .btn {
    justify-content: center;
  }
}
</style>
