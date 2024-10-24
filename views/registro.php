<?php
session_start();
require_once '../inc/conexion.php';
require_once '../inc/funciones.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$errores = [
    'nombre' => '',
    'email' => '',
    'password' => '',
    'foto' => '',
    'exito' => ''
];

$nombre = '';
$email = '';
$password = '';
$rol = ''; 
$foto = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = limpiar_dato($_POST['nombre']);
    $email = limpiar_dato($_POST['email']);
    $password = $_POST['password'];
    $rol = $_POST['rol'];

    // Verificar si el archivo ha sido subido
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $foto = $_FILES['archivo'];

        // Validar el tipo de archivo
        $tipoArchivo = mime_content_type($foto['tmp_name']);
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($tipoArchivo, $tiposPermitidos)) {
            $errores['foto'] = 'Solo se permiten archivos JPG, PNG y GIF.';
        } else {
            // Definir la ruta donde se guardará la imagen
            $directorioDestino = '../uploads/';
            $nombreArchivo = uniqid() . '-' . basename($foto['name']); // Generar un nombre único
            $rutaArchivo = $directorioDestino . $nombreArchivo;

            // Verificar la ruta generada
            echo "Ruta del archivo: " . $rutaArchivo . "<br>"; // Para depuración

            // Mover el archivo a la carpeta de destino
            if (move_uploaded_file($foto['tmp_name'], $rutaArchivo)) {
                // La ruta se ha guardado correctamente
            } else {
                $errores['foto'] = 'Error al mover la imagen al directorio de destino.';
            }
        }
    } else {
        $errores['foto'] = 'Error al cargar la imagen o no se ha enviado un archivo.';
    }

    // Validaciones
    if (empty($nombre)) {
        $errores['nombre'] = 'El nombre es obligatorio.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = 'El email no es válido.';
    }
    if (strlen($password) < 6) {
        $errores['password'] = 'La contraseña debe tener al menos 6 caracteres.';
    }

    // Verificar si el email ya existe en la base de datos
    $sqlVerificacion = "SELECT COUNT(*) FROM usuarios WHERE email = :email";
    $stmtVerificacion = $conexion->prepare($sqlVerificacion);
    $stmtVerificacion->bindParam(':email', $email);
    $stmtVerificacion->execute();
    $emailExiste = $stmtVerificacion->fetchColumn();

    if ($emailExiste) {
        $errores['email'] = 'El correo electrónico ya está registrado.';
    }

    // Si no hay errores, proceder con el registro
    if (empty(array_filter($errores))) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Inserción en la base de datos
        $sql = "INSERT INTO usuarios (nombre, email, password, rol, foto) VALUES (:nombre, :email, :password, :rol, :foto)";
        $stmt = $conexion->prepare($sql);

        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $passwordHash);
        $stmt->bindParam(':rol', $rol);
        $stmt->bindParam(':foto', $rutaArchivo); // Guardar la ruta del archivo

        if ($stmt->execute()) {
            $errores['exito'] = 'Usuario registrado exitosamente.';
        } else {
            echo "Error al registrar el usuario.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        body {
            margin: 0;
        }
        .caja {
            display: grid;
            place-items: center;
            min-height: 100vh;
            background-color: #f0f0f0;
        }
        header {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            height: 50px;
        }
        a {
            padding-right: 20px;
            text-decoration: none;
            color: black;
            font-size: 27px;
        }
        form {
            width: 100%;
        }
        h2 {
            text-align: center;
        }
        .exito {
            text-align: center;
            color: green;
            font-weight: bold;
        }
        input {
            width: -webkit-fill-available;
        }
        .error {
            color: red;
            font-size: 0.9em;
        }
        .rol {
            border: 2px solid #D6D6D6;
            width: 47%;
            text-align: center;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            flex-wrap: nowrap;
            justify-content: center;
        }
        .distribucion {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        input[type="file"] {
            margin-bottom: 0px;
            padding-left: 21px;
            cursor: pointer;  
            color: transparent;
        }
    </style>
</head>
<body>
    <header>
        <a href="../index.php">Index</a>
        <a href="login.php">Login</a>
    </header>

    <div class="caja">
        <form method="post" enctype="multipart/form-data">
            <h2>Registro de Usuario</h2>
            <?php if (!empty($errores['exito'])): ?>
                <p class="exito"><?php echo $errores['exito']; ?></p>
            <?php endif; ?>

            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($nombre); ?>" >
            <?php if (!empty($errores['nombre'])): ?>
                <p class="error"><?php echo $errores['nombre']; ?></p>
            <?php endif; ?>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" >
            <?php if (!empty($errores['email'])): ?>
                <p class="error"><?php echo $errores['email']; ?></p>
            <?php endif; ?>

            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password" >
            <?php if (!empty($errores['password'])): ?>
                <p class="error"><?php echo $errores['password']; ?></p>
            <?php endif; ?>

            <div class="distribucion">
                <div class="rol">
                    <p>Rol</p>
                </div>
                <div class="rol">
                    <select id="rol" name="rol"> 
                        <option value="invitado" <?php echo ($rol === 'invitado') ? 'selected' : ''; ?>>Invitado</option>
                        <option value="administrador" <?php echo ($rol === 'administrador') ? 'selected' : ''; ?>>Administrador</option>
                    </select>
                </div>

                <div class="rol">
                    <p>Imagen de perfil</p>
                </div>
                <div class="rol">
                    <input type="file" name="archivo" id="archivo" accept="image/*">
                    <?php if (!empty($errores['foto'])): ?>
                        <p class="error"><?php echo $errores['foto']; ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <button type="submit">Registrar</button>
        </form>
    </div>
</body>
</html>