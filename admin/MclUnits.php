<?php

class MclUnits {

    const option_group_name = "mcl-units-group";
    const option_prefix = "mcl_unit_";

    public static function get_unit_of_category( $category ) {
        $unit = get_option( self::option_prefix . "{$category->slug}" );

        if ( empty( $unit ) ) {
            $unit = $category->name;
        }

        return $unit;
    }

    public static function register_settings() {
        $categories = get_categories( "exclude=" . MclSettingsHelper::getStatusExcludeCategory() );

        foreach ( $categories as $category ) {
            register_setting( self::option_group_name, self::option_prefix . "{$category->slug}" );
        }
    }

    public static function create_page() {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }
        ?>
        <div class="wrap">
            <h2>Media Consumption Log - <?php _e( 'Units', 'media-consumption-log' ); ?></h2>

            <form method="post" action="options.php">
                <?php settings_fields( self::option_group_name ); ?>
                <?php do_settings_sections( self::option_group_name ); ?>

                <h3><?php _e( 'Units', 'media-consumption-log' ); ?></h3>
                <p class="description"><?php _e( 'Please define the units of the categories.', 'media-consumption-log' ); ?></p>
                <table class="form-table">
                    <?php
                    $categories = get_categories( "exclude=" . MclSettingsHelper::getStatusExcludeCategory() );
                    foreach ( $categories as $category ) {
                        ?>
                        <tr>
                            <th scope="row"><?php echo $category->name; ?></th>
                            <td><input type="text" name="<?php echo self::option_prefix . "{$category->slug}"; ?>" value="<?php echo esc_attr( get_option( self::option_prefix . "{$category->slug}" ) ); ?>" style="width:100%;" />
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

}

?>