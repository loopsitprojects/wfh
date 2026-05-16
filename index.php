<?php

/**
 * Proxy file for subfolder deployment
 */

// Fix the SCRIPT_NAME to prevent 404 routing errors in Laravel
$_SERVER['SCRIPT_NAME'] = '/wfh/index.php';

require __DIR__.'/public/index.php';
