// Display Audio System

document.addEventListener('DOMContentLoaded', () => {
    initializeDisplayAudio();
});

// ---- Global variables (accessible to all functions) ----
let lastUpdatedAt = '';
let audioQueue = [];
let isSpeaking = false;

function initializeDisplayAudio() {
    let lastModalTokenId = null;   // remember which token already opened the modal
    let audioEnabled = sessionStorage.getItem('audioEnabled') === 'true';
    const notification = document.getElementById('audioNotification');
    const modal = document.getElementById('token-modal');
    const modalTokenNumber = document.getElementById('modal-token-number');
    const modalCounterNumber = document.getElementById('modal-counter-number');

    // Show notification if audio not enabled
    if (!audioEnabled && notification) {
        notification.classList.remove('hidden');
        setTimeout(() => {
            notification.style.opacity = '0.8';
        }, 500);
    }

    // Enable audio on any click
    const enableAudio = () => {
        if (!audioEnabled) {
            audioEnabled = true;
            sessionStorage.setItem('audioEnabled', 'true');

            // Hide notification with fade
            if (notification) {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }

            // Unlock audio context
            const audio = new Audio('/audio/custom/en/token_number.mp3');
            audio.volume = 0.01; // Very quiet test
            audio.play().catch(e => console.error('Audio unlock failed:', e));

            // Remove click listener after first enable
            document.removeEventListener('click', enableAudio);
        }
    };

    // Add click listener to entire document
    if (!audioEnabled) {
        document.addEventListener('click', enableAudio);
    }


    // Reduced polling to 2 second for faster updates
    setInterval(fetchUpdates, 2000);

    function fetchUpdates() {
        const url = new URL(window.location.href);
        if (lastUpdatedAt) {
            url.searchParams.append('last_updated_at', lastUpdatedAt);
        }

        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                lastUpdatedAt = data.serverTime;
                if (data.reload) window.location.reload();

                // Update Main Token Display (Pass null if not present to show waiting)
                updateMainToken(data.mainToken);

                // Show a centered popup only when a **new** token is called
                if (
                    data.mainToken &&
                    data.mainToken.status === 'called' &&
                    data.mainToken.id !== lastModalTokenId
                ) {
                    // Remove any blink class from the modal content (keep background steady)
                    const modalContent = document.getElementById('token-modal-content');
                    if (modalContent) {
                        modalContent.classList.remove('blink');
                    }

                    showTokenModal(data.mainToken);
                    // Keep the popup visible longer for readability
                    setTimeout(() => hideTokenModal(), 4000);

                    // Remember that we have already shown the modal for this token
                    lastModalTokenId = data.mainToken.id;
                }

                // Update History List
                if (data.historyTokens) {
                    updateHistoryList(data.historyTokens);
                }

                if (data.newlyCalled && data.newlyCalled.length > 0) {
                    data.newlyCalled.forEach(token => {
                        if (!audioQueue.find(t => t.id === token.id)) {
                            audioQueue.push(token);
                        }
                    });

                    if (!isSpeaking && audioEnabled) {
                        processAudioQueue();
                    }
                }
            })
            .catch(error => console.error('Fetch error:', error));
    }

    function updateMainToken(token) {
        const container = document.getElementById('current-call-container');
        if (!container) return;

        if (!token) {
            // Render Waiting State
            container.innerHTML = `
                <div class="bg-gradient-to-r from-slate-700 via-slate-600 to-slate-700 rounded-3xl shadow-2xl w-full h-full flex items-center justify-center border-2 border-slate-500">
                    <div class="text-center">
                        <div class="text-5xl font-black text-slate-300 mb-3">⏳ WAITING</div>
                        <div class="text-lg text-slate-400 uppercase tracking-wider">Please wait for your number</div>
                    </div>
                </div>
            `;
            return;
        }

        // Render Token State
        const counterName = token.counter ? token.counter.name.replace('Counter ', '') : '--';
        container.innerHTML = `
            <div id="main-token-card" class="bg-gradient-to-r from-cyan-600 via-blue-600 to-purple-600 rounded-3xl shadow-2xl w-full h-full flex items-center justify-around px-16 border-2 border-cyan-400 pulse-animation">
                <div class="flex flex-col items-center gap-2">
                    <div class="text-sm text-cyan-100 uppercase tracking-widest font-semibold">Token Number</div>
                    <div id="main-token-number" class="text-7xl font-black text-white leading-none drop-shadow-2xl">
                        ${token.token_number}
                    </div>
                </div>
                <div class="flex items-center">
                    <span id="main-status" class="badge bg-white text-cyan-600 border-0 text-xl px-10 py-7 font-bold ${token.status === 'called' ? 'blink' : ''} shadow-xl">
                        ${token.status === 'called' ? '🔔 CALLING...' : '✓ NOW SERVING'}
                    </span>
                </div>
                <div class="flex flex-col items-center gap-2">
                    <div class="text-sm text-cyan-100 uppercase tracking-widest font-semibold">Counter</div>
                    <div id="main-counter" class="text-7xl font-black text-white leading-none drop-shadow-2xl">
                        ${counterName}
                    </div>
                </div>
            </div>
        `;
    }

    function updateHistoryList(tokens) {
        const container = document.getElementById('history-container');
        if (!container) return;

        container.innerHTML = tokens.map(token => {
            const counterName = token.counter ? token.counter.name.replace('Counter ', '') : '--';
            const deptName = token.department ? token.department.name : '';
            return `
                <div class="bg-gradient-to-r from-slate-800 to-slate-700 rounded-xl p-4 border-l-4 ${token.status === 'completed' ? 'border-gray-500 opacity-70' : (token.status === 'serving' ? 'border-cyan-400' : 'border-blue-400')} flex justify-between items-center hover:shadow-lg transition-all">
                    <div>
                        <div class="text-3xl font-bold text-white">
                            ${token.token_number}
                        </div>
                        <div class="text-xs text-gray-400 mt-1">
                            ${deptName}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xl text-cyan-300 font-semibold">
                            Counter ${counterName}
                        </div>
                        <div class="mt-1">
                            <span class="badge badge-sm ${token.status === 'completed' ? 'badge-ghost' : (token.status === 'serving' ? 'bg-cyan-500 text-white border-0' : 'bg-blue-500 text-white border-0')}">
                                ${token.status.charAt(0).toUpperCase() + token.status.slice(1)}
                            </span>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function showTokenModal(token) {
        if (!modal) return;
        const modalContent = document.getElementById('token-modal-content');
        modalTokenNumber.textContent = token.token_number;
        modalCounterNumber.textContent = token.counter.name.replace('Counter ', '');
        // Update modal title if needed, though it's static in HTML currently. 
        // Actually, the modal title "Now Calling" is static in HTML. 
        // Since the modal is hidden/shown, we can update the title here too if we want dynamic update, 
        // but the HTML in index.blade.php already uses the translation key.
        // However, if the page is not reloaded, the JS won't re-render the static HTML of the modal.
        // But wait, the modal HTML is NOT re-rendered by JS, it's just shown/hidden.
        // So the Blade translation will work for the static parts of the modal.
        // The dynamic parts are token number and counter number.
        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            modal.classList.remove('opacity-0');
            if (modalContent) {
                modalContent.classList.remove('scale-0');
                modalContent.classList.add('scale-100');
            }
        });
    }

    function hideTokenModal() {
        if (!modal) return;
        const modalContent = document.getElementById('token-modal-content');
        modal.classList.add('opacity-0');
        if (modalContent) {
            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-0');
        }
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    function processAudioQueue() {
        if (audioQueue.length === 0) {
            isSpeaking = false;
            return;
        }
        isSpeaking = true;
        const token = audioQueue.shift();
        showTokenModal(token);
        setTimeout(() => {
            hideTokenModal();
        }, 2000);
        speakToken(token.token_number, token.counter?.name || 'Counter 1', () => {
            setTimeout(() => processAudioQueue(), 500);
        });
    }

    function speakToken(token, counter, onComplete) {
        const lang = 'en';
        const langPlaylist = buildPlaylistForLang(lang, token, counter);
        playPlaylist(langPlaylist, onComplete);
    }

    function buildPlaylistForLang(lang, token, counter) {
        const playlist = [];
        const path = `/audio/custom/${lang}/`;
        playlist.push(path + 'token_number.mp3');
        const tokenParts = token.toString().match(/[a-zA-Z]+|[0-9]+/g) || [];
        tokenParts.forEach(part => {
            if (isNaN(part)) {
                // It's letters (e.g., "VM")
                for (let char of part) {
                    playlist.push(path + char.toLowerCase() + '.mp3');
                }
            } else {
                // It's numbers (e.g., "001")
                // We want to pronounce each digit individually for token numbers like "001" -> "Zero Zero One"
                for (let char of part) {
                    playlist.push(path + char + '.mp3');
                }
            }
        });
        playlist.push(path + 'please_proceed.mp3');
        const counterNum = parseInt(counter.replace(/[^0-9]/g, ''), 10);
        if (!isNaN(counterNum)) {
            const files = lang === 'ar' ? getArabicNumberFiles(counterNum) : getEnglishNumberFiles(counterNum);
            files.forEach(f => playlist.push(path + f));
        }
        return playlist;
    }

    function getEnglishNumberFiles(n) {
        const files = [];
        if (n === 0) return ['0.mp3'];
        if (n >= 1000) {
            const thousands = Math.floor(n / 1000);
            files.push(...getEnglishNumberFiles(thousands));
            files.push('1000.mp3');
            n %= 1000;
        }
        if (n >= 100) {
            const hundreds = Math.floor(n / 100);
            // files.push(`${hundreds}.mp3`); // Removed to prevent "One One Hundred"
            files.push(`${hundreds}00.mp3`);
            n %= 100;
        }
        if (n > 0) {
            if (n < 20) {
                files.push(`${n}.mp3`);
            } else {
                const tens = Math.floor(n / 10) * 10;
                const ones = n % 10;
                files.push(`${tens}.mp3`);
                if (ones > 0) files.push(`${ones}.mp3`);
            }
        }
        return files;
    }

    function getArabicNumberFiles(n) {
        const files = [];
        if (n === 0) return ['0.mp3'];
        if (n >= 1000) {
            const thousands = Math.floor(n / 1000);
            if (thousands === 1) files.push('1000.mp3');
            else if (thousands === 2) files.push('2000.mp3');
            else {
                files.push(...getArabicNumberFiles(thousands));
                files.push('1000.mp3');
            }
            n %= 1000;
            if (n > 0) files.push('and.mp3');
        }
        if (n >= 100) {
            const hundreds = Math.floor(n / 100) * 100;
            files.push(`${hundreds}.mp3`);
            n %= 100;
            if (n > 0) files.push('and.mp3');
        }
        if (n > 0) {
            if (n < 20) {
                files.push(`${n}.mp3`);
            } else {
                const ones = n % 10;
                const tens = Math.floor(n / 10) * 10;
                if (ones > 0) {
                    files.push(`${ones}.mp3`);
                    files.push('and.mp3');
                }
                files.push(`${tens}.mp3`);
            }
        }
        return files;
    }

    function playPlaylist(playlist, onComplete) {
        if (playlist.length === 0) {
            if (onComplete) onComplete();
            return;
        }
        const src = playlist.shift();
        const audio = new Audio(src);
        audio.playbackRate = 1.25; // Speed up playback
        audio.onended = () => playPlaylist(playlist, onComplete);
        audio.onerror = () => {
            console.warn(`Audio file not found: ${src}`);
            playPlaylist(playlist, onComplete);
        };
        audio.play().catch(e => {
            console.error('Playback error:', e);
            playPlaylist(playlist, onComplete);
        });
    }
}
