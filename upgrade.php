<?php
/*
Plugin Name: PaypalPlus payment
Plugin URI: http://www.osclass.org/
Description: Paypal payment options
Version: 2.0.3
Author: OSClass . New features by Cris
Author URI: http://www.osclass.org/
Short Name: paypalplus
*/


    // load necessary functions

  
	    $conn = getConnection() ;
        $path = osc_plugin_resource('paypalplus/struct.sql');
        $sql  = file_get_contents($path);
        $conn->osc_dbImportSQL($sql);

        
        osc_set_preference('allow_premium_plus', '0', 'paypal', 'BOOLEAN');
		$conn->autocommit(true);
		echo "done";
  		 Plugins::runHook('paypal_uninstall') ;
                    Plugins::uninstall('paypal');

                    osc_add_flash_ok_message( _m('Plugin uninstalled'), 'admin');
?>
