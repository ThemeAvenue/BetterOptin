BetterOptin: Stunning Opt-In Popups for WordPress
============

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ThemeAvenue/BetterOptin/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ThemeAvenue/BetterOptin/?branch=master)

Ok. Popups usually convert better. But how annoying are they? Do you know how much you piss your visitors off? Actually yes, you do. How much does it annoy YOU when a site throws a popup at your face as soon as you land on it?

You know what? We're just like you. We don't like being harassed when we browse the internet. That's why we developed a new WordPress popup plugin: BetterOptin, a better way to use popups. And it converts even better!

## Why is BetterOptin Better?

Not only because of its name obviously ;) Let me explain how it works and you'll understand why it's better right away...

### The Problem

BetterOptin is based on a simple observation: people don't like popups thrown at their face for no apparent reason.

Seriously, let's say you land on a site after a Google search. Maybe you'll like the site. Maybe you'll enjoy reading the content and you'll become a subscriber. But what happens with traditional popups is you're asked to subscribe / like / download this / do that before even reading a sentence!

Some popup systems tried to change that by adding a delay before triggering the popup. But really? Will you be happier if a popup shows up in the middle of your reading? I don't think so.

With BetterOptin, you stay away from all these bad impressions given to visitors and you keep your brand image unharmed.

### Our Solution

![BetterOptin Exit Intent](http://i.imgur.com/iKbk9ON.png)

With BetterOptin, you only ask people to complete an action (be it subscribing to your newsletter, downloading your e-book or else) at an appropriate moment. And this particular moment is when the visitor is about to leave without having completed the action first. This is called exit-intent.

BetterOptin is capable of detecting when the visitors leaves your site and will trigger the popup just then.

## How Do I Create Popups?

Creating a popup can take as little as a minute, but it can also take a lot more time if you want to fine tune it.

The creation process is as follows:

1. Choose a template
2. Customize the settings
3. Choose where to display the popup
4. Customize the popup

## Technical Mumbo-Jumbo

#### Templating System

You know HTML? You'll love what you're about to hear...

One of the great advantages of BetterOptin is that we built it with a very, very simple templating system for popups. The plugin comes with 8 templates, but you can create your own. It only requires HTML. Just code the template as you would do for any HTML page, and add our "template tags" in data attributes. This will tell the customizer what he can or cannot customize.

#### Custom Table & Statistics API

In order to provide valuable insights without slowing down your site, BetterOptin uses a custom database table to save all the "hits".

As working with SQL queries is not safe if done the wrong way, we developed an API within the plugin to let you easily and safely interact with the custom table. No need to write any SQL queries, our helper functions will do it for you. Of course the API uses the WordPress $wpdb object when working with the database.

#### Titan Framework

BetterOptin relies on Titan Framework to handle plugin options. This framework is built by expert WordPress developers and well maintained on GitHub.

#### Addons Compatibility

The beauty of BetterOptin (and a painful part for the developers ;)) is that it's built around a flexible core, allowing for new features to be added as extensions of the plugin. This means that we will develop new features in the future, and, if you have the skills, you can do it too.

### Roadmap

Wanna know what's coming next in BetterOptin? [Check out the roadmap](https://trello.com/b/mWrd0wUg). You can also vote for the ideas you like best.

***

## Developer Guide

If you're a deveveloper, then you can get started right away:

```
git clone git@github.com:ThemeAvenue/BetterOptin.git && cd BetterOptin && composer install && npm install && gulp
```

** What this does is: **

1. Clone the repository
2. Install Composer dependencies
3. Install Gulp dependencies
4. Run Gulp to watch for file changes

### Requirements

You need to have the following tools installed locally:

* [Composer](https://getcomposer.org/)
* [NodeJS](http://nodejs.org/)
* [Gulp](http://gulpjs.com/)