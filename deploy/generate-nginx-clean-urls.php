<?php
// Genera deploy/nginx-clean-urls.conf desde includes/clean-urls.php: php deploy/generate-nginx-clean-urls.php
$urls = require __DIR__.'/../includes/clean-urls.php';
$out = <<<'CONF'
# URLs limpias del front. GENERADO por deploy/generate-nginx-clean-urls.php: no editar a mano.
# Se incluye dentro del bloque server de swgpi.online. Para deshacerlo, quita el include y recarga nginx:
# no se mueve ni se renombra ningún archivo.

# Archivos internos que no deben responder en público. .well-known se conserva para renovar el certificado.
if ($uri ~* "/\.(?!well-known/)|^/(includes|deploy|tests|docs|vendor)(/|$)|^/(test-api|router)\.php$|^/api/session-helpers\.php$|\.(md|sql|lock|log|json|key|crt|p12)$") {
    return 404;
}

# Redirecciones relativas: detrás de un proxy o túnel HTTPS, nginx no debe escribir http:// ni su puerto interno.
absolute_redirect off;

set $sgpi_request "$request_method:$request_uri";

CONF;
$out .= "\n# Direcciones anteriores: se redirige solo GET y HEAD, para no perder los datos de un formulario.\n";
$out .= "if (\$sgpi_request ~ \"^(?:GET|HEAD):/index\\.php(\\?.*)?$\") { return 302 /\$1; }\n";
foreach ($urls as $file => $clean) {
    $pattern = preg_quote($file, '#');
    $out .= "if (\$sgpi_request ~ \"^(?:GET|HEAD):{$pattern}(\\?.*)?$\") { return 302 {$clean}\$1; }\n";
}
$out .= "\n# Cada dirección limpia sirve su archivo sin mostrarlo.\n";
foreach ($urls as $file => $clean) {
    $out .= 'rewrite "^'.preg_quote($clean, '#').'/?$" '.$file." last;\n";
}
file_put_contents(__DIR__.'/nginx-clean-urls.conf', $out);
echo count($urls)." direcciones\n";
