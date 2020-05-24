---
layout: post
title: Is Kubernetes too complex for my organization?
categories: Is Kubernetes too complex for my organization?
keywords:  Is Kubernetes too complex for my organization?
# https://jekyll.github.io/jekyll-seo-tag/advanced-usage/#customizing-image-output
# This adds the html metadata "og:image" tags to the page for URL previews
image:
  path: "/assets/logo/M_1000.jpg"
#   height: 100
#   width: 100
description: A dicussion if Kubernetes is the right fit for your organization
---

{%- include share-bar.html -%}

<Picture - Kubernetes in one hand and Chef/puppet/ansible on another>



I often get the question from my clients asking if Kubernetes is a right fit for them or is it too complex?

I of course answer a question with a question like, what are you trying to do?

The common answer is that they need to run a few APIs, a web frontend, and some databases.  They want to have a dev, test, production environment and being able to promote code along this path.

Most people need this stack.  As you can see, the infrastructure items (green and orange) out number your application (blue) tremendously.
<The kubernetes-common-service stack diagram>

This is a very traditional and common setup.  We have been building this kind of architecture for 15+ years now, way before Kubernetes came around.  So why is everyone talking about Kubernetes now and should you use it?

As always, the answer to that question is, it depends.
* What are your alternatives?
* Is this a new project?
* Is Kubernetes really that complex?

Lets try to answer each one of these questions.

## What are your alternatives?
You are going to need something to help you build your infrastructure.  I would not recommend doing all of this by hand from some cloud web GUI.  If this is a prototype then ok.  Go for it.  If you are intending to productionalize this and people are paying you for it's usage, then I would highly suggest against going the manual route.  You will run into production issues and trying to fix it later will take a lot longer to just do it right from the beginning.

Lets talk about the alternate tools you can sub in for Kubernetes.  You need something to build you machines and then run your software on those machines.  You can go with something like Check, Puppet, or Ansible.  The classis configuration management tools.  I have personally used all three in the pass 12 years and at the time of usage, it was the best tool for the job.  It got you just so much more than what I would have come up with and this was all tools already available.  However, as we progress, we have learned that these tools has a lot of deficiencies.  They are good at provisioning servers and putting it in a state we want but they are not so great at deploying our own software in a CI/CD pipeline.  A bigger point is that, we have moved on from configuring servers to thinking about what we want to run on those servers.  For example, I don't really care if my server is running Ubuntu, CentOS, or some other Linux flavor.  What I want is my API running on there and serving HTTP requests.  We have moved on from the days of managing servers.  This is where another big concepts like Docker comes into play.  With Docker, you can literally build once and run anywhere.  It will work the same on my local machine as it would in the cloud.  This is a very big change.  Now, I don't need to be tightly coupled to how my infrastructure looks like in the cloud.  I could almost be assured that if it works locally in my Docker container, it would 90% of the time work like that in the cloud.  This then devalues the configuration management tools a lot.  I am not configuring servers any more.  I am mostly slinging around Docker containers now.  You want a tool that does that and Kubernetes is one such tool.  There are other tools like Hashicorp Nomad but Kubernetes is what the industry has standardized on.  Most of the open source communities effort on making tools for a containerized infrastructure is around Kubernetes.  This is like Windows in the 90s.

## Is this a new project?
If you are starting from nothing, in my opinion, it makes total sense to go with Kubernetes.  I have just said this is Windows in the 90s.  Im not sure if I need to say more =).  This where you will find all of the tools and talent going forward.

Let's think through what it would like if we built this some other way with a configuration management tool like Chef, Puppet, or Ansible.  We would probably have to use a tool like Terraform (which we also use with Kubernetes) to bring up machines in our cloud.  Then we would start writing configuration management "scripts" to help us provision our servers.  We would have to setup some machines to be our proxies, some machines to be our API(s), and some machines to be our databases.  Now, I need to test all of those items and maintain it, including on how I scale each one of these items out.

On the flip side, lets say we want Kubernetes on GCP.  We would use Terraform to bring us up a Kubernetes cluster and then we would write `yaml` files for the proxies we want, the API(s), and the databases.  Everything but the API is done for us already by the Kubernetes community through Helm Chart.  They update it, they maintain, it and they test it.  Then we are left to work on our business value which is our API.  All of the non value add items such as the infrastructure, we are using open source items that the community is maintaining for us and we are using it like Lego pieces to build up our infrastructure.  We are maintaining a lot less code than we would if we have gone down the configuration management route.  Another big thing that people miss on the cost is on going maintenance.  Since we personally wrote a lot less code, we have a lot less to maintain our self.  Also, going forward if we hire someone new, they are not looking at some custom code we wrote and have to try to figure it out.  We are using all open source stuff that is well documented by the community.  If they have Kubernetes experience in other places and since we are using everything in a normal fashion, it will look like the previous Kubernetes infrastructure that they have been working on.  This is huge!  We don't have much tech dept and weird nuances going on here.  Someone can literally be productive on the first day.

## Is Kubernetes really that complex?
Linux is way more complex than Kubernetes but we still use it.  Most of the complexity in Linux is hidden from us and that is the way you want to run Kubernetes as well.  Kubernetes is very powerful and you know what comes with great power, its great responsibilities.  Just like Linux, if you want to turn all of the knobs and switches, you can get into a very complex situation fast.  If you just use Linux in a more basic fashion, it is rock solid and it does what you want it to do, which is run your application(s).

You also have to select the correct set of tools to use when building out your Kubernetes cluster and when you are running it.  This is an area where getting help might save you a lot of time later on.  I have personally helped over 20 companies in the last 4 years migrate their application to a Kubernetes infrastructure.  There has definitely been a lot of trial and error along the way since Kubernetes was so new.  All of that operational experience has given me a set of rock solid Lego blocks to use and to build an infrastructure with.  I know what works and what does not.  You have to select the right set of tools to use to control the complexity.

This comes in the form of these two projects we maintain:
* https://github.com/ManagedKube/kubernetes-ops
* https://github.com/ManagedKube/kubernetes-common-services

These projects uses all open source tools.  It uses them in a way where we know this has worked in many production systems.


