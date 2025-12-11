<?php
session_start();
session_unset();
session_destroy();
header('Location: /act20/index.php');
exit;

