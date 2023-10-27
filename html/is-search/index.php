<?php 

// ini_set('display_errors', true);
// ini_set('error_reporting', E_ALL ^ E_NOTICE );

require('../config.php');

// variables
$server = _IS_SERVER;
$user = _IS_USER;
$pass = _IS_PASS;
$db = _IS_DB;  
$name = preg_replace('/[^A-Za-z0-9\-]/', '', $_REQUEST['n']);
$year = preg_replace('/[^0-9]/', '', $_REQUEST['y']);
$title = str_replace('%20', ' ', $_REQUEST['t']);
$title = preg_replace('/[^A-Za-z0-9\-\s\:\,\/]/', '', $title);
$major = str_replace('%26', '&', $_REQUEST['m']);
$major = str_replace('%20', ' ', $major);
$major = preg_replace('/[^A-Za-z0-9\&\s]/', '', $major);
$advisor = preg_replace('/[^A-Za-z0-9\-]/', '', $_REQUEST['a']);

//connect

//$dbhandle = odbc_connect("Driver=ODBC Driver 11 for SQL Server;Server=$server;Database=$db;", $user, $pass)
	//or die("Could not connect to $server");

$dbhandle = odbc_connect("Driver={ODBC Driver 17 for SQL Server};Server=" . _IS_SERVER . ";Database=" . _IS_DB . ";", _IS_USER, _IS_PASS )
    or die("Could not connect to " . _IS_SERVER);

//query

$query = "SELECT STUDENT_FIRST, STUDENT_LAST, IS_TITLE, YEAR, MAJOR_1, MAJOR_2, ADVISOR_FIRST, ADVISOR_LAST ";
$query .= "FROM IS_TITLES ";
$query .= "WHERE LOWER(STUDENT_LAST) LIKE LOWER('%$name%') ";
$query .= "AND LOWER(IS_TITLE) LIKE LOWER('%$title%') ";

if($year == ''){
	$query .= "AND (LOWER(YEAR) LIKE LOWER('%$year%') OR YEAR IS NULL) ";
}

else{
	$query .= "AND (LOWER(YEAR) LIKE LOWER('%$year%')) ";
}

if($advisor == ''){
	$query .= "AND (LOWER(ADVISOR_LAST) LIKE LOWER('%$advisor%') OR ADVISOR_LAST IS NULL) ";
}

else{
	$query .= "AND LOWER(ADVISOR_LAST) LIKE LOWER('%$advisor%') ";
}

if($major == ''){
	$query .= "AND (LOWER(MAJOR_1) LIKE LOWER('%$major%') OR LOWER(MAJOR_2) LIKE LOWER('%$major%') OR MAJOR_1 IS NULL) ";
}

else{
	$query .= "AND (LOWER(MAJOR_1) LIKE LOWER('%$major%') OR LOWER(MAJOR_2) LIKE LOWER('%$major%')) ";
}

$query .= "ORDER BY STUDENT_LAST, STUDENT_FIRST";

//execute

$result = odbc_exec($dbhandle, $query);

//display

echo("<table id=\"is-table\"><tr><th>Student</th><th>Year</th><th>I.S. Title</th><th nowrap=\"nowrap\">Major 1</th><th nowrap=\"nowrap\">Major 2</th><th>Advisor</th></tr><span id=\"errortext\">");

if( odbc_num_rows( $result ) == 0 ) {
	echo("\n<tr><td colspan=\"6\" valign=\"center\" align=\"center\">No results</td></tr>");
}

else{
	while(odbc_fetch_row($result)){

		$first = odbc_result($result,'STUDENT_FIRST');
		$last = odbc_result($result,'STUDENT_LAST');
		$name = $first . " " . $last;
		$title = odbc_result($result,'IS_TITLE');
		$year = odbc_result($result,'YEAR');
		$major1 = odbc_result($result,'MAJOR_1');
		$major2 = "none";
		$major2 = odbc_result($result,'MAJOR_2');
		$afirst = odbc_result($result,'ADVISOR_FIRST');
		$alast = odbc_result($result,'ADVISOR_LAST');
		$advisor = $afirst . " " . $alast;

		echo("\n<tr><td nowrap=\"nowrap\">" . $name . "</td><td>" . $year . "</td><td>" . $title . "</td><td>" . $major1 . "</td><td>" . $major2 . "</td><td nowrap=\"nowrap\">" . $advisor . "</td></tr>");
		
	}
}

echo("</span></table>");

//close the connection

odbc_free_result($result);
odbc_close($dbhandle);

