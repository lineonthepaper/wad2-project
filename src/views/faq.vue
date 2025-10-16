<template>
  <div class="container py-4">
    <h2 class="text-center mb-4">Frequently Asked Questions</h2>

    <div class="faq-search">
      <input type="text" v-model="searchQuery" class="form-control" placeholder="Type your question..." />
    </div>

    <ul class="nav nav-tabs mt-4" id="faqTabs" role="tablist">
      <li class="nav-item" v-for="(tab, i) in tabs" :key="i">
        <button
          class="nav-link"
          :class="{ active: activeTab === tab.id }"
          @click="activeTab = tab.id"
        >
          {{ tab.label }}
        </button>
      </li>
    </ul>

    <div class="tab-content mt-3" id="faqContent">
      <div
        v-for="(tab, i) in tabs"
        :key="i"
        class="tab-pane fade"
        :class="{ 'show active': activeTab === tab.id }"
      >
        <div class="accordion" :id="'accordion' + tab.id">
          <div
            v-for="(item, j) in filteredQuestions(tab.questions)"
            :key="j"
            class="accordion-item"
          >
            <h2 class="accordion-header">
              <button
                class="accordion-button collapsed custom-toggle"
                type="button"
                data-bs-toggle="collapse"
                :data-bs-target="'#' + tab.id + j"
              >
                {{ item.q }}
              </button>
            </h2>
            <div :id="tab.id + j" class="accordion-collapse collapse">
              <div class="accordion-body" v-html="item.a"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <a href="quickguide.pdf" download>
      <button class="btn btn-primary need-help-btn">Download Quick Guide</button>
    </a>
  </div>
</template>

<script>
export default {
  name: "FAQ",
  data() {
    return {
      searchQuery: "",
      activeTab: "gettingStarted",
      tabs: [
        {
          id: "gettingStarted",
          label: "Getting Started",
          questions: [
            { q: "What are the requirements to use ezy2ship?", a: "You need a working PC with internet connection and a printer." },
            { q: "Which operating systems and browsers do ezy2ship support?", a: "ezy2ship supports Windows XP/7, Mac OS X, IE, Firefox, Chrome, Safari." },
            { q: "How do I ship as a non-registered consumer?", a: 'Click “Ship as Guest” on <a href="https://www.ezy2ship.net/" target="_blank">www.ezy2ship.net</a>' },
            { q: "What is the difference between “Sign Up” and “Ship as Guest”?", a: "Signed up accounts enjoy Address Book, Bulk Upload, and Tracking features." }
          ]
        },
        {
          id: "payment",
          label: "Payment",
          questions: [
            { q: "How do I proceed after payment?", a: "Click “Done” after payment; do not close the browser." },
            { q: "Can I get a refund after payment?", a: "No refund once booking is confirmed and paid." },
            { q: "Why must I top up the difference?", a: "Different scales may vary; ours are certified annually." }
          ]
        },
      ]
    };
  },
  methods: {
    filteredQuestions(questions) {
      const query = this.searchQuery.toLowerCase();
      return questions.filter(q => q.q.toLowerCase().includes(query));
    }
  }
};
</script>

<style scoped>
body {
  font-family: Arial, sans-serif;
}

li {
  font-size: 15px;
}
.faq-search {
  max-width: 500px;
  margin: 20px auto;
}
.accordion-button::after {
  display: none;
}
.custom-toggle {
  position: relative;
  padding-right: 2rem;
}
.custom-toggle::before {
  content: '+';
  position: absolute;
  right: 1rem;
  font-size: 1.2rem;
  color: #333;
  transition: all 0.2s ease;
}
.custom-toggle:not(.collapsed)::before {
  content: '–';
}
.need-help-btn {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 1000;
}
</style>
