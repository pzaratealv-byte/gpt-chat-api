# 🤖 Chatbot de Marketing Digital

> TFM — Asistente conversacional especializado en marketing digital, con límite de tokens por sesión, historial de conversación y envío de transcripciones por correo.

---

## 📋 Tabla de contenidos

- [Descripción](#descripción)
- [Características](#características)
- [Estructura del proyecto](#estructura-del-proyecto)
- [Requisitos](#requisitos)
- [Instalación](#instalación)
- [Configuración](#configuración)
- [Personalización (HTML y CSS)](#personalización-html-y-css)
- [Uso](#uso)
- [Arquitectura técnica](#arquitectura-técnica)
- [Seguridad](#seguridad)
- [Licencia](#licencia)

---

## Descripción

Chatbot web especializado en **marketing digital**, pensado como herramienta de apoyo para estudiantes de Formación Profesional. Responde preguntas sobre SEO, redes sociales, publicidad online, email marketing, analítica web, copywriting y estrategia digital.

Está construido en PHP puro (sin frameworks), se conecta a la API de OpenAI y puede desplegarse directamente en cualquier hosting con Plesk o cPanel.

---

## Características

- 💬 **Chat conversacional** con historial de sesión completo
- 🧠 **System prompt especializado** — solo responde sobre marketing digital
- 🔢 **Limitador de tokens por sesión** con indicador visual en tiempo real
- 🔄 **Reset de conversación** sin recargar la página
- 📧 **Envío de transcripción por email** vía SMTP propio (sin dependencias externas)
- 📱 **Responsive** — adaptado a escritorio y móvil
- ⚡ **Sin dependencias PHP externas** — el mailer SMTP está implementado desde cero con sockets

---

## Estructura del proyecto

```
/
├── index.php          # Interfaz principal (HTML + lógica de vista)  ← personalizable
├── styles.css         # Estilos visuales del chatbot                 ← personalizable
├── script.js          # Lógica cliente: envío, reset, modal de email
├── chat.php           # Backend: recibe mensajes, llama a OpenAI, devuelve respuesta
├── reset.php          # Limpia el historial de sesión
├── send_email.php     # Recoge la solicitud de envío y orquesta el mailer
├── smtp_mailer.php    # Implementación SMTP manual con sockets PHP
├── config.php         # ⚠️ Credenciales y parámetros — NO subir a repositorios públicos
└── README.md
```

> **Nota:** `index.php` y `styles.css` son los únicos archivos pensados para personalización visual. El resto implementa la lógica del sistema.

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
git clone https://github.com/tu-usuario/chatbot-marketing.git
```

### 2. Subir los archivos al hosting

Sube **todos los archivos** al directorio raíz de tu dominio, normalmente `/httpdocs` en Plesk o `public_html` en cPanel.

```
/httpdocs/
├── index.php
├── styles.css
├── script.js
├── chat.php
├── reset.php
├── send_email.php
├── smtp_mailer.php
└── config.php
```

### 3. Configurar `config.php`

Edita el archivo con tus credenciales reales (ver sección [Configuración](#configuración)).

### 4. Verificar

Abre tu dominio en el navegador. Si el chat responde, todo está funcionando.

---

## Configuración

Edita `config.php` con tus datos reales:

```php
<?php
return [
    // --- OpenAI ---
    'openai_api_key' => 'sk-proj-XXXXXXXXXXXXXXXXXXXXXXXX',
    'model'          => 'gpt-4o-mini',   // o 'gpt-4', 'gpt-4o', etc.

    // --- Identidad del chatbot ---
    'from_name'      => 'Chatbot Marketing',
    'site_name'      => 'tudominio.es',

    // --- Límite de tokens por conversación ---
    'max_tokens'     => 4000,

    // --- SMTP ---
    'smtp_host'      => 'mail.tudominio.es',  // o 'tudominio.es' si aplica
    'smtp_port'      => 587,                  // 587 para TLS, 465 para SSL
    'smtp_secure'    => 'tls',                // 'tls' o 'ssl'
    'smtp_username'  => 'chatbot@tudominio.es',
    'smtp_password'  => 'tu_contraseña_real',
    'from_email'     => 'chatbot@tudominio.es',
];
```

### Modelos OpenAI disponibles

| Modelo | Coste | Recomendado para |
|---|---|---|
| `gpt-4o-mini` | Bajo | Uso general, estudiantes |
| `gpt-4o` | Medio | Mayor calidad de respuesta |
| `gpt-4` | Alto | Máxima precisión |

### Configuración SMTP habitual en Plesk

```
smtp_host:    mail.tudominio.es  (o solo tudominio.es)
smtp_port:    587
smtp_secure:  tls
smtp_username: chatbot@tudominio.es
smtp_password: [contraseña del buzón en Plesk]
```

---

## Personalización (HTML y CSS)

Los únicos archivos que necesitas modificar para adaptar el chatbot a tu imagen son:

### `index.php` — Estructura HTML

Contiene la interfaz visible: cabecera, área de mensajes, campo de entrada, botones de sugerencias y modal de email.

Elementos clave que puedes cambiar:

```html
<!-- Nombre/logo del chatbot -->
<h1>Tu Asistente de Marketing</h1>

<!-- Subtítulo o descripción -->
<p>Especialista en SEO, redes sociales y publicidad digital</p>

<!-- Botones de sugerencias rápidas -->
<button class="suggestion" data-text="¿Cómo mejoro mi posicionamiento SEO?">
    SEO
</button>
```

### `styles.css` — Diseño visual

El archivo de estilos controla colores, tipografía, espaciado y animaciones. Variables CSS recomendadas para centralizar tu identidad visual:

```css
:root {
    --color-primary:    #4f46e5;   /* Color principal (botones, avatar bot) */
    --color-secondary:  #7c3aed;   /* Acento / degradados */
    --color-bg:         #0f172a;   /* Fondo general */
    --color-surface:    #1e293b;   /* Fondo de burbujas y paneles */
    --color-text:       #f1f5f9;   /* Texto principal */
    --color-muted:      #94a3b8;   /* Texto secundario */
    --radius-bubble:    1.25rem;   /* Redondeo de los mensajes */
    --font-main:        'Inter', sans-serif;
}
```

---

## Uso

### Enviar un mensaje

Escribe en el campo de texto y pulsa **Enter** o el botón de enviar. También puedes usar los botones de sugerencias rápidas.

### Nueva conversación

Pulsa **"Nueva conversación"** para limpiar el historial y reiniciar el contador de tokens.

### Enviar transcripción por email

1. Pulsa el botón **"Enviar conversación"** (en la cabecera)
2. Introduce la dirección de destino y el asunto
3. Pulsa **"Enviar"** — recibirás el historial completo en texto plano

### Contador de tokens

El indicador en la cabecera muestra los tokens restantes en la sesión:

- 🟢 **> 1500** — Normal
- 🟡 **500–1500** — Aviso
- 🔴 **< 500** — Próximo al límite

---

## Arquitectura técnica

```
Navegador (script.js)
    │
    ├── POST /chat.php          → Envía mensaje del usuario
    │       │
    │       ├── $_SESSION['chat_history']   Historial acumulado
    │       ├── Calcula tokens usados       Aproximación: strlen / 3.5
    │       └── POST api.openai.com/v1/responses   cURL
    │
    ├── POST /reset.php         → Limpia sesión y reinicia tokens
    │
    └── POST /send_email.php    → Solicita envío de transcripción
            │
            └── smtp_mailer.php
                    └── stream_socket_client()   Conexión SMTP raw
```

### Estimación de tokens

La estimación usa la fórmula `ceil(strlen_utf8 / 3.5)`, que aproxima de forma conservadora el consumo en español. No es exacta respecto a la tokenización real de OpenAI (tiktoken), pero evita llamadas adicionales a la API.

### Mailer SMTP

`smtp_mailer.php` implementa el protocolo SMTP directamente con sockets PHP (`stream_socket_client`), sin depender de PHPMailer ni SwiftMailer. Soporta:

- Conexión directa SSL (puerto 465)
- STARTTLS (puerto 587)
- Autenticación AUTH LOGIN
- Codificación UTF-8 con subject en Base64

---

## Seguridad

> ⚠️ **Nunca subas `config.php` a un repositorio público.** Contiene tu API key de OpenAI y las credenciales SMTP.

### Recomendaciones

1. **Añade `config.php` al `.gitignore`:**

```
# .gitignore
config.php
```

2. **Usa variables de entorno** en producción en lugar de valores hardcodeados:

```php
'openai_api_key' => getenv('OPENAI_API_KEY'),
'smtp_password'  => getenv('SMTP_PASSWORD'),
```

3. **Limita el acceso a los archivos PHP** que no deben ser llamados directamente por el usuario (reset.php, send_email.php) con reglas en `.htaccess` si es necesario.

4. **Rota la API key** si ha sido expuesta en algún momento desde el [dashboard de OpenAI](https://platform.openai.com/api-keys).

---

## Solución de problemas

| Síntoma | Causa probable | Solución |
|---|---|---|
| El chat no responde | API key inválida o sin crédito | Revisa la key en OpenAI Platform |
| Error de conexión cURL | `curl` no habilitado en PHP | Actívalo en php.ini o contacta al hosting |
| El email no se envía | Credenciales SMTP incorrectas | Verifica usuario/contraseña y que el buzón exista |
| Error TLS | Puerto o `smtp_secure` incorrecto | Prueba cambiando a puerto 465 con `ssl` |
| Tokens siempre en 0 | `max_tokens` muy bajo | Auméntalo en `config.php` |

---

## Licencia

Proyecto académico desarrollado como Trabajo de Fin de Módulo (TFM).  
Uso educativo y personal. Para uso comercial, contacta con el autor.

---

*Desarrollado con PHP, OpenAI API y mucho marketing digital.* 🚀
