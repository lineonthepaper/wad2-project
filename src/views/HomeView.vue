<script setup>
import { RouterLink } from 'vue-router'
import { ref, onMounted } from 'vue'
import gsap from 'gsap'

const welcomeMessage = ref('')
const isLoggedIn = ref(false) // added to track login state

onMounted(() => {
  // Get user data from sessionStorage
  const userData = sessionStorage.getItem('currentUser')
  if (userData) {
    const user = JSON.parse(userData)
    // Use display_name (from database) or fall back to email
    const name = user.display_name || user.displayName
    //  ||  user.email
    welcomeMessage.value = `Welcome, ${name}!`
    isLoggedIn.value = true
  }

  // Listen for login status changes to update welcome message
  window.addEventListener('loginStatusChanged', () => {
    const userData = sessionStorage.getItem('currentUser')
    if (userData) {
      const user = JSON.parse(userData)
      const name = user.displayName || user.email
      welcomeMessage.value = `Welcome, ${name}!`
      isLoggedIn.value = true
    } else {
      welcomeMessage.value = ''
      isLoggedIn.value = false
    }
  })
})
</script>

<template>
  <div class="icon-nav" :class="[mobileView ? 'icon-nav-mobile' : 'icon-nav-pc']">
    <img
      :src="section.data.imgUrl"
      class="p-1"
      v-for="section in sections"
      :key="section.name"
      @click="changeSection(section.name)"
      :class="{ 'selected-img': currentSection === section.name }"
    />
  </div>
  <div class="w-100 px-0 vh-100 top-0 z-n1" :class="{ 'position-absolute': !mobileView }">
    <div class="p-0 w-50 home-block home-left vh-100" :class="{ 'd-none': mobileView }"></div>
    <div
      class="p-0 home-block home-right bg-light-pink vh-100 d-flex justify-content-center align-items-center flex-column"
      :class="[{ 'home-left': mobileView }, mobileView ? 'w-100' : 'w-50']"
    >
      <div
        class="p-3 m-5 rounded"
        :class="[mobileView ? 'bg-translucent' : 'bg-white']"
        v-if="welcomeMessage"
      >
        <h3 class="text-hot-pink">{{ welcomeMessage }}</h3>
      </div>
      <div
        class="p-5 m-5 rounded"
        id="displayCard"
        :class="[mobileView ? 'bg-translucent' : 'bg-white']"
      >
        <h1 class="jua text-hot-pink">
          {{ currentSectionData.title }}
        </h1>
        <h4 class="text-dark-slate-blue">
          {{ currentSectionData.subtitle }}
        </h4>
        <RouterLink :to="{ name: currentSectionData.link }">
          <button type="button" class="btn btn-pink next-btn py-2 my-2">
            {{ currentSection != 'main' ? 'Take me there!' : 'Start shipping today!' }}
          </button>
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<style scoped>
.home-block {
  box-sizing: border-box;
  float: left;
  max-width: 100%;
  overflow-x: hidden;
}

.home-left {
  background-image: url('airplane.png');
  background-position: center;
  background-size: cover;
  background-repeat: no-repeat;
  box-sizing: border-box;
}

.icon-nav-pc {
  position: absolute;
  width: 50px;
  height: fit-content;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  margin: auto;
}

.icon-nav-mobile {
  position: fixed;
  width: fit-content;
  height: 60px;
  top: auto;
  bottom: 0;
  left: 0;
  right: 0;
  margin: auto;
}

.icon-nav-mobile img {
  width: 60px;
}

.icon-nav-pc img {
  width: 50px;
}

.icon-nav img {
  cursor: pointer;
  filter: opacity(60%);
  transition: filter 0.5s;
}

.icon-nav img:hover,
.selected-img {
  filter: opacity(100%) !important;
}

.bg-translucent {
  background-color: rgba(255, 255, 255, 0.75);
}
</style>

<script>
export default {
  data() {
    const sections = [
      {
        name: 'main',
        data: {
          link: '',
          title: 'Fluffy Shipping',
          subtitle: 'Why fret when sending parcels? Singapore Post to the rescue!',
          alt: 'Fluffy Shipping Logo',
          imgUrl: '/fluffy_shipping_logo.png',
        },
      },
      {
        name: 'services',
        data: {
          link: 'services',
          title: 'Our Services',
          subtitle: 'No matter your destination, Fluffy Shipping can ship it!',
          alt: 'Hand holding up two gears.',
          imgUrl: '/home/services.png',
        },
      },
      {
        name: 'faq',
        data: {
          link: 'faq',
          title: 'FAQ',
          subtitle: 'Any questions? Check out these frequently asked questions!',
          alt: 'Two overlapping speech bubbles. The top bubble has a question mark in it.',
          imgUrl: '/home/faq.png',
        },
      },
      {
        name: 'shipment',
        data: {
          link: 'shipment',
          title: 'Create Shipment',
          subtitle: "Ready to ship with Fluffy Shipping? We're ready when you are!",
          alt: 'A moving delivery van. It has a check mark on it.',
          imgUrl: '/home/shipment.png',
        },
      },
    ]
    let mobileView = screen.width < 768
    return {
      sections,
      currentSection: 'main',
      mobileView,
    }
  },
  computed: {
    currentSectionData() {
      for (let section of this.sections) {
        if (section.name == this.currentSection) {
          return section.data
        }
      }
      return this.sections[0].data
    },
    currentSectionNumber() {
      for (let i = 0; i < this.sections.length; i++) {
        if (this.sections[i].name == this.currentSection) {
          return i
        }
      }
      return 0
    },
  },
  methods: {
    setOffScreen(element) {
      let rect = element.getBoundingClientRect()
      return {
        x: screen.width - rect.left + element.offsetWidth / 2,
        y: screen.height - rect.top + element.offsetHeight / 2,
      }
    },

    animateChangeSection() {
      let tl = gsap.timeline()
      let display = document.getElementById('displayCard')
      let offScreen = this.setOffScreen(display)
      tl.set(display, { x: offScreen.x, y: offScreen.y, rotate: '45deg', opacity: 0 })
      tl.to(display, {
        stagger: 0.25,
        duration: 1,
        x: 0,
        y: 0,
        ease: 'back.out(.5)',
        rotate: '0deg',
        opacity: 1,
      })
    },

    changeSection(sectionName) {
      setTimeout(() => {
        this.currentSection = sectionName
      }, 100)
      this.animateChangeSection()
    },

    updateMobileView() {
      this.mobileView = screen.width < 768
    },

    carousel() {
      setInterval(() => {
        let newSectionNumber = (this.currentSectionNumber + 1) % 4
        let newSection = this.sections[newSectionNumber]
        this.changeSection(newSection.name)
      }, 6000)
    },
  },
  created() {
    window.addEventListener('resize', this.updateMobileView)
    this.carousel()
  },
  unmounted() {
    window.removeEventListener('resize', this.updateMobileView)
  },
}
</script>
