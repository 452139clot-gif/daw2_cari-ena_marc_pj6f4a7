<?php
// Ruta al fitxer de base de dades SQLite
$db_path = __DIR__ . '/dades.db';

// Connectar a la base de dades (es crearà si no existeix)
try {
    $db = new PDO("sqlite:$db_path");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear una taula si no existeix
    $db->exec("
        CREATE TABLE IF NOT EXISTS usuaris (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nom TEXT NOT NULL,
            email TEXT NOT NULL
        )
    ");

    // Inserir dades d'exemple
    $stmt = $db->prepare("INSERT INTO usuaris (nom, email) VALUES (:nom, :email)");
    $stmt->execute([
        ':nom' => 'Anna',
        ':email' => 'anna@example.com'
    ]);

    echo "Dades guardades correctament a dades.db 🎉";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
