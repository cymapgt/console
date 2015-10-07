<?php
//Levels for search status for service names
if (!(defined('CONSOLE_REGISTER_SERVICE_UNCHANGED'))) {
    define('CONSOLE_REGISTER_SERVICE_UNCHANGED', 0);
}

if (!(defined('CONSOLE_REGISTER_SERVICE_CHANGED'))) {
    define('CONSOLE_REGISTER_SERVICE_CHANGED', 1);
}

if (!(defined('CONSOLE_REGISTER_SERVICE_NOTFOUND'))) {
    define('CONSOLE_REGISTER_SERVICE_NOTFOUND', 2);
}
