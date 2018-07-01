<?php
namespace Next\View\Helper;

use Omeka\Api\Representation\MediaRepresentation;
use Zend\View\Helper\AbstractHelper;

class MediaSize extends AbstractHelper
{
    /**
     * @string
     */
    protected $basepath;

    public function __construct($basepath)
    {
        $this->basepath = $basepath;
    }

    /**
     * Get the file size of a media.
     *
     * @param MediaRepresentation $media
     * @return int
     */
    public function __invoke(MediaRepresentation $media)
    {
        if (version_compare(\Omeka\Module::VERSION, '1.2', '>=')) {
            return $media->size();
        }

        if (!$media->hasOriginal() || $media->renderer() !== 'file') {
            return 0;
        }

        $filepath = $this->basepath . '/original/' . $media->filename();
        return file_exists($filepath) ? filesize($filepath) : 0;
    }
}
