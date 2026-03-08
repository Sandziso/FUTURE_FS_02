<?php
require_once __DIR__ . '/../config/database.php';

class Lead {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM leads ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function find($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM leads WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO leads (name, email, phone, source, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$data['name'], $data['email'], $data['phone'], $data['source'], $data['status'] ?? 'new']);
    }

    public function update($id, $data) {
        $sql = "UPDATE leads SET name=?, email=?, phone=?, source=?, status=? WHERE id=?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$data['name'], $data['email'], $data['phone'], $data['source'], $data['status'], $id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM leads WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getNotes($leadId) {
        $stmt = $this->pdo->prepare("SELECT * FROM notes WHERE lead_id = ? ORDER BY created_at DESC");
        $stmt->execute([$leadId]);
        return $stmt->fetchAll();
    }

    public function addNote($leadId, $note) {
        $stmt = $this->pdo->prepare("INSERT INTO notes (lead_id, note) VALUES (?, ?)");
        return $stmt->execute([$leadId, $note]);
    }
}
?>