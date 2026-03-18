<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Graceful Spaces</title>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
body {
margin: 0;
font-family: 'Georgia', serif;
background: #f7f7f7;
min-height: 100vh;
}

/* Panel de Opciones - Estilos Rediseñados */
#panelView {
background: #ebe7df !important;
min-height: 100vh;
width: 100%;
position: relative;
display: block !important;
visibility: visible !important;
}

#panelView.hidden {
display: none !important;
visibility: hidden !important;
}

#panelView header {
background: #3d4626 !important;
color: white !important;
padding: 18px 35px;
display: flex !important;
align-items: center;
gap: 18px;
font-size: 14px;
letter-spacing: 6px;
font-variant: small-caps;
font-weight: 400;
height: 70px;
box-sizing: border-box;
visibility: visible !important;
}

#panelView header img {
height: 45px;
width: 45px;
object-fit: contain;
background: white;
padding: 8px;
border-radius: 3px;
}

#panelView .sidebar {
width: 50px;
background: #3d4626 !important;
position: fixed;
top: 70px;
left: 0;
height: calc(100vh - 70px);
display: flex !important;
justify-content: center;
align-items: flex-start;
padding-top: 25px;
color: white !important;
font-size: 28px;
cursor: pointer;
z-index: 100;
visibility: visible !important;
}

#panelView .sidebar:hover {
background: #2d3318;
}

#panelView .container {
margin-left: 70px;
padding: 50px 40px 120px 40px;
text-align: center;
display: block !important;
visibility: visible !important;
}

#panelView h1 {
letter-spacing: 18px;
color: #3d4626;
font-size: 28px;
font-weight: 300;
margin: 30px 0 50px 0;
text-align: center;
font-family: 'Georgia', serif;
}

#panelView .options {
display: grid;
grid-template-columns: repeat(3, 1fr);
gap: 30px;
max-width: 1200px;
margin: 0 auto 40px auto;
padding: 0 20px;
}

#panelView .card {
background: #d9cdb8;
border: 1.5px solid #a9a48a;
border-radius: 18px;
padding: 80px 40px 40px 40px;
width: auto;
height: 220px;
display: flex;
flex-direction: column;
justify-content: flex-end;
align-items: center;
position: relative;
box-sizing: border-box;
}

/* Iconos en las tarjetas */
#panelView .card:nth-child(1)::before {
content: "⊕";
position: absolute;
top: 60px;
left: 50%;
transform: translateX(-50%);
font-size: 70px;
color: #c4baa4;
font-weight: 200;
line-height: 1;
}

#panelView .card:nth-child(2)::before {
content: "✎";
position: absolute;
top: 65px;
left: 50%;
transform: translateX(-50%);
font-size: 65px;
color: #c4baa4;
font-weight: 300;
line-height: 1;
}

#panelView .card:nth-child(3)::before {
content: "🗑";
position: absolute;
top: 60px;
left: 50%;
transform: translateX(-50%);
font-size: 60px;
opacity: 0.5;
line-height: 1;
}

#panelView .card .btn {
background: #3d4626;
color: white;
border: none;
padding: 14px 40px;
border-radius: 25px;
margin: 0;
cursor: pointer;
font-size: 13px;
letter-spacing: 3px;
text-transform: uppercase;
font-weight: 400;
width: auto;
}

#panelView .card .btn:hover {
background: #2d3318;
}

#panelView .btn.top-btn {
position: absolute;
left: 70px;
top: 85px;
background: #3d4626;
color: white;
border: none;
padding: 12px 28px;
border-radius: 25px;
cursor: pointer;
font-size: 12px;
letter-spacing: 2px;
text-transform: uppercase;
font-weight: 400;
z-index: 50;
}

#panelView .btn.top-btn:hover {
background: #2d3318;
}

#panelView .container > .btn {
background: #3d4626;
color: white;
border: none;
padding: 14px 50px;
border-radius: 25px;
margin: 20px auto;
cursor: pointer;
font-size: 13px;
letter-spacing: 3px;
text-transform: uppercase;
font-weight: 400;
display: inline-block;
}

#panelView .container > .btn:hover {
background: #2d3318;
}

#panelView footer {
background: #3d4626;
color: white;
text-align: center;
padding: 20px;
position: fixed;
bottom: 0;
width: 100%;
letter-spacing: 5px;
font-variant: small-caps;
font-size: 12px;
font-weight: 300;
z-index: 10;
}

/* Estilos para formularios y tablas dentro del panel */
#panelView .image-btn {
background: #3d4626;
color: white;
border: none;
padding: 15px 25px;
border-radius: 25px;
margin: 15px auto;
cursor: pointer;
font-size: 13px;
letter-spacing: 2px;
text-align: center;
display: inline-block;
}

#panelView .image-btn:hover {
background: #2d3318;
}

#panelView .preview-img {
max-width: 150px;
max-height: 150px;
margin: 10px auto;
display: block;
border-radius: 10px;
border: 2px solid #a9a48a;
}

#panelView .table-box {
background: #d9cdb8;
border: 1.5px solid #a9a48a;
border-radius: 18px;
padding: 30px;
display: inline-block;
margin: 30px auto;
}

#panelView .tabla-empleados {
width: 750px;
border-collapse: collapse;
letter-spacing: 1px;
text-transform: uppercase;
margin: auto;
font-size: 13px;
}

#panelView .tabla-empleados th,
#panelView .tabla-empleados td {
border: 1px solid #8a8578;
padding: 16px;
height: 55px;
text-align: center;
}

#panelView .tabla-empleados th {
background: #3d4626;
color: white;
font-weight: 400;
letter-spacing: 2px;
}

#panelView .tabla-empleados img {
width: 60px;
height: 60px;
object-fit: cover;
border-radius: 8px;
}

#panelView .tabla-empleados tbody tr {
cursor: pointer;
background: white;
}

#panelView .tabla-empleados tbody tr:hover {
background: #ebe7df;
}

/* Login styles */
.login-box, .card {
background: #e8dcc8;
border: 3px solid #9a9f6b;
border-radius: 20px;
padding: 40px;
width: 320px;
margin: auto;
}

.btn {
background: #5f6536;
color: white;
border: none;
padding: 12px 30px;
border-radius: 25px;
margin: 10px;
cursor: pointer;
font-size: 15px;
letter-spacing: 2px;
text-transform: uppercase;
}

.btn:hover {
background: #444a1f;
}

.options {
display: flex;
justify-content: center;
gap: 40px;
margin-top: 40px;
}

.card {
width: 220px;
height: 200px;
display: flex;
flex-direction: column;
justify-content: flex-end;
align-items: center;
}

.image-btn {
background: #9a9f6b;
color: white;
border: none;
padding: 15px 20px;
border-radius: 15px;
margin: 15px auto;
cursor: pointer;
font-size: 16px;
letter-spacing: 1px;
text-align: center;
}

.image-btn:hover {
background: #444a1f;
}

.preview-img {
max-width: 150px;
max-height: 150px;
margin: 10px auto;
display: block;
border-radius: 10px;
border: 2px solid #9a9f6b;
}

.tabla-empleados img {
width: 60px;
height: 60px;
object-fit: cover;
border-radius: 8px;
}

.top-btn {
position: absolute;
left: 120px;
top: 95px;
}

.table-box {
background: #e8dcc8;
border: 3px solid #9a9f6b;
border-radius: 20px;
padding: 25px;
display: inline-block;
margin: auto;
margin-top: 30px;
}

.tabla-empleados {
width: 750px;
border-collapse: collapse;
letter-spacing: 1px;
text-transform: uppercase;
margin: auto;
}

.tabla-empleados th,
.tabla-empleados td {
border: 2px solid black;
padding: 16px;
height: 55px;
text-align: center;
}

.tabla-empleados th {
background: #9a9f6b;
color: white;
}

footer {
background: #3d4626;
color: transparent;
text-align: center;
padding: 20px;
position: fixed;
bottom: 0;
width: 100%;
letter-spacing: 4px;
text-transform: uppercase;
font-size: 13px;
font-weight: 300;
}

footer::before {
content: "GRACEFUL SPACES · 2026";
color: white;
}

.hidden {
display: none !important;
}

#loginView {
min-height: 100vh;
background: #e8dfd0;
display: flex;
align-items: center;
justify-content: center;
padding: 40px 60px;
padding-bottom: 80px;
position: relative;
}

.gs-login-layout {
display: flex;
align-items: center;
justify-content: space-between;
width: 100%;
max-width: 1200px;
gap: 80px;
min-height: calc(100vh - 140px);
}

.login-logo {
position: absolute;
top: 40px;
left: 40px;
width: 90px;
height: 90px;
object-fit: contain;
background: white;
padding: 12px;
border-radius: 4px;
}

.login-title {
text-align: left;
margin: 0;
color: transparent;
font-size: 48px;
font-weight: 300;
letter-spacing: 0.5px;
position: relative;
font-family: 'Georgia', 'Times New Roman', serif;
flex: 1;
}

.login-title::after {
content: "Iniciar sesión";
position: static;
display: block;
color: #5a5a4a;
font-size: 64px;
font-weight: 300;
letter-spacing: 0.5px;
font-family: 'Georgia', 'Times New Roman', serif;
text-transform: none;
margin-top: 10px;
}

.login-title::before {
content: "BIENVENIDO DE VUELTA";
display: block;
font-size: 9px;
letter-spacing: 4px;
color: #a19a8a;
margin-bottom: 12px;
font-weight: 300;
text-transform: uppercase;
font-family: 'Georgia', serif;
white-space: nowrap;
}

.login-box {
max-width: 520px;
width: 100%;
margin: 0;
background: #d9cdb8;
padding: 50px 45px;
border-radius: 20px;
border: 2px solid #8b9164;
box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}

.gs-input-label {
display: block;
font-size: 11px;
letter-spacing: 2px;
color: #8a8578;
margin-bottom: 10px;
font-weight: 500;
text-transform: uppercase;
font-family: 'Georgia', serif;
}

.login-box input {
width: 100%;
padding: 16px 20px 16px 50px;
border-radius: 10px;
border: 1px solid #d4cabb;
margin: 0 0 25px 0;
display: block;
font-size: 15px;
background: white;
box-sizing: border-box;
transition: border-color 0.3s ease;
position: relative;
color: #5a5a4a;
outline: none;
box-shadow: none;
}

.login-box fieldset {
border: none;
outline: none;
box-shadow: none;
padding: 0;
margin: 0;
}

.login-box form {
border: none;
outline: none;
box-shadow: none;
}

.login-box div {
border: none;
outline: none;
box-shadow: none;
}

.login-box input:focus {
outline: none;
border-color: #8b9164;
}

.login-box input::placeholder {
color: #b5aa9a;
}

#usuario {
background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="%23a19a8a" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>');
background-repeat: no-repeat;
background-position: 18px center;
background-size: 18px;
}

#password {
background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="%23a19a8a" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>');
background-repeat: no-repeat;
background-position: 18px center;
background-size: 18px;
}

.gs-label-usuario::before,
.gs-label-password::before {
content: attr(data-label);
display: block;
font-size: 11px;
letter-spacing: 2px;
color: #999;
margin-bottom: 8px;
font-weight: 500;
text-transform: uppercase;
}

#usuario::before {
content: "USUARIO";
}

.login-box br {
display: none;
}

.gs-forgot-link {
text-align: right;
margin-bottom: 25px;
}

.gs-forgot-link a {
color: #8a8578;
font-size: 13px;
text-decoration: none;
transition: color 0.3s ease;
}

.gs-forgot-link a:hover {
color: #5a5a4a;
}

.login-box .btn {
width: 100%;
padding: 16px;
border-radius: 10px;
border: none;
font-size: 14px;
font-weight: 600;
letter-spacing: 3px;
text-transform: uppercase;
cursor: pointer;
transition: all 0.3s ease;
margin-bottom: 15px;
}

.login-box .btn:first-of-type {
background: #3d4626;
color: white;
}

.login-box .btn:first-of-type:hover {
background: #2d3419;
transform: translateY(-1px);
box-shadow: 0 4px 12px rgba(61, 70, 38, 0.3);
}

.login-box .btn:last-of-type {
background: #e8dfd0;
color: #5a5a4a;
border: 1px solid #c4b8a5;
}

.login-box .btn:last-of-type:hover {
background: #f0e8d8;
border-color: #8b9164;
}

#mensajeLogin {
color: #c62828;
text-align: center;
margin-top: 20px;
font-size: 14px;
padding: 12px;
background: #ffebee;
border-radius: 6px;
display: none;
}

#mensajeLogin:not(:empty) {
display: block;
}

/* Responsive */
@media (max-width: 1024px) {
.gs-login-layout {
gap: 50px;
}

.login-title::after {
font-size: 52px;
}

.login-box {
max-width: 450px;
padding: 40px 35px;
}
}

@media (max-width: 768px) {
#loginView {
padding: 30px 20px 80px;
}

.gs-login-layout {
flex-direction: column;
gap: 40px;
align-items: stretch;
min-height: auto;
}
.login-logo {
width: 70px;
height: 70px;
top: 25px;
left: 25px;
padding: 10px;
}

.login-title {
text-align: center;
}

.login-title::after {
font-size: 42px;
margin-top: 8px;
letter-spacing: 0.3px;
}

.login-title::before {
font-size: 8px;
letter-spacing: 3px;
margin-bottom: 10px;
white-space: nowrap;
}

.login-box {
max-width: 100%;
padding: 35px 30px;
}

.login-box input {
padding: 14px 18px 14px 45px;
font-size: 14px;
}

#usuario,
#password {
background-position: 15px center;
background-size: 16px;
}

.login-box .btn {
padding: 14px;
font-size: 13px;
}

footer {
font-size: 11px;
padding: 15px;
letter-spacing: 3px;
}
}

@media (max-width: 480px) {
#loginView {
padding: 25px 15px 75px;
}

.login-title::after {
font-size: 36px;
letter-spacing: 0.3px;
}

.login-title::before {
font-size: 7px;
letter-spacing: 2px;
white-space: nowrap;
}

.login-box {
padding: 30px 25px;
border-radius: 15px;
}

.login-box input {
padding: 12px 15px 12px 42px;
font-size: 13px;
}

.login-box .btn {
padding: 13px;
font-size: 13px;
letter-spacing: 1.5px;
}
}

#mensajeLogin{
color:red;
text-align:center;
}

/* Estilos para inputs del formulario CRUD en el panel */
#panelView #formularioCRUD {
max-width: 500px;
margin: 30px auto;
}

#panelView #formularioCRUD input[type="text"] {
width: 90%;
max-width: 450px;
padding: 14px 20px;
border-radius: 10px;
border: 1.5px solid #a9a48a;
margin: 0 auto 20px auto;
display: block;
font-size: 14px;
font-family: 'Georgia', serif;
background: white;
color: #3d4626;
}

#panelView #formularioCRUD input[type="text"]:focus {
outline: none;
border-color: #3d4626;
}

#panelView #formularioCRUD input[type="text"]::placeholder {
color: #a9a48a;
}

#panelView #formularioCRUD input:disabled {
background-color: #ebe7df;
cursor: not-allowed;
border-color: #c4baa4;
}

#formularioCRUD input {
width: 80%;
padding: 10px;
border-radius: 10px;
border: 1px solid #999;
margin-bottom: 10px;
}

#formularioCRUD input:disabled {
background-color: #e0e0e0;
cursor: not-allowed;
}

.tabla-empleados tbody tr {
cursor: pointer;
}

.tabla-empleados tbody tr:hover {
background-color: #d4cfbc;
}

</style>
</head>

<body>

<div id="loginView">

<img src="GracefulSpacesLogo.jpg" class="login-logo">

<div class="gs-login-layout" style="display: flex; align-items: center; justify-content: space-between; width: 100%; max-width: 1200px; gap: 80px;">

<h1 class="login-title"></h1>

<div class="login-box">

<label class="gs-input-label" style="display:block;font-size:11px;letter-spacing:2px;color:#8a8578;margin-bottom:10px;font-weight:500;text-transform:uppercase;font-family:'Georgia',serif;">USUARIO</label>
<input type="text" id="usuario" placeholder="Usuario">

<br>

<label class="gs-input-label" style="display:block;font-size:11px;letter-spacing:2px;color:#8a8578;margin-bottom:10px;font-weight:500;text-transform:uppercase;font-family:'Georgia',serif;">CONTRASEÑA</label>
<input type="password" id="password" placeholder="Contraseña">

<br>

<div class="gs-forgot-link" style="text-align:right;margin-bottom:25px;"><a href="#" onclick="irALaU(); return false;" style="color:#8a8578;font-size:13px;text-decoration:none;">¿Olvidaste tu contraseña?</a></div>

<button class="btn" id="loginBtn">Ingresar</button>

<button class="btn" onclick="irALaU()">Ir a la U</button>

<div id="mensajeLogin"></div>

</div>

</div>

</div>

<div id="panelView" class="hidden">

<header>
<img src="GracefulSpaces1.jpg">
GRACEFUL SPACES
</header>

<div class="sidebar" onclick="volverLogin()">⤺</div>

<button class="btn top-btn" onclick="mostrarTabla()">FORMULARIOS | TABLA</button>

<div class="container">

<h1>OPCIONES</h1>

<div id="cardsView" class="options">

<div class="card">
<button class="btn" onclick="accion('crear')">CREAR</button>
</div>

<div class="card">
<button class="btn" onclick="accion('editar')">EDITAR</button>
</div>

<div class="card">
<button class="btn" onclick="accion('eliminar')">ELIMINAR</button>
</div>

</div>

<div id="formularioCRUD" class="hidden">

<div class="image-btn" id="btnSeleccionarImagen" onclick="document.getElementById('inputFoto').click()" style="display:none;">
📷 Seleccionar Imagen
</div>

<input type="hidden" id="id">

<input type="text" id="nombre" placeholder="Nombre del empleado">

<input type="text" id="funcion" placeholder="Función">

<input type="file" id="inputFoto" accept="image/*" style="display:none;" onchange="previsualizarImagen()">

<img id="previewImg" class="preview-img hidden">

</div>

<button class="btn" onclick="enviarAccion()">GUARDAR</button>

<div id="tableView" class="hidden">

<div class="table-box">

<table class="tabla-empleados">

<thead>

<tr>
<th>ID</th>
<th>FOTO</th>
<th>EMPLEADO</th>
<th>FUNCIÓN</th>
</tr>

</thead>

<tbody id="tablaBody"></tbody>

</table>

</div>

</div>

</div>

</div>

<script>

let accionActual = "";

function previsualizarImagen(){
const input=document.getElementById('inputFoto');
const preview=document.getElementById('previewImg');
const file=input.files[0];

if(file){
const reader=new FileReader();
reader.onload=function(e){
preview.src=e.target.result;
preview.classList.remove('hidden');
}
reader.readAsDataURL(file);
}
}

function irALaU(){
window.location.href="https://campus.ulatina.ac.cr";
}

function volverLogin(){
$("#panelView").addClass("hidden");
$("#loginView").removeClass("hidden");
}

function mostrarTabla(){
// Limpiar y volver a la vista inicial de tarjetas
$("#formularioCRUD").addClass("hidden");
$("#tableView").addClass("hidden");
$("#cardsView").removeClass("hidden");
$("#btnSeleccionarImagen").css("display","none");
$("#id").val("");
$("#nombre").val("").prop("disabled",false);
$("#funcion").val("").prop("disabled",false);
$("#inputFoto").val("");
$("#previewImg").addClass("hidden").attr("src","");
accionActual = "";
}

function accion(tipo){
accionActual=tipo;
$("#formularioCRUD").removeClass("hidden");

// Ocultar las tarjetas para todas las acciones
$("#cardsView").addClass("hidden");

// Mostrar la tabla si no está visible para editar y eliminar
if((tipo=="editar" || tipo=="eliminar") && $("#tableView").hasClass("hidden")){
$("#tableView").removeClass("hidden");
}

// Mostrar botón de imagen solo en CREAR y EDITAR
if(tipo=="crear"){
$("#btnSeleccionarImagen").css("display","block");
$("#nombre").prop("disabled",false);
$("#funcion").prop("disabled",false);
}else if(tipo=="editar"){
$("#btnSeleccionarImagen").css("display","block");
$("#nombre").prop("disabled",false);
$("#funcion").prop("disabled",false);
}else if(tipo=="eliminar"){
$("#btnSeleccionarImagen").css("display","none");
$("#nombre").prop("disabled",true);
$("#funcion").prop("disabled",true);
}
}

function enviarAccion(){

const id=$("#id").val();
const nombre=$("#nombre").val();
const funcion=$("#funcion").val();
const foto=document.getElementById('inputFoto').files[0];

// Si hay foto, usar FormData
if(foto){
const formData=new FormData();
formData.append('accion',accionActual);
formData.append('id',id);
formData.append('nombre',nombre);
formData.append('funcion',funcion);
formData.append('foto',foto);

fetch("funcion.php",{
method:"POST",
body:formData
})
.then(res=>res.json())
.then(data=>{
cargarEmpleados();
limpiarFormulario();
alert("Operación realizada");
});

}else{
// Sin foto, usar JSON
fetch("funcion.php",{
method:"POST",
headers:{"Content-Type":"application/json"},
body:JSON.stringify({
accion:accionActual,
id:id,
nombre:nombre,
funcion:funcion
})
})
.then(res=>res.json())
.then(data=>{
cargarEmpleados();
limpiarFormulario();
alert("Operación realizada");
});
}

}

function cargarEmpleados(){

fetch("funcion.php")

.then(res=>res.json())

.then(data=>{

const tabla=document.getElementById("tablaBody");

tabla.innerHTML="";

data.forEach(emp=>{

const row=document.createElement("tr");

const fotoHTML=emp.foto?`<img src="${emp.foto}" alt="Foto">`:'Sin foto';

row.innerHTML=`
<td>${emp.id}</td>
<td>${fotoHTML}</td>
<td>${emp.nombre_empleado}</td>
<td>${emp.funcion}</td>
`;

row.onclick=()=>{

$("#id").val(emp.id);
$("#nombre").val(emp.nombre_empleado);
$("#funcion").val(emp.funcion);

};

tabla.appendChild(row);

});

});

}

function limpiarFormulario(){

$("#id").val("");
$("#nombre").val("").prop("disabled",false);
$("#funcion").val("").prop("disabled",false);
$("#inputFoto").val("");
$("#previewImg").addClass("hidden").attr("src","");
$("#formularioCRUD").addClass("hidden");
$("#cardsView").removeClass("hidden");

}

/* LOGIN AJAX */

$(document).ready(function(){

$("#loginBtn").click(function(){

var usuario=$("#usuario").val();
var password=$("#password").val();

if(usuario!="" && password!=""){

$.ajax({

url:"login.php",

method:"POST",

data:{
usuario:usuario,
password:password
},

success:function(data){

data = data.trim();

if(data=="success"){

$("#loginView").addClass("hidden");
$("#panelView").removeClass("hidden");

cargarEmpleados();

}else{

$("#mensajeLogin").html("Usuario o contraseña incorrectos");

}

}

});

}else{

alert("Complete todos los campos");

}

});

});

</script>

<footer>GRACEFUL SPACES 2026</footer>

</body>
</html>