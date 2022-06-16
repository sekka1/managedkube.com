---
layout: post
title: Full GitOps AWS EKS
categories: Full GitOps AWS EKS
keywords:  Full GitOps AWS EKS
# https://jekyll.github.io/jekyll-seo-tag/advanced-usage/#customizing-image-output
# This adds the html metadata "og:image" tags to the page for URL previews
image:
  path: "/assets/logo/M_1000.jpg"
#   height: 100
#   width: 100
description: Full GitOps AWS EKS
---

{%- include share-bar.html -%}

### Table of Content
- [Install the pre-requisite tools](#install-the-pre-requisite-tools)
  * [Requirements](#requirements)

<small><i><a href='http://ecotrust-canada.github.io/markdown-toc/'>Table of contents generated with markdown-toc</a></i></small>






# What this is:

This is the start of a series of blogs that will walk through how to create an
AWS EKS ecosystem that is suitable for production usage.  This process and the
set of code here has been used in multiple production environment over the years
at various companies.                                                               <---Link to companies in the kubernetes-ops page

# The stack that this Creates

<-------------------------------------------------------------------------------add the kubernetes stack diagram here

# Why another AWS EKS How To?
Most tutorials or How Tos out there is not suitable for production use and in
fact most of the stuff out there tells you that outright saying something like
this is an example and do not use for production or if you want to use this for
production, do x,y,z as well and they don't walk you through the x,y,z.            <--there are a bunch of gcp docs that says this, find some

Everything outlined here is currently being used in a production environment
in many companies right now.

The problem is that if you wanted to do one thing you can mostly just Google
it and find a bunch of tutorials on how to do that one thing.  Even how to
create something with a bunch of components.  However, this creates a full
Kubernetes stack which are a lot of components.

Back to what is out there.  Most of everything out there deal with "Day 1/0"
activities where it helps you to create it and it doesn't deal with the "Day 2"
or on going maintenance, updates, and change cycle of the infrastructure.  Arguably,
the Day 2 activities is where you spend most of your time and the lifecycle of
the infrastructure.  Then why doesn't most of the tutorials out there talk
more about this instead of just the creation phase?  Because that phase of the
lifecycle is hard, it very much depends on your setup, and it is not as sexy
as hey, I can create this complex setup with one command.

This series of blogs will walk us through how we manage a real production
infrastructure.


# What this is not
This is for everyone and I think it is a good read even if you are not going to
use it yet.  It will give you some very good ideas on what is possible and why
you want to do it this way.

If you just want to experiment with running your application on Kubernetes I
would **not** suggest that you follow this set of guides.  This set of guides
was intended for people who have chosen Kubernetes and want to run it in
production.  If you just wanted to "try" out Kubernetes, just click through
the AWS EKS console and bring yourself up a Kubernetes cluster and get to the
running your application part to see if you like it.


# Why you would want to use this
You want to use this because you want to run your application on Kubernetes and
you are saying I want to go to production with it.  I want an infrastructure process
that has been tested and well worked out.  I want a secure infrastructure and
process.  Using this set of items will accelerate your time to production because
this setup has been through many many revisions and is battle tested.  I'm definitely
not saying it is perfect but a lot of the imperfections has been removed over time.

You want a GitOps Infrastructure as Code (IaC) workflow for creating and
managing your infrastructure.



# Why we are posting this
If you were like me, you would be asking why are they posting this?  What is the
motivation here?  Free is never free =).

You would be right.  We do have an ulterior motive for publishing this as all open
source stuff.  

We are consultants!  We think that the items here are table stakes even though
we have spent thousands of hours on it and living in it.  The code by itself
has never been the thing that gave us the income.  It has always been the
combination of the code and us helping people walk through this process that was
the magic combination.

We publish the code and this series of blogs as content marketing.  To get our
names out there on what we can do.  Some people will just use the code.  Cool!
But hopefully there will be those few that wants our expertise as well to even
further accelerate their journey into a Cloud Native framework.
















{% include blog-cta-1.html %}

# More troubleshooting blog posts

* <A HREF="https://managedkube.com/kubernetes/k8sbot/troubleshooting/pending/pod/2019/02/22/pending-pod.html">Kubernetes Troubleshooting Walkthrough - Pending pods</a>
* <A HREF="https://managedkube.com/kubernetes/trace/ingress/service/port/not/matching/pod/k8sbot/2019/02/13/trace-ingress.html">Kubernetes Troubleshooting Walkthrough - Tracing through an ingress</a>
* <A HREF="https://managedkube.com/kubernetes/k8sbot/troubleshooting/imagepullbackoff/2019/02/23/imagepullbackoff.html">Kubernetes Troubleshooting Walkthrough - imagepullbackoff</a>

<!-- Blog footer share -->
{%- include blog-footer-share.html -%}
