---
layout: post
title: KOPS Updating Cluster Is Awesome
categories: Kubernetes KOPS
keywords: Kubernetes kops
---

Here is yet another reason why I think the `Kops` tool for creating and managing a Kubernetes' cluster
lifecycle is so great.

I needed to do three things to the cluster:

1) Set the kubelet timeout to something higher than the default 2 minutes.  This cluster
is in ap-southeast-1 and pulling docker images is really really slow from there.  Some
time it takes longer than 2 minutes and the Kubelet will time out.

Well luckily `kops` exposes all of the Kubelets flags so you can tune it however
you want.  Here is the Kubelet documentation (https://kubernetes.io/docs/reference/command-line-tools-reference/kubelet/) showing me
I need to use the `--runtime-request-timeout` flag.  This maps to the `kops` doc (https://github.com/kubernetes/kops/blob/release-1.10/docs/apireference/build/documents/_generated_kubeletconfigspec_v1alpha2_kops_definition.md) on what this parameter should be in my `kops` configs:

```
runtimeRequestTimeout: 10m0s
```
This is documentation working to its finest here!  Everything lined up exactly how
I would expect it to.

2) Change the instance group size on a few instance groups

3) I needed to add a new `kops instance group`

So i make my changes and run the command to check what `kops` would change without
actually applying it to the cluster:

```
kops --name prod.ap-southeast-1.k8s.managedkube.com update cluster
```

It returns back to me this very handy and life saving diff on what it will exactly
do.

1) I can see here that it will add these `instance groups`:
```yaml
Will create resources:
  AutoscalingGroup/ondemand-zone-a-m4-large-infrastructure.prod.ap-southeast-1.k8s.managedkube.com
  	MinSize             	1
  	MaxSize             	5
  	Subnets             	[name:worker-zone-a.prod.ap-southeast-1.k8s.managedkube.com id:subnet-58c7a93f]
  	Tags                	{Project: cloud, k8s.io/cluster-autoscaler/node-template/label/k8s.info/hasPublicIP: false, k8s.io/cluster-autoscaler/node-template/label/k8s.info/instanceType: m4.large, k8s.io/cluster-autoscaler/node-template/label/k8s.info/application: infrastructure, k8s.io/role/node: 1, Name: ondemand-zone-a-m4-large-infrastructure.prod.ap-southeast-1.k8s.managedkube.com, k8s.io/cluster-autoscaler/node-template/label/prod.ap-southeast-1.k8s.managedkube.com/role: scale-zero, Owner: kubernetes, k8s.io/cluster-autoscaler/node-template/label/k8s.info/isSpot: false, k8s.io/cluster-autoscaler/node-template/label/kops.k8s.io/instancegroup: ondemand-zone-a, KubernetesCluster: prod.ap-southeast-1.k8s.managedkube.com, Purpose: kubernetes-ondemand-node, CostCenter: kubernetes-saas}
  	Granularity         	1Minute
  	Metrics             	[GroupDesiredCapacity, GroupInServiceInstances, GroupMaxSize, GroupMinSize, GroupPendingInstances, GroupStandbyInstances, GroupTerminatingInstances, GroupTotalInstances]
  	LaunchConfiguration 	name:ondemand-zone-a-m4-large-infrastructure.prod.ap-southeast-1.k8s.managedkube.com

  AutoscalingGroup/ondemand-zone-b-m4-large-infrastructure.prod.ap-southeast-1.k8s.managedkube.com
  	MinSize             	1
  	MaxSize             	5
  	Subnets             	[name:worker-zone-b.prod.ap-southeast-1.k8s.managedkube.com id:subnet-9455cbdd]
  	Tags                	{CostCenter: kubernetes-saas, Owner: kubernetes, k8s.io/cluster-autoscaler/node-template/label/k8s.info/isSpot: false, k8s.io/cluster-autoscaler/node-template/label/k8s.info/application: infrastructure, Name: ondemand-zone-b-m4-large-infrastructure.prod.ap-southeast-1.k8s.managedkube.com, KubernetesCluster: prod.ap-southeast-1.k8s.managedkube.com, Project: cloud, Purpose: kubernetes-ondemand-node, k8s.io/cluster-autoscaler/node-template/label/prod.ap-southeast-1.k8s.managedkube.com/role: scale-zero, k8s.io/cluster-autoscaler/node-template/label/kops.k8s.io/instancegroup: ondemand-zone-b, k8s.io/cluster-autoscaler/node-template/label/k8s.info/hasPublicIP: false, k8s.io/cluster-autoscaler/node-template/label/k8s.info/instanceType: m4.large, k8s.io/role/node: 1}
  	Granularity         	1Minute
  	Metrics             	[GroupDesiredCapacity, GroupInServiceInstances, GroupMaxSize, GroupMinSize, GroupPendingInstances, GroupStandbyInstances, GroupTerminatingInstances, GroupTotalInstances]
  	LaunchConfiguration 	name:ondemand-zone-b-m4-large-infrastructure.prod.ap-southeast-1.k8s.managedkube.com

  AutoscalingGroup/ondemand-zone-c-m4-large-infrastructure.prod.ap-southeast-1.k8s.managedkube.com
  	MinSize             	1
  	MaxSize             	5
  	Subnets             	[name:worker-zone-c.prod.ap-southeast-1.k8s.managedkube.com id:subnet-53715d15]
  	Tags                	{CostCenter: kubernetes-saas, k8s.io/cluster-autoscaler/node-template/label/k8s.info/isSpot: false, k8s.io/role/node: 1, Name: ondemand-zone-c-m4-large-infrastructure.prod.ap-southeast-1.k8s.managedkube.com, KubernetesCluster: prod.ap-southeast-1.k8s.managedkube.com, k8s.io/cluster-autoscaler/node-template/label/prod.ap-southeast-1.k8s.managedkube.com/role: scale-zero, Owner: kubernetes, Project: cloud, Purpose: kubernetes-ondemand-node, k8s.io/cluster-autoscaler/node-template/label/k8s.info/application: infrastructure, k8s.io/cluster-autoscaler/node-template/label/k8s.info/hasPublicIP: false, k8s.io/cluster-autoscaler/node-template/label/k8s.info/instanceType: m4.large, k8s.io/cluster-autoscaler/node-template/label/kops.k8s.io/instancegroup: ondemand-zone-c}
  	Granularity         	1Minute
  	Metrics             	[GroupDesiredCapacity, GroupInServiceInstances, GroupMaxSize, GroupMinSize, GroupPendingInstances, GroupStandbyInstances, GroupTerminatingInstances, GroupTotalInstances]
  	LaunchConfiguration 	name:ondemand-zone-c-m4-large-infrastructure.prod.ap-southeast-1.k8s.managedkube.com

  LaunchConfiguration/ondemand-zone-a-m4-large-infrastructure.prod.ap-southeast-1.k8s.managedkube.com
  	ImageID             	595879546273/CoreOS-stable-1745.7.0-hvm
  	InstanceType        	m4.large
  	SSHKey              	name:managedkube_automation_1 id:managedkube_automation_1
  	SecurityGroups      	[name:nodes.prod.ap-southeast-1.k8s.managedkube.com id:sg-c649a7bf]
  	AssociatePublicIP   	false
  	IAMInstanceProfile  	name:nodes.prod.ap-southeast-1.k8s.managedkube.com id:nodes.prod.ap-southeast-1.k8s.managedkube.com
  	RootVolumeSize      	128
  	RootVolumeType      	gp2
  	SpotPrice           	

  LaunchConfiguration/ondemand-zone-b-m4-large-infrastructure.prod.ap-southeast-1.k8s.managedkube.com
  	ImageID             	595879546273/CoreOS-stable-1745.7.0-hvm
  	InstanceType        	m4.large
  	SSHKey              	name:managedkube_automation_1 id:managedkube_automation_1
  	SecurityGroups      	[name:nodes.prod.ap-southeast-1.k8s.managedkube.com id:sg-c649a7bf]
  	AssociatePublicIP   	false
  	IAMInstanceProfile  	name:nodes.prod.ap-southeast-1.k8s.managedkube.com id:nodes.prod.ap-southeast-1.k8s.managedkube.com
  	RootVolumeSize      	128
  	RootVolumeType      	gp2
  	SpotPrice           	

  LaunchConfiguration/ondemand-zone-c-m4-large-infrastructure.prod.ap-southeast-1.k8s.managedkube.com
  	ImageID             	595879546273/CoreOS-stable-1745.7.0-hvm
  	InstanceType        	m4.large
  	SSHKey              	name:managedkube_automation_1 id:managedkube_automation_1
  	SecurityGroups      	[name:nodes.prod.ap-southeast-1.k8s.managedkube.com id:sg-c649a7bf]
  	AssociatePublicIP   	false
  	IAMInstanceProfile  	name:nodes.prod.ap-southeast-1.k8s.managedkube.com id:nodes.prod.ap-southeast-1.k8s.managedkube.com
  	RootVolumeSize      	128
  	RootVolumeType      	gp2
  	SpotPrice           	
```

2) It tells me that these instance groups `MinSize` will be changed from what value
to the new value:

```yaml
Will modify resources:
  AutoscalingGroup/spot-zone-a-m4-large-infrastructure.prod.ap-southeast-1.k8s.managedkube.com
  	MinSize             	 0 -> 1

  AutoscalingGroup/spot-zone-b-m4-large-infrastructure.prod.ap-southeast-1.k8s.managedkube.com
  	MinSize             	 0 -> 1

  AutoscalingGroup/spot-zone-c-m4-large-infrastructure.prod.ap-southeast-1.k8s.managedkube.com
  	MinSize             	 0 -> 1
```

3) Then finally it outputs the `runtimeRequestTimeout` flag that I wanted to add and
it enumerates that through to all of the instance groups that it touched.

```yaml
  LaunchConfiguration/master-ap-southeast-1a.masters.prod.ap-southeast-1.k8s.managedkube.com
  	UserData            
  	                    	...
  	                    	    podInfraContainerImage: gcr.io/google_containers/pause-amd64:3.0
  	                    	    podManifestPath: /etc/kubernetes/manifests
  	                    	+   runtimeRequestTimeout: 10m0s
  	                    	  masterKubelet:
  	                    	    allowPrivileged: true
  	                    	...
  	                    	    podManifestPath: /etc/kubernetes/manifests
  	                    	    registerSchedulable: false
  	                    	+   runtimeRequestTimeout: 10m0s

  	                    	  __EOF_CLUSTER_SPEC
  	                    	...


  LaunchConfiguration/master-ap-southeast-1b.masters.prod.ap-southeast-1.k8s.managedkube.com
  	UserData            
  	                    	...
  	                    	    podInfraContainerImage: gcr.io/google_containers/pause-amd64:3.0
  	                    	    podManifestPath: /etc/kubernetes/manifests
  	                    	+   runtimeRequestTimeout: 10m0s
  	                    	  masterKubelet:
  	                    	    allowPrivileged: true
  	                    	...
  	                    	    podManifestPath: /etc/kubernetes/manifests
  	                    	    registerSchedulable: false
  	                    	+   runtimeRequestTimeout: 10m0s

  	                    	  __EOF_CLUSTER_SPEC
  	                    	...
```

And finally at the end, it tells me if I want to apply these changes add the
`--yes` to my command.

```yaml
Must specify --yes to apply changes
```

This is absolutely wonderful and this is how I like to apply infrastructure level
changes to my cluster.  Changing any of these without being certain what it will
do exactly can mean major downtime!  I know this is mostly just a diff of some
configs but it is presented out so well that every time I apply this type of changes
it makes me smile =).
