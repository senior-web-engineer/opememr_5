<?php
/*
* Room List
*/

require_once("../globals.php");
require_once("../../library/acl.inc");
require_once("$srcdir/auth.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Services\UserService;
use Twilio\Rest\Client;


$twilio_sid    = "AC6fee688eaec10e6686cafeb2352aa612";
$twilio_token  = "33543e3f51d6346ecf04dce590a7aad0";
$twilio_from = "+17548022619";


if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

if (!empty($_GET)) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

if (!acl_check('admin', 'super')) {
    die(xlt('Access denied'));
}

$mode = 'get_roomlink';
$platform = '';
$room_link = '';
$meetingurl  = "";
$patient_phone = "";
$invite_message = "";

if (isset($_POST["mode"])) {
    $mode = $_POST["mode"];
    if ($_POST["mode"] == "get_roomlink") {
        $user_id = $_POST["user_id"];
        $platform = $_POST["platform"];
        $query = "SELECT * FROM rooms WHERE user_id=$user_id AND platform='$platform'";
        $res = sqlStatement($query);
        $row = sqlFetchArray($res);
        $room_link = "";
        if ($row)
            $room_link = $row['room_link'];

        /*if ($platform == "Twilio") {
            $room = create_room();
            $room_link = $room['url'];
        }*/
        echo $room_link;
    }

    if ($_POST["mode"] == "join_room") {
        $user_id = $_POST["user_id"];
        $platform = $_POST["platform"];
        if ($platform == "Zoom") {
            $query = "SELECT * FROM users WHERE id=$user_id";
            $res = sqlStatement($query);
            $row = sqlFetchArray($res);
            $name = implode(" ", [$row['fname'], $row['mname'], $row['lname']]);
            $un = base64_encode($name);
            $query = "SELECT * FROM rooms WHERE user_id=$user_id AND platform='$platform'";
            $res = sqlStatement($query);
            $row = sqlFetchArray($res);
            if ($row)
                $room_link = $row['room_link'];

            $pattern = preg_quote($room_link, '/');
            preg_match('/\/([0-9]+)\?/', $room_link, $matches);
            if (count($matches) > 0)
                $meetingid = str_replace("/", "", $matches[0]);
                $meetingid = str_replace("?", "", $meetingid);
            if ($meetingid != '') {
                $meetingurl = "https://zoom.us/s/".$meetingid;
                //$meetingurl = "https://zoom.us/wc/".$meetingid."/join?prefer=0&un=".$un;
            }
        }
        if ($platform == "Twilio") {
            $meetingurl = $room_link;
        }
    }

    if ($_POST["mode"] == "invite_room") {
        $patient_phone = $_POST["patient_phone"];
        $room_link = $_POST["invite_roomlink"];
        $invite_message = $_POST["invite_message"];

        //send_sms($patient_phone, $invite_message);
        $_to = $patient_phone;
        if (substr($_to, 0, 1) != '+')
            $_to = '+'.$_to;

        /*
        $client = new Client($twilio_sid, $twilio_token);
        $message = $client->account->messages->create(
            $_to,
            array(
                'from' => $twilio_from,
                'body' => $invite_message
            )
        );
        echo $message;
        */
        $accountSid = $twilio_sid;
        $url = 'https://api.twilio.com/2010-04-01/Accounts/' . $accountSid . '/Messages';
        $data = [
            'From' => $twilio_from,
            'To' => $_to,
            'Body' => $invite_message
        ];
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($curl, CURLOPT_POSTFIELDS,http_build_query($data));
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, $accountSid . ":" . $twilio_token);
        $response = curl_exec($curl);
        curl_close($curl);

//        if ($message)
//            //return $message->sid;
//            echo $message->sid;
//        else
//            echo "false";
    }
}

if (isset($_REQUEST["mode"])) {
    if ($_REQUEST["mode"] == "get_roomlink" || $_REQUEST["mode"] == "invite_room")
        exit();
}

?>

<html>
    <head>
        <title><?php echo xlt('Rooms / Telehealth');?></title>
        <?php Header::setupHeader(['common','jquery-ui']); ?>
        <script type="text/javascript">
            function change_invite(invite_type) {
                var room_link = $("#room_link").val();
                if (room_link == '')
                    return;
                if (invite_type == 'email') {
                    var subject = "Telemedicine meeting invitation";
                    var body = "Hello. \r\n\r\nPlease join me for a secure video call:\r\n\r\n" + room_link;
                    location.href="mailto:?subject=" + encodeURIComponent(subject) + "&body=" + encodeURIComponent(body);
                    return;
                }
                if (invite_type == 'text') {
                    dlgopen('', '', 660, 200, '', '', {
                        type: 'iframe',
                        url: 'invite_text.php?room=' + room_link
                    });
                }
            }

            function roomlink_copy() {
                $("#room_link").select();
                document.execCommand("copy");
            }

            function start_session(room_link) {
                $("#room_link").val(room_link);
                if (room_link.indexOf("zoom.") >= 0 || room_link.indexOf("teams.") >= 0) {
                    window.open(room_link, '_blank');
                }
                else {
                    $("#video-section").attr('src', room_link);
                }
            }

            function change_room() {
                let form_data = $("#telehealth_form").serialize();
                $.ajax({
                    url: '<?php echo $_SERVER['PHP_SELF'];?>',
                    type: 'post',
                    data: form_data
                }).done(function (r) {
                    $("#room_link").val(r);
                });

                var platform = $("#platform").val();
                if (platform == "WebRTC" || platform == "Twilio") {
                    $("#zoom_teams_btn").val(platform);
                    $("#patient-list").show();
                }
                else if (platform == "Teams" || platform == "Zoom") {
                    $("#zoom_teams_btn").val(platform);
                    $("#patient-list").hide();
                }
                else {
                    $("#zoom_teams_btn").val("--Select--");
                    $("#patient-list").show();
                }

                return false;
            }

            function join_room() {
                $("#mode").val('join_room');
                telehealth_form.submit();
            }

            function open_room(){
                var room_link = $("#room_link").val();

                if (room_link == "")
                    return;

                // location.target = '_blank';
                // location.href=room_link;
                window.open(room_link, '_blank');
            }
        </script>
        <style>
            .telehealth-table td {
                padding: 0 5px;
            }
        </style>
    </head>
    <body class="body_top">
        <div class="container">
            <div class="row">
                <div class="col-xs-1"></div>
                <div class="col-xs-10">
                    <div class="page-title">
                        <h2><?php echo xlt('Telehealth Meetings');?></h2>
                    </div>
                </div>
                <div class="col-xs-1"></div>
            </div>
            <form name='telehealth_form' id="telehealth_form" method='post' action="<?php echo $_SERVER['PHP_SELF'];?>">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <input type='hidden' name='mode' id='mode' value='<?php echo $mode; ?>'>
            <input type='hidden' name='user_id' id='user_id' value='<?php echo $_SESSION['authUserID']; ?>'>
            <div class="row">
                <div class="col-xs-12">
                    <div class="table-responsive">
                        <table border="0" class="table">
                            <tr>
                                <td style="width: 65% !important;vertical-align: top;">
                                    <table class="telehealth-table">
                                        <tr>
                                            <td>
                                                <label style="padding-top: 10px;">Room: </label>
                                            </td>
                                            <td>
                                                <select name="platform" id="platform" size="1" class="form-control" onchange="change_room()" style="height:34px;">
                                                    <option value="">--Select--</option>
                                                    <?php
                                                    $query = "SELECT * FROM room_platform ORDER BY priority ASC";
                                                    $res = sqlStatement($query);
                                                    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
                                                        ?>
                                                        <option value="<?php echo $row['platform']; ?>" <?php echo ($platform == $row['platform'])?'selected':''; ?>><?php echo $row['platform'];?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="room_link" id="room_link" readonly style="background:#fff;margin-top:0px" value="<?php echo $room_link; ?>">
                                            </td>
                                            <td>
                                                <input type="button" class="form-control" value="Copy" onclick="javascript:roomlink_copy()" />
                                            </td>
                                            <td>
                                                <select name="invite_mode" size="1" class="form-control" style="height:34px;" onchange="change_invite(this.value)">
                                                    <option value="">Invite via</option>
                                                    <option value="email">Email</option>
                                                    <option value="text">Text</option>
                                                </select>
                                            </td>
                                            <td >
                                                <input type="button" class="form-control" id="zoom_teams_btn" value="--Select--" onclick="open_room()">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="6" style="padding-top: 50px;">
                                                <div>
                                                    <label>Scheduled Meeting</label>
                                                </div>
                                                <table id="meeting_list" name="meeting_list" class="table" style="margin-top: 10px; width: 360px;">
                                                    <thead>
                                                    <tr>
                                                        <th class="text-center" width="50%">Name</th>
                                                        <th class="text-center" width="50%">Meeting Time</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php
                                                    $res = sqlStatement("SELECT b.fname, CONCAT(a.pc_eventDate, ' ', a.pc_startTime) as pc_eventDateTime FROM openemr_postcalendar_events a INNER JOIN patient_data b ON a.pc_pid=b.id WHERE a.pc_aid=? AND DATE(a.pc_eventDate) = CURDATE() ORDER BY a.pc_eventDate, a.pc_startTime, a.pc_endTime ASC ", array($_SESSION['authUserID']));
                                                    while ($row = sqlFetchArray($res)) {
                                                        ?>
                                                        <tr>
                                                            <td class="text-center"><?php echo text($row['fname']) ?></td>
                                                            <td class="text-center"><?php echo text($row['pc_eventDateTime']) ?></td>
                                                        </tr>
                                                        <?php
                                                    }
                                                    ?>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <!--
                            <tr>
                                <td rowspan="2">
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                </td>
                            </tr>
                            -->
                        </table>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </body>
</html>
