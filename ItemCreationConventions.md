If HTTP\_SLUG is set:

New item will use the slug as the item title AND will use Dase\_Util::makeSerialNumber($slug) for the serial number.  Note that duplicate serial numbers will throw an error

If HTTP\_SLUG is NOT set:

A new serial number will be created AND that serial number will appear as title.

For posted items, id atom:author/atom:name is set, it will be used in item table as "created\_by\_eid" (probably should be dirified!).  If it is not set, http authorized user will be used