<?php 

require( '../config.php' );

// interpret query vars
$name = preg_replace('/[^A-Za-z0-9\-]/', '', $_REQUEST['n']);
$year = preg_replace('/[^0-9]/', '', $_REQUEST['y']);
$title = str_replace('%20', ' ', $_REQUEST['t']);
$title = preg_replace('/[^A-Za-z0-9\-\s\:\,\/]/', '', $title);
$major = str_replace('%26', '&', $_REQUEST['m']);
$major = str_replace('%20', ' ', $major);
$major = preg_replace('/[^A-Za-z0-9\&\s]/', '', $major);
$advisor = preg_replace('/[^A-Za-z0-9\-]/', '', $_REQUEST['a']);


// database connection
$serverName = _IS_SERVER; // Replace with your actual server name or IP address
$connectionOptions = array(
    "Database" => _IS_DB, // Replace with your database name
    "Uid" => _IS_USER, // Replace with your database username
    "PWD" => _IS_PASS, // Replace with your database password
    "Encrypt" => "true",
    "TrustServerCertificate" => "true"
);

// establish database connection
$conn = sqlsrv_connect( $serverName, $connectionOptions );

// if our connection failed, die with error
if ( $conn === false ) {
	die( print_r( sqlsrv_errors(), true ) );
}


// assemble the query
$query = "SELECT STUDENT_FIRST, STUDENT_LAST, IS_TITLE, YEAR, MAJOR_1, MAJOR_2, ADVISOR_FIRST, ADVISOR_LAST ";
$query .= "FROM IS_TITLES ";
$query .= "WHERE LOWER(STUDENT_LAST) LIKE LOWER('%$name%') ";
$query .= "AND LOWER(IS_TITLE) LIKE LOWER('%$title%') ";

// if a year is passed in
if ( $year != '' ) {
	$query .= "AND (LOWER(YEAR) LIKE LOWER('%$year%')) ";
}

// if an advisor is passed in
if ( $advisor != '' ) {
	$query .= "AND LOWER(ADVISOR_LAST) LIKE LOWER('%$advisor%') ";
}

// if a major is passed in
if ( $major != '' ) {
	$query .= "AND (LOWER(MAJOR_1) LIKE LOWER('%$major%') OR LOWER(MAJOR_2) LIKE LOWER('%$major%')) ";
}

// query sorting
$query .= "ORDER BY STUDENT_LAST, STUDENT_FIRST";

// execute the query
$result = sqlsrv_query( $conn, $query );

// start outputting the results table
print "<table id=\"is-table\"><tr><th>Student</th><th>Year</th><th>I.S. Title</th><th nowrap=\"nowrap\">Major 1</th><th nowrap=\"nowrap\">Major 2</th><th>Advisor</th></tr><span id=\"errortext\">";

// if we don't have any results
if ( $result === false ) {

	// show 'no results' message
	print "\n<tr><td colspan=\"6\" valign=\"center\" align=\"center\">No results</td></tr>";

} else {

	// loop through the results
	while ( $row = sqlsrv_fetch_array( $result ) ){

		$first = $row['STUDENT_FIRST'];
		$last = $row['STUDENT_LAST'];
		$name = $first . " " . $last;
		$title = $row['IS_TITLE'];
		$year = $row['YEAR'];
		$major1 = $row['MAJOR_1'];
		$major2 = $row['MAJOR_2'];
		$afirst = $row['ADVISOR_FIRST'];
		$alast = $row['ADVISOR_LAST'];
		$advisor = $afirst . " " . $alast;

		// output the row
		print "\n<tr><td nowrap=\"nowrap\">" . $name . "</td><td>" . $year . "</td><td>" . $title . "</td><td>" . $major1 . "</td><td>" . $major2 . "</td><td nowrap=\"nowrap\">" . $advisor . "</td></tr>";
		
	}

}

print "</span></table>";


// close the connection
sqlsrv_free_stmt( $result );
sqlsrv_close( $conn );

