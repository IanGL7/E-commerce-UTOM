<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function the9_storefront_theme_setup(){

    // Make theme available for translation.
    load_theme_textdomain( 'the9-storefront', get_stylesheet_directory_uri() . '/languages' );
    
    add_theme_support( 'custom-header', apply_filters( 'the9_storefront_custom_header_args', array(
        'default-image' => get_stylesheet_directory_uri() . '/assets/images/custom-header.jpg',
        'default-text-color'     => '000000',
        'width'                  => 1000,
        'height'                 => 350,
        'flex-height'            => true,
        'wp-head-callback'       => 'the9_store_header_style',
    ) ) );
    
    register_default_headers( array(
        'default-image' => array(
        'url' => '%s/assets/images/custom-header.jpg',
        'thumbnail_url' => '%s/assets/images/custom-header.jpg',
        'description' => esc_html__( 'Default Header Image', 'the9-storefront' ),
        ),
    ));

    //remove_action( 'after_setup_theme', 'the9_store_customizer_starter_content' );

}
add_action( 'after_setup_theme', 'the9_storefront_theme_setup' );

// BEGIN ENQUEUE PARENT ACTION
if ( !function_exists( 'the9_storefront_cfg_locale_css' ) ):
    function the9_storefront_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'the9_storefront_cfg_locale_css' );

if ( !function_exists( 'the9_storefront_cfg_parent_css' ) ):
    function the9_storefront_cfg_parent_css() {
         wp_enqueue_style( 'the9-storefront-google-fonts', '//fonts.googleapis.com/css?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900|Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap&display=swap' );

        wp_enqueue_style( 'the9_storefront_cfg_parent', trailingslashit( get_template_directory_uri() ) . 'style.css', array( 'bootstrap','bi-icons','icofont','scrollbar','aos','the9-store-common' ) );

        $custom_css = ':root {--primary-color:'.esc_attr( get_theme_mod('__primary_color','#000') ).'!important; --secondary-color: '.esc_attr( get_theme_mod('__secondary_color','#dd3333') ).'!important; --nav-h-color:'.esc_attr( get_theme_mod('__secondary_color','#dd3333') ).'!important;}';
        
        wp_add_inline_style( 'the9_storefront_cfg_parent', $custom_css );

        
        wp_enqueue_script( 'masonry' );

        wp_enqueue_script( 'the9-jsstorefront-js', get_theme_file_uri( '/assets/the9-storefront.js'), array(),  wp_get_theme()->get('Version'), true);
    }
endif;
add_action( 'wp_enqueue_scripts', 'the9_storefront_cfg_parent_css', 10 );

// END ENQUEUE PARENT ACTION


if( !function_exists('the9_storefront_disable_from_parent') ):

    add_action('init','the9_storefront_disable_from_parent',10);
    function the9_storefront_disable_from_parent(){
      global $the9_store_header_layout;
      remove_action('the9_store_site_header', array( $the9_store_header_layout, 'site_header_top_bar' ), 10 );
      remove_action('the9_store_site_header', array( $the9_store_header_layout, 'site_header_layout' ), 30 );
      remove_action('the9_store_site_header', array( $the9_store_header_layout, 'get_site_navigation' ), 40 );

      remove_action('the9_store_site_header', array( $the9_store_header_layout, 'site_hero_sections' ), 9999 );

      remove_action( 'woocommerce_before_main_content', 'the9_store_woocommerce_wrapper_before' );
      remove_action( 'woocommerce_after_main_content', 'the9_store_woocommerce_wrapper_after' );
    }
    
endif;

if( !function_exists('the9_storefront_header_layout') ):
    add_action('the9_store_site_header','the9_storefront_header_layout',10);
    function the9_storefront_header_layout(){
           global $the9_store_header_layout;
    ?>
        <section class="logo-container">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-xl-3 col-lg-3 col-sm-4 col-12 text-right">
                        <div class="d-flex align-items-center gap-3">
                            <?php do_action('the9_store_header_layout_1_branding');?>
                        </div>
                    </div>
                    <div class="col-xl-9 col-lg-9 col-sm-8 col-12 d-flex justify-content-end">
                         <nav id="navbar" class="navbar-fill">
                            <div class="container d-flex align-items-center">
                                <button class="the9-store-responsive-navbar"><i class="bi bi-list"></i></button>
                                <div id="aside-nav-wrapper" class="nav-wrap flex-grow-1">
                                <button class="the9-store-navbar-close"><i class="bi bi-x-lg"></i></button> 
                                <?php
                                    wp_nav_menu( array(
                                        'theme_location'    => 'menu-1',
                                        'depth'             => 3,
                                        'menu_class'        => 'the9_store-main-menu navigation-menu',
                                        'container'         => 'ul',
                                        'walker'            => new the9_store_navwalker(),
                                        'fallback_cb'       => 'the9_store_navwalker::fallback_2',
                                    ) );
                                ?>
                                </div>
                                <div class="ml-auto">
                                    <?php do_action('the9_store_mini_cart');?>
                                </div>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </section>
    <?php
    }
endif;


if( !function_exists('the9_storefront_hero_sections') ):
add_action('the9_store_site_header', 'the9_storefront_hero_sections', 9999 );
function the9_storefront_hero_sections(){
        if( is_404() ) return;
        if ( is_front_page() && is_active_sidebar( 'slider' ) ) : 
         dynamic_sidebar( 'slider' );
        else: 
        $header_image = get_header_image();

        if ( has_post_thumbnail() && is_singular() && ( !function_exists('is_product') || !is_product() ) ) :

            $post_thumbnail_id  = get_post_thumbnail_id( get_the_ID() );
            $header_image = wp_get_attachment_url( $post_thumbnail_id );
            
        endif;
        
        ?>
            <?php if( !empty( $header_image ) ) : ?>
            <div id="static_header_banner" class="header-img" style="background-image: url(<?php echo esc_url( $header_image );?>); background-attachment: scroll; background-size: cover; background-position: center center;">
             <?php else: ?>
             <div id="static_header_banner">
            <?php endif;?>
                <div class="content-text">
                    <div class="container">
                    <div class="site-header-text-wrap">
                        <?php 
                            if ( is_home() ) {
                                if( display_header_text() == true ){
                                    echo '<h1 class="page-title-text home-page-title">';
                                    echo bloginfo( 'name' );
                                    echo '</h1>';
                                    echo '<p class="subtitle home-page-desc">';
                                    echo esc_html(get_bloginfo( 'description', 'display' ));
                                    echo '</p>';
                                }
                            }else if ( function_exists('is_shop') && is_shop() ){
                                if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
                                    echo '<h1 class="page-title-text">';
                                    echo esc_html( woocommerce_page_title() );
                                    echo '</h1>';
                                }
                            }else if( function_exists('is_product_category') && is_product_category() ){
                                echo '<h1 class="page-title-text">';
                                echo esc_html( woocommerce_page_title() );
                                echo '</h1>';
                                echo '<p class="subtitle">';
                                do_action( 'the9_store_archive_description' );
                                echo '</p>';
                                
                            }elseif( is_singular('post') ){
                                echo '<h1 class="page-title-text">';
                                    echo single_post_title( '', false );
                                echo '</h1>';
                                
                                $array  = array( 'author', 'date', 'category', 'comments' );
                                do_action('the9_store_meta_info', $array);
                                
                            }elseif ( is_singular() ) {
                                echo '<h1 class="page-title-text">';
                                    echo single_post_title( '', false );
                                echo '</h1>';
                            } elseif ( is_archive() ) {
                                the_archive_title( '<h1 class="page-title-text">', '</h1>' );
                                the_archive_description( '<p class="archive-description subtitle">', '</p>' );
                                
                            } elseif ( is_search() ) {
                                echo '<h1 class="title">';
                                printf( /* translators:straing */ esc_html__( 'Search Results for: %s', 'the9-storefront' ),  get_search_query() );
                                echo '</h1>';
                            } elseif ( is_404() ) {
                                echo '<h1 class="display-1">';
                                    esc_html_e( 'Oops! That page can&rsquo;t be found.', 'the9-storefront' );
                                echo '</h1>';
                            }
                        ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        endif;
    }
endif;


function the9_storefront_remove_customizer_sections( $wp_customize ) {
    // Remove the section by its ID
    $wp_customize->remove_section( 'topbar_section_settings' );
}
add_action( 'customize_register', 'the9_storefront_remove_customizer_sections', 20 );

function the9_storefront_modify_default_theme_options( $defaults ) {
    // Change a default value
   $defaults['__primary_color']                 = '#000';
   $defaults['__secondary_color']               = '#dd3333';
   $defaults['blog_layout']                     = 'full-container';
   $defaults['single_post_layout']              = 'no-sidebar';

    return $defaults;
}
add_filter( 'the9_store_filter_default_theme_options', 'the9_storefront_modify_default_theme_options' );


if ( ! function_exists( 'the9_storefront_woocommerce_wrapper_before' ) ) {
    /**
     * Before Content.
     *
     * Wraps all WooCommerce content in wrappers which match the theme markup.
     *
     * @return void
     */
    function the9_storefront_woocommerce_wrapper_before() {
        /**
        * Hook - the9_store_container_wrap_start    
        *
        * @hooked the9_store_container_wrap_start   - 5
        */
        if( is_product() ){
          do_action( 'the9_store_container_wrap_start', 'sidebar-content');
        }else{
          do_action( 'the9_store_container_wrap_start', 'sidebar-content');
        }
    }
   add_action( 'woocommerce_before_main_content', 'the9_storefront_woocommerce_wrapper_before' );
}


if ( ! function_exists( 'the9_storefront_woocommerce_wrapper_after' ) ) {
    /**
     * After Content.
     *
     * Closes the wrapping divs.
     *
     * @return void
     */
    function the9_storefront_woocommerce_wrapper_after() {
        /**
        * Hook - the9_store_container_wrap_end  
        *
        * @hooked container_wrap_end - 999
        */
        if( is_product() ){
          do_action( 'the9_store_container_wrap_end', 'sidebar-content');
        }else{
         do_action( 'the9_store_container_wrap_end', 'sidebar-content');
        }
       
    }
    add_action( 'woocommerce_after_main_content', 'the9_storefront_woocommerce_wrapper_after' );
}

/**
 * Default loop columns on product archives.
 *
 * @return integer products per row.
 */
function the9_storefront_woocommerce_loop_columns() {
    return 3;
}
add_filter( 'loop_shop_columns', 'the9_storefront_woocommerce_loop_columns',99 );


// Set 9 products per page
function the9_storefront_products_per_page( $cols ) {
    return 9;
}
add_filter( 'loop_shop_per_page', 'the9_storefront_products_per_page', 99 );