<?php
class Task {
    private $db;
    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function getAll() {
        $query = "SELECT id, title AS titulo, is_completed AS completada, created_at AS fecha_creacion FROM tasks";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($tasks as &$task) {
            $task['completada'] = (bool)$task['completada'];
        }
        
        return $tasks;
    }

    public function getById($id) {
        $query = "SELECT id, title AS titulo, is_completed AS completada, created_at AS fecha_creacion FROM tasks WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $task = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($task) {
            $task['completada'] = (bool)$task['completada'];
        }
        
        return $task;
    }

    public function create($data) {
        $query = "INSERT INTO tasks (title, is_completed) VALUES (:title, :is_completed)";
        $stmt = $this->db->prepare($query);
        
        /* Limpieza basica de los datos entrantes */
        $title = htmlspecialchars(strip_tags($data->titulo));
        $isCompleted = $data->completada ? 1 : 0;
        
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':is_completed', $isCompleted, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function update($id, $data) {
        $query = "UPDATE tasks SET title = :title, is_completed = :is_completed WHERE id = :id";
        $stmt = $this->db->prepare($query);
        
        $title = htmlspecialchars(strip_tags($data->titulo));
        $isCompleted = $data->completada ? 1 : 0;
        
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':is_completed', $isCompleted, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}
