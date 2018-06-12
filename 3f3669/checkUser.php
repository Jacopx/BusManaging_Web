<?php
    include 'base.php';

    if(isset($_POST['field1']) && isset($_POST['field2'])) {
        checkCookie($_POST['field1'], $_POST['field2']);
    }

    function checkCookie($user, $hash) {
        $type = -1; $data = -1;
        $conn = mysqli_connect(SQL_HOST, SQL_USER, SQL_PASS);

        if (mysqli_connect_errno()) {
            $type = 0;
            $data ="Internal error: connection to DB failed ". mysqli_connect_error();
        }
        if (!mysqli_select_db($conn, SQL_DB)) {
            $type = 0;
            $data = "Internal error: selection of DB failed";
        }

        $sql = "SELECT * FROM Users WHERE user='$user'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            // output data of each row
            while($row = mysqli_fetch_assoc($result)) {

                if ($hash == $row["pass"]) {
                    $type = 1;
                    $data = "Welcome, " . $user . "<br>";
                }
            }
        } else {
            $type = 0;
            $data = "Internal error: user not found";
        }

        echo json_encode(array("t" => $type, "d" => $data));
    }