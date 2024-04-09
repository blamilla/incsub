<?php
    /**
     * Plugin Name: Post Status Message on WordPress Admin Dashboard
     * Plugin URI: https://www.linkedin.com/in/brian-adri%C3%A1n-lamilla-5535b8148/
     * Description: This is a plugin created to apply for the developer application task.
     * Version: 1.0
     * Author: Brian Lamilla
     * Author URI: https://www.linkedin.com/in/brian-adri%C3%A1n-lamilla-5535b8148/
     */    

    // Call add_status_message_menu_item function to add plugin menu item in Dashboard Tools menu
    add_action( 'admin_menu', 'add_status_message_menu_item' );

    // Create WordPress's Tools admin menu item 
    function add_status_message_menu_item() {
        $page_title = 'Post Status Message in Dashboard';
        $menu_title = 'Settings - Status Message';
        $capability = 'manage_options';
        $menu_slug  = 'post-status-message-admin';
        $function   = 'status_message_page';

        add_management_page( $page_title, $menu_title, $capability, $menu_slug, $function, '' ); 

        // Call update_status_message function to update Status Message value in the database   
        add_action( 'admin_init', 'update_status_message' );        
        add_action( 'updated_option', 'set_all_subsites_message' );
    }

    // Create function to register plugin settings in the database 
    if( !function_exists("update_status_message") ) { 
        function update_status_message() {            
            register_setting( 'status-message-settings', 'status_message' ); 
        } 
    }

    if( !function_exists("set_all_subsites_message") ) { 
        function set_all_subsites_message() {
            if( get_site_option( 'apply_to_all_subsites' ) !== null ) {
                if( isset( $_POST['apply_to_subsites'] ) ) {                    
                    update_site_option( 'apply_to_all_subsites', '1' );
                    update_site_option( 'global_status_message', $_POST['status_message'] );
                }
                else {
                    update_site_option( 'apply_to_all_subsites', '0' );
                }
            }                                
            else {
                add_site_option( 'apply_to_all_subsites', '0' );
                add_site_option( 'global_status_message', $_POST['status_message'] );
            }
        } 
    }    

    // Create plugin page 
    if( !function_exists("status_message_page") ) { 
        function status_message_page() { 
?>   
            <div class="wrap">
                <h1>
                    Status Message for Admin Dashboard
                </h1> 
                <form method="post" action="options.php">
                    <?php settings_fields( 'status-message-settings' ); ?>
                    <?php do_settings_sections( 'status-message-settings' ); ?>
                    <?php $checkbox = get_site_option('apply_to_all_subsites'); ?>
                    <table class="form-table" role="presentation">
                        <tr valign="top">
                            <th scope="row">
                                Status Message:
                            </th>
                            <td>
                                <input type="text" name="status_message" value="<?php echo get_option( 'status_message' ); ?>" />
                            </td>
                        </tr>
                        <?php
                            if ( is_super_admin() ) {
                        ?>
                            <tr valign="top">
                                <th scope="row">
                                    Apply To All Subsites:
                                </th>
                                <td>
                                    <input type='checkbox' name='apply_to_subsites' value='1' <?php checked( $checkbox, '1' ); ?> /> 
                                </td>
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

    if( !function_exists("post_status_message_admin") ) {
        // Plugin logic. It shows the Status Message on the WordPress Admin Dashboard page
        function post_status_message_admin( ) {
            global $pagenow;
            ob_start(); 

            $status_message = get_option( 'status_message' );
            $global_status_message = get_site_option( 'global_status_message' );
            $global_message_enabled = get_site_option( 'apply_to_all_subsites' );

            if( 'index.php' === $pagenow && ($status_message || $global_status_message)) {
                if( (  1 == $global_message_enabled && '' !== $global_status_message ) || (  0 == $global_message_enabled && '' !== $status_message ) ) {                
?>
                    <div class="notice notice-success">
                        <p>
                            <strong>
<?php               
                                if( 1 == $global_message_enabled && '' !== $global_status_message )
                                    echo $global_status_message;
                                else
                                    echo $status_message;
?>                    
                            </strong>
                        </p>
                    </div>
<?php
                    echo ob_get_clean();
                }
            }
        }
        add_action( 'admin_notices', 'post_status_message_admin' );
    }        