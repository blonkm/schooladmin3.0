<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.  (http://www.aim4me.info)        |
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

  $login_qualify = 'ACT';
  require_once("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];

  $uid = intval($uid);

  $sid = trim($HTTP_POST_VARS['sid']);
  $reason = trim($HTTP_POST_VARS['reason']);
  $date = trim($HTTP_POST_VARS['date']);
  $time = trim($HTTP_POST_VARS['time']);
  $authorization = trim($HTTP_POST_VARS['authorization']);
  $explanation = trim($HTTP_POST_VARS['explanation']);
  if(isset($HTTP_POST_VARS['orgdate']))
    $orgdate = $HTTP_POST_VARS['orgdate'];
  else
    $orgdate = "";

  if ($sid == "" || $reason=="" || $date=="" || $authorization=="")
  {
    echo($dtext['missing_params']);
    echo("<br><a href=manabsents.php>" . $dtext['back_abs_reg'] . "</a>");
    echo("$sid;$reason;$date;$authorization");
    SA_closeDB();
    exit;
  }

  if($orgdate == "") // New record!
    $sql_query = "INSERT INTO absence (sid,aid,date,time,authorization,explanation) VALUES('$sid', '$reason', '$date', '$time', '$authorization', '$explanation')";
  else
    $sql_query = "UPDATE absence SET aid='$reason',date='$date',time='$time',authorization='$authorization',explanation='$explanation' WHERE sid='$sid' AND date='$orgdate'";
  $mysql_query = $sql_query;
  // echo $sql_query; //~~~

  $sql_result = mysql_query($mysql_query,$userlink);
  

//~~~  SA_closeDB();
  
  if($sql_result == 1)
  {	// operation succeeded, back to the absence management page!
    header("Location: " . $livesite ."manabsents.php");
    exit;
  }
  else
  {
    echo($dtext['op_fail']);
    echo(@mysql_error($userlink));
    echo("<br><a href=manabsents.php>" . $dtext['back_abs_reg'] . "</a>");
  }   

?>


