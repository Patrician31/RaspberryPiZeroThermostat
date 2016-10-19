<html>
<head>
<meta charset="UTF-8" />
<title>Thermostat</title>
</head>
<body>

<?php

setlocale(LC_CTYPE, "en_US.UTF-8");
// display timestamp
date_default_timezone_set("America/New_York");
echo date("F j, Y, g:i a") . "<br>" . PHP_EOL;
echo "--------------------------------<br>";

$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = '<your sql password>';
$conn = mysql_connect($dbhost, $dbuser, $dbpass);
if(! $conn )
{
  die('Could not connect: ' . mysql_error());
}
mysql_select_db('thermostat');
# get current values
$sql = 'SELECT temp, humidity FROM current WHERE id=1';
$retval = mysql_query( $sql, $conn );
if(! $retval )
{
  die('Could not get data: ' . mysql_error());
}
while($row = mysql_fetch_array($retval, MYSQL_ASSOC))
{
    echo "current temp :{$row['temp']}  <br> ".
         "humidity: {$row['humidity']} <br> ".
         "--------------------------------<br>";
} 
# get target values
$sql = 'SELECT temp FROM target WHERE id=1';
$retval = mysql_query( $sql, $conn );
if(! $retval )
{
  die('Could not get data: ' . mysql_error());
}
while($row = mysql_fetch_array($retval, MYSQL_ASSOC))
{
    echo "target temp :{$row['temp']}  <br> ".
         "--------------------------------<br>";
} 
mysql_close($conn);


# update target value
if (isset($_POST['increase'])) {
    $temp = $temp++;
    $conn = mysql_connect($dbhost, $dbuser, $dbpass);
    if(! $conn )
    {
      die('Could not connect: ' . mysql_error());
    }
    mysql_select_db('thermostat');
    $sql = 'UPDATE target SET temp=temp+1 WHERE id=1';
    $retval = mysql_query( $sql, $conn );
    if(! $retval )
    {
      die('Could not update data: ' . mysql_error());
    }
    mysql_close($conn);
    header("Refresh:0");
}
if (isset($_POST['decrease'])) {
    $temp = $temp++;
    $conn = mysql_connect($dbhost, $dbuser, $dbpass);
    if(! $conn )
    {
      die('Could not connect: ' . mysql_error());
    }
    mysql_select_db('thermostat');
    $sql = 'UPDATE target SET temp=temp-1 WHERE id=1';
    $retval = mysql_query( $sql, $conn );
    if(! $retval )
    {
      die('Could not update data: ' . mysql_error());
    }
    mysql_close($conn);
    header("Refresh:0");
}
# mode control
$mode_set = 0;
$mode = 0;
$mode_str = "off";

$conn = mysql_connect($dbhost, $dbuser, $dbpass);
if(! $conn ) {
  die('Could not connect: ' . mysql_error());
}
mysql_select_db('thermostat');
$sql = 'SELECT mode FROM target WHERE id=1';
$retval = mysql_query( $sql, $conn );
if(! $retval ) {
  die('Could not get data: ' . mysql_error());
}
while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
    #echo "mode is :{$row['mode']}  <br> ";
    $mode = $row['mode'];
} 
if ($mode==0) {
    $mode_str="off";
}
if ($mode==1) {
    $mode_str="fan";
}
if ($mode==2) {
    $mode_str="cooling";
}
if ($mode==3) {
    $mode_str="heating";
}
echo "mode is {$mode_str}<br>";

if (isset($_POST['submit'])) {
  if(isset($_POST['radio'])) {
    echo "setting mode : ".$_POST['radio']."<br>";  //  Displaying Selected Value
    $mode_set = $_POST['radio'];
    $conn = mysql_connect($dbhost, $dbuser, $dbpass);
    if(! $conn ) {
      die('Could not connect: ' . mysql_error());
    }
    mysql_select_db('thermostat');
    $sql = "UPDATE target SET mode={$mode_set} WHERE id=1";
    $retval = mysql_query( $sql, $conn );
    if(! $retval ) {
      die('Could not update data: ' . mysql_error());
    }
    mysql_close($conn);
    header("Refresh:0");
  }
}

?>

<form method="post">
<button name="increase">increase</button> <BR>
<button name="decrease">decrease</button> <BR>
</form>
<form action="" method="post">
  <input type="radio" name="radio" value="0">Off
  <input type="radio" name="radio" value="1">Fan
  <input type="radio" name="radio" value="2">Cooling
  <input type="radio" name="radio" value="3">Heating
  <input type="submit" name="submit" value="set" />
</form>
<br><br>

<br>
</body>
</html>

