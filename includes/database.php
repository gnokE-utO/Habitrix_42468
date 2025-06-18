<?php
require_once 'config.php';

class Database {
    private $pdo;
    
    public function __construct() {
        try {
            $this->pdo = new PDO('sqlite:' . DB_PATH);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->createTables();
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    private function createTables() {
        // Tasks table
        $sql = "
        CREATE TABLE IF NOT EXISTS tasks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT,
            priority TEXT DEFAULT 'medium',
            status TEXT DEFAULT 'todo',
            category TEXT DEFAULT 'work',
            urgency INTEGER DEFAULT 0,
            importance INTEGER DEFAULT 0,
            due_date DATE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        $this->pdo->exec($sql);
        
        // Quests table
        $sql = "
        CREATE TABLE IF NOT EXISTS quests (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            category TEXT NOT NULL,
            completed INTEGER DEFAULT 0,
            date DATE DEFAULT CURRENT_DATE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        $this->pdo->exec($sql);
        
        // Focus sessions table
        $sql = "
        CREATE TABLE IF NOT EXISTS focus_sessions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            duration INTEGER NOT NULL,
            session_type TEXT DEFAULT 'pomodoro',
            completed INTEGER DEFAULT 0,
            date DATE DEFAULT CURRENT_DATE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        $this->pdo->exec($sql);
        
        // Learning techniques table
        $sql = "
        CREATE TABLE IF NOT EXISTS learning_techniques (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            icon TEXT,
            category TEXT,
            active INTEGER DEFAULT 1
        )";
        $this->pdo->exec($sql);
        
        $this->insertDefaultData();
    }
    
    private function insertDefaultData() {
        // Insert default learning techniques if table is empty
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM learning_techniques");
        if ($stmt->fetchColumn() == 0) {
            $techniques = [
                ['Pomodoro Technique', 'Work in focused 25-minute intervals with short breaks', 'fas fa-clock', 'time'],
                ['Active Recall', 'Test yourself frequently instead of passive reading', 'fas fa-brain', 'memory'],
                ['Spaced Repetition', 'Review material at increasing intervals', 'fas fa-calendar-alt', 'memory'],
                ['Feynman Technique', 'Explain concepts in simple terms to identify gaps', 'fas fa-chalkboard-teacher', 'understanding'],
                ['Mind Mapping', 'Create visual representations of information', 'fas fa-project-diagram', 'visual'],
                ['Chunking', 'Break complex information into smaller parts', 'fas fa-puzzle-piece', 'organization']
            ];
            
            $stmt = $this->pdo->prepare("INSERT INTO learning_techniques (name, description, icon, category) VALUES (?, ?, ?, ?)");
            foreach ($techniques as $technique) {
                $stmt->execute($technique);
            }
        }
        
        // Insert sample quests for today if none exist
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM quests WHERE date = CURRENT_DATE");
        if ($stmt->fetchColumn() == 0) {
            $quests = [
                ['Complete daily standup', 'work'],
                ['Review project documentation', 'work'],
                ['30 minutes exercise', 'health'],
                ['Drink 8 glasses of water', 'health'],
                ['Call family member', 'relationship'],
                ['Send appreciation message', 'relationship']
            ];
            
            $stmt = $this->pdo->prepare("INSERT INTO quests (title, category) VALUES (?, ?)");
            foreach ($quests as $quest) {
                $stmt->execute($quest);
            }
        }
    }
}
?>
