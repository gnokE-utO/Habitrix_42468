<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';

$db = new Database();
$pdo = $db->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        getQuests();
        break;
    case 'POST':
        createQuest();
        break;
    case 'PUT':
        updateQuest();
        break;
    case 'DELETE':
        deleteQuest();
        break;
    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}

function getQuests() {
    global $pdo;
    
    $date = $_GET['date'] ?? date('Y-m-d');
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM quests WHERE date = ? ORDER BY category, id");
        $stmt->execute([$date]);
        $quests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $questsByCategory = [
            'work' => [],
            'health' => [],
            'relationship' => []
        ];
        
        foreach($quests as $quest) {
            $questsByCategory[$quest['category']][] = $quest;
        }
        
        jsonResponse(['quests' => $questsByCategory]);
    } catch(PDOException $e) {
        jsonResponse(['error' => 'Failed to fetch quests'], 500);
    }
}

function createQuest() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO quests (title, category, date) VALUES (?, ?, ?)");
        $stmt->execute([
            sanitizeInput($data['title']),
            $data['category'],
            $data['date'] ?? date('Y-m-d')
        ]);
        
        $questId = $pdo->lastInsertId();
        $stmt = $pdo->prepare("SELECT * FROM quests WHERE id = ?");
        $stmt->execute([$questId]);
        $quest = $stmt->fetch(PDO::FETCH_ASSOC);
        
        jsonResponse(['quest' => $quest], 201);
    } catch(PDOException $e) {
        jsonResponse(['error' => 'Failed to create quest'], 500);
    }
}

function updateQuest() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $questId = $data['id'] ?? null;
    
    if (!$questId) {
        jsonResponse(['error' => 'Quest ID is required'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE quests SET completed = ? WHERE id = ?");
        $stmt->execute([$data['completed'] ? 1 : 0, $questId]);
        
        if ($stmt->rowCount() > 0) {
            jsonResponse(['message' => 'Quest updated successfully']);
        } else {
            jsonResponse(['error' => 'Quest not found'], 404);
        }
    } catch(PDOException $e) {
        jsonResponse(['error' => 'Failed to update quest'], 500);
    }
}

function deleteQuest() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $questId = $data['id'] ?? null;
    
    if (!$questId) {
        jsonResponse(['error' => 'Quest ID is required'], 400);
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM quests WHERE id = ?");
        $stmt->execute([$questId]);
        
        if ($stmt->rowCount() > 0) {
            jsonResponse(['message' => 'Quest deleted successfully']);
        } else {
            jsonResponse(['error' => 'Quest not found'], 404);
        }
    } catch(PDOException $e) {
        jsonResponse(['error' => 'Failed to delete quest'], 500);
    }
}
?>
