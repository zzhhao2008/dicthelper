<?php
function putdata($data){
    return file_put_contents('data.php', "<?php return ".var_export($data, true).";?>");
}
function getdata(){
    return include 'data.php';
}
