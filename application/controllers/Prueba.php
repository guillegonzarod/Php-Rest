<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Prueba extends CI_Controller
{
    public function index($nombre)
    {
        echo "Hola $nombre!!!!";
    }

    public function obtener_arreglo($index)
    {
        $arreglo = array("Manzana", "Pera", "Piña");

        echo json_encode($arreglo[$index]);
    }

    public function obtener_producto($codigo)
    {
        $this->load->database();

        $query = $this->db->query("SELECT * FROM `productos` WHERE codigo = '" . $codigo . "'");

        echo json_encode($query->result());
    }
}
