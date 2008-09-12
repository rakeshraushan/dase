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

dase_host = 'littlehat.com'
coll = ''

class UploaderPanel(wx.Panel):
    def __init__(self, parent, *args, **kwargs):
        """Create the UploaderPanel."""
        wx.Panel.__init__(self, parent, *args, **kwargs)

        self.parent = parent 
        atom_ns = "http://www.w3.org/2005/Atom"
        d = minidom.parse(urllib.urlopen('http://littlehat.com/dase/collections.atom?get_all=1'))
        entries = d.getElementsByTagNameNS(atom_ns,'entry')
        coll_dict = {}
        colls = []
        for entry in entries:
            title = entry.getElementsByTagNameNS(atom_ns,'title')[0].firstChild.nodeValue
            colls.append(title)
            coll_dict[title] = entry.getElementsByTagNameNS(atom_ns,'id')[0].firstChild.nodeValue.split('/').pop()
        chooser = wx.Choice(self, -1, (85, 18), choices=colls)
        chooser.Bind(wx.EVT_CHOICE,self.choose_coll)
        dirButton = wx.Button(self, label='Select Directory')
        dirButton.Bind(wx.EVT_BUTTON, self.picker)
        uploadButton = wx.Button(self, label='Upload')
        uploadButton.Bind(wx.EVT_BUTTON, self.upload_file)
        closeButton = wx.Button(self, label='Close')
        closeButton.Bind(wx.EVT_BUTTON, self.close)
        self.directory = wx.TextCtrl(self)
        username = wx.TextCtrl(self)
        password = wx.TextCtrl(self,-1,'',(0,0),(0,0),wx.TE_PASSWORD)
        username_label = wx.StaticText(self, -1,"Username: ",wx.Point(0,0),wx.Size(80,-1),wx.ALIGN_RIGHT)
        password_label = wx.StaticText(self, -1,"Password: ",wx.Point(0,0),wx.Size(80,-1),wx.ALIGN_RIGHT)
        self.contents = wx.TextCtrl(self, style=wx.TE_MULTILINE | wx.HSCROLL)
        hbox = wx.BoxSizer()
        hbox.Add(chooser, proportion=1)
        hbox.Add(self.directory, proportion=1, flag=wx.EXPAND)
        hbox.Add(dirButton, proportion=0, flag=wx.LEFT, border=5)
        hbox2 = wx.BoxSizer()
        hbox2.Add(username_label)
        hbox2.Add(username, proportion=1, flag=wx.EXPAND)
        hbox2.Add(password_label)
        hbox2.Add(password, proportion=1, flag=wx.EXPAND)
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
            self.contents.SetValue('hhii')
            dialog.Destroy

    def upload_file(self,event):
        u = username.GetValue()
        p = password.GetValue()
        coll = coll_dict[colls[chooser.GetSelection()]]
        if not coll:
            return
        if '401' == str(checkAuth(dase_host,coll,u,p)):
            self.contents.SetValue('unauthorized')
            return
        path = directory.GetValue()+'/'
        for f in os.listdir(path):
            if not fnmatch.fnmatch(f,'.*'):
                (mime_type,enc) = mimetypes.guess_type(path+f)
                txt = contents.GetValue()
                print f
                #status = postFile(path,f,dase_host,coll,mime_type,u,p)
                #contents.SetValue(txt+"\n"+"uploading "+f+"("+status+")")

    def postFile(self,path,filename,dase_host,coll,mime_type,u,p):
        auth = 'Basic ' + string.strip(base64.encodestring(u + ':' + p))
        f = file(path+filename, "rb")
        body = f.read()                                                                     
        http = httplib.HTTP(dase_host);
        http.putrequest("POST",'/dase1/media/'+coll)
        http.putheader("Content-Type",mime_type);
        http.putheader("Content-Length",str(len(body)))
        http.putheader('Authorization', auth )
        http.endheaders()
        http.send(body)
        errcode,errmsg,headers = http.getreply()
        return str(errcode) 

    def checkAuth(self,dase_host,coll,u,p):
        auth = 'Basic ' + string.strip(base64.encodestring(u + ':' + p))
        body = ''                                                                     
        http = httplib.HTTP(dase_host);
        http.putrequest("POST",'/dase/collection/'+coll)
        http.putheader('Authorization', auth )
        http.endheaders()
        http.send(body)
        errcode,errmsg,headers = http.getreply()
        return str(errcode) 

    def choose_coll(self,event):
        coll = coll_dict[colls[chooser.GetSelection()]]
        print coll

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

