


<?php
session_start();
require_once '../config/conexion.php'; // Incluye la conexion a la BD en el puerto 3307

$mensaje = "";
$tipo_mensaje = "";
$tipo_acceso = "";
$usuario = "";
$vista = "seleccion";

// PROCESAR FORMULARIOS
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $accion = $_POST["accion"] ?? "";

    // INICIO DE SESION
    if ($accion === "ingresar") {

        $tipo_acceso = $_POST["tipo_acceso"] ?? "";
        $usuario = trim($_POST["usuario"] ?? "");
        $contrasena = trim($_POST["contrasena"] ?? "");

        if (!in_array($tipo_acceso, ["empleado", "jefe"], true)) {

            $mensaje = "Selecciona un tipo de acceso valido.";
            $tipo_mensaje = "error";
            $vista = "seleccion";

        } elseif ($usuario === "" || $contrasena === "") {

            $mensaje = "Por favor, completa todos los campos.";
            $tipo_mensaje = "error";
            $vista = "login";

        } else {

            // CONSULTA REAL A LA BASE DE DATOS (Buscando por correo o nombre)
            $sql = "SELECT * FROM usuarios WHERE correo = ? OR nombre = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("ss", $usuario, $usuario);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows === 1) {
                $row = $resultado->fetch_assoc();

                // Verificar contraseña (soporta texto plano como '123456' o contraseñas encriptadas con password_hash)
                $password_valida = false;
                if ($contrasena === $row['password'] || password_verify($contrasena, $row['password'])) {
                    $password_valida = true;
                }

                if ($password_valida) {
                    $rol_bd = $row['rol']; // 'admin' o 'vendedor'

                    // Validar correspondencia de permisos según el tipo de acceso elegido
                    if ($tipo_acceso === "jefe" && $rol_bd !== "admin") {
                        $mensaje = "Este usuario no cuenta con privilegios de Jefe (Administrador).";
                        $tipo_mensaje = "error";
                        $vista = "login";
                    } else {
                        // Guardar datos en la sesión
                        $_SESSION["id_usuario"] = $row['id_usuario'];
                        $_SESSION["usuario"] = $row['nombre'];
                        $_SESSION["correo"] = $row['correo'];
                        $_SESSION["rol"] = $rol_bd;

                        header("Location: dashboard.php");
                        exit;
                    }
                } else {
                    $mensaje = "Contraseña incorrecta.";
                    $tipo_mensaje = "error";
                    $vista = "login";
                }
            } else {
                $mensaje = "El usuario o correo no existe en el sistema.";
                $tipo_mensaje = "error";
                $vista = "login";
            }
        }
    }

    // RECUPERACIÓN DE CONTRASEÑA
    elseif ($accion === "recuperar") {

        $correo = trim($_POST["correo"] ?? "");

        if ($correo === "" || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {

            $mensaje = "Ingresa un correo electrónico válido.";
            $tipo_mensaje = "error";

        } else {

            // Opcional: verificar si el correo existe en la base de datos
            $sql = "SELECT id_usuario FROM usuarios WHERE correo = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("s", $correo);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows > 0) {
                $mensaje = "Se han enviado las instrucciones de recuperación a tu correo electrónico.";
                $tipo_mensaje = "exito";
            } else {
                $mensaje = "El correo ingresado no está registrado en el sistema.";
                $tipo_mensaje = "error";
            }

        }

        $vista = "recuperar";

    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Jewelry Manager - Acceso</title>

    <link rel="stylesheet" href="../css/estilos.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            padding: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            font-family: Arial, sans-serif;
        }

        .login-principal {
            width: 100%;
            max-width: 1000px;
            min-height: 570px;
            display: grid;
            grid-template-columns: 0.9fr 1.1fr;
            background: white;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 15px 45px rgba(20, 36, 61, 0.13);
        }

        .login-marca {
            background: linear-gradient(145deg, #14243d, #233d60);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 45px 30px;
        }

        .login-diamante {
            color: #dfbd72;
            font-size: 75px;
            margin-bottom: 15px;
        }

        .login-marca h1 {
            font-size: 29px;
            margin: 0 0 12px;
            letter-spacing: 1px;
        }

        .login-marca p {
            color: #d5dce6;
            font-size: 14px;
            line-height: 1.7;
        }

        .login-contenido {
            padding: 50px 45px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-contenido h2 {
            color: #172b49;
            font-size: 27px;
            margin: 0 0 10px;
        }

        .login-descripcion {
            color: #777;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .opciones-acceso {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .tarjeta-acceso {
            border: 1px solid #e4e1d9;
            background: white;
            border-radius: 13px;
            padding: 25px 12px;
            cursor: pointer;
            transition: all 0.25s ease;
            text-align: center;
            color: #172b49;
        }

        .tarjeta-acceso:hover {
            transform: translateY(-5px);
            border-color: #c5a35e;
            box-shadow: 0 8px 22px rgba(20, 36, 61, 0.12);
        }

        .tarjeta-acceso .icono {
            display: block;
            font-size: 36px;
            color: #b18d4a;
            margin-bottom: 13px;
        }

        .tarjeta-acceso h3 {
            margin: 0 0 8px;
            font-size: 17px;
        }

        .tarjeta-acceso p {
            color: #777;
            font-size: 12px;
            margin: 0 0 17px;
        }

        .boton-tarjeta {
            display: inline-block;
            background: #172b49;
            color: white;
            padding: 9px 18px;
            border-radius: 6px;
            font-size: 12px;
        }

        .tarjeta-acceso:hover .boton-tarjeta {
            background: #b18d4a;
        }

        .formulario-acceso {
            display: none;
        }

        .formulario-acceso.visible {
            display: block;
            animation: aparecer 0.35s ease;
        }

        @keyframes aparecer {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .encabezado-formulario {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .icono-rol {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #f6f0e3;
            color: #a5813e;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 25px;
        }

        .encabezado-formulario h3 {
            margin: 0 0 5px;
            color: #172b49;
        }

        .encabezado-formulario p {
            margin: 0;
            color: #888;
            font-size: 12px;
        }

        .formulario-acceso .campo {
            margin-bottom: 18px;
        }

        .formulario-acceso label {
            display: block;
            margin-bottom: 8px;
            color: #26364d;
            font-size: 13px;
            font-weight: bold;
        }

        .formulario-acceso input {
            width: 100%;
            padding: 13px;
            border: 1px solid #ddd;
            border-radius: 7px;
            outline: none;
            font-size: 14px;
        }

        .formulario-acceso input:focus {
            border-color: #b18d4a;
            box-shadow: 0 0 0 3px rgba(177, 141, 74, 0.12);
        }

        .enlace-recuperar {
            display: block;
            text-align: right;
            margin: -5px 0 20px;
            color: #a5813e;
            font-size: 13px;
            text-decoration: none;
        }

        .enlace-recuperar:hover {
            text-decoration: underline;
        }

        .btn-ingresar {
            width: 100%;
            border: none;
            border-radius: 7px;
            padding: 14px;
            background: #172b49;
            color: white;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-ingresar:hover {
            background: #b18d4a;
        }

        .btn-volver {
            background: transparent;
            border: none;
            color: #a5813e;
            cursor: pointer;
            margin-top: 18px;
            padding: 8px 0;
            font-size: 13px;
            text-align: left;
        }

        .mensaje-formulario {
            padding: 12px;
            border-radius: 7px;
            margin-bottom: 18px;
            font-size: 13px;
            line-height: 1.5;
        }

        .mensaje-formulario.error {
            background: #fce8e8;
            color: #a52a2a;
        }

        .mensaje-formulario.exito {
            background: #e8f5e9;
            color: #286438;
        }

        @media (max-width: 700px) {
            .login-principal {
                grid-template-columns: 1fr;
            }

            .login-marca {
                padding: 30px 20px;
            }

            .login-diamante {
                font-size: 48px;
            }

            .login-marca h1 {
                font-size: 23px;
            }

            .login-contenido {
                padding: 35px 22px;
            }
        }

        @media (max-width: 380px) {
            .opciones-acceso {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div class="login-principal">

    <section class="login-marca">

        <div class="login-diamante">◇</div>

        <h1>Jewelry Manager</h1>

        <p>
            Sistema de gestion de joyeria<br>
            Organizacion, control y elegancia<br>
            en un solo lugar.
        </p>

    </section>

    <main class="login-contenido">

        <?php if ($mensaje !== ""): ?>
            <div class="mensaje-formulario <?php echo htmlspecialchars($tipo_mensaje); ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <!-- SELECCION DE ACCESO -->
        <section id="seleccionAcceso"
            style="<?php echo ($vista !== "seleccion") ? 'display:none;' : ''; ?>">

            <h2>Bienvenido</h2>

            <p class="login-descripcion">
                Selecciona tu perfil para continuar
            </p>

            <div class="opciones-acceso">

                <button type="button"
                    class="tarjeta-acceso"
                    onclick="mostrarFormulario('empleado')">

                    <span class="icono">♙</span>
                    <h3>Empleado</h3>
                    <p>Acceso a funciones operativas</p>
                    <span class="boton-tarjeta">Ingresar →</span>

                </button>

                <button type="button"
                    class="tarjeta-acceso"
                    onclick="mostrarFormulario('jefe')">

                    <span class="icono">♛</span>
                    <h3>Jefe</h3>
                    <p>Acceso administrativo</p>
                    <span class="boton-tarjeta">Ingresar →</span>

                </button>

            </div>

        </section>

        <!-- FORMULARIO DE INICIO DE SESIÓN -->
        <section id="formularioAcceso"
            class="formulario-acceso <?php echo ($vista === "login") ? 'visible' : ''; ?>">

            <div class="encabezado-formulario">

                <div class="icono-rol" id="iconoRol">♙</div>

                <div>
                    <h3 id="tituloRol">Iniciar sesión</h3>
                    <p id="descripcionRol">Ingresa tus credenciales</p>
                </div>

            </div>

            <form action="login.php" method="POST">

                <input type="hidden" name="accion" value="ingresar">

                <input
                    type="hidden"
                    name="tipo_acceso"
                    id="tipoAcceso"
                    value="<?php echo htmlspecialchars($tipo_acceso); ?>"
                >

                <div class="campo">
                    <label for="usuario">Usuario o Correo</label>

                    <input
                        type="text"
                        id="usuario"
                        name="usuario"
                        placeholder="Ingresa tu usuario o correo"
                        autocomplete="username"
                        value="<?php echo htmlspecialchars($usuario); ?>"
                        required
                    >
                </div>

                <div class="campo">
                    <label for="contrasena">Contraseña</label>

                    <input
                        type="password"
                        id="contrasena"
                        name="contrasena"
                        placeholder="Ingresa tu contraseña"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <a href="#" class="enlace-recuperar"
                    onclick="mostrarRecuperacion(); return false;">
                    ¿Olvidaste tu contraseña?
                </a>

                <button type="submit" class="btn-ingresar">
                    Iniciar sesión
                </button>

            </form>

            <button type="button" class="btn-volver"
                onclick="volverSeleccion()">
                ← Cambiar tipo de acceso
            </button>

        </section>

        <!-- FORMULARIO DE RECUPERACIÓN -->
        <section id="formularioRecuperar"
            class="formulario-acceso <?php echo ($vista === "recuperar") ? 'visible' : ''; ?>">

            <div class="encabezado-formulario">

                <div class="icono-rol">♧</div>

                <div>
                    <h3>Recuperar contraseña</h3>
                    <p>Solicita ayuda para recuperar tu acceso</p>
                </div>

            </div>

            <form action="login.php" method="POST">

                <input type="hidden" name="accion" value="recuperar">

                <div class="campo">
                    <label for="correo">Correo electronico</label>

                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        placeholder="Ingresa tu correo registrado"
                        required
                    >
                </div>

                <button type="submit" class="btn-ingresar">
                    Enviar solicitud
                </button>

            </form>

            <button type="button" class="btn-volver"
                onclick="volverSeleccion()">
                ← Volver al inicio
            </button>

        </section>

    </main>

</div>

<script>

    function mostrarFormulario(tipo) {

        document.getElementById("seleccionAcceso").style.display = "none";

        document.getElementById("formularioRecuperar").classList.remove("visible");

        const formulario = document.getElementById("formularioAcceso");
        formulario.classList.add("visible");

        document.getElementById("tipoAcceso").value = tipo;

        if (tipo === "empleado") {

            document.getElementById("tituloRol").textContent =
                "Acceso de empleado";

            document.getElementById("descripcionRol").textContent =
                "Ingresa tus datos para acceder al sistema.";

            document.getElementById("iconoRol").textContent = "♙";

        } else {

            document.getElementById("tituloRol").textContent =
                "Acceso administrativo";

            document.getElementById("descripcionRol").textContent =
                "Ingresa tus credenciales de administrador.";

            document.getElementById("iconoRol").textContent = "♛";

        }
    }

    function mostrarRecuperacion() {

        document.getElementById("formularioAcceso").classList.remove("visible");

        document.getElementById("formularioRecuperar").classList.add("visible");

    }

    function volverSeleccion() {

        document.getElementById("formularioAcceso").classList.remove("visible");

        document.getElementById("formularioRecuperar").classList.remove("visible");

        document.getElementById("seleccionAcceso").style.display = "block";

        document.getElementById("tipoAcceso").value = "";

    }

    <?php if ($vista === "login"): ?>
        mostrarFormulario(<?php echo json_encode($tipo_acceso); ?>);
    <?php endif; ?>

</script>

</body>
</html>