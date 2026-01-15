<?php
require_once 'session.php';
// Clear session and redirect home
session_unset();
session_destroy();
header('Location: index.php');
exit();
