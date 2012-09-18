#!/usr/bin/python

import sys
import MySQLdb
import subprocess
from daemon import Daemon
from os import chdir
from datetime import datetime
from time import sleep

## Config ##

# Path to the Lansnap install directory
lspath = "/home/sean/python/lansnap/"

# Log file path and file
logfile = "ls.log"

# Dhdpd.hosts file path and file
hostfile = ""

# Databse Host
dbhost = ""
# Database User
dbuser = ""
# Database Password
dbpass = ""
# Database name
dbname = "pico"

# Number of seconds the daemon should sleep each pass
sleepytime = 2

## End Config ##

#Open the log file in Append mode with 0/no buffering
try:
    lslog = open(logfile, "a", 0)
except IOError, e:
    print "Was unable to open %s: %s" % (logfile, e)
    sys.exit(1)


# Logger
def logEntry(logtext):
    time = str(datetime.now())
    line = time + " - " + str(logtext)
    lslog.write(line + "\n")


# Rewrite the dhcpd hosts file
def hostWrite(dhcpText):
    try:
        dhcphosts = open(hostfile, "w")
    except IOError, e:
        logEntry("Could not open %s: %s" % (hostfile, e))
    dhcphosts.write(dhcpText + "\n")
    dhcphosts.close()


#Connect to the database
def dbConnect():
    try:
        db = MySQLdb.Connect(host=dbhost,
                                port=3306,
                                user=dbuser,
                                passwd=dbpass,
                                db=dbname)
    except MySQLdb.Error, e:
        logEntry("Error %d: %s" % (e.args[0], e.args[1]))
        sys.exit(1)
    else:
        logEntry("Successfully connected to database")
        return db.cursor()


#Setup complete, run daemon:
class MyDaemon(Daemon):
    def run(self):
        chdir(lspath)
        cursor = dbConnect()

        # Do useful things here
        while True:
            # Check the status on the Lansnap database
            status_check = cursor.execute("""SELECT COUNT(1)
                                            FROM lansnap_addresses
                                            WHERE status = 1""")
            logEntry("Status Check: %s" % status_check)
            # Check if hosts file needs to up rewritten
            if (status_check > 0):
                hostFileContent = ""
                logEntry("New host entries found, rewriting %s ..." % hostfile)
                cursor.execute("""SELECT mac_address,ip_address
                                  FROM lansnap_addresses""")
                hosts = cursor.fetchall()
                for host in hosts:
                    ip = host[1]
                    unformattedMac = host[0]
                    macBlocks = [unformattedMac[x:x + 2] for x in xrange(0, len(unformattedMac), 2)]
                    mac = ':'.join(macBlocks)
                    hostname = ip.replace(".", "_")
                    entry = "host  {hardware " + hostname + " ethernet " + mac + " ; fixed-address " + ip + "; }\n"
                    hostFileContent += entry

                # Rewrite the dhcpd.hosts file
                hostWrite(hostFileContent)
                # Reset the status of the database
                # Uncomment when does testing
                ###cursor.execute("UPDATE lansnap_addresses SET status = 0")
                return_code = subprocess.call(["/etc/init.d/isc-dhcp-server", "restart"], stdout=lslog, stderr=lslog, shell=False)
                if (return_code > 0):
                    logEntry("Restarting DHCP Failed. Check syslogs for errors")
            else:
                logEntry("Nothing to do here...")

            sleep(sleepytime)


#When called from the command line
if __name__ == "__main__":
    daemon = MyDaemon('/var/run/lansnap/lansnap.pid')
    if len(sys.argv) == 2:
        if 'start' == sys.argv[1]:
            daemon.start()
        elif 'stop' == sys.argv[1]:
            daemon.stop()
        elif 'restart' == sys.argv[1]:
            daemon.restart()
        else:
            print "Unknown command"
            sys.exit(2)
            sys.exit(0)
    else:
        print "usage: %s start|stop|restart" % sys.argv[0]
        sys.exit(2)
