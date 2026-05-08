<?php
function render_order_status_tracker($status) {
    $status = strtolower((string) $status);
    $steps = ['pending' => 'Pending', 'processing' => 'Processing', 'completed' => 'Completed'];
    $step_keys = array_keys($steps);
    $current_index = array_search($status, $step_keys, true);
    $is_cancelled = $status === 'cancelled';

    if ($current_index === false) {
        $current_index = 0;
    }

    echo '<div class="order-status-tracker ' . ($is_cancelled ? 'is-cancelled' : '') . '">';

    foreach ($steps as $key => $label) {
        $step_index = array_search($key, $step_keys, true);
        $class = $step_index < $current_index ? 'is-complete' : '';
        $class = $step_index === $current_index ? 'is-current' : $class;

        if ($is_cancelled) {
            $class = '';
        }

        echo '<div class="status-step ' . $class . '">';
        echo '<span class="status-dot"><i class="fas ' . ($step_index < $current_index ? 'fa-check' : 'fa-circle') . '"></i></span>';
        echo '<span>' . htmlspecialchars($label) . '</span>';
        echo '</div>';
    }

    if ($is_cancelled) {
        echo '<div class="status-step is-cancelled-step">';
        echo '<span class="status-dot"><i class="fas fa-xmark"></i></span>';
        echo '<span>Cancelled</span>';
        echo '</div>';
    }

    echo '</div>';
}
?>
