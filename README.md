# SEPE 3.1 — Ponderación demográfica
Prototipo educativo PHP/MySQL. No es un diagnóstico ni estima probabilidades clínicas.

## Instalación
- Copiar SEPE a htdocs, iniciar Apache y MySQL.
- Instalación nueva: importar sepe.sql (reinicia tablas; respaldar antes).
- Base existente: importar actualizar_bd.sql; reemplaza las reglas de edad de las 15 enfermedades originales, conserva síntomas, enfermedades y reglas de sexo existentes.
- Si MySQL tiene contraseña, copiar config.local.example.php a config.local.php y configurar credenciales.
- Abrir http://localhost/SEPE/.

## Algoritmo
Edad entera 0–120 y sexo Femenino/Masculino obligatorios. Puntuación = base por síntomas × factor edad × factor sexo. Se ordenan todas las coincidencias antes de elegir cinco. Los 90 factores de edad son ficticios, exclusivamente para demostración académica; las fuentes médicas del catálogo no validan sus valores. Se conservan los factores de sexo originales, también experimentales. Sin regla se usa 1.00 y se avisa en resultados si falta la regla de edad. No se almacenan datos del paciente.

## Prueba manual
Seleccionar los mismos síntomas y sexo con edades 2 y 100. Por ejemplo, otitis media: factor 1.50 frente a 0.70; neumonía: 1.30 frente a 1.50. Los puntos y el orden pueden cambiar; no se exige que toda edad produzca una puntuación distinta.

## Matriz experimental
| Enfermedad | 0–5 | 6–12 | 13–17 | 18–39 | 40–64 | 65–120 |
|---|---|---|---|---|---|---|
| Resfriado común | 1.30 | 1.20 | 1.10 | 1.00 | 1.00 | 1.00 |
| Influenza | 1.30 | 1.15 | 1.00 | 1.00 | 1.10 | 1.40 |
| COVID-19 | 0.85 | 0.90 | 1.00 | 1.10 | 1.20 | 1.35 |
| Bronquitis aguda | 1.10 | 1.00 | 0.95 | 1.00 | 1.15 | 1.30 |
| Neumonía | 1.30 | 0.90 | 0.90 | 1.00 | 1.20 | 1.50 |
| Asma | 1.15 | 1.30 | 1.20 | 1.00 | 0.95 | 0.90 |
| Rinitis alérgica | 0.85 | 1.20 | 1.30 | 1.20 | 1.00 | 0.80 |
| Sinusitis aguda | 0.75 | 1.00 | 1.10 | 1.20 | 1.10 | 0.90 |
| Faringitis viral | 1.20 | 1.30 | 1.15 | 1.00 | 0.90 | 0.80 |
| Amigdalitis estreptocócica | 0.80 | 1.50 | 1.30 | 1.00 | 0.80 | 0.70 |
| Gastroenteritis viral | 1.40 | 1.20 | 1.00 | 0.95 | 1.00 | 1.20 |
| Infección urinaria baja | 0.90 | 0.80 | 0.95 | 1.10 | 1.20 | 1.40 |
| Migraña | 0.50 | 0.80 | 1.10 | 1.40 | 1.20 | 0.80 |
| Reflujo gastroesofágico | 1.00 | 0.80 | 0.90 | 1.10 | 1.30 | 1.30 |
| Otitis media aguda | 1.50 | 1.30 | 0.90 | 0.70 | 0.70 | 0.70 |
