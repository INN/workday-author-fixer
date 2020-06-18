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



$all_posts = new WP_Query(array(
  "post_type" => "post",
  'posts_per_page'   => -1,
));
foreach ($all_posts->posts as $this_post) {
  $message = "";

  // get the drupal id if it exists
  $nid = get_post_meta($this_post->ID, "_fgd2wp_old_node_id", true);
  if (!empty($nid)) {
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
