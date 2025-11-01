<template>
  <div class="success-container">
    <div class="success-card">
      <div class="success-icon">
        <i class="fas fa-check-circle"></i>
      </div>
      
      <h1>Order Confirmed!</h1>
      <p class="success-message">Your shipment has been created successfully.</p>
      
      <div class="order-details" v-if="orderData">
        <div class="detail-row">
          <span>Order Number:</span>
          <strong>#{{ orderData.mail_id }}</strong>
        </div>
        <div class="detail-row">
          <span>Tracking Number:</span>
          <strong>{{ orderData.tracking_number }}</strong>
        </div>
        <div class="detail-row">
          <span>Total Amount:</span>
          <strong>${{ orderData.total_amount }}</strong>
        </div>
      </div>

      <div class="success-actions">
        <button @click="$router.push('/dashboard')" class="btn-primary">
          <i class="fas fa-tachometer-alt"></i>
          Go to Dashboard
        </button>
        <button @click="$router.push('/track')" class="btn-secondary">
          <i class="fas fa-search"></i>
          Track Shipment
        </button>
        <button @click="$router.push('/create-shipment')" class="btn-outline">
          <i class="fas fa-plus"></i>
          Create New Shipment
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CheckoutSuccess',
  data() {
    return {
      orderData: null
    };
  },
  mounted() {
    // You can load order data from route params or session storage
    this.loadOrderData();
  },
  methods: {
    loadOrderData() {
      // This would typically come from the checkout process
      const order = sessionStorage.getItem('lastOrder');
      if (order) {
        this.orderData = JSON.parse(order);
      }
    }
  }
};
</script>

<style scoped>
.success-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  padding: 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.success-card {
  background: white;
  padding: 3rem;
  border-radius: 20px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
  text-align: center;
  max-width: 500px;
  width: 100%;
}

.success-icon {
  font-size: 4rem;
  color: #28a745;
  margin-bottom: 1.5rem;
}

.success-card h1 {
  color: #2c3e50;
  margin-bottom: 1rem;
}

.success-message {
  color: #6c757d;
  font-size: 1.1rem;
  margin-bottom: 2rem;
}

.order-details {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 10px;
  margin-bottom: 2rem;
  text-align: left;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  padding: 0.5rem 0;
  border-bottom: 1px solid #e9ecef;
}

.detail-row:last-child {
  border-bottom: none;
  margin-bottom: 0;
}

.success-actions {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.btn-primary, .btn-secondary, .btn-outline {
  padding: 1rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-primary {
  background: #007bff;
  color: white;
}

.btn-primary:hover {
  background: #0056b3;
  transform: translateY(-2px);
}

.btn-secondary {
  background: #28a745;
  color: white;
}

.btn-secondary:hover {
  background: #218838;
  transform: translateY(-2px);
}

.btn-outline {
  background: transparent;
  color: #007bff;
  border: 2px solid #007bff;
}

.btn-outline:hover {
  background: #007bff;
  color: white;
  transform: translateY(-2px);
}

@media (max-width: 768px) {
  .success-card {
    padding: 2rem;
    margin: 1rem;
  }
  
  .success-actions {
    gap: 0.75rem;
  }
  
  .btn-primary, .btn-secondary, .btn-outline {
    padding: 0.875rem 1rem;
  }
}
</style>