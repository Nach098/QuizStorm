<?php

class JugarPartidaModel {
    private $database;

    public function __construct($database) {
        $this->database = $database;
    }

    public function crearPartida($id_usuario) {
        // Implementación de la creación de partida (si es necesaria)
    }

    // Obtiene una pregunta aleatoria depende la dificultad
    public function obtenerPreguntaAleatoria($idUsuario) {
        // Obtener el porcentaje de aciertos
        $query = "SELECT porcentaje_respuestas_correctas FROM Estadisticas WHERE id_usuario = ?";
        $stmt = $this->database->prepare($query);
        $stmt->bind_param("i", $idUsuario); // "i" indica que $idUsuario es un entero
        $stmt->execute();
        $result = $stmt->get_result();
        $porcentajeAciertos = $result->fetch_column();

        $dificultad = ($porcentajeAciertos >= 70) ? 'facil' : (($porcentajeAciertos < 30) ? 'dificil' : 'media');

        // Elegir una pregunta aleatoria de la dificultad y que el usuario no haya visto
        $query = "
            SELECT * FROM Pregunta 
            WHERE dificultad = ? 
            AND id_pregunta NOT IN (
                SELECT id_pregunta FROM Partida_Pregunta 
                JOIN Partida ON Partida.id_partida = Partida_Pregunta.id_partida
                WHERE Partida.id_usuario = ?
            ) 
            ORDER BY RAND() LIMIT 1";
        $stmt = $this->database->prepare($query);
        $stmt->bind_param("si", $dificultad, $idUsuario); // "s" para string y "i" para entero
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc(); // Devuelve la pregunta seleccionada
    }

    // Registra la respuesta del usuario y verifica si es correcta
    public function registrarRespuesta($idPartida, $idPregunta, $respuestaUsuario) {
        // Consultar la respuesta correcta
        $query = "SELECT respuesta_correcta FROM Pregunta WHERE id_pregunta = ?";
        $stmt = $this->database->prepare($query);
        $stmt->bind_param("i", $idPregunta); // "i" indica entero
        $stmt->execute();
        $result = $stmt->get_result();
        $respuestaCorrecta = $result->fetch_column();

        $esCorrecto = ($respuestaUsuario === $respuestaCorrecta);

        // Guarda la respuesta en Partida_Pregunta
        $query = "INSERT INTO Partida_Pregunta (id_partida, id_pregunta, respuesta_usuario, correcto) 
                  VALUES (?, ?, ?, ?)";
        $stmt = $this->database->prepare($query);
        $stmt->bind_param("iisi", $idPartida, $idPregunta, $respuestaUsuario, $esCorrecto); // "iisi" para entero, entero, string, entero
        $stmt->execute();

        return $esCorrecto; // Devuelve si la respuesta fue correcta o no
    }

    // Registrar la partida al finalizar y actualiza el puntaje
    public function finalizarPartida($idPartida, $puntosObtenidos) {
        $query = "UPDATE Partida SET puntos_obtenidos = ?, finalizada = 1 WHERE id_partida = ?";
        $stmt = $this->database->prepare($query);
        $stmt->bind_param("ii", $puntosObtenidos, $idPartida); // "ii" para enteros
        $stmt->execute();
    }
}
