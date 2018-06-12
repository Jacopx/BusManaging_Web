<!--* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *-->
<!--*        Distributed Programming - WebProgramming == Jacopo Nasi          *-->
<!--*      Repo avail: https://github.com/Jacopx/BusManaging_WebPlatform      *-->
<!--* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *-->

<?php
    define("BUS_SIZE", "4");
    define("SQL_HOST", "mysql");
    define("SQL_USER", "dev");
    define("SQL_PASS", "dev");
    define("SQL_DB", "database");


    if(isset($_GET['user']) && isset($_GET['pass'])) {
        login($_GET['user'], $_GET['pass']);
    }

    function login($user, $pass) {
        $conn = mysqli_connect(SQL_HOST, SQL_USER, SQL_PASS);

        if (mysqli_connect_errno()) {
            die("Internal error: connection to DB failed ".
                mysqli_connect_error());
        }
        if (!mysqli_select_db($conn, SQL_DB)) {
            die("Internal error: selection of DB failed");
        }

        $sql = "SELECT * FROM Users WHERE user='$user'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            // output data of each row
            while($row = mysqli_fetch_assoc($result)) {

                if (password_verify($pass, $row["pass"])) {
                    echo 'Password is valid!<br><br>Welcome ' . $user . ' <br>';

                    $cookie_name = 'polixbus';
                    $cookie_value = 'random_value';
                    setcookie($cookie_name, $cookie_value, time() + (86400 * 30), '/'); // 86400 = 1 day
                } else {
                    echo 'Invalid password.<br><br>';
                }

            }
        } else {
            echo "0 results";
        }
    }
?>