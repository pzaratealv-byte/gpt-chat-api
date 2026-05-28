# 🤖 Chatbot con IA — PHP + OpenAI

> Asistente conversacional web configurable, con límite de tokens por sesión, historial de conversación y envío de transcripciones por correo. Desplegable en cualquier hosting PHP sin dependencias externas.

---

## 📋 Tabla de contenidos

- [Descripción](#descripción)
- [Características](#características)
- [Estructura del proyecto](#estructura-del-proyecto)
- [Requisitos](#requisitos)
- [Instalación](#instalación)
- [Configuración](#configuración)
- [Crear index.php y styles.css](#crear-indexphp-y-stylescss)
- [Personalizar el comportamiento del bot](#personalizar-el-comportamiento-del-bot)
- [Uso](#uso)
- [Arquitectura técnica](#arquitectura-técnica)
- [Seguridad](#seguridad)
- [Solución de problemas](#solución-de-problemas)
- [Licencia](#licencia)

---

## Descripción

Chatbot web construido en **PHP puro** (sin frameworks) que se conecta a la API de OpenAI. El comportamiento del asistente se define mediante un system prompt completamente personalizable en `chat.php`, por lo que puede adaptarse a cualquier dominio o especialidad.

La interfaz visual (`index.php` + `styles.css`) la crea el usuario final según sus necesidades. Este repositorio incluye toda la lógica de backend lista para conectar.

---

## Características

- 💬 **Chat conversacional** con historial de sesión completo
- 🧠 **System prompt configurable** — define la especialidad y las reglas del asistente
- 🔢 **Limitador de tokens por sesión** con indicador visual en tiempo real
- 🔄 **Reset de conversación** sin recargar la página
- 📧 **Envío de transcripción por email** vía SMTP propio (sin dependencias externas)
- 📱 **Responsive** — adaptado a escritorio y móvil
- ⚡ **Sin dependencias PHP externas** — el mailer SMTP está implementado desde cero con sockets

---

## Estructura del proyecto

```
/
├── index.php          # ✏️ Interfaz HTML — CREAR por el usuario
├── styles.css         # ✏️ Estilos visuales — CREAR por el usuario
├── script.js          # Lógica cliente: envío, reset, modal de email
├── chat.php           # Backend: recibe mensajes, llama a OpenAI, devuelve respuesta
├── reset.php          # Limpia el historial de sesión
├── send_email.php     # Recoge la solicitud de envío y orquesta el mailer
├── smtp_mailer.php    # Implementación SMTP manual con sockets PHP
├── config.php         # ⚠️ Credenciales y parámetros — NO subir a repositorios públicos
└── README.md
```

> `index.php` y `styles.css` son los únicos archivos que el usuario debe crear desde cero. El resto del backend está listo para usar.

---

## Requisitos

| Componente | Versión mínima |
|---|---|
| PHP | 7.4+ |
| Extensión `curl` | habilitada |
| Extensión `mbstring` | habilitada |
| Cuenta OpenAI | con API key activa |
| Buzón SMTP real | en el hosting (Plesk, cPanel…) |

---

## Instalación

### 1. Clonar o descargar el repositorio

```bash
git clone https://github.com/tu-usuario/chatbot-php-openai.git
```

### 2. Subir los archivos al hosting

Sube todos los archivos al directorio raíz del dominio (`/httpdocs` en Plesk, `public_html` en cPanel).

### 3. Configurar `config.php`

Edita el archivo con tus credenciales reales (ver sección [Configuración](#configuración)).

### 4. Crear `index.php` y `styles.css`

Crea la interfaz visual (ver sección [Crear index.php y styles.css](#crear-indexphp-y-stylescss)).

### 5. Verificar

Abre tu dominio. Si el chat responde, todo está funcionando.

---

## Configuración

Edita `config.php` con tus datos:

```php
<?php
return [
    // --- OpenAI ---
    'openai_api_key' => 'sk-proj-XXXXXXXXXXXXXXXXXXXXXXXX',
    'model'          => 'gpt-4o-mini',   // o 'gpt-4', 'gpt-4o'

    // --- Identidad del chatbot ---
    'from_name'      => 'Mi Chatbot',
    'site_name'      => 'tudominio.es',

    // --- Límite de tokens por conversación ---
    'max_tokens'     => 4000,

    // --- SMTP ---
    'smtp_host'      => 'mail.tudominio.es',
    'smtp_port'      => 587,          // 587 (TLS) o 465 (SSL)
    'smtp_secure'    => 'tls',        // 'tls' o 'ssl'
    'smtp_username'  => 'chatbot@tudominio.es',
    'smtp_password'  => 'contraseña_del_buzón',
    'from_email'     => 'chatbot@tudominio.es',
];
```

### Modelos OpenAI disponibles

| Modelo | Coste | Recomendado para |
|---|---|---|
| `gpt-4o-mini` | Bajo | Uso general |
| `gpt-4o` | Medio | Mayor calidad de respuesta |
| `gpt-4` | Alto | Máxima precisión |

---

## Crear index.php y styles.css

Estos dos archivos son la capa visual del chatbot. A continuación se muestra una estructura mínima funcional que puedes usar como punto de partida y personalizar a tu gusto.

### IDs y clases requeridos por `script.js`

`script.js` ya está escrito y busca estos elementos en el HTML. **Deben existir con exactamente estos IDs:**

| ID | Elemento | Descripción |
|---|---|---|
| `chatMessages` | `<div>` | Contenedor donde aparecen los mensajes |
| `msg` | `<textarea>` | Campo de texto del usuario |
| `sendBtn` | `<button>` | Botón de enviar mensaje |
| `resetBtn` | `<button>` | Botón de nueva conversación |
| `typingIndicator` | `<div>` | Indicador de "escribiendo..." |
| `tokenCounter` | `<div>` | Contenedor del contador de tokens |
| `tokenCount` | `<span>` | Número de tokens restantes |
| `emailBtn` | `<button>` | Abre el modal de envío por email |
| `emailModal` | `<div>` | Modal de envío por email |
| `closeModalBtn` | `<button>` | Cierra el modal |
| `confirmEmailBtn` | `<button>` | Confirma el envío del email |
| `emailInput` | `<input>` | Campo de dirección de email |
| `subjectInput` | `<input>` | Campo de asunto del email |
| `emailFeedback` | `<span>` | Mensaje de resultado del envío |

Los botones de sugerencias rápidas son opcionales: cualquier elemento con clase `suggestion` y atributo `data-text="..."` rellena automáticamente el campo de texto al pulsarlo.

---

### Plantilla mínima — `index.php`

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Chatbot</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <div class="chat-container">

        <!-- CABECERA -->
        <header class="chat-header">
            <h1>Mi Chatbot</h1>
            <div class="header-actions">
                <div id="tokenCounter">
                    Tokens: <span id="tokenCount">4000</span>
                </div>
                <button id="emailBtn">Enviar conversación</button>
                <button id="resetBtn">Nueva conversación</button>
            </div>
        </header>

        <!-- SUGERENCIAS RÁPIDAS (opcional) -->
        <div class="suggestions">
            <button class="suggestion" data-text="¿Qué puedes hacer?">¿Qué puedes hacer?</button>
            <button class="suggestion" data-text="Ayúdame con un ejemplo">Dame un ejemplo</button>
        </div>

        <!-- ÁREA DE MENSAJES -->
        <main id="chatMessages" class="chat-messages">
            <!-- Mensaje de bienvenida -->
            <div class="message bot">
                <div class="avatar">BOT</div>
                <div class="bubble">
                    <p>Hola, ¿en qué puedo ayudarte?</p>
                </div>
            </div>
        </main>

        <!-- INDICADOR "ESCRIBIENDO..." -->
        <div id="typingIndicator" class="typing hidden">
            <span></span><span></span><span></span>
        </div>

        <!-- CAMPO DE ENTRADA -->
        <footer class="chat-footer">
            <textarea id="msg" placeholder="Escribe tu mensaje..." rows="1"></textarea>
            <button id="sendBtn">Enviar</button>
        </footer>

    </div>

    <!-- MODAL DE EMAIL -->
    <div id="emailModal" class="modal hidden">
        <div class="modal-box">
            <h2>Enviar conversación por email</h2>
            <input id="emailInput" type="email" placeholder="correo@ejemplo.com">
            <input id="subjectInput" type="text" placeholder="Asunto (opcional)">
            <span id="emailFeedback"></span>
            <div class="modal-actions">
                <button id="confirmEmailBtn">Enviar</button>
                <button id="closeModalBtn">Cancelar</button>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
```

---

### Plantilla mínima — `styles.css`

```css
/* ── Variables — cambia aquí los colores y fuentes ── */
:root {
    --color-bg:       #0f172a;
    --color-surface:  #1e293b;
    --color-primary:  #4f46e5;
    --color-text:     #f1f5f9;
    --color-muted:    #94a3b8;
    --radius:         0.75rem;
    --font:           system-ui, sans-serif;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    background: var(--color-bg);
    color: var(--color-text);
    font-family: var(--font);
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

/* Contenedor principal */
.chat-container {
    width: 100%;
    max-width: 720px;
    height: 100vh;
    display: flex;
    flex-direction: column;
    background: var(--color-surface);
}

/* Cabecera */
.chat-header {
    padding: 1rem 1.25rem;
    background: var(--color-primary);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}
.chat-header h1 { font-size: 1.1rem; }
.header-actions { display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; }
.header-actions button {
    background: rgba(255,255,255,0.15);
    border: none;
    color: #fff;
    padding: 0.35rem 0.75rem;
    border-radius: var(--radius);
    cursor: pointer;
    font-size: 0.8rem;
}
.header-actions button:hover { background: rgba(255,255,255,0.25); }

/* Sugerencias */
.suggestions {
    display: flex;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    flex-wrap: wrap;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.suggestion {
    background: transparent;
    border: 1px solid var(--color-muted);
    color: var(--color-muted);
    padding: 0.3rem 0.75rem;
    border-radius: 999px;
    cursor: pointer;
    font-size: 0.8rem;
    transition: all 0.2s;
}
.suggestion:hover { border-color: var(--color-primary); color: var(--color-text); }

/* Área de mensajes */
.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 1.25rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* Mensajes */
.message { display: flex; gap: 0.75rem; align-items: flex-start; }
.message.user { flex-direction: row-reverse; }

.avatar {
    width: 2.2rem;
    height: 2.2rem;
    border-radius: 50%;
    background: var(--color-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.65rem;
    font-weight: 700;
    flex-shrink: 0;
}
.message.user .avatar { background: var(--color-muted); }

.bubble {
    max-width: 75%;
    background: rgba(255,255,255,0.06);
    padding: 0.75rem 1rem;
    border-radius: var(--radius);
    font-size: 0.9rem;
    line-height: 1.6;
}
.message.user .bubble { background: var(--color-primary); }
.bubble p + p { margin-top: 0.5rem; }

/* Indicador typing */
.typing {
    padding: 0.5rem 1.25rem;
    display: flex;
    gap: 0.3rem;
    align-items: center;
}
.typing.hidden { display: none; }
.typing span {
    width: 7px; height: 7px;
    background: var(--color-muted);
    border-radius: 50%;
    animation: bounce 1.2s infinite;
}
.typing span:nth-child(2) { animation-delay: 0.2s; }
.typing span:nth-child(3) { animation-delay: 0.4s; }
@keyframes bounce {
    0%, 80%, 100% { transform: translateY(0); }
    40%           { transform: translateY(-6px); }
}

/* Pie de entrada */
.chat-footer {
    display: flex;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-top: 1px solid rgba(255,255,255,0.07);
}
#msg {
    flex: 1;
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.1);
    color: var(--color-text);
    padding: 0.6rem 0.9rem;
    border-radius: var(--radius);
    resize: none;
    font-family: var(--font);
    font-size: 0.9rem;
}
#msg:focus { outline: none; border-color: var(--color-primary); }
#sendBtn {
    background: var(--color-primary);
    color: #fff;
    border: none;
    padding: 0 1.25rem;
    border-radius: var(--radius);
    cursor: pointer;
    font-weight: 600;
}
#sendBtn:hover { opacity: 0.85; }
#sendBtn:disabled { opacity: 0.4; cursor: not-allowed; }

/* Modal */
.modal {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.6);
    display: flex; align-items: center; justify-content: center;
    z-index: 100;
}
.modal.hidden { display: none; }
.modal-box {
    background: var(--color-surface);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: var(--radius);
    padding: 1.5rem;
    width: min(400px, 90vw);
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.modal-box h2 { font-size: 1rem; }
.modal-box input {
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.1);
    color: var(--color-text);
    padding: 0.6rem 0.9rem;
    border-radius: var(--radius);
    font-size: 0.9rem;
}
.modal-box input:focus { outline: none; border-color: var(--color-primary); }
#emailFeedback { font-size: 0.8rem; color: var(--color-muted); min-height: 1rem; }
.modal-actions { display: flex; gap: 0.5rem; justify-content: flex-end; }
.modal-actions button {
    padding: 0.45rem 1rem;
    border-radius: var(--radius);
    border: none;
    cursor: pointer;
    font-size: 0.85rem;
}
#confirmEmailBtn { background: var(--color-primary); color: #fff; }
#closeModalBtn { background: rgba(255,255,255,0.1); color: var(--color-text); }

/* Contador de tokens */
#tokenCounter {
    font-size: 0.75rem;
    color: var(--color-muted);
    border: 1px solid rgba(255,255,255,0.2);
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
}

/* Responsive */
@media (max-width: 480px) {
    .bubble { max-width: 90%; }
    .chat-header h1 { font-size: 0.95rem; }
}
```

---

## Personalizar el comportamiento del bot

Edita la variable `$systemPrompt` en `chat.php` para definir la especialidad, el tono y las reglas del asistente:

```php
$systemPrompt = <<<PROMPT
Eres un asistente especializado en [TU TEMÁTICA].

PUEDES responder preguntas sobre:
- [Tema 1]
- [Tema 2]
- [Tema 3]

Si el usuario pregunta sobre algo fuera de tu especialidad, responde educadamente
que solo puedes ayudar con [TU TEMÁTICA].

Responde siempre en español, de forma clara y con ejemplos prácticos.
PROMPT;
```

---

## Uso

**Enviar un mensaje** — escribe en el campo de texto y pulsa Enter o el botón Enviar.

**Sugerencias rápidas** — pulsa cualquier botón de sugerencia para rellenar el campo de texto automáticamente.

**Nueva conversación** — limpia el historial y reinicia el contador de tokens.

**Enviar por email** — pulsa "Enviar conversación", introduce el email de destino y el asunto, y confirma.

**Contador de tokens** — indica los tokens restantes en la sesión actual:

| Color | Tokens restantes |
|---|---|
| 🟢 Normal | > 1500 |
| 🟡 Aviso | 500 – 1500 |
| 🔴 Límite próximo | < 500 |

---

## Arquitectura técnica

```
Navegador (script.js)
    │
    ├── POST /chat.php          → Envía mensaje del usuario
    │       ├── $_SESSION['chat_history']   Historial acumulado
    │       ├── Calcula tokens usados       ceil(strlen_utf8 / 3.5)
    │       └── POST api.openai.com/v1/responses   cURL
    │
    ├── POST /reset.php         → Limpia sesión y reinicia tokens
    │
    └── POST /send_email.php    → Solicita envío de transcripción
            └── smtp_mailer.php
                    └── stream_socket_client()   Conexión SMTP raw
```

**Estimación de tokens:** fórmula `ceil(strlen_utf8 / 3.5)`, aproximación conservadora para texto en español. No requiere la librería tiktoken ni llamadas adicionales a la API.

**Mailer SMTP:** `smtp_mailer.php` implementa el protocolo SMTP con sockets PHP sin dependencias externas. Soporta SSL (465), STARTTLS (587), AUTH LOGIN y codificación UTF-8.

---

## Seguridad

> ⚠️ **Nunca subas `config.php` a un repositorio público.** Contiene la API key de OpenAI y las credenciales SMTP.

**Añade `config.php` al `.gitignore`:**
```
config.php
```

**Usa variables de entorno** en producción:
```php
'openai_api_key' => getenv('OPENAI_API_KEY'),
'smtp_password'  => getenv('SMTP_PASSWORD'),
```

**Rota la API key** si ha sido expuesta desde el [dashboard de OpenAI](https://platform.openai.com/api-keys).

---

## Solución de problemas

| Síntoma | Causa probable | Solución |
|---|---|---|
| El chat no responde | API key inválida o sin crédito | Revisa la key en OpenAI Platform |
| Error de conexión cURL | `curl` no habilitado en PHP | Actívalo en php.ini o contacta al hosting |
| El email no se envía | Credenciales SMTP incorrectas | Verifica usuario, contraseña y que el buzón exista |
| Error TLS | Puerto o `smtp_secure` incorrecto | Prueba 465 con `ssl` o 587 con `tls` |
| Tokens siempre en 0 | `max_tokens` muy bajo | Auméntalo en `config.php` |
| El bot responde cosas fuera del tema | System prompt incompleto | Añade reglas de exclusión en `$systemPrompt` |

---

## Licencia

Proyecto académico desarrollado como Trabajo de Fin de Módulo (TFM).  
Uso educativo y personal. Para uso comercial, contacta con el autor.

---

*Desarrollado con PHP y OpenAI API.* 🚀

