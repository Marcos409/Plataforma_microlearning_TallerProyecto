<?php

namespace App\DataAccessObjects;

use App\Contracts\UserDAOInterface;
use App\Models\User;
use Illuminate\Support\Collection; // ⚠️ IMPORTANTE: Cambiar este import
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentUserDAO implements UserDAOInterface
{
    protected User $model;

    public function __construct(User $userModel)
    {
        $this->model = $userModel;
    }

    // ============================================
    // MÉTODOS CORREGIDOS
    // ============================================

    // ✅ CORRECCIÓN 1: Eliminar parámetro $timestamp (línea 64)
    public function updateLastActivity(int $userId): bool
    {
        $user = $this->findById($userId);
        if (!$user) {
            return false;
        }
        return $user->update(['last_activity' => now()]);
    }

    // ✅ CORRECCIÓN 2: Cambiar firma completa (línea 88)
    public function search(?string $search, ?int $roleId, ?bool $active): Collection
    {
        $query = $this->model->newQuery();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleId !== null) {
            $query->where('role_id', $roleId);
        }

        if ($active !== null) {
            $query->where('active', $active);
        }

        return $query->get();
    }

    // ✅ CORRECCIÓN 3: Cambiar firma y retornar Collection (línea 95)
    public function getStudentsWithFilters(?string $search, ?string $status): Collection
    {
        $query = $this->model->students();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('student_code', 'like', "%{$search}%");
            });
        }

        if ($status) {
            // Ajusta según tu lógica de status
            if ($status === 'active') {
                $query->where('active', true);
            } elseif ($status === 'inactive') {
                $query->where('active', false);
            }
        }

        return $query->get(); // ⚠️ Cambiar de paginate() a get()
    }

    // ✅ CORRECCIÓN 4: Retornar ?int en lugar de User (línea 125)
    public function create(array $data): ?int
    {
        try {
            $user = $this->model->create($data);
            return $user->id;
        } catch (\Exception $e) {
            return null;
        }
    }

    // ✅ CORRECCIÓN 5: Cambiar tipo de retorno a ?object (línea 119)
    public function findById(int $id): ?object
    {
        return $this->model->find($id);
    }

    // ============================================
    // RESTO DE MÉTODOS (mantener igual)
    // ============================================

    public function getUsersByRole(int $roleId): Collection
    {
        return $this->model->where('role_id', $roleId)->get();
    }

    public function getStudents(): Collection
    {
        return $this->model->students()->get();
    }

    public function getAdmins(): Collection
    {
        return $this->model->admins()->get();
    }

    public function getOverallProgress(int $userId): float
    {
        return $this->model->find($userId)->progress ?? 0.0;
    }

    public function getTotalTimeSpent(int $userId): int
    {
        return $this->model->find($userId)->time_spent ?? 0;
    }

    public function getCompletedActivitiesCount(int $userId): int
    {
        return $this->model->find($userId)->completed_activities ?? 0;
    }

    public function hasActiveRiskAlerts(int $userId): bool
    {
        return $this->model->find($userId)->risk_alerts_active ?? false;
    }

    public function getActiveRiskAlerts(int $userId): Collection
    {
        return $this->model->find($userId)->active_risk_alerts ?? collect();
    }

    public function getPendingRecommendationsCount(int $userId): int
    {
        return $this->model->find($userId)->pending_recommendations ?? 0;
    }

    public function isInactiveFor(int $userId, int $days): bool
    {
        $user = $this->findById($userId);
        if (!$user || !$user->last_activity) {
            return false;
        }
        $lastActivity = \Carbon\Carbon::parse($user->last_activity);
        return $lastActivity->diffInDays(now()) >= $days;
    }

    public function countByRole(int $roleId): int
    {
        return $this->model->where('role_id', $roleId)->count();
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function update(int $id, array $data): bool
    {
        $user = $this->findById($id);

        if (!$user) {
            return false;
        }
        return $user->update($data);
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id) > 0;
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function getActiveUsers(): Collection
    {
        return $this->model->active()->get();
    }

    public function getStudentsByCareer(string $career): Collection
    {
        return $this->model
                    ->students()
                    ->where('career', $career)
                    ->get();
    }
    
    public function getTeachers(): Collection
    {
        return $this->model->teachers()->get();
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('student_code', 'like', "%{$search}%");
            });
        }

        if (isset($filters['role']) && $filters['role']) {
            $query->where('role_id', $filters['role']);
        }
        
        if (isset($filters['active']) && $filters['active'] !== null && $filters['active'] !== '') {
            $query->where('active', (bool)$filters['active']);
        }
        
        return $query;
    }

    public function getUsersWithFilters(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();
        $query = $this->applyFilters($query, $filters);
        return $query->orderBy('name')->paginate($perPage);
    }
    
    public function getUsersPendingRole(): LengthAwarePaginator
    {
        $PENDING_ROLE_ID = 4;
        return $this->model->with('role')
                           ->where('role_id', $PENDING_ROLE_ID)
                           ->orderBy('created_at', 'desc')
                           ->paginate(15);
    }
    
    public function getUsersForExport(array $filters = []): Collection
    {
        $query = $this->model->with('role');
        $query = $this->applyFilters($query, $filters);
        return $query->get();
    }

    public function bulkUpdateRoles(array $userIds, int $roleId): int
    {
        return $this->model->whereIn('id', $userIds)
                           ->update(['role_id' => $roleId]);
    }

    public function getPendingUsersCount(): int
    {
        $PENDING_ROLE_ID = 4;
        return $this->model->where('role_id', $PENDING_ROLE_ID)->count();
    }
}