<?php
require_once 'includes/config.php';
require_once 'includes/database.php';

// Initialize database
$db = new Database();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HabitriX - Complete Productivity Suite</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Extension Status Indicator -->
    <div id="extensionStatus" class="extension-status">
        <i class="fas fa-shield-alt"></i> HabitriX Monitor Active
    </div>

    <!-- Pricing Badge -->
    <div class="pricing-badge" onclick="showPricing()">
        <i class="fas fa-crown"></i> Upgrade to Pro
    </div>

    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="logo">
                <i class="fas fa-cube"></i>
                HabitriX
            </div>
            <div class="header-controls">
                <button class="theme-toggle" onclick="toggleTheme()">
                    <i class="fas fa-moon" id="themeIcon"></i>
                    <span id="themeText">Dark Mode</span>
                </button>
                <div class="user-info">
                    <div class="user-avatar">JD</div>
                    <div>
                        <div style="font-weight: 600;">John Doe</div>
                        <div style="font-size: 14px; color: var(--text-secondary);">Free Plan</div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Navigation -->
        <nav class="nav-tabs">
            <div class="nav-tab active" onclick="showTab('tasks')">
                <i class="fas fa-tasks"></i> Task Management
            </div>
            <div class="nav-tab" onclick="showTab('time')">
                <i class="fas fa-clock"></i> Time Management
            </div>
            <div class="nav-tab" onclick="showTab('learning')">
                <i class="fas fa-graduation-cap"></i> Learning Techniques
            </div>
            <div class="nav-tab" onclick="showTab('analytics')">
                <i class="fas fa-chart-line"></i> Analytics
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Task Management Tab -->
            <div id="tasks" class="content-area tab-content active">
                <div class="task-header">
                    <h2>Task Management</h2>
                    <div class="task-actions">
                        <button class="btn btn-primary" onclick="showTaskModal()">
                            <i class="fas fa-plus"></i> Add Task
                        </button>
                        <button class="btn btn-secondary" onclick="showTemplateModal()">
                            <i class="fas fa-file-alt"></i> Templates
                        </button>
                    </div>
                </div>

                <!-- View Toggle -->
                <div class="view-toggle">
                    <div class="view-btn active" onclick="switchView('list')">
                        <i class="fas fa-list"></i> List
                    </div>
                    <div class="view-btn" onclick="switchView('kanban')">
                        <i class="fas fa-columns"></i> Kanban
                    </div>
                    <div class="view-btn" onclick="switchView('eisenhower')">
                        <i class="fas fa-th"></i> Matrix
                    </div>
                </div>

                <!-- Task List View -->
                <div id="listView" class="task-list">
                    <!-- Tasks will be populated here -->
                </div>

                <!-- Kanban View -->
                <div id="kanbanView" class="kanban-board hidden">
                    <div class="kanban-column">
                        <div class="kanban-header">To Do</div>
                        <div id="todoColumn" class="kanban-items"></div>
                    </div>
                    <div class="kanban-column">
                        <div class="kanban-header">In Progress</div>
                        <div id="inProgressColumn" class="kanban-items"></div>
                    </div>
                    <div class="kanban-column">
                        <div class="kanban-header">Review</div>
                        <div id="reviewColumn" class="kanban-items"></div>
                    </div>
                    <div class="kanban-column">
                        <div class="kanban-header">Done</div>
                        <div id="doneColumn" class="kanban-items"></div>
                    </div>
                </div>

                <!-- Eisenhower Matrix View -->
                <div id="eisenhowerView" class="eisenhower-matrix hidden">
                    <div class="matrix-quadrant">
                        <div class="quadrant-title quadrant-urgent-important">Urgent & Important</div>
                        <div id="urgentImportant" class="quadrant-items"></div>
                    </div>
                    <div class="matrix-quadrant">
                        <div class="quadrant-title quadrant-not-urgent-important">Not Urgent & Important</div>
                        <div id="notUrgentImportant" class="quadrant-items"></div>
                    </div>
                    <div class="matrix-quadrant">
                        <div class="quadrant-title quadrant-urgent-not-important">Urgent & Not Important</div>
                        <div id="urgentNotImportant" class="quadrant-items"></div>
                    </div>
                    <div class="matrix-quadrant">
                        <div class="quadrant-title quadrant-not-urgent-not-important">Not Urgent & Not Important</div>
                        <div id="notUrgentNotImportant" class="quadrant-items"></div>
                    </div>
                </div>
            </div>

            <!-- Time Management Tab -->
            <div id="time" class="content-area tab-content">
                <h2>Time Management</h2>
                
                <!-- Daily Quests -->
                <div class="daily-quests">
                    <div class="quest-category">
                        <div class="quest-header">
                            <i class="fas fa-briefcase" style="color: var(--primary-color);"></i>
                            Work Quests
                        </div>
                        <div class="quest-list" id="workQuests">
                            <!-- Work quests will be populated here -->
                        </div>
                    </div>
                    <div class="quest-category">
                        <div class="quest-header">
                            <i class="fas fa-heart" style="color: var(--danger-color);"></i>
                            Health Quests
                        </div>
                        <div class="quest-list" id="healthQuests">
                            <!-- Health quests will be populated here -->
                        </div>
                    </div>
                    <div class="quest-category">
                        <div class="quest-header">
                            <i class="fas fa-users" style="color: var(--success-color);"></i>
                            Relationship Quests
                        </div>
                        <div class="quest-list" id="relationshipQuests">
                            <!-- Relationship quests will be populated here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Learning Techniques Tab -->
            <div id="learning" class="content-area tab-content">
                <h2>Learning Techniques</h2>
                <div class="learning-techniques" id="learningTechniques">
                    <!-- Learning techniques will be populated here -->
                </div>
            </div>

            <!-- Analytics Tab -->
            <div id="analytics" class="content-area tab-content">
                <h2>Analytics & Insights</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number" id="tasksCompleted">0/0</div>
                        <div class="stat-label">Tasks Completed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="focusTime">0h 0m</div>
                        <div class="stat-label">Focus Time</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="questsCompleted">0/0</div>
                        <div class="stat-label">Daily Quests</div>
                    </div>
                </div>
                
                <div class="productivity-chart">
                    <h3>Weekly Productivity</h3>
                    <canvas id="productivityChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Pomodoro Timer Widget -->
                <div class="widget pomodoro-timer">
                    <div class="widget-title">
                        <i class="fas fa-clock"></i>
                        Pomodoro Timer
                    </div>
                    <div class="timer-display" id="timerDisplay">25:00</div>
                    <div class="timer-controls">
                        <button class="timer-btn start" id="startBtn" onclick="startTimer()">
                            <i class="fas fa-play"></i>
                        </button>
                        <button class="timer-btn pause" id="pauseBtn" onclick="pauseTimer()">
                            <i class="fas fa-pause"></i>
                        </button>
                        <button class="timer-btn stop" id="stopBtn" onclick="stopTimer()">
                            <i class="fas fa-stop"></i>
                        </button>
                    </div>
                    <div class="timer-modes">
                        <div class="timer-mode active" onclick="setTimerMode('pomodoro', 25)">
                            Work (25m)
                        </div>
                        <div class="timer-mode" onclick="setTimerMode('short', 5)">
                            Break (5m)
                        </div>
                        <div class="timer-mode" onclick="setTimerMode('long', 15)">
                            Long Break (15m)
                        </div>
                    </div>
                </div>

                <!-- Quick Stats Widget -->
                <div class="widget">
                    <div class="widget-title">
                        <i class="fas fa-chart-pie"></i>
                        Quick Stats
                    </div>
                    <div class="quick-stats">
                        <div class="quick-stat">
                            <div class="stat-value" id="todayTasks">0</div>
                            <div class="stat-desc">Tasks Today</div>
                        </div>
                        <div class="quick-stat">
                            <div class="stat-value" id="streak">0</div>
                            <div class="stat-desc">Day Streak</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Modal -->
    <div id="taskModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add New Task</h3>
                <button class="modal-close" onclick="closeModal('taskModal')">&times;</button>
            </div>
            <form id="taskForm" class="task-form">
                <div class="form-group">
                    <label class="form-label">Title</label>
                    <input type="text" class="form-input" id="taskTitle" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-input form-textarea" id="taskDescription"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Priority</label>
                    <select class="form-select" id="taskPriority">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select class="form-select" id="taskCategory">
                        <option value="work">Work</option>
                        <option value="personal">Personal</option>
                        <option value="health">Health</option>
                        <option value="learning">Learning</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Due Date</label>
                    <input type="date" class="form-input" id="taskDueDate">
                </div>
                <div class="form-group">
                    <label class="form-label">Urgency (1-10)</label>
                    <input type="range" class="form-input" id="taskUrgency" min="1" max="10" value="5">
                    <span id="urgencyValue">5</span>
                </div>
                <div class="form-group">
                    <label class="form-label">Importance (1-10)</label>
                    <input type="range" class="form-input" id="taskImportance" min="1" max="10" value="5">
                    <span id="importanceValue">5</span>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Task
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="app.js"></script>
</body>
</html>
