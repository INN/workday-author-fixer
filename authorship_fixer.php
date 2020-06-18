<style>
body {
  white-space: pre;
}
</style>

<?php
require_once('wp-load.php');
require_once('data.php');
global $wpdb;




$moi = get_user_by("email", "brenda@gravityswitch.com");
$them = get_user_by("email", "team@advantagelabs.com");
$generic = get_user_by("email", "brenda@gravityswitch.com");


$all_posts = new WP_Query(array(
  "post_type" => "post",
  'posts_per_page'   => -1,
));
foreach ($all_posts->posts as $this_post) {
  $message = "";

  // get the drupal id if it exists
  $nid = get_post_meta($this_post->ID, "_fgd2wp_old_node_id", true);
  $largo_byline_text = get_post_meta($this_post->ID, "largo_byline_text", true);
  if ( !empty( $nid )  && empty( $largo_byline_text ) ) {
    // enough revisions crap, do the byline
    $byline_data = array_values(array_filter($field_data_field_author, function($v) use ($nid) {return $v["entity_id"] === $nid;}));
    if (!empty($byline_data) && !empty($byline_data[0])) {
      $byline_author = $byline_data[0]["field_author_value"];
      update_post_meta($this_post->ID, "largo_byline_text", $byline_author);
      $message = $this_post->ID." NID:".$nid." WOO! added/updated byline data";
    }
    else {
      $authref = array_values(array_filter($field_data_field_author_reference, function($v) use ($nid) {return $v["entity_id"] === $nid;}));
      //var_dump($authref);
      if (!empty($authref[0]["field_author_reference_target_id"])) {
        $profile = array_values(array_filter($profiles, function($v) use ($authref) {return $v["nid"] === $authref[0]["field_author_reference_target_id"];}));
        //var_dump($profile);
        if (!empty($profile)) {
          // var_dump($profile[0]["title"]);
          update_post_meta($this_post->ID, "largo_byline_text", $profile[0]["title"]);
          $message = $this_post->ID." NID:".$nid." WOO! added/updated byline data: " . var_export( $profile[0]['title'], true );
        }
        else {
          $message .= $this_post->ID." NID:".$nid." ERROR! no byline data found. cause: empty profile";
        }
      }
      else {
        $message .= $this_post->ID." NID:" . $nid . ' ERROR! no byline data found. cause: no $authref[0]["field_author_reference_target_id"]';
      }
    }
  } else {
    $message .= $this_post->ID . "SKIP! no byline data found. cause: no nid in _fgd2wp_old_node_id post meta";
  }

  echo $message."\n";
}
