<?php
$db = require __DIR__ . '/db.php';
// test database! Important not to run tests on production or development databases
$db['dsn'] = 'sqlite:' . __DIR__ . '/../runtime/test_database.sqlite';

return $db;
