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
<?
include "../../../login_check.php";
include "../../../config/config.php";
include "../_info_.php";
include "../../../functions.php";

// Checking POST & GET variables...
if ($regex == 1) {
    regex_standard($_GET["service"], "../msg.php", $regex_extra);
    regex_standard($_GET["action"], "../msg.php", $regex_extra);
    regex_standard($_GET["page"], "../msg.php", $regex_extra);
    regex_standard($_GET["install"], "../msg.php", $regex_extra);
    regex_standard($_GET["worker"], "../msg.php", $regex_extra);
}

$service = $_GET['service'];
$action = $_GET['action'];
$page = $_GET['page'];
$install = $_GET['install'];
$worker = $_GET['worker'];

function setHostapdOption($option, $value, $hostapd_config_file) {
    global $bin_sed;
    
    $is_present = exec("grep -iEe '^#$option=' $hostapd_config_file");
    if ($is_present != "") {
        $exec = "$bin_sed -i 's/#$option=.*/$option=$value/g' $hostapd_config_file";
        exec_fruitywifi($exec);
    } else {
        $is_present = exec("grep -iEe '^$option=' $hostapd_config_file");
        if ($is_present == "") {
            $exec = "echo '\n$option=$value' >> $hostapd_config_file";
            exec_fruitywifi($exec);
        } else {
            $exec = "$bin_sed -i 's/^$option=.*/$option=$value/g' $hostapd_config_file";
            exec_fruitywifi($exec);
        }
    }
    
}

function setHostapd($hostapd_config_file) {
    global $mod_ap_hw_mode;
    global $mod_ap_channel;
    global $mod_ap_country_code;
    global $mod_ap_ieee80211n;
    global $mod_ap_wme_enabled;
    global $mod_ap_wmm_enabled;
    global $mod_ap_ht_capab_enabled;
    global $mod_ap_ht_capab;
    global $bin_sed;
    
    // OPTION: hw_mode
    $option = "hw_mode";
    $value = $mod_ap_hw_mode;
    setHostapdOption($option, $value, $hostapd_config_file);
    
    // OPTION: channel
    // SET CHANNEL 6 IF CHANNEL IS '-'
    if ($mod_ap_channel == "-") {
        $mod_ap_channel = "6";
    }
    
    $option = "channel";
    $value = $mod_ap_channel;
    setHostapdOption($option, $value, $hostapd_config_file);
        
    // OPTION: country_code
    $option = "country_code";
    $value = $mod_ap_country_code;
    setHostapdOption($option, $value, $hostapd_config_file);
    
    // OPTION: ieee80211n
    $option = "ieee80211n";
    $value = "1";
    if ($mod_ap_ieee80211n == "1") {
        setHostapdOption($option, $value, $hostapd_config_file);
    } else {
        $exec = "$bin_sed -i 's/^$option=/#$option=/g' $hostapd_config_file";
        exec_fruitywifi($exec);
    }
    
    // OPTION: wme_enabled
    $option = "wme_enabled";
    $value = "1";
    if ($mod_ap_wme_enabled == "1") {
        setHostapdOption($option, $value, $hostapd_config_file);
    } else {
        $exec = "$bin_sed -i 's/^$option=/#$option=/g' $hostapd_config_file";
        exec_fruitywifi($exec);
    }
    
    // OPTION: wmm_enabled
    $option = "wmm_enabled";
    $value = "1";
    if ($mod_ap_wmm_enabled == "1") {
        setHostapdOption($option, $value, $hostapd_config_file);
    } else {
        $exec = "$bin_sed -i 's/^$option=/#$option=/g' $hostapd_config_file";
        exec_fruitywifi($exec);
    }
    
    // OPTION: ht_capab
    $option = "ht_capab";
    $value = $mod_ap_ht_capab;
    if ($mod_ap_ht_capab_enabled == "1") {
        setHostapdOption($option, $value, $hostapd_config_file);
    } else {
        $exec = "$bin_sed -i 's/^$option=/#$option=/g' $hostapd_config_file";
        exec_fruitywifi($exec);
    }
    
}

function setFruityDNS() {
    global $api_token;
    
    $exec = "curl -sS 'http://localhost:8000/modules/api/includes/ws_action.php?token=$api_token&api=/module/fruitydns/start' > /dev/null &";
    exec($exec);
    //exec_fruitywifi($exec);
    /*
    global $io_in_ip;
    $log_path = "/usr/share/fruitywifi/logs";
    $path = "/usr/share/fruitywifi/www/modules/fruitydns";
    include "$path/_info_.php";
    
    $exec = "$path/includes/dnschef-master/dnschef.py --nameserver=8.8.8.8 --logfile=$mod_logs -i $io_in_ip > /dev/null &";
    exec_fruitywifi($exec);
    */
}

function setDNSmasq() {
    global $bin_echo;
    global $io_in_iface;
    global $io_in_ip;
    global $dnsmasq_domain;
    global $bin_dnsmasq;
    global $bin_sed;
    global $mod_dns_type;
    global $mod_dns_server_option;
    global $mod_dns_spoof_all;
    global $mod_dhcp_lease_start;
    global $mod_dhcp_lease_end;
    global $ap_mode;
    global $mod_dnsmasq_dhcp_script;
    
    $path_dnsmasq_conf = "/usr/share/fruitywifi/conf/dnsmasq.conf";
    $path_resolv= "/usr/share/fruitywifi/conf/resolv.conf";
    
    // SET INTERFACE
    if ($ap_mode != "2") {
        $exec = "$bin_sed -i 's/^interface=.*/interface=$io_in_iface/g' $path_dnsmasq_conf";
        exec_fruitywifi($exec);
    } else {
        // AIRMON-NG MODE [$ap_mode == 2]
        $exec = "$bin_sed -i 's/^interface=.*/interface=at0/g' $path_dnsmasq_conf";
        exec_fruitywifi($exec);
    }
    
    // SET DOMAIN
    $exec = "$bin_sed -i 's/^domain=.*/domain=$dnsmasq_domain/g' $path_dnsmasq_conf";
    exec_fruitywifi($exec);
    
    // SET LEASE
    $temp = explode(".", $io_in_ip);
    $net = $temp[0].".".$temp[1].".".$temp[2];
    $exec = "$bin_sed -i 's/^dhcp-range=.*/dhcp-range=$net.$mod_dhcp_lease_start,$net.$mod_dhcp_lease_end,12h/g' $path_dnsmasq_conf";
    exec_fruitywifi($exec);
    
    // DHCP ASSIGN DNS SERVER
    $is_present = exec("grep -iEe '^dhcp-option=6,' $path_dnsmasq_conf");
    if ($is_present == "") {
        $exec = "echo '\ndhcp-option=6,$io_in_ip' >> $path_dnsmasq_conf";
        exec_fruitywifi($exec);
    } else {
        $exec = "$bin_sed -i 's/^dhcp-option=6,.*/dhcp-option=6,$io_in_ip/g' $path_dnsmasq_conf";
        exec_fruitywifi($exec);
    }
    
    // DNS OPTION (DNSmasq|FruityDNS)
    if ($mod_dns_type == "fruitydns") {
        $is_present = exec("grep -iEe '^#port=' $path_dnsmasq_conf");
        if ($is_present != "") {
            $exec = "$bin_sed -i 's/#port=.*/port=0/g' $path_dnsmasq_conf";
            exec_fruitywifi($exec);
        } else {
            $is_present = exec("grep -iEe '^port=' $path_dnsmasq_conf");
            if ($is_present == "") {
                $exec = "echo '\nport=0' >> $path_dnsmasq_conf";
                exec_fruitywifi($exec);
            }
        }
    } else if ($mod_dns_type == "dnsmasq") {
        $is_present = exec("grep -iEe '^port=' $path_dnsmasq_conf");
        if ($is_present != "") {
            $exec = "$bin_sed -i 's/port=0/#port=0/g' $path_dnsmasq_conf";
            exec_fruitywifi($exec);
        }
    }

    // REMOVE SERVER OPTION
    //if ($mod_dns_server_option == "0") {
        $exec = "$bin_sed -i 's/^server=/#server=/g' $path_dnsmasq_conf";
        exec_fruitywifi($exec);
    //}
    
    // SET RESOLV-FILE OPTION	
    $is_present = exec("grep -iEe '^resolv-file=' $path_dnsmasq_conf");
    if ($is_present == "") {
        $exec = "echo '\nresolv-file=$path_resolv' >> $path_dnsmasq_conf";
        exec_fruitywifi($exec);
    } else {
        $exec = "$bin_sed -i 's/^resolv-file=.*/resolv-file=$path_resolv/g' $path_dnsmasq_conf";
        exec_fruitywifi($exec);
    }
    
    // SET SPOOF ALL
    //#address=/#/$io_in_ip
    if ($mod_dns_spoof_all == "1") {
        $is_present = exec("grep -iEe '^#address=' $path_dnsmasq_conf");
        if ($is_present != "") {
            $exec = "$bin_sed -i 's,^#address=.*,address=/#/$io_in_ip,g' $path_dnsmasq_conf";
            exec_fruitywifi($exec);
        } else {
            $is_present = exec("grep -iEe '^address=' $path_dnsmasq_conf");
            if ($is_present == "") {
                $exec = "echo '\naddress=/#/$io_in_ip' >> $path_dnsmasq_conf";
                exec_fruitywifi($exec);
            }
        }
    } else {
        $exec = "$bin_sed -i 's,^address=.*,#address=/#/$io_in_ip,g' $path_dnsmasq_conf";
        exec_fruitywifi($exec);
    }
    
    $exec = "$bin_echo 'nameserver $io_in_ip\nnameserver 8.8.8.8' > /usr/share/fruitywifi/conf/resolv.conf ";
    exec_fruitywifi($exec);
    
    //$exec = "$bin_echo 'nameserver $io_in_ip\nnameserver 8.8.8.8' > /etc/resolv.conf ";
    //exec_fruitywifi($exec);
    
    //$exec = "chattr +i /etc/resolv.conf";
    //exec_fruitywifi($exec);
    
    // SET DHCP-SCRIPT
    $dnsmasq_dhcp_script_path = "/usr/share/fruitywifi/www/modules/ap/includes/dnsmasq-dhcp-script.sh";
    
    $exec = "chmod 755 $dnsmasq_dhcp_script_path";
    exec_fruitywifi($exec);
    
    if ($mod_dnsmasq_dhcp_script == "1") {
        $is_present = exec("grep -iEe '^#dhcp-script=' $path_dnsmasq_conf");
        if ($is_present != "") {
            $exec = "$bin_sed -i 's,#dhcp-script=.*,dhcp-script=$dnsmasq_dhcp_script_path,g' $path_dnsmasq_conf";
            exec_fruitywifi($exec);
        } else {
            $is_present = exec("grep -iEe '^dhcp-script=' $path_dnsmasq_conf");
            if ($is_present == "") {
                $exec = "echo '\ndhcp-script=$dnsmasq_dhcp_script_path' >> $path_dnsmasq_conf";
                exec_fruitywifi($exec);
            } else {
                $exec = "$bin_sed -i 's,dhcp-script=.*,dhcp-script=$dnsmasq_dhcp_script_path,g' $path_dnsmasq_conf";
                exec_fruitywifi($exec);
            }
        }
    } else {
        $exec = "$bin_sed -i 's/^dhcp-script=/#dhcp-script=/g' $path_dnsmasq_conf";
        exec_fruitywifi($exec);
    }
    
    $exec = "$bin_dnsmasq -C /usr/share/fruitywifi/conf/dnsmasq.conf";
    exec_fruitywifi($exec);
}

function hostapdStationWhitelist($path_hostapd_conf) {
    global $bin_sed;
    global $bin_echo;
    
    $is_present = exec("grep -iEe '^#macaddr_acl' $path_hostapd_conf");
    if ($is_present != "") {
        $exec = "$bin_sed -i 's/#macaddr_acl.*/macaddr_acl=1/g' $path_hostapd_conf";
        exec_fruitywifi($exec);
    } else {
        $is_present = exec("grep -iEe '^macaddr_acl' $path_hostapd_conf");
        if ($is_present == "") {
            $exec = "echo '\nmacaddr_acl=1' >> $path_hostapd_conf";
            exec_fruitywifi($exec);
        }
    }
    
    $is_present = exec("grep -iEe '^#accept_mac_file' $path_hostapd_conf");
    if ($is_present != "") {
        $exec = "$bin_sed -i 's,#accept_mac_file.*,accept_mac_file=/usr/share/fruitywifi/conf/pool-station.conf,g' $path_hostapd_conf";
        exec_fruitywifi($exec);
    } else {
        $is_present = exec("grep -iEe '^accept_mac_file' $path_hostapd_conf");
        if ($is_present == "") {
            $exec = "echo '\naccept_mac_file=/usr/share/fruitywifi/conf/pool-station.conf' >> $path_hostapd_conf";
            exec_fruitywifi($exec);
        }
    }

}

function hostapdStationBlacklist($path_hostapd_conf) {
    global $bin_sed;
    global $bin_echo;
    
    $is_present = exec("grep -iEe '^#macaddr_acl' $path_hostapd_conf");
    if ($is_present != "") {
        $exec = "$bin_sed -i 's/#macaddr_acl.*/macaddr_acl=0/g' $path_hostapd_conf";
        exec_fruitywifi($exec);
    } else {
        $is_present = exec("grep -iEe '^macaddr_acl' $path_hostapd_conf");
        if ($is_present == "") {
            $exec = "echo '\nmacaddr_acl=0' >> $path_hostapd_conf";
            exec_fruitywifi($exec);
        }
    }
    
    $is_present = exec("grep -iEe '^#deny_mac_file' $path_hostapd_conf");
    if ($is_present != "") {
        $exec = "$bin_sed -i 's,#deny_mac_file.*,deny_mac_file=/usr/share/fruitywifi/conf/pool-station.conf,g' $path_hostapd_conf";
        exec_fruitywifi($exec);
    } else {
        $is_present = exec("grep -iEe '^deny_mac_file' $path_hostapd_conf");
        if ($is_present == "") {
            $exec = "echo '\ndeny_mac_file=/usr/share/fruitywifi/conf/pool-station.conf' >> $path_hostapd_conf";
            exec_fruitywifi($exec);
        }
    }
}

function flushIptables() {	
    global $bin_iptables;
    
    $exec = "$bin_iptables -F";
    exec_fruitywifi($exec);
    $exec = "$bin_iptables -t nat -F";
    exec_fruitywifi($exec);
    $exec = "$bin_iptables -t mangle -F";
    exec_fruitywifi($exec);
    $exec = "$bin_iptables -X";
    exec_fruitywifi($exec);
    $exec = "$bin_iptables -t nat -X";
    exec_fruitywifi($exec);
    $exec = "$bin_iptables -t mangle -X";
    exec_fruitywifi($exec);
    //echo $exec;
}

function flushIptablesNetHunter() {
    global $bin_iptables;
    
    $exec = "$bin_iptables -F";
    exec_fruitywifi($exec);
    $exec = "$bin_iptables -F -t nat";
    exec_fruitywifi($exec);
}

function setInIfaceDown() {
    global $io_in_iface;
    global $bin_ifconfig;
    
    /*
    $exec = "$bin_ifconfig $io_in_iface down";
    exec_fruitywifi($exec);
    $exec = "$bin_ifconfig $io_in_iface 0.0.0.0";
    exec_fruitywifi($exec);
    */
    
    $exec = "ip addr flush dev $io_in_iface";
    exec_fruitywifi($exec);
    
    $exec = "$bin_ifconfig $io_in_iface down";
    exec_fruitywifi($exec);
    
    $exec = "$bin_ifconfig $io_in_iface 0.0.0.0";
    exec_fruitywifi($exec);
}

function setInIfaceUP() {
    global $io_in_iface;
    global $io_out_iface;
    global $io_in_ip;
    
    /*
    $exec = "$bin_ifconfig $io_in_iface up";
    exec_fruitywifi($exec);
    $exec = "$bin_ifconfig $io_in_iface up $io_in_ip netmask 255.255.255.0";
    exec_fruitywifi($exec);
    */
    
    $exec = "rfkill unblock wlan";
    exec_fruitywifi($exec);
    
    $exec = "ip addr flush dev $io_in_iface";
    exec_fruitywifi($exec);
    
    $exec = "ip addr add $io_in_ip/24 dev $io_in_iface";
    exec_fruitywifi($exec);
    
    $exec = "ip link set $io_in_iface up";
    exec_fruitywifi($exec);
    
    $exec = "ip route add default via $io_in_ip dev $io_in_iface";
    exec_fruitywifi($exec);
}

function setIptablesSave() {
    global $bin_iptables;
    
    $exec = "$bin_iptables -F bw_INPUT";
    exec_fruitywifi($exec);
    $exec = "$bin_iptables -F bw_OUTPUT";
    exec_fruitywifi($exec);
    
    $exec = "iptables-save > backup-rules.txt";
    exec_fruitywifi($exec);
}

function setIptablesRestore() {
    global $bin_iptables;
    
    $exec = "iptables-restore < backup-rules.txt";
    exec_fruitywifi($exec);
}

function setForwarding() {
    global $io_in_iface;
    global $io_out_iface;
    global $bin_iptables;
    
    /*
    $exec = "$bin_echo 1 > /proc/sys/net/ipv4/ip_forward";
    exec_fruitywifi($exec);
    $exec = "$bin_iptables -t nat -A POSTROUTING -o $io_out_iface -j MASQUERADE";
    exec_fruitywifi($exec);
    */
    
    $exec = "$bin_iptables -t nat -A POSTROUTING -o $io_out_iface -j MASQUERADE";
    exec_fruitywifi($exec);
    $exec = "$bin_iptables -A FORWARD -i $io_in_iface -o $io_out_iface -j ACCEPT";
    exec_fruitywifi($exec);
    
    $exec = "echo '1' > /proc/sys/net/ipv4/ip_forward";
    exec_fruitywifi($exec);
}

function setNetHunter() {
    global $io_in_iface;
    global $io_out_iface;
    global $io_in_ip;
    
    $exec = "ip rule list | awk -F'lookup' '{print \\$2}'";
    $out_table = exec_fruitywifi($exec);
    
    foreach ($out_table as $table) {
        $exec = "ip route show table $table|grep default|grep $io_out_iface";
        $out_rule = exec_fruitywifi($exec);
        
        foreach ($out_rule as $rule) {
            $flag = True;
            break;
        }
        if ($flag) {
            $exec = "ip route add ".substr($io_in_ip, 0, -2).".0/24 dev $io_in_iface scope link table $table";
            exec_fruitywifi($exec);
            break;
        }
    }
}

function setNetworkManager() {
    
    global $io_in_iface;
    global $bin_sed;
    global $bin_echo;
    global $mod_network_manager_stop;
    
    if ($mod_network_manager_stop == "1") {
        $exec = "/etc/init.d/network-manager stop";
        exec_fruitywifi($exec);
    } else {
        
        $exec = "macchanger --show $io_in_iface |grep 'Permanent'";
        exec($exec, $output);
        $mac = explode(" ", $output[0]);
        
        $exec = "grep '^unmanaged-devices' /etc/NetworkManager/NetworkManager.conf";
        $ispresent = exec($exec);
        
        $exec = "$bin_sed -i '/unmanaged/d' /etc/NetworkManager/NetworkManager.conf";
        exec_fruitywifi($exec);
        $exec = "$bin_sed -i '/\[keyfile\]/d' /etc/NetworkManager/NetworkManager.conf";
        exec_fruitywifi($exec);
        
        if ($ispresent == "") {
            $exec = "$bin_echo '[keyfile]' >> /etc/NetworkManager/NetworkManager.conf";
            exec_fruitywifi($exec);
    
            $exec = "$bin_echo 'unmanaged-devices=mac:".$mac[2].";interface-name:".$io_in_iface."' >> /etc/NetworkManager/NetworkManager.conf";
            exec_fruitywifi($exec);
        }
    }
    
}

function cleanNetworkManager() {
    
    global $bin_sed;
    
    // REMOVE lines from NetworkManager
    $exec = "$bin_sed -i '/unmanaged/d' /etc/NetworkManager/NetworkManager.conf";
    exec_fruitywifi($exec);
    $exec = "$bin_sed -i '/\[keyfile\]/d' /etc/NetworkManager/NetworkManager.conf";
    exec_fruitywifi($exec);
}

function killRegex($regex){
    
    $exec = "ps aux|grep -E '$regex' | grep -v grep | awk '{print $2}'";
    exec($exec,$output);
    
    if (count($output) > 0) {
        $exec = "kill " . $output[0];
        exec_fruitywifi($exec);
    }
    
}

function copyLogsHistory() {
    
    global $bin_cp;
    global $bin_mv;
    global $mod_logs;
    global $mod_logs_history;
    global $bin_echo;
    
    if ( 0 < filesize( $mod_logs ) ) {
        $exec = "$bin_cp $mod_logs $mod_logs_history/".gmdate("Ymd-H-i-s").".log";
        exec_fruitywifi($exec);
        
        $exec = "$bin_echo '' > $mod_logs";
        exec_fruitywifi($exec);
    }
}

function poolStation($bin_hostapd_cli, $mode) {
    $data = open_file("/usr/share/fruitywifi/conf/pool-station.conf");
    $out = explode("\n", $data);
    
    for ($i=0; $i < count($out); $i++) {
        if ($out[$i] != "") {
            $exec = "$bin_hostapd_cli -p /var/run/hostapd $mode " . trim($out[$i]);
            exec_fruitywifi($exec);
        }
    }
}

function poolSSID($bin_hostapd_cli, $mode) {
    $data = open_file("/usr/share/fruitywifi/conf/pool-ssid.conf");
    $out = explode("\n", $data);
    
    for ($i=0; $i < count($out); $i++) {
        if ($out[$i] != "") {
            $exec = "$bin_hostapd_cli -p /var/run/hostapd $mode " . trim($out[$i]);
            exec_fruitywifi($exec);
        }
    }
}

function getMAC($iface) {
    $exec = "macchanger --show $iface |grep 'Permanent'";
    exec($exec, $output);
    $mac = explode(" ", $output[0]);
    return $mac[2];
}

function checkPool() {
    $filename = '/usr/share/fruitywifi/conf/pool-station.conf';
    if (!file_exists($filename)) {
        $exec = "touch $filename";
        exec_fruitywifi($exec);
    }
    
    $filename = '/usr/share/fruitywifi/conf/pool-ssid.conf';
    if (!file_exists($filename)) {
        $exec = "touch $filename";
        exec_fruitywifi($exec);
    }
    
    $filename = '/usr/share/fruitywifi/conf/ssid.conf';
    if (!file_exists($filename)) {
        $exec = "touch $filename";
        exec_fruitywifi($exec);
    }
}

# scatter: spoof-ssid.py
if ($worker == "scatter") {
    if ($action == "start") {
        $opt = "";
        
        if ($mod_filter_scatter_bssid == "1") {
            $opt .= " -b $mod_scatter_bssid";
        } else {
            $opt .= " -b " . getMAC($io_in_iface);
        }
        
        if ($mod_filter_scatter_station == "1") $opt .= " -s $mod_scatter_station";
        
        //$exec = "python ap-scatter.py -i mon0 -b $mod_scatter_bssid > /dev/null &";
        $exec = "python ap-scatter.py -i mon0 $opt -e $mod_filter_scatter_ssid > /dev/null &";
        exec_fruitywifi($exec);
    } else if ($action == "stop") {
        killRegex("ap-scatter.py");
        killRegex("ap-scatter.py");
    }
}

# picker: scan-ssid.py
if ($worker == "picker") {
    if ($action == "start") {
        $exec = "python ap-picker.py -i mon0 > /dev/null &";
        exec_fruitywifi($exec);
    } else if ($action == "stop") {
        killRegex("ap-picker.py");
        killRegex("ap-picker.py");
    }
}

# polite: spoof-response.py
if ($worker == "polite") {
    if ($action == "start") {
        
        if ($mod_filter_scatter_bssid == "1") {
            $opt .= " -b $mod_scatter_bssid";
        } else {
            $opt .= " -b " . getMAC($io_in_iface);
        }
        
        //$exec = "python ap-polite.py -i mon0 -s $mod_filter_polite_station -e $mod_filter_polite_ssid -b $use_bssid  > /dev/null &";
        $exec = "python ap-polite.py -i mon0 -s $mod_filter_polite_station -e $mod_filter_polite_ssid $opt  > /dev/null &";
        exec_fruitywifi($exec);
    } else if ($action == "stop") {
        killRegex("ap-polite.py");
        killRegex("ap-polite.py");
    }
}

if ($worker == "karma") {
    if ($action == "start") {
        $exec = "/usr/share/fruitywifi/www/modules/karma/includes/hostapd_cli -p /var/run/hostapd karma_enable";
        exec_fruitywifi($exec);
    } else if ($action == "stop") {
        $exec = "/usr/share/fruitywifi/www/modules/karma/includes/hostapd_cli -p /var/run/hostapd karma_disable";
        exec_fruitywifi($exec);
    }
}

if ($worker == "mana") {
    if ($action == "start") {
        $exec = "/usr/share/fruitywifi/www/modules/mana/includes/hostapd_cli -p /var/run/hostapd karma_enable";
        exec_fruitywifi($exec);
    } else if ($action == "stop") {
        $exec = "/usr/share/fruitywifi/www/modules/mana/includes/hostapd_cli -p /var/run/hostapd karma_disable";
        exec_fruitywifi($exec);
    }
}

// HOSTAPD
if($service != "" and $ap_mode == "1") {
    if ($action == "start") {
        
        // SAVE IPTABLES
        setIptablesSave();
        
        // CHECK FOR POOL FILES
        checkPool();
        
        // SETUP NetworkManager
        setNetworkManager();
        
        // IN IFACE DOWN
        setInIfaceDown();
        
        $exec = "$bin_killall hostapd";	
        exec_fruitywifi($exec);

        killRegex("hostapd");
        
        $exec = "$bin_rm /var/run/hostapd/$io_in_iface";
        exec_fruitywifi($exec);

        $exec = "$bin_killall dnsmasq";
        exec_fruitywifi($exec);

        killRegex("dnsmasq");
        killRegex("dnschef.py");
        
        // IN IFACE UP
        setInIfaceUp();
        
        /*
        $exec = "$bin_echo 'nameserver $io_in_ip\nnameserver 8.8.8.8' > /etc/resolv.conf ";
        exec_fruitywifi($exec);
        
        $exec = "chattr +i /etc/resolv.conf";
        exec_fruitywifi($exec);
        
        $exec = "$bin_dnsmasq -C /usr/share/fruitywifi/conf/dnsmasq.conf";
        exec_fruitywifi($exec);
        */
        
        // SET HOSTAPD CONF
        if ($hostapd_secure == 1) {
            // BLACKLIST|WHITELIST PATH
            $path_hostapd_conf = "/usr/share/fruitywifi/conf/hostapd-secure.conf";
        } else {
            // BLACKLIST|WHITELIST PATH
            $path_hostapd_conf = "/usr/share/fruitywifi/conf/hostapd.conf";
        }
        
        // SET HOSTAPD [BLACK|WHITE]
        $exec = "$bin_sed -i 's/^macaddr_acl.*/#macaddr_acl=0/g' $path_hostapd_conf";
        exec_fruitywifi($exec);
        $exec = "$bin_sed -i 's,^accept_mac_file.*,#accept_mac_file=/usr/share/fruitywifi/conf/pool-station.conf,g' $path_hostapd_conf";
        exec_fruitywifi($exec);
        $exec = "$bin_sed -i 's,^deny_mac_file.*,#deny_mac_file=/usr/share/fruitywifi/conf/pool-station.conf,g' $path_hostapd_conf";
        exec_fruitywifi($exec);
        
        if ($mod_filter_karma_station == "blacklist") {
            hostapdStationBlacklist($path_hostapd_conf);
        } else if ($mod_filter_karma_station == "whitelist") {
            hostapdStationWhitelist($path_hostapd_conf);
        }
        
        //Verifies if karma-hostapd is installed
        if ($hostapd_secure == 1) {
            
            $hostapd_config_file = "/usr/share/fruitywifi/conf/hostapd-secure.conf";
            
            // SET HOSTAPD
            setHostapd($hostapd_config_file);
            
            //REPLACE SSID
            $exec = "$bin_sed -i 's/^ssid=.*/ssid=".$hostapd_ssid."/g' /usr/share/fruitywifi/conf/hostapd-secure.conf";
            exec_fruitywifi($exec);
            
            //REPLACE IFACE                
            $exec = "$bin_sed -i 's/^interface=.*/interface=".$io_in_iface."/g' /usr/share/fruitywifi/conf/hostapd-secure.conf";
            exec_fruitywifi($exec);
            
            //REPLACE WPA_PASSPHRASE
            $exec = "$bin_sed -i 's/wpa_passphrase=.*/wpa_passphrase=".$hostapd_wpa_passphrase."/g' /usr/share/fruitywifi/conf/hostapd-secure.conf";
            exec_fruitywifi($exec);
            
            //EXTRACT MACADDRESS
            unset($output);
            $output = getIfaceMAC($io_in_iface);
            
            //REPLACE MAC
            $exec = "$bin_sed -i 's/^bssid=.*/bssid=".$output."/g' /usr/share/fruitywifi/conf/hostapd-secure.conf";
            exec_fruitywifi($exec);
            
            $exec = "/usr/sbin/hostapd -P /var/run/hostapd -B /usr/share/fruitywifi/conf/hostapd-secure.conf";
        } else {
            
            $hostapd_config_file = "/usr/share/fruitywifi/conf/hostapd.conf";
            
            // SET HOSTAPD
            setHostapd($hostapd_config_file);
            
            //REPLACE SSID
            $exec = "$bin_sed -i 's/^ssid=.*/ssid=".$hostapd_ssid."/g' /usr/share/fruitywifi/conf/hostapd.conf";
            exec_fruitywifi($exec);
            
            //REPLACE IFACE                
            $exec = "$bin_sed -i 's/^interface=.*/interface=".$io_in_iface."/g' /usr/share/fruitywifi/conf/hostapd.conf";
            exec_fruitywifi($exec);
            
            //REPLACE WPA_PASSPHRASE
            $exec = "$bin_sed -i 's/wpa_passphrase=.*/wpa_passphrase=".$hostapd_wpa_passphrase."/g' /usr/share/fruitywifi/conf/hostapd.conf";
            exec_fruitywifi($exec);
            
            //EXTRACT MACADDRESS
            unset($output);
            $output = getIfaceMAC($io_in_iface);
            
            //REPLACE BSSID
            $exec = "$bin_sed -i 's/^bssid=.*/bssid=".$output."/g' /usr/share/fruitywifi/conf/hostapd.conf";
            exec_fruitywifi($exec);
            
            $exec = "/usr/sbin/hostapd -P /var/run/hostapd -B /usr/share/fruitywifi/conf/hostapd.conf";
        }
        exec_fruitywifi($exec);
        
        // IPTABLES	FLUSH
        if ($mod_nethunter == "1") {
            flushIptablesNetHunter();
            setNetHunter();
        } else {
            flushIptables();
        }
        
        // SET FORWARDING
        setForwarding();
        
        // CLEAN DHCP log
        $exec = "$bin_echo '' > /usr/share/fruitywifi/logs/dhcp.leases";
        exec_fruitywifi($exec);
        
        // SET DNSMASQ (DHCP|DNS)
        setDNSmasq();
        
        // SET FruityDNS (DNS)
        if ($mod_dns_type == "fruitydns") {
            setFruityDNS();
        }
        
    } else if($action == "stop") {

        // REMOVE lines from NetworkManager
        cleanNetworkManager();
        
        $exec = "$bin_killall hostapd";	
        exec_fruitywifi($exec);

        killRegex("hostapd");
        
        $exec = "$bin_rm /var/run/hostapd/$io_in_iface";
        exec_fruitywifi($exec);
        
        /*
        $exec = "chattr -i /etc/resolv.conf";
        exec_fruitywifi($exec);
        */
        
        $exec = "$bin_killall dnsmasq";
        exec_fruitywifi($exec);

        killRegex("dnsmasq");
        killRegex("dnschef.py");
        
        // IN IFACE DOWN
        setInIfaceDown();
        
        // IPTABLES	FLUSH
        if ($mod_nethunter == "1") {
            flushIptablesNetHunter();
            setIptablesRestore();
        } else {
            flushIptables();
        }
        
        // LOGS COPY
        copyLogsHistory();
        
    }
}

// AIRCRACK
if($service != "" and $ap_mode == "2") {
    if ($action == "start") {
        
        // SAVE IPTABLES
        setIptablesSave();
        
        // CHECK FOR POOL FILES
        checkPool();
        
        // SETUP NetworkManager
        setNetworkManager();
        
        // IN IFACE DOWN
        setInIfaceDown();
        
        killRegex("airbase-ng");

        $exec = "$bin_killall dnsmasq";
        exec_fruitywifi($exec);

        killRegex("dnsmasq");
        killRegex("dnschef.py");

        // EXEC AIRBASE
        $exec = "/usr/sbin/airbase-ng -e $hostapd_ssid -c 2 $io_in_iface > /tmp/airbase.log &"; //-P (all)
        exec_fruitywifi($exec);

        $exec = "sleep 1";
        exec_fruitywifi($exec);
        
        $exec = "$bin_ifconfig at0 up";
        exec_fruitywifi($exec);
        $exec = "$bin_ifconfig at0 up $io_in_ip netmask 255.255.255.0";
        exec_fruitywifi($exec);
        
        
        // IPTABLES	FLUSH
        if ($mod_nethunter == "1") {
            flushIptablesNetHunter();
            setNetHunter();
        } else {
            flushIptables();
        }
        
        // SET FORWARDING
        setForwarding();
        
        // CLEAN DHCP log
        $exec = "$bin_echo '' > /usr/share/fruitywifi/logs/dhcp.leases";
        exec_fruitywifi($exec);
        
        // SET DNSMASQ (DHCP|DNS)
        setDNSmasq();
        
        // SET FruityDNS (DNS)
        if ($mod_dns_type == "fruitydns") {
            setFruityDNS();
        }
        
    } else if($action == "stop") {

        // REMOVE lines from NetworkManager
        cleanNetworkManager();
        
        killRegex("airbase-ng");
        
        $exec = "$bin_killall dnsmasq";
        exec_fruitywifi($exec);

        killRegex("dnsmasq");
        killRegex("dnschef.py");
        
        // IN IFACE DOWN
        setInIfaceDown();
        
        $exec = "ip addr flush dev at0";
        exec_fruitywifi($exec);
        
        $exec = "$bin_ifconfig at0 down";
        exec_fruitywifi($exec);
        
        // IPTABLES	FLUSH
        if ($mod_nethunter == "1") {
            flushIptablesNetHunter();
            setIptablesRestore();
        } else {
            flushIptables();
        }
        
        // LOGS COPY
        copyLogsHistory();
        
    }
}

// HOSTAPD MANA
if($service != ""  and $ap_mode == "3") {
    if ($action == "start") {
        
        // CHECK FOR POOL FILES
        checkPool();
        
        // SETUP NetworkManager
        setNetworkManager();
        
        // IN IFACE DOWN
        setInIfaceDown();
        
        $exec = "$bin_killall hostapd";
        exec_fruitywifi($exec);

        killRegex("hostapd");
        
        $exec = "$bin_rm /var/run/hostapd/$io_in_iface";
        exec_fruitywifi($exec);

        $exec = "$bin_killall dnsmasq";
        exec_fruitywifi($exec);

        killRegex("dnsmasq");
        killRegex("dnschef.py");
        
        // IN IFACE UP
        setInIfaceUp();
        
        /*
        $exec = "$bin_echo 'nameserver $io_in_ip\nnameserver 8.8.8.8' > /etc/resolv.conf ";
        exec_fruitywifi($exec);
        
        $exec = "chattr +i /etc/resolv.conf";
        exec_fruitywifi($exec);
        
        $exec = "$bin_dnsmasq -C /usr/share/fruitywifi/conf/dnsmasq.conf";
        exec_fruitywifi($exec);
        */
        
        // SET HOSTAPD CONF
        if ($hostapd_secure == 1) {
            // BLACKLIST|WHITELIST PATH
            $path_hostapd_conf = "/usr/share/fruitywifi/www/modules/mana/includes/conf/hostapd-secure.conf";
        } else {
            // BLACKLIST|WHITELIST PATH
            $path_hostapd_conf = "/usr/share/fruitywifi/www/modules/mana/includes/conf/hostapd.conf";
        }
        
        // SET HOSTAPD [BLACK|WHITE]
        $exec = "$bin_sed -i 's/^macaddr_acl.*/#macaddr_acl=0/g' $path_hostapd_conf";
        exec_fruitywifi($exec);
        $exec = "$bin_sed -i 's,^accept_mac_file.*,#accept_mac_file=/usr/share/fruitywifi/conf/pool-station.conf,g' $path_hostapd_conf";
        exec_fruitywifi($exec);
        $exec = "$bin_sed -i 's,^deny_mac_file.*,#deny_mac_file=/usr/share/fruitywifi/conf/pool-station.conf,g' $path_hostapd_conf";
        exec_fruitywifi($exec);
        
        if ($mod_filter_karma_station == "blacklist") {
            hostapdStationBlacklist($path_hostapd_conf);
        } else if ($mod_filter_karma_station == "whitelist") {
            hostapdStationWhitelist($path_hostapd_conf);
        }
        
        //Verifies if mana-hostapd is installed
        if ($hostapd_secure == 1) {
            
            if (file_exists("/usr/share/fruitywifi/www/modules/mana/includes/hostapd")) {
                include "/usr/share/fruitywifi/www/modules/mana/_info_.php";
                
                $hostapd_config_file = "$mod_path/includes/conf/hostapd-secure.conf";
                
                if ($mod_ap_karma_loud == "1") {
                    setHostapdOption("karma_loud", "1", $hostapd_config_file);
                } else {
                    setHostapdOption("karma_loud", "0", $hostapd_config_file);
                }
                
                // SET HOSTAPD
                setHostapd($hostapd_config_file);
                
                //REPLACE SSID
                $exec = "$bin_sed -i 's/^ssid=.*/ssid=".$hostapd_ssid."/g' $mod_path/includes/conf/hostapd-secure.conf";
                exec_fruitywifi($exec);
                
                //REPLACE IFACE                
                $exec = "$bin_sed -i 's/^interface=.*/interface=".$io_in_iface."/g' $mod_path/includes/conf/hostapd-secure.conf";
                exec_fruitywifi($exec);
                
                //REPLACE WPA_PASSPHRASE
                $exec = "$bin_sed -i 's/wpa_passphrase=.*/wpa_passphrase=".$hostapd_wpa_passphrase."/g' $mod_path/includes/conf/hostapd-secure.conf";
                exec_fruitywifi($exec);
                
                //EXTRACT MACADDRESS
                unset($output);
                $output = getIfaceMAC($io_in_iface);
                
                //REPLACE MAC
                $exec = "$bin_sed -i 's/^bssid=.*/bssid=".$output."/g' $mod_path/includes/conf/hostapd-secure.conf";
                exec_fruitywifi($exec);
                
                $exec = "$bin_hostapd $mod_path/includes/conf/hostapd-secure.conf -f $mod_logs -B"; // >> $mod_log &
            } else {
                $exec = "/usr/sbin/hostapd -P /var/run/hostapd -B /usr/share/fruitywifi/conf/hostapd-secure.conf";
            }
            
        } else {
            
            if (file_exists("/usr/share/fruitywifi/www/modules/mana/includes/hostapd")) {
                include "/usr/share/fruitywifi/www/modules/mana/_info_.php";
                
                $hostapd_config_file = "$mod_path/includes/conf/hostapd.conf";
                
                if ($mod_ap_karma_loud == "1") {
                    setHostapdOption("karma_loud", "1", $hostapd_config_file);
                } else {
                    setHostapdOption("karma_loud", "0", $hostapd_config_file);
                }
                
                // SET HOSTAPD
                setHostapd($hostapd_config_file);
                
                //REPLACE SSID
                $exec = "$bin_sed -i 's/^ssid=.*/ssid=".$hostapd_ssid."/g' $mod_path/includes/conf/hostapd.conf";
                exec_fruitywifi($exec);
                
                //REPLACE IFACE                
                $exec = "$bin_sed -i 's/^interface=.*/interface=".$io_in_iface."/g' $mod_path/includes/conf/hostapd.conf";
                exec_fruitywifi($exec);
                
                //EXTRACT MACADDRESS
                unset($output);
                $output = getIfaceMAC($io_in_iface);
                
                //REPLACE MAC
                $exec = "$bin_sed -i 's/^bssid=.*/bssid=".$output."/g' $mod_path/includes/conf/hostapd.conf";
                exec_fruitywifi($exec);
                
                $exec = "$bin_hostapd $mod_path/includes/conf/hostapd.conf -t -d -f $mod_logs -B";
            } else {
                $exec = "/usr/sbin/hostapd -P /var/run/hostapd -B /usr/share/fruitywifi/conf/hostapd.conf";
            }
            
        }
        exec_fruitywifi($exec);
        
        // IPTABLES	FLUSH
        if ($mod_nethunter == "1") {
            flushIptablesNetHunter();
            setNetHunter();
        } else {
            flushIptables();
        }
        
        // SET FORWARDING
        setForwarding();
        
        // CLEAN DHCP log
        $exec = "$bin_echo '' > /usr/share/fruitywifi/logs/dhcp.leases";
        exec_fruitywifi($exec);
        
        // SET DNSMASQ (DHCP|DNS)
        setDNSmasq();
        
        // SET FruityDNS (DNS)
        if ($mod_dns_type == "fruitydns") {
            setFruityDNS();
        }
        
        // FILTER MACADDRESS STATIONS [BLACK|WHITE]
        if ($mod_filter_karma_station == "blacklist") {
            // SET KARMA_BLACK
            $exec = "$bin_hostapd_cli -p /var/run/hostapd karma_black";
            exec_fruitywifi($exec);
            
            poolStation($bin_hostapd_cli, "karma_add_black_mac");
            
        } else if ($mod_filter_karma_station == "whitelist") {
            // SET KARMA_WHITE
            $exec = "$bin_hostapd_cli -p /var/run/hostapd karma_white";
            exec_fruitywifi($exec);
            
            poolStation($bin_hostapd_cli, "karma_add_white_mac");
        }
        
    } else if($action == "stop") {
        
        // REMOVE lines from NetworkManager
        cleanNetworkManager();
    
        $exec = "$bin_killall hostapd";	
        exec_fruitywifi($exec);

        killRegex("hostapd");
        
        $exec = "$bin_rm /var/run/hostapd/$io_in_iface";
        exec_fruitywifi($exec);
        
        /*
        $exec = "chattr -i /etc/resolv.conf";
        exec_fruitywifi($exec);
        */
        
        $exec = "$bin_killall dnsmasq";
        exec_fruitywifi($exec);

        killRegex("dnsmasq");
        killRegex("dnschef.py");
        
        // IN IFACE DOWN
        setInIfaceDown();

        // IPTABLES	FLUSH
        if ($mod_nethunter == "1") {
            flushIptablesNetHunter();
            setIptablesRestore();
        } else {
            flushIptables();
        }
        
        // LOGS COPY
        copyLogsHistory();
        
    }
}

// HOSTAPD KARMA
if($service != ""  and $ap_mode == "4") {
    if ($action == "start") {
        
        // CHECK FOR POOL FILES
        checkPool();
        
        // SETUP NetworkManager
        setNetworkManager();
        
        // IN IFACE DOWN
        setInIfaceDown();
        
        $exec = "$bin_killall hostapd";
        exec_fruitywifi($exec);

        killRegex("hostapd");
        
        $exec = "$bin_rm /var/run/hostapd/$io_in_iface";
        exec_fruitywifi($exec);

        $exec = "$bin_killall dnsmasq";
        exec_fruitywifi($exec);

        killRegex("dnsmasq");
        killRegex("dnschef.py");
        
        // IN IFACE UP
        setInIfaceUp();
        
        /*
        $exec = "$bin_echo 'nameserver $io_in_ip\nnameserver 8.8.8.8' > /etc/resolv.conf ";
        exec_fruitywifi($exec);
        
        $exec = "chattr +i /etc/resolv.conf";
        exec_fruitywifi($exec);
        
        $exec = "$bin_dnsmasq -C /usr/share/fruitywifi/conf/dnsmasq.conf";
        exec_fruitywifi($exec);
        */
        
        // SET HOSTAPD CONF
        if ($hostapd_secure == 1) {
            // BLACKLIST|WHITELIST PATH
            $path_hostapd_conf = "/usr/share/fruitywifi/www/modules/karma/includes/conf/hostapd-secure.conf";
        } else {
            // BLACKLIST|WHITELIST PATH
            $path_hostapd_conf = "/usr/share/fruitywifi/www/modules/karma/includes/conf/hostapd.conf";
        }
        
        // SET HOSTAPD [BLACK|WHITE]
        $exec = "$bin_sed -i 's/^macaddr_acl.*/#macaddr_acl=0/g' $path_hostapd_conf";
        exec_fruitywifi($exec);
        $exec = "$bin_sed -i 's,^accept_mac_file.*,#accept_mac_file=/usr/share/fruitywifi/conf/pool-station.conf,g' $path_hostapd_conf";
        exec_fruitywifi($exec);
        $exec = "$bin_sed -i 's,^deny_mac_file.*,#deny_mac_file=/usr/share/fruitywifi/conf/pool-station.conf,g' $path_hostapd_conf";
        exec_fruitywifi($exec);
        
        if ($mod_filter_karma_station == "blacklist") {
            hostapdStationBlacklist($path_hostapd_conf);
        } else if ($mod_filter_karma_station == "whitelist") {
            hostapdStationWhitelist($path_hostapd_conf);
        }
        
        //Verifies if karma-hostapd is installed
        if ($hostapd_secure == 1) {
            
            if (file_exists("/usr/share/fruitywifi/www/modules/karma/includes/hostapd")) {
                include "/usr/share/fruitywifi/www/modules/karma/_info_.php";
                
                $hostapd_config_file = "$mod_path/includes/conf/hostapd-secure.conf";
            
                // SET HOSTAPD
                setHostapd($hostapd_config_file);
                
                //REPLACE SSID
                $exec = "$bin_sed -i 's/^ssid=.*/ssid=".$hostapd_ssid."/g' $mod_path/includes/conf/hostapd-secure.conf";
                exec_fruitywifi($exec);
                
                //REPLACE IFACE                
                $exec = "$bin_sed -i 's/^interface=.*/interface=".$io_in_iface."/g' $mod_path/includes/conf/hostapd-secure.conf";
                exec_fruitywifi($exec);
                
                //REPLACE WPA_PASSPHRASE
                $exec = "$bin_sed -i 's/wpa_passphrase=.*/wpa_passphrase=".$hostapd_wpa_passphrase."/g' $mod_path/includes/conf/hostapd-secure.conf";
                exec_fruitywifi($exec);
                
                //EXTRACT MACADDRESS
                unset($output);
                $output = getIfaceMAC($io_in_iface);
                
                //REPLACE MAC
                $exec = "$bin_sed -i 's/^bssid=.*/bssid=".$output."/g' $mod_path/includes/conf/hostapd-secure.conf";
                exec_fruitywifi($exec);
                
                $exec = "$bin_hostapd $mod_path/includes/conf/hostapd-secure.conf -d -f $mod_logs -B";
            } else {
                $exec = "/usr/sbin/hostapd -P /var/run/hostapd -B /usr/share/fruitywifi/conf/hostapd-secure.conf";
            }
            
        } else {
            
            if (file_exists("/usr/share/fruitywifi/www/modules/karma/includes/hostapd")) {
                include "/usr/share/fruitywifi/www/modules/karma/_info_.php";
                
                $hostapd_config_file = "$mod_path/includes/conf/hostapd.conf";
            
                // SET HOSTAPD
                setHostapd($hostapd_config_file);
                
                //REPLACE SSID
                $exec = "$bin_sed -i 's/^ssid=.*/ssid=".$hostapd_ssid."/g' $mod_path/includes/conf/hostapd.conf";
                exec_fruitywifi($exec);
                
                //REPLACE IFACE                
                $exec = "$bin_sed -i 's/^interface=.*/interface=".$io_in_iface."/g' $mod_path/includes/conf/hostapd.conf";
                exec_fruitywifi($exec);
                
                //EXTRACT MACADDRESS
                unset($output);
                $output = getIfaceMAC($io_in_iface);
                
                //REPLACE MAC
                $exec = "$bin_sed -i 's/^bssid=.*/bssid=".$output."/g' $mod_path/includes/conf/hostapd.conf";
                exec_fruitywifi($exec);
                
                $exec = "$bin_hostapd $mod_path/includes/conf/hostapd.conf -dd -f $mod_logs -B";
            } else {
                $exec = "/usr/sbin/hostapd -P /var/run/hostapd -B /usr/share/fruitywifi/conf/hostapd.conf";
            }
            
        }
        exec_fruitywifi($exec);
        
        /// IPTABLES FLUSH
        if ($mod_nethunter == "1") {
            flushIptablesNetHunter();
            setNetHunter();
        } else {
            flushIptables();
        }
        
        // SET FORWARDING
        setForwarding();
        
        // CLEAN DHCP log
        $exec = "$bin_echo '' > /usr/share/fruitywifi/logs/dhcp.leases";
        exec_fruitywifi($exec);
        
        // SET DNSMASQ (DHCP|DNS)
        setDNSmasq();
        
        // SET FruityDNS (DNS)
        if ($mod_dns_type == "fruitydns") {
            setFruityDNS();
        }

        // FILTER MACADDRESS STATIONS [BLACK|WHITE]
        if ($mod_filter_karma_station == "blacklist") {
            // SET KARMA_BLACK
            $exec = "$bin_hostapd_cli -p /var/run/hostapd karma_black";
            exec_fruitywifi($exec);
            
            poolStation($bin_hostapd_cli, "karma_add_black_mac");
            
        } else if ($mod_filter_karma_station == "whitelist") {
            // SET KARMA_WHITE
            $exec = "$bin_hostapd_cli -p /var/run/hostapd karma_white";
            exec_fruitywifi($exec);
            
            poolStation($bin_hostapd_cli, "karma_add_white_mac");
        }
        
        // FILTER SSID [BLACK|WHITE] ? ** CHECK BLACK/WHITE MODE [TODO]
        if ($mod_filter_karma_ssid == "blacklist") {
            // SET KARMA_BLACK
            $exec = "$bin_hostapd_cli -p /var/run/hostapd karma_black";
            exec_fruitywifi($exec);
            
            poolSSID($bin_hostapd_cli, "karma_add_ssid");
            
        } else if ($mod_filter_karma_ssid == "whitelist") {
            // SET KARMA_WHITE
            $exec = "$bin_hostapd_cli -p /var/run/hostapd karma_white";
            exec_fruitywifi($exec);
            
            poolSSID($bin_hostapd_cli, "karma_add_ssid");
        }
        
        
    } else if($action == "stop") {
        
        // REMOVE lines from NetworkManager
        cleanNetworkManager();

        $exec = "$bin_killall hostapd";	
        exec_fruitywifi($exec);

        killRegex("hostapd");
        
        $exec = "$bin_rm /var/run/hostapd/$io_in_iface";
        exec_fruitywifi($exec);
        
        /*
        $exec = "chattr -i /etc/resolv.conf";
        exec_fruitywifi($exec);
        */
        
        $exec = "$bin_killall dnsmasq";
        exec_fruitywifi($exec);

        killRegex("dnsmasq");
        killRegex("dnschef.py");
        
        // IN IFACE DOWN
        setInIfaceDown();

        // IPTABLES	FLUSH
        if ($mod_nethunter == "1") {
            flushIptablesNetHunter();
            setIptablesRestore();
        } else {
            flushIptables();
        }
        
        // LOGS COPY
        copyLogsHistory();
    }
}


if ($install == "install_$mod_name") {

    $exec = "chmod 755 install.sh";
    exec_fruitywifi($exec);

    $exec = "$bin_sudo ./install.sh > $log_path/install.txt &";
    exec_fruitywifi($exec);

    header("Location: ../../install.php?module=ap");
    exit;
}

if ($page == "status") {
    header("Location: ../../../action.php");
} else {
    header("Location: ../../action.php?page=ap");
}

?>
