---
layout: post
title: Terraform/Terragrunt conversion to the new 0.12 format
categories: terraform terragrunt 0.12 convert cerversion
keywords: terraform terragrunt 0.12 convert cerversion
---
{%- include twitter-button-blank.html -%}

The latest Terraform 0.12.x version is out and it has a lot of good stuff but it
also has breaking changes that needs a conversion to make your current 0.11.x
Terraforms work with the latest version.  With this change the same goes with
Terrgrunt.  This blog shows you how to update your Terraform 0.11.x to 0.12.x.

First off, there are a bunch of good resources and articles you probably should
read before trying to do the conversion.  Just to get up to speed on what all
the changes are all about.

Changes to Terraform:  [https://www.hashicorp.com/blog/announcing-terraform-0-12](https://www.hashicorp.com/blog/announcing-terraform-0-12)

Terraform upgrading guide: [https://www.terraform.io/upgrade-guides/0-12.html](https://www.terraform.io/upgrade-guides/0-12.html)



With Terraform incorporating some of what Terragrunt is doing in the latest version,
is there still a need for Terragrunt?  The short answer is yes.  Read up on this
blog to see why you still want to use Terragrunt:  [https://blog.gruntwork.io/terragrunt-how-to-keep-your-terraform-code-dry-and-maintainable-f61ae06959d8](https://blog.gruntwork.io/terragrunt-how-to-keep-your-terraform-code-dry-and-maintainable-f61ae06959d8)

The good news is that this conversion is usually not that bad.  The latest Terragrunt
binary gives us an auto conversion tool to use `0.12upgrade`.

We'll walk you through this process with a full example.

## Download the new Terraform and Terragrunt
First, you need to download the newest version of Terraform and Terragrunt

Downloads:
* [https://www.terraform.io/downloads.html](https://www.terraform.io/downloads.html)
* [https://github.com/gruntwork-io/terragrunt/releases](https://github.com/gruntwork-io/terragrunt/releases)

## Initialize the module
In our example we have an AWS VPC module and `terraform.tfvars` file to use it with
some Terragrunt settings.

Here is the original VPC module: [https://github.com/ManagedKube/kubernetes-ops/tree/001253e2ae081e0f1005bed42d3c612ef3d4fd01/tf-modules/aws/vpc](https://github.com/ManagedKube/kubernetes-ops/tree/001253e2ae081e0f1005bed42d3c612ef3d4fd01/tf-modules/aws/vpc)

Looking at these files, the major thing you will notice inside these files are
statements with items such as `"${var.region}"`.  This is a tell tail sign
of a pre Terraform 0.12.x Terraform.  In post 0.12 versions all variables are
first class citizens and does not need the quotes and parsing of it.

The first thing we'll have to do is go into this modules directory and initialize it.

```bash
vpc$ terraform init

Initializing the backend...
bucket
  The name of the S3 bucket

  Enter a value: my-s3-bucket-foo

key
  The path to the state file inside the bucket

  Enter a value: foo


Successfully configured the backend "s3"! Terraform will automatically
use this backend unless the backend configuration changes.

Initializing provider plugins...
- Checking for available provider plugins...
- Downloading plugin for provider "aws" (hashicorp/aws) 2.27.0...

The following providers do not have any version constraints in configuration,
so the latest version was installed.

To prevent automatic upgrades to new major versions that may contain breaking
changes, it is recommended to add version = "..." constraints to the
corresponding provider blocks in configuration, with the constraint strings
suggested below.

* provider.aws: version = "~> 2.27"

Terraform has been successfully initialized!

You may now begin working with Terraform. Try running "terraform plan" to see
any changes that are required for your infrastructure. All Terraform commands
should now work.

If you ever set or change modules or backend configuration for Terraform,
rerun this command to reinitialize your working directory. If you forget, other
commands will detect it and remind you to do so if necessary.
```

Since we are using a S3 backend, it will ask me to initialize that.

## Run the conversion
We will now use the new Terraform 0.12 upgrade helper tool to help us update
our module to the new format.  This mostly does a good job of converting it.

```bash
vpc$ terraform 0.12upgrade

This command will rewrite the configuration files in the given directory so
that they use the new syntax features from Terraform v0.12, and will identify
any constructs that may need to be adjusted for correct operation with
Terraform v0.12.

We recommend using this command in a clean version control work tree, so that
you can easily see the proposed changes as a diff against the latest commit.
If you have uncommited changes already present, we recommend aborting this
command and dealing with them before running this command again.

Would you like to upgrade the module in the current directory?
  Only 'yes' will be accepted to confirm.

  Enter a value: yes

-----------------------------------------------------------------------------

Upgrade complete!

The configuration files were upgraded successfully. Use your version control
system to review the proposed changes, make any necessary adjustments, and
then commit.
```

And thats it.  The module is converted.

We can see it touched all of the files in our module and it added an extra file:

```bash
git status -s
 M main.tf
 M outputs.tf
 M vars.tf
?? versions.tf
```

Let's see what exactly it changed by running a `git diff`

```bash
vpc$ git diff
diff --git a/tf-modules/aws/vpc/main.tf b/tf-modules/aws/vpc/main.tf
index b3bfe2a..91c023c 100644
--- a/tf-modules/aws/vpc/main.tf
+++ b/tf-modules/aws/vpc/main.tf
@@ -1,17 +1,18 @@
 terraform {
-  backend "s3" {}
+  backend "s3" {
+  }
 }

 provider "aws" {
-  region = "${var.region}"
+  region = var.region
 }

 # VPC
 resource "aws_vpc" "main" {
-  cidr_block           = "${var.vpc_cidr}"
+  cidr_block           = var.vpc_cidr
   enable_dns_support   = true
   enable_dns_hostnames = true
-  tags                 = "${var.tags}"
+  tags                 = var.tags

   lifecycle {
     create_before_destroy = true
@@ -20,16 +21,16 @@ resource "aws_vpc" "main" {

 # Gateway
 resource "aws_internet_gateway" "main" {
-  vpc_id = "${aws_vpc.main.id}"
-  tags   = "${var.tags}"
+  vpc_id = aws_vpc.main.id
+  tags   = var.tags
 }

 resource "aws_nat_gateway" "main" {
-  count         = "${length(var.availability_zones)}"
-  allocation_id = "${element(aws_eip.nat.*.id, count.index)}"
-  subnet_id     = "${element(aws_subnet.public.*.id, count.index)}"
-  depends_on    = ["aws_internet_gateway.main"]
-  tags          = "${var.tags}"
+  count         = length(var.availability_zones)
+  allocation_id = element(aws_eip.nat.*.id, count.index)
+  subnet_id     = element(aws_subnet.public.*.id, count.index)
+  depends_on    = [aws_internet_gateway.main]
+  tags          = var.tags

   lifecycle {
     create_before_destroy = true
@@ -37,9 +38,9 @@ resource "aws_nat_gateway" "main" {
 }

 resource "aws_eip" "nat" {
-  count = "${length(var.availability_zones)}"
+  count = length(var.availability_zones)
   vpc   = true
-  tags  = "${var.tags}"
+  tags  = var.tags

   lifecycle {
     create_before_destroy = true
@@ -48,13 +49,13 @@ resource "aws_eip" "nat" {

 # Subnets
 resource "aws_subnet" "public" {
-  count                   = "${length(var.availability_zones)}"
-  vpc_id                  = "${aws_vpc.main.id}"
-  cidr_block              = "${element(var.public_cidrs, count.index)}"
-  availability_zone       = "${element(var.availability_zones, count.index)}"
+  count                   = length(var.availability_zones)
+  vpc_id                  = aws_vpc.main.id
+  cidr_block              = element(var.public_cidrs, count.index)
+  availability_zone       = element(var.availability_zones, count.index)
   map_public_ip_on_launch = true

-  tags = "${var.tags}"
+  tags = var.tags

   lifecycle {
     create_before_destroy = true
@@ -62,12 +63,12 @@ resource "aws_subnet" "public" {
 }

 resource "aws_subnet" "private" {
-  count             = "${length(var.availability_zones)}"
-  vpc_id            = "${aws_vpc.main.id}"
-  cidr_block        = "${element(var.private_cidrs, count.index)}"
-  availability_zone = "${element(var.availability_zones, count.index)}"
+  count             = length(var.availability_zones)
+  vpc_id            = aws_vpc.main.id
+  cidr_block        = element(var.private_cidrs, count.index)
+  availability_zone = element(var.availability_zones, count.index)

-  tags = "${var.tags}"
+  tags = var.tags

   lifecycle {
     create_before_destroy = true
@@ -78,22 +79,22 @@ resource "aws_subnet" "private" {

 // Public
 resource "aws_route_table" "public" {
-  vpc_id = "${aws_vpc.main.id}"
+  vpc_id = aws_vpc.main.id

-  tags = "${var.tags}"
+  tags = var.tags
 }

 resource "aws_route" "public" {
-  route_table_id         = "${aws_route_table.public.id}"
+  route_table_id         = aws_route_table.public.id
   destination_cidr_block = "0.0.0.0/0"
-  gateway_id             = "${aws_internet_gateway.main.id}"
+  gateway_id             = aws_internet_gateway.main.id
 }

 resource "aws_route_table" "private" {
-  count  = "${length(var.availability_zones)}"
-  vpc_id = "${aws_vpc.main.id}"
+  count  = length(var.availability_zones)
+  vpc_id = aws_vpc.main.id

-  tags = "${var.tags}"
+  tags = var.tags

   lifecycle {
     create_before_destroy = true
@@ -101,10 +102,10 @@ resource "aws_route_table" "private" {
 }

 resource "aws_route" "private" {
-  count                  = "${length(var.availability_zones)}"
-  route_table_id         = "${element(aws_route_table.private.*.id, count.index)}"
+  count                  = length(var.availability_zones)
+  route_table_id         = element(aws_route_table.private.*.id, count.index)
   destination_cidr_block = "0.0.0.0/0"
-  nat_gateway_id         = "${element(aws_nat_gateway.main.*.id, count.index)}"
+  nat_gateway_id         = element(aws_nat_gateway.main.*.id, count.index)
 }

 /**
@@ -112,9 +113,9 @@ resource "aws_route" "private" {
  */

 resource "aws_route_table_association" "private" {
-  count          = "${length(var.availability_zones)}"
-  subnet_id      = "${element(aws_subnet.private.*.id, count.index)}"
-  route_table_id = "${element(aws_route_table.private.*.id, count.index)}"
+  count          = length(var.availability_zones)
+  subnet_id      = element(aws_subnet.private.*.id, count.index)
+  route_table_id = element(aws_route_table.private.*.id, count.index)

   lifecycle {
     create_before_destroy = true
@@ -122,9 +123,9 @@ resource "aws_route_table_association" "private" {
 }

 resource "aws_route_table_association" "public" {
-  count          = "${length(var.availability_zones)}"
-  subnet_id      = "${element(aws_subnet.public.*.id, count.index)}"
-  route_table_id = "${aws_route_table.public.id}"
+  count          = length(var.availability_zones)
+  subnet_id      = element(aws_subnet.public.*.id, count.index)
+  route_table_id = aws_route_table.public.id

   lifecycle {
     create_before_destroy = true
@@ -138,8 +139,8 @@ resource "aws_route_table_association" "public" {
  */

 resource "aws_default_security_group" "default" {
-  vpc_id = "${aws_vpc.main.id}"
-  tags   = "${var.tags}"
+  vpc_id = aws_vpc.main.id
+  tags   = var.tags

   ingress {
     protocol  = -1
@@ -152,6 +153,7 @@ resource "aws_default_security_group" "default" {
     from_port   = 0
     to_port     = 0
     protocol    = "-1"
-    cidr_blocks = "${var.security_group_default_egress}"
+    cidr_blocks = var.security_group_default_egress
   }
 }
+
diff --git a/tf-modules/aws/vpc/outputs.tf b/tf-modules/aws/vpc/outputs.tf
index 8da208e..0f948cb 100644
--- a/tf-modules/aws/vpc/outputs.tf
+++ b/tf-modules/aws/vpc/outputs.tf
@@ -1,3 +1,4 @@
 output "aws_vpc_id" {
-  value = "${aws_vpc.main.id}"
+  value = aws_vpc.main.id
 }
+
diff --git a/tf-modules/aws/vpc/vars.tf b/tf-modules/aws/vpc/vars.tf
index 2e33e99..b468b11 100644
--- a/tf-modules/aws/vpc/vars.tf
+++ b/tf-modules/aws/vpc/vars.tf
@@ -1,15 +1,15 @@
 # Required

 variable "tags" {
-  type = "map"
+  type = map(string)

   default = {
-    Name            = "dev",
-    Environment     = "env",
-    Account         = "dev",
-    Group           = "devops",
-    Region          = "us-east-1",
-    managed_by      = "Terraform"
+    Name        = "dev"
+    Environment = "env"
+    Account     = "dev"
+    Group       = "devops"
+    Region      = "us-east-1"
+    managed_by  = "Terraform"
   }
 }

@@ -23,24 +23,25 @@ variable "vpc_cidr" {

 variable "availability_zones" {
   description = "AZs for subnets i.e. [us-east-1a, us-east-1b]"
-  type        = "list"
+  type        = list(string)
 }

 variable "public_cidrs" {
   description = "CIDR block for public subnets (should be the same amount as AZs)"
-  type        = "list"
+  type        = list(string)
 }

 variable "private_cidrs" {
   description = "CIDR block for private subnets (should be the same amount as AZs)"
-  type        = "list"
+  type        = list(string)
 }

 variable "optional_vpc_tags" {
   default = {}
-  type    = "map"
+  type    = map(string)
 }

 variable "security_group_default_egress" {
   default = ["0.0.0.0/0"]
 }
+
```
While there are a lot of changes it was mostly changing the same type of items
over and over again like making variables into first class citizens of 0.12.x.

It even added a versions file for us so pre 0.12.x Terrform will not be able to use this:

```bash
cat versions.tf

terraform {
  required_version = ">= 0.12"
}
```

That is it for our module.  Next we'll update our Terragrunt usage of this module.

## Update Terragrunt usage
With this change and if you have read the Terragrunt's blog above, there are some
breaking changes to the Terragrunt usage as well.  So, we'll have to update a few files.

Here is the original Terrgrunt file to use the Terraform 0.11.x module: [https://github.com/ManagedKube/kubernetes-ops/blob/98b2701023981e4adfb593a699f09f06823e7627/tf-environments/dev/aws/vpc/terraform.tfvars](https://github.com/ManagedKube/kubernetes-ops/blob/98b2701023981e4adfb593a699f09f06823e7627/tf-environments/dev/aws/vpc/terraform.tfvars)

We first have to rename the `terraforms.tfvars` file name to `terragrunt.hcl`.  It
turns out that Terragrunt has been taking advantage of a "feature/bug" in Terraform
where it did not fully validate the `terraform.tfvars` files and Terragrunt has been
putting their own directives in that file.  With Terraform 0.12.x Terraform checks
this file to make sure nothing it doesn't want is not in there.  So Terragrunt now
needs it's own file so we can use the Terragrunt's directives.

Rename the file `terraform.tfvars` to `terragrunt.hcl`

There are some slight syntax changes to our previous values file.  The new
file looks like:

```
include {
  path = find_in_parent_folders()
}

terraform {
  source = "../../../../tf-modules/aws/vpc/"

  extra_arguments "common_vars" {
    commands = get_terraform_commands_that_need_vars()

    arguments = [
      "-var-file=${get_parent_terragrunt_dir()}/_env_defaults/aws.tfvars",
    ]
  }
}

inputs = {

  availability_zones            = ["us-east-1a", "us-east-1b", "us-east-1c"]

  public_cidrs                  = ["10.10.6.0/24", "10.10.7.0/24", "10.10.8.0/24"]

  private_cidrs                 = ["10.10.1.0/24", "10.10.2.0/24", "10.10.3.0/24"]

  tags = {
    Name            = "dev",
    Environment     = "dev",
    Account         = "dev",
    Group           = "devops",
    Region          = "us-east-1"
    managed_by      = "Terraform"
  }

}
```

At the base of our environment folder we have a Terragrunt file that described
where the common settings for all modules in this folder like the statestore path.
For the same reason above we have to rename this file also.  If you are going to be
in a mix Terraform version (0.11 and 0.12) for a bit you can leave the `terraform.tfvars`
file here and just add a new `terragrunt.hcl` file.

For example: [https://github.com/ManagedKube/kubernetes-ops/tree/98b2701023981e4adfb593a699f09f06823e7627/tf-environments/dev](https://github.com/ManagedKube/kubernetes-ops/tree/98b2701023981e4adfb593a699f09f06823e7627/tf-environments/dev)

Again, there are slight changes to the file format.  Compare the two and make the
appropriate adjustments.

## Run terragrunt apply
Now we can apply this using the latest Terraform and Terragrunt!

```
terragrunt apply
```

If the conversion was correct and the new files are in place.  The Terrafrom will
continue as normal and create your resources.  In this case, it is an AWS VPC.

Here is the full Github Pull Request for the changes described above:

[https://github.com/ManagedKube/kubernetes-ops/pull/28](https://github.com/ManagedKube/kubernetes-ops/pull/28)

{%- include blurb-consulting.md -%}

<!-- Blog footer share -->
{%- include blog-footer-share.html -%}
