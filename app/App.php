<?php

namespace Swag;

// Initialize Routes
new App\Routes\SwagAdmin;
new App\Routes\REST\XAPI_Statements;
new App\Routes\REST\Swagifact_Progress;

require_once __DIR__ . '/Wordpress/Hooks.php';