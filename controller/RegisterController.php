<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/PHPMailer-master/src/PHPMailer.php';
require 'vendor/PHPMailer-master/src/SMTP.php';
require 'vendor/PHPMailer-master/src/Exception.php';

class RegisterController {

    private $registerModel;
    private $presenter;

    public function __construct($registerModel, $presenter) {
        $this->registerModel = $registerModel;
        $this->presenter = $presenter;
    }

    public function registrarse() {
        $nombre_completo = $_POST['nombre_completo'];
        $fecha_nacimiento = $_POST['fecha_nacimiento'];
        $sexo = $_POST['sexo'];
        $pais = $_POST['pais'];
        $email = $_POST['email'];
        $contraseña = $_POST['contraseña'];
        $contraseña_repetida = $_POST['repetir-contraseña'];
        $nombre_usuario = $_POST['nombre_usuario'];
        $img = "";

        // Verificar que las contraseñas coincidan
        if ($contraseña != $contraseña_repetida) {
            header("location:/");
            exit();
        }

        // Procesar la imagen de perfil si se carga
        if ($_FILES["foto_perfil"]["error"] == 0) {
            $nuevoNombre = time();
            $extension = pathinfo($_FILES["foto_perfil"]["name"], PATHINFO_EXTENSION);
            $destino = "public/uploads/" . $nuevoNombre . "." . $extension;
            move_uploaded_file($_FILES["foto_perfil"]["tmp_name"], $destino);
            $img = "$nuevoNombre.$extension";
        }

        // Generar un token único (aunque no lo usaremos en este caso)
        $token = uniqid();

        // Agregar usuario en la base de datos y activar la cuenta automáticamente
        $result = $this->registerModel->agregarUsuario($nombre_completo, $fecha_nacimiento, $sexo, $pais, $email, $contraseña, $nombre_usuario, $img, $token, true); // 'true' para activar la cuenta

        if (!$result) {
            if (!empty($img)) {
                unlink("public/uploads/" . $img);
            }
            header('Location:/registro?error=ERROR-DB');
            exit();
        }

        // Redirigir directamente al login, sin verificación de correo
        header("location:/login");
        exit();
    }

    public function list() {
        $this->presenter->show("register", []);
    }
}


