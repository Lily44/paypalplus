<?php 
$conn   = getConnection();
$paypal_log = $conn->osc_dbFetchResults("SELECT * FROM %st_paypal_premium_plus", DB_TABLE_PREFIX);
?>
 <link href="<?php echo osc_current_admin_theme_styles_url('datatables.css') ; ?>" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="<?php echo osc_current_admin_theme_js_url('jquery.dataTables.js') ; ?>"></script>
        <script type="text/javascript" src="<?php echo osc_current_admin_theme_js_url('datatables.pagination.js') ; ?>"></script>
        <script type="text/javascript" src="<?php echo osc_current_admin_theme_js_url('datatables.extend.js') ; ?>"></script>
<div class="dataTables_wrapper">
                    <? if (osc_version() < '240') {?>
                    <table cellpadding="0" cellspacing="0" border="0" class="display" id="datatables_list">
                   <? echo osc_version(); } else {?>
                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" id="datatables_list">
                    <? } ?>                          <thead>
                            <tr>
                                <th class="sorting"><?php _e('ID', 'paypalplus'); ?></th>
                                <th class="sorting"><?php _e('Date', 'paypalplus'); ?></th>
                                <th ><?php _e('Paypal Log ID', 'paypalplus'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $odd = 1;
                                foreach($paypal_log as $logs) {
                                    if($odd==1) {
                                        $odd_even = "odd";
                                        $odd = 0;
                                    } else {
                                        $odd_even = "even";
                                        $odd = 1;
                                    }
				
				
                            ?>
                            
                            <tr class="<?php echo $odd_even;?>">
                            	<td><?php echo $logs['fk_i_item_id']; ?></td>
                            	<td><?php echo osc_format_date($logs['dt_date']); ?></td>
                            	<td><?php echo $logs['fk_i_paypal_id']; ?></td>
                            </tr>
                            <?php }?>
                        </tbody>
                    </table>
                    
</div>
