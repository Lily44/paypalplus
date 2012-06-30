<?php
$conn = getConnection();
$paypal_log = $conn->osc_dbFetchResults("SELECT * FROM %st_paypal_log", DB_TABLE_PREFIX);
?>
<link href="<?php echo osc_current_admin_theme_styles_url('datatables.css'); ?>" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?php echo osc_current_admin_theme_js_url('jquery.dataTables.js'); ?>"></script>
<script type="text/javascript" src="<?php echo osc_current_admin_theme_js_url('datatables.pagination.js'); ?>"></script>
<script type="text/javascript" src="<?php echo osc_current_admin_theme_js_url('datatables.extend.js'); ?>"></script>
<div class="dataTables_wrapper">
    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" id="datatables_list"> 
        <thead>
            <tr>
                <th class="sorting"><?php _e('ID', 'paypalplus'); ?></th>
                <th ><?php _e('Description', 'paypalplus'); ?></th>
                <th class="sorting"><?php _e('Date', 'paypalplus'); ?></th>
                <th ><?php _e('Code', 'paypalplus'); ?></th>
                <th ><?php _e('Amount', 'paypalplus'); ?></th>
                <th ><?php _e('Email', 'paypalplus'); ?></th>
                <th ><?php _e('UserID', 'paypalplus'); ?></th>
                <th ><?php _e('ItemID', 'paypalplus'); ?></th>
                <th ><?php _e('Source', 'paypalplus'); ?></th>
                <th ><?php _e('Product type', 'paypalplus'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $odd = 1;
            foreach ($paypal_log as $logs) {
                if ($odd == 1) {
                    $odd_even = "odd";
                    $odd = 0;
                } else {
                    $odd_even = "even";
                    $odd = 1;
                }
                ?>
                <tr class="<?php echo $odd_even; ?>">
                    <td><?php echo $logs['pk_i_id']; ?></td>
                    <td><?php echo $logs['s_concept']; ?></td>
                    <td><?php echo osc_format_date($logs['dt_date']); ?></td>
                    <td><?php echo $logs['s_code']; ?></td>
                    <td><?php echo $logs['f_amount'];
            echo $logs['s_currency_code']; ?></td>
                    <td><?php echo $logs['s_email']; ?></td>
                    <td><?php echo $logs['fk_i_user_id']; ?></td>
                    <td><?php echo $logs['fk_i_item_id']; ?></td>
                    <td><?php echo $logs['s_source']; ?></td>
                    <td><?php
            if ($logs['i_product_type'] == '201')
                echo _e('Premium Ads', 'paypalplus');
            else if ($logs['i_product_type'] == '301')
                echo _e('PremiumPlus Ads', 'paypalplus');
            else if ($logs['i_product_type'] == '401')
                echo _e('Pack', 'paypalplus');
            ?></td>
                </tr>
<?php } ?>
        </tbody>
    </table>
</div>