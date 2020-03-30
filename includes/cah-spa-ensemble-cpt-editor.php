<?php
/**
 * A static helper class for building and managing the editor for the CPT.
 */

if( !class_exists( 'CAH_SPAEnsembleCPTEditor' ) ) {
    class CAH_SPAEnsembleCPTEditor
    {
        private function __construct() {}

        /**
         * Sets up our initial actions. Called from the main plugin file.
         * 
         * @author Mike W. Leavitt
         * @since 0.1.0
         * 
         * @return void
         */
        public static function setup() {
            add_action( 'add_meta_boxes', [ __CLASS__, 'register_metaboxes' ], 10, 0 );

            add_action( 'save_post_ensemble', [ __CLASS__, 'save' ], 10, 0 );

            // Scripts are empty at the moment, so we don't technically need this yet.
            // add_action( 'admin_enqueue_scripts', [ __CLASS__, 'maybe_load_scripts' ] );
        }


        /**
         * Register any custom metaboxes.
         * 
         * @author Mike W. Leavitt
         * @since 0.1.0
         */
        public static function register_metaboxes() {
            // The arguments here are:
            //      - the name of the metabox
            //      - the box's title in the editor
            //      - function to call for HTML markup
            //      - the post type to add the box for
            //      - situations to show the box in
            //      - priority for box display
            add_meta_box(
                'ensemble_sidebar',
                'Right Sidebar',
                [ __CLASS__, 'sidebar_box' ],
                'ensemble',
                'normal',
                'low'
            );
        }


        /**
         * Save the extra metadata for our ensemble post.
         * 
         * @author Mike W. Leavitt
         * @since 0.1.0
         * 
         * @return void
         */
        public static function save() {
            global $post;

            if( !is_object( $post ) ) return;

            if( isset( $_POST['sidebar-content'] ) ) {
                update_post_meta( $post->ID, 'spa-ensemble-sidebar-content', $_POST['sidebar-content'] );
            }
        }


        /**
         * Create the metabox for our sidebar content.
         * 
         * @author Mike W. Leavitt
         * @since 0.1.0
         * 
         * @return void
         */
        public static function sidebar_box() {
            global $post;

            $content = get_post_meta( $post->ID, 'spa-ensemble-sidebar-content', true );

            wp_editor( isset( $content ) ? $content : '', 'sidebar-content', [ 'textarea_rows' => 6] );
        }


        /**
         * Load our admin scripts and styles if we're creating a new ensemble post or
         * editing an existing one.
         * 
         * @author Mike W. Leavitt
         * @since 0.1.0
         * 
         * @return void
         */
        public static function maybe_load_scripts() {
            global $pagenow, $post;
            if( ( 'post-new.php' == $pagenow && isset( $_GET['post_type'] ) && 'ensemble' == $_GET['post_type'] )
                || ( 'post.php' == $pagenow && 'ensemble' == $post->post_type && isset( $_GET['action'] ) && 'edit' == $_GET['action'] ) 
            ) {
                wp_enqueue_script( 
                    'cah-spa-ensemble-admin', 
                    CAH_SPA_ENSEMBLE__PLUGIN_DIR_URL . 'dist/js/admin.min.js', 
                    [ 'jquery' ], 
                    CAH_SPA_ENSEMBLE__VERSION, 
                    true
                );

                wp_enqueue_style( 
                    'cah-spa-ensemble-admin-style', 
                    CAH_SPA_ENSEMBLE__PLUGIN_DIR_URL . 'dist/css/admin-style.css', 
                    [], CAH_SPA_ENSEMBLE__VERSION, 
                    'all' 
                );

            }
        }
    }
}
?>