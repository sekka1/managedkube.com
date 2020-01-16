---
layout: post
title: Kubernetes Troubleshooting Walkthrough - Pod Failure CrashLoopBackOff
categories: kubernetes pod failure CrashLoopBackOff k8sbot troubleshooting
keywords: kubernetes pod failure CrashLoopBackOff k8sbot troubleshooting
---

<a href="https://twitter.com/share?ref_src=twsrc%5Etfw" class="twitter-share-button" data-text="Kubernetes Troubleshooting Walkthrough - Pod Failure CrashLoopBackOff" data-via="managedkube" data-hashtags="#troubleshooting #devops #kubernetes" data-show-count="false">Tweet</a><script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>

* TOC
{:toc}

# Introduction: troubleshooting CrashLoopBackOff

I am writing a series of blog posts about troubleshooting Kubernetes. One of the reasons why Kubernetes is so complex is because troubleshooting what went wrong requires many levels of information gathering. It’s like trying to find the end of one string in a tangled ball of strings. In this post, I am going to walk you through troubleshooting the state, `CrashLoopBackOff`.

Your pod can fail in all kinds of ways.  One failure status is `CrashLoopBackOff`.  You will usually see this when you do a `kubectl get pods`.

```yaml
$ kubectl get pods
NAME                                    READY   STATUS             RESTARTS   AGE
pod-crashloopbackoff-7f7c556bf5-9vc89   1/2     CrashLoopBackOff   35         2h
```

What does this mean?

This means that your pod is starting, crashing, starting again, and then crashing again.

## Step One: Describe the pod for more information

To get more information you should describe the pod to get more information:

```yaml
$ kubectl describe pod pod-crashloopbackoff-7f7c556bf5-9vc89
Name:               pod-crashloopbackoff-7f7c556bf5-9vc89
Namespace:          dev-k8sbot-test-pods
Priority:           0
PriorityClassName:  <none>
Node:               gke-gar-3-pool-1-9781becc-bdb3/10.128.15.216
Start Time:         Tue, 12 Feb 2019 15:11:54 -0800
Labels:             app=pod-crashloopbackoff
                    pod-template-hash=3937112691
Annotations:        <none>
Status:             Running
IP:                 10.44.46.8
Controlled By:      ReplicaSet/pod-crashloopbackoff-7f7c556bf5
Containers:
  im-crashing:
    Container ID:  docker://a3ba2841f39414390b6cbd85fe94932a0f50c2698e68c34d52a5b23cfe73094c
    Image:         ubuntu:18.04
    Image ID:      docker-pullable://ubuntu@sha256:7a47ccc3bbe8a451b500d2b53104868b46d60ee8f5b35a24b41a86077c650210
    Port:          8080/TCP
    Host Port:     0/TCP
    Command:
      /bin/bash
      -ec
      echo 'hello, there...'; sleep 1; echo 'hello, there...'; sleep 1; echo 'exiting with status 0'; exit 1;
    State:          Terminated
      Reason:       Error
      Exit Code:    1
      Started:      Tue, 12 Feb 2019 15:12:47 -0800
      Finished:     Tue, 12 Feb 2019 15:12:49 -0800
    Last State:     Terminated
      Reason:       Error
      Exit Code:    1
      Started:      Tue, 12 Feb 2019 15:12:28 -0800
      Finished:     Tue, 12 Feb 2019 15:12:30 -0800
    Ready:          False
    Restart Count:  2
    Environment:    <none>
    Mounts:
      /var/run/secrets/kubernetes.io/serviceaccount from default-token-csrjs (ro)
  good-container:
    Container ID:   docker://00d634023be399358d9496d557e2cb7501cc5c52ac360d5809c74d4ca3a3b96c
    Image:          gcr.io/google_containers/echoserver:1.0
    Image ID:       docker-pullable://gcr.io/google_containers/echoserver@sha256:6240c350bb622e33473b07ece769b78087f4a96b01f4851eab99a4088567cb76
    Port:           8080/TCP
    Host Port:      0/TCP
    State:          Running
      Started:      Tue, 12 Feb 2019 15:12:27 -0800
    Ready:          True
    Restart Count:  0
    Environment:    <none>
    Mounts:
      /var/run/secrets/kubernetes.io/serviceaccount from default-token-csrjs (ro)
Conditions:
  Type              Status
  Initialized       True
  Ready             False
  ContainersReady   False
  PodScheduled      True
Volumes:
  default-token-csrjs:
    Type:        Secret (a volume populated by a Secret)
    SecretName:  default-token-csrjs
    Optional:    false
QoS Class:       BestEffort
Node-Selectors:  <none>
Tolerations:     node.kubernetes.io/not-ready:NoExecute for 300s
                 node.kubernetes.io/unreachable:NoExecute for 300s
Events:
  Type     Reason     Age               From                                     Message
  ----     ------     ----              ----                                     -------
  Normal   Scheduled  56s               default-scheduler                        Successfully assigned dev-k8sbot-test-pods/pod-crashloopbackoff-7f7c556bf5-9vc89 to gke-gar-3-pool-1-9781becc-bdb3
  Normal   Pulling    50s               kubelet, gke-gar-3-pool-1-9781becc-bdb3  pulling image "gcr.io/google_containers/echoserver:1.0"
  Normal   Created    24s               kubelet, gke-gar-3-pool-1-9781becc-bdb3  Created container
  Normal   Pulled     24s               kubelet, gke-gar-3-pool-1-9781becc-bdb3  Successfully pulled image "gcr.io/google_containers/echoserver:1.0"
  Normal   Started    23s               kubelet, gke-gar-3-pool-1-9781becc-bdb3  Started container
  Normal   Pulling    4s (x3 over 55s)  kubelet, gke-gar-3-pool-1-9781becc-bdb3  pulling image "ubuntu:18.04"
  Normal   Created    3s (x3 over 50s)  kubelet, gke-gar-3-pool-1-9781becc-bdb3  Created container
  Normal   Started    3s (x3 over 50s)  kubelet, gke-gar-3-pool-1-9781becc-bdb3  Started container
  Normal   Pulled     3s (x3 over 51s)  kubelet, gke-gar-3-pool-1-9781becc-bdb3  Successfully pulled image "ubuntu:18.04"
  Warning  BackOff    1s (x2 over 19s)  kubelet, gke-gar-3-pool-1-9781becc-bdb3  Back-off restarting failed container

```

That is a lot of output.  The first thing I would look at in this output are the `Events`.  This will tell you what Kubernetes is doing.  Reading the `Events` section from top to bottom tells me: the pod was assigned to a node, starts pulling the images, starting the images, and then it goes into this `BackOff` state.  

```
Type     Reason     Age               From                                     Message
----     ------     ----              ----                                     -------
Warning  BackOff    1s (x2 over 19s)  kubelet, gke-gar-3-pool-1-9781becc-bdb3  Back-off restarting failed container
```

This message says that it is in a `Back-off restarting failed container`.  This most likely means that Kubernetes started your container, then the container subsequently exited.  As we all know, the Docker container should hold and keep pid 1 running or the container exits.  When the container exits, Kubernetes will try to restart it.  After restarting it a few times, it will declare this `BackOff` state.  However, Kubernetes will keep on trying to restart it.

If you get the pods again, you can see the `restart` counter is incrementing as Kubernetes restarts the container but the container keeps on exiting.

```yaml
$ kubectl get pods
NAME                                    READY   STATUS   RESTARTS   AGE
pod-crashloopbackoff-7f7c556bf5-9vc89   1/2     Error    6          6m
```

## Step Two: Get the logs of the pod

At this point you should get the logs of the pod.  

```yaml
$ kubectl logs pod-crashloopbackoff-7f7c556bf5-9vc89 im-crashing
hello, there...
hello, there...
exiting with status 0
```

In our case, if you look above at the `Command`, we have it outputting some text and then exiting to show you this demo.  However, if you had a real app, this could mean that your application is exiting for some reason and hopefully the application logs will tell you why or give you a clue to why it is exiting.

## Step Three: Look at the Liveness probe

Another possibility is that the pod is crashing because of a `liveness` probe not returning a successful status.  It will be in the same `CrashLoopBackOff` state in the `get pods` output and you have to `describe pod` to get the real information.

```yaml
$ kubectl describe pod pod-crashloopbackoff-liveness-probe-7564df8646-v96tq
Name:               pod-crashloopbackoff-liveness-probe-7564df8646-v96tq
Namespace:          dev-k8sbot-test-pods
Priority:           0
PriorityClassName:  <none>
Node:               gke-gar-3-pool-1-9781becc-bdb3/10.128.15.216
Start Time:         Tue, 12 Feb 2019 15:21:45 -0800
Labels:             app=pod-crashloopbackoff-liveness-probe
                    pod-template-hash=3120894202
Annotations:        <none>
Status:             Running
IP:                 10.44.46.9
Controlled By:      ReplicaSet/pod-crashloopbackoff-liveness-probe-7564df8646
Containers:
  im-crashing:
    Container ID:  docker://e29f6ad6f28b740ad115a6eb3f32267f7067e3c725a92c0a909fbe2ff3aac855
    Image:         ubuntu:18.04
    Image ID:      docker-pullable://ubuntu@sha256:7a47ccc3bbe8a451b500d2b53104868b46d60ee8f5b35a24b41a86077c650210
    Port:          8080/TCP
    Host Port:     0/TCP
    Command:
      /bin/bash
      -ec
      echo 'hello, there...'; sleep 1;
    State:          Waiting
      Reason:       CrashLoopBackOff
    Last State:     Terminated
      Reason:       Completed
      Exit Code:    0
      Started:      Tue, 12 Feb 2019 15:22:28 -0800
      Finished:     Tue, 12 Feb 2019 15:22:29 -0800
    Ready:          False
    Restart Count:  2
    Environment:    <none>
    Mounts:
      /var/run/secrets/kubernetes.io/serviceaccount from default-token-csrjs (ro)
  good-container:
    Container ID:   docker://af6bc7caabd0fc8b682d3cd58d285dd577730492cc9d2ea43d94cf0684e44fb2
    Image:          gcr.io/google_containers/echoserver:1.0
    Image ID:       docker-pullable://gcr.io/google_containers/echoserver@sha256:6240c350bb622e33473b07ece769b78087f4a96b01f4851eab99a4088567cb76
    Port:           8080/TCP
    Host Port:      0/TCP
    State:          Running
      Started:      Tue, 12 Feb 2019 15:22:28 -0800
    Last State:     Terminated
      Reason:       Error
      Exit Code:    137
      Started:      Tue, 12 Feb 2019 15:21:47 -0800
      Finished:     Tue, 12 Feb 2019 15:22:27 -0800
    Ready:          True
    Restart Count:  1
    Liveness:       http-get http://:9999/healthz delay=3s timeout=1s period=3s #success=1 #failure=3
    Environment:    <none>
    Mounts:
      /var/run/secrets/kubernetes.io/serviceaccount from default-token-csrjs (ro)
Conditions:
  Type              Status
  Initialized       True
  Ready             False
  ContainersReady   False
  PodScheduled      True
Volumes:
  default-token-csrjs:
    Type:        Secret (a volume populated by a Secret)
    SecretName:  default-token-csrjs
    Optional:    false
QoS Class:       BestEffort
Node-Selectors:  <none>
Tolerations:     node.kubernetes.io/not-ready:NoExecute for 300s
                 node.kubernetes.io/unreachable:NoExecute for 300s
Events:
  Type     Reason     Age                From                                     Message
  ----     ------     ----               ----                                     -------
  Normal   Scheduled  81s                default-scheduler                        Successfully assigned dev-k8sbot-test-pods/pod-crashloopbackoff-liveness-probe-7564df8646-v96tq to gke-gar-3-pool-1-9781becc-bdb3
  Normal   Started    79s                kubelet, gke-gar-3-pool-1-9781becc-bdb3  Started container
  Warning  BackOff    73s (x2 over 74s)  kubelet, gke-gar-3-pool-1-9781becc-bdb3  Back-off restarting failed container
  Warning  Unhealthy  70s (x3 over 76s)  kubelet, gke-gar-3-pool-1-9781becc-bdb3  Liveness probe failed: Get http://10.44.46.9:9999/healthz: dial tcp 10.44.46.9:9999: connect: connection refused
  Normal   Pulling    39s (x3 over 80s)  kubelet, gke-gar-3-pool-1-9781becc-bdb3  pulling image "ubuntu:18.04"
  Normal   Killing    39s                kubelet, gke-gar-3-pool-1-9781becc-bdb3  Killing container with id docker://good-container:Container failed liveness probe.. Container will be killed and recreated.
  Normal   Pulled     38s (x2 over 79s)  kubelet, gke-gar-3-pool-1-9781becc-bdb3  Successfully pulled image "gcr.io/google_containers/echoserver:1.0"
  Normal   Created    38s (x2 over 79s)  kubelet, gke-gar-3-pool-1-9781becc-bdb3  Created container
  Normal   Pulled     38s (x3 over 79s)  kubelet, gke-gar-3-pool-1-9781becc-bdb3  Successfully pulled image "ubuntu:18.04"
  Normal   Created    38s (x3 over 79s)  kubelet, gke-gar-3-pool-1-9781becc-bdb3  Created container
  Normal   Started    38s (x3 over 79s)  kubelet, gke-gar-3-pool-1-9781becc-bdb3  Started container
  Normal   Pulling    38s (x2 over 79s)  kubelet, gke-gar-3-pool-1-9781becc-bdb3  pulling image "gcr.io/google_containers/echoserver:1.0"
```

Once again, we'll look at the `Events` first.  It has about the same items as last time but then we encounter:

```
Warning  BackOff    73s (x2 over 74s)  kubelet, gke-gar-3-pool-1-9781becc-bdb3  Back-off restarting failed container
Warning  Unhealthy  70s (x3 over 76s)  kubelet, gke-gar-3-pool-1-9781becc-bdb3  Liveness probe failed: Get http://10.44.46.9:9999/healthz: dial tcp 10.44.46.9:9999: connect: connection refused
```

Kubernetes is backing off on restarting the container so many times.  Then the next event tells us that
the `Liveness` probe failed.  This gives us an indication that we should look at our `Liveness` probe.  Either we configured the liveness probe incorrectly for our appplicatoin or it is indeed not working.  We should start with checking on one and then the other.

In summary, the error `CrashLoopBackOff` can be tricky if we don't know where to look but with a few commands and looking at the correct places, we can pull out the nugget of information we need to tell us why Kubernetes is declaring the error and doing what it is doing.  Then the next part is on us to test a few things to make sure everything is correct with our configuration and/or our application.

{%- include blurb-consulting.md -%}

# More troubleshooting blog posts

* <A HREF="https://managedkube.com/kubernetes/k8sbot/troubleshooting/pending/pod/2019/02/22/pending-pod.html">Kubernetes Troubleshooting Walkthrough - Pending pods</a>
* <A HREF="https://managedkube.com/kubernetes/trace/ingress/service/port/not/matching/pod/k8sbot/2019/02/13/trace-ingress.html">Kubernetes Troubleshooting Walkthrough - Tracing through an ingress</a>
* <A HREF="https://managedkube.com/kubernetes/k8sbot/troubleshooting/imagepullbackoff/2019/02/23/imagepullbackoff.html">Kubernetes Troubleshooting Walkthrough - imagepullbackoff</a>

<a href="https://twitter.com/share?ref_src=twsrc%5Etfw" class="twitter-share-button" data-text="Kubernetes Troubleshooting Walkthrough - Pod Failure CrashLoopBackOff" data-via="managedkube" data-hashtags="#troubleshooting #devops #kubernetes" data-show-count="false">Tweet</a><script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>