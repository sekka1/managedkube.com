---
layout: post
title: Kubernetes Cron Jobs
categories: kubernetes cron job
keywords: kubernetes cron job
# https://jekyll.github.io/jekyll-seo-tag/advanced-usage/#customizing-image-output
# This adds the html metadata "og:image" tags to the page for URL previews
image:
  path: "/assets/logo/M_1000.jpg"
#   height: 100
#   width: 100
description: Sometime you just have to run a cronjob that does something and sometime you have to run a cronjob that restarts a service.
---
{%- include share-bar.html -%}

Sometime you just have to run a cronjob that does something and sometime you have
to run a cronjob that restarts a service.  Yeah, unfortunately this is a thing.

For example, someone asked me if there is a way we can restart Redis every 24 hours.

I asked the normal questions, like are there any way we can just fix the problem?
The answer was we are going to use another library that wont need this but that is
coming soon...so that sounds like a reasonable answer.

Alright...this was my solution.  

I am going to make a Kubernetes cron job that will `patch` the `deployement` updating
the pod's label.  The effect this would have is that all of the pods in this deployment
would start to roll per the `rolling-update` parameters for this deployment.  Which
works out nicely because the `rolling-updates` are safe and there will be no outage
to this service.

Here are the yaml configurations:

```yaml
---
kind: Role
apiVersion: rbac.authorization.k8s.io/v1
metadata:
  namespace: my-namespace
  name: redis-restart
rules:
- apiGroups:
  - extensions
  - apps
  resources:
  - deployments
  - replicasets
  verbs:
  - 'patch'
  - 'get'

---
kind: RoleBinding
apiVersion: rbac.authorization.k8s.io/v1beta1
metadata:
  name: redis-restart
  namespace: my-namespace
subjects:
- kind: ServiceAccount
  name: sa-redis-restart
  namespace: my-namespace
roleRef:
  kind: Role
  name: redis-restart
  apiGroup: ""

---
apiVersion: v1
kind: ServiceAccount
metadata:
  name: sa-redis-restart
  namespace: my-namespace

---
apiVersion: batch/v1beta1
kind: CronJob
metadata:
  name: restart-redis
  namespace: my-namespace
spec:
  schedule: "0 */24 * * *"
  jobTemplate:
    spec:
      template:
        spec:
          serviceAccountName: sa-redis-restart
          containers:
          - name: kubectl
            image: garland/kubectl:1.10.4
            command:
            - /bin/sh
            - -c
            - kubectl patch deployment decision-server -p '{"spec":{"template":{"metadata":{"labels":{"restarted-by":"'${POD_NAME}'"}}}}}'
            env:
              - name: POD_NAME
                valueFrom:
                  fieldRef:
                    fieldPath: metadata.name
          restartPolicy: OnFailure
```

Of course you are using RBAC in your Kubernetes cluster...right?

The first 3 configs are for setting up the permissions: `Role`, `RoleBinding`, `ServiceAccount`

There is a schedule for the cron schedule: `schedule: "0 */24 * * *"`.  This is standard
cron syntax.

It setups a role with permissions only to patch a deployment in the `namespace` named: `my-namespace`.

The fourth yaml config is where the `CronJob` is defined.  As you can see it is fairly
simple.

It uses the `serviceAccountName` created above it.

The image (`garland/kubectl:1.10.4`) is a set of images that I maintain to help with
these sort of tasks (https://github.com/sekka1/containers).  It is a small image with
only one binary installed in it to perform distinct tasks (such as this one).

Since we have to change something in the labels to induce a `rolling-update` for the
`deployment`, we are updating the label with the pod's name that is performing this
patch.  These pod names are guaranteeded to be unique.

There we have it.  A simple cron job with limited permissions to perform a task.

<!-- Blog footer share -->
{%- include blog-footer-share.html -%}

{% include blog-cta-1.html %}
