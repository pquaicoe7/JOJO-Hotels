<?php
$config = require __DIR__.'/includes/config.php';
require __DIR__.'/includes/helpers.php';
start_session($config['SESSION_NAME']);
$_SESSION = [];
session_destroy();
header('Location: login.php');
