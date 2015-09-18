## Atom Categories ##
_atom categories are used for DASe-specific metadata. some are actionable_

note: URL prefix is always http://daseproject.org/category

note: maybe there should be BOTH /properties and /entities namespaces

| category scheme URL | category type it applies to | possible "term" values | can be changed w/ PUT | identity, membership, or property |
|:--------------------|:----------------------------|:-----------------------|:----------------------|:----------------------------------|
| /type               | all                         | item, collection, attribute, tag, search collection\_list, attribute\_list, comment | no                    | membership                        |
| /collection         | collection                  | {collection\_ascii\_id} | no                    | membership                        |
| /item\_type         | item                        | {item\_type\_ascii\_id} |  yes                  | membership                        |
| /parent\_item\_type | item                        | {item\_type\_ascii\_id} | yes                   | membership                        |
| /parent\_item\_type | attribute                   | {item\_type\_ascii\_id} | yes                   | membership                        |
| /item\_type/{collection\_ascii\_id}/{item\_type\_ascii\_id} | item (this is a parent item) | {serial\_number}       | yes                   | membership                        |
| /item\_count        | collection, tag             | (int)                  |  no                   | property                          |
| /position           | item, attribute             | (int)                  |  yes                  | property                          |
| /tag\_type          | tag                         | slideshow, cart, set, ?? | yes                   | property                          |
| /status             | any                         | draft, delete, public  |  yes                  | property                          |
| /visibility         | any                         | owner, group, public   | yes                   | property                          |
| /html\_input\_type  | attribute                   | text, textarea, select, radio, checkbox, noedit, list | yes                   | property                          |
| /background         | tag, tag\_item              | hex code or colorname  | yes                   | property                          |
| /metadata           | item                        | term is fully qualified att\_ascii\_id, value is value | yes                   | property+                         |
| /private\_metadata  | item                        | term is fully qualified att\_ascii\_id, value is value | no (use /metadata)    | property+                         |
| /admin\_metadata    | item                        | value                  | no                    | property                          |
| /applies\_to        | category\_scheme            | attribute,collection,item,set,set\_item,user | no                    | property                          |
| /base\_url          | all                         | app\_root              | no                    | property                          |



figure out how tag visibility overrides collection visibility for items in tag.


## Atom Link relations ##

prefix is: http://daseproject.org/relation/

  * collection (a link to the collection that this feed/entry applies to)
  * collection/attributes (link to the attributes for this collection)
  * feed-link (used to populate the "up" link on a search item)
  * search-item (w/in a search result, appearing in an entry)
  * item\_type/attributes (links the attributes for this item\_type)
  * childfeed (both application/atom+xml and application/json available)
  * parent