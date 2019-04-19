gcp-install-nginx
==================

The GCP Marketplace requires a page that accepts a POST request during the sign
up process.  Since this is a static site running on Github Pages, it does not
support a POST request.  

We have to run this page our self and accept that POST requests.

We are running an Nginx server serving out just the gcp install page on a POST
endpoint.


```
docker run \
-it \
--net=host \
garland/k8sbot-gcp-install:1.0
```
