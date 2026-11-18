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

// PROCESAR NUEVA VENTA
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["registrar_venta"])) {
    $id_producto = (int) ($_POST["id_producto"] ?? 0);
    $cantidad = (int) ($_POST["cantidad"] ?? 0);
    $id_cliente = (int) ($_POST["id_cliente"] ?? 1); // Cliente por defecto si no se selecciona
    $metodo_pago = trim($_POST["metodo_pago"] ?? 'Efectivo');
    
    // Obtener el ID del usuario logueado actualmente (si está guardado en sesión)
    $id_usuario = $_SESSION["id_usuario"] ?? 1; 

    if ($id_producto > 0 && $cantidad > 0) {
        try {
            // 1. Obtener los datos del producto y verificar stock
            $stmt = $conexion->prepare("SELECT nombre, precio_venta, stock FROM productos WHERE id_producto = :id");
            $stmt->execute([':id' => $id_producto]);
            $prod = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($prod) {
                if ($prod['stock'] >= $cantidad) {
                    $total_pagar = $prod['precio_venta'] * $cantidad;

                    // 2. Registrar la venta usando los campos exactos de tu tabla MySQL
                    $sql_venta = "INSERT INTO ventas (id_cliente, id_usuario, fecha_venta, total, metodo_pago) VALUES (:id_cliente, :id_usuario, NOW(), :total, :metodo_pago)";
                    $stmt_v = $conexion->prepare($sql_venta);
                    $stmt_v->execute([
                        ':id_cliente' => $id_cliente,
                        ':id_usuario' => $id_usuario,
                        ':total' => $total_pagar,
                        ':metodo_pago' => $metodo_pago
                    ]);

                    // 3. Descontar el stock en la tabla 'productos'
                    $nuevo_stock = $prod['stock'] - $cantidad;
                    $sql_stock = "UPDATE productos SET stock = :stock WHERE id_producto = :id";
                    $stmt_s = $conexion->prepare($sql_stock);
                    $stmt_s->execute([
                        ':stock' => $nuevo_stock,
                        ':id' => $id_producto
                    ]);

                    $mensaje = "¡Venta registrada con éxito! Total: $" . number_format($total_pagar, 0, ',', '.');
                    $tipo_mensaje = "exito";
                } else {
                    $mensaje = "Stock insuficiente: solo hay {$prod['stock']} unidades disponibles de '{$prod['nombre']}'.";
                    $tipo_mensaje = "error";
                }
            }
        } catch (PDOException $e) {
            $mensaje = "Error al procesar la venta: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    } else {
        $mensaje = "Por favor, selecciona un producto y una cantidad válida.";
        $tipo_mensaje = "error";
    }
}

// OBTENER PRODUCTOS PARA EL SELECT
$productos_db = [];
try {
    $res_prod = $conexion->query("SELECT * FROM productos WHERE stock > 0 ORDER BY nombre ASC");
    $productos_db = $res_prod->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $productos_db = [];
}

// OBTENER CLIENTES PARA EL SELECT (por si ya tienes la tabla clientes creada)
$clientes_db = [];
try {
    $res_cli = $conexion->query("SELECT * FROM clientes");
    $clientes_db = $res_cli->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $clientes_db = [];
}

// OBTENER EL HISTORIAL DE VENTAS REALES
$ventas_db = [];
try {
    $res_ventas = $conexion->query("SELECT * FROM ventas ORDER BY id_venta DESC");
    $ventas_db = $res_ventas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $ventas_db = [];
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
    <title>Jewelry Manager - Ventas</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        .modal-venta {
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

    <!-- MENÚ LATERAL -->
    <aside class="menu-lateral">
        <h2>◇ Jewelry Manager</h2>
        <nav>
            <a href="dashboard.php">Inicio</a>
            <a href="productos.php">Productos</a>
            <a href="inventario.php">Inventario</a>
            <a href="ventas.php" class="activo">Ventas</a>
            <a href="clientes.php">Clientes</a>
            <?php if ($rol === "jefe" || $rol === "admin"): ?>
                <a href="reportes.php">Reportes</a>
            <?php endif; ?>
        </nav>
        <a href="ventas.php?cerrar_sesion=1" class="cerrar-sesion">Cerrar sesión</a>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-dashboard">

        <header class="encabezado-dashboard">
            <div>
                <h1>Gestión de ventas</h1>
                <p>Registra transacciones comerciales y consulta el historial</p>
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

        <!-- FORMULARIO DE NUEVA VENTA -->
        <section class="modal-venta">
            <h2 style="color: #0b2545; margin-bottom: 15px;">Registrar nueva venta</h2>
            <form action="ventas.php" method="POST">
                <div class="campos-producto" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
                    <div class="campo">
                        <label for="id_producto" style="display:block; margin-bottom:5px; font-weight:600;">Seleccionar joya:</label>
                        <select id="id_producto" name="id_producto" required style="width:100%; padding:11px; border:1px solid #ccc; border-radius:6px;">
                            <option value="">-- Elige un producto --</option>
                            <?php foreach ($productos_db as $p): ?>
                                <option value="<?php echo $p['id_producto']; ?>">
                                    <?php echo htmlspecialchars($p['codigo'] . " - " . $p['nombre'] . " ($" . number_format($p['precio_venta'], 0, ',', '.') . " | Stock: " . $p['stock'] . ")"); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo">
                        <label for="id_cliente" style="display:block; margin-bottom:5px; font-weight:600;">Cliente:</label>
                        <select id="id_cliente" name="id_cliente" style="width:100%; padding:11px; border:1px solid #ccc; border-radius:6px;">
                            <?php if (count($clientes_db) > 0): ?>
                                <?php foreach ($clientes_db as $c): ?>
                                    <option value="<?php echo $c['id_cliente']; ?>"><?php echo htmlspecialchars($c['nombre'] ?? 'Cliente #' . $c['id_cliente']); ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="1">Cliente General (Predeterminado)</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="campo">
                        <label for="metodo_pago" style="display:block; margin-bottom:5px; font-weight:600;">Método de Pago:</label>
                        <select id="metodo_pago" name="metodo_pago" style="width:100%; padding:11px; border:1px solid #ccc; border-radius:6px;">
                            <option value="Efectivo">Efectivo</option>
                            <option value="Tarjeta">Tarjeta</option>
                            <option value="Transferencia">Transferencia</option>
                        </select>
                    </div>

                    <div class="campo">
                        <label for="cantidad" style="display:block; margin-bottom:5px; font-weight:600;">Cantidad:</label>
                        <input type="number" id="cantidad" name="cantidad" min="1" required placeholder="Ej: 1" style="width:100%; padding:11px; border:1px solid #ccc; border-radius:6px;">
                    </div>
                </div>

                <button type="submit" name="registrar_venta" class="btn-ingresar" style="width: auto; padding: 12px 25px; cursor:pointer;">
                    + Procesar venta
                </button>
            </form>
        </section>

        <!-- TABLA DE HISTORIAL DE VENTAS -->
        <section class="tabla-productos">
            <h2>Historial de ventas realizadas</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID Venta</th>
                        <th>Método de Pago</th>
                        <th>Total Cobrado</th>
                        <th>Fecha y Hora</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($ventas_db) > 0): ?>
                        <?php foreach ($ventas_db as $venta): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($venta['id_venta']); ?></td>
                                <td><?php echo htmlspecialchars($venta['metodo_pago']); ?></td>
                                <td><strong>$<?php echo number_format($venta['total'], 0, ',', '.'); ?></strong></td>
                                <td><?php echo htmlspecialchars($venta['fecha_venta']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #777;">No hay ventas registradas en la base de datos todavía.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

    </main>

</div>

</body>
</html>