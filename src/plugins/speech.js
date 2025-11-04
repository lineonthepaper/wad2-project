const SpeechToTextPlugin = {
  install(app, options = {}) {
    const defaultOptions = {
      buttonColor: '#3c4247ff',
      buttonPosition: 'right',
      lang: 'en-US',
      autoStart: false,
      enabledPages: ['/services', '/help', '/history'],
      ...options
    };

    // Store plugin state
    let currentInput = null;
    let isListening = false;
    let observer = null;
    let recognition = null;

    const isPageEnabled = () => {
      if (!defaultOptions.enabledPages || defaultOptions.enabledPages.length === 0) {
        return true;
      }

      const currentPath = window.location.pathname;
      return defaultOptions.enabledPages.some(page =>
        currentPath.includes(page) || currentPath === page
      );
    };

    const isSupported = () => {
      return ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) &&
             isPageEnabled();
    };

    // Initialize speech recognition
    const initializeRecognition = () => {
      if (!isSupported()) {
        console.warn('Speech recognition is not supported in this browser or page');
        return null;
      }

      const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
      recognition = new SpeechRecognition();
      recognition.continuous = false;
      recognition.interimResults = false;
      recognition.lang = defaultOptions.lang;

      return recognition;
    };

    // Initialize recognition on plugin install
    recognition = initializeRecognition();
    if (!recognition) return;

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

    const createMicButton = (input) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.innerHTML = micIcon;
      button.setAttribute('data-speech-button', 'true');
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
        button.style.transform = 'translateY(-50%) scale(1.05)';
      });

      button.addEventListener('mouseleave', () => {
        if (!isListening) {
          button.style.opacity = '0.8';
          button.style.transform = 'translateY(-50%) scale(1)';
        }
      });

      button.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        toggleListening(input, button);
      });

      button.addEventListener('mousedown', (e) => {
        e.preventDefault();
        e.stopPropagation();
      });

      return button;
    };

    const enhanceInput = (input) => {
      if (input.dataset.speechEnhanced) return;

      const wrapper = document.createElement('div');
      wrapper.style.position = 'relative';
      wrapper.style.display = 'inline-block';
      wrapper.style.width = '100%';

      const isDropdownInput = input.parentElement?.classList?.contains('dropdown-menu') ||
                             input.closest('.dropdown');

      if (isDropdownInput) {
        wrapper.style.zIndex = '1001';
      }

      input.parentNode.insertBefore(wrapper, input);
      wrapper.appendChild(input);

      const micButton = createMicButton(input);
      wrapper.appendChild(micButton);

      input.dataset.speechEnhanced = 'true';

      if (isDropdownInput) {
        wrapper.classList.add('speech-dropdown-input');
        micButton.style.zIndex = '1002';
      }
    };

    // Toggle listening state
    const toggleListening = (input, button) => {
      if (isListening) {
        stopListening();
        button.style.opacity = '0.8';
        button.style.background = defaultOptions.buttonColor;
        button.style.transform = 'translateY(-50%) scale(1)';
        button.innerHTML = micIcon;
      } else {
        startListening(input, button);
        button.style.opacity = '1';
        button.style.background = '#f44336';
        button.style.transform = 'translateY(-50%) scale(1.1)';
        button.innerHTML = recordingIcon;
      }
    };

    const startListening = (input, button) => {
      if (isListening) return;

      currentInput = input;
      isListening = true;

      recognition.onresult = (event) => {
        const transcript = event.results[0][0].transcript;
        if (currentInput) {
          const cursorPosition = currentInput.selectionStart;
          const currentValue = currentInput.value;
          const newValue = currentValue.substring(0, cursorPosition) +
                          transcript +
                          currentValue.substring(cursorPosition);
          currentInput.value = newValue;

          currentInput.dispatchEvent(new Event('input', { bubbles: true }));
          currentInput.dispatchEvent(new Event('change', { bubbles: true }));
          currentInput.focus();
        }
      };

      recognition.onerror = (event) => {
        console.error('Speech recognition error:', event.error);
        stopListening();
        if (button) {
          button.style.opacity = '0.8';
          button.style.background = defaultOptions.buttonColor;
          button.style.transform = 'translateY(-50%) scale(1)';
          button.innerHTML = micIcon;
        }
      };

      recognition.onend = () => {
        isListening = false;
        if (button) {
          button.style.opacity = '0.8';
          button.style.background = defaultOptions.buttonColor;
          button.style.transform = 'translateY(-50%) scale(1)';
          button.innerHTML = micIcon;
        }
      };

      recognition.start();
    };

    const stopListening = () => {
      isListening = false;
      recognition.stop();
    };

    // Remove all existing speech buttons
    const removeAllSpeechButtons = () => {
      const buttons = document.querySelectorAll('[data-speech-button]');
      buttons.forEach(button => {
        button.remove();
      });

      // Remove enhanced markers
      const enhancedInputs = document.querySelectorAll('[data-speech-enhanced]');
      enhancedInputs.forEach(input => {
        delete input.dataset.speechEnhanced;
      });
    };

    const enhanceAllInputs = () => {
      // Clean up first
      removeAllSpeechButtons();

      if (!isPageEnabled()) return;

      const inputs = document.querySelectorAll(`
        input[type="text"],
        input[type="search"],
        input[type="email"],
        input[type="url"],
        input[type="tel"],
        textarea:not([data-no-speech]),
        [contenteditable="true"]
      `);

      inputs.forEach(input => {
        if (!input.disabled && !input.readOnly) {
          enhanceInput(input);
        }
      });
    };

    const initObserver = () => {
      // Disconnect existing observer
      if (observer) {
        observer.disconnect();
      }

      observer = new MutationObserver((mutations) => {
        if (!isPageEnabled()) return;

        mutations.forEach((mutation) => {
          mutation.addedNodes.forEach((node) => {
            if (node.nodeType === 1) {
              const inputs = node.querySelectorAll ? node.querySelectorAll(`
                input[type="text"],
                input[type="search"],
                input[type="email"],
                input[type="url"],
                input[type="tel"],
                textarea:not([data-no-speech]),
                [contenteditable="true"]
              `) : [];

              inputs.forEach(input => {
                if (!input.disabled && !input.readOnly && !input.dataset.speechEnhanced) {
                  enhanceInput(input);
                }
              });

              if (node.matches && node.matches(`
                input[type="text"],
                input[type="search"],
                input[type="email"],
                input[type="url"],
                input[type="tel"],
                textarea:not([data-no-speech]),
                [contenteditable="true"]
              `) && !node.disabled && !node.readOnly) {
                enhanceInput(node);
              }
            }
          });
        });
      });

      observer.observe(document.body, {
        childList: true,
        subtree: true
      });
    };

    // Main initialization function
    const initializePlugin = () => {
      enhanceAllInputs();
      initObserver();
    };

    app.mixin({
      mounted() {
        this.$nextTick(() => {
          initializePlugin();
        });
      },
      beforeUnmount() {
        // Clean up when component unmounts
        if (observer) {
          observer.disconnect();
        }
      }
    });

    // ✅ FIX: Add Vue Router integration
    if (app.config.globalProperties.$router) {
      // Watch for route changes
      app.config.globalProperties.$router.afterEach((to, from) => {
        // Wait for Vue to update the DOM, then reinitialize
        setTimeout(() => {
          initializePlugin();
        }, 100);
      });

      // Also watch for initial route load
      const unwatch = app.config.globalProperties.$router.onReady(() => {
        setTimeout(() => {
          initializePlugin();
        }, 100);
        unwatch();
      });
    }

    // Initial initialization
    setTimeout(() => {
      initializePlugin();
    }, 500);

    app.config.globalProperties.$speechToText = {
      startListening: (input) => {
        const button = input.parentNode?.querySelector('button');
        startListening(input, button);
      },
      stopListening,
      isListening: () => isListening,
      setLanguage: (lang) => {
        recognition.lang = lang;
      },
      // Add reinitialization method
      reinitialize: () => {
        initializePlugin();
      }
    };
  }
};

if (typeof window !== 'undefined' && window.Vue) {
  window.Vue.use(SpeechToTextPlugin);
}

export default SpeechToTextPlugin;
