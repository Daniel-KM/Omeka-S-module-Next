<?php
namespace Next\File;

use XMLReader;

class TempFile extends \Omeka\File\TempFile
{
    /**
     * Map the output from xml checker and standard xml media types.
     *
     * Xml media types are generally not registered, so the unregistered tree
     * (prefix "x") is used, except when the format is public, in which case the
     * vendor tree is used (prefix "vnd").
     *
     * @var array
     */
    protected $xmlMediaTypes = [
        'application/xml' => 'application/xml',
        'text/xml' => 'text/xml',
        // Common (if not managed by fileinfo).
        'http://www.w3.org/2000/svg' => 'image/svg+xml',
        'application/vnd.oasis.opendocument.presentation' => 'application/vnd.oasis.opendocument.presentation-flat-xml',
        'application/vnd.oasis.opendocument.spreadsheet' => 'application/vnd.oasis.opendocument.spreadsheet-flat-xml',
        'application/vnd.oasis.opendocument.text' => 'application/vnd.oasis.opendocument.text-flat-xml',
        'http://www.w3.org/1999/xhtml' => 'application/xhtml+xml',
        'http://www.w3.org/2005/Atom' => 'application/atom+xml',
        'http://purl.org/rss/1.0/' => 'application/rss+xml',
        // Common in library and culture world.
        'http://bibnum.bnf.fr/ns/alto_prod' => 'application/vnd.alto+xml',
        'http://bibnum.bnf.fr/ns/refNum' => 'application/vnd.bnf.refnum+xml',
        'http://www.iccu.sbn.it/metaAG1.pdf' => 'application/vnd.iccu.mag+xml',
        'http://www.loc.gov/MARC21/slim' => 'application/vnd.marc21+xml',
        'http://www.loc.gov/METS/' => 'application/vnd.mets+xml',
        'http://www.loc.gov/mods/' => 'application/vnd.mods+xml',
        'http://www.loc.gov/standards/alto/ns-v3#' => 'application/vnd.alto+xml',
        'http://www.music-encoding.org/ns/mei' => 'application/vnd.mei+xml',
        'http://www.music-encoding.org/schema/3.0.0/mei-all.rng' => 'application/vnd.mei+xml',
        // See https://github.com/w3c/musicxml/blob/gh-pages/schema/musicxml.xsd
        'http://www.musicxml.org/xsd/MusicXML' => 'application/vnd.recordare.musicxml',
        'http://www.openarchives.org/OAI/2.0/' => 'application/vnd.openarchives.oai-pmh+xml',
        'http://www.openarchives.org/OAI/2.0/static-repository' => 'application/vnd.openarchives.oai-pmh+xml',
        'http://www.tei-c.org/ns/1.0' => 'application/vnd.tei+xml',
        // Omeka should support itself.
        'http://omeka.org/schemas/omeka-xml/v1' => 'text/vnd.omeka+xml',
        'http://omeka.org/schemas/omeka-xml/v2' => 'text/vnd.omeka+xml',
        'http://omeka.org/schemas/omeka-xml/v3' => 'text/vnd.omeka+xml',
        'http://omeka.org/schemas/omeka-xml/v4' => 'text/vnd.omeka+xml',
        'http://omeka.org/schemas/omeka-xml/v5' => 'text/vnd.omeka+xml',
        // Doctype and root elements in case there is no namespace.
        'alto' => 'application/vnd.alto+xml',
        'ead' => 'application/vnd.ead+xml',
        'feed' => 'application/atom+xml',
        'html' => 'text/html',
        'mag' => 'application/vnd.iccu.mag+xml',
        'mei' => 'application/vnd.mei+xml',
        'mets' => 'application/vnd.mets+xml',
        'mods' => 'application/vnd.mods+xml',
        'pdf2xml' => 'application/vnd.pdf2xml+xml',
        'refNum' => 'application/vnd.bnf.refnum+xml',
        'rss' => 'application/rss+xml',
        'score-partwise' => 'application/vnd.recordare.musicxml',
        'svg' => 'image/svg+xml',
        'TEI' => 'application/vnd.tei+xml',
        // 'collection' => 'application/vnd.marc21+xml',
    ];

    public function getMediaType()
    {
        if (isset($this->mediaType)) {
            return $this->mediaType;
        }

        parent::getMediaType();

        if ($this->mediaType === 'text/xml' || $this->mediaType === 'application/xml') {
            $this->mediaType = $this->getMediaTypeXml() ?: $this->mediaType;
        }
        if ($this->mediaType === 'application/zip') {
            $this->mediaType = $this->getMediaTypeZip() ?: $this->mediaType;
        }
        return $this->mediaType;
    }

    /**
     * Extract a more precise xml media type when possible.
     *
     * @return string
     */
    protected function getMediaTypeXml()
    {
        $filepath = $this->getTempPath();

        libxml_clear_errors();

        $reader = new XMLReader();
        if (!$reader->open($filepath)) {
            // TODO The logger is not available.
            return null;
        }

        $type = null;

        // Don't output error in case of a badly formatted file since there is no logger.
        while (@$reader->read()) {
            if ($reader->nodeType === XMLReader::DOC_TYPE) {
                $type = $reader->name;
                break;
            }

            if ($reader->nodeType === XMLReader::PI
                && !in_array($reader->name, ['xml-stylesheet', 'oxygen'])
            ) {
                $matches = [];
                if (preg_match('~href="(.+?)"~mi', $reader->value, $matches)) {
                    $type = $matches[1];
                    break;
                }
            }

            if ($reader->nodeType === XMLReader::ELEMENT) {
                if ($reader->namespaceURI === 'urn:oasis:names:tc:opendocument:xmlns:office:1.0') {
                    $type = $reader->getAttributeNs('mimetype', 'urn:oasis:names:tc:opendocument:xmlns:office:1.0');
                } else {
                    $type = $reader->namespaceURI ?: $reader->getAttribute('xmlns');
                }
                if (!$type) {
                    $type = $reader->name;
                }
                break;
            }
        }

        $reader->close();

        /*
        // TODO The logger is not available.
        $error = libxml_get_last_error();
        if ($error) {
            $message = new \Omeka\Stdlib\PsrMessage(
                'Error level {level}, code {code}, for file "{file}", line {line}, column {column}: {message}',
                ['level' => $error->level, 'code' => $error->code, 'file' => $error->file, 'line' => $error->line, 'column' => $error->column, 'message' => $error->message]
            );
            $this->logger->err($message);
        }
        */

        return isset($this->xmlMediaTypes[$type])
            ? $this->xmlMediaTypes[$type]
            : null;
    }

    /**
     * Extract a more precise zipped media type when possible.
     *
     * In many cases, the media type is saved in a uncompressed file "mimetype"
     * at the beginning of the zip file. If present, get it.
     *
     * @return string
     */
    protected function getMediaTypeZip()
    {
        $filepath = $this->getTempPath();
        $handle = fopen($filepath, 'rb');
        $contents = fread($handle, 256);
        fclose($handle);
        return substr($contents, 30, 8) === 'mimetype'
            ? substr($contents, 38, strpos($contents, 'PK', 38) - 38)
            : null;
    }
}
