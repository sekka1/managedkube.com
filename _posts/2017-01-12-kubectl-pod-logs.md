---
layout: post
title: "Kubectl Pod logs"
categories: Aws Kubernetes Logs
keywords: Aws Kubernetes Logs
author: Garland Kan

---

There are a few useful options when getting logs from your Kubernetes pods:

* tailing
* showing the last x number of lines
* combining options

### tailing

This will tail and follow the pod’s logs

``` yaml
kubectl -f <pod_name>
```

### Showing the last X number of lines

When a pod has been running for a while, the logs can be very long. You probably only want to see the last few lines.

``` yaml
kubectl --tail=10 <pod_name>
```

### Combining options

You can combine the follow and last x number of lines options

``` yaml
kubectl -f --tail=10 <pod_name>
```