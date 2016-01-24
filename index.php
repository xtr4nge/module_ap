<? 
/*
    Copyright (C) 2013-2016 xtr4nge [_AT_] gmail.com

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/ 
?>
<? include "../../header.php"; ?>
<!DOCTYPE HTML>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>FruityWiFi</title>
<script src="../js/jquery.js"></script>
<script src="../js/jquery-ui.js"></script>
<link rel="stylesheet" href="../css/jquery-ui.css" />
<link rel="stylesheet" href="../css/style.css" />
<link rel="stylesheet" href="../../../style.css" />

<script src="includes/scripts.js"></script>

<style>
        .div0 {
                width: 350px;
                margin-top: 2px;
         }
        .div1 {
                width: 120px;
                display: inline-block;
                text-align: left;
                margin-right: 10px;
        }
        .divName {
                width: 200px;
                display: inline-block;
                text-align: left;
                margin-right: 10px;
        }
        .divEnabled {
                width: 63px;
                color: lime;
                display: inline-block;
                font-weight: bold;
        }
        .divDisabled {
                width: 63px;
                color: red;
                display: inline-block;
                font-weight: bold;
        }
        .divAction {
                width: 80px;
                display: inline-block;
                font-weight: bold;
        }
        .divDivision {
                width: 16px;
                display: inline-block;
        }
        .divStartStopB {
                width: 34px;
        }
        
        
        .divBSSID {
                width: 140px;
                display: inline-block;
                text-align: left;
                margin-right: 10px;
        }
        .divSSID {
                w-idth: 140px;
                display: inline-block;
                text-align: left;
                margin-right: 10px;
        }
        
</style>

<script>
// BLOCK 0
$(function() {
    $( "#action" ).tabs();
    $( "#result" ).tabs();
});
</script>

</head>
<body>

<? include "../menu.php"; ?>

<br>

<?
include "../../login_check.php";
include "../../config/config.php";
include "_info_.php";
include "../../functions.php";

// Checking POST & GET variables...
if ($regex == 1) {
    regex_standard($_POST["newdata"], "msg.php", $regex_extra);
    regex_standard($_GET["logfile"], "msg.php", $regex_extra);
    regex_standard($_GET["action"], "msg.php", $regex_extra);
    regex_standard($_POST["service"], "msg.php", $regex_extra);
}

$newdata = $_POST['newdata'];
$logfile = $_GET["logfile"];
$action = $_GET["action"];
$tempname = $_GET["tempname"];
$service = $_POST["service"];

// DELETE LOG
if ($logfile != "" and $action == "delete") {
    $exec = "$bin_rm ".$mod_logs_history.$logfile.".log";
    exec_fruitywifi($exec);
}

?>

<div class="rounded-top" align="left"> &nbsp; <b><?=$mod_alias?></b> </div>
<div class="rounded-bottom">

    &nbsp;version <?=$mod_version?><br>
    
    <?
    $ismoduleup = exec("$mod_isup");
    if ($ismoduleup != "") {
        echo "&nbsp;&nbsp;&nbsp; $mod_alias  <font color='lime'><b>enabled</b></font>.&nbsp; | <a href='includes/module_action.php?service=$mod_name&action=stop&page=module'><b>stop</b></a>";
    } else { 
        echo "&nbsp;&nbsp;&nbsp; $mod_alias  <font color='red'><b>disabled</b></font>. | <a href='includes/module_action.php?service=$mod_name&action=start&page=module'><b>start</b></a>"; 
    }
    ?>
    <br>
    <?
    if ($ap_mode == 1) {
        $mode_name = "Hostapd";
        $log_ap = "";
    } if ($ap_mode == 2) {
        $mode_name = "Airmon-ng";
        $log_ap = "";
    } if ($ap_mode == 3) {
        $mode_name = "Hostapd-Mana";
        $log_ap = "/usr/share/fruitywifi/logs/mana.log";
        
        $exec = "sudo /usr/share/fruitywifi/www/modules/mana/includes/hostapd_cli -p /var/run/hostapd karma_get_state | grep 'ENABLE'";
        $ismoduleup = exec_fruitywifi("$exec");
        if ($ismoduleup[0] == "KARMA ENABLED") {
            echo "&nbsp;&nbsp;&nbsp; Mana  <font color='lime'><b>enabled</b></font>.&nbsp; | <a href='includes/module_action.php?worker=mana&action=stop&page=module'><b>stop</b></a><br>";
        } else { 
            echo "&nbsp;&nbsp;&nbsp; Mana  <font color='red'><b>disabled</b></font>. | <a href='includes/module_action.php?worker=mana&action=start&page=module'><b>start</b></a><br>"; 
        }
        
    } if ($ap_mode == 4) {
        $mode_name = "Hostapd-Karma";
        $log_ap = "/usr/share/fruitywifi/logs/karma.log";
        
        $exec = "/usr/share/fruitywifi/www/modules/karma/includes/hostapd_cli -p /var/run/hostapd karma_get_state | grep 'ENABLE'";
        $ismoduleup = exec_fruitywifi("$exec");
        if ($ismoduleup[0] == "ENABLED") {
            echo "&nbsp;&nbsp; Karma  <font color='lime'><b>enabled</b></font>.&nbsp; | <a href='includes/module_action.php?worker=karma&action=stop&page=module'><b>stop</b></a><br>";
        } else { 
            echo "&nbsp;&nbsp; Karma  <font color='red'><b>disabled</b></font>. | <a href='includes/module_action.php?worker=karma&action=start&page=module'><b>start</b></a><br>"; 
        }
        
    }
    ?>
    <?
    // PICKER
    $ismoduleup = exec("ps auxww | grep -iEe 'ap-picker.py' | grep -v -e grep");
    if ($ismoduleup != "") {
        echo "&nbsp;&nbsp;Picker <font color='lime'><b>enabled</b></font>.&nbsp; | <a href='includes/module_action.php?worker=picker&action=stop&page=module'><b>stop</b></a>";
    } else { 
        echo "&nbsp;&nbsp;Picker <font color='red'><b>disabled</b></font>. | <a href='includes/module_action.php?worker=picker&action=start&page=module'><b>start</b></a>"; 
    }
    ?>
    <br>
    <?
    // SCATTER
    $ismoduleup = exec("ps auxww | grep -iEe 'ap-scatter.py' | grep -v -e grep");
    if ($ismoduleup != "") {
        echo "&nbsp;Scatter <font color='lime'><b>enabled</b></font>.&nbsp; | <a href='includes/module_action.php?worker=scatter&action=stop&page=module'><b>stop</b></a>";
    } else { 
        echo "&nbsp;Scatter <font color='red'><b>disabled</b></font>. | <a href='includes/module_action.php?worker=scatter&action=start&page=module'><b>start</b></a>"; 
    }
    ?>
    <br>
    <?
    // POLITE
    $ismoduleup = exec("ps auxww | grep -iEe 'ap-polite.py' | grep -v -e grep");
    if ($ismoduleup != "") {
        echo "&nbsp;&nbsp;Polite <font color='lime'><b>enabled</b></font>.&nbsp; | <a href='includes/module_action.php?worker=polite&action=stop&page=module'><b>stop</b></a>";
    } else { 
        echo "&nbsp;&nbsp;Polite <font color='red'><b>disabled</b></font>. | <a href='includes/module_action.php?worker=polite&action=start&page=module'><b>start</b></a>"; 
    }
    ?>
    <br>
    <?
    echo "&nbsp;&nbsp;&nbsp; Mode <b>$mode_name</b>";
    ?>
    
</div>

<br>

<div id="msg" style="font-size:largest;">
Loading, please wait...
</div>

<div id="body" style="display:none;">


    <div id="result" class="module">
        <ul>
            <li><a href="#tab-dnsmasq">DNSmasq</a></li>
            <li><a href="#tab-log">Log</a></li>
            <li><a href="#tab-clients">Clients</a></li>
            <li><a href="#tab-config">Config</a></li>
            <li><a href="#tab-filter">Filter</a></li>
            <li><a href="#tab-picker">Picker</a></li>
            <li><a href="#tab-history">History</a></li>
            <li><a href="#tab-about">About</a></li>
        </ul>
        
        <!-- OUTPUT -->

        <div id="tab-dnsmasq">
            <form id="formLogs-Refresh" name="formLogs-Refresh" method="POST" autocomplete="off" action="index.php">
            <input class="btn btn-default btn-sm" type="submit" value="Refresh">
            <br><br>
            <?
                if ($logfile != "" and $action == "view") {
                    $filename = $mod_logs_history.$logfile.".log";
                } else {
                    $filename = $mod_logs;
                }
            
                $data = open_file($filename);
                
                // REVERSE
                //$data_array = explode("\n", $data);
                //$data = implode("\n",array_reverse($data_array));
                
            ?>
            <textarea id="output" class="module-content" style="font-family: courier;"><?=htmlspecialchars($data)?></textarea>
            <input type="hidden" name="type" value="logs">
            </form>
            
        </div>
        
        <!-- END OUTPUT -->
        
        <!-- LOG -->

        <div id="tab-log">
            
            <form id="formLogs-Refresh" name="formLogs-Refresh" method="POST" autocomplete="off" action="index.php">
            <input class="btn btn-default btn-sm" type="submit" value="Refresh">
            <br><br>
            <?
                if ($logfile != "" and $action == "view") {
                    $filename = $mod_logs_history.$logfile.".log";
                } else {
                    $filename = $mod_logs;
                }
            
                $data = open_file($log_ap);
                
                // REVERSE
                //$data_array = explode("\n", $data);
                //$data = implode("\n",array_reverse($data_array));
                
            ?>
            <textarea id="output" class="module-content" style="font-family: courier;"><?=htmlspecialchars($data)?></textarea>
            <input type="hidden" name="type" value="logs">
            </form>
            
        </div>
        
        <!-- END LOG -->
        
        <!-- CLIENTS -->

        <div id="tab-clients" class="history">
            <input class="btn btn-default btn-sm" type="submit" value="Refresh" onclick="cleanStation(); loadStation();">
            <br><br>
            <div>
                <div class='divBSSID'><b>MAC</b>
                </div><div class='div1'><b>Hostname</b>
                </div><div class='div1'><b>IP</b>
                </div><div class='div1'><b>Inactive</b>
                </div><div class='div1'><b>Signal</b>
                </div><div class='div1'><b>rx bytes</b>
                </div><div class='divSSID'><b>tx bytes</b></div>
            </div>
            <div id="station">
                
            </div>
            
        </div>
        
        <!-- END CLIENTS -->
        
        <!-- CONFIG -->

        <div id="tab-config" class="history">
            
            <h4>Karma | Mana</h4>
            Filter Station (Karma and Mana only) 
            <br>
            <div class="btn-group btn-group-sm" data-toggle="buttons">
                <label class="btn btn-default <? if ($mod_filter_karma_station == "none") echo "active" ?>">
                  <input type="radio" name="mod_filter_karma_station" id="none" autocomplete="off" checked> None
                </label>
                <label class="btn btn-default <? if ($mod_filter_karma_station == "whitelist") echo "active" ?>">
                  <input type="radio" name="mod_filter_karma_station" id="whitelist" autocomplete="off"> Whitelist
                </label>
                <label class="btn btn-default <? if ($mod_filter_karma_station == "blacklist") echo "active" ?>">
                  <input type="radio" name="mod_filter_karma_station" id="blacklist" autocomplete="off"> Blacklist
                </label>
            </div>
            
            <br><br>
            
            Filter SSID (Karma only)
            <br>
            <div class="btn-group btn-group-sm" data-toggle="buttons">
                <label class="btn btn-default <? if ($mod_filter_karma_ssid == "none") echo "active" ?>">
                  <input type="radio" name="mod_filter_karma_ssid" id="none" autocomplete="off" <? if ($mod_filter_karma_ssid == "none") echo "checked" ?> > None
                </label>
                <label class="btn btn-default <? if ($mod_filter_karma_ssid == "whitelist") echo "active" ?>">
                  <input type="radio" name="mod_filter_karma_ssid" id="whitelist" autocomplete="off" <? if ($mod_filter_karma_ssid == "whitelist") echo "checked" ?> > Whitelist
                </label>
                <label class="btn btn-default <? if ($mod_filter_karma_ssid == "blacklist") echo "active" ?>">
                  <input type="radio" name="mod_filter_karma_ssid" id="blacklist" autocomplete="off" <? if ($mod_filter_karma_ssid == "blacklist") echo "checked" ?> > Blacklist
                </label>
            </div> 
            
            <hr>
            
            <h4>Scatter</h4>
            
            <div class="btn-group btn-group-sm" data-toggle="buttons">
                <label class="btn btn-default <? if ($mod_filter_scatter_ssid == "none") echo "active" ?>">
                  <input type="radio" name="mod_filter_scatter_ssid" id="none" autocomplete="off" checked> None
                </label>
                <label class="btn btn-default <? if ($mod_filter_scatter_ssid == "whitelist") echo "active" ?>">
                  <input type="radio" name="mod_filter_scatter_ssid" id="whitelist" autocomplete="off"> Whitelist
                </label>
                <label class="btn btn-default <? if ($mod_filter_scatter_ssid == "blacklist") echo "active" ?>">
                  <input type="radio" name="mod_filter_scatter_ssid" id="blacklist" autocomplete="off"> Blacklist
                </label>
            </div>
            
            <br><br>
            
            <input id="filter_scatter_bssid" type="checkbox" name="my-checkbox" <? if ($mod_filter_scatter_bssid == "1") echo "checked"; ?> onclick="setCheckbox(this, 'mod_filter_scatter_bssid')" >
            Rogue BSSID
            <br>
            <input id="scatter_bssid" class="form-control input-sm" placeholder="BSSID" value="<?=$mod_scatter_bssid;?>" style="width: 120px; display: inline-block; " type="text" />
            <input class="btn btn-default btn-sm" type="submit" value="save" onclick="setOption('scatter_bssid', 'mod_scatter_bssid')">

            <br><br>
            
            <input id="filter_scatter_station" type="checkbox" name="my-checkbox" <? if ($mod_filter_scatter_station == "1") echo "checked"; ?> onclick="setCheckbox(this, 'mod_filter_scatter_station')" >
            Target
            <br>
            <input id="scatter_station" class="form-control input-sm" placeholder="Station" value="<?=$mod_scatter_station?>" style="width: 120px; display: inline-block; " type="text" />
            <input class="btn btn-default btn-sm" type="submit" value="save" onclick="setOption('scatter_station', 'mod_scatter_station')">
            
            <hr>
            
            <h4>Polite</h4>
            Filter Station
            <br>
            <div class="btn-group btn-group-sm" data-toggle="buttons">
                <label class="btn btn-default <? if ($mod_filter_polite_station == "none") echo "active" ?>">
                  <input type="radio" name="mod_filter_polite_station" id="none" autocomplete="off" checked> None
                </label>
                <label class="btn btn-default <? if ($mod_filter_polite_station == "whitelist") echo "active" ?>">
                  <input type="radio" name="mod_filter_polite_station" id="whitelist" autocomplete="off"> Whitelist
                </label>
                <label class="btn btn-default <? if ($mod_filter_polite_station == "blacklist") echo "active" ?>">
                  <input type="radio" name="mod_filter_polite_station" id="blacklist" autocomplete="off"> Blacklist
                </label>
            </div>
            
            <br><br>
            
            Filter SSID
            <br>
            <div class="btn-group btn-group-sm" data-toggle="buttons">
                <label class="btn btn-default <? if ($mod_filter_polite_ssid == "none") echo "active" ?>">
                  <input type="radio" name="mod_filter_polite_ssid" id="none" autocomplete="off" checked> None
                </label>
                <label class="btn btn-default <? if ($mod_filter_polite_ssid == "whitelist") echo "active" ?>">
                  <input type="radio" name="mod_filter_polite_ssid" id="whitelist" autocomplete="off"> Whitelist
                </label>
                <label class="btn btn-default <? if ($mod_filter_polite_ssid == "blacklist") echo "active" ?>">
                  <input type="radio" name="mod_filter_polite_ssid" id="blacklist" autocomplete="off"> Blacklist
                </label>
            </div> 
            
            <script>
            $('.btn-default').on('click', function(){
                //alert($(this).find('input').attr('name'));
                //alert($(this).find('input').attr('id'));
                $(this).addClass('active').siblings('.btn').removeClass('active');
                param = ($(this).find('input').attr('name'));
                value = ($(this).find('input').attr('id'));
                //setOption(param, value);
                $.getJSON('../api/includes/ws_action.php?api=/config/module/ap/'+param+'/'+value, function(data) {});
            }); 
            </script>
        
        </div>
        
        <!-- END CONFIG -->
        
        <!-- FILTER -->
        
        <div id="tab-filter" class="history">
            <h4>Filter Stations</h4>
            <select class="module-content" id="pool-station" multiple="multiple" style="width: 265px; height: 150px">

            </select>
            <br>
            <input class="form-control input-sm" placeholder="MAC Address" style="width: 200px; display: inline-block; " id="newMACText" type="text" />
            <input id="add" class="btn btn-default btn-sm" type="submit" value="+" onclick="addListStation();">
            <input id="remove" class="btn btn-default btn-sm" type="submit" value="-" onclick="removeListStation()">
            
            <hr>

            <h4>Filter SSID</h4>
            <select class="module-content" id="pool-ssid" multiple="multiple" style="width: 265px; height: 150px">

            </select>
            <br>
            <input class="form-control input-sm" placeholder="SSID" style="width: 200px; display: inline-block; " id="newSSIDText" type="text" />
            <input id="add-ssid" class="btn btn-default btn-sm" type="submit" value="+" onclick="addListSSID()">
            <input id="remove-ssid" class="btn btn-default btn-sm" type="submit" value="-" onclick="removeListSSID()">
            
        </div>

        <!-- END FILTER -->
        
        <!-- PICKER -->

        <div id="tab-picker" class="history">
            
            <form id="formLogs-Refresh" name="formLogs-Refresh" method="POST" autocomplete="off" action="?tab=6">
            <input class="btn btn-default btn-sm" type="submit" value="Refresh">
            <br><br>
            <?
                $filename = "/usr/share/fruitywifi/conf/ssid.conf";
            
                $data = open_file($filename);
                
                // REVERSE
                //$data_array = explode("\n", $data);
                //$data = implode("\n",array_reverse($data_array));
                
            ?>
            <textarea id="output" class="module-content" style="font-family: courier;"><?=htmlspecialchars($data)?></textarea>
            <input type="hidden" name="type" value="logs">
            </form>
            
        </div>
        
        <!-- END PICKER -->
        
        <!-- HISTORY -->

        <div id="tab-history" class="history">
            <input type="submit" value="refresh">
            <br><br>
            
            <?
            $logs = glob($mod_logs_history.'*.log');
            print_r($a);

            for ($i = 0; $i < count($logs); $i++) {
                $filename = str_replace(".log","",str_replace($mod_logs_history,"",$logs[$i]));
                echo "<a href='?logfile=".str_replace(".log","",str_replace($mod_logs_history,"",$logs[$i]))."&action=delete&tab=7'><b>x</b></a> ";
                echo $filename . " | ";
                echo "<a href='?logfile=".str_replace(".log","",str_replace($mod_logs_history,"",$logs[$i]))."&action=view'><b>view</b></a>";
                echo "<br>";
            }
            ?>
            
        </div>
        
        <!-- END HISTORY -->
        
        <!-- ABOUT -->

        <div id="tab-about" class="history">
            <? include "includes/about.php"; ?>
        </div>

        <!-- END ABOUT -->
        
    </div>

    <div id="loading" class="ui-widget" style="width:100%;background-color:#000; padding-top:4px; padding-bottom:4px;color:#FFF">
        Loading...
    </div>

    <script>
        // BLOCK 2, 3, 4
        //loadingContent()
        $('#loading').hide();
    </script>

    <?
    if ($_GET["tab"] == 1) {
        echo "<script>";
        echo "$( '#result' ).tabs({ active: 0 });";
        echo "</script>";
    } else if ($_GET["tab"] == 2) {
        echo "<script>";
        echo "$( '#result' ).tabs({ active: 1 });";
        echo "</script>";
    } else if ($_GET["tab"] == 3) {
        echo "<script>";
        echo "$( '#result' ).tabs({ active: 2 });";
        echo "</script>";
    } else if ($_GET["tab"] == 4) {
        echo "<script>";
        echo "$( '#result' ).tabs({ active: 3 });";
        echo "</script>";
    } else if ($_GET["tab"] == 5) {
        echo "<script>";
        echo "$( '#result' ).tabs({ active: 4 });";
        echo "</script>";
    } else if ($_GET["tab"] == 6) {
        echo "<script>";
        echo "$( '#result' ).tabs({ active: 5 });";
        echo "</script>";
    } else if ($_GET["tab"] == 7) {
        echo "<script>";
        echo "$( '#result' ).tabs({ active: 6 });";
        echo "</script>";
    } 
    ?>

</div>

<script type="text/javascript">
$(document).ready(function() {
    $('#body').show();
    $('#msg').hide();
});
</script>

<script>
// EXEC LOAD POOL
loadPoolStation()
loadPoolSSID()
loadStation()
</script>

</body>
</html>
