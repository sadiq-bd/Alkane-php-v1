<?php

function timestamp() {
    return date('Y-m-d H:i:s');
}

function compare_time(string $time1, string $operator, string $time2) {
    $time1 = strtotime($time1);
    $time2 = strtotime($time2);
    switch ($operator) {
        case '>':
            return $time1 > $time2;
        case '<':
            return $time1 < $time2;
        case '>=':
            return $time1 >= $time2;
        case '<=':
            return $time1 <= $time2;
        case '=':
        case '==':
            return $time1 == $time2;
        case '!=':
            return $time1 != $time2;
        default:
            return false;
    }
}

function get_time_difference(string $time1, string $time2) {
    $time1 = strtotime($time1);
    $time2 = strtotime($time2);
    return $time2 - $time1;
}



