<?php
declare(strict_types=1);

namespace App\Models;

use Core\Database;

abstract class BaseModel
{
    protected Database $db;
    protected string $table = '';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): ?array
    {
        return $this->db->fetch("SELECT * FROM `{$this->table}` WHERE id = :id LIMIT 1", ['id' => $id]);
    }

    public function create(array $data): int
    {
        return $this->db->insert($this->table, $data);
    }

    public function updateById(int $id, array $data): int
    {
        return $this->db->update($this->table, $data, 'id = :id', ['id' => $id]);
    }

    public function deleteById(int $id): int
    {
        return $this->db->delete($this->table, 'id = :id', ['id' => $id]);
    }
}
