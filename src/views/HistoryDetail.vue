
<template>
  <div class="history-detail-page">

    <div v-if="!isAuthenticated" class="login-required">
      <div class="login-message">
        <div class="message-icon">
          <i class="fas fa-lock"></i>
        </div>
        <h2>Authentication Required</h2>
        <p>Please log in to view shipments details</p>
        <div class="action-buttons">
          <button @click="redirectToLogin" class="btn btn-primary">
            <i class="fas fa-sign-in-alt"></i>
            Go to Login
          </button>
        </div>
      </div>
    </div>


    <div v-else-if="loading" class="text-center py-5">
      <div class="loading-spinner-large"></div>
      <p class="mt-3 text-muted">Loading shipments details...</p>
    </div>


    <div v-else-if="error" class="text-center py-5">
      <h4 class="text-danger">Failed to load shipments details</h4>
      <button class="btn btn-primary" @click="fetchshipmDetails">
      Try Again
      </button>
      <button class="btn btn-outline-secondary ms-2" @click="goBack">
         Go Back
      </button>
    </div>

    <div v-else-if="shipm" class="dashboard-wrapper">
      <hr />
      <div class="row bg-light-pink justify-content-center airplane-header">
        <div class="col-lg-4 col-md-6 col-sm-8 py-2 text-center">
          <h1 class="jua text-hot-pink">Shipment Details</h1>
        </div>
      </div>
      <hr />

<br>
   <div class="shipment-header">
  <button @click="goBack" class="back-btn">
  Back
  </button>

  <div class="tracking-number">
    {{ getTrackingId(shipm) }}
  </div>
</div>


      <div class="container mt-4">
        <div class="row">

          <div class="col-lg-8">

            <div class="detail-card card mb-4">
              <div class="card-header bg-light-pink">
                <h4 class="mb-0">
                  Route Information
                </h4>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <h6 class="text-muted">From:</h6>
                    <div class="address-section">
                      <strong>{{ shipm.senderAddress?.name || 'N/A' }}</strong>
                      <p class="mb-1">{{ getCountryName(shipm.senderAddress?.countryCode) }}</p>
                      <p class="mb-0 text-muted">
                        <small>Coordinates: {{ shipm.senderAddress?.coordinates?.lat?.toFixed(4) }}, {{ shipm.senderAddress?.coordinates?.lng?.toFixed(4) }}</small>
                      </p>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <h6 class="text-muted">To:</h6>
                    <div class="address-section">
                      <strong>{{ shipm.recipientAddress?.name || 'N/A' }}</strong>
                      <p class="mb-1">{{ getCountryName(shipm.recipientAddress?.countryCode) }}</p>
                      <p class="mb-0 text-muted">
                        <small>Coordinates: {{ shipm.recipientAddress?.coordinates?.lat?.toFixed(4) }}, {{ shipm.recipientAddress?.coordinates?.lng?.toFixed(4) }}</small>
                      </p>
                    </div>
                  </div>
                </div>

                <div class="route-progress mt-4">
                  <div class="progress-labels d-flex justify-content-between">
                    <span class="progress-label">{{ getCountryName(shipm.senderAddress?.countryCode) }}</span>
                    <span class="progress-percent">{{ Math.round(getProgressWidth(shipm.status)) }}%</span>
                    <span class="progress-label">{{ getCountryName(shipm.recipientAddress?.countryCode) }}</span>
                  </div>
                  <div class="progress-track">
                    <div class="progress-bar">
                      <div class="progress-fill" :style="{ width: getProgressWidth(shipm.status) + '%' }"></div>
                      <div class="progress-marker" :style="{ left: getProgressWidth(shipm.status) + '%' }">
                        <i class="fas fa-shipping-fast"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="detail-card card mb-4">
              <div class="card-header bg-light-pink">
                <h4 class="mb-0">

                  Shipment Details
                </h4>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="detail-item">
                      <span class="detail-label">Service Type:</span>
                      <span class="detail-value">{{ shipm.service?.name || 'N/A' }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Service Zone:</span>
                      <span class="detail-value">{{ shipm.service?.zone || 'N/A' }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Total Weight:</span>
                      <span class="detail-value">{{ shipm.totalWeight }} kg</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Created Date:</span>
                      <span class="detail-value">{{ formatDate(shipm.createdDate) }}</span>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="detail-item">
                      <span class="detail-label">Parcel Dimensions:</span>
                      <span class="detail-value">
                        {{ shipm.parcelLength }} × {{ shipm.parcelWidth }} × {{ shipm.parcelHeight }} cm
                      </span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Total Value:</span>
                      <span class="detail-value">${{ shipm.totalValue.toFixed(2) }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Expected Delivery:</span>
                      <span class="detail-value">{{ formatDate(shipm.expectedDelivery) }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>


            <div class="detail-card card mb-4">
              <div class="card-header bg-light-pink">
                <h4 class="mb-0">

                  Items in Shipment
                </h4>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Weight (kg)</th>
                        <th>Value</th>
                        <th>Total Value</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(item, index) in shipm.mailItems" :key="index">
                        <td>{{ item.itemDescription }}</td>
                        <td>{{ item.itemQuantity }}</td>
                        <td>{{ item.itemWeight }}</td>
                        <td>${{ item.declaredValue.toFixed(2) }}</td>
                        <td>${{ (item.declaredValue * item.itemQuantity).toFixed(2) }}</td>
                      </tr>
                    </tbody>
                    <tfoot>
                      <tr class="table-primary">
                        <td colspan="4" class="text-end fw-bold">Total:</td>
                        <td class="fw-bold">${{ shipm.totalValue.toFixed(2) }}</td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>
          </div>


          <div class="col-lg-4">

            <div class="detail-card card mb-4">
              <div class="card-header bg-light-pink">
                <h4 class="mb-0">
<i class="fas fa-clock me-2"></i>
                  Timeline
                </h4>
              </div>
              <div class="card-body">
                <div class="timeline">
                  <div class="timeline-item" :class="{ active: true }">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                      <h6>Order Created</h6>
                      <p class="text-muted mb-0">{{ formatDate(shipm.createdDate) }}</p>
                    </div>
                  </div>
                  <div class="timeline-item" :class="{ active: shipm.status === 'in_transit' || shipm.status === 'delivered' }">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                      <h6>In Transit</h6>
                      <p class="text-muted mb-0" v-if="shipm.status === 'in_transit'">Currently in transit</p>
                      <p class="text-muted mb-0" v-else-if="shipm.status === 'delivered'">Completed on {{ formatDate(shipm.expectedDelivery) }}</p>
                    </div>
                  </div>
                  <div class="timeline-item" :class="{ active: shipm.status === 'delivered' }">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                      <h6>Delivered</h6>
                      <p class="text-muted mb-0" v-if="shipm.status === 'delivered'">
                        Delivered on {{ formatDate(shipm.expectedDelivery) }}
                      </p>
                      <p class="text-muted mb-0" v-else>
                        Expected: {{ formatDate(shipm.expectedDelivery) }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>


<div class="detail-card card mb-4">
  <div class="card-header bg-light-pink">
    <h4 class="mb-0">
      <i class="fas fa-user me-2"></i>
      Sender Information
    </h4>
  </div>
  <div class="card-body">
    <div class="customer-info">
      <p class="mb-2">
        <strong>Name:</strong><br>
        {{ shipm.senderAddress?.name || 'N/A' }}
      </p>
      <p class="mb-2">
        <strong>Country:</strong><br>
        {{ getCountryName(shipm.senderAddress?.countryCode) }}
      </p>
      <p class="mb-0">
        <strong>Email:</strong><br>
        {{ shipm.customerEmail }}
      </p>
    </div>
  </div>
</div>

<div class="detail-card card mb-4">
  <div class="card-header bg-light-pink">
    <h4 class="mb-0">
<i class="fas fa-user me-2"></i>
      Recipient Information
    </h4>
  </div>
  <div class="card-body">
    <div class="customer-info">
      <p class="mb-2">
        <strong>Name:</strong><br>
        {{ shipm.recipientAddress?.name || 'N/A' }}
      </p>
      <p class="mb-0">
        <strong>Country:</strong><br>
        {{ getCountryName(shipm.recipientAddress?.countryCode) }}
      </p>
    </div>
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
import { useRouter, useRoute } from 'vue-router'

export default {
  name: 'HistoryDetail',
  setup() {
    const router = useRouter()
    const route = useRoute()


    const isAuthenticated = ref(false)
    const user = ref({ email: '' })
    const shipm = ref(null)
    const loading = ref(false)
    const error = ref(null)

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


    const fetchshipmDetails = async () => {
      if (!isAuthenticated.value) return

      loading.value = true
      error.value = null

      try {
        const shipmId = parseInt(route.params.id)


        const storedshipm = sessionStorage.getItem('selectedshipm')
        if (storedshipm) {
          shipm.value = JSON.parse(storedshipm)
          // console.log(shipm.value)
        } else {

          // console.log(user.value.email)

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

          const data = await response.json()
          // console.log('Db:', data)

          if (data.success && data.shipments) {

            const foundshipm = data.shipments.find(
              shipment => shipment.mailId === shipmId
            )

            if (foundshipm) {
              shipm.value = foundshipm
              // console.log('Found:', foundshipm)
            } else {
              throw new Error(`shipm with ID ${shipmId} not found in your shipments`)
            }
          } else {
            throw new Error(data.error || 'Failed to load shipments')
          }
        }

      } catch (err) {
        // console.error('Error:', err)
        error.value = `Failed to load shipm details: ${err.message}`

        // Fallback
        try {
          const shipmsData = sessionStorage.getItem('usershipms')
          if (shipmsData) {
            const shipms = JSON.parse(shipmsData)
            const foundshipm = shipms.find(
              t => t.mailId === parseInt(route.params.id)
            )
            if (foundshipm) {
              shipm.value = foundshipm
              error.value = null

            }
          }
        } catch (fallbackError) {
          console.error('failed:', fallbackError)
        }
      } finally {
        loading.value = false
      }
    }


    const getTrackingId = (shipm) => {
      let trackingId = `TRK-${shipm.mailId.toString().padStart(6, '0')}`;
      if (shipm.trackingNumber && shipm.trackingNumber !== 0) {
        trackingId = `TRK-${shipm.trackingNumber}`;
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
        'pending': 25,
        'in_transit': 60,
        'delivered': 100
      }
      return progressMap[status] || 0
    }

    const getCountryName = (countryCode) => {
      const countryMap = {
        'SG': 'Singapore',
        'US': 'United States',
        'MY': 'Malaysia',
        'UK': 'United Kingdom',
        'CN': 'China',
        'JP': 'Japan',
        'KR': 'South Korea',
        'AU': 'Australia',
        'CA': 'Canada',
        'FR': 'France',
        'DE': 'Germany',
        'IT': 'Italy',
        'IN': 'India',
        'TH': 'Thailand',
        'VN': 'Vietnam',
        'PH': 'Philippines',
        'ID': 'Indonesia'
      }
      return countryMap[countryCode] || countryCode || 'Unknown Country'
    }


    const downloadLabel = () => {
      alert('Downloading shipping label for ' + getTrackingId(shipm.value))
    }

    const trackPackage = () => {
      alert('Opening live tracking for ' + getTrackingId(shipm.value))
    }

    const goBack = () => {
      router.back()
    }

    const redirectToLogin = () => {
      router.push('/login')
    }


    onMounted(() => {
      checkAuthentication()
      if (isAuthenticated.value) {
        fetchshipmDetails()
      }
    })

    return {
      isAuthenticated,
      user,
      shipm,
      loading,
      error,
      fetchshipmDetails,
      getTrackingId,
      getStatusBadgeClass,
      formatStatus,
      formatDate,
      getProgressWidth,
      getCountryName,
      downloadLabel,
      trackPackage,
      goBack,
      redirectToLogin
    }
  }
}
</script>

<style scoped>

.shipment-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
  padding: 0.5rem;
  background: rgba(219, 112, 147, 0.7);
  border-radius: 8px;
  border: 1px solid rgba(199, 92, 127, 0.8);
}

.back-btn {
  background: transparent;
  border: 1px solid #fff;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.back-btn:hover {
  background: rgba(255, 255, 255, 0.2);
}

.tracking-number {
  flex-grow: 1;
  text-align: center;
  color: white;
  font-weight: bold;
  font-size: 1.25rem;
  text-decoration: underline;
}

.history-detail-page {
  min-height: 100vh;
  background: linear-gradient(135deg, var(--light-pink) 0%, var(--pink-grey) 100%);
}

.detail-card {
  border: none;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.detail-card .card-header {
  border-radius: 12px 12px 0 0 !important;
  border: none;
}

.address-section {
  background: var(--light-pink);
  padding: 1rem;
  border-radius: 8px;
  border-left: 4px solid var(--hot-pink);
}

.detail-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid var(--pink-grey);
}

.detail-item:last-child {
  border-bottom: none;
}

.detail-label {
  font-weight: 600;
  color: var(--dark-slate-blue);
}

.detail-value {
  color: var(--slate-blue);
  text-align: right;
}

.timeline {
  position: relative;
  padding-left: 2rem;
}

.timeline-item {
  position: relative;
  padding-bottom: 1.5rem;
}

.timeline-item:last-child {
  padding-bottom: 0;
}

.timeline-marker {
  position: absolute;
  left: -2rem;
  top: 0;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: var(--pink-grey);
  border: 3px solid white;
}

.timeline-item.active .timeline-marker {
  background: var(--hot-pink);
}

.timeline-content h6 {
  margin-bottom: 0.25rem;
  color: var(--dark-slate-blue);
}

.route-progress {
  margin-top: 2rem;
}

.progress-track {
  position: relative;
  margin-top: 0.5rem;
}

.progress-bar {
  height: 8px;
  background: var(--pink-grey);
  border-radius: 4px;
  overflow: hidden;
  position: relative;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--hot-pink), var(--pink), var(--dark-pink));
  transition: width 0.5s ease;
}

.progress-marker {
  position: absolute;
  top: -6px;
  transform: translateX(-50%);
  width: 20px;
  height: 20px;
  background: white;
  border: 2px solid var(--hot-pink);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  color: var(--hot-pink);
}

.status-badge {
  font-size: 0.9rem;
  padding: 0.5rem 1rem;
}

.customer-info {
  background: var(--light-pink);
  padding: 1rem;
  border-radius: 8px;
}
</style>
