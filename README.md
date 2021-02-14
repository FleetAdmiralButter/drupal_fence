# Drupal Fence

Drupal Fence allows you to specify a list of known malicious URIs that attempt to exploit vulnerabilities in Drupal or PHP.

Drupal Fence activates very early in the page request lifecycle and checks to see if a request attempts to access any of these routes.

If the request matches, Drupal Fence aborts the request and logs the event in Drupal's flood control system. If a client keeps attempting to access blocked URIs, they will be blocked by flood control.

# Importing URLs
A list of malicious URLs to block can be specified at /admin/config/development/drupal_fence_import.

The following YAML structure is used to import URLs:

```
urls:
  - '/url-to-block'
  - '/another-evil-url'
```

