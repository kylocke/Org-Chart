<?php
//Checks if the site is in Read Only mode, if so, do not print the search. 
if ($read_only == false) {
  printSearch();
}
else {
 echo "<br />";
}

$breadCrumb = "";
//Connection to additional database for information that cannot be stored in Active Directory
include("config/secondary_config.php");

$ldap_search='';

//This will print out information if a position is open and display the correct direct reports to that position.
if (is_numeric($username)) {
  $query = odbc_exec($dbconn, "SELECT DISTINCT OPRID3, DESCR, POSITION_REPORT_TO, DEPT FROM org_chart_open_position WHERE POSITION = '$username'");
  odbc_fetch_row($query, 1);
  $manager = odbc_result($query, "POSITION_REPORT_TO");
  $sub_search = ldap_search($ad, $config['dn'], "(&(samaccountname=$manager))", $attrs) or die ("ldap search failed");
  $sub_entries = ldap_get_entries($ad, $sub_search);
  $entries[0]["manager"][0] = $sub_entries[0]["dn"];
  $entries[0]["samaccountname"][0] = $username;
  $entries[0]["givenname"][0] = "Open";
  $entries[0]["sn"][0] = "Position";
  $entries[0]["title"][0] = odbc_result($query, "DESCR");
  $entries[0]["department"][0] = odbc_result($query, "DEPT");
  for ($i=1; $i<=odbc_num_rows($query); $i++) {
    odbc_fetch_row($query, $i);
	$user = odbc_result($query, "OPRID3");
	$sub_search = ldap_search($ad, $config['dn'], "(&(samaccountname=$user))", $attrs) or die ("ldap search failed");
    $sub_entries = ldap_get_entries($ad, $sub_search);
	$entries[0]["directreports"][$i-1] = $sub_entries[0]["dn"];
  }
  getBreadCrumb($username);
}

else {
  $ldap_search="(&(samaccountname=" . $username . ")(company=*)(!(company=Student)))";
  // Create the DN
  // Specify only those parameters we're interested in displaying
  // Create the filter from the search parameters
  $search = ldap_search($ad, $config['dn'], $ldap_search, $attrs) or die ("ldap search failed");
  $entries = ldap_get_entries($ad, $search);
  getBreadCrumb($entries[0]["distinguishedname"][0]);
  $title_query = odbc_exec($form_dbconn, "SELECT title FROM directory_info WHERE username = '" . $entries[0]["samaccountname"][0] . "'");
  $entries[0]["title"][0] = odbc_result($title_query, "title");
}

if ($breadCrumb != "") {
  echo "<br /><div id=\"bread_crumbs\"><a href=\"index.php\">Home</a> $breadCrumb</div>";
}
  echo "
   <div class=\"person_container\">
    <div class=\"person_image\">";
  $photo = "<img border=\"0\" src=\"$image_path" . $entries[0]["samaccountname"][0] . "$image_extention\" width=\"100px\" alt=\"" . $entries[0]["givenname"][0] . " " . $entries[0]["sn"][0] . "\" title=\"" . $entries[0]["givenname"][0] . " " . $entries[0]["sn"][0] . "\" />";
  if($read_only) {
    echo "$photo";
  }
  else {
    echo "<a href=\"index.php?username=" . $entries[0]["samaccountname"][0] . "\">$photo</a>";
  }
   echo "</div>
   <div class=\"person\">
	<h3>" . $entries[0]["givenname"][0] . " " . $entries[0]["sn"][0] . "</h3>";
  if ($entries[0]["title"][0] != "") {
    echo $entries[0]["title"][0] . "<br />";
  }
  if ($entries[0]["department"][0] != "") {
    if ($read_only) {
	  echo "" . $entries[0]["department"][0] . "<br />";
	}
	else {
	  echo "<a href=\"index.php?dept=" . str_replace("&", "%26", $entries[0]["department"][0]) . "\">" . $entries[0]["department"][0] . "</a><br />";
	}
  }
  if ($entries[0]["samaccountname"][0] != "vprivman") {
	  echo "<a href=\"mailto:" . $entries[0]["mail"][0] . "\">" . $entries[0]["mail"][0] . "</a><br />";
  }
  if ($entries[0]["telephonenumber"][0] != "") {
    echo "" . $entries[0]["telephonenumber"][0] . "<br />";
  }
  if ($entries[0]["physicaldeliveryofficename"][0] != "") {
    echo $entries[0]["physicaldeliveryofficename"][0] . "<br />";
  }
  if ($entries[0]["postofficebox"][0] != "" && $entries[0]["postofficebox"][0] != " ") {
  	if ($entries[0]["postofficebox"][0] == "199 Main Street") {
		echo "Beacon Institute for Rivers and Estuaries<br />" . $entries[0]["postofficebox"][0] . "<br />Beacon, NY 12508";
	}
	else {
    	echo "CU Box " . $entries[0]["postofficebox"][0] . ", Potsdam, NY 13699-" . $entries[0]["postofficebox"][0];
	}
  }
  echo "</div><div class=\"person_info\">";
  if ($username == "$home_user") {
    echo "<img src=\"images/board.jpg\" width=\"290\" title=\"Board of Trustees\" alt=\"Board of Trustees\"><br /><strong><a href=\"https://www.clarkson.edu/about/trustees.html\">Board of Trustees</a></strong>";
  }

//Prints the Report To:
  if ($entries[0]["manager"][0] == "") {
    $query = odbc_exec($dbconn, "SELECT DISTINCT POSITION, DESCR FROM org_chart_open_position WHERE OPRID3 = '$username'");
	if (odbc_num_rows($query) != 0) {
	  $position = odbc_result($query, "POSITION");
	  $descr = odbc_result($query, "DESCR");
	  echo "<br /><strong>Reports To:</strong> <a href=\"index.php?username=" . $position . "\">" . $descr  . " - Open Position</a><br />";
	}
  }
  else {
    $sub_ldap_search='';
    $sub_filter[$value]="(distinguishedName=" . $entries[0]["manager"][0] . ")";
    $sub_ldap_search=$sub_ldap_search.$sub_filter[$value];
    $sub_ldap_search='(&'.$and.$sub_ldap_search.')';
    $sub_search = ldap_search($ad, $config['dn'], $sub_ldap_search, $attrs) or die ("ldap search failed");
    $sub_entries = ldap_get_entries($ad, $sub_search);
    if ($sub_entries["count"] > 0) {  
      echo "<br /><strong>Reports To:</strong> <a href=\"index.php?username=" . $sub_entries[0]["samaccountname"][0] . "\">" . $sub_entries[0]["givenname"][0] . " " . $sub_entries[0]["sn"][0] . "</a><br />";
    }
  }
  //Prints Scope if there is one.
  echo $info["$username"][1];

  //Prints Peers
  if ($entries[0]["manager"][0] == "") {
    $query = odbc_exec($dbconn, "SELECT DISTINCT OPRID3 FROM org_chart_open_position WHERE POSITION = '$position'");
	$array_count = 0;
	for ($i=1; $i<=odbc_num_rows($query); $i++) {
	  odbc_fetch_row($query, $i);
	  $user = odbc_result($query, "OPRID3");
	  if ($user != $username) {
	    $sub_search = ldap_search($ad, $config['dn'], "(&(samaccountname=$user)(!(samaccountname=maymie)))", $attrs) or die ("ldap search failed");
        $sub_entries = ldap_get_entries($ad, $sub_search);
	    $peer_sort[$array_count][0] = $sub_entries[0]["sn"][0];
        $peer_sort[$array_count][1] = $sub_entries[0]["givenname"][0];
	    $peer_sort[$array_count][2] = $sub_entries[0]["samaccountname"][0];
	    $array_count++;
	  }
	}
  }
  else {
    $sub_ldap_search='';
    $sub_filter[$value]="(manager=" . $entries[0]["manager"][0] . ")";
    $sub_ldap_search=$sub_ldap_search.$sub_filter[$value];
    $sub_ldap_search='(&'.$and.$sub_ldap_search.')';
    $sub_search = ldap_search($ad, $config['dn'], $sub_ldap_search, $attrs) or die ("ldap search failed");
    $sub_entries = ldap_get_entries($ad, $sub_search);
    if ($sub_entries["count"] > 0) {
      $count=0;
      $array_count=0;

      while ($sub_entries[$count]["samaccountname"][0] != "") {
        if ($sub_entries[$count]["samaccountname"][0] != $entries[0]["samaccountname"][0]) {
          $peer_sort[$array_count][0] = $sub_entries[$count]["sn"][0];
          $peer_sort[$array_count][1] = $sub_entries[$count]["givenname"][0];
	      $peer_sort[$array_count][2] = $sub_entries[$count]["samaccountname"][0];
	      $array_count++;
        }
	    $count++;
      }
	}
  }
  if ($array_count != 0) {
	echo "<br /><strong>Peers:</strong> ";
    sort($peer_sort);
    for ($i=0; $i<$array_count; $i++) {
      if ($i != 0) {
  	  echo ", ";
  	}
      echo "<a href=\"index.php?username=" . $peer_sort[$i][2] . "\">" . $peer_sort[$i][1] . " " . $peer_sort[$i][0] . "</a>";
    }
  }
    $count=0;
    $array_counter=0;
  //Prints Direct Reports

  $test_data = "";  
  if ($entries[0]["directreports"][$count] != "") {
    while ($entries[0]["directreports"][$count] != "") {
      if (strpos($entries[0]["directreports"][$count], "OU=Disable Accounts") == false) {
  	    $sub_ldap_search='';
        $sub_filter[$value]="(distinguishedName=" . $entries[0]["directreports"][$count] . ")";
        $sub_ldap_search=$sub_ldap_search.$sub_filter[$value];
	    $sub_ldap_search='(&'.$and.$sub_ldap_search.')';
        $sub_search = ldap_search($ad, $config['dn'], $sub_ldap_search, $attrs) or die ("ldap search failed");
        $sub_entries = ldap_get_entries($ad, $sub_search);
		if ($sub_entries[0]["samaccountname"][0] <> "maymie") {
		$sub_title_query = odbc_exec($form_dbconn, "SELECT title FROM directory_info WHERE username = '" . $sub_entries[0]["samaccountname"][0] . "'");
        $sub_entries[0]["title"][0] = odbc_result($sub_title_query, "title");
        $report_sort[$array_counter][0] = $sub_entries[0]["sn"][0];
        $report_sort[$array_counter][1] = $sub_entries[0]["givenname"][0];
        $report_sort[$array_counter][2] = $sub_entries[0]["samaccountname"][0];
        $report_sort[$array_counter][3] = $sub_entries[0]["title"][0];
	    $report_sort[$array_counter][4] = $sub_entries[0]["directreports"][0];
		$array_counter++;
		}
	  }
      $count++;
    }
  }
	$query = odbc_exec($dbconn, "SELECT DISTINCT POSITION, DESCR, COUNT(OPRID3)as COUNT FROM org_chart_open_position WHERE POSITION_REPORT_TO = '$username' GROUP BY POSITION, DESCR");
	if (odbc_num_rows($query) != 0 ) {
	  for ($i=0; $i<odbc_num_rows($query); $i++) {
	    odbc_fetch_row($query, $i);
		
	    $report_sort[$array_counter][0] = "Position";
        $report_sort[$array_counter][1] = "Open";
        $report_sort[$array_counter][2] = odbc_result($query, "POSITION");
        $report_sort[$array_counter][3] = odbc_result($query, "DESCR");
	    $report_sort[$array_counter][4] = odbc_result($query, "COUNT");
		$array_counter++;
	  }
	}
	if (!empty($report_sort)) {
		sort ($report_sort);
	}
    for ($i=0; $i<$array_counter; $i++) {
      if ($i%$dr_col_count == 0 && $i !=0) {
        $test_data .= "<div class=\"clear\"></div>";
      }
	  $test_data .= "<div class=\"direct_report_container\"><div class=\"direct_report_image\">";
	  $photo = "<img border=\"0\" src=\"http://www.clarkson.edu/files/images/view_image.php?image_id=" . $report_sort[$i][2] . "\" width=\"100px\" title=\"" . $report_sort[$i][1] . " " . $report_sort[$i][0] . "\" alt=\"" . $report_sort[$i][1] . " " . $report_sort[$i][0] . "\" />";

	  $test_data .= ($read_only) ? "$photo" : "<a href=\"index.php?username=" . $report_sort[$i][2] . "\">$photo</a>";
	  $test_data .= "</div><div class=\"direct_report\"><div class=\"direct_report_text\">";
	  $test_data .= ($read_only == false) ? "<a href=\"index.php?username=" . $report_sort[$i][2] . "\">" . $report_sort[$i][1] . " " . $report_sort[$i][0] . "</a>" : "<strong><u>" . $report_sort[$i][1] . " " . $report_sort[$i][0] . "</u></strong>";
	  $test_data .= "<br />" . $report_sort[$i][3] . "<br /><br />" . $info[$report_sort[$i][2]][0] . "</div>";
	  $test_data .= ($report_sort[$i][4] != "" && !$read_only) ? "<div class=\"direct_report_arrow\"><a href=\"index.php?username=" . $report_sort[$i][2] . "\"><img src=\"images/nav-down.png\" border=\"0\" alt=\"Direct Reports\" title=\"Has Direct Reports\"></a></div>" : "";
	  $test_data .= "</div></div>";
    }
echo "
  </div>
  <div class=\"clear\"></div>
  </div>
  <div class=\"dr_container\">";
  if ($test_data != "") {
    echo "<div class=\"dr_title\"><h3>Direct Reports</h3></div>$test_data";
  }
  echo "</div><div class=\"clear\"></div></div></div>";
  ldap_unbind($ad);

?>
