---
layout: post
title: "Kubernetes DNS Troubleshooting"
categories: Aws Kubernetes
keywords: Aws Kubernetes
author: Garland Kan
# https://jekyll.github.io/jekyll-seo-tag/advanced-usage/#customizing-image-output
# This adds the html metadata "og:image" tags to the page for URL previews
image:
  path: "/assets/logo/M_1000.jpg"
#   height: 100
#   width: 100
description: Kubernetes DNS Troubleshooting
---
{%- include share-bar.html -%}

On a new cluster we saw the following logs from an application we launched:

```yaml
2018/06/15 20:20:43.840 WARN  [grpc-default-executor-8] [ManagedChannelImpl] [ManagedChannelImpl$NameResolverListenerImpl:942] [io.grpc.internal.ManagedChannelImpl-17247] Failed to resolve name. status=Status{code=UNAVAILABLE, description=Unable to resolve host pubsub.googleapis.com, cause=java.net.UnknownHostException: pubsub.googleapis.com
	at java.net.InetAddress.getAllByName0(InetAddress.java:1280)
	at java.net.InetAddress.getAllByName(InetAddress.java:1192)
	at java.net.InetAddress.getAllByName(InetAddress.java:1126)
	at io.grpc.internal.DnsNameResolver$JdkResolver.resolve(DnsNameResolver.java:358)
	at io.grpc.internal.DnsNameResolver$1.run(DnsNameResolver.java:172)
	at java.util.concurrent.ThreadPoolExecutor.runWorker(ThreadPoolExecutor.java:1142)
	at java.util.concurrent.ThreadPoolExecutor$Worker.run(ThreadPoolExecutor.java:617)
	at java.lang.Thread.run(Thread.java:745)
```

The pod didnt have ping but it had `curl`. Tried curling the endpoint `pubsub.googleapis.com` that it was trying to reach but that didnt work. Tried curling `google.com` and that didnt work.

Then I went to another pod that had ping to give it a try.

```
root@ingress-controller-external-7bc9767f69-7qxfb:/# ping google.com
ping: unknown host
```

Cant resolve any hostnames either. Tried to ping a known IP:

```yaml
root@ingress-controller-external-7bc9767f69-7qxfb:/# ping 8.8.8.8
PING 8.8.8.8 (8.8.8.8): 56 data bytes
64 bytes from 8.8.8.8: icmp_seq=0 ttl=51 time=0.941 ms
64 bytes from 8.8.8.8: icmp_seq=1 ttl=51 time=0.346 ms
```

That worked. This would make sense since the instance was able to pull the container image with a hostname. It would seem DNS and routing were working on the GCE instance level.

This lead me to think it was something wrong with the kubernetes DNS.

Then I looked at the `kube-systems` namespace to see what the DNS were doing:

```yaml
kube-dns-785f949785-5slck                                        0/4       Pending            0          23h       <none>       <none>
kube-dns-785f949785-w7str                                        0/4       Pending            0          23h       <none>       <none>
kube-dns-autoscaler-69c5cbdcdd-krfn9                             0/1       Pending            0          23h       <none>       <none>
```

They were still in pending state which would make sense.

This is showing there are a few level at play here from the instance to kubernetes

<!-- Bog footer share -->
{%- include blog-footer-share.html -%}

{% include blog-cta-1.html %}
