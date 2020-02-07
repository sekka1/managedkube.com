---
layout: post
title: Kops Cluster Templating
categories: kubernetes kops
keywords: kubernetes kops
---
{%- include share-bar.html -%}

If you havent heard of the tool Kops (https://github.com/kubernetes/kops) for create
Kubernetes clusters, you should check it out right now!  It is a great tool to create
and manage the lifecycle of a Kubernetes cluster.

If you use Kubernetes for production then you probably have some number of pre production
clusters like `dev`, `qa`, or `staging`.  Then this leads you to have to create and
maintain these clusters in a sane and easy way.  

The first thing is probably not creating the kops cluster with the kops cli tool input parameters.  
How would you version control this.  This most likely means you want to manage/maintain these clusters with kops cluster
yaml format that describes everything about the cluster (https://github.com/kubernetes/kops/blob/master/docs/apireference/examples/cluster/cluster.yaml).

But now you have more than one of these yaml files and when you update you have to make sure each one is updated.  This leads you to the cluster templating functionality the kops has to help you out:  https://github.com/kubernetes/kops/blob/master/docs/cluster_template.md

Now you can have one source `cluster.yaml` file which has templated out the names, region, etc and you have a `values.yaml` files which values for each cluster you want to make.

<!-- Bog footer share -->
{%- include blog-footer-share.html -%}
