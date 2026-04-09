<?php
require 'db.php';
session_unset();
session_destroy();
session_start();
flash('success', 'Logged out successfully.');
header('Location: index.php');
exit;
