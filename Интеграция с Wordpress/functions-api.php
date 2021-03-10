<?php

// Параметры подключения к API Paloma
function paloma_api_config($param) {
    $conf = array(
        'api_url' => 'https://proxy.paloma365.com/company/api', // Путь к API Paloma
        'auth_key' => '',                                       // Берётся из настройки аккаунта Paloma
        'cod_order_status' => 'processing',                     // 'processing' или 'pending'
    );
    return $conf[$param];
}

// Статус заказа при создании, при выборе оплаты после доставки
add_filter('woocommerce_cod_process_payment_order_status', 'change_cod_payment_order_status', 10, 2);
function change_cod_payment_order_status() {
    return paloma_api_config('cod_order_status');
}

// Включаем/отключаем счётчик статуса "Обработка"
add_filter('woocommerce_include_processing_order_count_in_menu', 'exclude_processing_order_count');
function exclude_processing_order_count() {
    return paloma_api_config('cod_order_status') == 'processing';
}

// Включаем/отключаем счётчик статусов "В ожидании" и "На удержании"
add_action('admin_head', 'menu_pending_order_count');
function menu_pending_order_count() {
    if (paloma_api_config('cod_order_status') == 'pending') {
        global $submenu;
        if (isset($submenu['woocommerce'])) {
            unset($submenu['woocommerce'][0]);
            if (current_user_can('manage_woocommerce')) {
                $order_count  = wc_orders_count('pending');
                $order_count += wc_orders_count('on-hold');
                if ($order_count) {
                    foreach ($submenu['woocommerce'] as $key => $menu_item) {
                        if (0 === strpos($menu_item[0], _x('Orders', 'Admin menu name', 'woocommerce'))) {
                            $submenu['woocommerce'][$key][0] .= ' <span class="awaiting-mod update-plugins count-' . esc_attr( $order_count ) . '"><span class="pending-count">' . number_format_i18n( $order_count ) . '</span></span>';
                            break;
                        }
                    }
                }
            }
        }
    }
}

// Отправка заказа при изменении статуса в "processing"
add_action('woocommerce_order_status_processing', 'on_order_status_processing');
function on_order_status_processing($order_id) {
    $order = wc_get_order($order_id);
    if (!empty($order)) {
        $order_data = $order->get_data();
        $items = $order->get_items();
        $data = prepare_order_data_to_paloma($order_id, $order_data, $items);
        $result = send_order_to_paloma($data);
        $status = $result['body']['status'];
        if ($status) {
            $order->update_status($status);
        }
         // $log = wc_get_logger();
         // $log->info(json_encode($data, JSON_UNESCAPED_UNICODE));
         // $log->info(json_encode($result, JSON_UNESCAPED_UNICODE));
    }
}

// Подготовка данных заказа перед отправкой
function prepare_order_data_to_paloma($order_id, $order_data, $items) {
    $total_price = 0;
    $order_items = array();
    foreach ($items as $item) {
        $product = $item->get_product();
        $item_data = $item->get_data();
        $total_price += $item_data['total'];
        $order_items[] = array(
            'object_id' => $product->get_sku(),
            'price' => (float) $product->get_price(),
            'count' => (float) $item_data['quantity'],
        );
    };
    $billing = $order_data['billing'];
    return array(
        'order_id' => $order_id, 
        'object_id' => 1,
        'name' => $billing['first_name'] . ' ' . $billing['last_name'],
        'address' => $billing['postcode'] . ', ' . $billing['country'] . ', ' . $billing['state'] . ', ' . $billing['city'] . ', ' . $billing['address_1'],
        'phone' => $billing['phone'],
        'email' => $billing['email'],
        'person_amount' => 1,
        'comment' => null,
        'date' => date('Y-m-d H:i:s'),
        'total_price' => (float) $total_price,
        // 'total_delivery' => (float) $order_data['shipping_total'],
        'discount_amount' => 0,
        'is_cash' => null,
        'order_items' => $order_items,
    );
}

// Отправка данных о заказе
function send_order_to_paloma($data) {
    $authkey = paloma_api_config('auth_key');
    $url  = paloma_api_config('api_url');
    $url .= "/?class=Wordpress&method=order&authkey=$authkey";
    $header = array('Content-Type: application/json; charset=utf-8');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $body = curl_exec($ch);
    $result = array(
        'body' => json_decode($body, true),
    );
    if (!curl_errno($ch)) {
        $result['status_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['content_type'] = curl_getInfo($ch, CURLINFO_CONTENT_TYPE);
    }
    return $result;
};

// Получение и обновление меню
// https://wordpress.yourdomain.com/wp-json/wc/v2/menu
add_action( 'rest_api_init', 'store_menu_register_route' );
function store_menu_register_route() {
    register_rest_route('wc/v2', 'menu', array(
        'methods' => 'PUT',
        'callback' => 'update_menu_from_paloma',
        'permission_callback' => function() {
            return wc_rest_check_manager_permissions('attributes', 'edit');
        }
    ));
}
// Обработчик получения и обновления меню
function update_menu_from_paloma($request) {
    $data = $request->get_json_params();
    $prod_args = array(
        'orderby' => 'id',
        'posts_per_page' => -1,
    );
    if ($data && isset($data['foodtypes'])) {
        // Получаем имена картинок с id
        $img_args = array(
            'post_type'      => 'attachment',
            'post_mime_type' => 'image',
            'post_status'    => 'inherit',
            'posts_per_page' => -1,
        );
        $query_images = new WP_Query($img_args);
        $images = array();
        foreach ($query_images->posts as $image) {
            $images[$image->post_title]['id'] = $image->ID;
        }
        // Подгружаем категории для изменений
        $cat_args = array(
             'taxonomy'     => 'product_cat',
             'orderby'      => 'slug',
             'hide_empty'   => 0,
        );
        $categories = get_categories($cat_args);
        $cats = array();
        foreach ($categories as $category) {
            $cats[$category->slug] = $category;
        }
        foreach ($data['foodtypes'] as $foodtype) {
            $object_id = $foodtype['object_id'];
            $term_id = null;
            if (in_array($object_id, array_keys($cats))) {
                $category = $cats[$object_id];
                $term_id = $category->term_id;
                if (trim($category->name) != trim($foodtype['title'])) {
                    wp_update_term($term_id, 'product_cat', array('slug' => $object_id, 'name' => $foodtype['title']));
                }
            } else {
                $term = wp_insert_term($foodtype['title'], 'product_cat', array('slug' => $object_id));
                if ($term) {
                    $term_id = $term['term_id'];
                }
            }
            if ($term_id && isset($foodtype['image'])) {
                $image_url = trim($foodtype['image']);
                $image_name = basename($image_url);
                if (in_array($image_name, array_keys($images))) {
                    $image_id = $images[$image_name]['id'];
                } else {
                    $image_id = upload_image($image_url);
                }
                update_woocommerce_term_meta($term_id, 'thumbnail_id', $image_id);
            }
        }
        // Повтроно подгружаем категории
        $categories = get_categories($cat_args);
        $cats = array();
        foreach ($categories as $category) {
            $cats[$category->slug] = $category;
        }
        // Подгружаем продукты для изменений
        $products = wc_get_products($prod_args);
        $prods = array();
        foreach ($products as $product) {
            $prods[$product->get_sku()] = $product;
        }
        foreach ($data['foodtypes'] as $foodtype) {
            $category = $cats[$foodtype['object_id']];
            foreach ($foodtype['foods'] as $food) {
                $object_id = $food['object_id'];
                if (in_array($object_id, array_keys($prods))) {
                    $product = $prods[$object_id];
                } else {
                    $product = new \WC_Product();
                }
                $product->set_name($food['title']);
                $product->set_sku($food['object_id']);
                $product->set_price($food['price']);
                $product->set_regular_price($food['price']);
                $product->set_description($food['description']);
                $product->set_category_ids(array($category->term_id));
                $product->set_catalog_visibility($food['in_menu'] == 1 ? 'visible' : 'hidden');
                $image_url = trim($food['image']);
                if ($image_url) {
                    $image_name = basename($image_url);
                    if (in_array($image_name, array_keys($images))) {
                        $image_id = $images[$image_name]['id'];
                    } else {
                        $image_id = upload_image($image_url);
                    }
                    $product->set_image_id($image_id);
                }
                $product->save();
            }
        }
    }
    // Пересчитаем продукты в категориях
    $product_cats = get_terms('product_cat', array('hide_empty' => false, 'fields' => 'id=>parent'));
    _wc_term_recount($product_cats, get_taxonomy('product_cat'), true, false);

    // Подгружаем продукты для ответа
    $products = wc_get_products($prod_args);
    $prods = array();
    foreach ($products as $product) {
        $prods[] = array(
            'id' => $product->get_id(),
            'name' => $product->get_name(),
            'description' => $product->get_description(),
            'sku' => $product->get_sku(),
            'price' => $product->get_price(),
            'categories' => $product->get_category_ids(),
            'image_id' => $product->get_image_id(),
            'visibility' => $product->get_catalog_visibility(),
        );
    }
    $response = rest_ensure_response(array(
//        'products' => $prods,
        'result' => 'ok',
        'message' => 'Список товаров обновлён!',
    ));
    $response->set_status(200);
    return $response;
}

function upload_image($image_url) {
    $filename = basename($image_url);
    $upload_dir = wp_upload_dir();
    $file = $upload_dir['basedir'] . '/' . $filename;
    $image_data = file_get_contents($image_url);
    file_put_contents($file, $image_data);
    $wp_filetype = wp_check_filetype($filename, null);
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => $filename,
        'post_content' => '',
        'post_status' => 'inherit'
    );
    $attach_id = wp_insert_attachment($attachment, $file);
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $file);
    wp_update_attachment_metadata($attach_id, $attach_data);
    return $attach_id;
}
