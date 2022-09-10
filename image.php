<?php 

include_once dirname(__FILE__) . '/commands/keys.php';

function save_base64_image($base64_image_string, $output_file_without_extension, $path_with_end_slash = "" ) {
    $splited = explode(',', substr($base64_image_string, 5), 2);
    $mime = $splited[0];
    $data = $splited[1];

    $mime_split_without_base64 = explode(';', $mime, 2);
    $mime_split = explode('/', $mime_split_without_base64[0], 2);
    if (count($mime_split) == 2) {
        $extension = $mime_split[1];
        if ($extension == 'jpeg') $extension = 'jpg';
        $output_file_with_extension = $output_file_without_extension . '.' . $extension;
    }

    file_put_contents($path_with_end_slash . $output_file_with_extension, base64_decode($data), LOCK_EX);
    return $output_file_with_extension;
}

if (!isset($_POST['key']) || empty($_POST['key']) || !isset($_POST['image']) || empty($_POST['image'])) {
    $json = [
        'error' => 404,
        'message' => 'Invalid request',
        'image' => ''
    ];
    
    echo json_encode($json);
    return;
}

$key = $_POST['key'];
$image = $_POST['image'];
$domain = $_SERVER['HTTP_REFERER'];

if (!isset($keys[$key]) || !in_array($domain, $keys[$key])) {
    $json = [
        'error' => 404,
        'message' => 'Invalid key',
        'image' => ''
    ];
    
    echo json_encode($json);
    return;
}

$img = 'data:image/png;base64,' . $image;

if (!$img) {
    $json = [
        'error' => 404,
        'message' => 'Invalid image',
        'image' => ''
    ];
    
    echo json_encode($json);
    return;
}

$output = dirname(__FILE__) . "/data/$key";
if (!file_exists($output)) {
    mkdir($output);
}

$time = time();

$image_input = save_base64_image($img, 'input-' . $time, "$output/");

echo $image_input;
return;