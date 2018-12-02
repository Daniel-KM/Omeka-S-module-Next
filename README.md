Next (module for Omeka S)
=========================

[Next] is a module for [Omeka S] that brings together various features too small
to be a full module. They may be integrated in the next release of Omeka S, or
not.

- Logger in view: allow to use `$this->logger()->err()` in the views.
- Simple page: allow to use a page as a block, so the same page can be use in
  multiple sites, for example the page "About" or "Privacy". Of course, the page
  is a standard page and can be more complex with multiple blocks. May be fun.
  This is an equivalent for the [shortcode as a page] in Omeka classic too.
- Trim property values: remove leading and trailing whitespaces preventively on
  any resource creation or update, or curatively via the batch edit, so values
  will be easier to find and to compare exactly (fix [#1258]).


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


Contact
-------

Current maintainers:

* Daniel Berthereau (see [Daniel-KM] on GitHub)


Copyright
---------

* Copyright Daniel Berthereau, 2018


[Omeka S]: https://omeka.org/s
[Next]: https://github.com/Daniel-KM/Omeka-S-module-Next
[shortcode as a page]: https://github.com/omeka/plugin-SimplePages/pull/24
[#1258]: https://github.com/omeka/omeka-s/issues/1258
[Installing a module]: http://dev.omeka.org/docs/s/user-manual/modules/#installing-modules
[module issues]: https://github.com/Daniel-KM/Omeka-S-module-Next/issues
[CeCILL v2.1]: https://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html
[GNU/GPL]: https://www.gnu.org/licenses/gpl-3.0.html
[FSF]: https://www.fsf.org
[OSI]: http://opensource.org
[MIT]: http://http://opensource.org/licenses/MIT
[Daniel-KM]: https://github.com/Daniel-KM "Daniel Berthereau"
