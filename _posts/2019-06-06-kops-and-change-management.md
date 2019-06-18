---
layout: post
title: Kops Change Management
categories: Kops Change Management
keywords: Kops Change Management
---

I am a big fan of using [kops](https://github.com/kubernetes/kops) for deploying
a Kubernetes clusters and more importantly maintaining a cluster throughout it's lifecycle.
Cluster creation is probably less than 10% of all the activities you will do with
a cluster.  The majority of the time is updating the cluster versions and changing
different settings to meet your needs as you evolve with the cluster.

This is why being able to make changes to a cluster *safely* should be a very
big part of any tool you use for deploying and maintaining Kubernetes clusters.  Kubernetes
clusters are very complex!

This is one of the big reason why I Love the kops tool so much.  Not only does it allow
me to bring up a cluster with the options I want but it also allows me to update it
in a very safe manor and it tells me what is about to be applied to the cluster.

Here is an example that inspired this blog where I just had to write about it.  It
is not so much about what I am changing here (which I will go through) but it is
more about what the tool told me what will happen.

Here is the scenario and what I wanted to change.  We have two regions with non-overlapping
IPs (yeah we thought ahead =) ) that we want to VPC peer together so that they can reach each other.
So great, we peered it and setup the routes but traffic couldn't reach each other.
With a little bit of troubleshooting we found out that `docker0` interface was using
the same IP CIDR range as the peered VPC =(.  So either change the VPC CIDR or the
`docker0` CIDR.  We choose to change the `docker0` CIDR.

Looking around in kops we found the option to change it but it wasn't very well
documented.  The best we found was this: https://github.com/kubernetes/kops/issues/5336

So we gave it a try adding this seciton into our kops cluster config:

```
docker:
  bridgeIP: 172.26.0.0/16
  logDriver: json-file
```

Then we ran: `kops cluster update my-cluster`

```
...
...
Will modify resources:
  LaunchConfiguration/infrastructure-zone-a.dev.us-east-1.k8s.local
        UserData            
                                ...
                                  cloudConfig: null
                                  docker:
                                +   bridgeIP: 172.26.0.0/16
                                    ipMasq: false
                                    ipTables: false
                                    logDriver: json-file
                                    logLevel: warn
                                -   logOpt:
                                -   - max-size=10m
                                -   - max-file=5
                                    storage: overlay2,overlay,aufs
                                    version: 17.03.2
                                ...

...
...
```  

This is showing me that by adding this option it is actually removing a few entries,
the ones with the minus sign on the left of it:

```
-   logOpt:
-   - max-size=10m
-   - max-file=5
```

Yikes...I don't think we want to remove entires.

So we updated the config to include those:

```
docker:
  bridgeIP: 172.26.0.0/16
  logDriver: json-file
  logOpt:
  - max-size=10m
  - max-file=5
```

Then ran `kops cluster update my-cluster` again:

```
...
...
LaunchConfiguration/on-demand-zone-a.dev.us-east-1.k8s.local
      UserData            
                              ...
                                cloudConfig: null
                                docker:
                              +   bridgeIP: 172.26.0.0/16
                                  ipMasq: false
                                  ipTables: false
                              ...
...
...                            
```

Which gave us one change, which is adding the `bridgeIP` param.

I find this awesome!  It is very expressive on what config is changing and it displays
it to my output in a clear and concise manor.
