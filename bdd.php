

<?php

  define('HOST','localhost'); //remplacer localhost par    0.tcp.ngrok.io:17218

  define('USER','root');

  define('PASS','');

  define('DB','boutique');

  $db=new mysqli(HOST,USER,PASS,DB);
  