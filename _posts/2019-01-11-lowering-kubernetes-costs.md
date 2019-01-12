---
layout: post
title: "How to Dramatically Lower Cloud Costs of Kubernetes Clusters: A Complete Guide and Walkthrough"
categories: kubernetes cloud costs
keywords: kubernetes cloud costs

---

- [Introduction: cost saving with your Kubernetes clusters](#Introduction: cost saving with your Kubernetes clusters)
- [How to Use Cluster AutoScaler to Keep Your Kubernetes Costs Under Control](#How to Use Cluster AutoScaler to Keep Your Kubernetes Costs Under Control)

## Introduction: cost saving with your Kubernetes clusters
Kubernetes is a great platform to deploy your application on.  It gives you a very nice framework to work in and it takes care of a lot of the low-level infrastructure for you.  This makes it easier for you and developers to deploy applications on top of it.  However, as with all things, there are tradeoffs that come with this ease of deployment. It’s great because it is easy and there is less friction to deploying applications, but this also means that users can turn on applications with little effort and thought. We regularly see clients whose clusters are very overprovisioned and have single digit utilization ($$$!).

Because of the inherent sharing nature of Kubernetes, it’s difficult to see what is driving your cloud costs and therefore understand how you can lower cloud costs. In this post, I’ll outline the best options for helping you lower your cloud spend, from open source tactics to paid tools. 

### First things first: understand your cloud spend today
The first step in controlling your costs is to take the time to understand what your current infrastructure and costs look like.  Assess the situation and then make smart data-driven decisions based on your evaluation. I fully believe in the mantra, “What you measure, you manage.” You need to know what’s going on holistically to take informed, actionable steps towards reducing your costs.  What if your major cost is not from instances but instead from users that are requesting very large and fast EBS disks?  By not taking an inventory on your compute spend, you might be optimizing the wrong thing.

From your cloud provider’s billing dashboard, you can get a total monthly cost across all services or by tags based on just one or all of your Kubernetes clusters:

![Example AWS Cost Dashboard]({{ "/assets/blog/images/costs-walkthrough-1-aws-dashboard-1.png" | absolute_url }})

This is a good starting point for high-level information.  It is telling me that I am spending around $100/day on all services on AWS.  This makes my monthly bill ~$3k. This information is good but it is too high level.  Let's try to drill down into the details.  

![Example AWS Cost Dashboard Drilldown]({{ "/assets/blog/images/costs-walkthrough-2-aws-dashboard (2).png" | absolute_url }})

Grouping by “Service” gives us a stacked bar chart on where the daily costs are going.  From here, we can see that “EC2-Other” and “EC2-Instances” are >80% of the daily cost and the rest of the costs are distributed over a few other items that are not contributing that much to my overall cloud spend. 

From this, I know to focus my energy and time on reducing EC2 costs because that is the primary driver of my monthly costs. I have the potential to save significant dollars by taking a closer look at my EC2 instances. 

You might be wondering what “EC2-Other” and “Others” are.  Depending on the view, AWS buckets items like snapshots, load balancers, or NAT gateways into these categories.

If you group by “User Type,” you can get info about which instance and spot types are allocated to a day’s cost:

![Example AWS Spot Pricing]({{ "/assets/blog/images/costs-walkthrough-3-spot pricing-1.png" | absolute_url }})

This is great information about my total costs at a high-level but I still don’t know which group of users on my Kubernetes cluster is using the most resources.  Let’s say that $80/day on instance related cost is too high and we need to optimize our cloud spend. These charts point out what is my biggest cost, but not which team or customer is responsible for it. 

This is because Kubernetes is a multi-tenant type cluster system.  Workloads from different applications or groups can run on the same node, giving you better efficiency and resiliency by utilizing a single node for more than one purpose. However, this also makes your billing and infrastructure more complicated.

To get the additional information we need, we need to go to Kubernetes and ask, “What is running on these nodes and how much CPU, memory, and disk(s) is it using?”

### Tools for understanding your cloud bill

There are many different ways that you can understand your Kubernetes cost allocation: 
- The large cloud monitoring companies, such as <A HREF="https://www.cloudhealthtech.com/">CloudHealth</a>, <A HREF="https://cloudcheckr.com/">CloudCheckr</a>, and <A HREF="https://www.cloudability.com/">Cloudability</a> , offer monitoring. This comes at a steep price (2.5% of your cloud spend) and unfortunately, you cannot decouple this feature from their overall product.
- For clusters on GKE, they just launched a free <A HREF="https://cloud.google.com/kubernetes-engine/docs/how-to/cluster-usage-metering">cost metering</a> tool. Here's an example of what the <A HREF="https://datastudio.google.com/u/0/reporting/1JsheUOianMrAlIIyR8Uk8-HSiJX2vLHZ/page/bLKZ">dashboard</a> looks like. 
- CoreOS offers an <A HREF="https://coreos.com/blog/metering">open source metering option</a>
- <A HREF="https://www.ManagedKube.com">ManagedKube</a> offers a paid Kubernetes cost allocation product.

The tool that you use to understand your cloud bill isn’t important, only that you build cost visibility into your Kubernetes clusters. Once you know how much you’re spending on every pod, instance, and persistent volume, you can:
- Create a game plan for controlling your cloud spend
- Understand product margins in multi-tenancy situations
- Forecast and plan budgets better

## How to Use Cluster AutoScaler to Keep Your Kubernetes Costs Under Control

The Cluster AutoScaler is a Kubernetes project that helps you dynamically scale your cloud instances on and off.  The project source and documentation is <A HREF="https://github.com/kubernetes/autoscaler/tree/master/cluster-autoscaler">here</a>.
It is a process that you turn on inside your cluster that talks to the Kubernetes API and watches for certain signals.  One of those signals are pods in a “pending” state due to no nodes being available for Kubernetes to schedule (run) on.

By doing a `kubectl get pods`, you can see what is pending.

![kubectl example]({{ "/assets/blog/images/costs-walkthrough-4-kubectl.png" | absolute_url }})

The Cluster AutoScaler takes into account what you are trying to turn on and if the nodes groups it has access to for it to increase the number of nodes.  If it determines that the pod that is trying to get scheduled out will get scheduled out if it turned on a new instance, the cluster autoscaler will turn on an instance for you.

By describing a pod by running the command: `kubectl describe pod <pod name>`

You get the events that are associated with this pod.  This event is telling you that the cluster-autoscaler” is scaling up the instance group from 1 node to 2 nodes.

![Cluster Autoscaler Example]({{ "/assets/blog/images/costs-walkthrough-5-autoscaler.png" | absolute_url }})

You might think, great...this will help me spend more.  How is this helping me save money?  

That is a very good question.  The Cluster AutoScaler also does this in reverse with the `scale-down-*` <A HREF="https://github.com/kubernetes/autoscaler/blob/master/cluster-autoscaler/FAQ.md#what-are-the-parameters-to-ca">parameters</a>.  The Cluster AutoScaler will scale down nodes with nothing running on them or, depending on the settings you give the cluster autoscaler, it will try to condense the pods you have running on various instances down to fewer instances.

This is a very good service for your cluster as it will scale the number of instances up and down per your requirements.  While this tool works well, it is not foolproof.  The Cluster AutoScaler is pretty conservative; most of the scaling down errs on the side of caution.  If for any reason it thinks it is not safe to scale down and compact the pods onto a node, it will not.  So while you might take a look at it and think it should scale down, sometimes it might not.  You have to tune the `scale-down-*` parameters to your liking.  The first step is to run this and have it in motion. Then, you can continue to customize the Customer AutoScaler’s actions to mirror how you would manage the cluster.
