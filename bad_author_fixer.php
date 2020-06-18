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

$bad_wpauthor_posts = new WP_Query(array(
  "post_type" => "post",
  'posts_per_page'   => -1,
  'author__in' => array($them->ID)
));
//var_dump($allposts);
foreach ($bad_wpauthor_posts->posts as $this_post) {

  $message = "";

  // get the drupal id if it exists
  $nid = get_post_meta($this_post->ID, "_fgd2wp_old_node_id", true);
  if (!empty($nid)) {
    // find earliest revision of post
    $revisions_of_this_post = array_values(array_filter($node_revision, function($v) use ($nid) {return $v["nid"] === $nid;})); //  && $v["uid"] != "0" && $v["uid"] != "1";

    if (!empty($revisions_of_this_post)) {
      // find earliest revision : don't need to anymore, we pre-sorted the revisions table
      /*
      usort($revisions_of_this_post, function($a, $b) {
        return $a['timestamp'] - $b['timestamp'];
      });
      */
      $earliest_version = $revisions_of_this_post[0];
      $original_author_uid = $earliest_version["uid"];
      $original_author_data = array_values(array_filter($users, function($v) use ($original_author_uid) {return $v["uid"] === $original_author_uid;}));
      if (!empty($original_author_data)) {
        $original_author = $original_author_data[0];

        $wp_version_of_author = get_user_by("email", $original_author["mail"]);

        if (!empty($wp_version_of_author)) {
          $outcome = wp_update_post( array (
        		'ID'           => $this_post->ID,
            'post_author'   => $wp_version_of_author->ID,
        	));

          $message = $this_post->ID." SUCCESS? ".print_r($outcome, true)." earliestver: ".$earliest_version["vid"]."author ".$wp_version_of_author->ID;
        } else {
          $message = $this_post->ID." ERROR! no wp author found";
        }
      }
      else {
        $message = $this_post->ID." ERROR! no drupal author found";
      }
    } else {
      $message = $this_post->ID." WELL! no valid drupal post revisions found... assigning to generic user";

      wp_update_post( array (
        'ID'           => $this_post->ID,
        'post_author'   => $generic->ID
      ));

    }

  } else {
    $message = $this_post->ID." ERROR! no drupal nid found";
  }

  echo $message."\n";

}


