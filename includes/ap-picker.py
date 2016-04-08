#!/usr/bin/env python

#REF: http://stackoverflow.com/questions/21613091/how-to-use-scapy-to-determine-wireless-encryption-type

import datetime
a = datetime.datetime.now()

import logging
logging.getLogger("scapy.runtime").setLevel(logging.ERROR)
from scapy.all import *

import sys, getopt
import json
from multiprocessing import Process
import signal
import threading

# scan-ap
# ------- MENU -------
def usage():
    print "\nap-picker 1.0 by xtr4nge"
    
    print "Usage: ap-picker.py <options>\n"
    print "Options:"
    print "-i <i>, --interface=<i>                  set interface (default: wlan0mon)"
    print "-t <time>, --time=<time>                 scan time (default: 5s)"
    print "-h                                       Print this help message."
    print ""
    print "Author: xtr4nge"
    print ""

def parseOptions(argv):
    INTERFACE = "mon0" # server, minion
    TIME =  int(0)
    LOG = ""

    try:
        opts, args = getopt.getopt(argv, "hi:t:l:",
                                   ["help", "interface=", "time=", "log="])

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

        return (INTERFACE, TIME, LOG)
                    
    except getopt.GetoptError:           
        usage()
        sys.exit(2) 

# -------------------------
# GLOBAL VARIABLES
# -------------------------
aps = {}
SSIDS = []

(INTERFACE, TIME, LOG) = parseOptions(sys.argv[1:])

LOG = "/usr/share/fruitywifi/conf/ssid.conf"
with open(LOG) as file:
    #SSIDS = file.readlines()
    for line in file:
        if line.strip() != "": SSIDS.append(line.strip())

print SSIDS

# -------------------------
# SNIFFER
# -------------------------
def insert_ap(pkt):
    global TIME
    global LOG
    
    ## Done in the lfilter param
    # if Dot11Beacon not in pkt and Dot11ProbeResp not in pkt:
    #     return
    
    p = pkt[Dot11Elt]
    cap = pkt.sprintf("{Dot11Beacon:%Dot11Beacon.cap%}"
                      "{Dot11ProbeResp:%Dot11ProbeResp.cap%}").split('+')
    
    ssid, channel = None, None
    
    while isinstance(p, Dot11Elt):
        if p.ID == 0:
            ssid = p.info
            ssid = ssid.encode('ascii',errors='ignore')
        p = p.payload
    
    # APPEND AND STORE NEW SSID
    if ssid not in SSIDS:
        SSIDS.append(ssid)
        print SSIDS
    
        #if LOG != "":
        with open(LOG, 'a') as f:
            f.write(ssid + "\n")
    
    return

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

'''
if len(sys.argv) != 2:
    print "Usage %s monitor_interface" % sys.argv[0]
    sys.exit(1)

INTERFACE = sys.argv[1]
'''

channel_hop = Process(target = channel_hopper, args=(INTERFACE,))
channel_hop.start()
signal.signal(signal.SIGINT, stop_channel_hop)

sniff(iface=INTERFACE, prn=insert_ap, store=False,
      lfilter=lambda p: (Dot11Beacon in p or Dot11ProbeResp in p))


