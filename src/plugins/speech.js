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

    if (!isSupported()) {
      console.warn('Speech recognition is not supported in this browser or page');
      return;
    }


    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const recognition = new SpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.lang = defaultOptions.lang;

    let currentInput = null;
    let isListening = false;
    let observer = null;


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


      if (input.parentElement?.classList?.contains('speech-input-wrapper')) {
        return;
      }

      const wrapper = document.createElement('div');
      wrapper.className = 'speech-input-wrapper';
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

    const toggleListening = (input, button) => {
      if (isListening) {
        stopListening();
        updateButtonState(button, false);
      } else {
        startListening(input, button);
        updateButtonState(button, true);
      }
    };


    const updateButtonState = (button, listening) => {
      if (listening) {
        button.style.opacity = '1';
        button.style.background = '#f44336';
        button.style.transform = 'translateY(-50%) scale(1.1)';
        button.innerHTML = recordingIcon;
      } else {
        button.style.opacity = '0.8';
        button.style.background = defaultOptions.buttonColor;
        button.style.transform = 'translateY(-50%) scale(1)';
        button.innerHTML = micIcon;
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
          updateButtonState(button, false);
        }
      };

      recognition.onend = () => {
        isListening = false;
        if (button) {
          updateButtonState(button, false);
        }
      };

      try {
        recognition.start();
      } catch (error) {
        console.error('Failed to start speech recognition:', error);
        isListening = false;
        if (button) {
          updateButtonState(button, false);
        }
      }
    };


    const stopListening = () => {
      isListening = false;
      try {
        recognition.stop();
      } catch (error) {

      }
    };


    const removeAllSpeechButtons = () => {
      const buttons = document.querySelectorAll('[data-speech-button]');
      buttons.forEach(button => {
        button.remove();
      });


      const wrappers = document.querySelectorAll('.speech-input-wrapper');
      wrappers.forEach(wrapper => {
        const input = wrapper.querySelector('input, textarea, [contenteditable="true"]');
        if (input) {
          delete input.dataset.speechEnhanced;
          wrapper.parentNode.insertBefore(input, wrapper);
        }
        wrapper.remove();
      });
    };

    e
    const enhanceAllInputs = () => {
      if (!isPageEnabled()) {
        removeAllSpeechButtons();
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
        if (!input.disabled && !input.readOnly && !input.dataset.speechEnhanced) {
          enhanceInput(input);
        }
      });
    };


    const initObserver = () => {
      if (observer) {
        observer.disconnect();
      }

      observer = new MutationObserver((mutations) => {
        if (!isPageEnabled()) {
          removeAllSpeechButtons();
          return;
        }

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
                [contenteditable="true"]:not([data-no-speech])
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
                [contenteditable="true"]:not([data-no-speech])
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


    const initializePlugin = () => {
      removeAllSpeechButtons();

      setTimeout(() => {
        enhanceAllInputs();
        initObserver();
      }, 100);
    };


    app.mixin({
      mounted() {
        this.$nextTick(() => {
          if (isPageEnabled()) {
            initializePlugin();
          }
        });
      },
      beforeUnmount() {
        if (observer) {
          observer.disconnect();
        }
      }
    });


    if (app.config.globalProperties.$router) {
      const originalPush = app.config.globalProperties.$router.push;
      const originalReplace = app.config.globalProperties.$router.replace;


      app.config.globalProperties.$router.push = function (...args) {
        return originalPush.apply(this, args).finally(() => {
          setTimeout(() => {
            initializePlugin();
          }, 300);
        });
      };

      app.config.globalProperties.$router.replace = function (...args) {
        return originalReplace.apply(this, args).finally(() => {
          setTimeout(() => {
            initializePlugin();
          }, 300);
        });
      };


      window.addEventListener('popstate', () => {
        setTimeout(() => {
          initializePlugin();
        }, 300);
      });
    }

   
    app.config.globalProperties.$speechToText = {
      startListening: (input) => {
        const button = input.parentNode?.querySelector('[data-speech-button]');
        startListening(input, button);
      },
      stopListening,
      isListening: () => isListening,
      setLanguage: (lang) => {
        recognition.lang = lang;
      },
      reinitialize: () => {
        initializePlugin();
      },
      isEnabled: () => isPageEnabled()
    };


    setTimeout(() => {
      initializePlugin();
    }, 500);
  }
};

if (typeof window !== 'undefined' && window.Vue) {
  window.Vue.use(SpeechToTextPlugin);
}

export default SpeechToTextPlugin;
