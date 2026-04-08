# Graceful Spaces - Estructura por Capas

## Estructura del Proyecto

```
GracefulSpaces/
|
|-- index.php                          <- Vista principal (HTML puro, sin logica)
|
|-- config/
|   |-- baseDatos.php                  <- CAPA CONFIG: parametros y funcion de conexion
|
|-- modelo/
|   |-- ModeloEmpleado.php             <- CAPA MODELO: CRUD de empleados (acceso a datos)
|   |-- ModeloUsuario.php              <- CAPA MODELO: autenticacion de usuarios
|
|-- controlador/
|   |-- ControladorLogin.php           <- CAPA CONTROLADOR: maneja peticion POST de login
|   |-- ControladorEmpleado.php        <- CAPA CONTROLADOR: API REST empleados (GET/POST)
|
|-- vistas/
|   |-- estilos.css                    <- CAPA VISTA: todos los estilos CSS
|   |-- aplicacion.js                  <- CAPA VISTA: logica del cliente (jQuery + Fetch)
|
|-- publico/
    |-- imagenes/                      <- Imagenes estaticas del sitio (logo, etc.)
    |-- subidas/                       <- Fotos subidas por el administrador
```

## Capas

| Capa         | Archivos                              | Responsabilidad                              |
|--------------|---------------------------------------|----------------------------------------------|
| Config       | config/baseDatos.php                  | Parametros de conexion a MySQL               |
| Modelo       | modelo/ModeloEmpleado.php             | Queries SQL de empleados                     |
|              | modelo/ModeloUsuario.php              | Verificacion de credenciales                 |
| Controlador  | controlador/ControladorEmpleado.php   | Recibe peticiones HTTP, llama al modelo      |
|              | controlador/ControladorLogin.php      | Valida login, responde success/error         |
| Vista        | vistas/estilos.css                    | Todos los estilos visuales                   |
|              | vistas/aplicacion.js                  | Navegacion, AJAX, render de tabla            |
| Principal    | index.php                             | Solo HTML: estructura de las vistas          |

## Flujo de Datos

```
Navegador
   |
   |- GET  index.php           -> Carga la pagina (HTML + CSS + JS)
   |
   |- POST ControladorLogin    -> Verifica credenciales via ModeloUsuario
   |
   |- GET  ControladorEmpleado -> Lista empleados via ModeloEmpleado
   |- POST ControladorEmpleado -> Crear / Editar / Eliminar via ModeloEmpleado
```

## Configuracion del Servidor

- PHP 7.4 o superior
- Extension mysqli habilitada
- Carpeta `publico/subidas/` con permisos de escritura (chmod 755)
