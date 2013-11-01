#!/usr/bin/python
import MySQLdb


startLine = 119
endLine = 757
data = open('liveData.log','rb')
linesCounter = 1
counter = 1

sqlData = [[None,None,None,None,None],[None,None,None,None,None],[None,None,None,None,None],
           [None,None,None,None,None],[None,None,None,None,None],[None,None,None,None,None],
           [None,None,None,None,None],[None,None,None,None,None],[None,None,None,None,None],
           [None,None,None,None,None],[None,None,None,None,None],[None,None,None,None,None],
           [None,None,None,None,None],[None,None,None,None,None],[None,None,None,None,None],
           [None,None,None,None,None],[None,None,None,None,None],[None,None,None,None,None],
           [None,None,None,None,None],[None,None,None,None,None],[None,None,None,None,None],
           [None,None,None,None,None],[None,None,None,None,None],[None,None,None,None,None],
           [None,None,None,None,None],[None,None,None,None,None],[None,None,None,None,None],
           [None,None,None,None,None],[None,None,None,None,None],[None,None,None,None,None],
           [None,None,None,None,None],[None,None,None,None,None],[None,None,None,None,None],
           [None,None,None,None,None],[None,None,None,None,None],[None,None,None,None,None],
           [None,None,None,None,None],[None,None,None,None,None],[None,None,None,None,None],
           [None,None,None,None,None],[None,None,None,None,None],[None,None,None,None,None],
           [None,None,None,None,None],[None,None,None,None,None],[None,None,None,None,None],
           [None,None,None,None,None],[None,None,None,None,None],[None,None,None,None,None],
           [None,None,None,None,None],[None,None,None,None,None],[None,None,None,None,None],
           [None,None,None,None,None],[None,None,None,None,None],[None,None,None,None,None],
           [None,None,None,None,None],[None,None,None,None,None],[None,None,None,None,None],
           [None,None,None,None,None]]

i=0    
for line in data:
    if linesCounter > startLine and  linesCounter < endLine:
        
        #print "Line Number:",linesCounter
        stripped=line.replace('Data: ','')
        #print stripped.strip()
        testing = stripped.rstrip()
        
        if testing == 'C':
            print "Not done!"
            testing = "C&G"
            #break

        if counter == 1:
            print "COMPANY:",testing
            sqlData[i][0]=testing
            #print sqlData
            counter += 1
        #xtra blank line
        elif counter == 2:
        	#print "counter:",counter
        	counter += 1
        elif counter == 3:
            print "HI DATA:",testing
            sqlData[i][1]=testing
            #print sqlData
            counter += 1
         #xtra blank line
        elif counter == 4:
        	#print "separator",testing
        	counter += 1
        #trash output line	
        elif counter == 5:
        	#print "trash:",testing
        	counter += 1
        elif counter == 6:
        	print "LO DATA:",testing
        	sqlData[i][2]=testing;
                #print sqlData
        	counter += 1
        #trash output line
        elif counter == 7:
        	#print "trash:",testing	
        	counter += 1
        elif counter == 8:
        	print "TURNOVER DATA:",testing
        	sqlData[i][3]=testing
                #print sqlData
        	counter += 1
        elif counter == 9:
        	print "TIMESTAMP:",testing
        	sqlData[i][4]=testing
                #print sqlData
        	counter += 1
        #trash output line
        elif counter == 10:
        	#print "trash1:",testing
        	counter += 1
        #trash output line
        elif counter == 11:
        	#print "trash2:",testing
        	print '\n'
        	counter = 1
                i += 1

        #print stripped.splitlines()
        
    linesCounter += 1

print "The data is:",sqlData,"\n"

#include the db_connect script to perform db ops.
#import db_connect


        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        