<template>
  <hr/>
  <div class="row bg-light-pink justify-content-center airplane-header">
    <div class="col-lg-4 col-md-6 col-sm-8 py-2 text-center">
      <h1 class="jua text-hot-pink">Dashboard</h1>
    </div>
  </div>
  <hr />

  <!-- Update to handle array of mail items -->
  <div v-if="mail && mail.length > 0">
    <div v-for="mailItem in mail" :key="mailItem.mailId" class="mail-item mb-3 p-3 border rounded">
      <p><strong>Mail ID:</strong> {{ mailItem.mailId }}</p>
      <p><strong>Customer Email:</strong> {{ mailItem.customerEmail }}</p>
      <p><strong>Parcel Height:</strong> {{ mailItem.parcelHeight }} cm</p>
      <p><strong>Service:</strong> {{ mailItem.serviceName }} ({{ mailItem.serviceType }})</p>
    </div>
  </div>
  <div v-else-if="mail && mail.length === 0">
    <p>No mail data found.</p>
  </div>
  <div v-else>
    <p>Loading mail data...</p>
  </div>
</template>

<script>
export default {
  data() {
    return {
      mail: null
    };
  },
  mounted() {
    const customerEmail = 'muhheeow@fakeemail.com';

    fetch('../api/mail.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        method: 'getAllMailByCustomerEmail',
        customerEmail: customerEmail
      })
    })
    .then(response => {
      if (!response.ok) throw new Error('Network response was not ok');
      return response.json();
    })
    .then(data => {
      console.log("Full response:", data);

      if (data.mail) {
        this.mail = data.mail;
      } else {
        this.mail = [];
        console.error("No mail data found:", data.message);
      }
    })
    .catch(error => {
      console.error("Error fetching mail data:", error);
      this.mail = [];
    });
  }
};
</script>
