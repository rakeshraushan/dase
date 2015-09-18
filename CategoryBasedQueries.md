Any collection entity (collection, tag, item\_type, etc) in DASe should provide a standard way to search-by-category.  That will retrieve all members that have the category scheme+term indicated in the search.

Typically, a category scheme can be shortened when the base namespace is the same as the dase app\_root.  So a scheme http://www.laits.utexas.edu/dase/scheme/little/things could be represented by "scheme/little/things".  Same applies for schemes beginning "http://daseproject.org/category." (todo: look into implementing).

So, to search over all of the items in "grants" collection that have a category scheme "http://wwww.laits.utexas.edu/dase/collection/grants/item_type/budget_line" and term "grants/000123", you'd perform a "GET" on:

```
http://www.laits.utexas.edu/dase/collection/grants.atom?category={collection/grants/item_type/budget_line}grants/0000123
```

To get all "sets" that are available for course Fine Arts 101, GET:

```
http://www.laits.utexas.edu/dase/tags.atom?category={scheme/utexas/courses}2008_fall_35410_FA_101
```