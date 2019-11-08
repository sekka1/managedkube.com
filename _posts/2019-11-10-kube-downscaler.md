---
layout: post
title: Kube-Downscaler
categories: Kube-Downscaler
keywords: Kube-Downscale
---

I have been hearing from my clients more and more that I want to save on my cloud cost.  Taking that ask,
I found this really cool project called [kube-downscaler](https://github.com/hjacobs/kube-downscaler) which
is a pod that runs in your Kubernetes cluster that will scale down a deployment/statefulset or a whole namespace.

You can configure it in many ways and I am outlining one way that I have been using it.

In our pre-production clusters we don't get much usage out of them during the weekends.  The idea is that we
will scale down the pods in these namespaces and in turn our [cluster-autoscaler](https://github.com/kubernetes/autoscaler/tree/master/cluster-autoscaler)
will scale down the nodes since they are not in use.  The reverse will happen on a scale up.


In this example, I will scale down the `gar` namespace for a sort period of time for 1 hour.

```yaml
apiVersion: v1
kind: Namespace
metadata:
  annotations:
    downscaler/downtime: Fri-Fri 01:45-02:45 UTC
  labels:
    name: gar
  name: gar
```

Pods are running in that namespace:
```yaml
kubectl -n gar get pods -o wide                                      
NAME                            READY   STATUS             RESTARTS   AGE    IP             NODE                           NOMINATED NODE
tornado-fb459c7f9-9mtm7         1/1     Running            0          2m9s   100.96.0.223   ip-172-17-51-20.ec2.internal   <none>
webapp-nginx-85fcf96f7f-5bxgs   1/2     CrashLoopBackOff   4          2m9s   100.96.6.19    ip-172-17-50-79.ec2.internal   <none>
webapp-nginx-85fcf96f7f-s2k4n   1/2     Error              4          2m9s   100.96.0.224   ip-172-17-51-20.ec2.internal   <none>
```

Date on the kube-downscaler
```yaml
kubectl -n kube-downscaler exec kube-downscaler-6969d86595-7g8dg date
Fri Nov  8 02:21:11 UTC 2019
```

Logs from the kube-downscaler
```
2019-11-08 01:45:24,425 INFO: Scaling down Deployment gar/webapp-nginx from 2 to 0 replicas (uptime: always, downtime: Fri-Fri 01:45-02:45 UTC)
2019-11-08 01:45:24,442 DEBUG: https://100.64.0.1:443 "PATCH /apis/apps/v1/namespaces/gar/deployments/webapp-nginx HTTP/1.1" 200 None
```

Pods in the `gar` namespace:
```
kubectl -n gar get pods -o wide                                        
No resources found in gar namespace.
```

Looks like it downscaled the pods in the `gar` namespace.  Cool!

Now im waiting for the upscale event...

Logs from the kube-downscaler
```
2019-11-08 02:46:03,626 INFO: Scaling up Deployment gar/webapp-nginx from 0 to 2 replicas (uptime: always, downtime: Fri-Fri 01:45-02:45 UTC)
2019-11-08 02:46:03,644 DEBUG: https://100.64.0.1:443 "PATCH /apis/apps/v1/namespaces/gar/deployments/webapp-nginx HTTP/1.1" 200 None
```

Pods in the `gar` namespace:
```yaml
kubectl -n gar get pods -o wide                                      
NAME                            READY   STATUS             RESTARTS   AGE     IP             NODE                           NOMINATED NODE
tornado-fb459c7f9-n5hzj         1/1     Running            0          2m52s   100.96.0.225   ip-172-17-51-20.ec2.internal   <none>
webapp-nginx-85fcf96f7f-4flvx   1/2     CrashLoopBackOff   4          2m52s   100.96.5.222   ip-172-17-50-67.ec2.internal   <none>
webapp-nginx-85fcf96f7f-vl9b5   1/2     CrashLoopBackOff   4          2m52s   100.96.0.226   ip-172-17-51-20.ec2.internal   <none>
```

Date on the kube-downscaler
```
kubectl -n kube-downscaler exec kube-downscaler-6969d86595-7g8dg date  
Fri Nov  8 02:49:28 UTC 2019
```

Looks like it scaled down and up as I configured it.




