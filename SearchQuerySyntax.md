# search syntax: #
  * query name is 'q'
  * include phrases in quotes (' or ")
  * use '-' to omit a word or phrase
  * for attribute searches, name is '

<coll\_ascii\_id>

~

<attribute\_ascii\_id>

'
  * use tilde (as in example) for auto substring/phrase search
  * use single period to match exact value\_text string (case-insensitive)
  * add more attribute searches (refinements) by adding them to the query string. Note that the use of '.' or '~' in a query parameter name that is NOT part of the search will make the search fail (since it'll be interpreted as an attribute search

  * also...prepend query term with att\_ascii\_id to limit to that attribute ascii (allows cross-coll searches!) that's the 'qualified' search

  * NOTE: everything is assumed to be "and."
  * The word 'and' has **no** boolean significance.
  * Parentheses have not functional significance.


## exact match: ##
test.title=farewell+to+arms

## match substring: ##
test~title=farewell+to+a

## match item\_type: ##
type=test:picture

## qualified search: ##
q=title:farewell+to+arms