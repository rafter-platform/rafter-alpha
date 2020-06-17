<?php

namespace App\Http\Livewire;

use App\Environment;
use App\GoogleCloud\Metrics;
use Livewire\Component;

class EnvironmentMetrics extends Component
{
    public $enviroment;

    public $ready = false;

    public $durations = [
        'hour' => 60 * 60,
        'day' => 60 * 60 * 24,
        'week' => 60 * 60 * 24 * 7,
    ];

    public $duration = 'hour';

    public function mount(Environment $environment)
    {
        $this->enviroment = $environment;
    }

    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    public function loadMetrics()
    {
        $this->ready = true;
    }

    public function render()
    {
        $requestCounts = [
            'total' => '-',
            'web' => '-',
            'worker' => '-',
        ];

        if ($this->ready && $this->enviroment->hasBeenDeployedSuccessfully()) {
            $metrics = new Metrics($this->enviroment);

            $metrics->for($this->durations[$this->duration]);

            $requestCounts['total'] = number_format($metrics->getTotalRequests());
            $requestCounts['web'] = number_format($metrics->getWebRequests());
            $requestCounts['worker'] = number_format($metrics->getWorkerRequests());
        }

        return view('livewire.environment-metrics', [
            'totalRequests' => $requestCounts['total'],
            'webRequests' => $requestCounts['web'],
            'workerRequests' => $requestCounts['worker'],
        ]);
    }
}
