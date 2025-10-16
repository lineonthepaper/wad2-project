<template>
  <div>
    <nav class="navbar fixed-top">
      <a href="#infosecurity" class="nav-icon">🔐</a>
      <a href="#websiteuse" class="nav-icon">🧭</a>
      <a href="#links" class="nav-icon">🔗</a>
      <a href="#disclaimer" class="nav-icon">⚖️</a>
    </nav>

    <section id="infosecurity" class="container py-5">
      <h3 class="mt-4 mb-4">Terms of use for ezy2ship website</h3>
      <p>
        By accessing this website, you are deemed to have accepted these terms and conditions...
      </p>
      <h5 class="mt-4">INFORMATION SECURITY</h5>
      <p>
        We use best efforts to ensure all data handled by this website is secure.
      </p>
    </section>

    <section id="websiteuse" class="container py-5">
      <h3 class="mb-4">Use of This Website</h3>
      <ul>
        <li>Do not hack or interfere with this website.</li>
        <li>Do not copy or distribute content without consent.</li>
        <li>Do not upload illegal or malicious content.</li>
      </ul>
    </section>

    <section id="links" class="container py-5">
      <h3 class="mb-4">Links to This Website</h3>
      <p>You may not create a link to any page without written consent.</p>
    </section>

    <section id="disclaimer" class="container py-5">
      <h3 class="mb-4">Disclaimer and Exclusion of Liability</h3>
      <p>This website and its services are provided “as is.”</p>
      <p>We disclaim all warranties and are not liable for any damages or losses.</p>
    </section>
  </div>
</template>

<script>
export default {
  name: "TermsAndConditions",
  mounted() {
    // enable Bootstrap JS functionality
    import("bootstrap/dist/js/bootstrap.bundle.min.js");
  }
};
</script>

<style scoped>
body {
  scroll-behavior: smooth;
}
.navbar {
  justify-content: center;
  background-color: white;
  padding: 1rem 0;
}
.nav-icon {
  font-size: 1.5rem;
  margin: 0 1rem;
  color: #0d6efd;
  transition: transform 0.2s;
}
.nav-icon:hover {
  transform: scale(1.2);
  color: #0056b3;
}
section {
  height: 100vh;
  padding: 2rem;
  border-bottom: 1px solid #ddd;
}
h3, h5 {
  border: none;
  text-decoration: none;
}
.container.py-5 {
  padding-top: 2rem;
  padding-bottom: 2rem;
}
</style>
