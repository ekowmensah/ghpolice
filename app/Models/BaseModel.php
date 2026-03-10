<?php

namespace App\Models;

use App\Config\Database;
use PDO;

abstract class BaseModel
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    public function all(array $columns = ['*']): array
    {
        $cols = implode(', ', $columns);
        $stmt = $this->db->query("SELECT {$cols} FROM {$this->table}");
        return $stmt->fetchAll();
    }
    
    public function find(int $id, array $columns = ['*']): ?array
    {
        $cols = implode(', ', $columns);
        $stmt = $this->db->prepare("SELECT {$cols} FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function where(string $column, $value, array $columns = ['*']): array
    {
        $cols = implode(', ', $columns);
        $stmt = $this->db->prepare("SELECT {$cols} FROM {$this->table} WHERE {$column} = ?");
        $stmt->execute([$value]);
        return $stmt->fetchAll();
    }
    
    public function create(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        
        return (int)$this->db->lastInsertId();
    }
    
    public function update(int $id, array $data): bool
    {
        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = "{$column} = ?";
        }
        
        $sql = sprintf(
            "UPDATE %s SET %s WHERE {$this->primaryKey} = ?",
            $this->table,
            implode(', ', $sets)
        );
        
        $values = array_values($data);
        $values[] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }
    
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }
    
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function paginate(int $page = 1, int $perPage = 25, array $columns = ['*']): array
    {
        $offset = ($page - 1) * $perPage;
        $cols = implode(', ', $columns);
        
        $countStmt = $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
        $total = $countStmt->fetch()['total'];
        
        $stmt = $this->db->prepare("SELECT {$cols} FROM {$this->table} LIMIT ? OFFSET ?");
        $stmt->execute([$perPage, $offset]);
        $data = $stmt->fetchAll();
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }
}
