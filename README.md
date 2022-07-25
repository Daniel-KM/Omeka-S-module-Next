Next (module for Omeka S)
=========================

> __New versions of this module and support for Omeka S version 3.0 and above
> are available on [GitLab], which seems to respect users and privacy better
> than the previous repository.__

[Next] is a module for [Omeka S] that brings together various features too small
to be a full module. They may be integrated in the next release of Omeka S, or
not.


Features (all versions)
-----------------------

### Public

#### Previous/Next resources

Allow to get the previous or the next resources, that simplifies browsing like
in Omeka Classic. An option allows to use it in admin board. For sites, there is
a specific option to limit and to order items and item sets according to a
standard query.

To use it, you need to add this in the item, item set, or media show page of the
theme:

```php
<?php
$plugins = $this->getHelperPluginManager();
$hasNext = $plugins->has('previousResource');
?>
<?php if ($hasNext): ?>
<div class="previous-next-items">
    <?php if ($previous = $this->previousResource($resource)): ?>
    <?= $previous->link($translate('Previous item'), null, ['class' => 'previous-item']) ?>
    <?php endif; ?>
    <?php if ($next = $this->nextResource($resource)): ?>
    <?= $next->link($translate('Next item'), null, ['class' => 'next-item']) ?>
    <?php endif; ?>
</div>
<?php endif; ?>
```

#### Last browse page

Allow to go back to the last list of results in order to browse inside item sets,
items or media after a search without losing the search results. The helper is
used by default in admin resources pages.

#### Is home page

Allow to check if the current page is the home page of the site, like in Omeka Classic.

#### Current site

Allow to get the current site in public view, that may be missing in some cases.

#### Default order of items in item set

Display resources in a specific order in the item set main page in front-end,
for example by title or identifier. This option can be specified differently for
each site.

#### Item set position

Determine the position of an item set in the site.

### Admin

#### Better display of json output in api

The json standard doesn’t require to escape anything, except ", "" and control
characters. But php and Zend escape many other characters by default : tags,
ampersand, apostrophe and overall the slashes "/" and the unicode characters,
making json unreadable in many cases, whereas it’s designed to be readable by
people and machines. So this feature displays the api output as unicode and
unescaped, so it can be readable by people who don’t have a json viewer in their
browser ([#1493]).


Features of older versions (< Omeka 3.2)
----------------------------------------

### Public

#### Breadcrumbs

This feature was moved to module [Menu] and improved since version 3.3.41.

A breadcrumb may be added on resources pages via the command `echo $this->breadcrumbs();`.
The default template is `common/breadcrumbs.phtml`, so the breadcrumb can be
themed. Some options are available too.
By default, the breadcrumb for an item uses the first item set as the parent
crumb. The first item set is the item set with the smallest id. If you want to
use another item set, set it as resource in the property that is set in the main
settings, or in the options of the view helper.

#### Simple mirror page

This block is now managed by module [Block Plus] since version 3.1.2.12.

Allow to use a page as a block, so the same page can be use in multiple sites,
for example the page "About" or "Privacy". Of course, the page is a standard
page and can be more complex with multiple blocks. May be fun.
This is an equivalent for the [shortcode as a page] in Omeka classic too.

#### Thumbnail url

This feature was moved to module [Block Plus] since version 3.3.11.8.

Allow to get the url of the thumbnail of a resource, a page or a site.
Warning: For site, the module [Advanced Search Plus] is needed when there is no
page with a thumbnail (the module provides the api for the url argument `has_thumbnails`).

#### Direct links in user bar

Display direct links to the current resource in the public user bar in order to
simplify edition and management of resources (fix [#1283], included in Omeka S 1.4).
The revert links are available too. They display the resource in the default
site, or in the first one (fix [#1259]).

#### Random order of resources (only for Omeka S version < 3)

Display resources in a random order, for example for a carousel or a featured
resource. Simply add `sort_by=random` to the query when needed, in particular
in the page block `Browse preview` (fix [#1281]).

#### Advanced search by start with, end with, or in list

Allow to do more advanced search in public or admin board on values of the
properties: start with, end with, in list (fix [#1274], [#1276]).
This feature is moved to module [Advanced Search Plus] in Omeka 3.

#### Advanced search with list of used properties and resource classes.

Display only the used properties and resources classes in the advanced search
form, via a site setting (fix [#1423]). This feature is integrated in Omeka 3.

In some cases, a change is required in the theme. In the files `themes/my-theme/common/advanced-search/properties.phtml`
and `themes/my-theme/common/advanced-search/resource-classes.phtml`, add this option
below `apply_templates`:
```php
    'used_terms' => $this->siteSetting('next_search_used_terms'),
```

#### Citation

Since 3.1.2.7, this feature has moved to module [Bibliography], that uses a
template view to easily customize it. The view helper doesn’t change.

### Admin

#### Trim property values

Remove leading and trailing whitespaces preventively on any resource creation or
update, or curatively via the batch edit, so values will be easier to find and
to compare exactly (fix [#1258]).

Warning: This feature is removed in the version for Omeka 3, but is available in
an improved version in module [Bulk Edit].

#### Deduplicate property values

Remove exact duplicated values on any new or updated resource preventively.
Note: preventive deduplication is case sensitive, but curative deduplication is
case insensitive (it uses a direct query and the Omeka database is case
insensitive by default).

Warning: This feature is removed in the version for Omeka 3, but is available in
an improved version in module [Bulk Edit].

#### New links

- From the site permissions to the user page (fix [#1301]).

### Backend

#### Choice of columns in admin browse view

An option is added to select the metadata to display in the main browse view (fix [#1497]).
This option in no more integrated in the module, but available through the pull
request.

#### Logger in view

Allow to use `$this->logger()` in the views (fix [#1371], included in Omeka S 1.4).

#### New links

- From the list of jobs to each log (fix [#1156]), moved to module [Log].

#### AbstractModule

This feature was moved to module [Generic] more simply.

A class to simplify management of generic methods of the module (install and
settings).

#### Better identification of media types for xml and zip files

This feature was moved to module [XML Viewer] since version 3.3.40.

In Omeka core, all xml files are identified as `text/xml` and zip files as `application/zip`,
so it’s not possible to make a distinction between a mets file and an ead file,
or to identify an epub, that is a zipped xhtml. This feature is required to use
the module [Verovio] when files don’t use the extension "mei" ([#1464]).

#### Cron tasks

This feature was moved to module [Easy Admin] since version 3.3.42.

A script allows to run jobs from the command line, even if they are not
initialized. It’s useful to run cron tasks. See required and optional arguments:

```sh
php /path/to/omeka/modules/Next/data/scripts/task.php --help
```

In your cron tab, you can add a task like that:

```sh
/bin/su - www-data -C "php /var/www/omeka/modules/Next/data/scripts/task.php" --task MyTask --user-id 1
```

Note: since there is no job id, the job should not require it (for example,
method `shouldStop()` should not be called. The use of the abstract class `AbstractTask`,
that extends `AbstractJob`, is recommended, as it takes care of this point.

#### Loop items task

This feature was moved to module [Easy Admin] and improved since version 3.3.42.

A task (job) allows to update all items, so all the modules that uses api events
are triggered. This job can be use as a one-time task that help to process
existing items when a new feature is added in a module.

```sh
php /path/to/omeka/modules/Next/data/scripts/task.php --task LoopItems --user-id 1
```


Installation
------------

Uncompress files and rename module folder `Next`. Then install it like any
other Omeka module and follow the config instructions.

See general end user documentation for [Installing a module].


TODO
----

- [x] Normalize the breadcrumbs with Laminas navigation (module [Menu]).
- [ ] Move all navigation and theme helpers to module BlockPlus.
- [ ] Site permission links to user page (v3)


Warning
-------

Use it at your own risk.

It’s always recommended to backup your files and your databases and to check
your archives regularly so you can roll back if needed.


Troubleshooting
---------------

See online issues on the [module issues] page on GitLab.


License
-------

This module is published under the [CeCILL v2.1] license, compatible with
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

* Copyright Daniel Berthereau, 2018-2022 (see [Daniel-KM] on GitLab)


[Omeka S]: https://omeka.org/s
[Next]: https://gitlab.com/Daniel-KM/Omeka-S-module-Next
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
[Bibliography]: https://gitlab.com/Daniel-KM/Omeka-S-module-Bibliography
[Generic]: https://gitlab.com/Daniel-KM/Omeka-S-module-Generic
[Advanced Search Plus]: https://gitlab.com/Daniel-KM/Omeka-S-module-AdvancedSearchPlus
[Block Plus]: https://gitlab.com/Daniel-KM/Omeka-S-module-BlockPlus
[Bulk Edit]: https://gitlab.com/Daniel-KM/Omeka-S-module-BulkEdit
[Menu]: https://gitlab.com/Daniel-KM/Omeka-S-module-Menu
[Easy Admin]: https://gitlab.com/Daniel-KM/Omeka-S-module-EasyAdmin
[Log]: https://gitlab.com/Daniel-KM/Omeka-S-module-Log
[Verovio]: https://gitlab.com/Daniel-KM/Omeka-S-module-Verovio
[XML Viewer]: https://gitlab.com/Daniel-KM/Omeka-S-module-XmlViewer
[Installing a module]: http://dev.omeka.org/docs/s/user-manual/modules/#installing-modules
[module issues]: https://gitlab.com/Daniel-KM/Omeka-S-module-Next/-/issues
[CeCILL v2.1]: https://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html
[GNU/GPL]: https://www.gnu.org/licenses/gpl-3.0.html
[FSF]: https://www.fsf.org
[OSI]: http://opensource.org
[MIT]: http://opensource.org/licenses/MIT
[GitLab]: https://gitlab.com/Daniel-KM
[Daniel-KM]: https://gitlab.com/Daniel-KM "Daniel Berthereau"
