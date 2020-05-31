<?php
include('/home/texhav/texturehaven.com/php/secret_config.php');

$servername = $GLOBALS['DB_SERV'];
$dbname = $GLOBALS['DB_NAME'];
$username = $GLOBALS['DB_USER'];
$password = $GLOBALS['DB_PASS'];
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
mysqli_set_charset($conn, 'utf8');

$sql = "SELECT * FROM social_media WHERE post_datetime <= NOW() ORDER BY post_datetime ASC";
$result = mysqli_query($conn, $sql);
$array = array();
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($array, $row);
    }
}

foreach($array as $post){
    if (!$post['published']){
        $sql = "UPDATE social_media SET published=1 WHERE id=".$post['id'];
        $result = mysqli_query($conn, $sql);

        echo "Running id: ".$post['id']."<br>";

        // Facebook
        $text = $post['twitface'];
        $img = $post['image'];
        $xml = "value1=".$text."&value2=".$img;
        $hook_url = $GLOBALS['HOOK_FACEBOOK'];
        $ch = curl_init($hook_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $response = curl_exec($ch);
        curl_close($ch);

        // Reddit
        $text = $post['reddit'];
        if ($text){
            $link = $post['link'];
            // NOTE: URL cannot contain an '&'
            $xml = "value1=".$text."&value2=".$link;
            $hook_url = $GLOBALS['HOOK_REDDIT'];
            $ch = curl_init($hook_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
            $response = curl_exec($ch);
            curl_close($ch);
        }

        break;
    }
}

?>
