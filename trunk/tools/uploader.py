from xml.dom import minidom
import fnmatch
import httplib
import md5
import mimetypes
import os
import urllib 
import wx
import base64
import string
import sys

DASE_HOST = 'littlehat.com'
DASE_BASE = '/dase'

class UploaderPanel(wx.Panel):
    def __init__(self, parent, *args, **kwargs):
        """Create the UploaderPanel."""
        wx.Panel.__init__(self, parent, *args, **kwargs)

        self.parent = parent 
        atom_ns = "http://www.w3.org/2005/Atom"
        d = minidom.parse(urllib.urlopen('http://'+DASE_HOST+DASE_BASE+'/collections.atom?get_all=1'))
        entries = d.getElementsByTagNameNS(atom_ns,'entry')
        self.coll = ''
        self.coll_dict = {}
        self.colls = []
        for entry in entries:
            title = entry.getElementsByTagNameNS(atom_ns,'title')[0].firstChild.nodeValue
            self.colls.append(title)
            self.coll_dict[title] = entry.getElementsByTagNameNS(atom_ns,'id')[0].firstChild.nodeValue.split('/').pop()
        self.chooser = wx.Choice(self, -1, (85, 18), choices=self.colls)
        self.chooser.Bind(wx.EVT_CHOICE,self.choose_coll)
        dirButton = wx.Button(self, label='Select Directory')
        dirButton.Bind(wx.EVT_BUTTON, self.picker)
        uploadButton = wx.Button(self, label='Upload')
        uploadButton.Bind(wx.EVT_BUTTON, self.upload_file)
        closeButton = wx.Button(self, label='Close')
        closeButton.Bind(wx.EVT_BUTTON, self.close)
        self.directory = wx.TextCtrl(self)
        self.username = wx.TextCtrl(self)
        self.password = wx.TextCtrl(self,-1,'',(0,0),(0,0),wx.TE_PASSWORD)
        username_label = wx.StaticText(self, -1,"Username: ",wx.Point(0,0),wx.Size(80,-1),wx.ALIGN_RIGHT)
        password_label = wx.StaticText(self, -1,"Password: ",wx.Point(0,0),wx.Size(80,-1),wx.ALIGN_RIGHT)
        self.contents = wx.TextCtrl(self, style=wx.TE_MULTILINE | wx.HSCROLL)
        hbox = wx.BoxSizer()
        hbox.Add(self.chooser, proportion=1)
        hbox.Add(self.directory, proportion=1, flag=wx.EXPAND)
        hbox.Add(dirButton, proportion=0, flag=wx.LEFT, border=5)
        hbox2 = wx.BoxSizer()
        hbox2.Add(username_label)
        hbox2.Add(self.username, proportion=1, flag=wx.EXPAND)
        hbox2.Add(password_label)
        hbox2.Add(self.password, proportion=1, flag=wx.EXPAND)
        hbox2.Add(uploadButton, proportion=1)
        hbox3 = wx.BoxSizer()
        hbox3.Add(closeButton, proportion=0, flag=wx.LEFT, border=5)
        vbox = wx.BoxSizer(wx.VERTICAL)
        vbox.Add(hbox, proportion=0, flag=wx.EXPAND | wx.ALL, border=5)
        vbox.Add(hbox2, proportion=0, flag=wx.EXPAND | wx.ALL, border=5)
        vbox.Add(self.contents, proportion=1, flag=wx.EXPAND | wx.LEFT | wx.BOTTOM | wx.RIGHT, border=5)
        vbox.Add(hbox3, proportion=0, flag=wx.EXPAND | wx.ALL, border=5)
        self.SetSizer(vbox)

    def picker(self,event):
        dialog = wx.DirDialog(None, "Choose a Directory", style=wx.DD_DEFAULT_STYLE | wx.DD_NEW_DIR_BUTTON)
        if dialog.ShowModal() == wx.ID_OK:
            self.directory.SetValue(dialog.GetPath())
            dialog.Destroy

    def upload_file(self,event):
        u = self.username.GetValue()
        p = self.password.GetValue()
        coll = self.coll
        if not self.coll:
            self.write("No collection selected!")
            return
        if '401' == str(self.checkAuth(coll,u,p)):
            self.write('Unauthorized User')
            return
        path = self.directory.GetValue()+'/'
        if '/' == path:
            self.write('Please select a directory')
            return
        file_count = sum((len(f) for _, _, f in os.walk(path)))
        self.write("Preparing to upload "+str(file_count)+" files")
        self.write(" ")
        for f in os.listdir(path):
            if not fnmatch.fnmatch(f,'.*'):
                (mime_type,enc) = mimetypes.guess_type(path+f)
                self.write("uploading "+f)
                status = self.postFile(path,f,coll,mime_type,u,p)
                if ('201' == status):
                    self.write("success!!")
        self.write("Uploading Complete");

    def write(self,txt):
        orig = self.contents.GetValue()
        if orig:
            self.contents.SetValue(orig+"\n"+txt)
        else:
            self.contents.SetValue(txt)
        wx.YieldIfNeeded()

    def postFile(self,path,filename,coll,mime_type,u,p):
        auth = 'Basic ' + string.strip(base64.encodestring(u + ':' + p))
        f = file(path+filename, "rb")
        body = f.read()                                                                     
        http = httplib.HTTP(DASE_HOST);
        http.putrequest("POST",DASE_BASE+'/media/'+coll)
        http.putheader("Content-Type",mime_type);
        (basename,ext)=os.path.splitext(filename)
        http.putheader("Slug",basename);
        http.putheader("Content-Length",str(len(body)))
        http.putheader('Authorization', auth )
        http.endheaders()
        http.send(body)
        errcode,errmsg,headers = http.getreply()
        return str(errcode) 

    def checkAuth(self,coll,u,p):
        auth = 'Basic ' + string.strip(base64.encodestring(u + ':' + p))
        body = ''                                                                     
        http = httplib.HTTP(DASE_HOST);
        http.putrequest("POST",DASE_BASE+'/collection/'+coll)
        http.putheader('Authorization', auth )
        http.endheaders()
        http.send(body)
        errcode,errmsg,headers = http.getreply()
        return str(errcode) 

    def choose_coll(self,event):
        self.coll = self.coll_dict[self.colls[self.chooser.GetSelection()]]

    def close(self,event):
        frame.Destroy()

class UploaderFrame(wx.Frame):
    """ Main Frame holding the Panel. """
    def __init__(self, *args, **kwargs):
        wx.Frame.__init__(self, *args, **kwargs)

        # Add the Panel
        self.Panel = UploaderPanel(self)

    def OnQuit(self, event=None):
        """Exit application."""
        self.Close()

if __name__=='__main__':
    app = wx.App()
    frame = UploaderFrame(None, title="DASe Uploader", size=(755, 535))
    frame.Show()
    app.MainLoop()

