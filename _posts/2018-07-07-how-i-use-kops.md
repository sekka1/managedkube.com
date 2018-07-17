---
layout: post
title: How I Use Kops
categories: AWS kubernetes kops
keywords: AWS kubernetes kops
author: Garland Kan

---

In a previous blog, I talked about [why I used `kops`]() and in this blog, I will   <----------
talk about how I use `kops`.

I always create my clusters by providing `kops` a yaml file describing the cluster
that I want.  Here is an example cluster configuration file.  You can also find
the source link [here]().                                           <-----------------------------

This file is then checked into the source code management so that we can keep
track of changes.

```
#
# Kops cli: 1.9.0
#
apiVersion: kops/v1alpha2
kind: Cluster
metadata:
  name: prod-1.k8s.devops.bot
spec:
  sshKeyName: ssh_key_file
  networkID: vpc-123456
  kubernetesApiAccess:
  # Nat GWs
  - 35.168.171.252/32
  - 52.5.225.10/32
  - 52.20.55.117/32
  - 52.201.181.125/32
  - 35.153.215.185/32
  - 34.237.126.200/32
  # Internal routes
  - 10.0.0.0/8
  api:
    dns: {}
    loadBalancer:
      type: Public
      idleTimeoutSeconds: 300
  authorization:
    rbac: {}
  channel: stable
  cloudProvider: aws
  configBase: s3://kubernetes-kops-store/prod-1.k8s.devops.bot
  etcdClusters:
  # https://github.com/kubernetes/kops/blob/master/docs/cluster_spec.md#etcdclusters-v3--tls
  - enableEtcdTLS: true
    etcdMembers:
    - instanceGroup: master-us-east-1a
      name: a
    - instanceGroup: master-us-east-1b
      name: b
    - instanceGroup: master-us-east-1c
      name: c
    name: main
    version: 3.0.17
  - enableEtcdTLS: true
    etcdMembers:
    - instanceGroup: master-us-east-1a
      name: a
    - instanceGroup: master-us-east-1b
      name: b
    - instanceGroup: master-us-east-1c
      name: c
    name: events
    version: 3.0.17
  iam:
    # https://github.com/kubernetes/kops/blob/master/docs/iam_roles.md#iam-roles
    allowContainerRegistry: false
    legacy: false
  kubeAPIServer:
    # auditLogPath: /var/log/kube-apiserver-audit.log
    # auditLogMaxAge: 10
    # auditLogMaxBackups: 1
    # auditLogMaxSize: 100
    # auditPolicyFile: /srv/kubernetes/audit.conf
    # https://github.com/kubernetes/kops/blob/master/docs/cluster_spec.md#runtimeconfig
    # runtimeConfig:
    #   batch/v2alpha1: "true"
    #   apps/v1alpha1: "true"
    # #Istio perm for kops: https://istio.io/docs/setup/kubernetes/quick-start.html#aws-wkops
    admissionControl:
    - NamespaceLifecycle
    - LimitRanger
    - ServiceAccount
    - PersistentVolumeLabel
    - DefaultStorageClass
    - DefaultTolerationSeconds
    - MutatingAdmissionWebhook
    - ValidatingAdmissionWebhook
    - ResourceQuota
    - NodeRestriction
    - Priority
  kubelet:
    # https://github.com/kubernetes/kops/blob/master/docs/security.md#kubelet-api
    # anonymousAuth: false
    # kubeReserved:
    #     cpu: "100m"
    #     memory: "100Mi"
    #     storage: "1Gi"
    # kubeReservedCgroup: "/kube-reserved"
    # systemReserved:
    #     cpu: "100m"
    #     memory: "100Mi"
    #     storage: "1Gi"
    # systemReservedCgroup: "/system-reserved"
    # enforceNodeAllocatable: "pods,system-reserved,kube-reserved"
  kubernetesVersion: 1.9.3
  masterPublicName: api.prod-1.k8s.devops.bot
  networkCIDR: 10.151.0.0/16
  networking:
    canal: {}
  nonMasqueradeCIDR: 100.64.0.0/10
  sshAccess:
  - 10.0.0.0/8
  subnets:
  # utility subnets
  - cidr: 10.151.0.0/24
    name: us-east-1a-utility
    type: Utility
    zone: us-east-1a
  - cidr: 10.151.1.0/24
    name: us-east-1b-utility
    type: Utility
    zone: us-east-1b
  - cidr: 10.151.2.0/24
    name: us-east-1c-utility
    type: Utility
    zone: us-east-1c
  - cidr: 10.151.3.0/24
    name: us-east-1d-utility
    type: Utility
    zone: us-east-1d
  - cidr: 10.151.4.0/24
    name: us-east-1e-utility
    type: Utility
    zone: us-east-1e
  - cidr: 10.151.5.0/24
    name: us-east-1f-utility
    type: Utility
    zone: us-east-1f
  # Kube masters subnets
  - cidr: 10.151.15.0/24
    name: kube-master-1a
    type: Private
    zone: us-east-1a
  - cidr: 10.151.16.0/24
    name: kube-master-1b
    type: Private
    zone: us-east-1b
  - cidr: 10.151.17.0/24
    name: kube-master-1c
    type: Private
    zone: us-east-1c
  # worker subnets
  - cidr: 10.151.21.0/24
    name: worker-zone-a
    type: Private
    zone: us-east-1a
    # egress: nat-0b280bf309e7fd7e7
    # id: subnet-c9a6f2ad
  - cidr: 10.151.22.0/24
    name: worker-zone-b
    type: Private
    zone: us-east-1b
    # egress: nat-0b280bf309e7fd7e7
    # id: subnet-aa1e7c95
  - cidr: 10.151.23.0/24
    name: worker-zone-c
    type: Private
    zone: us-east-1c
  - cidr: 10.151.24.0/24
    name: worker-zone-d
    type: Private
    zone: us-east-1d
  - cidr: 10.151.25.0/24
    name: worker-zone-e
    type: Private
    zone: us-east-1e
  - cidr: 10.151.26.0/24
    name: worker-zone-f
    type: Private
    zone: us-east-1f
  topology:
    dns:
      type: Public
    masters: private
    nodes: private

---

apiVersion: kops/v1alpha2
kind: InstanceGroup
metadata:
  labels:
    kops.k8s.io/cluster: prod-1.k8s.devops.bot
  name: master-us-east-1a
spec:
  cloudLabels:
    CostCenter: kubernetes-saas
    Owner: kubernetes
    Project: cloud
    Purpose: kubernetes-master
  # CoreOS: https://github.com/kubernetes/kops/blob/06b0111251ab87861e57dbf5f8d36f02e84af04d/docs/images.md#coreos
  image: 595879546273/CoreOS-stable-1745.7.0-hvm
  machineType: t2.medium
  maxSize: 1
  minSize: 1
  nodeLabels:
    kops.k8s.io/instancegroup: master-us-east-1a
    k8s.info/isSpot: "false"
    k8s.info/instanceType: t2.medium
    k8s.info/hasPublicIP: "false"
  role: Master
  subnets:
  - kube-master-1a

---

apiVersion: kops/v1alpha2
kind: InstanceGroup
metadata:
  labels:
    kops.k8s.io/cluster: prod-1.k8s.devops.bot
  name: master-us-east-1b
spec:
  cloudLabels:
    CostCenter: kubernetes-saas
    Owner: kubernetes
    Project: cloud
    Purpose: kubernetes-master
  # CoreOS: https://github.com/kubernetes/kops/blob/06b0111251ab87861e57dbf5f8d36f02e84af04d/docs/images.md#coreos
  image: 595879546273/CoreOS-stable-1745.7.0-hvm
  machineType: t2.medium
  maxSize: 1
  minSize: 1
  nodeLabels:
    kops.k8s.io/instancegroup: master-us-east-1b
    k8s.info/isSpot: "false"
    k8s.info/instanceType: t2.medium
    k8s.info/hasPublicIP: "false"
  role: Master
  subnets:
  - kube-master-1b

---

apiVersion: kops/v1alpha2
kind: InstanceGroup
metadata:
  labels:
    kops.k8s.io/cluster: prod-1.k8s.devops.bot
  name: master-us-east-1c
spec:
  cloudLabels:
    CostCenter: kubernetes-saas
    Owner: kubernetes
    Project: cloud
    Purpose: kubernetes-master
  # CoreOS: https://github.com/kubernetes/kops/blob/06b0111251ab87861e57dbf5f8d36f02e84af04d/docs/images.md#coreos
  image: 595879546273/CoreOS-stable-1745.7.0-hvm
  machineType: t2.medium
  maxSize: 1
  minSize: 1
  nodeLabels:
    kops.k8s.io/instancegroup: master-us-east-1c
    k8s.info/isSpot: "false"
    k8s.info/instanceType: t2.medium
    k8s.info/hasPublicIP: "false"
  role: Master
  subnets:
  - kube-master-1c

######################
## Spot instances
## type: m4.large
######################
---

apiVersion: kops/v1alpha2
kind: InstanceGroup
metadata:
  labels:
    kops.k8s.io/cluster: prod-1.k8s.devops.bot
  name: spot-zone-a-m4-large
spec:
  cloudLabels:
    CostCenter: kubernetes-saas
    Owner: kubernetes
    Project: cloud
    Purpose: kubernetes-spot-node
    # https://github.com/kubernetes/autoscaler/issues/511#issuecomment-354616866
    k8s.io/cluster-autoscaler/node-template/label/prod-1.k8s.devops.bot/role: scale-zero
  # CoreOS: https://github.com/kubernetes/kops/blob/06b0111251ab87861e57dbf5f8d36f02e84af04d/docs/images.md#coreos
  image: 595879546273/CoreOS-stable-1745.7.0-hvm
  machineType: m4.large
  maxPrice: "0.06"
  maxSize: 10
  minSize: 0
  nodeLabels:
    kops.k8s.io/instancegroup: spot-zone-a
    prod-1.k8s.devops.bot/role: scale-zero
    k8s.info/isSpot: "true"
    k8s.info/instanceType: m4.large
    k8s.info/hasPublicIP: "false"
  role: Node
  subnets:
  - worker-zone-a

---

apiVersion: kops/v1alpha2
kind: InstanceGroup
metadata:
  labels:
    kops.k8s.io/cluster: prod-1.k8s.devops.bot
  name: spot-zone-b-m4-large
spec:
  cloudLabels:
    CostCenter: kubernetes-saas
    Owner: kubernetes
    Project: cloud
    Purpose: kubernetes-spot-node
    # https://github.com/kubernetes/autoscaler/issues/511#issuecomment-354616866
    k8s.io/cluster-autoscaler/node-template/label/prod-1.k8s.devops.bot/role: scale-zero
  # CoreOS: https://github.com/kubernetes/kops/blob/06b0111251ab87861e57dbf5f8d36f02e84af04d/docs/images.md#coreos
  image: 595879546273/CoreOS-stable-1745.7.0-hvm
  machineType: m4.large
  maxPrice: "0.06"
  maxSize: 10
  minSize: 0
  nodeLabels:
    kops.k8s.io/instancegroup: spot-zone-b
    prod-1.k8s.devops.bot/role: scale-zero
    k8s.info/isSpot: "true"
    k8s.info/instanceType: m4.large
    k8s.info/hasPublicIP: "false"
  role: Node
  subnets:
  - worker-zone-b

---

apiVersion: kops/v1alpha2
kind: InstanceGroup
metadata:
  labels:
    kops.k8s.io/cluster: prod-1.k8s.devops.bot
  name: spot-zone-c-m4-large
spec:
  cloudLabels:
    CostCenter: kubernetes-saas
    Owner: kubernetes
    Project: cloud
    Purpose: kubernetes-spot-node
    # https://github.com/kubernetes/autoscaler/issues/511#issuecomment-354616866
    k8s.io/cluster-autoscaler/node-template/label/prod-1.k8s.devops.bot/role: scale-zero
  # CoreOS: https://github.com/kubernetes/kops/blob/06b0111251ab87861e57dbf5f8d36f02e84af04d/docs/images.md#coreos
  image: 595879546273/CoreOS-stable-1745.7.0-hvm
  machineType: m4.large
  maxPrice: "0.06"
  maxSize: 10
  minSize: 0
  nodeLabels:
    kops.k8s.io/instancegroup: spot-zone-c
    prod-1.k8s.devops.bot/role: scale-zero
    k8s.info/isSpot: "true"
    k8s.info/instanceType: m4.large
    k8s.info/hasPublicIP: "false"
  role: Node
  subnets:
  - worker-zone-c

---

apiVersion: kops/v1alpha2
kind: InstanceGroup
metadata:
  labels:
    kops.k8s.io/cluster: prod-1.k8s.devops.bot
  name: spot-zone-d-m4-large
spec:
  cloudLabels:
    CostCenter: kubernetes-saas
    Owner: kubernetes
    Project: cloud
    Purpose: kubernetes-spot-node
    # https://github.com/kubernetes/autoscaler/issues/511#issuecomment-354616866
    k8s.io/cluster-autoscaler/node-template/label/prod-1.k8s.devops.bot/role: scale-zero
  # CoreOS: https://github.com/kubernetes/kops/blob/06b0111251ab87861e57dbf5f8d36f02e84af04d/docs/images.md#coreos
  image: 595879546273/CoreOS-stable-1745.7.0-hvm
  machineType: m4.large
  maxPrice: "0.06"
  maxSize: 10
  minSize: 0
  nodeLabels:
    kops.k8s.io/instancegroup: spot-zone-d
    prod-1.k8s.devops.bot/role: scale-zero
    k8s.info/isSpot: "true"
    k8s.info/instanceType: m4.large
    k8s.info/hasPublicIP: "false"
  role: Node
  subnets:
  - worker-zone-d

---

apiVersion: kops/v1alpha2
kind: InstanceGroup
metadata:
  labels:
    kops.k8s.io/cluster: prod-1.k8s.devops.bot
  name: spot-zone-e-m4-large
spec:
  cloudLabels:
    CostCenter: kubernetes-saas
    Owner: kubernetes
    Project: cloud
    Purpose: kubernetes-spot-node
    # https://github.com/kubernetes/autoscaler/issues/511#issuecomment-354616866
    k8s.io/cluster-autoscaler/node-template/label/prod-1.k8s.devops.bot/role: scale-zero
  # CoreOS: https://github.com/kubernetes/kops/blob/06b0111251ab87861e57dbf5f8d36f02e84af04d/docs/images.md#coreos
  image: 595879546273/CoreOS-stable-1745.7.0-hvm
  machineType: m4.large
  maxPrice: "0.06"
  maxSize: 10
  minSize: 0
  nodeLabels:
    kops.k8s.io/instancegroup: spot-zone-e
    prod-1.k8s.devops.bot/role: scale-zero
    k8s.info/isSpot: "true"
    k8s.info/instanceType: m4.large
    k8s.info/hasPublicIP: "false"
  role: Node
  subnets:
  - worker-zone-e

---

apiVersion: kops/v1alpha2
kind: InstanceGroup
metadata:
  labels:
    kops.k8s.io/cluster: prod-1.k8s.devops.bot
  name: spot-zone-f-m4-large
spec:
  cloudLabels:
    CostCenter: kubernetes-saas
    Owner: kubernetes
    Project: cloud
    Purpose: kubernetes-spot-node
    # https://github.com/kubernetes/autoscaler/issues/511#issuecomment-354616866
    k8s.io/cluster-autoscaler/node-template/label/prod-1.k8s.devops.bot/role: scale-zero
  # CoreOS: https://github.com/kubernetes/kops/blob/06b0111251ab87861e57dbf5f8d36f02e84af04d/docs/images.md#coreos
  image: 595879546273/CoreOS-stable-1745.7.0-hvm
  machineType: m4.large
  maxPrice: "0.06"
  maxSize: 10
  minSize: 0
  nodeLabels:
    kops.k8s.io/instancegroup: spot-zone-f
    prod-1.k8s.devops.bot/role: scale-zero
    k8s.info/isSpot: "true"
    k8s.info/instanceType: m4.large
    k8s.info/hasPublicIP: "false"
  role: Node
  subnets:
  - worker-zone-f
```

Most of this is boiler plate configurations.  It will create 3 master nodes in
3 different AWS availability zones.  AWS NAT Gateways for all of the zones.  The
Kubernetes nodes are in all availability zones (us-east-1 zones a to f) and using
spot instances.  There are tags on the nodes for using the Cluster Autoscaler so
that it can scale an instance group down to zero if it is not needed.

### Setting up the DNS
Make sure that you have the DNS setup correctly in AWS Route53.

<link to the kops route53 setup>             <-----------------------------------------------

The easiest way is to name your cluster after the domain name you have setup.

### Creating a cluster
Set the cluster name:
```
export NAME=prod-us-east-1.devops.bot
```

Create the cluster
```
kops create -f ${NAME}.yaml
kops create secret --name ${NAME} sshpublickey admin -i ~/.ssh/id_rsa.pub
kops update cluster ${NAME} --yes
```

### Updating the cluster
We are always going to use the `--cluster` flag so that we know specifically which
cluster our command will apply to.

Apply changes and check to make sure it is doing what you intended it to update
```
kops --cluster ${NAME} replace -f ${NAME}.yaml
kops --cluster ${NAME} update cluster
```

Execute the changes:
```
kops --cluster ${NAME} rolling-update cluster --yes
```

Check on which nodes needs to be updated.  This will give you a list of nodes
that requires to be rolled or updated.
```
kops --name ${NAME} rolling-update cluster
```

Can target a specific node group one at a time.  For example, if you are updating
the masters and want to do one at a time.
```
kops --name ${NAME} rolling-update cluster --instance-group master-us-east-1a --yes
