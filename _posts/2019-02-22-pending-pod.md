---
layout: post
title: Kubernetes Troubleshooting Walkthrough - Pending Pods
categories: kubernetes k8sbot troubleshooting pending pod
keywords: kubernetes k8sbot troubleshooting pending pod
---

<a href="https://twitter.com/share?ref_src=twsrc%5Etfw" class="twitter-share-button" data-text="Kubernetes Troubleshooting Walkthrough - Pending Pods https://managedkube.com/kubernetes/k8sbot/troubleshooting/pending/pod/2019/02/22/pending-pod.html" data-via="managedkube" data-hashtags="#troubleshooting #devops #kubernetes" data-show-count="false">Tweet</a><script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>

* TOC
{:toc}

# Introduction: troubleshooting pending pods

You got your deployment, statefulset, or somehow turned on a pod on the Kubernetes
cluster and it is in a `pending` state.  What can you do now and how do you troubleshoot
it to see what the problem is?

```bash
$ kubectl get pods
NAME                                                   READY   STATUS             RESTARTS   AGE
echoserver-657f6fb8f5-wmgj5        0/1     Pending            0          1d
```

There can be various reasons why your pod is in a `pending` state.  We'll go through them one-by-one and how to
determine what the error messages are telling you.

With any of these errors, step one is to `describe` the pod:

```bash
$ kubectl describe pod echoserver-657f6fb8f5-wmgj5
```

This will give you additional information.  The describe output can be long but look
at the `Events` section first.

## Troubleshooting Reason #1: Not enough CPU

```bash
kubectl describe pod echoserver-657f6fb8f5-wmgj5
...
...
Events:
  Type     Reason            Age               From               Message
  ----     ------            ----              ----               -------
  Warning  FailedScheduling  2s (x6 over 11s)  default-scheduler  0/4 nodes are available: 4 Insufficient cpu.
```

To expand on this line.  Kubernetes `FailedScheduling` of this pod.  There are 0 out of 4 nodes
in the cluster that did not have sufficient CPU to allocate to this pod.

This could mean:
* You have requested more CPU than any of the nodes has.  For example, each node in the cluster has
2 CPU cores and you request 4 CPU cores.  This would mean that even if you turned on more nodes in
your cluster, Kubernetes will still not be able to schedule it out anywhere.
* There is no more capacity in the cluster per the CPU cores you have requested.  If it is not the first
case, then this would mean that if you had 4 nodes in the cluster and each node has 1 CPU, all of
those CPUs has already been requested and allocated to other pods.  In this case, you can turn on
more nodes in the cluster and your pod will schedule out.

You can check the total number of node via:

```bash
$ kubectl get nodes
NAME                             STATUS   ROLES    AGE   VERSION
gke-gar-3-pool-1-9781becc-bdb3   Ready    <none>   12h   v1.11.5-gke.5
gke-gar-3-pool-1-9781becc-d0m6   Ready    <none>   3d    v1.11.5-gke.5
gke-gar-3-pool-1-9781becc-gc8h   Ready    <none>   4h    v1.11.5-gke.5
gke-gar-3-pool-1-9781becc-zj3w   Ready    <none>   20h   v1.11.5-gke.5
```

Describing a node will give you more details about the capacity of the node:

```bash
$ kubectl describe node gke-gar-3-pool-1-9781becc-bdb3
Name:               gke-gar-3-pool-1-9781becc-bdb3
...
...
Allocatable:
 cpu:                940m
 ephemeral-storage:  4278888833
 hugepages-2Mi:      0
 memory:             2702164Ki
 pods:               110
...
...
Allocated resources:
  (Total limits may be over 100 percent, i.e., overcommitted.)
  Resource  Requests         Limits
  --------  --------         ------
  cpu       908m (96%)       2408m (256%)
  memory    1227352Ki (45%)  3172952Ki (117%)
...
...
```
This will tell you how much this node's CPU/memory has been requested.  The `Request`
can never go over 100% but the `Limits` can.  We are interested in the `Request`
column.  For example, this output is telling us that it is at 96% of the max cpu
that is allocatable.  This means that we have 4% more we can request.  Looking at
the Allocatable cpu section(940m) and the current Request cpu (908m), this means we have (940m - 908m)
32m worth of CPU that we can still request.

Looking back at our `describe pod` output:

```bash
Limits:
  cpu:     16
  memory:  128Mi
Requests:
  cpu:        16
  memory:     64Mi
```

We can see that we have requested 16 CPU.  What happened to the `m` and why is it 16?  This
deserves a little bit of explanation to understand this.  CPU request/limits are in the
units of CPU cores.  For 1 CPU core it is either `1` or `1000m`.  This means you can ask for
half a core by donoting `500m`.

For this example, we have requested a very high CPU core request at 16 cores.  From our
`describe node` output this node only has 940m it can allocate out which is under one
core which means it will never be able to schedule this pod out on this node type.  It
just doesnt have enough CPU cores on it.

On the flip side, even if we requested something reasonable like 1 core, it still wouldn't
be able to schedule it out.  We would have to request (per our calculation above) 32m of
CPU.


## Troubleshooting Reason #2: Not enough memory

```bash
Events:
  Type     Reason            Age                    From               Message
  ----     ------            ----                   ----               -------
  Warning  FailedScheduling  2m6s (x25 over 2m54s)  default-scheduler  0/4 nodes are available: 4 Insufficient cpu, 4 Insufficient memory.
```
We would go through about the same troubleshooting workflow as the CPU above.

The two problems are the same.  Either we have requested way too much memory or our nodes just don't
have the memory we are requesting.

We would look at our nodes and see what available memory they have:

```bash
$ kubectl describe node gke-gar-3-pool-1-9781becc-bdb3
Name:               gke-gar-3-pool-1-9781becc-bdb3
...
...
Allocatable:
 cpu:                940m
 ephemeral-storage:  4278888833
 hugepages-2Mi:      0
 memory:             2702164Ki
 pods:               110
...
...
Allocated resources:
  (Total limits may be over 100 percent, i.e., overcommitted.)
  Resource  Requests         Limits
  --------  --------         ------
  cpu       908m (96%)       2408m (256%)
  memory    1227352Ki (45%)  3172952Ki (117%)
...
...
```

This node has `1227352Ki` memory free.  About 1.2 GB.

Now we look at the describe pod output to see how much we have requested:

```bash
Limits:
  cpu:     100m
  memory:  125Gi
Requests:
  cpu:        100m
  memory:     64000Mi
```

We did request a lot of memory for this example; 64GB.  Same thing as the CPU, none
of our nodes has this much memory.  We either lower the memory request or change
the instance type to have sufficient memory.

## Troubleshooting Reason #3: Not enough CPU and memory

```bash
Events:
  Type     Reason            Age                     From               Message
  ----     ------            ----                    ----               -------
  Warning  FailedScheduling  2m30s (x25 over 3m18s)  default-scheduler  0/4 nodes are available: 4 Insufficient cpu, 4 Insufficient memory.
```

This is a combination on both of the above.  The event is telling us that there are
not enough CPU and memory to fulfill this request.  We will have to run through
the above two troubleshooting workflows and determine what we want to do for both
the CPU and memory.  You can alternatively just look at one (CPU or memory), fix that
problem and then look at what Kubernetes is telling you at that point and continue from there.


{%- include blurb-consulting.md -%}

# More troubleshooting blog posts

* <A HREF="https://managedkube.com/kubernetes/trace/ingress/service/port/not/matching/pod/k8sbot/2019/02/13/trace-ingress.html">Kubernetes Troubleshooting Walkthrough - Tracing through an ingress</a>
* <A HREF="https://managedkube.com/kubernetes/pod/failure/crashloopbackoff/k8sbot/troubleshooting/2019/02/12/pod-failure-crashloopbackoff.html">Kubernetes Troubleshooting Walkthrough - Pod Failure CrashLoopBackOff</a>
* <A HREF="https://managedkube.com/kubernetes/k8sbot/troubleshooting/imagepullbackoff/2019/02/23/imagepullbackoff.html">Kubernetes Troubleshooting Walkthrough - imagepullbackoff</a>

<a href="https://twitter.com/share?ref_src=twsrc%5Etfw" class="twitter-share-button" data-text="Kubernetes Troubleshooting Walkthrough - Pending Pods https://managedkube.com/kubernetes/k8sbot/troubleshooting/pending/pod/2019/02/22/pending-pod.html" data-via="managedkube" data-hashtags="#troubleshooting #devops #kubernetes" data-show-count="false">Tweet</a><script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>