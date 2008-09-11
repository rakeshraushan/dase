from elementtree import ElementTree as ET
import httplib2
import os
import time

username = 'pkeane'
htpasswd = 'xxxxxx'
coll = 'keanepj'
dase_url = 'http://quickdraw.laits.utexas.edu/dase1'

h = httplib2.Http()

def getSerialNumbers(h,dase_url,coll):
    url = dase_url+'/collection/'+coll+'/serial_numbers.txt'
    resp, content = h.request(url, "GET")  
    return content.split('|')

def getItemAtom(h,dase_url,coll,sn):
    url = dase_url+'/item/'+coll+'/'+sn+'.atom?type=entry'
    resp, content = h.request(url, "GET")  
    return content

def getMedia(h,source_atom):
    urls = {};
    tree = ET.fromstring(source_atom)
    mrss = "{http://search.yahoo.com/mrss/}"
    media = tree.find(mrss+"group")
    urls['thumbnail'] = media.find(mrss+"thumbnail").get('url')
    for link in media.findall(mrss+"content"):
        size = link.find(mrss+"category").text
        urls[size] = link.get('url')
    return urls 

def indent(elem, level=0):
    i = "\n" + level*"  "
    if len(elem):
        if not elem.text or not elem.text.strip():
            elem.text = i + "  "
        for elem in elem:
            indent(elem, level+1)
        if not elem.tail or not elem.tail.strip():
            elem.tail = i
    else:
        if level and (not elem.tail or not elem.tail.strip()):
            elem.tail = i

if __name__ == "__main__":
    atoms_dir = coll+'_atoms_'+str(int(time.time()))
    os.mkdir(atoms_dir)
    os.mkdir(atoms_dir+'/media/')
    h.add_credentials(username,htpasswd)
    for sn in getSerialNumbers(h,dase_url,coll):
        print "retrieving "+sn
        item_atom = getItemAtom(h,dase_url,coll,sn)
        f = open(atoms_dir+'/'+sn+'.atom',mode='w')
        f.write(item_atom)
        f.close()
        urls = getMedia(h,item_atom)
        for size in urls:
            if not os.path.exists(atoms_dir+'/media/'+size):
                os.mkdir(atoms_dir+'/media/'+size)
            media_file = urls[size].split('/').pop()
            f = open(atoms_dir+'/media/'+size+'/'+media_file,mode='w')
            resp,content = h.request(urls[size],"GET")
            f.write(content)
            f.close()


