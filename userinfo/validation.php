<?php
function validate_limit($value, $default,$max){
    if ($value <= 0 || $value > $max)
        return $default;

    return $value;
}

function validate_id($value){
    if ($value < 0)
        return 0;

    return $value;
}
