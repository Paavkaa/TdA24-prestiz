<?php
require_once __DIR__.'/router.php';

// Static GET
// In the URL -> http://localhost:8080/api
// The output -> Index
get('/api', 'backend/api/api.php');

// Static GET
// In the URL -> http://localhost:8080/
// The output -> Index
get('/', 'public/main.php');