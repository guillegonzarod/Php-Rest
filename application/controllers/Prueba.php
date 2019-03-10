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
}

