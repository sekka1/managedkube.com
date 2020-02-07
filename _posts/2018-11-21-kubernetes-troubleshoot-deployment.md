---
layout: post
title: Kubernetes Troubleshooting a Deployment
categories: Kubernetes troubleshooting
keywords: Kubernetes troubleshooting
---
{%- include twitter-button-blank.html -%}

A Kubernetes Deployment is actually a higher level resource that uses other Kubernetes resources to create pods.  The reason for this complexity is because the `Deployment` kind adds functionality to lower level resources.  This blog will guide you through looking at your `Deployment` and how to find information about it.  These steps are generic for all `Deployments`.

When you create a `Deployment` kind, you will see it created by running this command:

```
$ kubectl get deployment
NAME                          DESIRED   CURRENT   UP-TO-DATE   AVAILABLE   AGE
cost-attribution-grafana      1         1         1            1           2m18s
```

You can describe it to see what it did:

```
$ kubectl describe deploy cost-attribution-mk-agent
Name:                   cost-attribution-mk-agent
Namespace:              kubernetes-cost-attribution
CreationTimestamp:      Wed, 21 Nov 2018 12:30:47 -0800
Labels:                 app=cost-attribution-mk-agent
Annotations:            deployment.kubernetes.io/revision: 1
                        kubectl.kubernetes.io/last-applied-configuration:
                          {"apiVersion":"apps/v1","kind":"Deployment","metadata":{"annotations":{},"name":"cost-attribution-mk-agent","namespace":"kubernetes-cost-a...
Selector:               app=cost-attribution-mk-agent
Replicas:               1 desired | 0 updated | 0 total | 0 available | 1 unavailable
StrategyType:           RollingUpdate
MinReadySeconds:        0
RollingUpdateStrategy:  25% max unavailable, 25% max surge
Pod Template:
  Labels:           app=cost-attribution-mk-agent
  Service Account:  cost-attribution-kube-state-metric
  Containers:
   mk-agent:
    Image:      gcr.io/managedkube/kubernetes-cost-attribution/agent:1.0
    Port:       9101/TCP
    Host Port:  0/TCP
    Limits:
      cpu:     500m
      memory:  500Mi
    Requests:
      cpu:        20m
      memory:     20Mi
    Liveness:     http-get http://:9101/metrics delay=5s timeout=5s period=10s #success=1 #failure=3
    Readiness:    http-get http://:9101/metrics delay=5s timeout=5s period=5s #success=1 #failure=3
    Environment:  <none>
    Mounts:       <none>
  Volumes:
   ubbagent-state:
    Type:    EmptyDir (a temporary directory that shares a pod's lifetime)
    Medium:  
Conditions:
  Type             Status  Reason
  ----             ------  ------
  Progressing      True    NewReplicaSetCreated
  Available        False   MinimumReplicasUnavailable
  ReplicaFailure   True    FailedCreate
OldReplicaSets:    <none>
NewReplicaSet:     cost-attribution-mk-agent-6c78b8757f (0/1 replicas created)
Events:
  Type    Reason             Age    From                   Message
  ----    ------             ----   ----                   -------
  Normal  ScalingReplicaSet  2m27s  deployment-controller  Scaled up replica set cost-attribution-mk-agent-6c78b8757f to 1
```

In the `Events` section, there is an event where it scaled up a `ReplicaSet` to 1.  These events messages are critical to debugging your Deployment.  There could be other failure cases here and it will describe (or at least give you a clue) on why it failed so you can remedy it.

Even if the `Deployment` did create the `ReplicaSet` that does not mean that there are `Pods` that are created.  The next step in the flow is to look at the `ReplicaSet` resource by running this command:

```
$ kubectl get replicaset
NAME                                     DESIRED   CURRENT   READY   AGE
cost-attribution-grafana-bfdfddcbb       1         1         1       2m33s
```

This will show you the `ReplicaSets` that you have in this namespace.  With this you can describe this `ReplicaSet` to see what it has done:

```
$ kubectl describe replicaset cost-attribution-mk-agent-6c78b8757f
Name:           cost-attribution-mk-agent-6c78b8757f
Namespace:      kubernetes-cost-attribution
Selector:       app=cost-attribution-mk-agent,pod-template-hash=2734643139
Labels:         app=cost-attribution-mk-agent
                pod-template-hash=2734643139
Annotations:    deployment.kubernetes.io/desired-replicas: 1
                deployment.kubernetes.io/max-replicas: 2
                deployment.kubernetes.io/revision: 1
Controlled By:  Deployment/cost-attribution-mk-agent
Replicas:       0 current / 1 desired
Pods Status:    0 Running / 0 Waiting / 0 Succeeded / 0 Failed
Pod Template:
  Labels:           app=cost-attribution-mk-agent
                    pod-template-hash=2734643139
  Service Account:  cost-attribution-kube-state-metric
  Containers:
   mk-agent:
    Image:      gcr.io/managedkube/kubernetes-cost-attribution/agent:1.0
    Port:       9101/TCP
    Host Port:  0/TCP
    Limits:
      cpu:     500m
      memory:  500Mi
    Requests:
      cpu:        20m
      memory:     20Mi
    Liveness:     http-get http://:9101/metrics delay=5s timeout=5s period=10s #success=1 #failure=3
    Readiness:    http-get http://:9101/metrics delay=5s timeout=5s period=5s #success=1 #failure=3
    Environment:  <none>
    Mounts:       <none>
  Volumes:
   ubbagent-state:
    Type:    EmptyDir (a temporary directory that shares a pod's lifetime)
    Medium:  
Conditions:
  Type             Status  Reason
  ----             ------  ------
  ReplicaFailure   True    FailedCreate
Events:
  Type     Reason        Age                   From                   Message
  ----     ------        ----                  ----                   -------
  Warning  FailedCreate  76s (x15 over 2m38s)  replicaset-controller  Error creating: pods "cost-attribution-mk-agent-6c78b8757f-" is forbidden: error looking up service account kubernetes-cost-attribution/cost-attribution-kube-state-metric: serviceaccount "cost-attribution-kube-state-metric" not found
```

In this particular case, the Events reports a `FailedCreate`.  The specific reason here is that it did not find a service account that the `Pod` references.  Your particular error could be different though.  This is just one example.

# Conclusion
This blog walked you through tracing out your Deployment for a specific case, but the steps outlined here are generic to how you would look through your `Deployment` if it was not behaving or creating the pods you expected it to create.


<!-- Blog footer share -->
{%- include blog-footer-share.html -%}
