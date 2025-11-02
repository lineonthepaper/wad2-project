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

        <!-- Globe and Tracking -->
        <section class="tracking-section">
          <div class="section-column globe-column">
            <div class="section-card">
              <div class="card-header">
                <h3><i class="fas fa-globe-americas"></i> Live Tracking Map</h3>
                <div class="card-actions">
                  <button class="btn-icon" @click="forceReinit" title="Refresh Globe">
                    <i class="fas fa-sync-alt"></i>
                  </button>
                  <button class="btn-icon" v-if="selectedParcel" @click="clearRoute" title="Clear Route">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <!-- Fixed Globe Container with explicit dimensions -->
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

                <div v-if="selectedParcel" class="selected-parcel-info">
                  <div class="parcel-header">
                    <h4>Active Tracking: {{ selectedParcel.trackingId }}</h4>
                    <span class="status-badge" :class="`status-${selectedParcel.status.toLowerCase().replace(' ', '-')}`">
                      {{ selectedParcel.status }}
                    </span>
                  </div>
                  <div class="route-progress">
                    <div class="progress-labels">
                      <span class="progress-label">{{ getLocationName(selectedParcel.location) }}</span>
                      <span class="progress-percent">{{ Math.round(calculateProgress(selectedParcel)) }}%</span>
                      <span class="progress-label">{{ getLocationName(selectedParcel.destination) }}</span>
                    </div>
                    <div class="progress-track">
                      <div class="progress-bar">
                        <div class="progress-fill" :style="{ width: calculateProgress(selectedParcel) + '%' }"></div>
                        <div class="progress-marker" :style="{ left: calculateProgress(selectedParcel) + '%' }">
                          <i class="fas fa-shipping-fast"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-else class="no-selection">
                  <i class="fas fa-mouse-pointer"></i>
                  <p>Select a shipment from the list to view its route</p>
                </div>
              </div>
            </div>
          </div>

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
                    @click="showParcelRoute(parcel)"
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
        rawData: shipment,
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

        // Update arcs if a parcel is selected
        if (this.selectedParcel) {
          this.updateRouteForParcel(this.selectedParcel);
        }

        console.log('Globe data updated with', this.pointsData.length, 'points');

      } catch (error) {
        console.error('Error updating globe data:', error);
      }
    },

 showParcelRoute(parcel) {
  if (!this.globe || !this.globeInitialized) {
    console.error('Globe not ready');
    return;
  }

  console.log('Showing route for parcel:', {
    trackingId: parcel.trackingId,
    status: parcel.status,
    from: this.getLocationName(parcel.location),
    to: this.getLocationName(parcel.destination),
    progress: parcel.progress
  });

  // Set the selected parcel - this will make it appear below the globe
  this.selectedParcel = parcel;

  // Update the globe with the route
  this.updateRouteForParcel(parcel);
},

updateRouteForParcel(parcel) {
  if (!this.globe || !this.globeInitialized) return;

  try {
    // Show the full route from start to destination
    this.arcsData = [{
      startLat: parcel.location[0],
      startLng: parcel.location[1],
      endLat: parcel.destination[0],
      endLng: parcel.destination[1],
      color: '#ff4444' // Red line for the full route
    }];

    // Also show current position arc if in transit
    if (parcel.status === 'In Progress' && parcel.progress > 0) {
      this.arcsData.push({
        startLat: parcel.location[0],
        startLng: parcel.location[1],
        endLat: parcel.currentLocation[0],
        endLng: parcel.currentLocation[1],
        color: '#00ff00' // Green line for traveled path
      });
    }

    this.globe
      .arcsData(this.arcsData)
      .arcStartLat(d => d.startLat)
      .arcStartLng(d => d.startLng)
      .arcEndLat(d => d.endLat)
      .arcEndLng(d => d.endLng)
      .arcColor(d => d.color)
      .arcStroke(1.5)
      .arcAltitude(0.05);

    this.focusOnRoute(parcel.location, parcel.destination);

  } catch (error) {
    console.error('Error updating route:', error);
  }
},

    focusOnRoute(start, end) {
      if (!this.globe) return;

      const midLat = (start[0] + end[0]) / 2;
      const midLng = (start[1] + end[1]) / 2;

      this.globe.pointOfView({ lat: midLat, lng: midLng, altitude: 2.5 });
    },

    clearRoute() {
      if (this.globe && this.globeInitialized) {
        this.arcsData = [];
        this.selectedParcel = null;
        this.globe.arcsData(this.arcsData);
        this.globe.pointOfView({ lat: 20, lng: 0, altitude: 2 });
        this.updateGlobeData();
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
/* Your existing CSS styles remain exactly the same */
/* Import Font Awesome */
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');

/* Pink Color Palette */
:root {
  --hot-pink: #ff4275;
  --dark-pink: #ff759e;
  --pink: #ff9096;
  --dark-slate-blue: #455a64;
  --slate-blue: #8796b3;
  --light-pink: #ffe8ee;
  --pink-grey: #f1d9df;
}

/* Base Styles */
.dashboard-wrapper {
  min-height: 100vh;
  background: linear-gradient(135deg, var(--light-pink) 0%, var(--pink-grey) 100%);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Loading Overlay */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.9);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.loading-spinner-large {
  width: 60px;
  height: 60px;
  border: 6px solid var(--light-pink);
  border-top: 6px solid var(--hot-pink);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

/* Error Banner */
.error-banner {
  background: var(--dark-pink);
  color: white;
  padding: 1rem;
  border-radius: 8px;
  margin: 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  justify-content: space-between;
}

.btn-retry {
  background: white;
  color: var(--hot-pink);
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-retry:hover {
  background: var(--hot-pink);
  color: white;
}

/* Header */
.dashboard-header {
  margin-bottom: 2rem;
}

.header-background {
  background: linear-gradient(135deg, var(--hot-pink) 0%, var(--dark-pink) 100%);
  color: white;
  padding: 2rem;
  border-radius: 0 0 20px 20px;
  box-shadow: 0 4px 20px rgba(255, 66, 117, 0.3);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1200px;
  margin: 0 auto;
}

.header-text {
  flex: 1;
}

.dashboard-title {
  margin-bottom: 0.5rem;
}

.title-main {
  display: block;
  font-size: 2.5rem;
  font-weight: 700;
  line-height: 1.1;
}

.title-sub {
  display: block;
  font-size: 1.8rem;
  font-weight: 300;
  opacity: 0.9;
}

.welcome-message {
  font-size: 1.1rem;
  opacity: 0.9;
  margin: 0;
}

.header-stats {
  display: flex;
  gap: 2rem;
}

.header-stat {
  text-align: center;
}

.stat-value {
  display: block;
  font-size: 2.5rem;
  font-weight: 700;
  line-height: 1;
}

.stat-label {
  font-size: 0.9rem;
  opacity: 0.8;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Main Content */
.main-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem 2rem;
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

.stat-chart {
  width: 80px;
}

.mini-chart {
  display: flex;
  align-items: end;
  gap: 2px;
  height: 40px;
}

.chart-bar {
  flex: 1;
  background: linear-gradient(to top, var(--hot-pink), var(--dark-pink));
  border-radius: 2px;
  min-height: 2px;
}

/* Tracking Section */
.tracking-section {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.section-column {
  display: flex;
  flex-direction: column;
}

.section-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  height: 100%;
  display: flex;
  flex-direction: column;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 1.5rem 0;
  margin-bottom: 1rem;
  background: var(--light-pink);
  border-bottom: 1px solid var(--pink-grey);
}

.card-header h3 {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 600;
  color: var(--dark-slate-blue);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.card-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-icon {
  width: 36px;
  height: 36px;
  border: none;
  border-radius: 8px;
  background: white;
  color: var(--hot-pink);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-icon:hover {
  background: var(--hot-pink);
  color: white;
  transform: scale(1.05);
}

.search-box {
  position: relative;
  display: flex;
  align-items: center;
}

.search-box i {
  position: absolute;
  left: 10px;
  color: var(--slate-blue);
}

.search-box input {
  padding: 0.5rem 0.5rem 0.5rem 2rem;
  border: 1px solid var(--pink-grey);
  border-radius: 8px;
  font-size: 0.9rem;
  width: 180px;
  transition: border-color 0.3s ease;
  background: var(--light-pink);
}

.search-box input:focus {
  outline: none;
  border-color: var(--hot-pink);
}

.card-body {
  padding: 0 1.5rem 1.5rem;
  flex: 1;
  display: flex;
  flex-direction: column;
}

/* Globe Container */
.globe-container {
  width: 100%;
  height: 400px;
  border-radius: 12px;
  background: var(--dark-slate-blue);
  position: relative;
  overflow: hidden;
  margin-bottom: 1rem;
}

.globe-loading, .globe-error {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: white;
  font-size: 1.1rem;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid var(--light-pink);
  border-top: 4px solid var(--hot-pink);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

.globe-error {
  color: white;
  background: var(--dark-pink);
  padding: 2rem;
  text-align: center;
}

.error-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

/* Selected Parcel Info */
.selected-parcel-info {
  background: var(--light-pink);
  border-radius: 12px;
  padding: 1.5rem;
}

.parcel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.parcel-header h4 {
  margin: 0;
  font-size: 1.1rem;
  color: var(--dark-slate-blue);
}

.route-progress {
  margin-top: 1rem;
}

.progress-labels {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
  color: var(--slate-blue);
}

.progress-percent {
  font-weight: 600;
  color: var(--hot-pink);
}

.progress-track {
  position: relative;
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

.no-selection {
  text-align: center;
  padding: 3rem 1rem;
  color: var(--slate-blue);
}

.no-selection i {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.5;
  color: var(--pink);
}

.no-selection p {
  margin: 0;
  font-size: 1rem;
}

/* Parcels List */
.parcels-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  max-height: 600px;
  overflow-y: auto;
}

.parcel-item {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  border: 1px solid var(--pink-grey);
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.parcel-item:hover {
  border-color: var(--hot-pink);
  transform: translateX(5px);
}

.parcel-item.active {
  border-color: var(--hot-pink);
  background: var(--light-pink);
}

.parcel-icon {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, var(--hot-pink), var(--dark-pink));
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.2rem;
}

.parcel-details {
  flex: 1;
}

.parcel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.tracking-id {
  font-size: 1rem;
  font-weight: 600;
  color: var(--dark-slate-blue);
  margin: 0;
}

.parcel-info {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
  margin-bottom: 0.5rem;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  color: var(--slate-blue);
}

.info-item i {
  width: 12px;
  color: var(--hot-pink);
}

.parcel-progress {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.progress-mini {
  flex: 1;
  height: 4px;
  background: var(--pink-grey);
  border-radius: 2px;
  overflow: hidden;
}

.progress-fill-mini {
  height: 100%;
  background: linear-gradient(90deg, var(--hot-pink), var(--pink));
  transition: width 0.5s ease;
}

.progress-text {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--hot-pink);
  min-width: 35px;
}

/* Empty States */
.empty-state, .empty-notifications {
  text-align: center;
  padding: 3rem 1rem;
  color: var(--slate-blue);
}

.empty-state i, .empty-notifications i {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.5;
  color: var(--pink);
}

.empty-state p, .empty-notifications p {
  margin: 0;
  font-size: 1rem;
}

.empty-subtext {
  font-size: 0.9rem !important;
  opacity: 0.7;
}

/* Status Badges */
.status-badge {
  padding: 0.3rem 0.8rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-in-progress {
  background: var(--light-pink);
  color: var(--hot-pink);
}

.status-delivered {
  background: var(--pink-grey);
  color: var(--pink);
}

.status-pending {
  background: var(--pink-grey);
  color: var(--dark-pink);
}

/* Notifications Section */
.notifications-section {
  margin-bottom: 2rem;
}

.notifications-column {
  width: 100%;
}

.notifications-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.notification-item {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  border: 1px solid var(--pink-grey);
  border-radius: 12px;
  transition: all 0.3s ease;
}

.notification-item:hover {
  border-color: var(--hot-pink);
  transform: translateX(5px);
}

.notification-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}

.notification-icon.success {
  background: var(--light-pink);
  color: var(--hot-pink);
}

.notification-icon.info {
  background: var(--pink-grey);
  color: var(--pink);
}

.notification-icon.warning {
  background: var(--pink-grey);
  color: var(--dark-pink);
}

.notification-content {
  flex: 1;
}

.notification-text {
  margin: 0 0 0.3rem;
  font-size: 0.9rem;
  color: var(--dark-slate-blue);
}

.notification-time {
  font-size: 0.8rem;
  color: var(--slate-blue);
}

/* Login Required Styles */
.login-required {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, var(--light-pink) 0%, var(--pink-grey) 100%);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
  flex-wrap: wrap;
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

/* Animations */
@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 1024px) {
  .tracking-section {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    text-align: center;
    gap: 1.5rem;
  }

  .header-stats {
    justify-content: center;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .card-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }

  .search-box input {
    width: 100%;
  }

  .login-message {
    padding: 2rem 1.5rem;
    margin: 1rem;
  }

  .action-buttons {
    flex-direction: column;
  }

  .btn {
    justify-content: center;
  }

  .parcel-item {
    flex-direction: column;
    gap: 0.5rem;
  }

  .parcel-icon {
    align-self: flex-start;
  }
}

@media (max-width: 480px) {
  .header-background {
    padding: 1.5rem 1rem;
  }

  .title-main {
    font-size: 2rem;
  }

  .title-sub {
    font-size: 1.4rem;
  }

  .header-stats {
    gap: 1rem;
  }

  .stat-value {
    font-size: 2rem;
  }
}
</style>
