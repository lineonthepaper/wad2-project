<template>
  <div class="history-page">
    <!-- Authentication Check -->
    <div v-if="!isAuthenticated" class="login-required">
      <div class="login-message">
        <div class="message-icon">
          <i class="fas fa-lock"></i>
        </div>
        <h2>Authentication Required</h2>
        <p>Please log in to view your transaction history</p>
        <div class="action-buttons">
          <button @click="redirectToLogin" class="btn btn-primary">
            <i class="fas fa-sign-in-alt"></i>
            Go to Login
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content for Authenticated Users -->
    <div v-else class="dashboard-wrapper">
      <hr />
    <div class="row bg-light-pink justify-content-center airplane-header">
      <div class="col-lg-4 col-md-6 col-sm-8 py-2 text-center">
        <h1 class="jua text-hot-pink">Transaction History</h1>
      </div>
    </div>
    <hr />

      <div class="container mt-4">
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-5">
          <div class="loading-spinner-large"></div>
          <p class="mt-3 text-muted">Loading your transactions...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="text-center py-5">
          <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
          <h4 class="text-danger">Failed to load transactions</h4>
          <p class="text-muted">{{ error }}</p>
          <button class="btn btn-primary" @click="fetchTransactions">
            <i class="fas fa-redo"></i> Try Again
          </button>
        </div>

        <!-- Main Content -->
        <div v-else>
          <!-- Search and Filter Section -->
          <div class="row mb-4">
            <div class="col-md-6">
              <div class="input-group">
                <span class="input-group-text">
                  <i class="fas fa-search"></i>
                </span>
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
                  <i class="fas fa-times"></i> Clear
                </button>
              </div>
            </div>
          </div>

          <!-- Active Filters Display -->
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

          <!-- Stats Overview -->
          <section class="stats-overview mb-4">
            <div class="stats-grid">
              <div class="stat-card" :class="`stat-${stat.key}`" v-for="stat in enhancedStats" :key="stat.key">
                <div class="stat-content">
                  <div class="stat-icon">
                    <i :class="stat.icon"></i>
                  </div>
                  <div class="stat-data">
                    <h3 class="stat-title">{{ stat.title }}</h3>
                    <p class="stat-number">{{ stat.value }}</p>
                    <div class="stat-trend" :class="stat.trend">
                      <i :class="stat.trendIcon"></i>
                      <span>{{ stat.trendValue }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <!-- Transactions List -->
          <div class="row">
            <div class="col-12">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Found {{ filteredTransactions.length }} transactions</h5>
                <div class="text-muted small">
                  Showing {{ Math.min(filteredTransactions.length, itemsPerPage) }} of {{ filteredTransactions.length }}
                </div>
              </div>

              <!-- Transactions Grid -->
              <div class="transaction-grid">
                <div
                  class="transaction-card card mb-3 shadow-sm"
                  v-for="transaction in paginatedTransactions"
                  :key="transaction.mailId"
                  @click="viewTransactionDetails(transaction)"
                  style="cursor: pointer; transition: transform 0.2s;"
                >
                  <div class="card-body">
                    <div class="row">
                      <!-- Tracking and Basic Info -->
                      <div class="col-md-4">
                        <div class="d-flex align-items-center mb-2">
                          <h6 class="mb-0 me-2">TRK-{{ transaction.trackingNumber.toString().padStart(6, '0') }}</h6>
                          <span class="badge" :class="getStatusBadgeClass(transaction.status)">
                            {{ formatStatus(transaction.status) }}
                          </span>
                        </div>
                        <p class="text-muted small mb-2">
                          <i class="fas fa-calendar me-1"></i>
                          Created: {{ formatDate(transaction.createdDate) }}
                        </p>
                        <p class="text-muted small mb-0">
                          <i class="fas fa-shipping-fast me-1"></i>
                          {{ transaction.service.name }}
                        </p>
                      </div>

                      <!-- Route Information -->
                      <div class="col-md-4">
                        <div class="route-info">
                          <div class="d-flex align-items-center mb-1">
                            <i class="fas fa-map-marker-alt text-danger me-2"></i>
                            <small class="text-muted">From: {{ transaction.senderAddress?.countryCode || 'SG' }}</small>
                          </div>
                          <div class="d-flex align-items-center">
                            <i class="fas fa-flag-checkered text-success me-2"></i>
                            <small class="text-muted">To: {{ transaction.recipientAddress?.countryCode || 'Unknown' }}</small>
                          </div>
                          <div class="progress mt-2" style="height: 4px;">
                            <div
                              class="progress-bar"
                              :class="getProgressBarClass(transaction.status)"
                              :style="{ width: getProgressWidth(transaction.status) }"
                            ></div>
                          </div>
                          <small class="text-muted d-block mt-1 text-center">
                            {{ getProgressText(transaction.status) }}
                          </small>
                        </div>
                      </div>

                      <!-- Shipment Details -->
                      <div class="col-md-4">
                        <div class="shipment-details">
                          <div class="row text-center">
                            <div class="col-4">
                              <small class="text-muted d-block">Weight</small>
                              <strong>{{ transaction.totalWeight }}kg</strong>
                            </div>
                            <div class="col-4">
                              <small class="text-muted d-block">Value</small>
                              <strong>${{ transaction.totalValue }}</strong>
                            </div>
                            <div class="col-4">
                              <small class="text-muted d-block">Payment</small>
                              <span class="badge" :class="transaction.hasBeenPaid ? 'bg-success' : 'bg-warning text-dark'">
                                {{ transaction.hasBeenPaid ? 'Paid' : 'Pending' }}
                              </span>
                            </div>
                          </div>
                          <div class="mt-2 text-center">
                            <small class="text-muted">
                              Expected: {{ formatDate(transaction.expectedDelivery) }}
                            </small>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Items Preview -->
                    <div class="mt-3 pt-3 border-top">
                      <div class="d-flex align-items-center">
                        <div>
                          <small class="text-muted">Items:</small>
                          <span class="ms-2">
                            {{ transaction.mailItems.map(item => item.itemDescription).join(', ') }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- No Results -->
              <div v-if="filteredTransactions.length === 0 && transactions.length > 0" class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No transactions match your search</h4>
                <p class="text-muted">Try adjusting your search terms or filters</p>
                <button class="btn btn-primary" @click="clearFilters">
                  Clear All Filters
                </button>
              </div>

              <!-- No Transactions at All -->
              <div v-if="transactions.length === 0 && !loading" class="text-center py-5">
                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No transactions found</h4>
                <p class="text-muted">You haven't created any shipments yet.</p>
                <button class="btn btn-primary" @click="redirectToCreateShipment">
                  Create Your First Shipment
                </button>
              </div>

              <!-- Pagination -->
              <div v-if="filteredTransactions.length > itemsPerPage" class="d-flex justify-content-center mt-4">
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

    // Reactive data
    const isAuthenticated = ref(false)
    const user = ref({ email: '' })
    const searchQuery = ref("")
    const selectedStatus = ref("")
    const selectedService = ref("")
    const transactions = ref([])
    const loading = ref(false)
    const error = ref(null)
    const currentPage = ref(1)
    const itemsPerPage = ref(10)

    // Check authentication
    const checkAuthentication = () => {
      const userData = sessionStorage.getItem('currentUser')
      if (userData) {
        try {
          const userObj = JSON.parse(userData)
          user.value.email = userObj.email || userObj.display_name || 'User'
          isAuthenticated.value = true
          console.log('User authenticated:', user.value.email)
        } catch (error) {
          console.error('Error parsing user data:', error)
          isAuthenticated.value = false
        }
      } else {
        isAuthenticated.value = false
      }
    }

    // Search handler
    const handleSearch = () => {
      currentPage.value = 1 // Reset to first page when searching
    }

    // View transaction details
    const viewTransactionDetails = (transaction) => {
      console.log('Viewing transaction details:', transaction.mailId)

      // Store the transaction data for the detail page
      sessionStorage.setItem('selectedTransaction', JSON.stringify(transaction))

      // Navigate to history detail page
      router.push(`/history/${transaction.mailId}`)
    }

    // Computed properties
    const hasActiveFilters = computed(() => {
      return selectedStatus.value || selectedService.value || searchQuery.value
    })

    const filteredTransactions = computed(() => {
      if (!transactions.value.length) return []

      return transactions.value.filter(transaction => {
        const query = searchQuery.value.toLowerCase().trim()

        // If no search query, only apply status and service filters
        if (!query) {
          const matchesStatus = !selectedStatus.value || transaction.status === selectedStatus.value
          const matchesService = !selectedService.value ||
            transaction.service?.name?.toLowerCase().includes(selectedService.value.toLowerCase())
          return matchesStatus && matchesService
        }

        // Search across multiple fields
        const matchesSearch =
          // Search by tracking number
          (transaction.trackingNumber?.toString().toLowerCase().includes(query)) ||
          // Search by service name
          (transaction.service?.name?.toLowerCase().includes(query)) ||
          // Search by destination country code
          (transaction.recipientAddress?.countryCode?.toLowerCase().includes(query)) ||
          // Search by destination country name (if available)
          (transaction.recipientAddress?.name?.toLowerCase().includes(query)) ||
          // Search by item descriptions
          (transaction.mailItems?.some(item =>
            item.itemDescription?.toLowerCase().includes(query)
          )) ||
          // Search by status
          (formatStatus(transaction.status).toLowerCase().includes(query)) ||
          // Search by sender country
          (transaction.senderAddress?.countryCode?.toLowerCase().includes(query))

        const matchesStatus = !selectedStatus.value || transaction.status === selectedStatus.value
        const matchesService = !selectedService.value ||
          transaction.service?.name?.toLowerCase().includes(selectedService.value.toLowerCase())

        return matchesSearch && matchesStatus && matchesService
      })
    })

    const totalPages = computed(() => {
      return Math.ceil(filteredTransactions.value.length / itemsPerPage.value)
    })

    const paginatedTransactions = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage.value
      const end = start + itemsPerPage.value
      return filteredTransactions.value.slice(start, end)
    })

    const enhancedStats = computed(() => {
      const inProgress = transactions.value.filter(t => t.status === 'in_transit').length
      const delivered = transactions.value.filter(t => t.status === 'delivered').length
      const pending = transactions.value.filter(t => t.status === 'pending').length
      const total = transactions.value.length

      return [
        {
          key: 'in-progress',
          title: 'In Transit',
          value: inProgress,
          icon: 'fas fa-shipping-fast',
          trend: 'up',
          trendIcon: 'fas fa-arrow-up',
          trendValue: '+2 today'
        },
        {
          key: 'delivered',
          title: 'Delivered',
          value: delivered,
          icon: 'fas fa-check-circle',
          trend: 'up',
          trendIcon: 'fas fa-arrow-up',
          trendValue: '+12%'
        },
        {
          key: 'pending',
          title: 'Pending',
          value: pending,
          icon: 'fas fa-clock',
          trend: 'down',
          trendIcon: 'fas fa-arrow-down',
          trendValue: '-1 today'
        },
        {
          key: 'total',
          title: 'Total Shipments',
          value: total,
          icon: 'fas fa-boxes',
          trend: 'up',
          trendIcon: 'fas fa-arrow-up',
          trendValue: '+8%'
        }
      ]
    })

    // Methods
    const fetchTransactions = async () => {
      if (!isAuthenticated.value) return

      loading.value = true
      error.value = null

      try {
        console.log('Fetching transactions for:', user.value.email)

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
        console.log('API Response:', data)

        if (data.success) {
          transactions.value = data.shipments || []
          console.log('Successfully loaded transactions:', transactions.value.length)

          if (transactions.value.length > 0) {
            console.log('First transaction sample:', transactions.value[0])
          } else {
            console.log('No transactions found for user')
          }
        } else {
          throw new Error(data.error || 'Failed to load transactions from server')
        }
      } catch (err) {
        console.error('Error loading transactions:', err)
        error.value = `Failed to load transactions: ${err.message}`
        transactions.value = [] // Clear transactions on error
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
      router.push('/create-shipment')
    }

    // Lifecycle
    onMounted(() => {
      checkAuthentication()
      if (isAuthenticated.value) {
        fetchTransactions()
      }
    })

    return {
      isAuthenticated,
      user,
      searchQuery,
      selectedStatus,
      selectedService,
      transactions,
      loading,
      error,
      currentPage,
      itemsPerPage,
      hasActiveFilters,
      filteredTransactions,
      totalPages,
      paginatedTransactions,
      enhancedStats,
      fetchTransactions,
      clearFilters,
      handleSearch,
      viewTransactionDetails,
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
:root {
  --hot-pink: #ff4275;
  --dark-pink: #ff759e;
  --pink: #ff9096;
  --dark-slate-blue: #455a64;
  --slate-blue: #8796b3;
  --light-pink: #ffe8ee;
  --pink-grey: #f1d9df;
  --grey-outline: #6c757d;
  --light-grey: #f8f9fa;
}

.transaction-history-page {
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
.bg-light-pink {
  background-color: var(--light-pink) !important;
}

.text-hot-pink {
  color: var(--hot-pink) !important;
}

.jua {
  font-family: 'Jua', sans-serif;
}

.airplane-header {
  background: linear-gradient(135deg, var(--hot-pink) 0%, var(--dark-pink) 100%);
  color: white;
  border-radius: 0 0 20px 20px;
  box-shadow: 0 4px 20px rgba(255, 66, 117, 0.3);
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

/* Stats Overview */
.stats-overview {
  margin-bottom: 2rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  border-left: 4px solid;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}

.stat-card.stat-in-progress {
  border-left-color: var(--hot-pink);
}

.stat-card.stat-delivered {
  border-left-color: var(--pink);
}

.stat-card.stat-pending {
  border-left-color: var(--dark-pink);
}

.stat-card.stat-total {
  border-left-color: var(--slate-blue);
}

.stat-content {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

.stat-in-progress .stat-icon {
  background: linear-gradient(135deg, var(--hot-pink), var(--dark-pink));
}

.stat-delivered .stat-icon {
  background: linear-gradient(135deg, var(--pink), var(--dark-pink));
}

.stat-pending .stat-icon {
  background: linear-gradient(135deg, var(--dark-pink), var(--hot-pink));
}

.stat-total .stat-icon {
  background: linear-gradient(135deg, var(--slate-blue), var(--dark-slate-blue));
}

.stat-data {
  flex: 1;
}

.stat-title {
  font-size: 0.9rem;
  color: var(--dark-slate-blue);
  margin: 0 0 0.3rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-number {
  font-size: 2rem;
  font-weight: 700;
  margin: 0 0 0.3rem;
  color: var(--dark-slate-blue);
}

.stat-trend {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.8rem;
  font-weight: 600;
}

.stat-trend.up {
  color: var(--hot-pink);
}

.stat-trend.down {
  color: var(--dark-pink);
}

/* Transaction Cards */
.transaction-card:hover {
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

/* Form Controls - Updated Styles */
.input-group-text {
  background-color: white;
  border: 1px solid var(--grey-outline);
  color: var(--grey-outline);
}

.search-input {
  border: 1px solid var(--grey-outline);
  background-color: white;
}

.search-input:focus {
  border-color: var(--grey-outline);
  box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25);
  background-color: white;
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

/* Pagination */
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

/* Responsive */
@media (max-width: 768px) {
  .route-info,
  .shipment-details {
    border-left: none;
    border-top: 2px solid var(--pink-grey);
    padding-left: 0;
    padding-top: 15px;
    margin-top: 15px;
  }

  .transaction-card .card-body .row > div {
    margin-bottom: 15px;
  }

  .transaction-card .card-body .row > div:last-child {
    margin-bottom: 0;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .action-buttons {
    flex-direction: column;
  }

  .btn {
    justify-content: center;
  }
}
</style>
