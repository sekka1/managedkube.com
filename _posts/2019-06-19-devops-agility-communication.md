---
layout: post
title: Increasing DevOps Agility - follow these 3 rules to improve communication between DevOps and Developers
categories: devops agility communication developers
keywords: kubernetes devops agility communication developers
---

* TOC
{:toc}

# Introduction: Increasing DevOps Agility through Great Communication

Technology companies are working to incorporate agile DevOps principles so we can release and get-to-market faster. I’m writing a blog series about how organizations can accomplish this based on my experiences consulting with many types of companies (different industries, sizes, and points along their cloud journey). 

This first post is going to focus on good communication, which is absolutely critical for an agile organization. Releasing faster means developers and DevOps folks have to get on the same page faster than ever before. No, communication isn't some fancy tool, and yes, it is imperative to get the basics right in a fast-moving organization. We’ve all seen the spin, wasted time, and confusion that happens with bad communication - tasks take twice as long as they should have. Your team is moving fast and want to get things done faster, but this backfires badly when each team member has a different vision of what they need to do, assumptions aren’t aligned, or when the technical details of how things work just don’t line up. How do you ensure that doesn’t happen on your teams? Developers and DevOps groups must communicate well to understand what the other side expects and needs to deliver an application. Without this mutual understanding, it’s impossible to deliver an application, period. 

When you read this blog post, you’ll probably think, ‘I already knew that! I’m not learning anything new.’ Knowing how something should be done and consistently doing so are two different things, though. I see problems from poor communication arise all the time as people feel rushed for time or think that a project is not ‘worth’ the effort of good communication. So, it’s up to you to embody these communication principles 100% of the time, set a good example for the rest of your team, and gently correct your teammates when you see them straying from the principles (and ask that they do the same for you). Think of this blog post as a friendly reminder of good communication principles!

## Strategy 1: Establish good team communication norms. In other words, use existing communication tools well.
You may be thinking, ‘obviously, I know all this’, but it is 100% worth exploring. The communication tools that we use at work are only as effective as our workplace communication norms. If someone talks about a problem they should take offline with one other person at a scrum or someone is asking complicated ambiguous questions in Slack, everyone on the team suffers. Here are the norms that I suggest (which could be different from your company, depending on team size and makeup): 

1. Using Slack
   
   **Do:**
   * Be specific.  Try to avoid ambiguous words such as: it, him/her, they, that.
   * Use threading to streamline channel conversations
   * Use Direct Messages instead of a channel wherever possible - ask yourself the question, “does everyone else needs to hear this?”
   
2. Scrums

   **Do:** 	
   * Have these daily for 15 minutes
   * Use for quick updates and questions
   * Identify who needs to work together to solve a problem and set-up time outside the scrum, even if it’s right afterwards, to discuss
  
   **Don’t:**
   * Use this forum as time for two or three people to hash out a problem together while the whole team watches

3. Emails

   **Do:**
   * If your company prefers to communicate via emails, then use email
   * Use for external communication
   
   **Don’t:**
   * If internal conversations at your company mostly happens in Slack, don’t start an email with a group of people on email.  They might not be as responsive if they don’t check their emails often and it breaks the conversation into two mediums

## Strategy 2: Document, document, document

Seriously - do it! Documentation does require an upfront investment in time, but I promise that this will pay off in the long run. You may think, this project is so simple and doesn’t need documentation. Don’t fall into that trap - projects often become more complicated and even your simple project may not look the same as your teammate’s simple project.  It often looks simple at the time of creation.  Think about 6 months later, are you going to remember the details?  What if someone else has to pick up where you left off?  Are they going to understand the decisions that were made to up to this point?

DevOps must create very detailed step-by-step documentation on how to create the infrastructure. In the beginning, this helps the application developer understand what goes into a production deployment and problems that might arise.  This also will help the DevOps team in the future, after the application has been deployed, when you need to update the application (which you know will happen, even if it was just supposed to be a one-time thing).  Subsequent deployments may not be led by the same person that initially deployed the application and this documentation helps build organizational knowledge.  This document can also guide the work to automate this step.  With the details, it makes writing the automation that much easier in the future.

Remember that documentation doesn’t have to be only words - use diagrams in your documentation. Pictures are worth a thousand words and this holds true in DevOps/developer communications too.  Selecting the correct diagram to describe the requirements is essential.  Be creative and pause for a moment to think if a diagram would make it easier to express what you want to say.  How do you picture the problem you want to explain?  Does it lend itself to a flow diagram?  A ladder diagram?  A network diagram?  I think you get the point.  Pictures can be fun and very expressive.

If you are building a pipeline, it is nice to write up documentation but typically you would create a flow diagram to graph the necessary information. This allows you to see the starting points, the various branches where a document would have a hard time tracking all the various paths the graph can go. I have also found that a flow diagram is well suited to describing a CI/CD pipeline because it makes it very easy to understand and to turn the diagram into code.  As a developer, I don’t have to interpret the meaning of someone’s words.  I can look at a flow diagram and clearly understand the inputs, actions, and outputs at each step.

<center>![flow diagram example](/assets/blog/images/Flow-diagram-medium.png)</center>

Lastly, writing documentation doesn’t mean anything if it disappears into the ether. Have a central location where all the documentation is saved and make sure that it’s organized well so you can find it again!

## Strategy 3: Express everything in Git (and automate wherever possible!)
Expressing everything in Git (code and config) is a powerful way of describing what needs to be done without ambiguity - think of it as documentation in immutable code.  For example, the application developer probably knows how to build the application and run the application locally but doesn’t have a lot of information on how the application will be deployed out to the production environment.  Conversely, the DevOps person most likely knows how the application will or should be deployed out to the production environment but doesn’t know how the application is built.  The challenge is for the developer and DevOps team members to quickly exchange this information while preserving the integrity of the information.

Ideally, each side’s activities should be 100% automated so it is repeatable and will always behave in the same way.  You should be able to build the application from source with a script that is documented on how to perform that activity.  On the infrastructure side, everything should also be automated on how to deploy everything out including creating the databases, queues, S3 buckets, and whatever else the application needs.  

# Conclusion: Great Communication Leads to a More Agile Team

Increasing DevOps Agility means making foundational changes to your organization. This means changing norms, creating repeatable behaviors, and making sure that every team member is on board to this vision. Good communication means that everyone can march quickly along the same path, together, and avoid frustrating and time-losing misunderstandings. Communication happens in a lot of different ways and you’ll need to make sure that you follow good communication principles in all of these cases:
   * Place: virtually, in person, through the phone or video chat
   * Timing: in real-time, asynchronously, forever captured in documentation
   * Method: spoken words, written words, through code and config

So, remember - establish good communication norms, document, and express everything in Git!

# Increasing DevOps Agility with ManagedKube's k8sBot

I created my product, k8sBot, to facilitate better communication about Kubernetes. It's a Slack app that provides your Kubernetes cluster's information with an easy point-and-click user interface. With one click, you can retrieve pod status, get pod logs, and get troubleshooting recommendations.

Anyone, regardless of kubectl access or k8s knowledge, can get meaningful Kubernetes information with our easy point-and-click interface:
![k8sbot workflow - imagepullbackoff pod](/assets/images/k8sbot-database-example.png)

<A HREF="https://managedkube.com">Learn more</a> about k8sBot, a point-and-click interface for Kubernetes in Slack or sign up for a <A HREF="https://managedkube.com/free-k8sbot-trial-signup">free 30 day trial</a>
