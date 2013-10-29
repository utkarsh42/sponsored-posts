# Sponsored Posts Plugin
WordPress plugin for inserting sponsored posts into query results.

## Introduction
The Polyjuice Project at Postmedia Network Inc. was an attempt by the Digital Innovation Team to produce website that was fast, responsive, and felt native on mobile devices.  We've decided to release a number of those features we've developed as WordPress plugins.  These plugins, as much as we possibly can, have been stripped of most of their original design and layout material so that they can be incorporated into most any WordPress site with as little effort and interference as possible.

The third plugin to be released is this one, which inserted sponsored posts into the index and archive streams.  It has been further developed to allow for finer control over the insertion process.

## Features

The Sponsored Posts Plugin aims to provide the following features to your WordPress Site/Theme:

* Ability to author sponsored posts (custom post type)
* Ability to automatically inject sponsored posts into index (home), category, and tag archive pages.
* Ability to selelectively inject sponsored posts into any query
* Ability to target sponsored posts by category or tag
* Ability to randomly to inject sponsored posts at a random location or specific locations within your query results.

## Using

The first step is to create some sponsored posts.  Aside from being a custom post type, these posts work just like any other regular post.  In fact, without any template modifications, they will look just like any other post as well.  (We highly recommend you do in fact modify your template to identify sponsored posts, but that's totally up to you.)  

To inject sponsored posts, this plugin introduces a new query var named 'sponsored_posts' which can be set to a single numeric value or an array of values.  Specifying a value of -1 will insert a single sponsored post into a random location.  Specifying positive numbers will inject one or more sponsored posts after specific posts within your query results.  Here are a few examples:

#### Insert single sponsored post into random location

	$query->set( 'sponsored_posts', -1 );

	// or
	
	$query->set( 'sponsored_posts', '-1' );
	
	// or
	
	$query->set( 'sponsored_posts', array( -1 ) );

#### Insert single sponsored post after the 3rd post

	$query->set( 'sponsored_posts', 3 );

	// or

	$query->set( 'sponsored_posts', '3' );

	// or

	$query->set( 'sponsored_posts', array( 3 ) );


#### Insert multiple sponsored post after the 2rd, 4th, and 5th posts

	$query->set( 'sponsored_posts', '2, 4, 5' );

	// or

	$query->set( 'sponsored_posts', array( 3, 4, 5 ) );


## Auto Injection

To automatically inject sponsored posts into your home, cateogry, and tag archive pages without modifying your theme, you can specify the default sponsored_posts value in the WP Admin Dashboard via Settings->Sponsored Posts.  Specify a value of -1 to inject at a random location or a comma separated list of locations to be more specific.  To disable automatic injection, leave the configuration setting blank.

## Contributors

* Donnie Marges
* Keith Benedict
* Andrew Spearin
* Edward de Groot

## License

Copyright (c) 2013 Postmedia Network Inc.

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be included
in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.