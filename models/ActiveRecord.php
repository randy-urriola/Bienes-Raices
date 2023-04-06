<?php

namespace Model;

class ActiveRecord {

  // Base De Datos
  protected static $db;
  protected static $columnasDB = [];
  protected static $tabla = '';

  // Errores 
  protected static $errores = [];

  // Definir la conexión a la BD
  public static function setDB($database){
    self::$db = $database;
  }

  

  public function guardar() {
    if(!is_null($this->id)) {
      // Actualizando
      $this->actualizar();
    } else {
      // Creando un nuevo registro
      $this->crear();
    }
  }

  public function crear(){

    // Sanitizar los datos
    $atributos = $this->sanitizarAtributos();

    // Con Join se crea un string a partir de un arreglo, parametros: caracter que los separará, arreglo, nota: array_keys muestra las llaves o indices del arreglo; lo mismo para los valores.
    // * $string = join(', ', array_keys($atributos)); Llaves
    // * $string = join(', ', array_values($atributos)); valores
    

    // Insertar en la base de datos
    // Usando variables
    // * $columnas = join(', ',array_keys($atributos));
    // * $fila = join("', '",array_values($atributos));
    // * $query = "INSERT INTO propiedades($columnas) VALUES ('$fila')";

    // No usando variables
    $query = "INSERT INTO " . static::$tabla . " ( ";
    $query .= join(', ', array_keys($atributos));
    $query .= " ) VALUES (' ";
    $query .= join("', '", array_values($atributos));
    $query .= " ') ";

    $resultado = self::$db->query($query);

    // Mensaje de exito
    if($resultado){
      header('Location: /admin?resultado=1');// Redireccionar al usuario para confirmar la inserción.
    }

  }

  public function actualizar() {
    // Sanitizar los datos
    $atributos = $this->sanitizarAtributos();

    $valores = [];
    foreach($atributos as $key => $value) {
      $valores[] = "{$key}='{$value}'";
    }


    // Insertar en la base de datos
    $query = "UPDATE " . static::$tabla . " SET ";
    $query .= join(', ', $valores);
    $query .= " WHERE id = '" . self::$db->escape_string($this->id) . "' ";
    $query .= " LIMIT 1 ";

    $resultado = self::$db->query($query);

    if($resultado){
      // Redireccionar al usuario para confirmar la inserción.
      header('Location: /admin?resultado=2');
    }

  }

  // Eliminar un registro
  public function eliminar() {
		$query = "DELETE FROM " . static::$tabla . " WHERE id = " . self::$db->escape_string($this->id) . " LIMIT 1";

    $resultado = self::$db->query($query);

    if ($resultado) {
      $this->borrarImagen();
			header('Location: /admin?resultado=3');
		}
    
  }

  // Identificar y unir los atributos de la BD
  public function atributos() {
    $atributos = [];
    foreach(static::$columnasDB as $columna){
      if ($columna === 'id')
        continue;
      $atributos[$columna] = $this->$columna;
    }

    return $atributos;
  }

  public function sanitizarAtributos() {
    $atributos = $this->atributos();
    $sanitizado = [];

    foreach($atributos as $key => $value){
      $sanitizado[$key] = self::$db->escape_string($value);
    }

    return $sanitizado;
  }

  // Subida de archivos
  public function setImage($imagen) {

    // Elimina la imagen previa
    if(!is_null($this->id)){
      $this->borrarImagen();
    }

    // Asignar al atributo de la imagen el nombre de la imagen
    if($imagen) {
      $this->imagen = $imagen;
    }
  }

  // Eliminar el archivo imagen al eliminar una propiedad
  public function borrarImagen() {
    // Comprobar si existe el archivo
    $existeArchivo = file_exists(CARPETA_IMAGENES . $this->imagen);
    if($existeArchivo) {
      unlink(CARPETA_IMAGENES . $this->imagen);
    }
  }

  // Validación
  public static function getErrores(){
    return static::$errores;
  }

  public function validar() {

    static::$errores; /* Limpia el arreglo cada vez que se vaya a validar */

    return static::$errores;
  }

  // Lista todas los registros 
  public static function all() {
    $query = "SELECT * FROM " . static::$tabla;

    $resultado = self::consultarSQL($query);

    return $resultado;
  }

  // Obtine determinado numero de registros
  public static function get($cantidad) {
    $query = "SELECT * FROM " . static::$tabla . " LIMIT " . $cantidad;

    $resultado = self::consultarSQL($query);

    return $resultado;
  }

  // Busca un registro por su id
  public static function find($id) {
    $query = "SELECT * FROM " . static::$tabla . " WHERE id = {$id}";

    $resultado = self::consultarSQL($query);

    return array_shift($resultado); // array_shift retorna la primera poisicion de un arreglo, funcion de php.
  }

  public static function consultarSQL($query) {
    // Consultar la BD
    $resultado = self::$db->query($query);

    // Iterar los resultados
    $array = [];
    while($registro = $resultado->fetch_assoc()) {
      $array[] = static::crearObjeto($registro);
    }

    // Liberar la memoria
    $resultado->free();

    // Rotirnar los resultados 
    return $array;

  }

  protected static function crearObjeto($registro) {
    $objeto = new static; // Crea un objeto de la misma clase

    foreach($registro as $key => $value) {
      if(property_exists($objeto, $key)){ // Verifica que exista un objeto, parametros: el objeto a evaluar, y la llave.
        $objeto->$key = $value;
      }
    }

    return $objeto;
  }

  // Sincronizar el objeto en memoria con los cambios realizados por el usuario
  public function sincronizar( $args = [] ) {
    foreach($args as $key => $value) {
      if(property_exists($this, $key) && !is_null($value) ) {
        $this->$key = $value;
      }
    }
  }

}

?>