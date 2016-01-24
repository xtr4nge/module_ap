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
	echo $exec;
}

function setNetworkManager() {
	
	global $io_in_iface;
	global $bin_sed;
	global $bin_echo;
	
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
		$exec = "python ap-polite.py -i mon0 -s $mod_filter_polite_station -e $mod_filter_polite_ssid -b $mod_scatter_bssid  > /dev/null &";
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
		
		// CHECK FOR POOL FILES
		checkPool();
		
		// SETUP NetworkManager
		setNetworkManager();
		
		$exec = "$bin_ifconfig $io_in_iface down";
		exec_fruitywifi($exec);
		$exec = "$bin_ifconfig $io_in_iface 0.0.0.0";
		exec_fruitywifi($exec);
		
		$exec = "$bin_killall hostapd";	
		exec_fruitywifi($exec);

		killRegex("hostapd");
		
		$exec = "$bin_rm /var/run/hostapd/$io_in_iface";
		exec_fruitywifi($exec);

		$exec = "$bin_killall dnsmasq";
		exec_fruitywifi($exec);

		killRegex("dnsmasq");
		
		$exec = "$bin_ifconfig $io_in_iface up";
		exec_fruitywifi($exec);
		$exec = "$bin_ifconfig $io_in_iface up $io_in_ip netmask 255.255.255.0";
		exec_fruitywifi($exec);
		
		$exec = "$bin_echo 'nameserver $io_in_ip\nnameserver 8.8.8.8' > /etc/resolv.conf ";
		exec_fruitywifi($exec);
		
		$exec = "chattr +i /etc/resolv.conf";
        exec_fruitywifi($exec);
		
		$exec = "$bin_dnsmasq -C /usr/share/fruitywifi/conf/dnsmasq.conf";
		exec_fruitywifi($exec);
	
		//Verifies if karma-hostapd is installed
		if ($hostapd_secure == 1) {
			
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
			$exec = "$bin_ifconfig -a $io_in_iface |grep HWaddr";
			$output = exec_fruitywifi($exec);
			$output = preg_replace('/\s+/', ' ',$output[0]);
			$output = explode(" ",$output);
			
			//REPLACE MAC
			$exec = "$bin_sed -i 's/^bssid=.*/bssid=".$output[4]."/g' /usr/share/fruitywifi/conf/hostapd-secure.conf";
			exec_fruitywifi($exec);
			
			fixConfig("/usr/share/fruitywifi/conf/hostapd-secure.conf");
			$exec = "/usr/sbin/hostapd -P /var/run/hostapd -B /usr/share/fruitywifi/conf/hostapd-secure.conf";
		} else {
			
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
			$exec = "$bin_ifconfig -a $io_in_iface |grep HWaddr";
			$output = exec_fruitywifi($exec);
			$output = preg_replace('/\s+/', ' ',$output[0]);
			$output = explode(" ",$output);
			
			//REPLACE BSSID
			$exec = "$bin_sed -i 's/^bssid=.*/bssid=".$output[4]."/g' /usr/share/fruitywifi/conf/hostapd.conf";
			exec_fruitywifi($exec);
			
			$exec = "/usr/sbin/hostapd -P /var/run/hostapd -B /usr/share/fruitywifi/conf/hostapd.conf";
		}
		exec_fruitywifi($exec);

		// IPTABLES	FLUSH	
		flushIptables();
		
		$exec = "$bin_echo 1 > /proc/sys/net/ipv4/ip_forward";
		exec_fruitywifi($exec);
		$exec = "$bin_iptables -t nat -A POSTROUTING -o $io_out_iface -j MASQUERADE";
		exec_fruitywifi($exec);
		
		// CLEAN DHCP log
		$exec = "$bin_echo '' > /usr/share/fruitywifi/logs/dhcp.leases";
		exec_fruitywifi($exec);

	} else if($action == "stop") {

		// REMOVE lines from NetworkManager
		cleanNetworkManager();
		
		/*	
		if (file_exists("/usr/share/fruitywifi/www/modules/karma/includes/hostapd")) {
			$exec = "$bin_killall hostapd";
		} else {
			$exec = "$bin_killall hostapd";			
		}
		*/
		
		$exec = "$bin_killall hostapd";	
		exec_fruitywifi($exec);

		killRegex("hostapd");
		
		$exec = "$bin_rm /var/run/hostapd/$io_in_iface";
		exec_fruitywifi($exec);

		$exec = "chattr -i /etc/resolv.conf";
        exec_fruitywifi($exec);

		$exec = "$bin_killall dnsmasq";
		exec_fruitywifi($exec);

		killRegex("dnsmasq");
		
		$exec = "ip addr flush dev $io_in_iface";
		exec_fruitywifi($exec);
		
		$exec = "$bin_ifconfig $io_in_iface down";
		exec_fruitywifi($exec);
		
		// IPTABLES	FLUSH	
		flushIptables();
		
		// LOGS COPY
		copyLogsHistory();
		
	}
}

// AIRCRACK
if($service != "" and $ap_mode == "2") { // AIRCRACK (airbase-ng)
	if ($action == "start") {

		$exec = "/usr/bin/sudo /usr/sbin/airmon-ng stop mon0";
		exec_fruitywifi($exec);
	
		$exec = "$bin_killall airbase-ng";
		exec_fruitywifi($exec);
	
		killRegex("airbase-ng");
	
		$exec = "$bin_killall dnsmasq";
		exec_fruitywifi($exec);
		
		killRegex("dnsmasq");
		
		$exec = "$bin_echo 'nameserver $io_in_ip\nnameserver 8.8.8.8' > /etc/resolv.conf ";
		exec_fruitywifi($exec);
		
		$exec = "chattr +i /etc/resolv.conf";
        exec_fruitywifi($exec);
		
		// SETUP NetworkManager
		setNetworkManager();
					
		$exec = "/usr/bin/sudo /usr/sbin/airmon-ng start $io_in_iface";
		exec_fruitywifi($exec);
		
		//$exec = "/usr/sbin/airbase-ng -e $hostapd_ssid -c 2 mon0 > /dev/null &"; //-P (all)
		$exec = "/usr/sbin/airbase-ng -e $hostapd_ssid -c 2 mon0 > /tmp/airbase.log &"; //-P (all)
		exec_fruitywifi($exec);

		//$exec = "$bin_ifconfig at0 up 10.0.0.1 netmask 255.255.255.0";
		//exec("$bin_danger \"" . $exec . "\"" ); //DEPRECATED

		$exec = "sleep 1";
		exec_fruitywifi($exec);

		$exec = "$bin_ifconfig at0 up";
		exec_fruitywifi($exec);
		$exec = "$bin_ifconfig at0 up $io_in_ip netmask 255.255.255.0";
		exec_fruitywifi($exec);

		$exec = "$bin_dnsmasq -C /usr/share/fruitywifi/conf/dnsmasq.conf";
		exec_fruitywifi($exec);
		
		// IPTABLES	FLUSH	
		flushIptables();
		
		$exec = "$bin_echo 1 > /proc/sys/net/ipv4/ip_forward";
		exec_fruitywifi($exec);
		$exec = "$bin_iptables -t nat -A POSTROUTING -o $io_out_iface -j MASQUERADE";
		exec_fruitywifi($exec);
		
		// CLEAN DHCP log
		$exec = "$bin_echo '' > /usr/share/fruitywifi/logs/dhcp.leases";
		exec_fruitywifi($exec);

	} else if($action == "stop") {

		// REMOVE lines from NetworkManager
		cleanNetworkManager();

		$exec = "$bin_killall airbase-ng";
		exec_fruitywifi($exec);

		killRegex("airbase-ng");
		
		$exec = "chattr -i /etc/resolv.conf";
        exec_fruitywifi($exec);

		$exec = "$bin_killall dnsmasq";
		exec_fruitywifi($exec);

		killRegex("dnsmasq");
		
		$exec = "/usr/bin/sudo /usr/sbin/airmon-ng stop mon0";
		exec_fruitywifi($exec);

		$exec = "ip addr flush dev at0";
		exec_fruitywifi($exec);
		
		$exec = "$bin_ifconfig at0 down";
		exec_fruitywifi($exec);

		// IPTABLES	FLUSH	
		flushIptables();
		
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
		
		$exec = "$bin_ifconfig $io_in_iface down";
		exec_fruitywifi($exec);
		$exec = "$bin_ifconfig $io_in_iface 0.0.0.0";
		exec_fruitywifi($exec);
		
		$exec = "$bin_killall hostapd";
		exec_fruitywifi($exec);

		killRegex("hostapd");
		
		$exec = "$bin_rm /var/run/hostapd/$io_in_iface";
		exec_fruitywifi($exec);

		$exec = "$bin_killall dnsmasq";
		exec_fruitywifi($exec);

		killRegex("dnsmasq");
		
		$exec = "$bin_ifconfig $io_in_iface up";
		exec_fruitywifi($exec);
		$exec = "$bin_ifconfig $io_in_iface up $io_in_ip netmask 255.255.255.0";
		exec_fruitywifi($exec);
		
		$exec = "$bin_echo 'nameserver $io_in_ip\nnameserver 8.8.8.8' > /etc/resolv.conf ";
		exec_fruitywifi($exec);
		
		$exec = "chattr +i /etc/resolv.conf";
        exec_fruitywifi($exec);
		
		$exec = "$bin_dnsmasq -C /usr/share/fruitywifi/conf/dnsmasq.conf";
		exec_fruitywifi($exec);
	
		//Verifies if mana-hostapd is installed
		if ($hostapd_secure == 1) {
			
			if (file_exists("/usr/share/fruitywifi/www/modules/mana/includes/hostapd")) {
				include "/usr/share/fruitywifi/www/modules/mana/_info_.php";
				
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
				$exec = "$bin_ifconfig -a $io_in_iface |grep HWaddr";
				$output = exec_fruitywifi($exec);
				$output = preg_replace('/\s+/', ' ',$output[0]);
				$output = explode(" ",$output);
				
				//REPLACE MAC
				$exec = "$bin_sed -i 's/^bssid=.*/bssid=".$output[4]."/g' $mod_path/includes/conf/hostapd-secure.conf";
				exec_fruitywifi($exec);
				
				$exec = "$bin_hostapd $mod_path/includes/conf/hostapd-secure.conf -f $mod_logs -B"; // >> $mod_log &
			} else {
				$exec = "/usr/sbin/hostapd -P /var/run/hostapd -B /usr/share/fruitywifi/conf/hostapd-secure.conf";
			}
			
		} else {
			
			if (file_exists("/usr/share/fruitywifi/www/modules/mana/includes/hostapd")) {
				include "/usr/share/fruitywifi/www/modules/mana/_info_.php";
				
				//REPLACE SSID
				$exec = "$bin_sed -i 's/^ssid=.*/ssid=".$hostapd_ssid."/g' $mod_path/includes/conf/hostapd.conf";
				exec_fruitywifi($exec);
				
				//REPLACE IFACE                
				$exec = "$bin_sed -i 's/^interface=.*/interface=".$io_in_iface."/g' $mod_path/includes/conf/hostapd.conf";
				exec_fruitywifi($exec);
				
				//EXTRACT MACADDRESS
				unset($output);
				$exec = "$bin_ifconfig -a $io_in_iface |grep HWaddr";
				$output = exec_fruitywifi($exec);
				$output = preg_replace('/\s+/', ' ',$output[0]);
				$output = explode(" ",$output);
				
				//REPLACE MAC
				$exec = "$bin_sed -i 's/^bssid=.*/bssid=".$output[4]."/g' $mod_path/includes/conf/hostapd.conf";
				exec_fruitywifi($exec);
				
				$exec = "$bin_hostapd $mod_path/includes/conf/hostapd.conf -t -d -f $mod_logs -B";
			} else {
				$exec = "/usr/sbin/hostapd -P /var/run/hostapd -B /usr/share/fruitywifi/conf/hostapd.conf";
			}
			
		}
		exec_fruitywifi($exec);
		
		// IPTABLES	FLUSH	
		flushIptables();
		
		$exec = "$bin_echo 1 > /proc/sys/net/ipv4/ip_forward";
		exec_fruitywifi($exec);
		$exec = "$bin_iptables -t nat -A POSTROUTING -o $io_out_iface -j MASQUERADE";
		exec_fruitywifi($exec);
		
		// CLEAN DHCP log
		$exec = "$bin_echo '' > /usr/share/fruitywifi/logs/dhcp.leases";
		exec_fruitywifi($exec);

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

		/*
		// REMOVE lines from NetworkManager
		$exec = "$bin_sed -i '/unmanaged/d' /etc/NetworkManager/NetworkManager.conf";
		exec_fruitywifi($exec);
		$exec = "$bin_sed -i '/\[keyfile\]/d' /etc/NetworkManager/NetworkManager.conf";
		exec_fruitywifi($exec);
		*/
		
		// REMOVE lines from NetworkManager
		cleanNetworkManager();
	
		$exec = "$bin_killall hostapd";	
		exec_fruitywifi($exec);

		killRegex("hostapd");
		
		$exec = "$bin_rm /var/run/hostapd/$io_in_iface";
		exec_fruitywifi($exec);
		
		$exec = "chattr -i /etc/resolv.conf";
        exec_fruitywifi($exec);
		
		$exec = "$bin_killall dnsmasq";
		exec_fruitywifi($exec);

		killRegex("dnsmasq");
		
		$exec = "ip addr flush dev $io_in_iface";
		exec_fruitywifi($exec);
		
		$exec = "$bin_ifconfig $io_in_iface down";
		exec_fruitywifi($exec);

		// IPTABLES	FLUSH	
		flushIptables();
		
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
		
		$exec = "$bin_ifconfig $io_in_iface down";
		exec_fruitywifi($exec);
		$exec = "$bin_ifconfig $io_in_iface 0.0.0.0";
		exec_fruitywifi($exec);
		
		$exec = "$bin_killall hostapd";
		exec_fruitywifi($exec);

		killRegex("hostapd");
		
		$exec = "$bin_rm /var/run/hostapd/$io_in_iface";
		exec_fruitywifi($exec);

		$exec = "$bin_killall dnsmasq";
		exec_fruitywifi($exec);

		killRegex("dnsmasq");
		
		$exec = "$bin_ifconfig $io_in_iface up";
		exec_fruitywifi($exec);
		$exec = "$bin_ifconfig $io_in_iface up $io_in_ip netmask 255.255.255.0";
		exec_fruitywifi($exec);
		
		$exec = "$bin_echo 'nameserver $io_in_ip\nnameserver 8.8.8.8' > /etc/resolv.conf ";
		exec_fruitywifi($exec);
		
		$exec = "chattr +i /etc/resolv.conf";
        exec_fruitywifi($exec);
		
		$exec = "$bin_dnsmasq -C /usr/share/fruitywifi/conf/dnsmasq.conf";
		exec_fruitywifi($exec);
	
		//Verifies if karma-hostapd is installed
		if ($hostapd_secure == 1) {
			
			if (file_exists("/usr/share/fruitywifi/www/modules/karma/includes/hostapd")) {
				include "/usr/share/fruitywifi/www/modules/karma/_info_.php";
				
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
				$exec = "$bin_ifconfig -a $io_in_iface |grep HWaddr";
				$output = exec_fruitywifi($exec);
				$output = preg_replace('/\s+/', ' ',$output[0]);
				$output = explode(" ",$output);
				
				//REPLACE MAC
				$exec = "$bin_sed -i 's/^bssid=.*/bssid=".$output[4]."/g' $mod_path/includes/conf/hostapd-secure.conf";
				exec_fruitywifi($exec);
				
				$exec = "$bin_hostapd $mod_path/includes/conf/hostapd-secure.conf -d -f $mod_logs -B";
			} else {
				$exec = "/usr/sbin/hostapd -P /var/run/hostapd -B /usr/share/fruitywifi/conf/hostapd-secure.conf";
			}
			
		} else {
			
			if (file_exists("/usr/share/fruitywifi/www/modules/karma/includes/hostapd")) {
				include "/usr/share/fruitywifi/www/modules/karma/_info_.php";
				
				//REPLACE SSID
				$exec = "$bin_sed -i 's/^ssid=.*/ssid=".$hostapd_ssid."/g' $mod_path/includes/conf/hostapd.conf";
				exec_fruitywifi($exec);
				
				//REPLACE IFACE                
				$exec = "$bin_sed -i 's/^interface=.*/interface=".$io_in_iface."/g' $mod_path/includes/conf/hostapd.conf";
				exec_fruitywifi($exec);
				
				//EXTRACT MACADDRESS
				unset($output);
				$exec = "$bin_ifconfig -a $io_in_iface |grep HWaddr";
				$output = exec_fruitywifi($exec);
				$output = preg_replace('/\s+/', ' ',$output[0]);
				$output = explode(" ",$output);
				
				//REPLACE MAC
				$exec = "$bin_sed -i 's/^bssid=.*/bssid=".$output[4]."/g' $mod_path/includes/conf/hostapd.conf";
				exec_fruitywifi($exec);
				
				$exec = "$bin_hostapd $mod_path/includes/conf/hostapd.conf -dd -f $mod_logs -B";
			} else {
				$exec = "/usr/sbin/hostapd -P /var/run/hostapd -B /usr/share/fruitywifi/conf/hostapd.conf";
			}
			
		}
		exec_fruitywifi($exec);
		
		// IPTABLES	FLUSH	
		flushIptables();
		
		$exec = "$bin_echo 1 > /proc/sys/net/ipv4/ip_forward";
		exec_fruitywifi($exec);
		$exec = "$bin_iptables -t nat -A POSTROUTING -o $io_out_iface -j MASQUERADE";
		exec_fruitywifi($exec);
		
		// CLEAN DHCP log
		$exec = "$bin_echo '' > /usr/share/fruitywifi/logs/dhcp.leases";
		exec_fruitywifi($exec);

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

		/*
		// REMOVE lines from NetworkManager
		$exec = "$bin_sed -i '/unmanaged/d' /etc/NetworkManager/NetworkManager.conf";
		exec_fruitywifi($exec);
		$exec = "$bin_sed -i '/[keyfile]/d' /etc/NetworkMxanager/NetworkManager.conf";
		exec_fruitywifi($exec);
		*/
		
		// REMOVE lines from NetworkManager
		cleanNetworkManager();

		$exec = "$bin_killall hostapd";	
		exec_fruitywifi($exec);

		killRegex("hostapd");
		
		$exec = "$bin_rm /var/run/hostapd/$io_in_iface";
		exec_fruitywifi($exec);

		$exec = "chattr -i /etc/resolv.conf";
        exec_fruitywifi($exec);

		$exec = "$bin_killall dnsmasq";
		exec_fruitywifi($exec);

		killRegex("dnsmasq");
		
		$exec = "ip addr flush dev $io_in_iface";
		exec_fruitywifi($exec);
		
		$exec = "$bin_ifconfig $io_in_iface down";
		exec_fruitywifi($exec);

		// IPTABLES	FLUSH	
		flushIptables();
		
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
