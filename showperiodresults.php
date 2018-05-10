<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.   (http://www.aim4me.info)       |
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
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();

  $login_qualify = 'S';
  include ("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  if(isset($HTTP_GET_VARS['period']))
    $period = $HTTP_GET_VARS['period'];
    
  $uid = intval($uid);
  $sid = $uid;

  // First we get the data from student in an array.
  $sql_query = "SELECT * FROM student LEFT JOIN sgrouplink USING(sid) WHERE student.sid='$sid'";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  $nrows = 0;
  if (mysql_num_rows($sql_result)!=0)
  {
    $nfields = mysql_num_fields($sql_result);
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
     $nrows++;
     for ($i=0;$i<$nfields;$i++){
       $fieldname = mysql_field_name($sql_result,$i);
       $fieldvalu = mysql_result($sql_result,$r,mysql_field_name($sql_result,$i));
       $student_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $row_n = $nrows;
  // set the group id for smarter queries following
  $gid = $student_array['gid'][1];

  // Get the list of periods with their details
  $sql_query = "SELECT * FROM period";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  $nrows = 0;
  if (mysql_num_rows($sql_result)!=0)
  {
    $nfields = mysql_num_fields($sql_result);
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
     $nrows++;
     for ($i=0;$i<$nfields;$i++){
       $fieldname = mysql_field_name($sql_result,$i);
       $fieldvalu = mysql_result($sql_result,$r,mysql_field_name($sql_result,$i));
       $period_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $periods = $nrows;

  // Get the list of applicable subjects with their details
  $sql_query = "SELECT subject.*,class.* FROM class LEFT JOIN subject USING(mid) LEFT JOIN sgrouplink USING(gid) WHERE sid='$uid' AND show_sequence IS NOT NULL GROUP BY subject.mid ORDER BY show_sequence";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  echo mysql_error($userlink);
  $nrows = 0;
  if (mysql_num_rows($sql_result)!=0)
  {
    $nfields = mysql_num_fields($sql_result);
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
     $nrows++;
     for ($i=0;$i<$nfields;$i++){
       $fieldname = mysql_field_name($sql_result,$i);
       $fieldvalu = mysql_result($sql_result,$r,mysql_field_name($sql_result,$i));
       $subject_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $subjects = $nrows;

  // Get a list of testresults for the current period
  $sql_query = "SELECT result,type,mid,testdef.tdid FROM testresult LEFT JOIN testdef using (tdid) LEFT JOIN class USING (cid) LEFT JOIN period ON (testdef.period=period.id) where sid='$sid' AND period='$period' AND period.year=testdef.year AND testdef.type <> '0' ORDER BY testresult.last_update";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  $nrows = 0;
  if (mysql_num_rows($sql_result)!=0)
  {
    $nfields = mysql_num_fields($sql_result);
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
     $nrows++;
     $test_array[mysql_result($sql_result,$r,'mid')][mysql_result($sql_result,$r,'type')][mysql_result($sql_result,$r,'tdid')] = mysql_result($sql_result,$r,'result');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $tests = $nrows;

  // Get the list of pass criteria per subject & test type
  $sql_query = "SELECT * FROM reportcalc ORDER BY testtype,mid";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  $nrows = 0;
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
      $passpoints[mysql_result($sql_result,$r,'testtype')][mysql_result($sql_result,$r,'mid')] = mysql_result($sql_result,$r,'passthreshold');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0

  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['perres_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['perres_4'] . " " . $student_array['firstname'][1] . " " . $student_array['lastname'][1] . " " . $dtext['4_per'] . " " .$period. "</font><p>");
  include("studentmenu.php");

  echo("<br><div align=left>");

  // Now we must find out how many entries max. for each type of test (max # of collumns)
  if(isset($test_array))
  {
    foreach($test_array AS $subji => $subtest)
    {
      foreach($subtest AS $tti => $testtype)
        $testcount[$tti][$subji] = count($testtype);
    }
  }
  /* for($t=1;$t<=$tests;$t++)
  {
    if(isset($testcount[$test_array['type'][$t]][$test_array['mid'][$t]]))
      $testcount[$test_array['type'][$t]][$test_array['mid'][$t]]++;
    else
      $testcount[$test_array['type'][$t]][$test_array['mid'][$t]] = 1;
  } */

  if($tests > 0)
  {
    foreach($passpoints as $type => $value)
    {
      $typecount[$type] = 0;
      if(isset($testcount[$type]))
      {
        foreach($testcount[$type] as $count)
        {
          if($typecount[$type] < $count)
            $typecount[$type] = $count;
        }
      }
    }
  }

  if($tests > 0 && $period_array['status'][$period] != 'closed')
  {   
    // Now create a table with all subjects for this student to enable to go to the grade details
    // Create the first heading row for the table
    echo("<table border=1 cellpadding=0>");
    echo("<tr><td><center>" . $dtext['Subject'] . "</td>");
    // Now add types heading
    foreach($typecount as $type => $count)
    {
      if($count > 0)
        echo("<td COLSPAN='$count'><center>" . $type . "</td>");
    }
    echo("</tr>"); 
  

    // Create a row in the table for every subject
    $currentTest = 1;
    for($s=1;$s<=$subjects;$s++)
    { // each subject
      $mid = $subject_array['mid'][$s];
      $cid = $subject_array['cid'][$s];
      echo("<tr><td>" . $subject_array['fullname'][$s] . "</td>");
      foreach($typecount as $type => $count)
      {
         if(isset($passpoints[$type][$mid]))
           $passpoint=$passpoints[$type][$mid];
         else
           $passpoint=$passpoints[$type][0];
         if(isset($testcount[$type][$mid]))
         {
           /* for($r=0;$r<$testcount[$type][$mid]; $r++)
           {
             echo("<td>");
             $result = $test_array['result'][$currentTest++];
             // Colour depends on pass criteria
             if($passpoint > $result) echo("<font color=red>");
             else echo("<font color=blue>");
             echo($result);
             echo("</font></td>");
           } */
           foreach($test_array[$mid][$type] AS $tdid => $result)
           {
             echo("<td>");
             // Colour depends on pass criteria
             if($passpoint > $result) echo("<font color=red>");
             else echo("<font color=blue>");
             echo(str_replace(".",$dtext['dec_sep'],"".$result));
             echo("</font></td>");
           }

           // Now pad with empty cells
           for($r=$testcount[$type][$mid]; $r<$count; $r++)
             echo("<td> </td>");
         }
         else
         { // No tests found for this type & subject!
           for($r=0;$r<$count;$r++)
             echo("<td> </td>");
         }
      }
      echo("</tr>");
    }
    echo("</tr>");
    echo("</table>");
  }
  else
  { // No test results found or period is closed
    if($period_array['status'][$period] == 'closed')
      echo($dtext['perres_expl_1']);
    else
      echo($dtext['perres_expl_2']);

  }

  // close the page
  echo("</html>");

?>
