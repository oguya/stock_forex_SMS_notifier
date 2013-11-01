#!/usr/bin/python
import sys
import MySQLdb
from filter_stock_data import sqlData

#connect to db
try:
    db = MySQLdb.connect(host="localhost",user="root",passwd="",db="sentinel")
    cursor = db.cursor()

except MySQLdb.Error, e:
    print "%d: %s" % (e.args[0],e.args[1])
    sys.exit(1)

x=0;
try:
    status = "new"
    for x in range(0,len(sqlData)):
        #print"Details:",sqlData[x]
        sql="insert into tempData(company,hiData,loData,turnOver,timestamp,status) values ('"+sqlData[x][0].strip()+"','"+sqlData[x][1].strip()+"','"+sqlData[x][2].strip()+"','"+sqlData[x][3].strip()+"','"+sqlData[x][4].strip()+"',"+"'"+status+"')"
        print "SQL:",sql
        cursor.execute(sql)
    
    cursor.close()
        

except MySQLdb.Error, e:
    print "%d: %s" % (e.args[0],e.args[1])
    sys.exit(1)
