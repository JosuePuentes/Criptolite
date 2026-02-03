# Criptolite

App PHP con login, registro, dashboard, recargas y retiros. Base de datos MySQL.

**Una sola aplicación**: no hay carpeta “frontend” ni “backend” separados. Todo el proyecto es un único servicio PHP; Render y Vercel usan la raíz del repo.

---

## Local

1. **Base de datos**: Copia `db.example.php` como **`db.local.php`** y pon host, usuario, contraseña y nombre de la base.
2. Servidor con PHP y MySQL (XAMPP, WAMP, etc.) y crea las tablas necesarias (p. ej. `users`).

---

## Desplegar en Render

1. En [Render](https://render.com) crea un **Web Service** y enlaza el repo de GitHub.
2. Elige **Docker** como runtime (usa el `Dockerfile` del repo).
3. Crea una base **MySQL** en Render (o usa una externa) y en el servicio añade las variables de entorno:
   - `DB_HOST`
   - `DB_USER`
   - `DB_PASS`
   - `DB_NAME`
4. Opcional: usa el **Blueprint** con el `render.yaml` del repo para definir el servicio.

No hace falta indicar “dónde está el frontend o el backend”: el `Dockerfile` y `render.yaml` ya apuntan a la raíz del proyecto.

---

## Desplegar en Vercel

1. En [Vercel](https://vercel.com) importa el repo de GitHub.
2. Deja que use la configuración por defecto (lee `vercel.json`).
3. En **Settings → Environment Variables** añade:
   - `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`
4. La base de datos debe ser accesible desde internet (p. ej. PlanetScale, Railway, o MySQL en Render).

La raíz del proyecto es la app; `vercel.json` define qué archivos PHP se ejecutan y las rutas.

**Nota:** En Vercel PHP corre en modo serverless; las sesiones pueden no persistir bien. Si el login falla, prioriza **Render** para esta app.

---

## Repositorio

- GitHub: [github.com/JosuePuentes/Criptolite](https://github.com/JosuePuentes/Criptolite)
