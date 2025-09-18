# Plataforma_microlearning_TallerProyecto
Plataforma de microlearning con arquitectura por capas en Laravel
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
