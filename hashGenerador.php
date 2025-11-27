<?php
    // La contraseña de prueba que queremos usar
    $password_clara = 'test1234'; 

    // Generar el hash de esa contraseña
    $hash_nuevo = password_hash($password_clara, PASSWORD_DEFAULT);

    echo "Hash para 'test1234': " . $hash_nuevo;
    ?>