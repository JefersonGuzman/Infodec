<?php
    require_once "Conexion.php";

    class Vendedor {
        public static function getOrCreate($nombre) {
            $pdo = Conexion::getConexion();
            $stmt = $pdo->prepare("SELECT id FROM vendedores WHERE nombre = ?");
            $stmt->execute([$nombre]);
            $id = $stmt->fetchColumn();

            if (!$id) {
                $stmt = $pdo->prepare("INSERT INTO vendedores (nombre) VALUES (?)");
                $stmt->execute([$nombre]);
                $id = $pdo->lastInsertId();
            }
            return $id;
        }
    }
