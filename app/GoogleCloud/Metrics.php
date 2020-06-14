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
    protected $view = TimeSeriesView::FULL;

    /**
     * Default duration: 1 day
     *
     * @var int
     */
    protected $duration = 60 * 60 * 24;

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
        return sprintf(
            'resource.type="cloud_run_revision" AND metric.type="run.googleapis.com/request_count" AND resource.label.service_name=one_of("%s", "%s")',
            $this->environment->web_service_name,
            $this->environment->worker_service_name
        );
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

    public function alignmentInterval(): int
    {
        // TODO: Make this more configurable
        return $this->duration;
    }

    protected function aggregation(): Aggregation
    {
        $alignmentPeriod = new Duration();
        $alignmentPeriod->setSeconds($this->alignmentInterval());
        $aggregation = new Aggregation();
        $aggregation->setAlignmentPeriod($alignmentPeriod);

        $aligner = Aligner::ALIGN_SUM;
        $aggregation->setPerSeriesAligner($aligner);

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

            $value = $ts->getPoints()[0]->getValue()->getInt64Value();

            $this->results[$service][$ts->getMetric()->getLabels()['response_code_class']] = $value;
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
