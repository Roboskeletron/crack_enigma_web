<?php
function response_with_message($code, $message){
    response_with_array($code, array("message" => $message));
}

function response_with_array($code, $arr){
    http_response_code($code);
    echo json_encode($arr);
}

function get_raw_json(){
    return get_json_from_stream(fopen("php://input", "r"));
}

function get_json_from_stream($stream)
{
    $text = "";
    while ($data = fread($stream, 1024)) {
        $text = $text . $data;
    }

    fclose($stream);

    return json_decode($text, true);
}
