# gpt-chat-api
API REST en PHP para chat con OpenAI GPT-4 y generación de imágenes con DALL-E 3. Incluye autenticación, historial de conversaciones y envío por email. Fácilmente personalizable para cualquier temática.
gpt-chat-api/

# 🤖 GPT Chat API

[![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?logo=php)](https://php.net)
[![OpenAI](https://img.shields.io/badge/OpenAI-GPT--4-412991?logo=openai)](https://openai.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

API REST en PHP para integrar chat con inteligencia artificial (OpenAI GPT-4) y generación de imágenes (DALL-E 3) en cualquier proyecto web o aplicación.

---

## 📋 Tabla de Contenidos

- [Características](#-características)
- [Requisitos](#-requisitos)
- [Instalación](#-instalación)
- [Configuración](#️-configuración)
- [Endpoints](#-endpoints)
- [Ejemplos](#-ejemplos)
- [Personalización](#-personalización)
- [Licencia](#-licencia)

---

## ✨ Características

| Feature | Descripción |
|---------|-------------|
| 💬 **Chat GPT-4** | Conversaciones con contexto y memoria de sesión |
| 🎨 **DALL-E 3** | Generación de imágenes con IA |
| 🔐 **Autenticación** | Protección por contraseña con sesiones |
| 📧 **Email** | Envío de conversaciones por correo |
| 🎯 **Personalizable** | Configura la temática del asistente |
| 📱 **REST API** | Fácil integración con cualquier frontend |

---

## 📦 Requisitos

- PHP 7.4 o superior
- Extensión cURL habilitada
- API Key de [OpenAI](https://platform.openai.com/api-keys)

---

## 🚀 Instalación

### 1. Descargar

```bash
git clone https://github.com/tuusuario/gpt-chat-api.git
cd gpt-chat-api

## 🚀 Estructura
gpt-chat-api/
│
├── 📄 README.md          ← Documentación para GitHub
├── 📄 LICENSE            ← Licencia MIT
├── 📄 .gitignore         ← ficheros opcionales
│
├── 📁 api/               ← CÓDIGO PRINCIPAL (backend)
│   │
│   ├── auth.php          ← Login/logout (protección con contraseña)
│   ├── chat.php          ← Chat con GPT-4 (lo más importante)
│   ├── generate-image.php ← Generar imágenes con DALL-E
│   ├── send-email.php    ← Enviar conversaciones por email
│   ├── config-endpoint.php ← Configuración pública del asistente
│   ├── health.php        ← Verificar que todo funciona
│   │
│   └── config.example.php ← EJEMPLO de configuración (sin datos reales)
│                            ↑
│                            └── Copias esto a config.php en tu servidor
│
└── 📁 examples/          ← EJEMPLOS de cómo usar la API
    │
    ├── javascript-example.html  ← Ejemplo en JavaScript
    ├── python_example.py        ← Ejemplo en Python
    └── curl_examples.sh         ← Ejemplos con comandos cURL
