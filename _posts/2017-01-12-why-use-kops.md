---
layout: post
title: "Why use Kops"
categories: Aws Kubernetes Kops
keywords: Aws Kubernetes Kops
author: Garland Kan

---
<a href="https://twitter.com/share?ref_src=twsrc%5Etfw" class="twitter-share-button" data-text="" data-via="managedkube" data-hashtags="#troubleshooting #devops #kubernetes" data-show-count="false">Tweet</a><script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>

## Why Use Kops to create your Kubernetes cluster

`kops` is a tool that creates a Kubernetes clusters and help you maintain/update it.

Here is the project page for kops: <https://github.com/kubernetes/kops>

Creating and maintaining a Kubernetes cluster has always been hard and it is just getting harder because there are more and more features added which requires more configurations. While you should understand how your cluster works, I think it is not reasonable to have each operator understand everything about it. That will make it unscalable.

`kops` provides a configuration driven way for you to provision a cluster and be able to update it throughout the cluster’s lifetime in a safe and predictable manor.

## Documentation

One of the most important thing about a project is documentation. If you cant find information about what it can do and how to do it without looking at the code, that makes it very hard to use.

`kops` is very well documented. Almost every feature has various examples on usage with cli commands included.

## Should you use it?

I think if you dont need a customized Kubernetes cluster then you should use `kops`. What I mean by “customized” is that you don’t want to run an OS that `kops` does not support or if you need to install something onto the base OS.

I also think that as a first swag at your application, use `kops` to bring up a Kubernetes cluster. Put your app on there and try it out. A lot of the time you dont need to customize too much because your application is Dockerized already and it is agnostic to what runs it.

If after running your application on a cluster that `kops` built and it doesnt work and you have determined that you need to build your own cluster, not much time is lost. Mostly all of your Kubernetes application configuration files will work on your new cluster with zero to almost zero changes. You now also have an example of a working Kubernetes cluster to compare your own custom cluster to.

I would argue even if you think it will not work, I would prototype it on a cluster brought up by `kops` first to see if it will work for your use case. If it does or through this process you find an alternate method to make your application work, you would have not only saved yourself time on creating a cluster yourself but countless hours in the future on upgrades and security patches that the `kops` team will take of for you.

## Final recommendation

I would recommend using `kops` it is a great tool that I have used over and over again for building dev and production Kubernetes clusters.

I would also recommend using some kind of automated way of bringing up a Kubernetes cluster. This can be something like [kubeadm][kubadm] or [kube spray][kube-spray]. Or go SaaS and dont even manage your own cluster with [GKE][gke]. My recommendation is rolling your own cluster is the last option.


[kubadm]: https://kubernetes.io/docs/setup/independent/create-cluster-kubeadm/
[kube-spray]: https://github.com/kubernetes-incubator/kubespray
[gke]: https://cloud.google.com/kubernetes-engine/