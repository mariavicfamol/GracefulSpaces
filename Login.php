<?php

$host="bxqtzm3rnnsr7lwf9akd-mysql.services.clever-cloud.com";
$dbname="bxqtzm3rnnsr7lwf9akd";
$username="udgygsl4vnqzvmgp";
$password="e1vbFaaz1kHfFlZYbqII";

$conn=new mysqli($host,$username,$password,$dbname);

$usuario=$_POST['usuario'];
$password=$_POST['password'];

$sql="SELECT * FROM usuarios WHERE usuario='$usuario' AND password='$password'";

$result=$conn->query($sql);

if($result->num_rows>0){
echo "success";
}else{
echo "error";
}

?>