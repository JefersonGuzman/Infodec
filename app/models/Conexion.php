<?php
    class Conexion {
        public static function getConexion() {
            $dsn = "mysql:host=localhost;dbname=ventasplus;charset=utf8";
            $user = "root";
            $pass = "";
            return new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        }
    }
