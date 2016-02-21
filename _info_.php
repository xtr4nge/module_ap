<?
$mod_name="ap";
$mod_version="1.2";
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
$bin_danger = "/usr/share/fruitywifi/bin/danger";
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
