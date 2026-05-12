# ❓ FAQ y Troubleshooting - SWGPI Frontend

---

## ❓ Preguntas Frecuentes

### ¿Dónde está el archivo validations.php?
**R**: En `includes/validations.php`. Debe ser incluido en cualquier página que necesite validaciones del servidor.

```php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/validations.php';
```

### ¿Cuál es el tamaño máximo de archivo?
**R**: 50MB. Validado en cliente y servidor:
- Cliente: `validarTamañoArchivo(bytes)` en app.js
- Servidor: `validar_tamaño_archivo()` en validations.php

### ¿Qué tipos de archivo se permiten?
**R**: PDF, DOC, DOCX, XLS, XLSX, ZIP
```
- application/pdf
- application/msword
- application/vnd.openxmlformats-officedocument.wordprocessingml.document
- application/vnd.ms-excel
- application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
- application/zip
```

### ¿Cómo filtro entregas por perfil?
**R**: El filtrado ocurre automáticamente en el cliente:
- Admin: ve todas (no se filtra)
- Docente: obtiene `/my-deliverables` (solo las suyas)
- Estudiante: obtiene `/my-deliverables` (solo las suyas)

### ¿Dónde se muestra la calificación?
**R**: En múltiples lugares:
- **Admin**: `/pages/admin/deliverables.php` - columna "Calificación"
- **Docente**: `/pages/teacher/my-deliverables.php` - columna "Calificación"
- **Estudiante**: `/pages/student/my-deliverables.php` - en tarjeta, plus fecha y quién calificó

### ¿Cómo valido que el archivo fue subido?
**R**: En `/pages/student/my-deliverables.php`, si `file_path` existe, aparecerá:
- Ícono de archivo ✓
- Botón "Descargar Archivo"

### ¿Puedo calificar más de una vez?
**R**: Sí. El endpoint `/api/deliverables/{id}/calificar` permite actualizar.
Se sobrescribe la calificación anterior.

### ¿Qué pasa si token expira?
**R**: Se muestra error "Token no disponible. Por favor inicia sesión."
El usuario debe volver a login.

### ¿Dónde veo los errores?
**R**: En dos lugares:
1. **Alertas visuales**: En pantalla (rojo para error)
2. **Console**: F12 → Console → ver `console.error()`

### ¿Cómo reseteo un estado incorrecto?
**R**: Opciones:
```javascript
// En console del navegador:
localStorage.clear()
location.reload()

// O borra solo token:
localStorage.removeItem('auth_token')
```

---

## 🐛 Troubleshooting

### Problema: "Botón de calificar no aparece"

**Causas posibles**:
- [ ] No eres admin o docente
- [ ] Usuario no está autenticado
- [ ] Token expirado

**Solución**:
```javascript
// Verifica en console:
const user = auth.getCurrentUser();
console.log('Usuario:', user);
console.log('Es Admin:', user.perfil_id === 1);
console.log('Es Docente:', user.perfil_id === 2);
```

---

### Problema: "El archivo no se descarga"

**Causas posibles**:
- [ ] No existe `file_path`
- [ ] Sin permisos (403)
- [ ] Archivo no existe en servidor (404)
- [ ] Token inválido

**Solución**:
1. Verifica que `file_path` no es null:
```javascript
console.log('File path:', deliverable.file_path);
```

2. Abre Network tab (F12) y mira el request:
```
GET /api/deliverables/1/download
Response: 404 / 403 / 200 + blob
```

3. Si es 403, verifica que tienes permiso:
```javascript
const user = auth.getCurrentUser();
const canDownload = user.perfil_id === 1 || 
                    deliverable.owner_id === user.id;
```

---

### Problema: "Upload rechaza el archivo"

**Causas posibles**:
- [ ] MIME type no permitido
- [ ] Archivo > 50MB
- [ ] No seleccionaste archivo

**Solución**:
```javascript
// Verifica MIME type:
const file = document.getElementById('archivoInput').files[0];
console.log('MIME:', file.type);
console.log('Es válido:', validarMimeType(file.type));

// Verifica tamaño:
console.log('Tamaño:', formatearTamaño(file.size));
console.log('Válido:', validarTamañoArchivo(file.size));
```

---

### Problema: "Calificación no se guarda"

**Causas posibles**:
- [ ] Valor fuera de rango (0-100)
- [ ] Campo vacío
- [ ] Error del servidor (422)
- [ ] Sin permisos

**Solución**:
```javascript
const calificacion = 85.5;
console.log('Válida:', validarCalificacion(calificacion)); // true

// Mira Network tab para error 422
// Revisa el mensaje en respuesta JSON
```

---

### Problema: "No veo las entregas del docente"

**Causas posibles**:
- [ ] No es docente (perfil_id ≠ 2)
- [ ] No es asesor de ningún proyecto
- [ ] No hay entregas en esos proyectos

**Solución**:
1. Verifica que eres docente:
```javascript
const user = auth.getCurrentUser();
console.log('Perfil:', user.perfil_id); // debe ser 2
```

2. Verifica que tienes proyectos:
```javascript
api.get('/my-projects').then(r => console.log('Proyectos:', r.data));
```

3. Verifica que hay entregas:
```javascript
api.get('/deliverables').then(r => console.log('Entregas:', r.data));
```

---

### Problema: "Las fechas de competencia no validan"

**Causas posibles**:
- [ ] Competencia sin fechas
- [ ] Formato de fecha incorrecto
- [ ] Zona horaria

**Solución**:
```javascript
api.get('/competencias/1').then(comp => {
    console.log('Competencia:', comp);
    console.log('Inicio:', comp.data.fecha_inicio);
    console.log('Fin:', comp.data.fecha_fin);
});
```

---

### Problema: "Admin ve proyectos pero docente no"

**Causas posibles**:
- [ ] Docente no está asignado como asesor
- [ ] Filtrado incorrecto

**Solución**:
1. Verifica relación en BD:
```sql
-- En BD Laravel
SELECT * FROM project_user 
WHERE user_id = {user_id} 
  AND project_id = {project_id}
  AND rol_asesor IS NOT NULL;
```

2. Verifica datos en API:
```javascript
api.get('/projects').then(r => {
    const myProjects = r.data.filter(p => 
        p.advisors.some(a => a.id === auth.getCurrentUser().id)
    );
    console.log('Mis proyectos:', myProjects);
});
```

---

### Problema: "Modal de calificación no abre"

**Causas posibles**:
- [ ] Bootstrap no cargó
- [ ] JavaScript error
- [ ] Elemento no existe en DOM

**Solución**:
```javascript
// Verifica que Bootstrap está cargado:
console.log('Bootstrap:', typeof bootstrap);

// Verifica que modal existe:
const modal = document.getElementById('modalCalificar');
console.log('Modal existe:', modal !== null);

// Intenta abrir manualmente:
new bootstrap.Modal(modal).show();
```

---

### Problema: "Las alertas no desaparecen"

**Causas posibles**:
- [ ] Duration configurado incorrectamente
- [ ] CSS de Bootstrap no cargó

**Solución**:
```javascript
// Verifica que app.js se cargó:
console.log('showAlert:', typeof showAlert);

// Intenta mostrar alerta:
showAlert('#alertContainer', 'success', 'Test', 3000);
```

---

### Problema: "Página se queda cargando"

**Causas posibles**:
- [ ] API no responde
- [ ] Timeout (10s)
- [ ] Error de red

**Solución**:
```javascript
// En console, prueba API:
api.get('/deliverables')
   .then(r => console.log('OK:', r))
   .catch(e => console.log('Error:', e));

// Verifica Network tab (F12)
// Mira si las requests se completan
```

---

### Problema: "Tabla vacía cuando debería haber datos"

**Causas posibles**:
- [ ] Datos no coinciden con filtro
- [ ] Usuario no tiene acceso
- [ ] Respuesta vacía del servidor

**Solución**:
```javascript
// Cargamanual de datos:
api.get('/deliverables')
   .then(r => {
       console.log('Respuesta completa:', r);
       console.log('Datos:', r.data);
       console.log('Cantidad:', r.data?.length || 0);
   });
```

---

## 🔧 Configuración y Variables

### Base URL de API
```javascript
// En todas las páginas:
const API_BASE_URL = 'http://127.0.0.1:8000/api';
```

### LocalStorage Keys
```javascript
localStorage.getItem('auth_token')   // JWT token
localStorage.getItem('user')         // User object JSON
```

### Timeouts
```javascript
// ApiClient timeout
this.timeout = 10000; // 10 segundos

// Alert duration
showAlert(container, type, msg, 5000); // 5 segundos
```

### Límites
```javascript
const MAX_FILE_SIZE = 50 * 1024 * 1024;  // 50MB
const MIN_CALIFICACION = 0;
const MAX_CALIFICACION = 100;
```

---

## 📋 Checklist de Verificación Post-Deploy

- [ ] validations.php existe en includes/
- [ ] app.js tiene >300 líneas (ampliado)
- [ ] deliverables.php tiene modales
- [ ] student/my-deliverables.php funciona
- [ ] admin/projects.php filtra por perfil
- [ ] teacher/my-projects.php existe
- [ ] teacher/my-deliverables.php existe
- [ ] competencias.php muestra fechas
- [ ] Todos los botones aparecen
- [ ] Modales abren/cierran
- [ ] Alertas se muestran
- [ ] Badges tienen colores
- [ ] Upload valida MIME
- [ ] Upload valida tamaño
- [ ] Download funciona
- [ ] Calificación persiste
- [ ] Filtrado por perfil funciona
- [ ] Errores 401, 403, 404, 422 se manejan
- [ ] Responsive en móvil
- [ ] Console sin errores

---

## 📞 Información de Contacto

Para más información, consulta:
- [IMPLEMENTACION_COMPLETADA.md](IMPLEMENTACION_COMPLETADA.md) - Detalles técnicos
- [GUIA_TESTING.md](GUIA_TESTING.md) - Pasos de testing
- Código comentado en cada archivo

---

**Última actualización**: 7 de mayo de 2026
**Versión**: 1.0
