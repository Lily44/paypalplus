<?php 
    $itemsPerPage = (Params::getParam('itemsPerPage') != '') ? Params::getParam('itemsPerPage') : 5;
    $page         = (Params::getParam('iPage') != '') ? Params::getParam('iPage') : 0;
    $total_items  = Item::newInstance()->countByUserID($_SESSION['userId']);
    $total_pages  = ceil($total_items/$itemsPerPage);
    $items        = Item::newInstance()->findByUserID($_SESSION['userId'], $page * $itemsPerPage, $itemsPerPage);

    View::newInstance()->_exportVariableToView('items', $items);
    View::newInstance()->_exportVariableToView('list_total_pages', $total_pages);
    View::newInstance()->_exportVariableToView('list_total_items', $total_items);
    View::newInstance()->_exportVariableToView('items_per_page', $itemsPerPage);
    View::newInstance()->_exportVariableToView('list_page', $page); 
	
	$user = User::newInstance()->findByPrimaryKey(osc_logged_user_id());
    $conn = getConnection();
    $wallet = $conn->osc_dbFetchresult("SELECT * FROM %st_paypal_wallet WHERE fk_i_user_id = %d", DB_TABLE_PREFIX, osc_logged_user_id());
    $amount = isset($wallet['f_amount'])?$wallet['f_amount']:0;
	$bonuscr = $conn->osc_dbFetchresult("SELECT * FROM %st_paypal_bonus WHERE fk_i_user_id = %d", DB_TABLE_PREFIX, osc_logged_user_id());
	$bonusamount =  isset($bonuscr['f_bonus'])?$bonuscr['f_bonus']:0;
	//expiration days
	$expire =  isset($bonuscr['dt_date'])?$bonuscr['dt_date']:0;
	$expire = explode('-', $expire);
	$today= date('Y-m-d');
	$today= explode('-', $today);
	$timestampA = mktime(0, 0, 0, $today[1], $today[2], $today[0]);
	$timestampB = mktime(0, 0, 0, $expire[1], $expire[2], $expire[0]);
	$diff = floor(($timestampB - $timestampA) / (3600 * 24));
	
?>
    <div class="content user_account">
       <h1>
          <strong><?php _e('User account manager', 'paypalplus') ; ?></strong>
       </h1>
       <div id="sidebar">
          <?php echo osc_private_user_menu() ; ?>
             </div>
                <div id="main">
                  <h2><?php _e('Paypal & your items', 'paypalplus'); ?></h2>
                  <h2><?php echo sprintf(__('Your current credit is %.2f %s', 'paypalplus'), $amount, osc_get_preference('currency', 'paypalplus')); ?></h2>
                   
                  <?php if ($bonusamount>'0')  { ?>
					<h2 style="color:#060"> <?php echo sprintf(__('Your current Bonus credit is %.2f %s', 'paypalplus'), $bonusamount, osc_get_preference('currency', 'paypalplus'));
						if ($diff>'0')
						 	echo ' '.sprintf(__('maturing in %d days', 'paypalplus'), $diff);
						?>
                    </h2>
                  <?php } ?>
                    
				  <?php if(osc_count_items() == 0) { ?>
                        <h3><?php _e('You don\'t have any items yet', 'paypalplus'); ?></h3>
                  <?php } else { ?>
                     <?php while(osc_has_items()) { ?>
                           <div class="item" >
                           <h3>
                               <a href="<?php echo osc_item_url(); ?>"><?php echo osc_item_title(); ?></a>
                           </h3>
                               <p>
                               <?php _e('Publication date', 'paypalplus') ; ?>: <?php echo osc_format_date(osc_item_pub_date()) ; ?><br />
                               <?php _e('Price', 'paypalplus') ; ?>: <?php echo osc_format_price(osc_item_price()); ?>
                               </p>
                               <p class="options">
                               <?php if(osc_item_is_active()=='1') { ?>   
										
								   <?php if(osc_get_preference("pay_per_post", "paypalplus")=="1") { ?>
                                   	  <?php if(paypalplus_is_paid(osc_item_id())) { ?>
                                       	    <strong><?php _e('Paid!', 'paypalplus'); ?></strong>
                                   	  <?php } else { ?>
                                     		<strong><a href="<?php echo osc_render_file_url(osc_plugin_folder(__FILE__)."payperpublish.php&itemId=".osc_item_id()); ?>"><?php _e('Pay for this item', 'paypalplus'); ?></a></strong>
                                      <?php } ?>
                                   <?php } ?>
                                   <?php if(osc_get_preference("pay_per_post", "paypalplus")=="1" && osc_get_preference("allow_premium", "paypalplus")=="1") { ?>
                                   	    <span>|</span>
                                   <?php } ?>
                                            	
                                   <?php if (( paypalplus_item_is_enabled()=='1') || (osc_get_preference("auto_enable", "paypalplus")=="1"))  { ?>
                                                
										<?php if(osc_get_preference("allow_premium", "paypalplus")=="1") { ?>
                                        	<?php if(paypalplus_is_premium(osc_item_id())) { ?> 
                                            	<strong><?php _e('Already premium', 'paypalplus'); echo ' '. paypalplus_premium_days(osc_item_id()); _e('days!', 'paypalplus');?></strong>
                                            <?php  }  else { 
															if (!paypalplus_is_premium_plus(osc_item_id())) { ?>
                                                   			 <strong><a href="<?php echo osc_render_file_url(osc_plugin_folder(__FILE__)."makepremium.php&itemId=".osc_item_id()); ?>"><?php _e('Make premium', 'paypalplus'); echo  ' '.osc_get_preference("premium_days", "paypalplus"); _e('days!', 'paypalplus');?></a></strong>
                                            <?php } } ?>
                                       <?php } ?>
                                            
                                            <!-- premium plus -->
                                           
										<?php if(osc_get_preference("allow_premium_plus", "paypalplus")=="1") { ?>
                                        	<?php if(paypalplus_is_premium_plus(osc_item_id())) { ?>
                                            	<strong><?php _e('Already premium', 'paypalplus'); echo ' '. paypalplus_premium_days(osc_item_id()); _e('days!', 'paypalplus');?></strong>
                                            <?php } else { 
                                                     	if (!paypalplus_is_premium(osc_item_id())) { ?>													 
                                                   		 <strong><a href="<?php echo osc_render_file_url(osc_plugin_folder(__FILE__)."makepremiump.php&itemId=".osc_item_id()); ?>"> | <?php _e('Make premium', 'paypalplus'); echo  ' '.osc_get_preference("premium_plus_days", "paypalplus"); echo _e('days!', 'paypalplus'); ?></a></strong>
                                            	<?php } ?>                                                  
                                            <?php } ?>
										<?php } ?>
                                            
                                  <?php }  //this item is not enabled yet
									else { ?>
											<strong><?php echo _e('This item is not enabled yet!','paypalplus');  ?> </strong>
                       				<?php } ?>
								<?php }   //this item is inactive -->
                                    else  { ?>
                                    	   	<strong><? echo _e('This item is inactive!','paypalplus');  ?> </strong>
                                       <?php } ?>
                                       </p>
                                   <br />
                                </div>
                        <?php } ?>
                        <br />
                        <div class="paginate">
                        <?php for($i = 0 ; $i < osc_list_total_pages() ; $i++) {
                            if($i == osc_list_page()) {
                                printf('<a class="searchPaginationSelected" href="%s">%d</a>', osc_render_file_url(osc_plugin_folder(__FILE__) . 'user_menu.php') . '?iPage=' . $i, ($i + 1));
                            } else {
                                printf('<a class="searchPaginationNonSelected" href="%s">%d</a>', osc_render_file_url(osc_plugin_folder(__FILE__) . 'user_menu.php') . '?iPage='. $i, ($i + 1));
                            }
                        } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>