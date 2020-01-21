---
layout: post
title: Kops Kubernetes Audit Policy Setup Debug
categories: Kops Kubernetes Audit Policy Setup Debug
keywords: Kops Kubernetes Audit Policy Setup Debug
---
{%- include twitter-button-blank.html -%}

Trying to enable Kubernetes audit policy on my Kops cluster but ran into some
problems during my setup.  Some of this guide is very specific to the problem
about the audit policy but I think this is a good guide on general troubleshooting
the Kubernetes Masters when a problem arises and what you should take a look
at to figure out what the Kube masters are doing and if it is running correctly.

If you are troubleshooting the Kubernetes masters, you might not have `kubectl`
access.  This means you will have to ssh into the Kubernetes master's nodes.  Yup,
back to Linux!!  I'll run you through what you should be looking for.

Here is the link to the doc: https://github.com/kubernetes/kops/blob/master/docs/cluster_spec.md#audit-logging

Copying this section into my cluster config:

```yaml
spec:
  kubeAPIServer:
    auditLogPath: /var/log/kube-apiserver-audit.log
    auditLogMaxAge: 10
    auditLogMaxBackups: 1
    auditLogMaxSize: 100
    auditPolicyFile: /srv/kubernetes/audit.yaml
```

Following the direction to load this in via the `fileAsset` directive:

```yaml
spec:
  fileAssets:
  # https://github.com/kubernetes/kops/blob/master/docs/cluster_spec.md#audit-logging
  - name: apiserver-audit-policy
    path: /srv/kubernetes/audit.yaml
    roles: [Master]
    content: |
      apiVersion: audit.k8s.io/v1 # This is required.
      # apiVersion: audit.k8s.io/v1beta1
      kind: Policy
      # Don't generate audit events for all requests in RequestReceived stage.
      omitStages:
        - "RequestReceived"
      rules:
        # Log pod changes at RequestResponse level
        - level: RequestResponse
          resources:
          - group: ""
          ...
          ...
          ...
```

Was able to apply this but non of the masters came back up and reachable.

So now, it is time to ssh into the master to find out what is going on.

If you are like me, I have the entire cluster on private subnets with no public
IPs.  You will need a bastion host and jump from there to be able to reach the
Kube master.  Here is the instructions on how to enable a bastion host in Kops:
https://github.com/kubernetes/kops/blob/master/docs/bastion.md#configure-the-bastion-instance-group

Lets take a look at what the `kubelet` is telling us:

```
core@ip-172-16-30-135 ~ $ journalctl -fu kubelet
Mar 26 02:56:56 ip-172-16-30-135.ec2.internal kubelet[2341]: I0326 02:56:56.266464    2341 kuberuntime_manager.go:757] checking backoff for container "kube-apiserver" in pod "kube-apiserver-ip-172-16-30-135.ec2.internal_kube-system(9725139d01a4b4c33809817a7f87b185)"
Mar 26 02:56:56 ip-172-16-30-135.ec2.internal kubelet[2341]: I0326 02:56:56.266637    2341 kuberuntime_manager.go:767] Back-off 2m40s restarting failed container=kube-apiserver pod=kube-apiserver-ip-172-16-30-135.ec2.internal_kube-system(9725139d01a4b4c33809817a7f87b185)
Mar 26 02:56:56 ip-172-16-30-135.ec2.internal kubelet[2341]: E0326 02:56:56.266668    2341 pod_workers.go:186] Error syncing pod 9725139d01a4b4c33809817a7f87b185 ("kube-apiserver-ip-172-16-30-135.ec2.internal_kube-system(9725139d01a4b4c33809817a7f87b185)"), skipping: failed to "StartContainer" for "kube-apiserver" with CrashLoopBackOff: "Back-off 2m40s restarting failed container=kube-apiserver pod=kube-apiserver-ip-172-16-30-135.ec2.internal_kube-system(9725139d01a4b4c33809817a7f87b185)"
```

This is a lot of output but the `kubelet` is telling us that the `kube-apiserver`
pod is crashing.  Lets check the logs of this container by listing the containers
running on this system:

```
core@ip-172-16-30-135 ~ $ docker ps -a | grep api
add61058922f        d82b2643a56a                         "/bin/sh -c 'mkfifo …"   3 minutes ago       Exited (1) 3 minutes ago                       k8s_kube-apiserver_kube-apiserver-ip-172-16-30-135.ec2.internal_kube-system_9725139d01a4b4c33809817a7f87b185_6
```

This is telling us that the container `k8s_kube-apiserver_kube-apiserver-ip...`
exited.  With Docker containers this means that PID 1 exited and did not hold the
process as it should.  Lets take a look at the logs:

```
core@ip-172-16-30-135 ~ $ docker logs add61058922f
...
...
I0326 03:02:46.509849       1 server.go:145] Version: v1.11.7
Error: loading audit policy file: failed decoding file "/srv/kubernetes/audit.yaml": no kind "Policy" is registered for version "audit.k8s.io/v1"
Usage:
  kube-apiserver [flags]

Flags:
...
...
```

Ive omitted a bunch of logs and just showing what is pertinent here.  Looks like
the Kube API tried to load my `audit.yaml` file but it failed on:

```
no kind "Policy" is registered for version "audit.k8s.io/v1"
```

Lets Google this!

First page I got was: https://stackoverflow.com/questions/54238430/cant-create-policy-no-matches-for-kind-policy

Same problem!! Yay!

No solved answer =(

However, one person did say he fixed it by changing this:

```
apiVersion: audit.k8s.io/v1
```

to, this:

```
apiVersion: audit.k8s.io/v1beta1
```

Let's give it a try!

Edit the file: /srv/kubernetes/audit.yaml

and change the `apiVersion`

After the change, just wait a minute or so.  The `kubelet` is continually restarting
this pod, which will restart the container.

Checking `docker` again:

```
core@ip-172-16-30-135 ~ $ docker ps | grep api
8215a06872dd        d82b2643a56a                         "/bin/sh -c 'mkfifo …"   About a minute ago   Up About a minute                       k8s_kube-apiserver_kube-apiserver-ip-172-16-30-135.ec2.internal_kube-system_9725139d01a4b4c33809817a7f87b185_8
```

It looks like the container has been up for a minute.  That is good news.  take
a look at the logs with `docker logs` and the server has started and functioning.

I can now use `kubectl` to see if I can talk to the Kube API:

```
core@ip-172-16-30-135 ~ $ kubectl get nodes
NAME                            STATUS    ROLES     AGE       VERSION
ip-172-16-30-135.ec2.internal   Ready     master    55s       v1.11.7
```

This is looking good.

This post showed you how to fix this specific issue, but in general the workflow
is valid for other Kubernetes Master related issues where it is so early in the
process where the Master doesn't even start up.  At that point, you will have to
ssh into the Master's node and start debugging from there.  I showed you that you
should first look at the `kubelet` logs to see if it is telling you anything, then
from there you might even have to interact with `docker` to get more logs and details
on what the problem is.

{%- include blurb-consulting.md -%}

<!-- Blog footer share -->
{%- include blog-footer-share.html -%}
