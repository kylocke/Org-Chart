<?php

//Configuration Details
$config = array (
 'host'             => 'host.yoursite.edu',
 'dn'               => 'DC=host,DC=yoursite,DC=edu',
 'username'         => 'lookupuser',
 'password'         => 'lookuppassword',);
 
$ad = ldap_connect($config['host']) or die( "Could not connect!" );
// Set version number
ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3) or die ("Could not set ldap protocol");
ldap_set_option($ad, LDAP_OPT_REFERRALS,0) or die ("could no se the ldap referrals");
// Binding to ldap server
$bd = ldap_bind($ad, $config['username'], $config['password']) or die ("Could not bind");

//Fields to Return for the information
$return_fields= array (
 'Last Name'        => 'sn',
 'First Name'       => 'givenname',
 'Telephone Number' => 'telephonenumber',
 'Email Address'    => 'mail',
 'Department'       => 'department',
 'Reports To'       => 'manager',
 'Username'         => 'samaccountname',
 'Title'            => 'title',
 'PO Box'           => 'postOfficeBox',
 'Location'         => 'physicalDeliveryOfficeName',
 'Direct Reports'   => 'directReports',
 'DN'               => 'distinguishedName');

$attrs=array();
foreach ($return_fields as $item => $value) { //build attr array
  $attrs[]=$value;
}

//Fields to search
$search_fields= array (
 'First Name'       => 'givenname',
 'Last Name'        => 'sn',
 'Title'            => 'title',
 'Department'       => 'department',);

$search_bar_fields= array (
 'First Name'       => 'givenname',
 'Last Name'        => 'sn',
 'Title'            => 'title',);
 
$home_user = "acollins"; //home_user stores the top level person in the organizational chart
$dr_col_count = 3; //Number of direct reports per row
$image_path = "http://www.clarkson.edu/files/images/view_image.php?image_id="; //Image path
$image_extension = ""; //Image extensions
?>