// Global variables
let currentView = 'list';
let currentTab = 'tasks';
let tasks = [];
let quests = {};
let timer = {
    minutes: 25,
    seconds: 0,
    isRunning: false,
    interval: null,
    mode: 'pomodoro'
};

// Initialize app
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    loadTasks();
    loadQuests();
    loadLearningTechniques();
    loadAnalytics();
    setupEventListeners();
    updateTimerDisplay();
}

// API functions
async function apiRequest(url, options = {}) {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
        }
    };
    
    const response = await fetch(url, { ...defaultOptions, ...options });
    
    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    return await response.json();
}

// Task Management
async function loadTasks() {
    try {
        showLoading('listView');
        const data = await apiRequest('api/tasks.php');
        tasks = data.tasks;
        renderTasks();
    } catch (error) {
        showError('Failed to load tasks');
        console.error('Error loading tasks:', error);
    }
}

async function createTask(taskData) {
    try {
        const data = await apiRequest('api/tasks.php', {
            method: 'POST',
            body: JSON.stringify(taskData)
        });
        
        tasks.push(data.task);
        renderTasks();
        closeModal('taskModal');
        showSuccess('Task created successfully');
    } catch (error) {
        showError('Failed to create task');
        console.error('Error creating task:', error);
    }
}

async function updateTask(taskId, updates) {
    try {
        const data = await apiRequest('api/tasks.php', {
            method: 'PUT',
            body: JSON.stringify({ id: taskId, ...updates })
        });
        
        const taskIndex = tasks.findIndex(t => t.id == taskId);
        if (taskIndex !== -1) {
            tasks[taskIndex] = data.task;
            renderTasks();
        }
    } catch (error) {
        showError('Failed to update task');
        console.error('Error updating task:', error);
    }
}

async function deleteTask(taskId) {
    if (!confirm('Are you sure you want to delete this task?')) return;
    
    try {
        await apiRequest('api/tasks.php', {
            method: 'DELETE',
            body: JSON.stringify({ id: taskId })
        });
        
        tasks = tasks.filter(t => t.id != taskId);
        renderTasks();
        showSuccess('Task deleted successfully');
    } catch (error) {
        showError('Failed to delete task');
        console.error('Error deleting task:', error);
    }
}

function renderTasks() {
    if (currentView === 'list') {
        renderListView();
    } else if (currentView === 'kanban') {
        renderKanbanView();
    } else if (currentView === 'eisenhower') {
        renderEisenhowerView();
    }
}

function renderListView() {
    const listView = document.getElementById('listView');
    
    if (tasks.length === 0) {
        listView.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-tasks" style="font-size: 48px; color: var(--text-secondary); margin-bottom: 20px;"></i>
                <h3>No tasks yet</h3>
                <p>Create your first task to get started!</p>
            </div>
        `;
        return;
    }
    
    listView.innerHTML = tasks.map(task => `
        <div class="task-item ${task.status === 'done' ? 'completed' : ''} ${task.priority}-priority">
            <div class="task-content">
                <div class="task-info">
                    <div class="task-title">${task.title}</div>
                    <div class="task-description">${task.description || ''}</div>
                    <div class="task-meta">
                        <span><i class="fas fa-flag"></i> ${task.priority}</span>
                        <span><i class="fas fa-folder"></i> ${task.category}</span>
                        ${task.due_date ? `<span><i class="fas fa-calendar"></i> ${formatDate(task.due_date)}</span>` : ''}
                    </div>
                </div>
                <div class="task-controls">
                    <button class="task-btn btn-success" onclick="toggleTaskStatus(${task.id}, '${task.status}')">
                        <i class="fas fa-${task.status === 'done' ? 'undo' : 'check'}"></i>
                    </button>
                    <button class="task-btn btn-primary" onclick="editTask(${task.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="task-btn btn-danger" onclick="deleteTask(${task.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

function renderKanbanView() {
    const columns = {
        todo: document.getElementById('todoColumn'),
        in_progress: document.getElementById('inProgressColumn'),
        review: document.getElementById('reviewColumn'),
        done: document.getElementById('doneColumn')
    };
    
    // Clear columns
    Object.values(columns).forEach(column => column.innerHTML = '');
    
    tasks.forEach(task => {
        const column = columns[task.status];
        if (column) {
            column.innerHTML += `
                <div class="kanban-item" draggable="true" data-task-id="${task.id}">
                    <div class="task-title">${task.title}</div>
                    <div class="task-meta">
                        <span class="priority-badge ${task.priority}">${task.priority}</span>
                        ${task.due_date ? `<span class="due-date">${formatDate(task.due_date)}</span>` : ''}
                    </div>
                </div>
            `;
        }
    });
    
    setupDragAndDrop();
}

function renderEisenhowerView() {
    const quadrants = {
        urgentImportant: document.getElementById('urgentImportant'),
        notUrgentImportant: document.getElementById('notUrgentImportant'),
        urgentNotImportant: document.getElementById('urgentNotImportant'),
        notUrgentNotImportant: document.getElementById('notUrgentNotImportant')
    };
    
    // Clear quadrants
    Object.values(quadrants).forEach(quadrant => quadrant.innerHTML = '');
    
    tasks.forEach(task => {
        const isUrgent = task.urgency >= 6;
        const isImportant = task.importance >= 6;
        
        let targetQuadrant;
        if (isUrgent && isImportant) {
            targetQuadrant = quadrants.urgentImportant;
        } else if (!isUrgent && isImportant) {
            targetQuadrant = quadrants.notUrgentImportant;
        } else if (isUrgent && !isImportant) {
            targetQuadrant = quadrants.urgentNotImportant;
        } else {
            targetQuadrant = quadrants.notUrgentNotImportant;
        }
        
        targetQuadrant.innerHTML += `
            <div class="matrix-item">
                <div class="task-title">${task.title}</div>
                <div class="task-priority">${task.priority} priority</div>
            </div>
        `;
    });
}

// Quest Management
async function loadQuests() {
    try {
        const data = await apiRequest('api/quests.php');
        quests = data.quests;
        renderQuests();
    } catch (error) {
        showError('Failed to load quests');
        console.error('Error loading quests:', error);
    }
}

async function toggleQuest(questId, completed) {
    try {
        await apiRequest('api/quests.php', {
            method: 'PUT',
            body: JSON.stringify({ id: questId, completed: !completed })
        });
        
        // Update local data
        Object.keys(quests).forEach(category => {
            const quest = quests[category].find(q => q.id == questId);
            if (quest) {
                quest.completed = !completed;
            }
        });
        
        renderQuests();
        loadAnalytics(); // Refresh analytics
    } catch (error) {
        showError('Failed to update quest');
        console.error('Error updating quest:', error);
    }
}

function renderQuests() {
    const categories = ['work', 'health', 'relationship'];
    
    categories.forEach(category => {
        const container = document.getElementById(`${category}Quests`);
        const categoryQuests = quests[category] || [];
        
        container.innerHTML = categoryQuests.map(quest => `
            <div class="quest-item">
                <div class="quest-checkbox ${quest.completed ? 'checked' : ''}" 
                     onclick="toggleQuest(${quest.id}, ${quest.completed})">
                    ${quest.completed ? '✓' : ''}
                </div>
                <span class="${quest.completed ? 'completed' : ''}">${quest.title}</span>
            </div>
        `).join('');
    });
}

// Learning Techniques
async function loadLearningTechniques() {
    try {
        const data = await apiRequest('includes/database.php?action=getLearningTechniques');
        renderLearningTechniques(data.techniques);
    } catch (error) {
        // Fallback to static data
        const techniques = [
            {
                id: 1,
                name: 'Pomodoro Technique',
                description: 'Work in focused 25-minute intervals with short breaks',
                icon: 'fas fa-clock',
                category: 'time'
            },
            {
                id: 2,
                name: 'Active Recall',
                description: 'Test yourself frequently instead of passive reading',
                icon: 'fas fa-brain',
                category: 'memory'
            },
            {
                id: 3,
                name: 'Spaced Repetition',
                description: 'Review material at increasing intervals',
                icon: 'fas fa-calendar-alt',
                category: 'memory'
            },
            {
                id: 4,
                name: 'Feynman Technique',
                description: 'Explain concepts in simple terms to identify gaps',
                icon: 'fas fa-chalkboard-teacher',
                category: 'understanding'
            },
            {
                id: 5,
                name: 'Mind Mapping',
                description: 'Create visual representations of information',
                icon: 'fas fa-project-diagram',
                category: 'visual'
            },
            {
                id: 6,
                name: 'Chunking',
                description: 'Break complex information into smaller parts',
                icon: 'fas fa-puzzle-piece',
                category: 'organization'
            }
        ];
        renderLearningTechniques(techniques);
    }
}

function renderLearningTechniques(techniques) {
    const container = document.getElementById('learningTechniques');
    
    container.innerHTML = techniques.map(technique => `
        <div class="technique-card" onclick="openTechniqueModal(${technique.id})">
            <div class="technique-icon">
                <i class="${technique.icon}"></i>
            </div>
            <div class="technique-title">${technique.name}</div>
            <div class="technique-description">${technique.description}</div>
        </div>
    `).join('');
}

// Analytics
async function loadAnalytics() {
    try {
        const data = await apiRequest('api/analytics.php');
        updateAnalyticsDisplay(data);
    } catch (error) {
        showError('Failed to load analytics');
        console.error('Error loading analytics:', error);
    }
}

function updateAnalyticsDisplay(data) {
    // Update main stats
    document.getElementById('tasksCompleted').textContent = 
        `${data.tasks.completed}/${data.tasks.total}`;
    document.getElementById('focusTime').textContent = 
        formatDuration(data.focus_time);
    document.getElementById('questsCompleted').textContent = 
        `${data.quests.completed}/${data.quests.total}`;
    
    // Update sidebar quick stats
    const todayCompleted = tasks.filter(t => 
        t.status === 'done' && 
        isToday(new Date(t.updated_at))
    ).length;
    document.getElementById('todayTasks').textContent = todayCompleted;
    
    // Calculate streak (simplified)
    document.getElementById('streak').textContent = calculateStreak();
    
    // Update productivity chart (simplified)
    updateProductivityChart(data.weekly_productivity);
}

// Timer functions
function startTimer() {
    if (!timer.isRunning) {
        timer.isRunning = true;
        timer.interval = setInterval(updateTimer, 1000);
        updateTimerButtons();
    }
}

function pauseTimer() {
    if (timer.isRunning) {
        timer.isRunning = false;
        clearInterval(timer.interval);
        updateTimerButtons();
    }
}

function stopTimer() {
    timer.isRunning = false;
    clearInterval(timer.interval);
    resetTimer();
    updateTimerButtons();
}

function updateTimer() {
    if (timer.seconds === 0) {
        if (timer.minutes === 0) {
            // Timer finished
            stopTimer();
            playNotificationSound();
            showNotification('Timer finished!');
            
            // Save focus session
            saveFocusSession();
            return;
        }
        timer.minutes--;
        timer.seconds = 59;
    } else {
        timer.seconds--;
    }
    updateTimerDisplay();
}

function updateTimerDisplay() {
    const display = document.getElementById('timerDisplay');
    const minutes = timer.minutes.toString().padStart(2, '0');
    const seconds = timer.seconds.toString().padStart(2, '0');
    display.textContent = `${minutes}:${seconds}`;
}

function updateTimerButtons() {
    const startBtn = document.getElementById('startBtn');
    const pauseBtn = document.getElementById('pauseBtn');
    
    startBtn.style.display = timer.isRunning ? 'none' : 'inline-flex';
    pauseBtn.style.display = timer.isRunning ? 'inline-flex' : 'none';
}

function setTimerMode(mode, minutes) {
    if (timer.isRunning) return;
    
    timer.mode = mode;
    timer.minutes = minutes;
    timer.seconds = 0;
    updateTimerDisplay();
    
    // Update active mode
    document.querySelectorAll('.timer-mode').forEach(el => {
        el.classList.remove('active');
    });
    event.target.classList.add('active');
}

async function toggleTaskStatus(taskId) {
    const task = tasks.find(t => t.id === taskId);
    if (!task) {
        showError('Task not found for status update.');
        return;
    }

    let newStatus;
    switch (task.status) {
        case 'todo':
            newStatus = 'in_progress';
            break;
        case 'in_progress':
            newStatus = 'review';
            break;
        case 'review':
            newStatus = 'done';
            break;
        case 'done':
            newStatus = 'todo';
            break;
        default:
            newStatus = 'todo';
    }

    try {
        const response = await apiRequest(`api/tasks.php`, {
            method: 'PUT',
            body: JSON.stringify({
                id: taskId,
                status: newStatus,
                title: task.title,
                description: task.description,
                priority: task.priority,
                category: task.category,
                due_date: task.due_date,
                urgency: task.urgency,
                importance: task.importance
            })
        });

        const updatedTaskIndex = tasks.findIndex(t => t.id === taskId);
        if (updatedTaskIndex !== -1) {
            tasks[updatedTaskIndex] = response.task;
        }
        renderTasks();
        console.log('Task status updated successfully.');
        showError('Task status updated successfully.');
    } catch (error) {
        showError('Failed to update task status: ' + error.message);
        console.error('Error updating task status:', error);
    }
}

function resetTimer() {
    const modeMinutes = {
        pomodoro: 25,
        short: 5,
        long: 15
    };
    timer.minutes = modeMinutes[timer.mode];
    timer.seconds = 0;
    updateTimerDisplay();
}

async function saveFocusSession() {
    const sessionData = {
        duration: timer.mode === 'pomodoro' ? 25 : (timer.mode === 'short' ? 5 : 15),
        session_type: timer.mode,
        completed: 1
    };
    
    try {
        await apiRequest('api/focus_sessions.php', {
            method: 'POST',
            body: JSON.stringify(sessionData)
        });
        loadAnalytics(); // Refresh analytics
    } catch (error) {
        console.error('Error saving focus session:', error);
    }
}

// UI functions
function showTab(tabName) {
    currentTab = tabName;
    
    // Update nav tabs
    document.querySelectorAll('.nav-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelector(`[onclick="showTab('${tabName}')"]`).classList.add('active');
    
    // Update tab content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    document.getElementById(tabName).classList.add('active');
    
    // Load tab-specific data
    if (tabName === 'analytics') {
        loadAnalytics();
    }
}

function switchView(view) {
    currentView = view;
    
    // Update view buttons
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[onclick="switchView('${view}')"]`).classList.add('active');
    
    // Show/hide views
    const views = ['listView', 'kanbanView', 'eisenhowerView'];
    views.forEach(v => {
        const element = document.getElementById(v);
        if (v === view + 'View') {
            element.classList.remove('hidden');
        } else {
            element.classList.add('hidden');
        }
    });
    
    renderTasks();
}

function showTaskModal(taskId = null) {
    const modal = document.getElementById('taskModal');
    const form = document.getElementById('taskForm');
    
    if (taskId) {
        // Edit mode
        const task = tasks.find(t => t.id == taskId);
        if (task) {
            document.getElementById('taskTitle').value = task.title;
            document.getElementById('taskDescription').value = task.description || '';
            document.getElementById('taskPriority').value = task.priority;
            document.getElementById('taskCategory').value = task.category;
            document.getElementById('taskDueDate').value = task.due_date || '';
            document.getElementById('taskUrgency').value = task.urgency;
            document.getElementById('taskImportance').value = task.importance;
            form.dataset.taskId = taskId;
        }
    } else {
        // Create mode
        form.reset();
        delete form.dataset.taskId;
    }
    
    modal.classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

function toggleTheme() {
    const body = document.body;
    const themeIcon = document.getElementById('themeIcon');
    const themeText = document.getElementById('themeText');
    
    if (body.getAttribute('data-theme') === 'dark') {
        body.removeAttribute('data-theme');
        themeIcon.className = 'fas fa-moon';
        themeText.textContent = 'Dark Mode';
        localStorage.setItem('theme', 'light');
    } else {
        body.setAttribute('data-theme', 'dark');
        themeIcon.className = 'fas fa-sun';
        themeText.textContent = 'Light Mode';
        localStorage.setItem('theme', 'dark');
    }
}

function setupEventListeners() {
    // Task form submission
    document.getElementById('taskForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = {
            title: document.getElementById('taskTitle').value,
            description: document.getElementById('taskDescription').value,
            priority: document.getElementById('taskPriority').value,
            category: document.getElementById('taskCategory').value,
            due_date: document.getElementById('taskDueDate').value,
            urgency: parseInt(document.getElementById('taskUrgency').value),
            importance: parseInt(document.getElementById('taskImportance').value)
        };
        
        const taskId = this.dataset.taskId;
        
        if (taskId) {
            await updateTask(taskId, formData);
        } else {
            await createTask(formData);
        }
    });
    
    // Range input updates
    document.getElementById('taskUrgency').addEventListener('input', function() {
        document.getElementById('urgencyValue').textContent = this.value;
    });
    
    document.getElementById('taskImportance').addEventListener('input', function() {
        document.getElementById('importanceValue').textContent = this.value;
    });
    
    // Load saved theme
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        toggleTheme();
    }
    
    // Close modals on background click
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });
    });
}

// Utility functions
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString();
}

function formatDuration(minutes) {
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return `${hours}h ${mins}m`;
}

function isToday(date) {
    const today = new Date();
    return date.toDateString() === today.toDateString();
}

function calculateStreak() {
    // Simplified streak calculation
    return Math.floor(Math.random() * 10) + 1;
}

function showLoading(containerId) {
    const container = document.getElementById(containerId);
    container.innerHTML = '<div class="loading">Loading...</div>';
}

function showError(message) {
    // You could implement a toast notification system here
    console.error(message);
}

function showSuccess(message) {
    // You could implement a toast notification system here
    console.log(message);
}

function showNotification(message) {
    if (Notification.permission === 'granted') {
        new Notification('HabitriX', { body: message });
    }
}

function playNotificationSound() {
    // You could play a sound here
    console.log('Timer finished!');
}

function setupDragAndDrop() {
    // Simplified drag and drop for Kanban
    const kanbanItems = document.querySelectorAll('.kanban-item');
    const columns = document.querySelectorAll('.kanban-items');
    
    kanbanItems.forEach(item => {
        item.addEventListener('dragstart', function(e) {
            e.dataTransfer.setData('text/plain', this.dataset.taskId);
            this.classList.add('dragging');
        });
        
        item.addEventListener('dragend', function() {
            this.classList.remove('dragging');
        });
    });
    
    columns.forEach(column => {
        column.addEventListener('dragover', function(e) {
            e.preventDefault();
        });
        
        column.addEventListener('drop', function(e) {
            e.preventDefault();
            const taskId = e.dataTransfer.getData('text/plain');
            const newStatus = this.id.replace('Column', '').replace(/([A-Z])/g, '_$1').toLowerCase().slice(1);
            
            updateTask(taskId, { status: newStatus });
        });
    });
}

async function toggleTaskStatus(taskId, currentStatus) {
    const newStatus = currentStatus === 'done' ? 'todo' : 'done';
    await updateTask(taskId, { status: newStatus });
}

function editTask(taskId) {
    showTaskModal(taskId);
}

function openTechniqueModal(techniqueId) {
    // You could implement a detailed technique modal here
    console.log('Opening technique:', techniqueId);
}

function showPricing() {
    // You could implement a pricing modal here
    console.log('Show pricing modal');
}

function showTemplateModal() {
    // You could implement task templates here
    console.log('Show template modal');
}

function updateProductivityChart(data) {
    // You could implement Chart.js here for better charts
    console.log('Updating chart with:', data);
}
