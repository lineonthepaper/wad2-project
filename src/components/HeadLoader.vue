<script setup>
import { useHead } from '@unhead/vue'
import { routes } from '../router/routes.js'
import { watch } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
var title = ''

for (let r of routes) {
  if (r.path === route.path) {
    title = r.title
  }
}

useHead({
  meta: [
    { name: 'theme-color', content: '#0000FF', media: '(prefers-color-scheme: light)' },
    { name: 'theme-color', content: '#000000', media: '(prefers-color-scheme: dark)' },
    { name: 'color-scheme', content: 'light dark' },
    { name: 'viewport', content: 'width=device-width, initial-scale=1.0', charset: 'UTF-8' },
  ],
  link: [
    { rel: 'icon', href: '/public/favicon.ico' },
    { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
    { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
    {
      href: 'https://fonts.googleapis.com/css2?family=Jua&family=Quicksand:wght@300..700&display=swap',
      rel: 'stylesheet',
    },
  ],
  titleTemplate: `%s - Fluffy Shipping`,
})

const headTitle = useHead({
  title: title,
})

watch(
  () => route.fullPath,
  async () => {
    for (let r of routes) {
      if (r.path === route.path) {
        title = r.title
        headTitle.patch({ title: title })
      }
    }
  },
)
</script>

<script></script>
