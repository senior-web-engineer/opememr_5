<?php
/*
* Add Room
*/

require_once("../globals.php");
require_once("../../library/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/erx_javascript.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Menu\MainMenuRole;
use OpenEMR\Menu\PatientMenuRole;

/*
if (!acl_check('admin', 'practice')) {
    exit();
}
*/
$room_userid = "";
$room_platform = "";
$room_link = "";
if (isset($_GET['id']))
{
    $res = sqlStatement("SELECT * from rooms where id=?", array($_GET["id"]));
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
                    $result[$iter] = $row;
    }
    $room = $result[0];
    $room_userid = $room['user_id'];
    $room_platform = $room['platform'];
    $room_link = $room['room_link'];
}

$alertmsg = '';

?>
<html>
    <head>
        <?php Header::setupHeader(['common','opener']); ?>
        <script language="JavaScript">

            function change_platform() {
                var platform = $("#platform").val();
                if (platform == "WebRTC") {
                    $("#generate-roomid").show();
                }else {
                    $("#generate-roomid").hide();
                }
            }

            function generate_roomid() {
                var room_id           = '';
                var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                var charactersLength = characters.length;
                for ( var i = 0; i < 20; i++ ) {
                    room_id += characters.charAt(Math.floor(Math.random() * charactersLength));
                }
                var room_original = $("#roomlink").val();
                room_original = room_original + room_id;
                $("#roomlink").val(room_original);
            }

            function submitform() {
                top.restoreSession();
                alertMsg='';

                let mode = $("#mode").val();
                if (mode != 'delete_room'){
                    f=document.forms[0];
                    for(i=0;i<f.length;i++) {
                        if(f[i].type=='text') {
                            if (f[i].name == 'roomlink') {
                                if (f[i].value == '') {
                                    alertMsg += '<?php echo xl("Invalid link")." " ?>'+'roomlink'.toUpperCase()+"\n";
                                } else {
                                    alertMsg += checkLength('roomlink', f[i].value,512);
                                    alertMsg += checkWebUrl('roomlink', f[i].value);
                                }
                            }
                        }
                    }

                    if(alertMsg) {
                        alert(alertMsg);
                        return false;
                    }
                }

                let post_url = $("#new_room").attr("action");
                let request_method = $("#new_room").attr("method");
                let form_data = $("#new_room").serialize();

                $.ajax({
                    url: post_url,
                    type: request_method,
                    data: form_data
                }).done(function (r) {
                    if (r) {
                        alert(r);
                    } else {
                        dlgclose('reload', false);
                    }
                });

                return false;
            }

            $(function(){
                $("#delete").click(function(){
                    $("#mode").val('delete_room');
                    submitform();
                });
                $("#cancel").click(function() {
                    dlgclose();
                });
            });
        </script>
    </head>
    <body class="body_top">
        <div class="container">
            <table>
                <tr>
                    <td><span class="title"><?php echo xlt('Add Room'); ?></span>&nbsp;</td>
                </tr>
            </table>
            <form name='new_room' id="new_room" method='post' action="room_list.php">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                <input type='hidden' name='mode' id='mode' value='<?php echo (attr($_GET["id"]))?'edit_room':'new_room'; ?>'>
                <INPUT TYPE="hidden" NAME="id" VALUE="<?php echo attr($_GET["id"]); ?>">
                <table border=0>
                    <tr>
                        <td valign=top>
                            <span class="bold">&nbsp;</span>
                            <table border=0 cellpadding=0 cellspacing=0 style="width:600px;">
                                <tr>
                                    <td style="width:150px;"><span class="text"><?php echo xlt('Username'); ?>: </span></td>
                                    <td style="width:220px;">
                                        <select name="user_id" id="user_id" class="form-control" size="1" style="width:220px;">
                                        <?php
                                            if ($_SESSION['authUserID'] == 1)
                                            {
                                                $query = "SELECT * FROM users WHERE active = '1'";
                                            }
                                            else
                                            {
                                                $query = "SELECT * FROM users WHERE id=".$_SESSION['authUserID']." AND active = '1'";
                                            }
                                            $res = sqlStatement($query);
                                            for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
                                        ?>
                                            <option value="<?php echo $row['id']; ?>" <?php echo ($room_userid == $row['id'])?'selected':''; ?>><?php echo $row['fname'] . ' ' . $row['lname']; ?></option>
                                        <?php
                                            }
                                        ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width:150px;"><span class="text"><?php echo xlt('Platform'); ?>: </span></td>
                                    <td style="width:220px;">
                                        <select name="platform" class="form-control" size="1" style="width:220px;" id="platform" onchange="change_platform()">
                                            <option value="">--Select--</option>
                                        <?php
                                            $query = "SELECT * FROM room_platform ORDER BY priority ASC";
                                            $res = sqlStatement($query);
                                            for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
                                        ?>
                                            <option value="<?php echo $row['platform']?>" <?php echo ($room_platform == $row['platform'])?'selected':''; ?>><?php echo $row['platform']?></option>
                                        <?php
                                            }
                                        ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="button" class="btn btn-link btn-save" name="generate-roomid" id="generate-roomid" onclick="javascript:generate_roomid()" style="margin-left:10px;display:none" value="Generate" />
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width:150px;"><span class="text"><?php echo xlt('Room Link'); ?>: </span></td>
                                    <td colspan="2">
                                        <input type="text" name="roomlink" id="roomlink" class="form-control" value="<?php echo $room_link; ?>" style="width:100%">
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <table border=0 cellpadding=0 cellspacing=0 style="width:600px;padding-top:50px;">
                    <tr>
                        <td class="buttonbar">
                            <input type="button" class="btn btn-link btn-save" name='form_save' id='form_save' onclick="return submitform()" value="<?php echo xlt('Save'); ?>">
                            <input type="button" class="btn btn-link btn-cancel" id='cancel' value="<?php echo xlt('Cancel');?>">
                            <input type="button" class="btn btn-link btn-delete" id='delete' onclick="return submitform()" value="<?php echo xlt('Delete');?>">
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </body>
</html>
