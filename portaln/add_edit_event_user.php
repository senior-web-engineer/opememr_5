<?php
/**
 *
 * Modified from interface/main/calendar/add_edit_event.php for
 * the patient portal.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (C) 2005-2006 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// continue session
session_start();

//landing page definition -- where to go if something goes wrong
$landingpage = "index.php?site=".$_SESSION['site_id'];
//

// kick out if patient not authenticated
if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
    $pid = $_SESSION['pid'];
} else {
    session_destroy();
    header('Location: '.$landingpage.'&w');
    exit;
}

//

$ignoreAuth = 1;
global $ignoreAuth;

require_once("../interface/globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/forms.inc");

 // Exit if the modify calendar for portal flag is not set-pulled for v5
 /* if (!($GLOBALS['portal_onsite_appt_modify'])) {
   echo add_escape_custom( xl('You are not authorized to schedule appointments.'),ENT_NOQUOTES);
   exit;
 } */

 // Things that might be passed by our opener.
 //
 $eid           = $_GET['eid'];         // only for existing events
 $date          = $_GET['date'];        // this and below only for new events
 $userid        = $_GET['userid'];
 $default_catid = $_GET['catid'] ? $_GET['catid'] : '5';
 $patientid     = $_GET['patid'];
 //

if ($date) {
    $date = substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6);
} else {
    $date = date("Y-m-d");
}

 //
 $starttimem = '00';
if (isset($_GET['starttimem'])) {
    $starttimem = substr('00' . $_GET['starttimem'], -2);
}

 //
if (isset($_GET['starttimeh'])) {
    $starttimeh = $_GET['starttimeh'];
    if (isset($_GET['startampm'])) {
        if ($_GET['startampm'] == '2' && $starttimeh < 12) {
            $starttimeh += 12;
        }
    }
} else {
    $starttimeh = date("G");
}

 $startampm = '';

 $info_msg = "";

// EVENTS TO FACILITIES (lemonsoftware)
//(CHEMED) get facility name
// edit event case - if there is no association made, then insert one with the first facility
if ($eid) {
    $selfacil = '';
    $facility = sqlQuery("SELECT pc_facility, pc_multiple, pc_aid, facility.name
                            FROM openemr_postcalendar_events
                              LEFT JOIN facility ON (openemr_postcalendar_events.pc_facility = facility.id)
                              WHERE pc_eid = ?", array($eid));
    if (!$facility['pc_facility']) {
        $qmin = sqlQuery("SELECT facility_id as minId, facility FROM users WHERE id = ?", array($facility['pc_aid']));
        $min  = $qmin['minId'];
        $min_name = $qmin['facility'];

        // multiple providers case
        if ($GLOBALS['select_multi_providers']) {
            $mul  = $facility['pc_multiple'];
            sqlStatement("UPDATE openemr_postcalendar_events SET pc_facility = ? WHERE pc_multiple = ?", array($min, $mul));
        }

        // EOS multiple

        sqlStatement("UPDATE openemr_postcalendar_events SET pc_facility = ? WHERE pc_eid = ?", array($min, $eid));
        $e2f = $min;
        $e2f_name = $min_name;
    } else {
        $e2f = $facility['pc_facility'];
        $e2f_name = $facility['name'];
    }
}

// EOS E2F
// ===========================


 // If we are saving, then save and close the window.
 //
if ($_POST['form_action'] == "save") {
    //print_r($_POST);
    //exit();
    $event_date = fixDate($_POST['form_date']);

  // Compute start and end time strings to be saved.
    if ($_POST['form_allday']) {
        $tmph = 0;
        $tmpm = 0;
        $duration = 24 * 60;
    } else {
        $tmph = $_POST['form_hour'] + 0;
        $tmpm = $_POST['form_minute'] + 0;
        if ($_POST['form_ampm'] == '2' && $tmph < 12) {
            $tmph += 12;
        }

        $duration = $_POST['form_duration'];
    }

    $starttime = "$tmph:$tmpm:00";
  //
    $tmpm += $duration;
    while ($tmpm >= 60) {
        $tmpm -= 60;
        ++$tmph;
    }

    $endtime = "$tmph:$tmpm:00";

  // Useless garbage that we must save.
    $locationspec = 'a:6:{s:14:"event_location";N;s:13:"event_street1";N;' .
    's:13:"event_street2";N;s:10:"event_city";N;s:11:"event_state";N;s:12:"event_postal";N;}';

  // More garbage, but this time 1 character of it is used to save the
  // repeat type.
    if ($_POST['form_repeat']) {
        $recurrspec = 'a:5:{' .
        's:17:"event_repeat_freq";s:1:"' . $_POST['form_repeat_freq'] . '";' .
        's:22:"event_repeat_freq_type";s:1:"' . $_POST['form_repeat_type'] . '";' .
        's:19:"event_repeat_on_num";s:1:"1";' .
        's:19:"event_repeat_on_day";s:1:"0";' .
        's:20:"event_repeat_on_freq";s:1:"0";}';
    } else {
        $recurrspec = 'a:5:{' .
        's:17:"event_repeat_freq";N;' .
        's:22:"event_repeat_freq_type";s:1:"0";' .
        's:19:"event_repeat_on_num";s:1:"1";' .
        's:19:"event_repeat_on_day";s:1:"0";' .
        's:20:"event_repeat_on_freq";s:1:"1";}';
    }

  //The modification of the start date for events that take place on one day of the week
  //for example monday, or thursday. We set the start date on the first day of the week
  //that the event is scheduled. For example if you set the event to repeat on each monday
  //the start date of the event will be set on the first monday after the day the event is scheduled
    if ($_POST['form_repeat_type'] == 5) {
        $exploded_date= explode("-", $event_date);
        $edate = date("D", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2], $exploded_date[0]));
        if ($edate=="Tue") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+6, $exploded_date[0]));
        } elseif ($edate=="Wed") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+5, $exploded_date[0]));
        } elseif ($edate=="Thu") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+4, $exploded_date[0]));
        } elseif ($edate=="Fri") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+3, $exploded_date[0]));
        } elseif ($edate=="Sat") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+2, $exploded_date[0]));
        } elseif ($edate=="Sun") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+1, $exploded_date[0]));
        }
    } elseif ($_POST['form_repeat_type'] == 6) {
        $exploded_date= explode("-", $event_date);
        $edate = date("D", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2], $exploded_date[0]));
        if ($edate=="Wed") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+6, $exploded_date[0]));
        } elseif ($edate=="Thu") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+5, $exploded_date[0]));
        } elseif ($edate=="Fri") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+4, $exploded_date[0]));
        } elseif ($edate=="Sat") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+3, $exploded_date[0]));
        } elseif ($edate=="Sun") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+2, $exploded_date[0]));
        } elseif ($edate=="Mon") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+1, $exploded_date[0]));
        }
    } elseif ($_POST['form_repeat_type'] == 7) {
        $exploded_date= explode("-", $event_date);
        $edate = date("D", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2], $exploded_date[0]));
        if ($edate=="Thu") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+6, $exploded_date[0]));
        } elseif ($edate=="Fri") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+5, $exploded_date[0]));
        } elseif ($edate=="Sat") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+4, $exploded_date[0]));
        } elseif ($edate=="Sun") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+3, $exploded_date[0]));
        } elseif ($edate=="Mon") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+2, $exploded_date[0]));
        } elseif ($edate=="Tue") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+1, $exploded_date[0]));
        }
    } elseif ($_POST['form_repeat_type'] == 8) {
        $exploded_date= explode("-", $event_date);
        $edate = date("D", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2], $exploded_date[0]));
        if ($edate=="Fri") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+6, $exploded_date[0]));
        } elseif ($edate=="Sat") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+5, $exploded_date[0]));
        } elseif ($edate=="Sun") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+4, $exploded_date[0]));
        } elseif ($edate=="Mon") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+3, $exploded_date[0]));
        } elseif ($edate=="Tue") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+2, $exploded_date[0]));
        } elseif ($edate=="Wed") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+1, $exploded_date[0]));
        }
    } elseif ($_POST['form_repeat_type'] == 9) {
        $exploded_date= explode("-", $event_date);
        $edate = date("D", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2], $exploded_date[0]));
        if ($edate=="Sat") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+6, $exploded_date[0]));
        } elseif ($edate=="Sun") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+5, $exploded_date[0]));
        } elseif ($edate=="Mon") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+4, $exploded_date[0]));
        } elseif ($edate=="Tue") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+3, $exploded_date[0]));
        } elseif ($edate=="Wed") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+2, $exploded_date[0]));
        } elseif ($edate=="Thu") {
            $event_date=date("Y-m-d", mktime(0, 0, 0, $exploded_date[1], $exploded_date[2]+1, $exploded_date[0]));
        }
    }//if end
/* =======================================================
//                                  UPDATE EVENTS
========================================================*/
    if ($eid) {
        // what is multiple key around this $eid?
        $row = sqlQuery("SELECT pc_multiple FROM openemr_postcalendar_events WHERE pc_eid = ?", array($eid));

        if ($GLOBALS['select_multi_providers'] && $row['pc_multiple']) {
            /* ==========================================
            // multi providers BOS
            ==========================================*/

            // obtain current list of providers regarding the multiple key
            $up = sqlStatement("SELECT pc_aid FROM openemr_postcalendar_events WHERE pc_multiple = ?", array($row['pc_multiple']));
            while ($current = sqlFetchArray($up)) {
                $providers_current[] = $current['pc_aid'];
            }

            $providers_new = $_POST['form_provider_ae'];

            // this difference means that some providers from current was UNCHECKED
            // so we must delete this event for them
            $r1 = array_diff($providers_current, $providers_new);
            if (count($r1)) {
                foreach ($r1 as $to_be_removed) {
                    sqlQuery("DELETE FROM openemr_postcalendar_events WHERE pc_aid = ? AND pc_multiple = ?", array($to_be_removed, $row['pc_multiple']));
                }
            }

            // this difference means that some providers was added
            // so we must insert this event for them
            $r2 = array_diff($providers_new, $providers_current);
            if (count($r2)) {
                foreach ($r2 as $to_be_inserted) {
                    sqlInsert("INSERT INTO openemr_postcalendar_events ( pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_hometext, pc_informant, pc_eventDate, pc_endDate, pc_duration, pc_recurrtype, pc_recurrspec, pc_startTime, pc_endTime, pc_alldayevent, pc_apptstatus, pc_prefcatid, pc_location, pc_eventstatus, pc_sharing, pc_facility)
                VALUES ( " .
                    "'" . add_escape_custom($_POST['form_category'])         . "', " .
                    "'" . add_escape_custom($row['pc_multiple'])             . "', " .
                    "'" . add_escape_custom($to_be_inserted)                 . "', " .
                    "'" . add_escape_custom($_POST['form_pid'])              . "', " .
                    "'" . add_escape_custom($_POST['form_title'])            . "', " .
                    "NOW(), "                                                .
                    "'" . add_escape_custom($_POST['form_comments'])         . "', " .
                    "'" . add_escape_custom($_SESSION['providerId'])         . "', " .
                    "'" . add_escape_custom($event_date)                     . "', " .
                    "'" . add_escape_custom(fixDate($_POST['form_enddate'])) . "', " .
                    "'" . add_escape_custom(($duration * 60))                . "', " .
                    "'" . ($_POST['form_repeat'] ? '1' : '0')                . "', " .
                    "'" . add_escape_custom($recurrspec)                     . "', " .
                    "'" . add_escape_custom($starttime)                      . "', " .
                    "'" . add_escape_custom($endtime)                        . "', " .
                    "'" . add_escape_custom($_POST['form_allday'])           . "', " .
                    "'" . add_escape_custom($_POST['form_apptstatus'])       . "', " .
                    "'" . add_escape_custom($_POST['form_prefcat'])          . "', " .
                    "'" . add_escape_custom($locationspec)                   . "', " .
                    "1, " .
                    "1, " .(int)$_POST['facility']. " )"); // FF stuff
                } // foreach
            } //if count


          // after the two diffs above, we must update for remaining providers
         // those who are intersected in $providers_current and $providers_new
            foreach ($_POST['form_provider_ae'] as $provider) {
                     sqlStatement("UPDATE openemr_postcalendar_events SET " .
                     "pc_catid = '"       . add_escape_custom($_POST['form_category'])   . "', " .
                     "pc_pid = '"         . add_escape_custom($_POST['form_pid'])        . "', " .
                     "pc_title = '"       . add_escape_custom($_POST['form_title'])      . "', " .
                     "pc_time = NOW(), "                                                 .
                     "pc_hometext = '"    . add_escape_custom($_POST['form_comments'])   . "', " .
                     "pc_informant = '"   . add_escape_custom($_SESSION['providerId'])   . "', " .
                     "pc_eventDate = '"   . add_escape_custom($event_date)               . "', " .
                     "pc_endDate = '"     . add_escape_custom(fixDate($_POST['form_enddate'])) . "', " .
                     "pc_duration = '"    . add_escape_custom(($duration * 60))          . "', " .
                     "pc_recurrtype = '"  . ($_POST['form_repeat'] ? '1' : '0')          . "', "  .
                     "pc_recurrspec = '"  . add_escape_custom($recurrspec)               . "', " .
                     "pc_startTime = '"   . add_escape_custom($starttime)                . "', " .
                     "pc_endTime = '"     . add_escape_custom($endtime)                  . "', " .
                     "pc_alldayevent = '" . add_escape_custom($_POST['form_allday'])     . "', " .
                     "pc_apptstatus = '"  . add_escape_custom($_POST['form_apptstatus']) . "', "  .
                     "pc_prefcatid = '"   . add_escape_custom($_POST['form_prefcat'])    . "', "  .
                     "pc_facility = '"    . (int)$_POST['facility']                      . "' "  . // FF stuff
                     "WHERE pc_aid = '"   . add_escape_custom($provider) . "' AND pc_multiple='" . add_escape_custom($row['pc_multiple']) . "'");
            } // foreach

      /* ==========================================
    // multi providers EOS
      ==========================================*/
        } elseif (!$row['pc_multiple']) {
            if ($GLOBALS['select_multi_providers']) {
                $prov = $_POST['form_provider_ae'][0];
            } else {
                $prov =  $_POST['form_provider_ae'];
            }

                // simple provider case
                sqlStatement("UPDATE openemr_postcalendar_events SET " .
                "pc_catid = '"       . add_escape_custom($_POST['form_category'])   . "', " .
                "pc_aid = '"         . add_escape_custom($prov)                     . "', " .
                "pc_pid = '"         . add_escape_custom($_POST['form_pid'])        . "', " .
                "pc_title = '"       . add_escape_custom($_POST['form_title'])      . "', " .
                "pc_time = NOW(), "                                                 .
                "pc_hometext = '"    . add_escape_custom($_POST['form_comments'])   . "', " .
                "pc_informant = '"   . add_escape_custom($_SESSION['providerId'])   . "', " .
                "pc_eventDate = '"   . add_escape_custom($event_date)               . "', " .
                "pc_endDate = '"     . add_escape_custom(fixDate($_POST['form_enddate'])) . "', " .
                "pc_duration = '"    . add_escape_custom(($duration * 60))          . "', " .
                "pc_recurrtype = '"  . ($_POST['form_repeat'] ? '1' : '0')          . "', " .
                "pc_recurrspec = '"  . add_escape_custom($recurrspec)               . "', " .
                "pc_startTime = '"   . add_escape_custom($starttime)                . "', " .
                "pc_endTime = '"     . add_escape_custom($endtime)                  . "', " .
                "pc_alldayevent = '" . add_escape_custom($_POST['form_allday'])     . "', " .
                "pc_apptstatus = '"  . add_escape_custom($_POST['form_apptstatus']) . "', " .
                "pc_prefcatid = '"   . add_escape_custom($_POST['form_prefcat'])    . "', " .
                "pc_facility = '"    . (int)$_POST['facility']                       ."' "  . // FF stuff
                "WHERE pc_eid = '"   . add_escape_custom($eid) . "'");
        }

    // =======================================
    // EOS multi providers case
    // =======================================

      // EVENTS TO FACILITIES

        $e2f = (int)$eid;

    /* =======================================================
  //                                  INSERT EVENTS
    ========================================================*/
    } else {
    // =======================================
    // multi providers case
    // =======================================

        if (is_array($_POST['form_provider_ae'])) {
            // obtain the next available unique key to group multiple providers around some event
            $q = sqlStatement("SELECT MAX(pc_multiple) as max FROM openemr_postcalendar_events");
            $max = sqlFetchArray($q);
            $new_multiple_value = $max['max'] + 1;

            foreach ($_POST['form_provider_ae'] as $provider) {
                sqlInsert("INSERT INTO openemr_postcalendar_events ( " .
                "pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_hometext, " .
                "pc_informant, pc_eventDate, pc_endDate, pc_duration, pc_recurrtype, " .
                "pc_recurrspec, pc_startTime, pc_endTime, pc_alldayevent, " .
                "pc_apptstatus, pc_prefcatid, pc_location, pc_eventstatus, pc_sharing, pc_facility " .
                ") VALUES ( " .
                "'" . add_escape_custom($_POST['form_category'])   . "', " .
                "'" . add_escape_custom($new_multiple_value)       . "', " .
                "'" . add_escape_custom($provider)                 . "', " .
                "'" . add_escape_custom($_POST['form_pid'])        . "', " .
                "'" . add_escape_custom($_POST['form_title'])      . "', " .
                "NOW(), "                                          .
                "'" . add_escape_custom($_POST['form_comments'])   . "', " .
                "'" . add_escape_custom($_SESSION['providerId'])   . "', " .
                "'" . add_escape_custom($event_date)               . "', " .
                "'" . add_escape_custom(fixDate($_POST['form_enddate'])) . "', " .
                "'" . add_escape_custom(($duration * 60))          . "', " .
                "'" . ($_POST['form_repeat'] ? '1' : '0')          . "', " .
                "'" . add_escape_custom($recurrspec)               . "', " .
                "'" . add_escape_custom($starttime)                . "', " .
                "'" . add_escape_custom($endtime)                  . "', " .
                "'" . add_escape_custom($_POST['form_allday'])     . "', " .
                "'" . add_escape_custom($_POST['form_apptstatus']) . "', " .
                "'" . add_escape_custom($_POST['form_prefcat'])    . "', " .
                "'" . add_escape_custom($locationspec)             . "', " .
                "1, " .
                "1, " .(int)$_POST['facility']. " )"); // FF stuff
            } // foreach
        } else {
            $_POST['form_apptstatus'] = '^';
            sqlInsert("INSERT INTO openemr_postcalendar_events ( " .
                "pc_catid, pc_aid, pc_pid, pc_title, pc_time, pc_hometext, " .
                "pc_informant, pc_eventDate, pc_endDate, pc_duration, pc_recurrtype, " .
                "pc_recurrspec, pc_startTime, pc_endTime, pc_alldayevent, " .
                "pc_apptstatus, pc_prefcatid, pc_location, pc_eventstatus, pc_sharing, pc_facility " .
                ") VALUES ( " .
                "'" . add_escape_custom($_POST['form_category'])    . "', " .
                "'" . add_escape_custom($_POST['form_provider_ae']) . "', " .
                "'" . add_escape_custom($_POST['form_pid'])         . "', " .
                "'" . add_escape_custom($_POST['form_title'])       . "', " .
                "NOW(), "                                           .
                "'" . add_escape_custom($_POST['form_comments'])    . "', " .
                "'" . add_escape_custom($_SESSION['providerId'])    . "', " .
                "'" . add_escape_custom($event_date)                . "', " .
                "'" . add_escape_custom(fixDate($_POST['form_enddate'])) . "', " .
                "'" . add_escape_custom(($duration * 60))           . "', " .
                "'" . ($_POST['form_repeat'] ? '1' : '0')           . "', " .
                "'" . add_escape_custom($recurrspec)                . "', " .
                "'" . add_escape_custom($starttime)                 . "', " .
                "'" . add_escape_custom($endtime)                   . "', " .
                "'" . add_escape_custom($_POST['form_allday'])      . "', " .
                "'" . add_escape_custom($_POST['form_apptstatus'])  . "', " .
                "'" . add_escape_custom($_POST['form_prefcat'])     . "', " .
                "'" . add_escape_custom($locationspec)              . "', " .
                "1, " .
                "1, " . (int)$_POST['facility'] . ")"); // FF stuff
        } // INSERT single
    } // else - insert

  // Save new DOB if it's there.
    $patient_dob = trim($_POST['form_dob']);
    if ($patient_dob && $_POST['form_pid']) {
        sqlStatement("UPDATE patient_data SET DOB = ? WHERE " .
        "pid = ?", array($patient_dob, $_POST['form_pid']));
    }

  // Auto-create a new encounter if appropriate.
  //

/*  if ($GLOBALS['auto_create_new_encounters'] &&
    $_POST['form_apptstatus'] == '@' && $event_date == date('Y-m-d'))
*/

// We decided not to auto-create blank enconter when user arrives. Todd's decision 18 Jun 2010
// Applied by Cassian Lup (cassian.lup@clinicdr.com)

    if (0) {
        $tmprow = sqlQuery("SELECT count(*) AS count FROM form_encounter WHERE " .
        "pid = ? AND date = ?", array($_POST['form_pid'], $event_date." 00:00:00"));
        if ($tmprow['count'] == 0) {
              $tmprow = sqlQuery("SELECT username, facility, facility_id FROM users WHERE id = ?", array($_POST['form_provider_ae']));
              $username = $tmprow['username'];
              $facility = $tmprow['facility'];
              $facility_id = $tmprow['facility_id'];
              $conn = $GLOBALS['adodb']['db'];
              $encounter = $conn->GenID("sequences");
              addForm(
                  $encounter,
                  "New Patient Encounter",
                  sqlInsert("INSERT INTO form_encounter SET " .
                  "date = '" . add_escape_custom($event_date) . "', " .
                  "onset_date = '" . add_escape_custom($event_date) . "', " .
                  "reason = '" . add_escape_custom($_POST['form_comments']) . "', " .
                  "facility = '" . add_escape_custom($facility) . "', " .
                  "facility_id = '" . add_escape_custom($facility_id) . "', " .
                  "pid = '" . add_escape_custom($_POST['form_pid']) . "', " .
                  "encounter = '" . add_escape_custom($encounter) . "'"),
                  "newpatient",
                  $_POST['form_pid'],
                  "1",
                  "NOW()",
                  $username
              );
              $info_msg .= "New encounter $encounter was created. ";
        }
    }
} else if ($_POST['form_action'] == "delete") {
      // =======================================
      //  multi providers case
      // =======================================
    if ($GLOBALS['select_multi_providers']) {
        // what is multiple key around this $eid?
        $row = sqlQuery("SELECT pc_multiple FROM openemr_postcalendar_events WHERE pc_eid = ?", array($eid));
        if ($row['pc_multiple']) {
            sqlStatement("DELETE FROM openemr_postcalendar_events WHERE pc_multiple = ?", array($row['pc_multiple']));
        } else {
                        sqlStatement("DELETE FROM openemr_postcalendar_events WHERE pc_eid = ?", array($eid));
        }

        // =======================================
        //  EOS multi providers case
        // =======================================
    } else {
        sqlStatement("DELETE FROM openemr_postcalendar_events WHERE pc_eid = ?", array($eid));
    }
}

if ($_POST['form_action'] != "") {
  // Leave
    $_SESSION['whereto'] = 'appointmentpanel';
    header('Location:./home.php#appointmentpanel');
    exit();
}

 // If we get this far then we are displaying the form.

$statuses = array(
'-' => '',
'*' => xl('* Reminder done'),
'+' => xl('+ Chart pulled'),
'x' => xl('x Cancelled'), // added Apr 2008 by JRM
'?' => xl('? No show'),
'@' => xl('@ Arrived'),
'~' => xl('~ Arrived late'),
'!' => xl('! Left w/o visit'),
'#' => xl('# Ins/fin issue'),
'<' => xl('< In exam room'),
'>' => xl('> Checked out'),
'$' => xl('$ Coding done'),
'^' => xl('^ Pending'),
);

$repeats = 0; // if the event repeats
$repeattype = '0';
$repeatfreq = '0';
$patienttitle = "";
$hometext = "";
$row = array();

// If we are editing an existing event, then get its data.
if ($eid) {
    $row = sqlQuery("SELECT * FROM openemr_postcalendar_events WHERE pc_eid = ?", array($eid));
    $date = $row['pc_eventDate'];
    $userid = $row['pc_aid'];
    $patientid = $row['pc_pid'];
    $starttimeh = substr($row['pc_startTime'], 0, 2) + 0;
    $starttimem = substr($row['pc_startTime'], 3, 2);
    $repeats = $row['pc_recurrtype'];
    $multiple_value = $row['pc_multiple'];

    if (preg_match('/"event_repeat_freq_type";s:1:"(\d)"/', $row['pc_recurrspec'], $matches)) {
        $repeattype = $matches[1];
    }

    if (preg_match('/"event_repeat_freq";s:1:"(\d)"/', $row['pc_recurrspec'], $matches)) {
            $repeatfreq = $matches[1];
    }

    $hometext = $row['pc_hometext'];
    if (substr($hometext, 0, 6) == ':text:') {
        $hometext = substr($hometext, 6);
    }
} else {
    $patientid=$_GET['pid'];
}

// If we have a patient ID, get the name and phone numbers to display.
if ($patientid) {
    $prow = sqlQuery("SELECT lname, fname, phone_home, phone_biz, DOB " .
        "FROM patient_data WHERE pid = ?", array($patientid));
    $patientname = $prow['lname'] . ", " . $prow['fname'];
    if ($prow['phone_home']) {
        $patienttitle .= " H=" . $prow['phone_home'];
    }

    if ($prow['phone_biz']) {
        $patienttitle  .= " W=" . $prow['phone_biz'];
    }
}

// Get the providers list.
$ures = sqlStatement("SELECT id, username, fname, lname FROM users WHERE " .
    "authorized != 0 AND active = 1 AND calendar=1 ORDER BY lname, fname");

//-------------------------------------
//(CHEMED)
//Set default facility for a new event based on the given 'userid'
if ($userid) {
    $pref_facility = sqlFetchArray(sqlStatement("SELECT facility_id, facility FROM users WHERE id = ?", array($userid)));
    $e2f = $pref_facility['facility_id'];
    $e2f_name = $pref_facility['facility'];
}

 //END of CHEMED -----------------------

// Get event categories.
$cres = sqlStatement("SELECT pc_catid, pc_catname, pc_recurrtype, pc_duration, pc_end_all_day " .
"FROM openemr_postcalendar_categories ORDER BY pc_catname");

// Fix up the time format for AM/PM.
$startampm = '1';
if ($starttimeh >= 12) { // p.m. starts at noon and not 12:01
    $startampm = '2';
    if ($starttimeh > 12) {
        $starttimeh -= 12;
    }
}

?>
<html>
<head>

<title><?php echo $eid ? xlt("Edit Event") : xlt("Add New Event"); ?></title>

    <link href="assets/css/style.css?v=<?php echo $v_js_includes; ?>" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js"></script>
</head>

<body class="skin-blue" >
<div class="well">
<form class="form-inline" method='post' name='theaddform' id='theaddform' action='add_edit_event_user.php?eid=<?php echo attr($eid); ?>'>
<input type="hidden" name="form_action" id="form_action" value="">
   <input type='hidden' name='form_category' id='form_category' value='<?php echo $row['pc_catid'] ? attr($row['pc_catid']) : '5'; ?>' />
   <input type='hidden' name='form_apptstatus' id='form_apptstatus' value='<?php echo $row['pc_apptstatus'] ? attr($row['pc_apptstatus']) : "^" ?>' />
<table border='0' width='100%'>
 <tr>
  <td width='1%' nowrap>
   <b><?php echo xlt('Visit'); ?>: </b>
  </td>
  <td nowrap style='padding:0px 5px 5px 0'>
   <input class="form-control" type="text" id='form_title' name='form_title' value='<?php echo ($row['pc_title'] > "") ? attr($row['pc_title']) : xla('Office Visit'); ?>' readonly='readonly'/>
  </td>
  <td></td>
  <td width='1%' nowrap>
    <b><?php echo xlt('Date'); ?>:</b>
  </td>
  <td colspan='2' nowrap id='tdallday1'>
   <input class="form-control" type='text' size='10' name='form_date' readonly id='form_date'
    value='<?php echo (isset($eid) && $eid) ? attr($row['pc_eventDate']) : attr($date); ?>'  />
  </td>
 </tr>
 <tr>
  <td nowrap>
   <b><?php //xl('Title','e'); ?></b>
  </td>
  <td style='padding:0px 5px 5px 0' nowrap>
   <!-- <input class="form-control input-sm" type='text' size='10' name='form_title' readonly value='<?php //echo htmlspecialchars($row['pc_title'],ENT_QUOTES) ?>' title='<?php //xl('Event title','e'); ?>' /> -->
  </td>
  <td nowrap>
  </td>
  <td width='1%' nowrap id='tdallday2'>
   <b><?php echo xlt('Time');?>:</b>
  </td>
  <td width='1%' nowrap id='tdallday3'>
   <input class="form-control inline" type='text' size='2' name='form_hour' value='<?php echo (isset($eid)) ? $starttimeh : ''; ?>'
    title='<?php echo xla('Event start time'); ?>' readonly/> :
  <input class="form-control inline" type='text' size='2' name='form_minute' value='<?php echo (isset($eid)) ? $starttimem : ''; ?>'
    title='<?php echo xla('Event start time'); ?>' readonly/>&nbsp; <!--  -->
   <select class="form-control" name='form_ampm' title='Note: 12:00 noon is PM, not AM' readonly >
    <option value='1'><?php echo xlt('AM'); ?></option>
    <option value='2'<?php echo ($startampm == '2') ? " selected" : ""; ?>><?php echo xlt('PM'); ?></option>
   </select>
  </td>
 </tr>
 <tr>
  <td nowrap>
   <b><?php echo xlt('Patient'); ?>:</b>
  </td>
  <td style='padding:0px 5px 5px 0' nowrap>
   <input class="form-control" type='text' id='form_patient' name='form_patient' value='<?php echo attr($patientname); ?>' title='Patient' readonly />
   <input type='hidden' name='form_pid' value='<?php echo attr($patientid); ?>' />
  </td>
  <td nowrap>
   &nbsp;
  </td>
  <td nowrap id='tdallday4'><?php echo xlt('Duration'); ?></td>
  <td nowrap id='tdallday5'>
  <input class="form-control input-sm" type='text' size='1' name='form_duration' value='<?php echo $row['pc_duration'] ? ($row['pc_duration']*1/60) : "15" ?>' readonly /><?php echo "&nbsp" . xlt('minutes'); ?>
  </td>
 </tr>
    <tr>
    </tr>
 <tr>
  <td nowrap>
   <b><?php echo xlt('Provider'); ?>:</b>
  </td>
  <td style='padding:0px 5px 5px 0' nowrap>
  <select class="form-control" name='form_provider_ae' id='form_provider_ae' onchange='change_provider();'>
<?php
        // present a list of providers to choose from
        // default to the currently logged-in user
while ($urow = sqlFetchArray($ures)) {
    echo "    <option value='" . attr($urow['id']) . "'";
    if (($urow['id'] == $_GET['userid'])||($urow['id']== $userid)) {
        echo " selected";
    }

    echo ">" . text($urow['lname']);
    if ($urow['fname']) {
        echo ", " . text($urow['fname']);
    }

    echo "</option>\n";
}
?>
</select>
  </td>
  <td nowrap style='font-size:8pt'>
  </td>
  <td><input type='button' class='btn btn-danger btn-sm' value='<?php echo xla('Openings');?>' onclick='find_available()' /></td>
  <td></td>
 </tr>
 <tr>
  <td nowrap>
   <b><?php echo xlt('Reason'); ?>:</b>
  </td>
  <td style='padding:0px 5px 5px 0' colspan='4' nowrap>
    <input class="form-control" type='text' size='40' name='form_comments' style='width:100%' value='<?php echo attr($hometext); ?>' title='<?php echo xla('Optional information about this event'); ?>' />
  </td>
 </tr>
</table>
<p>
<input type='button' name='form_save' class='btn btn-success btn-md' onsubmit='return false' value='<?php echo xla('Save'); ?>' onclick="validate()" />
&nbsp;
</p>
</form>
<script>

 var durations = new Array();
<?php
 // Read the event categories, generate their options list, and get
 // the default event duration from them if this is a new event.
 $catoptions = "";
 $prefcat_options = "    <option value='0'>-- None --</option>\n";
 $thisduration = 0;
if ($eid) {
    $thisduration = $row['pc_alldayevent'] ? 1440 : round($row['pc_duration'] / 60);
}

while ($crow = sqlFetchArray($cres)) {
    $duration = round($crow['pc_duration'] / 60);
    if ($crow['pc_end_all_day']) {
        $duration = 1440;
    }

    echo " durations[" . attr($crow['pc_catid']) . "] = " . text($duration) . "\n";
  // echo " rectypes[" . $crow['pc_catid'] . "] = " . $crow['pc_recurrtype'] . "\n";
    $catoptions .= "    <option value='" . attr($crow['pc_catid']) . "'";
    if ($eid) {
        if ($crow['pc_catid'] == $row['pc_catid']) {
            $catoptions .= " selected";
        }
    } else {
        if ($crow['pc_catid'] == $default_catid) {
            $catoptions .= " selected";
            $thisduration = $duration;
        }
    }

    $catoptions .= ">" . text($crow['pc_catname']) . "</option>\n";

  // This section is to build the list of preferred categories:
    if ($duration) {
        $prefcat_options .= "    <option value='" . attr($crow['pc_catid']) . "'";
        if ($eid) {
            if ($crow['pc_catid'] == $row['pc_prefcatid']) {
                $prefcat_options .= " selected";
            }
        }

        $prefcat_options .= ">" . text($crow['pc_catname']) . "</option>\n";
    }
}
?>

<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

 // This is for callback by the find-patient popup.
 function setpatient(pid, lname, fname, dob) {
  var f = document.forms.namedItem("theaddform");
  f.form_patient.value = lname + ', ' + fname;
  f.form_pid.value = pid;
  dobstyle = (dob == '' || dob.substr(5, 10) == '00-00') ? '' : 'none';
  document.getElementById('dob_row').style.display = dobstyle;
 }
 function change_provider(){
  var f = document.forms.namedItem("theaddform");
  f.form_date.value='';
  f.form_hour.value='';
  f.form_minute.value='';
 }
 // This is for callback by the find-patient popup.
 function unsetpatient() {
  var f = document.forms.namedItem("theaddform");
  f.form_patient.value = '';
  f.form_pid.value = '';
 }

 // This invokes the find-patient popup.
 function sel_patient() {
  dlgopen('find_patient_popup.php', '_blank', 500, 400);
 }

 // Do whatever is needed when a new event category is selected.
 // For now this means changing the event title and duration.
 function set_display() {
  var f = document.forms.namedItem("theaddform");
  var si = document.getElementById('form_category');
  if (si.selectedIndex >= 0) {
   var catid = si.options[si.selectedIndex].value;
   var style_apptstatus = document.getElementById('title_apptstatus').style;
   var style_prefcat = document.getElementById('title_prefcat').style;
   if (catid == '2') { // In Office
    style_apptstatus.display = 'none';
    style_prefcat.display = '';
    f.form_apptstatus.style.display = 'none';
    f.form_prefcat.style.display = '';
   } else {
    style_prefcat.display = 'none';
    style_apptstatus.display = '';
    f.form_prefcat.style.display = 'none';
    f.form_apptstatus.style.display = '';
   }
  }
 }

 // Gray out certain fields according to selection of Category DDL
 function categoryChanged() {
    var value = '5';

    document.getElementById("form_patient").disabled=false;
    //document.getElementById("form_apptstatus").disabled=false;
    //document.getElementById("form_prefcat").disabled=false;

 }

 // Do whatever is needed when a new event category is selected.
 // For now this means changing the event title and duration.
 function set_category() {
  var f = document.forms.namedItem("theaddform");
  var s = f.form_category;
  if (s.selectedIndex >= 0) {
   var catid = s.options[s.selectedIndex].value;
   f.form_title.value = s.options[s.selectedIndex].text;
   f.form_duration.value = durations[catid];
   set_display();
  }
 }

 // Modify some visual attributes when the all-day or timed-event
 // radio buttons are clicked.
 function set_allday() {
  var f = document.forms.namedItem("theaddform");
  var color1 = '#777777';
  var color2 = '#777777';
  var disabled2 = true;
  /*if (document.getElementById('rballday1').checked) {
   color1 = '#000000';
  }
  if (document.getElementById('rballday2').checked) {
   color2 = '#000000';
   disabled2 = false;
  }*/
  document.getElementById('tdallday1').style.color = color1;
  document.getElementById('tdallday2').style.color = color2;
  document.getElementById('tdallday3').style.color = color2;
  document.getElementById('tdallday4').style.color = color2;
  document.getElementById('tdallday5').style.color = color2;
  f.form_hour.disabled     = disabled2;
  f.form_minute.disabled   = disabled2;
  f.form_ampm.disabled     = disabled2;
  f.form_duration.disabled = disabled2;
 }

 // Modify some visual attributes when the Repeat checkbox is clicked.
 function set_repeat() {
  var f = document.forms.namedItem("theaddform");
  var isdisabled = true;
  var mycolor = '#777777';
  var myvisibility = 'hidden';
  /*if (f.form_repeat.checked) {
   isdisabled = false;
   mycolor = '#000000';
   myvisibility = 'visible';
  }*/
  //f.form_repeat_type.disabled = isdisabled;
  //f.form_repeat_freq.disabled = isdisabled;
  //f.form_enddate.disabled = isdisabled;
  document.getElementById('tdrepeat1').style.color = mycolor;
  document.getElementById('tdrepeat2').style.color = mycolor;
  document.getElementById('img_enddate').style.visibility = myvisibility;
 }

 // This is for callback by the find-available popup.
 function setappt(year,mon,mday,hours,minutes) {
  var f = document.forms.namedItem("theaddform");
  f.form_date.value = '' + year + '-' +
   ('' + (mon  + 100)).substring(1) + '-' +
   ('' + (mday + 100)).substring(1);
  f.form_ampm.selectedIndex = (hours >= 12) ? 1 : 0;
  f.form_hour.value = (hours > 12) ? hours - 12 : hours;
  f.form_minute.value = ('' + (minutes + 100)).substring(1);
 }

 // Invoke the find-available popup.
 function find_available() {
     // when making an appointment for a specific provider
     var se = document.getElementById('form_provider_ae');
        <?php if ($userid != 0) { ?>
     s = se.value;
        <?php } else {?>
     s = se.options[se.selectedIndex].value;
        <?php }?>
     var formDate = document.getElementById('form_date');
     var url = 'find_appt_popup_user.php?bypatient&providerid=' + s + '&catid=5' + '&startdate=' + formDate.value;
     var params = {
         buttons: [
             {text: '<?php echo xla('Cancel'); ?>', close: true, style: 'danger btn-sm'}

         ],
         allowResize: true,
         dialogId: 'apptDialog',
         type: 'iframe'
     };
     dlgopen(url, 'apptFind', 'modal-md', 300, '', 'Find Date', params);
 }

 // Check for errors when the form is submitted.
 function validate() {
  var f = document.getElementById('theaddform');
  if (!f.form_date.value || !f.form_hour.value || !f.form_minute.value) {
   alert('Please click on "Openings" to select a time.');
   return false;
  }

//  in lunch outofoffice reserved vacation
 // f.form_category.value='2';
  if (f.form_patient.value=='Click to select' && (!(
         f.form_category.value=='2' || f.form_category.value=='8' || f.form_category.value=='3' || f.form_category.value=='4' || f.form_category.value=='11'
     || f.form_category.value=='10'))) {
   alert('Please select a patient.');
   return false;
  } else if (f.form_category.value=='10') {
    unsetpatient();
  }
  var form_action = document.getElementById('form_action');
  form_action.value="save";
  f.submit();
  return false;
 }

 function deleteEvent() {
    if (confirm("Deleting this event cannot be undone. It cannot be recovered once it is gone. Are you sure you wish to delete this event?")) {
        var f = document.getElementById('theaddform');
        var form_action = document.getElementById('form_action');
        form_action.value="delete";
        f.submit();
        return true;
    }
    return false;
 }

<?php if ($eid) { ?>
 set_display();
<?php } ?>

    $(document).ready(function() {
        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = false; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
    });
</script>
</div>
</body>
</html>
