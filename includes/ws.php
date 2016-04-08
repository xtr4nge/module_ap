<?php
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

include "../../api/includes/ws.php";

class WebServiceExtended extends WebService {
	
	// POOL STATION
	public function getPoolStation()
	{
		$data = open_file("/usr/share/fruitywifi/conf/pool-station.conf");
		$out = explode("\n", $data);
		
		$output = [];
		
		for ($i=0; $i < count($out); $i++) {
			if ($out[$i] != "") $output[] = $out[$i];
		}
		
		echo json_encode($output);
	}
	
	public function setPoolStation($value)
	{
		include "functions.php";
		
		$file = "/usr/share/fruitywifi/conf/pool-station.conf";
		
		$exec = "echo '".$value."' >> $file";
		$out = exec_fruitywifi($exec);
		
		echo json_encode($value);
	}
	
	public function delPoolStation($value)
	{
		include "functions.php";
		
		$file = "/usr/share/fruitywifi/conf/pool-station.conf";
		
		$exec = "sed -i '/".$value."/d' $file";
		$out = exec_fruitywifi($exec);
		
		echo json_encode($value);
	}
	
	// POOL SSID
	public function getPoolSSID()
	{
		$data = open_file("/usr/share/fruitywifi/conf/pool-ssid.conf");
		$out = explode("\n", $data);
		
		$output = [];
		
		for ($i=0; $i < count($out); $i++) {
			if ($out[$i] != "") $output[] = $out[$i];
		}
		
		echo json_encode($output);
	}
	
	public function setPoolSSID($value)
	{
		include "functions.php";
		
		$file = "/usr/share/fruitywifi/conf/pool-ssid.conf";
		
		$exec = "echo '".$value."' >> $file";
		$out = exec_fruitywifi($exec);
		
		echo json_encode($value);
	}
	
	public function delPoolSSID($value)
	{
		include "functions.php";
		
		$file = "/usr/share/fruitywifi/conf/pool-ssid.conf";
		
		$exec = "sed -i '/".$value."/d' $file";
		$out = exec_fruitywifi($exec);
		
		echo json_encode($value);
	}
	
    // SCAN
    
    public function getScanStation()
	{
		include "functions.php";
		include "../../../config/config.php";
		
		$data = open_file("/usr/share/fruitywifi/logs/dhcp.leases");
		$out = explode("\n", $data);
		
		$leases = [];
		
		for ($i=0; $i < count($out); $i++) {
			$temp = explode(" ", $out[$i]);
			$leases[$temp[1]] = array($temp[2], $temp[3]);
		}
		
		unset($out);
		unset($data);
		
		$exec = "iw dev $io_in_iface station dump | sed -e 's/^\\t/|/g' | tr '\\n' ' ' | sed -e 's/Sta/\\nSta/g' | tr '\\t' ' '";
		$out = exec_fruitywifi($exec);
		
		$output = [];
		
		for ($i=0; $i < count($out); $i++) {
			
			$station = [];
			
			$temp = explode("|", $out[$i]);
			
			if ($temp[0] != "")
			{
				foreach ($temp as &$value) {
					unset($sub);
					
					if (strpos($value,'Station ') !== false) {
						$value = str_replace("Station ","",$value);
						$value = explode(" ", $value);
						$mac = $value[0];
						$value = "station: " . $value[0];
						$key_mac = $value[0];
					}
					
					$sub = explode(": ",$value);
					//$station[] = $sub;
					//$station[] = array($sub[0] => $sub[1]);
					$station[$sub[0]] = $sub[1];
					
				}
				
				if (array_key_exists($mac, $leases)) {
					//$station[] = array("ip" => $leases[$mac][0]);
					//$station[] = array("hostname" => $leases[$mac][1]);
					$station["ip"] = $leases[$mac][0];
					$station["hostname"] = $leases[$mac][1];
				} else {
					//$station[] = array("ip" => "");
					//$station[] = array("hostname" => "");
					$station["ip"] = "";
					$station["hostname"] = "";
				}
				//$output[] = $station;
				$output[] = $station;
			}
		}
		echo json_encode($output);	
	}
    
}
?>