<?php
// app/DataAccessModels/ContenidoModel.php

namespace App\DataAccessModels;

class ContenidoModel extends BaseModel 
{
    // ==========================================
    // LISTAR Y FILTRAR CONTENIDOS
    // ==========================================
    
    /**
     * Listar contenidos con filtros opcionales
     * @param array $filters ['subject_area', 'type', 'difficulty_level', 'search']
     * @return array
     */
    public function listarContenidosFiltrados($filters = [])
    {
        $subjectArea = $filters['subject_area'] ?? null;
        $type = $filters['type'] ?? null;
        $difficultyLevel = $filters['difficulty_level'] ?? null;
        $search = $filters['search'] ?? null;

        return $this->callProcedureMultiple('sp_listar_contenidos_filtrados', 
            [$subjectArea, $type, $difficultyLevel, $search]
        );
    }

    /**
     * Listar contenidos (método legacy - mantener compatibilidad)
     * @param string|null $subjectArea
     * @param string|null $difficultyLevel
     * @param string|null $type
     * @return array
     */
    public function listarContenidos($subjectArea = null, $difficultyLevel = null, $type = null) 
    {
        return $this->callProcedureMultiple('sp_listar_contenidos', 
            [$subjectArea, $difficultyLevel, $type]
        );
    }

    /**
     * Buscar contenidos por palabra clave
     * @param string $keyword
     * @return array
     */
    public function buscarContenidos($keyword)
    {
        return $this->callProcedureMultiple('sp_buscar_contenidos', [$keyword]);
    }

    /**
     * Contenidos más vistos
     * @param int $limit
     * @return array
     */
    public function contenidosMasVistos($limit = 10) 
    {
        return $this->callProcedureMultiple('sp_contenidos_mas_vistos', [$limit]);
    }

    /**
     * Obtener contenidos recomendados para un usuario
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function obtenerContenidosRecomendados($userId, $limit = 5)
    {
        return $this->callProcedureMultiple('sp_contenidos_recomendados_usuario', 
            [$userId, $limit]
        );
    }

    // ==========================================
    // CRUD BÁSICO
    // ==========================================
    
    /**
     * Obtener un contenido por ID
     * @param int $id
     * @return array|null
     */
    public function obtenerContenido($id) 
    {
        return $this->callProcedureSingle('sp_obtener_contenido', [$id]);
    }

    /**
     * Crear un nuevo contenido
     * @param array $data
     * @return int|bool ID del nuevo contenido o false
     */
    public function crearContenido($data)
    {
        $result = $this->callProcedureSingle('sp_crear_contenido', [
            $data['title'],
            $data['description'] ?? null,
            $data['subject_area'],
            $data['topic'] ?? null,
            $data['type'],
            $data['difficulty_level'],
            $data['content_url'] ?? null,
            $data['duration_minutes'] ?? null,
            $data['tags'] ?? null,
            $data['active'] ?? 1
        ]);

        return $result ? (int) $result['id'] : false;
    }

    /**
     * Actualizar un contenido existente
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function actualizarContenido($id, $data)
    {
        $result = $this->callProcedureSingle('sp_actualizar_contenido', [
            $id,
            $data['title'],
            $data['description'] ?? null,
            $data['subject_area'],
            $data['topic'] ?? null,
            $data['type'],
            $data['difficulty_level'],
            $data['content_url'] ?? null,
            $data['duration_minutes'] ?? null,
            $data['tags'] ?? null,
            $data['active'] ?? 1
        ]);

        return $result && isset($result['affected_rows']) && (int) $result['affected_rows'] > 0;
    }

    /**
     * Eliminar un contenido (soft delete)
     * @param int $id
     * @return bool
     */
    public function eliminarContenido($id)
    {
        $result = $this->callProcedureSingle('sp_eliminar_contenido', [$id]);
        return $result && isset($result['affected_rows']) && (int) $result['affected_rows'] > 0;
    }

    // ==========================================
    // MÉTODOS DE UTILIDAD
    // ==========================================
    
    /**
     * Incrementar contador de vistas de un contenido
     * @param int $id
     * @return bool
     */
    public function incrementarVistas($id)
    {
        $result = $this->callProcedureSingle('sp_incrementar_vistas_contenido', [$id]);
        return $result && isset($result['affected_rows']) && (int) $result['affected_rows'] > 0;
    }

    /**
     * Obtener áreas de materia únicas (subject_area)
     * @return array
     */
    public function obtenerAreasUnicas()
    {
        $result = $this->callProcedureMultiple('sp_obtener_areas_unicas', []);
        return array_column($result, 'subject_area');
    }

    /**
     * Obtener tipos de contenido únicos
     * @return array
     */
    public function obtenerTiposUnicos()
    {
        $result = $this->callProcedureMultiple('sp_obtener_tipos_unicos', []);
        return array_column($result, 'type');
    }

    // ==========================================
    // ESTADÍSTICAS E INFORMACIÓN
    // ==========================================
    
    /**
     * Contar contenidos por tipo
     * @return array
     */
    public function contarContenidosPorTipo()
    {
        return $this->callProcedureMultiple('sp_contar_contenidos_por_tipo', []);
    }

    /**
     * Obtener estadísticas de contenidos
     * @return array|null
     */
    public function obtenerEstadisticasContenidos()
    {
        $result = $this->callProcedureSingle('sp_estadisticas_contenidos', []);
        
        return $result ?: [
            'total_contenidos' => 0,
            'contenidos_activos' => 0,
            'total_vistas' => 0,
            'duracion_promedio' => 0,
            'total_areas' => 0,
            'total_tipos' => 0
        ];
    }

    /**
     * Contar total de contenidos
     * @return int
     */
    public function contarContenidos()
    {
        $stats = $this->obtenerEstadisticasContenidos();
        return (int) ($stats['total_contenidos'] ?? 0);
    }

    /**
     * Contar contenidos activos
     * @return int
     */
    public function contarContenidosActivos()
    {
        $stats = $this->obtenerEstadisticasContenidos();
        return (int) ($stats['contenidos_activos'] ?? 0);
    }

    /**
     * Obtener contenidos por área
     * @param string $subjectArea
     * @return array
     */
    public function obtenerContenidosPorArea($subjectArea)
    {
        return $this->listarContenidos($subjectArea, null, null);
    }

    /**
     * Obtener contenidos por tipo
     * @param string $type
     * @return array
     */
    public function obtenerContenidosPorTipo($type)
    {
        return $this->listarContenidos(null, null, $type);
    }

    /**
     * Obtener contenidos por nivel de dificultad
     * @param string $difficultyLevel
     * @return array
     */
    public function obtenerContenidosPorDificultad($difficultyLevel)
    {
        return $this->listarContenidos(null, $difficultyLevel, null);
    }

    /**
     * Validar datos de contenido antes de crear/actualizar
     * @param array $data
     * @return array Array con 'valid' (bool) y 'errors' (array)
     */
    public function validarDatosContenido($data)
    {
        $errors = [];

        // Validar campos requeridos
        if (empty($data['title'])) {
            $errors[] = 'El título es requerido';
        }

        if (empty($data['subject_area'])) {
            $errors[] = 'El área de materia es requerida';
        }

        if (empty($data['type'])) {
            $errors[] = 'El tipo de contenido es requerido';
        }

        if (empty($data['difficulty_level'])) {
            $errors[] = 'El nivel de dificultad es requerido';
        }

        // Validar tipos permitidos
        $tiposPermitidos = ['Video', 'Documento', 'Interactivo', 'Quiz', 'Artículo'];
        if (!empty($data['type']) && !in_array($data['type'], $tiposPermitidos)) {
            $errors[] = 'Tipo de contenido no válido';
        }

        // Validar niveles de dificultad permitidos
        $nivelesPermitidos = ['Básico', 'Intermedio', 'Avanzado'];
        if (!empty($data['difficulty_level']) && !in_array($data['difficulty_level'], $nivelesPermitidos)) {
            $errors[] = 'Nivel de dificultad no válido';
        }

        // Validar duración si se proporciona
        if (isset($data['duration_minutes']) && (!is_numeric($data['duration_minutes']) || $data['duration_minutes'] < 0)) {
            $errors[] = 'La duración debe ser un número positivo';
        }

        // Validar URL si se proporciona
        if (!empty($data['content_url']) && !filter_var($data['content_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'La URL del contenido no es válida';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Verificar si un contenido existe
     * @param int $id
     * @return bool
     */
    public function existeContenido($id)
    {
        $contenido = $this->obtenerContenido($id);
        return $contenido !== null;
    }

    /**
     * Activar un contenido
     * @param int $id
     * @return bool
     */
    public function activarContenido($id)
    {
        $contenido = $this->obtenerContenido($id);
        
        if (!$contenido) {
            return false;
        }

        return $this->actualizarContenido($id, array_merge($contenido, ['active' => 1]));
    }

    /**
     * Desactivar un contenido
     * @param int $id
     * @return bool
     */
    public function desactivarContenido($id)
    {
        $contenido = $this->obtenerContenido($id);
        
        if (!$contenido) {
            return false;
        }

        return $this->actualizarContenido($id, array_merge($contenido, ['active' => 0]));
    }
}