<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Añadimos la referencia para utilizar la clase 'REST_Controller'
require_once(APPPATH . '/libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

// Heredamos de la clase 'REST_Controller':

class Prueba extends REST_Controller
{
    public function index($nombre)
    {
        echo "Hola $nombre!!!!";
    }

    public function obtener_arreglo_get($index = 0)
    {
        // Comprobamos que el índice exista en el Array:
        if ($index > 2) {
            // En la respuesta añadimos el Código de Respuesta que creamos conveniente:
            $respuesta = array('error' => true, 'mensaje' => 'No existe elemento con la posicion ' . $index);
            // Le añadimos el Código de Error que creamos conveniente (podemos ver todos los Códigos de Respuesta en 'application/libraries/REST_Controller.php'):
            $this->response($respuesta, REST_Controller::HTTP_BAD_REQUEST);
        } else {
            $arreglo = array("Manzana", "Pera", "Piña");
            // Si el índice es correcto devolvemos el Código de Respuesta 'HTTP_OK = 200' con el recurso solicitado:
            $respuesta = array('error' => false, 'fruta' => $arreglo[$index]);
            $this->response($respuesta);
        }
    }

    public function obtener_producto($codigo)
    {
        $this->load->database();

        $query = $this->db->query("SELECT * FROM `productos` WHERE codigo = '" . $codigo . "'");

        echo json_encode($query->result());
    }
}
