Resumen del Proyecto - maya-grok (03 de abril de 2025)
Estado Actual
Este proyecto, desarrollado con Laravel y Vue.js (Inertia), implementa un sistema de gestión de portafolios y activos financieros con monitoreo en tiempo real y notificaciones basadas en estrategias definidas.

Portafolios
CRUD completo: Crear, leer, actualizar y eliminar portafolios.
Autorización: Solo el usuario propietario puede gestionar sus portafolios (usando PortfolioPolicy).
Frontend: Vista en Portfolios/Index.vue con formulario de creación, lista de portafolios, modal de edición con enfoque automático en el campo "Nombre", y diseño limpio sin elementos innecesarios.
Campos: id, user_id, name, description, status (active/inactive), created_at, updated_at.
Activos
CRUD funcional: Crear, leer, actualizar y eliminar activos dentro de un portafolio.
Autorización: Vinculada al portafolio propietario (AssetPolicy).
Integración con API: Conexión a Alpha Vantage para obtener valores actuales (AssetValueService).
Campos: id, portfolio_id, name, symbol, description, value, comments, max_value, base_value, created_at, updated_at.
symbol: Identificador para la API (ej. "AAPL").
comments: Justificación de la inversión.
max_value: Máximo histórico, actualizado por el monitoreo.
base_value: Valor mínimo de la inversión, actualizado por el monitoreo.
Frontend: Vista en Assets/Index.vue con formulario de creación, lista de activos, modal de edición (enfoque en "Nombre"), y visualización de datos.
Estrategias
Tabla: strategies con campos id, asset_id, buy_threshold, sell_threshold, created_at, updated_at.
Relación: Cada activo tiene una estrategia (hasOne en Asset, belongsTo en Strategy).
Gestión: Configurable al editar un activo en el modal.
Monitoreo
Comando: assets:monitor actualiza value, max_value, y base_value de los activos usando la API.
Scheduler: Configurado para ejecutarse cada minuto (everyMinute() en Kernel.php).
Implementación: Usa MonitorAssets command y AssetValueService.
Notificaciones
Sistema: Notificaciones por correo cuando el valor de un activo cruza los umbrales de compra (buy_threshold) o venta (sell_threshold).
Clase: AssetThresholdReached envía emails personalizados.
Configuración: Requiere un servidor de correo (ej. Mailtrap en .env).
Dependencias
Backend: Laravel, Guzzle (composer require guzzlehttp/guzzle).
Frontend: Vue.js, Inertia.js, Tailwind CSS.
API: Alpha Vantage (clave en .env: ALPHA_VANTAGE_API_KEY).
Configuración
.env:
text

Contraer

Ajuste

Copiar
ALPHA_VANTAGE_API_KEY=tu_clave_api
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_usuario_mailtrap
MAIL_PASSWORD=tu_contraseña_mailtrap
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="example@example.com"
MAIL_FROM_NAME="Maya Grok"
Pendiente
Probar el monitoreo en tiempo real y manejar los límites de la API (5 solicitudes/minuto en el plan gratuito de Alpha Vantage).
Configurar notificaciones en tiempo real (WebSockets o polling).
Mejorar la interfaz de Assets/Index.vue (filtros, ordenamiento, actualización dinámica de valores).
Añadir reglas avanzadas a las estrategias (porcentajes, alertas personalizadas).
Desplegar el proyecto en un entorno de prueba/production.
Próximos Pasos
Optimizar el monitoreo para respetar los límites de la API y probar notificaciones.
Implementar actualizaciones en tiempo real en el frontend (WebSockets con Laravel Echo o polling).
Pulir la UI de activos con filtros y visualización dinámica.
