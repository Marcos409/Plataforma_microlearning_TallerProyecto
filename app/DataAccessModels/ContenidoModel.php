<?php
// app/DataAccessModels/ContenidoModel.php

namespace App\DataAccessModels;

class ContenidoModel extends BaseModel 
{
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
     * Listar contenidos (método existente - mantener compatibilidad)
     */
    public function listarContenidos($subjectArea = null, $difficultyLevel = null, $type = null) 
    {
        return $this->callProcedureMultiple('sp_listar_contenidos', 
            [$subjectArea, $difficultyLevel, $type]
        );
    }

    /**
     * Obtener un contenido por ID
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
        try {
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

            return $result ? $result['id'] : false;
        } catch (\Exception $e) {
            error_log("Error en crearContenido: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar un contenido existente
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function actualizarContenido($id, $data)
    {
        return $this->callProcedureNoReturn('sp_actualizar_contenido', [
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
    }

    /**
     * Eliminar un contenido (soft delete o hard delete según SP)
     * @param int $id
     * @return bool
     */
    public function eliminarContenido($id)
    {
        return $this->callProcedureNoReturn('sp_eliminar_contenido', [$id]);
    }

    /**
     * Incrementar contador de vistas de un contenido
     * @param int $id
     * @return bool
     */
    public function incrementarVistas($id)
    {
        return $this->callProcedureNoReturn('sp_incrementar_vistas_contenido', [$id]);
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

    /**
     * Contenidos más vistos (método existente - mantener)
     */
    public function contenidosMasVistos($limit = 10) 
    {
        return $this->callProcedureMultiple('sp_contenidos_mas_vistos', [$limit]);
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
     * @return array
     */
    public function obtenerEstadisticasContenidos()
    {
        return $this->callProcedureSingle('sp_estadisticas_contenidos', []);
    }
}