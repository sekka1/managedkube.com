---
layout: post
title: Using the AWS CLI - find and terminate an EC2 Instance
categories: aws cli ec2
keywords: aws cli ec2
---
{%- include share-bar.html -%}

![Delete]({{ "/assets/blog/images/delete-button.jpg" | absolute_url }})

Sometimes you need to delete an EC2 instance but dont want to go into the AWS
web console.  Most of the time you will have the internal DNS hostname of the instance
which would looks something like: `ip-10-151-21-159.ec2.internal`.

The AWS CLI doesnt let you terminate a node by a hostname.  It requires the
instance ID.  So the first task is to find the instance ID and then terminate it

You can find the instance ID by describing an instance and filtering on the `private-dns-name`:

```bash
aws ec2 describe-instances --filters "Name=private-dns-name,Values=ip-10-151-21-159.ec2.internal"
```

This will give you a big JSON output.  Look for this key: `InstanceId`.

If you have [jq](https://stedolan.github.io/jq/) installed, you can do this to rip it
right out of the json:

```bash
aws ec2 describe-instances --filters "Name=private-dns-name,Values=ip-10-151-21-159.ec2.internal" | jq -r .Reservations[0].Instances[0].InstanceId
```

To terminate the instance, you simply run:

```bash
aws ec2 terminate-instances --instance-ids i-07a8839d40f993baa
```

Replacing it with your instance ID.

<!-- Blog footer share -->
{%- include share-bar.html -%}
