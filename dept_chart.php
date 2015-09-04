<?php
printSearch();
if ($dept != "") {
  echo "<br /><h2>Department: $dept</h2><br />";
}
$reset = false;
$level = "";
$level_count = 0;


$ldap_search = ($dept == "Inst for a Sustainable Environ") ? "(|(department=$dept)(samaccountname=phopke))" : "(&(department=$dept)(company=Clarkson University))";

// Create the DN
// Specify only those parameters we're interested in displaying
// Create the filter from the search parameters

$search = ldap_search($ad, $config['dn'], $ldap_search, $attrs) or die ("ldap search failed");
$entries = ldap_get_entries($ad, $search);
if ($entries["count"] > 0) {
  if ($entries["count"] == 1) {
    echo "<p>There is " . $entries["count"] . " person in this department.</p><br />";
  }
  else {
    echo "<p>There are " . $entries["count"] . " people in this department.</p><br />";
  }
  for ($i=0; $i<$entries["count"]; $i++) {
    $entries_sort[$i][0] = $entries[$i]["sn"][0];
    $entries_sort[$i][1] = $entries[$i]["givenname"][0];
    $entries_sort[$i][2] = $entries[$i]["samaccountname"][0];
    $entries_sort[$i][3] = $entries[$i]["title"][0];
    $entries_sort[$i][4] = $entries[$i]["mail"][0];
    $entries_sort[$i][5] = $entries[$i]["telephonenumber"][0];	
	$entries_sort[$i][6] = $entries[$i]["physicaldeliveryofficename"][0];
  }
  sort($entries_sort);
  echo "<div class=\"dr_container\">";
  for ($i=0; $i<$entries["count"]; $i++) { 
    if ($i%3 == 0 && $i !=0) {
      echo "<div class=\"clear\"></div>";
    }
    echo "<div class=\"direct_report_container\"><div class=\"direct_report_image\">";
    $photo = "<img border=\"0\" src=\"$image_path" . $entries_sort[$i][2] . "$image_extension\" width=\"100px\" title=\"" . $entries_sort[$i][1] . " " . $entries_sort[$i][0] . "\" alt=\"" . $entries_sort[$i][1] . " " . $entries_sort[$i][0] . "\" />";
    echo "<a href=\"index.php?username=" . $entries_sort[$i][2] . "\">$photo</a>";
    echo "</div><div class=\"direct_report\"><div class=\"direct_report_text\">";
    echo "<a href=\"index.php?username=" . $entries_sort[$i][2] . "\">" . $entries_sort[$i][1] . " " . $entries_sort[$i][0] . "</a>";
    echo "<br />" . $entries_sort[$i][3];
	if ($entries_sort[$i][2] != "vprivman") {
    	echo "<br /><a href=\"mailto:" . $entries_sort[$i][4] . "\">" . $entries_sort[$i][4] . "</a>";
	}
    if ($entries_sort[$i][5] != "") {
      echo "<br />" . $entries_sort[$i][5];
    }
    if ($entries_sort[$i][6] != "") {
      echo "<br />" . $entries_sort[$i][6];
    }
    echo "</div></div></div>\n";
  }
  echo "</div>";
}
else {
  echo "<p>No results found!</p>";
}

echo "<div class=\"clear\"></div>";

ldap_unbind($ad);
?>