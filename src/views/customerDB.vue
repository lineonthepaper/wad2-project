
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
          <div class="history-card" @click="goToHistory">
      <div class="history-content">
        <div class="history-icon">
          <i class="fas fa-history"></i>
        </div>
        <div class="history-text">
          <h3>Shipment History</h3>
          <p>View complete tracking history</p>
        </div>
        <div class="history-arrow">
          <i class="fas fa-chevron-right"></i>
        </div>
      </div>
    </div>
  </div>

        </section>

        <!-- Globe and Tracking - RESTRUCTURED -->
        <section class="tracking-section">
          <div class="section-column globe-column">
            <!-- Globe Card - Always Present -->
            <div class="section-card">
              <div class="card-header">
                <h3><i class="fas fa-globe-americas"></i> Live Tracking Map</h3>
                <div class="card-actions">
                  <span v-if="globeUpdateTimeout" class="globe-updating-indicator">
                    <i class="fas fa-circle-notch fa-spin"></i> Updating...
                  </span>
                  <button class="btn-icon" @click="forceReinit" title="Refresh Globe">
                    <i class="fas fa-sync-alt"></i>
                  </button>
                  <button class="btn-icon" v-if="selectedParcel" @click="clearRoute" title="Clear Route">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <!-- Globe Container - Always Rendered -->
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
                    <button class="btn-retry" @click="forceReinit" style="margin-top: 1rem;">Force Initialize</button>
                  </div>
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
                    @click="selectParcel(parcel)"
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
      usingFallbackData: false,
      routeData: null,
      routeLoading: false,
      routeError: null,
      globeUpdateTimeout: null
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
    },
    hasRouteData() {
      return this.routeData && this.routeData.waypoints && this.routeData.waypoints.length > 0;
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
    if (this.globeUpdateTimeout) {
      clearTimeout(this.globeUpdateTimeout);
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
        this.routeData = null;
      }
    },

    async initializeDashboard() {
      console.log('Initializing dashboard for authenticated user...');

      // Debug coordinates first
      this.debugCountryCoordinates();

      // Fetch shipments first
      await this.fetchUserShipments();

      // Then initialize the globe after DOM is ready and data is loaded
      await this.$nextTick();
      await new Promise(resolve => setTimeout(resolve, 800));
      await this.initGlobe();
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
          if (this.globeInitialized && this.globe) {
            console.log('Updating globe with new shipment data');
            this.updateGlobeData();
          } else {
            console.log('Globe not ready yet, data will be loaded when globe initializes');
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
      if (this.globeInitialized && this.globe) {
        console.log('Updating globe after using fallback data');
        this.updateGlobeData();
      } else {
        console.log('Globe not ready, data will be loaded when globe initializes');
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
        console.log('🌍 Starting globe initialization...');

        const container = this.$refs.globeContainer;
        if (!container) {
          console.error('❌ Globe container not found in DOM');
          throw new Error('Globe container not found');
        }

        console.log('✅ Globe container found');

        // Ensure container has proper dimensions
        const width = container.clientWidth || 800;
        const height = container.clientHeight || 400;

        console.log('📐 Container dimensions:', width, 'x', height);

        if (width === 0 || height === 0) {
          console.error('❌ Container has zero dimensions');
          throw new Error('Container has invalid dimensions');
        }

        // Clear any existing content
        container.innerHTML = '';

        console.log('🚀 Creating Globe instance...');

        this.globe = Globe()
          .globeImageUrl('https://unpkg.com/three-globe/example/img/earth-blue-marble.jpg')
          .backgroundColor('#000011')
          .width(width)
          .height(height)
          (container);

        console.log('✅ Globe instance created');

        // Set initial view
        this.globe.pointOfView({ lat: 20, lng: 0, altitude: 2 });

        // Enable controls
        this.globe.controls().enableZoom = true;
        this.globe.controls().autoRotate = false;
        this.globe.controls().autoRotateSpeed = 0.5;

        this.globeInitialized = true;
        this.globeError = false;
        console.log('✅ Globe initialized successfully');

        // Now update with any existing parcel data
        if (this.parcels && this.parcels.length > 0) {
          console.log('📦 Loading', this.parcels.length, 'parcels into globe');
          await this.$nextTick();
          this.updateGlobeData();

          // If there's a selected parcel, show its route
          if (this.selectedParcel) {
            console.log('🗺️ Restoring route for selected parcel:', this.selectedParcel.trackingId);
            await this.$nextTick();
            this.updateGlobeRoute(this.selectedParcel);
          }
        } else {
          console.log('⚠️ No parcel data yet');
        }

      } catch (error) {
        console.error('❌ Globe initialization error:', error);
        this.globeError = true;
        this.globeInitialized = false;
      }
    },

    updateGlobeData() {
      if (!this.globe || !this.globeInitialized) {
        console.log('⚠️ Cannot update globe data - globe not initialized');
        return;
      }

      if (!this.parcels || this.parcels.length === 0) {
        console.log('⚠️ No parcels to display on globe');
        // Clear any existing data
        this.globe.pointsData([]);
        this.globe.arcsData([]);
        return;
      }

      try {
        console.log('📦 Updating globe with', this.parcels.length, 'parcels');

        // Create points data for all parcels
        this.pointsData = this.parcels.map(parcel => {
          const point = {
            id: parcel.id,
            lat: parcel.currentLocation[0],
            lng: parcel.currentLocation[1],
            color: this.getStatusColor(parcel.status),
            size: 0.4,
            altitude: 0.01,
            label: `${parcel.trackingId}: ${parcel.status}`
          };
          return point;
        });

        // Update globe with points
        this.globe
          .pointsData(this.pointsData)
          .pointLat('lat')
          .pointLng('lng')
          .pointColor('color')
          .pointAltitude('altitude')
          .pointRadius('size')
          .pointLabel('label')
          .pointsMerge(false); // Force complete update

        console.log('✅ Globe data updated with', this.pointsData.length, 'points');

      } catch (error) {
        console.error('❌ Error updating globe data:', error);
      }
    },

    selectParcel(parcel) {
      console.log('🎯 Parcel clicked:', parcel.trackingId);

      // Always update the selected parcel immediately for UI responsiveness
      this.selectedParcel = parcel;
      console.log('✅ Shipment details updated for:', this.selectedParcel.trackingId);

      // Clear any pending globe updates to prevent conflicts
      if (this.globeUpdateTimeout) {
        clearTimeout(this.globeUpdateTimeout);
      }

      // Update globe route if globe is ready (with debouncing for rapid clicks)
      if (this.globe && this.globeInitialized) {
        console.log('🗺️ Scheduling globe route update for:', parcel.trackingId);

        this.globeUpdateTimeout = setTimeout(() => {
          requestAnimationFrame(() => {
            this.updateGlobeRoute(parcel);
          });
        }, 100); // Small delay to handle rapid clicking
      } else {
        console.warn('⚠️ Globe not ready yet, but shipment details are showing');
      }
    },

    updateGlobeRoute(parcel) {
      if (!this.globe || !this.globeInitialized) {
        console.warn('⚠️ Globe not ready, skipping route update');
        return;
      }

      try {
        console.log('🔄 Updating globe route for parcel:', parcel.trackingId);

        // Clear existing arcs first
        this.arcsData = [];

        // Show the full route from start to destination (red dashed line)
        const mainRoute = {
          startLat: parcel.location[0],
          startLng: parcel.location[1],
          endLat: parcel.destination[0],
          endLng: parcel.destination[1],
          color: '#ff4275'
        };

        this.arcsData.push(mainRoute);

        // Also show current position arc if in transit (green line)
        if (parcel.status === 'In Progress' && parcel.progress > 0 && parcel.progress < 100) {
          const progressRoute = {
            startLat: parcel.location[0],
            startLng: parcel.location[1],
            endLat: parcel.currentLocation[0],
            endLng: parcel.currentLocation[1],
            color: '#00ff88'
          };
          this.arcsData.push(progressRoute);
        }

        // Update the globe with new arcs
        this.globe
          .arcsData(this.arcsData)
          .arcStartLat(d => d.startLat)
          .arcStartLng(d => d.startLng)
          .arcEndLat(d => d.endLat)
          .arcEndLng(d => d.endLng)
          .arcColor(d => d.color)
          .arcStroke(2)
          .arcDashLength(0.4)
          .arcDashGap(0.2)
          .arcDashAnimateTime(2000)
          .arcAltitude(0.1);

        // Focus on the route
        this.focusOnRoute(parcel.location, parcel.destination);

        // Clear the timeout indicator
        this.globeUpdateTimeout = null;

        console.log('✅ Globe route updated successfully with', this.arcsData.length, 'arcs');

      } catch (error) {
        console.error('❌ Error updating globe route:', error);
        this.globeUpdateTimeout = null;
      }
    },

    focusOnRoute(start, end) {
      if (!this.globe) return;

      try {
        const midLat = (start[0] + end[0]) / 2;
        const midLng = (start[1] + end[1]) / 2;

        // Calculate appropriate altitude based on distance
        const distance = this.calculateDistance(start, end);
        const altitude = Math.min(2.5, Math.max(1.5, distance / 5000));

        // Smooth transition to the route
        this.globe.pointOfView({
          lat: midLat,
          lng: midLng,
          altitude: altitude
        }, 1000); // 1 second transition

        console.log('🎥 Camera focused on route');
      } catch (error) {
        console.error('❌ Error focusing on route:', error);
      }
    },

    clearRoute() {
      console.log('🧹 Clearing route and selection');

      // Clear selection immediately for UI responsiveness
      this.selectedParcel = null;
      this.routeData = null;
      this.routeError = null;

      // Clear globe arcs if globe is ready (non-blocking)
      if (this.globe && this.globeInitialized) {
        try {
          this.arcsData = [];
          this.globe.arcsData(this.arcsData);
          this.globe.pointOfView({ lat: 20, lng: 0, altitude: 2 }, 1000);

          // Restore all points
          this.updateGlobeData();

          console.log('✅ Route cleared from globe');
        } catch (error) {
          console.error('❌ Error clearing route:', error);
        }
      }

      // Clear any pending updates
      if (this.globeUpdateTimeout) {
        clearTimeout(this.globeUpdateTimeout);
        this.globeUpdateTimeout = null;
      }
    },

    calculateDistance(start, end) {
      // Simple distance calculation for camera positioning
      const latDiff = Math.abs(start[0] - end[0]);
      const lngDiff = Math.abs(start[1] - end[1]);
      return Math.sqrt(latDiff * latDiff + lngDiff * lngDiff);
    },

    getStatusColor(status) {
      const colors = {
        'In Progress': '#ffa500',
        'Delivered': '#00ff88',
        'Pending': '#ff4275'
      };
      return colors[status] || '#ff4275';
    },

    getLocationName(coords) {
      if (!Array.isArray(coords) || coords.length < 2) {
        return 'Unknown Location';
      }

      // Find the closest country to these coordinates
      const closestCountry = countryData.reduce((closest, country) => {
        const distance = Math.sqrt(
          Math.pow(country.lat - coords[0], 2) +
          Math.pow(country.long - coords[1], 2)
        );
        return !closest || distance < closest.distance ? { country, distance } : closest;
      }, null);

      return closestCountry ? closestCountry.country.name : 'Unknown Location';
    },

    calculateProgress(parcel) {
      // Use the pre-calculated progress from transformation
      return parcel.progress || 0;
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A';
      try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        });
      } catch (error) {
        return 'Invalid Date';
      }
    },

    forceReinit() {
      console.log('🔄 Force reinitializing globe...');
      this.globeInitialized = false;
      this.globeError = false;

      // Clear any existing globe instance
      if (this.globe) {
        this.globe = null;
      }

      // Reinitialize after a brief delay
      setTimeout(() => {
        this.initGlobe();
      }, 500);
    },

    redirectToLogin() {
      console.log('Redirecting to login...');
      // Dispatch event to show login modal
      window.dispatchEvent(new CustomEvent('showLoginModal'));
    },
    goToHistory() {
  window.location.href = '/history';
}
  }
};
</script>

<style scoped>

.history-card {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 1.5rem;
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 120px;
}

.history-card:hover {
  transform: translateY(-5px);
  border-color: rgba(74, 144, 226, 0.3);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  background: rgba(74, 144, 226, 0.1);
}

.history-content {
  display: flex;
  align-items: center;
  gap: 1rem;
  width: 100%;
  justify-content: space-between;
}

.history-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  background: linear-gradient(135deg, #4a90e2 0%, #8e44ad 100%);
  color: white;
}

.history-text {
  flex: 1;
  text-align: center;
}

.history-text h3 {
  margin: 0 0 0.5rem 0;
  color: white;
  font-size: 1.1rem;
}

.history-text p {
  margin: 0;
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.9rem;
}

.history-arrow {
  color: #4a90e2;
  font-size: 1.2rem;
}

/* Update the stats-grid to accommodate the new card */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}
/* Dashboard Layout */
.dashboard-wrapper {
  min-height: 100vh;
  background: linear-gradient(135deg, #0c0c2e 0%, #1a1a3e 100%);
  color: white;
}

/* Header Styles */
.dashboard-header {
  position: relative;
  z-index: 10;
}

.header-background {
  background: linear-gradient(135deg, rgba(74, 144, 226, 0.2) 0%, rgba(142, 68, 173, 0.2) 100%);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.header-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 2rem;
}

.dashboard-title {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.title-main {
  font-size: 2.5rem;
  font-weight: 700;
  background: linear-gradient(135deg, #4a90e2 0%, #8e44ad 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.title-sub {
  font-size: 1.2rem;
  color: rgba(255, 255, 255, 0.8);
  font-weight: 300;
}

.welcome-message {
  margin-top: 0.5rem;
  color: rgba(255, 255, 255, 0.8);
  font-size: 1.1rem;
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
  color: #4a90e2;
}

.stat-label {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.7);
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* Main Content */
.main-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

/* Stats Overview */
.stats-overview {
  margin-bottom: 1rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.stat-card {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 1.5rem;
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.stat-card:hover {
  transform: translateY(-5px);
  border-color: rgba(74, 144, 226, 0.3);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.stat-content {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  background: linear-gradient(135deg, #4a90e2 0%, #8e44ad 100%);
}

.stat-data {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.stat-title {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.7);
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.stat-number {
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
  color: white;
}

.stat-trend {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.8rem;
}

.stat-trend.up {
  color: #00ff88;
}

.stat-trend.down {
  color: #ff4275;
}

.stat-chart {
  flex-shrink: 0;
}

.mini-chart {
  display: flex;
  align-items: end;
  gap: 2px;
  height: 40px;
}

.chart-bar {
  width: 4px;
  background: linear-gradient(to top, #4a90e2, #8e44ad);
  border-radius: 2px;
  transition: all 0.3s ease;
}

.chart-bar:hover {
  opacity: 0.8;
}

/* Tracking Section */
.tracking-section {
  display: grid;
  grid-template-columns: 1fr 400px;
  gap: 2rem;
  align-items: start;
}

.globe-column {
  min-height: 500px;
}

.parcels-column {
  height: fit-content;
  position: sticky;
  top: 2rem;
}

/* Section Cards */
.section-card {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  overflow: hidden;
  transition: all 0.3s ease;
}

.section-card:hover {
  border-color: rgba(255, 255, 255, 0.2);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.card-header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header h3 {
  margin: 0;
  font-size: 1.3rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.card-header h3 i {
  color: #4a90e2;
}

.card-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.search-box {
  position: relative;
  display: flex;
  align-items: center;
}

.search-box i {
  position: absolute;
  left: 0.75rem;
  color: rgba(255, 255, 255, 0.5);
}

.search-box input {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  padding: 0.5rem 0.75rem 0.5rem 2rem;
  color: white;
  font-size: 0.9rem;
  width: 200px;
  transition: all 0.3s ease;
}

.search-box input:focus {
  outline: none;
  border-color: #4a90e2;
  background: rgba(255, 255, 255, 0.15);
}

.search-box input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.btn-icon {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  padding: 0.5rem;
  color: white;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-icon:hover {
  background: rgba(255, 255, 255, 0.2);
  border-color: rgba(255, 255, 255, 0.3);
}

.card-body {
  padding: 1.5rem;
}

/* Globe Container */
.globe-container {
  position: relative;
  border-radius: 12px;
  overflow: hidden;
  background: rgba(0, 0, 0, 0.3);
  min-height: 400px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.globe-error,
.globe-loading {
  text-align: center;
  padding: 2rem;
  color: rgba(255, 255, 255, 0.8);
}

.error-icon,
.loading-spinner {
  font-size: 3rem;
  margin-bottom: 1rem;
  color: #ff4275;
}

.loading-spinner {
  border: 3px solid rgba(255, 255, 255, 0.1);
  border-top: 3px solid #4a90e2;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.btn-retry {
  background: linear-gradient(135deg, #4a90e2 0%, #8e44ad 100%);
  border: none;
  border-radius: 8px;
  padding: 0.75rem 1.5rem;
  color: white;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-retry:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(74, 144, 226, 0.4);
}

.globe-updating-indicator {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8rem;
  color: #4a90e2;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

/* Parcels List */
.parcels-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  max-height: 600px;
  overflow-y: auto;
}

.parcel-item {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1.25rem;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  gap: 1rem;
}

.parcel-item:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(74, 144, 226, 0.3);
  transform: translateX(5px);
}

.parcel-item.active {
  background: rgba(74, 144, 226, 0.15);
  border-color: #4a90e2;
}

.parcel-icon {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  background: linear-gradient(135deg, #4a90e2 0%, #8e44ad 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.parcel-details {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.parcel-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
}

.tracking-id {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: white;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  white-space: nowrap;
}

.status-in-progress {
  background: rgba(255, 165, 0, 0.2);
  color: #ffa500;
  border: 1px solid rgba(255, 165, 0, 0.3);
}

.status-delivered {
  background: rgba(0, 255, 136, 0.2);
  color: #00ff88;
  border: 1px solid rgba(0, 255, 136, 0.3);
}

.status-pending {
  background: rgba(255, 66, 117, 0.2);
  color: #ff4275;
  border: 1px solid rgba(255, 66, 117, 0.3);
}

.parcel-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.8);
}

.info-item i {
  width: 16px;
  color: #4a90e2;
}

.parcel-progress {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-top: 0.5rem;
}

.progress-mini {
  flex: 1;
  height: 4px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 2px;
  overflow: hidden;
}

.progress-fill-mini {
  height: 100%;
  background: linear-gradient(90deg, #4a90e2, #8e44ad);
  border-radius: 2px;
  transition: width 0.3s ease;
}

.progress-text {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.7);
  font-weight: 600;
  min-width: 40px;
}

.empty-state {
  text-align: center;
  padding: 3rem 2rem;
  color: rgba(255, 255, 255, 0.6);
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.empty-subtext {
  font-size: 0.9rem;
  margin-top: 0.5rem;
}

/* Notifications Section */
.notifications-section {
  margin-top: 1rem;
}

.notifications-column {
  max-width: 100%;
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
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
}

.notification-item:hover {
  background: rgba(255, 255, 255, 0.08);
  transform: translateX(5px);
}

.notification-icon {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 1rem;
}

.notification-icon.info {
  background: rgba(74, 144, 226, 0.2);
  color: #4a90e2;
}

.notification-icon.success {
  background: rgba(0, 255, 136, 0.2);
  color: #00ff88;
}

.notification-icon.warning {
  background: rgba(255, 165, 0, 0.2);
  color: #ffa500;
}

.notification-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.notification-text {
  margin: 0;
  color: white;
  font-size: 0.95rem;
  line-height: 1.4;
}

.notification-time {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.6);
}

.empty-notifications {
  text-align: center;
  padding: 3rem 2rem;
  color: rgba(255, 255, 255, 0.6);
}

.empty-notifications i {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

/* Loading & Error States */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(12, 12, 46, 0.9);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  gap: 1rem;
}

.loading-spinner-large {
  border: 4px solid rgba(255, 255, 255, 0.1);
  border-top: 4px solid #4a90e2;
  border-radius: 50%;
  width: 60px;
  height: 60px;
  animation: spin 1s linear infinite;
}

.error-banner {
  background: linear-gradient(135deg, #ff4275 0%, #ff6b9d 100%);
  color: white;
  padding: 1rem 1.5rem;
  border-radius: 12px;
  margin: 1rem 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  animation: slideDown 0.3s ease;
}

@keyframes slideDown {
  from {
    transform: translateY(-20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.error-banner i {
  font-size: 1.2rem;
}

/* Login Required */
.login-required {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  padding: 2rem;
}

.login-message {
  text-align: center;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 3rem 2rem;
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  max-width: 400px;
  width: 100%;
}

.message-icon {
  font-size: 4rem;
  color: #4a90e2;
  margin-bottom: 1.5rem;
}

.login-message h2 {
  margin: 0 0 1rem 0;
  color: white;
  font-size: 1.8rem;
}

.login-message p {
  color: rgba(255, 255, 255, 0.8);
  margin-bottom: 2rem;
  line-height: 1.6;
}

.action-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.btn {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  border: none;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
}

.btn-primary {
  background: linear-gradient(135deg, #4a90e2 0%, #8e44ad 100%);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(74, 144, 226, 0.4);
}

/* Responsive Design */
@media (max-width: 1024px) {
  .tracking-section {
    grid-template-columns: 1fr;
  }

  .parcels-column {
    position: static;
  }

  .header-content {
    flex-direction: column;
    text-align: center;
    gap: 1.5rem;
  }

  .header-stats {
    justify-content: center;
  }
}

@media (max-width: 768px) {
  .main-content {
    padding: 1rem;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .header-content {
    padding: 1.5rem 1rem;
  }

  .title-main {
    font-size: 2rem;
  }

  .stat-value {
    font-size: 2rem;
  }

  .card-header {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }

  .card-actions {
    justify-content: space-between;
  }

  .search-box input {
    width: 150px;
  }

  .parcel-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .action-buttons {
    flex-direction: column;
  }
}

@media (max-width: 480px) {
  .dashboard-title {
    align-items: center;
  }

  .header-stats {
    flex-direction: column;
    gap: 1rem;
  }

  .stat-card {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }

  .stat-content {
    flex-direction: column;
  }

  .parcel-item {
    flex-direction: column;
    text-align: center;
  }

  .parcel-info {
    align-items: center;
  }

  .notification-item {
    flex-direction: column;
    text-align: center;
  }
}

/* Scrollbar Styling */
.parcels-list::-webkit-scrollbar {
  width: 6px;
}

.parcels-list::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 3px;
}

.parcels-list::-webkit-scrollbar-thumb {
  background: linear-gradient(135deg, #4a90e2 0%, #8e44ad 100%);
  border-radius: 3px;
}

.parcels-list::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(135deg, #3a80d2 0%, #7e34a0 100%);
}
</style>

