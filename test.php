<?php
$hash='$argon2i$v=19$m=65536,t=4,p=1$L1ZGTlBUNEl3TFZVNzhPRQ$uoGB1ucXEs6MXismHZVBclGCcBgvwcYNv9J1uVBetBY';

if (password_verify('rasmuslerdorf', $hash)) {
  echo 'Password is valid!';
} else {
  echo 'Invalid password.';
}
?>