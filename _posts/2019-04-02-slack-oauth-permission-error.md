---
layout: post
title: Slack Oauth Permission Error
categories: Slack Oauth Permission Error
keywords: Slack Oauth Permission Error
---
{%- include share-bar.html -%}

We recently updated the Slack Oauth permissions for our Slack application.  This
update was to remove the `users:read.email` permissions.  The interesting thing is that
when you remove a permission, you have to remove this permission ask in the
Slack button for users to add this application to their Slack also.  

If not, your users will see the following message when trying to click on this button.

![k8sbot logs](/assets/blog/images/slack-oauth-permission-rejected.png)

```
Uh oh, k8sbot has run into a problem

Our apologies, but it looks like something has gone awry.  Don't
worry, it's not your fault, but you won't be able to install k8sbot
right now.  Give me the nerdy details

Unapproved permissions requested
user:read.email
```

The fix is simple.  After you have updated the Oauth permissions in your Slack
app.  Copy the `Install to Slack` button again and use that.  Or in the button
remove the permission(s) you have removed.




{%- include blurb-consulting.md -%}

<!-- Blog footer share -->
{%- include share-bar.html -%}
