#!/bin/bash
cat > .htaccess <<EOF
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f  
RewriteRule ^(.*)$ index.php/ [QSA,PT,L]
EOF
