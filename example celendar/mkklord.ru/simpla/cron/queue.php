<?php
$root = dirname($_SERVER['PHP_SELF'], 3);
require_once $root . '/api/Simpla.php';
(new Simpla())->queue->run();