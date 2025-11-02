<template>
  <div class="mode-toggle-container">
    <button 
      class="btn mode-toggle-btn"
      @click="cycleMode"
      :title="`Current mode: ${modeLabels[currentMode]}. Click to switch to ${modeLabels[(currentMode + 1) % 3]}`"
    >
      <i class="bi" :class="modeIcons[currentMode]"></i>
      <span class="mode-label">{{ modeLabels[currentMode] }}</span>
    </button>
  </div>

  <header>
      <hr />
      <div class="row bg-light-pink justify-content-center airplane-header">
        <div class="col-lg-4 col-md-6 col-sm-8 py-2 text-center">
          <h1 class="jua text-hot-pink">Help</h1>
        </div>
      </div>
      <hr />
  </header>

  <div class="terms-container" :class="`mode-${currentMode}`">
    <section class="container py-4 py-lg-5 border-bottom">
      <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
          <div class="text-center text-md-start">
            <h1 class="display-6 fw-bold text-primary mb-3">Terms of Use</h1>
            <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start justify-content-center justify-content-md-start text-muted mb-3">
            </div>
          </div>
          <div class="alert alert-light border" role="alert">
            <div class="d-flex">
              <i class="bi bi-info-circle-fill text-primary me-3 mt-1"></i>
              <p class="mb-0 fw-medium">
                By accessing this website, you are deemed to have accepted these terms and conditions.
                We reserve the right to amend these terms and conditions from time to time.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <div class="container py-4">
      <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">

          <div class="d-lg-none mb-4">
            <div class="card">
              <div class="card-header bg-light">
                <h6 class="mb-0">Quick Navigation</h6>
              </div>
              <div class="card-body p-2">
                <select class="form-select" @change="scrollToSection($event)">
                  <option value="">Jump to section...</option>
                  <option value="#acceptance">1. Acceptance</option>
                  <option value="#security">2. Information Security</option>
                  <option value="#usage">3. Website Usage</option>
                  <option value="#links">4. Links Policy</option>
                  <option value="#disclaimer">5. Disclaimer</option>
                  <option value="#ip">6. Intellectual Property</option>
                  <option value="#law">7. Governing Law</option>
                </select>
              </div>
            </div>
          </div>

          <section id="acceptance" class="terms-section mb-4 mb-lg-5">
            <div class="row align-items-center mb-3">
              <div class="col-2 col-sm-1">
                <div class="section-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 40px; height: 40px;">
                  <span class="fw-bold">1</span>
                </div>
              </div>
              <div class="col-10 col-sm-11">
                <h3 class="section-title mb-0">Acceptance of Terms</h3>
              </div>
            </div>
            <p class="terms-text">
              By accessing and using the fluffy shipping website, you unconditionally accept and agree to be bound by these Terms of Use.
              If you disagree with any of these terms, you must immediately discontinue your access to this website and your use of the services offered.
            </p>
          </section>

          <section id="security" class="terms-section mb-4 mb-lg-5">
            <div class="row align-items-center mb-3">
              <div class="col-2 col-sm-1">
                <div class="section-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 40px; height: 40px;">
                  <span class="fw-bold">2</span>
                </div>
              </div>
              <div class="col-10 col-sm-11">
                <div class="d-flex align-items-center">
                  <i class="bi bi-shield-check text-primary me-3 d-none d-sm-block"></i>
                  <h3 class="section-title mb-0">Information Security</h3>
                </div>
              </div>
            </div>

            <div class="card border-0 bg-light mb-4">
              <div class="card-body">
                <p class="terms-text mb-3">
                  We use best efforts to ensure that all data handled by this website, including your data and information,
                  login process, and all transactions are subject to security measures as mandated by relevant authorities
                  and compliance standards such as PCI DSS.
                </p>
                <p class="terms-text mb-0 fst-italic">
                  However, we do not warrant that the information provided on this website is complete, accurate or error-free.
                </p>
              </div>
            </div>

            <h5 class="mt-4 mb-3 fw-semibold text-center text-md-start">You agree to the following:</h5>
            <div class="agreement-list">
              <div class="agreement-item d-flex flex-column flex-md-row mb-3 p-3">
                <div class="d-flex align-items-start mb-2 mb-md-0 me-md-3">
                  <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                  <strong class="me-2">Confidentiality:</strong>
                </div>
                <div class="flex-grow-1">
                  Not to reveal to anyone your fluffy shipping account username, password and/or any identifier number allocated by SingPost.
                </div>
              </div>
              <div class="agreement-item d-flex flex-column flex-md-row mb-3 p-3">
                <div class="d-flex align-items-start mb-2 mb-md-0 me-md-3">
                  <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                  <strong class="me-2">Responsibility:</strong>
                </div>
                <div class="flex-grow-1">
                  To be fully responsible for all transactions resulting from the use of your account credentials.
                </div>
              </div>
              <div class="agreement-item d-flex flex-column flex-md-row mb-3 p-3">
                <div class="d-flex align-items-start mb-2 mb-md-0 me-md-3">
                  <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                  <strong class="me-2">Notification:</strong>
                </div>
                <div class="flex-grow-1">
                  To immediately inform us if you suspect any unauthorized access or compromise of your account.
                </div>
              </div>
            </div>
          </section>

          <section id="usage" class="terms-section mb-4 mb-lg-5">
            <div class="row align-items-center mb-3">
              <div class="col-2 col-sm-1">
                <div class="section-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 40px; height: 40px;">
                  <span class="fw-bold">3</span>
                </div>
              </div>
              <div class="col-10 col-sm-11">
                <div class="d-flex align-items-center">
                  <i class="bi bi-laptop text-primary me-3 d-none d-sm-block"></i>
                  <h3 class="section-title mb-0">Use of This Website</h3>
                </div>
              </div>
            </div>

            <div class="prohibited-actions">
              <h5 class="text-danger mb-3 text-center text-md-start">You must not:</h5>
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <div class="prohibited-item h-100">
                    <i class="bi bi-x-circle-fill text-danger me-2"></i>
                    <span>Attempt to change, add to, remove, deface, hack or otherwise interfere with this website</span>
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <div class="prohibited-item h-100">
                    <i class="bi bi-x-circle-fill text-danger me-2"></i>
                    <span>Distribute, modify, copy, or create derivative works without our written consent</span>
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <div class="prohibited-item h-100">
                    <i class="bi bi-x-circle-fill text-danger me-2"></i>
                    <span>Access or use this website in any way that could damage or impair the website</span>
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <div class="prohibited-item h-100">
                    <i class="bi bi-x-circle-fill text-danger me-2"></i>
                    <span>Upload any illegal, unlawful, or damaging material</span>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <section id="links" class="terms-section mb-4 mb-lg-5">
            <div class="row align-items-center mb-3">
              <div class="col-2 col-sm-1">
                <div class="section-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 40px; height: 40px;">
                  <span class="fw-bold">4</span>
                </div>
              </div>
              <div class="col-10 col-sm-11">
                <h3 class="section-title mb-0">Links Policy</h3>
              </div>
            </div>

            <div class="row g-4">
              <div class="col-12 col-lg-6">
                <div class="card h-100 border link-card">
                  <div class="card-header bg-transparent border-bottom-0 text-center">
                    <i class="bi bi-link-45deg text-primary fs-2 mb-2"></i>
                    <h5 class="mb-0">Links to This Website</h5>
                  </div>
                  <div class="card-body text-center">
                    <p class="terms-text mb-0">
                      You may not create a link to any page of this website without our prior written consent.
                      Any links created are at your own risk.
                    </p>
                  </div>
                </div>
              </div>
              <div class="col-12 col-lg-6">
                <div class="card h-100 border link-card">
                  <div class="card-header bg-transparent border-bottom-0 text-center">
                    <i class="bi bi-box-arrow-up-right text-primary fs-2 mb-2"></i>
                    <h5 class="mb-0">Links from This Website</h5>
                  </div>
                  <div class="card-body text-center">
                    <p class="terms-text mb-0">
                      We do not monitor or review content of third-party websites linked from our site.
                      Access to such sites is at your own risk.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <section id="disclaimer" class="terms-section mb-4 mb-lg-5">
            <div class="row align-items-center mb-3">
              <div class="col-2 col-sm-1">
                <div class="section-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 40px; height: 40px;">
                  <span class="fw-bold">5</span>
                </div>
              </div>
              <div class="col-10 col-sm-11">
                <div class="d-flex align-items-center">
                  <i class="bi bi-exclamation-triangle text-warning me-3 d-none d-sm-block"></i>
                  <h3 class="section-title mb-0">Disclaimer and Exclusion of Liability</h3>
                </div>
              </div>
            </div>

            <div class="disclaimer-content">
              <div class="alert alert-warning d-flex align-items-start" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-3 mt-1 flex-shrink-0"></i>
                <div>
                  <h6 class="alert-heading mb-2">Important Notice</h6>
                  This website and all services are provided on an "as is, as available" basis without any warranties.
                </div>
              </div>

              <div class="mt-4">
                <h5 class="fw-semibold mb-3 text-center text-md-start">General Disclaimer</h5>
                <p class="terms-text">
                  To the fullest extent permitted by applicable law, we disclaim all representations and warranties
                  relating to this website and its contents. We do not warrant that this website will always be
                  accessible, uninterrupted, timely, secure, or error-free.
                </p>
              </div>

              <div class="mt-4 p-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded text-center text-md-start">
                <div class="d-flex align-items-center justify-content-center justify-content-md-start mb-2">
                  <i class="bi bi-dash-circle-fill text-danger me-2"></i>
                  <h6 class="text-danger fw-bold mb-0">LIMITATION OF LIABILITY</h6>
                </div>
                <p class="terms-text mb-0">
                  WE SHALL NOT BE LIABLE for any direct, indirect, incidental, special, consequential or punitive damages
                  resulting from or in connection with your use of this website.
                </p>
              </div>
            </div>
          </section>

          <section id="ip" class="terms-section mb-4 mb-lg-5">
            <div class="row align-items-center mb-3">
              <div class="col-2 col-sm-1">
                <div class="section-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 40px; height: 40px;">
                  <span class="fw-bold">6</span>
                </div>
              </div>
              <div class="col-10 col-sm-11">
                <h3 class="section-title mb-0">Intellectual Property Rights</h3>
              </div>
            </div>
            <p class="terms-text">
              All content on this website is protected by copyright laws and belongs to us and/or our suppliers.
              No content may be copied, reproduced, or distributed without express permission.
            </p>
            <div class="row mt-3 g-3">
              <div class="col-12 col-md-4 text-center">
                <i class="bi bi-c-circle text-primary fs-1 d-block mb-2"></i>
                <small class="text-muted">Copyright Protected</small>
              </div>
              <div class="col-12 col-md-4 text-center">
                <i class="bi bi-trademark text-primary fs-1 d-block mb-2"></i>
                <small class="text-muted">Trademark Rights</small>
              </div>
              <div class="col-12 col-md-4 text-center">
                <i class="bi bi-file-earmark-lock text-primary fs-1 d-block mb-2"></i>
                <small class="text-muted">Content Licensing</small>
              </div>
            </div>
          </section>

          <section id="law" class="terms-section mb-4 mb-lg-5">
            <div class="row align-items-center mb-3">
              <div class="col-2 col-sm-1">
                <div class="section-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 40px; height: 40px;">
                  <span class="fw-bold">7</span>
                </div>
              </div>
              <div class="col-10 col-sm-11">
                <h3 class="section-title mb-0">Governing Law</h3>
              </div>
            </div>
            <div class="card border-primary">
              <div class="card-body text-center">
                <i class="bi bi-geo-alt-fill text-primary fs-1 mb-3 d-block"></i>
                <p class="terms-text mb-0">
                  These Terms of Use shall be governed by and construed in accordance with the laws of the Republic of Singapore.
                  You agree to submit to the exclusive jurisdiction of the courts of the Republic of Singapore.
                </p>
              </div>
            </div>
          </section>

          <section class="text-center py-4 mt-4 border-top">
            <p class="text-muted mb-2">
              &copy; 2025 WAD2 Project. All Rights Reserved.
            </p>
            <small class="text-muted">
              Fluffy Shipping is a registered trademark of WAD2 project.
            </small>
          </section>

        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TermsAndConditions',
  data() {
    return {
      currentMode: 0,
      modeLabels: ['Original', 'Dark Mode', 'Light Perception'],
      modeIcons: ['bi-palette', 'bi-moon', 'bi-eye']
    }
  },
  methods: {
    scrollToSection(event) {
      const sectionId = event.target.value;
      if (sectionId) {
        const target = document.querySelector(sectionId);
        if (target) {
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      }
    },
    cycleMode() {
      this.currentMode = (this.currentMode + 1) % 3;
      this.applyMode();
    },
    applyMode() {
      document.body.classList.remove('mode-0', 'mode-1', 'mode-2');
      document.body.classList.add(`mode-${this.currentMode}`);
    }
  },
  mounted() {
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(link => {
      link.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      });
    });
    this.applyMode();
  }
}
</script>

<style scoped>
:root {
  --hot-pink: #ff4275;
  --dark-pink: #ff759e;
  --pink: #ff9096;
  --light-pink: #fff9fb;
  --lighter-pink: #ffeef2;
}

.mode-toggle-container {
  position: fixed;
  bottom: 20px;
  right: 20px; 
  z-index: 1000;
}

.mode-toggle-btn {
  background: var(--hot-pink);
  color: white;
  border: none;
  border-radius: 25px;
  padding: 10px 15px;
  box-shadow: 0 4px 12px rgba(255, 66, 117, 0.3);
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
}

.mode-toggle-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(255, 66, 117, 0.4);
  background: var(--dark-pink);
  color: white;
}

.mode-label {
  font-size: 0.9rem;
}

.terms-container.mode-0 {
  background-color: var(--light-pink);
  min-height: 100vh;
}

.terms-container.mode-0 .terms-section {
  background: white;
  padding: 1.5rem;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(255, 66, 117, 0.1);
  border-left: 4px solid var(--pink);
}

.terms-container.mode-0 .section-title {
  color: var(--hot-pink);
}

.terms-container.mode-0 .terms-text {
  color: #495057;
}

.terms-container.mode-0 .agreement-item {
  background: var(--light-pink);
  border-left: 4px solid var(--dark-pink);
}

.terms-container.mode-0 .agreement-item:hover {
  background: var(--lighter-pink);
}

.terms-container.mode-0 .prohibited-item {
  background: #fff5f7;
  border: 1px solid #ffe3e9;
}

.terms-container.mode-0 .card {
  background: white;
}

.terms-container.mode-0 .alert-light {
  background-color: #fff5f7;
  border-color: var(--pink);
}

.terms-container.mode-0 .text-primary {
  color: var(--hot-pink) !important;
}

.terms-container.mode-0 .text-muted {
  color: #6c757d !important;
}

.terms-container.mode-1 {
  background-color: #1a1a1a;
  min-height: 100vh;
  color: #e0e0e0;
}

.terms-container.mode-1 .terms-section {
  background: #2d2d2d;
  padding: 1.5rem;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
  border-left: 4px solid #555;
}

.terms-container.mode-1 .section-title {
  color: #ffffff;
}

.terms-container.mode-1 .terms-text {
  color: #b0b0b0;
}

.terms-container.mode-1 .agreement-item {
  background: #3a3a3a;
  border-left: 4px solid #666;
  color: #e0e0e0;
}

.terms-container.mode-1 .agreement-item:hover {
  background: #444;
}

.terms-container.mode-1 .prohibited-item {
  background: #333;
  border: 1px solid #555;
  color: #e0e0e0;
}

.terms-container.mode-1 .card {
  background: #2d2d2d;
  border-color: #444;
  color: #e0e0e0;
}

.terms-container.mode-1 .alert-light {
  background: #333;
  border-color: #555;
  color: #e0e0e0;
}

.terms-container.mode-1 .text-primary {
  color: #ffffff !important;
}

.terms-container.mode-1 .text-muted {
  color: #888 !important;
}

.terms-container.mode-1 .bg-light {
  background-color: #333 !important;
}

.terms-container.mode-1 .border {
  border-color: #444 !important;
}

.terms-container.mode-1 .border-bottom {
  border-bottom-color: #444 !important;
}

.terms-container.mode-1 .border-top {
  border-top-color: #444 !important;
}

.terms-container.mode-1 .card-header.bg-light {
  background-color: #333 !important;
}

.terms-container.mode-1 .card.border-0.bg-light {
  background-color: #333 !important;
}

.terms-container.mode-1 .alert-warning {
  background-color: #332b00;
  border-color: #665800;
  color: #e0e0e0;
}

.terms-container.mode-1 .bg-danger.bg-opacity-10 {
  background-color: rgba(139, 0, 0, 0.2) !important;
  border-color: rgba(178, 34, 34, 0.4) !important;
}

.terms-container.mode-2 {
  background-color: #fffff0; 
  min-height: 100vh;
  color: #2c2c2c;
  font-weight: 500;
}

.terms-container.mode-2 .terms-section {
  background: #ffffff;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border-left: 4px solid #4a4a4a;
  border: 2px solid #333;
}

.terms-container.mode-2 .section-title {
  color: #000000;
  font-weight: 700;
}

.terms-container.mode-2 .terms-text {
  color: #2c2c2c;
  font-weight: 500;
  line-height: 1.8;
}

.terms-container.mode-2 .agreement-item {
  background: #f8f8f8;
  border-left: 4px solid #666;
  border: 1px solid #333;
  font-weight: 500;
}

.terms-container.mode-2 .agreement-item:hover {
  background: #eeeeee;
}

.terms-container.mode-2 .prohibited-item {
  background: #fffaf0;
  border: 2px solid #333;
  font-weight: 500;
  color: #2c2c2c;
}

.terms-container.mode-2 .card {
  background: #ffffff;
  border: 2px solid #333;
  color: #2c2c2c;
  font-weight: 500;
}

.terms-container.mode-2 .alert-light {
  background: #fffaf0;
  border: 2px solid #333;
  color: #2c2c2c;
  font-weight: 500;
}

.terms-container.mode-2 .text-primary {
  color: #000000 !important;
  font-weight: 700;
}

.terms-container.mode-2 .text-muted {
  color: #555 !important;
  font-weight: 500;
}

.terms-container.mode-2 .bg-light {
  background-color: #f8f8f8 !important;
}

.terms-container.mode-2 .border {
  border-color: #333 !important;
}

.terms-container.mode-2 .border-bottom {
  border-bottom-color: #333 !important;
}

.terms-container.mode-2 .border-top {
  border-top-color: #333 !important;
}

.terms-container.mode-2 .card-header.bg-light {
  background-color: #f0f0f0 !important;
}

.terms-container.mode-2 .card.border-0.bg-light {
  background-color: #f8f8f8 !important;
  border: 2px solid #333 !important;
}

.terms-container.mode-2 .alert-warning {
  background-color: #fff8e1;
  border: 2px solid #ffa000;
  color: #2c2c2c;
  font-weight: 500;
}

.terms-container.mode-2 .bg-danger.bg-opacity-10 {
  background-color: rgba(255, 235, 235, 0.9) !important;
  border: 2px solid #d32f2f !important;
}

.terms-container.mode-2 .btn,
.terms-container.mode-2 .form-select {
  border: 2px solid #333;
  font-weight: 500;
}

.terms-container.mode-2 .form-select:focus {
  border-color: #000;
  box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.25);
}

.terms-container {
  background-color: var(--light-pink);
  min-height: 100vh;
}

.terms-section {
  background: white;
  padding: 1.5rem;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(255, 66, 117, 0.1);
  border-left: 4px solid var(--pink);
}

.section-title {
  color: var(--hot-pink);
  font-weight: 600;
  font-size: 1.4rem;
}

.terms-text {
  color: #495057;
  line-height: 1.6;
  margin-bottom: 1rem;
}

.agreement-item {
  background: var(--light-pink);
  border-radius: 8px;
  border-left: 4px solid var(--dark-pink);
  transition: all 0.3s ease;
}

.agreement-item:hover {
  background: var(--lighter-pink);
  transform: translateX(5px);
}

.prohibited-item {
  background: #fff5f7;
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid #ffe3e9;
  display: flex;
  align-items: flex-start;
}

.link-card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  border-color: var(--pink);
}

.link-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 15px rgba(255, 66, 117, 0.2);
}

.section-number {
  background-color: var(--hot-pink) !important;
  font-size: 1.1rem;
}

.text-primary {
  color: var(--hot-pink) !important;
}

.border-primary {
  border-color: var(--hot-pink) !important;
}

.bg-primary {
  background-color: var(--hot-pink) !important;
}

.card-header.bg-light {
  background-color: var(--lighter-pink) !important;
}

.alert-light {
  background-color: #fff5f7;
  border-color: var(--pink);
}

.card.border-0.bg-light {
  background-color: var(--lighter-pink) !important;
  border-left: 4px solid var(--dark-pink) !important;
}

.alert-warning {
  background-color: #fff9e6;
  border-color: #ffd966;
}

.alert-warning .bi-exclamation-triangle-fill {
  color: #ffc107;
}

.card .card-header.bg-light {
  background-color: var(--lighter-pink) !important;
  border-bottom: 1px solid var(--pink);
}

.form-select {
  border-color: var(--pink);
  box-shadow: 0 2px 4px rgba(255, 66, 117, 0.1);
}

.form-select:focus {
  border-color: var(--hot-pink);
  box-shadow: 0 2px 8px rgba(255, 66, 117, 0.2);
}

.bg-danger.bg-opacity-10 {
  background-color: rgba(255, 66, 117, 0.1) !important;
  border-color: rgba(255, 66, 117, 0.3) !important;
}

.text-danger {
  color: var(--hot-pink) !important;
}

.bi-dash-circle-fill.text-danger {
  color: var(--hot-pink) !important;
}

.bi-check-circle-fill.text-success {
  color: #28a745 !important;
}

.bi-exclamation-triangle.text-warning {
  color: #ffc107 !important;
}

.bi-x-circle-fill.text-danger {
  color: var(--hot-pink) !important;
}

@media (max-width: 576px) {
  .terms-section {
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 8px;
  }

  .section-title {
    font-size: 1.25rem;
  }

  .agreement-item {
    padding: 0.75rem;
  }

  .prohibited-item {
    padding: 0.75rem;
    font-size: 0.9rem;
  }
  
  .mode-toggle-btn {
    padding: 8px 12px;
    font-size: 0.8rem;
  }
  
  .mode-label {
    display: none; 
  }
}

@media (min-width: 577px) and (max-width: 768px) {
  .terms-section {
    padding: 1.25rem;
  }

  .section-title {
    font-size: 1.3rem;
  }
}

@media (min-width: 769px) {
  .terms-section {
    padding: 2rem;
  }
}

@media (min-width: 1200px) {
  .terms-section {
    padding: 2.5rem;
  }
}

@media print {
  .terms-container {
    background: white;
  }

  .terms-section {
    box-shadow: none;
    border: 1px solid #dee2e6;
    page-break-inside: avoid;
  }

  .agreement-item, .prohibited-item {
    background: white !important;
    border: 1px solid #dee2e6 !important;
  }
  
  .mode-toggle-container {
    display: none;
  }
}

.container {
  padding-left: 15px;
  padding-right: 15px;
}

@media (max-width: 768px) {
  .agreement-item, .prohibited-item {
    min-height: 60px;
  }

  .btn, .form-select {
    min-height: 44px;
  }
}

.border-bottom {
  border-bottom-color: var(--pink) !important;
}

.border-top {
  border-top-color: var(--pink) !important;
}

.terms-container.mode-1 header {
  background-color: #1a1a1a;
  border-color: #444;
}

.terms-container.mode-1 .bg-light-pink {
  background-color: #333 !important;
}

.terms-container.mode-1 .text-hot-pink {
  color: #ffffff !important;
}

.terms-container.mode-1 hr {
  border-color: #444 !important;
}

.terms-container.mode-2 header {
  background-color: #fffff0;
  border-color: #333;
}

.terms-container.mode-2 .bg-light-pink {
  background-color: #f8f8f8 !important;
}

.terms-container.mode-2 .text-hot-pink {
  color: #000000 !important;
  font-weight: 700;
}

.terms-container.mode-2 hr {
  border-color: #333 !important;
}
</style>