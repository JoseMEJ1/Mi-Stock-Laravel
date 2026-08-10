# Documentación unificada de la API Mi-Stock

- Versión: 1.0
- Fecha: 2026
- Autor: Grupo Lazarus
- Propósito: unificar la documentación de la API para frontend, despliegue y prueba de integración.

> Nota: este documento representa el contrato de API unificado recomendado para Mi-Stock. Mantiene la estructura simple de la documentación del usuario y añade los endpoints adicionales necesarios para administración, operaciones de negocio y demos.

## 1. Resumen ejecutivo

| Aspecto | Estado |
|---------|--------|
| Estructura de respuesta | ✅ Definida |
| Autenticación con Bearer Token | ✅ Definida |
| Endpoints de login/register | ✅ Definidos |
| CRUD de recursos | ✅ Definido |
| Operaciones de compras | ✅ Definidas |
| Operaciones de ventas | ✅ Definidas |
| Movimientos de inventario | ✅ Definidos |
| Reportes | ✅ Definidos |
| Realtime / Broadcasting | ✅ Definido |
| Variables de entorno | ✅ Definidas |
| Ejemplos con curl | ✅ Incluidos |

## 2. Estructura de respuesta

Todas las respuestas exitosas deben seguir este envelope:

```json
{
  "status": "success",
  "message": "Mensaje descriptivo",
  "data": {}
}
```

Errores:

```json
{
  "status": "error",
  "message": "Descripción del error"
}
```

## 3. Autenticación

La API usa Bearer Token en el header `Authorization`:

```http
Authorization: Bearer <token>
Content-Type: application/json
```

El token se obtiene en los endpoints de autenticación.

### Respuestas de autenticación

- `401 Unauthorized` si el token no existe o es inválido.
- `200 OK` al iniciar sesión.
- `201 Created` al registrar un usuario.

## 4. Endpoints base

| Método | Endpoint | Descripción | Autenticación |
|--------|----------|-------------|---------------|
| POST | `/api/register` | Registrar usuario | No |
| POST | `/api/login` | Iniciar sesión | No |
| POST | `/api/auth/recovery` | Recuperar contraseña | No |
| POST | `/api/auth/reset-password` | Restablecer contraseña | No |
| POST | `/api/auth/refresh` | Refrescar token | Sí |
| GET | `/api/me` | Obtener usuario autenticado | Sí |
| POST | `/api/logout` | Cerrar sesión | Sí |

### Ejemplo de registro

```json
{
  "name": "Juan Pérez",
  "email": "juan@example.com",
  "password": "12345678",
  "password_confirmation": "12345678"
}
```

### Ejemplo de login

```json
{
  "email": "juan@example.com",
  "password": "12345678"
}
```

## 5. Recursos CRUD

Todos los recursos soportan el patrón CRUD básico:

- `GET /api/<resource>` → listar
- `GET /api/<resource>/{id}` → ver detalle
- `POST /api/<resource>` → crear
- `PUT/PATCH /api/<resource>/{id}` → actualizar
- `DELETE /api/<resource>/{id}` → eliminar

### Convención de nombres

- Los productos usan `code` como identificador único (en lugar de `sku`) para mantener consistencia con el resto del sistema.
- Los usuarios usan `role` como nombre del rol (en lugar de `role_id`) para simplificar el consumo desde frontend.

### 5.1 Categorías

Ruta base: `/api/categories`

Campos permitidos:

```json
{
  "name": "Electrónicos",
  "slug": "electronicos",
  "parent_id": "<id-opcional>",
  "description": "Productos electrónicos"
}
```

### 5.2 Proveedores

Ruta base: `/api/suppliers`

Campos permitidos:

```json
{
  "name": "Proveedor XYZ",
  "code": "PV-001",
  "email": "contacto@proveedor.com",
  "phone": "5551234567",
  "address": "Calle 123",
  "notes": "Observaciones"
}
```

### 5.3 Sucursales

Ruta base: `/api/branches`

Campos permitidos:

```json
{
  "name": "Sucursal Centro",
  "code": "SC-01",
  "address": "Av. Principal 100",
  "phone": "555000111",
  "is_main": true
}
```

### 5.4 Clientes

Ruta base: `/api/clients`

Campos permitidos:

```json
{
  "name": "Cliente Demo",
  "email": "cliente@example.com",
  "phone": "555777999",
  "address": "Col. Centro",
  "tax_id": "ABC123"
}
```

### 5.5 Productos

Ruta base: `/api/products`

Campos permitidos:

```json
{
  "code": "PR-001",
  "name": "Laptop",
  "description": "Laptop de ejemplo",
  "category_id": "<category-id>",
  "supplier_id": "<supplier-id>",
  "cost": 1500.5,
  "price": 2200,
  "unit": "unidad",
  "barcode": "789123456"
}
```

### 5.6 Usuarios

Ruta base: `/api/users`

Campos permitidos:

```json
{
  "name": "Admin",
  "email": "admin@example.com",
  "password": "12345678",
  "role": "admin"
}
```

### 5.7 Roles

Ruta base: `/api/roles`

Campos permitidos:

```json
{
  "name": "Administrador",
  "description": "Acceso completo"
}
```

### 5.8 Permisos

Ruta base: `/api/permissions`

Campos permitidos:

```json
{
  "name": "products.create",
  "description": "Crear productos"
}
```

## 6. Operaciones de negocio

### 6.1 Compras

Ruta base: `/api/purchases`

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/purchases` | Listar compras |
| POST | `/api/purchases` | Crear compra |
| GET | `/api/purchases/{id}` | Ver compra |
| PUT | `/api/purchases/{id}` | Actualizar compra |
| DELETE | `/api/purchases/{id}` | Eliminar compra |
| POST | `/api/purchases/{id}/receive` | Confirmar recepción |

Body sugerido:

```json
{
  "reference": "OC-001",
  "supplier_id": "<supplier-id>",
  "branch_id": "<branch-id>",
  "purchased_at": "2026-08-10",
  "status": "completed",
  "items": [
    {
      "product_id": "<product-id>",
      "quantity": 10,
      "price": 1500.5
    }
  ]
}
```

> Convención unificada: en compras y ventas se usa `items` para los detalles y `price` para el costo unitario o precio unitario. Esto simplifica el consumo desde frontend y evita ambigüedad con los nombres de campo.

### 6.2 Ventas

Ruta base: `/api/sales`

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/sales` | Listar ventas |
| POST | `/api/sales` | Crear venta |
| GET | `/api/sales/{id}` | Ver venta |
| PUT | `/api/sales/{id}` | Actualizar venta |
| DELETE | `/api/sales/{id}` | Eliminar venta |
| POST | `/api/sales/{id}/ticket` | Generar ticket |
| POST | `/api/sales/{id}/invoice` | Generar factura |
| POST | `/api/sales/{id}/return` | Registrar devolución |

Body sugerido:

```json
{
  "reference": "FV-001",
  "client_id": "<client-id>",
  "branch_id": "<branch-id>",
  "sold_at": "2026-08-10",
  "status": "completed",
  "items": [
    {
      "product_id": "<product-id>",
      "quantity": 2,
      "price": 2200
    }
  ]
}
```

### 6.3 Movimientos de inventario

Ruta base: `/api/stock-movements`

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/stock-movements` | Listar movimientos |
| POST | `/api/stock-movements` | Crear movimiento |
| GET | `/api/stock-movements/{id}` | Ver movimiento |

Body sugerido:

```json
{
  "product_id": "<product-id>",
  "branch_id": "<branch-id>",
  "movement_type": "in",
  "quantity": 5,
  "cost": 1500,
  "reference": "MOV-001",
  "note": "Ajuste de inventario"
}
```

Valores válidos para `movement_type`:
- `in`
- `out`
- `adjustment`
- `transfer`

### 6.4 Transferencias, conteos y kardex

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/inventory/transfer` | Transferir inventario |
| POST | `/api/inventory/physical-count` | Conteo físico |
| GET | `/api/inventory/kardex/{productId}` | Consultar kardex |

### 6.5 Snapshots y logs

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/inventory-snapshots` | Listar snapshots |
| POST | `/api/inventory-snapshots` | Crear snapshot |
| GET | `/api/inventory-snapshots/{id}` | Ver snapshot |
| GET | `/api/logs` | Listar logs |
| GET | `/api/logs/{id}` | Ver log |

## 7. Reportes

Se recomienda usar verbos `GET` para reportes simples y filtros en query params.

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/reports/sales` | Reporte de ventas |
| GET | `/api/reports/purchases` | Reporte de compras |
| GET | `/api/reports/inventory` | Reporte de inventario |
| GET | `/api/reports/out-of-stock` | Productos agotados |
| GET | `/api/reports/expiring` | Productos por vencer |

Query params sugeridos:
- `from`
- `to`
- `branch_id`

## 8. Endpoints adicionales recomendados

El contrato completo de Mi-Stock incluye además operaciones de búsqueda, gestión específica de productos, configuración y auditoría. Estos endpoints se recomiendan como parte del contrato unificado y pueden implementarse en fases posteriores.

### 8.1 Búsquedas

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/products/search` | Buscar productos |
| GET | `/api/users/search` | Buscar usuarios |
| GET | `/api/categories/search` | Buscar categorías |
| GET | `/api/branches/search` | Buscar sucursales |
| GET | `/api/inventory/movements/search` | Buscar movimientos |

### 8.2 Operaciones de productos

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/products/{id}/stock` | Consultar existencias |
| GET | `/api/products/{id}/history` | Historial del producto |
| POST | `/api/products/{id}/barcode` | Generar código de barras |
| PUT | `/api/products/{id}/barcode` | Registrar código de barras |
| POST | `/api/products/import` | Importar productos |
| GET | `/api/products/export` | Exportar productos |
| PATCH | `/api/products/{id}/price` | Cambiar precio |
| PATCH | `/api/products/{id}/cost` | Cambiar costo |
| PUT | `/api/products/{id}/category` | Asignar categoría |
| PUT | `/api/products/{id}/supplier` | Asignar proveedor |
| PUT | `/api/products/{id}/location` | Asignar ubicación |
| POST | `/api/products/{id}/images` | Registrar imagen |
| DELETE | `/api/products/{id}/images` | Eliminar imagen |

### 8.3 Configuración

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| PUT | `/api/config/currency` | Configurar moneda |
| PUT | `/api/config/language` | Configurar idioma |
| PUT | `/api/config/timezone` | Configurar zona horaria |
| PUT | `/api/config/backup` | Configurar respaldos |
| POST | `/api/config/backup/execute` | Realizar respaldo |
| POST | `/api/config/backup/restore` | Restaurar respaldo |
| PUT | `/api/config/email` | Configurar correo |
| PUT | `/api/config/api` | Configurar API |
| PUT | `/api/config/barcode-reader` | Configurar lectores |
| PUT | `/api/config/label-printer` | Configurar impresora |

### 8.4 Auditoría y notificaciones

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/audit/search` | Buscar registros |
| GET | `/api/audit/filter` | Filtrar registros |
| POST | `/api/audit/export` | Exportar bitácora |
| GET | `/api/notifications/settings` | Configurar notificaciones |
| PUT | `/api/notifications/settings` | Actualizar configuración |
| PUT | `/api/company/logo` | Cambiar logotipo |
| PUT | `/api/company/fiscal` | Configurar información fiscal |
| PUT | `/api/company/general` | Configurar datos generales |
| GET | `/api/config/taxes` | Configurar impuestos |
| PUT | `/api/config/taxes` | Actualizar impuestos |

## 9. Público

| Método | Endpoint | Descripción | Autenticación |
|--------|----------|-------------|---------------|
| GET | `/api/demo/summary` | Resumen para demo | No |

## 10. Realtime / Broadcasting

El sistema debe publicar eventos en tiempo real en el canal `mi-stock`.

### Evento

- `resource.changed`

### Payload

```json
{
  "resource": "product",
  "action": "created",
  "data": {},
  "timestamp": "2026-08-10T00:00:00.000000Z"
}
```

### Configuración recomendada

- `BROADCAST_DRIVER=pusher`
- `PUSHER_APP_ID=<app-id>`
- `PUSHER_APP_KEY=<app-key>`
- `PUSHER_APP_SECRET=<app-secret>`
- `PUSHER_APP_CLUSTER=<cluster>`
- `PUSHER_HOST=<host-o-dominio>`
- `PUSHER_PORT=6001`
- `PUSHER_SCHEME=https`
- `PUSHER_APP_ENCRYPTED=true`

## 11. Variables de entorno

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<tu-dominio>

DB_CONNECTION=mongodb
DB_HOST=<host>
DB_PORT=27017
DB_DATABASE=<db>
DB_USERNAME=<usuario>
DB_PASSWORD=<password>
DB_AUTHDATABASE=admin
DB_OPTIONS="retryWrites=true&w=majority&appName=Mi-Stock-Cluster&ssl=true&authSource=admin"

BROADCAST_DRIVER=pusher
PUSHER_APP_ID=<app-id>
PUSHER_APP_KEY=<app-key>
PUSHER_APP_SECRET=<app-secret>
PUSHER_APP_CLUSTER=<cluster>
PUSHER_HOST=<host-o-dominio>
PUSHER_PORT=6001
PUSHER_SCHEME=https
PUSHER_APP_ENCRYPTED=true
```

## 12. Ejemplos rápidos con curl

### Login

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"demo@example.com","password":"password"}'
```

### Listar productos

```bash
curl http://localhost:8000/api/products \
  -H "Authorization: Bearer <token>"
```

### Crear producto

```bash
curl -X POST http://localhost:8000/api/products \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"name":"Producto demo","price":1500,"cost":1000}'
```

## 13. Recomendaciones finales

- Mantener la convención `items` para compras y ventas.
- Mantener `GET` para reportes simples.
- Documentar cualquier endpoint nuevo en este mismo formato para evitar divergencias con frontend y backend.
- Usar esta especificación como fuente de verdad para el despliegue y las pruebas de integración.
