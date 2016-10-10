<?php

if (!function_exists('__')) {
    function __() {
        $args = func_get_args();
        $nargs = func_num_args();
        $trace = debug_backtrace();
        $caller = array_shift($trace);

        $key = $caller['file'].':'.$caller['line'];

        echo '<pre>', $key, "\n";
        for ($i=0; $i<$nargs; $i++)
            echo print_r($args[$i], 1), "\n";
        
        echo '</pre>';
    }
}

function array_combine_($keys, $values)
{
    $result = array();
    foreach ($keys as $i => $k) {
        $result[$k][] = isset($values[$i]) ? $values[$i] : '';
    }
    array_walk($result, create_function('&$v', '$v = (count($v) == 1)? array_pop($v): $v;'));
    return    $result;
}

function prepare($data = [], $intersect = [])
{
    $result = [];

    if (!empty($intersect) && !empty($data))
    {
        $result = array_combine_($intersect, $data);

        foreach ($result as $name => $value)
        {
            if ($name == 'name')
            {
                $value = str_replace(array(' , шт', ', шт'), '', $value);
            }

            if ($name == 'count')
            {
                $value = round($value, 0);
            }

            if (in_array($name, [ 'price', 'total' ]))
            {
                $value = intval(str_replace(',', '', $value));
            }

            $result[$name] = $value;
        }
    }

    return $result;
}

function array_keys_exists($array, $keys) {
    
    foreach ($array as $key)
    {
        if(!in_array($key, $keys)) {
            return false;
        }
    }

    return true;
}