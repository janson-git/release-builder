<?php

if (DEBUG_MODE) {
    // ~E_DEPRECATED used to mute vendor\slim\slim\Slim\Collection errors
    error_reporting(E_ALL & ~E_DEPRECATED);
    ini_set("display_errors", 1);
}
