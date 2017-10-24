<!DOCTYPE HTML>
<?php

$starttime = microtime(true);

$lang = "en";
$current_page = "showUAstrings.php";
$title = "Show user-agent strings";
include_once("include/head.php");

if (isset($_GET["debug"]) and $_GET["debug"] == "TRUE") $debug = true;
else $debug = false;




// error_reporting(E_ALL);
error_reporting(E_ALL & ~E_NOTICE);
if ($debug) {
  ini_set("display_errors", 1);
}
else {
  ini_set("display_errors", 0);
}

ini_set("log_errors", 1);
ini_set("error_log", "php_errors.log");
// error_reporting(E_ALL);








$badBots = file_get_contents("/var/www/shared/blackhole/blackhole.dat");





?>

<body>

<?php
require_once("../include/dbconnect.php");
include_once("../include/functions.php");
// include_once("../include/counter-mySQL.php");




// ===================================================================


$counterdb = mysqli_connect($hostname, $userdb, $passworddb, $db);
if (mysqli_connect_errno()) {
  echo "Failed to connect to MySQL: " . mysqli_connect_error() . "<br>\n";
} else {
  if ($debug) echo "Successfully connected. " . mysqli_connect_error() . "<br>\n";
}

if (!mysqli_set_charset($counterdb, "utf8")) {
  printf("Error loading character set utf8: %s<br>\n", mysqli_error($counterdb));
} else {
  if ($debug) printf("Current character set: %s<br>\n", mysqli_character_set_name($counterdb));
}



if (isset($_GET["reset_counters"]) and $_GET["reset_counters"] == "true") {
  $query = mysqli_real_escape_string($counterdb, "UPDATE `$db`.`user-agents` SET `hits` = 0 ;");
  $result = mysqli_query($counterdb, $query);
}



if (!isset($_GET["orderby"])) {
//   $_GET["orderby"] = "id";
  $orderby = "id";
  $order = "ASC";
} else {
  switch ($_GET["orderby"]) {
    case "hits":              { $orderby = "hits";              $order = "DESC"; break; }
    case "user-agent-string": { $orderby = "user-agent-string"; $order = "ASC";  break; }
    default:                  { $orderby = "id";                $order = "ASC";  break; }
  }
}

$query = mysqli_real_escape_string($counterdb, "SELECT * FROM `{$db}`.`user-agents` ORDER BY `{$orderby}` {$order};");
if ($debug) echo "Line 87: " . $query . "<br>\n";
$result = mysqli_query($counterdb, $query);
$numlen = strlen(mysqli_num_rows($result));

?>
<h1 class="center">Recorded user-agent-strings</h1>

<p class="center">
  <span class="badbot">bad bots</span>
  <span class="bot">Bots</span>
  <span class="feedreader">Feedreaders</span>
</p>

<div class="left">
  <form action="showUAstrings.php" method="GET" accept-charset="UTF-8">
  <?php
    foreach ($_GET as $key => $value) {
      if ($key == "reset_counters") continue;
      ?>
      <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>">
      <?php
    }
  ?>
    <button class="big" type="submit">↻ Refresh</button>
  </form>
</div>
<div class="left">
  <form action="showUAstrings.php" method="GET" accept-charset="UTF-8">
    <?php if (isset($_GET["orderby"])) { ?>
    <input type="hidden" name="orderby" value="<?php echo $_GET["orderby"]; ?>">
    <?php } ?>
    <input type="hidden" name="reset_counters" value="true">
    <button class="big" type="submit">↻ Reset counters</button>
  </form>
</div>

<div class="right">
  <form action="showUAstrings.php" method="GET" accept-charset="UTF-8">
    <input type="hidden" name="orderby" value="hits">
    <button class="big" type="submit">⌖ Order by hits</button>
  </form>
</div>
<div class="right">
  <form action="showUAstrings.php" method="GET" accept-charset="UTF-8">
    <input type="hidden" name="orderby" value="user-agent-string">
    <button class="big" type="submit">⚿ Order by UAS</button>
  </form>
</div>
<div class="right">
  <form action="showUAstrings.php" method="GET" accept-charset="UTF-8">
    <button class="big" type="submit">♻ Reset</button>
  </form>
</div>
<div class="clear"></div>

<?php
$countTotal = 0;
$countBadBots = 0;
$countBots = 0;
$countFR = 0;

$strlen = 0;
$strlenHits = 0;
while ($row = $result->fetch_assoc()) {
  // ["id", "user-agent-string", "hits"]
  $userAgents[] = array("raw" => $row);
  $countTotal += $row["hits"];
  $strlenHits = strlen($row["hits"]);
  if ($strlenHits > $strlen) $strlen = $strlenHits;
}

mysqli_free_result($result);
mysqli_close($counterdb);


// Examples:
// Firefox 7.0.1 Windows XP
// Mozilla/5.0 (Windows NT 5.1; rv:7.0.1) Gecko/20100101 Firefox/7.0.1
//
// Firefox 55.0.3 Linux x86_64
// Mozilla/5.0 (X11; Linux x86_64; rv:55.0) Gecko/20100101 Firefox/55.0
//
// Tor-Browser 7.0.5 (based on Mozilla Firefox 52.3.0) (64-Bit)
// Mozilla/5.0 (Windows NT 6.1; rv:52.0) Gecko/20100101 Firefox/52.0
//
// Chromium 60.0.3112.78
// Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.78 Safari/537.36
//
// Opera 47.0.2631.80
// Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36 OPR/47.0.2631.80










$skipUasRegistry = false;
include_once("/var/www/vhosts/chris-webserver.de/include/detect_bots.php");

$i = 0;
echo "<pre>";

foreach ($userAgents as $key => $value) {

  if ($countTotal != 0) {
    $userAgents[$key]["percentage"] = ($value["raw"]["hits"] / $countTotal) * 100;
  } else {
    $userAgents[$key]["percentage"] = 0.0;
  }

  $output = sprintf("% {$numlen}d : % {$strlen}d hits (% 5.2f%%) - ", ++$i, $value["raw"]["hits"], $userAgents[$key]["percentage"]);

  if ($value["raw"]["user-agent-string"] == $_SERVER["HTTP_USER_AGENT"]) {
    $output = "<b title=\"your browser:\n{$_SERVER["HTTP_USER_AGENT"]}\">{$output}</b>";
  }



  $botSwitch = detectBot($value["raw"]["user-agent-string"]);
  $FRSwitch = detectFeedreader($value["raw"]["user-agent-string"]);

  $userAgentString = trim($value["raw"]["user-agent-string"]);



  if (
    isset($userAgentString)
    and
    strlen($userAgentString) > 0
    and
    stristr($badBots, $userAgentString) !== false
  ) {

    // if we have identified a bad bot, mark it!
    $countBadBots++;
    $countBots++;
    $output .= "<span class=\"badbot\" title=\"badBot\">{$userAgentString}</span>";

  } elseif ($botSwitch) {

    // if we have identified a bot, mark it!
    $countBots++;
    $output .= "<span class=\"bot\" title=\"Bot\">{$userAgentString}</span>";

  } elseif ($FRSwitch) {

    // if we have identified a feedreader, mark it!
    $countFR++;
    $output .= "<span class=\"feedreader\" title=\"Feedreader\">{$userAgentString}</span>";

  } else {

    $output .= $userAgentString;

  }

  echo $output . "\n";
}
echo "</pre>\n";

$endtime = microtime(true);

?>

  <p>Detected <span class="badbot"><?php echo $countBadBots; ?> bad bots</span>, <span class="bot"><?php echo $countBots; ?> bots</span> and <span class="feedreader"><?php echo $countFR; ?> feedreaders</span>.</p>

  <p class="center notes"><?php echo $countTotal; ?> hits total - processing needed <?php echo prettyTime($endtime - $starttime); ?></p>



</body>
</html>
