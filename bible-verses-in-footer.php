<?php
/**
 * Plugin Name: Bible Verses in Footer
 * Plugin URI: https://www.7pixel.cl/bible-verses-in-footer
 * Description: This plugin displays a daily Bible verse in the footer of the WordPress admin area, using the Verse of the Day API.
 * Version: 1.0
 * Author: Marco Alvarado
 * Author URI: https://www.7pixel.cl
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: bible-verses-in-footer
 */

if (!class_exists('WP_Http')) {
    include_once(ABSPATH . WPINC . '/class-http.php');
}

function bible_verses_in_footer() {
    $cached_verse = get_transient('bible_verse_of_the_day');

    if (false === $cached_verse) {
        $language = get_bloginfo('language');
        $supported_languages = array(
            'en' => 'kjv',
            'es' => 'rvr60'
            // Add more supported languages and their corresponding Bible versions here
        );

        $lang_code = substr($language, 0, 2);
        $version = isset($supported_languages[$lang_code]) ? $supported_languages[$lang_code] : 'kjv';
        
        $url = "https://labs.bible.org/api/?passage=votd&type=json&version={$version}";
        $request = new WP_Http;
        $response = $request->request($url);

        if (!is_wp_error($response) && isset($response['body'])) {
            $verse_data = json_decode($response['body'], true);
            if (!empty($verse_data)) {
                $verse = $verse_data[0]['bookname'] . " " . $verse_data[0]['chapter'] . ":" . $verse_data[0]['verse'] . " - " . $verse_data[0]['text'];
                // Cache the verse for 24 hours (86400 seconds)
                set_transient('bible_verse_of_the_day', $verse, 86400);
                echo "<p id='bible-verse'>" . esc_html($verse) . "</p>";
            }
        }
    } else {
        echo "<p id='bible-verse'>" . esc_html($cached_verse) . "</p>";
    }
}


add_action('admin_footer', 'bible_verses_in_footer');

function bible_verses_in_footer_css() {
    echo "<style>
    #bible-verse {
        font-size: 14px;
        color: #666;
        padding-top: 10px;
        padding-left: 25vw;
        padding-right: 25vw;
        text-align: center;
        box-sizing: border-box;
        width: calc(100% - 30px);
        position: absolute;
        left: 0;
        right: 0;
        margin-left: auto;
        margin-right: auto;    
    }
    </style>";
}

add_action('admin_head', 'bible_verses_in_footer_css');
?>