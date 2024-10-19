# CMS / Software Scanner for AlternC

This is a module for [AlternC](https://alternc.org/) control panel hosting software, that creates reports of installed CMS (or other PHP software) installed in a server.

Each user can ask for a scan, and the admin can ask for the entire server to be scanned again.

There is a default scheduled task that rescan the installed software every month, which can be tuned to daily of weekly update via AlternC variables in admin control panel.

The admin can see the entire server report in the "Administrator Panel" menu,

This program depends on the [Cms Scanner](https://octoforge.fr/octopuce/cmsscanner) coded and maintained by [Octopuce](https://www.octopuce.fr/). If you want to contribute to the detection of other software, pleae contribute there.

This is a Debian package, you should be able to compile it using `debuild` or `dpkg-buildpackage` command.

This package can be installed using [AlternC's Debian repository](https://debian.alternc.org/)

