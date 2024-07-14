<?php


function parseActionLog($data)
{
    if (is_string($data) || is_numeric($data)) {
        return $data;
    }

    if (is_array($data)) {
        if (count($data) == 1 && isset($data[0])) {
            return parseActionLog(reset($data));
        }
        $res = [];

        $data = array_filter($data);

        $assoc = array_keys($data) !== range(0, count($data) - 1);
        foreach ($data as $k => $item) {
            $res [] = ($assoc ? "<b>{$k}</b>: " : '') . parseActionLog($item);
        }

        return $res ? implode('<br />', $res) : '';
    }

    if (is_object($data)) {
        return 'Object: ' . get_class($data) . parseActionLog(get_object_vars($data));
    }

    if (is_bool($data)) {
        return $data ? "Success" : "Fail";
    }

    return 'Closure';
}
