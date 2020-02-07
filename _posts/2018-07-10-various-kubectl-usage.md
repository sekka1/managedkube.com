---
layout: post
title: Various kubectl Usage
categories: kubernetes kubectl logs
keywords: kubernetes kubectl logs
# https://jekyll.github.io/jekyll-seo-tag/advanced-usage/#customizing-image-output
# This adds the html metadata "og:image" tags to the page for URL previews
image:
  path: "/assets/logo/M_1000.jpg"
#   height: 100
#   width: 100
description: kubernetes kubectl logs
---
{%- include share-bar.html -%}

Download the latest `kubectl` version here:  https://kubernetes.io/docs/tasks/tools/install-kubectl/


## Get a kube config file
The kube config file is your authentication with the cluster.  It has information
about the credentials to use and where the Kubernetes cluster is located.

Ask your local friendly administrator for this.

Once you have this file, place it in your local path: `~/.kube/config`

## Test accessing the cluster

Run this command:

```
kubectl get pods
```

## Viewing pods
This will list the pods

```
kubectl get pods
```

Viewing pods in another namespace.  Namespaces are semi isolated areas in the cluster.
We are using one "namespace" for each tenent.

```
kubectl --namespace devops get pods
```

## Getting logs

```
kubectl get logs <pod name from get pods>
```

## Get a shell in a container

This will start a bash shell in the container

```
kubectl exec -it <pod name from get pods> bash
```

Now you can do anything like install a mysql client

```
root@tomcat-7b9788b887-vfnnm:/usr/local/tomcat# apt-get update && apt-get install -y mysql-client
```

All of our MySQL RDS has a CNAME and inside of these containers it is simply referred to as: `mysql`
This will resolve to the local namespace's RDS instance.

```
mysql -h mysql -u root -p
```

That will connect you in.

## Port forwarding from your local to the cluster
doc: https://kubernetes.io/docs/tasks/access-application-cluster/port-forward-access-application-cluster/

```
port-forward <pod name from get pods> 8080:8080
```

kubectl port-forward $(kubectl get pod -o=name -lcomponent=... | awk -F/ '{print $2}' | head -1) PORT


Being able to port forward to a service is coming: https://github.com/kubernetes/kubernetes/pull/59809

<!-- Bog footer share -->
{%- include blog-footer-share.html -%}
