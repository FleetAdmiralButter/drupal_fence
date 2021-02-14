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

# Warning and Disclaimer

Please, please, do not use Drupal Fence on anything important just yet - it is far from production ready.

You use this module at your own risk. I will not be responsible for any consequences resulting from the use of this module. This includes, but is not limited to: injury/death, loss of data, loss of revenue, information security breaches, environmental damage and Umbral Calamities.