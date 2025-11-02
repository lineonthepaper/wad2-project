<template>
  <div class="history-detail-page">
    <!-- Authentication Check -->
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

    <!-- Loading State -->
    <div v-else-if="loading" class="text-center py-5">
      <div class="loading-spinner-large"></div>
      <p class="mt-3 text-muted">Loading transaction details...</p>
    </div>

    <!-- Error State -->
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

    <!-- Main Content -->
    <div v-else-if="transaction" class="dashboard-wrapper">
      <!-- Header -->
      <div class="row bg-light-pink justify-content-center airplane-header">
        <div class="col-lg-8 col-md-10 col-sm-12 py-3 text-center">
          <div class="d-flex justify-content-between align-items-center">
            <button @click="goBack" class="btn btn-outline-light">
              <i class="fas fa-arrow-left"></i> Back
            </button>
            <h1 class="jua text-hot-pink mb-0">Shipment Details</h1>
            <div class="status-display">

            </div>
          </div>

        </div>
      </div>
 <h4 class="text-white mb-0 mt-2">TRK-{{ transaction.trackingNumber.toString().padStart(6, '0') }}</h4>
      <div class="container mt-4">
        <div class="row">
          <!-- Main Content -->
          <div class="col-lg-8">
            <!-- Route Information Card -->
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
                      <strong>{{ senderAddress?.name || 'N/A' }}</strong>
                      <p class="mb-1">{{ senderAddressLine1 }}</p>
                      <p class="mb-1" v-if="senderAddressLine2">{{ senderAddressLine2 }}</p>
                      <p class="mb-1" v-if="senderAddressLine3">{{ senderAddressLine3 }}</p>
                      <p class="mb-0">
                        {{ senderAddress?.postalCode || 'N/A' }},
                        {{ getCountryName(senderAddress?.countryCode) }}
                      </p>
                      <p class="mb-0" v-if="senderAddress?.phone">
                        <i class="fas fa-phone me-1"></i>
                        {{ senderAddress?.phoneCountryCode || '' }} {{ senderAddress?.phone }}
                      </p>
                      <p class="mb-0">
                        <i class="fas fa-envelope me-1"></i>
                        {{ senderAddress?.email || 'N/A' }}
                      </p>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <h6 class="text-muted">To:</h6>
                    <div class="address-section">
                      <strong>{{ recipientAddress?.name || 'N/A' }}</strong>
                      <p class="mb-1">{{ recipientAddressLine1 }}</p>
                      <p class="mb-1" v-if="recipientAddressLine2">{{ recipientAddressLine2 }}</p>
                      <p class="mb-1" v-if="recipientAddressLine3">{{ recipientAddressLine3 }}</p>
                      <p class="mb-0">
                        {{ recipientAddress?.postalCode || 'N/A' }},
                        {{ getCountryName(recipientAddress?.countryCode) }}
                      </p>
                      <p class="mb-0" v-if="recipientAddress?.phone">
                        <i class="fas fa-phone me-1"></i>
                        {{ recipientAddress?.phoneCountryCode || '' }} {{ recipientAddress?.phone }}
                      </p>
                      <p class="mb-0">
                        <i class="fas fa-envelope me-1"></i>
                        {{ recipientAddress?.email || 'N/A' }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Progress Bar -->
                <div class="route-progress mt-4">
                  <div class="progress-labels d-flex justify-content-between">
                    <span class="progress-label">{{ getCountryName(senderAddress?.countryCode) }}</span>
                    <span class="progress-percent">{{ Math.round(getProgressWidth(transaction.status)) }}%</span>
                    <span class="progress-label">{{ getCountryName(recipientAddress?.countryCode) }}</span>
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
                      <span class="detail-value">${{ transaction.totalValue }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Payment Status:</span>
                      <span class="detail-value badge" :class="transaction.hasBeenPaid ? 'bg-success' : 'bg-warning text-dark'">
                        {{ transaction.hasBeenPaid ? 'Paid' : 'Pending' }}
                      </span>
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
                        <th>Currency</th>
                        <th>HS Code</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(item, index) in transaction.mailItems" :key="index">
                        <td>{{ item.itemDescription }}</td>
                        <td>{{ item.itemQuantity }}</td>
                        <td>{{ item.itemWeight }}</td>
                        <td>${{ item.declaredValue }}</td>
                        <td>{{ item.declaredCurrency }}</td>
                        <td>{{ item.hsCode || 'N/A' }}</td>
                      </tr>
                    </tbody>
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
                  <div class="timeline-item" :class="{ active: transaction.status === 'pending' }">
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
                    </div>
                  </div>
                  <div class="timeline-item" :class="{ active: transaction.status === 'delivered' }">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                      <h6>Delivered</h6>
                      <p class="text-muted mb-0" v-if="transaction.status === 'delivered'">
                        {{ formatDate(transaction.expectedDelivery) }}
                      </p>
                      <p class="text-muted mb-0" v-else>
                        Expected: {{ formatDate(transaction.expectedDelivery) }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Customer Information Card -->
            <div class="detail-card card mb-4">
              <div class="card-header bg-light-pink">
                <h4 class="mb-0">
                  <i class="fas fa-user me-2"></i>
                  Customer Information
                </h4>
              </div>
              <div class="card-body">
                <div class="customer-info">
                  <p class="mb-2">
                    <strong>Email:</strong><br>
                    {{ user.email }}
                  </p>
                  <p class="mb-0">
                    <strong>Customer ID:</strong><br>
                    {{ transaction.customerEmail }}
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

    // Reactive data
    const isAuthenticated = ref(false)
    const user = ref({ email: '' })
    const transaction = ref(null)
    const senderAddress = ref(null)
    const recipientAddress = ref(null)
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

    // Computed properties for address lines
    const senderAddressLine1 = computed(() => senderAddress.value?.address?.addressLines?.[0] || 'N/A')
    const senderAddressLine2 = computed(() => senderAddress.value?.address?.addressLines?.[1] || '')
    const senderAddressLine3 = computed(() => senderAddress.value?.address?.addressLines?.[2] || '')

    const recipientAddressLine1 = computed(() => recipientAddress.value?.address?.addressLines?.[0] || 'N/A')
    const recipientAddressLine2 = computed(() => recipientAddress.value?.address?.addressLines?.[1] || '')
    const recipientAddressLine3 = computed(() => recipientAddress.value?.address?.addressLines?.[2] || '')

    // Methods
    const fetchTransactionDetails = async () => {
      if (!isAuthenticated.value) return

      loading.value = true
      error.value = null

      try {
        const transactionId = route.params.id

        // First try to get from session storage (from click)
        const storedTransaction = sessionStorage.getItem('selectedTransaction')
        if (storedTransaction) {
          transaction.value = JSON.parse(storedTransaction)
          console.log('Loaded transaction from session storage:', transaction.value)
        } else {
          // If not in session storage, fetch from API
          console.log('Fetching transaction from API for ID:', transactionId)
          const response = await fetch('/api/mail.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              method: 'getMailById',
              mailId: parseInt(transactionId)
            })
          })

          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`)
          }

          const data = await response.json()
          console.log('API Response for transaction:', data)

          if (data.success && data.mail) {
            transaction.value = data.mail
          } else {
            throw new Error(data.error || 'Transaction not found')
          }
        }

        // Fetch address details if available
        if (transaction.value) {
          await fetchAddressDetails()
        }

      } catch (err) {
        console.error('Error loading transaction details:', err)
        error.value = `Failed to load transaction details: ${err.message}`
      } finally {
        loading.value = false
      }
    }

    const fetchAddressDetails = async () => {
      try {
        // Fetch sender address
        if (transaction.value.senderAddressId) {
          const senderResponse = await fetch('/api/addresses.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              method: 'getAddressById',
              addressId: transaction.value.senderAddressId
            })
          })

          if (senderResponse.ok) {
            const senderData = await senderResponse.json()
            if (senderData.success) {
              senderAddress.value = senderData.address
            }
          }
        }

        // Fetch recipient address
        if (transaction.value.recipientAddressId) {
          const recipientResponse = await fetch('/api/addresses.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              method: 'getAddressById',
              addressId: transaction.value.recipientAddressId
            })
          })

          if (recipientResponse.ok) {
            const recipientData = await recipientResponse.json()
            if (recipientData.success) {
              recipientAddress.value = recipientData.address
            }
          }
        }
      } catch (err) {
        console.error('Error fetching address details:', err)
      }
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
        'IN': 'India'
      }
      return countryMap[countryCode] || countryCode || 'Unknown'
    }

    const goBack = () => {
      router.back()
    }

    const redirectToLogin = () => {
      router.push('/login')
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
      senderAddress,
      recipientAddress,
      senderAddressLine1,
      senderAddressLine2,
      senderAddressLine3,
      recipientAddressLine1,
      recipientAddressLine2,
      recipientAddressLine3,
      loading,
      error,
      fetchTransactionDetails,
      getStatusBadgeClass,
      formatStatus,
      formatDate,
      getProgressWidth,
      getCountryName,
      goBack,
      redirectToLogin
    }
  }
}
</script>

<style scoped>
.history-detail-page {
  min-height: 100vh;
  background: linear-gradient(135deg, var(--light-pink) 0%, var(--pink-grey) 100%);
}



.transaction-detail-page {
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
