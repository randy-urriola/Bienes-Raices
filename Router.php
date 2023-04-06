<?php

namespace MVC;

class Router
{

  public $rutasGET = [];
  public $rutasPOST = [];


  public function get($url, $fn)
  {
    $this->rutasGET[$url] = $fn;
  }

  public function post($url, $fn)
  {
    $this->rutasPOST[$url] = $fn;
  }

  public function comprobarRutas()
  {
    session_start();

    $auth = $_SESSION['login'] ?? null;

    // Arreglo de rutas protegidas 
    $rutas_protegidas = ['/admin', '/propiedades/crear', '/propiedades/actualizar', '/propiedades/eliminar', '/vendedores/crear', '/vendedores/actualizar', '/vendedores/eliminar'];


    $urlActual = $_SERVER['PATH_INFO'] ?? '/';
    $metodo = $_SERVER['REQUEST_METHOD'];

    if ($metodo === 'GET') {
      $fn = $this->rutasGET[$urlActual] ?? null;
    } else {
      $fn = $this->rutasPOST[$urlActual] ?? null;
    }


    // Proteger las rutas
    if(in_array($urlActual, $rutas_protegidas) && !$auth) {
      header('Location: /');
    }

    if ($fn) {
      // La URL existe y hay una función asociada
      call_user_func($fn, $this);
    } else {
      echo "Pagina no encontrada";
    }
  }

  public function render($view, $datos = []) {

    foreach($datos as $key => $value) {
      $$key = $value; // La doble $$ significa variable de variable para cuando no se sabe como se llama la variable, genera variable con los nombres de los keys del arreglo datos en este caso.
    }

    ob_start(); // empieza el almacenamiento en memoria durante un momento de lo que viene.

    include __DIR__ . "/views/$view.php";
    $contenido = ob_get_clean(); // Limpiar el buffer y le asigna lo almacenado a la variable contenido
    include __DIR__ . "/views/layout.php";
  }

}

?>