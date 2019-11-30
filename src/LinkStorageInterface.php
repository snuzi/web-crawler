<?php

namespace sabri\Extractor;

interface LinkStorageInterface
{
    public function isLinkExtracted(string $link);
    public function saveLink(string $link, string $hostname);
}
