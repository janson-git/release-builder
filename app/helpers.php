<?php

if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        return \Service\Util\Env::get($key, $default);
    }
}

if (! function_exists('__')) {
    function __($key, $lang = 'en') {
        // get translation and return
        return \Admin\App::getInstance()->getLangStringForKey($key, $lang) ?? $key;
    }
}


if (! function_exists('request')) {
    function request() {
        return \Admin\App::getInstance()->getRequest();
    }
}

if (! function_exists('dd')) {
    function dd(...$args) {
        foreach ($args as $arg) {
            var_dump($arg);
        }

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $file = $backtrace[0]['file'] ?? 'unknown';
        $line = $backtrace[0]['line'] ?? 'unknown';

        echo "<b>dd() called on line {$line} of {$file} file</b>";
        exit;
    }
}
