# Frontend SGPI ITSSMT

Sistema de Gestión de Proyectos Integradores - Frontend HTML/CSS/JavaScript

## 🚀 Instalación y Ejecución

### Requisitos
- PHP 7.4 o superior
- Navegador web moderno
- Acceso a la API REST (http://127.0.0.1:8000/api)

### Pasos de Instalación

1. **Clonar o descargar el proyecto**
   ```bash
   cd /ruta/a/Frontend_Swgpi
   ```

2. **Configurar la URL de la API**
   - Editar el archivo base en los scripts
   - Por defecto: `http://127.0.0.1:8000/api`

3. **Ejecutar con servidor built-in de PHP**
   ```bash
   php -S localhost:3000
   ```
   
   Luego acceder a: `http://localhost:3000`

### Configuración con Apache/Nginx

#### Apache
```apache
<VirtualHost *:80>
    ServerName frontend.local
    DocumentRoot /ruta/a/Frontend_Swgpi
    
    <Directory /ruta/a/Frontend_Swgpi>
        AllowOverride All
        Order allow,deny
        Allow from all
    </Directory>
</VirtualHost>
```

#### Nginx
```nginx
server {
    listen 80;
    server_name frontend.local;
    root /ruta/a/Frontend_Swgpi;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

## 📁 Estructura de Carpetas

```
Frontend_Swgpi/
├── index.php                    # Página de inicio
├── .htaccess                    # Configuración de reescritura
├── assets/
│   ├── css/
│   │   └── app.css             # Estilos globales
│   ├── js/
│   │   ├── app.js              # Funciones globales
│   │   ├── auth.js             # Gestión de autenticación
│   │   ├── api.js              # Cliente de API
│   │   ├── router.js           # Gestión de rutas
│   │   └── table.js            # Utilidades para tablas
│   └── img/
│       └── ITSSMT/             # Logos e imágenes
├── pages/
│   ├── login.php               # Página de login
│   ├── repositorio.php         # Repositorio digital
│   ├── repositorio-detail.php  # Detalles del documento
│   ├── forgot-password.php     # Recuperar contraseña
│   ├── reset-password.php      # Restablecer contraseña
│   ├── admin/
│   │   ├── dashboard.php       # Panel de admin
│   │   ├── users.php           # Listado de usuarios
│   │   ├── user-create.php     # Crear usuario
│   │   ├── user-edit.php       # Editar usuario
│   │   ├── projects.php        # Gestión de proyectos
│   │   ├── project-create.php  # Crear proyecto
│   │   ├── project-edit.php    # Editar proyecto
│   │   ├── deliverables.php    # Gestión de entregables
│   │   ├── asignaturas.php     # Gestión de asignaturas
│   │   ├── competencias.php    # Gestión de competencias
│   │   └── document-tags.php   # Gestión de etiquetas
│   ├── teacher/
│   │   ├── dashboard.php       # Panel del docente
│   │   └── my-projects.php     # Mis proyectos
│   └── student/
│       ├── dashboard.php       # Panel del estudiante
│       └── my-deliverables.php # Mis entregables
├── includes/
│   ├── config.php              # Configuración global
│   ├── navbar.php              # Barra de navegación
│   ├── footer.php              # Pie de página
│   ├── sidebar.php             # Sidebar según rol
│   ├── alert.php               # Componente de alertas
│   └── modal.php               # Modal genérico
└── api/
    └── set-session.php         # Guardar sesión del usuario
```

## 🎨 Colores y Diseño

**Colores principales:**
- Azul Primario: `#1B396A`
- Azul Secundario: `#2D5A96`
- Fondo Claro: `#ffffff`
- Texto Oscuro: `#333645`
- Texto Mutado: `#7a7f88`

**Tipografía:**
- Roboto (body)
- Raleway (headings)

## 🔐 Autenticación

El sistema utiliza **JWT (JSON Web Tokens)** almacenado en localStorage

### Flujo de Login
1. Usuario ingresa matrícula/nómina y contraseña
2. API valida y retorna `access_token`
3. Token se almacena en `localStorage`
4. Se envía en el header `Authorization: Bearer {token}`

### Perfiles de Usuario
- **Admin (1)**: Acceso total al sistema
- **Docente (2)**: Gestión de proyectos y estudiantes
- **Estudiante (3)**: Vista de proyectos y entregables

## 🔌 API REST

Todas las peticiones a `http://127.0.0.1:8000/api`

**Endpoints principales:**
- `POST /auth/login` - Iniciar sesión
- `POST /auth/logout` - Cerrar sesión
- `GET /users` - Listado de usuarios
- `POST /users` - Crear usuario
- `PUT /users/:id` - Actualizar usuario
- `DELETE /users/:id` - Eliminar usuario
- `GET /projects` - Listado de proyectos
- `GET /dashboard/stats` - Estadísticas

## 🛠️ Tecnologías

- **HTML5** - Estructura
- **CSS3** - Estilos
- **JavaScript Vanilla** - Interactividad
- **Bootstrap 5.3** - Framework CSS (CDN)
- **Bootstrap Icons** - Iconografía (CDN)
- **Axios** - Peticiones HTTP (CDN)
- **PHP** - Backend de frontend (sesiones)

## 📝 Notas Importantes

1. **Librería desde CDN**: Axios, Bootstrap, Bootstrap Icons
2. **Sin build tools**: No requiere npm o webpack
3. **Sin frameworks JS**: JavaScript vanilla puro
4. **Compatibilidad**: Chrome, Firefox, Safari, Edge (últimas versiones)
5. **Modo offline**: LocalStorage mantiene autenticación hasta logout

## 🚨 Troubleshooting

### "Error al conectar con la API"
- Verificar que el backend Laravel esté corriendo en http://127.0.0.1:8000
- Revisar la configuración de CORS en el backend

### "Sesión no se mantiene"
- Limpiar localStorage: `localStorage.clear()`
- Verificar que las cookies estén habilitadas

### "CSS o JS no se cargan"
- Verificar rutas relativas en las etiquetas `<script>` y `<link>`
- Revisar la consola del navegador (F12)

## 📞 Soporte

Para reportar errores o sugerencias, contactar al equipo de desarrollo.

---

**Versión:** 1.0  
**Última actualización:** Mayo 2026
