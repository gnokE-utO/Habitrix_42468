<?php
require_once 'database.php';
require_once 'functions.php'; // Assuming you have a functions.php with jsonResponse and sanitizeInput

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode the JSON input
    $data = json_decode(file_get_contents('php://input'), true);

    // Basic validation
    if (!isset($data['title']) || empty($data['title'])) {
        jsonResponse(['error' => 'Title is required'], 400);
        exit;
    }

    try {
        $db = new Database();
        $pdo = $db->getConnection();
        
        $stmt = $pdo->prepare("
            INSERT INTO tasks (
                title, 
                description, 
                priority, 
                category, 
                urgency, 
                importance, 
                due_date
            ) VALUES (
                :title,
                :description,
                :priority,
                :category,
                :urgency,
                :importance,
                :due_date
            )
        ");
        
        $stmt->execute([
            ':title' => sanitizeInput($data['title']),
            ':description' => sanitizeInput($data['description'] ?? ''),
            ':priority' => $data['priority'] ?? 'medium',
            ':category' => $data['category'] ?? 'work',
            ':urgency' => (int)($data['urgency'] ?? 0),
            ':importance' => (int)($data['importance'] ?? 0),
            ':due_date' => $data['due_date'] ?? null
        ]);
        
        // Fetch the newly created task to return it
        $taskId = $pdo->lastInsertId();
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$taskId]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        jsonResponse(['success' => true, 'task' => $task], 201);
    } catch (PDOException $e) {
        jsonResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
    }
} else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}
?>
