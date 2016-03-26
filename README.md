# CloudFront Invalidation plugin for Craft CMS

Invalidate the CloudFront cache for one or more assets from the toolbar.

## Installation

To install CloudFront invalidation, follow these steps:

1. Download & unzip the file and place the `cloudfrontinvalidation` directory into your `craft/plugins` directory
2.  -OR- do a `git clone https://github.com/sjelfull/cloudfrontinvalidation.git` directly into your `craft/plugins` folder.  You can then update it with `git pull`
3. Install plugin in the Craft Control Panel under Settings > Plugins
4. The plugin folder should be named `cloudfrontinvalidation` for Craft to see it.  GitHub recently started appending `-master` (the branch name) to the name of the folder for zip file downloads.

CloudFront invalidation works on Craft 2.4.x and Craft 2.5.x, and requires PHP 5.4.* or higher.

## Configuring

The plugin will use the S3 key/secret defined in your Asset Source settings, but you need to provide the CloudFront Distribution ID. You can get it from [the AWS Console](https://console.aws.amazon.com/cloudfront/home)

## Roadmap

* Add option to invalidate all assets

## Changelog

### 1.0.0 -- 2016.03.24

* Initial release

Brought to you by [Fred Carlsen](http://sjelfull.no)