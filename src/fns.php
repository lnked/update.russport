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
    $result = [];
    foreach ($keys as $i => $k) {
        $result[$k][] = isset($values[$i]) ? $values[$i] : '';
    }
    array_walk($result, create_function('&$v', '$v = (count($v) == 1)? array_pop($v): $v;'));
    return    $result;
}

function roundPrice($price = 0, $increments = 5)
{
    $increments = 1 / $increments; 
    return (ceil($price * $increments) / $increments);
}

function updateProduct($product = [], $data = [], $discount = 0)
{
    if (!empty($product))
    {
        $price = floatval($data['price']);

        $price = $price-$price*$discount/100;
        
        $discount_price = roundPrice($price);

        $price = sprintf('%02d.000000', $discount_price);

        Q("UPDATE `#_product` SET `price`=?s, active=1, `date_upd`=NOW() WHERE `id_product`=?i LIMIT 1", [
            $price,
            $product['id_product']
        ]);

        return $discount_price;
    }

    return false;
}

function distinctQuery($words = [], $current = 0)
{
    if ($current == (count($words) - 1))
    {
        return Qb("SELECT DISTINCT `id_product` FROM `#_product_lang` AS `t?i` WHERE `t?i`.`name` LIKE '%?e%'", [
            $current,
            $current,
            $words[$current]
        ]);
    }

    return Qb("SELECT DISTINCT `id_product` FROM `#_product_lang` AS `t?i` WHERE `t?i`.`name` LIKE '%?e%' AND `id_product` IN (?e)", [
        $current,
        $current,
        $words[$current],
        distinctQuery($words, $current + 1)
    ]);
}

function makeQuery($name = '')
{
    $words = preg_split('/\ +/', $name, -1, PREG_SPLIT_NO_EMPTY);
    return preg_replace('/^SELECT DISTINCT `id_product`/', 'SELECT DISTINCT `id_product`, `link_rewrite`, `name`', distinctQuery($words)) . ' LIMIT 1';
}

function combine($data = [], $sample = [])
{
    return array_intersect_key($data, $sample);
}

function calculate($data = [])
{
    if (!empty($data)) {
        foreach ($data as $key => &$val)
        {
            $val['number'] = $key;
        }
    }

    return $data;
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
                $value = str_replace('  ', ' ', $value);
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