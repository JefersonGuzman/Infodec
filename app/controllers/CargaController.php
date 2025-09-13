<?php
    require_once "app/models/Operacion.php";
    require_once "app/models/Vendedor.php";

    class CargaController {
        public function index() {
            include "app/views/carga/index.php";
        }

        public function upload() {
            if (isset($_FILES['csvfile'])) {
                $file = $_FILES['csvfile']['tmp_name'];
                $operacion = new Operacion();
                $operacion->cargarCSV($file);
                header("Location: index.php?controller=Carga&action=index&msg=ok");
            } else {
                header("Location: index.php?controller=Carga&action=index&msg=error");
            }
        }
    }
