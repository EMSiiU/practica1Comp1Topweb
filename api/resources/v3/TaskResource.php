<?php
require_once __DIR__ . '/../../models/Task.php';
require_once __DIR__ . '/../../config/database.php';

class TaskResource {
    private $taskModel;
    private $db;

    /* Instancia el modelo*/
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->taskModel = new Task($this->db);
    }

    public function getAllTasks() {
        $result = $this->taskModel->getAll();
        $this->sendResponse(200, $result);
    }

    public function getTask($id) {
        $result = $this->taskModel->getById($id);
        if ($result) {
            $this->sendResponse(200, $result);
        } else {
            $this->sendResponse(404, ["message" => "Tarea no encontrada."]);
        }
    }

    public function createTask() {
        $input = json_decode(file_get_contents("php://input"));
        
        if (isset($input->titulo) && isset($input->completada)) {
            if ($this->taskModel->create($input)) {
                $this->sendResponse(201, ["message" => "Tarea creada exitosamente."]);
            } else {
                $this->sendResponse(503, ["message" => "No se pudo crear la tarea."]);
            }
        } else {
            $this->sendResponse(400, ["message" => "Datos incompletos. 'titulo' y 'completada' son obligatorios."]);
        }
    }

    public function updateTask($id) {
        $input = json_decode(file_get_contents("php://input"));
        
        if (isset($input->titulo) && isset($input->completada)) {
            if ($this->taskModel->update($id, $input)) {
                $this->sendResponse(200, ["message" => "Tarea actualizada exitosamente."]);
            } else {
                $this->sendResponse(503, ["message" => "No se pudo actualizar la tarea."]);
            }
        } else {
            $this->sendResponse(400, ["message" => "Datos incompletos para actualizar."]);
        }
    }

    public function sendResponse($statusCode, $data) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}

