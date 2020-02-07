---
layout: post
title: Terraform 0.12.x Transformation
categories: Terraform 0.12.x Transformation
keywords: Terraform 0.12.x Transformation
---
{%- include share-bar.html -%}

Terraform 0.12.x and future versions will not be compatible with previous versions.  The syntax has changed and the usage of Terragrunt has also changed along with it.

With Terraform 0.12.x out now for a few months we will eventually run into to this and most likely we will eventually want to update to this version for items that needs to be maintained for the "long run".

This page has some information on how to convert your pre 0.12.x Terraform to one that is compatible with 0.12.x.

Here are some items that were helpful when I was transforming one of our first Terraform to be 0.12.x compatible.

# What they changed:
    https://www.hashicorp.com/blog/announcing-terraform-0-12

# Upgrading to 0.12
    https://www.terraform.io/upgrade-guides/0-12.html

# Download:
    https://www.terraform.io/downloads.html
        0.12.6

    https://github.com/gruntwork-io/terragrunt/releases
        v0.19.16

# Terragrunt and Terraform 0.12.x
    Still needed (yes)?  https://blog.gruntwork.io/terragrunt-how-to-keep-your-terraform-code-dry-and-maintainable-f61ae06959d8

# Terragrunt doc:
    https://github.com/gruntwork-io/terragrunt

    ## Dynamic blocks
        https://www.hashicorp.com/blog/hashicorp-terraform-0-12-preview-for-and-for-each

        -tags, taints

# examples:

    https://github.com/hashicorp/terraform-guides/tree/master/infrastructure-as-code/terraform-0.12-examples


# There is a handy tool that will convert your module files:
```
# Go into the tf-module dir of the module to transform
cd <directory with the module files>

# init the project
terraform init

# Do the auto upgrade
terraform 0.12upgrade
```

You might see blocks of comments with TODO in them and these are the items that the conversion tools couldn't convert or there are some ambiguity around it.  You should search for these and take a look.


# Update your Terragrunt files as they have changed:
```
# Update the environments terragrunt's hcl file

    -rename the terraform.tfvars file to terragrunt.hcl

# Delete the tf files
    -This file will cause runtime problems for Terragrunt if left here.

# Run the new terragrunt 0.19.16

    terragrunt apply
```

Examples of a Terraform/Terragrunt conversion: https://github.com/ManagedKube/kubernetes-ops/pull/17

{%- include blurb-consulting.md -%}

<!-- Blog footer share -->
{%- include share-bar.html -%}
