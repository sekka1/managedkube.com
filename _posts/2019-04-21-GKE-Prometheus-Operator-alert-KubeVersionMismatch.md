---
layout: post
title: Kubernetes Troubleshooting Walkthrough - GKE Prometheus Operator alert KubeVersionMismatch
categories: GKE Prometheus Operator alert KubeVersionMismatch
keywords: GKE Prometheus Operator alert KubeVersionMismatch
---
{%- include share-bar.html -%}

I am writing a series of blog posts about troubleshooting Kubernetes. One of the reasons why Kubernetes is so complex is because troubleshooting requires many levels of information gathering. It’s like trying to find the other end of a string in a tangled string ball.

Today, I got this alert in my Slack channel and I have no idea what this means.

![Annotations message: There are 2 different versions of Kubernetes components running.](/assets/blog/images/prometheus-alert-KubeVersionMismatch.png)

Let's dig into it.

I am using the <A HREF="https://github.com/helm/charts/tree/master/stable/prometheus-operator">Helm Chart Prometheus Operator</a>.
This gives me a very good all-in-one Prometheus/Grafana/Alerting solution.

This alert is one of the default alerts that comes in the package.

The alert sends me to this run book:

```
- runbook_url: https://github.com/kubernetes-monitoring/kubernetes-mixin/tree/master/runbook.md#alert-name-kubeversionmismatch
```

However, this doesn't really tell me anything besides the name of the alert.

Let's look at the alert it self and the query in the Prometheus UI:

![alert: KubeVersionMismatch expr: count(count by(gitVersion) (kubernetes_build_info{job!="kube-dns"})) > 1](/assets/blog/images/prometheus-alert-KubeVersionMismatch-ui.png)

I still only seem to know that there are some different versions of Kubernetes running
but I am not sure exactly what it is talking about.

Here is the query:

```
count(count
  by(gitVersion) (kubernetes_build_info{job!="kube-dns"})) > 1
```

Let's run this query ourselves and get more details of what it returns

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
in a multi version state.  You probably shouldn't leave it at this state but it will
be fine.  If you are not upgrading your server and no changes are planned, then you will definitely want to investigate this.

As it turns out, I am running on GKE and it is automatically upgrading the k8s
master for me.

{%- include blurb-consulting.md -%}

# More troubleshooting blog posts

* <A HREF="https://managedkube.com/kubernetes/k8sbot/troubleshooting/pending/pod/2019/02/22/pending-pod.html">Kubernetes Troubleshooting Walkthrough - Pending pods</a>
* <A HREF="https://managedkube.com/kubernetes/pod/failure/crashloopbackoff/k8sbot/troubleshooting/2019/02/12/pod-failure-crashloopbackoff.html">Kubernetes Troubleshooting Walkthrough - Pod Failure CrashLoopBackOff</a>
* <A HREF="https://managedkube.com/kubernetes/trace/ingress/service/port/not/matching/pod/k8sbot/2019/02/13/trace-ingress.html">Kubernetes Troubleshooting Walkthrough - Tracing through an ingress</a>
* <A HREF="https://managedkube.com/kubernetes/k8sbot/troubleshooting/imagepullbackoff/2019/02/23/imagepullbackoff.html">Kubernetes Troubleshooting Walkthrough - imagepullbackoff</a>

<!-- Blog footer share -->
{%- include blog-footer-share.html -%}

{% include blog-cta-1.html %}
