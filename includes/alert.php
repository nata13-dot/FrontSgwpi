<?php
/**
 * Mostrar alerta
 * 
 * @param string $type - success, danger, warning, info
 * @param string $message - Mensaje a mostrar
 * @param bool $dismissible - Mostrar botón de cerrar
 */
function showAlert($type = 'info', $message = '', $dismissible = true) {
    $dismissClass = $dismissible ? ' alert-dismissible fade show' : '';
    echo "
    <div class='alert alert-$type$dismissClass' role='alert'>
        <i class='bi bi-info-circle'></i> $message
    ";
    
    if ($dismissible) {
        echo "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>";
    }
    
    echo "</div>";
}
?>