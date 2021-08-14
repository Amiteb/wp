<?php
/**
 * Plugin Name: Event
 * Plugin URI: #
 * Description: A custom Event Custom Post Type
 * Version: 1.0.0
 * Author: #
 * Author URI: #
 * Text Domain: event
 */

 if( ! defined( 'ABSPATH' ) ) exit();
 // Exit if accessed directly

 /**
 * Event main CLass
 * @since 1.0.0
 */
final class Event_Post_Type {

    // Plugins slug Name
    public static $slug = 'event';

    // Plugin version
    const VERSION = '1.0.0';

    // Instance
    private static $_instance = null;

    /**
    * SIngletone Instance Method
    * @since 1.0.0
    */
    public static function instance() {
        if( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
    * Construct Method
    * @since 1.0.0
    */
    public function __construct() {

        // Call Constants Method
        $this->define_constants();
        // $this->event_helper_files();
        add_action( 'wp_enqueue_scripts', [ $this, 'scripts_styles' ] );
        add_action( 'init', [ $this, 'i18n' ] );
        add_action( 'plugins_loaded', [ $this, 'init' ] );
    }

    /**
    * Define Plugin Constants
    * @since 1.0.0
    */
    public function define_constants() {
        define( 'EVENT_PLUGIN_URL', trailingslashit( plugins_url( '/', __FILE__ ) ) );
        define( 'EVENT_PLUGIN_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
    }

    /**
    * Load Scripts & Styles
    * @since 1.0.0
    */
    public function scripts_styles() {
        wp_register_style( 'event-responsive', EVENT_PLUGIN_URL . 'assets/css/responsive.css', [], rand(), 'all' );
        wp_register_style( 'event-style', EVENT_PLUGIN_URL . 'assets/css/style.css', [], rand(), 'all' );
        wp_register_script( 'event-script', EVENT_PLUGIN_URL . 'assets/js/script.js', [ 'jquery' ], rand(), true );

        wp_enqueue_style( 'event-responsive' );
        wp_enqueue_style( 'event-style' );
        wp_enqueue_script( 'event-script' );
        wp_localize_script( 
            'event-script', 
            'events_opt', 
            array('jsonUrl' => rest_url('wp/v2/event'))
        );
    }

    /**
    * Load Text Domain
    * @since 1.0.0
    */
    public function i18n() {
       load_plugin_textdomain( self::$slug, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    /**
    * Initialize the plugin
    * @since 1.0.0
    */
    public function init() {

        add_action( 'init', array( $this, 'event_post_type' ) );
        add_action('rest_api_init', array( $this, 'event_register_rest_fields' ));
        add_shortcode('event-list', array( $this, 'event_shortcode_callback' ));
    }

    public function event_post_type(){

            $labels = array(
            'name'               => esc_html__('Events', self::$slug),
            'singular_name'      => esc_html__('Event', self::$slug),
            'add_new'            => esc_html__('Add New Event', self::$slug),
            'add_new_item'       => esc_html__('Add New Event', self::$slug),
            'edit_item'          => esc_html__('Edit Event', self::$slug),
            'new_item'           => esc_html__('New Event', self::$slug),
            'all_items'          => esc_html__('All Events', self::$slug),
            'view_item'          => esc_html__('View Event', self::$slug),
            'search_items'       => esc_html__('Search Events', self::$slug),
            'not_found'          => esc_html__('No Events found', self::$slug),
            'not_found_in_trash' => esc_html__('No Events found in trash', self::$slug), 
            'parent_item_colon'  => '',
            'menu_name'          => esc_html__('Events', self::$slug)
        );
     
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true, 
            'show_in_menu'       => true, 
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'event','with_front' => false ),
            'capability_type'    => 'post',
            'has_archive'        => true, 
            'hierarchical'       => false,
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-admin-post',
            'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt','author'),
            'show_in_rest'          => true,
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'rest_base'             => 'event',
        );
     
        register_post_type( 'event', $args );
     
        register_taxonomy('event_type', 'event', array(
            'hierarchical' => true,
            'labels' => array(
                'name'              => esc_html__( 'Event Type', self::$slug ),
                'singular_name'     => esc_html__( 'Event Type', self::$slug ),
                'search_items'      => esc_html__( 'Search Event Type', self::$slug ),
                'all_items'         => esc_html__( 'All Event Types', self::$slug ),
                'parent_item'       => esc_html__( 'Parent Event Type', self::$slug ),
                'parent_item_colon' => esc_html__( 'Parent Event Type', self::$slug ),
                'edit_item'         => esc_html__( 'Edit Event Type', self::$slug ),
                'update_item'       => esc_html__( 'Update Event Type', self::$slug ),
                'add_new_item'      => esc_html__( 'Add New Event Type', self::$slug ),
                'new_item_name'     => esc_html__( 'New Event Type', self::$slug ),
                'menu_name'         => esc_html__( 'Event Type', self::$slug ),
            ),
            'rewrite' => array(
                'slug'         => 'event_type',
                'with_front'   => true,
                'hierarchical' => true
            ),
            'show_in_nav_menus' => true,
            'show_tagcloud'     => true,
            'show_admin_column' => true,
            'show_in_rest'          => true,
            'rest_controller_class' => 'WP_REST_Terms_Controller',
            'rest_base'             => 'event_type',
        ));
 
        register_taxonomy('event_tag', 'event', array(
            'hierarchical' => false,
            'labels' => array(
                'name'              => esc_html__( 'Events Tag', self::$slug ),
                'singular_name'     => esc_html__( 'Events Tag', self::$slug ),
                'search_items'      => esc_html__( 'Search Event Tags', self::$slug ),
                'all_items'         => esc_html__( 'All Event Tags', self::$slug ),
                'parent_item'       => esc_html__( 'Parent Event Tags', self::$slug ),
                'parent_item_colon' => esc_html__( 'Parent Event Tag:', self::$slug ),
                'edit_item'         => esc_html__( 'Edit Event Tag', self::$slug ),
                'update_item'       => esc_html__( 'Update Event Tag', self::$slug ),
                'add_new_item'      => esc_html__( 'Add New Event Tag', self::$slug ),
                'new_item_name'     => esc_html__( 'New Event Tag', self::$slug ),
                'menu_name'         => esc_html__( 'Event Tags', self::$slug ),
            ),
            'rewrite'          => array(
                'slug'         => 'event_tag',
                'with_front'   => true,
                'hierarchical' => false
            ),
            'show_admin_column' => true,
            'show_in_rest'          => true,
            'rest_controller_class' => 'WP_REST_Terms_Controller',
            'rest_base'             => 'event_tag',
        ));
    }

    //Adding Rest End Point Controller Name
    function event_register_rest_fields(){
 
        register_rest_field('event',
            'event_type_attr',
            array(
                'get_callback'    => array( $this, 'event_type_callback' ),
                'update_callback' => null,
                'schema'          => null
            )
        );
     
        register_rest_field('event',
            'event_tag_attr',
            array(
                'get_callback'    => array( $this, 'event_tag_callback' ),
                'update_callback' => null,
                'schema'          => null
            )
        );
     
        register_rest_field('event',
            'event_image_src',
            array(
                'get_callback'    => array( $this, 'event_images_src_callback' ),
                'update_callback' => null,
                'schema'          => null
            )
        );
    }

    //calback Method Define
    public function event_type_callback($object,$field_name,$request){
        $terms_result = array();
        $terms =  wp_get_post_terms( $object['id'], 'event_type');
        foreach ($terms as $term) {
        $terms_result[$term->term_id] = array($term->name,get_term_link($term->term_id));
        }
        return $terms_result;
    }
 
    public function event_tag_callback($object,$field_name,$request){
        $terms_result = array();
        $terms =  wp_get_post_terms( $object['id'], 'event_tag');
        foreach ($terms as $term) {
            $terms_result[$term->term_id] = array($term->name,get_term_link($term->term_id));
        }
        return $terms_result;
    }
     
    public function event_images_src_callback($object,$field_name,$request){
     
        $img = wp_get_attachment_image_src($object['featured_media'],'full');
        if(!empty($img)){
            return $img[0];
        }
        
    }

    public function event_shortcode_callback($atts, $content = null){

        extract(shortcode_atts(
        array(
            'layout'     => 'grid', // grid / list
            'limit'   => '3',    // int number
            'type'   => '',    // int number
            ), $atts)
        );
        global $post;
        $query_options = array(
            'post_type'           => 'event',
            'post_status'         => 'publish',
            'ignore_sticky_posts' => 1,
            'orderby'             => 'date',
            'order'               => 'DESC',
            'posts_per_page'      => absint($limit) 
        );
        if (isset($type) & !empty($type)) {
            $tax_query_array = array(
                'tax_query' => array(
                array(
                    'taxonomy' => 'event_type',
                    'field'    => 'slug',
                    'terms'    => $type,
                    'operator' => 'IN'
                ))
            );
            $query_options = array_merge($query_options,$tax_query_array);
        }
        $tuts = new WP_Query($query_options);
        if($tuts->have_posts()){
            wp_enqueue_script('event-script');
            $output = '';
            $class  = array();
            $class[] = 'recent-tuts';
            $class[] = esc_attr($layout);
            $output .= '<div class="recent-tuts-wrapper">';
            $args = array(
                'orderby'           => 'name', 
                'order'             => 'ASC',
                'fields'            => 'all', 
                'child_of'          => 0, 
                'parent'            => 0,
                'hide_empty'        => true, 
                'hierarchical'      => false, 
                'pad_counts'        => false, 
            );
            $terms = get_terms('event_type',$args);
            if (count($terms) != 0){
                $output .= '<div class="term-filter" data-per-page="'.absint($limit).'">';
                if (empty($start_cat)) {
                    $output .= '<a href="'.esc_url(get_post_type_archive_link('event')).'" class="active">'.esc_html__('All','event').'</a>';
                }
                foreach($terms as $term){
                    $term_class = (isset($start_cat) && !empty($start_cat) && $start_cat == $term->term_id) ? $term->slug.' active' : $term->slug;
                    $term_data  = array();

                    $term_data[] = 'data-filter="'.$term->slug.'"';
                    $term_data[] = 'data-filter-id="'.$term->term_id.'"';

                    $output .= '<a href="'.esc_url(get_term_link($term->term_id, 'event_type')).'" class="'.esc_attr($term_class).'" '.implode(' ', $term_data).'>'.$term->name.'</a>';
                }
                $output .= '</div>';
            }
            $output .= '<ul class="'.implode(' ', $class).'">';
            while ($tuts->have_posts() ) {
                $tuts->the_post();
                $IMAGE = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'full', false);
                $output .= '<li>';
                if(isset($IMAGE[0])){
                $output .= '<img src="'.esc_url($IMAGE[0]).'" alt="'.esc_attr(get_the_title()).'" />';
                }

                $output .='<div class="event-content">';
                $output .='<div class="event_type">';
                $output .= get_the_term_list( get_the_ID(), 'event_type', '', ', ', '' );
                $output .='</div>';
                if ( '' != get_the_title() ){
                    $output .='<h4 class="event-title entry-title">';
                    $output .= '<a href="'.get_the_permalink().'" title="'.get_the_title().'" rel="bookmark">';
                    $output .= get_the_title();
                    $output .= '</a>';
                    $output .='</h4>';
                }
                if ( '' != get_the_excerpt() && $layout == 'grid'){
                    $output .='<div class="event-excerpt">';
                    $output .= get_the_excerpt();
                    $output .='</div>';
                }
                $output .='<div class="event-tag">';
                $output .= get_the_term_list( get_the_ID(), 'event_tag', '', ' ', '' );
                $output .='</div>';
                $output .='</div>';
                $output .= '</li>';
            }
            wp_reset_postdata();
            $output .= '</ul>';
            $output .= '</div>';
            return $output;
        }
    }

    // Function to include helper functions
    // public function event_helper_files(){

    //     require_once EVENT_PLUGIN_PATH.'/includes/queries.php';
    // }

}

Event_Post_Type::instance();