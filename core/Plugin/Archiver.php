<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik_PluginArchiver
 */

namespace Piwik\Plugin;

use Piwik\ArchiveProcessor;
use Piwik\Config as PiwikConfig;

/**
 * The base class that should be extended by plugins that archive their own
 * metrics.
 * 
 * ### Examples
 * 
 * **Extending Archiver**
 * 
 *     class MyArchiver extends Archiver
 *     {
 *         public function aggregateDayReport()
 *         {
 *             $logAggregator = $this->getLogAggregator();
 *             
 *             $data = $logAggregator->queryVisitsByDimension(...);
 *             
 *             $dataTable = new DataTable();
 *             $dataTable->addRowsFromSimpleArray($data);
 * 
 *             $archiveProcessor = $this->getProcessor();
 *             $archiveProcessor->insertBlobRecords('MyPlugin_myReport', $dataTable->getSerialized(500));
 *         }
 *         
 *         public function aggregateMultipleReports()
 *         {
 *             $archiveProcessor = $this->getProcessor();
 *             $archiveProcessor->aggregateDataTableRecords('MyPlugin_myReport', 500);
 *         }
 *     }
 * 
 * @api
 */
abstract class Archiver
{
    /**
     * @var \Piwik\ArchiveProcessor
     */
    private $processor;

    /**
     * Constructor.
     * 
     * @param ArchiveProcessor $aggregator The ArchiveProcessor instance sent to the archiving
     *                                     event observer.
     */
    public function __construct(ArchiveProcessor $aggregator)
    {
        $this->maximumRows = PiwikConfig::getInstance()->General['datatable_archiving_maximum_rows_standard'];
        $this->processor = $aggregator;
    }

    /**
     * Archives data for a day period.
     * 
     * Implementations of this method should do more computation intensive activities such
     * as aggregating data across log tables. Since this method only deals w/ data logged for a day,
     * aggregating individual log table rows isn't a problem. Doing this for any larger period,
     * however, would cause performance issues.
     * 
     * Aggregate log table rows using a {@link Piwik\DataAccess\LogAggregator} instance. Get a {@link Piwik\DataAccess\LogAggregator} instance
     * using the {@link getLogAggregator()} method.
     */
    abstract public function aggregateDayReport();

    /**
     * Archives data for a non-day period.
     * 
     * Implementations of this method should only aggregate existing reports of subperiods of the
     * current period. For example, it is more efficient to aggregate reports for each day of a
     * week than to aggregate each log entry of the week.
     * 
     * Use {@link Piwik\ArchiveProcessor::aggregateNumericMetrics()} and {@link Piwik\ArchiveProcessor::aggregateDataTableRecords()}
     * to aggregate archived reports. Get the {@link Piwik\ArchiveProcessor} instance using the {@link getProcessor()}.
     */
    abstract public function aggregateMultipleReports();

    /**
     * Returns an {@link Piwik\ArchiveProcessor} instance that can be used to insert archive data for
     * this period, segment and site.
     * 
     * @return \Piwik\ArchiveProcessor
     */
    protected function getProcessor()
    {
        return $this->processor;
    }

    /**
     * Returns a {@link Piwik\DataAccess\LogAggregator} instance that can be used to aggregate log table rows
     * for this period, segment and site.
     * 
     * @return \Piwik\DataAccess\LogAggregator
     */
    protected function getLogAggregator()
    {
        return $this->getProcessor()->getLogAggregator();
    }
}