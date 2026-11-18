<?php
session_start();

// Verificar sesion
if (!isset($_SESSION["usuario"]) || !isset($_SESSION["rol"])) {
    header("Location: login.php");
    exit;
}

$rol = strtolower(trim($_SESSION["rol"]));

if ($rol !== "jefe" && $rol !== "empleado" && $rol !== "admin") {
    session_unset();
    session_destroy();
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

require_once "../config/conexion.php";

$mensaje = "";
$tipo_mensaje = "";

// PROCESAR ENTRADAS Y SALIDAS DE INVENTARIO
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["tipo_movimiento"])) {
    $id_producto = (int) ($_POST["id_producto"] ?? 0);
    $cantidad = (int) ($_POST["cantidad"] ?? 0);
    $tipo = $_POST["tipo_movimiento"];

    if ($id_producto > 0 && $cantidad > 0) {
        try {
            // Consultar el stock actual del producto seleccionado
            $stmt = $conexion->prepare("SELECT nombre, stock FROM productos WHERE id_producto = :id");
            $stmt->execute([':id' => $id_producto]);
            $prod = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($prod) {
                if ($tipo === "entrada") {
                    $nuevo_stock = $prod['stock'] + $cantidad;
                    $update = $conexion->prepare("UPDATE productos SET stock = :stock WHERE id_producto = :id");
                    $update->execute([':stock' => $nuevo_stock, ':id' => $id_producto]);

                    $mensaje = "Entrada registrada: se agregaron {$cantidad} unidades a '{$prod['nombre']}'.";
                    $tipo_mensaje = "exito";
                } elseif ($tipo === "salida") {
                    if ($prod['stock'] >= $cantidad) {
                        $nuevo_stock = $prod['stock'] - $cantidad;
                        $update = $conexion->prepare("UPDATE productos SET stock = :stock WHERE id_producto = :id");
                        $update->execute([':stock' => $nuevo_stock, ':id' => $id_producto]);

                        $mensaje = "Salida registrada: se descontaron {$cantidad} unidades de '{$prod['nombre']}'.";
                        $tipo_mensaje = "exito";
                    } else {
                        $mensaje = "Stock insuficiente: '{$prod['nombre']}' solo tiene {$prod['stock']} unidades disponibles.";
                        $tipo_mensaje = "error";
                    }
                }
            }
        } catch (PDOException $e) {
            $mensaje = "Error al actualizar el inventario: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    } else {
        $mensaje = "Selecciona un producto valido e ingresa una cantidad mayor a 0.";
        $tipo_mensaje = "error";
    }
}

// CONSULTAR PRODUCTOS REALES Y CALCULAR INDICADORES
$productos_db = [];
$total_productos = 0;
$unidades_disponibles = 0;
$stock_bajo = 0;
$sin_existencias = 0;

try {
    $resultado = $conexion->query("SELECT * FROM productos ORDER BY nombre ASC");
    $productos_db = $resultado->fetchAll(PDO::FETCH_ASSOC);

    $total_productos = count($productos_db);

    foreach ($productos_db as $p) {
        $s = (int) $p['stock'];
        $unidades_disponibles += $s;

        if ($s === 0) {
            $sin_existencias++;
        } elseif ($s <= 3) {
            $stock_bajo++;
        }
    }
} catch (PDOException $e) {
    $productos_db = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jewelry Manager - Inventario</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

<div class="contenedor-dashboard">

    <!-- MENU LATERAL -->
    <aside class="menu-lateral">
        <h2>◇ Jewelry Manager</h2>
        <nav>
            <a href="dashboard.php">Inicio</a>
            <a href="productos.php">Productos</a>
            <a href="inventario.php" class="activo">Inventario</a>
            <a href="ventas.php">Ventas</a>
            <a href="clientes.php">Clientes</a>
            <?php if ($rol === "jefe" || $rol === "admin"): ?>
                <a href="reportes.php">Reportes</a>
            <?php endif; ?>
        </nav>
        <a href="inventario.php?cerrar_sesion=1" class="cerrar-sesion">Cerrar sesion</a>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido-dashboard">

        <header class="encabezado-dashboard">
            <div>
                <h1>Control de inventario</h1>
                <p>Consulta y administra las existencias de joyeria en tiempo real</p>
            </div>
            <div class="usuario-dashboard">
                <?php echo htmlspecialchars($_SESSION["usuario"]); ?>
            </div>
        </header>

        <!-- MENSAJES DE ALERTA -->
        <?php if (!empty($mensaje)): ?>
            <div class="mensaje-formulario <?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <!-- RESUMEN DINMMICO -->
        <section class="tarjetas-resumen">
            <div class="tarjeta">
                <h3>Total de productos</h3>
                <p class="numero"><?php echo $total_productos; ?></p>
                <span>Referencias registradas</span>
            </div>

            <div class="tarjeta">
                <h3>Unidades disponibles</h3>
                <p class="numero"><?php echo $unidades_disponibles; ?></p>
                <span>Existencias actuales</span>
            </div>

            <div class="tarjeta">
                <h3>Stock bajo</h3>
                <p class="numero"><?php echo $stock_bajo; ?></p>
                <span>3 unidades o menos</span>
            </div>

            <div class="tarjeta">
                <h3>Sin existencias</h3>
                <p class="numero"><?php echo $sin_existencias; ?></p>
                <span>Productos agotados</span>
            </div>
        </section>

        <!-- FORMULARIO DE MOVIMIENTOS -->
        <section class="formulario-producto" style="margin-bottom: 35px;">
            <h2>Registrar movimiento de inventario</h2>
            <p style="color: #666; margin-bottom: 20px;">
                Selecciona una joya y define la cantidad para sumar o descontar existencias en la base de datos.
            </p>

            <form action="inventario.php" method="POST">
                <div class="campos-producto">
                    <div class="campo">
                        <label for="id_producto">Producto</label>
                        <select id="id_producto" name="id_producto" required>
                            <option value="">-- Seleccionar joya --</option>
                            <?php foreach ($productos_db as $prod): ?>
                                <option value="<?php echo $prod['id_producto']; ?>">
                                    <?php echo htmlspecialchars($prod['codigo'] . " - " . $prod['nombre'] . " (Stock actual: " . $prod['stock'] . ")"); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo">
                        <label for="cantidad">Cantidad de unidades</label>
                        <input type="number" id="cantidad" name="cantidad" min="1" required placeholder="Ej: 5">
                    </div>
                </div>

                <div class="acciones-inventario">
                    <button type="submit" name="tipo_movimiento" value="entrada" class="btn-ingresar" style="width: auto; padding: 14px 22px;">
                        + Registrar entrada
                    </button>

                    <button type="submit" name="tipo_movimiento" value="salida" class="btn-salida">
                        − Registrar salida
                    </button>
                </div>
            </form>
        </section>

        <!-- TABLA DE EXISTENCIAS REALES -->
        <section class="tabla-productos">
            <h2>Existencias de productos</h2>

            <table>
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Producto</th>
                        <th>Material</th>
                        <th>Existencias</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($productos_db) > 0): ?>
                        <?php foreach ($productos_db as $prod): ?>
                            <?php
                                $stock_actual = (int) $prod['stock'];
                                if ($stock_actual === 0) {
                                    $clase_estado = "estado-agotado";
                                    $texto_estado = "Agotado";
                                } elseif ($stock_actual <= 3) {
                                    $clase_estado = "estado-bajo";
                                    $texto_estado = "Stock bajo";
                                } else {
                                    $clase_estado = "estado-disponible";
                                    $texto_estado = "Disponible";
                                }
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($prod['codigo']); ?></td>
                                <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($prod['material']); ?></td>
                                <td><strong><?php echo $stock_actual; ?></strong></td>
                                <td><span class="<?php echo $clase_estado; ?>"><?php echo $texto_estado; ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #777;">
                                No hay productos registrados en la base de datos todavia.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

    </main>

</div>

</body>
</html>