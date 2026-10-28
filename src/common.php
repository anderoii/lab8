<?php
// Екранування для HTML
function e($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
