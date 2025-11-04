<template>
  <div>
    <div class="dashboard-wrapper" v-if="isAuthenticated">
      <div v-if="loading" class="loading-overlay">
        <div class="loading-spinner-large"></div>
        <p>Loading your shipments...</p>
      </div>

      <div v-if="errorMessage" class="error-banner">
        <i class="fas fa-exclamation-circle"></i>
        {{ errorMessage }}
        <button @click="fetchUserShipments" class="btn-retry">Retry</button>
      </div>

      <div class="dashboard-header">
        <div class="header-background">
          <div class="header-content">
            <div class="header-text">
              <h1 class="dashboard-title">
                <span class="title-main">Parcel Tracking</span>
                <span class="title-sub">Dashboard</span>
              </h1>
              <p> Track your shipments in real-time.</p>
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

      <div class="main-content" v-if="!loading">
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

        <div class="text-center my-3">
          <button @click="viewHistory"
                  style="background: #ff6b9d; border: none; border-radius: 8px; padding: 15px 25px; color: white; transition: all 0.3s ease; cursor: pointer;"
                  onmouseover="this.style.background='#ff4d8d'"
                  onmouseout="this.style.background='#ff6b9d'">
            <h3 class="mb-0">Shipment History</h3>
            <p class="mb-0">Click to view more</p>
          </button>
        </div>

        <section class="tracking-section">
          <div class="section-column globe-column">
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
                <div class="card-actions">
                  <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input
                      type="text"
                      placeholder="Search by tracking, customer, location..."
                      v-model="searchQuery"
                      @input="handleSearchInput"
                    >
                  </div>
                  <button class="btn-icon" title="Filter">
                    <i class="fas fa-filter"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="search-info" v-if="searchQuery && filteredParcels.length > 0">
                  <p class="search-results-text">
                    Found {{ filteredParcels.length }} shipment(s) for "{{ searchQuery }}"
                  </p>
                </div>
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

                  <div v-if="filteredParcels.length === 0 && searchQuery" class="empty-state">
                    <i class="fas fa-search"></i>
                    <p>No shipments found for "{{ searchQuery }}"</p>
                    <p class="empty-subtext">Try searching by tracking number, customer name, or location</p>
                  </div>

                  <div v-if="filteredParcels.length === 0 && !searchQuery" class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <p>No shipments available</p>
                    <p class="empty-subtext">Your shipments will appear here once created</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

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
      if (!this.searchQuery.trim()) return this.parcels;

      const query = this.searchQuery.trim().toLowerCase();
      console.log('🔍 Searching for:', query, 'in', this.parcels.length, 'parcels');

      const filtered = this.parcels.filter(parcel => {
        // Check tracking ID (with and without TRK- prefix)
        const trackingMatch = parcel.trackingId.toLowerCase().includes(query);
        const rawTrackingMatch = parcel.trackingId.replace('TRK-', '').toLowerCase().includes(query.replace('trk-', ''));

        // Check customer name
        const customerMatch = parcel.customer && parcel.customer.toLowerCase().includes(query);

        // Check location
        const locationMatch = this.getLocationName(parcel.currentLocation || parcel.location).toLowerCase().includes(query);

        // Check mailId (numeric search)
        const mailIdMatch = parcel.mailId && parcel.mailId.toString().includes(query);

        // Check status
        const statusMatch = parcel.status && parcel.status.toLowerCase().includes(query);

        // Check recipient country
        const recipientCountryMatch = parcel.recipientAddress?.countryCode &&
                                     parcel.recipientAddress.countryCode.toLowerCase().includes(query);

        // Check sender country
        const senderCountryMatch = parcel.senderAddress?.countryCode &&
                                  parcel.senderAddress.countryCode.toLowerCase().includes(query);

        const matches = trackingMatch || rawTrackingMatch || customerMatch ||
                       locationMatch || mailIdMatch || statusMatch ||
                       recipientCountryMatch || senderCountryMatch;

        if (matches) {
          console.log('✅ Found match:', parcel.trackingId, {
            trackingMatch,
            rawTrackingMatch,
            customerMatch,
            locationMatch,
            mailIdMatch,
            statusMatch,
            recipientCountryMatch,
            senderCountryMatch
          });
        }

        return matches;
      });

      console.log('📊 Filtered results:', filtered.length, 'parcels found');
      return filtered;
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
      this.globe = null;
    }
    window.removeEventListener('loginStatusChanged', this.handleLoginStatusChange);
  },
  methods: {
    handleSearchInput() {
      console.log('Search query updated:', this.searchQuery);
    },

    viewHistory() {
      this.$router.push('/history');
    },

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
        this.parcels = [];
        this.notifications = [];
        this.stats = { inProgress: 0, delivered: 0, pending: 0 };
        this.usingFallbackData = false;
        this.routeData = null;
      }
    },

    async initializeDashboard() {
      console.log('Initializing dashboard for authenticated user...');
      this.debugCountryCoordinates();
      await this.fetchUserShipments();
      await this.$nextTick();
      await new Promise(resolve => setTimeout(resolve, 800));
      await this.initGlobe();
    },

    debugCountryCoordinates() {
      const australia = countryData.find(c => c.code2 === 'AU');
      console.log('Australia coordinates from JSON:', australia);
      const singapore = countryData.find(c => c.code2 === 'SG');
      console.log('Singapore coordinates from JSON:', singapore);
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
          if (response.data.note) {
            this.usingFallbackData = true;
            console.log('Using fallback data:', response.data.note);
          }

          this.parcels = this.transformShipmentData(response.data.shipments);
          this.updateStats();
          this.generateNotifications();
          console.log('Successfully loaded', this.parcels.length, 'shipments');

          if (this.globeInitialized && this.globe) {
            console.log('Updating globe with new shipment data');
            this.updateGlobeData();
          } else {
            console.log('Globe not ready yet, data will be loaded when globe initializes');
          }

        } else {
          console.error('Failed to fetch shipments:', response.data.error);
          this.useFallbackData();
        }
      } catch (error) {
        console.error('Error fetching shipments:', error);
        this.useFallbackData();
      } finally {
        this.loading = false;
      }
    },

    useFallbackData() {
      console.log('Using fallback data due to error');
      this.usingFallbackData = true;
      this.errorMessage = 'Connected to demo data. Real shipments will appear here once you create them.';

      const exampleData = this.getExampleShipments(this.user.email);
      this.parcels = this.transformShipmentData(exampleData);
      this.updateStats();
      this.generateNotifications();

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
          let senderCoords = this.getCountryCoordinates(shipment.senderAddress?.countryCode || 'SG');
          let recipientCoords = this.getCountryCoordinates(shipment.recipientAddress?.countryCode || 'US');

          console.log('Sender coords for', shipment.senderAddress?.countryCode, ':', senderCoords);
          console.log('Recipient coords for', shipment.recipientAddress?.countryCode, ':', recipientCoords);

          if (!Array.isArray(senderCoords) || senderCoords.some(isNaN)) {
            console.warn('Invalid sender coordinates, using Singapore default');
            senderCoords = [1.3521, 103.8198];
          }

          if (!Array.isArray(recipientCoords) || recipientCoords.some(isNaN)) {
            console.warn('Invalid recipient coordinates, using US default');
            recipientCoords = [39.8283, -98.5795];
          }

          let currentLocation = senderCoords;
          const progress = this.calculateProgressFromStatus(shipment.status);

          if (progress > 0 && progress < 100) {
            currentLocation = [
              senderCoords[0] + (recipientCoords[0] - senderCoords[0]) * (progress / 100),
              senderCoords[1] + (recipientCoords[1] - senderCoords[1]) * (progress / 100)
            ];
          } else if (progress >= 100) {
            currentLocation = recipientCoords;
          }

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
      const code = countryCode === 'UK' ? 'GB' : countryCode;
      const country = countryData.find(c => c.code2 === code);
      if (country) {
        console.log(`Found coordinates for ${countryCode}:`, [country.lat, country.long]);
        return [country.lat, country.long];
      }
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
          console.error('Globe container not found in DOM');
          throw new Error('Globe container not found');
        }

        console.log('Globe container found');
        const width = container.clientWidth || 800;
        const height = container.clientHeight || 400;
        console.log(' Container dimensions:', width, 'x', height);

        if (width === 0 || height === 0) {
          console.error('Container has zero dimensions');
          throw new Error('Container has invalid dimensions');
        }

        container.innerHTML = '';
        console.log('Creating Globe instance...');

        this.globe = Globe()
          .globeImageUrl('https://unpkg.com/three-globe/example/img/earth-blue-marble.jpg')
          .backgroundColor('#000011')
          .width(width)
          .height(height)
          (container);

        console.log(' Globe instance created');
        this.globe.pointOfView({ lat: 20, lng: 0, altitude: 2 });
        this.globe.controls().enableZoom = true;
        this.globe.controls().autoRotate = false;
        this.globe.controls().autoRotateSpeed = 0.5;

        this.globeInitialized = true;
        this.globeError = false;
        console.log(' Globe initialized successfully');

        if (this.parcels && this.parcels.length > 0) {
          console.log(' Loading', this.parcels.length, 'parcels into globe');
          await this.$nextTick();
          this.updateGlobeData();

          if (this.selectedParcel) {
            console.log('Restoring route for selected parcel:', this.selectedParcel.trackingId);
            await this.$nextTick();
            this.updateGlobeRoute(this.selectedParcel);
          }
        } else {
          console.log('No parcel data yet');
        }

      } catch (error) {
        console.error(' Globe initialization error:', error);
        this.globeError = true;
        this.globeInitialized = false;
      }
    },

    updateGlobeData() {
      if (!this.globe || !this.globeInitialized) {
        console.log(' Cannot update globe data - globe not initialized');
        return;
      }

      if (!this.parcels || this.parcels.length === 0) {
        console.log(' No parcels to display on globe');
        this.globe.pointsData([]);
        this.globe.arcsData([]);
        return;
      }

      try {
        console.log(' Updating globe with', this.parcels.length, 'parcels');
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

        this.globe
          .pointsData(this.pointsData)
          .pointLat('lat')
          .pointLng('lng')
          .pointColor('color')
          .pointAltitude('altitude')
          .pointRadius('size')
          .pointLabel('label')
          .pointsMerge(false);

        console.log(' Globe data updated with', this.pointsData.length, 'points');
      } catch (error) {
        console.error(' Error updating globe data:', error);
      }
    },

    selectParcel(parcel) {
      console.log(' Parcel clicked:', parcel.trackingId);
      this.selectedParcel = parcel;
      console.log('Shipment details updated for:', this.selectedParcel.trackingId);

      if (this.globeUpdateTimeout) {
        clearTimeout(this.globeUpdateTimeout);
      }

      if (this.globe && this.globeInitialized) {
        console.log('Scheduling globe route update for:', parcel.trackingId);
        this.globeUpdateTimeout = setTimeout(() => {
          requestAnimationFrame(() => {
            this.updateGlobeRoute(parcel);
          });
        }, 100);
      } else {
        console.warn(' Globe not ready yet, but shipment details are showing');
      }
    },

    updateGlobeRoute(parcel) {
      if (!this.globe || !this.globeInitialized) {
        console.warn(' Globe not ready, skipping route update');
        return;
      }

      try {
        console.log(' Updating globe route for parcel:', parcel.trackingId);
        this.arcsData = [];

        const mainRoute = {
          startLat: parcel.location[0],
          startLng: parcel.location[1],
          endLat: parcel.destination[0],
          endLng: parcel.destination[1],
          color: '#ff4275'
        };

        this.arcsData.push(mainRoute);

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

        this.focusOnRoute(parcel.location, parcel.destination);
        this.globeUpdateTimeout = null;
        console.log(' Globe route updated successfully with', this.arcsData.length, 'arcs');

      } catch (error) {
        console.error(' Error updating globe route:', error);
        this.globeUpdateTimeout = null;
      }
    },

    focusOnRoute(start, end) {
      if (!this.globe) return;
      try {
        const midLat = (start[0] + end[0]) / 2;
        const midLng = (start[1] + end[1]) / 2;
        const distance = this.calculateDistance(start, end);
        const altitude = Math.min(2.5, Math.max(1.5, distance / 5000));
        this.globe.pointOfView({
          lat: midLat,
          lng: midLng,
          altitude: altitude
        }, 1000);
        console.log(' Camera focused on route');
      } catch (error) {
        console.error(' Error focusing on route:', error);
      }
    },

    clearRoute() {
      console.log('Clearing route and selection');
      this.selectedParcel = null;
      this.routeData = null;
      this.routeError = null;

      if (this.globe && this.globeInitialized) {
        try {
          this.arcsData = [];
          this.globe.arcsData(this.arcsData);
          this.globe.pointOfView({ lat: 20, lng: 0, altitude: 2 }, 1000);
          this.updateGlobeData();
          console.log('Route cleared from globe');
        } catch (error) {
          console.error('Error clearing route:', error);
        }
      }

      if (this.globeUpdateTimeout) {
        clearTimeout(this.globeUpdateTimeout);
        this.globeUpdateTimeout = null;
      }
    },

    calculateDistance(start, end) {
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
      console.log('Force reinitializing globe...');
      this.globeInitialized = false;
      this.globeError = false;

      if (this.globe) {
        this.globe = null;
      }

      setTimeout(() => {
        this.initGlobe();
      }, 500);
    },

    redirectToLogin() {
      console.log('Redirecting to login...');
      window.dispatchEvent(new CustomEvent('showLoginModal'));
    },

    goToHistory() {
      window.location.href = '/history';
    }
  }
};
</script>

<style scoped>
.history-page {
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

  .action-buttons {
    flex-direction: column;
  }

  .btn {
    justify-content: center;
  }
}
</style>
