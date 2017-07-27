# Changelog

* **v.3.0.6.3** - 2017-07-27
    - no changes.

* **v.3.0.6.2** - 2017-07-20
    - no changes.

* **v.3.0.6.1** - 2017-05-24
    - no changes.

* **v.3.0.6.0** - 2017-05-05
    - Merge feature/symfony-upgrade-2.8
    - Merge release/3.0.5
    - Clean up popup.js documentation
    - Fix autocomplete.js get local name variable instead of global one
    - Fix use SecurityContext by ACEType and ACLType
    - Merge @hwbllmnn symfony 2.7 upgrade branch
    - Merge remote-tracking branch 'composer/feature/symfony2.7' into release/3.0.6
    - Add fullscreen template region "align" and "closed" properties
    - Clean up LoginController, PasswordController and UserController
    - Remove IE 6-10 template support
    - Remove jquery-1.7.1.js and jquery-ui-1.8.16.min.js
    - Remove ACLSIDHelper
    - Remove old binded jquery libraries
    - Remove and refactor LoginController, PasswordController and UserController imports
    - Added back comments
    - Added LDAP Binding for requests. Added some comments and fixed indentation.
    - Refactor and describe RolesService
    - Merge pull request #26 from mapbender/hotfix/ldap_integration_bundle
    - Fix for LDAP user autorization. Rename to correct entity class.
    - Remove LDAP components to Mapbender/Ldap Bundle as composer 'mapbender/ldap' module
    - Merge pull request #24 from mapbender/hotfix/dropdown-scrolls-background
    - Merge pull request #25 from mapbender/fix/acl-handling
    - Remove and unregister LDAP from FOM
    - Fix internal issue #7093
    - Merge pull request #22 from mapbender/hotfix/sspi_authentication_fix
    - Merge pull request #23 from mapbender/fix/route-name
    - Add names for ACL-routes in ACLController
    - Make login, register, forgot password and restore password screens responsive
    - Fixed Sspi-User Authentication
    - Merge pull request #21 from mapbender/hotfix/accordion-in-accordion
    - Fix 'active' by accordion in accordion
    - Fix set admin page title default
    - Merge pull request #20 from mapbender/hotfix/changelog
    - Intergrate bootstrap and refactor/fix administration SCSS files
    - Fix/Remove displaying pasword resend confirmation screen
    - Fix and refactor login and manager template
    - Fix deprecated call by AnnotatedRouteControllerLoader route  configuring
    - Restrict move popups outside of visible area application
    - Merge pull request #19 from mapbender/hotfix/stored-xss
    - fixed dropdown part of vulnerability
    - Merge hotfix/fix-travis-ci
    - Short user name russian translation
    - Deprecate FOM SharedApplicationWebTestCase
    - Improve tab navigation to use keyboard (TAB)
    - Fix find object ACL (add try-catch block)
    - Add ability to see which security permissions are set for an element (or some other object)
    - Extract administration border radius variables
    - Add new ACL has and get methods
    - Improve login box screen
    - Improve application list navigation
    - Fix embedded login screen if session time is out
    - Improve DoctrineHelper to get create tables for new entities if connection is sqlite
    - Fix xls ExportResponse decode utf-8

* **v.3.0.5.3** - 2016-02-04
    - Improve reset form styles
    - Fix reset password page styling
    - Fix add user group with same prefix
    - Fix select element
    - Fix add group with same prefix in security tab
    - Fix select element global listener
    - Improve scale and srs selector styles
    - Fix FOM composer.json error
    - Merge pull request #18 from mapbender/hotfix/user-activate
    - Update messages.ru.xlf
    - add 'de' translations
    - translate default fos messages, reformate code
    - Merge branch 'release/3.0.5' into hotfix/user-activate
    - Fix reset password email body text
    - 5190 change format of forgot password mail
    - translation typo de
    - Merge branch 'hotfix/user-activate' into release/3.0.5
    - fix activate/deactivate only other users
    - add aktivate a self registrated user
    - Merge pull request #17 from mapbender/hotfix/changelog-5489
    - added changelog.md information

* **v.3.0.5.2** - 2015-10-27
    - Add missed 'Bad credentials' translations for ES, NL, PT #5009
    - Add 'Bad credentials' translating and fix some erroneous russian translations #5009
    - change message
    - formate code, merge message
    - Refactor and remove old properties from FOM/UserBundle/Entity/User
    - fix error flash, formate code
    - Fix checking log in request Closes: #4874, #4885
    - Fix authors
    - Add composer.json file

* **v.3.0.5.1** - 2015-08-26
    - fixed removing of groups
    - fixed filtering of users to keep group info visible
    - added profile form validation
    - fixed delete user
    - backported acl commit fix
    - github #307 update some missing german translations
    - add fom  ru translations

* **v.3.0.5.0** - 2015-07-01
    -  fixed aclmanager reference
    -  fixed file name wrt class name
    -  removed deprecated composer option from .travis.yml
    -  added more portuguese translations
    -  fix 'uid' for MySQL
    -  use admin email from configuration
    -  fixed saving of own user data
    -  fixed own access rights for self registered users
    -  do not assume all people use ldap
    -  fixed registration page layout
    -  do not allow editing of username for normal users
    -  fixed texts for user/group backend
    -  added descriptive text to password reset form
    -  fixed overlapping icons in group user table
    -  mark skel.html.twig as deprecated
    -  use FontAwesomem from composer components in manager.html.twig
    -  use assets from composer components in skel.html.twig
    -  remove using exCanvas for IE8
    -  fix add view port meta configuration
    -  change mobile screen scale dpi=240
    -  added nl translations
    -  fix permissions before login
    -  remove LdapUser ORM annotation
    -  fix entity ldapuser
    -  extend GeoConverterComponent
    -  update ExportResponse.php
    -  extend LdapUser annotations
    -  add triggering "ready" state to the tabcontainer (accordion)
    -  add GeoConverterComponent as "geo.converter" service to convert geometries
    -  extend tabcontainer.js with select method
    -  add hasProfile method to User
    -  Fixing acl apply for ladp users
    -  #PQ-22: fixed error in twig with ldap user
    -  add isAnonymous method to User
    -  Adding LDAP User entity and make profile page optional
    -  remove filter with compass from twigs
    -  remove filter with compass from twigs
    -  fix using entity manager
    -  fix merge error
    -  fix twigs
    -  fix UserProfileListener entityManager using
    -  fix UserController; UserProfileListener; UserSubscriber
    -  fix back office template to get SCSSC  work
    -  fix side pane
    -  Check DB platform for profile uid column name

* **v.3.0.4.1** - 2015-01-23
    - fix closing sidepane.js
    - add sortable expanded choice
    - add sidepane ability to define as closed on start
    - Rename ACL SID when username is changed
    - add 60 px for dropdown
    - fix change dropdown from onchange select
    - fix side pane animation
    - temp files removed
    - fix user bundle scss path
    - fix input place holder handling in IE<10
    - change file mode
    - fix place holder bug in IE 9
    - add vf specific change to UserProfileListener.php
    - fix scss assets path
    - fix side pane animation
    - added IE 8 javascript scripts to the skel.html.twig
    - updated jQuery from 1.9 to 1.11.1
    - fix accordion scrolling area (IE bug)
    - userprofile set uid
    - replace type hinting '=null' with '=NULL'
    - Popup: Added detachOnClose configuration
    - tab container css class changed
    - Layertree: Added option to not detach element on close
    - accordion noActive added
    - fixed doctrine problems when basic profile is not configured
    - list by value
    - ExportResponse added
    - added sep= line to top of csv
    - csvResponce delimiter changed
    - CsvResponse added

* **v3.0.4.0** - 2014-09-12
    - Switched to MIT license
    - added session entity
    - delete ACL with delete
    - region properties (tabs/accordion)
    - fixed application copy bugs
    - Symfony 2.3 upgrade
    - popups can prevent close when unsaved data
    - dynamic user profile insertion
    - enhanced autocomplete with query term preprocessing
    - fixed popup focus behavior
    - travis-ci.org integration for automated tests
    - spanish translations
    - external user/group providers can be configured instead of FOM

