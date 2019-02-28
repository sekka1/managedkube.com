---
layout: post
title: K8sbot Getting Pod Logs
categories: kubernetes k8sbot pod logs
keywords: kubernetes k8sbot pod logs
---

Getting the pod logs into you Slack channel is easy now.  No more cutting and pasting
from `kubectl` to Slack.  You can directly ask `k8sbot` to fetch the logs for you and
post it to the channel.

k8sbot will get the last 50 lines of the logs from all of of the containers in
the pod and post it to Slack.

![k8sbot logs](/assets/blog/images/workflow/k8sbot-pod-logs.png)
