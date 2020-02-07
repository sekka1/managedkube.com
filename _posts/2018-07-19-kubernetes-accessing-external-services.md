---
layout: post
title: Kubernetes Access External Services
categories: kubernetes service external
keywords: kubernetes service external
---
{%- include share-bar.html -%}

There are often time when you will need to access an external service in Kubernetes
but still want to use a static name.  

For example, you have an external database like an AWS RDS (MySQL) hosted by Amazon.
In your application you simply want to refer to this database by the name `mysql` and
not the fully URL of the name that AWS assigns to it.

You can add an external service mapping a hostname or by an IP  

### Mapping by a hostname (CNAME)  

You want your application to use the hostname `mysql` which will redirect it to
`mysql–instance1.123456789012.us-east-1.rds.amazonaws.com`.  We can have Kubernetes
set this CNAME.

```yaml
kind: Service
apiVersion: v1
metadata:
  name: mysql
spec:
  type: ExternalName
  externalName: mysql–instance1.123456789012.us-east-1.rds.amazonaws.com
```

Now, if you go to your pod, you can look up `mysql` and see that it points to
`mysql–instance1.123456789012.us-east-1.rds.amazonaws.com`:

```bash
dig mysql
```  

### Mapping a hostname to an IP  

You want your application to use the hostname `mysql` which will redirect to an
IP address.  We can have Kubernetes set this up for us:  

```yaml
---
kind: "Service"
apiVersion: "v1"
metadata:
  name: "mysql"
spec:
  ports:
    -
      name: "mysql"
      protocol: "TCP"
      port: 3306
      targetPort: 3306
      nodePort: 0

---
kind: "Endpoints"
apiVersion: "v1"
metadata:
  name: "mysql"
subsets:
  -
    addresses:
      -
        ip: "1.1.1.1"
    ports:
      -
        port: 3306
        name: "mysql"

```  

In your pod, you can check the connectivity.  This will map the hostname `mysql`
to the IP address `1.1.1.1`.

<!-- Blog footer share -->
{%- include blog-footer-share.html -%}
