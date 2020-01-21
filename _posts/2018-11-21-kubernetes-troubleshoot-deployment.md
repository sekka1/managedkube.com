---
layout: post
title: Kubernetes Troubleshooting a Deployment
categories: Kubernetes troubleshooting
keywords: Kubernetes troubleshooting
---
{%- include twitter-button-blank.html -%}

I have launched my deployment but when i list the pods, I don't see a pod for
that deployment.  How would I troubleshoot this and what would I look for.

Start by looking to see if the deployment was created, and then describing the
deployment to see what information it gives back to you.

Then it says it launched this thing called a replicaset.  You should then
check this and describe it to see why it did not launch anything.

The reason is given to you in the replicaset.  It didnt have the service account.

```bash
$ kubectl -n kubernetes-cost-attribution get pods -o wide
NAME                                           READY   STATUS     RESTARTS   AGE   IP          NODE                                   NOMINATED NODE
cost-attribution-grafana-bfdfddcbb-8xrqj       0/1     Running    0          10s   10.56.1.9   gke-gar-2-default-pool-bf1ffdf3-2pn7   <none>
cost-attribution-prometheus-6dbb987568-wjzwm   0/1     Init:0/1   0          10s   <none>      gke-gar-2-default-pool-bf1ffdf3-2pn7   <none>
$ kubectl -n kubernetes-cost-attribution get deploy
NAME                          DESIRED   CURRENT   UP-TO-DATE   AVAILABLE   AGE
cost-attribution-grafana      1         1         1            1           2m18s
cost-attribution-mk-agent     1         0         0            0           2m18s
cost-attribution-prometheus   1         1         1            1           2m18s
$ kubectl -n kubernetes-cost-attribution describe deploy cost-attribution-mk-agent
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
$ kubectl -n kubernetes-cost-attribution get rs
NAME                                     DESIRED   CURRENT   READY   AGE
cost-attribution-grafana-bfdfddcbb       1         1         1       2m33s
cost-attribution-mk-agent-6c78b8757f     1         0         0       2m33s
cost-attribution-prometheus-6dbb987568   1         1         1       2m33s
$ kubectl -n kubernetes-cost-attribution describe rs cost-attribution-mk-agent-6c78b8757f
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

<!-- Blog footer share -->
{%- include blog-footer-share.html -%}
