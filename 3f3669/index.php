<!--* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *-->
<!--*        Distributed Programming - WebProgramming == Jacopo Nasi          *-->
<!--*      Repo avail: https://github.com/Jacopx/BusManaging_WebPlatform      *-->
<!--* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *-->

<?php
    if(isset($_GET['user']) && isset($_GET['pass'])) {
        echo "User: " . $_GET['user'] . ";";
        echo "Pass: " . $_GET['pass'] . ";";
    }


?>