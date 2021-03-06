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

            add_action( 'admin_enqueue_scripts', [ __CLASS__, 'maybe_load_scripts' ] );
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
                'ensemble_right_menu_links',
                'Sidebar Menu Links',
                [ __CLASS__, 'menu_links' ],
                'ensemble',
                'normal',
                'low'
            );

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

            $sections = [];

            if( isset( $_POST['section_names'] ) && !empty( $_POST['section_names'] ) ) {
                foreach( $_POST['section_names'] as $i => $section ) {
                    $new_section = [ 'name' => $section, 'links' => [], ];

                    if( isset( $_POST["section_${i}_link_hrefs"] ) ) {
                        foreach( $_POST["section_${i}_link_hrefs"] as $j => $link ) {
                            $new_section['links'][] = [
                                'name' => isset( $_POST["section_${i}_link_names"][$j] ) ? $_POST["section_${i}_link_names"][$j] : '',
                                'href' => $link,
                            ];
                        }
                    }

                    $sections[] = $new_section;
                }
            }

            update_post_meta( $post->ID, 'spa-ensemble-link-sections', $sections );
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


        public static function menu_links() {

            global $post;

            $sections = maybe_unserialize( get_post_meta( $post->ID, 'spa-ensemble-link-sections', true ) );

            if( !is_array( $sections ) || empty( $sections ) ) {
                $sections = [
                    [
                        'name' => '',
                        'links' => [
                            [
                                'name' => '',
                                'href' => '',
                            ],
                        ],
                    ],
                ];
            }

            ?>

            <div class="inner-meta" id="link-flex-box">
            <?php foreach( $sections as $i => $section ) : ?>
                <div class="link-section" id="section-<?= $i ?>">
                    <div class="section-name">
                        <div>
                            <label>Section Name: </label>
                            <input type="text" 
                                size="50" 
                                name="section_names[]" 
                                id="name-section-<?= $i ?>"
                                value="<?= isset( $section['name'] ) ? $section['name'] : '' ?>"
                            >
                        </div>
                        <button type="button" class="button button-delete button-delete-section" id="delete-section-<?= $i ?>" aria-label="Delete Section">
                            <span class="dashicons dashicons-trash" aria-hidden="true"></span>
                        </button>
                    </div>
                    <div class="link-box">
                    <?php if( isset( $section['links'] ) && !empty( $section['links'] ) ) : ?>
                        <?php foreach( $section['links'] as $j => $link ) : ?>
                        <div class="link-entry" id="section-<?= $i ?>-link-<?= $j ?>">
                            <div class="link-name">
                                <label>Link Name: </label>
                                <input type="text" 
                                    size="30"
                                    name="section_<?= $i ?>_link_names[]"
                                    id="label-section-<?= $i ?>-link-<?= $j ?>"
                                    value="<?= isset( $link['name'] ) ? $link['name'] : '' ?>"
                                >
                            </div>
                            <div class="link-addr">
                                <label>Link Address: </label>
                                <input type="text" 
                                    size="75"
                                    name="section_<?= $i ?>_link_hrefs[]"
                                    id="href-section-<?= $i ?>-link-<?= $j ?>"
                                    value="<?= isset( $link['href'] ) ? $link['href'] : '' ?>"
                                >
                            </div>
                            <div class="link-delete">
                                <button type="button" 
                                    class="button button-delete button-delete-link"
                                    id="delete-section-<?= $i ?>-link-<?= $j ?>"
                                    aria-label="Delete Link"
                                >
                                    <span class="dashicons dashicons-trash" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                        <div>
                            <button type="button"
                                class="button button-primary button-add-link"
                                id="button-add-link-section-<?= $i ?>"
                                aria-label="Add Link"
                            >
                                <span class="dashicons dashicons-plus" aria-hidden="true"></span>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
                <div>
                    <button type="button"
                            class="button button-primary"
                            id="button-add-section"
                            aria-label="Add Section"
                    >
                        <span class="dashicons dashicons-plus" aria-hidden="true"></span>
                    </button>
                </div>
            </div>

            <?php
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
                    CAH_SPA_ENSEMBLE__PLUGIN_DIR_URL . 'src/js/admin.js', 
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