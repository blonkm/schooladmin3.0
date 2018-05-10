<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.info)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  
  // Operational definitions
  $vakcats = array("Taal & Communicatie","Maatschappij","Exacte vakken","Onderw. Onderst.","Kunst & Cultuur","Individu");
  /* JUL */
  $vakhead["Taal & Communicatie"] = array("ne","en","sp","pa");
  $vakhead["Maatschappij"] = array("gs","ak");
  $vakhead["Exacte vakken"] = array("wi","na","sk","bi");
  $vakhead["Onderw. Onderst."] = array("ec","ik");
  $vakhead["Kunst & Cultuur"] = array("ckv","bv","mu");
  $vakhead["Individu"] = array("lo");
  $ptvakken = array("lo","gs","ak","ec","pa","ne","en","sp","ckv","na","sk","wi","bi","ik");
  $newvakken = array("ne","en","wi");
  
  /* TEST systeem 
  $vakhead["Taal & Communicatie"] = array("ne","en","sp","pa");
  $vakhead["Maatschappij"] = array("gs","ak");
  $vakhead["Exacte vakken"] = array("wi","na","sk","bio");
  $vakhead["Onderw. Onderst."] = array("ec","ik");
  $vakhead["Kunst & Cultuur"] = array("ckv");
  $vakhead["Individu"] = array("kgl","lo");
  $ptvakken = array("lo","gs","ak","ec","pa","ne","en","sp","ckv","na","sk","wi","bio","ik");
  */
  $afwezigreden = array(1,2,3,4,5,11,12,17,18,21);
  $telaatreden = array(6,7,8,9,10,19);
  $groepfilter = "3%";
  $llnperpage = 8;
  
  // Functions
  function get_initials($name)
  {
    $explstring = explode(" ",$name);
    $retstr = "";
    foreach($explstring AS $addstr)
      $retstr .= " ". substr($addstr,0,1);
    return $retstr;
  }
  
  
  $uid = $_SESSION['uid'];
  $uid = intval($uid);

  // Get the school name
  $schoolname = $announcement;
  $schoolname = str_replace("!","",$schoolname);
  $schoolname = str_replace("Welkom bij ","",$schoolname);
  $schoolname = str_replace("het ","",$schoolname);
  $schoolname = str_replace("de ","",$schoolname);
  
  // Get the year
  $schoolyear = SA_loadquery("SELECT year FROM period");
  $schoolyear = $schoolyear['year'][1];
  
  // Get a list of groups
  $groups = SA_loadquery("SELECT * FROM sgroup LEFT JOIN ". $teachercode. " ON(tid_mentor=tid) WHERE active=1 AND groupname LIKE '". $groepfilter. "' ORDER BY groupname");
  
	// Get a list of subject packages for all students
	$packagesqr = SA_loadquery("SELECT sid,GROUP_CONCAT(shortname) AS sbpack FROM s_package LEFT JOIN subjectpackage USING(packagename) LEFT JOIN subject USING(mid) GROUP BY sid");
	if(isset($packagesqr['sid']))
		foreach($packagesqr['sid'] AS $sbix => $asid)
			$package[$asid] = $packagesqr['sbpack'][$sbix];
  
  if(isset($groups))
  {
    // First part of the page
    echo("<html><head><title>Bespreeklijst</title></head><body link=blue vlink=blue>");
    echo '<LINK rel="stylesheet" type="text/css" href="style_Bespreeklijst.css" title="style1">';

	// Check if periods are blocked, if so, only admin can see the list!
	$pblock = SA_loadquery("SELECT id FROM period WHERE status='closed'");
	$admincheck = SA_loadquery("SELECT tid FROM teacherroles WHERE role=1 AND tid=". $_SESSION['uid']);
	if(isset($pblock) && !isset($admincheck))
	{
	  echo("Omdat er een periode afgesloten is, is de bespreeklijst alleen beschikbaar voor beheerders. </html>");
	  exit;
	}

    foreach($groups['gid'] AS $gix => $gid)
	{
	  // Create a list of subject details
	  $sdquery = "SELECT type, fullname, shortname, data FROM class LEFT JOIN subject USING(mid) LEFT JOIN ". $teachercode. " USING(tid) WHERE gid=". $gid;
	  $sdquery .= " UNION SELECT type, fullname, shortname, data FROM subject LEFT JOIN (SELECT meta_subject,data FROM subject LEFT JOIN class USING(mid) LEFT JOIN ". $teachercode. " USING(tid) WHERE gid=". $gid. " AND type='sub' GROUP BY mid) AS t1 ON (subject.mid=t1.meta_subject) WHERE type='meta'";
	  $subjectdata = SA_loadquery($sdquery);
	  foreach($subjectdata['shortname'] AS $cix => $subjab)
	  {
	    $subjdata[$subjab]["teacher"] = $subjectdata["data"][$cix];
		$subjdata[$subjab]["fullname"] = $subjectdata["fullname"][$cix];
		$subjdata[$subjab]["type"] = $subjectdata["type"][$cix];
	  }

      // Get a list of students
      $students = SA_loadquery("SELECT student.* FROM student LEFT JOIN sgrouplink USING(sid) WHERE gid=". $gid. " ORDER BY lastname,firstname");

	  // Get a list of period dates
	  $pquery = "SELECT id, startdate, enddate FROM period";
	  $perdata = SA_loadquery($pquery);
	  foreach($perdata['id'] AS $pix => $pid)
	  {
	    $pdata[$pid]["sdate"] = $perdata["startdate"][$pix];
	    $pdata[$pid]["edate"] = $perdata["enddate"][$pix];
	  }
	  // Fill in invalid period data
	  $curmonth = Date('n');
	  $curyear = Date('Y');
	  if($curmonth < 8)
	    $curyear--;
	  if(!isset($pdata[1]["sdate"]))
	    $pdata[1]["sdate"]= $curyear. "-08-15";
	  if(!isset($pdata[1]["edate"]))
	    $pdata[1]["edate"] = $curyear. "-11-15";
	  if(!isset($pdata[2]["sdate"]))
	    $pdata[2]["sdate"]= $curyear. "-11-16";
	  if(!isset($pdata[2]["edate"]))
	    $pdata[2]["edate"]= ($curyear+1). "-03-15";
	  if(!isset($pdata[3]["sdate"]))
	    $pdata[3]["sdate"]= ($curyear+1). "-03-16";
	  if(!isset($pdata[3]["edate"]))
	    $pdata[3]["edate"]= ($curyear+1). "-07-16";

	  if(isset($students))
	  {
	    $llnoffset = 0;
			while ($llnoffset < sizeof($students['sid']))
			{
				$scnt = $llnperpage;
				if(sizeof($students['sid']) - $llnoffset < $scnt)
					$scnt = sizeof($students['sid']) - $llnoffset;
				echo("<TABLE BORDER=1><TR><TH class=headleft COLSPAN=2>". $schoolname. "<BR>Schooljaar ". $schoolyear. "<BR>Klas: ". $groups['groupname'][$gix]. "<BR>Mentor: ". $groups['data'][$gix]. " </TH>");
				for($sx = 1; $sx <= $scnt; $sx++)
				{
					echo("<TH COLSPAN=4>". ($sx+$llnoffset). ". ". $students['lastname'][$sx+$llnoffset]. "<BR><SPAN class=stfirstname>". $students['firstname'][$sx+$llnoffset]. "</SPAN></TH>");
				}
				echo("</TR>");
				
				echo("<TR><TH>Vakken</TH><TH>Doc.</TH>");
				for($sx = 1; $sx <= $scnt; $sx++)
				{
					echo("<TH>1</TH><TH>2</TH><TH>3</TH><TH>E</TH>");
				}
				echo("</TR>");
				
				// Get the student results for students in set
				for($sx = 1; $sx <= $scnt; $sx++)
				{
					$sres = SA_loadquery("SELECT period, result, shortname FROM gradestore LEFT JOIN subject USING(mid) WHERE sid=". $students['sid'][$llnoffset+$sx]. " AND year=\"". $schoolyear. "\" ");
					if(isset($sres))
						foreach($sres['period'] AS $rix => $perid)
							$stres[$llnoffset+$sx][$sres['shortname'][$rix]][$perid] = $sres['result'][$rix];
					unset($sres);
				}

				
				foreach($vakcats AS $vk)
				{
					echo("<TR><TD class=cathead COLSPAN=2>". $vk. "</TD>");
					for($sx = 1; $sx <= $scnt; $sx++)
					{
						echo("<TD COLSPAN=4>&nbsp;</TD>");
					}
					echo("</TR>");
					foreach($vakhead[$vk] AS $vkn)
					{
						echo("<TR><TD>". $subjdata[$vkn]["fullname"]. "</TD><TD>". $subjdata[$vkn]["teacher"]. "</TD>");
							for($sx = 1; $sx <= $scnt; $sx++)
							{
							echo("<TD>");
						if(isset($stres[$llnoffset+$sx][$vkn][1]))
							echo(colored($stres[$llnoffset+$sx][$vkn][1] > 0.0 ? number_format($stres[$llnoffset+$sx][$vkn][1],1,',','.') : $stres[$llnoffset+$sx][$vkn][1]));
						else
							echo("&nbsp;");
						echo("</TD>");
							echo("<TD>");
						if(isset($stres[$llnoffset+$sx][$vkn][2]))
							echo(colored($stres[$llnoffset+$sx][$vkn][2] > 0.0 ? number_format($stres[$llnoffset+$sx][$vkn][2],1,',','.') : $stres[$llnoffset+$sx][$vkn][2]));
						else
							echo("&nbsp;");
						echo("</TD>");
							echo("<TD>");
						if(isset($stres[$llnoffset+$sx][$vkn][3]))
							echo(colored($stres[$llnoffset+$sx][$vkn][3] ? number_format($stres[$llnoffset+$sx][$vkn][3],1,',','.') : $stres[$llnoffset+$sx][$vkn][3]));
						else
							echo("&nbsp;");
						echo("</TD>");
							echo("<TD>");
						if(isset($stres[$llnoffset+$sx][$vkn][0]))
							echo(colored($stres[$llnoffset+$sx][$vkn][0]));
						else
							echo("&nbsp;");
						echo("</TD>");
							}
						echo("</TR>");
						
					} // End for each subject
				} // End subject categories
				
				// Absence data
				echo("<TR><TH class=headleft COLSPAN=2>Afwezig</TH>");
				for($sx = 1; $sx <= $scnt; $sx++)
				{
					// Get the absence data
				$absq = "SELECT SUM(IF(date >= '". $pdata[1]["sdate"]. "' AND date < '". $pdata[2]["sdate"]. "',1,0)) AS ap1";
				$absq .= ",SUM(IF(date >= '". $pdata[2]["sdate"]. "' AND date < '". $pdata[3]["sdate"]. "',1,0)) AS ap2";
				$absq .= ",SUM(IF(date >= '". $pdata[3]["sdate"]. "',1,0)) AS ap3";
				$absq .= " FROM absence WHERE sid=". $students['sid'][$llnoffset + $sx]. " AND (";
				$afwfilt = "";
				foreach($afwezigreden AS $afwr)
				{
					$afwfilt .= " OR aid=". $afwr;
				}
				$absq .= substr($afwfilt,4). ") GROUP BY sid";
				$absdat = SA_loadquery($absq);
				if(isset($absdat))
				{
					echo("<TD>". $absdat["ap1"][1]. "</TD><TD>". $absdat["ap2"][1]. "</TD><TD>". $absdat["ap3"][1]. "</TD><TD>");
					echo(($absdat["ap1"][1] + $absdat["ap2"][1] + $absdat["ap3"][1]). "</TD>");
				}
				else
						echo("<TD COLSPAN=4>&nbsp;</TD>");
				unset($absdat);
				}
				echo("</TR>");

				echo("<TR><TH class=headleft COLSPAN=2>Te laat</TH>");
				for($sx = 1; $sx <= $scnt; $sx++)
				{
					// Get the absence data
				$absq = "SELECT SUM(IF(date >= '". $pdata[1]["sdate"]. "' AND date < '". $pdata[2]["sdate"]. "',1,0)) AS ap1";
				$absq .= ",SUM(IF(date >= '". $pdata[2]["sdate"]. "' AND date < '". $pdata[3]["sdate"]. "',1,0)) AS ap2";
				$absq .= ",SUM(IF(date >= '". $pdata[3]["sdate"]. "',1,0)) AS ap3";
				$absq .= " FROM absence WHERE sid=". $students['sid'][$llnoffset + $sx]. " AND (";
				$afwfilt = "";
				foreach($telaatreden AS $afwr)
				{
					$afwfilt .= " OR aid=". $afwr;
				}
				$absq .= substr($afwfilt,4). ") GROUP BY sid";
				$absdat = SA_loadquery($absq);
				if(isset($absdat))
				{
					echo("<TD>". $absdat["ap1"][1]. "</TD><TD>". $absdat["ap2"][1]. "</TD><TD>". $absdat["ap3"][1]. "</TD><TD>");
					echo(($absdat["ap1"][1] + $absdat["ap2"][1] + $absdat["ap3"][1]). "</TD>");
				}
				else
						echo("<TD COLSPAN=4>&nbsp;</TD>");
				unset($absdat);
				}
				echo("</TR>");
				
				// Calculate points for advice
				for($sx = 1; $sx <= $scnt; $sx++)
				{
					for($p=0;$p<4;$p++)
					{
						$stcalp[$sx][$p] = 0.0; // Total points of all subjects
						$stcalo[$sx][$p] = 0; // <6 count of all subjects
						$stcalc[$sx][$p] = 0; // shortage of all subjects
						$stcalpo[$sx][$p] = 0; // < 6 in selected subjects
						$stcalpt[$sx][$p] = 0; // shortage in selected subjects
						$stcalpno[$sx][$p] = 0; // < 6 in NEW in selected subjects
						$stcalpnc[$sx][$p] = 0; // Shortage in NEW selected subjects
					}
					foreach($ptvakken AS $vak)
					{
						for($p=0;$p<4;$p++)
						{
							if(isset($stres[$sx+$llnoffset][$vak][$p]))
							{
								$stcalp[$sx][$p] += $stres[$sx+$llnoffset][$vak][$p];
								if($stres[$sx+$llnoffset][$vak][$p] < 5.5)
								{
									$stcalo[$sx][$p]++;
									$stcalc[$sx][$p] += 6 - round($stres[$sx+$llnoffset][$vak][$p],0);
								}			    
							}
						}
					}
					if(isset($package[$students['sid'][$sx+$llnoffset]]))
						$pvaks = explode(",",$package[$students['sid'][$sx+$llnoffset]]);
					else
						$pvaks = $newvakken;
					foreach($newvakken AS $vak)
					{
						for($p=0;$p<4;$p++)
						{
							if(isset($stres[$sx+$llnoffset][$vak][$p]) && in_array($vak,$pvaks))
							{
								if($stres[$sx+$llnoffset][$vak][$p] < 5.5)
								{
									$stcalpno[$sx][$p]++;
									$stcalpnc[$sx][$p] += 6 - round($stres[$sx+$llnoffset][$vak][$p],0);
								}
							}
						}
					}
					foreach($pvaks AS $vak)
					{
						for($p=0;$p<4;$p++)
						{
							if(isset($stres[$sx+$llnoffset][$vak][$p]))
							{
								if($stres[$sx+$llnoffset][$vak][$p] < 5.5)
								{
									$stcalpo[$sx][$p]++;
									$stcalpt[$sx][$p] += 6 - round($stres[$sx+$llnoffset][$vak][$p],0);
								}			    
							}
						}
					}
				}
				
				// Show calculated results
				echo("<TR><TD COLSPAN=2>Totaal punten</TD>");
				for($sx = 1; $sx <= $scnt; $sx++)
				{
					for($p=0;$p<4;$p++)
				{
					$pi = ($p + 1) % 4;
					echo("<TD>");
					if(isset($stcalp[$sx][$pi]) && $stcalp[$sx][$pi] > 0)
						echo($stcalp[$sx][$pi]);
					else
						echo("&nbsp");
					echo("</TD>");
				}
				}
				echo("</TR>");
				
				echo("<TR><TD COLSPAN=2>Aantal onvoldoende</TD>");
				for($sx = 1; $sx <= $scnt; $sx++)
				{
					for($p=0;$p<4;$p++)
				{
					$pi = ($p + 1) % 4;
					echo("<TD>");
					if(isset($stcalo[$sx][$pi]) && $stcalp[$sx][$pi] > 0)
						echo($stcalo[$sx][$pi]);
					else
						echo("&nbsp");
					echo("</TD>");
				}
				}
				echo("</TR>");
				
				echo("<TR><TD COLSPAN=2>Tekorten</TD>");
				for($sx = 1; $sx <= $scnt; $sx++)
				{
					for($p=0;$p<4;$p++)
				{
					$pi = ($p + 1) % 4;
					echo("<TD>");
					if(isset($stcalc[$sx][$pi]) && $stcalp[$sx][$pi] > 0)
						echo($stcalc[$sx][$pi]);
					else
						echo("&nbsp");
					echo("</TD>");
				}
				}
				echo("</TR>");
				
				echo("<TR><TH class=headleft COLSPAN=2>Vakkenpakket</TH>");
				for($sx = 1; $sx <= $scnt; $sx++)
				{
					echo("<TD COLSPAN=4>");
					if(isset($package[$students['sid'][$sx+$llnoffset]]))
						echo("<font size=-2>". $package[$students['sid'][$sx+$llnoffset]]. "</font></td>");
					else
						echo("*</td>");
				}
				echo("</tr>");
							
				echo("<TR><TH class=headleft COLSPAN=2>Norm</TH>");
				for($sx = 1; $sx <= $scnt; $sx++)
				{
					for($p=0;$p<4;$p++)
				{
					$pi = ($p + 1) % 4;
					echo("<TD>");
					if($stcalp[$sx][$pi] >= 83.0 && $stcalo[$sx][$pi] <= 4 && $stcalc[$sx][$pi] <= 5 && $stcalpno[$sx][$pi] <= 2 && 
					$stcalpo[$sx][$pi] <= 2 && $stcalpno[$sx][$pi] <= 2 &&  $stcalpno[$sx][$pi] == $stcalpnc[$sx][$pi])
						echo("BEV");
					else if($stcalp[$sx][$pi] >= 78.0 && $stcalo[$sx][$pi] <= 4 && $stcalc[$sx][$pi] <= 6 && $stcalpno[$sx][$pi] <= 2 && 
					$stcalpo[$sx][$pi] <= 2 && $stcalpno[$sx][$pi] <= 2 &&  $stcalpno[$sx][$pi] == $stcalpnc[$sx][$pi])
						echo("BES");
					else
						echo("ONV");
					echo("</TD>");
				}
				}
				echo("</TR>");
				
				echo("</TABLE>");
				echo("<P class=footer>Resultaat codes: BEV=Bevorderen, BES=Bespreekgeval, ONV=Onvoldoende, een * geeft aan dat er geen profiel is gekozen en daarom de profiel gebonden voorwaarden niet zijn meegnomen</P>");
				$llnoffset += $llnperpage;
			} // End while for subgroups of students
	  } // End if student for the group
	
	  unset($stres);
	} // End for each group
  } // End if groups defined
      
  echo("</html>");
  
  function colored($res)
  {
     $res2 = str_replace(',','.',$res);
	 if($res2 < 5.5)
	   return("<SPAN class=redcolor>". $res. "</SPAN>");
	 else
	   return($res);
  }
?>
