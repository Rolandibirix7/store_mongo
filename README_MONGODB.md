# Store — Migrado a MongoDB

## ¿Qué cambió?

| Antes (MySQL)             | Ahora (MongoDB)                            |
|---------------------------|--------------------------------------------|
| `mysqli` / `$conn`        | `MongoDB\Client` / colecciones             |
| IDs numéricos (`int`)     | `ObjectId` de MongoDB (cadena hex 24 chars)|
| `SELECT * FROM tabla`     | `$col->find()`                             |
| `INSERT INTO ...`         | `$col->insertOne([...])`                   |
| `UPDATE ... WHERE id=`    | `$col->updateOne(['_id'=>$oid], ['$set'=>])`|
| `DELETE FROM ... WHERE`   | `$col->deleteOne(['_id'=>$oid])`           |
| `WHERE nombre LIKE %x%`   | Regex: `new MongoDB\BSON\Regex($q, 'i')`  |
| `JOIN` entre tablas       | Lookup manual por `categoria_id` (ObjectId)|

## Instalación paso a paso

### 1. Instalar la extensión PHP de MongoDB
```bash
# Ubuntu/Debian
sudo apt install php-mongodb

# O vía PECL
pecl install mongodb
# Agregar al php.ini: extension=mongodb.so
```

### 2. Instalar la librería PHP (Composer)
```bash
cd store/
composer install
```

### 3. Migrar los datos de MySQL → MongoDB
```bash
# Con MySQL y MongoDB corriendo:
php migrate_to_mongo.php
```

### 4. Configurar la conexión
Edita `config/conexion.php` si tu MongoDB usa autenticación o un host diferente:
```php
$mongoClient = new MongoDB\Client("mongodb://usuario:password@host:27017");
```

## Estructura de documentos en MongoDB

### Colección `categorias`
```json
{
  "_id": ObjectId("..."),
  "nombre": "Jabones",
  "imagen": "jabones.png"
}
```

### Colección `productos`
```json
{
  "_id": ObjectId("..."),
  "nombre": "Jabón Barra",
  "descripcion": "Jabón natural",
  "precio": 5.00,
  "imagen": "jabon.jpg",
  "categoria_id": ObjectId("...") // referencia a categorias
}
```

### Colección `usuarios`
```json
{
  "_id": ObjectId("..."),
  "usuario": "admin",
  "password": "hash_md5_o_bcrypt",
  "rol": "admin"
}
```

## Notas importantes
- Los IDs en las URLs ahora son ObjectIds de 24 caracteres (ej: `?id=6612abc...`)
- La búsqueda usa regex de MongoDB en lugar de `LIKE`
- `categoria_id` en productos almacena el `ObjectId` de la categoría (no un número)
