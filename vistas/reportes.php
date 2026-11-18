

<?php
session_start();

// Verificar que el usuario haya iniciado sesion
if (!isset($_SESSION["usuario"]) || !isset($_SESSION["rol"])) {
    header("Location: login.php");
    exit;
}

$rol = strtolower(trim($_SESSION["rol"]));

// Reportes exclusivo para el Administrador / Jefe
if ($rol !== "jefe" && $rol !== "admin") {
    header("Location: dashboard.php");
    exit;
}

// Cerrar sesion
if (isset($_GET["cerrar_sesion"])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

// Datos de demostracion
$totalVentas = 12500000;
$cantidadVentas = 28;
$unidadesVendidas = 45;
$stockBajo = 3;

$ventasPorMes = [
    "Junio" => 1800000,
    "Julio" => 2400000,
    "Agosto" => 1950000,
    "Septiembre" => 2800000,
    "Octubre" => 3550000
];

$productos = [
    ["Anillo de oro", 18, 850000],
    ["Cadena de plata", 14, 320000],
    ["Aretes de oro", 9, 450000],
    ["Pulsera de plata", 4, 280000]
];

$productosStockBajo = [
    ["Pulsera de plata", 2],
    ["Anillo de oro", 3],
    ["Cadena de plata", 4]
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jewelry Manager - Reportes</title>

    <link rel="stylesheet" href="../css/estilos.css">

    <style>
        /* TARJETAS DE INDICADORES */
        .tarjetas-reportes {
            display: grid;
            grid-template-columns: repeat(4, minmax(160px, 1fr));
            gap: 18px;
            margin-bottom: 25px;
        }

        .tarjeta-reporte {
            background: white;
            padding: 22px;
            border-radius: 12px;
            border-left: 4px solid #c5a04b;
            box-shadow: 0 3px 12px rgba(0,0,0,0.06);
        }

        .tarjeta-reporte p {
            color: #777;
            margin-bottom: 12px;
        }

        .tarjeta-reporte h2 {
            color: #17233d;
            font-size: 25px;
            margin-bottom: 8px;
        }

        .tarjeta-reporte small {
            color: #777;
        }

        /* SECCIONES */
        .seccion-reportes {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.06);
        }

        .seccion-reportes h2 {
            color: #17233d;
            margin-bottom: 20px;
        }

        /* VENTAS MENSUALES */
        .fila-mes {
            margin-bottom: 20px;
        }

        .datos-mes {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 8px;
        }

        .barra-fondo {
            background: #eeeeee;
            height: 11px;
            border-radius: 10px;
            overflow: hidden;
        }

        .barra-valor {
            background: #c5a04b;
            height: 100%;
            border-radius: 10px;
        }

        /* TABLAS */
        .seccion-reportes table {
            width: 100%;
            border-collapse: collapse;
        }

        .seccion-reportes th,
        .seccion-reportes td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #eeeeee;
        }

        .seccion-reportes th {
            background: #17233d;
            color: white;
        }

        .estado-bajo {
            color: #b45309;
            font-weight: bold;
        }

        .nota-reportes {
            color: #777;
            font-size: 13px;
            margin-top: 12px;
        }

        @media (max-width: 1000px) {
            .tarjetas-reportes {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .tarjetas-reportes {
                grid-template-columns: 1fr;
            }

            .seccion-reportes {
                overflow-x: auto;
            }
        }
    </style>
</head>

<body>

    <div class="contenedor-dashboard">

        <!-- MENU LATERAL -->
        <aside class="menu-lateral">

            <h2>◇ Jewelry Manager</h2>

            <nav>
                <a href="dashboard.php">Inicio</a>
                <a href="productos.php">Productos</a>
                <a href="inventario.php">Inventario</a>
                <a href="ventas.php">Ventas</a>
                <a href="clientes.php">Clientes</a>
                <a href="reportes.php" class="activo">Reportes</a>
            </nav>

            <a href="reportes.php?cerrar_sesion=1" class="cerrar-sesion">Cerrar sesión</a>

        </aside>

        <!-- CONTENIDO -->
        <main class="contenido-dashboard">

            <header class="encabezado-dashboard">
                <div>
                    <h1>Reportes y estadísticas</h1>
                    <p>Consulta el comportamiento y rendimiento de tu joyería.</p>
                </div>
            </header>

            <!-- INDICADORES GENERALES -->
            <section class="tarjetas-reportes">

                <div class="tarjeta-reporte">
                    <p>Ventas acumuladas</p>
                    <h2>$<?php echo number_format($totalVentas, 0, ',', '.'); ?></h2>
                    <small>Valor de demostracion</small>
                </div>

                <div class="tarjeta-reporte">
                    <p>Ventas registradas</p>
                    <h2><?php echo $cantidadVentas; ?></h2>
                    <small>Operaciones de demostracion</small>
                </div>

                <div class="tarjeta-reporte">
                    <p>Unidades vendidas</p>
                    <h2><?php echo $unidadesVendidas; ?></h2>
                    <small>Productos de demostracion</small>
                </div>

                <div class="tarjeta-reporte">
                    <p>Stock bajo</p>
                    <h2><?php echo $stockBajo; ?></h2>
                    <small>Productos por revisar</small>
                </div>

            </section>

            <!-- FILTROS -->
            <section class="seccion-reportes">

                <h2>Consulta de reportes</h2>

                <p>
                    Selecciona un periodo para consultar la informacion
                    de ventas cuando se conecte la base de datos.
                </p>

                <br>

                <label for="fecha_inicio">Fecha inicial:</label>
                <input type="date" id="fecha_inicio">

                &nbsp;&nbsp;

                <label for="fecha_fin">Fecha final:</label>
                <input type="date" id="fecha_fin">

                <p class="nota-reportes">
                    Los filtros se habilitaron para consultar registros reales
                    durante la integracion con MySQL.
                </p>

            </section>

            <!-- VENTAS MENSUALES -->
            <section class="seccion-reportes">

                <h2>Resumen de ventas mensuales</h2>

                <?php foreach ($ventasPorMes as $mes => $valor): ?>

                    <div class="fila-mes">

                        <div class="datos-mes">
                            <strong><?php echo $mes; ?></strong>

                            <span>
                                $<?php echo number_format($valor, 0, ',', '.'); ?>
                            </span>
                        </div>

                        <div class="barra-fondo">
                            <div class="barra-valor"
                                style="width: <?php echo ($valor / 3550000) * 100; ?>%;">
                            </div>
                        </div>

                    </div>

                <?php endforeach; ?>

            </section>

            <!-- PRODUCTOS MAS VENDIDOS -->
            <section class="seccion-reportes">

                <h2>Productos mas vendidos</h2>

                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Unidades vendidas</th>
                            <th>Precio unitario</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($productos as $producto): ?>

                            <tr>
                                <td><?php echo htmlspecialchars($producto[0]); ?></td>
                                <td><?php echo $producto[1]; ?></td>
                                <td>
                                    $<?php echo number_format($producto[2], 0, ',', '.'); ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>
                </table>

            </section>

            <!-- PRODUCTOS CON STOCK BAJO -->
            <section class="seccion-reportes">

                <h2>Productos con stock bajo</h2>

                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Existencias</th>
                            <th>Estado</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($productosStockBajo as $producto): ?>

                            <tr>
                                <td><?php echo htmlspecialchars($producto[0]); ?></td>
                                <td><?php echo $producto[1]; ?></td>
                                <td class="estado-bajo">Stock bajo</td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>
                </table>

            </section>

        </main>

    </div>

</body>
</html>