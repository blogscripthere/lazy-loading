<?php
/**
 * @package Lazy_Loading
 * @version 1.0
 */
/*
Plugin Name: ScriptHere's Lazy loading
Plugin URI: https://github.com/blogscripthere/lazy-loading/
Description: ScriptHere's simple Lazy loading Images in WordPress .
Author: Narendra Padala
Author URI: https://in.linkedin.com/in/narendrapadala
Text Domain: shll
Version: 1.0
Last Updated: 03/02/2018
*/

/**
 * We are going to use jQuery library to implement lazy loading,
 * i have provided here two options any one we can use, each one have there own pros and cons,
 * you can enhance this code your self based on your need.
 * jQuery Lazy Load XT url : https://github.com/ressio/lazy-load-xt
 * jQuery Lazy url : http://jquery.eisbehr.de/lazy/
 * jQuery libraries Options - "lazy_xt" or "lazy"
 */
define('SH_LAZY_LOAD_LIBRARY',"lazy");


/**
 * Enqueue required javascript libraries to implement lazy loading hook
 */
add_action('wp_enqueue_scripts', 'sh_lazy_load_enqueue_scripts');

/**
 * Enqueue required javascript libraries to implement lazyloading callback
 */
function sh_lazy_load_enqueue_scripts() {

    //check if jquery not loaded already your plugins /theme load it due lazy libraries are jQuery dependents
    if(!wp_script_is('jquery')) {
        wp_enqueue_script('jquery', '//cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js', array(), '3.3.1', true);
    }
    //check libraray option and load accordingly
    if(SH_LAZY_LOAD_LIBRARY == "lazy"){
        wp_enqueue_script('jquery-lazy', '//cdnjs.cloudflare.com/ajax/libs/jquery.lazy/1.7.4/jquery.lazy.min.js', array('jquery'), '1.7.4', true);
    }else{
        wp_enqueue_script('jquery-lazy-load-xt', '//cdnjs.cloudflare.com/ajax/libs/jquery.lazyloadxt/1.0.0/jquery.lazyloadxt.min.j', array('jquery'), '1.0.0', true);
    }

    //if your using in code in plugin uncomment this line and comment other two custom js files
    $custom_js_url =  plugin_dir_url( __FILE__ )."js/custom.js";
    //if your using in code in theme uncomment this line and comment other two custom js files
    //$custom_js_url = get_template_directory_uri() ."js/custom.js";

    //if your using in code in child theme uncomment this line and comment other two custom js files
    //$custom_js_url = get_stylesheet_directory_uri() ."js/custom.js";

    // Register the custom script
    wp_register_script('lazy-custom', $custom_js_url, array('jquery'), '1.0.0', true);
    //Localize the script with based on library
    wp_localize_script( 'lazy-custom', 'lazy', array('library'=>SH_LAZY_LOAD_LIBRARY));
    // Enqueued script with localized library data.
    wp_enqueue_script( 'lazy-custom' );
}



/**
 * Add image placeholders to post or page content,avatar and thumbnail images using "wp_head" hook
 */
add_action( 'wp_head',  'sh_lazy_load_setup_filters_callback' , 9999 );

/**
 * Add image placeholders to post or page content,avatar and thumbnail images using "wp_head" hook callback
 */
function sh_lazy_load_setup_filters_callback(){
    //add image placeholders to post/page content
    add_filter( 'the_content', 'sh_add_lazyload_placeholders_callback' , 99 );
    //run this later, so other content filters have run, including image_add_wh on WP.com
    add_filter( 'post_thumbnail_html','sh_add_lazyload_placeholders_callback' , 11 );
    add_filter( 'get_avatar', 'sh_add_lazyload_placeholders_callback' , 11 );
}
/**
 * Find the images on content and add lazy load image placeholders on it callback
 */
function sh_add_lazyload_placeholders_callback($content) {
    //init dom object
    $dom_obj = new DOMDocument();
    //load content
    @$dom_obj->loadHTML($content);
    //loop html objects which contains image tag
    foreach ($dom_obj->getElementsByTagName('img') as $node) {
        //getting original image source path
        $original_img_src = $node->getAttribute('src');
        //set a new attribute to image "i.e data-src" tag and set image source path
        $node->setAttribute("data-src", $original_img_src );
        //init default load image path
        $default_load_img_src = 'http://localhost/wp/wp-content/uploads/2018/01/sun-300x225.gif';
        //set or replace a src attribute value to default load image path
        $node->setAttribute("src", $default_load_img_src);

        //check for responsive post data
        if ( $node->hasAttribute( 'sizes' ) && $node->hasAttribute( 'srcset' ) ) {
            //getting original image sizes
            $sizes_attr = $node->getAttribute( 'sizes' );
            //getting original image srcsets
            $srcset     = $node->getAttribute( 'srcset' );
            //set a new attribute to image "i.e data-sizes" tag and set original image sizes
            $node->setAttribute( 'data-sizes', $sizes_attr );
            //set a new attribute to image "i.e data-srcset" tag and set original image srcsets
            $node->setAttribute( 'data-srcset', $srcset );
            //remove original image sizes
            $node->removeAttribute( 'sizes' );
            //remove original image srcsets
            $node->removeAttribute( 'srcset' );
        }
        //check for any class included for image tag append class, if not add our class name i.e "lazy"
        if ( $node->hasAttribute( 'class' ) ) {
            $class = $node->getAttribute( 'class' );
            $class .=" lazy";
            $node->setAttribute( 'class',$class );
        }else{
            $node->setAttribute( 'class', "lazy" );
        }
    }
    //save modification
    $newContent = $dom_obj->saveHtml();
    //return
    return $newContent;
}
