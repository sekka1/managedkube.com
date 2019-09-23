---
layout: post
title: Kubernetes Nginx Ingress SSL Cipher ELB Hanging
categories: Kubernetes Nginx Ingress SSL Cipher ELB Hanging
keywords: Kubernetes Nginx Ingress SSL Cipher ELB Hanging
---

Helm chart: https://github.com/helm/charts/tree/master/stable/nginx-ingress

If you are using the Nginx-ingress Helm chart on an AWS Kubernetes cluster and
want encryption between the AWS ELB and the Nginx ingress you might run into an
error where the http call hangs between the ELB and the Nginx.  

Topology:
```
Internet<-->(HTTPS offload here with AWS ACM Certs)ELB<--->(HTTPS - self signed)Nginx Ingress
```

Config that produces this topology:
```yaml
controller:
  ## Name of the ingress class to route through this controller
  ##
  # ingressClass: nginx-external

  service:
    enableHttp: true
    enableHttps: true
    targetPorts:
      http: http
      https: https

    annotations:
      service.beta.kubernetes.io/aws-load-balancer-connection-idle-timeout: "60"
      service.beta.kubernetes.io/aws-load-balancer-ssl-ports: "443"
      service.beta.kubernetes.io/aws-load-balancer-backend-protocol: "https"
      # service.beta.kubernetes.io/aws-load-balancer-ssl-cert: "arn:aws:acm:us-east-1:xxxxx:certificate/596fcfd0-419a-45b2-b766-xxxxx"
```

When you try to reach the Nginx from the ELB say with a cURL, the call will hang and
then eventually time out.  That is because there is an SSL cipher issue.  The cipher
that the ELB is willing to use is not the same as the ones Nginx is willing to use.

If you do a cURL call you will get a `408 REQUEST_TIMEOUT`:
```
$ curl -v https://a70749687774511e9ad000aefbb298f7-1467484959.us-east-1.elb.amazonaws.com -k
* Rebuilt URL to: https://a70749687774511e9ad000aefbb298f7-1467484959.us-east-1.elb.amazonaws.com/
*   Trying 54.165.37.154...
* TCP_NODELAY set
* Connected to a70749687774511e9ad000aefbb298f7-1467484959.us-east-1.elb.amazonaws.com (54.165.37.154) port 443 (#0)
* ALPN, offering h2
* ALPN, offering http/1.1
* successfully set certificate verify locations:
*   CAfile: /etc/ssl/certs/ca-certificates.crt
  CApath: /etc/ssl/certs
* TLSv1.2 (OUT), TLS handshake, Client hello (1):
* TLSv1.2 (IN), TLS handshake, Server hello (2):
* TLSv1.2 (IN), TLS handshake, Certificate (11):
* TLSv1.2 (IN), TLS handshake, Server key exchange (12):
* TLSv1.2 (IN), TLS handshake, Server finished (14):
* TLSv1.2 (OUT), TLS handshake, Client key exchange (16):
* TLSv1.2 (OUT), TLS change cipher, Client hello (1):
* TLSv1.2 (OUT), TLS handshake, Finished (20):
* TLSv1.2 (IN), TLS handshake, Finished (20):
* SSL connection using TLSv1.2 / ECDHE-RSA-AES128-GCM-SHA256
* ALPN, server did not agree to a protocol
* Server certificate:
*  subject: CN=*.dev.us-east-1.healthtap.com
*  start date: May 13 00:00:00 2019 GMT
*  expire date: Jun 13 12:00:00 2020 GMT
*  issuer: C=US; O=Amazon; OU=Server CA 1B; CN=Amazon
*  SSL certificate verify ok.
> GET / HTTP/1.1
> Host: a70749687774511e9ad000aefbb298f7-1467484959.us-east-1.elb.amazonaws.com
> User-Agent: curl/7.58.0
> Accept: */*
>
< HTTP/1.1 408 REQUEST_TIMEOUT
< Content-Length:0
< Connection: Close
<
* Closing connection 0
* TLSv1.2 (OUT), TLS alert, Client hello (1):
```

To fix this, you need to tell nginx to use some additional ciphers:
```yaml
controller:
  ## Name of the ingress class to route through this controller
  ##
  # ingressClass: nginx-external

  service:
    enableHttp: true
    enableHttps: true
    targetPorts:
      http: http
      https: https

    annotations:
      service.beta.kubernetes.io/aws-load-balancer-connection-idle-timeout: "60"
      service.beta.kubernetes.io/aws-load-balancer-ssl-ports: "443"
      service.beta.kubernetes.io/aws-load-balancer-backend-protocol: "https"
      # service.beta.kubernetes.io/aws-load-balancer-ssl-cert: "arn:aws:acm:us-east-1:227450484680:certificate/596fcfd0-419a-45b2-b766-82e2c7db0581"

  config:
    # https://kubernetes.github.io/ingress-nginx/user-guide/nginx-configuration/configmap/#ssl-ciphers
    ssl-ciphers: "ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-AES256-GCM-SHA384:DHE-RSA-AES128-GCM-SHA256:DHE-DSS-AES128-GCM-SHA256:kEDH+AESGCM:ECDHE-RSA-AES128-SHA256:ECDHE-ECDSA-AES128-SHA256:ECDHE-RSA-AES128-SHA:ECDHE-ECDSA-AES128-SHA:ECDHE-RSA-AES256-SHA384:ECDHE-ECDSA-AES256-SHA384:ECDHE-RSA-AES256-SHA:ECDHE-ECDSA-AES256-SHA:DHE-RSA-AES128-SHA256:DHE-RSA-AES128-SHA:DHE-DSS-AES128-SHA256:DHE-RSA-AES256-SHA256:DHE-DSS-AES256-SHA:DHE-RSA-AES256-SHA:AES128-GCM-SHA256:AES256-GCM-SHA384:AES128-SHA256:AES256-SHA256:AES128-SHA:AES256-SHA:AES:CAMELLIA:DES-CBC3-SHA:!aNULL:!eNULL:!EXPORT:!DES:!RC4:!MD5:!PSK:!aECDH:!EDH-DSS-DES-CBC3-SHA:!EDH-RSA-DES-CBC3-SHA:!KRB5-DES-CBC3-SHA"
```

Now when you cURL again through the ELB, the call makes it through to the Nginx.

{%- include blurb-consulting.md -%}
