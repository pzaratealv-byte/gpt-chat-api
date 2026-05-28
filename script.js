const chatMessages = document.getElementById('chatMessages');
const msgInput = document.getElementById('msg');
const sendBtn = document.getElementById('sendBtn');
const resetBtn = document.getElementById('resetBtn');
const typingIndicator = document.getElementById('typingIndicator');
const suggestionButtons = document.querySelectorAll('.suggestion');
const tokenCountEl = document.getElementById('tokenCount');

const emailBtn = document.getElementById('emailBtn');
const emailModal = document.getElementById('emailModal');
const closeModalBtn = document.getElementById('closeModalBtn');
const confirmEmailBtn = document.getElementById('confirmEmailBtn');
const emailInput = document.getElementById('emailInput');
const subjectInput = document.getElementById('subjectInput');
const emailFeedback = document.getElementById('emailFeedback');

function addMessage(role, text) {
    const wrapper = document.createElement('div');
    wrapper.className = `message ${role}`;

    const avatar = document.createElement('div');
    avatar.className = 'avatar';
    avatar.textContent = role === 'user' ? 'TÚ' : 'MB';

    const bubble = document.createElement('div');
    bubble.className = 'bubble';

    const paragraphs = String(text).split(/\n{2,}|\n/).filter(Boolean);
    if (paragraphs.length === 0) paragraphs.push(text);

    paragraphs.forEach(p => {
        const el = document.createElement('p');
        el.textContent = p;
        bubble.appendChild(el);
    });

    wrapper.appendChild(avatar);
    wrapper.appendChild(bubble);
    chatMessages.appendChild(wrapper);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function showTyping(show) {
    typingIndicator.classList.toggle('hidden', !show);
    if (show) chatMessages.scrollTop = chatMessages.scrollHeight;
}

function autoResize() {
    msgInput.style.height = 'auto';
    msgInput.style.height = Math.min(msgInput.scrollHeight, 180) + 'px';
}

function updateTokens(remaining) {
    if (tokenCountEl && remaining !== undefined) {
        tokenCountEl.textContent = remaining;
        const pill = document.getElementById('tokenCounter');
        if (remaining < 500) {
            pill.style.color = '#ef4444';
            pill.style.borderColor = 'rgba(239,68,68,0.4)';
        } else if (remaining < 1500) {
            pill.style.color = '#f59e0b';
            pill.style.borderColor = 'rgba(245,158,11,0.4)';
        } else {
            pill.style.color = '';
            pill.style.borderColor = '';
        }
    }
}

async function sendMessage(prefilledText = null) {
    const message = (prefilledText ?? msgInput.value).trim();
    if (!message) return;

    addMessage('user', message);
    msgInput.value = '';
    autoResize();
    sendBtn.disabled = true;
    showTyping(true);

    try {
        const response = await fetch('chat.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'message=' + encodeURIComponent(message)
        });

        const data = await response.json();
        showTyping(false);

        if (data.success) {
            addMessage('bot', data.reply);
        } else {
            addMessage('bot', data.reply || 'Ha ocurrido un error.');
        }

        if (data.tokens_remaining !== undefined) {
            updateTokens(data.tokens_remaining);
        }
    } catch (error) {
        showTyping(false);
        addMessage('bot', 'Ha ocurrido un error al procesar tu consulta. Revisa la configuración del servidor o la API.');
    } finally {
        sendBtn.disabled = false;
        msgInput.focus();
    }
}

async function resetConversation() {
    try {
        await fetch('reset.php', { method: 'POST' });
        chatMessages.innerHTML = `
            <div class="message bot">
                <div class="avatar">MB</div>
                <div class="bubble">
                    <p>Hola. Puedo ayudarte con SEO, redes sociales, publicidad online, email marketing y estrategia digital.</p>
                    <p>Cuéntame tu caso y te responderé con un enfoque práctico.</p>
                </div>
            </div>
        `;
        updateTokens(4000);
    } catch (e) {
        alert('No se pudo reiniciar la conversación.');
    }
}

function openEmailModal() {
    emailModal.classList.remove('hidden');
    emailFeedback.textContent = '';
}

function closeEmailModal() {
    emailModal.classList.add('hidden');
    emailFeedback.textContent = '';
}

async function sendConversationByEmail() {
    const email = emailInput.value.trim();
    const subject = subjectInput.value.trim();

    if (!email) {
        emailFeedback.textContent = 'Introduce un email válido.';
        return;
    }

    confirmEmailBtn.disabled = true;
    emailFeedback.textContent = 'Enviando...';

    try {
        const response = await fetch('send_email.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'email=' + encodeURIComponent(email) + '&subject=' + encodeURIComponent(subject)
        });

        const data = await response.json();
        if (data.success) {
            emailFeedback.textContent = data.message || 'Enviado correctamente.';
            setTimeout(() => closeEmailModal(), 900);
        } else {
            emailFeedback.textContent = (data.message || 'No se pudo enviar.') + (data.error ? '\n' + data.error : '');
        }
    } catch (error) {
        emailFeedback.textContent = 'No se pudo enviar la conversación.';
    } finally {
        confirmEmailBtn.disabled = false;
    }
}

sendBtn.addEventListener('click', () => sendMessage());
resetBtn.addEventListener('click', resetConversation);
emailBtn.addEventListener('click', openEmailModal);
closeModalBtn.addEventListener('click', closeEmailModal);
confirmEmailBtn.addEventListener('click', sendConversationByEmail);

suggestionButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        msgInput.value = btn.dataset.text || btn.textContent.trim();
        autoResize();
        msgInput.focus();
    });
});

msgInput.addEventListener('input', autoResize);
msgInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

emailModal.addEventListener('click', (e) => {
    if (e.target === emailModal) closeEmailModal();
});

autoResize();