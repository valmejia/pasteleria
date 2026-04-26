<?php
echo "<h1>Verificación de rutas</h1>";
echo "<h2>Ruta actual: " . __DIR__ . "</h2>";

// Verificar si existe el dashboard
$dashboardPath = __DIR__ . "/pages/admin/dashboard.php";
echo "<p>Buscando en: " . $dashboardPath . "</p>";

if(file_exists($dashboardPath)) {
    echo "<p style='color:green'>✅ El archivo dashboard.php EXISTE</p>";
    echo "<p>Puedes acceder directamente en: <a href='pages/admin/dashboard.php'>pages/admin/dashboard.php</a></p>";
} else {
    echo "<p style='color:red'>❌ El archivo dashboard.php NO EXISTE en esa ubicación</p>";
}

// Listar carpetas
echo "<h2>Estructura de carpetas:</h2>";
echo "<pre>";
echo "📁 Raíz:\n";
$items = scandir(__DIR__);
foreach($items as $item) {
    if($item != '.' && $item != '..') {
        if(is_dir(__DIR__ . "/" . $item)) {
            echo "📁 $item/\n";
            // Si es pages, mostrar su contenido
            if($item == 'pages') {
                $pagesItems = scandir(__DIR__ . "/pages");
                foreach($pagesItems as $page) {
                    if($page != '.' && $page != '..') {
                        if(is_dir(__DIR__ . "/pages/" . $page)) {
                            echo "   📁 pages/$page/\n";
                            // Mostrar contenido de admin
                            if($page == 'admin') {
                                $adminItems = scandir(__DIR__ . "/pages/admin");
                                foreach($adminItems as $adminFile) {
                                    if($adminFile != '.' && $adminFile != '..') {
                                        echo "      📄 $adminFile\n";
                                    }
                                }
                            }
                        } else {
                            echo "   📄 $page\n";
                        }
                    }
                }
            }
        } else {
            echo "📄 $item\n";
        }
    }
}
echo "</pre>";
?>