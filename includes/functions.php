<?php
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function validateTask($data) {
    $errors = [];
    
    if (empty($data['title'])) {
        $errors[] = 'Title is required';
    }
    
    if (!in_array($data['priority'], ['low', 'medium', 'high'])) {
        $errors[] = 'Invalid priority level';
    }
    
    if (!in_array($data['status'], ['todo', 'in_progress', 'review', 'done'])) {
        $errors[] = 'Invalid status';
    }
    
    return $errors;
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function formatDuration($minutes) {
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    return $hours . 'h ' . $mins . 'm';
}
?>
