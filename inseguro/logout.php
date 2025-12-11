<?php
session_start();
session_unset();
session_destroy();
header('Location: /inseguro/index.php');
exit;

