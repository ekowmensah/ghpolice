<?php

namespace App\Models;

class Role extends BaseModel
{
    protected string $table = 'roles';
    
    public function getWithPermissions(int $id): ?array
    {
        return $this->find($id);
    }
    
    public function getPermissions(int $roleId): array
    {
        $role = $this->find($roleId);
        
        if (!$role) {
            return [];
        }
        
        $permissions = [];
        
        if ($role['can_manage_cases']) $permissions[] = 'manage_cases';
        if ($role['can_manage_officers']) $permissions[] = 'manage_officers';
        if ($role['can_manage_evidence']) $permissions[] = 'manage_evidence';
        if ($role['can_manage_firearms']) $permissions[] = 'manage_firearms';
        if ($role['can_view_intelligence']) $permissions[] = 'view_intelligence';
        if ($role['can_approve_operations']) $permissions[] = 'approve_operations';
        if ($role['can_manage_users']) $permissions[] = 'manage_users';
        if ($role['can_view_reports']) $permissions[] = 'view_reports';
        if ($role['can_export_data']) $permissions[] = 'export_data';
        if ($role['is_system_admin']) $permissions[] = 'system_admin';
        
        return $permissions;
    }
    
    public function getByAccessLevel(string $accessLevel): array
    {
        $sql = "SELECT * FROM roles WHERE access_level = ? ORDER BY role_name";
        return $this->query($sql, [$accessLevel]);
    }
}
