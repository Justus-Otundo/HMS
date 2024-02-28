<?php
/**
 * Created by PhpStorm.
 * User: lmx
 * Date: 2/26/2015
 * Time: 12:27 PM
 */

$GLOBALS['title'] = "Attendance-HMS";
$base_url = "http://localhost/hms/";
$GLOBALS['output'] = '';
$GLOBALS['isData'] = "";
require('./../../inc/sessionManager.php');
require('./../../inc/dbPlayer.php');
require('./../../inc/handyCam.php');

$ses = new \sessionManager\sessionManager();
$ses->start();
$name = $ses->Get("name");
if ($ses->isExpired()) {
    header('Location:' . $base_url . 'login.php');
} else {
    if (isset($_GET['id']) && isset($_GET['wtd'])) {
        $GLOBALS['serial'] = $_GET['id'];
        $db = new \dbPlayer\dbPlayer();
        $msg = $db->open();
        if ($_GET['wtd'] === "delete") {
            if ($msg === true) {
                $result = $db->delete("DELETE FROM attendence WHERE serial='" . $GLOBALS['serial'] . "'");
                if (strpos((string) $result, "Can't") === false) {
                    echo '<script type="text/javascript"> alert("Attendance Deleted Successfully."); window.location.href = "list.php"; </script>';
                } else {
                    echo '<script type="text/javascript"> alert("' . $result . '"); window.location.href = "list.php"; </script>';
                }
            } else {
                echo '<script type="text/javascript"> alert("' . $msg . '"); window.location.href = "list.php"; </script>';
            }
        } else {
            header("location: view.php");
        }
    } elseif (isset($_GET['update'])) {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && $_GET['update'] == "1") {
            if (isset($_POST["btnUpdate"])) {
                if ($ses->Get("serial") !== NULL) {
                    $serialFor = $ses->Get("serial");
                    $db = new \dbPlayer\dbPlayer();
                    $msg = $db->open();
                    if ($msg === true) {
                        $handyCam = new \handyCam\handyCam();
                        $data = array(
                            'isAbsence' => $_POST['isabs'],
                            'isLeave' => $_POST['isLeave'],
                            'remark' => $_POST['remark'],
                        );
                        $result = $db->updateData("attendence", "serial", $serialFor, $data);
                        if ($result === "true") {
                            echo '<script type="text/javascript"> alert("Attendance Updated Successfully."); window.location.href = "list.php"; </script>';
                        } else {
                            echo '<script type="text/javascript"> alert("' . $result . '"); window.location.href = "list.php"; </script>';
                        }
                    } else {
                        echo '<script type="text/javascript"> alert("' . $msg . '"); window.location.href = "list.php"; </script>';
                    }
                } else {
                    echo '<script type="text/javascript"> alert("Please Select attendance from below table!!!"); window.location.href = "list.php"; </script>';
                }
                $ses->remove("serial");
            }
        }
    }
    $name = $ses->Get("loginId");
    $msg = "";
    $db = new \dbPlayer\dbPlayer();
    $msg = $db->open();
    if ($msg === true) {
        $handyCam = new \handyCam\handyCam();
        $data = array();
        $result = $db->getData("SELECT a.serial, b.name, a.date, a.isAbsence, a.isLeave, a.remark FROM attendence AS a, studentinfo AS b WHERE a.userId = b.userId AND b.isActive = 'Y'");
        if ($result == false) {
            $GLOBALS['output'] .= '<div class="table-responsive">
                                    <table id="attendenceList" class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Attend Date</th>
                                                <th>Is Absence</th>
                                                <th>Is Leave</th>
                                                <th>Remark</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>';
            while ($row = mysqli_fetch_array($result)) {
                $GLOBALS['isData'] = "1";
                $GLOBALS['output'] .= "<tr>";
                $GLOBALS['output'] .= "<td>" . $row['name'] . "</td>";
                $GLOBALS['output'] .= "<td>" . $handyCam->getAppDate($row['date']) . "</td>";
                $GLOBALS['output'] .= "<td>" . $row['isAbsence'] . "</td>";
                $GLOBALS['output'] .= "<td>" . $row['isLeave'] . "</td>";
                $GLOBALS['output'] .= "<td>" . $row['remark'] . "</td>";
                $GLOBALS['output'] .= "<td><a title='Edit' class='btn btn-success btn-circle editBtn' href='#" . $row['serial'] . "'><i class='fa fa-pencil'></i></a>&nbsp&nbsp<a title='Delete' class='btn btn-danger btn-circle' href='list.php?id=" . $row['serial'] . "&wtd=delete'" . "><i class='fa fa-trash-o'></i></a></td>";
                $GLOBALS['output'] .= "</tr>";
            }
            $GLOBALS['output'] .= '</tbody>
                                    </table>
                                </div>';
        } else {
            echo '<script type="text/javascript"> alert(" "); window.location="list.php"; </script>';
        }
    } else {
        echo '<script type="text/javascript"> alert("' . $msg . '"); window.location="list.php"; </script>';
    }
}
