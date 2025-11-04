<template>
  <div class="history-detail-page">

    <div v-if="!isAuthenticated" class="login-required">
      <div class="login-message">
        <div class="message-icon">
          <i class="fas fa-lock"></i>
        </div>
        <h2>Authentication Required</h2>
        <p>Please log in to view transaction details</p>
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
      <p class="mt-3 text-muted">Loading transaction details...</p>
    </div>


    <div v-else-if="error" class="text-center py-5">
      <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
      <h4 class="text-danger">Failed to load transaction details</h4>
      <p class="text-muted">{{ error }}</p>
      <button class="btn btn-primary" @click="fetchTransactionDetails">
        <i class="fas fa-redo"></i> Try Again
      </button>
      <button class="btn btn-outline-secondary ms-2" @click="goBack">
        <i class="fas fa-arrow-left"></i> Go Back
      </button>
    </div>

    <div v-else-if="transaction" class="dashboard-wrapper">
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
    <i class="fas fa-arrow-left"></i> Back
  </button>

  <div class="tracking-number">
    {{ formatTrackingNumber(transaction.trackingNumber) }}
  </div>
</div>


      <div class="container mt-4">
        <div class="row">

          <div class="col-lg-8">

            <div class="detail-card card mb-4">
              <div class="card-header bg-light-pink">
                <h4 class="mb-0">
                  <i class="fas fa-route me-2"></i>
                  Route Information
                </h4>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <h6 class="text-muted">From:</h6>
                    <div class="address-section">
                      <strong>{{ transaction.senderAddress?.name || 'N/A' }}</strong>
                      <p class="mb-1">{{ getCountryName(transaction.senderAddress?.countryCode) }}</p>
                      <p class="mb-0 text-muted">
                        <small>Coordinates: {{ transaction.senderAddress?.coordinates?.lat?.toFixed(4) }}, {{ transaction.senderAddress?.coordinates?.lng?.toFixed(4) }}</small>
                      </p>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <h6 class="text-muted">To:</h6>
                    <div class="address-section">
                      <strong>{{ transaction.recipientAddress?.name || 'N/A' }}</strong>
                      <p class="mb-1">{{ getCountryName(transaction.recipientAddress?.countryCode) }}</p>
                      <p class="mb-0 text-muted">
                        <small>Coordinates: {{ transaction.recipientAddress?.coordinates?.lat?.toFixed(4) }}, {{ transaction.recipientAddress?.coordinates?.lng?.toFixed(4) }}</small>
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Progress Bar -->
                <div class="route-progress mt-4">
                  <div class="progress-labels d-flex justify-content-between">
                    <span class="progress-label">{{ getCountryName(transaction.senderAddress?.countryCode) }}</span>
                    <span class="progress-percent">{{ Math.round(getProgressWidth(transaction.status)) }}%</span>
                    <span class="progress-label">{{ getCountryName(transaction.recipientAddress?.countryCode) }}</span>
                  </div>
                  <div class="progress-track">
                    <div class="progress-bar">
                      <div class="progress-fill" :style="{ width: getProgressWidth(transaction.status) + '%' }"></div>
                      <div class="progress-marker" :style="{ left: getProgressWidth(transaction.status) + '%' }">
                        <i class="fas fa-shipping-fast"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Shipment Details Card -->
            <div class="detail-card card mb-4">
              <div class="card-header bg-light-pink">
                <h4 class="mb-0">
                  <i class="fas fa-info-circle me-2"></i>
                  Shipment Details
                </h4>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="detail-item">
                      <span class="detail-label">Service Type:</span>
                      <span class="detail-value">{{ transaction.service?.name || 'N/A' }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Service Zone:</span>
                      <span class="detail-value">{{ transaction.service?.zone || 'N/A' }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Total Weight:</span>
                      <span class="detail-value">{{ transaction.totalWeight }} kg</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Created Date:</span>
                      <span class="detail-value">{{ formatDate(transaction.createdDate) }}</span>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="detail-item">
                      <span class="detail-label">Parcel Dimensions:</span>
                      <span class="detail-value">
                        {{ transaction.parcelLength }} × {{ transaction.parcelWidth }} × {{ transaction.parcelHeight }} cm
                      </span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Total Value:</span>
                      <span class="detail-value">${{ transaction.totalValue.toFixed(2) }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Payment Status:</span>
                      <span class="detail-value badge" :class="transaction.hasBeenPaid ? 'bg-success' : 'bg-warning text-dark'">
                        {{ transaction.hasBeenPaid ? 'Paid' : 'Pending' }}
                      </span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Expected Delivery:</span>
                      <span class="detail-value">{{ formatDate(transaction.expectedDelivery) }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Items Card -->
            <div class="detail-card card mb-4">
              <div class="card-header bg-light-pink">
                <h4 class="mb-0">
                  <i class="fas fa-boxes me-2"></i>
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
                      <tr v-for="(item, index) in transaction.mailItems" :key="index">
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
                        <td class="fw-bold">${{ transaction.totalValue.toFixed(2) }}</td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="col-lg-4">
            <!-- Timeline Card -->
            <div class="detail-card card mb-4">
              <div class="card-header bg-light-pink">
                <h4 class="mb-0">
                  <i class="fas fa-history me-2"></i>
                  Timeline
                </h4>
              </div>
              <div class="card-body">
                <div class="timeline">
                  <div class="timeline-item" :class="{ active: true }">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                      <h6>Order Created</h6>
                      <p class="text-muted mb-0">{{ formatDate(transaction.createdDate) }}</p>
                    </div>
                  </div>
                  <div class="timeline-item" :class="{ active: transaction.status === 'in_transit' || transaction.status === 'delivered' }">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                      <h6>In Transit</h6>
                      <p class="text-muted mb-0" v-if="transaction.status === 'in_transit'">Currently in transit</p>
                      <p class="text-muted mb-0" v-else-if="transaction.status === 'delivered'">Completed on {{ formatDate(transaction.expectedDelivery) }}</p>
                    </div>
                  </div>
                  <div class="timeline-item" :class="{ active: transaction.status === 'delivered' }">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                      <h6>Delivered</h6>
                      <p class="text-muted mb-0" v-if="transaction.status === 'delivered'">Successfully delivered</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Status Card -->
            <div class="detail-card card mb-4">
              <div class="card-header bg-light-pink">
                <h4 class="mb-0">
                  <i class="fas fa-truck me-2"></i>
                  Current Status
                </h4>
              </div>
              <div class="card-body text-center">
                <div class="status-indicator mb-3">
                  <div class="status-icon" :class="transaction.status">
                    <i class="fas" :class="getStatusIcon(transaction.status)"></i>
                  </div>
                </div>
                <h4 :class="getStatusTextClass(transaction.status)">
                  {{ formatStatus(transaction.status) }}
                </h4>
                <p class="text-muted mb-0">
                  {{ getStatusDescription(transaction.status) }}
                </p>
              </div>
            </div>

            <!-- Actions Card -->
            <div class="detail-card card">
              <div class="card-header bg-light-pink">
                <h4 class="mb-0">
                  <i class="fas fa-cog me-2"></i>
                  Actions
                </h4>
              </div>
              <div class="card-body">
                <div class="d-grid gap-2">
                  <button class="btn btn-outline-primary" @click="downloadInvoice">
                    <i class="fas fa-download me-2"></i>
                    Download Invoice
                  </button>
                  <button class="btn btn-outline-secondary" @click="shareTracking">
                    <i class="fas fa-share-alt me-2"></i>
                    Share Tracking
                  </button>
                  <button class="btn btn-outline-info" @click="contactSupport">
                    <i class="fas fa-headset me-2"></i>
                    Contact Support
                  </button>
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

    // Reactive data
    const isAuthenticated = ref(false)
    const user = ref({ email: '' })
    const transaction = ref(null)
    const loading = ref(false)
    const error = ref(null)

    // Check authentication
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

    // Fetch transaction details
    const fetchTransactionDetails = async () => {
      if (!isAuthenticated.value) return

      loading.value = true
      error.value = null

      try {
        // Try to get transaction from session storage first
        const storedTransaction = sessionStorage.getItem('selectedTransaction')
        if (storedTransaction) {
          transaction.value = JSON.parse(storedTransaction)
          console.log('Loaded transaction from session storage:', transaction.value)
          loading.value = false
          return
        }

        // If not in session storage, try to fetch from API
        const mailId = route.params.id
        if (!mailId) {
          throw new Error('No transaction ID provided')
        }

        console.log('Fetching transaction details for mailId:', mailId)

        const response = await fetch('/api/dashboard.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            method: 'getMailById',
            mailId: mailId
          })
        })

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`)
        }

        const data = await response.json()
        console.log('API Response for transaction details:', data)

        if (data.success && data.shipment) {
          transaction.value = data.shipment
          console.log('Successfully loaded transaction details:', transaction.value)
        } else {
          throw new Error(data.error || 'Failed to load transaction details from server')
        }
      } catch (err) {
        console.error('Error loading transaction details:', err)
        error.value = `Failed to load transaction details: ${err.message}`
      } finally {
        loading.value = false
      }
    }

    // Navigation methods
    const goBack = () => {
      router.push('/history')
    }

    const redirectToLogin = () => {
      router.push('/login')
    }

    // Action methods
    const downloadInvoice = () => {
      alert('Invoice download feature would be implemented here')
      // Implementation for invoice download
    }

    const shareTracking = () => {
      if (transaction.value?.trackingNumber) {
        const trackingUrl = `${window.location.origin}/tracking/${formatTrackingNumber(transaction.value.trackingNumber)}`
        navigator.clipboard.writeText(trackingUrl).then(() => {
          alert('Tracking link copied to clipboard!')
        }).catch(() => {
          alert(`Tracking Number: ${formatTrackingNumber(transaction.value.trackingNumber)}`)
        })
      }
    }

    const contactSupport = () => {
      alert('Contact support feature would be implemented here')
      // Implementation for contact support
    }

    // Formatting methods
    const formatTrackingNumber = (trackingNumber) => {
      if (!trackingNumber) return 'N/A'
      
      // Handle both string and number tracking numbers
      const trackingStr = trackingNumber.toString()
      
      // If it's already formatted as TRK-XXXXXX, return as is
      if (trackingStr.startsWith('TRK-')) {
        return trackingStr
      }
      
      // Otherwise format it as TRK-XXXXXX
      return `TRK-${trackingStr.padStart(6, '0')}`
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
          month: 'long',
          day: 'numeric'
        })
      } catch (error) {
        return 'Invalid Date'
      }
    }

    const getCountryName = (countryCode) => {
      if (!countryCode) return 'Unknown'
      const countryNames = {
        'SG': 'Singapore',
        'MY': 'Malaysia',
        'ID': 'Indonesia',
        'TH': 'Thailand',
        'VN': 'Vietnam',
        'PH': 'Philippines',
        'US': 'United States',
        'GB': 'United Kingdom'
      }
      return countryNames[countryCode] || countryCode
    }

    const getProgressWidth = (status) => {
      const progressMap = {
        'pending': 25,
        'in_transit': 60,
        'delivered': 100
      }
      return progressMap[status] || 0
    }

    const getStatusIcon = (status) => {
      const iconMap = {
        'pending': 'fa-clock',
        'in_transit': 'fa-shipping-fast',
        'delivered': 'fa-check-circle'
      }
      return iconMap[status] || 'fa-question-circle'
    }

    const getStatusTextClass = (status) => {
      const classMap = {
        'pending': 'text-warning',
        'in_transit': 'text-info',
        'delivered': 'text-success'
      }
      return classMap[status] || 'text-secondary'
    }

    const getStatusDescription = (status) => {
      const descriptionMap = {
        'pending': 'Your shipment is being processed',
        'in_transit': 'Your shipment is on the way',
        'delivered': 'Your shipment has been delivered'
      }
      return descriptionMap[status] || 'Status information not available'
    }

    // Lifecycle
    onMounted(() => {
      checkAuthentication()
      if (isAuthenticated.value) {
        fetchTransactionDetails()
      }
    })

    return {
      isAuthenticated,
      user,
      transaction,
      loading,
      error,
      goBack,
      redirectToLogin,
      fetchTransactionDetails,
      downloadInvoice,
      shareTracking,
      contactSupport,
      formatTrackingNumber,
      formatStatus,
      formatDate,
      getCountryName,
      getProgressWidth,
      getStatusIcon,
      getStatusTextClass,
      getStatusDescription
    }
  }
}
</script>

<style scoped>
.history-detail-page {
  min-height: 100vh;
  background: linear-gradient(135deg, var(--light-pink) 0%, var(--pink-grey) 100%);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Authentication Styles */
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

/* Header Styles */
.text-hot-pink {
  color: var(--hot-pink) !important;
}

.jua {
  font-family: 'Jua', sans-serif;
}

.bg-light-pink {
  background-color: var(--light-pink) !important;
}

/* Loading Styles */
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

/* Shipment Header */
.shipment-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 2rem;
  padding: 0 1rem;
}

.back-btn {
  background: var(--light-pink);
  border: 2px solid var(--hot-pink);
  color: var(--hot-pink);
  padding: 0.5rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.back-btn:hover {
  background: var(--hot-pink);
  color: white;
}

.tracking-number {
  font-size: 1.5rem;
  font-weight: bold;
  color: var(--dark-slate-blue);
  background: white;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Detail Cards */
.detail-card {
  border: none;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.detail-card .card-header {
  border-bottom: 2px solid var(--pink-grey);
  padding: 1rem 1.5rem;
}

.detail-card .card-body {
  padding: 1.5rem;
}

/* Route Information */
.address-section {
  background: var(--pink-grey);
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

/* Progress Bar */
.route-progress {
  margin-top: 2rem;
}

.progress-labels {
  margin-bottom: 0.5rem;
}

.progress-label {
  font-size: 0.875rem;
  color: var(--slate-blue);
  font-weight: 500;
}

.progress-percent {
  font-size: 0.875rem;
  color: var(--hot-pink);
  font-weight: bold;
}

.progress-track {
  position: relative;
  height: 8px;
  background: var(--pink-grey);
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--hot-pink), var(--dark-pink));
  border-radius: 4px;
  transition: width 0.5s ease;
}

.progress-marker {
  position: absolute;
  top: 50%;
  transform: translate(-50%, -50%);
  background: white;
  border: 3px solid var(--hot-pink);
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  color: var(--hot-pink);
}

/* Detail Items */
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
  color: var(--slate-blue);
  font-weight: 500;
}

.detail-value {
  color: var(--dark-slate-blue);
  font-weight: 600;
}

/* Timeline */
.timeline {
  position: relative;
  padding-left: 2rem;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 0.75rem;
  top: 0;
  bottom: 0;
  width: 2px;
  background: var(--pink-grey);
}

.timeline-item {
  position: relative;
  margin-bottom: 1.5rem;
}

.timeline-item:last-child {
  margin-bottom: 0;
}

.timeline-marker {
  position: absolute;
  left: -2rem;
  top: 0.25rem;
  width: 1rem;
  height: 1rem;
  border-radius: 50%;
  background: var(--pink-grey);
  border: 3px solid white;
  z-index: 1;
}

.timeline-item.active .timeline-marker {
  background: var(--hot-pink);
}

.timeline-content h6 {
  margin-bottom: 0.25rem;
  color: var(--dark-slate-blue);
}

/* Status Indicator */
.status-indicator {
  display: flex;
  justify-content: center;
}

.status-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  color: white;
}

.status-icon.pending {
  background: var(--warning);
}

.status-icon.in_transit {
  background: var(--info);
}

.status-icon.delivered {
  background: var(--success);
}

/* Table Styles */
.table th {
  background: var(--light-pink);
  border-bottom: 2px solid var(--pink-grey);
  color: var(--dark-slate-blue);
  font-weight: 600;
}

.table td {
  border-color: var(--pink-grey);
}

/* Responsive */
@media (max-width: 768px) {
  .shipment-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }

  .detail-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.25rem;
  }

  .progress-labels {
    flex-direction: column;
    gap: 0.5rem;
    text-align: center;
  }

  .timeline {
    padding-left: 1.5rem;
  }

  .timeline::before {
    left: 0.5rem;
  }

  .timeline-marker {
    left: -1.5rem;
  }
}
</style>