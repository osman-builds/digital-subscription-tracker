@props([
    'mode' => 'concierge', // 'concierge' or 'copilot'
])

@php
    $isCopilot = $mode === 'copilot';
    $endpoint = $isCopilot ? route('ai.copilot') : route('ai.concierge');
    $title = $isCopilot ? 'SubShield Copilot' : 'SubShield AI Concierge';
    $subtitle = $isCopilot ? 'Your personal subscription advisor' : 'Instant product & security answers';
    $welcome = $isCopilot
        ? "Hello " . (auth()->user()->name ?? 'there') . "! 👋 I'm your SubShield Copilot. Ask me about your spending, upcoming renewals, or how to improve your Optimization Score!"
        : "Hi there! 👋 I'm the SubShield AI Concierge. Ask me anything about how SubShield protects your privacy, our AI receipt scanner, or how to test the 1-hour sandbox!";
    $chips = $isCopilot ? [
        ['label' => '📊 Spend summary', 'q' => 'Give me a quick summary of my monthly and yearly spend.'],
        ['label' => '🔔 Renewals in 7 days', 'q' => 'Which subscriptions are renewing in the next 7 days?'],
        ['label' => '📈 Price creep?', 'q' => 'Do I have any detected price increases or stealth creep?'],
        ['label' => '💡 Improve score', 'q' => 'How can I improve my Optimization Health Score?'],
    ] : [
        ['label' => '🛡️ Is my data safe?', 'q' => 'How does SubShield track subscriptions without bank logins?'],
        ['label' => '📸 How does AI scan work?', 'q' => 'How does the multimodal AI receipt scanner work?'],
        ['label' => '⚡ 1-Hour Sandbox?', 'q' => 'How does the 1-hour interactive demo sandbox work?'],
        ['label' => '🌍 Supported currencies?', 'q' => 'Which foreign currencies and exchange rates are supported?'],
    ];
@endphp

<div id="subshieldAiWidget" class="ss-ai-root" data-endpoint="{{ $endpoint }}" data-mode="{{ $mode }}">
    {{-- Floating Action Bubble --}}
    <button type="button" id="ssAiToggleBtn" class="ss-ai-bubble-btn" aria-label="Open SubShield AI Chat" title="{{ $title }}">
        <div class="ss-ai-bubble-pulse"></div>
        <div class="ss-ai-bubble-inner">
            <i class="bi bi-stars ss-ai-bubble-icon" id="ssAiBubbleIcon"></i>
            <span class="ss-ai-bubble-dot" id="ssAiBubbleDot"></span>
        </div>
    </button>

    {{-- Chat Window Pane --}}
    <div id="ssAiWindow" class="ss-ai-window" style="display: none;" role="dialog" aria-modal="true" aria-label="{{ $title }}">
        {{-- Header --}}
        <div class="ss-ai-header">
            <div class="d-flex align-items-center gap-2">
                <div class="ss-ai-avatar">
                    <img src="/favicon.svg" width="22" height="22" alt="SubShield AI">
                    <span class="ss-ai-status-dot"></span>
                </div>
                <div>
                    <div class="ss-ai-header-title">{{ $title }}</div>
                    <div class="ss-ai-header-sub">{{ $subtitle }}</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1">
                <button type="button" class="ss-ai-header-btn" id="ssAiResetBtn" title="Reset Conversation" aria-label="Reset chat">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
                <button type="button" class="ss-ai-header-btn" id="ssAiCloseBtn" title="Close Chat" aria-label="Close chat">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        {{-- Messages Container --}}
        <div class="ss-ai-body" id="ssAiMessages">
            <div class="ss-ai-msg ss-ai-msg-ai">
                <div class="ss-ai-bubble-ai">
                    {!! nl2br(e($welcome)) !!}
                </div>
            </div>

            {{-- Quick Suggestion Chips --}}
            <div class="ss-ai-chips-wrap" id="ssAiChips">
                <div class="ss-ai-chips-label">Suggested questions:</div>
                <div class="ss-ai-chips-list">
                    @foreach($chips as $chip)
                        <button type="button" class="ss-ai-chip-btn" data-query="{{ $chip['q'] }}">
                            {{ $chip['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Typing Indicator --}}
        <div class="ss-ai-typing" id="ssAiTyping" style="display: none;">
            <div class="ss-ai-typing-avatar">
                <img src="/favicon.svg" width="16" height="16" alt="AI">
            </div>
            <div class="ss-ai-typing-dots">
                <span></span><span></span><span></span>
            </div>
            <span class="ss-ai-typing-text">Thinking...</span>
        </div>

        {{-- Input Footer --}}
        <div class="ss-ai-footer">
            <form id="ssAiForm" class="ss-ai-input-form" onsubmit="return false;">
                <div class="ss-ai-input-wrap">
                    <input type="text" id="ssAiInput" class="ss-ai-input" placeholder="Ask about subscriptions or privacy..." maxlength="250" autocomplete="off">
                    <button type="submit" id="ssAiSendBtn" class="ss-ai-send-btn" aria-label="Send message" disabled>
                        <i class="bi bi-arrow-up-circle-fill"></i>
                    </button>
                </div>
                <div class="d-flex justify-content-between align-items-center px-1 mt-1">
                    <span class="ss-ai-disclaimer"><i class="bi bi-shield-check text-success me-1"></i>Privacy-guaranteed &bull; Zero bank scraping</span>
                    <span class="ss-ai-char-counter" id="ssAiCounter">0/250</span>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* ── SubShield Floating AI Chatbot Styles ───────────────────────────────── */
.ss-ai-root {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

/* Floating Action Button */
.ss-ai-bubble-btn {
    position: relative;
    width: 58px;
    height: 58px;
    border-radius: 50%;
    border: none;
    outline: none;
    cursor: pointer;
    background: transparent;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.55), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
    transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.ss-ai-bubble-btn:hover {
    transform: scale(1.08);
}
.ss-ai-bubble-btn:active {
    transform: scale(0.95);
}
.ss-ai-bubble-pulse {
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(99, 102, 241, 0.6) 0%, rgba(99, 102, 241, 0) 70%);
    animation: ssAiPulseGlow 3s infinite ease-in-out;
    pointer-events: none;
}
@keyframes ssAiPulseGlow {
    0%, 100% { transform: scale(1); opacity: 0.6; }
    50% { transform: scale(1.3); opacity: 0.15; }
}
.ss-ai-bubble-inner {
    position: relative;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
    border: 2px solid rgba(255, 255, 255, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
}
.ss-ai-bubble-icon {
    font-size: 1.55rem;
    transition: transform 0.3s ease;
}
.ss-ai-bubble-dot {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 11px;
    height: 11px;
    background: #10b981;
    border: 2px solid #ffffff;
    border-radius: 50%;
    box-shadow: 0 0 6px #10b981;
}

/* Chat Window Frame */
.ss-ai-window {
    position: absolute;
    bottom: 72px;
    right: 0;
    width: 380px;
    height: 520px;
    max-height: calc(100vh - 120px);
    background: #0f172a;
    border: 1px solid rgba(99, 102, 241, 0.35);
    border-radius: 20px;
    box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(255, 255, 255, 0.08);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: ssAiSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes ssAiSlideUp {
    from { opacity: 0; transform: translateY(20px) scale(0.96); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

/* Header */
.ss-ai-header {
    background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
    padding: 0.95rem 1.15rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.ss-ai-avatar {
    position: relative;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: rgba(99, 102, 241, 0.2);
    border: 1px solid rgba(99, 102, 241, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
}
.ss-ai-status-dot {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 9px;
    height: 9px;
    background: #10b981;
    border: 1.5px solid #0f172a;
    border-radius: 50%;
}
.ss-ai-header-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #ffffff;
    line-height: 1.2;
}
.ss-ai-header-sub {
    font-size: 0.72rem;
    color: #94a3b8;
}
.ss-ai-header-btn {
    background: transparent;
    border: none;
    color: #94a3b8;
    width: 28px;
    height: 28px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.15s ease;
}
.ss-ai-header-btn:hover {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.08);
}

/* Chat Body */
.ss-ai-body {
    flex: 1;
    overflow-y: auto;
    padding: 1.1rem;
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    scroll-behavior: smooth;
}
.ss-ai-body::-webkit-scrollbar {
    width: 5px;
}
.ss-ai-body::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 4px;
}

/* Message Bubbles */
.ss-ai-msg {
    display: flex;
    width: 100%;
}
.ss-ai-msg-ai {
    justify-content: flex-start;
}
.ss-ai-msg-user {
    justify-content: flex-end;
}
.ss-ai-bubble-ai {
    background: #1e293b;
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: #f1f5f9;
    padding: 0.75rem 0.95rem;
    border-radius: 14px 14px 14px 2px;
    font-size: 0.86rem;
    line-height: 1.5;
    max-width: 90%;
    word-break: break-word;
}
.ss-ai-bubble-ai b, .ss-ai-bubble-ai strong {
    color: #ffffff;
    font-weight: 700;
}
.ss-ai-bubble-ai ul, .ss-ai-bubble-ai ol {
    margin: 0.4rem 0 0.2rem 1.1rem;
    padding: 0;
}
.ss-ai-bubble-ai li {
    margin-bottom: 0.25rem;
}
.ss-ai-bubble-user {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    color: #ffffff;
    padding: 0.65rem 0.95rem;
    border-radius: 14px 14px 2px 14px;
    font-size: 0.86rem;
    line-height: 1.45;
    max-width: 85%;
    word-break: break-word;
}

/* Suggestion Chips */
.ss-ai-chips-wrap {
    margin-top: 0.4rem;
}
.ss-ai-chips-label {
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    margin-bottom: 0.45rem;
}
.ss-ai-chips-list {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}
.ss-ai-chip-btn {
    background: rgba(99, 102, 241, 0.08);
    border: 1px solid rgba(99, 102, 241, 0.25);
    border-radius: 8px;
    color: #c7d2fe;
    font-size: 0.8rem;
    padding: 0.45rem 0.75rem;
    text-align: left;
    cursor: pointer;
    transition: all 0.15s ease;
}
.ss-ai-chip-btn:hover {
    background: rgba(99, 102, 241, 0.18);
    border-color: rgba(99, 102, 241, 0.5);
    color: #ffffff;
    transform: translateX(2px);
}

/* Typing Indicator */
.ss-ai-typing {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 1.1rem;
    background: #0f172a;
}
.ss-ai-typing-avatar {
    width: 22px;
    height: 22px;
    border-radius: 6px;
    background: rgba(99, 102, 241, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
}
.ss-ai-typing-dots {
    display: flex;
    gap: 4px;
}
.ss-ai-typing-dots span {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #818cf8;
    animation: ssAiBounce 1.4s infinite ease-in-out;
}
.ss-ai-typing-dots span:nth-child(2) { animation-delay: 0.2s; }
.ss-ai-typing-dots span:nth-child(3) { animation-delay: 0.4s; }
@keyframes ssAiBounce {
    0%, 80%, 100% { transform: translateY(0); opacity: 0.4; }
    40% { transform: translateY(-4px); opacity: 1; }
}
.ss-ai-typing-text {
    font-size: 0.75rem;
    color: #94a3b8;
}

/* Input Footer */
.ss-ai-footer {
    background: #111827;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    padding: 0.85rem 1.1rem;
}
.ss-ai-input-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.ss-ai-input {
    width: 100%;
    background: #1e293b;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 12px;
    padding: 0.65rem 2.8rem 0.65rem 0.9rem;
    font-size: 0.86rem;
    color: #ffffff;
    outline: none;
    transition: all 0.2s ease;
}
.ss-ai-input:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
    background: #192338;
}
.ss-ai-input::placeholder {
    color: #64748b;
    font-size: 0.82rem;
}
.ss-ai-send-btn {
    position: absolute;
    right: 6px;
    background: transparent;
    border: none;
    color: #6366f1;
    cursor: pointer;
    font-size: 1.45rem;
    padding: 0 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.15s ease, opacity 0.15s ease;
}
.ss-ai-send-btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
    color: #64748b;
}
.ss-ai-send-btn:not(:disabled):hover {
    color: #818cf8;
}
.ss-ai-disclaimer {
    font-size: 0.68rem;
    color: #64748b;
}
.ss-ai-char-counter {
    font-size: 0.68rem;
    color: #64748b;
}

/* Mobile Responsive */
@media (max-width: 480px) {
    .ss-ai-root {
        bottom: 16px;
        right: 16px;
    }
    .ss-ai-window {
        right: 0;
        bottom: 68px;
        width: calc(100vw - 32px);
        height: 75vh;
    }
}
</style>

<script>
(function() {
    var CSRF_TOKEN = '{{ csrf_token() }}';
    var widget = document.getElementById('subshieldAiWidget');
    if (!widget) return;

    var endpoint = widget.getAttribute('data-endpoint');
    var toggleBtn = document.getElementById('ssAiToggleBtn');
    var windowPane = document.getElementById('ssAiWindow');
    var closeBtn = document.getElementById('ssAiCloseBtn');
    var resetBtn = document.getElementById('ssAiResetBtn');
    var messagesBox = document.getElementById('ssAiMessages');
    var input = document.getElementById('ssAiInput');
    var sendBtn = document.getElementById('ssAiSendBtn');
    var form = document.getElementById('ssAiForm');
    var typing = document.getElementById('ssAiTyping');
    var counter = document.getElementById('ssAiCounter');
    var bubbleDot = document.getElementById('ssAiBubbleDot');
    var bubbleIcon = document.getElementById('ssAiBubbleIcon');

    var isOpen = false;
    var isSending = false;
    var initialHtml = messagesBox ? messagesBox.innerHTML : '';

    function toggleChat(open) {
        isOpen = (open !== undefined) ? open : !isOpen;
        if (isOpen) {
            windowPane.style.display = 'flex';
            bubbleIcon.className = 'bi bi-x-lg ss-ai-bubble-icon';
            if (bubbleDot) bubbleDot.style.display = 'none';
            setTimeout(function() { input.focus(); }, 100);
            scrollToBottom();
        } else {
            windowPane.style.display = 'none';
            bubbleIcon.className = 'bi bi-stars ss-ai-bubble-icon';
        }
    }

    function scrollToBottom() {
        if (messagesBox) {
            messagesBox.scrollTop = messagesBox.scrollHeight;
        }
    }

    function updateCounter() {
        var len = input.value.length;
        counter.textContent = len + '/250';
        sendBtn.disabled = (len === 0 || isSending);
    }

    function appendUserMessage(text) {
        var wrap = document.createElement('div');
        wrap.className = 'ss-ai-msg ss-ai-msg-user';
        var bubble = document.createElement('div');
        bubble.className = 'ss-ai-bubble-user';
        bubble.textContent = text;
        wrap.appendChild(bubble);
        messagesBox.appendChild(wrap);
        scrollToBottom();
    }

    function appendAiMessage(html) {
        var wrap = document.createElement('div');
        wrap.className = 'ss-ai-msg ss-ai-msg-ai';
        var bubble = document.createElement('div');
        bubble.className = 'ss-ai-bubble-ai';
        bubble.innerHTML = html;
        wrap.appendChild(bubble);
        messagesBox.appendChild(wrap);
        scrollToBottom();
    }

    function sendQuery(text) {
        text = text ? text.trim() : '';
        if (!text || isSending) return;

        isSending = true;
        sendBtn.disabled = true;
        input.disabled = true;
        input.value = '';
        updateCounter();

        appendUserMessage(text);

        // Hide chips after first message
        var chips = document.getElementById('ssAiChips');
        if (chips) chips.style.display = 'none';

        // Show typing indicator
        typing.style.display = 'flex';
        scrollToBottom();

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({ query: text })
        })
        .then(function(res) {
            if (res.status === 429) {
                return { ok: false, reply: "You're sending questions very quickly. Please wait a moment before asking again!" };
            }
            return res.json();
        })
        .then(function(data) {
            typing.style.display = 'none';
            isSending = false;
            input.disabled = false;
            input.focus();
            updateCounter();

            if (data && data.reply) {
                appendAiMessage(data.reply);
            } else {
                appendAiMessage("I'm having trouble processing that right now. Please try again in a few moments.");
            }
        })
        .catch(function(err) {
            typing.style.display = 'none';
            isSending = false;
            input.disabled = false;
            input.focus();
            updateCounter();
            appendAiMessage("Connection error. Please check your internet or retry shortly.");
        });
    }

    // Toggle click
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() { toggleChat(); });
    }
    if (closeBtn) {
        closeBtn.addEventListener('click', function() { toggleChat(false); });
    }
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            messagesBox.innerHTML = initialHtml;
            wireChips();
            input.value = '';
            updateCounter();
        });
    }

    // Input events
    input.addEventListener('input', updateCounter);
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendQuery(input.value);
        }
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        sendQuery(input.value);
    });

    // Wire chips
    function wireChips() {
        document.querySelectorAll('.ss-ai-chip-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var q = this.getAttribute('data-query');
                sendQuery(q);
            });
        });
    }
    wireChips();
})();
</script>
