<?php

/*
 * functions.php
 *
 * holds functions for EC for index.php and return.php for Digital Goods EC Calls
 */

//Function PPHttpPost
//Makes an API call using an NVP String and an Endpoint
function PPHttpPost($my_endpoint, $my_api_str) {
    // setting the curl parameters.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $my_endpoint);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    // turning off the server and peer verification(TrustManager Concept).
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    // setting the NVP $my_api_str as POST FIELD to curl
    curl_setopt($ch, CURLOPT_POSTFIELDS, $my_api_str);
    // getting response from server
    $httpResponse = curl_exec($ch);
    if (!$httpResponse) {
        $response = "$API_method failed: " . curl_error($ch) . '(' . curl_errno($ch) . ')';
        return $response;
    }
    $httpResponseAr = explode("&", $httpResponse);
    $httpParsedResponseAr = array();
    foreach ($httpResponseAr as $i => $value) {
        $tmpAr = explode("=", $value);
        if (sizeof($tmpAr) > 1) {
            $httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
        }
    }

    if ((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
        $response = "Invalid HTTP Response for POST request($my_api_str) to $API_Endpoint.";
        return $response;
    }

    return $httpParsedResponseAr;
}

// get item status
function paypalplus_item_is_enabled() {
    return (osc_item_field("b_enabled") == 1);
}

/**
 * Create a record on the DB for the paypal transaction
 * 
 * @param string $concept
 * @param string $code
 * @param float $amount
 * @param string $currency
 * @param string $email
 * @param integer $user
 * @param integer $item
 * @param string $product_type (publish fee, premium, pack and which category)
 * @param string $source
 * @return integer $last_id
 */
function paypalplus_save_log($concept, $code, $amount, $currency, $email, $user, $item, $product_type, $source) {

    $conn = getConnection();
    $conn->osc_dbExec("INSERT INTO %st_paypal_log (s_concept, dt_date, s_code, f_amount, s_currency_code, s_email, fk_i_user_id, fk_i_item_id, i_product_type, s_source) VALUES 
                          ('" . $concept . "',"
            . "'" . date("Y-m-d H:i:s") . "',"
            . "'" . $code . "',"
            . "'" . $amount . "',"
            . "'" . $currency . "',"
            . "'" . $email . "',"
            . "'" . $user . "',"
            . "'" . $item . "',"
            . "'" . $product_type . "',"
            . "'" . $source . "'"
            . ")", DB_TABLE_PREFIX);
    return $conn->get_last_id();
}

/**
 * Know if the ad is paid
 * 
 * @param integer $itemId
 * @return boolean
 */
function paypalplus_is_paid($itemId) {
    $conn = getConnection();
    $paid = $conn->osc_dbFetchResult("SELECT b_paid FROM %st_paypal_publish WHERE fk_i_item_id = %d", DB_TABLE_PREFIX, $itemId);
    if (isset($paid) && $paid['b_paid'] == 1) {
        return true;
    }
    return false;
}

/**
 * Know if the ad is marked as premium (and paid)
 * 
 * @param integer $itemId
 * @return boolean
 */
function paypalplus_is_premium($itemId) {
    $conn = getConnection();
    $paid = $conn->osc_dbFetchResult("SELECT dt_date FROM %st_paypal_premium WHERE fk_i_item_id = %d AND TIMESTAMPDIFF(DAY,dt_date,'%s') < %d", DB_TABLE_PREFIX, $itemId, date('Y-m-d H:i:s'), osc_get_preference("premium_days", "paypalplus"));
    if ($paid) {
        return true;
    }
    return false;
}

function paypalplus_is_premium_plus($itemId) {
    $conn = getConnection();
    $paid = $conn->osc_dbFetchResult("SELECT dt_date FROM %st_paypal_premium_plus WHERE fk_i_item_id = %d AND TIMESTAMPDIFF(DAY,dt_date,'%s') < %d", DB_TABLE_PREFIX, $itemId, date('Y-m-d H:i:s'), osc_get_preference("premium_plus_days", "paypalplus"));
    if ($paid) {
        return true;
    }
    return false;
}

//show premium days
function paypalplus_premium_days($itemId) {
    $conn = getConnection();
    $pdays = $conn->osc_dbFetchResult("SELECT i_product_type FROM %st_paypal_log WHERE fk_i_item_id = %d ", DB_TABLE_PREFIX, $itemId, osc_get_preference("premium_days", "paypalplus"));
    $pdays['i_product_type'];
    if ($pdays['i_product_type'] == '201')
        return osc_get_preference("premium_days", "paypalplus");
    else if ($pdays['i_product_type'] == '301')
        return osc_get_preference("premium_plus_days", "paypalplus");
}

function paypal_crypt($cadena) {
    $cifrado = MCRYPT_RIJNDAEL_256;
    $modo = MCRYPT_MODE_ECB;
    return base64_encode(mcrypt_encrypt($cifrado, PAYPAL_CRYPT_KEY, $cadena, $modo, mcrypt_create_iv(mcrypt_get_iv_size($cifrado, $modo), MCRYPT_RAND)
                    ));
}

function paypal_decrypt($cadena) {
    $cifrado = MCRYPT_RIJNDAEL_256;
    $modo = MCRYPT_MODE_ECB;
    return str_replace("\0", "", mcrypt_decrypt($cifrado, PAYPAL_CRYPT_KEY, base64_decode($cadena), $modo, mcrypt_create_iv(mcrypt_get_iv_size($cifrado, $modo), MCRYPT_RAND)
                    ));
}

function paypalplus_make_premium_byadmin() {
    $pItem = Params::getParam('id');
    $pDays = Params::getParam('type');
    $conn = getConnection();
    if ($pDays == '201') {
        $paypal_id = paypalplus_save_log(Params::getParam('item_name'), '0', '0', 'nd', 'nd', Params::getParam('userid'), $pItem, '201', 'ADMIN');
        $paid = $conn->osc_dbFetchResult("SELECT * FROM %st_paypal_premium WHERE fk_i_item_id = %d", DB_TABLE_PREFIX, $pItem);
        if ($paid) {
            $conn->osc_dbExec("UPDATE %st_paypal_premium SET dt_date = '%s', fk_i_paypal_id = '%d' WHERE fk_i_item_id = %d", DB_TABLE_PREFIX, date('Y-m-d H:i:s'), $paypal_id, $pItem);
        } else {
            $conn->osc_dbExec("INSERT INTO  %st_paypal_premium (fk_i_item_id, dt_date, fk_i_paypal_id) VALUES ('%d',  '%s',  '%s')", DB_TABLE_PREFIX, $pItem, date('Y-m-d H:i:s'), $paypal_id);
        }
        $mItem = new ItemActions(false);
        $mItem->premium($pItem, true);
    } else if ($pDays == '301') {
        $paypal_id = paypalplus_save_log(Params::getParam('item_name'), '0', '0', 'nd', 'nd', Params::getParam('userid'), $pItem, '301', 'ADMIN');
        $paid = $conn->osc_dbFetchResult("SELECT * FROM %st_paypal_premium_plus WHERE fk_i_item_id = %d", DB_TABLE_PREFIX, $pItem);
        if ($paid) {
            $conn->osc_dbExec("UPDATE %st_paypal_premium_plus SET dt_date = '%s', fk_i_paypal_id = '%d' WHERE fk_i_item_id = %d", DB_TABLE_PREFIX, date('Y-m-d H:i:s'), $paypal_id, $pItem);
        } else {
            $conn->osc_dbExec("INSERT INTO  %st_paypal_premium_plus (fk_i_item_id, dt_date, fk_i_paypal_id) VALUES ('%d',  '%s',  '%s')", DB_TABLE_PREFIX, $pItem, date('Y-m-d H:i:s'), $paypal_id);
        }
        $mItem = new ItemActions(false);
        $mItem->premium($pItem, true);
    }
}
?>