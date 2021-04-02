# Drupal Fence
Drupal Fence allows you to specify a list of known malicious URIs that attempt to exploit vulnerabilities in Drupal or PHP.

Drupal Fence activates very early in the page request lifecycle and checks to see if a request attempts to access any of these routes.

If the request matches, Drupal Fence aborts the request and logs the event in Drupal's flood control system. If a client keeps attempting to access blocked URIs, they will be blocked by flood control.

# URL Blocking
Drupal Fence can be configured to block certain requests based on the content of the request path.

A list of strings to check for and block can be specified at `/admin/config/development/drupal_fence_import`.
The following YAML structure is used to import URLs:

```
urls:
  - 'string-to-block'
  - '/another-evil-url'
```

Take care that your imported strings don't contain unescaped ' or " characters - this can cause Drupal's YAML parser to fail.

The example above will block all requests that contain `url-to-block` or `/another-evil-url` as part of the request path.
E.g `https://drupal.example.com/page?string-to-block` or `https://drupal.example.com/another-evil-url`.

# Flood Control
Drupal Fence will register blocked requests with flood control. By default, a client will be completely blocked from the site after they exceed 5 violations within the space of an hour. This can be configured at `/admin/config/development/drupal_fence`.

Drupal Fence relies on the client IP address to uniquely identify a client. If your site is behind a load balancer or reverse proxy, please make sure that the `X-Forwarded-For` header is correctly configured. If this isn't configured, Flood Control might block your proxy, resulting in all traffic to your site being blocked!!

# Locked Out
If you've locked yourself out of your site, do the following:

1) If you have Drush installed, run `drush config-set drupal_fence.settings drupal_fence.enabled 0`, then `drush cr`. This will disable Drupal Fence without uninstalling the module.
2) If you do not have Drush, you will need to access the database directly, then truncate both the `flood` and `drupal_fence_flagged_routes`. If you still do not have access to the site, you will need to clear the cache by following the instructions at https://www.drupal.org/docs/user_guide/en/prevent-cache-clear.html.

# Listener Priority
You can configure Drupal Fence's event listener priority in settings.php using the value `$settings['drupal_fence.listener_priority']`.
If this value isn't configured, the module defaults to a priority of 1000 - this ensures that Drupal Fence always fires regardless of caching, authentication, and the behavior other modules.

However, if you'd like Drupal Fence to fire only on an uncached request, you can reduce this to a value of 150 in settings.php. Then, you can check that this is configured correctly by vising the configuration page at `/admin/config/development/drupal_fence`

# Warning and Disclaimer
Please take necessary precautions (perform database backups, QA, etc) if you're using Drupal Fence on anything critical. Although I have tested it thoroughly, there might still be certain edge cases or configurations that will cause it to misbehave. If you find anything, please feel free to create a new issue and I'll have a look. Feedback is always welcome.

You use this module at your own risk. I will not be responsible for any consequences resulting from the use of this module. This includes, but is not limited to: injury/death, loss of data, loss of revenue, information security breaches, environmental damage and Umbral Calamities.