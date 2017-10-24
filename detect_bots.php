<?php

function detectBot($UAS) {

  $bots = "bot|crawl|spider|slurp|search|google|bing|okhttp|Daum|ubermetrics|Scrapy|Gigablast|qwant|Test Certificate Info|Dataprovider|ltx71|ips-agent|riddler|securepoint|panscient|KOCMOHABT|PocketParser|survey|agent|b-o-t|facebook|classify|splash";

// ^                      from the beginning of the line
// .*                     get any character except \n (0 or more times)
// (8768|9875|2353)       if the line contains the string '8768' OR '9875' OR '2353'
// .*                     and get any character except \n (0 or more times)
// $                      until the end of the line

  $match = preg_match("/^.*($bots).*\$/i", $UAS);

  return $match;
}

function detectFeedreader($UAS) {
  $feedreaders = "feed|rss|liferea|NewsBlur";

  $match = preg_match("/^.*($feedreaders).*\$/i", $UAS);

  return $match;
}








// =======================================================================================


  if (!isset($debugUAS) and isset($_GET["debug"]) and $_GET["debug"] == "TRUE") {
    $debugUAS = TRUE;
  } else {
    if (isset($debug) and $debug == "TRUE") {
      $debugUAS = TRUE;
    } else {
      $debugUAS = FALSE;
    }
  }


  $dbUAS = mysqli_connect($hostname, $userdb, $passworddb, $db);
  if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error() . "<br>\n";
  }

  if (!mysqli_set_charset($dbUAS, "utf8")) {
    printf("Error loading character set utf8: %s<br>\n", mysqli_error($dbUAS));
  }

  $uas = $_SERVER["HTTP_USER_AGENT"];
  $uas_escaped = mysqli_real_escape_string($dbUAS, $uas);

  $db_UAS = "chris-webserver.de";
  $db_UAS_table = "user-agents";

  // don't record the same UAS twice! But record the hits.
  $query = "SELECT * FROM `$db_UAS`.`$db_UAS_table` WHERE `user-agent-string` LIKE '$uas_escaped' ;";
  $result = mysqli_query($dbUAS, $query) or die(mysqli_error($dbUAS));
  $uas_num = mysqli_num_rows($result);
  mysqli_free_result($result);

  if ($uas_num < 1) {
    $query = "INSERT INTO `$db_UAS`.`$db_UAS_table` (`id`, `user-agent-string`, `hits`) VALUES (NULL, '$uas_escaped', 1) ;";
    $result = mysqli_query($dbUAS, $query) or die(mysqli_error($dbUAS));
  } else {
    $query = "UPDATE `$db_UAS`.`$db_UAS_table` SET `hits` = hits + 1 WHERE `user-agent-string` = '$uas_escaped' ;";
    $result = mysqli_query($dbUAS, $query) or die(mysqli_error($dbUAS));
  }

  mysqli_close($dbUAS);

// =======================================================================================





?>
<!-- EOF detect_bots.php -->
