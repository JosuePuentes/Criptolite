# Criptolite

App PHP con login, registro, dashboard, recargas y retiros. **Base de datos: MongoDB (Atlas).**

**Una sola aplicación**: todo el proyecto es un único servicio PHP; Render y Vercel usan la raíz del repo.

---

## Requisitos

- **PHP 8** con extensión `mongodb` (pecl) y **Composer**
- **MongoDB Atlas** (gratis en [mongodb.com/cloud/atlas](https://www.mongodb.com/cloud/atlas))

---

## Local

1. **MongoDB Atlas**: crea un cluster gratis en [mongodb.com/cloud/atlas](https://www.mongodb.com/cloud/atlas), obtén la **Connection String**.
2. **PHP 8** con extensión **mongodb** (`pecl install mongodb`) y **Composer** ([getcomposer.org](https://getcomposer.org)).
3. En la raíz del proyecto:
   ```bash
   composer install
   ```
4. **Variable de entorno** (o crea `db.local.php` con la conexión):
   - `MONGODB_URI` = `mongodb+srv://usuario:password@cluster.xxxxx.mongodb.net/criptolite?retryWrites=true&w=majority`
5. Ejecuta la app (XAMPP, WAMP o `php -S localhost:8000`).

Las colecciones (`users`, `recargas`, `retiros`, `planes_disponibles`, `compras`, `historial_ganancias`) se crean solas al usarlas. Puedes añadir un plan desde el panel admin.

---

## Desplegar en Render

1. En [Render](https://render.com) crea un **Web Service** y enlaza el repo.
2. Runtime: **Docker** (usa el `Dockerfile` del repo).
3. Variables de entorno:
   - `MONGODB_URI` = tu Connection String de MongoDB Atlas (incluye usuario, contraseña y nombre de base en la URL).

Opcional: **Blueprint** con `render.yaml` (ajusta las variables en el dashboard).

---

## Desplegar en Vercel

1. En [Vercel](https://vercel.com) importa el repo.
2. Variables de entorno: **`MONGODB_URI`** (Connection String de Atlas).
3. **Importante**: el runtime PHP de Vercel puede no incluir la extensión `mongodb`. En ese caso:
   - Ejecuta `composer install` en local y sube la carpeta `vendor/` al repo, o
   - Usa **Render** para esta app (recomendado con MongoDB).

---

## Repositorio

- GitHub: [github.com/JosuePuentes/Criptolite](https://github.com/JosuePuentes/Criptolite)
