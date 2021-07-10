
 <head>
  <title>Robot</title>
 </head>
 <body>
 <?php 

date_default_timezone_set('Europe/Paris');


 
$today = date('l jS \of F Y h:i:s A');
$alert = -1;
$no_picture = 0;
$motor_state = 0;
$alert_type =array('No alert','Motion','Lux','Temperature','Humidity','Noise');    
$motor_state_type =array('stopped','running');
$obstacle_status_type=array('No Obstacle', 'Left OK', 'Right OK', 'Obstacle', 'Obstacle Left', 'Obstacle Right', 'Obstacle Left and Right'); 
$alert            =(int)$_POST["alert_status"];
$no_picture       =(int)$_POST["no_picture"];
$motor_state      =(int)$_POST["motor_state"];
$direction        =     $_POST["direction"];
$obstacle_status  =(int)$_POST["obstacle_status"];
$distance         =     $_POST["distance"];
$temperature      =(float) $_POST["temperature"]/100;
$humidity         =(float)$_POST["humidity"]/100;
$brightness       =     $_POST["brightness"];
$noise            =     $_POST["noise"];

$msg = " ";
// clé aléatoire de limite
$boundary = md5(uniqid(microtime(), TRUE));

$pictfile= 'PICT'.$no_picture.'.jpg';

$flog = fopen("robotInfos.log","a"); // ouverture du fichier en écriture
fputs($flog, "Start\n");
fputs($flog, "alert: $alert\n");
fputs($flog, "no_picture: $no_picture\n");
fputs($flog, "*************\n");
  
  
if ($alert >= 0){
    //write to file
    $fp = fopen("robotInfos.txt","a"); // ouverture du fichier en écriture
    fputs($fp, "\n"); // on va a la ligne
    fputs($fp, "$today|$alert_type[$alert]|$no_picture|$motor_state|$direction|$obstacle_status|$distance|$temperature|$humidity|$brightness|$noise|\n");
    fclose($fp);

    //write to DB
     $con = mysqli_connect("localhost","edh","edh","ROBOT");

    if (!$con) {
       die('Could not connect: ' . mysqli_connect_error());
    }

    
    $sql = "INSERT into robot_infos (source, alert, no_picture, motor_state, direction, obstacle_status, distance, temperature, humidity, brightness, noise) values (1, '$alert','$no_picture','$motor_state','$direction','$obstacle_status','$distance','$temperature','$humidity','$brightness','$noise')";
    $result = mysqli_query($con, $sql);    
 
    mysqli_close($con);

    
    fputs($flog, "$today|$alert|$no_picture|\n");


    if ($alert > 0){ // alert
        fputs($flog, "call send_alert\n");
        send_alert();
        fputs($flog, "end send_alert\n");
    }

    fclose($flog);
}


function send_alert() {
    
    global $today, $alert, $alert_type, $obstacle_status_type, $no_picture, $pictfile, $msg, $boundary, $flog;
    global $motor_state, $motor_state_type, $direction, $obstacle_status, $distance, $temperature, $humidity, $brightness, $noise;

    fputs($flog, "$today|$alert|$no_picture|\n");
    // clé aléatoire de limite
    $boundary = md5(uniqid(microtime(), TRUE));

    $to = 'eric.delahoupliere@free.fr';
    
    $subject = 'Alert '.$alert_type[$alert].' on '.$today;
    
    // Headers
    $headers  = 'From: robot'."\r\n";
    $headers .= 'Mime-Version: 1.0'."\r\n";
    $headers .= 'Content-Type: multipart/mixed;boundary='.$boundary."\r\n";
    $headers .= "\r\n" ;
    
    // Message
    $msg  = 'This is a multipart/mixed message.'."\r\n\r\n";
    $msg .= '--'.$boundary."\r\n"; 
    $msg .= 'Content-type:text/plain;charset=iso-8859-1'."\r\n";
    $msg .= 'Content-Transfer-Encoding:8bit'."\r\n";


    if ($no_picture > 0)
    {
    // Pièce jointes
       $pictfile= 'PICT'.$no_picture.'.jpg';
       fputs($flog, "$pictfile\n");
       $msg .= "\r\n";
       $msg .= "Hello,\r\n";
       $msg .= "Robot ALERT\r\n";
       $msg .= "Pictures are attached\r\n";
       $msg .= "\r\n";

       add_pictfile();

       if ($no_picture > 1)
       {
           $no_picture = $no_picture-1;
           $pictfile= 'PICT'.$no_picture.'.jpg';
           fputs($flog, "$pictfile\n");
           add_pictfile();
       }

       if ($no_picture > 1)
       {
           $no_picture = $no_picture-1;
           $pictfile= 'PICT'.$no_picture.'.jpg';
           fputs($flog, "$pictfile\n");
           add_pictfile();
       }
    }
    else
    {
       $msg .= "\r\n";
       $msg .= "Hello,\r\n";
       $msg .= "Robot ALERT $alert_type[$alert]\r\n";
       $msg .= "\r\n";
       $msg .= "Motor state: $motor_state_type[$motor_state]\r\n";
       $msg .= "Direction: $direction\xB0\r\n";
       $msg .= "Obstacle status: $obstacle_status_type[$obstacle_status]\r\n";
       $msg .= "Distance: $distance mm\r\n";
       $msg .= "Temperature: $temperature \xB0C\r\n";
       $msg .= "Humidity: $humidity %\r\n";
       $msg .= "Brightness: $brightness\r\n";
       $msg .= "Noise: $noise\r\n";
       $msg .= "\r\n";
    }
 
    // Fin
    $msg .= '--'.$boundary.'--'."\r\n";


    // Function mail()
    fputs($flog, "$to\n");
    fputs($flog, "_______________________________________________________\n");
    fputs($flog, "$subject\n");
    fputs($flog, "_______________________________________________________\n");
    fputs($flog, "$msg\n");
    fputs($flog, "_______________________________________________________\n");
    fputs($flog, "$headers\n");
    fputs($flog, "_______________________________________________________\n");

    if(mail($to, $subject, $msg, $headers))
    {
        fputs ($flog, "send mail OK****\n");
    }
    else
    {
        fputs ($flog, "Send mail KO****\n");
    }
 

} //function send_alert


function add_pictfile() {
    
    global $pictfile, $msg, $boundary, $flog;
    fputs($flog, "pictfile is ");
    fputs($flog, "$pictfile\n");
      
    if (file_exists($pictfile))
    {
	    $file_type = filetype($pictfile);
	    $file_size = filesize($pictfile);
            fputs($flog, "Picture found\n");
            fputs($flog, "$file_type\n");
            fputs($flog, "$file_size\n");

	    $handle = fopen($pictfile, 'r') or die('File '.$pictfile.'can t be open');
	    $content = fread($handle, $file_size);
	    $encoded_content = chunk_split(base64_encode($content));
	    $f = fclose($handle);
 
	    $msg .= '--'.$boundary."\r\n";
	    $msg .= 'Content-type:image/jpeg; name='.$pictfile."\r\n";
	    $msg .= 'Content-transfer-encoding:base64'."\r\n";
            $msg .= 'Content-Disposition: attachment; filename='.$pictfile."\r\n"; 
	    $msg .= "\r\n";
	    $msg .= $encoded_content."\r\n";	     
    }
    else
    {
        fputs($flog, "Picture not found\n");

    }

} //function add_pictfile   
	?>
 </body>
</html>