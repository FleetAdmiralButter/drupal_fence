# Drupal Fence
Drupal Fence allows you to specify a list of known malicious URIs that attempt to exploit vulnerabilities in Drupal or PHP.

Drupal Fence activates very early in the page request lifecycle and checks to see if a request attempts to access any of these routes.

If the request matches, Drupal Fence aborts the request and logs the event in Drupal's flood control system. If a client keeps attempting to access blocked URIs, they will be blocked by flood control.

# URL Blocking
Drupal Fence can be configured to block certain requests based on the content of the request path.

A list of strings to check for and block can be specified at /admin/config/development/drupal_fence_import.
The following YAML structure is used to import URLs:

```
urls:
  - 'string-to-block'
  - '/another-evil-url'
```

Take care that your imported strings don't contain unescaped ' or " characters - this can cause Drupal's YAML parser to fail.

The example above will block all requests that contain `url-to-block` or `/another-evil-url` as part of the request path.
E.g https://drupal.example.com/page?string-to-block or https://drupal.example.com/another-evil-url.

# Flood Control
Drupal Fence will register blocked requests with flood control. By default, a client will be completely blocked from the site after they exceed 5 violations within the space of an hour. This can be configured at /admin/config/development/drupal_fence.

Drupal Fence relies on the client IP address to uniquely identify a client. If your site is behind a load balancer or reverse proxy, please make sure that the `X-Forwarded-For` header is correctly configured. If this isn't configured, Flood Control might block your proxy, resulting in all traffic to your site being blocked!!

# Warning and Disclaimer
Please, please, do not use Drupal Fence on anything important just yet - it is far from production ready.

You use this module at your own risk. I will not be responsible for any consequences resulting from the use of this module. This includes, but is not limited to: injury/death, loss of data, loss of revenue, information security breaches, environmental damage and Umbral Calamities.