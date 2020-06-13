<?php

namespace App\Http\Livewire;

use App\Environment;
use App\GoogleCloud\Metrics;
use Livewire\Component;

class EnvironmentMetrics extends Component
{
    public $enviroment;

    public $ready = false;

    public function mount(Environment $environment)
    {
        $this->enviroment = $environment;
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

        if ($this->ready) {
            $metrics = new Metrics($this->enviroment);

            $metrics->for(Metrics::DURATION_HOUR);

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