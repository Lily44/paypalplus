<?php
/*
  Plugin Name: PaypalPlus payment
  Plugin URI: http://www.osclass.org/
  Description: Paypal payment options
  Version: 1.0.3
  Author: OSClass . New features by Cris
  Author URI: http://www.osclass.org/
  Short Name: PaypalPlus
 */

define('PAYPAL_CRYPT_KEY', 'randompasswordchangethis');

// load necessary functions
require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'functions.php';

/**
 * Create tables and variables on t_preference and t_pages
 */
function paypalplus_install() {
    $conn = getConnection();
    $path = osc_plugin_resource('paypalplus/struct.sql');
    $sql = file_get_contents($path);
    $conn->osc_dbImportSQL($sql);

    osc_set_preference('default_premium_cost', '1.0', 'paypalplus', 'STRING');
    osc_set_preference('default_premium_plus_cost', '2.0', 'paypalplus', 'STRING');
    osc_set_preference('allow_premium', '0', 'paypalplus', 'BOOLEAN');
    osc_set_preference('allow_premium_plus', '0', 'paypalplus', 'BOOLEAN');
    osc_set_preference('default_publish_cost', '1.0', 'paypalplus', 'STRING');
    osc_set_preference('pay_per_post', '0', 'paypalplus', 'BOOLEAN');
    osc_set_preference('premium_days', '7', 'paypalplus', 'INTEGER');
    osc_set_preference('bonus_days', '60', 'paypalplus', 'INTEGER');
    osc_set_preference('premium_plus_days', '14', 'paypalplus', 'INTEGER');
    osc_set_preference('currency', 'USD', 'paypalplus', 'STRING');
    osc_set_preference('api_username', '', 'paypalplus', 'STRING');
    osc_set_preference('api_password', '', 'paypalplus', 'STRING');
    osc_set_preference('api_signature', '', 'paypalplus', 'STRING');
    osc_set_preference('allow_bonus', '0', 'paypalplus', 'BOOLEAN');
    osc_set_preference('allow_bonus_expiration', '1', 'paypalplus', 'BOOLEAN');
    osc_set_preference('pack_price_1', '', 'paypalplus', 'STRING');
    osc_set_preference('pack_price_2', '', 'paypalplus', 'STRING');
    osc_set_preference('pack_price_3', '', 'paypalplus', 'STRING');
    osc_set_preference('bonus_pack_1', '', 'paypalplus', 'STRING');
    osc_set_preference('bonus_pack_2', '', 'paypalplus', 'STRING');
    osc_set_preference('bonus_pack_3', '', 'paypalplus', 'STRING');
    osc_set_preference('pdt', '', 'paypalplus', 'STRING');
    osc_set_preference('email', '', 'paypalplus', 'STRING');
    osc_set_preference('standard', '1', 'paypalplus', 'BOOLEAN');
    osc_set_preference('sandbox', '1', 'paypalplus', 'BOOLEAN');
    osc_set_preference('auto_enable', '0', 'paypalplus', 'BOOLEAN');
    osc_set_preference('version', '1.0.5', 'paypalplus', 'BOOLEAN');


    $items = $conn->osc_dbFetchResults("SELECT pk_i_id FROM %st_item", DB_TABLE_PREFIX);
    $date = date('Y-m-d H:i:s');
    foreach ($items as $item) {
        $conn->osc_dbExec("INSERT INTO %st_paypal_publish (fk_i_item_id, dt_date, b_paid) VALUES ('%d', '%s', '1')", DB_TABLE_PREFIX, $item['pk_i_id'], $date);
    }

    $conn->osc_dbExec("INSERT INTO %st_pages (s_internal_name, b_indelible, dt_pub_date) VALUES ('email_paypal', 1,'%s' )", DB_TABLE_PREFIX, date('Y-m-d H:i:s'));
    $conn->osc_dbExec("INSERT INTO %st_pages_description (fk_i_pages_id, fk_c_locale_code, s_title, s_text) VALUES (%d, '%s', '{WEB_TITLE} - Publish option for your ad: {ITEM_TITLE}', '<p>Hi {CONTACT_NAME}!</p>\r\n<p> </p>\r\n<p>We just published your item ({ITEM_TITLE}) on {WEB_TITLE}.</p>\r\n<p>{START_PUBLISH_FEE}</p>\r\n<p>In order to make your ad available to anyone on {WEB_TITLE}, you should complete the process and pay the publish fee. You could do that on the following link: {PUBLISH_LINK}</p>\r\n<p>{END_PUBLISH_FEE}</p>\r\n<p> </p>\r\n<p>{START_PREMIUM_FEE}</p>\r\n<p>You could make your ad premium and make it to appear on top result of the searches made on {WEB_TITLE}. You could do that on the following link: {PREMIUM_LINK}</p>\r\n<p>{END_PREMIUM_FEE}</p>\r\n<p> </p>\r\n<p>This is an automatic email, if you already did that, please ignore this email.</p>\r\n<p> </p>\r\n<p>Thanks</p>')", DB_TABLE_PREFIX, $conn->get_last_id(), osc_language());
    $conn->autocommit(true);
}

/**
 * Clean up all the tables and preferences
 */
function paypalplus_uninstall() {
    $conn = getConnection();

    $conn->osc_dbExec('DROP TABLE %st_paypal_wallet', DB_TABLE_PREFIX);
    $conn->osc_dbExec('DROP TABLE %st_paypal_bonus', DB_TABLE_PREFIX);
    $conn->osc_dbExec('DROP TABLE %st_paypal_premium', DB_TABLE_PREFIX);
    $conn->osc_dbExec('DROP TABLE %st_paypal_premium_plus', DB_TABLE_PREFIX);
    $conn->osc_dbExec('DROP TABLE %st_paypal_publish', DB_TABLE_PREFIX);
    $conn->osc_dbExec('DROP TABLE %st_paypal_prices', DB_TABLE_PREFIX);
    $conn->osc_dbExec('DROP TABLE %st_paypal_log', DB_TABLE_PREFIX);
    $page_id = $conn->osc_dbFetchResult("SELECT * FROM %st_pages WHERE s_internal_name = 'email_paypal'", DB_TABLE_PREFIX);
    $conn->osc_dbExec("DELETE FROM %st_pages_description WHERE fk_i_pages_id = %d", DB_TABLE_PREFIX, $page_id['pk_i_id']);
    $conn->osc_dbExec("DELETE FROM %st_pages WHERE pk_i_id = %d", DB_TABLE_PREFIX, $page_id['pk_i_id']);

    osc_delete_preference('default_premium_cost', 'paypalplus');
    osc_delete_preference('default_premium_plus_cost', 'paypalplus');
    osc_delete_preference('allow_premium', 'paypalplus');
    osc_delete_preference('allow_bonus', 'paypalplus');
    osc_delete_preference('allow_premium_plus', 'paypalplus');
    osc_delete_preference('default_publish_cost', 'paypalplus');
    osc_delete_preference('pay_per_post', 'paypalplus');
    osc_delete_preference('premium_days', 'paypalplus');
    osc_delete_preference('bonus_days', 'paypalplus');
    osc_delete_preference('allow_bonus_expiration', 'paypalplus');
    osc_delete_preference('premium_plus_days', 'paypalplus');
    osc_delete_preference('currency', 'paypalplus');
    osc_delete_preference('api_username', 'paypalplus');
    osc_delete_preference('api_password', 'paypalplus');
    osc_delete_preference('api_signature', 'paypalplus');
    osc_delete_preference('pack_price_1', 'paypalplus');
    osc_delete_preference('pack_price_2', 'paypalplus');
    osc_delete_preference('pack_price_3', 'paypalplus');
    osc_delete_preference('bonus_pack_1', 'paypalplus');
    osc_delete_preference('bonus_pack_2', 'paypalplus');
    osc_delete_preference('bonus_pack_3', 'paypalplus');
    osc_delete_preference('pdt', 'paypalplus');
    osc_delete_preference('email', 'paypalplus');
    osc_delete_preference('standard', 'paypalplus');
    osc_delete_preference('sandbox', 'paypalplus');
    osc_delete_preference('auto_enable', 'paypalplus');
    osc_delete_preference('version', 'paypalplus');
    $conn->autocommit(true);
}

/**
 * Gets the path of paypals folder
 * 
 * @return string
 */
function paypalplus_path() {
    return osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__);
}

/**
 * Create and print a "Wallet" button
 * 
 * @param float $amount
 * @param string $description
 * @param string $rpl custom variables
 * @param string $itemnumber (publish fee, premium, pack and which category)
 */
function wallet_button($amount = '0.00', $description = '', $rpl = '||', $itemnumber = '101') {
    echo '<a href="' . osc_render_file_url(osc_plugin_folder(__FILE__) . "wallet.php?a=" . $amount . "&desc=" . $description . "&rpl=" . $rpl . "&inumber=" . $itemnumber) . '"><button>' . __("Pay with your credit", "paypalplus") . '</button></a>';
}

/**
 * Create and print a "Pay with Paypal" button
 * 
 * @param float $amount
 * @param string $description
 * @param string $rpl custom variables
 * @param string $itemnumber (publish fee, premium, pack and which category)
 */
function paypalplus_button($amount = '0.00', $description = '', $rpl = '||', $itemnumber = '101') {

    if (osc_get_preference('standard', 'paypalplus') == 1) {
        if (osc_get_preference('sandbox', 'paypalplus') == 1) {
            $ENDPOINT = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        } else {
            $ENDPOINT = 'https://www.paypal.com/cgi-bin/webscr';
        }

        $RETURNURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'return.php?rpl=' . $rpl;
        $CANCELURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'cancel.php?rpl=' . $rpl;
        $NOTIFYURL = osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'standard_notify_url.php?rpl=' . $rpl;

        $r = rand(0, 1000);
        $rpl .= "|" . $r;
        ?>


        <form action="<?php echo $ENDPOINT; ?>" method="post" id="payment_<?php echo $r; ?>">
            <input type="hidden" name="cmd" value="_xclick" />
            <input type="hidden" name="upload" value="1" />
            <input type="hidden" name="business" value="<?php echo osc_get_preference('email', 'paypalplus'); ?>" />
            <input type="hidden" name="item_name" value="<?php echo $description; ?>" />
            <input type="hidden" name="item_number" value="<?php echo $itemnumber; ?>" />
            <input type="hidden" name="amount" value="<?php echo $amount; ?>" />
            <input type="hidden" name="quantity" value="1" />

            <input type="hidden" name="currency_code" value="<?php echo osc_get_preference('currency', 'paypalplus'); ?>" />
            <input type="hidden" name="rm" value="2" />
            <input type="hidden" name="no_note" value="1" />
            <input type="hidden" name="charset" value="utf-8" />
            <input type="hidden" name="return" value="<?php echo $RETURNURL; ?>" />
            <input type="hidden" name="notify_url" value="<?php echo $NOTIFYURL; ?>" />
            <input type="hidden" name="cancel_return" value="<?php echo $CANCELURL; ?>" />
            <input type="hidden" name="custom" value="<?php echo $rpl; ?>" />
        </form>
        <div class="buttons">
            <div class="right"><a id="button-confirm" class="button" onclick="$('#payment_<?php echo $r; ?>').submit();"><span><img src='<?php echo paypalplus_path(); ?>paypal.gif' border='0' /></span></a></div>
        </div>
        <?php
    } else {

        $APIUSERNAME = paypal_decrypt(osc_get_preference('api_username', 'paypalplus'));
        $APIPASSWORD = paypal_decrypt(osc_get_preference('api_password', 'paypalplus'));
        $APISIGNATURE = paypal_decrypt(osc_get_preference('api_signature', 'paypalplus'));
        if (osc_get_preference('sandbox', 'paypalplus') == 1) {
            $ENDPOINT = 'https://api-3t.sandbox.paypal.com/nvp';
        } else {
            $ENDPOINT = 'https://api-3t.paypal.com/nvp';
        }
        $VERSION = '65.1'; // must be >= 65.1
        $REDIRECTURL = 'https://www.paypal.com/incontext?token=';
        if (osc_get_preference('sandbox', 'paypalplus') == 1) {
            $REDIRECTURL = "https://www.sandbox.paypal.com/incontext?token=";
        }

        $r = rand(0, 1000);
        $rpl .= "|" . $r;

        //Build the Credential String:
        $cred_str = 'USER=' . $APIUSERNAME . '&PWD=' . $APIPASSWORD . '&SIGNATURE=' . $APISIGNATURE . '&VERSION=' . $VERSION;
        //For Testing this is hardcoded. You would want to set these variable values dynamically
        $nvp_str = "&METHOD=SetExpressCheckout"
                . '&RETURNURL=' . osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'return.php?rpl=' . $rpl //set your Return URL here
                . '&CANCELURL=' . osc_base_url() . 'oc-content/plugins/' . osc_plugin_folder(__FILE__) . 'cancel.php?rpl=' . $rpl //set your Cancel URL here
                . '&PAYMENTREQUEST_0_CURRENCYCODE=' . osc_get_preference('currency', 'paypalplus')
                . '&PAYMENTREQUEST_0_AMT=' . $amount
                . '&PAYMENTREQUEST_0_ITEMAMT=' . $amount
                . '&PAYMENTREQUEST_0_TAXAMT=0'
                . '&PAYMENTREQUEST_0_DESC=' . $description
                . '&PAYMENTREQUEST_0_PAYMENTACTION=Sale'
                . '&L_PAYMENTREQUEST_0_ITEMCATEGORY0=Digital'
                . '&L_PAYMENTREQUEST_0_NAME0=' . $description
                . '&L_PAYMENTREQUEST_0_NUMBER0=' . $itemnumber
                . '&L_PAYMENTREQUEST_0_QTY0=1'
                . '&L_PAYMENTREQUEST_0_TAXAMT0=0'
                . '&L_PAYMENTREQUEST_0_AMT0=' . $amount
                . '&L_PAYMENTREQUEST_0_DESC0=Download'
                . '&CUSTOM=' . $rpl
                . '&useraction=commit';

        //combine the two strings and make the API Call
        $req_str = $cred_str . $nvp_str;
        $response = PPHttpPost($ENDPOINT, $req_str);

        //check Response
        if ($response['ACK'] == "Success" || $response['ACK'] == "SuccessWithWarning") {
            //setup redirect URL
            $redirect_url = $REDIRECTURL . urldecode($response['TOKEN']);
            ?>
            <a href="<?php echo $redirect_url; ?>" id='paypalBtn_<?php echo $r; ?>'>
                <img src='<?php echo paypalplus_path(); ?>paypal.gif' border='0' />
            </a>
            <script>
                var dg_<?php echo $r; ?> = new PAYPAL.apps.DGFlow({
                    trigger: "paypalBtn_<?php echo $r; ?>"
                });
            </script><?php
        } else if ($response['ACK'] == 'Failure' || $response['ACK'] == 'FailureWithWarning') {
            $redirect_url = ''; //SOMETHING FAILED
        }
    }
}

/**
 * Create a menu on the admin panel
 */
function paypalplus_admin_menu() {
    echo '<h3><a href="#">Paypal+ Options</a></h3>
        <ul> 
            <li><a href="' . osc_admin_render_plugin_url(osc_plugin_folder(__FILE__) . 'conf.php') . '">&raquo; ' . __('Paypal+ Options', 'paypalplus') . '</a></li>
            <li><a href="' . osc_admin_render_plugin_url(osc_plugin_folder(__FILE__) . 'conf_prices.php') . '">&raquo; ' . __('Categories fees', 'paypalplus') . '</a></li>
			<li><a href="' . osc_admin_render_plugin_url(osc_plugin_folder(__FILE__) . 'paypal_log.php') . '">&raquo; ' . __('Paypal+ Log', 'paypalplus') . '</a></li>
			<li><a href="' . osc_admin_render_plugin_url(osc_plugin_folder(__FILE__) . 'premium_log.php') . '">&raquo; ' . __('Premium Log', 'paypalplus') . '</a></li>
			<li><a href="' . osc_admin_render_plugin_url(osc_plugin_folder(__FILE__) . 'premiumplus_log.php') . '">&raquo; ' . __('PremiumPlus Log', 'paypalplus') . '</a></li>
			<li><a href="' . osc_admin_render_plugin_url(osc_plugin_folder(__FILE__) . 'publish_log.php') . '">&raquo; ' . __('Publish Log', 'paypalplus') . '</a></li>
        </ul>';
}

/**
 * Load paypal's js library
 */
function paypalplus_load_js() {
    echo '<script src="https://www.paypalobjects.com/js/external/dg.js" type="text/javascript"></script>';
}

/**
 * Redirect to function, for some reason "header" function was not working inside an "IF" clause
 *
 * @param string $url 
 */
function paypalplus_redirect_to($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Redirect to function via JS
 *
 * @param string $url 
 */
function paypalplus_js_redirect_to($url) {
    ?>
    <script type="text/javascript">
        window.location = "<?php echo $url; ?>"
    </script>
    <?php
}

/**
 * Redirect to payment page after publishing an item
 *
 * @param integer $item 
 */
function paypalplus_publish($item) {
    if (osc_get_preference('pay_per_post', 'paypalplus')) {
        // Check if it's already payed or not
        $conn = getConnection();
        // Item is not paid, continue
        $ppl_category = $conn->osc_dbFetchResult("SELECT f_publish_cost FROM %st_paypal_prices WHERE fk_i_category_id = %d", DB_TABLE_PREFIX, $item['fk_i_category_id']);
        if ($ppl_category && isset($ppl_category['f_publish_cost'])) {
            $category_fee = $ppl_category["f_publish_cost"];
        } else {
            $category_fee = osc_get_preference('default_publish_cost', 'paypalplus');
        }
        paypalplus_send_email($item, $category_fee);
        if ($category_fee > 0) {
            // Catch and re-set FlashMessages
            osc_resend_flash_messages();
            Item::newInstance()->update(array('b_enabled' => 0), array('pk_i_id' => $item['pk_i_id']));
            $conn->osc_dbExec("INSERT INTO %st_paypal_publish (fk_i_item_id, dt_date, b_paid) VALUES ('%d',  '%s',  '0')", DB_TABLE_PREFIX, $item['pk_i_id'], date('Y-m-d H:i:s'));
            paypalplus_redirect_to(osc_render_file_url(osc_plugin_folder(__FILE__) . 'payperpublish.php&itemId=' . $item['pk_i_id']));
        } else {
            // PRICE IS ZERO
            $conn->osc_dbExec("INSERT INTO  %st_paypal_publish (fk_i_item_id, dt_date, b_paid) VALUES ('%d',  '%s',  '1')", DB_TABLE_PREFIX, $item['pk_i_id'], date('Y-m-d H:i:s'));
        }
    } else {
        // NO NEED TO PAY PUBLISH FEE
        paypalplus_send_email($item, 0);
    }
    $category = Category::newInstance()->findByPrimaryKey($item['fk_i_category_id']);
    View::newInstance()->_exportVariableToView('category', $category);
    paypalplus_redirect_to(osc_search_category_url());
}

/**
 * Create a new menu option on users' dashboards
 */
function paypalplus_user_menu() {
    echo '<li class="opt_paypal" ><a href="' . osc_render_file_url(osc_plugin_folder(__FILE__) . "user_menu.php") . '" >' . __("Item payment status", "paypalplus") . '</a></li>';
    if ((osc_get_preference('pack_price_1', 'paypalplus') != '' && osc_get_preference('pack_price_1', 'paypalplus') != '0') || (osc_get_preference('pack_price_2', 'paypalplus') != '' && osc_get_preference('pack_price_2', 'paypalplus') != '0') || (osc_get_preference('pack_price_3', 'paypalplus') != '' && osc_get_preference('pack_price_3', 'paypalplus') != '0')) {
        echo '<li class="opt_paypal_pack" ><a href="' . osc_render_file_url(osc_plugin_folder(__FILE__) . "user_menu_pack.php") . '" >' . __("Buy credit for payments", "paypalplus") . '</a></li>';
    }
}

/**
 * Send email to un-registered users with payment options
 * 
 * @param integer $item
 * @param float $category_fee 
 */
function paypalplus_send_email($item, $category_fee) {

    if (osc_is_web_user_logged_in()) {
        return false;
    }

    $mPages = new Page();
    $aPage = $mPages->findByInternalName('email_paypal');
    $locale = osc_current_user_locale();
    $content = array();
    if (isset($aPage['locale'][$locale]['s_title'])) {
        $content = $aPage['locale'][$locale];
    } else {
        $content = current($aPage['locale']);
    }

    $item_url = osc_item_url();
    $item_url = '<a href="' . $item_url . '" >' . $item_url . '</a>';
    $publish_url = osc_render_file_url(osc_plugin_folder(__FILE__) . 'payperpublish.php&itemId=' . $item['pk_i_id']);
    $premium_url = osc_render_file_url(osc_plugin_folder(__FILE__) . 'makepremium.php&itemId=' . $item['pk_i_id']);

    $words = array();
    $words[] = array('{ITEM_ID}', '{CONTACT_NAME}', '{CONTACT_EMAIL}', '{WEB_URL}', '{ITEM_TITLE}',
        '{ITEM_URL}', '{WEB_TITLE}', '{PUBLISH_LINK}', '{PUBLISH_URL}', '{PREMIUM_LINK}', '{PREMIUM_URL}',
        '{START_PUBLISH_FEE}', '{END_PUBLISH_FEE}', '{START_PREMIUM_FEE}', '{END_PREMIUM_FEE}');
    $words[] = array($item['pk_i_id'], $item['s_contact_name'], $item['s_contact_email'], osc_base_url(), $item['s_title'],
        $item_url, osc_page_title(), '<a href="' . $publish_url . '">' . $publish_url . '</a>', $publish_url, '<a href="' . $premium_url . '">' . $premium_url . '</a>', $premium_url, '', '', '', '');

    if ($category_fee == 0) {
        $content['s_text'] = preg_replace('|{START_PUBLISH_FEE}(.*){END_PUBLISH_FEE}|', '', $content['s_text']);
    }

    $conn = getConnection();
    $ppl_category = $conn->osc_dbFetchResult("SELECT f_premium_cost FROM %st_paypal_prices WHERE fk_i_category_id = %d", DB_TABLE_PREFIX, $item['fk_i_category_id']);

    if ($ppl_category && isset($ppl_category['f_premium_cost']) && $ppl_category['f_premium_cost'] > 0) {
        $premium_fee = $ppl_category["f_premium_cost"];
    } else {
        $premium_fee = osc_get_preference("default_premium_cost", "paypalplus");
    }

    if ($premium_fee == 0) {
        $content['s_text'] = preg_replace('|{START_PREMIUM_FEE}(.*){END_PREMIUM_FEE}|', '', $content['s_text']);
    }

    $title = osc_mailBeauty($content['s_title'], $words);
    $body = osc_mailBeauty($content['s_text'], $words);

    $emailParams = array('subject' => $title
        , 'to' => $item['s_contact_email']
        , 'to_name' => $item['s_contact_name']
        , 'body' => $body
        , 'alt_body' => $body);

    osc_sendMail($emailParams);
}

/**
 * Add new options to supertoolbar plugin (if installed)
 */
function paypalplus_supertoolbar() {

    if (!osc_is_web_user_logged_in()) {
        return false;
    }

    if (Rewrite::newInstance()->get_location() != 'item') {
        return false;
    }

    if (osc_item_user_id() != osc_logged_user_id()) {
        return false;
    }

    $conn = getConnection();
    $toolbar = SuperToolBar::newInstance();

    if (osc_get_preference('pay_per_post', 'paypalplus')) {
        $paid = $conn->osc_dbFetchResult("SELECT b_paid FROM %st_paypal_publish WHERE fk_i_item_id = %d", DB_TABLE_PREFIX, osc_item_id());
        if (!$paid || (isset($paid) && $paid['b_paid'] == 0)) {
            $ppl_category = $conn->osc_dbFetchResult("SELECT f_publish_cost FROM %st_paypal_prices WHERE fk_i_category_id = %d", DB_TABLE_PREFIX, osc_item_category_id());
            if ($ppl_category && isset($ppl_category['f_publish_cost'])) {
                $category_fee = $ppl_category["f_publish_cost"];
            } else {
                $category_fee = osc_get_preference("default_publish_cost", "paypalplus");
            }
            if ($category_fee > 0) {
                $publish_url = osc_render_file_url(osc_plugin_folder(__FILE__) . "payperpublish.php&itemId=" . osc_item_id());
                $toolbar->addOption('<a href="' . $publish_url . '" />' . __("Pay to publish it", "superuser") . '</a>');
            }
        }
    }

    if (osc_get_preference('allow_premium', 'paypalplus')) {
        if (!paypalplus_is_premium(osc_item_id()) || !paypalplus_is_premium_plus(osc_item_id())) {
            $ppl_category = $conn->osc_dbFetchResult("SELECT f_premium_cost FROM %st_paypal_prices WHERE fk_i_category_id = %d", DB_TABLE_PREFIX, osc_item_category_id());
            if ($ppl_category && isset($ppl_category['f_premium_cost']) && $ppl_category['f_premium_cost'] > 0) {
                $category_fee = $ppl_category['f_premium_cost'];
            } else {
                $category_fee = osc_get_preference('default_premium_cost', 'paypalplus');
            }
            if ($category_fee > 0) {
                $premium_url = osc_render_file_url(osc_plugin_folder(__FILE__) . 'makepremium.php&itemId=' . osc_item_id());
                $toolbar->addOption('<a href="' . $premium_url . '" />' . __('Make premium', 'paypalplus') . ' ' . osc_get_preference("premium_days", "paypalplus") . __('days!', 'paypalplus') . '</a>');
            }
        }
    }

    if (osc_get_preference('allow_premium_plus', 'paypalplus')) {
        if (!paypalplus_is_premium_plus(osc_item_id()) || !paypalplus_is_premium(osc_item_id())) {
            $ppl_category = $conn->osc_dbFetchResult("SELECT f_premium_plus_cost FROM %st_paypal_prices WHERE fk_i_category_id = %d", DB_TABLE_PREFIX, osc_item_category_id());
            if ($ppl_category && isset($ppl_category['f_premium_plus_cost']) && $ppl_category['f_premium_plus_cost'] > 0) {
                $category_fee = $ppl_category['f_premium_plus_cost'];
            } else {
                $category_fee = osc_get_preference('default_premium_plus_cost', 'paypalplus');
            }
            if ($category_fee > 0) {
                $premium_url = osc_render_file_url(osc_plugin_folder(__FILE__) . 'makepremiump.php&itemId=' . osc_item_id());
                $toolbar->addOption('<a href="' . $premium_url . '" />' . __('Make premium', 'paypalplus') . ' ' . osc_get_preference("premium_plus_days", "paypalplus") . __('days!', 'paypalplus') . '</a>');
            }
        }
    }
}

/**
 * Executed hourly with cron to clean up the expired-premium ads
 */
function paypalplus_cron() {
    $conn = getConnection();
    $items = $conn->osc_dbFetchResults("SELECT fk_i_item_id FROM %st_paypal_premium WHERE TIMESTAMPDIFF(DAY,dt_date,'%s') >= %d", DB_TABLE_PREFIX, date('Y-m-d H:i:s'), osc_get_preference("premium_days", "paypalplus"));
    $mItem = new ItemActions(false);
    foreach ($items as $item) {
        $mItem->premium($item['fk_i_item_id'], false);
    }
    $conn->osc_dbExec("DELETE FROM %st_paypal_premium WHERE TIMESTAMPDIFF(DAY,dt_date,'%s') >= %d", DB_TABLE_PREFIX, date('Y-m-d H:i:s'), osc_get_preference("premium_days", "paypalplus"));
}

function paypalplus_plus_cron() {
    $conn = getConnection();
    $items = $conn->osc_dbFetchResults("SELECT fk_i_item_id FROM %st_paypal_premium_plus WHERE TIMESTAMPDIFF(DAY,dt_date,'%s') >= %d", DB_TABLE_PREFIX, date('Y-m-d H:i:s'), osc_get_preference("premium_plus_days", "paypalplus"));
    $mItem = new ItemActions(false);
    foreach ($items as $item) {
        $mItem->premium($item['fk_i_item_id'], false);
    }
    $conn->osc_dbExec("DELETE FROM %st_paypal_premium_plus WHERE TIMESTAMPDIFF(DAY,dt_date,'%s') >= %d", DB_TABLE_PREFIX, date('Y-m-d H:i:s'), osc_get_preference("premium_plus_days", "paypalplus"));
}

function paypalplus_bonus_cron() {
    $conn = getConnection();
    $conn->osc_dbExec("DELETE FROM %st_paypal_bonus WHERE dt_date < '%s' ", DB_TABLE_PREFIX, date('Y-m-d H:i:s'));
}

/**
 * Executed when an item is manually set to NO-premium to clean up it on the plugin's table
 * 
 * @param integer $id 
 */
function paypalplus_premium_off($id) {
    $conn = getConnection();
    $conn->osc_dbExec("DELETE FROM %st_paypal_premium WHERE fk_i_item_id = %d", DB_TABLE_PREFIX, $id);
    $conn->osc_dbExec("DELETE FROM %st_paypal_premium_plus WHERE fk_i_item_id = %d", DB_TABLE_PREFIX, $id);
}

/**
 * Executed before editing an item
 * 
 * @param array $item 
 */
function paypalplus_before_edit($item) {
    if (osc_get_preference('pay_per_post', 'paypalplus') == '1' || (osc_get_preference('allow_premium', 'paypalplus') == '1' && paypalplus_is_premium($item['pk_i_id'])) || (osc_get_preference('allow_premium_plus', 'paypalplus') == '1' && paypalplus_is_premium_plus($item['pk_i_id']))) {
        $cat[0] = Category::newInstance()->findByPrimaryKey($item['fk_i_category_id']);
        View::newInstance()->_exportVariableToView('categories', $cat);
    }
}

/**
 * Executed before showing an item
 * 
 * @param array $item 
 */
function paypalplus_show_item($item) {
    if (osc_get_preference("pay_per_post", "paypalplus") == "1" && !paypalplus_is_paid($item['pk_i_id'])) {
        $conn = getConnection();
        $ppl_category = $conn->osc_dbFetchResult("SELECT f_publish_cost FROM %st_paypal_prices WHERE fk_i_category_id = %d", DB_TABLE_PREFIX, $item['fk_i_category_id']);
        if ($ppl_category && isset($ppl_category['f_publish_cost'])) {
            $category_fee = $ppl_category["f_publish_cost"];
        } else {
            $category_fee = osc_get_preference("default_publish_cost", "paypalplus");
        }
        if ($category_fee > 0) {
            if ($item['fk_i_user_id'] != null && $item['fk_i_user_id'] == osc_logged_user_id()) {
                osc_add_flash_error_message(__('You need to pay the publish fee in order to make the ad public to the rest of users', 'paypalplus'));
                paypalplus_redirect_to(osc_render_file_url(osc_plugin_folder(__FILE__) . "payperpublish.php&itemId=" . $item['pk_i_id']));
            } else {
                osc_add_flash_error_message(__('Sorry, this ad is not available at the moment', 'paypalplus'));
                $category = Category::newInstance()->findByPrimaryKey($item['fk_i_category_id']);
                View::newInstance()->_exportVariableToView('category', $category);
                paypalplus_redirect_to(osc_search_category_url());
            }
        }
    }
}

function paypalplus_item_delete($itemId) {
    $conn = getConnection();
    $conn->osc_dbExec("DELETE FROM %st_paypal_premium WHERE fk_i_item_id = '%d'", DB_TABLE_PREFIX, $itemId);
    $conn->osc_dbExec("DELETE FROM %st_paypal_premium_plus WHERE fk_i_item_id = '%d'", DB_TABLE_PREFIX, $itemId);
    $conn->osc_dbExec("DELETE FROM %st_paypal_publish WHERE fk_i_item_id = '%d'", DB_TABLE_PREFIX, $itemId);
}

function paypalplus_configure_link() {
    paypalplus_redirect_to(osc_admin_render_plugin_url(osc_plugin_folder(__FILE__)) . 'conf.php');
}

/**
 * ADD HOOKS
 */
osc_register_plugin(osc_plugin_path(__FILE__), 'paypalplus_install');
osc_add_hook(osc_plugin_path(__FILE__) . "_configure", 'paypalplus_configure_link');
osc_add_hook(osc_plugin_path(__FILE__) . "_uninstall", 'paypalplus_uninstall');

osc_add_hook('admin_menu', 'paypalplus_admin_menu');
osc_add_hook('header', 'paypalplus_load_js');
osc_add_hook('posted_item', 'paypalplus_publish');
osc_add_hook('user_menu', 'paypalplus_user_menu');
osc_add_hook('supertoolbar_hook', 'paypalplus_supertoolbar');
osc_add_hook('cron_hourly', 'paypalplus_cron');
osc_add_hook('cron_hourly', 'paypalplus_plus_cron');
osc_add_hook('cron_daily', 'paypalplus_bonus_cron');
osc_add_hook('item_premium_off', 'paypalplus_premium_off');
osc_add_hook('before_item_edit', 'paypalplus_before_edit');
osc_add_hook('show_item', 'paypalplus_show_item');
osc_add_hook('delete_item', 'paypalplus_item_delete');
?>