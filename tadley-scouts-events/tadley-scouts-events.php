<?php
/**
 * Plugin Name:       Tadley Scouts Events
 * Description:       Create and manage the Events custom post type.
 * Version:           1.2.0
 * Author:            Louis Soccard
 * Author URI:        https://louis.soccard.uk
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

function tsevents_register_event_post_type()
{
    register_post_type('tsevents_event',
        array(
            'labels'      => array(
                'name'          => __('Events', 'textdomain'),
                'singular_name' => __('Event', 'textdomain')
            ),
            'public'      => true,
            'has_archive' => false,
            'rewrite'     => array('slug' => 'event'),
            'menu_icon'   => 'dashicons-calendar',
            'supports'    => array('title', 'editor', 'custom-fields'),
        )
    );
}

function tsevents_cpt_enqueue($hook_suffix)
{
    $cpt = 'tsevents_event';

    if (in_array($hook_suffix, array('post.php', 'post-new.php'))) {
        $screen = get_current_screen();

        if (is_object($screen) && $cpt == $screen->post_type) {
            wp_enqueue_script('admin', plugin_dir_url(__FILE__).'admin.js', array('jquery', 'acf-input'), 1.3, true);
        }
    }
}

function tsevents_is_past_event()
{
    global $post;
    $end_datetime = get_post_meta($post->ID, 'end_datetime', true);

    return $end_datetime < date('Y-m-d H:i:s') ? 'true' : 'false';
}

function tsevents_set_custom_columns($columns)
{
    unset($columns['date']);
    $columns['type']           = 'Event Type';
    $columns['start_datetime'] = 'Start Date/Time';
    $columns['end_datetime']   = 'End Date/Time';

    return $columns;
}

function tsevents_set_sortable_columns($columns)
{
    $columns['type']           = 'event_type';
    $columns['start_datetime'] = 'start_datetime';
    $columns['end_datetime']   = 'end_datetime';

    return $columns;
}

function tsevents_posts_orderby($query)
{
    if ( ! is_admin() || ! $query->is_main_query() || 'tsevents_event' !== $query->get('post_type')) {
        return;
    }

    $orderby = strtolower($query->get('orderby'));
    $mods    = [
        'event_type'     => ['meta_key' => 'event_type', 'orderby' => 'meta_value'],
        'start_datetime' => ['meta_key' => 'start_datetime', 'meta_type' => 'datetime', 'orderby' => 'meta_value'],
        'end_datetime'   => ['meta_key' => 'end_datetime', 'meta_type' => 'datetime', 'orderby' => 'meta_value'],
        ''               => [
            'meta_key'  => 'start_datetime',
            'meta_type' => 'datetime',
            'orderby'   => 'meta_value',
            'order'     => 'ASC'
        ],
    ];
    if (isset($mods[$orderby])) {
        $query->set('meta_key', $mods[$orderby]['meta_key']);
        $query->set('orderby', $mods[$orderby]['orderby']);

        if (isset ($mods[$orderby]['meta_type'])) {
            $query->set('meta_type', $mods[$orderby]['meta_type']);
        }

        if (isset ($mods[$orderby]['order'])) {
            $query->set('order', $mods[$orderby]['order']);
        }
    }
}

function tsevents_get_type_column($post_id)
{
    switch (get_post_meta($post_id, 'event_type', true)) {

        case 'beavers' :
            echo 'Beavers';
            break;
        case 'cubs' :
            echo 'Cubs';
            break;
        case 'scouts' :
            echo 'Scouts';
            break;
        case 'district-county-event' :
            echo 'District/County';
            break;
        case 'group-event' :
            echo 'Group';
            break;
        default :
            echo 'Other';
            break;

    }
}

function tsevents_get_date_column($post_id, $key)
{
    $date = get_post_meta($post_id, $key, true);
    echo date('d/m/Y (H:i)', strtotime($date));
}

function tsevents_custom_column($column, $post_id)
{
    switch ($column) {

        case 'type' :
            tsevents_get_type_column($post_id);
            break;

        case 'start_datetime' :
            tsevents_get_date_column($post_id, 'start_datetime');
            break;

        case 'end_datetime' :
            tsevents_get_date_column($post_id, 'end_datetime');
            break;

    }
}

add_action('init', 'tsevents_register_event_post_type');
add_action('admin_enqueue_scripts', 'tsevents_cpt_enqueue');

add_filter('manage_tsevents_event_posts_columns', 'tsevents_set_custom_columns');

add_filter('manage_edit-tsevents_event_sortable_columns', 'tsevents_set_sortable_columns');
add_action('pre_get_posts', 'tsevents_posts_orderby');

add_action('manage_tsevents_event_posts_custom_column', 'tsevents_custom_column', 10, 2);
