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
        $errors['title'] = 'Title is required.';
    }
    
    if (isset($data['priority']) && !in_array($data['priority'], ['low', 'medium', 'high'])) {
        $errors['priority'] = 'Invalid priority level. Must be low, medium, or high.';
    } elseif (!isset($data['priority'])) {
    }
    
    if (isset($data['status']) && !in_array($data['status'], ['todo', 'in_progress', 'review', 'done'])) {
        $errors['status'] = 'Invalid status. Must be todo, in_progress, review, or done.';
    }
    
    if (isset($data['urgency']) && (!is_numeric($data['urgency']) || $data['urgency'] < 1 || $data['urgency'] > 10)) {
        $errors['urgency'] = 'Urgency must be a number between 1 and 10.';
    }
    
    if (isset($data['importance']) && (!is_numeric($data['importance']) || $data['importance'] < 1 || $data['importance'] > 10)) {
        $errors['importance'] = 'Importance must be a number between 1 and 10.';
    }

    if (isset($data['due_date']) && !empty($data['due_date'])) {
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $data['due_date'])) {
            $errors['due_date'] = 'Due date must be in YYYY-MM-DD format.';
        }
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
