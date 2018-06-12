<?php
    // * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
    // *        Distributed Programming - WebProgramming == Jacopo Nasi          *
    // *      Repo avail: https://github.com/Jacopx/BusManaging_WebPlatform      *
    // * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
    include 'base.php';

    if(isset($_POST['field1']) && isset($_POST['field2'])) {
        checkCookie($_POST['field1'], $_POST['field2']);
    }

    function checkCookie($user, $hash) {
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

                if ($hash == $row["pass"]) {
                    echo "Welcome, " . $user . "<br>";
                    exit();
                }
            }
        } else {
            die("Internal error: user not found");
        }
    }