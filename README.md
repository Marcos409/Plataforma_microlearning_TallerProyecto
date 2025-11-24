# Plataforma Microlearning

## 📦 Guía de Instalación

### Requisitos
        - Python 3.8+
        - MySQL
        - Git

### Pasos Rápidos

        1. **Clonar proyecto**
        ```bash
        git clone https://github.com/Marcos409/Plataforma_microlearning_TallerProyecto.git
        cd Plataforma_microlearning_TallerProyecto
        
        2. **Entorno virtual**
        python -m venv venv
        # Linux/Mac: source venv/bin/activate
        # Windows: venv\Scripts\activate
        
        3. **Instalar dependencias**
        bash
        pip install -r requirements.txt
        
        4. **Base de datos**
        sql
        CREATE DATABASE microlearning;
        CREATE USER micro_user WITH PASSWORD 'password123';
        
        5. **Configurar .env**
         env
        DATABASE_URL=postgresql://micro_user:password123@localhost:5432/microlearning
        SECRET_KEY=tu-clave-secreta
        
        6. **Migraciones y usuario**
        bash
        python manage.py migrate
        python manage.py createsuperuser
        
        7. **Ejecutar**
        bash
        python manage.py runserver
        Visitar: http://localhost:8000


## 👤 Guía de Usuario

Para Estudiantes
### Registro y Acceso
        - Crear cuenta con email/contraseña
        - Verificar email (si está activo)
        - Iniciar sesión en plataforma
### Tomar Cursos
        - Explorar catálogo de cursos
        - Inscribirse en curso deseado
        - Completar lecciones en orden
        - Realizar evaluaciones
        - Obtener certificado

### Contenido Disponible
        - 📹 Videos y multimedia
        - 📚 Texto e imágenes
        - 🎯 Quiz interactivos
        - 📝 Ejercicios prácticos

### Seguimiento
        - Dashboard con progreso
        - Estadísticas de aprendizaje
        - Certificados descargables
        - Historial de cursos

## Para Instructores
### Gestión de Cursos
        - Crear nuevos cursos
        - Agregar módulos y lecciones
        - Subir contenido multimedia
        - Configurar evaluaciones
### Monitoreo
        - Ver progreso de estudiantes
        - Revisar calificaciones
        - Generar reportes
        - Emitir certificados
