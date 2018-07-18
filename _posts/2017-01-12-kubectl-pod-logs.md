---
layout: post
title: "Kubectl Pod logs"
categories: Aws Kubernetes Logs
keywords: Aws Kubernetes Logs
author: Garland Kan

---

There are a few useful options when getting logs from your Kubernetes pods:

&nbsp;

* tailing
* showing the last x number of lines
* combining options

&nbsp;

### tailing

&nbsp;

This will tail and follow the pod’s logs

&nbsp;

```
kubectl -f <pod_name>
```

&nbsp;

### Showing the last X number of lines

&nbsp;

When a pod has been running for a while, the logs can be very long. You probably only want to see the last few lines.

&nbsp;

```
kubectl --tail=10 <pod_name>
```

&nbsp;

### Combining options

&nbsp;

You can combine the follow and last x number of lines options

&nbsp;

```
kubectl -f --tail=10 <pod_name>
```