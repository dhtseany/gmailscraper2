<?php session_start(); ?>
<html>
<head>
    <title>CSV Import Utility</title>
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>

<body>
<?php
require('dbconfig.php');

if(!empty($_GET['status'])){
    switch($_GET['status']){
        case 'succ':
            $procCount = $_GET['procCount'];
            $insertCount = $_GET['insertCount'];
            $failCount = $_GET['failCount'];
            $dupCount = $_GET['dupCount'];
            $statusMsgClass = 'alert-success';
            $statusMsg = $procCount .' records processed, '. $insertCount .' records inserted, '. $failCount .' records failed, '. $dupCount .' duplicate records ignored.';
            break;
        case 'err':
            $procCount = $_GET['procCount'];
            $insertCount = $_GET['insertCount'];
            $failCount = $_GET['failCount'];
            $dupCount = $_GET['dupCount'];
            $statusMsgClass = 'alert-danger';
            $statusMsg = 'Undefined errors occured, however '. $procCount .' records processed, '. $insertCount .' records inserted, '. $failCount .' records failed, '. $dupCount .' duplicate records ignored.';
            break;
        case 'invalid_file':
            $statusMsgClass = 'alert-danger';
            $statusMsg = 'Please upload a valid CSV file.';
            break;
        default:
            $statusMsgClass = '';
            $statusMsg = '';
    }
}
?>
<div class="container">
    <?php if(!empty($statusMsg)){
        echo '<div class="alert '.$statusMsgClass.'">'.$statusMsg.'</div>';
    } ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            Email Record Import Tool
            <a href="javascript:void(0);" onclick="$('#importFrm').slideToggle();">Collapse</a>
        </div>
        <div class="panel-body">
            <form action="importData.php" method="post" enctype="multipart/form-data" id="importFrm">
                <label>Choose Database to Import to:</label>
                    <select name="databaseName">
                        <option value="testdump1">testdump1</option>
                        <option value="primaryData">primaryData</option>
                    </select>
                <br />
                <label>Choose Table to Import to:</label>
                    <select name="tableName">
                        <option value="supportmailbox">supportmailbox</option>
                        <option value="phoneSystem">phoneSystem</option>
                    </select>
                <input type="file" name="file" /><br />
                <input type="submit" class="btn btn-primary" name="importSubmit" value="IMPORT">
            </form>
            <p>
            <?php 
            if(isset($_POST['mysqlErrors'])){
                $_SESSION['mysqlErrors'] = $mysqlErrors;
                var_dump($mysqlErrors);
            } else echo "Session wasn't set.";?>
            </p>
        </div>
    </div>
</div>
</body>
</html>