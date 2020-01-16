---
layout: post
title: Helm v3 Debugging with cert-manager
categories: Helm v3 Debugging with cert-manager
keywords: Helm v3 Debugging with cert-manager
---
{%- include twitter-button-blank.html -%}

# Helm v2 to v3 migration

Must read doc: [https://helm.sh/blog/helm-3-released/](https://helm.sh/blog/helm-3-released/)

## Download

### Helm v3 binary
[https://github.com/helm/helm/releases](https://github.com/helm/helm/releases)

### The helm-2to3 tool

[https://github.com/helm/helm-2to3#install](https://github.com/helm/helm-2to3#install)

This will help you migrate your local configs over

## Converting an existing chart from v2 to v3

This page documents the changes from v2 to v3: [https://helm.sh/docs/topics/v2_v3_migration/](https://helm.sh/docs/topics/v2_v3_migration/)

## This is based on the ManagedKube/kubernetes-ops repository

Located:  [https://github.com/ManagedKube/kubernetes-ops/tree/master/kubernetes/helm/cert-manager/cert-manager](https://github.com/ManagedKube/kubernetes-ops/tree/master/kubernetes/helm/cert-manager/cert-manager)

All of the values files are in there and the `workdir` is based the root of this repository


# Launching cert-manager

Workdir: `./kubernetes/helm/cert-manager/cert-manager`

Apply cert-manager CRDs before installing (or it will fail)
```bash
kubectl apply --validate=false -f https://raw.githubusercontent.com/jetstack/cert-manager/release-0.11/deploy/manifests/00-crds.yaml
```

Install cert-manager
```bash
kubectl create ns cert-manager
helm upgrade cert-manager --install --namespace cert-manager -f values.yaml ./ --dry-run
```


```bash
helm list --namespace cert-manager
```

Install cert-manager-cluster-issuer

Workdir: `./kubernetes/helm/cert-manager/cert-manager-cluster-issuer`

```bash
helm upgrade cert-manager-cluster-issuer --install --namespace cert-manager -f values.yaml -f environments/gcp-dev/values.yaml ./ --dry-run
```


# Launching cert-manager-cluster-issuer

Workdir: `./kubernetes/helm/cert-manager/cert-manager-cluster-issuer`


Template:
```bash
helm template cert-manager-cluster-issuer --namespace cert-manager -f values.yaml -f ./environments/gcp-dev/values.yaml ./ 
```


```bash
helm upgrade cert-manager-cluster-issuer --install --namespace cert-manager -f values.yaml -f ./environments/gcp-dev/values.yaml ./
```



# Deleting

Seems like there are a few steps to delete.

Doc: https://docs.cert-manager.io/en/master/tasks/uninstall/kubernetes.html


Check for CRDs:
```
kubectl get Issuers,ClusterIssuers,Certificates,CertificateRequests,Orders,Challenges --all-namespaces
```

Delete CRDs:
```bash
kubectl delete -f https://raw.githubusercontent.com/jetstack/cert-manager/release-0.11/deploy/manifests/00-crds.yaml
kubectl delete  APIService v1beta1.webhook.cert-manager.io
```

Delete helm chart:
```bash
helm --namespace cert-manager delete cert-manager
```

# Debugging

Creating the certificate:
```yaml
---
apiVersion: cert-manager.io/v1alpha2
kind: Certificate
metadata:
  name: my-domain
  namespace: my-domain
spec:
  secretName: my-domain-tls
  issuerRef:
    name: issuer-dns01
  dnsNames:
  - my-domain.example.com
```

Checkout out what the status of ths certificate is.

```bash
kubectl -n my-domain get certificate   
NAME       READY   SECRET         AGE
my-domain   True    my-domain-tls   4m20s
```

Then we can describe it:

```bash
kubectl -n my-domain describe certificate my-domain                       
Name:         my-domain
Namespace:    my-domain
Labels:       <none>
Annotations:  kubectl.kubernetes.io/last-applied-configuration:
                {"apiVersion":"cert-manager.io/v1alpha2","kind":"Certificate","metadata":{"annotations":{},"name":"my-domain","namespace":"my-domain"},"spec...
API Version:  cert-manager.io/v1alpha2
Kind:         Certificate
Metadata:
  Creation Timestamp:  2019-11-16T03:45:54Z
  Generation:          1
  Resource Version:    32857679
  Self Link:           /apis/cert-manager.io/v1alpha2/namespaces/my-domain/certificates/my-domain
  UID:                 9738f3f8-0823-11ea-a9f7-42010a660008
Spec:
  Dns Names:
    my-domain.example.com
  Issuer Ref:
    Kind:       ClusterIssuer
    Name:       issuer-dns01
  Secret Name:  my-domain-tls
Status:
  Conditions:
    Last Transition Time:  2019-11-16T03:48:11Z
    Message:               Certificate is up to date and has not expired
    Reason:                Ready
    Status:                True
    Type:                  Ready
  Not After:               2020-02-14T02:48:10Z
Events:
  Type    Reason        Age    From          Message
  ----    ------        ----   ----          -------
  Normal  GeneratedKey  2m48s  cert-manager  Generated a new private key
  Normal  Requested     2m48s  cert-manager  Created new CertificateRequest resource "my-domain-3817824489"
```

From the `Events` it tells us that it created a new `CertificateRequest` named `my-domain-3817824489`

We can describe that CRD to get the status of the request:

```bash
kubectl -n my-domain describe CertificateRequest my-domain-3817824489
Name:         my-domain-3817824489
Namespace:    my-domain
Labels:       <none>
Annotations:  cert-manager.io/certificate-name: my-domain
              cert-manager.io/private-key-secret-name: my-domain-tls
              kubectl.kubernetes.io/last-applied-configuration:
                {"apiVersion":"cert-manager.io/v1alpha2","kind":"Certificate","metadata":{"annotations":{},"name":"my-domain","namespace":"my-domain"},"spec...
API Version:  cert-manager.io/v1alpha2
Kind:         CertificateRequest
Metadata:
  Creation Timestamp:  2019-11-16T03:53:41Z
  Generation:          1
  Owner References:
    API Version:           cert-manager.io/v1alpha2
    Block Owner Deletion:  true
    Controller:            true
    Kind:                  Certificate
    Name:                  my-domain
    UID:                   ad59c00f-0824-11ea-a9f7-42010a660008
  Resource Version:        32859582
  Self Link:               /apis/cert-manager.io/v1alpha2/namespaces/my-domain/certificaterequests/my-domain-3817824489
  UID:                     ad7a55b6-0824-11ea-a9f7-42010a660008
Spec:
  Csr:  LS0tLS1CRUdJTiBDRVJUSUZJQ0FURSBSRVFVR.....
  Issuer Ref:
    Name:  issuer-dns01
Status:
  Conditions:
    Last Transition Time:  2019-11-16T03:53:41Z
    Message:               Referenced "Issuer" not found: issuer.cert-manager.io "issuer-dns01" not found
    Reason:                Pending
    Status:                False
    Type:                  Ready
Events:
  Type    Reason          Age                From          Message
  ----    ------          ----               ----          -------
  Normal  IssuerNotFound  35s (x5 over 35s)  cert-manager  Referenced "Issuer" not found: issuer.cert-manager.io "issuer-dns01" not found
```

There seems to be an error: `Referenced "Issuer" not found: issuer.cert-manager.io "issuer-dns01" not found`

It did not find the `Issuer` named `issuer-dns01`

This puzzled me for a bit and had to read some of the cert-manager's documentation.  The problem is that an `Issuer` is
local to the namespace and we created a `ClusterIssuer`.  A `ClusterIssuer` can serve the entire cluster with one issuer.

I made a mistake on the `Certificate` yaml

I was missing the `kind`:  `kind: ClusterIssuer`

The `Certificate` definition should be:

```yaml
---
apiVersion: cert-manager.io/v1alpha2
kind: Certificate
metadata:
  name: my-domain
  namespace: my-domain
spec:
  secretName: my-domain-tls
  issuerRef:
    kind: ClusterIssuer
    name: issuer-dns01
  dnsNames:
  - my-domain.example.com
```

Lets apply this again:

```bash
kubectl -n my-domain apply -f ~/Downloads/certificate.yaml
certificate.cert-manager.io/my-domain unchanged
```

Describing the cert:

```bash
kubectl -n my-domain describe certificate my-domain         
Name:         my-domain
Namespace:    my-domain
Labels:       <none>
Annotations:  kubectl.kubernetes.io/last-applied-configuration:
                {"apiVersion":"cert-manager.io/v1alpha2","kind":"Certificate","metadata":{"annotations":{},"name":"my-domain","namespace":"my-domain"},"spec...
API Version:  cert-manager.io/v1alpha2
Kind:         Certificate
Metadata:
  Creation Timestamp:  2019-11-16T03:59:32Z
  Generation:          1
  Resource Version:    32861603
  Self Link:           /apis/cert-manager.io/v1alpha2/namespaces/my-domain/certificates/my-domain
  UID:                 7f0228fa-0825-11ea-a9f7-42010a660008
Spec:
  Dns Names:
    my-domain.example.com
  Issuer Ref:
    Name:       issuer-dns01
  Secret Name:  my-domain-tls
Status:
  Conditions:
    Last Transition Time:  2019-11-16T03:59:32Z
    Message:               Waiting for CertificateRequest "my-domain-3817824489" to complete
    Reason:                InProgress
    Status:                False
    Type:                  Ready
Events:
  Type    Reason     Age   From          Message
  ----    ------     ----  ----          -------
  Normal  Requested  2s    cert-manager  Created new CertificateRequest resource "my-domain-3817824489"
```

Describing the `CertificateRequest`

```bash
kubectl -n my-domain describe CertificateRequest my-domain-1647441326
Name:         my-domain-1647441326
Namespace:    my-domain
Labels:       <none>
Annotations:  cert-manager.io/certificate-name: my-domain
              cert-manager.io/private-key-secret-name: my-domain-tls
              kubectl.kubernetes.io/last-applied-configuration:
                {"apiVersion":"cert-manager.io/v1alpha2","kind":"Certificate","metadata":{"annotations":{},"name":"my-domain","namespace":"my-domain"},"spec...
API Version:  cert-manager.io/v1alpha2
Kind:         CertificateRequest
Metadata:
  Creation Timestamp:  2019-11-16T04:00:43Z
  Generation:          1
  Owner References:
    API Version:           cert-manager.io/v1alpha2
    Block Owner Deletion:  true
    Controller:            true
    Kind:                  Certificate
    Name:                  my-domain
    UID:                   7f0228fa-0825-11ea-a9f7-42010a660008
  Resource Version:        32862040
  Self Link:               /apis/cert-manager.io/v1alpha2/namespaces/my-domain/certificaterequests/my-domain-1647441326
  UID:                     a93e9fea-0825-11ea-a9f7-42010a660008
Spec:
  Csr:  LS0tLS1CRUdJTiBDRVJUSUZJQ0FURSBSR
  Issuer Ref:
    Kind:  ClusterIssuer
    Name:  issuer-dns01
Status:
  Certificate:  LS0tLS1CRUdJTiBDRVJUSUZJQ0FURS0tLS0tCk1JSUZiVENDQ
  Conditions:
    Last Transition Time:  2019-11-16T04:00:45Z
    Message:               Certificate fetched from issuer successfully
    Reason:                Issued
    Status:                True
    Type:                  Ready
Events:
  Type    Reason             Age   From          Message
  ----    ------             ----  ----          -------
  Normal  OrderCreated       17s   cert-manager  Created Order resource my-domain/my-domain-1647441326-1246269154
  Normal  CertificateIssued  15s   cert-manager  Certificate fetched from issuer successfully
```

We can also look at the cert-manager's logs to see what is happening:

```bash
kubectl -n cert-manager logs -f cert-manager-67d8bc785c-4w7rf
I1116 03:46:58.236681       1 controller.go:129] cert-manager/controller/challenges "level"=0 "msg"="syncing item" "key"="my-domain/my-domain-1647441326-1246269154-1548188278" 
I1116 03:46:58.236752       1 metrics.go:385] cert-manager/metrics "level"=3 "msg"="incrementing controller sync call count"  "controllerName"="challenges"
I1116 03:46:58.236979       1 dns.go:121] cert-manager/controller/challenges/Check "level"=0 "msg"="checking DNS propagation" "dnsName"="my-domain.example.com" "domain"="my-domain.example.com" "resource_kind"="Challenge" "resource_name"="my-domain-1647441326-1246269154-1548188278" "resource_namespace"="my-domain" "type"="dns-01" "nameservers"=["10.104.0.10:53"]
I1116 03:46:58.241334       1 wait.go:277] Searching fqdn "_acme-challenge.my-domain.example.com." using seed nameservers [10.104.0.10:53]
I1116 03:46:58.241361       1 wait.go:308] Returning cached zone record "example.com." for fqdn "_acme-challenge.my-domain.example.com."
I1116 03:46:58.244250       1 wait.go:295] Returning authoritative nameservers [ns-cloud-d1.googledomains.com., ns-cloud-d2.googledomains.com., ns-cloud-d3.googledomains.com., ns-cloud-d4.googledomains.com.]
I1116 03:46:58.255396       1 wait.go:123] Looking up TXT records for "_acme-challenge.my-domain.example.com."
I1116 03:46:58.356011       1 wait.go:123] Looking up TXT records for "_acme-challenge.my-domain.example.com."
E1116 03:46:58.356112       1 sync.go:184] cert-manager/controller/challenges "msg"="propagation check failed" "error"="DNS record for \"my-domain.example.com\" not yet propagated" "dnsName"="my-domain.example.com" "resource_kind"="Challenge" "resource_name"="my-domain-1647441326-1246269154-1548188278" "resource_namespace"="my-domain" "type"="dns-01" 
I1116 03:46:58.356195       1 controller.go:135] cert-manager/controller/challenges "level"=0 "msg"="finished processing work item" "key"="my-domain/my-domain-1647441326-1246269154-1548188278" 
I1116 03:47:08.356438       1 controller.go:129] cert-manager/controller/challenges "level"=0 "msg"="syncing item" "key"="my-domain/my-domain-1647441326-1246269154-1548188278" 
I1116 03:47:08.358285       1 metrics.go:385] cert-manager/metrics "level"=3 "msg"="incrementing controller sync call count"  "controllerName"="challenges"
I1116 03:47:08.358756       1 dns.go:121] cert-manager/controller/challenges/Check "level"=0 "msg"="checking DNS propagation" "dnsName"="my-domain.example.com" "domain"="my-domain.example.com" "resource_kind"="Challenge" "resource_name"="my-domain-1647441326-1246269154-1548188278" "resource_namespace"="my-domain" "type"="dns-01" "nameservers"=["10.104.0.10:53"]
I1116 03:47:08.365683       1 wait.go:277] Searching fqdn "_acme-challenge.my-domain.example.com." using seed nameservers [10.104.0.10:53]
I1116 03:47:08.365716       1 wait.go:308] Returning cached zone record "example.com." for fqdn "_acme-challenge.my-domain.example.com."
I1116 03:47:08.370115       1 wait.go:295] Returning authoritative nameservers [ns-cloud-d1.googledomains.com., ns-cloud-d2.googledomains.com., ns-cloud-d3.googledomains.com., ns-cloud-d4.googledomains.com.]
I1116 03:47:08.381055       1 wait.go:123] Looking up TXT records for "_acme-challenge.my-domain.example.com."
I1116 03:47:08.482071       1 wait.go:123] Looking up TXT records for "_acme-challenge.my-domain.example.com."
```

There is a lot of different things going on in here.

Cert-manager has access to create DNS records and it looks like it created one and searching for it:

```bash
I1116 03:46:58.241334       1 wait.go:277] Searching fqdn "_acme-challenge.my-domain.example.com." using seed nameservers
```

It is querying it and it looks like it currently can't resolve it and declaring that the DNS has not proprogated yet:

```bash
I1116 03:46:58.356011       1 wait.go:123] Looking up TXT records for "_acme-challenge.my-domain.example.com."
E1116 03:46:58.356112       1 sync.go:184] cert-manager/controller/challenges "msg"="propagation check failed" "error"="DNS record for \"my-domain.example.com\" not yet propagated" "dnsName"="my-domain.example.com" "resource_kind"="Challenge"
```

This makes sense, since adding a DNS entry sometime takes a while to proprogate.

Subsequently it can resolve the DNS and the certificate request is approved by Let's Encrypt.

```bash
I1116 04:00:45.052096       1 sync.go:63] cert-manager/controller/orders "level"=3 "msg"="updated Order resource status successfully" "resource_kind"="Order" "resource_name"="my-domain-1647441326-1246269154" "resource_namespace"="my-domain" 
I1116 04:00:45.135225       1 sync.go:63] cert-manager/controller/orders "level"=3 "msg"="updated Order resource status successfully" "resource_kind"="Order" "resource_name"="my-domain-1647441326-1246269154" "resource_namespace"="my-domain" 
I1116 04:00:45.619888       1 event.go:255] Event(v1.ObjectReference{Kind:"Order", Namespace:"my-domain", Name:"my-domain-1647441326-1246269154", UID:"a9415afa-0825-11ea-a9f7-42010a660008", APIVersion:"acme.cert-manager.io/v1alpha2", ResourceVersion:"32862033", FieldPath:""}): type: 'Normal' reason: 'Complete' Order completed successfully
I1116 04:00:45.631311       1 sync.go:63] cert-manager/controller/orders "level"=3 "msg"="updated Order resource status successfully" "resource_kind"="Order" "resource_name"="my-domain-1647441326-1246269154" "resource_namespace"="my-domain" 
I1116 04:00:45.635073       1 sync.go:159] cert-manager/controller/certificaterequests-issuer-acme/updateStatus "level"=3 "msg"="updating resource due to change in status" "resource_kind"="CertificateRequest" "resource_name"="my-domain-1647441326" "resource_namespace"="my-domain" "diff"=["\"{\\\"conditions\\\":[{\\\"type\\\":\\\"Ready\\\",\\\"status\\\":\\\"False\\\",\\\"lastTransitionTime\\\":\\\"2019-11-16T04:00:43Z\\\",\\\"reason\\\":\\\"Pending\\\",\\\"message\\\":\\\"Waiting on certificate issuance from order my-domain/my-domain-1647441326-1246269154: \\\\\\\"ready\\\\\\\"\\\"}]}\" != \"{\\\"conditions\\\":[{\\\"type\\\":\\\"Ready\\\",\\\"status\\\":\\\"True\\\",\\\"lastTransitionTime\\\":\\\"2019-11-16T04:00:45Z\\\",\\\"reason\\\":\\\"Issued\\\",\\\"message\\\":\\\"Certificate fetched from issuer successfully\\\"}],\\\"certificate\\\":\\\"LS0tLS1CRUdJTiBDRVJUSUZJ\\\"}\""]
I1116 04:00:45.636467       1 event.go:255] Event(v1.ObjectReference{Kind:"CertificateRequest", Namespace:"my-domain", Name:"my-domain-1647441326", UID:"a93e9fea-0825-11ea-a9f7-42010a660008", APIVersion:"cert-manager.io/v1alpha2", ResourceVersion:"32862032", FieldPath:""}): type: 'Normal' reason: 'CertificateIssued' Certificate fetched from issuer successfully
I1116 04:00:45.668671       1 event.go:255] Event(v1.ObjectReference{Kind:"Certificate", Namespace:"my-domain", Name:"my-domain", UID:"7f0228fa-0825-11ea-a9f7-42010a660008", APIVersion:"cert-manager.io/v1alpha2", ResourceVersion:"32862017", FieldPath:""}): type: 'Normal' reason: 'Issued' Certificate issued successfully
```

The cert-manager retrieves the certificate and places it in the secret.




# Migration from Helm v2 to v3

Helm provides a handy tool named [https://github.com/helm/helm-2to3#migrate-helm-v2-releases](2to3) for helping users
migrate currently deployed helm releases from Helm v2 to v3.


My current helm version:
```bash
helm version                             
Client: &version.Version{SemVer:"v2.12.3", GitCommit:"eecf22f77df5f65c823aacd2dbd30ae6c65f186e", GitTreeState:"clean"}
Server: &version.Version{SemVer:"v2.12.3", GitCommit:"eecf22f77df5f65c823aacd2dbd30ae6c65f186e", GitTreeState:"clean"}
```

My current Helm v2 releases:
```bash
helm list                               
NAME                                            REVISION        UPDATED                         STATUS          CHART                           APP VERSION     NAMESPACE           
cert-manager                                    1               Sat Nov 16 07:18:06 2019        DEPLOYED        cert-manager-v0.11.0            v0.11.0         cert-manager       
```

```bash
~/Downloads/helm-v3.0.0-linux-amd64/linux-amd64/helm 2to3 convert --delete-v2-releases cert-manager --dry-run

2019/11/16 07:24:07 NOTE: This is in dry-run mode, the following actions will not be executed.
2019/11/16 07:24:07 Run without --dry-run to take the actions described below:
2019/11/16 07:24:07 
2019/11/16 07:24:07 Release "cert-manager" will be converted from Helm v2 to Helm v3.
2019/11/16 07:24:07 [Helm 3] Release "cert-manager" will be created.
2019/11/16 07:24:07 [Helm 3] ReleaseVersion "cert-manager.v1" will be created.
2019/11/16 07:24:07 [Helm 2] Release "cert-manager" will be deleted.
2019/11/16 07:24:07 [Helm 2] ReleaseVersion "cert-manager.v1" will be deleted.
```

The dry run output looks reasonable.  Lets run it for reals this time with the `--dry-run` flag:

```bash
~/Downloads/helm-v3.0.0-linux-amd64/linux-amd64/helm 2to3 convert --delete-v2-releases cert-manager         
2019/11/16 07:24:50 Release "cert-manager" will be converted from Helm v2 to Helm v3.
2019/11/16 07:24:50 [Helm 3] Release "cert-manager" will be created.
2019/11/16 07:24:50 [Helm 3] ReleaseVersion "cert-manager.v1" will be created.
2019/11/16 07:24:50 [Helm 3] ReleaseVersion "cert-manager.v1" created.
2019/11/16 07:24:50 [Helm 3] Release "cert-manager" created.
2019/11/16 07:24:50 [Helm 2] Release "cert-manager" will be deleted.
2019/11/16 07:24:50 [Helm 2] ReleaseVersion "cert-manager.v1" will be deleted.
2019/11/16 07:24:50 [Helm 2] ReleaseVersion "cert-manager.v1" deleted.
2019/11/16 07:24:50 [Helm 2] Release "cert-manager" deleted.
2019/11/16 07:24:50 Release "cert-manager" was converted successfully from Helm v2 to Helm v3.
```

Lets list it:

```bash
~/Downloads/helm-v3.0.0-linux-amd64/linux-amd64/helm list --all-namespaces
NAME            NAMESPACE       REVISION        UPDATED                                 STATUS          CHART                   APP VERSION
cert-manager    cert-manager    1               2019-11-16 15:18:06.162681463 +0000 UTC deployed        cert-manager-v0.11.0    v0.11.0  
```

Looks like it is in the Helm v3 now.

```bash
kubectl -n cert-manager get pods -o wide
NAME                                       READY   STATUS    RESTARTS   AGE     IP            NODE                                  NOMINATED NODE   READINESS GATES
cert-manager-756d9f56d6-7f7dm              1/1     Running   0          7m57s   10.103.5.56   gke-gcp-dev-generic-1-16723766-xcs5   <none>           <none>
cert-manager-cainjector-74bb68d67c-tlwgn   1/1     Running   0          7m57s   10.103.5.57   gke-gcp-dev-generic-1-16723766-xcs5   <none>           <none>
```

The `cert-manager` pods are all still there with no changes

Running a deploy with Helm v3 to test it out:

```bash
~/Downloads/helm-v3.0.0-linux-amd64/linux-amd64/helm upgrade cert-manager --install --namespace cert-manager -f values.yaml ./         
load.go:112: Warning: Dependencies are handled in Chart.yaml since apiVersion "v2". We recommend migrating dependencies to Chart.yaml.
Error: UPGRADE FAILED: cannot patch "cert-manager-cainjector" with kind Deployment: Deployment.apps "cert-manager-cainjector" is invalid: spec.selector: Invalid value: v1.LabelSelector{MatchLabels:map[string]string{"app":"cainjector", "app.kubernetes.io/instance":"cert-manager", "app.kubernetes.io/managed-by":"Helm", "app.kubernetes.io/name":"cainjector"}, MatchExpressions:[]v1.LabelSelectorRequirement(nil)}: field is immutable && cannot patch "cert-manager" with kind Deployment: Deployment.apps "cert-manager" is invalid: spec.selector: Invalid value: v1.LabelSelector{MatchLabels:map[string]string{"app":"cert-manager", "app.kubernetes.io/instance":"cert-manager", "app.kubernetes.io/managed-by":"Helm", "app.kubernetes.io/name":"cert-manager"}, MatchExpressions:[]v1.LabelSelectorRequirement(nil)}: field is immutable
```

Oops...it fails...ugg

Well...it looks like it is trying to change an immutable field.  Not really sure what we can do about this or if
we really want to spend time figuring out this problem.  In this specific case, it is probably just easier to
delete the release and install it again.  For this deployment, it wouldnt really matter.  If this was an nginx-ingress
then that would matter because deleting it would mean downtime.


I also confirmed that installing the `cert-manager` with Helm v3 and upgrading it works as expected.  So I guess
with all migrations it is not perfect.












