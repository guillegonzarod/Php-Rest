<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Añadimos la referencia para utilizar la clase 'REST_Controller'
require_once(APPPATH . '/libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

// Heredamos de la clase 'REST_Controller':

class Productos extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        header("Access-Control-Allow-Methods: GET");
        header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
        header("Access-Control-Allow-Origin: *");
    }

    public function todos_get($pagina = 0)
    {
        $pagina = $pagina * 10;

        $query = $this->db->query('SELECT * FROM `productos` limit '.$pagina.',10');

        $respuesta = array(
            'error' => false,
            'lineas' => $query->result_array()
        );

        $this->response($respuesta);
    }
}
