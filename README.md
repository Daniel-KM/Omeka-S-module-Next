Next (module for Omeka S) [archived]
=========================

> **IMPORTANT**
> This module is deprecated and replaced by the modules indicated below.

> __New versions of this module and support for Omeka S version 3.0 and above
> are available on [GitLab], which seems to respect users and privacy better
> than the previous repository.__

[Next] is a module for [Omeka S] that brought together various features too
small to be a full module.

**This module is now deprecated**: all features have been moved to dedicated
modules:

| Feature                       | Moved to                                  |
|-------------------------------|-------------------------------------------|
| Default site slug             | [Common] (`$this->defaultSite()`)         |
| Item set position             | [Block Plus] (`$this->itemSetPosition()`) |
| Unescaped JSON API renderer   | [Api Unescaped Json]                      |
| Breadcrumbs                   | [Menu]                                    |
| Previous/Next resources       | [Block Plus] and [Easy Admin]             |
| Last browse page              | [Block Plus]                              |
| Mirror page                   | [Block Plus]                              |
| Is home page                  | [Block Plus] and [Menu]                   |
| Thumbnail url                 | [Block Plus]                              |
| Cron tasks                    | [Cron] and [Easy Admin]                   |
| Loop items task               | [Easy Admin]                              |
| Public view button            | [Easy Admin]                              |
| Logger in view                | Omeka S core (since v1.4)                 |
| Current site                  | Omeka S core (since v4)                   |
| Columns in browse view        | Omeka S core (since v4)                   |
| Random order                  | Omeka S core (since v3)                   |
| Advanced search               | [Advanced Search]                         |
| Trim / deduplicate values     | [Bulk Edit]                               |
| AbstractModule                | [Common]                                  |
| Xml/zip media type detection  | [Common] and [XML Viewer]                 |
| Job log links                 | [Log]                                     |
| Citation                      | [Bibliography]                            |
| Public resource url           | [Sawa]                                    |
| User site slugs               | [Sawa]                                    |

Fixes for old versions of Omeka S:

- Direct link to logs for jobs ([#1156])
- Search results different with/without spaces ([#1258])
- Button to link to the public page of a resource ([#1259])
- Advanced search "starts with" and "ends with" ([#1274])
- Search a property in a list of values ([#1276])
- Search in a random order ([#1281])
- User bar admin links ([#1283])
- Link to the user in the user permissions page ([#1301])
- View helper for logging ([#1371])
- Mimetype xml zip ([#1464])
- Pretty unescaped json ([#1493])
- Show template in item list ([#1497])

You can safely uninstall this module after installing the replacement modules
listed above, if needed.


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

* Copyright Daniel Berthereau, 2018-2026 (see [Daniel-KM] on GitLab)


[Omeka S]: https://omeka.org/s
[Next]: https://gitlab.com/Daniel-KM/Omeka-S-module-Next
[Common]: https://gitlab.com/Daniel-KM/Omeka-S-module-Common
[Block Plus]: https://gitlab.com/Daniel-KM/Omeka-S-module-BlockPlus
[Api Unescaped Json]: https://gitlab.com/Daniel-KM/Omeka-S-module-ApiUnescapedJson
[Menu]: https://gitlab.com/Daniel-KM/Omeka-S-module-Menu
[Cron]: https://gitlab.com/Daniel-KM/Omeka-S-module-Cron
[Easy Admin]: https://gitlab.com/Daniel-KM/Omeka-S-module-EasyAdmin
[Advanced Search]: https://gitlab.com/Daniel-KM/Omeka-S-module-AdvancedSearch
[Bulk Edit]: https://gitlab.com/Daniel-KM/Omeka-S-module-BulkEdit
[Log]: https://gitlab.com/Daniel-KM/Omeka-S-module-Log
[Bibliography]: https://gitlab.com/Daniel-KM/Omeka-S-module-Bibliography
[XML Viewer]: https://gitlab.com/Daniel-KM/Omeka-S-module-XmlViewer
[Sawa]: https://gitlab.com/Daniel-KM/Omeka-S-module-Sawa
[#1493]: https://github.com/omeka/omeka-s/issues/1493
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
[installing a module]: https://omeka.org/s/docs/user-manual/modules/#installing-modules
[module issues]: https://gitlab.com/Daniel-KM/Omeka-S-module-Next/-/issues
[CeCILL v2.1]: https://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html
[GNU/GPL]: https://www.gnu.org/licenses/gpl-3.0.html
[FSF]: https://www.fsf.org
[OSI]: http://opensource.org
[GitLab]: https://gitlab.com/Daniel-KM
[Daniel-KM]: https://gitlab.com/Daniel-KM "Daniel Berthereau"
