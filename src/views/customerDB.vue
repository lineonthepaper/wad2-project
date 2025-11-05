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
            <div class="stat-card stat-total">
              <div class="stat-content">
                <div class="stat-icon">📦</div>
                <div class="stat-data">
                  <h3 class="stat-title">Total Shipments</h3>
                  <div class="stat-number">{{ totalShipments }}</div>
                </div>
              </div>
              <div class="stat-chart">
                <div class="mini-chart">
                  <div class="chart-bar" :style="{ height: '70%' }"></div>
                  <div class="chart-bar" :style="{ height: '50%' }"></div>
                  <div class="chart-bar" :style="{ height: '80%' }"></div>
                  <div class="chart-bar" :style="{ height: '60%' }"></div>
                </div>
              </div>
            </div>

            <div class="stat-card stat-delivered">
              <div class="stat-content">
                <div class="stat-icon">✅</div>
                <div class="stat-data">
                  <h3 class="stat-title">Delivered</h3>
                  <div class="stat-number">{{ stats.delivered }}</div>
                </div>
              </div>
              <div class="stat-chart">
                <div class="mini-chart">
                  <div class="chart-bar" :style="{ height: '90%' }"></div>
                  <div class="chart-bar" :style="{ height: '85%' }"></div>
                  <div class="chart-bar" :style="{ height: '95%' }"></div>
                  <div class="chart-bar" :style="{ height: '88%' }"></div>
                </div>
              </div>
            </div>

            <div class="stat-card stat-in-progress">
              <div class="stat-content">
                <div class="stat-icon">🚚</div>
                <div class="stat-data">
                  <h3 class="stat-title">In Transit</h3>
                  <div class="stat-number">{{ stats.inProgress }}</div>
                </div>
              </div>
              <div class="stat-chart">
                <div class="mini-chart">
                  <div class="chart-bar" :style="{ height: '60%' }"></div>
                  <div class="chart-bar" :style="{ height: '75%' }"></div>
                  <div class="chart-bar" :style="{ height: '55%' }"></div>
                  <div class="chart-bar" :style="{ height: '70%' }"></div>
                </div>
              </div>
            </div>

            <div class="stat-card stat-pending">
              <div class="stat-content">
                <div class="stat-icon">⏰</div>
                <div class="stat-data">
                  <h3 class="stat-title">Pending</h3>
                  <div class="stat-number">{{ stats.pending }}</div>
                </div>
              </div>
              <div class="stat-chart">
                <div class="mini-chart">
                  <div class="chart-bar" :style="{ height: '40%' }"></div>
                  <div class="chart-bar" :style="{ height: '35%' }"></div>
                  <div class="chart-bar" :style="{ height: '45%' }"></div>
                  <div class="chart-bar" :style="{ height: '30%' }"></div>
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
           <button @click="viewCharts"
                  style="background: #ff6b9d; border: none; border-radius: 8px; padding: 15px 25px; color: white; transition: all 0.3s ease; cursor: pointer;"
                  onmouseover="this.style.background='#ff4d8d'"
                  onmouseout="this.style.background='#ff6b9d'">
            <h3 class="mb-0">Shipment Charts</h3>
            <p class="mb-0">Click to view more</p>
          </button>
        </div>

        <section class="tracking-section">

          <div class="section-column globe-column">
            <div class="section-card">
              <div class="card-header">
                <h3><span style="display:inline-block;width:10px;height:10px;background:red;border-radius:50%;margin-left:6px;"></span>
                 Live Tracking Map</h3>
                <div class="card-actions">
                  <span v-if="globeUpdateTimeout" class="globe-updating-indicator">
                    <i class="fa-spin">.</i> Updating...
                  </span>
                  <button class="btn-icon" @click="forceReinit" title="Refresh Globe">
                    X
                  </button>
                  <button class="btn-icon" v-if="selectedParcel" @click="clearRoute" title="Clear Route">
                    X
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div ref="globeContainer" class="globe-container" style="width: 100%; height: 400px;">
                  <div v-if="globeError" class="globe-error">
                    <div class="error-icon">
                      X
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
                  <h3> Recent Shipments</h3>
                </div>
              </div>
              <div class="card-body">
                <div class="parcels-list">
                  <div
                    v-for="parcel in parcels"
                    :key="parcel.id"
                    class="parcel-item"
                    :class="{ 'active': selectedParcel && selectedParcel.id === parcel.id }"
                    @click="selectParcel(parcel)"
                  >
                    <div class="parcel-icon">
                      <i class="fas fa-star"></i>
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
                          <span style="font-size:14px;">📍</span>
                          <span>{{ parcel.customer }}</span>
                        </div>
                        <div class="info-item">
                          <i class="fa-map-marker"></i>
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

                  <div v-if="parcels.length === 0" class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <p>No shipments found</p>
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
    viewHistory() {
      this.$router.push('/history');
    },
    viewCharts() {
      this.$router.push('/Charts');
    },
    checkAuthentication() {
      const userData = sessionStorage.getItem('currentUser');
      if (userData) {
        try {
          const user = JSON.parse(userData);
          this.user.email = user.email || user.display_name || 'User';
          this.isAuthenticated = true;
        } catch (error) {
          this.isAuthenticated = false;
        }
      } else {
        this.isAuthenticated = false;
      }
    },
    handleLoginStatusChange() {
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
      await this.fetchUserShipments();

      await this.$nextTick();
      await new Promise(resolve => setTimeout(resolve, 800));
      await this.initGlobe();
    },

    async fetchUserShipments() {
      this.loading = true;
      this.errorMessage = "";
      this.usingFallbackData = false;

      try {
        const response = await axios.post('/api/dashboard.php', {
          method: 'getAllMailByCustomerEmail',
          email: this.user.email
        });

        if (response.data.success) {
          if (response.data.note) {
            this.usingFallbackData = true;
          }

          this.parcels = this.transformShipmentData(response.data.shipments);
          this.updateStats();
          this.generateNotifications();

          if (this.globeInitialized && this.globe) {
            this.updateGlobeData();
          }
        } else {
          this.useFallbackData();
        }
      } catch (error) {
        this.useFallbackData();
      } finally {
        this.loading = false;
      }
    },

    useFallbackData() {
      this.usingFallbackData = true;
      this.errorMessage = 'Connected to demo data. Real shipments will appear here once you create them.';

      const exampleData = this.getExampleShipments(this.user.email);
      this.parcels = this.transformShipmentData(exampleData);
      this.updateStats();
      this.generateNotifications();

      if (this.globeInitialized && this.globe) {
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
        return [];
      }

      return shipments.map(shipment => {
        try {
          let senderCoords = this.getCountryCoordinates(shipment.senderAddress?.countryCode || 'SG');
          let recipientCoords = this.getCountryCoordinates(shipment.recipientAddress?.countryCode || 'US');

          if (!Array.isArray(senderCoords) || senderCoords.some(isNaN)) {
            senderCoords = [1.3521, 103.8198];
          }

          if (!Array.isArray(recipientCoords) || recipientCoords.some(isNaN)) {
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

          return transformedParcel;

        } catch (error) {
          return null;
        }
      }).filter(parcel => parcel !== null);
    },

    getCountryCoordinates(countryCode) {
      const code = countryCode === 'UK' ? 'GB' : countryCode;
      const country = countryData.find(c => c.code2 === code);
      if (country) {
        return [country.lat, country.long];
      }
      return [1.28478, 103.776222]; //Singapore
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
        const container = this.$refs.globeContainer;
        if (!container) {
          throw new Error('Globe not found');
        }

        const width = container.clientWidth || 800;
        const height = container.clientHeight || 400;

        if (width === 0 || height === 0) {
          throw new Error('Container has invalid dimensions');
        }

        container.innerHTML = '';

        this.globe = Globe()
          .globeImageUrl('https://unpkg.com/three-globe/example/img/earth-blue-marble.jpg')
          .backgroundColor('#000011')
          .width(width)
          .height(height)
          (container);

        this.globe.pointOfView({ lat: 20, lng: 0, altitude: 2 });

        this.globe.controls().enableZoom = true;
        this.globe.controls().autoRotate = false;
        this.globe.controls().autoRotateSpeed = 0.5;

        this.globeInitialized = true;
        this.globeError = false;

        if (this.parcels && this.parcels.length > 0) {
          await this.$nextTick();
          this.updateGlobeData();

          if (this.selectedParcel) {
            await this.$nextTick();
            this.updateGlobeRoute(this.selectedParcel);
          }
        }
      } catch (error) {
        this.globeError = true;
        this.globeInitialized = false;
      }
    },

    updateGlobeData() {
      if (!this.globe || !this.globeInitialized) {
        return;
      }

      if (!this.parcels || this.parcels.length === 0) {
        this.globe.pointsData([]);
        this.globe.arcsData([]);
        return;
      }

      try {
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

      } catch (error) {
      }
    },

    selectParcel(parcel) {
      this.selectedParcel = parcel;

      if (this.globeUpdateTimeout) {
        clearTimeout(this.globeUpdateTimeout);
      }

      if (this.globe && this.globeInitialized) {
        this.globeUpdateTimeout = setTimeout(() => {
          requestAnimationFrame(() => {
            this.updateGlobeRoute(parcel);
          });
        }, 100);
      }
    },

    updateGlobeRoute(parcel) {
      if (!this.globe || !this.globeInitialized) {
        return;
      }

      try {
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

      } catch (error) {
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
      } catch (error) {
      }
    },

    clearRoute() {
      this.selectedParcel = null;
      this.routeData = null;
      this.routeError = null;

      if (this.globe && this.globeInitialized) {
        try {
          this.arcsData = [];
          this.globe.arcsData(this.arcsData);
          this.globe.pointOfView({ lat: 20, lng: 0, altitude: 2 }, 1000);

          this.updateGlobeData();
        } catch (error) {
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
      window.dispatchEvent(new CustomEvent('showLoginModal'));
    }
  }
};
</script>

<style scoped>

:root {
  --hot-pink: #ff4275;
  --dark-pink: #ff759e;
  --pink: #ff9096;
  --dark-slate-blue: #455a64;
  --slate-blue: #8796b3;
  --light-pink: #ffe8ee;
  --pink-grey: #f1d9df;
}

.dashboard-wrapper {
  min-height: 100vh;
  background: linear-gradient(135deg, var(--light-pink) 0%, var(--pink-grey) 100%);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

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

.main-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem 2rem;
}

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

/* Rest of your existing styles remain unchanged */
.tracking-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  margin-bottom: 2rem;
}

.section-column {
  display: flex;
  flex-direction: column;
}

.section-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  overflow: hidden;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.card-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--pink-grey);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header h3 {
  margin: 0;
  color: var(--dark-slate-blue);
  font-size: 1.2rem;
  font-weight: 700;
}

.card-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-icon {
  background: var(--light-pink);
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  color: var(--dark-slate-blue);
}

.btn-icon:hover {
  background: var(--hot-pink);
  color: white;
}

.card-body {
  padding: 1.5rem;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.globe-container {
  position: relative;
  border-radius: 12px;
  overflow: hidden;
  background: #000011;
  flex: 1;
}

.globe-error,
.globe-loading {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 17, 0.8);
  color: white;
}

.error-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(255, 255, 255, 0.3);
  border-top: 4px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

.parcels-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  max-height: 400px;
  overflow-y: auto;
}

.parcel-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-radius: 12px;
  border: 2px solid transparent;
  cursor: pointer;
  transition: all 0.3s ease;
  background: var(--light-pink);
}

.parcel-item:hover {
  border-color: var(--hot-pink);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 66, 117, 0.2);
}

.parcel-item.active {
  border-color: var(--hot-pink);
  background: rgba(255, 66, 117, 0.1);
}

.parcel-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: var(--hot-pink);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1rem;
  flex-shrink: 0;
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
  font-weight: 700;
  color: var(--dark-slate-blue);
  margin: 0;
  font-size: 1rem;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-in-progress {
  background: rgba(255, 165, 0, 0.2);
  color: #ffa500;
}

.status-delivered {
  background: rgba(0, 255, 136, 0.2);
  color: #00ff88;
}

.status-pending {
  background: rgba(255, 66, 117, 0.2);
  color: var(--hot-pink);
}

.parcel-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  color: var(--slate-blue);
}

.parcel-progress {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-top: 0.5rem;
}

.progress-mini {
  flex: 1;
  height: 6px;
  background: var(--pink-grey);
  border-radius: 3px;
  overflow: hidden;
}

.progress-fill-mini {
  height: 100%;
  background: linear-gradient(90deg, var(--hot-pink), var(--dark-pink));
  border-radius: 3px;
  transition: width 0.5s ease;
}

.progress-text {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--dark-slate-blue);
  min-width: 40px;
}

.empty-state {
  text-align: center;
  padding: 3rem 2rem;
  color: var(--slate-blue);
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

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
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem;
  border-radius: 12px;
  background: var(--light-pink);
  transition: all 0.3s ease;
}

.notification-item:hover {
  transform: translateX(5px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.notification-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1rem;
  flex-shrink: 0;
}

.notification-icon.info {
  background: var(--hot-pink);
}

.notification-icon.success {
  background: #00ff88;
}

.notification-icon.warning {
  background: #ffa500;
}

.notification-content {
  flex: 1;
}

.notification-text {
  margin: 0 0 0.25rem;
  color: var(--dark-slate-blue);
  font-weight: 500;
}

.notification-time {
  font-size: 0.8rem;
  color: var(--slate-blue);
}

.empty-notifications {
  text-align: center;
  padding: 3rem 2rem;
  color: var(--slate-blue);
}

.empty-notifications i {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.login-required {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  padding: 2rem;
}

.login-message {
  text-align: center;
  max-width: 400px;
}

.message-icon {
  width: 80px;
  height: 80px;
  background: var(--light-pink);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
  color: var(--hot-pink);
  font-size: 2rem;
}

.login-message h2 {
  color: var(--dark-slate-blue);
  margin-bottom: 1rem;
}

.login-message p {
  color: var(--slate-blue);
  margin-bottom: 2rem;
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
  box-shadow: 0 4px 12px rgba(255, 66, 117, 0.3);
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@media (max-width: 768px) {
  .tracking-section {
    grid-template-columns: 1fr;
  }

  .header-content {
    flex-direction: column;
    text-align: center;
    gap: 1.5rem;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .stat-card {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }
}

@media (max-width: 480px) {
  .header-background {
    padding: 1.5rem;
  }

  .title-main {
    font-size: 2rem;
  }

  .title-sub {
    font-size: 1.4rem;
  }

  .header-stats {
    flex-direction: column;
    gap: 1rem;
  }
}
</style>
