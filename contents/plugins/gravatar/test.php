<?php
$email = "someone@somewhere.com";
$default = "http://localhost/orzoj-website/contents/uploads/user_avatars/default.gif";
$size = 40;
$grav_url = "http://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?d=" . urlencode( $default ) . "&s=" . $size;
echo $grav_url;
?>
