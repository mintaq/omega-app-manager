<?php

/**
 * Description of Helper File
 * @author : Windy
 * @since Dec 27, 2019
*/
 

date_default_timezone_set('UTC');
require 'vendor/autoload.php';
require './config.php';


// DATABASE 
function db_query($query_string) {
    global $dbr;
    $result = mysqli_query($dbr, $query_string);
    if (!$result) {
         echo ('Query Error' . $query_string); die();
    }
    return $result;
} 
function db_insert($table, $data) {
    global $dbw;
    $fields = "(" . implode(", ", array_keys($data)) . ")";
    $values = "";

    foreach ($data as $field => $value) {
        if ($value === NULL) {
            $values .= "NULL, ";
        } elseif (is_numeric($value)) {
            $values .= $value . ", ";
        } elseif ($value == 'true' || $value == 'false') {
            $values .= $value . ", ";
        } else {
            $values .= "'" . addslashes($value) . "', ";
        }
    }
    $values = substr($values, 0, -2); 
    db_query("
            INSERT INTO $table $fields
            VALUES($values)
        ");
    return mysqli_insert_id($dbw);
} 
function db_update($table, $data, $where) {
    global $dbw;
    $sql = "";
    foreach ($data as $field => $value) {
        if ($value === NULL) {
            $sql .= "$field=NULL, ";
        } elseif (is_numeric($value)) {
            $sql .= "$field=" . addslashes($value) . ", ";
        } elseif ($value == 'true' || $value == 'false') {
            $sql .= "$field=" . addslashes($value) . ", ";
        }  else
            $sql .= "$field='" . addslashes($value) . "', ";
    }
    $sql = substr($sql, 0, -2);
    db_query("
        UPDATE `$table`
        SET $sql
        WHERE $where
    ");
    return mysqli_affected_rows($dbw);
} 
function db_delete($table, $where) {
    global $dbw;
    $query_string = "DELETE FROM " . $table . " WHERE $where";
     db_query($query_string);
    return mysqli_affected_rows($dbw);
}
function db_duplicate($table,$data,$content_duplicate){
    global $dbw;
    $fields = "(" . implode(", ", array_keys($data)) . ")";
    $values = "(";
    foreach ($data as $field => $value) {
        if ($value === NULL)
            $values .= "NULL, ";
        elseif ($value === TRUE || $value === FALSE)
            $values .= "$value, ";
        else
            $values .= "'" . addslashes($value) . "',";
    }  
    $values = rtrim($values,',');
    $values .= ")"; 
    $query = "INSERT INTO $table  $fields  VALUES $values ON DUPLICATE KEY UPDATE $content_duplicate ;"; 
    db_query($query); 
    return  mysqli_insert_id($dbw); 
}  
function db_fetch_array($query_string) {
    global $dbr;
    $result = array();
    $mysqli_result = db_query($query_string);
    while ($row = mysqli_fetch_assoc($mysqli_result)) {
        $result[] = $row;
    }
    mysqli_free_result($mysqli_result);
    if(!is_array($result)){
        $result = array();
    }
    return $result;
} 
function db_fetch_row($query_string) {
    global $dbr;
    $result = array();
    $mysqli_result = db_query($query_string);
    $result = mysqli_fetch_assoc($mysqli_result);
    mysqli_free_result($mysqli_result);
    if(!is_array($result)){
        $result = array();
    }
    return $result;
}
 
// SHOPIFY
 
function deleteWebhook($shopify,$id){
    $result = $shopify("DELETE", "/admin/webhooks/".$id.".json");
    return $result;
}
function createWebhook($shopify,$link){
    $webhook = array(
        "webhook" => array(
            "topic" => "products/create",
            "address" => $link,
            "format" => "json"
        )
    ); 
    $result = $shopify("POST", "/admin/webhooks.json",$webhook);
    return $result;
}
function editWebhook($shopify,$link,$id){
    $webhook = array(
        "webhook" => array(
            "id"    => $id,
            "topic" => "products/create",
            "address" => $link,
            "format" => "json"
        )
    ); 
    $result = $shopify("PUT", "/admin/webhooks.json",$webhook);
    return $result;
}
function getListWebhook($shopify){
    $result = $shopify("GET", "/admin/webhooks.json");
    return $result;
}
function shopifyInit($dbr, $shop, $appId) {
    $select_settings = $dbr->query("SELECT * FROM tbl_appsettings WHERE id = $appId");
    $app_settings = $select_settings->fetch_object(); 
    $shop_data1 = $dbr->query("select * from tbl_usersettings where store_name = '" . $shop . "' and app_id = $appId");
    $shop_data = $shop_data1->fetch_object();
    if(!isset($shop_data->access_token)){
        die("Please check the store: ".$shop." seems to be incorrect access_token.");
    }
    $shopify = shopify_api\client( 
        $shop, $shop_data->access_token, $app_settings->api_key, $app_settings->shared_secret
    );
    return $shopify;
}
function getProductInPage($shopify,$since_id = 0,$limit = 50,$fields = "id,title,handle,image"){  
    $products =[];
    $products = $shopify("GET", "/admin/products.json?since_id=$since_id&limit=$limit&fields=$fields"); 
    if(!isset($products) || !is_array($products)) return []; 
    return $products;
}
function getProductByCollectionID($shopify,$collectionID,$limit=250,$fields = "id,title,handle,variants"){
    if(!isset($collectionID)) return [];
    $products = $shopify("GET", "/admin/products.json?collection_id=$collectionID&limit=$limit&fields=$fields");
    if(!isset($products) || !is_array($products)) return [];
    return $products; 
}
function getProductByProductID($shopify,$idProduct,$fields = "id,variants"){
    if(!isset($idProduct)) return [];
    $product = $shopify("GET", "/admin/products/".$idProduct.".json?fields=$fields"); 
    return $product; 
}
function getAllTag($shopify){
    $listAllTag = [];
    $result = $shopify('GET','/admin/products/tags.json'); 
    if($result['tags']){
        $listAllTag = $result;
    }
    return $listAllTag;
}
function getAllCollection($shopify,$limit=250,$fields="id,title,handle"){
    $collections = [];
    $collections_smart =  [];
    $collections_custom =  []; 
    $smart  = $shopify("GET","/admin/smart_collections.json?published_status=published&limit=$limit&fields=$fields");
    $custom = $shopify("GET","/admin/custom_collections.json?published_status=published&limit=$limit&fields=$fields");
    if(is_array($smart)) $collections_smart = $smart;
    if(is_array($custom)) $collections_custom = $custom; 
    $collections = array_merge($collections_smart,$collections_custom);
    return $collections;
}
function getVariantByProductID($shopify,$product_id){
    if(!isset($idProduct)) return [];
    $variants = $shopify("GET","admin/products/".$product_id."/variants.json");
    if(!is_array($variants)) return [];
    return $variants;
}
function getCollectionByID($shopify,$collection_id,$limit = 250,$fields = "id,title,handle"){
    if(!isset($collection_id)) return [];
    $infoCollectionByID = [];
    $infoCollectionByID = $shopify("GET","/admin/custom_collections/".$collection_id.".json?fields=$fields&limit=$limit");
    if(is_array($infoCollectionByID) && count($infoCollectionByID) > 0){
        return $infoCollectionByID; 
    }else{
        $infoCollectionByID = $shopify("GET","/admin/smart_collections/".$collection_id.".json?fields=$fields&limit=$limit");
        if(is_array($infoCollectionByID) && count($infoCollectionByID) > 0){
            return $infoCollectionByID; 
        } 
    }
    return $infoCollectionByID;
} 
function getCountProductByCollection($collection_id,$shopify){
    if(!isset($collection_id)) return 0;
    $countProduct  = $shopify("GET", "/admin/products/count.json?collection_id=".$collection_id."&fields=id");
    return $countProduct;
} 
function getCountAllProduct($shopify){
    $counProduct  = $shopify("GET","/admin/products/count.json"); 
    return $counProduct;
}
function getPriceRule($shopify){
    $result = [];
    $result = $shopify('GET','/admin/price_rules.json');
    if(!is_array($result)) return [];
    return $result;
}
function getCustomColletionByProductID($shopify,$IDProduct){
    if(!isset($IDProduct)) return [];
    $collections = $shopify("GET", "/admin/custom_collections.json?product_id=$IDProduct"); 
    if(!is_array($collections)) return [];
    return $collections;
}
function getSmartColletionByProductID($shopify,$IDProduct){
    if(!isset($IDProduct)) return [];
    $collections = $shopify("GET", "/admin/smart_collections.json?product_id=$IDProduct"); 
    if(!is_array($collections)) return [];
    return $collections;
}
function getDataByFilterFromShopify($shopify,$topic,$filter = null){
    if(!isset($topic)) return [];
    $result = [];
    $resAPI = $shopify("GET","/admin/$topic.json?".$filter);
    if(is_array($resAPI)){$result = $resAPI;}
    return $result;
}
function postDataPriceRule($shopify,$data){
    if(!isset($data) || (!isset($shopify))) return [];
    $newDiscountRule = $shopify("POST", "/admin/price_rules.json", $data); 
    return $newDiscountRule;
}
function postDiscountCode($shopify,$discountRuleID,$data){
    if(!isset($data) || (!isset($discountRuleID))) return [];
    $newDiscountCode = $shopify("POST", "/admin/price_rules/". $discountRuleID ."/discount_codes.json", $data);
    return $newDiscountCode;
}
function postDataDraftOrder($shopify,$data){
    if(!isset($data) || (!isset($shopify))) return [];
    $response = $shopify("POST", "/admin/draft_orders.json", $data);  
    return $response;
}
function getMoneyFormat($shopify){  
    $shopInfo = $shopify("GET", "/admin/shop.json");  
    $result = array();
    if(isset($shopInfo['money_format'])){
        $result['money_format'] = $shopInfo['money_format'];
        $result['money_with_currency_format'] = $shopInfo['money_with_currency_format'];
    }else{
        $result['money_format'] = NULL;
        $result['money_with_currency_format'] = NULL;
    } 
    return $result;
}
function getInfoShop($shopify){
    $shopInfo = $shopify("GET", "/admin/shop.json");   
    return $shopInfo;
}
function getCustomer($shopify,$customerId) { 
    if(!isset($customerId)) return [];
    $result = $shopify('GET', "/admin/customers/{$customerId}.json");
    return $result;
}
 
// orther function
function checkExistArray($array1, $array2) {
    if (is_array($array1) && is_array($array2)) {
        $check = array();
        foreach ($array1 as $v1) {
            array_push($check, $v1);
        }
        foreach ($array2 as $v2) {
            if (in_array($v2, $check)) {
                return $result = 1;
                break;
            } else {
                $result = 0;
            }
        }
    } else {
        return 0;
    }
    return $result;
} 
function getmicrotime() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
} 
function cvf_convert_object_to_array($data) {
    if (is_object($data)) {
        $data = get_object_vars($data);
    }
    if (is_array($data)) {
        return array_map(__FUNCTION__, $data);
    } else {
        return $data;
    }
} 
function remove_dir($dir = null) {
    if (is_dir($dir)) {
        $objects = scandir($dir); 
        foreach ($objects as $object) {
        if ($object != "." && $object != "..") {
            if (filetype($dir."/".$object) == "dir") remove_dir($dir."/".$object);
            else unlink($dir."/".$object);
        }
        }
        reset($objects);
        rmdir($dir);
    }
}
function creatSlug($string, $plusString) {
    $search = array(
        '#(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)#',
        '#(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)#',
        '#(ì|í|ị|ỉ|ĩ)#',
        '#(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)#',
        '#(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)#',
        '#(ỳ|ý|ỵ|ỷ|ỹ)#',
        '#(đ)#',
        '#(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)#',
        '#(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)#',
        '#(Ì|Í|Ị|Ỉ|Ĩ)#',
        '#(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)#',
        '#(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)#',
        '#(Ỳ|Ý|Ỵ|Ỷ|Ỹ)#',
        '#(Đ)#',
        "/[^a-zA-Z0-9\-\_]/",
    );
    $replace = array(
        'a',
        'e',
        'i',
        'o',
        'u',
        'y',
        'd',
        'A',
        'E',
        'I',
        'O',
        'U',
        'Y',
        'D',
        '-',
    );
    $string = preg_replace($search, $replace, $string);
    $string = preg_replace('/(-)+/', '-', $string);
    $string = strtolower($string);
    return $string . $plusString;
} 
function pr($data) {
    if (is_array($data)) {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }else{
        var_dump($data);
    }
} 
function dd($data) {
    if (is_array($data)) {
        echo "<pre>";
        print_r($data);
        echo "</pre>"; 
    }else{
        var_dump($data);
    }
    die();
} 
function redirect($data)  {
    header("Location: $data");
} 
function getCurl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1');
    $response = curl_exec($ch);
    if ($response === false) {
        $api_response = curl_error($ch);
    } else {
        $api_response = $response;
    }
    curl_close($ch);
    return $api_response;
} 
function valaditon_get($data) {
    $data = "";
    if($data)  return $data;
    return $data;
} 
function result_fetch_object($data) {
    $result = $data->fetch_object();
    return $result;
} 
 


// CHECK CONDITION
function checkConditionProduct($conditions,$product,$shop){ 
    $conditions = formatCondition($conditions);
    foreach($conditions as $condition){ 
        if(isset($condition['AND'])){
            $conditionAND = $condition['AND'];
            $checkConditionAND = isOkAnd($conditionAND,$condition,$product,$shop);
            if($checkConditionAND == true){
                return true;
                break;
            }
        }else{
            if(checkConditionCompare(getValueProduct($condition['feildProduct'],$product,$shop),$condition) === true){
                return true;
                break;
            }
        }  
    } 
    return false;
}
function isOkAnd($conditionAND,$condition,$product,$shop){ 
    foreach($conditionAND as $and){ 
        if(checkConditionCompare(getValueProduct($and['feildProduct'],$product,$shop),$and) === false){
            return false;
            break;
        } 
    }
    if(checkConditionCompare(getValueProduct($condition['feildProduct'],$product,$shop),$condition) === false){
        return false; 
    } 
    return true;
}
function formatCondition($conditions){
    $listCondition = []; 
    foreach($conditions as $key => $condition){
        if(isset($condition['index'])){
            $listCondition[$condition['index']]['AND'] = [];
            array_push($listCondition[$condition['index']]['AND'],$condition);
        }else{
            array_push($listCondition,$condition);
        }
    }
    return $listCondition;
}
function checkConditionCompare($value,$rule){ 
    // $rule['valueIs']  condition 
    if ($rule['condition'] == 'contains') return isContain($value,$rule['value']);
    if ($rule['condition'] == 'not_contains') return isNotContain($value,$rule['value']);
    if ($rule['condition'] == 'contains_sensitive') return isContainSensitive($value,$rule['value']);
    if ($rule['condition'] == 'not_contains_sensitive') return isNotContainSensitive($value,$rule['value']);
    if ($rule['condition'] == 'equals') return isEqualsString($value,$rule['value']);
    if ($rule['condition'] == 'starts_with') return isStartsWith($value,$rule['value']);
    if ($rule['condition'] == 'in_set') return isInSet($value,$rule['value']);
    if ($rule['condition'] == 'not_in_set') return isNotInSet($value,$rule['value']);
    if ($rule['condition'] == '>') return isGreater($value,$rule['value']);
    if ($rule['condition'] == '>=') return isGreaterEquals($value,$rule['value']);
    if ($rule['condition'] == '=') return isEqualsNumber($value,$rule['value']);
    if ($rule['condition'] == 'not_equals') return isNotEquals($value,$rule['value']);
    if ($rule['condition'] == '<') return isSmaller($value,$rule['value']);
    if ($rule['condition'] == '<=') return isSmallerEquals($value,$rule['value']); 
}
function getValueProduct($field,$product,$shop){
   
    $string = file_get_contents("https://windy.omegatheme.com/dev-feed-data/admin/assets/scripts/fieldsProduct.json");
    $data = json_decode($string, true); 
    foreach ($data['fieldsConditionProduct'] as $key => $value) { 
        if ($value['value'] == $field) {
            return getValueProductByField ($value,$product,$shop);
        }
    }
    return '';
}
function getValueProductByField($row,$product,$shop){  
    if (isset($product['created_at']) && $row['value'] == 'created_at') return date("Y-m-d",strtotime($product['created_at']));
    else if (isset($product[$row['value']]) && $row['value'] != 'created_at') return $product[$row['value']];
    else return '';
}
function getCollectionByProduct($shop,$productId){
    global $appId, $dbr;
    $shopify = shopifyInit($dbr, $shop, $appId);
    $collections['id'] = '';
    $collections['title'] = '';
    $customCollections = getDataByFilterFromShopify($shopify,"custom_collections","product_id=".$productId);
    foreach ($customCollections as $key => $value) {
        $collections['id'] .= ", ".$value['id'];
        $collections['title'] .= ", ".$value['title'];
    }
    $smartCollections = getDataByFilterFromShopify($shopify,"smart_collections","product_id=".$productId);
    foreach ($smartCollections as $key => $value) {
        $collections['id'] .= ", ".$value['id'];
        $collections['title'] .= ", ".$value['title'];
    } 
    $collections['id'] = trim($collections['id'], ', ');
    $collections['title'] = trim($collections['title'], ', ');
    return $collections;
}
function isBoolean($value){
    if(is_bool($value)) return ($value === true ) ? 'true':'false';
    return $value;
}

function isContain($value,$valueCondition){
    $value = isBoolean($value);
    $value = strtolower($value);
    $valueCondition = strtolower($valueCondition);
    if ($valueCondition == '' || $valueCondition == null) return ($value == '' || $value == null)?true:false;
    return (strpos($value,$valueCondition) !== false)?true:false;
}

function isNotContain($value,$valueCondition){
    $value = isBoolean($value);
    $value = strtolower($value);
    $valueCondition = strtolower($valueCondition);
    if ($valueCondition == '' || $valueCondition == null) return ($value != '' || $value != null)?true:false;
    return (strpos($value,$valueCondition) === false)?true:false;
}

function isContainSensitive($value,$valueCondition){
    $value = isBoolean($value);
    if ($valueCondition == '' || $valueCondition == null) return ($value == '' || $value == null)?true:false;
    return (strpos($value,$valueCondition) !== false)?true:false;
}

function isNotContainSensitive($value,$valueCondition){
    $value = isBoolean($value);
    if ($valueCondition == '' || $valueCondition == null) return ($value == '' || $value == null)?true:false;
    return (strpos($value,$valueCondition) === false)?true:false;
}

function isEqualsString($value,$valueCondition){
    $value = isBoolean($value);
    $value = (string)strtolower($value);
    $valueCondition = (string)strtolower($valueCondition);
    return ($value === $valueCondition)?true:false;
}

function isStartsWith($value,$valueCondition){
    $value = isBoolean($value);
    $value = strtolower($value);
    $valueCondition = strtolower($valueCondition);
    if ($valueCondition == '' || $valueCondition == null) return ($value == '' || $value == null)?true:false;
    return (stripos($value,$valueCondition) === 0)?true:false;
}

function isInSet($value,$valueCondition){
    $value = isBoolean($value);
    $value = strtolower($value);
    $valueCondition = strtolower($valueCondition);
    if ($valueCondition == '' || $valueCondition == null) return ($value == '' || $value == null)?true:false;
    return (in_array($value, explode(", ",$valueCondition)))?true:false;
}

function isNotInSet($value,$valueCondition){
    $value = isBoolean($value);
    $value = strtolower($value);
    $valueCondition = strtolower($valueCondition);
    if ($valueCondition == '' || $valueCondition == null) return ($value == '' || $value == null)?true:false;
    return (in_array($value, explode(", ",$valueCondition)))?false:true;
}
function isGreater($value,$valueCondition){
    return ((float)$value > (float)$valueCondition)?true:false;
}
function isGreaterEquals($value,$valueCondition){
    return ((float)$value >= (float)$valueCondition)?true:false;
}
function isEqualsNumber($value,$valueCondition){
    return ((float)$value == (float)$valueCondition)?true:false;
}
function isNotEquals($value,$valueCondition){
    return ((float)$value != (float)$valueCondition)?true:false;
}
function isSmaller($value,$valueCondition){
    return ((float)$value < (float)$valueCondition)?true:false;
}
function isSmallerEquals($value,$valueCondition){
    return ((float)$value <= (float)$valueCondition)?true:false;
}