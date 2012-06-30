<?php
    /*
     *      OSCLass – software for creating and publishing online classified
     *                           advertising platforms
     *
     *                        Copyright (C) 2010 OSCLASS
     *
     *       This program is free software: you can redistribute it and/or
     *     modify it under the terms of the GNU Affero General Public License
     *     as published by the Free Software Foundation, either version 3 of
     *            the License, or (at your option) any later version.
     *
     *     This program is distributed in the hope that it will be useful, but
     *         WITHOUT ANY WARRANTY; without even the implied warranty of
     *        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     *             GNU Affero General Public License for more details.
     *
     *      You should have received a copy of the GNU Affero General Public
     * License along with this program.  If not, see <http://www.gnu.org/licenses/>.
     */
	// paypal+ version
	$thisversion ='1.0.5';
		
    if(Params::getParam('plugin_action')=='done') {
        osc_set_preference('default_premium_cost', Params::getParam("default_premium_cost") ? Params::getParam("default_premium_cost") : '1.0', 'paypalplus', 'STRING');
		osc_set_preference('default_premium_plus_cost', Params::getParam("default_premium_plus_cost") ? Params::getParam("default_premium_plus_cost") : '2.0', 'paypalplus', 'STRING');
        osc_set_preference('allow_premium', Params::getParam("allow_premium") ? Params::getParam("allow_premium") : '0', 'paypalplus', 'BOOLEAN');
		osc_set_preference('allow_bonus', Params::getParam("allow_bonus") ? Params::getParam("allow_bonus") : '0', 'paypalplus', 'BOOLEAN');
		osc_set_preference('allow_premium_plus', Params::getParam("allow_premium_plus") ? Params::getParam("allow_premium_plus") : '0', 'paypalplus', 'BOOLEAN');
		osc_set_preference('allow_bonus_expiration', Params::getParam("allow_bonus_expiration") ? Params::getParam("allow_bonus_expiration") : '0', 'paypalplus', 'BOOLEAN');
        osc_set_preference('default_publish_cost', Params::getParam("default_premium_cost") ? Params::getParam("default_publish_cost") : '1.0', 'paypalplus', 'STRING');
        osc_set_preference('pay_per_post', Params::getParam("pay_per_post") ? Params::getParam("pay_per_post") : '0', 'paypalplus', 'BOOLEAN');
        osc_set_preference('premium_days', Params::getParam("premium_days") ? Params::getParam("premium_days") : '7', 'paypalplus', 'INTEGER');
		osc_set_preference('bonus_days', Params::getParam("bonus_days") ? Params::getParam("bonus_days") : '60', 'paypalplus', 'INTEGER');
		osc_set_preference('premium_plus_days', Params::getParam("premium_plus_days") ? Params::getParam("premium_plus_days") : '14', 'paypalplus', 'INTEGER');
		osc_set_preference('auto_enable', Params::getParam("auto_enable") ? Params::getParam("auto_enable") : '0', 'paypalplus', 'BOOLEAN');
        osc_set_preference('currency', Params::getParam("currency") ? Params::getParam("currency") : 'USD', 'paypalplus', 'STRING');
        osc_set_preference('api_username', paypal_crypt(Params::getParam("api_username")), 'paypalplus', 'STRING');
        osc_set_preference('api_password', paypal_crypt(Params::getParam("api_password")), 'paypalplus', 'STRING');
        osc_set_preference('api_signature', paypal_crypt(Params::getParam("api_signature")), 'paypalplus', 'STRING');
        echo '<div style="text-align:center; font-size:22px; background-color:#00bb00;"><p>' . __('Congratulations. The plugin is now configured', 'paypalplus') . '.</p></div>' ;
        osc_set_preference('pack_price_1', Params::getParam("pack_price_1"), 'paypalplus', 'STRING');
        osc_set_preference('pack_price_2', Params::getParam("pack_price_2"), 'paypalplus', 'STRING');
        osc_set_preference('pack_price_3', Params::getParam("pack_price_3"), 'paypalplus', 'STRING');
		osc_set_preference('bonus_pack_1', Params::getParam("bonus_pack_1"), 'paypalplus', 'STRING');
        osc_set_preference('bonus_pack_2', Params::getParam("bonus_pack_2"), 'paypalplus', 'STRING');
        osc_set_preference('bonus_pack_3', Params::getParam("bonus_pack_3"), 'paypalplus', 'STRING');
        osc_set_preference('email', Params::getParam("email"), 'paypalplus', 'STRING');
        //osc_set_preference('pdt', Params::getParam("pdt"), 'paypal', 'STRING');
        osc_set_preference('standard', Params::getParam("standard_payment") ? Params::getParam("standard_payment") : '0', 'paypalplus', 'BOOLEAN');
        osc_set_preference('sandbox', Params::getParam("sandbox") ? Params::getParam("sandbox") : '0', 'paypalplus', 'BOOLEAN');
        osc_reset_preferences();
    }
	
	 if(Params::getParam('plugin_action')=='erase') {
		$conn  = getConnection();
		$conn->osc_dbExec("DELETE FROM %st_paypal_bonus WHERE dt_date is null ", DB_TABLE_PREFIX ); 
		echo '<div style="text-align:center; font-size:22px; background-color:#00bb00;"><p>' . __('All the bonus without expiration deleted ', 'paypalplus') . '.</p></div>' ;
	 }
	 
	//upgrade from paypal plugin 
	if(Params::getParam('plugin_action')=='upgrade') {
		$conn  = getConnection();
		$conn->osc_dbExec("ALTER TABLE %st_paypal_log CHANGE `s_code` `s_code` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL", DB_TABLE_PREFIX);
		$conn->osc_dbExec("UPDATE %st_paypal_log SET i_product_type = '401' WHERE i_product_type = '301'", DB_TABLE_PREFIX);
		$conn->osc_dbExec("ALTER TABLE %st_paypal_prices ADD `premium_plus_cost` FLOAT NULL ",  DB_TABLE_PREFIX);
		osc_set_preference('version', '1.0.5', 'paypalplus', 'BOOLEAN');
		osc_set_preference('auto_enable', '0', 'paypalplus', 'BOOLEAN');
		$conn->autocommit(true);
		echo '<div style="text-align:center; font-size:22px; background-color:#00bb00;"><p>' . __('Paypal+ Upgraded succesfully! ', 'paypalplus') . '.</p></div>' ;
	 }
	 
	 //update paypalplus
	 if(Params::getParam('plugin_action')=='update') { 
	 	$conn  = getConnection();
		$conn->osc_dbExec("ALTER TABLE %st_paypal_prices ADD `premium_plus_cost` FLOAT NULL ",  DB_TABLE_PREFIX);
		osc_set_preference('version', '1.0.5', 'paypalplus', 'BOOLEAN');
		osc_set_preference('auto_enable', '0', 'paypalplus', 'BOOLEAN');
		$conn->autocommit(true);
		echo '<div style="text-align:center; font-size:22px; background-color:#00bb00;"><p>' . __('Paypal+ Upgraded succesfully! ', 'paypalplus') . '.</p></div>' ;
	}
		
	 $version=osc_get_preference('version','paypalplus'); 
	 echo sprintf(__('Paypal+ Version %s ','paypalplus'), $version);
		if ($version < $thisversion || $version==null) { ?>
			<form name="paypal_form" id="paypal_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
                    <input type="hidden" name="page" value="plugins" />
                    <input type="hidden" name="action" value="renderplugin" />
                    <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>conf.php" />
                    <input type="hidden" name="plugin_action" value="update" />
				<button type="submit" style="float: left;"><?php _e('Upgrade to new version!', 'paypalplus');?></button>
            </form>
		<? } ?>
<? $path = str_replace(osc_plugins_path(), 'paypalplus', $path); echo $path;?>
<div id="settings_form" style="border: 1px solid #ccc; background: #eee;  ">
    <div style="padding: 20px;">
        <h2> <? echo _e('Paypal+ Options', 'paypalplus'); ?> </h2> 
       		<div style="float: left; width: 100%;">
            <fieldset style=" border: 1px solid #BBBBBB; border-radius: 3px 3px 3px 3px; margin: 10px; margin: 10px;">
                <legend><strong><?php _e('Paypal Account', 'paypalplus'); ?></strong></legend>
                <form name="paypal_form" id="paypal_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
                    <input type="hidden" name="page" value="plugins" />
                    <input type="hidden" name="action" value="renderplugin" />
                    <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>conf.php" />
                    <input type="hidden" name="plugin_action" value="done" />
                    <div style="float: left; width: 100%;">
                      <table width="100%" border="0" cellspacing="0" cellpadding="0">
 						<tr> 						 
                        	<td width="150px"><label><?php _e('API username', 'paypalplus'); ?></label></td><td><input type="text"  name="api_username" id="api_username" value="<?php echo paypal_decrypt(osc_get_preference('api_username', 'paypalplus')); ?>" />
                            </td>
                        </tr>
                        <br/>
                        <tr> 						 
                        	<td width="150px"><label><?php _e('API password', 'paypalplus'); ?></label></td><td><input type="password" name="api_password" id="api_password" value="<?php echo paypal_decrypt(osc_get_preference('api_password', 'paypalplus')); ?>" />
                        <br/>
                        	</td>
                        </tr>
                        <tr> 						 
                        	<td width="150px"><label><?php _e('API signature', 'paypalplus'); ?></label></td><td><input type="text" name="api_signature" id="api_signature" value="<?php echo paypal_decrypt(osc_get_preference('api_signature', 'paypalplus')); ?>" />
                        <br/>
                        	</td>
                        </tr>
                        <tr> 						 
                        	<td width="150px"><label><?php _e('Paypal email', 'paypalplus'); ?></label></td><td><input type="text" name="email" id="email" value="<?php echo osc_get_preference('email', 'paypalplus'); ?>" />
                        <br/>
                        <?php /*<label><?php _e('PDT', 'paypal'); ?></label><input type="text" name="pdt" id="pdt" value="<?php echo osc_get_preference('pdt', 'paypal'); ?>" />
                        <br/> */ ?>
                        	</td>
                        </tr>
                     </table>
                     <table>  
                        <tr>                         					 
                        	<td width="300px"><br /><input style="height: 20px; padding-left: 4px;padding-top: 4px;" type="checkbox" <?php echo (osc_get_preference('standard', 'paypalplus') ? 'checked="true"' : ''); ?> name="standard_payment" id="standard_payment" value="1" />
                        <label for="standard_payment"><?php _e('Use standard payment', 'paypalplus'); ?></label>
                        	</td>
                            <td><br /><input style="height: 20px; padding-left: 4px;padding-top: 4px;" type="checkbox" <?php echo (osc_get_preference('sandbox', 'paypalplus') ? 'checked="true"' : ''); ?> name="sandbox" id="sandbox" value="1" /><label for="sandbox"><?php _e('Sandbox environment', 'paypalplus'); ?></label>
                        	</td>
                        </tr>
                     </table>
                     <br/>
                   </div>
          </fieldset>
          <fieldset style=" border: 1px solid #BBBBBB; border-radius: 3px 3px 3px 3px; margin: 10px; margin: 10px;">
               <legend><strong><?php _e('Premium Ads', 'paypalplus'); ?></strong></legend>
                    <div style="float: left; width: 50%;">
                        <table>
                        	<tr>
                        		<td width="200px">
                        <input style="height: 20px; padding-left: 4px;padding-top: 4px;" type="checkbox" <?php echo (osc_get_preference('allow_premium', 'paypalplus') ? 'checked="true"' : ''); ?> name="allow_premium" id="allow_premium" value="1" /><label for="allow_premium"><?php _e('Allow premium ads', 'paypalplus'); ?></label>
                        <br /><br />
                        		</td>
                           </tr>
                        	<tr>
                            	<td width="200px">
                                   <label><?php _e('Default premium cost', 'paypalplus'); ?></label></td><td><input type="text" name="default_premium_cost" id="default_premium_cost" value="<?php echo osc_get_preference('default_premium_cost', 'paypalplus'); ?>" />
                                </td>
                            </tr>
                        	<tr>
                            	<td width="200px">                       
                        			<label><?php _e('Premium days', 'paypalplus'); ?></label></td><td><input type="text" name="premium_days" id="premium_days" value="<?php echo osc_get_preference('premium_days', 'paypalplus'); ?>" />
                        		<br/>
                        		</td>
                        	</tr>
                        </table>
                        <br /><br />
                    </div>
         </fieldset>               
          <fieldset style=" border: 1px solid #BBBBBB; border-radius: 3px 3px 3px 3px; margin: 10px; margin: 10px;">
               <legend><strong><?php _e('PremiumPlus Ads', 'paypalplus'); ?></strong></legend>              
                     <div style="float: left; width: 50%;">
                        <table>
                        	<tr>
                        		<td width="200px">
                        			<input style="height: 20px; padding-left: 4px;padding-top: 4px;" type="checkbox" <?php echo (osc_get_preference('allow_premium_plus', 'paypalplus') ? 'checked="true"' : ''); ?> name="allow_premium_plus" id="allow_premium_plus" value="1" /> <label for="allow_premium_plus"><?php _e('Allow premium Plus ads', 'paypalplus'); ?></label>
                        		<br/><br />
                        		</td>
                           </tr>
                        	<tr>
                        		<td width="200px"><label><?php _e('Default premium Plus cost', 'paypalplus'); ?></label></td><td><input type="text" name="default_premium_plus_cost" id="default_premium_plus_cost" value="<?php echo osc_get_preference('default_premium_plus_cost', 'paypalplus'); ?>" />
                       			<br/>
                        		</td>
                        	</tr>
                        	<tr>
                        		<td width="200px">
                        			<label><?php _e('Premium Plus days', 'paypalplus'); ?></label></td><td><input type="text" name="premium_plus_days" id="premium_plus_days" value="<?php echo osc_get_preference('premium_plus_days', 'paypalplus'); ?>" />
                        		</td>
                        	</tr>
                        </table>
                        <br/>
                     </div>
                     <br />
      	</fieldset>               
          <fieldset style=" border: 1px solid #BBBBBB; border-radius: 3px 3px 3px 3px; margin: 10px; margin: 10px;">
                <legend><strong><?php  _e('Other Options', 'paypalplus'); ?></strong></legend>
                    <table>
                        <tr>                         
                        <input style="height: 20px; padding-left: 4px;padding-top: 4px;" type="checkbox" <?php echo (osc_get_preference('pay_per_post', 'paypalplus') ? 'checked="true"' : ''); ?> name="pay_per_post" id="pay_per_post" value="1" /> <label for="pay_per_post"><?php _e('Pay per post ads', 'paypalplus'); ?></label>
                         
                          <br />                      
                        <input style="height: 20px; padding-left: 4px;padding-top: 4px;" type="checkbox" <?php echo (osc_get_preference('auto_enable', 'paypalplus') ? 'checked="true"' : ''); ?> name="auto_enable" id="auto_enable" value="1" /> <label for="auto_enable"><?php _e('Auto enable item if user make it Premium/PremiumPlus', 'paypalplus'); ?></label>
                         
                        </tr>
                        <tr>
                        	<td width="200px">                        
                       
                        <label><?php _e('Default publish cost', 'paypalplus'); ?></label></td><td><input type="text" name="default_publish_cost" id="default_publish_cost" value="<?php echo osc_get_preference('default_publish_cost', 'paypalplus'); ?>" />
                        	<br/><br />
                        	</td>
                        </tr>
                        <tr>
                        	<td width="200px">
                        		<label><?php _e('Currency (3-character code)', 'paypalplus'); ?></label>
                        	</td>
                       		<td>
                        		<select name="currency" id="currency">
                            		<option value="AUD" <?php if(osc_get_preference('currency', 'paypalplus')=="AUD") { echo 'selected="selected"';}; ?> >AUD</option>
		                            <option value="CAD" <?php if(osc_get_preference('currency', 'paypalplus')=="CAD") { echo 'selected="selected"';}; ?> >CAD</option>
		                            <option value="CHF" <?php if(osc_get_preference('currency', 'paypalplus')=="CHF") { echo 'selected="selected"';}; ?> >CHF</option>
        		                    <option value="CZK" <?php if(osc_get_preference('currency', 'paypalplus')=="CZK") { echo 'selected="selected"';}; ?> >CZK</option>
                		            <option value="DKK" <?php if(osc_get_preference('currency', 'paypalplus')=="DKK") { echo 'selected="selected"';}; ?> >DKK</option>
                        		    <option value="EUR" <?php if(osc_get_preference('currency', 'paypalplus')=="EUR") { echo 'selected="selected"';}; ?> >EUR</option>
		                            <option value="GBP" <?php if(osc_get_preference('currency', 'paypalplus')=="GBP") { echo 'selected="selected"';}; ?> >GBP</option>
        		                    <option value="HKD" <?php if(osc_get_preference('currency', 'paypalplus')=="HKD") { echo 'selected="selected"';}; ?> >HKD</option>
                		            <option value="HUF" <?php if(osc_get_preference('currency', 'paypalplus')=="HUF") { echo 'selected="selected"';}; ?> >HUF</option>
                        		    <option value="JPY" <?php if(osc_get_preference('currency', 'paypalplus')=="JPY") { echo 'selected="selected"';}; ?> >JPY</option>
		                            <option value="NOK" <?php if(osc_get_preference('currency', 'paypalplus')=="NOK") { echo 'selected="selected"';}; ?> >NOK</option>
        		                    <option value="NZD" <?php if(osc_get_preference('currency', 'paypalplus')=="NZD") { echo 'selected="selected"';}; ?> >NZD</option>
                		            <option value="PLN" <?php if(osc_get_preference('currency', 'paypalplus')=="PLN") { echo 'selected="selected"';}; ?> >PLN</option>
                        		    <option value="SEK" <?php if(osc_get_preference('currency', 'paypalplus')=="SEK") { echo 'selected="selected"';}; ?> >SEK</option>
		                            <option value="SGD" <?php if(osc_get_preference('currency', 'paypalplus')=="SGD") { echo 'selected="selected"';}; ?> >SGD</option>
		                            <option value="USD" <?php if(osc_get_preference('currency', 'paypalplus')=="USD") { echo 'selected="selected"';}; ?> >USD</option>
        		                </select>
                        	<br/><br /><br />
                        	</td>
                        </tr>
                     </table>
                  </div>
                   
             </fieldset>  
                   <br />
                   <br />
                   <br />
             <fieldset style=" border: 1px solid #BBBBBB; border-radius: 3px 3px 3px 3px; margin: 10px; margin: 10px;">
                 <legend><strong><?php  _e('Packs And Bonus Credit', 'paypalplus'); ?></strong></legend>
                   <div style="float: left; width: 100%;">
                    <div style="float: left; width: 30%;">
						<label><strong><?php echo __('Packs', 'paypalplus'); ?>*</strong></label>
                        <br/><br />
                        <label><?php echo sprintf(__('Price of pack #%d', 'paypalplus'), '1'); ?></label><br /><input type="text"  name="pack_price_1" id="pack_price_1" value="<?php echo osc_get_preference('pack_price_1', 'paypalplus'); ?>" />
                        <br/>
                        <label><?php echo sprintf(__('Price of pack #%d', 'paypalplus'), '2'); ?></label><br /><input type="text"  name="pack_price_2" id="pack_price_2" value="<?php echo osc_get_preference('pack_price_2', 'paypalplus'); ?>" />
                        <br/>
                        <label><?php echo sprintf(__('Price of pack #%d', 'paypalplus'), '3'); ?></label><br /><input type="text"  name="pack_price_3" id="pack_price_3" value="<?php echo osc_get_preference('pack_price_3', 'paypalplus'); ?>" />
                        <br/>
                        
                    </div>
                    <div style="float: left; width: 20%;">
						<input type="checkbox" <?php echo (osc_get_preference('allow_bonus', 'paypalplus') ? 'checked="true"' : ''); ?> name="allow_bonus" id="allow_bonus" value="1" /> <label><strong><?php echo __('Allow Bonus', 'paypalplus'); ?>**</strong></label>
                        <br/><br />
                        <label> <?php echo sprintf(__('Bonus for pack #%d', 'paypalplus'), '1'); ?></label><br /><input type="text"  style="width:60px" name="bonus_pack_1" id="bonus_pack_1" value="<?php echo osc_get_preference('bonus_pack_1', 'paypalplus'); ?>" />%
                        <br/>
                        <label> <?php echo sprintf(__('Bonus for pack #%d', 'paypalplus'), '2'); ?></label><br /><input type="text" style="width:60px" name="bonus_pack_2" id="bonus_pack_2" value="<?php echo osc_get_preference('bonus_pack_2', 'paypalplus'); ?>" />%
                        <br/>
                        <label> <?php echo sprintf(__('Bonus for pack #%d', 'paypalplus'), '3'); ?></label><br /><input type="text" style="width:60px" name="bonus_pack_3" id="bonus_pack_3" value="<?php echo osc_get_preference('bonus_pack_3', 'paypalplus'); ?>" />%
                        <br/>
                       
                    </div>
                    <div style="float: left; width: 30%;">
						<input type="checkbox" <?php echo (osc_get_preference('allow_bonus_expiration', 'paypalplus') ? 'checked="true"' : ''); ?> name="allow_bonus_expiration" id="allow_bonus_expiration" value="1" /> <label><strong><?php echo __('Allow Bonus Expiration', 'paypalplus'); ?>***</strong></label>
                        <br/><br />
                        <label> <?php echo __('Bonus expiration days', 'paypalplus'); ?></label><br /><input type="text"  style="width:60px" name="bonus_days" id="bonus_days" value="<?php echo osc_get_preference('bonus_days', 'paypalplus'); ?>" />
                     	<br />                  
                    </div>
                    	<br />
                    <div style="float: left; width: 100%;">
                        <p>
                           * <?php _e("You could specify up to 3 'packs' that users can buy, so they don't need to pay each time they publish an ad. The credit from the pack will be stored for later uses.",'paypalplus'); ?>
                        </p>                        
                    </div>
                    <div style="float: left; width: 100%;">
                        <p>
                           ** <?php _e("You could specify a percentage of the bonus that the user gets buying a package. User can have a bonus only if you allow bonus. Disable bonus will take effects only for new purchase and users bonus can be still used.",'paypalplus'); ?>
                        </p>
                        
                    </div>
                    <div style="float: left; width: 100%;">
                        <p>
                           *** <?php _e("You could specify if user bonus have to expire. This will take effects only for new purchase.",'paypalplus'); ?>
                        </p>
                        <br/>
                    </div>
                    <div style="float: left; width: 100%;">
                        <p>
                           <?php _e("NOTE: The bonus credit is not cumulative. When finished, users can buy a new pack for more bonus credit.",'paypalplus'); ?>
                        </p>
                        <br/>
                    </div>
                    </div>
                     
                    <button type="submit" style="float: right;"><?php _e('Update', 'paypalplus');?></button>
                </form>
            </fieldset>
       <fieldset style=" border: 1px solid #BBBBBB; border-radius: 3px 3px 3px 3px; margin: 10px; margin: 10px;">     		     	
                    <form name="erase_form" id="erase_form" action="<?php echo osc_admin_base_url(true); ?>" method="POST" enctype="multipart/form-data" >
                    <input type="hidden" name="page" value="plugins" />
                    <input type="hidden" name="action" value="renderplugin" />
                    <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>conf.php" />
                    <input type="hidden" name="plugin_action" value="erase" />
       				<legend><?php  _e('Advanced', 'paypalplus'); ?></legend> 
                    <label> <?php  _e('Delete all users bonus without Expiration time.', 'paypalplus'); ?><strong> <? echo _e('Use this option careful','paypalplus'); ?></strong></label>
                        
                    <button type="submit"  id="butErase"  name="butErase" ><?php _e('Delete', 'paypalplus');?></button>
                </form>
       </fieldset>
        </div>
        
        <div style="float: left; width: 100%;">
            <fieldset style=" border: 1px solid #BBBBBB; border-radius: 3px 3px 3px 3px; margin: 10px; margin: 10px;">
                <legend><?php _e('Help', 'paypalplus'); ?></legend>
                <h3><?php _e('API or Standard Payments?', 'paypalplus'); ?></h3>
                <p>
                    <?php _e('API payments give you more control over the payment process, it\'s required for digital goods & micropayments (Note: Not all countries are allowed to have digital goods & micropayments processes). On the other side standard payments are simple, less customizable but works everywhere.', 'paypalplus'); ?>.
                    <br/>
                    <?php _e('Micropayments offers a reduction on the fee to pay Paypal for orders under 4$ (or equivalent), around 5cents + 5% while standard payments have a fee around 30cents + 5%. Due the nature of OSClass is recommended to use micropayments, but we\'re aware that they\'re not available worldwide. Please check with Paypal the avalaibility of the service in your area.', 'paypalplus'); ?>.
                    <br/>
                </p>
                <h3><?php _e('Setting up your Paypal account for Standard Payments', 'paypalplus'); ?></h3>
                <p>
                    <?php _e('Introduce your paypal email and check the "Use Standard Payment" option here.', 'paypalplus'); ?>.
                    <br/>
                    <?php _e('You need Paypal API credentials (before entering here your API credentials, MODIFY index.php file of this plugin and change the value of PAYPAL_CRYPT_KEY variable to make your API more secure)', 'paypalplus'); ?>.
                    <br/>
                    <?php _e('You need to tell Paypal where is your IPN file', 'paypalplus'); ?>
                </p>
                <h3><?php _e('Setting up your Paypal account for micropayments/API', 'paypalplus'); ?></h3>
                <p>
                    <?php _e('Before being able to use Paypal plugin, you need to set up some configuration at your Paypal account', 'paypalplus'); ?>.
                    <br/>
                    <?php _e('Your Paypal account has to be set as Business or Premier, you could change that at Your Profile, under My Settings', 'paypalplus'); ?>.
                    <br/>
                    <?php echo sprintf( __('You need to sign in up for micropayments/digital good <a href="%s">from here</a>.', 'paypalplus'), 'https://merchant.paypal.com/cgi-bin/marketingweb?cmd=_render-content&content_ID=merchant/digital_goods'); ?>.
                    <br/>
                    <?php _e('You need Paypal API credentials (before entering here your API credentials, MODIFY index.php file of this plugin and change the value of PAYPAL_CRYPT_KEY variable to make your API more secure)', 'paypalplus'); ?>.
                    <br/>
                    <?php _e('You need to tell Paypal where is your IPN file', 'paypalplus'); ?>
                </p>
                <h3><?php _e('Setting up your IPN', 'paypalplus'); ?></h3>
                <p>
                    <?php _e('Click Profile on the My Account tab', 'paypalplus'); ?>.
                    <br/>
                    <?php _e('Click Instant Payment Notification Preferences in the Selling Preferences column', 'paypalplus'); ?>.
                    <br/>
                    <?php _e("Click Choose IPN Settings to specify your listener’s URL and activate the listener (usually is http://www.yourdomain.com/oc-content/plugins/paypal/notify_url.php)", 'paypalplus'); ?>.
                </p>
                <h3><?php _e('How to obtain API credentials', 'paypalplus'); ?></h3>
                <p>
                    <?php _e('In order to use the Paypal plugin you will need Paypal API credentials, you could obtain them for free following theses steps', 'paypalplus'); ?>:
                    <br/>
                    <?php _e('Verify your account status. Go to your PayPal Profile under My Settings and verify that your Account Type is Premier or Business, or upgrade your account', "paypalplus"); ?>.
                    <br/>
                    <?php _e('Verify your API settings. Click on My Selling Tools. Click Selling Online and verify your API access. Click Update to view or set up your API signature and credentials', 'paypalplus'); ?>.
                </p>
            </fieldset>
        </div>
        
    </div>
</div>