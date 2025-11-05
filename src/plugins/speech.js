// plugins/speech.js
const SpeechToTextPlugin = {
  install(app, options = {}) {
    const defaultOptions = {
      buttonColor: '#3c4247ff',
      buttonPosition: 'right',
      lang: 'en-US',
      autoStart: false,
      enabledPages: ['/services', '/chatbot', '/history', '/faq'],
      ...options
    };

    let currentInput = null;
    let isListening = false;
    let recognition = null;
    let observer = null;

    const isPageEnabled = () => {
      if (!defaultOptions.enabledPages || defaultOptions.enabledPages.length === 0) return true;
      const path = window.location.pathname;
      return defaultOptions.enabledPages.some(page => path.includes(page) || path === page);
    };

    const isSupported = () => {
      return ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) && isPageEnabled();
    };

    const initializeRecognition = () => {
      if (!isSupported()) return null;
      const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
      recognition = new SpeechRecognition();
      recognition.continuous = false;
      recognition.interimResults = false;
      recognition.lang = defaultOptions.lang;
      return recognition;
    };

    // Initialize recognition immediately
    recognition = initializeRecognition();

    const micIcon = `
      <svg width="18" height="18" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 15C13.6569 15 15 13.6569 15 12V5C15 3.34315 13.6569 2 12 2C10.3431 2 9 3.34315 9 5V12C9 13.6569 10.3431 15 12 15Z" stroke="white" stroke-width="2"/>
        <path d="M19 12V13C19 15.973 16.8377 18.441 14 18.917M5 12V13C5 15.973 7.16229 18.441 10 18.917" stroke="white" stroke-width="2" stroke-linecap="round"/>
        <path d="M12 19V22" stroke="white" stroke-width="2" stroke-linecap="round"/>
      </svg>
    `;

    const recordingIcon = `
      <svg width="18" height="18" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
        <rect x="8" y="8" width="8" height="8" rx="2" fill="#f44336"/>
      </svg>
    `;

    const stopListening = () => {
      isListening = false;
      if (recognition) recognition.stop();
    };

    const startListening = (input, button) => {
      if (isListening || !recognition) return;
      currentInput = input;
      isListening = true;

      recognition.onresult = (event) => {
        const transcript = event.results[0][0].transcript;
        if (currentInput) {
          const cursor = currentInput.selectionStart;
          const val = currentInput.value;
          currentInput.value = val.slice(0, cursor) + transcript + val.slice(cursor);
          currentInput.dispatchEvent(new Event('input', { bubbles: true }));
          currentInput.dispatchEvent(new Event('change', { bubbles: true }));
          currentInput.focus();
        }
      };

      recognition.onerror = (event) => {
        console.error('Speech recognition error:', event.error);
        stopListening();
        if (button) button.innerHTML = micIcon;
      };

      recognition.onend = () => {
        isListening = false;
        if (button) button.innerHTML = micIcon;
        if (button) button.style.background = defaultOptions.buttonColor;
      };

      try {
        recognition.start();
      } catch (error) {
        console.error('Failed to start speech recognition:', error);
        isListening = false;
        if (button) button.innerHTML = micIcon;
      }
    };

    const toggleListening = (input, button) => {
      if (isListening) {
        stopListening();
        button.innerHTML = micIcon;
        button.style.background = defaultOptions.buttonColor;
      } else {
        startListening(input, button);
        button.innerHTML = recordingIcon;
        button.style.background = '#f44336';
      }
    };

    const createMicButton = (input) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.innerHTML = micIcon;
      button.style.cssText = `
        position: absolute;
        ${defaultOptions.buttonPosition === 'right' ? 'right' : 'left'}: 8px;
        top: 50%;
        transform: translateY(-50%);
        background: ${defaultOptions.buttonColor};
        border: none;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0.8;
        transition: all 0.3s ease;
        z-index: 1000;
        padding: 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      `;

      button.addEventListener('mouseenter', () => {
        button.style.opacity = '1';
        button.style.transform = 'translateY(-50%) scale(1.1)';
      });

      button.addEventListener('mouseleave', () => {
        button.style.opacity = '0.8';
        button.style.transform = 'translateY(-50%) scale(1)';
      });

      button.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        toggleListening(input, button);
      });

      return button;
    };

    const enhanceInput = (input) => {
      if (input.dataset.speechEnhanced) return;
      if (!isPageEnabled()) return;

      // Check if already wrapped
      if (input.parentNode.querySelector('button[data-speech-button]')) return;

      const wrapper = document.createElement('div');
      wrapper.style.position = 'relative';
      wrapper.style.display = 'inline-block';
      wrapper.style.width = '100%';

      input.parentNode.insertBefore(wrapper, input);
      wrapper.appendChild(input);

      const micButton = createMicButton(input);
      micButton.setAttribute('data-speech-button', 'true');
      wrapper.appendChild(micButton);

      input.dataset.speechEnhanced = 'true';
      
      // Add padding to input to prevent text overlap
      if (defaultOptions.buttonPosition === 'right') {
        input.style.paddingRight = '45px';
      } else {
        input.style.paddingLeft = '45px';
      }
    };

    const enhanceAllInputs = () => {
      // Reinitialize recognition on page change
      recognition = initializeRecognition();

      if (!isPageEnabled()) {
        // Remove all mic buttons if page is not enabled
        document.querySelectorAll('[data-speech-button]').forEach(button => {
          button.remove();
        });
        document.querySelectorAll('[data-speech-enhanced]').forEach(input => {
          delete input.dataset.speechEnhanced;
          if (defaultOptions.buttonPosition === 'right') {
            input.style.paddingRight = '';
          } else {
            input.style.paddingLeft = '';
          }
        });
        return;
      }

      const inputs = document.querySelectorAll(`
        input[type="text"],
        input[type="search"],
        input[type="email"],
        input[type="url"],
        input[type="tel"],
        textarea:not([data-no-speech]),
        [contenteditable="true"]:not([data-no-speech])
      `);

      inputs.forEach(input => {
        if (!input.disabled && !input.readOnly) enhanceInput(input);
      });
    };

    const initObserver = () => {
      if (observer) observer.disconnect();
      
      observer = new MutationObserver((mutations) => {
        let shouldEnhance = false;
        
        mutations.forEach((mutation) => {
          if (mutation.type === 'childList') {
            mutation.addedNodes.forEach((node) => {
              if (node.nodeType === 1) { // Element node
                if (node.matches && (
                  node.matches('input[type="text"], input[type="search"], input[type="email"], input[type="url"], input[type="tel"], textarea, [contenteditable="true"]') ||
                  node.querySelector('input[type="text"], input[type="search"], input[type="email"], input[type="url"], input[type="tel"], textarea, [contenteditable="true"]')
                )) {
                  shouldEnhance = true;
                }
              }
            });
          }
        });

        if (shouldEnhance) {
          setTimeout(enhanceAllInputs, 10);
        }
      });

      observer.observe(document.body, { 
        childList: true, 
        subtree: true 
      });
    };

    const initializePlugin = () => {
      enhanceAllInputs();
    };

    // Initialize immediately
    initializePlugin();
    initObserver();

    // Handle Vue Router navigation
    if (app.config.globalProperties.$router) {
      // Enhance inputs on route changes
      app.config.globalProperties.$router.afterEach(() => {
        setTimeout(() => {
          enhanceAllInputs();
        }, 100);
      });
    }

    // Also enhance on DOM content loaded and window load as fallbacks
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', enhanceAllInputs);
    } else {
      enhanceAllInputs();
    }

    window.addEventListener('load', enhanceAllInputs);

    app.config.globalProperties.$speechToText = {
      startListening: (input) => {
        const button = input.parentNode?.querySelector('[data-speech-button]');
        startListening(input, button);
      },
      stopListening,
      isListening: () => isListening,
      setLanguage: (lang) => { 
        if (recognition) recognition.lang = lang; 
      },
      reinitialize: () => {
        enhanceAllInputs();
      },
      isSupported: () => isSupported()
    };
  }
};

export default SpeechToTextPlugin;