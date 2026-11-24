SET NAMES utf8mb4;
USE sepe;
CREATE TABLE IF NOT EXISTS riesgo_edad (id INT AUTO_INCREMENT PRIMARY KEY, enfermedad_id INT NOT NULL, edad_minima TINYINT UNSIGNED NOT NULL, edad_maxima TINYINT UNSIGNED NOT NULL, factor DECIMAL(4,2) NOT NULL, fuente VARCHAR(500) NOT NULL, UNIQUE(enfermedad_id,edad_minima,edad_maxima), FOREIGN KEY(enfermedad_id) REFERENCES enfermedades(id), CHECK(edad_minima <= edad_maxima), CHECK(factor > 0)) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS riesgo_sexo (id INT AUTO_INCREMENT PRIMARY KEY, enfermedad_id INT NOT NULL, sexo ENUM('Femenino','Masculino') NOT NULL, factor DECIMAL(4,2) NOT NULL, fuente VARCHAR(500) NOT NULL, UNIQUE(enfermedad_id,sexo), FOREIGN KEY(enfermedad_id) REFERENCES enfermedades(id), CHECK(factor > 0)) ENGINE=InnoDB;
-- Multiplicadores ilustrativos, no estimaciones epidemiológicas ni valores publicados por las fuentes.
INSERT IGNORE INTO riesgo_sexo(enfermedad_id,sexo,factor,fuente) SELECT id,'Femenino',1.2,'https://medlineplus.gov/urinarytractinfections.html' FROM enfermedades WHERE nombre='Infección urinaria baja';
INSERT IGNORE INTO riesgo_sexo(enfermedad_id,sexo,factor,fuente) SELECT id,'Femenino',1.15,'https://medlineplus.gov/migraine.html' FROM enfermedades WHERE nombre='Migraña';

-- Reemplaza reglas de edad anteriores para evitar solapamientos.
START TRANSACTION;
DELETE FROM riesgo_edad WHERE enfermedad_id IN (SELECT id FROM enfermedades WHERE nombre IN ('Resfriado común','Influenza','COVID-19','Bronquitis aguda','Neumonía','Asma','Rinitis alérgica','Sinusitis aguda','Faringitis viral','Amigdalitis estreptocócica','Gastroenteritis viral','Infección urinaria baja','Migraña','Reflujo gastroesofágico','Otitis media aguda'));
INSERT INTO riesgo_edad(enfermedad_id,edad_minima,edad_maxima,factor,fuente)
SELECT id,0,5,1.30,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Resfriado común'
UNION ALL
SELECT id,6,12,1.20,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Resfriado común'
UNION ALL
SELECT id,13,17,1.10,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Resfriado común'
UNION ALL
SELECT id,18,39,1.00,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Resfriado común'
UNION ALL
SELECT id,40,64,1.00,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Resfriado común'
UNION ALL
SELECT id,65,120,1.00,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Resfriado común'
UNION ALL
SELECT id,0,5,1.30,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Influenza'
UNION ALL
SELECT id,6,12,1.15,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Influenza'
UNION ALL
SELECT id,13,17,1.00,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Influenza'
UNION ALL
SELECT id,18,39,1.00,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Influenza'
UNION ALL
SELECT id,40,64,1.10,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Influenza'
UNION ALL
SELECT id,65,120,1.40,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Influenza'
UNION ALL
SELECT id,0,5,0.85,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='COVID-19'
UNION ALL
SELECT id,6,12,0.90,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='COVID-19'
UNION ALL
SELECT id,13,17,1.00,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='COVID-19'
UNION ALL
SELECT id,18,39,1.10,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='COVID-19'
UNION ALL
SELECT id,40,64,1.20,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='COVID-19'
UNION ALL
SELECT id,65,120,1.35,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='COVID-19'
UNION ALL
SELECT id,0,5,1.10,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Bronquitis aguda'
UNION ALL
SELECT id,6,12,1.00,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Bronquitis aguda'
UNION ALL
SELECT id,13,17,0.95,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Bronquitis aguda'
UNION ALL
SELECT id,18,39,1.00,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Bronquitis aguda'
UNION ALL
SELECT id,40,64,1.15,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Bronquitis aguda'
UNION ALL
SELECT id,65,120,1.30,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Bronquitis aguda'
UNION ALL
SELECT id,0,5,1.30,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Neumonía'
UNION ALL
SELECT id,6,12,0.90,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Neumonía'
UNION ALL
SELECT id,13,17,0.90,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Neumonía'
UNION ALL
SELECT id,18,39,1.00,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Neumonía'
UNION ALL
SELECT id,40,64,1.20,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Neumonía'
UNION ALL
SELECT id,65,120,1.50,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Neumonía'
UNION ALL
SELECT id,0,5,1.15,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Asma'
UNION ALL
SELECT id,6,12,1.30,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Asma'
UNION ALL
SELECT id,13,17,1.20,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Asma'
UNION ALL
SELECT id,18,39,1.00,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Asma'
UNION ALL
SELECT id,40,64,0.95,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Asma'
UNION ALL
SELECT id,65,120,0.90,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Asma'
UNION ALL
SELECT id,0,5,0.85,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Rinitis alérgica'
UNION ALL
SELECT id,6,12,1.20,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Rinitis alérgica'
UNION ALL
SELECT id,13,17,1.30,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Rinitis alérgica'
UNION ALL
SELECT id,18,39,1.20,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Rinitis alérgica'
UNION ALL
SELECT id,40,64,1.00,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Rinitis alérgica'
UNION ALL
SELECT id,65,120,0.80,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Rinitis alérgica'
UNION ALL
SELECT id,0,5,0.75,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Sinusitis aguda'
UNION ALL
SELECT id,6,12,1.00,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Sinusitis aguda'
UNION ALL
SELECT id,13,17,1.10,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Sinusitis aguda'
UNION ALL
SELECT id,18,39,1.20,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Sinusitis aguda'
UNION ALL
SELECT id,40,64,1.10,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Sinusitis aguda'
UNION ALL
SELECT id,65,120,0.90,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Sinusitis aguda'
UNION ALL
SELECT id,0,5,1.20,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Faringitis viral'
UNION ALL
SELECT id,6,12,1.30,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Faringitis viral'
UNION ALL
SELECT id,13,17,1.15,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Faringitis viral'
UNION ALL
SELECT id,18,39,1.00,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Faringitis viral'
UNION ALL
SELECT id,40,64,0.90,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Faringitis viral'
UNION ALL
SELECT id,65,120,0.80,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Faringitis viral'
UNION ALL
SELECT id,0,5,0.80,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Amigdalitis estreptocócica'
UNION ALL
SELECT id,6,12,1.50,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Amigdalitis estreptocócica'
UNION ALL
SELECT id,13,17,1.30,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Amigdalitis estreptocócica'
UNION ALL
SELECT id,18,39,1.00,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Amigdalitis estreptocócica'
UNION ALL
SELECT id,40,64,0.80,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Amigdalitis estreptocócica'
UNION ALL
SELECT id,65,120,0.70,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Amigdalitis estreptocócica'
UNION ALL
SELECT id,0,5,1.40,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Gastroenteritis viral'
UNION ALL
SELECT id,6,12,1.20,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Gastroenteritis viral'
UNION ALL
SELECT id,13,17,1.00,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Gastroenteritis viral'
UNION ALL
SELECT id,18,39,0.95,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Gastroenteritis viral'
UNION ALL
SELECT id,40,64,1.00,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Gastroenteritis viral'
UNION ALL
SELECT id,65,120,1.20,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Gastroenteritis viral'
UNION ALL
SELECT id,0,5,0.90,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Infección urinaria baja'
UNION ALL
SELECT id,6,12,0.80,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Infección urinaria baja'
UNION ALL
SELECT id,13,17,0.95,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Infección urinaria baja'
UNION ALL
SELECT id,18,39,1.10,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Infección urinaria baja'
UNION ALL
SELECT id,40,64,1.20,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Infección urinaria baja'
UNION ALL
SELECT id,65,120,1.40,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Infección urinaria baja'
UNION ALL
SELECT id,0,5,0.50,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Migraña'
UNION ALL
SELECT id,6,12,0.80,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Migraña'
UNION ALL
SELECT id,13,17,1.10,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Migraña'
UNION ALL
SELECT id,18,39,1.40,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Migraña'
UNION ALL
SELECT id,40,64,1.20,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Migraña'
UNION ALL
SELECT id,65,120,0.80,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Migraña'
UNION ALL
SELECT id,0,5,1.00,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Reflujo gastroesofágico'
UNION ALL
SELECT id,6,12,0.80,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Reflujo gastroesofágico'
UNION ALL
SELECT id,13,17,0.90,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Reflujo gastroesofágico'
UNION ALL
SELECT id,18,39,1.10,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Reflujo gastroesofágico'
UNION ALL
SELECT id,40,64,1.30,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Reflujo gastroesofágico'
UNION ALL
SELECT id,65,120,1.30,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Reflujo gastroesofágico'
UNION ALL
SELECT id,0,5,1.50,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Otitis media aguda'
UNION ALL
SELECT id,6,12,1.30,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Otitis media aguda'
UNION ALL
SELECT id,13,17,0.90,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Otitis media aguda'
UNION ALL
SELECT id,18,39,0.70,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Otitis media aguda'
UNION ALL
SELECT id,40,64,0.70,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Otitis media aguda'
UNION ALL
SELECT id,65,120,0.70,'SIMULACION EDUCATIVA: factor ficticio' FROM enfermedades WHERE nombre='Otitis media aguda';
COMMIT;
