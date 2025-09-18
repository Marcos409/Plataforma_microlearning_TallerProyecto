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
