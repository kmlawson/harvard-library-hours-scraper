<?php

/*

Custom Harvard Library Hours 2.0

This script scrapes the library hours home page at harvard:
http://lib.harvard.edu/libraries/hours.html

It extracts the hours for certain libraries and shows whether
they are currently open. It allows users to build their own
custom link to show only the libraries they are interested in.

Copyright 2012: Konrad M. Lawson http://muninn.net
License: GNU General Public License Version 3.0
A copy of the license should be found with the code.

*/


date_default_timezone_set("America/New_York");
$url="http://lib.harvard.edu/libraries/hours.html";
$page=file_get_contents($url);

function findtimes($libraryname) {
	global $page;
	$libraryname=str_replace("(","\(",$libraryname);
	$libraryname=str_replace(")","\)",$libraryname);
	$librarylook="/<A HREF=[0-9]+.html>".$libraryname."<\/A><\/STRONG><\/TD><\/TR>
<TR><TD><\/TD><TD BGCOLOR='DDDDDD' ALIGN=CENTER>([^<]*)<\/TD><TD BGCOLOR='DDDDDD' ALIGN=CENTER>([^<]*)<\/TD>/";
	preg_match_all($librarylook,$page,$libraryarray);
	return $libraryarray;
}

function is_odd( $int )
{
  return( $int & 1 );
}

function opennow($time) {
	if (strrpos($time,"closed")) {
		return false;
	}
	
	$time=str_replace(" noon", "12pm", $time);
	$timearray=explode(" - ", $time);
	$start=$timearray[0];
	$end=$timearray[1];
	$start=str_replace(" midnight", "am", $start);
	$end=str_replace("12 midnight", "11:59pm", $end);
	// Get timestamp of starting time
	$fullstart=strtotime(date("F j, Y ").$start);
	// Get timestamp of ending time
	$fullend=strtotime(date("F j, Y ").$end);
	// Check if the current time is later than opening but earlier than closing time:
	if (time()>$fullstart && time()<$fullend) {
		return true;
	} else {
		return false;	
	}
}

// LIBRARY LIST:

$lib['b']="The Afro-American Studies Reading Room";
$lib['a']="Andover-Harvard Theological Library";
$lib['d']="Arnold Arboretum Horticultural Library (Jamaica Plain)";
$lib['s']="Arthur and Elizabeth Schlesinger Library on the History of Women in America";
$lib['e']="Biblioteca Berenson";
$lib['h']="Blue Hill Meteorological Observatory Library";
$lib['c']="Cabot Science Library";
$lib['i']="Center for Hellenic Studies Library";
$lib['j']="Chemistry and Chemical Biology Library";
$lib['k']="Child Memorial Library";
$lib['n']="Countway Library of Medicine";
$lib['o']="Dumbarton Oaks Research Library";
$lib['p']="Ernst Mayr Library of the Museum of Comparative Zoology";
$lib['q']="Fine Arts Library (Littauer and Sackler Locations)";
$lib['f']="Fung Library";
$lib['r']="George David Birkhoff Mathematical Library";
$lib['u']="George Edward Woodberry Poetry Room";
$lib['v']="Gibb Islamic Seminar Library";
$lib['z']="Gordon McKay Library of Engineering and Applied Sciences";
$lib['g']="The Grossman Library and Resource Center for the Harvard Extension School";
$lib['A']="Gutman Library";
$lib['B']="Harvard Film Archive";
$lib['C']="Harvard Forest Library";
$lib['x']="Harvard Law School Library";
$lib['E']="Harvard Map Collection";
$lib['F']="Harvard MIT-Data Center";
$lib['G']="Harvard Theatre Collection, Houghton Library";
$lib['H']="Harvard University Development Office Library";
$lib['y']="Harvard-Yenching Library";
$lib['J']="History Departmental Library";
$lib['h']="Houghton Library";
$lib['l']="Lamont Library";
$lib['K']="Loeb Music Library";
$lib['L']="Nieman Foundation Bill Kovach Collection of Contemporary Journalism";
$lib['M']="Office of Career Services Library";
$lib['N']="Physics Research Library";
$lib['O']="Property Information Resource Center";
$lib['P']="Robbins Library of Philosophy";
$lib['Q']="Statistics Library";
$lib['t']="Tozzer Library";
$lib['R']="Ukrainian Research Institute Reference Library";
$lib['S']="Weissman Preservation Center Library";
$lib['w']="Widener Library";
$lib['T']="John G. Wolbach Library";

if(empty($_GET)==FALSE) {
    $myletters=array_keys($_GET);
    $myletters=$myletters[0];
    // Look for non-alphabetic characters and return error if they are found
    $badletters="/[^a-zA-Z]/";
    if (preg_match($badletters,$myletters)) {
    	$error="The request contained characters that are not understood.";
    } 
 }

?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta name="viewport" content="width=320, initial-scale=1.0, user-scalable=no" />
	
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>Harvard Library Hours</title>
	<style type="text/css">
	<!--
	.all {
		font-family: Arial, Helvetica, sans-serif;
		font-size: medium;
	}
	.green {
		font-family: Arial, Helvetica, sans-serif;
		font-size: medium;
		background-color: #BAF7B5;
	}
	.greengrey {
		font-family: Arial, Helvetica, sans-serif;
		font-size: medium;
		background-color: #BAF7B5;
	}
	.style1 {font-family: Arial, Helvetica, sans-serif;
	font-size: medium; font-weight: bold; }
	.style2 {font-size: small}
	.grey {
		background-color: #CCCCCC; 
	}
	.red {
		font-family: Arial, Helvetica, sans-serif;
		font-size: medium;
		background-color: #FAA0A0;
	}
	.redgrey {
		font-family: Arial, Helvetica, sans-serif;
		font-size: medium;
		background-color: #FAA0A0;
	}
	a:link {
		color: #333333;
	}
	.style2 {font-size: small}
	a:visited {
		color: #333333;
	}
	a:hover {
		color: #333333;
	}
	a:active {
		color: #333333;
	}
	-->
	</style>
	</head>

	<body>
	<table align="center" width="100%" border="0" cellpadding="5" cellspacing="0">
	  <tr>
	    <td class="all">&nbsp;</td>
	    <td class="all"><strong>Today</strong></td>
	    <td class="all"><strong>Tomorrow</strong></td>
	  </tr>
	  
	  <?php
	  
	  // Default to show Widener, Law library, Lamont, Yenching, and Fung
	  if(empty($myletters)) { 
	  	$myletters="wxlyf";  
	  }
	  
	 for($i=0;$i<strlen($myletters);$i++) {
	      $lhours=findtimes($lib[$myletters[$i]]);
	      //print_r($lhours)
	   	  
	   	  if(is_odd($i)){ 
	   	  ?>
	   	  <tr> 
	   	  <?php } else { ?>
	   	  <tr class="grey"> <?php } ?>
	   	  <td class="style1"><?php echo str_replace("Library", "", $lib[$myletters[$i]]); ?></td>
	   	  <?php if (opennow($lhours[1][0])) { if(is_odd($i)) { ?>
	   	  <td class="green"> <?php } else { ?>
	   	  <td class="greengrey">
	   	  <?php } } else { if(is_odd($i)) { ?>
	   	  <td class="red"> <?php } else { ?>
	   	  <td class="redgrey">
	   	  <?php } }
	   	  echo $lhours[1][0]; ?></td>
	   	  <td class="all"><?php echo $lhours[2][0]; ?></td>
	   	  <?php
	   }
	  
?>
	</table>
	<p align="center" class="all">
	<a href="about.html" class="style2">About</a> |
	<a href="http://lib.harvard.edu/libraries/hours.html" class="style2">Full Listing</a>
	 | <a class="style2" href="http://huginn.net/h/build.html">Customize List</a>
	</p>
	
	</body>
	</html>
