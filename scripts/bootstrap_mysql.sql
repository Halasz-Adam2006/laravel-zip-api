-- Bootstrap MySQL schema and load postal codes from iranyitoszamok.csv
-- CSV absolute path
-- Using local path for this workspace
-- /Users/adamhalasz/Library/CloudStorage/GoogleDrive-aszaszinking@gmail.com/Other computers/Saját laptop/prog/laravel feladat/laravel-zip-api/app/iranyitoszamok.csv

SET NAMES utf8mb4;
SET SESSION sql_mode = 'STRICT_ALL_TABLES';
SET GLOBAL local_infile = 1;

CREATE DATABASE IF NOT EXISTS zip_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE zip_api;

DROP TABLE IF EXISTS postal_codes;
DROP TABLE IF EXISTS counties;
DROP TABLE IF EXISTS raw_postal_codes;

CREATE TABLE counties (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY counties_name_unique (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE postal_codes (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  zip VARCHAR(10) NOT NULL,
  city VARCHAR(255) NOT NULL,
  county_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  KEY postal_codes_zip_index (zip),
  UNIQUE KEY postal_codes_zip_city_unique (zip, city),
  CONSTRAINT postal_codes_county_id_foreign FOREIGN KEY (county_id) REFERENCES counties (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Staging table to load the raw CSV rows
CREATE TABLE raw_postal_codes (
  zip VARCHAR(10),
  city VARCHAR(255),
  county VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Load CSV (header has 3 lines). Adjust LINES TERMINATED BY if your file uses \r\n.
LOAD DATA LOCAL INFILE '/Users/adamhalasz/Library/CloudStorage/GoogleDrive-aszaszinking@gmail.com/Other computers/Saját laptop/prog/laravel feladat/laravel-zip-api/app/iranyitoszamok.csv'
INTO TABLE raw_postal_codes
CHARACTER SET utf8mb4
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 3 LINES
(zip, city, county);

-- Deduplicate counties
INSERT INTO counties (name, created_at, updated_at)
SELECT DISTINCT county, NOW(), NOW()
FROM raw_postal_codes
WHERE county IS NOT NULL AND county <> ''
ON DUPLICATE KEY UPDATE
  updated_at = VALUES(updated_at);

-- Insert postal codes with FK to counties
INSERT INTO postal_codes (zip, city, county_id, created_at, updated_at)
SELECT rpc.zip, rpc.city, c.id, NOW(), NOW()
FROM raw_postal_codes rpc
JOIN counties c ON c.name = rpc.county
WHERE rpc.zip IS NOT NULL AND rpc.zip <> '' AND rpc.city IS NOT NULL AND rpc.city <> ''
ON DUPLICATE KEY UPDATE
  city = VALUES(city),
  county_id = VALUES(county_id),
  updated_at = VALUES(updated_at);

DROP TABLE raw_postal_codes;
