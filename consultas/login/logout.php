<?php
session_start();
session_unset();
session_destroy();

// Redirigir al index principal
header("Location: ../../index.php");
exit;