<?php
/**
 * @package JoinUs4HealthNewsletterConsents
 * @version 0.1
 */

/*
Plugin Name: JoinUs4Health newsletter consents
Description: Plugin for JoinUs4Health webpage
Version: 0.1
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

function ju4hconewsletter_custom_post_type() {
    $labels = array(
        'name'                => _x('Newsletter consents', 'Post Type General Name'),
        'singular_name'       => _x('Newsletter consent', 'Post Type Singular Name'),
        'menu_name'           => __('Newsletter consents'),
        'parent_item_colon'   => __('Newsletter parent consent'),
        'all_items'           => __('All Newsletter consents'),
        'view_item'           => __('View Newsletter consent'),
        'add_new_item'        => __('Add new newsletter consent'),
        'add_new'             => __('Add new'),
        'edit_item'           => __('Edit newsletter consent'),
        'update_item'         => __('Update newsletter consent'),
        'search_items'        => __('Search newsletter consent'),
        'not_found'           => __('Not found'),
        'not_found_in_trash'  => __('Not found in trash'),
    );
    
    $show_ui = false;
    if (current_user_can('administrator')) {
        $show_ui = true;
    }
    
    $args = array(
        'label'               => __('Newsletter consents'),
        'description'         => __('Newsletter consents'),
        'labels'              => $labels,
        'supports'            => array(''),
        'hierarchical'        => false,
        'public'              => false,
        'show_ui'             => $show_ui,
        'show_in_menu'        => $show_ui,
        'show_in_nav_menus'   => $show_ui,
        'show_in_admin_bar'   => $show_ui,
        'menu_position'       => 5,
        'can_export'          => true,
        'has_archive'         => false,
        'exclude_from_search' => true,
        'publicly_queryable'  => false,
        'capability_type'     => 'post',
        'show_in_rest'        => false,
        'capabilities' => array(
            'create_posts' => false, // Removes support for the "Add New" function ( use 'do_not_allow' instead of false for multisite set ups )
        ),
        'map_meta_cap' => false, // Set to `false`, if users are not allowed to edit/delete existing posts
    );
    
    register_post_type('ju4hconewsletter', $args);
}
add_action('init', 'ju4hconewsletter_custom_post_type', 0);

function manage_ju4hconewsletter_posts_columns_callback($columns) {
    $columns['user'] = __('User');
    $columns['userid'] = __('User ID');
    $columns['consent'] = __('Consent');
    unset($columns['cb']);
    unset($columns['title']);
    unset($columns['author']);
    unset($columns['comments']);
    return $columns;
}
add_filter('manage_ju4hconewsletter_posts_columns', 'manage_ju4hconewsletter_posts_columns_callback');

function manage_ju4hconewsletter_posts_custom_column_callback($column, $post_id) {
    global $wpdb;
    
    if ('userid' === $column) {
        $user_id = get_post_meta($post_id, 'm_newsletter_conset_user_id', true);
        echo '#'.$user_id;
    }
    
    if ('user' === $column) {
        $user_id = get_post_meta($post_id, 'm_newsletter_conset_user_id', true);
        $result = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ig_contacts WHERE id=".((int)$user_id)." LIMIT 1");

        if (count($result) > 0) {
            echo $result[0]->email;
        } else {
            echo 'not exists';
        }
    }
    
    if ('consent' === $column) {
        echo get_post_meta($post_id, 'm_post_value', true);
    }
}
add_action('manage_ju4hconewsletter_posts_custom_column', 'manage_ju4hconewsletter_posts_custom_column_callback', 10, 2);

function ju4h_ig_es_contact_subscribe($newsletter_user_id, $param2) {
    global $wpdb;

    $new = array(
        'post_title'   => 'Newsletter consent #'.$newsletter_user_id,
        'post_status'  => 'publish',
        'post_type'    => 'ju4hconewsletter',
    );
    
    $es_gdpr_consent = 'false';
    if (isset($_POST['es_gdpr_consent']) && $_POST['es_gdpr_consent'] == 'true') {
        $es_gdpr_consent = 'true';
    }

    $post_id = wp_insert_post($new);
    add_post_meta($post_id, 'm_newsletter_conset_user_id', $newsletter_user_id);
    add_post_meta($post_id, 'm_post_value', $es_gdpr_consent);
}

add_filter('ig_es_contact_subscribe', 'ju4h_ig_es_contact_subscribe', 10, 2);
