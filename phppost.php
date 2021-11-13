<?
$url = 'http://yourURL.com/api';
$data = array('field1' => 'value', 'field2' => 'value');
$options = array(
        'http' => array(
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
    )
);

$context  = stream_context_create($options);
$result = file_get_contents( $url, false, $context );
$response = json_decode( $result );
?>