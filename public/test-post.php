<?php
echo "POST OK";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    print_r($_POST);
    print_r($_FILES);
}
