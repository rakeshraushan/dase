  1. add an item to a DASe collection
    * post atom:entry to collection items collection
    * post atom:entry to a specific itrm\_type collection
    * post a media file to collection media collection
  1. add an attribute
    * post an atom:entry to collection attributes collection
  1. set/replace attribute defined values
    * look for link@rel=http://daseproject.org/relation/defined_values and GET/PUT atomcat doc
  1. add a parent item to an item
    * look at this item's item\_type collection service doc to find app:categories of potential parents, then add a parent as a category, with parent sernum as term and parent item\_type url as
    * retrieve app:categories document listing possible parents by following the link w/ rel of relation/item\_type\_items