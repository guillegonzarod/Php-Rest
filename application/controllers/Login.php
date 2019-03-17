<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Añadimos la referencia para utilizar la clase 'REST_Controller'
require_once(APPPATH . '/libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

// Heredamos de la clase 'REST_Controller':

class Login extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        header("Access-Control-Allow-Methods: GET");
        header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
        header("Access-Control-Allow-Origin: *");
    }

    public function index_post()
    {
        // Datos enviados por el Cliente en la Petición POST
        $data = $this->post();

        // Comprobamos si en los datos enviados por el Cliente vienen los nombres de campo 'correo' o 'contrasena':
        if (!isset($data['correo']) or !isset($data['contrasena'])) {
            $respuesta = array(
                'error' => true,
                'mensaje' => 'La información enviada no es válida'
            );

            $this->response($respuesta, REST_Controller::HTTP_BAD_REQUEST);
            return;
        }

        // En el caso de que en los datos enviados por el Cliente vengan los nombres de campo 'correo' o 'contrasena', 
        // comprobamos sus valores con los de la Base de Datos:
        $condiciones = array('correo' => $data['correo'], 'contrasena' => $data['contrasena']);
        $query = $this->db->get_where('login', $condiciones);
        $usuario = $query->row();
        // Si no coincide el login del usuario, no habrá valor en la variable '$usuario': 
        if (!isset($usuario)) {
            $respuesta = array(
                'error' => true,
                'mensaje' => 'Usuario y/o contrasena no son válidos'
            );

            $this->response($respuesta);
            return;
        }

        // Si coincide el login del usuario, generamos el TOKEN: 
        // Establecemos (siempre distinto) un 'hash' aleatorio:
        $token = bin2hex(openssl_random_pseudo_bytes(20));
        // Establecemos (siempre el mismo) un 'hash' partiendo valor del correo del usuario:
        $token = hash('ripemd160', $data['correo']);
        // Guardamos el Token en la Base de Datos:
        // Reiniciamos el valor de la variable '$query':
        $this->db->reset_query();
        // Asignamos que queremos Actualizar el campo 'token' de la Base de Datos con el valor de la variable '$token':
        $actualizar_token = array('token' => $token);
        // Asignamos la cláusula WHERE de la consulta UPDATE:
        $this->db->where('id', $usuario->id);
        // Ejecutamos la consulta UPDATE:
        $hecho = $this->db->update('login', $actualizar_token);

        $respuesta = array(
            'error' => false,
            'token' => $token,
            'id_usuario' => $usuario->id
        );

        $this->response($respuesta);
    }
}
