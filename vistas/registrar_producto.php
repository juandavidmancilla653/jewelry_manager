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
    $codigo = trim($_POST["codigo"] ?? '');
    $nombre = trim($_POST["nombre"] ?? '');
    $material = trim($_POST["material"] ?? '');
    $stock = trim($_POST["stock"] ?? 0);
    $precio_compra = trim($_POST["precio_compra"] ?? 0);
    $precio_venta = trim($_POST["precio_venta"] ?? 0);
    $descripcion = trim($_POST["descripcion"] ?? '');

    if (!empty($codigo) && !empty($nombre) && !empty($material)) {
        try {
            $sql = "INSERT INTO productos (codigo, nombre, descripcion, material, precio_compra, precio_venta, stock) 
                    VALUES (:codigo, :nombre, :descripcion, :material, :precio_compra, :precio_venta, :stock)";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                ':codigo' => $codigo,
                ':nombre' => $nombre,
                ':descripcion' => $descripcion,
                ':material' => $material,
                ':precio_compra' => $precio_compra,
                ':precio_venta' => $precio_venta,
                ':stock' => $stock
            ]);
            
            header("Location: productos.php");
            exit();
        } catch (PDOException $e) {
            $mensaje = "Error al guardar en la base de datos: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    } else {
        $mensaje = "Por favor, completa los campos obligatorios.";
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
        <a href="productos.php?cerrar_sesion=1" class="cerrar-sesion">Cerrar sesiosn</a>
    </aside>

    <main class="contenido-dashboard">
        <header class="encabezado-dashboard">
            <div>
                <h1>Gestion de productos</h1>
                <p>Registra una nueva joya en el sistema</p>
            </div>
        </header>

        <?php if (!empty($mensaje)): ?>
            <div style="background: #fbe3e3; color: #a83232; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <section class="formulario-producto">
            <h2>Informacion del producto</h2>
            <form action="registrar_producto.php" method="POST">
                <div class="campos-producto">
                    <div class="campo">
                        <label for="codigo">Codigo del producto</label>
                        <input type="text" id="codigo" name="codigo" required placeholder="Ej: PR004">
                    </div>
                    <div class="campo">
                        <label for="nombre">Nombre de la joya</label>
                        <input type="text" id="nombre" name="nombre" required placeholder="Ej: Anillo de oro">
                    </div>
                    <div class="campo">
                        <label for="material">Material</label>
                        <select id="material" name="material" required style="width: 100%; padding: 13px; border: 1px solid #d5d5d5; border-radius: 6px; font-size: 14px;">
                            <option value="">Seleccionar material</option>
                            <option value="Oro">Oro</option>
                            <option value="Plata">Plata</option>
                            <option value="Acero">Acero</option>
                            <option value="Platino">Platino</option>
                        </select>
                    </div>
                    <div class="campo">
                        <label for="stock">Cantidad disponible</label>
                        <input type="number" id="stock" name="stock" required placeholder="Ej: 10">
                    </div>
                    <div class="campo">
                        <label for="precio_compra">Precio de compra</label>
                        <input type="number" step="0.01" id="precio_compra" name="precio_compra" required placeholder="Ej: 300000">
                    </div>
                    <div class="campo">
                        <label for="precio_venta">Precio de venta</label>
                        <input type="number" step="0.01" id="precio_venta" name="precio_venta" required placeholder="Ej: 450000">
                    </div>
                </div>
                
                <div class="campo" style="margin-top: 20px;">
                    <label for="descripcion">Descripcion</label>
                    <textarea id="descripcion" name="descripcion" placeholder="Describe las caracteristicas de la joya" style="width:100%; padding:13px; border:1px solid #d5d5d5; border-radius:6px;"></textarea>
                </div>

                <div class="botones-producto" style="margin-top: 20px;">
                    <a href="productos.php" class="btn-cancelar" style="text-decoration:none; display:inline-block; padding: 14px 25px;">Cancelar</a>
                    <button type="submit" class="btn-ingresar" style="width: auto; padding: 14px 25px;">Guardar producto</button>
                </div>
            </form>
        </section>
    </main>
</div>

</body>
</html>