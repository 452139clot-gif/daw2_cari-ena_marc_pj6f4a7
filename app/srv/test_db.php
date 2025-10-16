<?php
// test_db.php
// Prueba simple de conexión a la base de datos SQLite

$dbPath = __DIR__ . '/onlineOrders.db'; // Ajusta según la ubicación real de tu .db

try {
    if (!file_exists($dbPath)) {
        throw new Exception("No se encontró el archivo de base de datos en: $dbPath");
    }

    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>Conexión exitosa a la base de datos SQLite</h2>";

    $stmt = $pdo->query("SELECT * FROM orders LIMIT 5"); // mostramos solo 5 filas
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($rows) === 0) {
        echo "<p>No hay comandes registradas.</p>";
    } else {
        echo "<pre>";
        print_r($rows);
        echo "</pre>";
    }

} catch (Exception $e) {
    echo "<h2>Error al conectar o leer la base de datos</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
