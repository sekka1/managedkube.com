---
layout: post
title: Using k8sBot to get Kubernetes Pod Logs in Slack
categories: kubernetes k8sBot pod logs
keywords: kubernetes k8sBot pod logs
---
{%- include share-bar.html -%}

Getting your Kubernetes pod logs into your Slack channel is easy now.  No more cutting and pasting
from `kubectl` to Slack.  You can directly ask `k8sBot` to fetch the logs for you and
post it to the channel.

k8sBot will get the last 50 lines of the logs from all of of the containers in
the pod and post it to Slack.

![k8sbot logs](/assets/blog/images/workflow/k8sbot-pod-logs.png)

Interested in giving k8sBot a try? <A HREF="https://managedkube.com/">Learn more</a> or <A HREF="https://managedkube.com/free-k8sbot-trial-signup">start a free 30 day trial</a>

{%- include blurb-consulting.md -%}

<!-- Blog footer share -->
{%- include share-bar.html -%}

{% include blog-cta-1.html %}
