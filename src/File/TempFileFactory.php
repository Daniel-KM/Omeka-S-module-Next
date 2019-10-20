<?php
namespace Next\File;

class TempFileFactory extends \Omeka\File\TempFileFactory
{
    public function build()
    {
        // Return \Next\File\TempFile.
        $tempFile = new TempFile($this->tempDir, $this->mediaTypeMap,
            $this->store, $this->thumbnailManager, $this->validator
        );
        $tempFile->setEventManager($this->getEventManager());
        return $tempFile;
    }
}
