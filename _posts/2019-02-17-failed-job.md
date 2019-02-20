---
layout: post
title: Failing Job
categories: kubernetes Failing Job k8sbot
keywords: kubernetes Failing Job k8sbot
---

You encounter an error on your job.

```bash
$ kubectl get pods
NAME                  READY   STATUS   RESTARTS   AGE
job-failure-1-6hkbz   0/1     Error    0          36s
job-failure-1-6n2ng   0/1     Error    0          1m
job-failure-1-6xgzh   0/1     Error    0          56s
job-failure-1-75s4g   0/1     Error    0          1m
```

Ask @k8sbot for troubleshooting help:

![get jobs](/assets/blog/images/workflow/failing-job/failed-job-1.png)

@k8sbot runs inside of your Kubernetes cluster and gives you diagnostic information
from interacting with the Kubernetes API

![get ingress](/assets/blog/images/workflow/k8sbot-agent-request.png)

K8sbot provides troubleshooting recommendations based on real time information
from your cluster.  It offers relevant suggestions based on what's happening
in your cluster, right now.

![trace ingress](/assets/blog/images/workflow/failing-job/failed-job-2.png)

<A HREF="https://www.managedkube.com">Learn more</a> about k8sBot, a Kubernetes troubleshoot Slackbot.
