
<?php
session_start();

if (!isset($_SESSION["usuario"]) || !isset($_SESSION["rol"])) {
    header("Location: login.php");
    exit;
}

$rol = strtolower(trim($_SESSION["rol"]));
require_once "../config/conexion.php";

$productos_db = [];
try {
    $resultado = $conexion->query("SELECT * FROM productos");
    $productos_db = $resultado->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $productos_db = [];
}

if (isset($_GET["cerrar_sesion"])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Jewelry Manager - Productos</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

    <div class="contenedor-dashboard">

        <aside class="menu-lateral">
            <h2>◇ Jewelry Manager</h2>
            <nav>
                <a href="dashboard.php">Inicio</a>
                <a href="productos.php" class="activo">Productos</a>
                <a href="inventario.php">Inventario</a>
                <a href="ventas.php">Ventas</a>
                <a href="clientes.php">Clientes</a>
                <?php if ($rol === "jefe" || $rol === "admin"): ?>
                    <a href="reportes.php">Reportes</a>
                <?php endif; ?>
            </nav>
            <a href="productos.php?cerrar_sesion=1" class="cerrar-sesion">Cerrar sesion</a>
        </aside>

        <main class="contenido-dashboard">

            <header class="encabezado-dashboard">
                <div>
                    <h1>Gestion de productos</h1>
                    <p>Administra las joyas registradas en el sistema</p>
                </div>
                <div class="usuario-dashboard">
                    <?php echo htmlspecialchars($_SESSION["usuario"]); ?>
                </div>
            </header>

            <section class="bienvenida-dashboard">
                <h2>Productos</h2>
                <p>Desde este modulo podras registrar, consultar, actualizar y eliminar los productos de la joyeria.</p>
                <br>
                <a href="registrar_producto.php" class="btn-ingresar" style="display:inline-block; width:auto; text-decoration:none;">
                    + Registrar producto
                </a>
            </section>

            <section class="tabla-productos">
                <h2>Listado de productos</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Codigo</th>
                            <th>Nombre</th>
                            <th>Material</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($productos_db) > 0): ?>
                            <?php foreach ($productos_db as $prod): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($prod['codigo']); ?></td>
                                    <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($prod['material']); ?></td>
                                    <td>$<?php echo number_format($prod['precio_venta'], 0, ',', '.'); ?></td>
                                    <td><?php echo htmlspecialchars($prod['stock']); ?></td>
                                    <td>
                                        <a href="editar_producto.php?id=<?php echo $prod['id_producto']; ?>" style="color:#0b2545; text-decoration:none; font-weight:bold;">Editar</a> | 
                                        <a href="eliminar_producto.php?id=<?php echo $prod['id_producto']; ?>" style="color:#a83232; text-decoration:none; font-weight:bold;" onclick="return confirm('¿Estas seguro de eliminar este producto?');">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: #777;">No hay productos registrados en la base de datos todavia.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>

        </main>
    </div>

</body>
</html>