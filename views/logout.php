<?php
session_start();
//startorresumethesessionn
session_unset();
//removeallsessiondata
session_destroy();
//destroythesessionntologouttheusereffectively
header("Location: login.php");
//redirecttologinpageafterlogout
exit;
?>