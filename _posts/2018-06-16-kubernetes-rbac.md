---
layout: post
title: "Kubernetes RBAC - giving permissions for logging and port-forwarding"
categories: Kubernetes Rbac Security
keywords: Kubernetes Rbac Security

---

I am really liking Kubernetes [RBAC][rbac]. It is “fairly” simple to use and so powerful.

For example, a user said I can’t port forward to a port and pasted me the error:

&nbsp;

{% highlight ruby %}
error: error upgrading connection: pods "selenium-node-firefox-debug-mtw7r" is forbidden: User "john" cannot create pods/portforward in the namespace "app1"
{% endhighlight %}

&nbsp;

This basically told me all that I need. It states the user cannot perform the action `create` on the resource `pods/portforward`.

&nbsp;

I do think the “action” (create) and the “resource” (pods/portforward) should be highlighted somehow in the error message to make it even clearer.

&nbsp;

So I added this to his role:

&nbsp;

{% highlight ruby %}
---
kind: Role
apiVersion: rbac.authorization.k8s.io/v1
metadata:
  namespace: gawkbox-spinnaker
  name: kube-saas:list-and-logs
rules:
- apiGroups: [""]
  resources: ["pods", "pods/log", pods/portforward]
  verbs: ["get", "list", "create"]

{% endhighlight %}

&nbsp;

This solved the problem. I love it when the error tells me exactly what it is and it is so easy to express this in the role.


[rbac]: https://kubernetes.io/docs/reference/access-authn-authz/rbac/