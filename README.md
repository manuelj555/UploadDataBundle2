UploadDataBundle2
==================

Bundle para la gestión de carga de archivo de datos

Instalación
----

Ejecutar 

    composer require "optimeconsulting/upload-data-bundle" "~5.0@dev"

Luego de ello, registrar los bundles en el **config/bundles.php**:

```php

return [
    ...
    Manuel\Bundle\UploadDataBundle\UploadDataBundle::class => ['all' => true],
];
```

Por ultimo se debe crear la base de datos (si no se ha hecho aun) y agregar a la bd las tablas competentes al bundle, por lo que se deben ejecutar los siguientes comandos de consola:

    app\console doctrine:database:create
    console doctrine:schema:update --force

Si se prefiere, se pueden ejecutar los siguientes queries para crear las tablas necesarias:

```sql
CREATE TABLE upload_data_upload (id INT AUTO_INCREMENT NOT NULL, columns_match JSON DEFAULT NULL, filename VARCHAR(255) DEFAULT NULL, full_filename VARCHAR(255) DEFAULT NULL, file VARCHAR(255) DEFAULT NULL, config_class VARCHAR(255) NOT NULL, valids INT DEFAULT NULL, invalids INT DEFAULT NULL, total INT DEFAULT NULL, uploaded_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE upload_data_upload_action (id INT AUTO_INCREMENT NOT NULL, upload_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, status SMALLINT NOT NULL, completed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', completed TINYINT(1) DEFAULT NULL, INDEX IDX_676B5C8CCCFBA31 (upload_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE upload_data_upload_attribute (id INT AUTO_INCREMENT NOT NULL, upload_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, value JSON DEFAULT NULL, INDEX IDX_BE6193F4CCCFBA31 (upload_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE upload_data_uploaded_item (id INT AUTO_INCREMENT NOT NULL, upload_id INT DEFAULT NULL, file_row_number INT DEFAULT NULL, data JSON DEFAULT NULL, extras JSON DEFAULT NULL, errors JSON DEFAULT NULL, valid TINYINT(1) DEFAULT NULL, INDEX IDX_EB128DB1CCCFBA31 (upload_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
ALTER TABLE upload_data_upload_action ADD CONSTRAINT FK_676B5C8CCCFBA31 FOREIGN KEY (upload_id) REFERENCES upload_data_upload (id);
ALTER TABLE upload_data_upload_attribute ADD CONSTRAINT FK_BE6193F4CCCFBA31 FOREIGN KEY (upload_id) REFERENCES upload_data_upload (id);
ALTER TABLE upload_data_uploaded_item ADD CONSTRAINT FK_EB128DB1CCCFBA31 FOREIGN KEY (upload_id) REFERENCES upload_data_upload (id);
``` 

Con esto ya se ha instalado correctamente el bundle.

## Cargando Archivos

[Uso](./Resources/doc/usage.md)

