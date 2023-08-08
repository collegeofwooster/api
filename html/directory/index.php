<?php

// include the configuration
require( '../config.php' );


// set the cache filenames
$cache_file_json = './cache/directory.json';
$cache_file_html = './cache/directory.html';

// if we have a cache file
if ( file_exists( $cache_file_html ) ) {

    // refresh the cache if the cache file is older than 30 minutes
    if ( filemtime( $cache_file_html ) < ( time() - ( 60 * 60 ) ) || isset( $_REQUEST['fresh'] ) ) {

        // establish database connection
        $dbhandle = odbc_connect("Driver={ODBC Driver 17 for SQL Server};Server=" . _IS_SERVER . ";Database=" . _IS_DB . ";", _IS_USER, _IS_PASS )
            or die("Could not connect to " . _IS_SERVER);

        // the query
        $query = "SELECT NAME, POSITION, EXT, EMAIL, PHONE1, PERSONAL_PRONOUN ";
        $query .= "FROM X_WEB_DIRECTORY_AZ ";
        $query .= "WHERE LOWER(POSITION) NOT LIKE LOWER('%emeritus%') AND LOWER(POSITION) NOT LIKE LOWER('%emerita%') ";
        $query .= "ORDER BY NAME;";


        // begin the results array
        $results_final = array();

        // execute the query
        $result = odbc_exec( $dbhandle, $query );

        // begin the directory table string
        $directory_table = '<table cellpadding=0 cellspacing=0 border=0 class="employee-directory dataTable display">';

        // add a header row to the directory table.
        $directory_table .= '<thead><tr><th>Name</th><th>Title</th><th>Contact Information</th></tr></thead>';

        // begin looping thru the results
        while ( $row = odbc_fetch_array( $result ) ) {

            // separate the position from the office
            $temp = explode( ' (', $row['POSITION'] );

            // put the position back into the original result array.
            $row['POSITION'] = $temp[0];

            // strip the end parenthesis from the office and store it back into the original result array values
            $row['OFFICE'] = str_replace( ')', '', $temp[1] );

            if ( $username == 'sbolton' ){
                $row['EMAIL'] = 'president@wooster.edu';
            }

            // store each record in the final results array.
            $results_final[] = $row;

            // print_r( $row );
            $directory_table .= '<tr><td>' . $row['NAME'] . "</td><td>" . $row['POSITION'] . "</td><td nowrap=\"nowrap\">" . ( !empty( $row['OFFICE'] ) ? $row['OFFICE'] . '<br />' : '' ) . ( !empty( $row['PHONE1'] ) ? $row['PHONE1'] . ( !empty( $ext['EXT'] ) ? ' ext #' . $ext['EXT'] . "<br />" : "" ) : '' ) . "<a href=\"mailto:" . $row['EMAIL'] . "\">" . $row['EMAIL'] . "</a></td></tr>";

        }
        
        // close the table
        $directory_table .= "</table>";

        // store the json results in its own file
        file_put_contents( './cache/directory.json', json_encode( $results_final ) );

        // store the directory table (html) in its own file
        file_put_contents( './cache/directory.html', $directory_table );

        // print the table
        print $directory_table;
        
    } else {

        // display the cached file
        print file_get_contents( $cache_file_html );

    }
}

