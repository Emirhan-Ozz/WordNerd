<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (preg_match('#^/(data|includes)(/|$)#i', $path) || preg_match('#/\.#', $path)) {
    http_response_code(403);
    exit('403 - Erişim engellendi');
}

return false;
