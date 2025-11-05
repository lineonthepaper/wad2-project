<template>
  <div class="chatbot-page">
    <div class="page-header">
      <h1>Fluffy Shipping Assistant</h1>
      <p>Get instant smart answers to your shipping questions</p>
      <router-link to="/" class="back-link">← Back to Home</router-link>
    </div>

    <div class="chatbot-fullpage">
      <div class="chatbot-window">
        <div class="chatbot-header">
          <div class="header-info">
            <h2>Fluffy Shipping Assistant</h2>
            <div class="context-indicator" v-if="conversationContext.userNeeds.length > 0">
              <span> Understanding your needs...</span>
            </div>
          </div>
        </div>

        <div ref="messagesContainer" class="chat-messages">
          <div
            v-for="(message, index) in messages" :key="index" :class="['message', message.sender]">
            <div class="message-content">
              {{ message.text }}
              <div v-if="message.suggestions" class="suggestions">
                <button
                  v-for="(suggestion, sIndex) in message.suggestions" :key="sIndex" @click="sendSuggestion(suggestion)" class="suggestion-btn">
                  {{ suggestion }}
                </button>
              </div>
            </div>
          </div>

          <div v-if="isTyping" class="message bot typing">
            <div class="typing-dots">
              <span></span>
              <span></span>
              <span></span>
            </div>
          </div>
        </div>

        <div class="chat-input">
          <input
            v-model="userInput"
            @keypress.enter="sendMessage"
            placeholder="Ask anything about shipping, tracking, pricing..."
            type="text"
            class="input-field"
            ref="inputField"
          />
          <p class="input-hint">Press Enter to send </p>
        </div>
      </div>

      <div class="smart-sidebar">

        <div class="quick-questions">
          <h3>Quick Questions</h3>
          <div class="question-buttons">
            <button @click="askQuickQuestion('What shipping options do you offer and how fast are they?')" class="quick-btn">
              Shipping Speeds
            </button>
            <button @click="askQuickQuestion('How can I track my package and where do I find my tracking number?')" class="quick-btn">
              Track Package
            </button>
            <button @click="askQuickQuestion('How much does it cost to ship a 5kg package with Basic delivery?')" class="quick-btn">
              Get Price Quote
            </button>
            <button @click="askQuickQuestion('What is your contact information and support hours?')" class="quick-btn">
              Contact Support
            </button>
          </div>
        </div>

        <div class="stats-card" v-if="messages.length > 2">
          <h4>Conversation Insights</h4>
          <div class="stat-item">
            <span>Messages:</span>
            <span>{{ messages.length }}</span>
          </div>
          <div class="stat-item">
            <span>Topics discussed:</span>
            <span>{{ getDiscussedTopics().length }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'

// Chatbot state
const isTyping = ref(false)
const userInput = ref('')
const messages = ref([])
const messagesContainer = ref(null)
const inputField = ref(null)

const conversationContext = ref({
  lastTopic: null,
  userNeeds: [],
  discussedTopics: new Set()
})

const chatbotKnowledge = {
  "shipping": `**Shipping Options & Delivery Times**

We offer four main shipping services:

**Basic Mail**
• 5-24 days
• From $35.85
• Mail = documents

**Registered Mail**
• 7-26 days
• From $39.45
• Mail = documents

**Basic Package**
• 5-24 days
• From $38.30

**Registered Package**
• 7-26 days
• From $41.90

All packages include full tracking and customer support!`,

  "tracking": `**Package Tracking Guide**

**To track your package:**
Go to the customer dashboard page in the accounts dropdown, and view real-time where your parcels are currently.

**You'll see real-time updates:**
• Current location & status
• Estimated delivery date/time
• Delivery attempts & history

**Need help?** Contact support with your tracking number!`,

  "pricing": `**Shipping Pricing & Quotes**

Our pricing is simple and transparent:

**Based on:**
• Package weight & dimensions
• Delivery mode chosen
• Destination distance

**Get Exact Quote:**
Use our create shipment page to see the approximate price to ship your item overseas`,

  "contact": `**Contact Fluffy Shipping**

**Customer Support:**
Email: support@fluffyshipping.com
Phone: 1-800-SHIP-NOW (1-800-744-7669)
Live Chat: Available on website

**Business Hours:**
Monday-Friday: 9AM - 6PM
Saturday: 10AM - 4PM
Sunday: Emergency support only

**Office Address:**
123 Shipping Lane, Delivery City Singapore 10101

**Quick Help:**
Most tracking and billing questions can be solved instantly through our automated system!`
}


const smartKnowledgeBase = [
  {
    questions: [
      "how much does shipping cost", "what are your rates", "shipping prices",
      "how much to ship", "delivery cost", "price for shipping", "cost to deliver",
      "how much you charge", "shipping fees", "delivery charges"
    ],
    answer: chatbotKnowledge.pricing,
    topic: "pricing",
    suggestions: ["Get exact quote", "Registered Package Pricing", "Registered Mail pricing"]
  },
  {
    questions: [
      "where is my package", "track my order", "package status", "tracking number",
      "where's my shipment", "find my package", "package location", "delivery status",
      "when will it arrive", "track shipment"
    ],
    answer: chatbotKnowledge.tracking,
    topic: "tracking",
    suggestions: ["Need tracking help?", "Contact support", "Delivery issues"]
  },
  {
    questions: [
      "shipping options", "delivery times", "how long does shipping take",
      "express shipping", "same day delivery", "shipping speeds", "delivery options",
      "fastest shipping", "standard delivery time", "rush delivery"
    ],
    answer: chatbotKnowledge.shipping,
    topic: "shipping",
    suggestions: ["Time estimates"]
  },
  {
    questions: [
      "contact information", "phone number", "email address", "customer service",
      "support contact", "how to contact", "call support", "email support",
      "help number", "customer care"
    ],
    answer: chatbotKnowledge.contact,
    topic: "contact",
    suggestions: ["Live chat", "Business hours", "Office location"]
  },
]

onMounted(() => {
  addMessage('bot', `Welcome to your Smart Shipping Assistant!

I'm enhanced with AI capabilities to better understand your needs. I can help you with:

• Shipping options & delivery timeframes
• Package tracking & status updates
• Pricing quotes & cost estimates
• Contact details & support hours

Try asking naturally: "How much to ship a 5kg box?" or "Where's my package?"

What would you like to know today?`, [
    "Shipping options & times",
    "Track a package",
    "Get price estimate",
    "Contact support"
  ])

  nextTick(() => {
    if (inputField.value) {
      inputField.value.focus()
    }
  })
})

const sendMessage = () => {
  const userQuestion = userInput.value.trim()
  if (!userQuestion) return

  addMessage('user', userQuestion)
  userInput.value = ''

  updateContext(userQuestion)

  isTyping.value = true
  scrollToBottom()

  const processingTime = userQuestion.length > 50 ? 1500 : 1000

  setTimeout(() => {
    isTyping.value = false
    const { answer, suggestions } = generateSmartResponse(userQuestion)
    addMessage('bot', answer, suggestions)
  }, processingTime)
}

const sendSuggestion = (suggestion) => {
  userInput.value = suggestion
  sendMessage()
}

const askQuickQuestion = (question) => {
  userInput.value = question
  sendMessage()
}

const updateContext = (question) => {
  const lowerQuestion = question.toLowerCase()

  const needsMapping = {
    'track': 'tracking_help',
    'package': 'tracking_help',
    'where is': 'tracking_help',
    'cost': 'pricing_info',
    'price': 'pricing_info',
    'how much': 'pricing_info',
  }

  for (const [keyword, need] of Object.entries(needsMapping)) {
    if (lowerQuestion.includes(keyword) && !conversationContext.value.userNeeds.includes(need)) {
      conversationContext.value.userNeeds.push(need)
    }
  }

  const topic = detectTopic(lowerQuestion)
  if (topic) {
    conversationContext.value.discussedTopics.add(topic)
    conversationContext.value.lastTopic = topic
  }
}

const generateSmartResponse = (question) => {
  const lowerQuestion = question.toLowerCase()

  const faqMatch = findFAQMatch(lowerQuestion)
  if (faqMatch) {
    return enhanceWithContext(faqMatch.answer, faqMatch.topic, faqMatch.suggestions)
  }

  const keywordMatch = findEnhancedKeywordMatch(lowerQuestion)
  if (keywordMatch.answer) {
    return enhanceWithContext(keywordMatch.answer, keywordMatch.topic, keywordMatch.suggestions)
  }

  return handleUnknownQuestion(question)
}

const findFAQMatch = (question) => {
  let bestMatch = { score: 0, data: null }

  for (const item of smartKnowledgeBase) {
    for (const faqQuestion of item.questions) {
      const score = calculateSimilarity(question, faqQuestion)
      if (score > bestMatch.score) {
        bestMatch = { score, data: item }
      }
    }
  }

  return bestMatch.score > 0.4 ? bestMatch.data : null
}

const findEnhancedKeywordMatch = (question) => {
  const keywordWeights = {
    "track": { topic: "tracking", weight: 10 },
    "tracking": { topic: "tracking", weight: 10 },
    "where is": { topic: "tracking", weight: 9 },
    "package": { topic: "tracking", weight: 8 },
    "shipment": { topic: "tracking", weight: 8 },

    "cost": { topic: "pricing", weight: 10 },
    "price": { topic: "pricing", weight: 10 },
    "how much": { topic: "pricing", weight: 9 },
    "rate": { topic: "pricing", weight: 8 },

    "shipping": { topic: "shipping", weight: 10 },
    "delivery": { topic: "shipping", weight: 9 },
    "how long": { topic: "shipping", weight: 8 },
    "time": { topic: "shipping", weight: 7 },

    "contact": { topic: "contact", weight: 10 },
    "phone": { topic: "contact", weight: 9 },
    "email": { topic: "contact", weight: 8 },
  }

  let bestMatch = { topic: null, score: 0 }

  for (const [keyword, data] of Object.entries(keywordWeights)) {
    if (question.includes(keyword)) {
      if (data.weight > bestMatch.score) {
        bestMatch = { topic: data.topic, score: data.weight }
      }
    }
  }

  if (bestMatch.score > 5) {
    const faqItem = smartKnowledgeBase.find(item => item.topic === bestMatch.topic)
    return {
      answer: chatbotKnowledge[bestMatch.topic],
      topic: bestMatch.topic,
      suggestions: faqItem?.suggestions || []
    }
  }

  return { answer: null, topic: null, suggestions: [] }
}

const enhanceWithContext = (answer, topic, baseSuggestions = []) => {
  let enhancedAnswer = answer
  const suggestions = [...baseSuggestions]

  if (conversationContext.value.userNeeds.includes('tracking_help') && topic === 'tracking') {
    enhancedAnswer += "\n\n**Quick help:** Having trouble with tracking? Contact support with your tracking number!"
    suggestions.push("Contact support", "Tracking issues")
  }

  if (conversationContext.value.lastTopic && conversationContext.value.lastTopic !== topic) {
    const followUps = {
      'pricing': 'Would you like a specific price quote?',
      'tracking': 'Need help with tracking?',
      'shipping': 'Want to check where your parcel is exactly?',
    }

    if (followUps[topic]) {
      enhancedAnswer += `\n\n${followUps[topic]}`
    }
  }

  return { answer: enhancedAnswer, suggestions: suggestions.slice(0, 3) }
}

const calculateSimilarity = (str1, str2) => {
  const words1 = new Set(str1.split(/\s+/))
  const words2 = new Set(str2.split(/\s+/))
  const intersection = new Set([...words1].filter(x => words2.has(x)))
  return intersection.size / Math.max(words1.size, words2.size)
}

const detectTopic = (question) => {
  for (const item of smartKnowledgeBase) {
    for (const q of item.questions) {
      if (question.includes(q.split(' ')[0])) {
        return item.topic
      }
    }
  }
  return null
}

const handleUnknownQuestion = (question) => {
  console.log('Unknown question for learning:', question)

  const contextSuggestions = conversationContext.value.discussedTopics.size > 0
    ? `Based on our conversation, you might want to know more about: ${Array.from(conversationContext.value.discussedTopics).join(', ')}.`
    : ''

  return {
    answer: `I want to make sure I give you the best shipping information!

I couldn't find a specific answer for: "${question}"

${contextSuggestions}

I'm great at helping with:
• Shipping options & delivery times
• Package tracking & status
• Pricing
• Contact information

What specific shipping question can I help you with?`,
    suggestions: ["Shipping options", "Track package", "Get price", "Contact support"]
  }
}

const addMessage = (sender, text, suggestions = []) => {
  messages.value.push({
    sender,
    text,
    suggestions,
    timestamp: new Date()
  })
  scrollToBottom()
}

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })
}

const getDiscussedTopics = () => {
  return Array.from(conversationContext.value.discussedTopics)
}
</script>


<style scoped>
.chatbot-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 100%);
  padding: 20px;
}

.page-header {
  text-align: center;
  margin-bottom: 30px;
}

.page-header h1 {
  color: #ec4899;
  margin-bottom: 10px;
  font-size: 2.5rem;
  background: linear-gradient(135deg, #ec4899, #db2777);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.page-header p {
  color: #9d174d;
  font-size: 1.1rem;
  margin-bottom: 15px;
}

.back-link {
  color: #ec4899;
  text-decoration: none;
  font-weight: 500;
  padding: 8px 16px;
  border: 1px solid #ec4899;
  border-radius: 6px;
  transition: all 0.2s;
}

.back-link:hover {
  background: #ec4899;
  color: white;
  text-decoration: none;
}

.chatbot-fullpage {
  display: flex;
  gap: 30px;
  max-width: 1400px;
  margin: 0 auto;
  height: 75vh;
}

.chatbot-window {
  flex: 1;
  background: white;
  border: 1px solid #fbcfe8;
  border-radius: 16px;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 40px rgba(236, 72, 153, 0.1);
}

.chatbot-header {
  background: linear-gradient(135deg, #ec4899, #db2777);
  padding: 20px;
  border-radius: 16px 16px 0 0;
}

.header-info h2 {
  margin: 0;
  color: white;
  font-size: 1.5rem;
  font-weight: 600;
}

.header-info p {
  margin: 5px 0 0 0;
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.9rem;
}

.context-indicator {
  margin-top: 8px;
  padding: 6px 12px;
  background: rgba(255, 255, 255, 0.3);
  border-radius: 20px;
  font-size: 0.8rem;
}

.context-indicator span {
  color: white;
}

.chat-messages {
  flex: 1;
  padding: 20px;
  overflow-y: auto;
  background: #fdf2f8;
}

.message {
  margin: 16px 0;
  max-width: 85%;
  word-wrap: break-word;
}

.message.user {
  margin-left: auto;
}

.message.bot {
  margin-right: auto;
}

.message-content {
  padding: 16px 20px;
  border-radius: 18px;
  white-space: pre-line;
  line-height: 1.5;
  font-size: 14px;
  box-shadow: 0 2px 8px rgba(236, 72, 153, 0.1);
}

.message.user .message-content {
  background: linear-gradient(135deg, #ec4899, #db2777);
  color: white;
  border-bottom-right-radius: 6px;
}

.message.bot .message-content {
  background: white;
  color: #831843;
  border: 1px solid #fbcfe8;
  border-bottom-left-radius: 6px;
}

.suggestions {
  margin-top: 12px;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.suggestion-btn {
  background: rgba(236, 72, 153, 0.1);
  color: #ec4899;
  border: 1px solid rgba(236, 72, 153, 0.3);
  border-radius: 16px;
  padding: 6px 12px;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.2s;
}

.suggestion-btn:hover {
  background: #ec4899;
  color: white;
  transform: translateY(-1px);
}

.typing {
  color: #9d174d;
  font-style: italic;
}

.typing-dots {
  display: flex;
  gap: 4px;
  align-items: center;
}

.typing-dots span {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #ec4899;
  animation: typing 1.4s infinite ease-in-out;
}

.typing-dots span:nth-child(1) { animation-delay: -0.32s; }
.typing-dots span:nth-child(2) { animation-delay: -0.16s; }

@keyframes typing {
  0%, 80%, 100% { transform: scale(0.8); opacity: 0.5; }
  40% { transform: scale(1); opacity: 1; }
}

.chat-input {
  padding: 20px;
  border-top: 1px solid #fbcfe8;
  background: white;
  border-radius: 0 0 16px 16px;
}

.input-field {
  width: 100%;
  padding: 16px;
  border: 2px solid #fbcfe8;
  border-radius: 12px;
  font-size: 16px;
  outline: none;
  transition: all 0.2s;
  background: #fdf2f8;
  color: #831843;
}

.input-field:focus {
  border-color: #ec4899;
  background: white;
  box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.1);
}

.input-field::placeholder {
  color: #d946ef;
  opacity: 0.6;
}

.input-hint {
  font-size: 12px;
  color: #9d174d;
  margin: 12px 0 0 0;
  text-align: center;
}

.smart-sidebar {
  width: 320px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.capabilities-card, .quick-questions, .stats-card {
  background: white;
  border: 1px solid #fbcfe8;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 12px rgba(236, 72, 153, 0.05);
}

.capabilities-card h3, .quick-questions h3, .stats-card h4 {
  color: #ec4899;
  margin-bottom: 15px;
  font-size: 1.1rem;
}

.capabilities-card ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.capabilities-card li {
  padding: 6px 0;
  color: #9d174d;
  font-size: 14px;
}

.question-buttons {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.quick-btn {
  background: #fdf2f8;
  border: 1px solid #fbcfe8;
  border-radius: 10px;
  padding: 12px 15px;
  cursor: pointer;
  text-align: left;
  transition: all 0.2s;
  font-size: 14px;
  color: #831843;
}

.quick-btn:hover {
  background: #ec4899;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(236, 72, 153, 0.3);
}

.stats-card {
  background: linear-gradient(135deg, #fdf2f8, #fce7f3);
}

.stat-item {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid #fbcfe8;
  font-size: 14px;
  color: #831843;
}

.stat-item:last-child {
  border-bottom: none;
}

.chat-messages::-webkit-scrollbar {
  width: 8px;
}

.chat-messages::-webkit-scrollbar-track {
  background: #fce7f3;
  border-radius: 4px;
}

.chat-messages::-webkit-scrollbar-thumb {
  background: #ec4899;
  border-radius: 4px;
}

.chat-messages::-webkit-scrollbar-thumb:hover {
  background: #db2777;
}

@media (max-width: 1024px) {
  .chatbot-fullpage {
    flex-direction: column;
    height: auto;
  }

  .smart-sidebar {
    width: 100%;
    flex-direction: row;
    flex-wrap: wrap;
  }

  .capabilities-card, .quick-questions, .stats-card {
    flex: 1;
    min-width: 300px;
  }
}

@media (max-width: 768px) {
  .chatbot-page {
    padding: 10px;
  }

  .page-header h1 {
    font-size: 2rem;
  }

  .smart-sidebar {
    flex-direction: column;
  }
}
</style>
