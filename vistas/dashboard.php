





<?php
session_start();

// Validar que el usuario haya iniciado sesion
if (!isset($_SESSION["usuario"]) || !isset($_SESSION["rol"])) {
    header("Location: login.php");
    exit;
}

// Cerrar sesion
if (isset($_GET["cerrar_sesion"])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

$rol = strtolower(trim($_SESSION["rol"]));
$nombre_usuario = htmlspecialchars($_SESSION["usuario"]);
$nombre_rol = ($rol === "jefe" || $rol === "admin") ? "Administrador" : "Empleado";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jewelry Manager - Panel Principal</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

<div class="contenedor-dashboard">

    <!-- MENU LATERAL -->
    <aside class="menu-lateral">
        <h2>◇ Jewelry Manager</h2>
        
        <nav>
            <a href="dashboard.php" class="activo">Inicio</a>
            <a href="productos.php">Productos</a>
            <a href="inventario.php">Inventario</a>
            <a href="ventas.php">Ventas</a>
            <a href="clientes.php">Clientes</a>
            <?php if ($rol === "jefe" || $rol === "admin"): ?>
                <a href="reportes.php">Reportes</a>
            <?php endif; ?>
        </nav>

        <a href="dashboard.php?cerrar_sesion=1" class="cerrar-sesion">Cerrar sesion</a>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-dashboard">

        <!-- ENCABEZADO -->
        <header class="encabezado-dashboard">
            <div>
                <h1>Panel principal</h1>
                <p>Resumen general de tu joyeria</p>
            </div>
            <div class="usuario-dashboard">
                <?php echo $nombre_usuario; ?> (<?php echo $nombre_rol; ?>)
            </div>
        </header>

        <!-- TARJETAS DE RESUMEN -->
        <section class="tarjetas-resumen">
            <article class="tarjeta">
                <h3>Productos registrados</h3>
                <div class="numero">25</div>
                <span>Catalogo activo</span>
            </article>

            <article class="tarjeta">
                <h3>Unidades disponibles</h3>
                <div class="numero">120</div>
                <span>Inventario total</span>
            </article>

            <article class="tarjeta">
                <h3>Ventas realizadas</h3>
                <div class="numero">28</div>
                <span>Este mes</span>
            </article>

            <article class="tarjeta">
                <h3>Clientes registrados</h3>
                <div class="numero">18</div>
                <span>Base de datos</span>
            </article>
        </section>

        <!-- BIENVENIDA -->
        <section class="bienvenida-dashboard">
            <h2>Bienvenido/a, <?php echo $nombre_usuario; ?></h2>
            <p>
                Desde aqui puedes consultar el estado general de las operaciones, gestionar el inventario, 
                revisar las ventas y acceder a los diferentes modulos de control de <strong>Jewelry Manager</strong>. 
                Utiliza el menu lateral para navegar por el sistema.
            </p>
        </section>

    </main>

</div>

</body>
</html>