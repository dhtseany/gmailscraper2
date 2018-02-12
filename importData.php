<?php
session_start();
require('functions.php');
require('settings.php');
require('dbconfig.php');


if(isset($_POST['importSubmit'])){
    $sqlDatabase = $_POST['databaseName'];
    $sqlTable = $_POST['tableName'];
    $db = new mysqli($sqlHost, $sqlUser, $sqlPwd, $sqlDatabase);
    if(mysqli_connect_errno()){
        echo "Error: Could not connect to database.";
        exit;
    }
    if(is_uploaded_file($_FILES['file']['tmp_name'])){
        //open uploaded csv file with read only mode
        $csvFile = fopen($_FILES['file']['tmp_name'], 'r');
        // Check which SQL table we're working with to determine the script that should continue
        // if($sqlTable === 'supportmailbox'){
        //     importTosupportmailbox($csvFile,$sqlTable,$db);
        // }
        if(!empty($_POST['tableName'])){
            switch($_POST['tableName']){
                case 'supportmailbox':
                    importTosupportmailbox($csvFile,$sqlTable,$db);
                    break;
                case 'phoneSystem':
                    importTophoneSystem($csvFile,$sqlTable,$db);
                    break;
                default:
                    $statusMsgClass = '';
                    $statusMsg = '';
            }
        }
    }else{
        $qstring = '?status=invalid_file';
    }
}
?>