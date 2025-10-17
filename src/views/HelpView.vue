<template>
  <div class="terms-container">
    <!-- Header Section -->
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

    <!-- Main Content -->
    <div class="container py-4">
      <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
          
          <!-- Quick Navigation - Mobile Only -->
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

          <!-- Acceptance Section -->
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

          <!-- Information Security -->
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

          <!-- Website Use -->
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

          <!-- Links -->
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

          <!-- Disclaimer -->
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

          <!-- Intellectual Property -->
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

          <!-- Governing Law -->
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

          <!-- Footer -->
          <section class="text-center py-4 mt-4 border-top">
            <p class="text-muted mb-2">
              &copy; 2025 Singapore Post Limited. All Rights Reserved.
            </p>
            <small class="text-muted">
              ezy2ship is a registered trademark of Singapore Post Limited.
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
    }
  },
  mounted() {
    // Add smooth scrolling behavior
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
  }
}
</script>

<style scoped>
.terms-container {
  background-color: #f8f9fa;
  min-height: 100vh;
}

.terms-section {
  background: white;
  padding: 1.5rem;
  border-radius: 10px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.section-title {
  color: #2c3e50;
  font-weight: 600;
  font-size: 1.4rem;
}

.terms-text {
  color: #495057;
  line-height: 1.6;
  margin-bottom: 1rem;
}

.agreement-item {
  background: #f8f9fa;
  border-radius: 8px;
  border-left: 4px solid #0d6efd;
  transition: all 0.3s ease;
}

.agreement-item:hover {
  background: #e9ecef;
  transform: translateX(5px);
}

.prohibited-item {
  background: #fff5f5;
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid #ffe3e3;
  display: flex;
  align-items: flex-start;
}

.link-card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.link-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.section-number {
  font-size: 1.1rem;
}

/* Mobile First Styles */
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
}

/* Tablet Styles */
@media (min-width: 577px) and (max-width: 768px) {
  .terms-section {
    padding: 1.25rem;
  }
  
  .section-title {
    font-size: 1.3rem;
  }
}

/* Desktop Styles */
@media (min-width: 769px) {
  .terms-section {
    padding: 2rem;
  }
}

/* Large Desktop */
@media (min-width: 1200px) {
  .terms-section {
    padding: 2.5rem;
  }
}

/* Print styles */
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
}

/* Ensure proper spacing for all devices */
.container {
  padding-left: 15px;
  padding-right: 15px;
}

/* Improve touch targets for mobile */
@media (max-width: 768px) {
  .agreement-item, .prohibited-item {
    min-height: 60px;
  }
  
  .btn, .form-select {
    min-height: 44px;
  }
}
</style>