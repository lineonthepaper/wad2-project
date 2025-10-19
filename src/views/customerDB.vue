<template>
   <hr/>
  <div class="row bg-light-pink justify-content-center airplane-header">
    <div class="col-lg-4 col-md-6 col-sm-8 py-2 text-center">
      <h1 class="jua text-hot-pink">Dashboard</h1>
    </div>
  </div>
  <hr />

  <div v-if="mail">
    <p>Mail ID: {{ mail.mailId }}
{{ mail.customerEmail }}
{{ mail.parcelHeight }}
</p>
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

    fetch('../api/mail.php')
      .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
      })
      .then(data => {
        this.mail = data;
        console.log("Mail data:", data);
      })
      .catch(error => console.error("Error fetching mail data:", error));
  }
};
</script>
