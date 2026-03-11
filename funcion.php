<?php

$host="bxqtzm3rnnsr7lwf9akd-mysql.services.clever-cloud.com";
$dbname="bxqtzm3rnnsr7lwf9akd";
$username="udgygsl4vnqzvmgp";
$password="e1vbFaaz1kHfFlZYbqII";

$conn=new mysqli($host,$username,$password,$dbname);

if($conn->connect_error){
die(json_encode(["error"=>"conexion fallida"]));
}

$method=$_SERVER["REQUEST_METHOD"];


/* CARGAR EMPLEADOS */

if($method=="GET"){

header("Content-Type: application/json");

$result=$conn->query("SELECT * FROM empleados");

$datos=[];

while($row=$result->fetch_assoc()){
$datos[]=$row;
}

echo json_encode($datos);

}


/* CRUD */

if($method=="POST"){

// Verificar si viene FormData (con imagen) o JSON
if(isset($_POST['accion'])){
// FormData
$accion=$_POST["accion"];
$id=$_POST["id"];
$nombre=$_POST["nombre"];
$funcion=$_POST["funcion"];
$foto="";

// Manejar subida de imagen
if(isset($_FILES['foto']) && $_FILES['foto']['error']==0){
$carpeta="uploads/";
if(!file_exists($carpeta)){
mkdir($carpeta,0777,true);
}
$nombreArchivo=time()."_".$_FILES['foto']['name'];
$rutaDestino=$carpeta.$nombreArchivo;
if(move_uploaded_file($_FILES['foto']['tmp_name'],$rutaDestino)){
$foto=$rutaDestino;
}
}

}else{
// JSON
$data=json_decode(file_get_contents("php://input"),true);
$accion=$data["accion"];
$id=$data["id"];
$nombre=$data["nombre"];
$funcion=$data["funcion"];
$foto="";
}


/* CREAR */

if($accion=="crear"){

if($foto!=""){
$sql="INSERT INTO empleados(nombre_empleado,funcion,foto)
VALUES('$nombre','$funcion','$foto')";
}else{
$sql="INSERT INTO empleados(nombre_empleado,funcion)
VALUES('$nombre','$funcion')";
}

$conn->query($sql);

}


/* EDITAR */

if($accion=="editar"){

if($foto!=""){
$sql="UPDATE empleados
SET nombre_empleado='$nombre', funcion='$funcion', foto='$foto'
WHERE id='$id'";
}else{
$sql="UPDATE empleados
SET nombre_empleado='$nombre', funcion='$funcion'
WHERE id='$id'";
}

$conn->query($sql);

}


/* ELIMINAR */

if($accion=="eliminar"){

$sql="DELETE FROM empleados WHERE id='$id'";

$conn->query($sql);

}

header("Content-Type: application/json");
echo json_encode(["status"=>"ok"]);

}

$conn->close();

?>