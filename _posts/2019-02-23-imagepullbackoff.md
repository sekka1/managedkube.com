---
layout: post
title: Kubernetes Troubleshooting Walkthrough - imagepullbackoff
categories: kubernetes k8sbot troubleshooting imagepullbackoff
keywords: kubernetes k8sbot troubleshooting imagepullbackoff
---
{%- include twitter-button-blank.html -%}

* TOC
{:toc}

# Introduction: troubleshooting the Kubernetes error, imagepullbackoff

I am writing a series of blog posts about troubleshooting Kubernetes. One of the reasons why Kubernetes is so complex is because troubleshooting what went wrong requires many levels of information gathering. It's like trying to find the other end of a string in a tangled ball of strings. In this post, I am going to walk you through troubleshooting the state, imagepullbackoff.

You got your deployment, statefulset, or somehow turned on a pod on the Kubernetes
cluster and it is in a `imagepullbackoff` state.  What can you do now and how do you troubleshoot
it to see what the problem is?

```bash
$ kubectl get pods
NAME                                                   READY   STATUS             RESTARTS   AGE
invalid-container-5896955f9f-cg9jg                     1/2     ImagePullBackOff   0          21h
```

There can be various reasons on why it is in a `imagepullbackoff` state.  First, let's figure out what error message you have and what it's telling you with `describe`.

```bash
$ kubectl describe pod invalid-container-5896955f9f-cg9jg
```

This will give you additional information.  The describe output can be long but look
at the `Events` section first.

## Troubleshooting: Invalid container image

```bash
$ kubectl describe pod invalid-container-5896955f9f-cg9jg
...
...
Containers:
  my-container:
    Container ID:   
    Image:          foobartest4
...
...
Events:
  Type     Reason     Age                 From                                     Message
  ----     ------     ----                ----                                     -------
  Normal   Scheduled  115s                default-scheduler                        Successfully assigned dev-k8sbot-test-pods/invalid-container-5896955f9f-r6sgz to gke-gar-3-pool-1-9781becc-gc8h
  Normal   Pulling    113s                kubelet, gke-gar-3-pool-1-9781becc-gc8h  pulling image "gcr.io/google_containers/echoserver:1.0"
  Normal   Pulled     84s                 kubelet, gke-gar-3-pool-1-9781becc-gc8h  Successfully pulled image "gcr.io/google_containers/echoserver:1.0"
  Normal   Created    84s                 kubelet, gke-gar-3-pool-1-9781becc-gc8h  Created container
  Normal   Started    83s                 kubelet, gke-gar-3-pool-1-9781becc-gc8h  Started container
  Normal   BackOff    27s (x4 over 82s)   kubelet, gke-gar-3-pool-1-9781becc-gc8h  Back-off pulling image "foobartest4"
  Warning  Failed     27s (x4 over 82s)   kubelet, gke-gar-3-pool-1-9781becc-gc8h  Error: ImagePullBackOff
  Normal   Pulling    13s (x4 over 114s)  kubelet, gke-gar-3-pool-1-9781becc-gc8h  pulling image "foobartest4"
  Warning  Failed     12s (x4 over 113s)  kubelet, gke-gar-3-pool-1-9781becc-gc8h  Failed to pull image "foobartest4": rpc error: code = Unknown desc = Error response from daemon: repository foobartest4 not found: does not exist or no pull access
  Warning  Failed     12s (x4 over 113s)  kubelet, gke-gar-3-pool-1-9781becc-gc8h  Error: ErrImagePull
```

There is a long list of events but only a few with the `Reason` of `Failed`.

```bash
Warning  Failed     27s (x4 over 82s)   kubelet, gke-gar-3-pool-1-9781becc-gc8h  Error: ImagePullBackOff
Warning  Failed     12s (x4 over 113s)  kubelet, gke-gar-3-pool-1-9781becc-gc8h  Failed to pull image "foobartest4": rpc error: code = Unknown desc = Error response from daemon: repository foobartest4 not found: does not exist or no pull access
Warning  Failed     12s (x4 over 113s)  kubelet, gke-gar-3-pool-1-9781becc-gc8h  Error: ErrImagePull
```

This gives us a really good indication of what the problem is:

```bash
Error response from daemon: repository foobartest4 not found: does not exist or no pull access
```

From here, we either have a non-existent container registry name or we dont have access to it.
Usually a system will not tell you if an item exist or not if you don't have access to it.  This
would allow someone to glean more information than they have access to.  This is why the error
message can mean multiple things.

As a user you should at this point take a look at the image name and make sure you have the
correct name.  If you do, then you should make sure that this container registry for this
image does not require authentication.  As a test you can try to pull the same imae from yor laptop
to see if it works locally for you.

## Troubleshooting: Invalid container image tag

Another variation to this is if the container tag does not exist:

```bash
$ kubectl describe pod invalid-container-5896955f9f-cg9jg
...
...
Containers:
  my-container:
    Container ID:   
    Image:          redis:foobar
...
...
Events:
  Type     Reason     Age                  From                                     Message
  ----     ------     ----                 ----                                     -------
  Normal   Scheduled  12m                  default-scheduler                        Successfully assigned dev-k8sbot-test-pods/invalid-container-tag-85d478dfbd-hddzg to gke-gar-3-pool-1-9781becc-bdb3
  Normal   Pulling    12m                  kubelet, gke-gar-3-pool-1-9781becc-bdb3  pulling image "gcr.io/google_containers/echoserver:1.0"
  Normal   Started    11m                  kubelet, gke-gar-3-pool-1-9781becc-bdb3  Started container
  Normal   Pulled     11m                  kubelet, gke-gar-3-pool-1-9781becc-bdb3  Successfully pulled image "gcr.io/google_containers/echoserver:1.0"
  Normal   Created    11m                  kubelet, gke-gar-3-pool-1-9781becc-bdb3  Created container
  Normal   BackOff    10m (x4 over 11m)    kubelet, gke-gar-3-pool-1-9781becc-bdb3  Back-off pulling image "redis:foobar"
  Normal   Pulling    10m (x4 over 12m)    kubelet, gke-gar-3-pool-1-9781becc-bdb3  pulling image "redis:foobar"
  Warning  Failed     10m (x4 over 12m)    kubelet, gke-gar-3-pool-1-9781becc-bdb3  Error: ErrImagePull
  Warning  Failed     10m (x4 over 12m)    kubelet, gke-gar-3-pool-1-9781becc-bdb3  Failed to pull image "redis:foobar": rpc error: code = Unknown desc = Error response from daemon: manifest for redis:foobar not found
  Warning  Failed     2m1s (x40 over 11m)  kubelet, gke-gar-3-pool-1-9781becc-bdb3  Error: ImagePullBackOff

```

This is very similar to the previous error but there is a slight difference that can tell us
that it is the image tag.  Once again pulling out the pertinent events:

```bash
Warning  Failed     10m (x4 over 12m)    kubelet, gke-gar-3-pool-1-9781becc-bdb3  Failed to pull image "redis:foobar": rpc error: code = Unknown desc = Error response from daemon: manifest for redis:foobar not found
```

The previous error said the `repository` was not found and this one does not.  It tells
you the `manifest for redis:foobar not found`.  This is a very good indication that the
registry `redis` exist but it didn't find the tag `foobar`.

You can test and confirm this by trying to pull this image locally on your laptop:

```bash
$ docker pull redis:foobar
Error response from daemon: manifest for redis:foobar not found
```

We receive the save message.  If we try a valid tag:

```bash
$ docker pull redis:latest
latest: Pulling from library/redis
6ae821421a7d: Already exists
e3717477b42d: Pull complete
8e70bf6cc2e6: Pull complete
0f84ab76ce60: Pull complete
0903bdecada2: Pull complete
492876061fbd: Pull complete
Digest: sha256:dd5b84ce536dffdcab79024f4df5485d010affa09e6c399b215e199a0dca38c4
Status: Downloaded newer image for redis:latest
```

We are able to successfully pull this image.

This will help us determine what are the valid tags.  Or if your registry has a web
GUI, you can go to that also to see what the valid tags are.

## Troubleshooting: Unable to pull a private image
As we mentioned above for the `invalid image` name, a private image that you don't
have access to will return the same error messages.

If you did determine your image is private, you have to give the pod a secret that
has the proper authentication to allow it to pull the image.  This can be the same
credential that you use locally to allow you to pull the image or another read only
machine credential.

Either way, you need to do at least two things:
* Add the credential secret to Kubernetes
* Add the reference of the secret to use in your pod definition

```bash
kubectl -namespace <YOUR NAMESPACE> \
create secret docker-registry registry-secret \
--docker-server=https://index.docker.io/v1/ \
--docker-username=<THE USERNAME> \
--docker-password=<THE PASSWORD> \
--docker-email=not-needed@example.com
```

In this case the secret name is: `registry-secret`

Then add this reference so that your pod knows to use it:

```bash
apiVersion: v1
kind: Pod
metadata:
  name: foo
  namespace: awesomeapps
spec:
  containers:
    - name: foo
      image: janedoe/awesomeapp:v1
  imagePullSecrets:
    - name: registry-secret
```

More information: <a href="https://kubernetes.io/docs/concepts/containers/images/#referring-to-an-imagepullsecrets-on-a-pod">https://kubernetes.io/docs/concepts/containers/images/#referring-to-an-imagepullsecrets-on-a-pod</a>

{%- include blurb-consulting.md -%}

# More troubleshooting blog posts

* <A HREF="https://managedkube.com/kubernetes/k8sbot/troubleshooting/pending/pod/2019/02/22/pending-pod.html">Kubernetes Troubleshooting Walkthrough - Pending pods</a>
* <A HREF="https://managedkube.com/kubernetes/pod/failure/crashloopbackoff/k8sbot/troubleshooting/2019/02/12/pod-failure-crashloopbackoff.html">Kubernetes Troubleshooting Walkthrough - Pod Failure CrashLoopBackOff</a>
* <A HREF="https://managedkube.com/kubernetes/trace/ingress/service/port/not/matching/pod/k8sbot/2019/02/13/trace-ingress.html">Kubernetes Troubleshooting Walkthrough - Tracing through an ingress</a>

<!-- Blog footer share -->
{%- include blog-footer-share.html -%}
