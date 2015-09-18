This was a post on the Duke Digital Collection Blog on Oct 10, 2008. ( http://library.duke.edu/blogs/digital-collections/2008/10/10/a-metadata-tool-that-scales/).

## Digitization and Description Use Cases ##

### digitization ###

  * Supports identification – an itemized list representing each scan in the collection. This list includes 1st pass descriptive metadata, structural metadata (how the components of an item are related to  one another) and location metadata (box #, folder #, album, chapter, etc) identifiers, item type and dimensions.

  * Check out systems – The ability to create an arbitrary grouping of consecutive components that can be “checked out” by a scanner operator during the scanning/QC process. These units include information about the physical location of the materials and any pertinent information needed to scan. Needs to enable multiple users at the same time.

  * Reporting interface – In conjunction with the checkout system above the system needs to be able to report statistics such as total number of items in a collection, number of items left to scan and/or qc, the average amount of time it take to scan a “unit.”

  * Pulls technical metadata from the image header – Must be able to extract technical metadata from image files.

  * Student worker login access – Restricted read/write access through login credentials

  * Generates image derivatives according to dimension specification – Generation of derivatives via batch processing with the option to change dimensions to fit changing web displays. Must use color profiles and have options to control compression quality.

  * Generates checksums – Creates checksums of files that move through the system to ensure that the files have not become altered.

### description ###

  * Supports Duke Core (multiple metadata schema) metadata creation – Duke Core, a modified version of qualified Dublin Core, is the standard metadata schema developed for digital collections at the Duke libraries by the Metadata Advisory Group.

  * Authority lists — including sharing authority lists between similar projects, setting default dropdowns for all projects/items (e.g. Type), etc.

  * Set mandatory fields and cardinality constraints.

  * Assign values in mass to every item in a collection – Collections often have particular metadata that needs to be applied to every item in the collection (e.g., subject terms, creator, etc.).

  * Find and edit existing records easily.

  * Integrates with digitization workflow.

  * See digital object while editing metadata.  Users should be able to see the digital object while they are creating or editing the corresponding metadata.  Does not have to be the highest-resolution image, but a working version.

  * Displays record status – Allows catalogers to specify the state of a record.  System should allow catalogers to specify this status and list records in a way that provides at-a-glance overview of work remaining within a collection.

  * Handle item-level metadata-only records – Some of our digital collections are metadata only. The tool must allow users to create and edit metadata records that do not have an attached digital object.

  * User Interface simple and intuitive, distributed system.  Could be web-based – The interface should be simple and intuitive, and should allow multiple users to work at the same time, though they should not be able to edit the same record at the same time.  A web-based tool would allow users to work on digital collections from anywhere and would not require them to use a computer with particular software installed.

  * Supports UTF-8 universal character sets – Metadata for Duke’s digital collections often includes special characters (diacritics, non-Roman characters, etc.). The tool must accommodate UTF-8 character sets.


1. Peter Gorman - October 13, 2008

> We’re not just using homegrown systems (Access, FileMaker, etc.), but we’re also in the early stages of building Web-based of the sort you’re describing here. However, we’d love to be able to beg, borrow, or steal one and not have to develop it ourselves.

> The crux, though, is workflow support: different institutions have developed or evolved different kinds of workflow, and a data entry tool too closely bound to a particular way of doing things may make it difficult for other institutions to adopt without completely retooling their internal processes to fit the tool. This is probably unavoidable, as batch operations, authorization, collection assignment, etc. are necessary for efficient data entry, but are also tightly bound to particular models of workflow organization.

> Your functional requirements above are similar to ours; here are some other features that may be useful:

> - support batch import/export of objects
> - allow a single object to belong to many (or no) collections
> - provide for compound objects, where a first-class object’s children may (or may not) be other first-class objects.

> Good luck with the project! Ours started as a “new data entry interface project”, but has evolved into “change the entire infrastructure” - the tail (in hindsight, necessarily) wagged the dog.