<?php
namespace Craft;

class CloudfrontInvalidation_InvalidateElementAction extends BaseElementAction
{
    public function getName()
    {
        return Craft::t('Invalidate CloudFront cache');
    }

    public function performAction(ElementCriteriaModel $criteria)
	{
        $request = craft()->cloudfrontInvalidation->invalidate($criteria->ids());

        if ($request) {
            $count = count($criteria->ids());
            $suffix = $count === 1 ? 'asset' : 'assets';
            $text = sprintf('Invalidated the cache of %s %s', $count, $suffix);
            $this->setMessage($text);
        } else {
            $this->setMessage('No assets were invalidated');
        }

		return true;
	}
}
