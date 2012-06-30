	<?php 
    $packs = array();
    if(osc_get_preference("pack_price_1", "paypalplus")!='' && osc_get_preference("pack_price_1", "paypalplus")!='0') {
        $packs[] = osc_get_preference("pack_price_1", "paypalplus");
    }
    if(osc_get_preference("pack_price_2", "paypalplus")!='' && osc_get_preference("pack_price_2", "paypalplus")!='0') {
        $packs[] = osc_get_preference("pack_price_2", "paypalplus");
    }
    if(osc_get_preference("pack_price_3", "paypalplus")!='' && osc_get_preference("pack_price_3", "paypalplus")!='0') {
        $packs[] = osc_get_preference("pack_price_3", "paypalplus");
    }
	
	$bonus = array();
    if(osc_get_preference("bonus_pack_1", "paypalplus")!='' && osc_get_preference("bonus_pack_1", "paypalplus")!='0') {
        $bonus[] = osc_get_preference("bonus_pack_1", "paypalplus");
    }
    if(osc_get_preference("bonus_pack_2", "paypalplus")!='' && osc_get_preference("bonus_pack_2", "paypalplus")!='0') {
        $bonus[] = osc_get_preference("bonus_pack_2", "paypalplus");
    }
    if(osc_get_preference("bonus_pack_3", "paypalplus")!='' && osc_get_preference("bonus_pack_3", "paypalplus")!='0') {
        $bonus[] = osc_get_preference("bonus_pack_3", "paypalplus");
    }
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
                    <h2><?php echo sprintf(__('Credit packs. Your current credit is %.2f %s', 'paypalplus'), $amount, osc_get_preference('currency', 'paypalplus')); ?></h2>
                   
                    <? if ($bonusamount>'0')  { ?>
						<h2 style="color:#060"> <?php echo sprintf(__('Your current Bonus credit is %.2f %s', 'paypalplus'), $bonusamount, osc_get_preference('currency', 'paypalplus'));
						if ($diff>'0')
						 	echo ' '.sprintf(__('maturing in %d days', 'paypalplus'), $diff);
						else
							echo __('no expiry', 'paypalplus'); ?></h2>
                        <? } ?>
                   
                    <?php $pack_n = 0; $bonus_n = 0;
                        foreach($packs as $pack) { $pack_n++; ?>
                        <div>
                            <h3><?php echo sprintf(__('Credit pack #%d', 'paypalplus'), $pack_n); ?></h3>
                            <div style="float:left;width:200px"><label><?php _e("Price", "paypalplus");?>:</label> <?php echo $pack." ".osc_get_preference('currency', 'paypalplus'); ?>
                            <? if (osc_get_preference("allow_bonus", "paypalplus")=='1') { 
									if ($bonusamount=='0' || $bonusamount==null) {?>
                            <br /><strong><? echo __('Get','paypalplus').' '.$bonus[$bonus_n]. '% '. __('Free Bonus!','paypalplus'); ?>*</strong>
                            	<?  } else $morecredit='1'; 
								$bonus_n++; }
								?>
                            </div> 
                            <div style="float:left;">
                                <?php paypalplus_button($pack, sprintf(__("Credit for %s %s at %s", "paypalplus"), $pack, osc_get_preference("currency", "paypalplus"), osc_page_title()), $user['pk_i_id']."|dash|".$user['s_email'], "401x".$pack); ?>
                            </div>
                        </div>
                        <div style="clear:both;"></div>
                        <br/>
                    <?php } ?>
                    <? if ($morecredit=='1') echo _e('You can get more Bonus Credit after using all you actual bonus!','paypalplus');
							else  {
								if  (osc_get_preference("allow_bonus", "paypalplus")=='1') echo '*'.__('Bonus Credit is not cumulative.','paypalplus');
								if (osc_get_preference("allow_bonus_expiration", "paypalplus")=='1') echo ' '.sprintf(__('Your Bonus Credit will expire in %d days','paypalplus'), osc_get_preference("bonus_days", "paypalplus") );
							} ?>
                    
                    <div name="result_div" id="result_div"></div>
                    <script type="text/javascript">
                        var rd = document.getElementById("result_div");
                    </script>
                </div>
            </div>