---
layout: post
title: Launching ManagedKube, a tool to help you decrease Kubernetes Cloud Costs
categories: Kubernetes k8s cost monitoring tool Docker cloud
keywords: Kubernetes costs
---
{%- include twitter-button-blank.html -%}

Using Kubernetes? Us too. It’s helped us deploy code that is more flexible, reliable, and efficient. However, the inherent sharing nature of Kubernetes that facilitates these benefits also creates a problem — you have no visibility into how much stuff costs. Once you’re up and running with Kubernetes, the next question I always hear as a DevOps consultant is, “What is this costing me?”

Unfortunately, cloud providers like AWS and GCP don’t tell you exactly how you’re spending money in Kubernetes clusters. In a Kubernetes cluster, each instance can be shared by multiple namespaces and pods. The cloud providers will tell you how much an instance costs you but they don’t tell you how much each namespace or pod costs that was running on the same instance.

![Cluster illustration]({{ "/assets/blog/images/Cluster_illustration.png" | absolute_url }})

Because of this, you don’t know how much team A or team B is spending on compute, making it difficult to plan budgets, understand product margins, and optimize cloud costs. Without cost visibility, companies don’t know what’s going on in their Kubernetes cluster.

That’s why we’re so excited to launch ManagedKube, a tool that will help you measure and manage your cloud spend. Our tool attributes the costs of each pod, node, and persistent volume over multiple time dimensions. Now, in addition to seeing instance usage, you will be able to see how much team A or team B is using and how much customer Y or customer Z is using. You’ll also be able to look at the hidden costs of your cluster, such as ELB, EBS, and network transfers. We’re demystifying Kubernetes costs.

![ManagedKube dashboard showing pod costs per day for the last 5 days]({{ "/assets/blog/images/ManagedKube_Pod_Cost_Per_Day.png" | absolute_url }})

ManagedKube’s dashboard is an easy-to-read detailed cloud bill designed to help you take action. With this visibility, you can more accurately forecast your budget and identify opportunities for optimizing your cloud utilization.
