<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2013 Aim4me N.V.  (http://www.aim4me.info)        |
// +----------------------------------------------------------------------+
// | This program is free software.  You can redistribute in and/or       |
// | modify it under the terms of the GNU General Public License Version  |
// | 2 as published by the Free Software Foundation.                      |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY, without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program;  If not, write to the Free Software         |
// | Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.            |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlcian@bigfoot.com                   |
// +----------------------------------------------------------------------+
//
  session_start();

  $login_qualify = 'A';
  require_once("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];

  $uid = intval($uid);

  $table_name = trim($HTTP_POST_VARS['table_name']);
  $table_name = str_replace(" ","_",$table_name); // Replace spaces withuderscores in the table name
  $label = trim($HTTP_POST_VARS['label']);
  $raccess = trim($HTTP_POST_VARS['raccess']);
  $waccess = trim($HTTP_POST_VARS['waccess']);
  $seq_no = intval(trim($HTTP_POST_VARS['seq_no']));
  if(isset($HTTP_POST_VARS['size']))
    $size = $HTTP_POST_VARS['size'];
  else
    $size = "";
  if(isset($HTTP_POST_VARS['multi']))
    $multi = $HTTP_POST_VARS['multi'];
  else
    $multi = "";
  if(isset($HTTP_POST_VARS['new']))
    $new = $HTTP_POST_VARS['new'];
  else
    $new = "N";
  if(isset($HTTP_POST_VARS['type']))
    $type = $HTTP_POST_VARS['type'];
  else
    $type = "";
  if(isset($HTTP_POST_VARS['params']))
    $params = $HTTP_POST_VARS['params'];
  else
    $params = "";

  if ($table_name == "")
  {
    echo($dtext['missing_params']);
    echo("<br><a href=manstuddetails.php>" . $dtext['back_studetman'] . "</a>");
    SA_closeDB();
    exit;
  }
  if (($size == "") && ($new == "Y"))
  {
    echo($dtext['missing_params']);
    echo("<br><a href=manstuddetails.php>" . $dtext['back_studetman'] . "</a>");
    SA_closeDB();
    exit;
  }
  if (($type == "") && ($new == "Y"))
  {
    echo($dtext['mising_params']);
    echo("<br><a href=manstuddetails.php>" . $dtext['back_studetman'] . "</a>");
    SA_closeDB();
    exit;
  }
  // Create the new table if required!
  if($new == "Y" && substr($table_name,0,1) != "*")
  {
    $sql_query = "CREATE TABLE `s_" . $table_name . "` (`sid` int(11) NOT NULL, `data` ";
    $sql_query .= "text";
    if($multi == "Y")
      $sql_query .= ", KEY (`sid`) ) engine=MyISAM CHARTSET=utf8;";
    else
      $sql_query .= ", PRIMARY KEY (`sid`) ) engine=MyISAM;";
    $sql_result = mysql_query($sql_query,$userlink);
    if(!$sql_result)
    { // Unable to create the new table!
      echo($dtext['err_creatable']);
			echo("<BR>". mysql_error($userlink));
      echo("<br><a href=manstuddetails.php>" . $dtext['back_studetman'] . "</a>");
      SA_closeDB();
      exit;
    }
  }
  // Maybe we need to create a choice table as well...
  if($type == "choice" && $new=="Y" && substr($params,0,1) == "*")
  { // Yes, need to create table (with if nox exists clause...)
    $stabcq = "CREATE TABLE IF NOT EXISTS ". substr($params,1). " (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,`tekst` text,PRIMARY KEY (`id`),UNIQUE KEY `id` (`id`)) ENGINE=MyISAM CHARACTER SET=utf8";
	mysql_query($stabcq,$userlink);
	if(mysql_error($userlink))
	  echo(mysql_error($userlink));
 }
  // now, it seems we got our params right, first we see if we need to reshuffle the seq_no fields
  $sql_query = "SELECT seq_no FROM student_details WHERE table_name='" . $table_name . "'";
  $sql_result = mysql_query($sql_query,$userlink);
  if((!$sql_result || (mysql_num_rows($sql_result) < 1)) && ($new == "N"))
  { // O O, no result while there should be a table!
    echo($dtext['err_notable']);
    echo("<br><a href=manstuddetails.php>" . $dtext['back_studetman'] . "</a>");
    SA_closeDB();
    exit;
  }
  if($new == 'N')
    $old_seq_no = intval(mysql_result($sql_result,0,'seq_no'));
  else
    $old_seq_no = 9999; // Sure new records started at the end...
  if(($new == "Y") || ($old_seq_no != $seq_no))
  { // need to reshuffle seq_no!
    if($seq_no > $old_seq_no)
    { // reduce seq_nos in special range
      $sql_query = "SELECT table_name,seq_no FROM student_details WHERE seq_no>'" . $old_seq_no . "' AND seq_no<='" .$seq_no . "'";
      $sql_result = mysql_query($sql_query,$userlink);
      for($n=0;$n<mysql_num_rows($sql_result);$n++)
      {
        $upd_query = "UPDATE student_details SET seq_no='" . (@intval(mysql_result($sql_result,$n,'seq_no')) - 1) . "' WHERE table_name='" . @mysql_result($sql_result,$n,'table_name') . "'";
        mysql_query($upd_query,$userlink);
      }
    }
    else
    { // Increase seq_no's in special range
      $sql_query = "SELECT table_name,seq_no FROM student_details WHERE seq_no>='" . $seq_no . "' AND seq_no < '" . $old_seq_no . "'";
      $sql_result = mysql_query($sql_query,$userlink);
      for($n=0;$n<mysql_num_rows($sql_result);$n++)
      {
        //echo("Updating table with seqno " . @mysql_result($sql_result,$n,'seq_no') . "<br>"); //~~~
        $upd_query = "UPDATE student_details SET seq_no='" . (@intval(mysql_result($sql_result,$n,'seq_no')) + 1) . "' WHERE table_name='" . @mysql_result($sql_result,$n,'table_name') . "'";
        mysql_query($upd_query,$userlink);
      }
    }
  }
  // Now it's time to insert or update
  if($new == "Y")
  {
    $sql_query = "INSERT INTO student_details (table_name,label,type,size,multi,fixed,raccess,waccess,seq_no,overview,toverview,initialentry,params) VALUES('";
	if(substr($table_name,0,1) != "*")
	  $sql_query .= "s_";
    $sql_query .= $table_name . "','" . $label . "','" . $type . "','" . $size . "','" . $multi . "','N','" . $raccess . "','" . 
	$waccess . "','" . $seq_no . "',". 
	((isset($HTTP_POST_VARS['overview']) && $multi == 'N') ? "1" : "0"). ",".
	((isset($HTTP_POST_VARS['toverview']) && $multi == 'N') ? "1" : "0"). ",".
	((isset($HTTP_POST_VARS['initialentry']) && $multi == 'N') ? "1" : "0"). 
	",\"". $params. "\")";
  }
  else
  {
    $sql_query = "UPDATE student_details SET label='" . $label . "',";
    if($size != "")
      $sql_query .= "size = '" . $size . "',";
    if($multi != "")
      $sql_query .= "multi='" . $multi . "',";
    $sql_query .= "raccess = '" . $raccess . "',waccess='" . $waccess . "',seq_no='" . $seq_no . "',
	               overview=". (isset($HTTP_POST_VARS['overview']) ? "1" : "0").",
	               toverview=". (isset($HTTP_POST_VARS['toverview']) ? "1" : "0").",
	               initialentry=". (isset($HTTP_POST_VARS['initialentry']) ? "1" : "0").",
				   params=\"". $params. "\" WHERE table_name='" . $table_name . "'";
  }
  $sql_result = mysql_query($sql_query,$userlink);
  if(!$sql_result)
  { // Unable to create the new table!
    echo($dtext['op_fail'] . " " . $dtext['Reason'] . ": " . @mysql_error($userlink));
    echo("<br><a href=manstuddetails.php>" . $dtext['back_studetman'] . "</a>");
    SA_closeDB();
    exit;
  }
  
  // OK, we're ready to link back to the original page
  SA_closeDB();
  
  header("Location: " . $livesite ."manstuddetails.php");

?>