<?php
//ip route add default dev tun3 via 10.255.0.10 table vpn
$localip = $_SERVER['REMOTE_ADDR'];

//Only allow access from our local network
if(!preg_match('/^192\.168\.13\.(([0-1]?[0-9]?[0-9])|([2][0-4][0-9])|(25[0-5]))$/',$localip))
    return false;

function p($s){/*{{{*/
    echo "<pre>";
    print_r($s);
    echo "</pre>";
}/*}}}*/
function isVpn($ip){/*{{{*/
    exec('ip rule list',$out);
    foreach($out AS $line){
        if(strstr($line,'from '.$_SERVER['REMOTE_ADDR'].' lookup vpn')){
            return substr($line,-3);
        }
    }
    return false;
}/*}}}*/

$sites = array(
        'vaxjo'=> array('default'=> true, 'name'=>'Växjö','active'=> false),
        'vpn'=> array('default'=>false,'name'=>'New York','active'=> false),
    );

if( array_key_exists($_GET['vpn'],$sites) ){
    if($localip == '192.168.13.1')
        return false;

    if($sites[$_GET['vpn']]['default'])
        exec('sudo /usr/local/bin/webaddroute del '.$localip,$out);
    else
        exec('sudo /usr/local/bin/webaddroute add '.$localip,$out);
    header("Location: /");
}
        
        
if( $rt_table = isVpn($localip) ) {
    $sites[$rt_table]['active'] = true;
}
else{
    //no vpn active. Set default site as active
    foreach($sites as $key=>$line){
        if($line['default'])
            $sites[$key]['active'] = true;
    }
}

//p($sites);

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="../../assets/ico/favicon.png">
    <title>VPN Selector</title>
    <link href="/css/bootstrap.css" rel="stylesheet">
  </head>

  <body>

    <div class="container">
      <div class="header">
        <ul class="nav nav-pills pull-right">

        <!--
          <li class="active"><a href="#">Home</a></li>
          <li><a href="#">About</a></li>
          <li><a href="#">Contact</a></li>
        -->
        </ul>
        <h3 class="text-muted">Internet</h3>
      </div>

      <div class="jumbotron">
        <p>Local ip : <?php echo $localip; ?></p>
        <!-- <p>Public ip: <?php echo $localip; ?></p> -->
        <?php
            foreach($sites as $key=>$line){
                if($line['active'])
                    echo '<span style="padding-right:20px;"><a class="btn btn-lg btn-success" href="?vpn='.$key.'">'.$line['name'].'</a></span>';
                else
                    echo '<span style="padding-right:20px;"><a class="btn btn-lg btn-default" href="?vpn='.$key.'">'.$line['name'].'</a></span>';
            }
        ?>
      </div>


      <div class="footer">
        <p>&copy; jonaz 2013</p>
      </div>

    </div> <!-- /container -->


    <script src="//code.jquery.com/jquery.js"></script>
    <script src="js/bootstrap.min.js"></script>

  </body>
</html>

