<?php
require_once('/home/customer/www/depodental.cl/public_html/wp-load.php');


function generar_xml_personalizado() {
    // Crear un objeto XML
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><productos></productos>');
    libxml_use_internal_errors(true);

    // Obtener todos los productos personalizados
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
    );

    $productos = get_posts($args);

    // Verificar si hay productos
    if ($productos) {
        echo 'Total de productos obtenidos: ' . count($productos) . PHP_EOL;

        // Recorrer cada producto y agregar los datos al XML
        foreach ($productos as $producto) {
            // Verificar si el producto tiene la etiqueta '3M'
            $etiquetas_producto = get_the_terms($producto->ID, 'product_tag');
            $tiene_etiqueta_3M = false;
            $marca_producto = ''; // Variable para almacenar la marca

            if (!empty($etiquetas_producto)) {
                foreach ($etiquetas_producto as $etiqueta) {
                    if (stripos($etiqueta->name, '3M') !== false) {
                        $tiene_etiqueta_3M = true;
                        $marca_producto = $etiqueta->name; // Asignar la etiqueta como marca
                        break;
                    }
                }
            }

            // Si el producto tiene la etiqueta '3M', procesar el producto
            if ($tiene_etiqueta_3M) {
                // Resto del código para agregar el producto al XML
                $item = $xml->addChild('producto');
                $item->addChild('marca', $marca_producto); // Agregar la marca al XML
                $item->addChild('titulo', $producto->post_title);
                $item->addChild('precio', get_post_meta($producto->ID, '_price', true));
                $item->addChild('sku', get_post_meta($producto->ID, '_sku', true));
                $item->addChild('permalink', get_permalink($producto->ID));
                $item->addChild('stock_status', $producto->stock_status);
                $item->addChild('categorias', implode(', ', wp_get_post_terms($producto->ID, 'product_cat', array('fields' => 'names'))));
            }
        }
    } else {
        echo 'No se encontraron productos.' . PHP_EOL;
    }

    // Resto del código para guardar el XML

    // Guardar el XML en el servidor
    $ruta_archivo = '/TU_PATH/Nombre_Archivo.xml';
    $ruta_completa = ABSPATH . $ruta_archivo;

    if (!is_dir(dirname($ruta_completa))) {
        mkdir(dirname($ruta_completa), 0755, true);
    }

    $xml->asXML($ruta_completa);
    $errors = libxml_get_errors();
    libxml_clear_errors();

    if (!empty($errors)) {
        foreach ($errors as $error) {
            error_log('Error XML: ' . $error->message); // Registrar errores en un archivo de registro
            echo 'Error XML: ' . $error->message . PHP_EOL; // Mostrar errores en pantalla
        }
    }

    echo 'Script ejecutado correctamente.';
}

// Acciona la función manualmente para probar
generar_xml_personalizado();
?>
