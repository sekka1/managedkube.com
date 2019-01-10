---
layout: post
title: Introducing k8sBot
categories: kubernetes slack bot troubleshooting chatbot k8sbot
keywords: kubernetes slack bot troubleshooting chatbot k8sbot
---

# A Kubernetes troubleshooting chatbot


We have been working on a Slack chatbot that will help you to troubleshoot Kubernetes and would love your feedback. k8sBot is your friendly Kubernetes companion that helps you navigate the system and save you time.

We heard from a lot of you that Kubernetes is complex. We also learned that many non-infrastructure engineers are getting pulled into Kubernetes to deploy their code. We created k8sBot to help make Kubernetes easier to use.

k8sBot helps you quickly and easily troubleshoot your clusters. Ask k8sBot what’s going on in and it will let you know in plain old English. You can:
Save valuable time with faster troubleshooting
Learn Kubernetes on-the-fly
Quickly parse k8s output into easily digestible information

## K8sBot Pending Pod Use Example

A user encounters a very common Kubernetes problem: she’s deployed something but the pod is stuck in a pending state. She doesn’t know why, so she asks k8sBot what pods she has and why a pod is in a particular state. It can be that the cluster doesn’t have sufficient CPU/nodes/memory/etc. so the pod is stuck in a pending state.  With k8sBot this means that she can quickly find out the command(s) to view the error(s) and have the output interpreted. No more cryptic long output messages.

![Pod pending gif]({{ "/assets/blog/images/pod-pending-animation.gif" | absolute_url }})

This saves the developer’s time from having to learn “kubectl”, install it, configure it, and then get access to the cluster where her workload is running.  However, if the developer wanted to know how the bot got the information, the bot also provides the commands it used.

If the bot can make a determination of what the problem might be, it will also make recommendations.

## K8sBot Kubernetes Service Mapping Use Example

The way that Kubernetes maps and wires up containers (pods) to service discoverable resources and ingresses is very powerful but also can get very complex.  The question, “How is this service mapped to my pod?” is one of the top questions that comes up in our consulting business.  To troubleshoot this, you need to look at different pieces of information from kubectl to figure out how it is mapped together.

This is an example of a kubectl output when you describe a service:

```bash
$ kubectl -n  my-namespace describe svc my-app
Name:              my-app
Namespace:         my-namespace
Labels:            app=my-app

Annotations:       <none>
Selector:          app=my-app
Type:              ClusterIP
IP:                100.66.44.153
Port:              http  8080/TCP
TargetPort:        8080/TCP
Endpoints:         100.96.5.34:8080
Session Affinity:  None
Events:            <none>
```

All of the pertinent information is there, but if something is wrong, it can be very difficult and time-consuming to identify the right pieces of information and how to solve the problem quickly.  In addition to this output, you also need the information from the pod to make sure that they match up to what you have configured your service to.

In this instance, a user can ask the bot for the list of services and then put the information in a more human digestible way:

![service mapping diagnosis gif]({{ "/assets/blog/images/service-mapping-diagnosis-animation.gif" | absolute_url }})

The user can also ask the bot why the service is not working correctly.

I’ve run into this issue countless times. I’m looking at the lines of service description output and just can’t figure out what is wrong with it.  This bot can save users minutes to hours of debugging and poking around to figure out what is wrong with the service.

## Summary

We’d love to hear what you think about k8sBot. Would a Kubernetes troubleshooting chatbot be useful to you? What are the problems that you’d like k8sBot to help you solve? Drop us a line at info@managedkube.com
