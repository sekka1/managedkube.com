---
layout: post
title: "Kubernetes: troubleshooting ingress and services traffic flows"
categories: Kubernetes ingress flows
keywords: Kubernetes ingress flows
author: Garland Kan

---

Kubernetes is a great platform to run your application on, and it handles a lot of the heavy lifting on wiring up your application together.

You can write a few yaml configuration files and get a three tiered web architecture running in minutes. There is a lot of “magic” happening on the backend to make all of this happen. It is great when everything works. It can get confusing when things are not working as you expect it to. I am going to walk you through how traffic flows from the ingress all the way to a pod. Hopefully, this will help you in your troubleshooting effort in the future.

Let’s say you followed this tutorial (https://github.com/wercker/kubernetes-ci-cd). You should have a setup and traffic flow like:

Internet <--> AWS ELB <--> k8s ingress <--> k8s service <--> k8s pods

If you followed the tutorial, everything should work as expected, but what if they doesn’t? Where and how would you troubleshoot this? I think it is the easiest to walk through the traffic flow from the right (k8s pods) to the left (internet) in the above diagram. If this is an initial cluster turn up, then all parts have to be verified but if this cluster has been running then mostly the stuff on the left is working already. So assuming this is a known good cluster troubleshooting from the pods first makes sense since that would most likely be the new or the part that has changed in the infrastructure.

## The pod
The first thing to do is to make sure that the pod is up and running and doing what you want it to do.

Make sure the pod’s “Status” is “Running” (pod status doc).

```$ kubectl get pods -o wide
NAME                  READY     STATUS    RESTARTS   AGE       
IP            NODE
web-2136164036-ghs1p  1/1       Running   0          36m       100.96.3.11   ip-172-20-57-113.ec2.internal```
Look at the logs to make sure everything looks good.

```$ kubectl logs web-2136164036-ghs1p
warn:    --minUptime not set. Defaulting to: 1000ms
warn:    --spinSleepTime not set. Your script will exit if it does not stay up for at least 1000ms
sleep: using busy loop fallback
Server is running on port 3000```
The logs seem to be good. It says it is listening on port 3000. Your application will most likely have some other output, but the general idea here is to make sure the logs looks good. If the logs don’t indicate your application is up or running, then it is time to make your logs a little bit more verbose.

Once we verified that our pod/application is running, we can keep walking left in our traffic flow to see if anything else is wrong.

##The Kubernetes Service
Now, we will take a look at the Kubernetes service. A Kubernetes service is an abstraction on top of a set of related pods. Every Kubernetes service is assigned a virtual IP and requests to that service will be proxied to one of its pods. This is important because your pod has to be running and has passed it’s healthchecks before it is added to the service.

Let’s list all of the services:

```$ kubectl get service
NAME                    CLUSTER-IP       EXTERNAL-IP   PORT(S)    AGE
web                       100.70.162.175   <none>              3000/TCP   39m```
For this current example, we are interested in the “web” application. Let’s describe this service.

```$ kubectl describe service web
Name: drywall-flowlog-stats
Namespace: flowlog-stats
Labels: app=drywall-flowlog-stats
Selector: app=drywall-flowlog-stats
Type: ClusterIP
IP: 100.70.162.175
Port: http 3000/TCP
Endpoints: 100.96.3.11:3000
Session Affinity: None
No events.```
The item we are looking for in here is that there is an IP address in the “Endpoints” field. The IP addresses in this field mean that these pods are running and has passed their health checks. If you also take a look back above to where you listed all of the pods. This IP should be on that list.

Declaring a healthcheck for your pod is very important. It is basically some kind of check towards your pod to tell Kubernetes that your pod is alive and healthy. This can be an HTTP request to some endpoint that is returning and HTTP 200 or it can be a command that runs in the container. Here is more information about how to setup the healthchecks: https://kubernetes.io/docs/tasks/configure-pod-container/configure-liveness-readiness-probes/.

Since we do have a healthcheck on our pod and the IP showed up in the service endpoint. We know that the pod is up and running in a good state and that Kubernetes service proxy is proxying it. We can move onto the next item in the traffic flow.

##The Ingress
Next we will take a look at the ingress to make sure everything is hooked up correctly here.

List all of the ingresses:

```$ kubectl get ingress
NAME    HOSTS             ADDRESS         PORTS     AGE
Web     www.example.com   54.236.40.106   80, 443   46m```
Let’s get more details about the ingress we are working with: “web”.

```$kubectl describe ingress web
Name: Web
Namespace: default
Address: 54.236.40.106
Default backend: default-http-backend:80(<none>)
Rules
Host Path Backends
 ---- ---- --------
www.example.com
    / web:3000 (<none>)```
We are checking to make sure that the output here is routing to the correct place. Looking at the “Backends” column, you’ll note that it is sending traffic to the endpoints in the service named “web”. This is what I would expect to see if everything was configured correctly. If everything still looks good, we can “jack” into the ingress controller and see if it can reach your pod.

Let’s “jack” into the ingress pod. First we need to find the ingress pod.

```$ kubectl get pods
NAME                            READY     STATUS    RESTARTS   AGE       IP             NODE
nginx-ingress-1167843297-40nbm  1/1       Running   0          1d        10.2.105.5     ip-10-15-82-74.ec2.internal```
It should be a pod named Nginx, ingress, or something. It is really up to you to name the ingress whatever you want. Once you have the ingress pod’s name, we can “exec” into it with an interactive shell and run the cURL command to see what the Ingress returns us.

```$ kubectl exec nginx-ingress-1167843297-40nbm -it bash
root@nginx-ingress-1167843297-40nbm:/# 
root@nginx-ingress-1167843297-40nbm:/#
root@nginx-ingress-1167843297-40nbm:/# curl -H "HOST: www.example.com" localhost 
Hello from web
root@nginx-ingress-1167843297-40nbm:/#```
We got an interactive shell into the ingress controller pod (note: you can do this with any pod). The Ingress pod is running a Nginx process that creates its config from querying the Kubernetes API on which “ingress” resources have been created on the cluster, and it gets the backend IPs from the Kubernetes service endpoints.

Next, we are executing the cURL command to see if the nginx is returning the content from our “web” pod. The Nginx is doing virtual host routing, which means we have to pass in the “HOST” header for the server we are trying to reach. Replace “www.example.com” to what you have configured.

In this example, the cURL call returned “Hello from web” which is what I expect from my “web” pod.

## ELB
The next item down the line is the ELB. We are making sure traffic is routing from the Internet through the ELB into our Ingress and to the Kubernetes cluster. We will use the same cURL trick we used in the last step to verify this. Doing this eliminates any DNS issues that we might have (we will talk about DNS issues next).

The first thing we need to do is to find out the ELB URL. If you are using a cloud provider that created your external load balancer for you, you can issue a few kubectl commands to get it by first listing all of the services:

```$ kubectl get services
 
NAME                      CLUSTER-IP       EXTERNAL-IP        PORT(S)                      AGE

ingress-default-backend   100.69.45.28     <none>             
80/TCP                       14d
ingress-lb                100.70.128.181  a1e2f6a9e0f76...    80:32686/TCP,443:31345/TCP   14d```
Here the ingress service is “ingress-lb”. Let’s describe that ingress to get more information.

```$ kubectl describe service ingress-lb
Name: ingress-lb
Namespace: default
Labels: <none>
Selector: app=ingress-controller
Type: LoadBalancer
IP: 100.70.128.181
LoadBalancer Ingress: a1e2f6a9e0f7611e79389293847201-54998145.us-east-1.elb.amazonaws.com
Port: http 80/TCP
NodePort: http 32686/TCP
Endpoints: 100.96.3.6:80
Port: https 443/TCP
NodePort: https 31345/TCP
Endpoints: 100.96.3.6:443
Session Affinity: None
No events.```
In the describe output it tells us that the external load balancer is `a1e2f6a9e0f7611e79389293847201-54998145.us-east-1.elb.amazonaws.com`

Now we have all of the information we need to make a test cURL call over to the external load balancer. On your local machines terminal run the following:

```$ curl -H "HOST: www.example.com" a1e2f6a9e0f7611e79389293847201-54998145.us-east-1.elb.amazonaws.com```
This will make a cURL call over to the load balancer with the host headers. If everything is working correctly it would return content from your web pod. If it doesnt, then it is most likely something is misconfigured with your external load balancer.

## DNS
The next thing to check is if the DNS is resolving correctly to our external load balancer. If it is not, then going to “www.example.com” is just sending you to the wrong place.

We will run an `nslookup` on our local terminal.

```$ nslookup www.example.com
Server: 127.0.1.1
Address: 127.0.1.1#53
Non-authoritative answer:
www.example.com canonical name = a1e2f6a9e0f7611e79389293847201-54998145.us-east-1.elb.amazonaws.com
Name: a1e2f6a9e0f7611e79389293847201-54998145.us-east-1.elb.amazonaws.com
Address: 52.21.46.39```
The `nslookup` should return us an answer that has our external load balancer’s URL or IP. If it doesnt, then the DNS for “www.example.com” is set to the incorrect CNAME.

##Summary
This walk through took you through the traffic flow on how traffic from the internet will get to your application (aka pod). Everything shown here is in a working state. It is good to see what to expect in a working state so that you can compare to a nonworking one. I hope the series of commands and what to look for helps you out troubleshooting your Kubernetes application.
