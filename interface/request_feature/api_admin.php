<?php
require_once("../globals.php");
if (!acl_check('admin', 'acl')) {
    echo "(" . xlt('ACL Administration Not Authorized') . ")";
    exit;
}

function setDateFilterValue(& $datafield, & $value) {
  if($datafield === 'username') {
    $datafield = 'CONCAT(users.fname, "", users.lname)';
    return false;
  }
  else if($datafield === 'admin_comment_datetime' || $datafield === 'request_datetime') {
    $value = date_create($value);
    $value = date_format($value, "Y-m-d H:i:s");
    if(strpos($value, '12:00:00') !== false) {
      $value = str_replace('12:00:00', '00:00:00', $value);
    }
    else if(strpos($value, '11:59:59') !== false) {
      $value = str_replace('11:59:59', '23:59:59', $value);
    }
  }
  $datafield = "request_features.${datafield}";
}
// Connect to the database
// connection String
$mysqli = new mysqli($sqlconf['host'], $sqlconf['login'], $sqlconf['pass'], $sqlconf['dbase']);
/* check connection */
if (mysqli_connect_errno())
  {
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
  }
// get first visible row.
$firstvisiblerow = $_GET['recordstartindex'];
// get the last visible row.
$lastvisiblerow = $_GET['recordendindex'];
$rowscount = $lastvisiblerow - $firstvisiblerow;
$pagesize = $rowscount;
$start = $firstvisiblerow;
$joinQuery = 'JOIN users ON request_features.user_id = users.id';
$selectQuery = 'request_features.*, users.fname, users.mname, users.lname';
$query = "SELECT SQL_CALC_FOUND_ROWS {$selectQuery} FROM request_features {$joinQuery} LIMIT ?, ?";
$result = $mysqli->prepare($query);
$result->bind_param('ii', $start, $pagesize);
$filterquery = "";

// filter data.
if (isset($_GET['filterscount'])) {
  $filterscount = $_GET['filterscount'];
  if ($filterscount > 0)
    {
    $where = " WHERE (";
    $tmpdatafield = "";
    $tmpfilteroperator = "";
    $valuesPrep = "";
    $value = [];
    for ($i = 0; $i < $filterscount; $i++) {
      // get the filter's value.
      $filtervalue = $_GET["filtervalue" . $i];
      // get the filter's condition.
      $filtercondition = $_GET["filtercondition" . $i];
      // get the filter's column.
      $filterdatafield = $_GET["filterdatafield" . $i];
      // get the filter's operator.
      $filteroperator = $_GET["filteroperator" . $i];
      if ($tmpdatafield == "") {
        $tmpdatafield = $filterdatafield;
      }
      else if ($tmpdatafield <> $filterdatafield) {
        $where.= ") AND (";
      }
      else if ($tmpdatafield == $filterdatafield) {
        if ($tmpfilteroperator == 0) {
          $where.= " AND ";
        }
        else $where.= " OR ";
      }
      setDateFilterValue($filterdatafield, $filtervalue);

      // build the "WHERE" clause depending on the filter's condition, value and datafield.
      switch ($filtercondition) {
        case "CONTAINS":
          $condition = " LIKE ";
          $value[0][$i] = "%{$filtervalue}%";
          $values[] = & $value[0][$i];
          break;

        case "DOES_NOT_CONTAIN":
          $condition = " NOT LIKE ";
          $value[1][$i] = "%{$filtervalue}%";
          $values[] = & $value[1][$i];
          break;

        case "EQUAL":
          $condition = " = ";
          $value[2][$i] = $filtervalue;
          $values[] = & $value[2][$i];
          break;

        case "NOT_EQUAL":
          $condition = " <> ";
          $value[3][$i] = $filtervalue;
          $values[] = & $value[3][$i];
          break;

        case "GREATER_THAN":
          $condition = " > ";
          $value[4][$i] = $filtervalue;
          $values[] = & $value[4][$i];
          break;

        case "LESS_THAN":
          $condition = " < ";
          $value[5][$i] = $filtervalue;
          $values[] = & $value[5][$i];
          break;

        case "GREATER_THAN_OR_EQUAL":
          $condition = " >= ";
          $value[6][$i] = $filtervalue;
          $values[] = & $value[6][$i];
          break;

        case "LESS_THAN_OR_EQUAL":
          $condition = " <= ";
          $value[7][$i] = $filtervalue;
          $values[] = & $value[7][$i];
          break;

        case "STARTS_WITH":
          $condition = " LIKE ";
          $value[8][$i] = "{$filtervalue}%";
          $values[] = & $value[8][$i];
          break;

        case "ENDS_WITH":
          $condition = " LIKE ";
          $value[9][$i] = "%{$filtervalue}";
          $values[] = & $value[9][$i];
          break;

        case "NULL":
          $condition = " IS NULL ";
          $value[10][$i] = "%{$filtervalue}%";
          $values[] = & $value[10][$i];
          break;

        case "NOT_NULL":
          $condition = " IS NOT NULL ";
          $value[11][$i] = "%{$filtervalue}%";
          $values[] = & $value[11][$i];
          break;
      }
      $where.= " " . $filterdatafield . $condition . "? ";
      $valuesPrep = $valuesPrep . "s";
      if ($i == $filterscount - 1)
        {
        $where.= ")";
        }
      $tmpfilteroperator = $filteroperator;
      $tmpdatafield = $filterdatafield;
      }

    $filterquery.= "SELECT SQL_CALC_FOUND_ROWS {$selectQuery} FROM request_features {$joinQuery} " . $where;
    // build the query.
    $valuesPrep = $valuesPrep . "ii";
    $values[] = & $start;
    $values[] = & $pagesize;
    $query = "SELECT SQL_CALC_FOUND_ROWS {$selectQuery} FROM request_features {$joinQuery} " . $where . " LIMIT ?, ?";

    $result = $mysqli->prepare($query);
    call_user_func_array(array(
      $result,
      "bind_param"
    ) , array_merge(array(
      $valuesPrep
    ) , $values));
    }
  }
if (isset($_GET['sortdatafield']))
  {
  $sortfield = $_GET['sortdatafield'];
  $sortorder = $_GET['sortorder'];
  if ($sortorder != '')
    {
    if ($_GET['filterscount'] == 0)
      {
      if ($sortorder == "desc")
        {
        $query = "SELECT SQL_CALC_FOUND_ROWS {$selectQuery} FROM request_features {$joinQuery} ORDER BY" . " " . $sortfield . " DESC LIMIT ?, ?";
        }
        else if ($sortorder == "asc")
        {
        $query = "SELECT SQL_CALC_FOUND_ROWS {$selectQuery} FROM request_features {$joinQuery} ORDER BY" . " " . $sortfield . " ASC LIMIT ?, ?";
        }
      $result = $mysqli->prepare($query);
      $result->bind_param('ii', $start, $pagesize);
      }
      else
      {
      if ($sortorder == "desc")
        {
        $filterquery.= " ORDER BY " . $sortfield . " DESC LIMIT ?, ?";
        }
        else if ($sortorder == "asc")
        {
        $filterquery.= " ORDER BY " . $sortfield . " ASC LIMIT ?, ?";
        }
      // build the query.
      $query = $filterquery;
      $result = $mysqli->prepare($query);
      call_user_func_array(array(
        $result,
        "bind_param"
      ) , array_merge(array(
        $valuesPrep
      ) , $values));
      }
    }
  }
$result->execute();

/* fetch values */
$orders = [];
$result = $result->get_result();
// get data and store in a json array
while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
  $orders[] = array(
    'id' => $row['id'],
    'title' => $row['title'],
    'feature' => $row['feature'],
    'feature_comment' => $row['feature_comment'],
    'request_datetime' => $row['request_datetime'],
    'status' => $row['status'],
    'username' => str_replace("  ", " ", "{$row['fname']} {$row['mname']} {$row['lname']}"),
    'admin_comment' => $row['admin_comment'],
    'admin_comment_datetime' => $row['admin_comment_datetime']
  );
 }
$result = $mysqli->prepare("SELECT FOUND_ROWS()");
$result->execute();
$result->bind_result($total_rows);
$result->fetch();
$data[] = array(
  'TotalRows' => $total_rows,
  'Rows' => $orders
);
// echo '<pre>';
// print_r($data);exit;
echo json_encode($data);
/* close statement */
$result->close();
/* close connection */
$mysqli->close();
