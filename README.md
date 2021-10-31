# Drupal Fence

CONTENTS OF THIS FILE
---------------------

* Introduction
* Requirements
* Installation
* Configuration
* Troubleshooting
* FAQ
* Maintainers


INTRODUCTION
------------

Drupal Fence allows a site admin to specify a list of URLs to block users from accessing.

These can include malicious URIs that attempt to exploit vulnerabilities in Drupal or PHP.

Drupal Fence activates very early in the page request lifecycle and checks to see if a request attempts to access any of these routes.

If the request matches, Drupal Fence aborts the request and logs the event in Drupal's flood control system. If a client keeps attempting to access blocked URIs, they will be blocked by flood control.

### URL Blocking

A list of strings to check for and block can be specified at `/admin/config/development/drupal_fence_import`.
The following is used to import URLs:

```
/url-to-block
/another-url-to-block?query
```

E.g `https://drupal.example.com/url-to-block` or `https://drupal.example.com/another-url-to-block?query` will trigger a block.

### Flood Control
Drupal Fence will register blocked requests with flood control. By default, a client will be completely blocked from the site after they exceed 5 violations within the space of an hour. This can be configured at `/admin/config/development/drupal_fence`.

Drupal Fence relies on the client IP address to uniquely identify a client. If your site is behind a load balancer or reverse proxy, please make sure that the `X-Forwarded-For` header is correctly configured. If this isn't configured, Flood Control might block your proxy, resulting in all traffic to your site being dropped!


REQUIREMENTS
------------

This module requires the following:
* Drupal 8.8 and above or Drupal 9.0 and above
* PHP 7+

INSTALLATION
------------

* Install using Composer or downloading the release from Drupal.org.
* Enable using the admin interface or Drush.

CONFIGURATION
------------
### Listener Priority
You can configure Drupal Fence's event listener priority in settings.php using the value `$settings['drupal_fence.listener_priority']`.
If this value isn't configured, the module defaults to a priority of 1000 - this ensures that Drupal Fence always fires after the static page cache.


### Forms
* A config form is available at `/admin/config/development/drupal_fence`. This will allow you to modify Drupal Fence's behaviour.
* A form to add URLs to the blocklist is available at `/admin/config/development/drupal_fence_import`.

TROUBLESHOOTING
---------------
### Locked Out
If you've locked yourself out of your site, do the following:

1) If you have Drush installed, run `drush config-set drupal_fence.settings enabled 0`, then `drush cr`. This will disable Drupal Fence without uninstalling the module.
2) If you do not have Drush, you will need to access the database directly, then truncate the `flood` table. If you still do not have access to the site, you will need to clear the cache by following the instructions at https://www.drupal.org/docs/user_guide/en/prevent-cache-clear.html.


MAINTAINERS
-----------

Current maintainers:
* Ming Quah (FleetAdmiralButter) -
  https://www.drupal.org/u/fleetadmiralbutter