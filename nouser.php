<?php
  
$ldap_search='';
if ($_SERVER['REQUEST_METHOD'] == "POST") {
  foreach ($search_fields as $item => $value) { //build filter
    if (empty($_POST[$value])) {
	  $filter[$value]="($value=*)";
	} 
	else {
	  $filter[$value]="($value=*" . rtrim(ltrim(stripslashes($_POST[$value]))) . "*)";
	}
	$ldap_search=$ldap_search.$filter[$value];
  }
 
  $ldap_search='(&'.$and.$ldap_search.'(department=*)(!(|(company=Other)(company=Student))))';
  $search = ldap_search($ad, $config['dn'], $ldap_search, $attrs) or die ("ldap search failed");

  $entries = ldap_get_entries($ad, $search);
  if ($entries["count"] > 0) {
    $row_count = 0;
	$sort_count=0;
	for ($i=0; $i<$entries["count"]; $i++) {
	  if ((strpos($entries[$i]["dn"], "OU=Staff") == true || strpos($entries[$i]["dn"], "OU=Faculty") == true) && $entries[$i]["department"][0] != "") {
  	    $entries_sort[$sort_count][0] = $entries[$i]["sn"][0];
	    $entries_sort[$sort_count][1] = $entries[$i]["givenname"][0];
	    $entries_sort[$sort_count][2] = $entries[$i]["samaccountname"][0];
        include("../../oit/config/config.php");
        $title_query = odbc_exec($form_dbconn, "SELECT title FROM directory_info WHERE username = '" . $entries[$i]["samaccountname"][0] . "'");
        $entries_sort[$sort_count][3] = odbc_result($title_query, "title");
		$entries_sort[$sort_count][4] = $entries[$i]["department"][0];
		$entries_sort[$sort_count][5] = str_replace("&", "%26", $entries[$i]["department"][0]);
		$sort_count++;
	  }
	}
	if ($sort_count == 1) {
	  header("Location: index.php?username=" . $entries_sort[0][2] . "");
	}
	include ("../../oit/config/template_header_new.php");
    echo "<style>";
    include ("orgchart.css");
    echo "</style>";

	printSearch();
	if ($sort_count > 1) {
	  sort($entries_sort);	
	}
    echo "<p>" . $sort_count . " results returned</p>";
    echo "
	 <table width=\"700px\" border=\"0\">
	  <tr><th>Name</th><th>Title</th><th>Department</th></tr>";
	for ($i=0; $i<$sort_count; $i++) {
	  $row_color = ($row_count % 2) ? $config['color1'] : $config['color2'];
      echo "<tr bgcolor=\"$row_color\" valign=\"top\">";
      echo "<td width=\"200px\"><a href=\"index.php?username=" . $entries_sort[$i][2] . "\">" .$entries_sort[$i][0] . ", " . $entries_sort[$i][1] . "</a></td>
	  <td width=\"300px\">" . $entries_sort[$i][3] . "</td>
	  <td width=\"200px\"><a href=\"index.php?dept=" . $entries_sort[$i][5] . "\">" . $entries_sort[$i][4] . "</a></td>";
      echo "</tr>";
      $row_count++;
	}
    echo "</table>";
  } 
  else {
    include ("../../oit/config/template_header_new.php");
    echo "<style>";
    include ("orgchart.css");
    echo "</style>";

    printSearch();
    echo "<p>No results found!</p>";
  }
  ldap_unbind($ad);
}
else {
printSearch();
}
?>
