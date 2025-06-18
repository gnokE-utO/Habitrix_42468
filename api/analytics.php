<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';

$db = new Database();
$pdo = $db->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    getAnalytics();
} else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

function getAnalytics() {
    global $pdo;
    
    try {
        // Tasks statistics
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tasks");
        $totalTasks = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as completed FROM tasks WHERE status = 'done'");
        $completedTasks = $stmt->fetch(PDO::FETCH_ASSOC)['completed'];
        
        // Focus time from sessions
        $stmt = $pdo->query("SELECT SUM(duration) as total FROM focus_sessions WHERE completed = 1 AND date = CURRENT_DATE");
        $focusTime = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Weekly productivity (tasks completed per day)
        $stmt = $pdo->query("
            SELECT DATE(updated_at) as date, COUNT(*) as completed
            FROM tasks 
            WHERE status = 'done' AND DATE(updated_at) >= DATE('now', '-7 days')
            GROUP BY DATE(updated_at)
            ORDER BY date
        ");
        $weeklyProductivity = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Quest completion today
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM quests WHERE date = CURRENT_DATE");
        $totalQuests = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as completed FROM quests WHERE date = CURRENT_DATE AND completed = 1");
        $completedQuests = $stmt->fetch(PDO::FETCH_ASSOC)['completed'];
        
        jsonResponse([
            'tasks' => [
                'total' => $totalTasks,
                'completed' => $completedTasks
            ],
            'focus_time' => $focusTime,
            'quests' => [
                'total' => $totalQuests,
                'completed' => $completedQuests
            ],
            'weekly_productivity' => $weeklyProductivity
        ]);
    } catch(PDOException $e) {
        jsonResponse(['error' => 'Failed to fetch analytics'], 500);
    }
}
?>
