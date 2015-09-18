## Atom Categories ##

scheme uri is used in atom:category@scheme, but can also be used in atom:link@rel when atom:link@href expresses a query of all (pertinent) entities with a corresponding atom:category@scheme with a @term tthat matches the atom:id of the entity holding the atom:link.

note: terms should (often) be an atom:id and if/when they are, automatically set up a child-to-parent relationship with that atom entity, a relationship defined by the scheme.

So...if I use categories for DASe metadata, the term would be the atom:id of the attribute. e.g.:
```
<category term="http://www.laits.utexas.edu/dasebeta/attribute/test/description" label="Description" scheme="http://daseproject.org/schema/attribute">This is a really cool picture of a really cool thing</category>
```
nice (also solves problem of instance - specific scheme).

idea -- what if "everything is an entry" in DASe (feeds, categories, attributes, items, item\_types, set) and had three standard views: entry, category, feed.  Any entity could be made child of another by means of a category w/ term that was the atom:id of the parent (entry).

thought -- categories are the current version of @rev in an html link. http://www.w3.org/TR/html401/struct/links.html#adef-rev.  It defines a link back to here. (??).  Or maybe it is just different "kind" of link -- one that suggests a child of a parent.

so how do we attach a budget item to a grant proposal?

application has many budget items. application and budget\_item are item\_types. We related these in the database. Each budget\_item has this category (w/ item\_type you need to note relationship in scheme uri)
```
<category scheme="http://www.laits.utexas.edu/dasebeta/item_type/grants/application/rel/budget_item" term="http://www.laits.utexas.edu/dasebeta/item/grants/000123"/>

OR

<category scheme="http://www.laits.utexas.edu/dasebeta/item_type/grants/application/has/budget_item" term="http://www.laits.utexas.edu/dasebeta/item/grants/000123"/>
```
and application has this link:
```
<link rel="http://www.laits.utexas.edu/dasebeta/item_type/grants/application/rel/budget_item" href="http://www.laits.utexas.edu/dasebeta/search/grants/000123"/>
```

### take two ###
```
<entry>
   <id>http://www.laits.utexas.edu/dasebeta/item/grants/000123</id>
   <title>pkeane grant application</title>
   <!-- this link is JUST HERE, whether the link actually retrives items or not
        given the fact of the existing relationship for this item type.  So every item
        being serialized to atom need to "lookup" it's item_type in the
        item_type_relations and if it's there, construct this link -->
   <link rel="http://www.laits.utexas.edu/dasebeta/collection/grants/item_type/budget_item" href="http://www.laits.utexas.edu/dasebeta/grants/collection/grants/item_type/budget_item?type=feed&filter=http://www.laits.utexas.edu/dasebeta/item/grants/000123"/>
</entry>

<entry>
   <id>http://www.laits.utexas.edu/dasebeta/item/grants/000124</id>
   <title>budget item: apple powerbook</title>
   <category scheme="http://daseproject.org/category/item_type" term="budget_item"/>
   <!-- likewise, every item must ALSO look in item_category table and if so,
        construct this category (easier than creating the link!) -->
   <category scheme="http://www.laits.utexas.edu/dasebeta/collection/grants/item_type/budget_item" term="http://www.laits.utexas.edu/dasebeta/item/grants/000123" label="budget item for pkeane grant application"/>
</entry>

<entry>
   <id>http://www.laits.utexas.edu/dasebeta/grants/000124</id>
   <title>budget item: printer cartridges</title>
   <category scheme="http://daseproject.org/category/item_type" term="budget_item"/>
   <category scheme="http://www.laits.utexas.edu/dasebeta/collection/grants/item_type/budget_item" term="http://www.laits.utexas.edu/dasebeta/item/grants/000123" label="budget item for pkeane grant application"/>
</entry>

```

**note:** the shared scheme/rel in this thing must always point at a collection (item\_type is a kind of collection).  The category is saying "I am in this collection -- specifically in this role: @term" and the link is saying "I am related to a set of things in that collection -- here's how to retrieve 'em: @href"

...therefore this idea could be used to relate any item to any other item by way of collection OR item\_type

**note:** right now, I do not plan on implementing a cross-collection relationship mechanism in dase.  Presumably, that can be created in a module.

**note:** link@href always points to a **feed** (entry aggregation).  So...category points to an entry while links points to a feed.


what needs relating:

  * item\_type to item\_type
  * item\_type to attributes
  * item to item
  * item to category (easy -- category element)
  * collection to community

some schemes are complete and some schemes should be documented as uri templates.

in vra core -- and image is related to a building.  Say that building is respresented by an item in another item type. Do I grab the "title" from the buildings entry to put in "label" or do I put it as text content of atom:catgory (necessary is I use category to represent attribute/vals).

#### question: how are schemes established? ####


### from wiki: ###

scheme prefix is: http://daseproject.org/category/

based on intrinsic factors:
  * error (error)
  * collection  (collection\_ascii\_id)
  * collection/item\_count
  * entrytype (attribute | collection | item | set)
  * feedtype (collection | collection\_list | search | item | tag)
  * item/type (type\_ascii\_id)
  * tag/count (count of items)
  * position (integer)

can be "set" dynamically (either not required, or there is an obvious default value)
  * collection/visibility (manager | user | public)
  * tag/background (color or hex code)
  * tag/type (set | slideshow | cart | admin)
  * tag/visibility (owner | user | public)
  * attribute/html\_input\_type ('text'|'textarea'|'radio'|'checkbox'|'select'|'listbox'|'no\_edit'|'text\_with\_menu)
  * item/status (public | draft | delete )
tbd: split out read access and write access.