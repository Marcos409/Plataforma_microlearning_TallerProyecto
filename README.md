# 📚 Plataforma de Microlearning con Laravel

## 📌 Descripción
Plataforma educativa con enfoque en **microlearning**, desarrollada en **Laravel** usando una **arquitectura por capas** para separar responsabilidades y facilitar el mantenimiento.

---

## 🏗️ Arquitectura del Proyecto
Este proyecto sigue una **arquitectura por capas** para garantizar escalabilidad y modularidad:

```mermaid
graph TD;
    Presentacion-->LogicaNegocio;
    LogicaNegocio-->Persistencia;
    Persistencia-->Dominio;

Plataforma_microlearning_TallerProyecto/
├── app/
│   ├── Http/
│   │   ├── Controllers/   # Controladores
│   │   └── Requests/      # Validaciones de solicitudes
│   └── Models/            # Modelos
├── database/
│   ├── migrations/        # Estructura de la base de datos
│   └── seeders/           # Datos iniciales
├── resources/
│   └── views/              # Vistas
└── routes/                 # Rutas
