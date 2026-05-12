# 🧪 GUÍA DE TESTING - SWGPI Frontend

## ✅ Cómo Probar las Nuevas Funcionalidades

---

## 1️⃣ Calificación de Entregas

### Acceso:
- **Admin**: `/pages/admin/deliverables.php`
- **Docente**: `/pages/teacher/my-deliverables.php`

### Pasos de Testing:
1. Inicia sesión como Admin o Docente
2. Navega a "Entregas" o "Mis Entregas"
3. Busca una entrega sin calificar
4. Haz clic en botón ⭐ (estrella)
5. Se abrirá modal con input 0-100
6. Ingresa calificación (ej: 85.5)
7. Haz clic en "Calificar"
8. Verifica que aparezca badge de calificación

### Validaciones a Verificar:
- ✅ No permite valores menores a 0
- ✅ No permite valores mayores a 100
- ✅ No permite campos vacíos
- ✅ Muestra error si token inválido
- ✅ Actualiza tabla automáticamente
- ✅ Badge cambia de color según rango

---

## 2️⃣ Descarga de Archivos

### Acceso:
- Cualquier vista con entregas/proyectos que tengan `file_path`

### Pasos de Testing:
1. Busca una entrega/proyecto con archivo ✅
2. Haz clic en botón ⬇️ (descarga)
3. Se descargará el archivo en tu carpeta de descargas
4. Verifica que el archivo sea válido

### Validaciones a Verificar:
- ✅ Solo aparece botón si existe `file_path`
- ✅ Descarga con nombre correcto
- ✅ Archivo se descarga como blob
- ✅ Error 404 si archivo no existe
- ✅ Error 403 si sin acceso
- ✅ Mensaje de éxito después de descargar

---

## 3️⃣ Upload de Archivos

### Acceso:
- **Admin**: `/pages/admin/deliverables.php` - botón ☁️
- **Estudiante**: `/pages/student/my-deliverables.php` - botón "Subir/Actualizar"

### Pasos de Testing:
1. Abre modal de upload
2. Selecciona un archivo PDF/DOC (ej: documento.pdf)
3. Haz clic en "Subir Archivo"
4. Verifica mensaje de éxito
5. Actualiza página y verifica que archivo persiste

### Validaciones a Verificar:
- ✅ Valida MIME type (rechaza .txt, .exe, etc)
- ✅ Valida tamaño (rechaza >50MB)
- ✅ Solo permite PDF, DOC, DOCX, XLS, XLSX, ZIP
- ✅ Muestra error específico si MIME inválido
- ✅ Muestra error específico si tamaño excedido
- ✅ FormData enviado correctamente
- ✅ Archivo se guarda en backend

### Archivos de Prueba:
```
Válidos:
- documento.pdf (application/pdf)
- reporte.docx (application/vnd.openxmlformats-officedocument.wordprocessingml.document)
- datos.xlsx (application/vnd.openxmlformats-officedocument.spreadsheetml.sheet)
- proyecto.zip (application/zip)

Inválidos (deben rechazarse):
- script.exe (application/x-msdownload)
- imagen.png (image/png)
- video.mp4 (video/mp4)
- archivo.txt (text/plain)
```

---

## 4️⃣ Validación de Fechas de Competencia

### Acceso:
- Formulario de crear entrega (si tiene competencia_id)

### Pasos de Testing:
1. Abre formulario de crear entrega
2. Selecciona una competencia
3. Intenta ingresar fecha FUERA del rango
4. Se mostrará error con rango permitido
5. Ingresa fecha DENTRO del rango
6. Permite continuar

### Validaciones a Verificar:
- ✅ Obtiene rango de competencia
- ✅ Muestra error si fecha < fecha_inicio
- ✅ Muestra error si fecha > fecha_fin
- ✅ Permite si fecha está dentro
- ✅ Mensaje muestra fechas permitidas
- ✅ Formato de fechas localizado

---

## 5️⃣ Filtrado por Perfil

### Proyecto Admin:
1. Inicia como Admin
2. Va a `/pages/admin/projects.php`
3. ✅ Debe ver TODOS los proyectos

### Proyecto Docente:
1. Inicia como Docente
2. Va a `/pages/admin/projects.php`
3. ✅ Debe ver SOLO proyectos donde es asesor
4. Va a `/pages/teacher/my-projects.php`
5. ✅ Mismos proyectos que arriba

### Proyecto Estudiante:
1. Inicia como Estudiante
2. Va a `/pages/admin/projects.php`
3. ✅ Debe ver SOLO proyectos asignados

### Entrega Estudiante:
1. Inicia como Estudiante
2. Va a `/pages/student/my-deliverables.php`
3. ✅ Debe ver SOLO sus entregas

### Entrega Docente:
1. Inicia como Docente
2. Va a `/pages/teacher/my-deliverables.php`
3. ✅ Debe ver entregas de sus proyectos SOLAMENTE

---

## 6️⃣ Campos Nuevos en Tablas

### Proyectos - Columnas Nuevas:
- [ ] `year` - Año del proyecto
- [ ] `authors` - Autores (puede ser null)
- [ ] `file_path` - Ícono si tiene archivo

### Entregas - Columnas Nuevas:
- [ ] `calificacion` - Con badge coloreado
- [ ] `file_path` - Ícono si tiene archivo
- [ ] `fecha_calificacion` - En tarjeta (estudiante)
- [ ] `calificado_por` - En tarjeta (estudiante)

### Competencias - Columnas Nuevas:
- [ ] `fecha_inicio` - DD/MM/YYYY
- [ ] `fecha_fin` - DD/MM/YYYY
- [ ] Estado dinámico - "En rango", "Próxima", "Vencida"

---

## 7️⃣ Validaciones en validations.php

### Para Verificar (desde logs backend):

```php
// Calificación
validar_calificacion(85); // true
validar_calificacion(150); // false
validar_calificacion(-5); // false

// MIME Type
validar_mime_type('application/pdf'); // true
validar_mime_type('application/x-msdownload'); // false

// Tamaño (50MB = 52428800 bytes)
validar_tamaño_archivo(10485760); // true (10MB)
validar_tamaño_archivo(104857600); // false (100MB)

// Acceso
// Admin (perfil_id=1): siempre true
validar_acceso_entrega(1, 1, 123); // true

// Docente (perfil_id=2): solo su proyectos
validar_acceso_entrega(1, 2, 123); // true si es asesor

// Estudiante (perfil_id=3): solo asignado
validar_acceso_entrega(1, 3, 456); // true si es miembro
```

---

## 🔍 Testing de Errores HTTP

### 401 Unauthorized:
1. Copia token del localStorage
2. Modifica token en ConsoleTools
3. Intenta descargar/subir
4. ✅ Debe mostrar "Token no disponible"

### 403 Forbidden:
1. Inicia como Estudiante
2. Intenta acceder a entrega de otro estudiante
3. ✅ Debe mostrar "No tienes permiso"

### 404 Not Found:
1. Intenta descargar entregable inexistente
2. Url: `/api/deliverables/99999/download`
3. ✅ Debe mostrar "Archivo no encontrado"

### 422 Validation Error:
1. Intenta calificar con valor > 100
2. ✅ Backend debe rechazar
3. ✅ Mostrar error de validación

---

## 🖼️ Testing UI/UX

### Badges de Calificación:
- [ ] Rojo: <50
- [ ] Amarillo: 50-69
- [ ] Verde: ≥70

### Badges de Competencia:
- [ ] Verde "En rango": fecha actual dentro
- [ ] Amarillo "Próxima": no ha comenzado
- [ ] Rojo "Vencida": ya terminó

### Alertas:
- [ ] Verde para éxito
- [ ] Rojo para error
- [ ] Azul para info
- [ ] Se desaparecen en 5s

### Modales:
- [ ] Abren correctamente
- [ ] Se cierran al hacer click X
- [ ] Se cierran al cancelar
- [ ] Datos se limpian al reabrir

### Tablas:
- [ ] Responsive en móvil
- [ ] Spinners durante carga
- [ ] Mensajes "No hay datos"
- [ ] Botones con tooltips

---

## 🔐 Testing de Seguridad

### Control de Acceso:
1. **Admin** puede ver/editar todo
   - [ ] Ver todas las entregas
   - [ ] Calificar todas
   - [ ] Editar proyectos

2. **Docente** accede solo sus proyectos
   - [ ] NO ve proyectos de otros
   - [ ] NO ve entregas de otros docentes
   - [ ] SÍ ve entregas de sus proyectos

3. **Estudiante** accede solo lo asignado
   - [ ] NO ve entregas de otros
   - [ ] NO puede descargar de otros
   - [ ] SÍ puede subir su archivo

---

## 📊 Casos de Uso Completos

### Flujo Completo - Admin Califica:
1. Admin → `/pages/admin/deliverables.php`
2. Busca entrega sin calificar
3. Click en ⭐ (calificar)
4. Ingresa 85
5. Click "Calificar"
6. ✅ Badge aparece "85%" en verde
7. ✅ Mensaje de éxito
8. ✅ Tabla se actualiza

### Flujo Completo - Estudiante Sube:
1. Estudiante → `/pages/student/my-deliverables.php`
2. Click en "Subir/Actualizar Archivo"
3. Selecciona documento.pdf
4. Click "Subir Archivo"
5. ✅ Mensaje "Archivo subido"
6. ✅ Botón descargar aparece
7. Click descargar
8. ✅ Archivo se descarga

### Flujo Completo - Docente Califica:
1. Docente → `/pages/teacher/my-deliverables.php`
2. Ve solo entregas de sus proyectos
3. Click ⭐ en entrega sin calificar
4. Ingresa 92
5. Click "Calificar"
6. ✅ Badge 92% en verde
7. Estudiate ve la calificación en su vista

---

## 📝 Checklist de Testing

### Funcionalidades:
- [ ] Calificación: crear, validar, mostrar
- [ ] Descarga: cliente, errores, permisos
- [ ] Upload: MIME, tamaño, persistencia
- [ ] Fechas: validación, rango, display
- [ ] Filtrado: admin, docente, estudiante
- [ ] Campos: mostrados correctamente

### Validaciones:
- [ ] Cliente: rango, MIME, tamaño
- [ ] Servidor: PHP validations.php
- [ ] Errores: 401, 403, 404, 422

### UI:
- [ ] Badges coloreados
- [ ] Modales Bootstrap
- [ ] Alertas dismissibles
- [ ] Responsive

### Seguridad:
- [ ] Control de acceso
- [ ] Token JWT
- [ ] Perfiles funcionan

---

## 🐛 Debugging Tips

### Browser Console:
```javascript
// Ver token
localStorage.getItem('auth_token')

// Ver usuario actual
auth.getCurrentUser()

// Test API
api.get('/deliverables')

// Test download
descargarEntregable(1)

// Test upload
subirArchivo(1, fileObject)
```

### Network Tab (F12):
- Ver requests a `/api/deliverables/*/calificar`
- Ver requests a `/api/deliverables/*/upload`
- Ver requests a `/api/deliverables/*/download`
- Verificar headers (Authorization)
- Verificar status codes

### Console Errors:
```javascript
// Limpiar localStorage y recargar
localStorage.clear()
location.reload()

// Ver todos los alerts
console.log('Alerts HTML:', document.querySelectorAll('.alert'))
```

---

## 📞 Contacto y Soporte

Si encuentras problemas:
1. Revisa el archivo IMPLEMENTACION_COMPLETADA.md
2. Verifica la consola del navegador (F12)
3. Revisa Network tab para requests fallidos
4. Valida que el token esté presente
5. Verifica que el usuario tiene el perfil correcto

---

**Última Actualización**: 7 de mayo de 2026
