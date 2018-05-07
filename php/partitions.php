<html>
<head>
<style>
table {
    border-collapse: collapse;
    width: 100%;
}

th, td {
    padding: 8px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

tr:nth-child(even){background-color: #f2f2f2}
</style>
</head>
<body>
<div align='center'><img src='http://mobilemarketingmagazine.com/images/directory/8/upstream.jpg' width="29%" height="24%"></img></div>
<div align='right'>
  <form action="">
    <select name="numb" onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
      <option value="">Unit</option>
      <option value="http://example.com/partitions.php?q=gb">GBs</option>
      <option value="http://example.com/partitions.php?q=mb">MBs</option>
      <option value="http://example.com/partitions.php?q=kb">KBs</option>
    </select>
  </form>
</div>
<table>
  <tr>
    <th>VM</th>
    <th>Partition</th>
    <th>Availability</th>
    <th>Est/Day</th>
  </tr>

<?php

//variables
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'partitions';
$mu = $_GET["q"];

$vms = array();
$part = array();
$available = array();
$diff_avail = array();
$qvms = "SELECT DISTINCT(server) FROM pusage";

// Create connection
$conn = new mysqli($host, $user, $pass);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
} 

mysqli_select_db($conn, $db);

switch($mu) {
  case "gb":
    $mu = pow(1024, 2);
    break;
  case "mb":
    $mu = 1024;
    break;
  case "kb":
    $mu = 1;
    break;
  default:
    $mu = 1;
    break;
}

//Retrieve hostnames from db and push to array
$result = $conn->query($qvms);
while($row = $result->fetch_assoc()) {
  array_push($vms, $row["server"]);
}


foreach ($vms as $vm) {
  //Retrieve partitions foreach hostname
  $result = $conn->query("SELECT DISTINCT(part) FROM pusage WHERE server='$vm'");
  while($row = $result->fetch_assoc()) {
    array_push($part, $row["part"]);
  }

  foreach ($part as $p) {
    //Retrive todays availability
    $today_query = $conn->query("SELECT available FROM pusage WHERE server='$vm' AND part='$p' ORDER BY date DESC LIMIT 1");
    while($row = $today_query->fetch_assoc()) {
      $today = $row["available"];
    }
    //Retrieve availability foreach partition
    $result = $conn->query("SELECT available FROM pusage WHERE server='$vm' AND part='$p' ORDER BY date");
    while($row = $result->fetch_assoc()) {
      array_push($available, $row["available"]);
      //echo $row["available"]." ";
    }
    //var_dump($available);
    for ($i=1; $i<count($available); $i++) {
      $diff = ($available[$i-1] - $available[$i]);
      array_push($diff_avail, $diff);
      //echo " ".$diff_avail[$i-1];
    }
    if (count($diff_avail) !=0){
      $average = array_sum($diff_avail) / count($diff_avail);
      echo "<tr><th>".$vm."</th><th>".$p."</th><th id='numb'>".round($today/$mu, 2)."</th><th id='numb'>".round($average/$mu, 2)."</th></tr>";
    }
    
    //echo "VM: $vm Partition: $p ";
    $available = array(); //initialize array
    $diff_avail = array();
  }
  $part = array();
}
?>
</table>
</body>
