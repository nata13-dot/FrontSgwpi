<?php
/**
 * Funciones de validación del sistema SWGPI
 * 
 * NOTA: Solo contiene validaciones de CLIENTE (sin acceso a BD)
 * Las validaciones complejas (acceso, fechas) son responsabilidad del servidor Laravel API
 * Todo dato viene del servidor, las validaciones aquí son solo para UX
 */

/**
 * Validar que una calificación esté en el rango permitido (0-100)
 * Cliente: Validación de UX
 * Servidor: API valida nuevamente
 */
function validar_calificacion($calificacion) {
    if (is_null($calificacion) || $calificacion === '') {
        return false;
    }
    $valor = (float)$calificacion;
    return $valor >= 0 && $valor <= 100;
}

/**
 * Validar MIME type de archivo
 * Cliente: Previene upload de archivos inválidos
 * Servidor: API valida nuevamente
 */
function validar_mime_type($mime_type) {
    $tiposPermitidos = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/zip'
    ];
    
    return in_array($mime_type, $tiposPermitidos);
}

/**
 * Validar tamaño de archivo (máximo 50MB)
 * Cliente: Previene upload de archivos demasiado grandes
 * Servidor: API valida nuevamente
 */
function validar_tamaño_archivo($tamaño_bytes) {
    $max_size = 50 * 1024 * 1024; // 50MB
    return $tamaño_bytes <= $max_size;
}
