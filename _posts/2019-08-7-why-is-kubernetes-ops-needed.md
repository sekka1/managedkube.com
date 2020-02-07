---
layout: post
title: Why Is Kubernetes-ops Needed?
categories: Why Is Kubernetes-ops Needed?
keywords: Why Is Kubernetes-ops Needed?
---
{%- include share-bar.html -%}

The main reason is so that we can all help each other out.  Why should we all
have to toil in infrastructure items?

For example; Terraform came out with verion 0.12.x which is pretty much all
incompatible with the previous 0.11.x version.  You literally have to change
your module files for it to work again.

Here are the changes and the PR opened for this work: https://github.com/ManagedKube/kubernetes-ops/compare/terraform-0.12.6-update?expand=1

Not fun!

It seems the Terraform guys knew this was going to be a hairy update so they
made some nice tools to help you convert.  It is fairly nice.  It converts the
modules file for you:  https://www.terraform.io/upgrade-guides/0-12.html

However, with all tools like these, they are not fool proof.  There can be
some side case where it does not know exactly what to do.

This happened to me with the node pool module.  Launching the node pool module
with version 0.11.x works.  Launching it with the 0.12.x version doesn't after
the update.  The Terraform did it's job but the nodes in the node pool never
joins the cluster.

With a lot of troubleshooting and debugging after about 3 hours, I finally figured
it out.  It turned out that the Kubernetes Taints in the new Terraform module
after the conversion didn't produce the same data structure so none of the pods
on the node were able to launch.  

I know I didn't go into too much details of the problem but this blog is to point
out why we all shouldn't have to toil in "lower level" infrastructure items.  We
can all gain the wisdom of the community and leverage what everyone contribute.

Come check out `kubernetes-ops`.  This is a full featured repository on how to
run Kubernetes in production:  https://github.com/ManagedKube/kubernetes-ops

<!-- Blog footer share -->
{%- include blog-footer-share.html -%}
