<?php
/*
 * return.php
 *
 * This page will handle the GetECDetails, and DoECPayment API Calls
 */

//set include
define('ABS_PATH', dirname(dirname(dirname(dirname(__FILE__)))) . '/');
require_once ABS_PATH . 'oc-load.php';
require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'functions.php';

if (osc_get_preference('standard', 'paypalplus') == 1) {

    $rpl = explode('|', Params::getParam('rpl'));
    $product_type = explode('x', Params::getParam('item_number'));

    if (Params::getParam('payment_status') == 'Completed') {

        $conn = getConnection();
        $payment = $conn->osc_dbFetchResult("SELECT * FROM %st_paypal_log WHERE s_code = '%s'", DB_TABLE_PREFIX, Params::getParam('txn_id'));
        if (!isset($payment['pk_i_id'])) {
            // Payment is not processed yet
            osc_add_flash_info_message(__('We are processing your payment, if we did not finish in a few seconds, please contact us', 'paypalplus'));
            if ($product_type[0] == '101') {
                paypalplus_js_redirect_to(osc_render_file_url(osc_plugin_folder(__FILE__) . 'user_menu.php'));
            } else if ($product_type[0] == '201') {
                paypalplus_js_redirect_to(osc_render_file_url(osc_plugin_folder(__FILE__) . 'user_menu.php'));
            } else if ($product_type[0] == '301') {
                paypalplus_js_redirect_to(osc_render_file_url(osc_plugin_folder(__FILE__) . 'user_menu.php'));
            } else {
                paypalplus_js_redirect_to(osc_render_file_url(osc_plugin_folder(__FILE__) . 'user_menu_pack.php'));
            }
        } else {
            osc_add_flash_ok_message(__('Payment processed correctly', 'paypalplus'));
            if ($product_type[0] == '101') {
                Item::newInstance()->update(array('b_enabled' => 1), array('pk_i_id' => $rpl[1]));
                $item = Item::newInstance()->findByPrimaryKey($rpl[1]);
                $category = Category::newInstance()->findByPrimaryKey($item['fk_i_category_id']);
                View::newInstance()->_exportVariableToView('category', $category);
                paypalplus_js_redirect_to(osc_search_category_url());
            } else if ($product_type[0] == '201') {
                paypalplus_js_redirect_to(osc_render_file_url(osc_plugin_folder(__FILE__) . 'user_menu.php'));
            } else if ($product_type[0] == '301') {
                paypalplus_js_redirect_to(osc_render_file_url(osc_plugin_folder(__FILE__) . 'user_menu.php'));
            } else {
                paypalplus_js_redirect_to(osc_render_file_url(osc_plugin_folder(__FILE__) . 'user_menu_pack.php'));
            }
        }
    }
} else {

    //set GET var's to local vars:
    $token = $_GET['token'];
    $payerid = $_GET['PayerID'];
    //set API Creds, Version, and endpoint:
    //**************************************************//
    // This is where you would set your API Credentials //
    // Please note this is not considered "SECURE" this // 
    // is an example only. It is NOT Recommended to use //
    // this method in production........................//
    //**************************************************//
    $APIUSERNAME = paypal_decrypt(osc_get_preference('api_username', 'paypalplus'));
    $APIPASSWORD = paypal_decrypt(osc_get_preference('api_password', 'paypalplus'));
    $APISIGNATURE = paypal_decrypt(osc_get_preference('api_signature', 'paypalplus'));
    $ENDPOINT = 'https://api-3t.paypal.com/nvp';
    if (osc_get_preference('sandbox', 'paypalplus') == 1) {
        $ENDPOINT = 'https://api-3t.sandbox.paypal.com/nvp';
    }

    $VERSION = '65.1'; //must be >= 65.1
    //Build the Credential String:
    $cred_str = 'USER=' . $APIUSERNAME . '&PWD=' . $APIPASSWORD . '&SIGNATURE=' . $APISIGNATURE . '&VERSION=' . $VERSION;
    //Build NVP String for GetExpressCheckoutDetails
    $nvp_str = '&METHOD=GetExpressCheckoutDetails&TOKEN=' . urldecode($token);

    //combine the two strings and make the API Call
    $req_str = $cred_str . $nvp_str;
    $response = PPHttpPost($ENDPOINT, $req_str);
    //based on the API Response from GetExpressCheckoutDetails
    $doec_str = $cred_str . '&METHOD=DoExpressCheckoutPayment'
            . '&TOKEN=' . $token
            . '&PAYERID=' . $payerid
            . '&PAYMENTREQUEST_0_CURRENCYCODE=' . urldecode($response['PAYMENTREQUEST_0_CURRENCYCODE'])
            . '&PAYMENTREQUEST_0_AMT=' . urldecode($response['PAYMENTREQUEST_0_AMT'])
            . '&PAYMENTREQUEST_0_ITEMAMT=' . urldecode($response['PAYMENTREQUEST_0_ITEMAMT'])
            . '&PAYMENTREQUEST_0_TAXAMT=' . urldecode($response['PAYMENTREQUEST_0_TAXAMT'])
            . '&PAYMENTREQUEST_0_DESC=' . urldecode($response['PAYMENTREQUEST_0_DESC'])
            . '&PAYMENTREQUEST_0_PAYMENTACTION=Sale'
            . '&L_PAYMENTREQUEST_0_ITEMCATEGORY0=' . urldecode($response['L_PAYMENTREQUEST_0_ITEMCATEGORY0'])
            . '&L_PAYMENTREQUEST_0_NAME0=' . urldecode($response['L_PAYMENTREQUEST_0_NAME0'])
            . '&L_PAYMENTREQUEST_0_NUMBER0=' . urldecode($response['L_PAYMENTREQUEST_0_NUMBER0'])
            . '&L_PAYMENTREQUEST_0_QTY0=' . urldecode($response['L_PAYMENTREQUEST_0_QTY0'])
            . '&L_PAYMENTREQUEST_0_TAXAMT0=' . urldecode($response['L_PAYMENTREQUEST_0_TAXAMT0'])
            . '&L_PAYMENTREQUEST_0_AMT0=' . urldecode($response['L_PAYMENTREQUEST_0_AMT0'])
            . '&L_PAYMENTREQUEST_0_DESC0=' . urldecode($response['L_PAYMENTREQUEST_0_DESC0'])
            . '&NOTIFYURL=';

    //make the DoEC Call:
    $doresponse = PPHttpPost($ENDPOINT, $doec_str);

    //check Response
    if ($doresponse['ACK'] == 'Success' || $doresponse['ACK'] == 'SuccessWithWarning') {
        //Save transaction to DB
        $rpl = explode('|', Params::getParam('rpl'));
        $product_type = explode('x', urldecode($response['L_PAYMENTREQUEST_0_NUMBER0']));
        $paypal_id = paypalplus_save_log(urldecode($response['L_PAYMENTREQUEST_0_NAME0']), urldecode($doresponse['PAYMENTINFO_0_TRANSACTIONID']), urldecode($doresponse['PAYMENTINFO_0_AMT']), urldecode($doresponse['PAYMENTINFO_0_CURRENCYCODE']), isset($response['EMAIL']) ? urldecode($response['EMAIL']) : '', $rpl[0], $rpl[1], $product_type[0], 'PAYPAL');
        if ($product_type[0] == '101') {
            // PUBLISH FEE
            $conn = getConnection();
            $paid = $conn->osc_dbFetchResult("SELECT * FROM %st_paypal_publish WHERE fk_i_item_id = %d", DB_TABLE_PREFIX, $rpl[1]);
            if ($paid) {
                $conn->osc_dbExec("UPDATE %st_paypal_publish SET dt_date = '%s', b_paid =  '1', fk_i_paypal_id = '%d' WHERE fk_i_item_id = %d", DB_TABLE_PREFIX, date('Y-m-d H:i:s'), $paypal_id, $rpl[1]);
            } else {
                $conn->osc_dbExec("INSERT INTO  %st_paypal_publish (fk_i_item_id, dt_date, b_paid, fk_i_paypal_id) VALUES ('%d',  '%s', 1, '%s')", DB_TABLE_PREFIX, $rpl[1], date('Y-m-d H:i:s'), $paypal_id);
            }

            Item::newInstance()->update(array('b_enabled' => 1), array('pk_i_id' => $rpl[1]));
            $item = Item::newInstance()->findByPrimaryKey($rpl[1]);
            $category = Category::newInstance()->findByPrimaryKey($item['fk_i_category_id']);
            View::newInstance()->_exportVariableToView('category', $category);
            $html = '<p>' . __('Payment processed correctly', 'paypalplus') . ' <a href=\\"' . osc_search_category_url() . '\\">' . __('Click here to continue', 'paypalplus') . '</a></p>';
            $url = osc_search_category_url();
        } else if ($product_type[0] == '201') {
            // PREMIUM FEE
            $conn = getConnection();
            $paid = $conn->osc_dbFetchResult("SELECT * FROM %st_paypal_premium WHERE fk_i_item_id = %d", DB_TABLE_PREFIX, $rpl[1]);
            if ($paid) {
                $conn->osc_dbExec("UPDATE %st_paypal_premium SET dt_date = '%s', fk_i_paypal_id = '%d' WHERE fk_i_item_id = %d", DB_TABLE_PREFIX, date('Y-m-d H:i:s'), $paypal_id, $rpl[1]);
            } else {
                $conn->osc_dbExec("INSERT INTO  %st_paypal_premium (fk_i_item_id, dt_date, fk_i_paypal_id) VALUES ('%d',  '%s',  '%s')", DB_TABLE_PREFIX, $rpl[1], date('Y-m-d H:i:s'), $paypal_id);
            }
            $mItem = new ItemActions(false);
            //$mItem->premium($item['pk_i_id'], true);
            $mItem->premium($rpl[1], true);
            if (osc_get_preference("auto_enable", "paypalplus") == "1") {
                Item::newInstance()->update(array('b_enabled' => 1), array('pk_i_id' => $rpl[1]));
            }

            $html = '<p>' . __('Payment processed correctly', 'paypalplus') . ' <a href=\\"' . osc_render_file_url(osc_plugin_folder(__FILE__) . 'user_menu.php') . '\\">' . __("Click here to continue", 'paypalplus') . '</a></p>';
            $url = osc_render_file_url(osc_plugin_folder(__FILE__) . 'user_menu.php');
        } else if ($product_type[0] == '301') {
            // PREMIUM FEE
            $conn = getConnection();
            $paid = $conn->osc_dbFetchResult("SELECT * FROM %st_paypal_premium_plus WHERE fk_i_item_id = %d", DB_TABLE_PREFIX, $rpl[1]);
            if ($paid) {
                $conn->osc_dbExec("UPDATE %st_paypal_premium_plus SET dt_date = '%s', fk_i_paypal_id = '%d' WHERE fk_i_item_id = %d", DB_TABLE_PREFIX, date('Y-m-d H:i:s'), $paypal_id, $rpl[1]);
            } else {
                $conn->osc_dbExec("INSERT INTO  %st_paypal_premium_plus (fk_i_item_id, dt_date, fk_i_paypal_id) VALUES ('%d',  '%s',  '%s')", DB_TABLE_PREFIX, $rpl[1], date('Y-m-d H:i:s'), $paypal_id);
            }
            $mItem = new ItemActions(false);
            //$mItem->premium($item['pk_i_id'], true);
            $mItem->premium($rpl[1], true);
            if (osc_get_preference("auto_enable", "paypalplus") == "1") {
                Item::newInstance()->update(array('b_enabled' => 1), array('pk_i_id' => $rpl[1]));
            }

            $html = '<p>' . __('Payment processed correctly', 'paypalplus') . ' <a href=\\"' . osc_render_file_url(osc_plugin_folder(__FILE__) . 'user_menu.php') . '\\">' . __("Click here to continue", 'paypalplus') . '</a></p>';
            $url = osc_render_file_url(osc_plugin_folder(__FILE__) . 'user_menu.php');
        } else {
            // PUBLISH/PREMIUM PACKS
            $conn = getConnection();
            $wallet = $conn->osc_dbFetchResult("SELECT * FROM %st_paypal_wallet WHERE fk_i_user_id = %d", DB_TABLE_PREFIX, $rpl[0]);
            $havebonus = $conn->osc_dbFetchResult("SELECT * FROM %st_paypal_bonus WHERE fk_i_user_id = %d", DB_TABLE_PREFIX, $rpl[0]);
            if (osc_get_preference("allow_bonus", "paypalplus") == '1') {
                //if (!isset($havebonus['f_bonus']))  { //non ha bonus
                $bonus = array();
                if (osc_get_preference("bonus_pack_1", "paypalplus") != '' && osc_get_preference("bonus_pack_1", "paypalplus") != '0') {
                    $bonus[] = osc_get_preference("bonus_pack_1", "paypalplus");
                }
                if (osc_get_preference("bonus_pack_2", "paypalplus") != '' && osc_get_preference("bonus_pack_2", "paypalplus") != '0') {
                    $bonus[] = osc_get_preference("bonus_pack_2", "paypalplus");
                }
                if (osc_get_preference("bonus_pack_3", "paypalplus") != '' && osc_get_preference("bonus_pack_3", "paypalplus") != '0') {
                    $bonus[] = osc_get_preference("bonus_pack_3", "paypalplus");
                }
                $packs = array();
                if (osc_get_preference("pack_price_1", "paypalplus") != '' && osc_get_preference("pack_price_1", "paypalplus") != '0') {
                    $packs[] = osc_get_preference("pack_price_1", "paypalplus");
                }
                if (osc_get_preference("pack_price_2", "paypalplus") != '' && osc_get_preference("pack_price_2", "paypalplus") != '0') {
                    $packs[] = osc_get_preference("pack_price_2", "paypalplus");
                }
                if (osc_get_preference("pack_price_3", "paypalplus") != '' && osc_get_preference("pack_price_3", "paypalplus") != '0') {
                    $packs[] = osc_get_preference("pack_price_3", "paypalplus");
                }
                $pack_n = 0;
                $bonus_n = 0;
                foreach ($packs as $pack) {
                    $pack_n++;
                    if ($pack == urldecode($doresponse['PAYMENTINFO_0_AMT']))
                        $perc = $bonus[$bonus_n];
                    $bonus_n++;
                }
                $bonuscredit = (urldecode($doresponse['PAYMENTINFO_0_AMT']) / 100) * $perc;
                if (osc_get_preference("allow_bonus_expiration", "paypalplus") == '1') {
                    $dayexp = osc_get_preference("bonus_days", "paypalplus");
                    $expiration = Date('Y-m-d H:i:s', strtotime("+" . $dayexp . " days"));
                    if ($havebonus['f_bonus'] == '0') {
                        $conn->osc_dbExec("UPDATE %st_paypal_bonus SET `f_bonus` = '%d' , `dt_date` = '%s' WHERE fk_i_user_id = %d", DB_TABLE_PREFIX, $bonuscredit, $expiration, $rpl[0]);
                    } else {
                        $conn->osc_dbExec("INSERT INTO  %st_paypal_bonus (`fk_i_user_id`, `f_bonus`, `dt_date` ) VALUES ('%d', '%f', '%s')", DB_TABLE_PREFIX, $rpl[0], $bonuscredit, $expiration);
                    }
                } else
                if ($havebonus['f_bonus'] == '0') {
                    $conn->osc_dbExec("UPDATE %st_paypal_bonus SET `f_bonus` = '%d' , `dt_date` = null WHERE fk_i_user_id = %d", DB_TABLE_PREFIX, $bonuscredit, $rpl[0]);
                } else {
                    $conn->osc_dbExec("INSERT INTO  %st_paypal_bonus (`fk_i_user_id`, `f_bonus` ) VALUES ('%d', '%f')", DB_TABLE_PREFIX, $rpl[0], $bonuscredit);
                }

                //end allow bonus
            } //end havebonus
            if (isset($wallet['f_amount'])) {
                $conn->osc_dbExec("UPDATE %st_paypal_wallet SET f_amount = '%f' WHERE fk_i_user_id = %d", DB_TABLE_PREFIX, ($wallet['f_amount'] + urldecode($doresponse['PAYMENTINFO_0_AMT'])), $rpl[0]);
            } else {

                $conn->osc_dbExec("INSERT INTO  %st_paypal_wallet (`fk_i_user_id`, `f_amount`) VALUES ('%d',  '%f')", DB_TABLE_PREFIX, $rpl[0], urldecode($doresponse['PAYMENTINFO_0_AMT']));
            }

            osc_add_flash_ok_message(__('Payment processed correctly', 'paypalplus'));
            $html = '<p>' . __('Payment processed correctly', 'paypalplus') . ' <a href=\\"' . osc_render_file_url(osc_plugin_folder(__FILE__) . 'user_menu_pack.php') . '\\">' . __("Click here to continue", 'paypalplus') . '</a></p>';
            $url = osc_render_file_url(osc_plugin_folder(__FILE__) . "user_menu_pack.php");
        }

        osc_add_flash_ok_message(__("Payment processed correctly", "paypalplus"));
    } else if ($doresponse['ACK'] == "Failure" || $doresponse['ACK'] == "FailureWithWarning") {
        $rpl = explode("|", Params::getParam("rpl"));
        $item = Item::newInstance()->findByPrimaryKey($rpl[1]);
        $category = Category::newInstance()->findByPrimaryKey($item['fk_i_item_id']);
        View::newInstance()->_exportVariableToView('category', $category);
        $html = '<p>' . __("There was a problem processing your payment. Please contact the administrators and", "paypalplus") . ' <a href=\\"' . osc_search_category_url() . '\\">' . __("Click here to continue", "paypalplus") . '</a></p>';

        osc_add_flash_error_message(__("There was a problem processing your payment. Please contact the administrators", "paypalplus"));
    }
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
            <script type="text/javascript" src="https://www.paypalobjects.com/js/external/dg.js"></script>
            <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.js"></script>

            <title><?php echo osc_page_title(); ?></title>
        </head>
        <body>
            <script type="text/javascript">
                top.rd.innerHTML = "<?php echo $html; ?>" ;
                top.location.href = "<?php echo $url; ?>" ;
                top.dg_<?php echo $rpl[3]; ?>.closeFlow() ;
            </script>
        </body>
    </html>
    <?php
};
?>