UploadDataBundle2
==================

Bundle para la gestión de carga de archivo de datos

Instalación
----

Agregar al composer.json:

```json
"require" : {
    "manuelj555/upload-data-bundle": "~3.0@dev",
}
```

Y ejecutar 

    composer update 

Luego de ello, registrar los bundles en el **AppKernel.php**:

```php
public function registerBundles()
{
    $bundles = array(
        ...
        new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(), //solo si no esta antes agregado
        new Ku\AjaxBundle\KuAjaxBundle(), //solo si no esta antes agregado
        new Manuel\Bundle\UploadDataBundle\UploadDataBundle(),
    );
    
    ...
}
```

En el **app/config/routing.yml** agregar:

```yaml
_upload_data:
    resource: "@UploadDataBundle/Resources/config/routing.yml"
    prefix:   /uploads
``` 

Por ultimo se debe crear la base de datos (si no se ha hecho aun) y agregar a la bd las tablas competentes al bundle, por lo que se deben ejecutar los siguientes comandos de consola:

    app\console doctrine:database:create
    console doctrine:schema:update --force
        
Otra opción para no ejecutar el comando del update es ejecutar el siguiente SQL para Mysql:

```sql
CREATE TABLE `upload_data_upload`  (`id` int(11) NOT NULL AUTO_INCREMENT, `filename` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, `full_filename` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, `file` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, `type` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, `valids` int(11) NULL DEFAULT NULL, `invalids` int(11) NULL DEFAULT NULL, `total` int(11) NULL DEFAULT NULL, `uploadedAt` datetime(0) NULL DEFAULT NULL, PRIMARY KEY (`id`) USING BTREE ) ENGINE = InnoDB AUTO_INCREMENT = 400 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Compact;
CREATE TABLE `upload_data_upload_action`  (`id` int(11) NOT NULL AUTO_INCREMENT, `upload_id` int(11) NULL DEFAULT NULL, `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, `status` smallint(6) NOT NULL, `completedAt` datetime(0) NULL DEFAULT NULL, `completed` tinyint(1) NOT NULL, PRIMARY KEY (`id`) USING BTREE, INDEX `IDX_676B5C8CCCFBA31`(`upload_id`) USING BTREE, CONSTRAINT `FK_676B5C8CCCFBA31` FOREIGN KEY (`upload_id`) REFERENCES `upload_data_upload` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT ) ENGINE = InnoDB AUTO_INCREMENT = 2181 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Compact;
CREATE TABLE `upload_data_upload_attribute`  (`id` int(11) NOT NULL AUTO_INCREMENT, `upload_id` int(11) NULL DEFAULT NULL, `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, `label` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, `value` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL, `is_array` tinyint(1) NULL DEFAULT NULL, PRIMARY KEY (`id`) USING BTREE, INDEX `IDX_BE6193F4CCCFBA31`(`upload_id`) USING BTREE, CONSTRAINT `FK_BE6193F4CCCFBA31` FOREIGN KEY (`upload_id`) REFERENCES `upload_data_upload` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT ) ENGINE = InnoDB AUTO_INCREMENT = 1676 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Compact;
CREATE TABLE `upload_data_uploaded_item`  (`id` int(11) NOT NULL AUTO_INCREMENT, `upload_id` int(11) NULL DEFAULT NULL, `data` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL COMMENT '(DC2Type:array)', `extras` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL COMMENT '(DC2Type:json_array)', `errors` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL COMMENT '(DC2Type:array)', `isValid` tinyint(1) NULL DEFAULT NULL, `status` int(11) NULL DEFAULT NULL, PRIMARY KEY (`id`) USING BTREE, INDEX `IDX_EB128DB1CCCFBA31`(`upload_id`) USING BTREE, CONSTRAINT `FK_EB128DB1CCCFBA31` FOREIGN KEY (`upload_id`) REFERENCES `upload_data_upload` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT ) ENGINE = InnoDB AUTO_INCREMENT = 522460 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Compact;
```

Además ejecutar el comando 
    
    app/console assets:install

Con esto ya se ha instalado correctamente el bundle.


Configurando el bundle AjaxBundle:
___________

 ver: [AjaxBundle](https://github.com/manuelj555/AjaxBundle#flash-messages)

## Cargando Archivos

[Uso](./Resources/doc/usage.md)

