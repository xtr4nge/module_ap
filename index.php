<? 
/*
    Copyright (C) 2013-2018 xtr4nge [_AT_] gmail.com

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
<?
include "../../login_check.php";
?>
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

<script src="includes/scripts.js?<?=time()?>"></script>

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
    $ismoduleup = exec("ps aux | grep dnsmasq | grep -v grep");
    if ($ismoduleup != "") {
        echo "&nbsp;&nbsp;&nbsp; DHCP  <font color='lime'><b>enabled</b></font>.&nbsp;";
    } else { 
        echo "&nbsp;&nbsp;&nbsp; DHCP  <font color='red'><b>disabled</b></font>."; 
    }
    ?>
    <br>
    <?
    if ($mod_dns_type == "fruitydns") {
        $ismoduleup = exec("ps aux | grep dnschef.py | grep -v grep");
    } else {
        $ismoduleup = exec("ps aux | grep dnsmasq | grep -v grep");
    }
    if ($ismoduleup != "") {
        echo "&nbsp;&nbsp;&nbsp;&nbsp; DNS  <font color='lime'><b>enabled</b></font>.&nbsp;";
    } else { 
        echo "&nbsp;&nbsp;&nbsp;&nbsp; DNS  <font color='red'><b>disabled</b></font>."; 
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
        
        $exec = "sudo /usr/share/fruitywifi/www/modules/mana/includes/hostapd_cli -p /var/run/hostapd mana_get_state | tail -1 | grep 'MANA EN'";
        $ismoduleup = exec_fruitywifi("$exec");
        if ($ismoduleup[0] == "MANA ENABLED") {
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
    echo "&nbsp;&nbsp;&nbsp; Mode <b>$mode_name</b>";
    ?>
</div>
<br>
<div class="rounded-bottom">
    <?
    $iface_mon0 = exec("/sbin/ifconfig | grep mon0 ");
    ?>
    <?
    // PICKER
    if ($iface_mon0 != "") {
        $ismoduleup = exec("ps auxww | grep -iEe 'ap-picker.py' | grep -v -e grep");
        if ($ismoduleup != "") {
            echo "&nbsp;&nbsp;Picker <font color='lime'><b>enabled</b></font>.&nbsp; | <a href='includes/module_action.php?worker=picker&action=stop&page=module'><b>stop</b></a>";
        } else { 
            echo "&nbsp;&nbsp;Picker <font color='red'><b>disabled</b></font>. | <a href='includes/module_action.php?worker=picker&action=start&page=module'><b>start</b></a>"; 
        }
    } else {
        echo "&nbsp;&nbsp;Picker [ <a href='../../../page_config_adv.php'>start</a> mon0 ]";
    }
    ?>
    <br>
    <?
    // SCATTER
    if ($iface_mon0 != "") {
        $ismoduleup = exec("ps auxww | grep -iEe 'ap-scatter.py' | grep -v -e grep");
        if ($ismoduleup != "") {
            echo "&nbsp;Scatter <font color='lime'><b>enabled</b></font>.&nbsp; | <a href='includes/module_action.php?worker=scatter&action=stop&page=module'><b>stop</b></a>";
        } else { 
            echo "&nbsp;Scatter <font color='red'><b>disabled</b></font>. | <a href='includes/module_action.php?worker=scatter&action=start&page=module'><b>start</b></a>"; 
        }
    } else {
        echo "&nbsp;Scatter [ <a href='../../../page_config_adv.php'>start</a> mon0 ]";
    }
    ?>
    <br>
    <?
    // POLITE
    if ($iface_mon0 != "") {
        $ismoduleup = exec("ps auxww | grep -iEe 'ap-polite.py' | grep -v -e grep");
        if ($ismoduleup != "") {
            echo "&nbsp;&nbsp;Polite <font color='lime'><b>enabled</b></font>.&nbsp; | <a href='includes/module_action.php?worker=polite&action=stop&page=module'><b>stop</b></a>";
        } else { 
            echo "&nbsp;&nbsp;Polite <font color='red'><b>disabled</b></font>. | <a href='includes/module_action.php?worker=polite&action=start&page=module'><b>start</b></a>"; 
        }
    } else {
        echo "&nbsp;&nbsp;Polite [ <a href='../../../page_config_adv.php'>start</a> mon0 ]";
    }
    ?>
    
</div>

<br>

<div id="msg" style="font-size:largest;">
Loading, please wait...
</div>

<div id="body" style="display:none;">


    <div id="result" class="module">
        <ul>
            <li><a href="#tab-dnsmasq">LogDHCP</a></li>
            <li><a href="#tab-log">LogAP</a></li>
            <li><a href="#tab-clients">Clients</a></li>
            <li><a href="#tab-ap">AP</a></li>
            <li><a href="#tab-dns-config">DHCP-DNS</a></li>
            <li><a href="#tab-filter">Filter</a></li>
            <li><a href="#tab-worker">Worker</a></li>
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
            <textarea id="output" class="module-content" style="font-family: monospace, courier;"><?=htmlspecialchars($data)?></textarea>
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
            <textarea id="output" class="module-content" style="font-family: monospace, courier;"><?=htmlspecialchars($data)?></textarea>
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
        
        <!-- AP -->
        
        <div id="tab-ap" class="history">
            
            <h4>
                <input id="mod_nethunter" type="checkbox" name="my-checkbox" <? if ($mod_nethunter == "1") echo "checked"; ?> onclick="setCheckbox(this, 'mod_nethunter')" >
                NetHunter
            </h4> 
            
            <hr>
            
            <h4>Hostapd</h4>
            
            <h5>
                <span style="width: 70px; display: inline-block;">hw_mode</span>
                <select class="btn btn-default btn-sm" id="ap_hw_mode" onchange="setOptionSelect(this, 'mod_ap_hw_mode')" style="width: 70px">
                    <option <? if ($mod_ap_hw_mode == "a") echo "selected"?> >a</option>
                    <option <? if ($mod_ap_hw_mode == "b") echo "selected"?> >b</option>
                    <option <? if ($mod_ap_hw_mode == "g") echo "selected"?> >g</option>
                </select>
                
                <br>
                
                <span style="width: 70px; display: inline-block;">channel</span>
                <select class="btn btn-default btn-sm" id="ap_channel" onchange="setOptionSelect(this, 'mod_ap_channel')" style="width: 70px">
                    
                </select>
                
                <br>
                
                <span style="width: 70px; display: inline-block;">country</span>
                <select class="btn btn-default btn-sm" id="ap_country_code" onchange="setOptionSelect(this, 'mod_ap_country_code')" style="width: 70px">
                    
                </select>
                
                <!--
                <br><br>
                
                <input id="ap_ht_capab_enabled" type="checkbox" name="my-checkbox" <? if ($mod_ap_ht_capab_enabled == "1") echo "checked"; ?> onclick="setCheckbox(this, 'mod_ap_ht_capab_enabled')" >
                ht_capab
                <br>
                <input id="ap_ht_capab" class="form-control input-sm" placeholder="ht_capab" value="<?=$mod_ap_ht_capab;?>" style="width: 145px; display: inline-block; " type="text" />
                <input class="btn btn-default btn-sm" type="submit" value="save" onclick="setOption('ap_ht_capab', 'mod_ap_ht_capab')">
                -->
                
                <br><br>
                                
                <input id="ap_ht_capab_enabled" type="checkbox" name="my-checkbox" <? if ($mod_ap_ht_capab_enabled == "1") echo "checked"; ?> onclick="setCheckbox(this, 'mod_ap_ht_capab_enabled')" >
                ht_capab
                <br>
                <form action="includes/save.php" method="POST">
                    <input id="ap_ht_capab" name="ap_ht_capab" class="form-control input-sm" placeholder="ht_capab" value="<?=$mod_ap_ht_capab;?>" style="width: 145px; display: inline-block; " type="text" />
                    <input class="btn btn-default btn-sm" type="submit" value="save">
                </form>
                
                <br>
                
                <input id="mod_ap_wme_enabled" type="checkbox" name="my-checkbox" <? if ($mod_ap_wme_enabled == "1") echo "checked"; ?> onclick="setCheckbox(this, 'mod_ap_wme_enabled')" >
                wme_enabled
                <br>
                <input id="mod_ap_wmm_enabled" type="checkbox" name="my-checkbox" <? if ($mod_ap_wmm_enabled == "1") echo "checked"; ?> onclick="setCheckbox(this, 'mod_ap_wmm_enabled')" >
                wmm_enabled
                <br>
                <input id="mod_ap_ieee80211n" type="checkbox" name="my-checkbox" <? if ($mod_ap_ieee80211n == "1") echo "checked"; ?> onclick="setCheckbox(this, 'mod_ap_ieee80211n')" >
                ieee80211n
                
            </h5>
            
            <h5>
                <input id="mod_ap_mana_loud" type="checkbox" name="my-checkbox" <? if ($mod_ap_mana_loud == "1") echo "checked"; ?> onclick="setCheckbox(this, 'mod_ap_mana_loud')" >
                mana_loud (Mana only)
            </h5>
            
            <br>
            
            <!--
            <h4>Blacklist | Whitelist</h4>
            -->
            Filter Station
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
            
            <script>
                var country_code = ["AT","AU","BE","BR","CA","CH","CN","CY","CZ","DE","DK","EE","ES","FI","FR","GB","GR","HK","HU","ID","IE","IL","ILO",
                                    "IN","IS","IT","J1","JP","KE","KR","LT","LU","LV","MY","NL","NO","NZ","PH","PL","PT","SE","SG","SI","SK","TH","TW",
                                    "US","USE","USL","ZA"];
                for(var i in country_code) {
                    if (country_code[i] == "<?=$mod_ap_country_code?>") {
                        $("#ap_country_code").append('<option value='+country_code[i]+' selected>'+country_code[i]+'</option>');
                    } else {
                        $("#ap_country_code").append('<option value='+country_code[i]+'>'+country_code[i]+'</option>');
                    }
                }
                
                var channel_2ghz = ["-",0,1,2,3,4,5,6,7,8,9,10,11,12,13,14];
                var channel_5ghz = ["-",0,36,40,44,48];
                var stored_hw_mode = "<?=$mod_ap_hw_mode?>";
                var stored_channel = "<?=$mod_ap_channel?>";
                
                function setChannel() {
                    hw_mode = $("#ap_hw_mode").val();
                    if (hw_mode == "a") {
                        channels = channel_5ghz;
                    } else {
                        channels = channel_2ghz;
                    }
                    $("#ap_channel").empty();
                    for(var i in channels) {
                        //console.log(i +"|"+channels[i])
                        if (channels[i] == stored_channel) {
                            $("#ap_channel").append('<option value='+channels[i]+' selected>'+channels[i]+'</option>');
                        } else {
                            $("#ap_channel").append('<option value='+channels[i]+'>'+channels[i]+'</option>');
                            //$("#ap_channel").append('<option value='+i+'>'+i+'</option>');
                        }
                    }
                    //console.log($("#ap_channel")[0])
                    //console.log($("#ap_channel").val())
                    setOptionSelect($("#ap_channel")[0], 'mod_ap_channel')
                }
                
                setChannel()
                
                $("#ap_hw_mode").change(function() {
                    //$("#ap_channel").load("textdata/" + $(this).val() + ".txt");
                    setChannel()
                 });
            </script>
            
        </div>

        <!-- END AP -->
        
        <!-- WORKER -->

        <div id="tab-worker" class="history">
            
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
            
        </div>
        
        <!-- END WORKER -->
        
        <!-- DNS-DHCP-CONFIG -->

        <div id="tab-dns-config" class="history">
            <h4>
                <!-- <input id="mod_dhcp" type="checkbox" name="my-checkbox" <? if ($mod_dhcp == "1") echo "checked"; ?> onclick="setCheckbox(this, 'mod_dhcp')" > -->
                DHCP
            </h4>
            
            <?
            $temp = explode(".", $io_in_ip);
            $range = $temp[0].".".$temp[1].".".$temp[2]
            ?>
            LEASE IP (<?=$range?>.xx - <?=$range?>.yy)
            <br>
            <input id="dhcp_lease_start" class="form-control input-sm" placeholder="START" value="<?=$mod_dhcp_lease_start;?>" style="width: 50px; display: inline-block; font-family: monospace; " type="text" />
            -
            <input id="dhcp_lease_end" class="form-control input-sm" placeholder="END" value="<?=$mod_dhcp_lease_end;?>" style="width: 50px; display: inline-block; font-family: monospace; " type="text" />
            <input class="btn btn-default btn-sm" type="submit" value="save" onclick="setLease();">
            <script>
                function setLease() {
                    setOption('dhcp_lease_start', 'mod_dhcp_lease_start');
                    setOption('dhcp_lease_end', 'mod_dhcp_lease_end');
                }
            </script>
            
            <hr>
            
            <h4>
                <!-- <input id="mod_dns" type="checkbox" name="my-checkbox" <? if ($mod_dns == "1") echo "checked"; ?> onclick="setCheckbox(this, 'mod_dns')" > -->
                DNS
            </h4>
            
            <div class="btn-group btn-group-sm" data-toggle="buttons">
                <label class="btn btn-default <? if ($mod_dns_type == "dnsmasq") echo "active" ?>">
                  <input type="radio" name="mod_dns_type" id="dnsmasq" autocomplete="off" checked> DNSmasq
                </label>
                <label class="btn btn-default <? if ($mod_dns_type == "fruitydns") echo "active" ?>">
                  <input type="radio" name="mod_dns_type" id="fruitydns" autocomplete="off"> FruityDNS
                </label>
            </div>
            <!--
            <br><br>
            
            <input id="mod_dns_server" type="checkbox" name="my-checkbox" <? if ($mod_dns_server == "1") echo "checked"; ?> onclick="setCheckbox(this, 'mod_dns_server')" >
            SET DNS SERVER
            <br>
            <input id="dns_server_ip" class="form-control input-sm" placeholder="DNS-SERVERs" value="<?=$mod_dns_server_ip;?>" style="width: 120px; display: inline-block; font-family: monospace " type="text" />
            <input class="btn btn-default btn-sm" type="submit" value="save" onclick="setOption('dns_server_ip', 'mod_dns_server_ip')">
            -->
            <br><br>
            
            <input id="mod_dns_spoof_all" type="checkbox" name="my-checkbox" <? if ($mod_dns_spoof_all == "1") echo "checked"; ?> onclick="setCheckbox(this, 'mod_dns_spoof_all')" >
            SPOOF ALL (option dnsmasq)
            
            <hr>
            <!--
            <input id="mod_dnsmasq_dhcp_script" type="checkbox" name="my-checkbox" <? if ($mod_dnsmasq_dhcp_script == "1") echo "checked"; ?> onclick="setCheckbox(this, 'mod_dnsmasq_dhcp_script')" >
            DNSMASQ SCRIPT
            
            <br>
            -->
            
            <input id="mod_network_manager_stop" type="checkbox" name="my-checkbox" <? if ($mod_network_manager_stop == "1") echo "checked"; ?> onclick="setCheckbox(this, 'mod_network_manager_stop')" >
            NETWORK-MANAGER (stop)
            
            <!--
            <br>
            <input id="dns_spoof_all_ip" class="form-control input-sm" placeholder="BSSID" value="<?=$mod_dns_spoof_all_ip;?>" style="width: 120px; display: inline-block; font-family: monospace " type="text" />
            <input class="btn btn-default btn-sm" type="submit" value="save" onclick="setOption('dns_spoof_all_ip', 'mod_dns_spoof_all_ip')">
            
            <br><br>
            
            <input id="dns_server_option" type="checkbox" name="my-checkbox" <? if ($mod_dns_server_option == "1") echo "checked"; ?> onclick="setCheckbox(this, 'dns_server_option')" >
            SERVER (option dnsmasq)
            -->
        </div>
        
        <!-- END DNS-DHCP-CONFIG -->
        
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
            
            <form id="formLogs-Refresh" name="formLogs-Refresh" method="POST" autocomplete="off" action="?tab=7">
            <input class="btn btn-default btn-sm" type="submit" value="Refresh">
            <br><br>
            <?
                $filename = "/usr/share/fruitywifi/conf/ssid.conf";
            
                $data = open_file($filename);
                
                // REVERSE
                //$data_array = explode("\n", $data);
                //$data = implode("\n",array_reverse($data_array));
                
            ?>
            <textarea id="output" class="module-content" style="font-family: monospace, courier;"><?=htmlspecialchars($data)?></textarea>
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
                echo "<a href='?logfile=".str_replace(".log","",str_replace($mod_logs_history,"",$logs[$i]))."&action=delete&tab=8'><b>x</b></a> ";
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
    } else if ($_GET["tab"] == 8) {
        echo "<script>";
        echo "$( '#result' ).tabs({ active: 7 });";
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

<script>
// EXEC LOAD POOL
loadPoolStation()
loadPoolSSID()
loadStation()
</script>

</body>
</html>
