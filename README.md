# 📚 Plataforma de Microlearning con Laravel

## 📌 Descripción
Plataforma educativa con enfoque en **microlearning**, desarrollada en **Laravel**.  
Su objetivo es facilitar el **aprendizaje en módulos cortos**, fomentando la retención de conocimientos y la flexibilidad en la enseñanza.

---

## 🏗️ Arquitectura del Proyecto
El proyecto sigue una **arquitectura por capas**, lo que permite mayor **escalabilidad, organización y modularidad**.  

La estructura de carpetas es la siguiente:
```
Plataforma_microlearning_TallerProyecto/
├── app/
│ ├── Http/
│ │ ├── Controllers/ # Controladores de la lógica de negocio
│ │ └── Requests/ # Validaciones de formularios y solicitudes
│ └── Models/ # Modelos de la base de datos
├── database/
│ ├── migrations/ # Migraciones para crear/modificar tablas
│ └── seeders/ # Datos iniciales (semillas)
├── resources/
│ └── views/ # Vistas Blade (interfaz de usuario)
└── routes/ # Definición de rutas (web.php, api.php, etc.)
```
## 🔄 Relación entre MVC y Arquitectura por Capas

Este proyecto combina el patrón **MVC** con una **arquitectura por capas** para mejorar la organización y el mantenimiento del código:

- **Modelo (Model)**: Ubicado en la **capa de dominio** (`app/Models/`). Representa las entidades de negocio.
- **Vista (View)**: Ubicada en la **capa de presentación** (`resources/views/`). Muestra los datos al usuario.
- **Controlador (Controller)**: Ubicado en la **capa de presentación** (`app/Http/Controllers/`). Maneja las solicitudes HTTP.

La **base de datos** se maneja a través de:
- **Migrations y Seeders** (`database/migrations/`, `database/seeders/`): Definen la estructura y datos iniciales.
- **Repositorios** (`app/Repositories/`): Interfaz para acceder a los datos.
---

## 💻 Código de la aplicación por capas
En Laravel, la **arquitectura en capas** se aplica de la siguiente manera:

- **Modelos (app/Models):**  
  Representan las entidades y manejan la interacción con la base de datos.  
  Ejemplo: `User.php`, `Course.php`.

- **Controladores (app/Http/Controllers):**  
  Contienen la lógica de negocio y procesan las solicitudes.  
  Ejemplo: `CourseController.php`.

- **Requests (app/Http/Requests):**  
  Encargados de la validación de datos antes de llegar al controlador.  
  Ejemplo: `StoreCourseRequest.php`.

- **Migraciones (database/migrations):**  
  Definen la estructura de las tablas.  
  Ejemplo: `create_courses_table.php`.

- **Seeders (database/seeders):**  
  Insertan datos iniciales para pruebas.  
  Ejemplo: `CourseSeeder.php`.

- **Vistas (resources/views):**  
  Presentan la información al usuario con Blade.  
  Ejemplo: `courses/index.blade.php`.

- **Rutas (routes/web.php):**  
  Definen los endpoints y conectan las solicitudes con los controladores.  

---

## 📂 Evidencias de la aplicación de la arquitectura
- ✅ Los **controladores** gestionan la lógica (ejemplo: `CourseController` para CRUD de cursos).  
- ✅ Los **modelos** representan entidades (`Course`, `User`).  
- ✅ Las **migraciones** crean las tablas necesarias (`courses`, `users`, `modules`).  
- ✅ Las **vistas** muestran los datos organizados para el usuario.  
- ✅ Las **rutas** conectan cada vista con su controlador.  

---

## 🔗 Enlace del repositorio en GitHub
👉 [Repositorio en GitHub](https://github.com/tuusuario/Plataforma_microlearning_TallerProyecto)  

---

## 📌 Próximos pasos
- Integrar autenticación y roles de usuario.  
- Implementar módulos de microlearning.  
- Mejorar la interfaz con TailwindCSS.  
