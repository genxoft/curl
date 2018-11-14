<?php
    if (empty($_FILES['test_file'])) {
        echo "0";
        exit;
    }
    echo $_FILES['test_file']['size'];