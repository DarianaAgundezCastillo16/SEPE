# SEPE — prototipo educativo ampliado

40 enfermedades, 111 síntomas, 12 categorías, 8 grupos etarios y 320 reglas de edad. Los pesos (1–5) y multiplicadores son ficticios, no probabilidades clínicas ni recomendaciones diagnósticas. Las referencias generales no validan las asociaciones nuevas.

## Instalación
- Copiar SEPE a htdocs, iniciar Apache y MySQL.
- Instalación nueva: importar sepe.sql (ATENCIÓN: elimina las tablas SEPE existentes).
- Instalación existente: respaldar la BD e importar actualizar_bd.sql; conserva el catálogo previo y reemplaza sus reglas de edad. Ejecutar uno u otro, no ambos.
- Configurar config.local.php según config.local.example.php si es necesario.
- Abrir http://localhost/SEPE/.

## Pruebas sugeridas
- Comparar edades 2 y 100 con los mismos síntomas: tos, fiebre y dificultad para respirar.
- Comparar 1/2, 5/6, 12/13, 17/18, 39/40, 64/65, 79/80.
- Buscar síntomas con y sin acentos; cambiar categorías y comprobar que las selecciones se mantienen.
- Seleccionar confusión repentina y comprobar la alerta.
- Verificar 40 enfermedades, 111 síntomas y 320 reglas en una instalación limpia.

No se ha ejecutado una prueba de integración MySQL en el entorno de edición.
