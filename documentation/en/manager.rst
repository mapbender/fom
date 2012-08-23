Using the FOM Manager
#####################

Configuring the Manager
***********************
The FOM Manager has the following configuration options, here given with their defaults. Change them in your config.yml:

.. code-block:: yaml

   fom_manager:
       route_prefix: /manager # Route prefix to enforce on Manager routes

IMPORTANT: Make sure there's a matching firewall entry in your security.yml - otherwise there's no guarantee that the
manager is actually secured. Unless of course you know what you are doing...

Writing Manager Modules
***********************
Writing manager modules is as easy as writing regular Symfony 2 controller classes. The main difference is that you need
to give the route using a route annotation using the FOM\ManagerBundle\Configuration\Route annotation class. It is
recommended to import that class under a different name, so that the annotation is easily distinguishable from a regular
route annotation:

.. code-block:: php

   use FOM\ManagerBundle\Configuration\Route as ManagerRoute

Using the route annotation enforces the route prefix configured in the bundle configuration (see above). This makes it
easy to set a comment security in your security.yml.

You're still responsible to make sure the current user only has access to function he is allowed to use! The Manager
does not take care of that and in the standard configuration will only enforce that the user is logged in - nothing
more!
