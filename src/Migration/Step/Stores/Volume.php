<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Stores;

use Migration\App\Step\AbstractVolume;
use Migration\ResourceModel;
use Migration\App\ProgressBar;
use Migration\Logger\Logger;
use Migration\Step\Stores\Model\DocumentsList;

/**
 * Class Volume
 */
class Volume extends AbstractVolume
{
    /**
     * @var ResourceModel\Source
     */
    private $source;

    /**
     * @var ResourceModel\Destination
     */
    private $destination;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    private $progress;

    /**
     * @var DocumentsList
     */
    private $documentsList;

    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param DocumentsList $documentsList
     * @param Logger $logger
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        DocumentsList $documentsList,
        Logger $logger
    ) {
        $this->progress = $progress;
        $this->source = $source;
        $this->destination = $destination;
        $this->documentsList = $documentsList;
        parent::__construct($logger);
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount());
        foreach ($this->documentsList->getDocumentsMap() as $sourceDocumentName => $destinationDocumentName) {
            $this->progress->advance();
            $sourceRecordsCount = $this->source->getRecordsCount($sourceDocumentName);
            $destinationRecordsCount = $this->destination->getRecordsCount($destinationDocumentName);
            if ($sourceRecordsCount != $destinationRecordsCount) {
                $this->errors[] = 'Mismatch of entities in the document: ' . $destinationDocumentName;
            }
        }
        $this->progress->finish();
        return $this->checkForErrors(Logger::ERROR);
    }

    /**
     * Get iterations count for step
     *
     * @return int
     */
    private function getIterationsCount()
    {
        return count($this->documentsList->getDocumentsMap());
    }
}
