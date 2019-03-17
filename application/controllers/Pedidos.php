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

    public function obtener_pedidos_get($token = "0", $id_usuario = "0")
    {

        // Comprobamos que exista algún 'Token' de autorización o algún 'id' de usuario en la Petición:
        if ($token == "0" || $id_usuario == "0") {
            $respuesta = array(
                'error' => true,
                'mensaje' => "Token invalido y/o usuario invalido."
            );
            $this->response($respuesta, REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Comprobamos que el 'id' y el 'token' mandado por el Cliente coinciden con su correspondiente registro en la Base de Datos: 
        $condiciones = array('id' => $id_usuario, 'token' => $token);
        $this->db->where($condiciones);
        $query = $this->db->get('login');

        $existe = $query->row();

        if (!$existe) {
            $respuesta = array(
                'error' => true,
                'mensaje' => "Usuario y Token incorrectos"
            );
            $this->response($respuesta);
            return;
        }

        // Obtenemos todos los Pedidos del Usuario ('$id_usuario'):
        $query = $this->db->query('SELECT * FROM `ordenes` where usuario_id = ' . $id_usuario);

        $ordenes = array();

        foreach ($query->result() as $row) {

            // Obtenemos el Detalle de cada Pedido:
            $query_detalle = $this->db->query('SELECT a.orden_id, b.* FROM `ordenes_detalle` a inner join productos b on a.producto_id = b.codigo where orden_id = ' . $row->id);

            // Creamos un array con la Cabecera ('id' y 'creado_en') y Líneas del Pedido ('detalle'):
            $orden = array(
                'id' => $row->id,
                'creado_en' => $row->creado_en,
                'detalle' => $query_detalle->result()
            );

            array_push($ordenes, $orden);
        }

        $respuesta = array(
            'error' => false,
            'ordenes' => $ordenes
        );


        $this->response($respuesta);
    }

    public function borrar_pedido_delete($token = "0", $id_usuario = "0", $orden_id = "0")
    {

        // Comprobamos que exista algún 'Token' de autorización o algún 'id' de usuario en la Petición:
        if ($token == "0" || $id_usuario == "0" || $orden_id == "0") {
            $respuesta = array(
                'error' => true,
                'mensaje' => "Token invalido y/o usuario invalido."
            );
            $this->response($respuesta, REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // Comprobamos que el 'id' y el 'token' mandado por el Cliente coinciden con su correspondiente registro en la Base de Datos: 
        $condiciones = array('id' => $id_usuario, 'token' => $token);
        $this->db->where($condiciones);
        $query = $this->db->get('login');

        $existe = $query->row();

        if (!$existe) {
            $respuesta = array(
                'error' => true,
                'mensaje' => "Usuario y Token incorrectos"
            );
            $this->response($respuesta);
            return;
        }

        // Comprobamos que el Pedido corresponde al Usuario ('$id_usuario'):
        $this->db->reset_query();
        $condiciones = array('id' => $orden_id, 'usuario_id' => $id_usuario);
        $this->db->where($condiciones);
        $query = $this->db->get('ordenes');

        $existe = $query->row();

        if (!$existe) {
            $respuesta = array(
                'error' => true,
                'mensaje' => "Esa orden no puede ser borrada"
            );
            $this->response($respuesta);
            return;
        }

        // Si el Pedido corresponde al Usuario ('$id_usuario') borramos el Pedido ('$orden_id'):
        $condiciones = array('id' => $orden_id);
        $this->db->delete('ordenes', $condiciones);
        // Y borramos el Detalle del Pedido:
        $condiciones = array('orden_id' => $orden_id);
        $this->db->delete('ordenes_detalle', $condiciones);

        $respuesta = array(
            'error' => false,
            'mensaje' => 'Orden eliminada'
        );

        $this->response($respuesta);
    }
}
