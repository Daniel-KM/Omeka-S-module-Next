Next (module for Omeka S)
=========================

[Next] is a module for [Omeka S] that brings together various features too small
to be a full module. They may be integrated in the next release of Omeka S, or
not.

Features
--------

### Public

#### Simple page

Allow to use a page as a block, so the same page can be use in multiple sites,
for example the page "About" or "Privacy". Of course, the page is a standard
page and can be more complex with multiple blocks. May be fun.
This is an equivalent for the [shortcode as a page] in Omeka classic too.
This block is now managed by module [Block Plus] since version 3.1.2.12.

#### Direct links in user bar

Display direct links to the current resource in the public user bar in order to
simplify edition and management of resources (fix [#1283], included in Omeka S 1.4).
The revert links are available too. They display the resource in the default
site, or in the first one (fix [#1259]).

#### Default order of items in item set

Display resources in a specific order in the item set main page in front-end,
for example by title or identifier. This option can be specified differently for
each site.

#### Random order of resources

Display resources in a random order, for example for a carousel or a featured
resource. Simply add `sort_by=random` to the query when needed, in particular
in the page block `Browse preview` (fix [#1281]).

#### Advanced search by start with, end with, or in list

Allow to do more advanced search in public or admin board on values of the
properties: start with, end with, in list (fix [#1274], [#1276]).

#### Advanced search with list of used properties and resource classes.

Display only the used properties and resources classes in the advanced search
form, via a site setting (fix [#1423]).

In some cases, a change is required in the theme. In the files `themes/my-theme/common/advanced-search/properties.phtml`
and `themes/my-theme/common/advanced-search/resource-classes.phtml`, add this option
below `apply_templates`:
```php
    'used_terms' => $this->siteSetting('next_search_used_terms'),
```

#### Citation

Since 3.1.2.7, this feature has moved to module [Bibliography], that uses a
template view to easily customize it. The view helper doesn’t change.

#### Is home page

Allow to check if the current page is the home page of the site, like in Omeka Classic.

#### Item set position

Determine the position of an item set in the site.

#### Breadcrumbs

A breadcrumb may be added on resources pages via the command `echo $this->breadcrumbs();`.
The default template is `common/breadcrumbs.phtml`, so the breadcrumb can be
themed. Some options are available too.
By default, the breadcrumb for an item uses the first item set as the parent
crumb. The first item set is the item set with the smallest id. If you want to
use another item set, set it as resource in the property that is set in the main
settings, or in the options of the view helper.

#### Previous/Next resources

Allow to get the previous or the next resources, that simplifies browsing like
in Omeka Classic. Set by default in admin board for items.

#### Thumbnail url

Allow to get the url of the thumbnail of a resource, a page or a site. The core
allows to get only the full image tag.

#### Last browse page

Add a button in admin resources pages to go back to the last list of results. It
allows to browse inside item sets, items or media after a search without losing
the search results. A helper allows to get the same feature in public front-end.

#### Current site

Allow to get the current site in public view, that may be missing in some cases.

#### Block "search form"

Allow to add a search form in any page, typically in the home page.
This block is now managed by module [Block Plus] since version 3.1.2.12.

### Admin

#### Trim property values

Remove leading and trailing whitespaces preventively on any resource creation or
update, or curatively via the batch edit, so values will be easier to find and
to compare exactly (fix [#1258]).

Warning: This feature is still available, but is improved in module [Bulk Edit].

#### Deduplicate property values

Remove exact duplicated values on any new or updated resource preventively.
Note: preventive deduplication is case sensitive, but curative deduplication is
case insensitive (it uses a direct query and the Omeka database is case
insensitive by default).

Warning: This feature is still available, but is improved in module [Bulk Edit].

#### Choice of columns in admin browse view

An option is added to select the metadata to display in the main browse view (fix [#1497]).

#### New links

- From the site permissions to the user page (fix [#1301]).
- From the list of jobs to each log (fix [#1156]).

#### Better identification of media types for xml and zip files

In Omeka core, all xml files are identified as `text/xml` and zip files as `application/zip`,
so it’s not possible to make a distinction between a mets file and an ead file,
or to identify an epub, that is a zipped xhtml. This feature is required to use
the module [Verovio] when files don’t use the extension "mei" ([#1464]).

#### Better display of json output in api

The json standard doesn’t require to escape anything, except ", "" and control
characters. But php and Zend escape many other characters by default : tags,
ampersand, apostrophe and overall the slashes "/" and the unicode characters,
making json unreadable in many cases, whereas it’s designed to be readable by
people and machines. So this feature displays the api output as unicode and
unescaped, so it can be readable by people who don’t have a json viewer in their
browser ([#1493]).

### Backend

#### Cron tasks

A script allows to run jobs from the command line, even if they are not
initialized. It’s useful to run cron tasks. See required and optional arguments:

```
php /path/to/omeka/modules/Next/data/scripts/task.php --help
```

In your cron tab, you can add a task like that:

```
/bin/su - www-data -C "php /var/www/omeka/modules/Next/data/scripts/task.php --task MyTask --user-id 1
```

Note: since there is no job id, the job should not require it (for example,
method `shouldStop()` should not be called. The use of the abstract class `AbstractTask`,
that extends `AbstractJob`, is recommended, as it takes care of this point.

#### Loop items task

A task (job) allows to update all items, so all the modules that uses api events
are triggered. This job can be use as a one-time task that help to process
existing items when a new feature is added in a module.

```
php /path/to/omeka/modules/Next/data/scripts/task.php --task LoopItems --user-id 1
```

#### Logger in view

Allow to use `$this->logger()` in the views (fix [#1371], included in Omeka S 1.4).

#### AbstractModule

A class to simplify management of generic methods of the module (install and
settings). This part is now managed in module [Generic] more simply.


Installation
------------

Uncompress files and rename module folder `Next`. Then install it like any
other Omeka module and follow the config instructions.

See general end user documentation for [Installing a module].


Warning
-------

Use it at your own risk.

It’s always recommended to backup your files and your databases and to check
your archives regularly so you can roll back if needed.


Troubleshooting
---------------

See online issues on the [module issues] page on GitHub.


License
-------

This module is published under the [CeCILL v2.1] licence, compatible with
[GNU/GPL] and approved by [FSF] and [OSI].

In consideration of access to the source code and the rights to copy, modify and
redistribute granted by the license, users are provided only with a limited
warranty and the software’s author, the holder of the economic rights, and the
successive licensors only have limited liability.

In this respect, the risks associated with loading, using, modifying and/or
developing or reproducing the software by the user are brought to the user’s
attention, given its Free Software status, which may make it complicated to use,
with the result that its use is reserved for developers and experienced
professionals having in-depth computer knowledge. Users are therefore encouraged
to load and test the suitability of the software as regards their requirements
in conditions enabling the security of their systems and/or data to be ensured
and, more generally, to use and operate it in the same conditions of security.
This Agreement may be freely reproduced and published, provided it is not
altered, and that no provisions are either added or removed herefrom.


Copyright
---------

* Copyright Daniel Berthereau, 2018-2020 (see [Daniel-KM] on GitHub)


[Omeka S]: https://omeka.org/s
[Next]: https://github.com/Daniel-KM/Omeka-S-module-Next
[shortcode as a page]: https://github.com/omeka/plugin-SimplePages/pull/24
[#1156]: https://github.com/omeka/omeka-s/issues/1156
[#1258]: https://github.com/omeka/omeka-s/issues/1258
[#1259]: https://github.com/omeka/omeka-s/issues/1259
[#1274]: https://github.com/omeka/omeka-s/issues/1274
[#1276]: https://github.com/omeka/omeka-s/issues/1276
[#1281]: https://github.com/omeka/omeka-s/issues/1281
[#1283]: https://github.com/omeka/omeka-s/issues/1283
[#1301]: https://github.com/omeka/omeka-s/issues/1301
[#1371]: https://github.com/omeka/omeka-s/issues/1371
[#1464]: https://github.com/omeka/omeka-s/issues/1464
[#1493]: https://github.com/omeka/omeka-s/issues/1493
[#1497]: https://github.com/omeka/omeka-s/issues/1497
[Bibliography]: https://github.com/Daniel-KM/Omeka-S-module-Bibliography
[Generic]: https://github.com/Daniel-KM/Omeka-S-module-Generic
[Block Plus]: https://github.com/Daniel-KM/Omeka-S-module-BlockPlus
[Bulk Edit]: https://github.com/Daniel-KM/Omeka-S-module-BulkEdit
[Verovio]: https://github.com/Daniel-KM/Omeka-S-module-Verovio
[Installing a module]: http://dev.omeka.org/docs/s/user-manual/modules/#installing-modules
[module issues]: https://github.com/Daniel-KM/Omeka-S-module-Next/issues
[CeCILL v2.1]: https://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html
[GNU/GPL]: https://www.gnu.org/licenses/gpl-3.0.html
[FSF]: https://www.fsf.org
[OSI]: http://opensource.org
[MIT]: http://http://opensource.org/licenses/MIT
[Daniel-KM]: https://github.com/Daniel-KM "Daniel Berthereau"
