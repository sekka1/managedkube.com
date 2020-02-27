---
layout: post
title: How to use Prometheus and k8sBot
categories: GKE Prometheus k8sBot
keywords: GKE Prometheus k8sBot
---
{%- include share-bar.html -%}

This is us eating our own dog food.  How else are we going to find these use
cases =)

Our Prometheus alerting sent an alert to our Slack channel telling us that the
deployment for one of our GCP Marketplace backend pods `has been in a non-ready state for longer than an hour.`

![Kubernetes prometheus slack alert KubeDeploymentReplicasMismatch](/assets/blog/images/k8sbot-prometheus-blog-backend-server-alert.png)

From there in Slack, I can ask the bot to list the pods:

![k8sbot prometheus alert pod list with drop down menu in slack](/assets/blog/images/k8sbot-prometheus-blog-pod-list.png)

Then I see that the `k8sbot-gcp-marketplace-backend-server-5f54ddbdd5-6bbrf` is
in a `ImagePullBackOff` state.  

I then ask @k8sBot to describe this pod from the drop down menu, which then @k8sbot returns in Slack:

![k8sbot prometheus alert pod list with drop down menu in slack and describe pod with ImagePullBackOff kubernetes](/assets/blog/images/k8sbot-prometheus-blog-pod-describe.png)

This telling me that it is failing to pull the image and there is a specific event
that k8sBot brought back to us that gives a really good clue on what happened:

```
unauthorized: incorrect username or password
```

That triggered my memory that Docker Hub had one of their databases compromised
and they sent out emails to everyone to reset the password

https://success.docker.com/article/docker-hub-user-notification

So I did.  However, this has some downstream effects that were not known to me
at the time, like this one.  The final fix was to update the password used to pull
these images and we are back!

{%- include blurb-consulting.md -%}

<!-- Blog footer share -->
{%- include blog-footer-share.html -%}

{% include blog-cta-1.html %}
