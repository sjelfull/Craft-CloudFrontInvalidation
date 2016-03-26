<?php
/**
 * CloudFront invalidation plugin for Craft CMS
 *
 * CloudfrontInvalidation Service
 *
 * @author    Fred Carlsen
 * @copyright Copyright (c) 2016 Fred Carlsen
 * @link      http://sjelfull.no
 * @package   CloudfrontInvalidation
 * @since     1.0.0
 */

namespace Craft;

class CloudfrontInvalidationService extends BaseApplicationComponent
{
    /**
     * This function can literally be anything you want, and you can have as many service functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     craft()->cloudfrontInvalidation->invalidate()
     */
    public function invalidate($criteraIds)
    {
        $objects = [];

        foreach($criteraIds as $id) {
            // Get asset
            $asset = craft()->assets->getFileById($id);

            if ($asset) {
                $sourceType = craft()->assetSources->getSourceTypeById($asset->sourceId);
                $isS3 = $sourceType->getClassHandle() === 'S3';

                // Skip if not S3
                if (! $isS3) {
                    continue;
                }

                $baseUrl = $sourceType->getBaseUrl();
                $path = $asset->getPath();

                $source     = $asset->getSource();
                $parsedUrl = parse_url($asset->getUrl());
                $fileName = $asset->filename;

                $objects[] = [
                    'baseUrl' => $baseUrl,
                    'host' => $parsedUrl['host'],
                    'path' => $path,
                    'fileName' => $fileName,
                    'url' => $asset->getUrl(),
                    'key' => $source->settings['keyId'],
                    'secret' => $source->settings['secret'],
                ];
            }
        }

        if (count($objects) > 0) {
            $s3Key = $objects[0]['key'];
            $s3Secret = $objects[0]['secret'];

            // Reduce object array to just the relative path
            array_walk($objects, function( &$val, $key ) {
                $val = '/' . $val['path'];
            });

            // Create invalidation request
            $request = craft()->cloudfrontInvalidation_aPI->invalidate($s3Key, $s3Secret, $objects);

            return $request;
        }

        return false;
    }

}
