---
layout: post
title: GKE Prometheus Operator alert KubeVersionMismatch
categories: GKE Prometheus Operator alert KubeVersionMismatch
keywords: GKE Prometheus Operator alert KubeVersionMismatch
---

I get this alert in my Slack channel and have no idea what this means.

![Annotations message: There are 2 different versions of Kubernetes components running.](/assets/blog/images/prometheus-alert-KubeVersionMismatch.png)

Lets dig into it.

I am using the Helm Chart Prometheus Operator: https://github.com/helm/charts/tree/master/stable/prometheus-operator

This gives me a very good all in one Prometheus/Grafana/Alerting solution all packaged up.

This alert is one of the default alerts that comes in the package.

The alert sends me to this run book:

```
- runbook_url: https://github.com/kubernetes-monitoring/kubernetes-mixin/tree/master/runbook.md#alert-name-kubeversionmismatch
```

However, that doesn't really tell me anything besides the name of the alert.

Lets look at the alert it self and the query in the Prometheus UI:

![alert: KubeVersionMismatch expr: count(count by(gitVersion) (kubernetes_build_info{job!="kube-dns"})) > 1](/assets/blog/images/prometheus-alert-KubeVersionMismatch-ui.png)

I still only seem to know that there are some different versions of Kubernetes running
but I am not sure exactly what it is talking about.

Here is the query:

```
count(count
  by(gitVersion) (kubernetes_build_info{job!="kube-dns"})) > 1
```

Let's run this query our self and get more details of what it returns

![query](/assets/blog/images/prometheus-alert-KubeVersionMismatch-query-1.png)

Ah right, it returns a number.  We can eliminate most of the query to see what
the underlying data is.  Change the query to:

```
kubernetes_build_info{job!="kube-dns"}
```

This would give you:

![query](/assets/blog/images/prometheus-alert-KubeVersionMismatch-gitversion.png)

Yup, per the query the data does show that there are different `gitVersion` being
returned.  

However, what does this mean?

Looking at the data some more you will notice this key/value

```
job="apiserver"
```

This is information on the job `apiserver`.  This is the Kubernetes API.  It is
saying currently there are 2 different versions of Kubernetes API server running
in this cluster.  This might or might not be a concern.  If you are upgrading
your Kubernetes cluster, then this is not a concern, because Kubernetes can work
in a multi version state.  You probably shouldnt leave it at this state but it will
be fine.  If you are not upgrading your server and nobody or no changes are suppose
to be happening then you will definitely want to investigate this.

As it turns out, I am running on GKE and it is automatically upgrading the kubernetes
master for me.
