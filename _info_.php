<?
$mod_name="ap";
$mod_version="1.7";
$mod_path="/usr/share/fruitywifi/www/modules/$mod_name";
//$mod_logs="$log_path/$mod_name.log";
$mod_logs="$log_path/dnsmasq.log";
$mod_logs_history="$mod_path/includes/logs/";
$mod_panel="show";
$mod_type="service";
$mod_alias="[AP]";
$mod_filter_station="0";
$mod_filter_station_mode="1";
$mod_filter_ssid="0";
$mod_filter_ssid_mode="1";
$mod_nethunter="0";

# DHCP
$mod_dhcp="1";
$mod_dhcp_lease_start="10";
$mod_dhcp_lease_end="200";

# DNS
$mod_dns_type="dnsmasq";
$mod_dns="1";
$mod_dns_server="0";
$mod_dns_server_ip="8.8.8.8";
$mod_dns_server_option="0";
$mod_dns_spoof_all="0";
$mod_dns_spoof_all_ip="10.0.0.1";

# AP
$mod_ap_hw_mode="g";
$mod_ap_channel="6";
$mod_ap_country_code="GB";
$mod_ap_ieee80211n="0";
$mod_ap_wme_enabled="0";
$mod_ap_wmm_enabled="0";
$mod_ap_ht_capab_enabled="0";
$mod_ap_ht_capab="[HT20][SHORT-GI-20][HT40+][SHORT-GI-40][TX-STBC][RX-STBC2]";
$mod_ap_mana_loud="0";

# OTHER
$mod_network_manager_stop="1";
$mod_dnsmasq_dhcp_script="1";

# KARMA
$mod_filter_karma_station="none";
$mod_filter_karma_ssid="none";

# POLITE
$mod_filter_polite_station="none";
$mod_filter_polite_ssid="none";

# SCATTER
$mod_filter_scatter_ssid="none";
$mod_filter_scatter_bssid="0";
$mod_filter_scatter_station="0";
$mod_scatter_bssid="00:00:00:00:00:ff";
$mod_scatter_station="00:00:00:00:00:01";

# EXEC
$bin_sudo = "/usr/bin/sudo";
$bin_hostapd = "$mod_path/includes/hostapd";
$bin_hostapd_cli = "$mod_path/includes/hostapd_cli";
$bin_sh = "/bin/sh";
$bin_echo = "/bin/echo";
$bin_grep = "/usr/bin/ngrep";
$bin_killall = "/usr/bin/killall";
$bin_cp = "/bin/cp";
$bin_chmod = "/bin/chmod";
$bin_sed = "/bin/sed";
$bin_rm = "/bin/rm";
$bin_route = "/sbin/route";
$bin_perl = "/usr/bin/perl";

$bin_danger = "/usr/share/fruitywifi/bin/danger";
$bin_killall = "/usr/bin/killall";
$bin_ifconfig = "/sbin/ifconfig";
$bin_iptables = "/sbin/iptables";
$bin_dnsmasq = "/usr/sbin/dnsmasq";
$bin_sed = "/bin/sed";
$bin_echo = "/bin/echo";
$bin_rm = "/bin/rm";
$bin_mv = "/bin/mv";

# ISUP
$mod_isup="ps auxww | grep -iEe 'hostapd|airbase-ng -e' | grep -v -e grep";
?>
