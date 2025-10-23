<template>
  <hr />
  <div class="row bg-light-pink justify-content-center airplane-header">
    <div class="col-lg-4 col-md-6 col-sm-8 py-2 text-center">
      <h1 class="jua text-hot-pink">Parcel Tracking Dashboard</h1>
    </div>
  </div>
  <hr />
  <div class="dashboard-container">
    <div class="main-content">
      <header class="dashboard-header">
        <div class="header-left">
          <h1>Customer(name) Dashboard</h1>
          <div class="live-badge">
            <span class="live-dot"></span> LIVE
          </div>
        </div>
        <!-- <div class="header-right">
          <select v-model="selectedPeriod" class="form-select">
            <option value="today">Today</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
          </select> -->
         <!-- <button class="btn btn-secondary mt-3 w-100" @click="toggleTheme">
        Toggle {{ theme === 'light' ? 'Dark' : 'Light' }} Mode // not working
      </button> -->
        <!-- </div> -->
      </header>

      <!-- Debug info -->
      <!-- <div class="debug-info" v-if="showDebug">
        <p>Globe Status: {{ globeInitialized ? 'Initialized' : globeError ? 'Error' : 'Loading...' }}</p>
        <p>Selected Parcel: {{ selectedParcel ? selectedParcel.trackingId : 'None' }}</p>
        <button @click="forceReinit" class="btn btn-sm btn-warning">Reinitialize Globe</button>
        <button @click="showDebug = false" class="btn btn-sm btn-secondary">Hide Debug</button>
      </div> -->

      <!-- Stats cards -->
      <section class="stats-cards">
        <div class="stat-card">
          <h3>In Progress</h3>
          <p>{{ stats.inProgress }}</p>
        </div>
        <div class="stat-card">
          <h3>Delivered</h3>
          <p>{{ stats.delivered }}</p>
        </div>
        <div class="stat-card">
          <h3>Pending</h3>
          <p>{{ stats.pending }}</p>
        </div>
      </section>

      <!-- Globe Section -->
      <section class="globe-section">
        <div class="globe-header">
          <h3>Live Parcel Tracking</h3>
          <div class="globe-controls">
            <button v-if="selectedParcel" @click="clearRoute" class="btn btn-sm btn-outline-secondary">
              Clear Route
            </button>
            <!-- <button @click="showDebug = !showDebug" class="btn btn-sm btn-outline-info">
              {{ showDebug ? 'Hide' : 'Show' }} Debug
            </button> -->
          </div>
        </div>
        <div ref="globeContainer" class="globe-container">
          <div v-if="globeError" class="globe-error">
            <p>⚠️ Globe failed to load. Please refresh the page.</p>
            <p class="error-details">{{ errorMessage }}</p>
          </div>
          <div v-else-if="!globeInitialized" class="globe-loading">
            <p>Loading globe visualization...</p>
          </div>
        </div>
        <div v-if="selectedParcel" class="globe-info">
          <div class="route-info">
            <div class="route-point">
              <span class="point-label start">Start</span>
              <strong>{{ getLocationName(selectedParcel.location) }}</strong>
              ({{ formatCoordinates(selectedParcel.location) }})
            </div>
            <div class="route-point">
              <span class="point-label current">Current</span>
              <strong>{{ getLocationName(selectedParcel.currentLocation || selectedParcel.location) }}</strong>
              ({{ formatCoordinates(selectedParcel.currentLocation || selectedParcel.location) }})
            </div>
            <div class="route-point">
              <span class="point-label end">End</span>
              <strong>{{ getLocationName(selectedParcel.destination) }}</strong>
              ({{ formatCoordinates(selectedParcel.destination) }})
            </div>
          </div>
          <div class="progress-info" v-if="selectedParcel.status === 'In Progress'">
            <div class="progress-bar">
              <div class="progress-fill" :style="{ width: calculateProgress(selectedParcel) + '%' }"></div>
            </div>
            <span class="progress-text">{{ Math.round(calculateProgress(selectedParcel)) }}% Complete</span>
          </div>
        </div>
      </section>

      <!-- Parcel list -->
      <section class="parcel-list">
        <h2 class="mb-3">Recent Shipments</h2>
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle">
            <thead>
              <tr>
                <th scope="col">Tracking ID</th>
                <th scope="col">Customer</th>
                <th scope="col">Status</th>
                <th scope="col">Expected Delivery</th>
                <th scope="col">Current Location</th>
                <th scope="col">Progress</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="parcel in parcels"
                :key="parcel.id"
                :class="{ 'table-active': selectedParcel && selectedParcel.id === parcel.id }"
              >
                <td>
                  <a href="#" @click.prevent="showParcelRoute(parcel)" class="tracking-link">
                    {{ parcel.trackingId }}
                  </a>
                </td>
                <td>{{ parcel.customer }}</td>
                <td>
                  <span
                    class="badge"
                    :class="{
                      'bg-warning text-dark': parcel.status === 'In Progress',
                      'bg-success': parcel.status === 'Delivered',
                      'bg-danger': parcel.status === 'Pending'
                    }"
                  >
                    {{ parcel.status }}
                  </span>
                </td>
                <td>{{ formatDate(parcel.expectedDelivery) }}</td>
                <td>{{ getLocationName(parcel.currentLocation || parcel.location) }}</td>
                <td>
                  <div v-if="parcel.status === 'In Progress'" class="progress-cell">
                    <div class="progress-bar-sm">
                      <div class="progress-fill-sm" :style="{ width: calculateProgress(parcel) + '%' }"></div>
                    </div>
                    <small>{{ Math.round(calculateProgress(parcel)) }}%</small>
                  </div>
                  <span v-else class="text-muted">-</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </div>
</template>

<script>
import Globe from 'globe.gl';

export default {
  name: "ParcelDashboardGlobe",
  data() {
    return {
      selectedPeriod: "today",
      selectedParcel: null,
      globeInitialized: false,
      globeError: false,
      showDebug: true,
      errorMessage: "",
      stats: {
        inProgress: 12,
        delivered: 48,
        pending: 5,
      },
      parcels: [
        {
          id: 1,
          trackingId: "P12345",
          customer: "Alice",
          status: "In Progress",
          expectedDelivery: "2025-10-18",
          location: [37.7749, -122.4194], // San Francisco (Start)
          currentLocation: [39.8283, -98.5795], // Kansas (Current - midway)
          destination: [40.7128, -74.0060] // New York (End)
        },
        {
          id: 2,
          trackingId: "P12346",
          customer: "Bob",
          status: "Delivered",
          expectedDelivery: "2025-10-15",
          location: [51.5074, -0.1278], // London (Start)
          currentLocation: [48.8566, 2.3522], // Paris (Current/Final)
          destination: [48.8566, 2.3522] // Paris (End)
        },
        {
          id: 3,
          trackingId: "P12347",
          customer: "Charlie",
          status: "Pending",
          expectedDelivery: "2025-10-20",
          location: [35.6895, 139.6917], // Tokyo (Start)
          currentLocation: [35.6895, 139.6917], // Tokyo (Current - not moved)
          destination: [34.0522, -118.2437] // Los Angeles (End)
        },
        {
          id: 4,
          trackingId: "P12348",
          customer: "David",
          status: "In Progress",
          expectedDelivery: "2025-10-19",
          location: [48.8566, 2.3522], // Paris (Start)
          currentLocation: [45.4642, 9.1900], // Milan (Current)
          destination: [41.9028, 12.4964] // Rome (End)
        },
      ],
      globe: null,
      arcsData: [],
      pointsData: []
    };
  },
  mounted() {
    console.log('Component mounted, initializing globe...');
    this.$nextTick(() => {
      setTimeout(() => {
        this.initGlobe();
      }, 100);
    });
  },
  methods: {
    async initGlobe() {
      try {
        console.log('Starting globe initialization...');

        const container = this.$refs.globeContainer;
        if (!container) {
          throw new Error('Globe container not found');
        }

        const width = container.clientWidth || 800;
        const height = container.clientHeight || 500;

        console.log('Container dimensions:', width, 'x', height);

        // Initialize globe with basic settings
        this.globe = Globe()
          .globeImageUrl('https://unpkg.com/three-globe/example/img/earth-blue-marble.jpg')
          .backgroundColor('#000011')
          .width(width)
          .height(height)(container);

        console.log('Basic globe created');

        // Add initial data
        this.updateGlobeData();

        this.globeInitialized = true;
        console.log('Globe initialized successfully');

      } catch (error) {
        console.error('Globe initialization error:', error);
        this.globeError = true;
        this.errorMessage = error.message;
      }
    },

    updateGlobeData() {
      if (!this.globe) return;

      try {
        // Update points data - show all parcels as points
        this.pointsData = this.parcels.map(parcel => ({
          ...parcel,
          // For display, use current location if available, otherwise start location
          displayLocation: parcel.currentLocation || parcel.location
        }));

        this.globe
          .pointsData(this.pointsData)
          .pointLat(d => d.displayLocation[0])
          .pointLng(d => d.displayLocation[1])
          .pointColor(d => this.getStatusColor(d.status))
          .pointAltitude(0.1)
          .pointRadius(0.4)
          .pointLabel(d => `${d.trackingId}: ${d.customer} (${d.status})`);

        // Update arcs if we have a selected parcel
        if (this.selectedParcel) {
          this.updateRouteForParcel(this.selectedParcel);
        }

        console.log('Globe data updated successfully');

      } catch (error) {
        console.error('Error updating globe data:', error);
        this.globeError = true;
        this.errorMessage = error.message;
      }
    },

    showParcelRoute(parcel) {
      if (!this.globe) {
        console.error('Globe not ready');
        return;
      }

      this.selectedParcel = parcel;
      this.updateRouteForParcel(parcel);
    },

    updateRouteForParcel(parcel) {
      if (!this.globe) return;

      // Create multiple arcs to show the complete journey
      this.arcsData = [
        // Arc from start to current location (green - completed path)
        {
          start: parcel.location,
          end: parcel.currentLocation || parcel.location,
          color: '#00ff00',
          stroke: 1.5
        },
        // Arc from current location to destination (orange - remaining path)
        {
          start: parcel.currentLocation || parcel.location,
          end: parcel.destination,
          color: '#ffa500',
          stroke: 1.5
        }
      ];

      // Update the globe with new arcs
      this.globe
        .arcsData(this.arcsData)
        .arcStartLat(d => d.start[0])
        .arcStartLng(d => d.start[1])
        .arcEndLat(d => d.end[0])
        .arcEndLng(d => d.end[1])
        .arcColor(d => d.color)
        .arcStroke(d => d.stroke)
        .arcAltitude(0.05)
        .arcDashLength(0.3)
        .arcDashGap(0.1)
        .arcDashAnimateTime(4000);

      // Focus camera on the entire route
      this.focusOnRoute(parcel.location, parcel.destination);
    },

    focusOnRoute(start, end) {
      if (!this.globe) return;

      const midLat = (start[0] + end[0]) / 2;
      const midLng = (start[1] + end[1]) / 2;

      // Calculate a good altitude to show the entire route
      const latDiff = Math.abs(start[0] - end[0]);
      const lngDiff = Math.abs(start[1] - end[1]);
      const maxDiff = Math.max(latDiff, lngDiff);
      const altitude = Math.max(1.5, 3 - maxDiff * 0.5);

      this.globe.pointOfView({ lat: midLat, lng: midLng, altitude });
    },

    clearRoute() {
      if (this.globe) {
        this.arcsData = [];
        this.selectedParcel = null;
        this.globe.arcsData(this.arcsData);
        this.globe.pointOfView({ lat: 0, lng: 0, altitude: 1.8 });
        // Refresh points data
        this.updateGlobeData();
      }
    },

    calculateProgress(parcel) {
      if (parcel.status === 'Delivered') return 100;
      if (parcel.status === 'Pending') return 0;

      // Simple linear progress calculation based on distance
      const start = parcel.location;
      const current = parcel.currentLocation || parcel.location;
      const end = parcel.destination;

      const totalDistance = this.calculateDistance(start, end);
      const traveledDistance = this.calculateDistance(start, current);

      if (totalDistance === 0) return 0;

      return Math.min(100, (traveledDistance / totalDistance) * 100);
    },

    calculateDistance(coord1, coord2) {
      // Haversine distance calculation
      const [lat1, lon1] = coord1;
      const [lat2, lon2] = coord2;

      const R = 6371; // Earth's radius in km
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
      // Simple coordinate to city name mapping
      const locations = {
        '37.7749,-122.4194': 'San Francisco',
        '40.7128,-74.0060': 'New York',
        '51.5074,-0.1278': 'London',
        '48.8566,2.3522': 'Paris',
        '35.6895,139.6917': 'Tokyo',
        '34.0522,-118.2437': 'Los Angeles',
        '41.9028,12.4964': 'Rome',
        '39.8283,-98.5795': 'Kansas',
        '45.4642,9.1900': 'Milan'
      };

      const key = `${coords[0]},${coords[1]}`;
      return locations[key] || 'Unknown Location';
    },

    getStatusColor(status) {
      const colors = {
        'In Progress': '#ffa500',
        'Delivered': '#00ff00',
        'Pending': '#ff4444'
      };
      return colors[status] || '#cccccc';
    },

    formatCoordinates(coords) {
      if (!coords) return 'N/A';
      return `${coords[0].toFixed(2)}°, ${coords[1].toFixed(2)}°`;
    },

    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString();
    },

    forceReinit() {
      this.globeInitialized = false;
      this.globeError = false;
      this.globe = null;
      this.arcsData = [];
      this.selectedParcel = null;

      this.$nextTick(() => {
        setTimeout(() => {
          this.initGlobe();
        }, 100);
      });
    }
  }
};
</script>

<style scoped>

/* Light/Dark mode background for main content */
/* Main content theme colors */


.dashboard-container {
  display: flex;
  min-height: 100vh;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.main-content {
  flex: 1;
  width: 100%;
  padding: 1.5rem;
  background-color: #f5f6fa;
}

.debug-info {
  background: #fff3cd;
  border: 1px solid #ffeaa7;
  border-radius: 4px;
  padding: 0.5rem;
  margin-bottom: 1rem;
  font-size: 0.9rem;
}

.debug-info p {
  margin: 0.2rem 0;
}

/* Header */
.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

/* Stats cards */
.stats-cards {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  flex: 1;
  background-color: #fff;
  padding: 1.5rem;
  border-radius: 8px;
  text-align: center;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Globe section */
.globe-section {
  margin-bottom: 2rem;
  background: #fff;
  border-radius: 8px;
  padding: 1rem;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.globe-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.globe-controls {
  display: flex;
  gap: 0.5rem;
}

.globe-container {
  width: 100%;
  height: 500px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  background: #000011;
  position: relative;
}

.globe-loading, .globe-error {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #666;
  font-size: 1.1rem;
}

.globe-error {
  color: #e74c3c;
  background: #ffeaea;
}

.error-details {
  font-size: 0.8rem;
  color: #999;
  margin-top: 0.5rem;
}

.globe-info {
  margin-top: 1rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 4px;
  font-size: 0.9rem;
}

.route-info {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 0.5rem;
}

.route-point {
  flex: 1;
  text-align: center;
  padding: 0.5rem;
  border-radius: 4px;
  background: white;
}

.point-label {
  display: block;
  font-size: 0.8rem;
  font-weight: bold;
  margin-bottom: 0.2rem;
  padding: 0.2rem 0.5rem;
  border-radius: 3px;
  color: white;
}

.point-label.start {
  background: #28a745;
}

.point-label.current {
  background: #17a2b8;
}

.point-label.end {
  background: #dc3545;
}

.progress-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-top: 0.5rem;
}

.progress-bar {
  flex: 1;
  height: 8px;
  background: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #28a745, #17a2b8);
  transition: width 0.3s ease;
}

.progress-text {
  font-weight: bold;
  color: #495057;
  min-width: 80px;
}

/* Parcel list */
.parcel-list {
  background: #fff;
  border-radius: 8px;
  padding: 1.5rem;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.tracking-link {
  color: #3498db;
  text-decoration: none;
  font-weight: 500;
}

.tracking-link:hover {
  color: #2980b9;
  text-decoration: underline;
}

.table-active {
  background-color: #e8f4fd !important;
}

.progress-cell {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.progress-bar-sm {
  flex: 1;
  height: 4px;
  background: #e9ecef;
  border-radius: 2px;
  overflow: hidden;
  min-width: 50px;
}

.progress-fill-sm {
  height: 100%;
  background: #17a2b8;
  transition: width 0.3s ease;
}

/* Live badge */
.live-badge {
  display: inline-flex;
  align-items: center;
  background-color: #e74c3c;
  color: #fff;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  margin-left: 1rem;
  font-weight: bold;
  font-size: 0.9rem;
}

.live-dot {
  width: 8px;
  height: 8px;
  background-color: #fff;
  border-radius: 50%;
  margin-right: 0.4rem;
  animation: pulse 1.5s infinite;
}


/* Responsive */
@media (max-width: 768px) {
  .route-info {
    flex-direction: column;
    gap: 0.5rem;
  }

  .stats-cards {
    flex-direction: column;
  }

  .globe-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }

  .globe-controls {
    width: 100%;
    justify-content: flex-start;
  }
}
</style>
