---
layout: post
title: AWS IAM Authenticator Troubleshooting
categories: AWS IAM Authenticator Troubleshooting
keywords: AWS IAM Authenticator Troubleshooting okta
---

I was setting up the `aws-iam-authenticator` to work with a kops cluster the other day and ran into all 
kinds of issues.  Thought it would be nice to document out what I did to troubleshoot it.  

What I wanted to achieve:
* Get Kops working with the `aws-iam-authenticator` and authenticating via AWS roles
* Once that is working, I wanted to get it working with authenticating with [Okta](https://www.okta.com)

# Get Binaries
Before you begin, you will need the following:

## aws-okta
Maybe not this if you are not using Okta

https://github.com/segmentio/aws-okta/wiki/Installation

## aws-iam-authenticator

https://docs.aws.amazon.com/eks/latest/userguide/install-aws-iam-authenticator.html


## kubectl

If you don't have it already

https://kubernetes.io/docs/tasks/tools/install-kubectl/

# Setting up Kops to use authenticator

Setting up the `aws-iam-authenticator` in Kops is not too bad.  A few config changes and deploy it out.

## Kops
Kops doc: https://github.com/kubernetes/kops/blob/master/docs/authentication.md#aws-iam-authenticator

This doc outlines a few things:
* setting the authentication yaml in the cluster definition
* Creating the config map with the authenticators info once the cluster is up and running

There don't seem to be that many steps from the Kops configuration side.

## aws-iam-authenticator

The setup: https://github.com/kubernetes-sigs/aws-iam-authenticator#how-do-i-use-it

Check if the aws-iam-authenticator's config map is there:
```
kubectl -n kube-system get cm aws-iam-authenticator -o yaml 
```

If not, you will have to create one.  This is the tricky part where you are kinda on your own since this is 
very specific to you.

The role that is created will be used to create a config file for the `aws-iam-authenticator` to use. This file
is very important and as you will see below, it ties everything together. 

Here is a link to all of the options for this file:  https://github.com/kubernetes-sigs/aws-iam-authenticator#full-configuration-format

My aws-iam-authenticator's config map was:

```yaml
---
apiVersion: v1
kind: ConfigMap
metadata:
  namespace: kube-system
  name: aws-iam-authenticator
  labels:
    k8s-app: aws-iam-authenticator
data:
  config.yaml: |
    # a unique-per-cluster identifier to prevent replay attacks
    # (good choices are a random token or a domain name that will be unique to your cluster)
    clusterID: dev.cluster
    server:
      mapRoles:
      # statically map arn:aws:iam::123456789:role/KubernetesAdmin to a cluster admin
      - roleARN: arn:aws:iam::123456789:role/KubernetesAdmin
        username: kubernetes-admin
        groups:
        - system:masters
      - roleARN: arn:aws:iam::123456789:role/Engineering
        username: kubernetes-admin
        groups:
        - developer
      # mapUsers:
      # map user IAM user Alice in 000000000000 to user "alice" in "system:masters"
      # - userARN: arn:aws:iam::123456789:user/first.last-name-example
      #   username: first.last-name-example
      #   groups:
      #   - developer

```

The kube config should look something like this:

```yaml
apiVersion: v1
clusters:
- cluster:
    certificate-authority-data: LS0tLS1CRUdJTiBDRVJUSUZJQ0FURS0tLS0tCk1JSUMwekNDQWJ1Z0F3SUJBZ0lNRmRMU1M2MW45YXhWVzlBbE1BMEdDU3FHU0liM0RRRUJDd1VBTUJVeEV6QVIKQmdOVkJBTVRDbXQxWW1WeWJtVjBaWE13SGhjTk1Ua3hNREk1TVRrek5EQXlXaGNOTWpreE1ESTRNVGt6TkRBeQpXakFWTVJNd0VRWURWUVFERXdwcmRXSmxjbTVsZEdWek1JSUJJakFOQmdrcWhraUc5dzBCQVFFRkFBT0NBUThBCk1JSUJDZ0tDQVFFQXhrNS9DN3dNZTZocGFDWnIwYXoyNEovNExOZk5TOU1HV0R0==
    server: https://internal-api-dev-k8s-loc-lhnsou-12334568.us-east-1.elb.amazonaws.com
  name: dev.us-east-1.k8s.local
contexts:
- context:
    cluster: dev.us-east-1.k8s.local
    user: dev.us-east-1.k8s.local
  name: dev.us-east-1.k8s.local
current-context: dev.us-east-1.k8s.local
kind: Config
preferences: {}
users:
- name: dev.us-east-1.k8s.local
  user:
    exec:
      apiVersion: client.authentication.k8s.io/v1alpha1
      command: aws-iam-authenticator
      args:
        - "token"
        - "-i"
        - "dev.cluster"
```

The cluster name after `-i` should match the `clusterID` in the `aws-iam-authenticator`'s config map

### Create the IAM roles

Doc: https://github.com/kubernetes-sigs/aws-iam-authenticator#1-create-an-iam-role



## Now that Kops is up and running, how do I use this?

That is a good question.  I couldn't find a complete walk through and had to figure out what was next.

The kops docs above link to the aws-iam-authenticator project:


Setup your AWS creds either in the aws cli config file or export the keys to your environment:
```yaml
export AWS_ACCESS_KEY_ID=""
export AWS_SECRET_ACCESS_KEY=""
export AWS_DEFAULT_REGION=us-east-1
```

Test if you can auth and get a token back

```
aws-iam-authenticator token -i dev.cluster
{"kind":"ExecCredential","apiVersion":"client.authentication.k8s.io/v1alpha1","spec":{},"status":{"token":"k8s-aws-v1.aHR0cHM6Ly-xxx-very-long-string-here"}}
```

This is a good response.

# Troubleshooting the auth path on Kops 1.11

I was on Kops 1.11 initially and this was not working.  So I looked into the `aws-iam-authenticator` logs after an authentication attempt:

```bash
aws-iam-authenticator-8t2gb aws-iam-authenticator time="2019-10-31T15:26:08Z" level=info msg="listening on https://127.0.0.1:21362/authenticate"
aws-iam-authenticator-8t2gb aws-iam-authenticator time="2019-10-31T15:26:08Z" level=info msg="reconfigure your apiserver with `--authentication-token-webhook-config-file=/etc/kubernetes/heptio-authenticator-aws/kubeconfig.yaml` to enable (assuming default hostPath mounts)"
aws-iam-authenticator-r6mvh aws-iam-authenticator time="2019-10-31T16:42:49Z" level=info msg="http: TLS handshake error from 127.0.0.1:59466: remote error: tls: bad certificate" http=error
```

This does not look good.

kube-apiserver's logs on a failed attempt:
```bash
kube-apiserver-ip-10-4-0-25.ec2.internal kube-apiserver E1031 15:32:06.497256       1 webhook.go:90] Failed to make webhook authenticator request: Post https://127.0.0.1:21362/authenticate?timeout=30s: x509: certificate signed by unknown authority
kube-apiserver-ip-10-4-0-25.ec2.internal kube-apiserver E1031 15:32:06.497290       1 authentication.go:62] Unable to authenticate the request due to an error: [invalid bearer token, [invalid bearer token, Post https://127.0.0.1:21362/authenticate?timeout=30s: x509: certificate signed by unknown authority]]
```

looks like it is failing because it is going to the aws-iam-authenticator and it has a self signed cert so it didn't try


The `aws-iam-authenticator` was also complaining about some apiserver setting, so I also looked at that to confirm it was there:

I exec'ed into the pod:
```
kubectl -n kube-system exec -it kube-apiserver-ip-172-17-30-194.ec2.internal sh
```

```bash
ps aux | grep authentication-token-webhook-config-file
mkfifo /tmp/pipe; (tee -a /var/log/kube-apiserver.log < /tmp/pipe & ) ; exec /usr/local/bin/kube-apiserver --allow-privileged=true --anonymous-auth=false --apiserver-count=3 --authentication-token-webhook-config-file=/etc/kubernetes/authn.config --authorization-mode=RBAC --basic-auth-file=/srv/kubernetes/basic_auth.csv --bind-address=0.0.0.0 --client-ca-file=/srv/kubernetes/ca.crt --cloud-provider=aws --enable-admission-plugins=Initializers,NamespaceLifecycle,LimitRanger,ServiceAccount,PersistentVolumeLabel,DefaultStorageClass,DefaultTolerationSeconds,MutatingAdmissionWebhook,ValidatingAdmissionWebhook,NodeRestriction,ResourceQuota --etcd-cafile=/srv/kubernetes/ca.crt --etcd-certfile=/srv/kubernetes/etcd-client.pem --etcd-keyfile=/srv/kubernetes/etcd-client-key.pem --etcd-servers-overrides=/events#https://127.0.0.1:4002 --etcd-servers=https://127.0.0.1:4001 --insecure-bind-address=127.0.0.1 --insecure-port=8080 --kubelet-preferred-address-types=InternalIP,Hostname,ExternalIP --proxy-client-cert-file=/srv/kubernetes/apiserver-aggregator.cert --proxy-client-key-file=/srv/kubernetes/apiserver-aggregator.key --requestheader-allowed-names=aggregator --requestheader-client-ca-file=/srv/kubernetes/apiserver-aggregator-ca.cert --requestheader-extra-headers-prefix=X-Remote-Extra- --requestheader-group-headers=X-Remote-Group --requestheader-username-headers=X-Remote-User --secure-port=443 --service-cluster-ip-range=100.64.0.0/13 --storage-backend=etcd3 --tls-cert-file=/srv/kubernetes/server.cert --tls-private-key-file=/srv/kubernetes/server.key --token-auth-file=/srv/kubernetes/known_tokens.csv --v=2 > /tmp/pipe 2>&1
```

Looks like that setting is there.  Just wanted to check.  Nothing I really can do about this at the moment and I not sure if this is affecting anything.


## Looking at the kube config file for the auth

From above kubernetes should be using this file to auth:

```
--authentication-token-webhook-config-file=/etc/kubernetes/authn.config
```


```yaml
# cat /etc/kubernetes/authn.config
apiVersion: ""
clusters:
- cluster:
    certificate-authority-data: LS0tLS1CRUdJTiBDRVJUSUZJQ0FURS0tLS0tCk1JSUMwekNDQWJ1Z0F3SUJBZ0lNRlc0NWVPTmpKSGdFNC9zTE1BMEdDU3FHU0liM0RRRUJDd1VBTUJVeEV6QVIKQmdOVkJBTVRDbXQxWW1WeWJtVjBaWE13SGhjTk1UZ3hNakEyTURJd09ET==
    server: https://127.0.0.1:21362/authenticate
  name: aws-iam-authenticator
contexts:
- context:
    cluster: aws-iam-authenticator
    user: kube-apiserver
  name: webhook
current-context: webhook
kind: ""
users:
- name: kube-apiserver
  user: {}
```

Looks like it is using this file to auth since we see "https://127.0.0.1:21362/authenticate" in the
logs above for the location.


It has the "certificate-authority-data" so it should not complain about a self signed cert?


This is an older version.  Lets go to a later version of Kops


## Using Kops 1.13 with Kubernetes 1.13.x
Searching around, Kops did update the `aws-iam-authenticator` from 0.3 to 0.4 in later releases.  I think
it is time to upgrade since 0.3 is pretty old:

Kops updated the authenticator: https://github.com/kubernetes/kops/pull/6803


So i upgraded my Kops to 1.13.


# Kube api logs on a failed attempt

Running the command:
```bash
kubectl get nodes -v=7
I1031 13:22:28.226031    3780 helpers.go:199] server response object: [{
  "metadata": {},
  "status": "Failure",
  "message": "Unauthorized",
  "reason": "Unauthorized",
  "code": 401
}]
F1031 13:22:28.226045    3780 helpers.go:114] error: You must be logged in to the server (Unauthorized)
```

Gives me back an 401 unauth error (we'll be running this command a lot).

Looking at the kube-apiserver logs:

```bash
kube-apiserver-ip-10-10-31-69.ec2.internal kube-apiserver E1031 20:17:25.409048       1 webhook.go:106] Failed to make webhook authenticator request: unknown
kube-apiserver-ip-10-10-31-69.ec2.internal kube-apiserver E1031 20:17:25.409078       1 authentication.go:65] Unable to authenticate the request due to an error: [invalid bearer token, [invalid bearer token, unknown]]
```
Not sure if this is good or bad yet

Looking at the `aws-iam-authenticator` logs:

```bash
aws-iam-authenticator-lzjt6 aws-iam-authenticator time="2019-10-31T20:16:50Z" level=info msg="STS response" accountid=1234567890 arn="arn:aws:iam::1234567890:user/dev-garland" client="127.0.0.1:55534" method=POST path=/authenticate session= userid=AIDAJOZJZRVACX2SX5NPE
aws-iam-authenticator-lzjt6 aws-iam-authenticator time="2019-10-31T20:16:50Z" level=warning msg="access denied" arn="arn:aws:iam::1234567890:user/dev-garland" client="127.0.0.1:55534" error="ARN is not mapped: arn:aws:iam::1234567890:user/dev-garland" method=POST path=/authenticate
```

Failing but looking better..it did more than before

No more TLS handshaking errors

looks like a legit failed auth?

# Going to focus on the aws-iam-authenticator now

From the logs it looks like the `aws-iam-authenticator` is telling me that it tried to auth and got an access denied back from AWS.


Oops using the wrong keys.  The keys I have in that shell is for another account.

Ok so the negative test case works =)

Exporting the correct AWS keys and trying again

```bash
kubectl get nodes -v=7
I1031 13:22:28.226031    3780 helpers.go:199] server response object: [{
  "metadata": {},
  "status": "Failure",
  "message": "Unauthorized",
  "reason": "Unauthorized",
  "code": 401
}]
F1031 13:22:28.226045    3780 helpers.go:114] error: You must be logged in to the server (Unauthorized)
```

Still not working

`aws-iam-authenticator` logs:
```bash
aws-iam-authenticator-tpnj8 aws-iam-authenticator time="2019-10-31T20:22:27Z" level=info msg="STS response" accountid=123456789 arn="arn:aws:iam::123456789:user/garland.kan" client="127.0.0.1:35474" method=POST path=/authenticate session= userid=KEIEPOWIEKELEOQIEURIW
aws-iam-authenticator-tpnj8 aws-iam-authenticator time="2019-10-31T20:22:27Z" level=warning msg="access denied" arn="arn:aws:iam::123456789:user/garland.kan" client="127.0.0.1:35474" error="ARN is not mapped: arn:aws:iam::123456789:user/garland.kan" method=POST path=/authenticate
```

Also, the username is now correct and it detected `garland.kan`.  The `dev-garland` was for another AWS account.

It is not complaining about the AWS STS stuff anymore..so did it auth ok?

It does complain about the `ARN is not mapped:....`.  Maybe im not mapped correctly somewhere?

# Lets make some roles to check it out

I was playing around with the rbac role

```yaml
# For now we will give everyone cluster-admin perms
---
apiVersion: rbac.authorization.k8s.io/v1beta1
kind: ClusterRoleBinding
metadata:
  name: test1
roleRef:
  apiGroup: rbac.authorization.k8s.io
  kind: ClusterRole
  name: cluster-admin
subjects:
- kind: User
  name: garland.kan
  namespace: default

```

Trying to run `kubectl get nodes` it was still the same thing.

Then i thought was it the `aws-iam-authenticator`'s config map that is not mapping the user to the correct role?

Re-reading the doc on the config map helped me out:

https://github.com/kubernetes-sigs/aws-iam-authenticator#full-configuration-format

I was indeed not mapping the user correctly.

Adding this part to the `aws-iam-authenticator`'s configmap and applying it to the cluster:

```yaml
      # each mapUsers entry maps an IAM role to a static username and set of groups
      mapUsers:
      # map user IAM user Alice in 000000000000 to user "alice" in group "system:masters"
      - userARN: arn:aws:iam::123456789:user/garland.kan
        username: garland.kan
        groups:
        - system:masters
```


After this change:

```bash
kubectl get nodes -v=7                                
I1031 13:40:15.310585   15203 loader.go:375] Config loaded from file:  clusters/dev-expanse/kubeconfig/kubeconfig
I1031 13:40:15.317690   15203 round_trippers.go:420] GET https://internal-api-dev-k8s-loc-lhnsou-12334568.us-east-1.elb.amazonaws.com/api/v1/nodes?limit=500
I1031 13:40:15.317720   15203 round_trippers.go:427] Request Headers:
I1031 13:40:15.317737   15203 round_trippers.go:431]     Accept: application/json;as=Table;v=v1beta1;g=meta.k8s.io, application/json
I1031 13:40:15.317747   15203 round_trippers.go:431]     User-Agent: kubectl/v1.16.2 (linux/amd64) kubernetes/c97fe50
I1031 13:40:16.081729   15203 round_trippers.go:446] Response Status: 200 OK in 763 milliseconds
NAME                           STATUS   ROLES    AGE   VERSION
ip-10-10-30-126.ec2.internal   Ready    master   58m   v1.13.10
ip-10-10-31-69.ec2.internal    Ready    master   59m   v1.13.10
ip-10-10-32-150.ec2.internal   Ready    master   51m   v1.13.10
```

I am able to get the nodes!!

Cool...this seems to be working now.

`aws-iam-authenticator` logs are now showing:
```bash
aws-iam-authenticator-h58c8 aws-iam-authenticator time="2019-10-31T20:40:16Z" level=info msg="STS response" accountid=123456789 arn="arn:aws:iam::123456789:user/garland.kan" client="127.0.0.1:34008" method=POST path=/authenticate session= userid=KEIEPOWIEKELEOQIEURIW
aws-iam-authenticator-h58c8 aws-iam-authenticator time="2019-10-31T20:40:16Z" level=info msg="access granted" arn="arn:aws:iam::123456789:user/garland.kan" client="127.0.0.1:34008" groups="[system:masters]" method=POST path=/authenticate uid="aws-iam-authenticator:123456789:KEIEPOWIEKELEOQIEURIW" username=garland.kan

```

Logs shows "access granted"


Shows the username as "garland.kan"
groups: system:masters

This is starting to look good and seeing what I am expecting.


# However, I want to map groups of users and not individual users

While this works, I can't possibly add each new user with their own mappings.

There has to be a way to map a group of users to Kubernetes

Back to the `aws-iam-authenticator` docs to see what I can do in the configmap


Checking what role I am or my identity

```bash
aws sts get-caller-identity                             
{
    "Account": "123456789", 
    "UserId": "KEIEPOWIEKELEOQIEURIW", 
    "Arn": "arn:aws:iam::123456789:user/garland.kan"
}
```

The kube config should look something like this:

```yaml
apiVersion: v1
clusters:
- cluster:
    certificate-authority-data: LS0tLS1CRUdJTiBDRVJUSUZJQ0FURS0tLS0tCk1JSUMwekNDQWJ1Z0F3SUJBZ0lNRmRMU1M2MW45YXhWVzlBbE1BMEdDU3FHU0liM0RRRUJDd1VBTUJVeEV6QVIKQmdOVkJBTVRDbXQxWW1WeWJtVjBaWE13SGhjTk1Ua3hNREk1TVRrek5EQXlXaGNOTWpreE1ESTRNVGt6TkRBeQpXakFWTVJNd0VRWURWUVFERXdwcmRXSmxjbTVsZEdWek1JSUJJakFOQmdrcWhraUc5dzBCQVFFRkFBT0NBUThBCk1JSUJDZ0tDQVFFQXhrNS9DN3dNZTZocGFDWnIwYXoyNEovNExOZk5TOU1HV0R0d1kw==
    server: https://internal-api-dev-k8s-loc-lhnsou-12334568.us-east-1.elb.amazonaws.com
  name: dev.us-east-1.k8s.local
contexts:
- context:
    cluster: dev.us-east-1.k8s.local
    user: dev.us-east-1.k8s.local
  name: dev.us-east-1.k8s.local
current-context: dev.us-east-1.k8s.local
kind: Config
preferences: {}
users:
- name: dev.us-east-1.k8s.local
  user:
    exec:
      apiVersion: client.authentication.k8s.io/v1alpha1
      command: aws-iam-authenticator
      args:
        - "token"
        - "-i"
        - "dev.cluster"
```


Running `aws-iam-authenticator` manually should get my token and more info?

```
aws-iam-authenticator token -i dev.cluster
{"kind":"ExecCredential","apiVersion":"client.authentication.k8s.io/v1alpha1","spec":{},"status":{"token":"k8s-aws-v1.aHR0cHM6Ly9zdHMuYW1hem9uYXdzLmNvbS8_QWN0aW9uPUdldENhbGxlcklkZW50aXR5JlZlcnNpb249Mjxxxxxx"}}
```

hmm...not really


What I really want to do is assume a role and not be me (garland.kan) so that role name gets passed to the `aws-iam-authenticator`.

I can do this and add in the role flag:

```bash
aws-iam-authenticator token -i dev.cluster -r arn:aws:iam::123456789:role/KubernetesAdmin
{"kind":"ExecCredential","apiVersion":"client.authentication.k8s.io/v1alpha1","spec":{},"status":{"token":"k8s-aws-v1.aHR0cHM6Ly9zdHMuYW1hem9uYXdzLmNvbS8_QWN0aW9uPUdldENhbGxlcklkZW50aXR5JlZxxxxxxx"}}
```

That looks cool and authenticated

Adding the -r flag into my kube config:

```yaml
apiVersion: v1
clusters:
- cluster:
    certificate-authority-data: LS0tLS1CRUdJTiBDRVJUSUZJQ0FURS0tLS0tCk1JSUMwekNDQWJ1Z0F3SUJBZ0lNRmRMU1M2MW45YXhWVzlBbE1BMEdDU3FHU0liM0RRRUJDd1VBTUJVeEV6QVIKQmdOVkJBTVRDbXQxWW1WeWJtVjBaWE13SGhjTk1Ua3hNREk1TVRrek5EQXlXaGNOTWpreE1ESTRNVGt6TkRBeQpXakFWTVJNd0VRWURWUVFERXdwcmRXSmxjbTVsZEdWek1JSUJJakFOQmdrcWhraUc5dzBCQVFFRkFBT0NBUThBCk1JSUJDZ0tDQVFFQXhrNS9DN3dNZTZocGFDWnIwYXoyNEovNExOZk5TOU1HV0R0d1kwK==
    server: https://internal-api-dev-k8s-loc-lhnsou-12334568.us-east-1.elb.amazonaws.com
  name: dev.us-east-1.k8s.local
contexts:
- context:
    cluster: dev.us-east-1.k8s.local
    user: dev.us-east-1.k8s.local
  name: dev.us-east-1.k8s.local
current-context: dev.us-east-1.k8s.local
kind: Config
preferences: {}
users:
- name: dev.us-east-1.k8s.local
  user:
    exec:
      apiVersion: client.authentication.k8s.io/v1alpha1
      command: aws-iam-authenticator
      args:
        - "token"
        - "-i"
        - "dev.cluster"
        - "-r"
        - "arn:aws:iam::123456789:role/KubernetesAdmin"
```

Testing this out:

```bash
kubectl get nodes -v=7                                
I1031 14:09:51.084438     576 loader.go:375] Config loaded from file:  clusters/dev-expanse/kubeconfig/kubeconfig
I1031 14:09:51.090450     576 round_trippers.go:420] GET https://internal-api-dev-k8s-loc-lhnsou-12334568.us-east-1.elb.amazonaws.com/api/v1/nodes?limit=500
I1031 14:09:51.090462     576 round_trippers.go:427] Request Headers:
I1031 14:09:51.090470     576 round_trippers.go:431]     User-Agent: kubectl/v1.16.2 (linux/amd64) kubernetes/c97fe50
I1031 14:09:51.090477     576 round_trippers.go:431]     Accept: application/json;as=Table;v=v1beta1;g=meta.k8s.io, application/json
I1031 14:09:51.834751     576 round_trippers.go:446] Response Status: 200 OK in 744 milliseconds
NAME                           STATUS   ROLES    AGE   VERSION
ip-10-10-30-126.ec2.internal   Ready    master   87m   v1.13.10
ip-10-10-31-69.ec2.internal    Ready    master   88m   v1.13.10
ip-10-10-32-150.ec2.internal   Ready    master   80m   v1.13.10
```

That worked!

However, now im curious.  I was half expecting it to fail because does that role mapping exist?

Ah it does.  it was already in my `aws-iam-authenticator`'s configmap and my IAM user has the `role/KubernetesAdmin`

```yaml
      mapRoles:
      # statically map arn:aws:iam::123456789:role/KubernetesAdmin to a cluster admin
      - roleARN: arn:aws:iam::123456789:role/KubernetesAdmin
        username: kubernetes-admin
        groups:
        - system:masters
```

Lets look at the `aws-iam-authenticator` logs to check what it did:

```bash
aws-iam-authenticator-2wjhm aws-iam-authenticator time="2019-10-31T21:11:12Z" level=info msg="STS response" accountid=123456789 arn="arn:aws:sts::123456789:assumed-role/KubernetesAdmin/1572556271727784364" client="127.0.0.1:41228" method=POST path=/authenticate session=1572556271727784364 userid=AROAIMK75QQC2FPGWWGWW
aws-iam-authenticator-2wjhm aws-iam-authenticator time="2019-10-31T21:11:12Z" level=info msg="access granted" arn="arn:aws:iam::123456789:role/KubernetesAdmin" client="127.0.0.1:41228" groups="[system:masters]" method=POST path=/authenticate uid="aws-iam-authenticator:123456789:AROAIMK75QQC2FPGWWGWW" username=kubernetes-admin
```

That is what I wanted.

From the logs, the user is an STS assumed role: assumed-role/KubernetesAdmin/1572556271727784364

And it go mapped to:  username=kubernetes-admin

Our configmap maps that username to: system:masters


# The fun continues with Okta

You can skip the rest if you are not using Okta.

There are always more requirements.  We are gaining access to AWS via a federated SSO auth to Okta.

This means that to get access to the AWS Console, a person would log into Okta and then click on one of the apps which is AWS and that takes you to the AWS console.

Which has roles you can assume on AWS.  Selecting one of those will log you into AWS console with the permission of that role.

There are two projects that I know of that helps with this:

* Okta developers: https://github.com/oktadeveloper/okta-aws-cli-assume-role
* segmento.io: https://github.com/segmentio/aws-okta

Lets try the more official okta-aws-cli-assume-role first

* Install it per their docs

Export envparams it needs:

```
export OKTA_ORG=example.okta.com
export OKTA_AWS_APP_URL=https://acmecorp.oktapreview.com/home/amazon_aws/849fdurjru33jjdie820d/123
```

* OKTA_ORG - is the domain name you log into
* OKTA_AWS_APP_URL - is from the AWS app in Okta, right clicking you can get the URL


The local install didnt work.  It could not find the binary when executing: "okta-aws test sts get-caller-identity"

I dont' have java installed locally...doh


Trying the docker route for now:
```bash
docker run -v ~/.okta/config.properties:/root/.okta/config.properties -it tomsmithokta/okta-awscli-java
```
	-same here, doesnt work and cant find the binary okta-aws

ok...nope i cant get it working.


# Trying out aws-okta

Downloaded the go binary from their release page:

The setup:  https://github.com/segmentio/aws-okta#adding-okta-credentials

```bash
root@fa955d3d1c3d:/# aws-okta add
Okta organization: example         		<---just the first part of the hostname when you log into Okta in your browser

Okta region ([us], emea, preview): 

Okta domain [example.okta.com]: 

Okta username: garland.kan

Okta password: 

Enter passphrase to unlock /root/.aws-okta/: 
INFO[0032] Added credentials for user garland.kan 
```

Setup your aws config

```bash
g44@g44:~$ cat ~/.aws/config 
[default]
region = us-east-1

[okta]
aws_saml_url = home/amazon_aws/dkjdkeudjejekdh/123
role_arn = arn:aws:iam::123456789:role/Engineering 
```

Lets try to run the command and auth:

```bash
 ~/Downloads/aws-okta-v0.26.3-linux-amd64 --debug exec okta -- kubectl get nodes
DEBU[0000] Parsing config file /home/g44/.aws/config    
DEBU[0000] Using KrItemPerSessionStore                  
DEBU[0000] cache get `okta session (39343134333962323662)`: miss (unmarshal error): unexpected end of JSON input 
DEBU[0000] Using aws_saml_url from profile okta: home/amazon_aws/dkjdkeudjejekdh/123 
DEBU[0000] Using okta provider (okta-creds)             
DEBU[0000] domain: example.okta.com                      
DEBU[0000] Failed to reuse session token, starting flow from start 
DEBU[0000] Step: 1                                      
DEBU[0001] Step: 2                                      
DEBU[0001] Step: 3                                      
DEBU[0002] Got SAML role attribute: arn:aws:iam::123456789:saml-provider/Okta,arn:aws:iam::123456789:role/Administrator 
DEBU[0002] Got SAML role attribute: arn:aws:iam::123456789:saml-provider/Okta,arn:aws:iam::123456789:role/Engineering 
DEBU[0002] Got SAML role attribute: arn:aws:iam::075917778168:saml-provider/Okta,arn:aws:iam::075917778168:role/Engineering 
DEBU[0002] Step 4: Assume Role with SAML                
DEBU[0002] Writing session for garland.kan to keyring   
DEBU[0002] Using session J6FJ, expires in 59m59.849744475s 
could not get token: AccessDenied: User: arn:aws:sts::123456789:assumed-role/Engineering/garland.kan@example.com is not authorized to perform: sts:AssumeRole on resource: arn:aws:iam::123456789:role/KubernetesAdmin
        status code: 403, request id: f0f999e2-fc55-11e9-92f8-eb7cf9d59bb0
could not get token: AccessDenied: User: arn:aws:sts::123456789:assumed-role/Engineering/garland.kan@example.com is not authorized to perform: sts:AssumeRole on resource: arn:aws:iam::123456789:role/KubernetesAdmin
        status code: 403, request id: f144d3b5-fc55-11e9-8d0c-61be6e829cb3
could not get token: AccessDenied: User: arn:aws:sts::123456789:assumed-role/Engineering/garland.kan@example.com is not authorized to perform: sts:AssumeRole on resource: arn:aws:iam::123456789:role/KubernetesAdmin
        status code: 403, request id: f18cff83-fc55-11e9-abca-8949210d11b0
could not get token: AccessDenied: User: arn:aws:sts::123456789:assumed-role/Engineering/garland.kan@example.com is not authorized to perform: sts:AssumeRole on resource: arn:aws:iam::123456789:role/KubernetesAdmin
        status code: 403, request id: f1d7761e-fc55-11e9-a26c-cb85880ac402
could not get token: AccessDenied: User: arn:aws:sts::123456789:assumed-role/Engineering/garland.kan@example.com is not authorized to perform: sts:AssumeRole on resource: arn:aws:iam::123456789:role/KubernetesAdmin
        status code: 403, request id: f2219ee9-fc55-11e9-8472-5b52b951cb88
Unable to connect to the server: getting credentials: exec: exit status 1
exit status 1
```

It is doing something but failing auth


Testing this to make sure I can authenticate through Okta without `kubect` to
make it simplier by just running `aws s3 ls` commmand.


This works:

```bash
aws-okta --debug exec okta -- aws s3 ls                 
DEBU[0000] Parsing config file /home/g44/.aws/config    
DEBU[0000] Using KrItemPerSessionStore                  
DEBU[0000] cache get `okta session (39343134333962323662)`: hit 
DEBU[0000] Using session J6FJ, expires in 57m48.829353491s 
2016-11-30 17:51:51 bucket-1
2017-07-10 08:11:05 bucket-2
2017-04-24 15:29:20 bucket-3
...
...
```

I think i need to change my kubeconfig?
* per this dock?  https://github.com/segmentio/aws-okta#exec-for-eks-and-kubernetes


```yaml
cat $KUBECONFIG                                       
apiVersion: v1
clusters:
- cluster:
    certificate-authority-data: LS0tLS1CRUdJTiBDRVJUSUZJQ0FURS0tLS0tCk1JSUMwekNDQWJ1Z0F3SUJBZ0lNRmRMU1M2MW45YXhWVzlBbE1BMEdDU3FHU0liM0RRRUJDd1VBTUJVeEV6QVIKQmdOVkJBTVRDbXQxWW1WeWJtVjBaWE13SGhjTk1Ua3hNREk1TVRrek5EQXlXaGNOTWpreE1ESTRNVGt6T==
    server: https://internal-api-dev-k8s-loc-lhnsou-12334568.us-east-1.elb.amazonaws.com
  name: dev.us-east-1.k8s.local
contexts:
- context:
    cluster: dev.us-east-1.k8s.local
    user: dev.us-east-1.k8s.local
  name: dev.us-east-1.k8s.local
current-context: dev.us-east-1.k8s.local
kind: Config
preferences: {}
users:
- name: dev.us-east-1.k8s.local
  user:
    exec:
      apiVersion: client.authentication.k8s.io/v1alpha1
      command: aws-iam-authenticator
      args:
        - "token"
        - "-i"
        - "dev.cluster"
```

Something more is happing now

```bash
aws-okta --debug exec okta -- kubectl get nodes -v=7    
DEBU[0000] Parsing config file /home/g44/.aws/config    
DEBU[0000] Using KrItemPerSessionStore                  
DEBU[0000] cache get `okta session (39343134333962323662)`: hit 
DEBU[0000] Using session J6FJ, expires in 49m23.048135084s 
I1031 20:26:42.408794   16774 loader.go:375] Config loaded from file:  clusters/dev-expanse/kubeconfig/kubeconfig
I1031 20:26:42.413850   16774 round_trippers.go:420] GET https://internal-api-dev-k8s-loc-lhnsou-12334568.us-east-1.elb.amazonaws.com/api/v1/nodes?limit=500
I1031 20:26:42.413899   16774 round_trippers.go:427] Request Headers:
I1031 20:26:42.413911   16774 round_trippers.go:431]     Accept: application/json;as=Table;v=v1beta1;g=meta.k8s.io, application/json
I1031 20:26:42.413923   16774 round_trippers.go:431]     User-Agent: kubectl/v1.16.2 (linux/amd64) kubernetes/c97fe50
I1031 20:26:43.753685   16774 round_trippers.go:446] Response Status: 403 Forbidden in 1339 milliseconds
I1031 20:26:43.754318   16774 helpers.go:199] server response object: [{
  "kind": "Status",
  "apiVersion": "v1",
  "metadata": {},
  "status": "Failure",
  "message": "nodes is forbidden: User \"kubernetes-admin\" cannot list resource \"nodes\" in API group \"\" at the cluster scope",
  "reason": "Forbidden",
  "details": {
    "kind": "nodes"
  },
  "code": 403
}]
F1031 20:26:43.754403   16774 helpers.go:114] Error from server (Forbidden): nodes is forbidden: User "kubernetes-admin" cannot list resource "nodes" in API group "" at the cluster scope
exit status 255
```

`aws-iam-authenticator` logs:

```bash
aws-iam-authenticator-2wjhm aws-iam-authenticator time="2019-11-01T03:27:47Z" level=info msg="STS response" accountid=123456789 arn="arn:aws:sts::123456789:assumed-role/Engineering/garland.kan@example.com" client="127.0.0.1:41228" method=POST path=/authenticate session=garland.kan@example.com userid=AROAJDSGQ4VHVGA5MQ53Q
aws-iam-authenticator-2wjhm aws-iam-authenticator time="2019-11-01T03:27:47Z" level=info msg="access granted" arn="arn:aws:iam::123456789:role/Engineering" client="127.0.0.1:41228" groups="[developer]" method=POST path=/authenticate uid="aws-iam-authenticator:123456789:AROAJDSGQ4VHVGA5MQ53Q" username=kubernetes-admin
```

Looks ok?

Maybe we don't have the group "developer" mapped to an RBAC role?


Creating:

```yaml
# For now we will give everyone cluster-admin perms
---
apiVersion: rbac.authorization.k8s.io/v1beta1
kind: ClusterRoleBinding
metadata:
  name: test2
roleRef:
  apiGroup: rbac.authorization.k8s.io
  kind: ClusterRole
  name: cluster-admin
subjects:
- kind: Group
  name: developer
  namespace: default
```

Running:

```bash
aws-okta --debug exec okta -- kubectl get nodes -v=7    
DEBU[0000] Parsing config file /home/g44/.aws/config    
DEBU[0000] Using KrItemPerSessionStore                  
DEBU[0000] cache get `okta session (39343134333962323662)`: hit 
DEBU[0000] Using session J6FJ, expires in 43m37.474677853s 
I1031 20:32:28.173550   21362 loader.go:375] Config loaded from file:  clusters/dev-expanse/kubeconfig/kubeconfig
I1031 20:32:28.178770   21362 round_trippers.go:420] GET https://internal-api-dev-k8s-loc-lhnsou-12334568.us-east-1.elb.amazonaws.com/api/v1/nodes?limit=500
I1031 20:32:28.178798   21362 round_trippers.go:427] Request Headers:
I1031 20:32:28.178806   21362 round_trippers.go:431]     User-Agent: kubectl/v1.16.2 (linux/amd64) kubernetes/c97fe50
I1031 20:32:28.178813   21362 round_trippers.go:431]     Accept: application/json;as=Table;v=v1beta1;g=meta.k8s.io, application/json
I1031 20:32:28.844742   21362 round_trippers.go:446] Response Status: 200 OK in 665 milliseconds
NAME                           STATUS   ROLES    AGE     VERSION
ip-10-10-30-126.ec2.internal   Ready    master   7h50m   v1.13.10
ip-10-10-31-69.ec2.internal    Ready    master   7h51m   v1.13.10
ip-10-10-32-150.ec2.internal   Ready    master   7h43m   v1.13.10
```

That is working!!

`aws-iam-authenticator` logs still says the same thing:

```bash
aws-iam-authenticator-h58c8 aws-iam-authenticator time="2019-11-01T03:32:28Z" level=info msg="STS response" accountid=123456789 arn="arn:aws:sts::123456789:assumed-role/Engineering/garland.kan@example.com" client="127.0.0.1:34008" method=POST path=/authenticate session=garland.kan@example.com userid=AROAJDSGQ4VHVGA5MQ53Q
aws-iam-authenticator-h58c8 aws-iam-authenticator time="2019-11-01T03:32:28Z" level=info msg="access granted" arn="arn:aws:iam::123456789:role/Engineering" client="127.0.0.1:34008" groups="[developer]" method=POST path=/authenticate uid="aws-iam-authenticator:123456789:AROAJDSGQ4VHVGA5MQ53Q" username=kubernetes-admin
```

No new logs in the kube-apiserver when i make this query.  Thought there would have been something but I guess not?


Now, im trying to change the role im in to:

```yaml
cat ~/.aws/config                                       
[default]
region = us-east-1

[okta]
aws_saml_url = home/amazon_aws/dkjdkeudjejekdh/123
#role_arn = arn:aws:iam::123456789:role/Engineering 
role_arn = arn:aws:iam::123456789:role/KubernetesAdmin
```

Got back:

```bash
 aws-okta --debug exec okta -- kubectl get nodes         
DEBU[0000] Parsing config file /home/g44/.aws/config    
DEBU[0000] Using KrItemPerSessionStore                  
DEBU[0000] cache get `okta session (61323166316139366333)`: miss (unmarshal error): unexpected end of JSON input 
DEBU[0000] Using aws_saml_url from profile okta: home/amazon_aws/dkjdkeudjejekdh/123 
DEBU[0000] Using okta provider (okta-creds)             
DEBU[0000] domain: example.okta.com                      
DEBU[0000] Got SAML role attribute: arn:aws:iam::123456789:saml-provider/Okta,arn:aws:iam::123456789:role/Administrator 
DEBU[0000] Got SAML role attribute: arn:aws:iam::123456789:saml-provider/Okta,arn:aws:iam::123456789:role/Engineering 
DEBU[0000] Got SAML role attribute: arn:aws:iam::075917778168:saml-provider/Okta,arn:aws:iam::075917778168:role/Engineering 
getting creds via SAML: ARN isn't valid
```


ok lets try with:  arn:aws:iam::123456789:role/Administrator

It didnt barf on the arn isnt valid:

```bash
aws-okta --debug exec okta -- kubectl get nodes         
DEBU[0000] Parsing config file /home/g44/.aws/config    
DEBU[0000] Using KrItemPerSessionStore                  
DEBU[0000] cache get `okta session (38646339363030333337)`: miss (unmarshal error): unexpected end of JSON input 
DEBU[0000] Using aws_saml_url from profile okta: home/amazon_aws/dkjdkeudjejekdh/123 
DEBU[0000] Using okta provider (okta-creds)             
DEBU[0000] domain: example.okta.com                      
DEBU[0000] Got SAML role attribute: arn:aws:iam::123456789:saml-provider/Okta,arn:aws:iam::123456789:role/Administrator 
DEBU[0000] Got SAML role attribute: arn:aws:iam::123456789:saml-provider/Okta,arn:aws:iam::123456789:role/Engineering 
DEBU[0000] Got SAML role attribute: arn:aws:iam::075917778168:saml-provider/Okta,arn:aws:iam::075917778168:role/Engineering 
DEBU[0000] Step 4: Assume Role with SAML                
DEBU[0001] Writing session for garland.kan to keyring   
DEBU[0001] Using session NRHU, expires in 59m59.768054413s 
error: You must be logged in to the server (Unauthorized)
exit status 1
```

```
aws-iam-authenticator-kjt9t aws-iam-authenticator time="2019-11-01T03:39:31Z" level=info msg="STS response" accountid=123456789 arn="arn:aws:sts::123456789:assumed-role/Administrator/garland.kan@example.com" client="127.0.0.1:56834" method=POST path=/authenticate session=garland.kan@example.com userid=AROAJ53VWQVWK7WKOBJB6
aws-iam-authenticator-kjt9t aws-iam-authenticator time="2019-11-01T03:39:31Z" level=warning msg="access denied" arn="arn:aws:iam::123456789:role/Administrator" client="127.0.0.1:56834" error="ARN is not mapped: arn:aws:iam::123456789:role/administrator" method=POST path=/authenticate
```

This ARN is not mapped in the `aws-iam-authenticator`'s configmap so I was denied.

I think that is cool, I know why it denied me and this is expected since it is not mapped.


# Testing without any AWS keys exported to the local shell

I had AWS keys exported to my local shell for the test without using Okta.  I just want to make sure if I remove this that 
everthing still works with Okta and it is indeed going through Okta for authentication.


Came back to this task the next day.  Trying the calls I had previously working:

```bash
aws-okta --debug exec okta -- kubectl get nodes                         
DEBU[0000] Parsing config file /home/g44/.aws/config    
DEBU[0000] Using KrItemPerSessionStore                  
DEBU[0000] cache get `okta session (38646339363030333337)`: expired 
DEBU[0000] Using aws_saml_url from profile okta: home/amazon_aws/dkjdkeudjejekdh/123 
DEBU[0000] Using okta provider (okta-creds)             
DEBU[0000] domain: example.okta.com                      
DEBU[0001] Got SAML role attribute: arn:aws:iam::123456789:saml-provider/Okta,arn:aws:iam::123456789:role/Administrator 
DEBU[0001] Got SAML role attribute: arn:aws:iam::123456789:saml-provider/Okta,arn:aws:iam::123456789:role/Engineering 
DEBU[0001] Got SAML role attribute: arn:aws:iam::075917778168:saml-provider/Okta,arn:aws:iam::075917778168:role/Engineering 
DEBU[0001] Step 4: Assume Role with SAML                
DEBU[0001] Writing session for garland.kan to keyring   
DEBU[0001] Using session ND55, expires in 59m59.070412394s 
error: You must be logged in to the server (Unauthorized)
exit status 1
```

Now it doesnt work.

This works though.  which should mean I am authenticating through Okta

```bash
aws-okta --debug exec okta -- aws s3 ls                                 
DEBU[0000] Parsing config file /home/g44/.aws/config    
DEBU[0000] Using KrItemPerSessionStore                  
DEBU[0000] cache get `okta session (38646339363030333337)`: hit 
DEBU[0000] Using session ND55, expires in 58m4.130782637s 
2016-11-30 17:51:51 bucket-1
2017-07-10 08:11:05 bucket-2
2017-04-24 15:29:20 bucket-3
```

Something is wrong on the kube side?

Ah...we left off switching the `~/.aws/config` role to the Administor one which wasn't mapped =)

Changing that back to the Engineering role in the `~/.aws/config`.

```bash
aws-okta --debug exec okta -- kubectl get nodes                         
DEBU[0000] Parsing config file /home/g44/.aws/config    
DEBU[0000] Using KrItemPerSessionStore                  
DEBU[0000] cache get `okta session (39343134333962323662)`: expired 
DEBU[0000] Using aws_saml_url from profile okta: home/amazon_aws/dkjdkeudjejekdh/123 
DEBU[0000] Using okta provider (okta-creds)             
DEBU[0000] domain: example.okta.com                      
DEBU[0000] Got SAML role attribute: arn:aws:iam::123456789:saml-provider/Okta,arn:aws:iam::123456789:role/Administrator 
DEBU[0000] Got SAML role attribute: arn:aws:iam::123456789:saml-provider/Okta,arn:aws:iam::123456789:role/Engineering 
DEBU[0000] Got SAML role attribute: arn:aws:iam::075917778168:saml-provider/Okta,arn:aws:iam::075917778168:role/Engineering 
DEBU[0000] Step 4: Assume Role with SAML                
DEBU[0001] Writing session for garland.kan to keyring   
DEBU[0001] Using session AZXB, expires in 59m59.822831008s 
NAME                           STATUS   ROLES    AGE   VERSION
ip-10-10-30-126.ec2.internal   Ready    master   20h   v1.13.10
ip-10-10-31-69.ec2.internal    Ready    master   20h   v1.13.10
ip-10-10-32-150.ec2.internal   Ready    master   20h   v1.13.10
```

Working as expected.  Phew...

Checking to see what AWS keys I have in the shell:

```bash
env | grep -i aws                                                       
PWD=/home/g44/Documents/managedkube/kubernetes-ops/clusters/aws/kops
AWS_ACCESS_KEY_ID=xxxxxxx
AWS_SECRET_ACCESS_KEY=xxxxxxxxxx
AWS_DEFAULT_REGION=us-east-1
OKTA_AWS_APP_URL=https://example.okta.com/home/amazon_aws/xxxxxx/123
```

Looks like I do have AWS keys in the shell.  Lets unset those:

```bash
unset AWS_ACCESS_KEY_ID                                                 
unset AWS_SECRET_ACCESS_KEY                                             
env | grep -i aws                                                       
AWS_DEFAULT_REGION=us-east-1
OKTA_AWS_APP_URL=https://example.okta.com/home/amazon_aws/xxxxxx/123
```

Lets try the same call to auth and get nodes again:

```bash
aws-okta --debug exec okta -- kubectl get nodes                         
DEBU[0000] Parsing config file /home/g44/.aws/config    
DEBU[0000] Using KrItemPerSessionStore                  
DEBU[0000] cache get `okta session (39343134333962323662)`: hit 
DEBU[0000] Using session AZXB, expires in 56m40.296764841s 
NAME                           STATUS   ROLES    AGE   VERSION
ip-10-10-30-126.ec2.internal   Ready    master   20h   v1.13.10
ip-10-10-31-69.ec2.internal    Ready    master   20h   v1.13.10
ip-10-10-32-150.ec2.internal   Ready    master   20h   v1.13.10
```

Still working...interesting that there is less logs and it didnt find the other roles.  

Logs from the aws-iam-authenticator:

```bash
aws-iam-authenticator-2wjhm aws-iam-authenticator time="2019-11-01T16:14:07Z" level=info msg="STS response" accountid=123456789 arn="arn:aws:sts::123456789:assumed-role/Engineering/garland.kan@example.com" client="127.0.0.1:41228" method=POST path=/authenticate session=garland.kan@example.com userid=AROAJDSGQ4VHVGA5MQ53Q
aws-iam-authenticator-2wjhm aws-iam-authenticator time="2019-11-01T16:14:07Z" level=info msg="access granted" arn="arn:aws:iam::123456789:role/Engineering" client="127.0.0.1:41228" groups="[developer]" method=POST path=/authenticate uid="aws-iam-authenticator:123456789:AROAJDSGQ4VHVGA5MQ53Q" username=kubernetes-admin
```

Looking good.  This is what I was expecting to see.  The Engineering group gets sent to the kube auth and it mapped me to the `developer` group.



