<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';

$db = new Database();
$pdo = $db->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        getAllTasks();
        break;
    case 'POST':
        createTask();
        break;
    case 'PUT':
        updateTask();
        break;
    case 'DELETE':
        deleteTask();
        break;
    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}

function getAllTasks() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM tasks ORDER BY created_at DESC");
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        jsonResponse(['tasks' => $tasks]);
    } catch(PDOException $e) {
        jsonResponse(['error' => 'Failed to fetch tasks'], 500);
    }
}

function createTask() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $errors = validateTask($data);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO tasks (title, description, priority, status, category, urgency, importance, due_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            sanitizeInput($data['title']),
            sanitizeInput($data['description'] ?? ''),
            $data['priority'],
            $data['status'] ?? 'todo',
            sanitizeInput($data['category'] ?? 'work'),
            (int)($data['urgency'] ?? 0),
            (int)($data['importance'] ?? 0),
            $data['due_date'] ?? null
        ]);
        
        $taskId = $pdo->lastInsertId();
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$taskId]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);
        
        jsonResponse(['task' => $task], 201);
    } catch(PDOException $e) {
        jsonResponse(['error' => 'Failed to create task'], 500);
    }
}

function updateTask() {
    global $pdo;
    
    $incomingData = json_decode(file_get_contents('php://input'), true);
    $taskId = $incomingData['id'] ?? null;
    
    if (!$taskId) {
        jsonResponse(['error' => 'Task ID is required for update'], 400);
    }

    try {
        // Fetch existing task to merge with incoming data
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$taskId]);
        $existingTask = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$existingTask) {
            jsonResponse(['error' => 'Task not found'], 404);
        }

        // Merge existing data with incoming data (incoming data overrides existing)
        $data = array_merge($existingTask, $incomingData);

        // Validate the merged data
        $errors = validateTask($data);
        if (!empty($errors)) {
            jsonResponse(['errors' => $errors], 400);
        }
        
        $stmt = $pdo->prepare("
            UPDATE tasks 
            SET title = ?, description = ?, priority = ?, status = ?, category = ?, 
                urgency = ?, importance = ?, due_date = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        
        $stmt->execute([
            sanitizeInput($data['title']),
            sanitizeInput($data['description']),
            $data['priority'],
            $data['status'],
            sanitizeInput($data['category']),
            (int)($data['urgency']),
            (int)($data['importance']),
            $data['due_date'],
            $taskId
        ]);
        
        if ($stmt->rowCount() > 0) {
            // Fetch the updated task to return it
            $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
            $stmt->execute([$taskId]);
            $updatedTask = $stmt->fetch(PDO::FETCH_ASSOC);
            jsonResponse(['task' => $updatedTask]);
        } else {
            // If rowCount is 0, it means no changes were made or task not found (already handled)
            jsonResponse(['message' => 'No changes made or task not found.', 'task' => $existingTask]);
        }
    } catch(PDOException $e) {
        error_log("PDOException in updateTask: " . $e->getMessage()); // Log detailed error server-side
        jsonResponse(['error' => 'Failed to update task due to a server error.'], 500);
    }
}

function deleteTask() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $taskId = $data['id'] ?? null;
    
    if (!$taskId) {
        jsonResponse(['error' => 'Task ID is required'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$taskId]);
        
        if ($stmt->rowCount() > 0) {
            jsonResponse(['message' => 'Task deleted successfully']);
        } else {
            jsonResponse(['error' => 'Task not found'], 404);
        }
    } catch(PDOException $e) {
        jsonResponse(['error' => 'Failed to delete task'], 500);
    }
}
?>
