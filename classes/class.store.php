<?php
/**
 * Inbound Now Store
 */
if ( ! class_exists( 'Inbound_Now_Store' ) ) {

    class Inbound_Now_Store {

        static function init() {
            self::load_hooks();
        }

        /**
         * Loads hooks and filters
         */
        public static function load_hooks() {
            add_action('admin_menu', array( __CLASS__ , 'add_sub_menus' ) );
            add_action('admin_init', array( __CLASS__ , 'inbound_store_template_redirect'));
            add_action( 'wp_ajax_show_store_ajax' , array( __CLASS__ , 'show_store_ajax' ) );
            add_action( 'admin_enqueue_scripts' , array( __CLASS__ , 'enqueue_scripts' ) );
        }

        /**
         * enqueues scripts and styles
         */
        public static function enqueue_scripts() {
            global $plugin_page;

            if ( !in_array( $plugin_page, array( 'lp_store', 'lp_addons' ) ) ) {
                return;
            }

            wp_dequeue_script('easyXDM');
            wp_enqueue_script('easyXDM', LANDINGPAGES_URLPATH . 'js/libraries/easyXDM.debug.js');
            wp_enqueue_script('lp-js-store', LANDINGPAGES_URLPATH . 'js/admin/admin.store.js');

        }

        public static function add_sub_menus() {
            if ( !current_user_can('manage_options')) {
                return;
            }

            add_submenu_page('edit.php?post_type=landing-page', __('Extensions' , 'landing-pages'),'<span style="color:#f18500">'.__('Extensions' , 'landing-pages').'</span>', 'manage_options', 'lp_store', array( __CLASS__ , 'store_display' ),100);

            add_submenu_page('edit.php?post_type=landing-page', __('Download Templates' , 'landing-pages'),'<span style="color:#fff">'.__('Download Templates' , 'landing-pages').'</span>', 'manage_options', 'inbound-templates-redirect', array( __CLASS__ , 'inbound_store_template_redirect' ),100);

        }

        public static function show_store_ajax() {
            if(empty($_POST) || !isset($_POST)) {
                return;
            } else {
                /* show store forever */
                $user_id = get_current_user_id();
                add_user_meta($user_id, 'inbound_show_store', true);
                die();
            }

        }
        /* main display function */
        public static function store_display(){
            global $current_user;

            $user_id = $current_user->ID;

            self::dom_output();

            if ( !get_user_meta($user_id, 'inbound_show_store') ) {
                self::inbound_store_notice();
            } else {
                // normal display here
                self::display_store();
            }

        }

        /* loads when user_meta opt in is NOT found */
        public static function inbound_store_notice(){

            echo '<div id="agreement" style="margin-top:30px;">
				<h1>WordPress Guidelines Compliance Agreement</h1>
				<h3>To ensure complaince with <a href="https://wordpress.org/plugins/about/guidelines/">WordPress.orgs Plugin Guidelines</a>, we need your express permission to load our <a target="_blank" href="http://www.inboundnow.com/market">marketplace</a>.

				<div class="details">
					<h4 style="margin-bottom:0px;"><u>What is happening?</u></h4>
					<p>To streamline your experience, We are loading in our marketplace from <a target="_blank" href="http://www.inboundnow.com/market">http://www.inboundnow.com/market</a> into this page.</p>
					<p>Don\'t worry. We do not access <u>ANY</u> of your private/personal information contained within your site</p>
					<a href="#" id="accept-agreement" class="button button-primary">I accept this agreement, show me the goods!</a>
				</div>

				</div>'; ?>
            <script>
                jQuery(document).ready(function($) {

                    jQuery("#accept-agreement").on('click', function (e) {
                        e.preventDefault();

                        $('#agreement').slideToggle();

                        showInboundStore();

                        jQuery.ajax({
                            type: 'POST',
                            url: ajaxadmin.ajaxurl,
                            data: {
                                action: 'show_store_ajax'
                            },
                            success: function(user_id){
                                console.log('user meta updated');
                            },
                            error: function(MLHttpRequest, textStatus, errorThrown){

                            }

                        });

                    });
                });
            </script>

        <?php }
        /* loads when user_meta opt in is found */
        public static function display_store() { ?>
            <script>
                jQuery(document).ready(function($) {
                    showInboundStore();
                });
            </script>
        <?php }
        /* Always loads on store pages */
        public static function dom_output(){

            if (isset($_GET['inbound-store']) && $_GET['inbound-store'] === 'templates') {
                $url = 'http://www.inboundnow.com/products/landing-pages/templates/';
            } else if (isset($_GET['inbound-store']) && $_GET['inbound-store'] === 'addons') {
                $url = 'http://www.inboundnow.com/products/landing-pages/extensions/';
            } else {
                $url = LANDINGPAGES_STORE_URL;
            }
            ?>
            <style type="text/css">
                #setting-error-tgmpa, .updated, #wpfooter { display: none !important; }
                #wpwrap { background: #fff !important; }
                div#inbound-store-container { margin-top: 0px !important; }
                div#inbound-store-container iframe { width:100%; }
                #wpbody-content { padding-bottom: 0px !important; }
            </style>
            <script type='text/javascript'>
                function showInboundStore(){
                    new easyXDM.Socket({
                        remote: "<?php echo $url;?>",
                        container: document.getElementById("inbound-store-container"),
                        onMessage: function(message, origin){
                            var height = Number(message) + 1000;
                            this.container.getElementsByTagName("iframe")[0].scrolling="no";
                            this.container.getElementsByTagName("iframe")[0].style.height = height + "px";

                        },
                        onReady: function() {
                            socket.postMessage("Yay, it works!");
                            //alert('run');
                        }
                    });

                    setTimeout(function() {
                        jQuery("#inbound-store-container iframe").css('height', window.outerHeight + "px");
                    }, 2000);
                }
            </script>

            <div id="inbound-store-container"></div>
        <?php }
        /* redirect for additional menu item */
        public static function inbound_store_template_redirect($value){
            global $pagenow;
            $page = (isset($_REQUEST['page']) ? $_REQUEST['page'] : false);
            if($pagenow=='edit.php' && $page=='inbound-templates-redirect'){
                $link = admin_url( 'edit.php?post_type=landing-page&page=lp_store&inbound-store=templates');
                wp_redirect($link);
                exit;
            }
        }

    }

    Inbound_Now_Store::init();

}