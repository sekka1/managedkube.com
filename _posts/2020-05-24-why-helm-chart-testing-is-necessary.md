---
layout: post
title: Why Helm Chart Testing is Necessary
categories: Why Helm Chart Testing is Necessary
keywords:  Why Helm Chart Testing is Necessary
# https://jekyll.github.io/jekyll-seo-tag/advanced-usage/#customizing-image-output
# This adds the html metadata "og:image" tags to the page for URL previews
image:
  path: "/assets/logo/M_1000.jpg"
#   height: 100
#   width: 100
description: A discussion on why testing Helm chart is a good idea
---

Helm Charts is made up of Golang Templates and it has variables and conditional statements.  As you start to create a Helm Chart and expand it's use cases over time, it can instantiate itself in many ways.  As an example for a Helm Chart that launches your application container.  It can instantiate an `ingress` if the app deployer wants to or not.  The container can expose a port for other pods to be able to reach it by or not.  As the Chart evolves it will have a lot of these variations and as you add in the variations, it is a very good idea to add in a test for this because you don't want the functionality to break later on.

## An Example
Here is something I ran into the other day.  I was refactoring a Helm Chart that can run most of our application apps.  This is a generic Helm Chart that creates a Kubernetes `Deployment`, `Service`, and `Ingress`.  I was refactoring this chart to take in a list of containers for the `Deployment` instead of just one container.  The point of this article is not what I was doing but more of what the Chart testing found.  I did my refactoring.  I tested it out in a few variations locally templating it out and even installed it into my dev Kubernetes cluster.  It was working as I have expected it to.  So I commit this into Github and created a PR (Pull Request).

The PR has a set of built in tests and it failed on one.  At first, I thought the test was broken =).  Upon closer inspection, a few variation of the test has passed and it failed on one particular test that did not expose out any ports for the container. 

```bash
2020-05-24T17:29:42.0753721Z >>> helm install standard-application-4dw3lecw30 charts/standard-application --namespace standard-application-4dw3lecw30 --wait --values charts/standard-application/ci/ci-values.yaml --timeout 601s
2020-05-24T17:29:42.3503995Z Error: Service "test-app" is invalid: spec.ports: Required value
```

The test was running a `helm install` with a set of values that did not configure the `Deployment` to expose out any ports but the Chart was set to always create a `Service`.  Templating this out works fine because syntactically it is valid.  When validating it to the Kubernetes `Service` spec on apply it is not valid to have a `Service` with no `spec.ports` values.  This makes sense, after all this is the whole point of a Kubernetes `Service`.  Without this value, why even create a `Service` =).

### The details
For those of you that want to check out the details and maybe setup a Chart testing pipeline of your own.  Here are the low level deets.

If you want to create a Chart testing pipeline, I would highly recommend you to read: [how-to-host-your-helm-chart-repository-on-github](https://jamiemagee.co.uk/blog/how-to-host-your-helm-chart-repository-on-github/).  This was tremendously helpful for me to get started.

This is the test that was failing for me that found out when trying to `helm install` a `Service` without a port fails: [https://github.com/ManagedKube/helm-charts/runs/704148470?check_suite_focus=true](https://github.com/ManagedKube/helm-charts/runs/704148470?check_suite_focus=true)

After some debugging (a few hours worth), I eventually figured out the problem and added these items:
* I added this to check to make sure the `Deployment` defined some ports before out putting the `Service` resource: [https://github.com/ManagedKube/helm-charts/pull/2/commits/bc39fd816aab4db7a70524f7de505c0245e9b37b#diff-eee7292c81fdd890fb4ab55a4ef8063dR1-R10](https://github.com/ManagedKube/helm-charts/pull/2/commits/bc39fd816aab4db7a70524f7de505c0245e9b37b#diff-eee7292c81fdd890fb4ab55a4ef8063dR1-R10)
* Here are some additional tests I added in to make sure it would work if one container had ports while the other did not in a `Pod`: [https://github.com/ManagedKube/helm-charts/pull/2/commits/bc39fd816aab4db7a70524f7de505c0245e9b37b#diff-21f1299efcb46dca2385f1ebb6746aebR1-R19](https://github.com/ManagedKube/helm-charts/pull/2/commits/bc39fd816aab4db7a70524f7de505c0245e9b37b#diff-21f1299efcb46dca2385f1ebb6746aebR1-R19)

## Conclusion
There was a lot of tech talk here but the over all moral of the story is that testing is a good thing.  It helps catches errors that you didn't manually test for.  This also helps you to create more robust tests.  After the test failed and diagnosing the problem, there were actually a few tests that I added to cover a few more variation on how this can break depending on how the user will use this Chart.  This leads to a more stable Helm Chart that can save the downstream users of this time.  Imagine if 1 or 5 teams used this and each one of them runs into this problem.  It could be at a minimum hours saved and at a maximum it could even save you from production down time.
