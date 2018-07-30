---
layout: post
title: Creating a Prometheus Alert
categories: kubernetes prometheus alert
keywords: kubernetes prometheus alert
---

1) Go to prometheus graph

2) Select a metric

3) Now go to the graph view

    (picture)

  This is showing you what will alert.  If there are any lines over whatever
  time period you have set, these will alarm

  Now, tighten the query rules so that it only shows you items in the graph
  when you really want it to notify you.



## My alert is too chatty

1) Go to the `alert` menu

2) Expand the alarm that is too chatty

3) Click on the `expr`.  This is the query that is used to alarm.

4) Click on the graph view

    (show picture)

5) Edit the expression so that it reduces what you dont need
