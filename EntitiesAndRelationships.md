# Entities #

  * collection
  * item
  * media file
  * attribute
  * item\_type
  * value (key/val) or 'triple' : item(s) -> attribute(p) -> value(o)
  * content
  * comment
  * category (kind of a "meta" entity?)
  * category\_scheme
  * defined value (unattached key/val) or 'double' : attribute(p) -> value(o)
  * set (known as tag)
  * set\_item (known as tag\_item)
  * user
  * manager (triple of user -> auth\_level -> collection)
  * search result


## what do we do with entities? ##

  * add instances
  * delete instances
  * modify instances
  * aggregate instances
  * create relationships
  * undo relationships
  * define new types of relationships?
  * (maybe a relationship is an entity)

## relationships ##

  * collection -> items (default)
  * collection -> attributes
  * item -> key/vals
  * item -> media\_files
  * item\_type -> attributes
  * item\_type -> item\_type
  * tag -> tag\_items (default)

categories are a lightweight way to create new relationship types and to create/delete instances of that relationship.

## relationship patterns ##

typically we try to express a relationship w/ a category element on the children and a link element on the parent.  For known relationships, the category on the child has scheme = URI of entity (abstract) and term = URI of entity (instance/concrete).  So:
```
<entry>
  <category scheme="http://daseproject.org/term/collection" term="http://www.laits.utexas.edu/dasebeta/collection/test"/>
</entry>
```
and on parent:
```
<link rel="http://daseproject.org/term/attributes" href="http://www.laits.utexas.edu/dasebeta/collection/test/attributes"/>
```

Note that known relationships do not have a category scheme in the DB -- they are "known"

Also -- category@term will be a URI when it is an actual atom:id -- no need when it is a "concept"  (like entrytype "item")

relationship-as-entry is another (when do we allow a new relationshipp to be created w/ AtomPub)

How about...to create a category scheme, post an atom entry w/ content "text/uri-list" -- this is the scheme URI and include a title/description.