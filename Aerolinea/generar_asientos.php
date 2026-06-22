<?php
// 1. CONEXIÓN A LA BASE DE DATOS
$host = 'localhost';
$db   = 'aerolinea'; // Tu base de datos se llama aerolinea según tu SQL
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
     $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (\PDOException $e) {
     die("Error de conexión: " . $e->getMessage());
}

echo "<h2>🛠️ Iniciando generación automática...</h2>";

// 2. TRAER TUS 10 AVIONES
$stmtAviones = $pdo->query("SELECT id_avion, modelo, capacidad FROM aviones");
$aviones = $stmtAviones->fetchAll(PDO::FETCH_ASSOC);

// 3. PREPARAR EL INSERT PARA TU TABLA 'asientos_avion'
$stmtInsert = $pdo->prepare("
    INSERT INTO asientos_avion (id_avion, numero_asiento, categoria, cargo_extra) 
    VALUES (?, ?, ?, ?)
");

$asientos_por_fila = 6;
$letras = ['A', 'B', 'C', 'D', 'E', 'F'];

foreach ($aviones as $avion) {
    $id_avion = $avion['id_avion'];
    $capacidad = $avion['capacidad'];
    
    // Calcula cuántas filas necesita el avión (Ej: 396 / 6 = 66 filas)
    $total_filas = ceil($capacidad / $asientos_por_fila);
    $asientos_creados = 0;

    echo "Procesando <strong>{$avion['modelo']}</strong>... <br>";

    for ($fila = 1; $fila <= $total_filas; $fila++) {
        foreach ($letras as $letra) {
            
            // Si ya completamos los asientos que entran en este avión, pasamos al siguiente
            if ($asientos_creados >= $capacidad) {
                break 2; 
            }

            $numero_asiento = $fila . $letra; // Crea "1A", "1B", "66F", etc.

            // REGLAS AUTOMÁTICAS DE CATEGORÍAS (Configuración estándar)
            if ($fila <= 3) {
                $categoria = 'Business';
                $cargo_extra = 8500.00;
            } elseif ($fila >= 4 && $fila <= 8) {
                $categoria = 'Premium Economy';
                $cargo_extra = 4500.00;
            } elseif ($fila == 12 || $fila == 13) { 
                $categoria = 'Estándar Preferente';
                $cargo_extra = 2000.00;
            } else {
                $categoria = 'Estándar';
                $cargo_extra = 0.00;
            }

            // Guarda el asiento individual en la BD
            $stmtInsert->execute([$id_avion, $numero_asiento, $categoria, $cargo_extra]);
            $asientos_creados++;
        }
    }
    echo "✅ ¡Éxito! Se crearon $asientos_creados asientos fijos.<br><br>";
}

echo "<h3>🎉 ¡PROCESO TERMINADO! Ya no tenés que cargar nada más a mano.</h3>";
echo "</div>";
include_once 'includes/footer.php';
echo "</body></html>";
?>