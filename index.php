<?php 

include_once dirname(__FILE__) . '/commands/keys.php';

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
$type = 'embroidery';

if (!isset($keys[$key]) || !in_array($domain, $keys[$key])) {
    $json = [
        'error' => 404,
        'message' => 'Invalid key',
        'image' => ''
    ];
    
    echo json_encode($json);
    return;
}

// $img = 'data:image/png;base64,' . $image;

// if (!$img) {
//     $json = [
//         'error' => 404,
//         'message' => 'Invalid image',
//         'image' => $img_base64
//     ];
    
//     echo json_encode($json);
//     return;
// }

$output = dirname(__FILE__) . "/data/$key";
if (!file_exists($output)) {
    mkdir($output);
}

$time = time();

// $image_input = save_base64_image($img, 'input-' . $time, "$output/");
$image_input = "$output/$image";
// chmod($image_input, 0777);
if (!file_exists($image_input)) {
    $json = [
        'error' => 404,
        'message' => 'Input image not found',
        'image' => ''
    ];
    
    echo json_encode($json);
    return;
}

$img_type = pathinfo($image_input, PATHINFO_EXTENSION);

$image_output = $output . '/output-' . $time . '.' . $img_type;

// $new_image_input = $output . '/input-1662148533.png';

$cmd = dirname(__FILE__) . '/commands/' . $type;
echo (shell_exec("bash $cmd -n 8 -p linear -t 2 -e 0 -P preserve $image_input $image_output"));

if (!file_exists($image_output)) {
    $json = [
        'error' => 204,
        'message' => 'Error processing image',
        'image' => ''
    ];
    
    echo json_encode($json);
    return;
}

$img_data = file_get_contents($image_output);
$img_base64 = 'data:image/' . $img_type . ';base64,' . base64_encode($img_data);

$json = [
    'error' => 200,
    'message' => 'Success',
    'image' => $img_base64
];

echo json_encode($json);
return;