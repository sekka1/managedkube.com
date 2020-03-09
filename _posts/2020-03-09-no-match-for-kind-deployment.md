---
layout: post
title: No Match for Kind Deployment in version extensions/v1beta1
categories: No Match for Kind Deployment in version extensions/v1beta1
keywords:  Match Kind Deployment extensions/v1beta1
# https://jekyll.github.io/jekyll-seo-tag/advanced-usage/#customizing-image-output
# This adds the html metadata "og:image" tags to the page for URL previews
image:
  path: "/assets/logo/M_1000.jpg"
#   height: 100
#   width: 100
description: A troubleshooting guide for deployments
---

{%- include share-bar.html -%}

I recently ran into this when applying a Deployment that has always worked for me in my repo.

```
# kubectl create -f deployment.yaml 
error: unable to recognize "deployment.yaml": no matches for kind "Deployment" in version "extensions/v1beta1"
```

One recent change I did do, was upgrade to Kubernetes version 1.16.

Googling around, I found out that the `Deployment` version of `extensions/v1beta1` was deprecated and to use the new version.  Just like the error told me to =).

# The old deployment yaml:

```
---
apiVersion: extensions/v1beta1
kind: Deployment
metadata:
  name: echoserver
...
...
...
```

# The new deployment yaml:

```
apiVersion: apps/v1
kind: Deployment
metadata:
  name: echoserver
...
...
...
```

Notice the `apiVersion` changed.

By changing that, I was able to deploy.

More info about deprecation: [https://kubernetes.io/blog/2019/07/18/api-deprecations-in-1-16/](https://kubernetes.io/blog/2019/07/18/api-deprecations-in-1-16/)



{% include blog-cta-1.html %}

# More troubleshooting blog posts

* <A HREF="https://managedkube.com/kubernetes/k8sbot/troubleshooting/pending/pod/2019/02/22/pending-pod.html">Kubernetes Troubleshooting Walkthrough - Pending pods</a>
* <A HREF="https://managedkube.com/kubernetes/trace/ingress/service/port/not/matching/pod/k8sbot/2019/02/13/trace-ingress.html">Kubernetes Troubleshooting Walkthrough - Tracing through an ingress</a>
* <A HREF="https://managedkube.com/kubernetes/k8sbot/troubleshooting/imagepullbackoff/2019/02/23/imagepullbackoff.html">Kubernetes Troubleshooting Walkthrough - imagepullbackoff</a>

<!-- Blog footer share -->
{%- include blog-footer-share.html -%}


