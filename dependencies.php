<?php

use Pimple\Container;

global $wpdb;

$mpwd_get    = $_GET;
$mpwd_post   = $_POST;
$mpwd_cookie = $_COOKIE;
$mpwd_server = $_SERVER;

$mpwd_container = new Container();

require_once 'routes.php';
require_once 'dependencies/core.php';
require_once 'dependencies/http.php';
require_once 'dependencies/hooks.php';
require_once 'dependencies/authentication.php';
