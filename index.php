<?php
$page_title = "Clarkson University: Organizational Chart";
$page_heading = "Organizational Chart";
$page_breadcrumb = "<li><a href=\"http://www.clarkson.edu/index.html\">Home</a></li>
 <li class=\"last\"><a href=\"http://www.clarkson.edu/directories/\"> Directories</a></li>
 <li class=\"active\"><a href=\"http://www.clarkson.edu/directories/orgchart/\">Organizational Chart</a></li>";
include ("config/secondary_config.php");

$read_only = false;
$username = $_GET['username'];



$dept = rtrim(ltrim($_GET["dept"]));
if ($dept == "") { $dept = rtrim(ltrim($_POST["dept"]));}
$info['kchezum'][0] = "Marketing & Communications,  Long-range planning, Board Relations, Events, and Dining";
$info['kchezum'][1] = "<br /><strong>Scope:</strong> Integrated Marketing, Communication, Long-Range Planning collaboration initiatives and oversight of Board of Trustees Relations, Brand Management, Web Development, Publications, Crisis Communications, Media Relations, Special Projects/Events, External Partnerships<br />";
$info['gmeserve'][0] = "";

$info['bgrant'][0] = "Undergraduate Admission, New Student Financial Aid, Enrollment Initiatives";
$info['bgrant'][1] = "<br /><strong>Scope:</strong> University undergraduate admission recruitment plan; new student financial aid administration;  strategic direction and leadership for enrollment initiatives and activities.<br />";

$info['syianouk'][0] = "Division I and Division III Intercollegiate Athletics, Intramurals, Indoor and Outdoor Recreation.";
$info['syianouk'][1] = "<br /><strong>Scope:</strong> Intercollegiate Athletics Division I and Division III; Intramurals; Indoor and Outdoor Recreation; Fitness Center; Cheel Operations; Student-Athlete Welfare; Sports Marketing and Communication; Athletic Fundraising and Alumni Relations and Community Outreach<br />";

$info['jfish'][0] = "Budgets & Planning, Purchasing & Risk Management, Student Administrative Services, and Financial Aid";
$info['jfish'][1] = "<br /><strong>Scope:</strong> Budgets & Planning, Purchasing & Risk Management, Student Administrative Services, and Financial Aid<br />";

$info['rjohnson'][0] = "Alumni, Donor Relations, Campaign & Development";
$info['rjohnson'][1] = "<br /><strong>Scope:</strong> Alumni, Donor Relations, Campaign & Development<br />";

$info['cthorpe'][0] = "Schools, Institutes & Research Centers, Student Affairs, Library, Information Technology, Government Relations, and Career Center";
$info['cthorpe'][1] = "<br /><strong>Scope:</strong> Schools, Institutes & Research Centers, Student Affairs, Library, Information Technology, Government Relations, and Career Center<br />";

$info['ihazen'][0] = "Construction, Maintenance, Custodial, Buildings & Grounds, and Service Center";
$info['ihazen'][1] = "<br /><strong>Scope:</strong> Construction, Maintenance, Custodial, Buildings & Grounds, and Service Center<br />";

$info['mardito'][0] = "Human Resources, Health & Safety, and Employee Wellness";
$info['mardito'][1] = "<br /><strong>Scope:</strong> Human Resources, Health & Safety, and Employee Wellness<br />";

$info['wjemison'][0] = "additionally serves as Interim Vice Provost for Research reporting to Charles Thorpe, SVP & Provost";
$info['wjemison'][1] = "<br /><strong>Scope:</strong> Additionally serves as Interim Vice Provost for Research reporting to Charles Thorpe, SVP & Provost";

// CONFIGURATION START

if ($dept != "") {
  include ("../../oit/config/template_header_new.php");
  echo "<style>";
  include ("orgchart.css");
  echo "</style>";

  include ("dept_chart.php");
}
else {
  if ($_SERVER['REQUEST_METHOD'] == "POST") {
    include ("nouser.php");  
  }
  else {
    include ("../../oit/config/template_header_new.php");
    echo "<style>";
    include ("orgchart.css");
    echo "</style>";
    $username = ($username == "") ? "$home_user" : $username;
    include ("user.php");
  }
} 
include ("../../oit/config/template_footer_new.php");

function printSearch() {
  global $search_bar_fields;
?>
  <form id="form1" name="form1" method="post" action="index.php">
<?php
  foreach ($search_bar_fields as $key => $value) { 
    echo "<label><b>$key</b> <input name=\"$value\" type=\"text\" id=\"givenname\" size=\"10\" /></label>  ";
  }
  echo "<strong>Dept</strong> <select name=\"dept\"><option value=\"\"></option>";
  include("../../oit/config/config.php");
  $query = odbc_exec($dbconn, "SELECT DISTINCT dept_name FROM org_chart_depts ORDER BY dept_name");
  for ($i=1; $i<=odbc_num_rows($query); $i++) {
  	odbc_fetch_row($query, $i);
	$dept = odbc_result($query, "dept_name");
	echo "<option value=\"$dept\">$dept</option>\n";
  }
  echo "</select>"; 
?>
   <label><input name="Search" type="submit" id="Search" value="Search" /></label>
  </form>
<?php
}

function getBreadCrumb($username) {
  global $breadCrumb, $config, $return_fields, $home_user;
  include("../../oit/config/config.php");
  if ($_GET["username"] != "$home_user" && $_GET["username"] != "") {
    $ldap_search='';
  
    $attrs=array();
    foreach ($return_fields as $item => $value) { //build attr array
      $attrs[]=$value;
    }

    $ad = ldap_connect($config['host']) or die( "Could not connect!" );

    // Set version number
    ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3) or die ("Could not set ldap protocol");
    ldap_set_option($ad, LDAP_OPT_REFERRALS,0) or die ("Could not set the ldap referrals");

    // Binding to ldap server
    $bd = ldap_bind($ad, $config['username'], $config['password']) or die ("Could not bind");

    if (is_numeric($username)) {
	  $query = odbc_exec($dbconn, "SELECT DISTINCT DESCR, POSITION_REPORT_TO FROM org_chart_open_position WHERE POSITION = '$username'");
	  if (odbc_num_rows($query) != 0) {
	    $descr = odbc_result($query, "DESCR");
		$POSITION_REPORT_TO = odbc_result($query, "POSITION_REPORT_TO");
	    $breadCrumb = " &#155; <a href=\"index.php?username=" . $username . "\">" . $descr . " - Open Position</a>$breadCrumb";
        $ldap_search="(&(samaccountname=" . $POSITION_REPORT_TO . "))";
		$search = ldap_search($ad, $config['dn'], $ldap_search, $attrs) or die ("ldap search failed");
		$entries = ldap_get_entries($ad, $search);
		$entries[0]["manager"][0] = $entries[0]["dn"];
	  }
	}
	else {
      $ldap_search="(&(distinguishedName=" . $username . "))";
      // Create the DN
      // Specify only those parameters we're interested in displaying
      // Create the filter from the search parameters
      $search = ldap_search($ad, $config['dn'], $ldap_search, $attrs) or die ("ldap search failed");
      $entries = ldap_get_entries($ad, $search);
	  $breadCrumb = " &#155; <a href=\"index.php?username=" . $entries[0]["samaccountname"][0] . "\">" . $entries[0]["givenname"][0] . " " . $entries[0]["sn"][0] . "</a>$breadCrumb";
	  if ($entries[0]["manager"][0] == "") {
		$query = odbc_exec($dbconn, "SELECT DISTINCT POSITION FROM org_chart_open_position WHERE OPRID3 = '" . $entries[0]["samaccountname"][0] . "'");
		if (odbc_num_rows($query) != 0) {
		  $entries[0]["manager"][0] = odbc_result($query, "POSITION");
		}
	  }
	}
	if ($entries[0]["manager"][0] != "") {
	  getBreadCrumb($entries[0]["manager"][0]) . "$breadCrumb";
	}
  }
}
?>
