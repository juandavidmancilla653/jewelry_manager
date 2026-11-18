<?php
session_start();

if (!isset($_SESSION["usuario"]) || !isset($_SESSION["rol"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/conexion.php";

$mensaje = "";
$tipo_mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $codigo = trim($_POST["codigo"]);
    $nombre = trim($_POST["nombre"]);
    $material = trim($_POST["material"]);
    $precio_venta = trim($_POST["precio_venta"]);
    $stock = trim($_POST["stock"]);

    if (!empty($codigo) && !empty($nombre) && !empty($material) && !empty($precio_venta) && !empty($stock)) {
        try {
            // Se usa 'precio_venta' tal como esta en tu base de datos
            $sql = "INSERT INTO productos (codigo, nombre, material, precio_venta, stock) VALUES (:codigo, :nombre, :material, :precio_venta, :stock)";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                ':codigo' => $codigo,
                ':nombre' => $nombre,
                ':material' => $material,
                ':precio_venta' => $precio_venta,
                ':stock' => $stock
            ]);
            
            header("Location: productos.php");
            exit();
        } catch (PDOException $e) {
            $mensaje = "Error al guardar: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    } else {
        $mensaje = "Por favor, completa todos los campos obligatorios.";
        $tipo_mensaje = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Jewelry Manager - Registrar Producto</title>
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
        </nav>
        <a href="productos.php?cerrar_sesion=1" class="cerrar-sesion">Cerrar sesion</a>
    </aside>

    <main class="contenido-dashboard">
        <header class="encabezado-dashboard">
            <div>
                <h1>Registrar nuevo producto</h1>
                <p>Ingresa los datos de la joya para guardarla en la base de datos</p>
            </div>
        </header>

        <?php if (!empty($mensaje)): ?>
            <div style="background: #fbe3e3; color: #a83232; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <section class="formulario-producto">
            <form action="registrar_producto.php" method="POST">
                <div class="campos-producto">
                    <div class="campo">
                        <label for="codigo">Codigo</label>
                        <input type="text" id="codigo" name="codigo" required placeholder="Ej: PR001">
                    </div>
                    <div class="campo">
                        <label for="nombre">Nombre de la joya</label>
                        <input type="text" id="nombre" name="nombre" required placeholder="Ej: Anillo de oro">
                    </div>
                    <div class="campo">
                        <label for="material">Material</label>
                        <input type="text" id="material" name="material" required placeholder="Ej: Oro">
                    </div>
                    <div class="campo">
                        <label for="precio_venta">Precio de Venta ($)</label>
                        <input type="number" step="0.01" id="precio_venta" name="precio_venta" required placeholder="Ej: 850000">
                    </div>
                    <div class="campo">
                        <label for="stock">Cantidad / Stock</label>
                        <input type="number" id="stock" name="stock" required placeholder="Ej: 5">
                    </div>
                </div>

                <div class="botones-producto">
                    <a href="productos.php" class="btn-cancelar" style="text-decoration:none; display:inline-block; padding: 14px 25px;">Cancelar</a>
                    <button type="submit" class="btn-ingresar" style="width: auto; padding: 14px 25px;">Guardar producto</button>
                </div>
            </form>
        </section>
    </main>
</div>

</body>
</html>