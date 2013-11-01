from HTMLParser import HTMLParser
from htmlentitydefs import name2codepoint
import urllib
import cgi

def recordData(data):
    outputFile=open('liveData.log','a')
    outputFile.write(''.join('%s' % x for x in data))
    outputFile.close()

class StockHTMLParser(HTMLParser):
    def handle_starttag(self,tag,attrs):
        print "Start Tag: ",tag
        liveData = "Start Tag: ",tag
        for attr in attrs:
            print "attr:",attr
            liveData += "attr:",attr
        #recordData(liveData)
        
    def handle_endtag(self,tag):
        print "End Tag: ",tag
        liveData = "End Tag: ",tag
        #recordData(liveData)
    
    def handle_data(self,data):
        print "Data: ",data
        if data:
            liveData = "\nData: ",data
            recordData(liveData)
        
    
    def handle_comment(self,data):
        print "Comment: ",data
        liveData = "Comment: ",data
        #recordData(liveData)
        
    def handle_entityref(self,name):
        if name == "G":
            #name = cgi.escape("&")
            name = "nG"
        else:
            c = unichr(name2codepoint[name])
            print "Named ent:",c
     
    def handle_charref(self, name):
        if name.startswitch('x'):
            c = unichr(int(name[1:],16))
        else:
            c = unichr(int(name))
        print "Num ent: ",c
        liveData = "Num ent: ",c
        #recordData(liveData)
        
    def handle_decl(self,data):
        print "Decl: ",data
        liveData = "Decl: ",data
        #recordData(liveData)
        
    def emptyFile(self):
        File=open('liveData.log','w')
        File.close()

parser = StockHTMLParser()

#empty file
parser.emptyFile()

#parser.feed('<h1>Python</h1>')

#proxy settings
#proxies = {'http': 'http://proxy.uonbi.ac.ke:80/'}
proxies = {'http': 'http://P15%2F35231%2F2010%40students:rzd%40uon@proxy.uonbi.ac.ke:80/'}
urlopener = urllib.FancyURLopener(proxies)

#open html file
stocks_html = urlopener.open("http://live.rich.co.ke/tdy_turnover2.php").read( )
parser.feed(stocks_html)













