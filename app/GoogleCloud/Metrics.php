<?php

namespace App\GoogleCloud;

use App\Environment;
use Google\Cloud\Monitoring\V3\Aggregation;
use Google\Cloud\Monitoring\V3\Aggregation\Aligner;
use Google\Cloud\Monitoring\V3\ListTimeSeriesRequest\TimeSeriesView;
use Google\Cloud\Monitoring\V3\MetricServiceClient;
use Google\Cloud\Monitoring\V3\TimeInterval;
use Google\Protobuf\Duration;
use Google\Protobuf\Timestamp;

/**
 * Fetch the metrics for a given environment on Google Cloud Run.
 */
class Metrics
{
    /**
     * @var \App\Environment
     */
    protected $environment;

    /**
     * @var MetricServiceClient
     */
    protected $client;

    /**
     * @var int
     */
    protected $view;

    /**
     * @var int
     */
    protected $duration;

    /**
     * Results
     *
     * @var array
     *
     * ```
     * [
     *     'laravel-example-production' => [
     *         '2xx' => 30,
     *         '3xx' => 1,
     *     ],
     *     ...
     * ]
     * ```
     */
    public $results;

    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
        $this->client = new MetricServiceClient([
            'credentials' => $this->environment->serviceAccountJson(),
            'projectId' => $this->environment->projectId(),
        ]);

        $this->view = TimeSeriesView::FULL;

        // Default duration is 1 day
        $this->duration = 60 * 60 * 24;
    }

    /**
     * Get metrics for a given duration, in seconds.
     *
     * @param int $duration Duration in seconds
     */
    public function for(int $duration)
    {
        $this->duration = $duration;

        return $this;
    }

    protected function filter(): string
    {
        return 'resource.type="cloud_run_revision" AND metric.type="run.googleapis.com/request_count"';
    }

    protected function interval(): TimeInterval
    {
        $startTime = new Timestamp();
        $startTime->setSeconds(time() - $this->duration);
        $endTime = new Timestamp();
        $endTime->setSeconds(time());

        $interval = new TimeInterval();
        $interval->setStartTime($startTime);
        $interval->setEndTime($endTime);

        return $interval;
    }

    protected function aggregation(): Aggregation
    {
        $alignmentPeriod = new Duration();
        $alignmentPeriod->setSeconds($this->duration);
        $aggregation = new Aggregation();
        $aggregation->setAlignmentPeriod($alignmentPeriod);

        // TODO: Make this configurable.
        $aggregation->setPerSeriesAligner(Aligner::ALIGN_SUM);

        return $aggregation;
    }

    protected function fetchResults()
    {
        if ($this->results) return;

        $results = $this->client->listTimeSeries(
            "projects/{$this->environment->projectId()}",
            $this->filter(),
            $this->interval(),
            $this->view,
            ['aggregation' => $this->aggregation()]
        );

        foreach ($results->iterateAllElements() as $ts) {
            $service = $ts->getResource()->getLabels()['service_name'];

            if (empty($this->results[$service])) {
                $this->results[$service] = [];
            }

            $this->results[$service][$ts->getMetric()->getLabels()['response_code_class']] = $ts->getPoints()[0]->getValue()->getInt64Value();
        }
    }

    public function getWebResults(): array
    {
        $this->fetchResults();

        return $this->results[$this->environment->web_service_name] ?? [];
    }

    public function getWorkerResults(): array
    {
        $this->fetchResults();

        return $this->results[$this->environment->worker_service_name] ?? [];
    }

    public function getTotalRequests(): int
    {
        return $this->getWebRequests() + $this->getWorkerRequests();
    }

    public function getWebRequests(): int
    {
        return collect($this->getWebResults())
            ->sum(fn ($num) => $num);
    }

    public function getWorkerRequests(): int
    {
        return collect($this->getWorkerResults())
            ->sum(fn ($num) => $num);
    }
}
