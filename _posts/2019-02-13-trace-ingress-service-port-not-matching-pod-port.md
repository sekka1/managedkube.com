---
layout: post
title: trace ingress - service port not matching pod port
categories: kubernetes trace ingress service port not matching pod port k8sbot
keywords: kubernetes trace ingress service port not matching pod port k8sbot
---

When you are not able to reach your website or your API endpoint through a Kubernetes ingress, there can be various reasons on why that is the case.  An ingress resource depends on a Kubernetes `service` and a `service` depends on pod(s) where it can send the traffic to.  If any of these items are misconfigured or not in a ready state, you can potentially not reach your website or API endpoint.

You should take note on what the error code the ingress loadbalancer returns

[from wikipedia](https://en.wikipedia.org/wiki/List_of_HTTP_status_codes):
```
1xx (Informational): The request was received, continuing process
2xx (Successful): The request was successfully received, understood, and accepted
3xx (Redirection): Further action needs to be taken in order to complete the request
4xx (Client Error): The request contains bad syntax or cannot be fulfilled
5xx (Server Error): The server failed to fulfill an apparently valid request
```

You can get this code using the cURL cli:

```
$ curl example.com/foo -v
*   Trying 172.217.7.14...
* TCP_NODELAY set
* Connected to example.com (172.217.7.14) port 80 (#0)
> GET /foo HTTP/1.1
> Host: example.com
> User-Agent: curl/7.58.0
> Accept: */*
>
< HTTP/1.1 503 Service Unavailable
< Content-Type: text/html; charset=UTF-8
< Referrer-Policy: no-referrer
< Content-Length: 1564
< Date: Wed, 13 Feb 2019 19:28:34 GMT
< X-Cache: MISS from row44proxy-postauth
< Via: 1.1 row44proxy-postauth (squid/3.5.25)
< Connection: keep-alive
<
503
```

The error code the loadbalancer returned is a 503.  Looking at [wikipedia](https://en.wikipedia.org/wiki/List_of_HTTP_status_codes):

```
503 Service Unavailable
The server is currently unavailable (because it is overloaded or down for maintenance). Generally, this is a temporary state.[65]
```

This tells us a few things.  It tells us that our we are making it to our ingress and
the ingress loadbalancer is trying to route it but it is saying there is nothing to route it to.  When you receive this message, this usually means that no `pods` associated with this `service` is in a ready state.  You should check if the `pods` are in a ready state to serve traffic.

You can also use @k8sbot to help you troubleshoot this:

![get ingress](/assets/blog/images/workflow/trace-ingress-service-port-not-matching-pod-port/get-ingress.png)

@k8sbot runs inside of your Kubernetes cluster and gives you diagnostic information
from interacting with the Kubernetes API

![k8sbot](/assets/blog/images/workflow/k8sbot-agent-request.png)

K8sbot provides troubleshooting recommendations based on real time information
from your cluster.  It offers relevant suggestions based on what's happening
in your cluster, right now.

![trace ingress](/assets/blog/images/workflow/trace-ingress-service-port-not-matching-pod-port/trace-ingress.png)
