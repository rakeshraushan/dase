import atomlib
from xml.etree import cElementTree as ET

def _indent(elem, level=0):
    i = "\n" + level*"  "
    if len(elem):
        if not elem.text or not elem.text.strip():
            elem.text = i + "  "
        for e in elem:
            _indent(e, level+1)
        if not e.tail or not e.tail.strip():
            e.tail = i
    if level and (not elem.tail or not elem.tail.strip()):
            elem.tail = i

filename = 'solr.atom'

atom = atomlib.Atom.fromFile(filename)

root = ET.Element("add")
doc = ET.SubElement(root,"doc")
id_field = ET.SubElement(doc,"field")
id_field.set('name','_id')
parts = atom.id.split('/')
id_field.text = parts[2]+'/'+parts[3]

updated_field = ET.SubElement(doc,"field")
updated_field.set('name','_updated')
updated_field.text = atom.updated 

for cat in atom.getCategories('http://daseproject.org/category/collection'):
    metadata_field = ET.SubElement(doc,"field")
    metadata_field.set('name','c')
    metadata_field.text = cat['term']
    metadata_field = ET.SubElement(doc,"field")
    metadata_field.set('name','collection')
    metadata_field.text = cat['label']

for cat in atom.getCategories('http://daseproject.org/category/item_type'):
    metadata_field = ET.SubElement(doc,"field")
    metadata_field.set('name','item_type')
    metadata_field.text = cat['term']
    metadata_field = ET.SubElement(doc,"field")
    metadata_field.set('name','item_type_name')
    metadata_field.text = cat['label']

search_text = '';

for cat in atom.getCategories('http://daseproject.org/category/metadata'):
    if cat.has_key('text') and cat.has_key('label'):
        metadata_field = ET.SubElement(doc,"field")
        metadata_field.set('name',cat['label'])
        metadata_field.text = cat['text']
        metadata_field = ET.SubElement(doc,"field")
        metadata_field.set('name',cat['term'])
        metadata_field.text = cat['text']
        search_text = search_text+' '+cat['text']

admin = '';

for cat in atom.getCategories('http://daseproject.org/category/admin_metadata'):
    if cat.has_key('text') and cat.has_key('label'):
        metadata_field = ET.SubElement(doc,"field")
        metadata_field.set('name',cat['label'])
        metadata_field.text = cat['text']
        metadata_field = ET.SubElement(doc,"field")
        metadata_field.set('name',cat['term'])
        metadata_field.text = cat['text']
        admin = admin+' '+cat['text']

field = ET.SubElement(doc,"field")
field.set('name','_search_text')
field.text = search_text.strip() 

field = ET.SubElement(doc,"field")
field.set('name','admin')
field.text = admin.strip() 

atom_fh = open(filename)
atom = atom_fh.read()
encoded_atom = atom.replace('<','&lt;').replace('>','&gt;').replace('"','&quot;')

field = ET.SubElement(doc,"field")
field.set('name','atom')
field.text = encoded_atom 

_indent(root)
print ET.tostring(root) 


