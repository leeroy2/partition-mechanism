# Fabfile to:
#    - get disk availability from /data and /applogs partition of hadoop nodes

# Import Fabric's API module

from fabric.api import *
from datetime import datetime
import MySQLdb

env.hosts = [
  #define hosts here
]

env.user = "lambros.batalas"

def insert(host, part, avail, date):
  try:
    db = MySQLdb.connect(host="localhost", user="root", passwd="root", db="partitions" )
    cursor = db.cursor()        
    cursor.execute('''INSERT into partitions.pusage VALUES (%s, %s, %s, %s)''',(host, part, avail, date))
    db.commit()
  except MySQLdb.Error:
    print "ERROR IN CONNECTION"

def get_size(partitions):
  host = run("hostname")
  avail = run("df %s | sed -n '2p' | awk '{print $4}'" % partitions)
  date = run("date +%Y-%m-%d")
  insert(host, partitions, avail, date)
  
def main():
  partitions_to_check = ["/data", "/applogs"]
  partitions = run("df -P | awk '{print $6}' | sed -n '1!p'")
  partitions = partitions.split()
  for i in partitions_to_check:
    if i in partitions:
      get_size(i)
