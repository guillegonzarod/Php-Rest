<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Añadimos la referencia para utilizar la clase 'REST_Controller'
require_once(APPPATH . '/libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

// Heredamos de la clase 'REST_Controller':

class Pedidos extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        header("Access-Control-Allow-Methods: GET");
        header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
        header("Access-Control-Allow-Origin: *");
    }

    public function realizar_orden_post($token = "0", $id_usuario = "0")
    {

        $data = $this->post();

        // Comprobamos que exista algún 'Token' de autorización o algún 'id' de usuario en la Petición:
        if ($token == "0" || $id_usuario == "0") {
            $respuesta = array(
                'error' => true,
                'mensaje' => "Token invalido y/o usuario invalido."
            );
            $this->response($respuesta, REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Comprobamos si el Cliente manda algún producto (item) en el Pedido (cadena con los items separados por ','):
        if (!isset($data["items"]) || strlen($data['items']) == 0) {
            $respuesta = array(
                'error' => true,
                'mensaje' => "Faltan los items en el post"
            );
            $this->response($respuesta, REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Comprobamos que el 'id' y el 'token' mandado por el Cliente coinciden con su correspondiente registro en la Base de Datos: 
        $condiciones = array('id' => $id_usuario, 'token' => $token);
        $this->db->where($condiciones);
        $query = $this->db->get('login');

        $existe = $query->row();

        // Si el 'id' o el 'token' del Cliente no son correctos:
        if (!$existe) {
            $respuesta = array(
                'error' => true,
                'mensaje' => "Usuario y Token incorrectos"
            );
            $this->response($respuesta);
            return;
        }

        // Si el 'id' y el 'token' del Cliente son correctos:
        $this->db->reset_query();
        // Insertamos el registro del Pedido en la tabla 'ordenes':
        $insertar = array('usuario_id' => $id_usuario);
        $this->db->insert('ordenes', $insertar);

        // Obtenemos el 'id' (tiene que ser un campo tipo 'Id' autoincrementado) del último registro insertado en la tabla 'ordenes':
        $orden_id = $this->db->insert_id();

        // Insertamos los registros del Detalle del Pedido en la tabla 'ordenes_detalle':
        $this->db->reset_query();
        // Descomponemos la cadena en un array:
        $items = explode(',', $data['items']);

        // Recorremos el array de Productos Pedidos y los vamos insertando en la tabla 'ordenes_detalle':
        foreach ($items as &$producto_id) {
            $data_insertar = array('producto_id' => $producto_id, 'orden_id' => $orden_id);
            $this->db->insert('ordenes_detalle', $data_insertar);
        }

        $respuesta = array(
            'error' => false,
            'orden_id' => $orden_id
        );

        $this->response($respuesta);
    }
}
