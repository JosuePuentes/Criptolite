# Criptolite

App PHP con login, registro, dashboard, recargas y retiros. **Base de datos: MongoDB (Atlas).**

Todo está en esta carpeta: no hace falta “activar” nada ni seguir pasos extra. En producción solo configuras la base de datos y el servicio (Render) se encarga del resto.

---

## Poner en línea (Render)

1. **MongoDB Atlas**: crea un cluster gratis en [mongodb.com/cloud/atlas](https://www.mongodb.com/cloud/atlas) y obtén la **Connection String**.
2. En [Render](https://render.com) crea un **Web Service**, enlaza este repo y elige **Docker** (usa el `Dockerfile` del proyecto).
3. En el servicio, **Environment** → añade:
   - `MONGODB_URI` = tu Connection String (ej: `mongodb+srv://user:pass@cluster.xxxxx.mongodb.net/criptolite?retryWrites=true&w=majority`).

Listo: Render levanta PHP, nginx y las dependencias. No hay que iniciar XAMPP, MySQL ni ningún servicio manual.

Opcional: en Atlas → Network Access permite `0.0.0.0/0` para que Render pueda conectar.

Si usas **Blueprint**: el repo incluye `render.yaml`; en Render puedes importar el repo y definir `MONGODB_URI` en el dashboard.

---

## Local (opcional)

Solo si quieres probar en tu PC: PHP 8 con extensión `mongodb`, Composer, y `composer install`. Crea `db.local.php` (usa `db.example.php` como plantilla) o define `MONGODB_URI`. Luego `php -S localhost:8000`. Las colecciones se crean solas al usarlas.

---

**Vercel**: el runtime PHP de Vercel suele no incluir la extensión MongoDB; para esta app se recomienda usar **Render**.

---

Repo: [github.com/JosuePuentes/Criptolite](https://github.com/JosuePuentes/Criptolite)
