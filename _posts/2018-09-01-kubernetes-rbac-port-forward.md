---
layout: post
title: Kubernetes RBAC Port Forward
categories: Kubernetes RBAC Port Forward
keywords: Kubernetes RBAC Port Forward port-forward
---
{%- include share-bar.html -%}

The `kubectl` CLI tool has a really nice feature that lets you port-forward a local
port to a remote port into a pod.  For example, if you are running a Postgres server
or a web server, you usually cant reach it without exposing a `nodeport` or an
`ingress`.  Sometime this is undesirable because you dont want to expose it out to
the world or you just need to access this port for debugging reasons.

The `kubectl port-forward` command allows you to port forward any arbitrary port
from a pod to your local machine.

```
Local machine (8080)  <---> Kubernetes <--> web (8080)
```

After setting up the port forward, you can go to your web browser at `http://localhost:8080`
and it will send the request to the pod inside of the Kuberentes cluster.

Usage:
```bash
Usage:
  kubectl port-forward POD [LOCAL_PORT:]REMOTE_PORT [...[LOCAL_PORT_N:]REMOTE_PORT_N] [options]
```

Listen on port 8888 locally, forwarding to 5000 in the pod
```bash
kubectl port-forward mypod 8888:5000
```

## RBAC
To take this example even further, lets say that you want to give a person
access to only port-forward.  You will have to create an RBAC role that lets this
person only do this:

The role:
```yaml
---
kind: Role
apiVersion: rbac.authorization.k8s.io/v1
metadata:
  namespace: my-namespace
  name: allow-port-forward
rules:
- apiGroups: [""]
  resources: ["pods", "pods/portforward"]
  verbs: ["get", "list", "create"]
```

This sets up a role in the namespace `my-namespace` and allows this role to `get`,
`list`, and `create` on `pods` and `pods/portforward`.  These are all of the
permissions needed to allow someone to port-forward.  This person will be able
to list the pods in this namespace.

Then you bind this role to a user:

```yaml
---
apiVersion: rbac.authorization.k8s.io/v1
kind: RoleBinding
metadata:
  name: allow-port-forward
  namespace: my-namespace
subjects:
- kind: User
  name: bob
  apiGroup: rbac.authorization.k8s.io
roleRef:
  kind: Role
  name: allow-port-forward
  apiGroup: ""
```

This will give the user `bob` the rights to perform the above actions in the namespace `my-namespace`

<!-- Blog footer share -->
{%- include share-bar.html -%}
