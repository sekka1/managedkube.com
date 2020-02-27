---
layout: post
title: Kubernetes using AWS Spot Fleet with Kops
categories: Kubernetes using AWS Spot Fleet with Kops
keywords: Kubernetes using AWS Spot Fleet with Kops
---
{%- include share-bar.html -%}

# AWS Spot Fleet Usaging with Kops and Kubernetes

We all want to lower cost and this is a constant ask that I get.

One way to cut cost is to use Spot instances.  Yes, there are dangers in using Spot Instances but
then again there can be a really big cost savings when using these instances since they
can be 2/3 of the price of an on-demand instance.

So how can you make it more "safe" to use Spot instances?  Enter Spot Fleet.  This
allows you to request a number of different Spot instance types so that your chances
of a single type is above your requested bid is lower.  The more instance type you
select the less of a chance you have that you will get priced out of it.

If you are using Kops to build your Kubernetes cluster on AWS, it supports [Spot Fleets](https://github.com/kubernetes/kops/blob/master/docs/instance_groups.md#creating-a-instance-group-of-mixed-instances-types-aws-only).

This post will go through on how to select a set of instances that makes sense to 
combine with the Kubernetes [cluster-autoscaler](https://github.com/kubernetes/autoscaler).

This post will not show you how to bring up a Kubernetes cluster.  If you want to learn about bringing up a Kubernetes cluster
to use all of these items, head over to our project [kubernetes-ops](https://github.com/ManagedKube/kubernetes-ops) which
shows you how to maintain Kubernetes cluster life cycle with a Gitops Workflow.

This is a must read before you go on to understand how the [cluster-autoscaler works with
Spot Fleet](https://github.com/kubernetes/autoscaler/tree/master/cluster-autoscaler/cloudprovider/aws#using-autoscalinggroup-mixedinstancespolicy)

# Lets do some simple math to see if this makes sense.  

My faviorte way to look at AWS instance pricing is with [ec2instances](https://www.ec2instances.info/).  it has a very
nice spreadsheet like layout that is way better than the AWS own website.

As an example we have been using this instance type with this cost:
```
c5.2xlarge	$0.340000 hourly	$248.200000 monthly
```

The `c5.2xlarge` has 8 CPU and 16GB of memory.

From reading the `cluster-autoscaler` link above, it does "kinda" support Spot Fleet but you have to select
instances with the same CPU and Memory size because that is how the `cluster-autoscaler` does it's calculation
on what to scale up.  If you use instances that don't have the same sizing, you will get unexpected results since
it won't know what capacity it just scaled up by.

Using the [ec2instances](https://www.ec2instances.info/) site, and trying to find instances with 8 CPU and
16GB of memory only yielded 4 instance type.  That was not nearly enough.  Playing around with the filtering
and filtering for 32 memory / 8 cpu yileded about 13 instance types!  This is a good start and for our use
case this combination of CPU and memory works for us.  Further filtering out machines that were just too
expensive, we came down to the following list:

```
m5a.2xlarge	$0.344000 hourly	0.1360-0.1900 - zone f is the cheapest
t2.2xlarge	$0.371200 hourly	0.0605		zone e is the cheapest
m5n.2xlarge	$0.476000 hourly	0.1360		all zones
h1.2xlarge	$0.468000 hourly	0.1413		all zones
t3.2xlarge	$0.332800 hourly	0.1002		all zones
m4.2xlarge	$0.400000 hourly	0.1361		zone d, e, f
t3a.2xlarge	$0.300800 hourly	0.0902		all zone
m5.2xlarge	$0.384000 hourly	0.1900		big fluctuations
m5d.2xlarge	$0.452000 hourly	0.1874		fluctuation
```

Not bad, this machine type at an on-demand price is around the same price but the Spot pricing is way WAY
far below what we were paying.

We then configured out Kops cluster's Spot fleet ot use all of these instance type.

Our kops config for this:

```yaml
    image: kope.io/k8s-1.12-debian-stretch-amd64-hvm-ebs-2019-08-16
          # This image is working with the spot fleet
    machineType: m5.2xlarge
    maxSize: 20
    minSize: 0
    # add the mixed instance here
    # Doc: https://github.com/kubernetes/kops/blob/master/docs/instance_groups.md#creating-a-instance-group-of-mixed-instances-types-aws-only
    mixedInstancesPolicy:
      instances:
      - m5a.2xlarge
      - t2.2xlarge
      - h1.2xlarge
      - t3.2xlarge
      - m4.2xlarge
      - t3a.2xlarge
      - m5.2xlarge
      - m5d.2xlarge
      onDemandAboveBase: 0
      spotInstancePools: 5
```

The next thing I do is to update my `cluster-autoscaler`'s configuration to scale out this new ASG.  My ASG group name
is `fleet-main-zone-X.dev2.us-east-1.k8s.local`.  I use [cluster-autoscaler helm chart](https://github.com/helm/charts/tree/master/stable/cluster-autoscaler)
to bring this up and I have an example usage here via my [kubernetes-ops](https://github.com/ManagedKube/kubernetes-ops/tree/master/kubernetes/helm/cluster-autoscaler) repository.

```yaml
Containers:
  aws-cluster-autoscaler:
    Container ID:  docker://afb878e10328975b309c724af4a04a5303412f916116abc5d292addc559d1683
    Image:         k8s.gcr.io/cluster-autoscaler:v1.14.6
    Image ID:      docker-pullable://k8s.gcr.io/cluster-autoscaler@sha256:e566a369b14648f257e25cae9bf4b6bea8af7fca47a8b7737fd91ea4934b35fa
    Port:          8085/TCP
    Host Port:     0/TCP
    Command:
      ./cluster-autoscaler
      --cloud-provider=aws
      --namespace=cluster-autoscaler
      --nodes=0:10:fleet-main-zone-a.dev2.us-east-1.k8s.local
      --nodes=0:10:fleet-main-zone-b.dev2.us-east-1.k8s.local
      --nodes=0:10:fleet-main-zone-c.dev2.us-east-1.k8s.local
```

I wait a while and now I see spot instances spun up:

![aws spot fleet instances](/assets/blog/images/spot-fleet-instances-1.png)

I can also verify the node is correct by describing one of the nodes just to verify it is all good.

```yaml
kubectl describe node ip-172-17-52-214.ec2.internal                                              
Name:               ip-172-17-52-214.ec2.internal
Roles:              node
Labels:             beta.kubernetes.io/arch=amd64
                    beta.kubernetes.io/instance-type=t3a.2xlarge
                    beta.kubernetes.io/os=linux
                    failure-domain.beta.kubernetes.io/region=us-east-1
                    failure-domain.beta.kubernetes.io/zone=us-east-1c
                    kubernetes-ops/hasPublicIP=false
                    kubernetes-ops/isSpot=true                                          
                    kubernetes.io/hostname=ip-172-17-52-214.ec2.internal
                    kubernetes.io/role=node
                    node-role.kubernetes.io/node=
                    prod.us-east-1.k8s.local/role=scale-zero
Annotations:        flannel.alpha.coreos.com/backend-data: {"VtepMAC":"4e:f4:02:72:2f:32"}
                    flannel.alpha.coreos.com/backend-type: vxlan
                    flannel.alpha.coreos.com/kube-subnet-manager: true
                    flannel.alpha.coreos.com/public-ip: 172.17.52.214
                    node.alpha.kubernetes.io/ttl: 0
                    volumes.kubernetes.io/controller-managed-attach-detach: true
CreationTimestamp:  Thu, 07 Nov 2019 21:26:03 -0800
Taints:             <none>
Unschedulable:      false
Conditions:
  Type             Status  LastHeartbeatTime                 LastTransitionTime                Reason                       Message
  ----             ------  -----------------                 ------------------                ------                       -------
  OutOfDisk        False   Fri, 08 Nov 2019 09:56:15 -0800   Thu, 07 Nov 2019 21:26:03 -0800   KubeletHasSufficientDisk     kubelet has sufficient disk space available
  MemoryPressure   False   Fri, 08 Nov 2019 09:56:15 -0800   Thu, 07 Nov 2019 21:26:03 -0800   KubeletHasSufficientMemory   kubelet has sufficient memory available
  DiskPressure     False   Fri, 08 Nov 2019 09:56:15 -0800   Thu, 07 Nov 2019 21:26:03 -0800   KubeletHasNoDiskPressure     kubelet has no disk pressure
  PIDPressure      False   Fri, 08 Nov 2019 09:56:15 -0800   Thu, 07 Nov 2019 21:26:03 -0800   KubeletHasSufficientPID      kubelet has sufficient PID available
  Ready            True    Fri, 08 Nov 2019 09:56:15 -0800   Thu, 07 Nov 2019 21:26:23 -0800   KubeletReady                 kubelet is posting ready status
Addresses:
  InternalIP:   172.17.52.214
  InternalDNS:  ip-172-17-52-214.ec2.internal
  Hostname:     ip-172-17-52-214.ec2.internal
Capacity:
 attachable-volumes-aws-ebs:  25
 cpu:                         8
 ephemeral-storage:           125753328Ki
 hugepages-1Gi:               0
 hugepages-2Mi:               0
 memory:                      32676964Ki
 pods:                        110
Allocatable:
 attachable-volumes-aws-ebs:  25
 cpu:                         8
 ephemeral-storage:           115894266893
 hugepages-1Gi:               0
 hugepages-2Mi:               0
 memory:                      32574564Ki
 pods:                        110
System Info:
 Machine ID:                 ec2d9f82f4e7fc287f6403b0b361e9ab
 System UUID:                EC2D9F82-F4E7-FC28-7F64-03B0B361E9AB
 Boot ID:                    c9b2929a-c8b4-45a1-89d0-c8918232d3dd
 Kernel Version:             4.9.0-9-amd64
 OS Image:                   Debian GNU/Linux 9 (stretch)
 Operating System:           linux
 Architecture:               amd64
 Container Runtime Version:  docker://18.6.3
 Kubelet Version:            v1.12.10
 Kube-Proxy Version:         v1.12.10
PodCIDR:                     100.104.146.0/24
```

This all looks good.

<!-- Blog footer share -->
{%- include blog-footer-share.html -%}

{% include blog-cta-1.html %}

{% include blog-cta-1.html %}
