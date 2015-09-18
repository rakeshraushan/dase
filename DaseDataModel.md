![http://daseproject.org/images/dase_er.001.jpg](http://daseproject.org/images/dase_er.001.jpg)

  * Each DASe deployment (a.k.a. Archive) is comprised of Collections.
  * Each Collection contains Items and an Item can only be in one collection.
  * Items have Media Files associated with them and a Media File can only be associated with one Item.  Typically, multiple files associated with an item will represent different representations of the same resource (e.g., a small, medium, and large version of an image).
  * Items have metadata associated with them.  The metadata takes the form of key-value pairs.  (DASe uses the word "attribute" instead of "key").  Items can have any number of key-value pairs _and_ can have multiple values for any key.
  * The Attributes (keys) used to describe an item are limited to the set of Attributes defined by the Collection.  The DASe data model restricts an Attribute to only one collection, but common Attributes (Title, Description. Keyword, Rights) _can_ appear in many Collections and can thus be collated, based on the name (not the internal id) of the Attribute.
  * Users can group Items into Sets for sharing, displaying, downloading, etc.  A Set can be comprised of Items from multiple Collections.