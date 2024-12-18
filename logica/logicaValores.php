<?php
// logicaValores.php

session_start();

// Incluir el archivo de conexión a MongoDB
require '../datos/conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Verificar si se ha recibido una solicitud POST con los datos necesarios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valores']) && isset($_POST['plan_id']) && isset($_POST['action'])) {

    // Verificar el token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = "Token CSRF inválido.";
        header("Location: ../presentacion/valores.php");
        exit();
    }

    // Capturar y limpiar los datos del formulario
    $valores = trim($_POST['valores']);
    $plan_id = $_POST['plan_id'];  // ID del plan
    $accion = $_POST['action'];

    // Validar que el campo 'valores' no esté vacío
    if ($valores !== "") {
        try {
            // Seleccionar la colección de MongoDB
            $collection = $db->planes;

            // Crear el filtro para encontrar el documento específico por su ID
            $filtro = ['_id' => new MongoDB\BSON\ObjectId($plan_id)];

            // Definir los datos a actualizar
            $datos_actualizados = [
                '$set' => [
                    'valores' => $valores,
                    'fecha_modificacion' => new MongoDB\BSON\UTCDateTime()
                ]
            ];

            // Realizar la actualización
            $resultado = $collection->updateOne($filtro, $datos_actualizados);

            // Verificar si la operación fue exitosa
            if ($resultado->getModifiedCount() > 0) {
                $_SESSION['success_message'] = "Valores guardados exitosamente.";
            } else {
                $_SESSION['error_message'] = "No se realizaron cambios en los valores o el documento no existe.";
            }

        } catch (Exception $e) {
            // Manejar errores de conexión o actualización
            error_log("Error al guardar/actualizar valores: " . $e->getMessage());
            $_SESSION['error_message'] = "Ocurrió un error al guardar los valores. Por favor, intenta nuevamente.";
        }
    } else {
        // Manejar el caso donde el campo 'valores' está vacío
        $_SESSION['error_message'] = "Por favor, ingresa los valores antes de continuar.";
    }

    // Redirigir según la acción seleccionada
    switch ($accion) {
        case 'index':
            header("Location: ../presentacion/index.php");
            exit();
        case 'vision':
            header("Location: ../presentacion/vision.php");
            exit();
        case 'resumen':
            header("Location: ../presentacion/resumen.php");
            exit();
        default:
            // Redirigir a una página por defecto si la acción no coincide
            header("Location: ../presentacion/index.php");
            exit();
    }

} else {
    // Si no se recibe una solicitud POST válida, redirigir al formulario
    header("Location: ../presentacion/valores.php");
    exit();
}
