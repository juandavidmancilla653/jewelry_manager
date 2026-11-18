<?php
session_start();

if (!isset($_SESSION["usuario"]) || !isset($_SESSION["rol"])) {
    header("Location: login.php");
    exit;
}

$rol = strtolower(trim($_SESSION["rol"]));
require_once "../config/conexion.php";

$mensaje = "";
$tipo_mensaje = "";


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["registrar_cliente"])) {
    $documento = trim($_POST["documento"] ?? '');
    $nombre = trim($_POST["nombre"] ?? '');
    $telefono = trim($_POST["telefono"] ?? '');
    $correo = trim($_POST["correo"] ?? '');
    $direccion = trim($_POST["direccion"] ?? '');

    if (!empty($documento) && !empty($nombre)) {
        try {
            $sql = "INSERT INTO clientes (documento, nombre, telefono, correo, direccion, fecha_registro) VALUES (:documento, :nombre, :telefono, :correo, :direccion, NOW())";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                ':documento' => $documento,
                ':nombre' => $nombre,
                ':telefono' => $telefono,
                ':correo' => $correo,
                ':direccion' => $direccion
            ]);
            $mensaje = "¡Cliente registrado con exito en la base de datos!";
            $tipo_mensaje = "exito";
        } catch (PDOException $e) {
            $mensaje = "Error al registrar el cliente: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    } else {
        $mensaje = "El documento y el nombre del cliente son obligatorios.";
        $tipo_mensaje = "error";
    }
}


$clientes_db = [];
try {
    $resultado = $conexion->query("SELECT * FROM clientes ORDER BY id_cliente DESC");
    $clientes_db = $resultado->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $clientes_db = [];
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jewelry Manager - Clientes</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        .modal-cliente {
            background: #f8f9fa;
            padding: 22px;
            border-radius: 12px;
            border: 1px solid #e1e1e1;
            margin-bottom: 25px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.04);
        }
    </style>
</head>
<body>

<div class="contenedor-dashboard">


    <aside class="menu-lateral">
        <h2>◇ Jewelry Manager</h2>
        <nav>
            <a href="dashboard.php">Inicio</a>
            <a href="productos.php">Productos</a>
            <a href="inventario.php">Inventario</a>
            <a href="ventas.php">Ventas</a>
            <a href="clientes.php" class="activo">Clientes</a>
            <?php if ($rol === "jefe" || $rol === "admin"): ?>
                <a href="reportes.php">Reportes</a>
            <?php endif; ?>
        </nav>
        <a href="clientes.php?cerrar_sesion=1" class="cerrar-sesion">Cerrar sesion</a>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-dashboard">

        <header class="encabezado-dashboard">
            <div>
                <h1>Gestion de clientes</h1>
                <p>Registra y consulta la base de datos de compradores</p>
            </div>
            <div class="usuario-dashboard">
                <?php echo htmlspecialchars($_SESSION["usuario"]); ?>
            </div>
        </header>

        <?php if (!empty($mensaje)): ?>
            <div style="padding: 12px; margin-bottom: 20px; border-radius: 6px; background: <?php echo $tipo_mensaje === 'exito' ? '#d4edda' : '#fbe3e3'; ?>; color: <?php echo $tipo_mensaje === 'exito' ? '#155724' : '#a83232'; ?>;">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        
        <section class="modal-cliente">
            <h2 style="color: #0b2545; margin-bottom: 15px;">Registrar nuevo cliente</h2>
            <form action="clientes.php" method="POST">
                <div class="campos-producto" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
                    <div class="campo">
                        <label for="documento" style="display:block; margin-bottom:5px; font-weight:600;">Documento:</label>
                        <input type="text" id="documento" name="documento" required placeholder="Ej: 10203040" style="width:100%; padding:11px; border:1px solid #ccc; border-radius:6px;">
                    </div>

                    <div class="campo">
                        <label for="nombre" style="display:block; margin-bottom:5px; font-weight:600;">Nombre completo:</label>
                        <input type="text" id="nombre" name="nombre" required placeholder="Ej: julano" style="width:100%; padding:11px; border:1px solid #ccc; border-radius:6px;">
                    </div>

                    <div class="campo">
                        <label for="telefono" style="display:block; margin-bottom:5px; font-weight:600;">Telefono:</label>
                        <input type="text" id="telefono" name="telefono" placeholder="Ej: 3001234567" style="width:100%; padding:11px; border:1px solid #ccc; border-radius:6px;">
                    </div>

                    <div class="campo">
                        <label for="correo" style="display:block; margin-bottom:5px; font-weight:600;">Correo electronico:</label>
                        <input type="email" id="correo" name="correo" placeholder="Ej: maria@correo.com" style="width:100%; padding:11px; border:1px solid #ccc; border-radius:6px;">
                    </div>

                    <div class="campo">
                        <label for="direccion" style="display:block; margin-bottom:5px; font-weight:600;">Direccion:</label>
                        <input type="text" id="direccion" name="direccion" placeholder="Ej: Calle 10 # 20-30" style="width:100%; padding:11px; border:1px solid #ccc; border-radius:6px;">
                    </div>
                </div>

                <button type="submit" name="registrar_cliente" class="btn-ingresar" style="width: auto; padding: 12px 25px; cursor:pointer;">
                    + Guardar cliente
                </button>
            </form>
        </section>

        
        <section class="tabla-productos">
            <h2>Listado de clientes registrados</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Documento</th>
                        <th>Nombre</th>
                        <th>Telefono</th>
                        <th>Correo</th>
                        <th>Direccion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($clientes_db) > 0): ?>
                        <?php foreach ($clientes_db as $cliente): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($cliente['id_cliente']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['documento']); ?></td>
                                <td><strong><?php echo htmlspecialchars($cliente['nombre']); ?></strong></td>
                                <td><?php echo htmlspecialchars($cliente['telefono'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($cliente['correo'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($cliente['direccion'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #777;">No hay clientes registrados en la base de datos todavia.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

    </main>

</div>

</body>
</html>