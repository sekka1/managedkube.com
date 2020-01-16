---
layout: post
title: Docker Container Toolbox
categories: docker aws tools troubleshooting
keywords: docker aws tools troubleshooting
---
{%- include twitter-button-blank.html -%}

![Docker Toolbox]({{ "/assets/blog/images/Cover-DockerToolbox.png" | absolute_url }})

Docker is useful in many different ways. One way that I use containers is to use them like a toolbox. Mostly the toolbox contains just a binary that I need or a few that are useful together.

As an example, lot of my work involves using [Amazon Web Services (AWS)][amazon-web-services] and I used the [aws-cli][aws-cli] to automate things and look up information.

Most of everything I run nowadays are in containers and most of the systems has [Docker][docker] installed. Since I usually dont want to install anything more onto these system(s) but have a need for a tools on the machine, I run a Docker container with the tool I need for my task.

I have a repository of different tools that are public and in [Docker Hub][docker-hub] that I keep up to date: https://github.com/sekka1/containersp.  


On a machine, if I need to use the `aws-cli` I would run this:


```bash
docker run \
--env AWS_ACCESS_KEY_ID=${YOUR_ACCESS_KEY{} \
--env AWS_SECRET_ACCESS_KEY=${YOUR_SECRET_ACCESS} \
--env AWS_DEFAULT_REGION=us-east-1 \
garland/aws-cli:1.15.47 \
aws ec2 describe-instances --instance-ids i-90949d7a
```

Since this is a public image, I dont need to log into Docker Hub on that machine to get it and I can use this image from anywhere.


This is even useful locally when I am testing something out. For example, the `aws-cli` has been around for a long time and has many versions. When im updating a script, a lot of the time the systems that are using this script has a certain `aws-cli` version. So I would want to test with that version of the `aws-cli` locally to make sure everything works out. Instead of installing various versions locally, I just run my tests with the correct versioned container.


Another use for this container is to upload items to S3 from a server. The server would usually not have `aws-cli` installed but it does have Docker. With this command you can upload anything to your S3 buckets:


```bash
docker run \
--env AWS_ACCESS_KEY_ID=${YOUR_ACCESS_KEY} \
--env AWS_SECRET_ACCESS_KEY=${YOUR_SECRET_ACCESS} \
-v $PWD:/data \
garland/aws-cli:1.15.47 \
aws s3 sync . s3://mybucket
```


[amazon-web-services]: https://aws.amazon.com
[aws-cli]: https://aws.amazon.com/cli/
[docker]: https://www.docker.com
[docker-hub]: https://hub.docker.com
