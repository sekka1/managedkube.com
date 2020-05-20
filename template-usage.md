To use _layouts/home.html, we should add "animation: true" on the page frontmatter.
Refer to the notes below for more information.

Added on 5/16/2020 4:46:06 AM

---
The root cause of the problem earlier, was not the image link
(I checked the network tab, and they are all downloaded).
But I set the hero section initial visibility to be hidden via CSS.

Then when the javascript load (from _includes/footer.html),
the javascript then, set it to visible again (un-hide them) & start to animate them.
This specific code is only needed for this particular page & I don't want them in any other page
that use _includes./footer.html. So I create a conditional

```
{ % if page.animation % }
 javascript code to set hero section, and benefit icons visibility to visible.
{ % endif % }
```
this line ({% if page.animation %} ) is checking if there is "animation: true" frontmatter in the page, like so:
```
---
layout: home
animation: true
---
```
so if that frontmatter is missing, the javascript code won't be included by jekyll.

The original homepage.html has this "animation: true" frontmatter.
But when extracted, the index.md that refer to "layout\home.html" did not contains  this "animation: true" frontmatter.
so I simply add that frontmatter to index.md to the pull request.

Why do I need to do this?
It's because when doing animation, most of the time there will be "Flash of Un-styled Content" / FOUC.
For the first split second, we'll see the hero content, then it disappeared and start animating.
so the fix is to set the hero content to be initially hidden by CSS, and have the javascript un-hide them.

