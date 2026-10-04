# Gestor de clientes

[English version](README.en.md)

ABM (alta, baja, modificación) de clientes con login, hecho en **PHP sin frameworks** (compatible con PHP 5.6 a 8.x), **MySQL**, **Bootstrap 5** y **jQuery**.

![Listado](docs/capturas/2-listado.png)

## Funcionalidades
- Login con contraseñas hasheadas (`password_hash` / `password_verify`), regeneración de sesión y bloqueo de 5 minutos después de 5 intentos fallidos.
- Listado con búsqueda (razón social, CUIT, email, ciudad), filtro por estado y paginación.
- Alta y edición en un mismo formulario con validación del lado del servidor: CUIT con dígito verificador, CUIT único, email y teléfono.
- Baja con confirmación en un modal y solo por POST.
- Formato automático del CUIT con jQuery mientras se escribe.

## Seguridad
| Riesgo | Cómo se resuelve |
|---|---|
| Inyección SQL | Todas las consultas son preparadas con PDO (`includes/clientes.php`). |
| XSS | Toda salida pasa por `htmlspecialchars` (función `e()`). |
| CSRF | Token por sesión en cada formulario POST (`verificar_csrf()`). |
| Fuerza bruta | Límite de intentos de login por sesión. |
| Acceso a archivos internos | `.htaccess` deniega `includes/`, `data/` y `sql/`. |

## Estructura
```
index.php        Listado, búsqueda y paginación
cliente.php      Alta y edición
eliminar.php     Baja (POST + CSRF)
login.php        Ingreso
logout.php       Salida (POST + CSRF)
includes/
  config.php     Configuración de base de datos
  db.php         Conexión PDO (MySQL, o SQLite en modo demo)
  funciones.php  Sesión, autenticación, CSRF, mensajes flash
  clientes.php   Consultas y validaciones de clientes
  layout.php     Encabezado y pie comunes
sql/schema.sql   Tablas MySQL + usuario demo
```

## Casos de uso documentados
| # | Caso | Resultado esperado |
|---|---|---|
| 1 | Entrar a `index.php` sin sesión | Redirige a `login.php`. |
| 2 | Login con contraseña incorrecta | Mensaje "Usuario o contraseña incorrectos"; al 5.º intento, bloqueo de 5 minutos. |
| 3 | Alta con razón social de 1 letra, CUIT inválido y email mal formado | No guarda; marca los tres campos con su error. |
| 4 | Alta con un CUIT que ya existe | No guarda; "Ya existe un cliente con ese CUIT". |
| 5 | Razón social con HTML (`<b>Test</b>`) | Se guarda tal cual y se muestra escapada, sin interpretar el HTML. |
| 6 | Baja enviada con un token CSRF inválido | Responde 403 y no borra. |
| 7 | Búsqueda "Almac" y filtro "Inactivos" | Lista solo coincidencias y actualiza el contador. |

## Instalación
**Local (modo demo):** `php -S localhost:8000` y entrar con `demo` / `demo1234`. Crea `data/demo.sqlite` con 12 clientes de ejemplo.

**Servidor con MySQL (ej. InfinityFree):**
1. Crear la base y ejecutar `sql/schema.sql` en phpMyAdmin.
2. Completar `DB_DSN`, `DB_USER` y `DB_PASS` en `includes/config.php`.
3. Subir los archivos a `htdocs/`.

## Capturas
| Login | Validaciones | Celular |
|---|---|---|
| ![](docs/capturas/1-login.png) | ![](docs/capturas/4-validaciones.png) | ![](docs/capturas/5-celular.png) |
