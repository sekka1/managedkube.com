---
layout: post
title: Prometheus Operator ServiceMonitor Troubleshooting
categories: Prometheus Operator ServiceMonitor Troubleshooting
keywords: Prometheus Operator ServiceMonitor Troubleshooting
---
{%- include twitter-button-blank.html -%}

# Debugging Prometheus ServiceMonitors

The way that Prometheus uses `ServiceMonitors` to scrape metrics from your pods might not be so straight
forward for various reasons (you are new to this or like me, I don't deal with this everyday and I just 
simply forget).

Here is a Doc about it that is a must read:

[https://github.com/coreos/prometheus-operator/blob/master/Documentation/user-guides/getting-started.md#related-resources](https://github.com/coreos/prometheus-operator/blob/master/Documentation/user-guides/getting-started.md#related-resources)

The following also walks you through a concrete example of the usage and how everything is linked together.

The final output is being able to go to the Prometheus WebUI and to the `Status->Targets` page and see your
endpoints being scraped per your config.

## How does Prometheus know which ServiceMonitor to use?

The Prometheus Operator is configured pick up `ServiceMonitor`s via a config.  Here is how to
find out what that label is.  Get the prometheus CRD:

```bash
kubectl -n monitoring get prometheus
NAME                             AGE
prometheus-operator-prometheus   23d
```

Describe the CRD to output it's config:

```yaml
kubectl -n monitoring describe prometheus prometheus-operator-prometheus
Name:         prometheus-operator-prometheus
Namespace:    monitoring
Labels:       app=prometheus-operator-prometheus
              chart=prometheus-operator-6.11.0
              heritage=Tiller
              release=prometheus-operator
Annotations:  <none>
API Version:  monitoring.coreos.com/v1
Kind:         Prometheus
Metadata:
  Creation Timestamp:  2019-10-15T00:45:15Z
  Generation:          1
  Resource Version:    87788471
  Self Link:           /apis/monitoring.coreos.com/v1/namespaces/monitoring/prometheuses/prometheus-operator-prometheus
  UID:                 0dd57ecf-eee5-11e9-a08f-02d13237d5b2
Spec:
  Alerting:
    Alertmanagers:
      Name:          prometheus-operator-alertmanager
      Namespace:     monitoring
      Path Prefix:   /
      Port:          web
  Base Image:        quay.io/prometheus/prometheus
  Enable Admin API:  false
  Listen Local:  false
  Log Format:    logfmt
  Log Level:     info
  Paused:        false
 ...
....
...
...
  Resources:
    Requests:
      Memory:    1000Mi
  Retention:     10d
  Route Prefix:  /
  Rule Namespace Selector:
  Rule Selector:
    Match Labels:
      App:      prometheus-operator
      Release:  prometheus-operator
  Security Context:
    Fs Group:            2000
    Run As Non Root:     true
    Run As User:         1000
  Service Account Name:  prometheus-operator-prometheus
  Service Monitor Namespace Selector:
  Service Monitor Selector:
    Match Labels:
      Release:  prometheus-operator                         <---- We are interested in this
  Storage:
    Volume Claim Template:
      Selector:
      Spec:
        Access Modes:
          ReadWriteOnce
        Resources:
          Requests:
            Storage:  10Gi
  Version:            v2.12.0
Events:               <none>
```

The section we are interested in that output is:

```yaml
  Service Monitor Selector:
    Match Labels:
      Release:  prometheus-operator
```

This is the label that has to be matched in the `ServiceMonitor` CRD.


Our `ServiceMonitor` MUST have this label:

```yaml
kubectl -n myapp-gar describe servicemonitors myapp-api-svc-live
Name:         myapp-api-svc-live
Namespace:    myapp-gar
Labels:       app.kubernetes.io/instance=myapp-gar-api-svc-live
              app.kubernetes.io/managed-by=Tiller
              app.kubernetes.io/name=myapp-api-svc-live
              app.selector=myapp-api-svc-live
              helm.sh/chart=master-service-1-0.1.3
              release=prometheus-operator			<------- It has the label
Annotations:  <none>
API Version:  monitoring.coreos.com/v1
Kind:         ServiceMonitor
Metadata:
  Creation Timestamp:  2019-11-07T18:47:26Z
  Generation:          1
  Resource Version:    95279034
  Self Link:           /apis/monitoring.coreos.com/v1/namespaces/myapp-gar/servicemonitors/myapp-api-svc-live
  UID:                 0b020a96-018f-11ea-9ce4-024e93ab8437
Spec:
  Endpoints:
    Interval:  30s
    Path:      /foo
    Port:      prometheus
    Scheme:    http
  Namespace Selector:
    Match Name:
      myapp-gar
  Selector:
    Match Labels:
      app.selector:  myapp-api-v1.1.1
Events:              <none>
```

When this Prometheus (because you can have multiple instances of Prometheus running) sees a `ServiceMonitor`
with this label, it will add it into the list of `ServiceMonitor`s it monitors.


## ServiceMonitor Configuration

The `ServiceMonitor` configuration also has label selectors and it uses these selectors to find the endpoints
to monitor.  From our previous `ServiceMonitor` describe output we saw this section:

```yaml
  Selector:
    Match Labels:
      app.selector:  myapp-api-v1.1.1
```

This section is configuring a label to select from a Kuberenetes Service on which endpoint to include in.

This means that the Kubernetes Service needs to have this label for the Prometheus `ServiceMonitor` to find
the endpoints to monitor.

```yaml
kubectl -n myall-gar describe svc myapp-api-svc-live
Name:              myapp-api-svc-live
Namespace:         myapp-gar
Labels:            app.kubernetes.io/instance=myapp-gar-api-svc-live
                   app.kubernetes.io/managed-by=Tiller
                   app.kubernetes.io/name=myapp-api-svc-live
                   app.selector=myapp-api-v1.1.1                        <------ Needs this lable in the Kube Service
                   helm.sh/chart=master-service-1-0.1.3
```                   

You should now be able to go to `Status->Targets` in the Prometheus WebGUI and see your targets being scraped.



