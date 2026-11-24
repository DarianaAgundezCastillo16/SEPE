# SEPE 
Prototipo educativo de evaluación de síntomas. PHP 8 y MySQL/MariaDB con PDO MySQL.


El algoritmo ponderado y los datos originales se conservan. Las puntuaciones no son probabilidades médicas. No es un producto clínicamente validado. La opción PDF usa la impresión del navegador. No almacena historial. Las fuentes originales no se han revalidado.

## Instalación local

1. Inicia Apache y MySQL en XAMPP.
2. Coloca la carpeta SEPE-main en htdocs.
3. Importa sepe.sql en phpMyAdmin solo si necesitas inicializar la base: elimina y recrea las tablas existentes.
4. Si MySQL requiere contraseña, copia config.local.example.php como config.local.php y ajusta las credenciales. No subas ese archivo a GitHub.
5. Abre http://localhost/SEPE-main/ en tu navegador.
