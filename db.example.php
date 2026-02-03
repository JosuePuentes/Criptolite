<?php
// db.example.php — Para desarrollo local con MongoDB Atlas.
// Copia como db.local.php y define $mongoClient y $db, o usa variable de entorno MONGODB_URI.
//
// En producción (Vercel/Render) configura solo:
//   MONGODB_URI = mongodb+srv://usuario:password@cluster.xxxxx.mongodb.net/criptolite?retryWrites=true&w=majority
// Opcional: MONGODB_DB_NAME = criptolite
