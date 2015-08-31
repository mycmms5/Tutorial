<?php
/** 
* @author Werner Huysmans
* @package webcalendar
* @subpackage views
* @filesource
* CVS
* $Id: plan_mycmms.php,v 1.2 2013/08/19 13:37:54 werner Exp $
* $Source: /var/www/cvs/mycmms_wc/myCMMS_WC/_main/plan_mycmms.php,v $
* $Log: plan_mycmms.php,v $
* Revision 1.2  2013/08/19 13:37:54  werner
* Cleanup Code (old WebCalendar code removed)
* Planned technicians come from sys_groups and no longer from webcal_users.
*
* Revision 1.1  2013/07/29 10:33:33  werner
* PlanCalendar planning screen
*
**/
session_start();
require("../includes/functions_mycmms.php");
require("functions_webcal.php");
require("../libraries/setup.php"); 

# $DISPLAY_WEEKENDS='Y';
# $WEEK_START=1; //Set to Monday.
/**
* Initializing the navigation bar
* 
* @var mixed
*/
# $special = array ('month.php','day.php','week.php','week_details.php','year.php','minical.php' );
# $DMW = in_array ( $SCRIPT, $special );
# include_once 'includes/' . $user_inc;
# Calculate Date options
$years=years($_REQUEST['date']);
$months=months($_REQUEST['date']);
$weeks=weeks($_REQUEST['date']);
/**
* Initialization:
* - popup_counter 
* - error
*/
$popup_counter=0;
$error = '';
$DB=DBC::get();
set_today($_REQUEST['date']);   // Sets the date
$nextdate=date('Ymd',mktime(0,0,0,$thismonth,$thisday+7,$thisyear));
$prevdate=date('Ymd',mktime(0,0,0,$thismonth,$thisday-7,$thisyear));
$wkstart=get_weekday_before($thisyear,$thismonth,$thisday+1);
$wkend=$wkstart+(86400*7);
$thisdate=date('Ymd',$wkstart);
# if (!empty($error)) { echo print_error ( $error ) . print_trailer (); exit; }
/**
* For speed, we transfer all data in a MEMORY table
* - If multiple planners work, the table-name is user-dependant
*/
$temp_table="wc_".$_SESSION['user'];
try {
    $start=date("Y-m-d",$wkstart); $end=date("Y-m-d",$wkend);    
    DBC::execute("CREATE TEMPORARY TABLE $temp_table(
      `ID` int(11) NOT NULL,
      `WONUM` int(11) NOT NULL,
      `EMPCODE` char(50) NOT NULL,
      `WODATE` date NOT NULL DEFAULT '0000-00-00',
      `REGHRS` float DEFAULT NULL,
      `ESTHRS` float DEFAULT NULL,
      `EQNUM` varchar(255) DEFAULT NULL,
      `TASKDESC` varchar(255) DEFAULT NULL,
      `WOTYPE` char(25) DEFAULT NULL,
      `WOSTATUS` char(25) DEFAULT NULL,
      PRIMARY KEY (`ID`),
      KEY `EMPCODE` (`EMPCODE`) USING BTREE,
      KEY `WODATE` (`WODATE`) USING BTREE
      ) ENGINE=MEMORY DEFAULT CHARSET=utf8;");
    DBC::execute("INSERT INTO $temp_table (ID,WONUM,EMPCODE,WODATE,REGHRS,ESTHRS,EQNUM,TASKDESC,WOTYPE,WOSTATUS) 
        SELECT ID,WONUM,EMPCODE,WODATE,REGHRS,ESTHRS,EQNUM,TASKDESC,WOTYPE,WOSTATUS FROM webcal_entries 
        WHERE WODATE BETWEEN '$start' AND '$end'",array());
/**   DBC::execute("TRUNCATE temp_webcal_entries");
*     DBC::execute("INSERT INTO temp_webcal_entries (ID,WONUM,EMPCODE,WODATE,REGHRS,ESTHRS,EQNUM,TASKDESC,WOTYPE,WOSTATUS) SELECT ID,WONUM,EMPCODE,WODATE,REGHRS,ESTHRS,EQNUM,TASKDESC,WOTYPE,WOSTATUS FROM webcal_entries WHERE WODATE BETWEEN '$start' AND '$end'",array());
*/
} catch (Exception $e) {
    $DB->rollBack();
    PDO_log_XML(__FILE__,$e);
}
// @todo    Get users from sys_groups
#$viewusercnt=DBC::fetchColumn("SELECT COUNT(*) FROM webcal_user",0);
$viewusercnt=DBC::fetchcolumn("SELECT COUNT(*) FROM sys_groups WHERE (profile & 64)=64");
// @todo    Cleanup this section
/** Preloading events and repeated events 
$e_save = $re_save = array ();
for ($i=0; $i<$viewusercnt; $i++ ) {    // Load events for users
  //$re_save[$i]=read_repeated_events($viewusers[$i], $wkstart, $wkend, '' );
  $result=$DBWC->query("SELECT we.cal_name, we.cal_description, we.cal_date, we.cal_time,
    we.cal_id, we.cal_ext_for_id, we.cal_priority, we.cal_access,
    we.cal_duration, weu.cal_status, we.cal_create_by, weu.cal_login,
    we.cal_type, we.cal_location, we.cal_url, we.cal_due_date, we.cal_due_time,
    weu.cal_percent, we.cal_mod_date, we.cal_mod_time FROM webcal_entry we 
    LEFT JOIN webcal_entry_user weu ON we.cal_id=weu.cal_id WHERE ((we.cal_date >= '$wkstart-604800' AND we.cal_date <= $wkend))");
  $e_save[$i]=$result->fetchAll(PDO::FETCH_ASSOC);  
  // $e_save[$i]=read_events( $viewusers[$i], $wkstart - 604800, $wkend );
} 
**/
/** Loop
* $j the weekdays
* $i the technicians
*/
$DAYS_PER_TABLE = 7;
for ( $j = 0; $j < 7; $j += $DAYS_PER_TABLE ) {
    $todayYmd=date('Ymd',$today);
    for ( $i = 0; $i < $viewusercnt; $i++ ) {   // For every user
        $result=$DB->query("SELECT uname,longname FROM sys_groups WHERE (profile & 64)=64");   # Get better info on users
        $user=$result->fetchAll(PDO::FETCH_ASSOC);
        $weekday=0;
        for ($date=$wkstart; $date<$wkend; $date+=86400) {
            $is_weekend=is_weekend($date);  // Weekend ?
            # if ($is_weekend && $DISPLAY_WEEKENDS == 'N' ) { continue; }
            $dateYmd=date('Ymd',$date);
            $dateYmd2=date("Y-m-d",$date);
            if ($dateYmd==$todayYmd) { $class="today"; } else { $class="row"; }
            $result=$DB->query("SELECT * FROM $temp_table WHERE WODATE='$dateYmd2' AND EMPCODE='{$user[$i]['uname']}'");
            if ($result) {
                $events=$result->fetchAll(PDO::FETCH_ASSOC);
                if (count($events)>0) {
                    $class="hasevents";
                    $techs[$i][$weekday]['EVENT']=true;
                    foreach ($events as $event) {
/**                        
                        $popup[$popup_counter]['pop_id']=$event['cal_id']."-".$popup_counter;
                        $popup[$popup_counter]['time']=$event['cal_date']." ".$event['cal_duration'];
                        $popup[$popup_counter]['location']=$event['cal_location'];
                        $popup[$popup_counter]['description']=$event['cal_name']."<br>".$event['cal_description'];
**/                        
                        $popup[$popup_counter]['pop_id']=$event['ID'];  # ."-".$popup_counter;
                        $popup[$popup_counter]['time']="ESTHRS: ".$event['ESTHRS']."</br>REGHRS ".$event['REGHRS'];
                        $popup[$popup_counter]['location']=$event['EQNUM'];
                        $popup[$popup_counter]['description']=$event['TASKDESC']."<br>".$event['TEXTS_B'];
                        $popup_counter++;
                    } // EO foreach event
                } else {
                    $techs[$i][$weekday]['EVENT']=false;
                } // EO if event               
            }
            # ---
            if (!empty($entrystr) && ($entrystr != "&nbsp;")) { $class="hasevents"; }
            if ($is_weekend) { $class="weekend"; }
                if ($i == 0 ) { # Build header row.
                    $headers[$k]['CLASS']=$class;
                    $headers[$k]['DAY']=$date;
                    $k++;
                }
/**
                $techs[$i][$weekday]['NAME']=$user[$i]['cal_lastname']; // Complete name of user
                $techs[$i][$weekday]['UNAME']=$user[$i]['cal_login'];    // Internal name of user
**/                
                $techs[$i][$weekday]['NAME']=$user[$i]['longname']; // Complete name of user
                $techs[$i][$weekday]['UNAME']=$user[$i]['uname'];    // Internal name of use
                $techs[$i][$weekday]['DATE']=$dateYmd;  // Date of Call
                $techs[$i][$weekday]['CLASS']=$class;
                $techs[$i][$weekday]['EVENT']=true;
                $techs[$i][$weekday]['EVENTS']=$events;
                $weekday++;
        } // EO for days
        }
}
$tpl=new smarty_mycmms();
$tpl->caching=false;
$tpl->debugging=false;
$tpl->assign("user",$_SESSION['webcal_login']);
$tpl->assign("planner",true);
$tpl->assign("weeks",$weeks);
$tpl->assign("months",$months);
$tpl->assign("years",$years);
$tpl->assign("view_name","PLAN Calendar for myCMMS");
$tpl->assign("prevdate",$prevdate); // Previous week Ymd
$tpl->assign("nextdate",$nextdate); // Next week Ymd
$tpl->assign("thisdate",strtotime($thisdate));
$tpl->assign("wkend",$wkend);
$tpl->assign("cw","10%");
$tpl->assign("headers",$headers);
$tpl->assign("techs",$techs);
$tpl->assign("max_techs",$viewusercnt);
$tpl->assign("popups",$popup);
$tpl->assign("max_events",count($events));
$tpl->display_error("cal/view_mycmms_plan.tpl");
unset($_SESSION['PDO_ERROR']);
?>
