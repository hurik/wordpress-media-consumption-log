<?php

function mcl_unit_register() {
    $categories = get_categories( "exclude=" . SettingsHelper::getStatusExcludeCategory() );
    foreach ( $categories as $category ) {
        register_setting( 'mcl-unit-group', "mcl_unit_{$category->slug}" );
    }
}

function mcl_unit() {
    ?>
    <div class="wrap">
        <h2>Media Consumption Log - <?php _e( 'Units', 'media-consumption-log' ); ?></h2>

        <form method="post" action="options.php">
            <?php settings_fields( 'mcl-unit-group' ); ?>
            <?php do_settings_sections( 'mcl-unit-group' ); ?>

            <h3><?php _e( 'Units', 'media-consumption-log' ); ?></h3>
            <p class="description"><?php _e( 'Please define the units of the categories.', 'media-consumption-log' ); ?></p>
            <table class="form-table">
                <?php
                $categories = get_categories( "exclude=" . SettingsHelper::getStatusExcludeCategory() );
                foreach ( $categories as $category ) {
                    ?>
                    <tr>
                        <th scope="row"><?php echo $category->name; ?></th>
                        <td><input type="text" name="<?php echo "mcl_unit_{$category->slug}"; ?>" value="<?php echo esc_attr( get_option( "mcl_unit_{$category->slug}" ) ); ?>" style="width:100%;" />
                    </tr>
                    <?php
                }
                ?>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>	
    <?php
}

?>