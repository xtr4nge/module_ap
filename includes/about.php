<b>Access Point</b> module by @xtr4nge
<br><br>
- Filters have been added (MACs and SSID)
<br>
- Connected Stations are displayed on Clients Tab.
<br>
- DNS Server can be changed from DHCP-DNS Tab [DNSmasq or FruityDNS]
<br>
- Hostapd options can be changed.
<br>
- Network-Manager can be disabled to avoid conflicts.

<br><br>

[AP]
<br>
<b>Nethunter</b>: This option needs to be enable when FruityWiFi is running on NetHunter.
<br><br>
<b>hw_mode</b>: Operation mode (<b>a</b> = IEEE 802.11a (5 GHz), <b>b</b> = IEEE 802.11b (2.4 GHz), <b>g</b> = IEEE 802.11g (2.4 GHz)).
<br>
<b>channel</b>: Channel number (IEEE 802.11) (0=auto, but is not supported used by all drivers).
<br>
<b>country</b>: Country code (ISO/IEC 3166-1). Used to set regulatory domain.
<br>
<b>ht_capab</b>: HT capabilities (use "<b>iw list</b>" to get the HT capabilities of your wifi card).
<br>
<b>wmm_enabled</b>: Default WMM parameters.
<br>
<b>ieee80211n</b>: Whether IEEE 802.11n (HT) is enabled.
<br>
<b>karma_loud</b>: Limit karma to responding only to the device probing (0), or not (1) ([AP] mode Hostapd-Mana only).
<br>
<b>Filter Station</b>: Filter stations to be allowed (or not) by the AP.

<br><br>

Note: If the AP module is not starting after change this options, just set the default values (hw_mode=<b>g</b>, channel=<b>6</b> and disable all the options under Hostapd).
<br>
For more details about the options: https://w1.fi/cgit/hostap/plain/hostapd/hostapd.conf

<br><br>

[DHCP-DNS]
<br>
<b>Lease IP</b>: Range of IPs to be given by the DHCP server.
<br>
<b>DNS</b>: DNS server to be used by FruityWiFi (DNSmasq | FruityDNS)
<br>
<b>Spoof ALL</b>: This option can only be used by DNSmasq as DNS server. (For FruityDNS use <b>*</b> in FruityDNS setup.)
<br>
<b>Network Manager</b>: If this option is enabled, Network-Manager will be disabled when the AP module starts. (this options is recommended)


<br><br>

[Filter]
<br>
This are the list of MACADDRESS and SSID to be used by the AP filters.
<br><br>

[Worker]
<br>
<b>Picker</b>: Collects all the SSIDs around you.
<br>
<b>Scatter</b>: Broadcast all the SSIDs collected.
<br>
<b>Polite</b>: Response to all the probe requests.

