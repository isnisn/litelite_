<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// include database and object files
include_once '../config/database.php';
include_once '../objects/product.php';

// get database connection
$database = new Database();
$db = $database->getConnection();

// prepare product object
$product = new Product($db);

// get id of product to be edited
$data = json_decode(file_get_contents("php://input"));

// set ID property of product to be edited
$product->id = $data->id;

$fields = array(
    'status',
    'manufacturer_id',
    'supplier_id',
    'delivery_status_id',
    'sold_out_status_id',
    'default_category_id',
    'categories',
    'attributes',
    'keywords',
    'date_valid_from',
    'date_valid_to',
    'quantity',
    'quantity_unit_id',
    'purchase_price',
    'purchase_price_currency_code',
    'prices',
    'campaigns',
    'tax_class_id',
    'code',
    'sku',
    'mpn',
    'gtin',
    'taric',
    'dim_x',
    'dim_y',
    'dim_z',
    'dim_class',
    'weight',
    'weight_class',
    'name',
    'short_description',
    'description',
    'technical_data',
    'head_title',
    'meta_description',
    'images',
    'options',
    'options_stock',
);

foreach ($fields as $field) {
    if (isset($data[$field])) $product->data[$field] = $data[$field];
}

// update the product
if($product->update()){

    // set response code - 200 ok
    http_response_code(200);

    // tell the user
    echo json_encode(array("message" => "Product was updated."));
}

// if unable to update the product, tell the user
else{

    // set response code - 503 service unavailable
    http_response_code(503);

    // tell the user
    echo json_encode(array("message" => "Unable to update product."));
}
?>