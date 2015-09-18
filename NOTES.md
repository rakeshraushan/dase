  * establish convention for non-public attributes like tmp`_` or x`_`
  * xmldump will **not** include value revision hostory
  * user sets (tags) need to be dumped separately from collections, prob w/ users
  * change 'tag' to 'set' throughout codebase
  * start using meta element in head for page data a la twitter
  * Implement PPD token for added security
  * fancy-up set downloader w/ stuart's code
  * implement db-backed config so users can switch off and on modules
  * add indexes to db schemas
  * clean up dase courses: "delete from tag\_category where id NOT IN (select tag\_category.id from tag,tag\_category where tag\_category.tag\_id = tag.id)"
  * set up cron task to archive/rotate logs