<?php

error_reporting(E_ALL);
ini_set("display_errors", 0);

require( dirname(__FILE__) . '/../wp-load.php' );
include_once dirname(__FILE__) . '/../wp-config.php';
include_once dirname(__FILE__) . '/../wp-includes/wp-db.php';
include_once dirname(__FILE__) . '/../wp-includes/pluggable.php';

global $wpdb;
$select = $wpdb->get_results("SELECT * FROM " . $wpdb->base_prefix . "options WHERE option_name LIKE 'chunk_%' ORDER BY option_name ASC LIMIT 0 , 1");

$count = count($select);
if ($count == 0) {
    $query = new WP_Query($args = array(
        'order' => 'ASC',
        'orderby' => 'title',
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => -1
            )
    );
    $total = $query->found_posts;
    $post_ids = wp_list_pluck($query->posts, 'ID');
    $chunks = array_chunk($post_ids, 10);

    $i = 0;
    foreach ($chunks as $chunk) {
        $i++;
        update_option('chunk_' . $i, $chunk);
    }
}

$idsBlock = unserialize($select[0]->option_value);
foreach ($idsBlock as $id) {

    // common
    $data = array();
    $url = get_permalink($id);
    $post_id = $id;

    // twitter
    $dataUrl = 'https://cdn.api.twitter.com/1/urls/count.json?url=' . $url;
    $dataOrig = file_get_contents($dataUrl);
    if (!empty($dataOrig)) {
        $dataOrig = json_decode($dataOrig);
        $data['twitter'] = $dataOrig->count;
    }

    // facebook
    $dataUrl = 'http://graph.facebook.com/fql?q=SELECT%20total_count%20FROM%20link_stat%20WHERE%20url=%22' . $url . '%22';
    $dataOrig = file_get_contents($dataUrl);
    if (!empty($dataOrig)) {
        $dataOrig = json_decode($dataOrig);
        $dataOrig = $dataOrig->data[0];
        $data['facebook'] = $dataOrig->total_count;
    }

    // google
    $dataUrl = 'https://plusone.google.com/u/0/_/+1/fastbutton?url=' . $url . '&count=true';
    $content = parse($dataUrl);

    $dom = new DOMDocument;
    $dom->preserveWhiteSpace = false;
    @$dom->loadHTML($content);
    $domxpath = new DOMXPath($dom);
    $newDom = new DOMDocument;
    $newDom->formatOutput = true;

    $filtered = $domxpath->query("//div[@id='aggregateCount']");
    $data['google'] = str_replace('>', '', $filtered->item(0)->nodeValue);


    //pinterest
    $dataUrl = 'http://api.pinterest.com/v1/urls/count.json?callback=receiveCount&url=' . $url;
    $dataOrig = json_decode(str_replace(array('(', ')'), array('', ''), $dataUrl));
    if (is_int($dataOrig->count)) {
        $data['pinterest'] = $dataOrig->count;
    }

    //var_dump($post_id, $data, array_sum($data));
    update_post_meta($post_id, 'social_count', array_sum($data));
}
delete_option($select[0]->option_name);

// function
function parse($encUrl) {
    $options = array(
        CURLOPT_RETURNTRANSFER => true, // return web page
        CURLOPT_HEADER => false, // don't return headers
        CURLOPT_FOLLOWLOCATION => true, // follow redirects
        CURLOPT_ENCODING => "", // handle all encodings
        CURLOPT_USERAGENT => 'sharrre', // who am i
        CURLOPT_AUTOREFERER => true, // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 5, // timeout on connect
        CURLOPT_TIMEOUT => 10, // timeout on response
        CURLOPT_MAXREDIRS => 3, // stop after 10 redirects
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => false,
    );
    $ch = curl_init();

    $options[CURLOPT_URL] = $encUrl;
    curl_setopt_array($ch, $options);

    $content = curl_exec($ch);
    $err = curl_errno($ch);
    $errmsg = curl_error($ch);

    curl_close($ch);

    if ($errmsg != '' || $err != '') {
        print_r($errmsg);
    }
    return $content;
}

?>