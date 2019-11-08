---
layout: post
title: Updating Terraformed GKE clusters via a GitOps Workflow
categories: Updating Terraformed GKE clusters via a GitOps Workflow
keywords: Updating Terraformed GKE clusters via a GitOps Workflow
---

# Updating Terraformed GKE clusters via a GitOps Workflow
This will show you how to update your GKE cluster through a GitOps workflow and shows you how to update
the clusters in a safe way.

This is based on managing a GKE cluster via Terraform from this project: [kubernetes-ops](https://github.com/ManagedKube/kubernetes-ops)

[GCP/GKE Upgrading docs](https://cloud.google.com/kubernetes-engine/docs/how-to/upgrading-a-cluster)

# masters on auto upgrade?

No

# nodes on auto upgrade?

This is set to disabled

# How to roll in a master upgrade via terraform?

Updating the masters min version

from: 1.12.10-gke.15   to 1.13.11-gke.5


Checking the version our cluster and nodes are currently at:

```yaml
gcloud container clusters list                  
NAME         LOCATION     MASTER_VERSION  MASTER_IP      MACHINE_TYPE   NODE_VERSION    NUM_NODES  STATUS
gcp-staging  us-central1  1.12.10-gke.15  34.70.108.205  n1-standard-2  1.12.10-gke.15  3          RUNNING
```

```yaml
kubectl get nodes                            
NAME                                      STATUS   ROLES    AGE     VERSION
gke-gcp-staging-generic-1-0364f8f0-x9sg   Ready    <none>   5m33s   v1.12.10-gke.15
gke-gcp-staging-generic-1-998bcf48-58c1   Ready    <none>   5m38s   v1.12.10-gke.15
gke-gcp-staging-generic-1-be76e357-npl2   Ready    <none>   5m38s   v1.12.10-gke.15
                                                  
                                                  
kubectl version                                   
Client Version: version.Info{Major:"1", Minor:"16", GitVersion:"v1.16.2", GitCommit:"c97fe5036ef3df2967d086711e6c0c405941e14b", GitTreeState:"clean", BuildDate:"2019-10-15T19:18:23Z", GoVersion:"go1.12.10", Compiler:"gc", Platform:"linux/amd64"}
Server Version: version.Info{Major:"1", Minor:"12+", GitVersion:"v1.12.10-gke.15", GitCommit:"7b5157a7c600aa8ee6e2b36b56c478e030d5bfe2", GitTreeState:"clean", BuildDate:"2019-10-07T20:39:12Z", GoVersion:"go1.11.13b4", Compiler:"gc", Platform:"linux/amd64"}
```

Updating the Terraform:

```yaml
 terragrunt apply
[terragrunt] 2019/11/05 16:40:02 Running command: terraform apply
data.google_compute_network.main-network: Refreshing state...
google_compute_subnetwork.public_subnet: Refreshing state... [id=us-central1/gcp-staging-gke-public-subnet]
google_compute_subnetwork.private_subnet: Refreshing state... [id=us-central1/gcp-staging-gke-private-subnet]
google_container_cluster.primary: Refreshing state... [id=gcp-staging]

An execution plan has been generated and is shown below.
Resource actions are indicated with the following symbols:
  ~ update in-place

Terraform will perform the following actions:

  # google_container_cluster.primary will be updated in-place
  ~ resource "google_container_cluster" "primary" {
        additional_zones            = [
            "us-central1-a",
            "us-central1-b",
            "us-central1-c",
        ]
        cluster_ipv4_cidr           = "10.103.64.0/19"
        default_max_pods_per_node   = 110
        enable_binary_authorization = false
        enable_intranode_visibility = false
        enable_kubernetes_alpha     = false
        enable_legacy_abac          = false
        enable_shielded_nodes       = false
        enable_tpu                  = false
        endpoint                    = "34.70.108.205"
        id                          = "gcp-staging"
        initial_node_count          = 1
        instance_group_urls         = [
            "https://www.googleapis.com/compute/beta/projects/my-gcp-project/zones/us-central1-a/instanceGroups/gke-gcp-staging-generic-1-0364f8f0-grp",
            "https://www.googleapis.com/compute/beta/projects/my-gcp-project/zones/us-central1-b/instanceGroups/gke-gcp-staging-generic-1-998bcf48-grp",
            "https://www.googleapis.com/compute/beta/projects/my-gcp-project/zones/us-central1-c/instanceGroups/gke-gcp-staging-generic-1-be76e357-grp",
        ]
        ip_allocation_policy        = [
            {
                cluster_ipv4_cidr_block       = "10.103.64.0/19"
                cluster_secondary_range_name  = "gcp-staging-gke-pods"
                create_subnetwork             = false
                node_ipv4_cidr_block          = "10.101.32.0/20"
                services_ipv4_cidr_block      = "10.104.32.0/20"
                services_secondary_range_name = "gcp-staging-gke-services"
                subnetwork_name               = ""
                use_ip_aliases                = true
            },
        ]
        location                    = "us-central1"
        logging_service             = "logging.googleapis.com"
        master_version              = "1.12.10-gke.15"
      ~ min_master_version          = "1.12.10-gke.15" -> "1.13.11-gke.5"
        monitoring_service          = "monitoring.googleapis.com"
        name                        = "gcp-staging"
        network                     = "projects/my-gcp-project/global/networks/gcp-staging"
        node_locations              = [
            "us-central1-a",
            "us-central1-b",
            "us-central1-c",
        ]
      ~ node_version                = "1.12.10-gke.15" -> "1.13.11-gke.5"
        project                     = "my-gcp-project"
        region                      = "us-central1"
        remove_default_node_pool    = true
        resource_labels             = {
            "env"       = "staging"
            "managedby" = "terraform"
        }
        services_ipv4_cidr          = "10.104.32.0/20"
        subnetwork                  = "projects/my-gcp-project/regions/us-central1/subnetworks/gcp-staging-gke-private-subnet"

        addons_config {

            http_load_balancing {
                disabled = false
            }

            kubernetes_dashboard {
                disabled = true
            }

            network_policy_config {
                disabled = false
            }
        }

        authenticator_groups_config {
            security_group = "gke-security-groups@expample.com"
        }

        cluster_autoscaling {
            enabled = false
        }

        database_encryption {
            state = "DECRYPTED"
        }

        maintenance_policy {
            daily_maintenance_window {
                duration   = "PT4H0M0S"
                start_time = "03:00"
            }
        }

        master_auth {
            cluster_ca_certificate = "xxxxxx"

            client_certificate_config {
                issue_client_certificate = false
            }
        }

        master_authorized_networks_config {
            cidr_blocks {
                cidr_block   = "10.0.0.0/8"
                display_name = "10x"
            }
            cidr_blocks {
                cidr_block   = "172.16.0.0/12"
                display_name = "172x"
            }
            cidr_blocks {
                cidr_block   = "192.168.0.0/16"
                display_name = "192x"
            }
            cidr_blocks {
                cidr_block   = "38.30.8.136/29"
                display_name = "office_sf"
            }
            cidr_blocks {
                cidr_block   = "50.242.246.136/29"
                display_name = "office_dc"
            }
        }

        network_policy {
            enabled  = true
            provider = "CALICO"
        }

        node_config {
            disk_size_gb      = 100
            disk_type         = "pd-standard"
            guest_accelerator = []
            image_type        = "COS"
            labels            = {}
            local_ssd_count   = 0
            machine_type      = "n1-standard-2"
            metadata          = {
                "disable-legacy-endpoints" = "true"
            }
            oauth_scopes      = [
                "https://www.googleapis.com/auth/devstorage.read_only",
                "https://www.googleapis.com/auth/logging.write",
                "https://www.googleapis.com/auth/monitoring",
                "https://www.googleapis.com/auth/service.management.readonly",
                "https://www.googleapis.com/auth/servicecontrol",
                "https://www.googleapis.com/auth/trace.append",
            ]
            preemptible       = false
            service_account   = "default"
            tags              = []
        }

        node_pool {
            initial_node_count  = 1
            instance_group_urls = [
                "https://www.googleapis.com/compute/v1/projects/my-gcp-project/zones/us-central1-a/instanceGroupManagers/gke-gcp-staging-generic-1-0364f8f0-grp",
                "https://www.googleapis.com/compute/v1/projects/my-gcp-project/zones/us-central1-b/instanceGroupManagers/gke-gcp-staging-generic-1-998bcf48-grp",
                "https://www.googleapis.com/compute/v1/projects/my-gcp-project/zones/us-central1-c/instanceGroupManagers/gke-gcp-staging-generic-1-be76e357-grp",
            ]
            max_pods_per_node   = 110
            name                = "generic-1"
            node_count          = 1
            node_locations      = [
                "us-central1-a",
                "us-central1-b",
                "us-central1-c",
            ]
            version             = "1.12.10-gke.15"

            autoscaling {
                max_node_count = 3
                min_node_count = 0
            }

            management {
                auto_repair  = true
                auto_upgrade = false
            }

            node_config {
                disk_size_gb      = 100
                disk_type         = "pd-standard"
                guest_accelerator = []
                image_type        = "COS"
                labels            = {}
                local_ssd_count   = 0
                machine_type      = "n1-standard-2"
                metadata          = {
                    "disable-legacy-endpoints" = "true"
                }
                oauth_scopes      = [
                    "https://www.googleapis.com/auth/devstorage.read_only",
                    "https://www.googleapis.com/auth/logging.write",
                    "https://www.googleapis.com/auth/monitoring",
                    "https://www.googleapis.com/auth/service.management.readonly",
                    "https://www.googleapis.com/auth/servicecontrol",
                    "https://www.googleapis.com/auth/trace.append",
                ]
                preemptible       = false
                service_account   = "default"
                tags              = []
            }
        }

        private_cluster_config {
            enable_private_endpoint = false
            enable_private_nodes    = true
            master_ipv4_cidr_block  = "10.102.0.32/28"
            private_endpoint        = "10.102.0.34"
            public_endpoint         = "34.70.108.205"
        }

        release_channel {
            channel = "UNSPECIFIED"
        }
    }

Plan: 0 to add, 1 to change, 0 to destroy.

Do you want to perform these actions?
  Terraform will perform the actions described above.
  Only 'yes' will be accepted to approve.

  Enter a value: 
```


Terraform says it will update in place.  It will not delete the GKE cluster first.  Good!!

Just to confirm.  In the GCP->GKE web console for this cluster it gives me a message saying it is upgrading the masters

```
Upgrading cluster master.
The values shown below are going to change soon.
```

This is looking good and what I would expect to happen.

FYI - it takes a while for the upgrade to finish.

```
google_container_cluster.primary: Still modifying... [id=gcp-staging, 19m10s elapsed]
```

After the upgrade:

```yaml
gcloud container clusters list                    
NAME         LOCATION     MASTER_VERSION  MASTER_IP      MACHINE_TYPE   NODE_VERSION      NUM_NODES  STATUS
gcp-staging  us-central1  1.13.11-gke.5   34.70.108.205  n1-standard-2  1.12.10-gke.15 *  2          RUNNING
```

```yaml
kubectl get nodes                                 
NAME                                      STATUS   ROLES    AGE   VERSION
gke-gcp-staging-generic-1-998bcf48-58c1   Ready    <none>   31m   v1.12.10-gke.15
gke-gcp-staging-generic-1-be76e357-npl2   Ready    <none>   31m   v1.12.10-gke.15
```

Looking good.  The master's version is upgraded but not the node versions



# How to roll in a node upgrade via terraform?

Now that our masters are at the later version, we want to make our node pools to use the same `1.13.11-gke.5` version.

Make the changes in our Terraform values file and run the update:

```yaml
terragrunt apply
[terragrunt] 2019/11/05 17:07:48 Running command: terraform apply
google_container_node_pool.node_nodes: Refreshing state... [id=us-central1/gcp-staging/generic-1]

An execution plan has been generated and is shown below.
Resource actions are indicated with the following symbols:
  ~ update in-place

Terraform will perform the following actions:

  # google_container_node_pool.node_nodes will be updated in-place
  ~ resource "google_container_node_pool" "node_nodes" {
        cluster             = "gcp-staging"
        id                  = "us-central1/gcp-staging/generic-1"
        initial_node_count  = 1
        instance_group_urls = [
            "https://www.googleapis.com/compute/v1/projects/my-gcp-project/zones/us-central1-a/instanceGroupManagers/gke-gcp-staging-generic-1-0364f8f0-grp",
            "https://www.googleapis.com/compute/v1/projects/my-gcp-project/zones/us-central1-b/instanceGroupManagers/gke-gcp-staging-generic-1-998bcf48-grp",
            "https://www.googleapis.com/compute/v1/projects/my-gcp-project/zones/us-central1-c/instanceGroupManagers/gke-gcp-staging-generic-1-be76e357-grp",
        ]
        location            = "us-central1"
        max_pods_per_node   = 110
        name                = "generic-1"
      ~ node_count          = 0 -> 1
        project             = "my-gcp-project"
        region              = "us-central1"
      ~ version             = "1.12.10-gke.15" -> "1.13.11-gke.5"

        autoscaling {
            max_node_count = 3
            min_node_count = 0
        }

        management {
            auto_repair  = true
            auto_upgrade = false
        }

        node_config {
            disk_size_gb      = 100
            disk_type         = "pd-standard"
            guest_accelerator = []
            image_type        = "COS"
            labels            = {}
            local_ssd_count   = 0
            machine_type      = "n1-standard-2"
            metadata          = {
                "disable-legacy-endpoints" = "true"
            }
            oauth_scopes      = [
                "https://www.googleapis.com/auth/devstorage.read_only",
                "https://www.googleapis.com/auth/logging.write",
                "https://www.googleapis.com/auth/monitoring",
                "https://www.googleapis.com/auth/service.management.readonly",
                "https://www.googleapis.com/auth/servicecontrol",
                "https://www.googleapis.com/auth/trace.append",
            ]
            preemptible       = false
            service_account   = "default"
            tags              = []
        }
    }

Plan: 0 to add, 1 to change, 0 to destroy.

Do you want to perform these actions?
  Terraform will perform the actions described above.
  Only 'yes' will be accepted to approve.

  Enter a value: yes
```

Terraform says it will do an in place update.

Another thing to note is that we have cluster autoscaler on and initially we set the nodes to 1.  If the cluster is not
doing anything it would have scaled the nodes to 0 and this update will scale the nodes back out to one.  The more
dangerous operation is that if you were using the cluster and this might scale the nodes back down to 1 which might 
interrupt service.  You should check to see what the node count is for this node pool and then set the `initial_node_count`
to that number to keep it the same.

Terraform might timeout during this operation since it can take 4-6 minutes per node:


```
google_container_node_pool.node_nodes: Still modifying... [id=us-central1/gcp-staging/generic-1, 10m50s elapsed]
google_container_node_pool.node_nodes: Still modifying... [id=us-central1/gcp-staging/generic-1, 11m0s elapsed]

Error: Error waiting for updating GKE node pool version: timeout while waiting for state to become 'DONE' (last state: 'RUNNING', timeout: 10m0s)

  on main.tf line 13, in resource "google_container_node_pool" "node_nodes":
  13: resource "google_container_node_pool" "node_nodes" {
```

Looking at the GCP web console at this cluster.  It is still updating the nodes.

I can also see some nodes are updated via `kubectl` and it is rolling through the nodes still:

```yaml
kubectl get nodes                                 
NAME                                      STATUS                        ROLES    AGE   VERSION
gke-gcp-staging-generic-1-0364f8f0-7qkj   Ready                         <none>   15m   v1.13.11-gke.5
gke-gcp-staging-generic-1-998bcf48-58c1   Ready                         <none>   51m   v1.13.11-gke.5
gke-gcp-staging-generic-1-be76e357-npl2   NotReady,SchedulingDisabled   <none>   51m   v1.12.10-gke.15
```

After it finish rolling the nodes the `gcloud` info is updated:

```yaml
gcloud container clusters list                    
NAME         LOCATION     MASTER_VERSION  MASTER_IP      MACHINE_TYPE   NODE_VERSION   NUM_NODES  STATUS
gcp-staging  us-central1  1.13.11-gke.5   34.70.108.205  n1-standard-2  1.13.11-gke.5  3          RUNNING
```

# Summary

This gives us a very nice workflow on updating our cluster through a GitOps workflow.  We update the version
we want in Git and we manually apply this to the cluster.




