<template>
  <div>
    <!-- Show dashboard only when authenticated -->
    <div class="dashboard-wrapper" v-if="isAuthenticated">
      <!-- Loading Overlay -->
      <div v-if="loading" class="loading-overlay">
        <div class="loading-spinner-large"></div>
        <p>Loading your shipments...</p>
      </div>

      <!-- Error Banner -->
      <div v-if="errorMessage" class="error-banner">
        <i class="fas fa-exclamation-circle"></i>
        {{ errorMessage }}
        <button @click="fetchUserShipments" class="btn-retry">Retry</button>
      </div>

      <!-- Header -->
      <div class="dashboard-header">
        <div class="header-background">
          <div class="header-content">
            <div class="header-text">
              <h1 class="dashboard-title">
                <span class="title-main">Parcel Tracking</span>
                <span class="title-sub">Dashboard</span>
              </h1>
              <p class="welcome-message">Welcome back, <strong>{{user.email}}</strong>! Track your shipments in real-time.</p>
            </div>
            <div class="header-stats">
              <div class="header-stat">
                <span class="stat-value">{{ totalShipments }}</span>
                <span class="stat-label">Total Shipments</span>
              </div>
              <div class="header-stat">
                <span class="stat-value">{{ stats.inProgress }}</span>
                <span class="stat-label">In Transit</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="main-content" v-if="!loading">
        <!-- Stats Overview -->
        <section class="stats-overview">
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
              <div class="stat-chart">
                <div class="mini-chart">
                  <div class="chart-bar" v-for="(point, index) in stat.chartData" :key="index"
                       :style="{ height: point + '%' }"></div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Globe and Shipment Details -->
        <section class="tracking-section">
          <!-- Left Column - Globe -->
          <div class="section-column globe-column">
            <div class="section-card">
              <div class="card-header">
                <h3><i class="fas fa-globe-americas"></i> Live Tracking Map</h3>
                <div class="card-actions">
                  <button class="btn-icon" @click="forceReinit" title="Refresh Globe">
                    <i class="fas fa-sync-alt"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <!-- Fixed Globe Container -->
                <div ref="globeContainer" class="globe-container" style="width: 100%; height: 400px;">
                  <div v-if="globeError" class="globe-error">
                    <div class="error-icon">
                      <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <p>Failed to load globe visualization</p>
                    <button class="btn-retry" @click="forceReinit">Retry</button>
                  </div>
                  <div v-else-if="!globeInitialized" class="globe-loading">
                    <div class="loading-spinner"></div>
                    <p>Initializing 3D Globe...</p>
                  </div>
                </div>

                <!-- Selected Shipment Brief Info -->
                <div v-if="selectedParcel" class="selected-shipment-brief">
                  <div class="brief-header">
                    <h4>Selected Shipment</h4>
                    <span class="status-badge" :class="`status-${selectedParcel.status.toLowerCase().replace(' ', '-')}`">
                      {{ selectedParcel.status }}
                    </span>
                  </div>
                  <div class="brief-details">
                    <div class="brief-item">
                      <strong>Tracking ID:</strong> {{ selectedParcel.trackingId }}
                    </div>
                    <div class="brief-item">
                      <strong>Route:</strong> {{ getLocationName(selectedParcel.location) }} → {{ getLocationName(selectedParcel.destination) }}
                    </div>
                    <div class="brief-item">
                      <strong>Progress:</strong> {{ Math.round(calculateProgress(selectedParcel)) }}%
                    </div>
                  </div>
                </div>
                <div v-else class="no-selection">
                  <i class="fas fa-mouse-pointer"></i>
                  <p>Select a shipment from the list to view details</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column - Recent Shipments -->
          <div class="section-column parcels-column">
            <div class="section-card">
              <div class="card-header">
                <h3><i class="fas fa-boxes"></i> Recent Shipments</h3>
                <div class="card-actions">
                  <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search shipments..." v-model="searchQuery">
                  </div>
                  <button class="btn-icon" title="Filter">
                    <i class="fas fa-filter"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="parcels-list">
                  <div
                    v-for="parcel in filteredParcels"
                    :key="parcel.id"
                    class="parcel-item"
                    :class="{ 'active': selectedParcel && selectedParcel.id === parcel.id }"
                    @click="showShipmentDetails(parcel)"
                  >
                    <div class="parcel-icon">
                      <i class="fas fa-box"></i>
                    </div>
                    <div class="parcel-details">
                      <div class="parcel-header">
                        <h4 class="tracking-id">{{ parcel.trackingId }}</h4>
                        <span class="status-badge" :class="`status-${parcel.status.toLowerCase().replace(' ', '-')}`">
                          {{ parcel.status }}
                        </span>
                      </div>
                      <div class="parcel-info">
                        <div class="info-item">
                          <i class="fas fa-user"></i>
                          <span>{{ parcel.customer }}</span>
                        </div>
                        <div class="info-item">
                          <i class="fas fa-map-marker-alt"></i>
                          <span>{{ getLocationName(parcel.currentLocation || parcel.location) }}</span>
                        </div>
                        <div class="info-item">
                          <i class="fas fa-calendar-alt"></i>
                          <span>{{ formatDate(parcel.expectedDelivery) }}</span>
                        </div>
                      </div>
                      <div v-if="parcel.status === 'In Progress'" class="parcel-progress">
                        <div class="progress-mini">
                          <div class="progress-fill-mini" :style="{ width: calculateProgress(parcel) + '%' }"></div>
                        </div>
                        <span class="progress-text">{{ Math.round(calculateProgress(parcel)) }}%</span>
                      </div>
                    </div>
                  </div>

                  <!-- Empty state -->
                  <div v-if="filteredParcels.length === 0" class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <p>No shipments found</p>
                    <p class="empty-subtext" v-if="searchQuery">Try adjusting your search terms</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Shipment Details Section (Shows when shipment is selected) -->
        <section v-if="selectedParcel && shipmentDetails" class="shipment-details-section">
          <div class="section-card">
            <div class="card-header">
              <h3><i class="fas fa-info-circle"></i> Shipment Details: {{ shipmentDetails.trackingId }}</h3>
              <div class="card-actions">
                <button class="btn-icon" @click="clearSelection" title="Close Details">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
            <div class="card-body">
              <!-- Shipment Details Content -->
              <div class="shipment-details-content">
                <!-- Progress Bar -->
                <div class="route-progress">
                  <div class="progress-labels">
                    <span class="progress-label">{{ getLocationName(shipmentDetails.location) }}</span>
                    <span class="progress-percent">{{ Math.round(calculateProgress(shipmentDetails)) }}%</span>
                    <span class="progress-label">{{ getLocationName(shipmentDetails.destination) }}</span>
                  </div>
                  <div class="progress-track">
                    <div class="progress-bar">
                      <div class="progress-fill" :style="{ width: calculateProgress(shipmentDetails) + '%' }"></div>
                      <div class="progress-marker" :style="{ left: calculateProgress(shipmentDetails) + '%' }">
                        <i class="fas fa-shipping-fast"></i>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Detailed Information Grid -->
                <div class="shipment-details-grid">
                  <!-- Sender & Recipient Information -->
                  <div class="detail-section">
                    <h5><i class="fas fa-user-friends"></i> Parties Involved</h5>
                    <div class="detail-group">
                      <div class="detail-item">
                        <span class="detail-label">Sender:</span>
                        <span class="detail-value">{{ shipmentDetails.senderAddress?.name || 'N/A' }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Recipient:</span>
                        <span class="detail-value">{{ shipmentDetails.recipientAddress?.name || 'N/A' }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Customer Email:</span>
                        <span class="detail-value">{{ shipmentDetails.customerEmail || 'N/A' }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- Shipment Information -->
                  <div class="detail-section">
                    <h5><i class="fas fa-box"></i> Shipment Information</h5>
                    <div class="detail-group">
                      <div class="detail-item">
                        <span class="detail-label">Service:</span>
                        <span class="detail-value">{{ shipmentDetails.service?.name || 'Standard' }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Weight:</span>
                        <span class="detail-value">{{ shipmentDetails.totalWeight }} kg</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Value:</span>
                        <span class="detail-value">${{ shipmentDetails.totalValue }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Dimensions:</span>
                        <span class="detail-value">
                          {{ shipmentDetails.parcelLength }}cm × {{ shipmentDetails.parcelWidth }}cm × {{ shipmentDetails.parcelHeight }}cm
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- Timeline Information -->
                  <div class="detail-section">
                    <h5><i class="fas fa-calendar-alt"></i> Timeline</h5>
                    <div class="detail-group">
                      <div class="detail-item">
                        <span class="detail-label">Created:</span>
                        <span class="detail-value">{{ formatDate(shipmentDetails.createdDate) }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Expected Delivery:</span>
                        <span class="detail-value">{{ formatDate(shipmentDetails.expectedDelivery) }}</span>
                      </div>
                      <div class="detail-item">
                        <span class="detail-label">Payment Status:</span>
                        <span class="detail-value" :class="{ 'paid': shipmentDetails.hasBeenPaid, 'unpaid': !shipmentDetails.hasBeenPaid }">
                          {{ shipmentDetails.hasBeenPaid ? 'Paid' : 'Pending' }}
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- Package Contents -->
                  <div class="detail-section full-width" v-if="shipmentDetails.mailItems && shipmentDetails.mailItems.length > 0">
                    <h5><i class="fas fa-list"></i> Package Contents</h5>
                    <div class="items-table">
                      <div class="table-header">
                        <span>Description</span>
                        <span>Quantity</span>
                        <span>Weight</span>
                        <span>Value</span>
                      </div>
                      <div class="table-row" v-for="item in shipmentDetails.mailItems" :key="item.itemId">
                        <span>{{ item.itemDescription }}</span>
                        <span>{{ item.itemQuantity }}</span>
                        <span>{{ item.itemWeight }} kg</span>
                        <span>${{ item.declaredValue }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Notifications Section Only -->
        <section class="notifications-section">
          <div class="section-column notifications-column">
            <div class="section-card">
              <div class="card-header">
                <h3><i class="fas fa-bell"></i> Recent Activity</h3>
              </div>
              <div class="card-body">
                <div class="notifications-list">
                  <div class="notification-item" v-for="notification in notifications" :key="notification.id">
                    <div class="notification-icon" :class="notification.type">
                      <i :class="notification.icon"></i>
                    </div>
                    <div class="notification-content">
                      <p class="notification-text">{{ notification.message }}</p>
                      <span class="notification-time">{{ notification.time }}</span>
                    </div>
                  </div>

                  <!-- Empty notifications -->
                  <div v-if="notifications.length === 0" class="empty-notifications">
                    <i class="fas fa-bell-slash"></i>
                    <p>No recent activity</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>

    <!-- Show login prompt when not authenticated -->
    <div v-else class="login-required">
      <div class="login-message">
        <div class="message-icon">
          <i class="fas fa-lock"></i>
        </div>
        <h2>Authentication Required</h2>
        <p>Please log in to access your parcel tracking dashboard</p>
        <div class="action-buttons">
          <button @click="redirectToLogin" class="btn btn-primary">
            <i class="fas fa-sign-in-alt"></i>
            Go to Login
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Globe from 'globe.gl';
import countryData from '/json/countryData.json';
import axios from 'axios';

export default {
  name: "EnhancedParcelDashboard",
  data() {
    return {
      isAuthenticated: false,
      user: {
        email: ''
      },
      searchQuery: '',
      selectedParcel: null,
      shipmentDetails: null,
      globeInitialized: false,
      globeError: false,
      errorMessage: "",
      loading: false,
      stats: {
        inProgress: 0,
        delivered: 0,
        pending: 0,
      },
      parcels: [],
      notifications: [],
      globe: null,
      arcsData: [],
      pointsData: [],
      refreshInterval: null,
      usingFallbackData: false
    };
  },
  computed: {
    totalShipments() {
      return this.stats.inProgress + this.stats.delivered + this.stats.pending;
    },
    filteredParcels() {
      if (!this.searchQuery) return this.parcels;
      const query = this.searchQuery.toLowerCase();
      return this.parcels.filter(parcel =>
        parcel.trackingId.toLowerCase().includes(query) ||
        (parcel.customer && parcel.customer.toLowerCase().includes(query)) ||
        this.getLocationName(parcel.currentLocation || parcel.location).toLowerCase().includes(query)
      );
    },
    enhancedStats() {
      return [
        {
          key: 'in-progress',
          title: 'In Transit',
          value: this.stats.inProgress,
          icon: 'fas fa-shipping-fast',
          trend: 'up',
          trendIcon: 'fas fa-arrow-up',
          trendValue: '+2 today',
          chartData: [65, 70, 75, 80, 75, 70, 68]
        },
        {
          key: 'delivered',
          title: 'Delivered',
          value: this.stats.delivered,
          icon: 'fas fa-check-circle',
          trend: 'up',
          trendIcon: 'fas fa-arrow-up',
          trendValue: '+12%',
          chartData: [40, 45, 50, 55, 60, 65, 70]
        },
        {
          key: 'pending',
          title: 'Pending',
          value: this.stats.pending,
          icon: 'fas fa-clock',
          trend: 'down',
          trendIcon: 'fas fa-arrow-down',
          trendValue: '-1 today',
          chartData: [80, 75, 70, 65, 60, 55, 50]
        },
        {
          key: 'total',
          title: 'Total Shipments',
          value: this.totalShipments,
          icon: 'fas fa-boxes',
          trend: 'up',
          trendIcon: 'fas fa-arrow-up',
          trendValue: '+8%',
          chartData: [50, 55, 60, 65, 70, 75, 80]
        }
      ];
    }
  },
  mounted() {
    this.checkAuthentication();
    if (this.isAuthenticated) {
      this.initializeDashboard();
      // Refresh every 30 seconds for real-time updates
      this.refreshInterval = setInterval(() => {
        if (this.isAuthenticated) {
          this.fetchUserShipments();
        }
      }, 30000);
    }

    window.addEventListener('loginStatusChanged', this.handleLoginStatusChange);
  },
  beforeUnmount() {
    if (this.refreshInterval) {
      clearInterval(this.refreshInterval);
    }
    if (this.globe) {
      // Clean up globe instance
      this.globe = null;
    }
    window.removeEventListener('loginStatusChanged', this.handleLoginStatusChange);
  },
  methods: {
    checkAuthentication() {
      const userData = sessionStorage.getItem('currentUser');
      if (userData) {
        try {
          const user = JSON.parse(userData);
          this.user.email = user.email || user.display_name || 'User';
          this.isAuthenticated = true;
          console.log('User authenticated:', this.user.email);
        } catch (error) {
          console.error('Error parsing user data:', error);
          this.isAuthenticated = false;
        }
      } else {
        this.isAuthenticated = false;
        console.log('No user data found in sessionStorage');
      }
    },

    handleLoginStatusChange() {
      console.log('Login status changed, rechecking authentication...');
      this.checkAuthentication();
      if (this.isAuthenticated) {
        this.initializeDashboard();
      } else {
        // Clear data when logging out
        this.parcels = [];
        this.notifications = [];
        this.stats = { inProgress: 0, delivered: 0, pending: 0 };
        this.usingFallbackData = false;
        this.selectedParcel = null;
        this.shipmentDetails = null;
      }
    },

    async initializeDashboard() {
      console.log('Initializing dashboard for authenticated user...');

      // Debug coordinates first
      this.debugCountryCoordinates();

      await this.fetchUserShipments();
      // Wait for DOM to be fully updated before initializing globe
      this.$nextTick(() => {
        setTimeout(() => {
          this.initGlobe();
        }, 500);
      });
    },

    debugCountryCoordinates() {
      const australia = countryData.find(c => c.code2 === 'AU');
      console.log('Australia coordinates from JSON:', australia);

      const singapore = countryData.find(c => c.code2 === 'SG');
      console.log('Singapore coordinates from JSON:', singapore);

      // Test the getCountryCoordinates method
      console.log('getCountryCoordinates("AU"):', this.getCountryCoordinates('AU'));
      console.log('getCountryCoordinates("SG"):', this.getCountryCoordinates('SG'));
    },

    async fetchUserShipments() {
      this.loading = true;
      this.errorMessage = "";
      this.usingFallbackData = false;

      try {
        console.log('Fetching shipments for:', this.user.email);

        const response = await axios.post('/api/dashboard.php', {
          method: 'getAllMailByCustomerEmail',
          email: this.user.email
        });

        console.log('API Response:', response.data);

        if (response.data.success) {
          // Check if we're using fallback data
          if (response.data.note) {
            this.usingFallbackData = true;
            console.log('Using fallback data:', response.data.note);
          }

          this.parcels = this.transformShipmentData(response.data.shipments);
          this.updateStats();
          this.generateNotifications();
          console.log('Successfully loaded', this.parcels.length, 'shipments');

          // Update globe data if globe is already initialized
          if (this.globeInitialized) {
            this.updateGlobeData();
          }

        } else {
          console.error('Failed to fetch shipments:', response.data.error);
          // Fallback to example data if API fails
          this.useFallbackData();
        }
      } catch (error) {
        console.error('Error fetching shipments:', error);
        // Fallback to example data on network error
        this.useFallbackData();
      } finally {
        this.loading = false;
      }
    },

    showShipmentDetails(parcel) {
      console.log('📦 Showing detailed shipment info for:', parcel.trackingId);

      // Set selected parcel
      this.selectedParcel = parcel;

      // Use the raw data from the existing API response for detailed information
      this.shipmentDetails = {
        ...parcel.rawData,
        trackingId: parcel.trackingId,
        location: parcel.location,
        destination: parcel.destination,
        currentLocation: parcel.currentLocation,
        status: parcel.status,
        progress: parcel.progress
      };

      console.log('✅ Shipment details loaded from existing data');

      // Update globe to show this shipment
      if (this.globe && this.globeInitialized) {
        this.highlightShipmentOnGlobe(parcel);
      }
    },

    highlightShipmentOnGlobe(parcel) {
      if (!this.globe || !this.globeInitialized) return;

      try {
        console.log('📍 Highlighting shipment on globe:', parcel.trackingId);

        // Show the route for this shipment
        this.arcsData = [{
          startLat: parcel.location[0],
          startLng: parcel.location[1],
          endLat: parcel.destination[0],
          endLng: parcel.destination[1],
          color: '#ff4444'
        }];

        // Update points to highlight selected shipment
        this.pointsData = this.parcels.map(p => ({
          ...p,
          lat: p.currentLocation[0],
          lng: p.currentLocation[1],
          color: p.id === parcel.id ? '#ff4444' : this.getStatusColor(p.status),
          size: p.id === parcel.id ? 0.5 : 0.3,
          label: `${p.trackingId}: ${p.status}`
        }));

        this.globe
          .arcsData(this.arcsData)
          .arcStartLat(d => d.startLat)
          .arcStartLng(d => d.startLng)
          .arcEndLat(d => d.endLat)
          .arcEndLng(d => d.endLng)
          .arcColor(d => d.color)
          .arcStroke(1.5)
          .arcAltitude(0.05)
          .pointsData(this.pointsData)
          .pointLat('lat')
          .pointLng('lng')
          .pointColor('color')
          .pointAltitude(0.1)
          .pointRadius('size')
          .pointLabel('label');

        this.focusOnRoute(parcel.location, parcel.destination);

      } catch (error) {
        console.error('Error highlighting shipment on globe:', error);
      }
    },

    focusOnRoute(start, end) {
      if (!this.globe) return;

      const midLat = (start[0] + end[0]) / 2;
      const midLng = (start[1] + end[1]) / 2;

      this.globe.pointOfView({ lat: midLat, lng: midLng, altitude: 2.5 });
    },

    clearSelection() {
      this.selectedParcel = null;
      this.shipmentDetails = null;

      if (this.globe && this.globeInitialized) {
        this.arcsData = [];
        this.globe.arcsData(this.arcsData);
        this.globe.pointOfView({ lat: 20, lng: 0, altitude: 2 });
        this.updateGlobeData();
      }
    },

    useFallbackData() {
      console.log('Using fallback data due to error');
      this.usingFallbackData = true;
      this.errorMessage = 'Connected to demo data. Real shipments will appear here once you create them.';

      // Use the example data directly
      const exampleData = this.getExampleShipments(this.user.email);
      this.parcels = this.transformShipmentData(exampleData);
      this.updateStats();
      this.generateNotifications();

      // Update globe data if globe is already initialized
      if (this.globeInitialized) {
        this.updateGlobeData();
      }
    },

    getExampleShipments(customerEmail) {
      return [
        {
          'mailId': 1001,
          'customerEmail': customerEmail,
          'senderAddressId': 1,
          'recipientAddressId': 2,
          'mailItems': [
            {
              'itemId': 1,
              'itemDescription': 'Electronics Package',
              'declaredValue': 250.00,
              'itemWeight': 1.5,
              'itemQuantity': 1
            }
          ],
          'parcelLength': 30.0,
          'parcelWidth': 20.0,
          'parcelHeight': 15.0,
          'service': {
            'name': 'Registered Package',
            'type': 'Packets',
            'zone': 3
          },
          'totalWeight': 1.5,
          'totalValue': 250.00,
          'hasBeenPaid': true,
          'trackingNumber': 'TRK784231',
          'createdDate': new Date(Date.now() - 2 * 24 * 60 * 60 * 1000).toISOString(),
          'senderAddress': {
            'name': 'John Doe',
            'countryCode': 'SG'
          },
          'recipientAddress': {
            'name': 'Sarah Wilson',
            'countryCode': 'US'
          },
          'status': 'in_transit',
          'expectedDelivery': new Date(Date.now() + 3 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]
        },
        {
          'mailId': 1002,
          'customerEmail': customerEmail,
          'senderAddressId': 1,
          'recipientAddressId': 3,
          'mailItems': [
            {
              'itemId': 2,
              'itemDescription': 'Documents',
              'declaredValue': 50.00,
              'itemWeight': 0.5,
              'itemQuantity': 1
            }
          ],
          'parcelLength': 35.0,
          'parcelWidth': 25.0,
          'parcelHeight': 2.0,
          'service': {
            'name': 'Registered Mail',
            'type': 'Documents',
            'zone': 2
          },
          'totalWeight': 0.5,
          'totalValue': 50.00,
          'hasBeenPaid': true,
          'trackingNumber': 'TRK784232',
          'createdDate': new Date(Date.now() - 5 * 24 * 60 * 60 * 1000).toISOString(),
          'senderAddress': {
            'name': 'John Doe',
            'countryCode': 'SG'
          },
          'recipientAddress': {
            'name': 'Mike Chen',
            'countryCode': 'MY'
          },
          'status': 'delivered',
          'expectedDelivery': new Date(Date.now() - 1 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]
        }
      ];
    },

    transformShipmentData(shipments) {
      if (!shipments || !Array.isArray(shipments)) {
        console.log('No shipments data found, using empty array');
        return [];
      }

      console.log('Transforming', shipments.length, 'shipments');

      return shipments.map(shipment => {
        try {
          // Get coordinates with better error handling
          let senderCoords = this.getCountryCoordinates(shipment.senderAddress?.countryCode || 'SG');
          let recipientCoords = this.getCountryCoordinates(shipment.recipientAddress?.countryCode || 'US');

          console.log('Sender coords for', shipment.senderAddress?.countryCode, ':', senderCoords);
          console.log('Recipient coords for', shipment.recipientAddress?.countryCode, ':', recipientCoords);

          // Ensure coordinates are valid numbers
          if (!Array.isArray(senderCoords) || senderCoords.some(isNaN)) {
            console.warn('Invalid sender coordinates, using Singapore default');
            senderCoords = [1.3521, 103.8198]; // Singapore coordinates
          }

          if (!Array.isArray(recipientCoords) || recipientCoords.some(isNaN)) {
            console.warn('Invalid recipient coordinates, using US default');
            recipientCoords = [39.8283, -98.5795]; // US center coordinates
          }

          // Determine current location based on status
          let currentLocation = senderCoords;
          const progress = this.calculateProgressFromStatus(shipment.status);

          if (progress > 0 && progress < 100) {
            // For in-progress shipments, interpolate between start and end
            currentLocation = [
              senderCoords[0] + (recipientCoords[0] - senderCoords[0]) * (progress / 100),
              senderCoords[1] + (recipientCoords[1] - senderCoords[1]) * (progress / 100)
            ];
          } else if (progress >= 100) {
            currentLocation = recipientCoords;
          }

          // Create tracking ID from available data
          let trackingId = `TRK-${shipment.mailId.toString().padStart(6, '0')}`;
          if (shipment.trackingNumber && shipment.trackingNumber !== 0) {
            trackingId = `TRK-${shipment.trackingNumber}`;
          }

          const transformedParcel = {
            id: shipment.mailId,
            mailId: shipment.mailId,
            trackingId: trackingId,
            customer: shipment.senderAddress?.name || this.user.email,
            status: this.mapApiStatus(shipment.status),
            expectedDelivery: shipment.expectedDelivery,
            location: senderCoords,
            currentLocation: currentLocation,
            destination: recipientCoords,
            progress: progress,
            service: shipment.service,
            totalWeight: shipment.totalWeight,
            totalValue: shipment.totalValue,
            hasBeenPaid: shipment.hasBeenPaid,
            createdDate: shipment.createdDate,
            rawData: shipment, // Store the raw data for detailed view
            senderAddress: shipment.senderAddress,
            recipientAddress: shipment.recipientAddress
          };

          console.log('Transformed parcel:', {
            id: transformedParcel.id,
            trackingId: transformedParcel.trackingId,
            status: transformedParcel.status,
            from: this.getLocationName(transformedParcel.location),
            to: this.getLocationName(transformedParcel.destination),
            progress: transformedParcel.progress
          });
          return transformedParcel;

        } catch (error) {
          console.error('Error transforming shipment:', shipment, error);
          return null;
        }
      }).filter(parcel => parcel !== null);
    },

    getCountryCoordinates(countryCode) {
      // Handle UK/GB country code variation
      const code = countryCode === 'UK' ? 'GB' : countryCode;

      const country = countryData.find(c => c.code2 === code);
      if (country) {
        console.log(`Found coordinates for ${countryCode}:`, [country.lat, country.long]);
        return [country.lat, country.long];
      }

      // Default to Singapore if not found
      console.warn(`Country code ${countryCode} not found, using Singapore as default`);
      return [1.28478, 103.776222];
    },

    calculateProgressFromStatus(status) {
      const progressMap = {
        'pending': 0,
        'in_transit': 50,
        'delivered': 100
      };
      return progressMap[status] || 0;
    },

    mapApiStatus(apiStatus) {
      const statusMap = {
        'pending': 'Pending',
        'in_transit': 'In Progress',
        'delivered': 'Delivered'
      };
      return statusMap[apiStatus] || 'Pending';
    },

    updateStats() {
      this.stats = {
        inProgress: this.parcels.filter(p => p.status === 'In Progress').length,
        delivered: this.parcels.filter(p => p.status === 'Delivered').length,
        pending: this.parcels.filter(p => p.status === 'Pending').length,
      };
    },

    generateNotifications() {
      // Create notifications from recent shipments
      this.notifications = this.parcels
        .slice(0, 4)
        .sort((a, b) => new Date(b.createdDate) - new Date(a.createdDate))
        .map((parcel, index) => {
          const statusConfig = {
            'In Progress': {
              type: 'info',
              icon: 'fas fa-shipping-fast',
              message: `Shipment ${parcel.trackingId} is in transit to ${this.getLocationName(parcel.destination)}`
            },
            'Delivered': {
              type: 'success',
              icon: 'fas fa-check-circle',
              message: `Shipment ${parcel.trackingId} has been delivered successfully`
            },
            'Pending': {
              type: 'warning',
              icon: 'fas fa-clock',
              message: `Shipment ${parcel.trackingId} is awaiting processing`
            }
          };

          const config = statusConfig[parcel.status] || statusConfig.Pending;

          return {
            id: index + 1,
            type: config.type,
            icon: config.icon,
            message: config.message,
            time: this.formatRelativeTime(parcel.createdDate)
          };
        });
    },

    formatRelativeTime(dateString) {
      if (!dateString) return 'Recently';
      try {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins} minutes ago`;
        if (diffHours < 24) return `${diffHours} hours ago`;
        if (diffDays === 1) return '1 day ago';
        return `${diffDays} days ago`;
      } catch (error) {
        return 'Recently';
      }
    },

    async initGlobe() {
      try {
        console.log('Starting globe initialization...');

        const container = this.$refs.globeContainer;
        if (!container) {
          throw new Error('Globe container not found');
        }

        // Ensure container has proper dimensions
        const width = container.clientWidth || 800;
        const height = container.clientHeight || 400;

        console.log('Container dimensions:', width, 'x', height);

        // Clear any existing content
        container.innerHTML = '';

        this.globe = Globe()
          .globeImageUrl('https://unpkg.com/three-globe/example/img/earth-blue-marble.jpg')
          .backgroundColor('#000011')
          .width(width)
          .height(height)
          (container);

        console.log('Basic globe created');

        // Set initial view
        this.globe.pointOfView({ lat: 20, lng: 0, altitude: 2 });

        // Initialize with points data first
        this.updateGlobeData();

        this.globeInitialized = true;
        this.globeError = false;
        console.log('Globe initialized successfully');

      } catch (error) {
        console.error('Globe initialization error:', error);
        this.globeError = true;
        this.globeInitialized = false;
      }
    },

    updateGlobeData() {
      if (!this.globe || !this.globeInitialized) return;

      try {
        // Create points data for all parcels
        this.pointsData = this.parcels.map(parcel => ({
          ...parcel,
          lat: parcel.currentLocation[0],
          lng: parcel.currentLocation[1],
          color: this.getStatusColor(parcel.status),
          size: 0.3,
          label: `${parcel.trackingId}: ${parcel.status}`
        }));

        // Update globe with points
        this.globe
          .pointsData(this.pointsData)
          .pointLat('lat')
          .pointLng('lng')
          .pointColor('color')
          .pointAltitude(0.1)
          .pointRadius('size')
          .pointLabel('label');

        console.log('Globe data updated with', this.pointsData.length, 'points');

      } catch (error) {
        console.error('Error updating globe data:', error);
      }
    },

    calculateProgress(parcel) {
      // Use the stored progress if available
      if (parcel.progress !== undefined) {
        return parcel.progress;
      }

      if (parcel.status === 'Delivered') return 100;
      if (parcel.status === 'Pending') return 0;

      const start = parcel.location;
      const current = parcel.currentLocation || parcel.location;
      const end = parcel.destination;

      const totalDistance = this.calculateDistance(start, end);
      const traveledDistance = this.calculateDistance(start, current);

      if (totalDistance === 0) return 0;

      return Math.min(100, (traveledDistance / totalDistance) * 100);
    },

    calculateDistance(coord1, coord2) {
      const [lat1, lon1] = coord1;
      const [lat2, lon2] = coord2;

      const R = 6371;
      const dLat = (lat2 - lat1) * Math.PI / 180;
      const dLon = (lon2 - lon1) * Math.PI / 180;
      const a =
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
        Math.sin(dLon/2) * Math.sin(dLon/2);
      const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
      return R * c;
    },

    getLocationName(coords) {
      if (!coords || !Array.isArray(coords) || coords.length < 2) {
        return 'Unknown Location';
      }

      const [lat, lng] = coords;

      // Find country by coordinates with a reasonable tolerance
      const country = countryData.find(c => {
        const latDiff = Math.abs(c.lat - lat);
        const lngDiff = Math.abs(c.long - lng);
        return latDiff < 15 && lngDiff < 15; // Increased tolerance for better matching
      });

      if (country) {
        console.log(`Matched coordinates [${lat}, ${lng}] to country: ${country.name}`);
        return country.name;
      }

      console.warn(`No country found for coordinates [${lat}, ${lng}]`);
      return 'Unknown Location';
    },

    getStatusColor(status) {
      const colors = {
        'In Progress': '#ffa500',
        'Delivered': '#00ff00',
        'Pending': '#ff4444'
      };
      return colors[status] || '#cccccc';
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A';
      try {
        return new Date(dateString).toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        });
      } catch (error) {
        return 'Invalid Date';
      }
    },

    forceReinit() {
      this.globeInitialized = false;
      this.globeError = false;

      // Clean up existing globe
      if (this.globe) {
        const container = this.$refs.globeContainer;
        if (container) {
          container.innerHTML = '';
        }
        this.globe = null;
      }

      this.arcsData = [];
      this.selectedParcel = null;
      this.shipmentDetails = null;

      // Reinitialize after a short delay
      this.$nextTick(() => {
        setTimeout(() => {
          this.initGlobe();
        }, 300);
      });
    },

    redirectToLogin() {
      window.location.href = '/login';
    }
  }
};
</script>

<style scoped>
/* Your existing CSS styles remain the same, but add these new styles */

/* Selected Shipment Brief Info */
.selected-shipment-brief {
  background: var(--light-pink);
  border-radius: 12px;
  padding: 1.5rem;
  margin-top: 1rem;
  border: 2px solid var(--hot-pink);
}

.brief-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid var(--pink-grey);
}

.brief-header h4 {
  margin: 0;
  font-size: 1.1rem;
  color: var(--dark-slate-blue);
}

.brief-details {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.brief-item {
  font-size: 0.9rem;
  color: var(--dark-slate-blue);
}

.brief-item strong {
  color: var(--hot-pink);
}

/* Shipment Details Section */
.shipment-details-section {
  margin-bottom: 1.5rem;
}

.shipment-details-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.shipment-details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
}

.detail-section {
  background: var(--light-pink);
  padding: 1.5rem;
  border-radius: 12px;
  border-left: 4px solid var(--hot-pink);
}

.detail-section.full-width {
  grid-column: 1 / -1;
}

.detail-section h5 {
  margin: 0 0 1rem 0;
  font-size: 1rem;
  color: var(--hot-pink);
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.detail-group {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 0.8rem;
  border-bottom: 1px solid var(--pink-grey);
}

.detail-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.detail-label {
  font-size: 0.85rem;
  color: var(--slate-blue);
  font-weight: 500;
}

.detail-value {
  font-size: 0.9rem;
  color: var(--dark-slate-blue);
  font-weight: 600;
  text-align: right;
}

.detail-value.paid {
  color: var(--hot-pink);
}

.detail-value.unpaid {
  color: var(--dark-pink);
}

/* Items Table */
.items-table {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.table-header {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 1rem;
  padding: 1rem;
  background: var(--hot-pink);
  color: white;
  font-weight: 600;
  font-size: 0.85rem;
}

.table-row {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 1rem;
  padding: 1rem;
  border-bottom: 1px solid var(--pink-grey);
  font-size: 0.85rem;
}

.table-row:last-child {
  border-bottom: none;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .shipment-details-grid {
    grid-template-columns: 1fr;
  }

  .table-header,
  .table-row {
    grid-template-columns: 1fr;
    gap: 0.5rem;
  }

  .detail-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.3rem;
  }

  .detail-value {
    text-align: left;
  }
}
</style>
