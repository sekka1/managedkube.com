---
layout: post
title: Kubernetes Troubleshooting Walkthrough - Trace ingress no service endpoints
categories: kubernetes Trace ingress no service endpoints k8sbot
keywords: kubernetes Trace ingress no service endpoints k8sbot
# https://jekyll.github.io/jekyll-seo-tag/advanced-usage/#customizing-image-output
# This adds the html metadata "og:image" tags to the page for URL previews
image:
  path: "/assets/logo/M_1000.jpg"
#   height: 100
#   width: 100
description: Trace ingress with no service endpoint
---
{%- include share-bar.html -%}

This post is part of a Troubleshooting Walkthrough Series. I will talk about how to resolve common errors in Kubernetes clusters.

You encounter an error on your ingress where you can't reach your website.

```bash
$ curl example.com/foo -v
*   Trying 172.217.7.14...
* TCP_NODELAY set
* Connected to example.com (172.217.7.14) port 80 (#0)
> GET /foo HTTP/1.1
> Host: example.com
> User-Agent: curl/7.58.0
> Accept: */*
>
< HTTP/1.1 404 Not Found
< Content-Type: text/html; charset=UTF-8
< Referrer-Policy: no-referrer
< Content-Length: 1564
< Date: Wed, 13 Feb 2019 19:28:34 GMT
< X-Cache: MISS from row44proxy-postauth
< Via: 1.1 row44proxy-postauth (squid/3.5.25)
< Connection: keep-alive
<
404
```

Ask @k8sbot for troubleshooting help:

![get ingress](/assets/blog/images/trace-ingress-no-endpoints-1.png)

@k8sbot runs inside of your Kubernetes cluster and gives you diagnostic information
from interacting with the Kubernetes API

![get ingress](/assets/blog/images/workflow/k8sbot-agent-request.png)

K8sbot provides troubleshooting recommendations based on real time information
from your cluster.  It offers relevant suggestions based on what's happening
in your cluster, right now.

![trace ingress](/assets/blog/images/trace-ingress-no-endpoints-2.png)


{%- include blurb-consulting.md -%}

<!-- Blog footer share -->
{%- include blog-footer-share.html -%}

{% include blog-cta-1.html %}
