<template>
  <div class="container-fluid">
    <hr />
    <div class="row bg-light-pink justify-content-center airplane-header">
      <div class="col-lg-3 col-md-6 col-sm-6 py-2 text-center">
        <h1 class="jua text-hot-pink">Cart</h1>
      </div>
    </div>
    <hr />

    <!-- <div class="row">
      <button @click="console.log(cart.$state)">Cart state</button>
      <button @click="cart.shipments.push(shipment.$state)">Add shipment to cart</button>
      <button @click="console.log(shipment.$state)">Shipment state</button>
    </div> -->

    <div
      class="row justify-content-center"
      v-for="(shipment, index) in cart.shipments"
      :key="index"
    >
      <div class="col-lg-10 border">sdfsdfds</div>
    </div>
  </div>
</template>

<script setup>
import { useShipmentStore } from '@/stores/shipment'
import { useCartStore } from '@/stores/cart'
</script>

<script>
export default {
  data() {
    const shipment = useShipmentStore()
    const cart = useCartStore()
    return {
      shipment,
      cart,
    }
  },
}
</script>
