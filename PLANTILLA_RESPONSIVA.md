# Plantilla Responsiva - Nueva Página

Use esta plantilla cuando cree una nueva página para asegurar que sea 100% responsiva.

## HTML Base Responsivo

```html
<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
if (!is_authenticated()) {
    header('Location: /index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#1B396A">
    <title>Nombre Página - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>
    
    <div class="content-wrapper">
        <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php'; ?>
        
        <div class="main-content flex-grow-1">
            <!-- Hero Section -->
            <div class="hero-gradient">
                <div class="container-xl">
                    <h1 class="display-4 fw-bold mb-3">Título de Página</h1>
                    <p class="lead">Descripción breve</p>
                </div>
            </div>

            <!-- Main Content -->
            <div class="container-xl mt-5 mb-5">
                <!-- Grid responsivo -->
                <div class="row g-4">
                    <!-- Columna responsive: 1 en móvil, 2 en tablet, 3 en desktop -->
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Tarjeta 1</h5>
                            </div>
                            <div class="card-body">
                                Contenido responsive
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Tarjeta 2</h5>
                            </div>
                            <div class="card-body">
                                Contenido responsive
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Tarjeta 3</h5>
                            </div>
                            <div class="card-body">
                                Contenido responsive
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla responsiva -->
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Tabla Responsiva</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Columna 1</th>
                                            <th>Columna 2</th>
                                            <th>Columna 3</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Dato 1</td>
                                            <td>Dato 2</td>
                                            <td>Dato 3</td>
                                            <td>
                                                <button class="btn btn-sm btn-primary">Editar</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulario responsivo -->
                <div class="row mt-5">
                    <div class="col-12 col-lg-8 offset-lg-2">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Formulario Responsivo</h5>
                            </div>
                            <div class="card-body">
                                <form id="formulario">
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                <label for="input1" class="form-label">Campo 1</label>
                                                <input type="text" class="form-control" id="input1" placeholder="Ingresa valor">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                <label for="input2" class="form-label">Campo 2</label>
                                                <input type="text" class="form-control" id="input2" placeholder="Ingresa valor">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="textarea" class="form-label">Mensaje</label>
                                                <textarea class="form-control" id="textarea" rows="4" placeholder="Escribe aquí..."></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <button type="button" class="btn btn-secondary">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">Guardar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>const API_BASE_URL = 'http://127.0.0.1:8000/api';</script>
    <script src="/assets/js/auth.js"></script>
    <script src="/assets/js/api.js"></script>
    <script src="/assets/js/app.js"></script>
    <script src="/assets/js/responsive.js"></script>
    
    <script>
        // Tu código JavaScript aquí
    </script>
</body>
</html>
```

---

## 📱 Clases Bootstrap Responsivas Comunes

### Grid System
```html
<!-- 1 columna en móvil, 2 en tablet, 3 en desktop -->
<div class="row g-4">
    <div class="col-12 col-md-6 col-lg-4">...</div>
    <div class="col-12 col-md-6 col-lg-4">...</div>
    <div class="col-12 col-md-6 col-lg-4">...</div>
</div>

<!-- Full width en móvil, mitad en tablet, 1/3 en desktop -->
<div class="col-12 col-md-6 col-lg-4">...</div>

<!-- Offset responsivo -->
<div class="col-12 col-lg-8 offset-lg-2">...</div>
```

### Botones Responsivos
```html
<!-- Full width en móvil -->
<div class="d-grid d-md-flex gap-2">
    <button class="btn btn-secondary">Cancelar</button>
    <button class="btn btn-primary">Guardar</button>
</div>
```

### Espaciado Responsivo
```html
<!-- Padding responsive -->
<div class="px-3 px-md-4 py-3 py-md-4">...</div>

<!-- Margen responsive -->
<div class="mt-3 mt-md-5 mb-3 mb-md-4">...</div>
```

### Visibilidad Responsiva
```html
<!-- Visible solo en móvil -->
<div class="d-md-none">Solo móvil</div>

<!-- Visible solo en desktop -->
<div class="d-none d-md-block">Solo desktop</div>
```

---

## 🎨 Media Queries Personalizados

Si necesitas agregar estilos personalizados, usa estos breakpoints:

```css
/* Móvil pequeño */
@media (max-width: 575.98px) {
    .mi-clase {
        font-size: 14px;
    }
}

/* Tablet */
@media (min-width: 576px) and (max-width: 768px) {
    .mi-clase {
        font-size: 15px;
    }
}

/* Desktop */
@media (min-width: 992px) {
    .mi-clase {
        font-size: 16px;
    }
}
```

---

## ✅ Checklist para Nueva Página

- [ ] Incluido viewport meta tag
- [ ] Incluido responsive.js
- [ ] Usado content-wrapper + sidebar
- [ ] Usado container-xl para contenido
- [ ] Usado grid col-12 col-md-* col-lg-*
- [ ] Tablas envueltas en table-responsive
- [ ] Botones con clases apropiadas
- [ ] Formularios con labels y validación
- [ ] Probado en móvil (<576px)
- [ ] Probado en tablet (576-992px)
- [ ] Probado en desktop (>992px)
- [ ] Sin horizontal scroll innecesario
- [ ] Texto legible en todos los tamaños
- [ ] Botones clickeables en móvil (min 44x44px)

---

## 🚀 Tips de Responsividad

1. **Siempre usa `col-12` como base** - Asegura que el contenido sea 100% ancho en móvil
2. **Testea en DevTools** - F12 → Device Toolbar para simular dispositivos
3. **No uses medidas fijas** - Usa %, em, rem en lugar de px
4. **Imágenes responsive** - `<img class="img-fluid" src="...">`
5. **Tablas scrolleables** - Envuelve en `<div class="table-responsive">`
6. **Campos de formulario** - Font-size mínimo 16px para evitar zoom iOS
7. **Touch targets** - Botones y links mínimo 44x44px
8. **Overflow handling** - Usa `overflow-x: auto` en lugar de permitir scroll

---

*Documentación de plantilla responsiva - 14 de mayo de 2026*
