<?php
// PHP script by Ken True, webmaster@saratoga-weather.org
// buoy-data.php  version 1.00 - 28-Jun-2006
//                version 1.01 - 29-Jun-2006 added buoy config checking,
//                                           ?cache=no for testing
//                Version 1.02 - 30-Jun-2006 added inc=MAP, inc=TABLE options
//		          Version 1.03 - 26-Aug-2006 added run/pause/step controls
//                                           and new wind arrows from Kevin at
// 		                                     www.tnetweather.com
//                Version 1.04 - 17-Nov-2006 updated code to fix "Notice" type errors
//                Version 1.05 - 28-Nov-2006 updated code for XHTML 1.0-Strict output
//                Version 1.06 - 22-Feb-2007 updated code for IIS \r line terminations
//	              Version 1.07 - 21-Aug-2007 corrected flag processing for kts->mph conversion
//                Version 1.08 - 20-Sep-2007 update to reflect change in NDBC website
//                Version 1.09 - 22-Sep-2007 more update to add debugging code and more NDBC website changes
//                Version 1.10 - 15-Oct-2007 new kts=Y/N parm, $showKnots spec force wind to Knots display
//                Version 1.11 - 28-Dec-2007 added another fix for processing on IIS servers
//                Version 1.12 - 21-Feb-2008 added features for simpler include to webpage
//                Version 1.13 - 22-Mar-2008 corrected generated CSS + more ISS support
//                Version 1.14 - 20-Mar-2009 added support for IE8 rotating conditions
//                Version 1.15 - 29-Apr-2009 minor fixes for PHP 5.2+
//                Version 1.16 - 21-Nov-2010 added diagnostics to page get from NDBC
//
    $Version = "buoy-data.php V1.16 21-Nov-2010";
// error_reporting(E_ALL);  // uncomment to turn on full error reporting
// script available at http://saratoga-weather.org/scripts.php
//  
// you may copy/modify/use this script as you see fit,
// no warranty is expressed or implied.
//
// Customized for: buoy data from www.nbdc.noaa.gov using
//   http://www.ndbc.noaa.gov/radial_search.php
//
// output: creates XHTML 1.0-Strict HTML page 
//
// Options on URL:
//      units=E (default)-- use English units in display 
//      units=M          -- use Metric units in display
//
//      cnv=Y   (default)-- convert wind speed m/s->kph and kts->mph
//      cnv=N            -- leave wind speed in m/s and kts
//
//      inc=Y           -- returns only the body code for inclusion
//                         in other webpages.  Omit to return full HTML.
//      inc=CSS         -- returns the CSS style sheet only
//      inc=MAP         -- returns the map with rotating values display
//      inc=TABLE       -- returns the table of values
//
//  for debugging purposes, these are useful:
//
//      show=normal (default) - shows normal graphic
//      show=hotspots     - shows graphic with hotspots outlined in green
//      show=map          - returns graphic only with outlined hotspots
//
//      cfg=list          - display config info as HTML comments
//
// example URL:
//  http://your.website/buoy-data.php?inc=Y&distance=300
//  would return data without HTML header/footer for buoys 
//  within a 300 nautical mile radius of your location.
//
// Usage:
//  you can use this webpage standalone (customize the HTML portion below)
//  or you can include it in an existing page by doing the following:
//
//  in the <head></head> portion of your page insert
//    <?php include("http://your.website/buoy-data.php?inc=CSS"); 
//   and in the <body> portion where you'd like the map/table to appear
//    <?php include("http://your.website/buoy-data.php?inc=Y");
//  Note: you must include the CSS in your <head> part otherwise the
//    mesomap will not be formatted correctly. 
//
//  Alternative method for PHP only:
//  
//  $doPrintBUOY = false;
//  include("buoy-data.php");
//  print $BUOY_CSS; 
//  
//  in the <head></head> section of the page, then in the <body></body> section
//
//  print $BUOY_MAP; print $BUOY_TABLE; 
//
// settings: ---------------------------------------------------------------
//  most of the script is controlled by the $Config file, so make sure
//  it is correct ;-) 
//
// ------- REQUIRED SETTINGS ------
  $Config = 'mybuoy-Monterey_Bay.txt';        // configuration file name
//  
//  use the config file listed above to control the mapimage name, location,
//  and buoys to be processed.  See the sample files for more examples.
//
//
  $DefaultUnits = 'E';        // 'E' = English, 'M' = Metric, may be 
//                           overridden by units=M or units=E parameter
//
  $ourTZ = 'America/Los_Angeles';  //NOTE: this *MUST* be set correctly to
// translate UTC times to your LOCAL time for the displays.
//  http://saratoga-weather.org/timezone.txt  has the list of timezone names
//  pick the one that is closest to your location and put in $ourTZ like:
//    $ourTZ = 'America/Los_Angeles';  // or
//    $ourTZ = 'Europe/Brussels';
// also available is the list of country codes (helpful to pick your zone
//  from the timezone.txt table
//  http://saratoga-weather.org/country-codes.txt : list of country codes
// ------- END OF REQUIRED SETTINGS ------
//
// cacheName is name of file used to store cached NDBC webpage
// 
  $cacheName = "NDBC-buoydata.txt";  // used to store the file so we 
//                                        don't have to fetch it each time
//  Note: the cache name will have an E or M prepended to the .txt so there
//    can be separate cache files for English and Metric measurements.
//  If you use more than one instance of the script in a directory with
//  different config files, then be sure to change $cacheName to prevent 
//  interference between the scripts.
//
  $refetchSeconds = 600;     // refetch every nnnn seconds (600=10 minutes)
//
  $doWindConvert = true;  // convert knots->mph and m/s->kph (override by
//                          cvt=Y or cvt=N parm on URL.
  $showKnots = false;      // always show wind in Knots (set =true )
//                           override by kts=Y or kts=N on URL.
//
  $windArrowDir = './arrows/'; // set to directory with wind arrows, or
//                        set to '' if wind arrows are not wanted
//                        the program will test to see if images are 
//                        available, and set it to '' if no images are
//                        found in the directory specified.
//                        // used for rotating legend display :
  $windArrowSize = 'S';   // ='S' for Small 9x9 arrows   (*-sm.gif)
//                           ='L' for Large 14x14 arrows (*.gif)
//
//  program control variables.  Set to false to turn off, or true to turn on
//  
  $showNoData = true;    // display 'no recent data' in table
//                        set to true if you want to see those
  $doPrintTable = true;   // turn on/off print of the table data
  $doPrintMap = true;     // turn on/off print of the meso-map
//
//$timeFormat = 'D, Y-m-d H:i:s T';  // Fri, 2006-03-31 14:03:22 TZone
  $timeFormat = 'D, Y-m-d H:i:s T';  //
//
//
// end of settings ------------------------------------------------------
//
// Changes to the code below are not required for normal operation.  There
// are areas where the HTML can be tweaked (marked with comments).  If you
// want to do code changes, be aware that the HTML is generated and stored 
// in variables, then output at the last of the main program (before the 
// function definitions) so finding what to tweak may be a challenge.. 
//    Good luck, and best regards, Ken
//
if (isset($_REQUEST['sce']) && strtolower($_REQUEST['sce']) == 'view' ) {
//--self downloader --
   $filenameReal = __FILE__;
   $download_size = filesize($filenameReal);
   header('Pragma: public');
   header('Cache-Control: private');
   header('Cache-Control: no-cache, must-revalidate');
   header("Content-type: text/plain");
   header("Accept-Ranges: bytes");
   header("Content-Length: $download_size");
   header('Connection: close');
   readfile($filenameReal);
   exit;
}

// process parameters
global $Status;
$Status = '';
if (isset($_REQUEST['errors']) and $_REQUEST['errors'] == 'all') { 
  error_reporting(E_ALL);
  $Status .= "<!-- error_reporting(E_ALL) used -->\n";
}

// include the settings override file if it exists
if (file_exists("buoy-data-config.txt")) {
  require_once("buoy-data-config.txt");
  $Status .= "<!-- buoy-data-config.txt settings override -->\n";
}
if(isset($_REQUEST['units'])) { 
$myUOM = strtoupper($_REQUEST['units']);
} else {
 $myUOM = '';
}
if ($myUOM <> "E" and $myUOM <> "M" ) {
   $myUOM = "$DefaultUnits";  
} 
   
if ($myUOM == "E") {
  $distUnits = "nm";
} else {
  $distUnits = "km";
}
if (isset($_REQUEST['cvt']) &&
   strtoupper($_REQUEST['cvt']) == 'N') { 
   $doWindConvert = false; 
 }
if (isset($_REQUEST['cvt']) &&
   strtoupper($_REQUEST['cvt']) == 'Y') { 
   $doWindConvert = true; 
 }
if (isset($_REQUEST['kts']) &&
   strtoupper($_REQUEST['kts']) == 'Y') { 
   $showKnots = true; 
 }
if (isset($_REQUEST['kts']) &&
   strtoupper($_REQUEST['kts']) == 'N') { 
   $showKnots = false; 
 }
if (! isset($doPrintBUOY) ) { $doPrintBUOY = true; }

if (isset($_REQUEST['inc'])) {
$includeOnly = strtoupper($_REQUEST['inc']); // any nonblank is ok
} else {
$includeOnly = ''; 
}
if ($includeOnly == 'CSS') {
  $CSSonly = true;
 } else {
  $CSSonly = false;
}
if ($includeOnly == 'MAP') {
  $doPrintTable = false;
  $doPrintMap   = true;
}
if ($includeOnly == 'TABLE') {
  $doPrintTable = true;
  $doPrintMap   = false;
}

// show map with hotspots outlined
if (isset($_REQUEST['show']) && strtolower($_REQUEST['show']) == 'map' ) {
 $ShowMap = '&show=map';
 $genJPEG = true;
} else {
 $ShowMap = '';  // no outlines for map
 $genJPEG = false;
}


if($windArrowDir && ! file_exists("$windArrowDir" . 'NNE.gif') ) {
   $windArrowDir = '';  // bad spec.. no arrows found
}
  
if (isset($_REQUEST['cfg']) && strtolower($_REQUEST['cfg']) == 'list') {
  $doListConfig = true; 
  } else {
  $doListConfig = false;
}

if (isset($_REQUEST['cache']) && strtolower($_REQUEST['cache']) == 'no') {
  $refetchSeconds = 0;  // set short period for refresh of cache
}

$t = pathinfo(__FILE__);
$Program = $t['basename'];
if (!isset($PHP_SELF)) {$PHP_SELF = $_SERVER['PHP_SELF']; }
	
// Constants -------------------------------------------------------------
// don't change $baseURL or $fileName or script may break ;-)
  $NDBCURL = "http://www.ndbc.noaa.gov";  //NDBC website (omit trailing slash)
// end of constants ------------------------------------------------------

// ------------- Main code starts here -------------------

global $seenBuoy,$MapImage,$myLat,$myLong,$Buoys,$table,$scroller;
global $myUOM,$doWindConvert,$showKnots;
// Establish timezone offset for time display
  if (!function_exists('date_default_timezone_set')) {
	putenv("TZ=" . $ourTZ);
	$Status .= "<!-- using putenv(\"TZ=$ourTZ\") -->\n";
    } else {
	date_default_timezone_set("$ourTZ");
	$Status .= "<!-- using date_default_timezone_set(\"$ourTZ\") -->\n";
   }
  $timediff = tzdelta();
  $curTZ = date('T',time());
  $Status .= "<!-- server lcl time is: " . date($timeFormat) . " -->\n" .
     "<!-- server GMT time is: " . gmdate($timeFormat) . " -->\n" .
     "<!-- server timezone for this script is: $curTZ -->\n";
//  $Status .= "<!-- TZ Delta = $timediff seconds (" . 
//          $timediff/3600 . " hours) curTZ='$curTZ' -->\n";

  load_config($Config);  // Load the configuration file

// show image of map with hotspots outlined
  if (isset($_REQUEST['show']) && strtolower($_REQUEST['show']) == 'hotspots' ) {
    $ourGraphic = $PHP_SELF . "?show=map";
    $toggleState = "Now showing Hotspots -- <a href=\"$PHP_SELF?show=normal\">click to show normal graphic</a>.</p>\n";
   } else {
    $ourGraphic = $MapImage;  // no outlines for map
    $toggleState = "Now showing normal graphic -- <a href=\"$PHP_SELF?show=hotspots\">click to show hotspots outlined</a>.</p>\n";
   }

  if ($genJPEG) { // just produce the map with hotspots outlined
    outline_hotspots($MapImage);
	exit;
  } 
   	
  $Units = load_units(); // setup display units
  $Status .= "<!-- wind UOM='" . $Units['wind'] . "' -->\n";

  load_strings();        // load the assembly strings for CSS, HTML, Maps
  
// Change cache name to handle both English and Metric caches
  $cacheName = str_replace(".txt","$myUOM.txt",$cacheName);
  $fileName = "http://www.ndbc.noaa.gov/radial_search.php?lat1=$myLat&lon1=$myLong&uom=$myUOM&dist=$maxDistance&ot=A&time=2";

// refresh cached copy of page if needed
// fetch/cache code by Tom at carterlake.org

if (file_exists($cacheName) and filemtime($cacheName) + $refetchSeconds > time()) {
      $Status .= "<!-- using Cached version from $cacheName -->\n";
      $html = implode('', file($cacheName));
	  $Status .= "<!-- cache last updated " . date($timeFormat,filemtime($cacheName)) . 
	   " size=" . strlen($html) . " bytes -->\n";
    } else {
      $Status .= "<!-- loading $cacheName from URL\n $fileName\n -->\n";
      $html = fetchURLWithoutHangingBUOY($fileName);
	  $Status .= "<!-- returned " . strlen($html) . " bytes -->\n";
	  if(strlen($html) < 10) {
		  $Status .= "<!-- Note: fetch of data from website unsuccessful. See above for error. -->\n";
	  } else {
		$fp = fopen($cacheName, "w");
		if ($fp) {
		  $write = fputs($fp, $html);
		  $Status .= "<!-- wrote " . strlen($html) . " bytes to $cacheName. -->\n";
		  fclose($fp); 
		} else {
		  $Status .= "<!-- unable to open $cacheName for writing. -->\n";
		} 
		$Status .= "<!-- loading finished. -->\n";
	  }
	}

// parse and handle the returned buoy page from www.ndbc.noaa.gov
// extract buoy data lines from page
 $buoyrawdata = preg_replace('|\r|Uis',"\n",$html);  // V1.06 - IIS fix
 preg_match_all('|<p class="red"><strong>(.*)</pre>|si',$html,$betweenspan);
 
 //$Status .= "<!-- betweenspan \n" . print_r($betweenspan,true) . " -->\n";
 
 $buoyrawdata = $betweenspan[1][0];  
 $Status .= "<!-- data size " . strlen($buoyrawdata) . " -->\n"; 
 $buoydata = explode("\n",$buoyrawdata); // get lines of html to process
 $Status .= "<!-- linecount " . count($buoydata) . " -->\n";

 // things to clean out of the buoy table row
 $removestr = array (
      "'<span[^>]+>|</span>|<a[^>]+>|</a>'si"
	  );

 // examine, process and format each line of the buoy data 
 $buoysFound = 0; 
 $seenBuoy = array();   // storage area for buoy data lines
 
 foreach ($buoydata as $key => $buoy) { // read each text line of buoy data
 
   if (preg_match("|\d+ observations from|i",$buoy) ) {
     // found the from - to timestamp line
     list($nObs,$firstDate,$firstTime,$lastDate,$lastTime) = getTimeRange($buoy);
	  $Status .= "<!-- $nObs observations from '$firstDate' '$firstTime' GMT to '$lastDate' '$lastTime' GMT -->\n";
   }

   if (preg_match("|^<span style=|i",$buoy))  // keep only the buoy data ) 
	{
    // clear out NDBC formatting of <strong> and <font>
    $buoy = preg_replace($removestr,"",$buoy);
    $buoy = preg_replace("/\s\-\s/si",' n/a ',$buoy); // change '-' to 'n/a'
	list($ID,$TYPE,$TIME,$LAT,$LON,$DIST,$HDG,$WDIR,$WSPD,$GST,$WVHT,$DPD,$APD,$MWD,$PRES,$PTDY,$ATMP,$WTMP,$DEWP,$VIS,$TCC,$TIDE,$S1HT,$S1PD,$S1DIR,$S2HT,$S2PD,$S2DIR,$Ice,$Sea) = preg_split("/[\s]+/",$buoy); //all the data for one buoy

// save only the first-seen entry.. it's the most recent one
	
    	if (! isset($seenBuoy["$ID"]) && isset($Buoys["$ID"]) ) {
	      $seenBuoy["$ID"] = $buoy;   // save off record we want for later
          $buoysFound++;    // keep a tally of quakes for summary

        } // end $seenBuoy
//	     else { print "<!-- buoy $ID time=$TIME skipped as older -->\n"; }
	 } // end skip non-data
  } // end foreach loop


// now generate the data table by looking at our buoys
 $doneHeader = 0;  // flag to determine when to do the header

 foreach ($Buoys as $key => $buoy) { // loop over buoys in config file

   if (! $doneHeader) {  // print the header if needed
     prt_tablehead();
     $doneHeader = 1;
   } // end doneHeader
	  
   prt_tabledata($key);
} // all table data is now in $table

// Write trailer info
 
	  if ($doneHeader) {
// --------------- customize HTML if you like -----------------------
	     $table .= "</table>\n";
	  
	  } else {
// --------------- customize HTML if you like -----------------------
	    $table .= "<p>No buoys within $maxDistance $distUnits of $myLat $myLong found.</p>\n";
	  
	  }	 

// Now generate the mesomap 

// top boilerplate for include text
  $htmlstart = '<div id="mesobuoy">
  <img src="';  // leave room for toggling the image here

  $htmlnext ='" usemap="#meso" 
    alt="Mesomap of nearby weather buoys" style="border: none"/>
';

// generate the MAP list sorted by State, Station name
  $html = '<p><map name="meso" id="meso">' . "\n";


// generate the map hotspots and links
  reset($Buoys);
  foreach ($Buoys as $key => $val) { 
    list($BuoyName,$Coords) = explode("\t",$Buoys["$key"]);
	$BuoyURL = $NDBCURL . "/station_page.php?station=$key&amp;unit=$myUOM";
    $tag = gen_tag($key);
	
	$html .= "	  <area shape=\"rect\" coords=\"$Coords\" href=\"$BuoyURL\" 
		title=\"$tag\" 
		  alt=\"$tag\" />\n";
  }

// finish up the CSS/HTML assembly strings

$html .= "</map></p>\n";

$CSS .= "</style>\n<!-- end buoy-data CSS -->\n";

$scroller .= "</div>\n";

$table .= "<!-- end of included buoy-data text -->\n";
// ------------------------------------------------------------------
// now that all the HTML is ready, print it (or the CSS)

if (! $doPrintBUOY) { // no printing needed.. just return the full variables for printing on the including page
  $BUOY_CSS = $CSS;
  $BUOY_MAP = $Status .
	"<!-- begin included buoy-data text -->\n" .
	"<!-- Generated by $Version -->\n" .
    $htmlstart . $ourGraphic . $htmlnext . $scroller . $html .
	  prt_jscript();  // the JavaScript does the rotation
  $BUOY_TABLE = $table;
  return;
}

  if ($CSSonly) {
    print $CSS;
  } else {
// omit HTML <HEAD>...</HEAD><BODY> if only tables wanted	
// --------------- customize HTML if you like -----------------------
if (! $includeOnly) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Refresh" content="300" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Cache-Control" content="no-cache" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Buoy data within <?php echo "$maxDistance $distUnits"; ?></title>
<?php echo $CSS ?>
</head>
<body style="background-color:#FFFFFF;">
<?php
}
// now send the content we've assembled 
    print $Status;
	print "<!-- begin included buoy-data text -->\n";
	print "<!-- Generated by $Version -->\n";
    if ($doPrintMap) { 
	  print $htmlstart . $ourGraphic . $htmlnext . $scroller . $html;
	  print prt_jscript();  // the JavaScript does the rotation
	}
	if ($doPrintTable) { 
	  print $table; 
	}
  }

// print footer of page if needed    
// --------------- customize HTML if you like -----------------------
if (! $includeOnly ) {   
?>
</body>
</html>
<?php
}
// --------------------- END OF MAIN PROGRAM -------------------------------


// ----------------------------functions ----------------------------------- 
 
 
// get contents from one URL and return as string 
 function fetchUrlWithoutHangingBUOY($url,$useFopen=false) {
// thanks to Tom at Carterlake.org for this script fragment
  global $Status, $TOTALtime;
  $overall_start = time();
  if (! $useFopen) {
   // Set maximum number of seconds (can have floating-point) to wait for feed before displaying page without feed
   $numberOfSeconds=8;   

   // Suppress error reporting so Web site visitors are unaware if the feed fails
   error_reporting(0);

   // Extract resource path and domain from URL ready for fsockopen
   $FullUrl = $url;
   $urlParts = parse_url($url);
   
   $domain = $urlParts['host'];
   if(isset($urlParts['port'])) {
      $port   = $urlParts['port'];
   } else { 
      $port   = 80;
   }
   $resourcePath = $urlParts['path'];
   if(isset($urlParts['query']))    {$resourcePath .= "?" . $urlParts['query']; }
   if(isset($urlParts['fragment'])) {$resourcePath .= "#" . $urlParts['fragment']; }
   $T_start = microtime_float();
   $hostIP = gethostbyname($domain);
   $T_dns = microtime_float();
   $ms_dns  = sprintf("%01.3f",round($T_dns - $T_start,3));
   
   $Status .= "<!-- GET $resourcePath HTTP/1.1 \n      Host: $domain  Port: $port IP=$hostIP-->\n";
//   print "GET $resourcePath HTTP/1.1 \n      Host: $domain  Port: $port IP=$hostIP\n";

   // Establish a connection
   $socketConnection = fsockopen($hostIP, $port, $errno, $errstr, $numberOfSeconds);
   $T_connect = microtime_float();
   $T_puts = 0;
   $T_gets = 0;
   $T_close = 0;
   
   if (!$socketConnection)
       {
       // You may wish to remove the following debugging line on a live Web site
       $Status .= "<!-- Network error: $errstr ($errno) -->\n";
//       print "Network error: $errstr ($errno)\n";
       }    // end if
   else    {
       $xml = '';
	   $getString = "GET $resourcePath HTTP/1.1\r\nHost: $domain\r\nConnection: Close\r\n\r\n";
//	   if (isset($needCookie[$domain])) {
//	     $getString .= $needCookie[$domain] . "\r\n";
//		 print " used '" . $needCookie[$domain] . "' for GET \n";
//	   }
	   
//	   $getString .= "\r\n";
//	   print "Sending:\n$getString\n\n";
       fputs($socketConnection, $getString);
       $T_puts = microtime_float();
	   
       // Loop until end of file
	   $TGETstats = array();
	   $TGETcount = 0;
       while (!feof($socketConnection))
           {
		   $T_getstart = microtime_float();
           $xml .= fgets($socketConnection, 8192);
		   $T_getend = microtime_float();
		   $TGETcount++;
		   $TGETstats[$TGETcount] = sprintf("%01.3f",round($T_getend - $T_getstart,3));
           }    // end while
       $T_gets = microtime_float();
       fclose ($socketConnection);
       $T_close = microtime_float();
       }    // end else
   $ms_connect = sprintf("%01.3f",round($T_connect - $T_dns,3));

   if($T_close > 0) {
      $ms_puts = sprintf("%01.3f",round($T_puts - $T_connect,3));
	  $ms_gets = sprintf("%01.3f",round($T_gets - $T_puts,3));
	  $ms_close = sprintf("%01.3f",round($T_close - $T_gets,3));
	  $ms_total = sprintf("%01.3f",round($T_close - $T_start,3)); 
    } else {
       $ms_puts = 'n/a';
	  $ms_gets = 'n/a';
	  $ms_close = 'n/a';
	  $ms_total = sprintf("%01.3f",round($T_connect - $T_start,3)); 
   }

   $Status .= "<!-- HTTP stats:  dns=$ms_dns conn=$ms_connect put=$ms_puts get($TGETcount blocks)=$ms_gets close=$ms_close total=$ms_total secs -->\n";
//   print  "HTTP stats: dns=$ms_dns conn=$ms_connect put=$ms_puts get($TGETcount blocks)=$ms_gets close=$ms_close total=$ms_total secs \n";
//   foreach ($TGETstats as $block => $mstimes) {
//     print "HTTP Block $block took $mstimes\n";
//   }
   $TOTALtime+= ($T_close - $T_start);
   $overall_end = time();
   $overall_elapsed =   $overall_end - $overall_start;
   $Status .= "<!-- fetch function elapsed= $overall_elapsed secs. -->\n"; 
//   print "fetch function elapsed= $overall_elapsed secs.\n"; 
   return($xml);
 } else {
//   print "<!-- using file function -->\n";
   $T_start = microtime_float();

   $xml = implode('',file($url));
   $T_close = microtime_float();
   $ms_total = sprintf("%01.3f",round($T_close - $T_start,3)); 
   $Status .= "<!-- file() stats: total=$ms_total secs -->\n";
//   print " file() stats: total=$ms_total secs.\n";
   $TOTALtime+= ($T_close - $T_start);
   $overall_end = time();
   $overall_elapsed =   $overall_end - $overall_start;
   $Status .= "<!-- fetch function elapsed= $overall_elapsed secs. -->\n"; 
//   print "fetch function elapsed= $overall_elapsed secs.\n"; 
   return($xml);
 }

   }    // end fetchUrlWithoutHangingBUOY
// ------------------------------------------------------------------

function microtime_float()
{
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}

// ------------------------------------------------------------------

//  convert degrees into wind direction abbreviation   
function getWindDir ($degrees) {
   // figure out a text value for compass direction
   $winddir = $degrees;
   if ($winddir == "n/a") { return($winddir); }
   
   switch (TRUE) {
     case (($winddir >= 349) and ($winddir <= 360)):
       $winddir = 'N';
     break;
     case (($winddir >= 0) and ($winddir <= 11)):
       $winddir = 'N';
     break;
     case (($winddir > 11) and ($winddir <= 34)):
       $winddir = 'NNE';
     break;
     case (($winddir > 34) and ($winddir <= 56)):
       $winddir = 'NE';
     break;
     case (($winddir > 56) and ($winddir <= 78)):
       $winddir = 'ENE';
     break;
     case (($winddir > 78) and ($winddir <= 101)):
       $winddir = 'E';
     break;
     case (($winddir > 101) and ($winddir <= 124)):
       $winddir = 'ESE';
     break;
     case (($winddir > 124) and ($winddir <= 146)):
       $winddir = 'SE';
     break;
     case (($winddir > 146) and ($winddir <= 169)):
       $winddir = 'SSE';
     break;
     case (($winddir > 169) and ($winddir <= 191)):
       $winddir = 'S';
     break;
     case (($winddir > 191) and ($winddir <= 214)):
       $winddir = 'SSW';
     break;
     case (($winddir > 214) and ($winddir <= 236)):
       $winddir = 'SW';
     break;
     case (($winddir > 236) and ($winddir <= 259)):
       $winddir = 'WSW';
     break;
     case (($winddir > 259) and ($winddir <= 281)):
       $winddir = 'W';
     break;
     case (($winddir > 281) and ($winddir <= 304)):
       $winddir = 'WNW';
     break;
     case (($winddir > 304) and ($winddir <= 326)):
       $winddir = 'NW';
     break;
     case (($winddir > 326) and ($winddir <= 349)):
       $winddir = 'NNW';
     break;
   } // end switch

//  return("$winddir ($degrees&deg; true)");
  return("$winddir");

} // end function getWindDir
// ------------------------------------------------------------------

//  produce the table heading row 
function prt_tablehead ( ){

global $updated,$maxDistance,$distUnits, $Units, $table, $scroller,$CSS,$LegendX,$LegendY,$ControlX,$ControlY;
// --------------- customize HTML if you like -----------------------
	    $table .= "
<table border=\"0\" class=\"buoytable\">
<tr>
  <th>ID</th>
  <th>Name</th>
  <th>Time<br/>" . $Units['time'] . "</th>
  <th>Air<br />" . $Units['temp'] . "</th>
  <th>Water<br />" . $Units['temp'] . "</th>
  <th>Wind<br />" . $Units['wind'] . "</th>
  <th>Gust<br />" . $Units['wind'] . "</th>
  <th>Baro<br />" . $Units['baro'] . "</th>
  <th>Trend<br />" . $Units['baro'] . "</th>
  <th>Waves<br />" . $Units['wave'] . "</th>
  <th>Period<br />Sec</th>
  <!-- th>Dist<br />$distUnits</th -->
</tr>
";

/* --------- older style -----------
    $scroller .= "<p id=\"mesolegend\">
  <span class=\"content0\" name=\"content0\">Air Temperature units: <b>" . $Units['temp'] . "</b></span>
 <span class=\"content1\" name=\"content1\">Water Temperature units: <b>" . $Units['temp'] . "</b></span>
  <span class=\"content2\" name=\"content2\">Wind speed units: <b>" . $Units['wind'] . "</b></span>
  <span class=\"content3\" name=\"content3\">Gust speed units: <b>" . $Units['wind'] . "</b></span>
  <span class=\"content4\" name=\"content4\">Barometer units: <b>. $Units['wind'] . "</b></span>
  <span class=\"content5\" name=\"content5\">Barometer trend units: <b>" . $Units['baro'] . "</b></span>
  <span class=\"content6\" name=\"content6\">Wave height units: <b>" . $Units['wave'] . "</b></span>
  <span class=\"content7\" name=\"content7\">Wave Dominant Period units: <b>sec</b></span>
</p>\n";
*/ 
    $scroller .= "<p id=\"mesolegend\">
  <span class=\"content0\">&nbsp;Air Temperature&nbsp;</span>
  <span class=\"content1\">&nbsp;Water Temperature&nbsp;</span>
  <span class=\"content2\">&nbsp;Wind Direction @ Speed&nbsp;</span>
  <span class=\"content3\">&nbsp;Wind Gust Speed&nbsp;</span>
  <span class=\"content4\">&nbsp;Barometer&nbsp;</span>
  <span class=\"content5\">&nbsp;Barometer Trend&nbsp;</span>
  <span class=\"content6\">&nbsp;Wave Height&nbsp;</span>
  <span class=\"content7\">&nbsp;Wave Dominant Period&nbsp;</span>
</p>\n";


  $Top = 5;  // default location for values legend on map
  $Left = 5;
  if ($LegendX) {$Left = $LegendX;}
  if ($LegendY) {$Top = $LegendY;}
 //------------customize this CSS entry for font/color/background for legend 
	$CSS .= "#mesolegend {
      top:  ${Top}px;
      left: ${Left}px;
	  font-size: 10pt;
	  color: #0000FF;
	  background-color: #FFFFFF;
	  padding: 3px 3px;
}
";

// set up the run/pause/step controls
   $scroller .= '<form action=""> 
<p id="BuoyControls">
<input type="button" value="Run" name="run" onclick="set_run(1);" />
<input type="button" value="Pause" name="pause" onclick="set_run(0);" />
<input type="button" value="Step" name="step" onclick="step_content();" />
</p>
</form>
';
// old text-based button controls:
//   $scroller .= '<p id="BuoyControls"> 
//  <a onclick="set_run(1);" style="cursor:pointer;cursor:hand">&nbsp;Run&nbsp;</a>
//  <a onclick="set_run(0);" style="cursor:pointer;cursor:hand">&nbsp;Pause&nbsp;</a>
//  <a onclick="step_content();" style="cursor:pointer;cursor:hand">&nbsp;Step&nbsp;</a>
//  </p>';

  $Top = $Top + 25;  // default start for controls is under legend
  if ($ControlX) {$Left = $ControlX;}
  if ($ControlY) {$Top = $ControlY;}


   $CSS .= "#BuoyControls {
	  top: ${Top}px;
	  left: ${Left}px;
	  font-family: Verdana, Arial, Helvetica, sans-serif; 
	  font-size: 8pt;
	  font-weight: normal;
	  position: relative;
	  display: inline;
	  padding: 0 0;
	  border: none;
	  z-index: 15;
}
#BuoyControls a {
      padding: 3px 3px;
	  background: #666666;
	  color: white;
	  border: 1px solid white;
}
";
return;
}  // end function prt_tablehead
// ------------------------------------------------------------------

// produce one row of buoy data
function prt_tabledata($ID) {

 global $seenBuoy,$Buoys,$Units,$NDBCURL,$myUOM,$table,$scroller,$CSS,$skipNoData,$windArrowDir,$showNoData,$windArrowSize;
;
  if ($skipNoData && ! isset($seenBuoy["$ID"])) { return; }
  
  list($Name,$Coords,$Offsets) = preg_split("/\t/",$Buoys["$ID"]);
  $BuoyURL = $NDBCURL . "/station_page.php?station=$ID&amp;unit=$myUOM";
 
  if (! isset($seenBuoy["$ID"])) {
  
    if ($showNoData) {
      $table .= "
<tr>
  <td><a href=\"$BuoyURL\">$ID</a></td>
  <td>$Name</td>
  <td colspan=\"9\" align=\"left\">No recent reports.</td>
</tr>
 ";
     } // end showNoData
   return;
   }
// got data for one of our buoys.. format the table entry
	list($ID,$TYPE,$TIME,$LAT,$LON,$DIST,$HDG,$WDIR,$WSPD,$GST,$WVHT,$DPD,$APD,$MWD,$PRES,$PTDY,$ATMP,$WTMP,$DEWP,$VIS,$TCC,$TIDE,$S1HT,$S1PD,$S1DIR,$S2HT,$S2PD,$S2DIR,$Ice,$Sea) = preg_split("/[\s]+/",$seenBuoy["$ID"]);

 $TIME = chgTime($TIME);   // go adjust the time if needed
 
 	if ($windArrowSize == 'S') {
	  $windGIF = '-sm.gif';
	  $windSIZE = 'height="9" width="9"';
	} else {
	  $windGIF = '.gif';
	  $windSIZE = 'height="14" width="14"';
	}

 
// --------------- customize HTML if you like -----------------------
	    $table .= "
<tr>
  <td><a href=\"$BuoyURL\">$ID</a></td>
  <td>$Name</td>
  <td>$TIME</td>
  <td align=\"center\">$ATMP</td>
  <td align=\"center\">$WTMP</td>
  <td align=\"right\">"; 
  if ($WDIR == 'n/a') {
    $table .= $WDIR; 
  } else {
    $wda = getWindDir($WDIR);
    $table .=  $wda . " ";
	if ($windArrowDir) {
       $table .= "<img src=\"$windArrowDir$wda.gif\" height=\"14\" width=\"14\" 
	    alt=\"Wind from $wda\" title=\"Wind from $wda\" />";
	}
	$table .= " " .convertWind($WSPD);
  }
  $table .= "</td>
  <td align=\"center\">" . convertWind($GST) . "</td>
  <td align=\"center\">$PRES</td>
  <td align=\"center\">$PTDY</td>
  <td align=\"center\">$WVHT</td>
  <td align=\"center\">$DPD</td>
  <!-- td align=\"right\">$DIST </td -->
</tr>\n";

// generate the data for the changing conditions display 
// NOTE: changes here may break the rotating conditions display..
    $scroller .= "<p id=\"buoy$ID\">
  <span class=\"content0\">$ATMP " . $Units['temp'] . "</span>
  <span class=\"content1\">$WTMP " . $Units['temp'] . "</span>
  <span class=\"content2\">";
  $wda = '';
  if ($WDIR <> 'n/a') {
    $wda = getWindDir($WDIR);
	if ($windArrowDir) {
    	$scroller .= "<img src=\"$windArrowDir${wda}${windGIF}\" $windSIZE  
	    alt=\"Wind from $wda\" title=\"Wind from $wda\" style=\"float: left\"/>";
	}
    $scroller .= getWindDir($WDIR) . " " . 
       convertWind($WSPD) . " " . $Units['wind'] ;
  } else {
    $scroller .= $WDIR;
  }
  $scroller .= "</span>
  <span class=\"content3\">";
  
//  if ($GST <> 'n/a' && $wda && $windArrowDir) { 
//    	$scroller .= "<img src=\"$windArrowDir${wda}${windGIF}\" $windSIZE  
//	    alt=\"Wind from $wda\" title=\"Wind from $wda\" align=\"left\"/>";
//  }
  $scroller .=  convertWind($GST) . " " . $Units['wind'] . "</span>
  <span class=\"content4\">$PRES ". $Units['baro'] . "</span>
  <span class=\"content5\">$PTDY ". $Units['baro'] . "</span>
  <span class=\"content6\">$WVHT " . $Units['wave'] . "</span>
  <span class=\"content7\">${DPD} sec</span>
</p>\n";

// now generate the CSS to place the rotating display over the map
    $Coords = preg_replace("|\s|is","",$Coords);
	$Coords .= ',,,,'; // set null default arguments
	list($Left,$Top,$Right,$Bottom) = explode(",",$Coords);
	$Offsets .= ','; // set null default arguments;
	list($OLeft,$OTop) = explode(",",$Offsets);
//	$Top = $Top-1;      // with the border off
//	$Right = $Right+2;
    if (! $Offsets) {  // use default positioning
      $Bottom = $Bottom - 2;
      $Left = $Left - 5 ;
	} else {  // use relative positioning from bottom/left
	  $Bottom = $Bottom + $OTop;
	  $Left = $Left + $OLeft;
	}

	$CSS .= "#buoy$ID {
      top:  ${Bottom}px;
      left: ${Left}px;
}
";

return;
} // end prt_taabledata
// ------------------------------------------------------------------

// generate the alt=/title= text for area statement tooltip popups

function gen_tag($ID) {
   global $seenBuoy,$Buoys,$Units,$NDBCURL;

	list($Name,$Coords) = preg_split("/\t/",$Buoys["$ID"]);

   if (! isset($seenBuoy["$ID"]) ) {

   return "$Name ($ID) - no recent report available";
   }

	list($ID,$TYPE,$TIME,$LAT,$LON,$DIST,$HDG,$WDIR,$WSPD,$GST,$WVHT,$DPD,$APD,$MWD,$PRES,$PTDY,$ATMP,$WTMP,$DEWP,$VIS,$TCC,$TIDE,$S1HT,$S1PD,$S1DIR,$S2HT,$S2PD,$S2DIR,$Ice,$Sea) = preg_split("/[\s]+/",$seenBuoy["$ID"]);

// --------------- customize HTML if you like -----------------------
// note: only IE supports using new-line and tabs in tooltip displays
//   Firefox, Netscape and Opera all ignore these formatting characters,
//   or display a funky character instead.  That's why the code just below
//   is all commented out.  KTrue 23-Jun-2006
//$tag = "$Name ($ID) at $TIME\n" .
//"Distance:\t$DIST ". $Units['dist'] . "\n" .
//"Wind:\t\t". getWindDir($WDIR) . "\n" .
//"Speed:\t\t$WSPD ". $Units['wind'] . "\n" .
//"Gust:\t\t$GST ". $Units['wind'] . "\n" .
//"Pressure:\t$PRES ". $Units['baro'] . "\n" .
//"Trend:\t\t$PTDY ". $Units['baro'] . "\n" .
//"Temperature:\t$ATMP ". $Units['temp'] . "\n" .
//"Water Temp:\t$WTMP ". $Units['temp'] . "\n" .
//"Wave Height:\t$WVHT ". $Units['wave'] . "\n" .
//"Wave Period:\t$DPD sec" . "\n";

$wind = convertWind($WSPD);
$gust = convertWind($GST);
$tag = "$Name at " . chgTime($TIME) . ": " .
  "Air:$ATMP". $Units['temp'] . ", " .
  "Wtr:$WTMP". $Units['temp'] . ", ";
  if ($WDIR <> 'n/a') {
    $tag .=  
	  "". getWindDir($WDIR) . "@" .
     "$wind". $Units['wind'] . " " .
     "G $gust, ";
  }
  $tag .= 
  "$PRES". $Units['baro'] . ", " .
//"Trend:\t\t$PTDY ". $Units['baro'] . "\n" .
  "Wav:$WVHT". $Units['wave'];

return $tag;
} // end gen_tag
// ------------------------------------------------------------------

// convert GMT to locally selected time if needed
function chgTime($TIME) {

  global $Status,$firstDate,$firstTime,$lastDate,$lastTime;
   
  $thisDate = $firstDate;
  if ($TIME < $firstTime) {
    $thisDate = $lastDate;
  }
//  $Status .= "<!-- chgTime: $TIME $thisDate -->\n";

// assemble date for processing
//    thisDate=mm/dd/yyyy TIME=hhmm
   $d = substr($thisDate,6,4) . '-' .
        substr($thisDate,0,2) . '-' .
		substr($thisDate,3,2) . ' ' .
		substr($TIME,0,2) . ':' .
		substr($TIME,2,2) . ':00 GMT';
   $t = strtotime($d);
   $TIME = date('Hi',$t);
//   $Status .= "<!-- d='$d' TIME='$TIME' -->\n";

  return $TIME;
}
// ------------------------------------------------------------------
// change wind units if necessary
function convertWind($wind) {
  global $myUOM,$doWindConvert,$showKnots;
  
  if ($wind == 'n/a') {
    return $wind;
  }
  
  if ($showKnots and $myUOM == 'E') { return $wind; } // already in knots
  
  if ($showKnots and $myUOM == 'M') {
    // convert m/s to knots
	return round($wind * 1.94384449,1);
  }
  
  if (! $doWindConvert) { 
    return $wind; 
  }
  
  if ($myUOM == 'E') {
    return round($wind*1.150779,0);  // knots rounded to integer MPH
  } else {
    return round($wind*3.6,0);      // m/s rounded to integer kph
  }

} // end convertWind
// ------------------------------------------------------------------

// load configuration file from disk
 function load_config($Config) {
 
      global $MapImage,$myLat,$myLong,$Buoys,$ImageH,$ImageW,$LegendX,$LegendY,$maxDistance,$Status,$doListConfig,$ControlX,$ControlY;
      $rawconfig = file($Config); // read file into array
      $myStatus = "<!-- loading config from '$Config' -->\n";
	  // strip comment records, build $Stations indexed array
	  $nrec = 0;
      foreach ($rawconfig as $rec) {
	    $rec = preg_replace("|\n|","",$rec);
	    $len = strlen($rec);
	    if($rec and substr($rec,0,1) <> "#") {  //only take non-comments
//	 	   echo "Rec $nrec ($len): $rec\n";
           $rec .='||||||||'; // null defaults for missing arguments
		   list($BuoyID,$BuoyName,$Coords,$Offsets,$COffsets) = explode("|",$rec);
		   if ($BuoyID == 'MAPIMAGE') {
		     $MapImage = trim($BuoyName);
			 $Coords = preg_replace("|\s|is","",$Coords);
			 list($ImageH,$ImageW) = explode(",",$Coords);
			 $Offsets = preg_replace("|\s|is","",$Offsets);
			 $Offsets .= ','; // null defaults for missing arguments
			 list($LegendX,$LegendY) = explode(",",$Offsets);
			 $COffsets .= ','; // null defaults for missing arguments
			 list($ControlX,$ControlY) = explode(",",$COffsets);
    	     $myStatus .= "<!-- image='$MapImage' w='$ImageW' h='$ImageH' LX='$LegendX' LY='$LegendY' CX='$ControlX' CY='$ControlY' -->\n";
		   } elseif($BuoyID == 'LOCATION') {
		     $myLat = $BuoyName;
			 $myLong = $Coords;
			 if ($Offsets) {$maxDistance = $Offsets;}
			 
		     $myStatus .= "<!-- location myLat='$myLat' myLong='$myLong' maxDist='$maxDistance' -->\n";
		   } else {
			 $Coords = preg_replace("|\s|is","",$Coords);
		     list($Left,$Top,$Right,$Bottom) = explode(",",$Coords);
			 if ($Bottom) { // look like a coord set?
	  	     $Buoys["$BuoyID"] = "$BuoyName\t$Coords\t$Offsets";  // prepare for sort
             $myStatus .= "<!-- buoy='$BuoyID' name='$BuoyName' coord='$Coords' offsets='$Offsets' -->\n";
			 }
		   }
		   
		} elseif (strlen($rec) > 0) {
//		   echo "comment $nrec ($len): $rec\n";
		} else {
//		   echo "blank record ignored\n";
		}
	    $nrec++;
	  }
   if($doListConfig) { // return debugging status for later printing
      $Status .= $myStatus;
	}

} // end function load_config
// ------------------------------------------------------------------

//  select units to display based on units of measure (UOM)
function load_units() {
global $myUOM,$doWindConvert,$showKnots,$Status;

  $Status .= "<!-- wind: myUOM='$myUOM' doWindConvert='$doWindConvert' showKnots='$showKnots' -->\n";


$Units = array();
if ($myUOM == 'M') {
    $Units =  array(  // metric with native wind units
    'wind' => 'm/s',
	'temp' => '&deg;C',
	'baro' => 'hPa',
	'wave' => 'm',
	'dist' => 'km');
  } else {
    $Units =  array(  // english with native wind units
    'wind' => 'kts',
	'temp' => '&deg;F',
	'baro' => 'in',
	'wave' => 'ft',
	'dist' => 'nm');
}

if ($doWindConvert) {  // change units for wind conversions
  if ($myUOM == 'M') {
    $Units['wind'] = 'kph';
   } else {
    $Units['wind'] = 'mph';
   }
 } 
 
if ($showKnots) { $Units['wind'] = 'kts'; }

 $Units['time'] = date('T',time());
 
// $Status .= "<!-- Units\n" . print_r($Units,true) . " -->\n"; 
 
 return $Units;
} // end load_units
// ------------------------------------------------------------------


// get first/last date/time for observations from 'observations' 
//  line in website
function getTimeRange($text) {

     $t = preg_match_all("|(\d+) observations from (\S+) (\S+) GMT to (\S+) (\S+) GMT|Usi",$text,$dates);
//	 print "<!-- \n";
//	 print_r ($dates);
//	 print "-->\n";
     $rtn[0] = $dates[1][0];
	 $rtn[1] = $dates[2][0];
	 $rtn[2]  = $dates[3][0];
	 $rtn[3]  = $dates[4][0];
	 $rtn[4]  = $dates[5][0];
  
return $rtn; // list($firstDate,$firstTime,$lastDate,$lastTime) in $rtn

} // end getTimeRange
// ------------------------------------------------------------------

// print the rotation JavaScript to browser page
function prt_jscript () {
// NOTE: the following is not PHP, it's JavaScript
//   no changes should be required here.
$t = '
<script type="text/javascript">
<!-- 
var delay=3000;
var ie4=document.all;
var browser = navigator.appName;
var ie8 = false;
if (ie4 && /MSIE (\d+\.\d+);/.test(navigator.userAgent)){ //test for MSIE x.x;
 var ieversion=new Number(RegExp.$1) // capture x.x portion and store as a number
 if (ieversion>=8) {
   ie4=false;
   ie8=true;
 }
}
var curindex = 0;
var totalcontent = 0;
var runrotation = 1;
var browser = navigator.appName;

function get_content_tags ( tag ) {
// search all the span tags and return the list with class=tag 
//
  if (ie4 && browser != "Opera" && ! ie8) {
    var elem = document.body.getElementsByTagName(\'span\');
	var lookfor = \'className\';
  } else {
    var elem = document.getElementsByTagName(\'span\');
	var lookfor = \'class\';
  }
     var arr = new Array();
     for(i = 0,iarr = 0; i < elem.length; i++) {
          att = elem[i].getAttribute(lookfor);
          if(att == tag) {
               arr[iarr] = elem[i];
               iarr++;
          }
     }

     return arr;
}


function get_total() {
  if (ie4) {
//    while (eval("document.all.content"+totalcontent)) {
//      totalcontent++;
	  totalcontent = 8;
//	}
  } else{
//    while (var elements = document.getElementsByName("content"+totalcontent)) {
//      var nelements = elements.length;
//	  alert("content"+totalcontent + " length=" +nelements);
	  totalcontent = 8;
//	}
  }
}

function contract_all() {
  for (var y=0;y<totalcontent;y++) {
      var elements = get_content_tags("content"+y);
	  var numelements = elements.length;
//	  alert("contract_all: content"+y+" numelements="+numelements);
	  for (var index=0;index!=numelements;index++) {
         var element = elements[index];
		 element.style.display="none";
      }
  }
}

function expand_one(which) {
  contract_all();
  var elements = get_content_tags("content"+which);
  var numelements = elements.length;
  for (var index=0;index!=numelements;index++) {
     var element = elements[index];
	 element.style.display="inline";
  }
}
function step_content() {
  get_total();
  contract_all();
  curindex=(curindex<totalcontent-1)? curindex+1: 0;
  expand_one(curindex);
}
function set_run(val) {
  runrotation = val;
  rotate_content();
}
function rotate_content() {
  if (runrotation) {
    get_total();
    contract_all();
    expand_one(curindex);
    curindex=(curindex<totalcontent-1)? curindex+1: 0;
    setTimeout("rotate_content()",delay);
  }
}

rotate_content();
// -->
</script>
';
 return($t);
           // That's the end of the JavaScript, now back to PHP
}  // end prt_jscript
// ------------------------------------------------------------------

// initalize the assembly string for CSS
function load_strings () {

  global $CSS,$MapImage;
  
// top of CSS for mesomap display
  $CSS ='<!-- begin buoy-data CSS -->
<style type="text/css">
#mesobuoy {
      background: url(' . $MapImage . ') no-repeat;
      font-family: Tahoma,Arial,sans-serif;
	  font-size: 8pt;
      color: #000088;
      position: relative;
}
#mesobuoy p {
      position: absolute;
	  margin: 0 0 0 0;
	  padding: 0 0 0 0;
}
#mesobuoy p img {
      border-style: none;
}
#mesobuoy img {
      border-style: none;
}
.buoytable {
	font-family: Verdana,Arial,sans-serif;
	font-size: 10pt;
	color: #000000;

}
.content0 {
	display: inline;
}
.content1 {
	display: none;
}
.content2 {
	display: none;
}
.content3 {
	display: none;
}
.content4 {
	display: none;
}
.content5 {
	display: none;
}
.content6 {
	display: none;
}
.content7 {
	display: none;
}
'; 

} // end load_strings
// ------------------------------------------------------------------

//To calculate the delta between the local time and UTC:
function tzdelta ( $iTime = 0 )
{
   if ( 0 == $iTime ) { $iTime = time(); }
   $ar = localtime ( $iTime );
   $ar[5] += 1900; $ar[4]++;
   $iTztime = gmmktime ( $ar[2], $ar[1], $ar[0],
       $ar[4], $ar[3], $ar[5], $ar[8] );
   return ( $iTztime - $iTime );
} // end tzdelta
// ------------------------------------------------------------------

function outline_hotspots ($Graphic) {

  global $Buoys;
    $image = loadJPEG($Graphic);  // fetch our map image
    $color = imagecolorallocate($image, 0, 255, 0);
	$MaxX = imagesx($image);
	$MaxY = imagesy($image);

    while (list($key, $val) = each($Buoys)) { //write each hotspot
	  list($BuoyName,$Coords,$Offsets) = explode("\t",$Buoys["$key"]);
      list($X1,$Y1,$X2,$Y2) = explode(",",$Coords);
      // make sure images in top-left, bottom-right order
	  if($X1 > $X2) { $tmp = $X2; $X2 = $X1; $X1 = $tmp; }
      if($Y1 > $Y2) { $tmp = $Y2; $Y2 = $Y1; $Y1 = $tmp; }
	  imagerectangle($image, $X1, $Y1, $X2, $Y2, $color);
    }
//	imagerectangle($image,5,5,20,15,$color);  // write legend
	$msg = "Hotspots outlined.";
    imagestring($image, 3, 5, 45, $msg, $color); 

	header("Content-type: image/jpeg"); // now send to browser
    imagejpeg($image); 
    imagedestroy($image); 
  } // end outline_hotspots
// ------------------------------------------------------------------

// load JPG image for hotspot work
function loadJPEG ($imgname) { 
   $im = @imagecreatefromjpeg ($imgname); /* Attempt to open */ 
   if (!$im) { /* See if it failed */ 
       $im  = imagecreate (150, 30); /* Create a blank image */ 
       $bgc = imagecolorallocate ($im, 255, 255, 255); 
       $tc  = imagecolorallocate ($im, 0, 0, 0); 
       imagefilledrectangle ($im, 0, 0, 150, 30, $bgc); 
       /* Output an errmsg */ 
       imagestring ($im, 1, 5, 5, "Error loading $imgname", $tc); 
   } 
   return $im; 
} // end loadJPEG
// ------------------------------------------------------------------


// --------------end of functions ---------------------------------------

?>