<?php

namespace App\Http\Livewire;

use Livewire\Component;

class LogViewer extends Component
{
    public $environment;

    /**
     * Represents which type of logs to show.
     * all: stdout, requests, app
     * app: app
     *
     * @var string
     */
    public $logType = 'all';

    /**
     * Represents which service to show logs for
     * web, worker
     *
     * @var string
     */
    public $service = 'web';

    /**
     * Automatically update the query string each time a model is changed.
     *
     * @var array
     */
    protected $updatesQueryString = ['logType', 'service'];

    public function mount($environment)
    {
        $this->environment = $environment;
        $this->logType = request()->query('logType', $this->logType);
        $this->service = request()->query('service', $this->service);
    }

    /**
     * Get the logs to display
     *
     * @return array
     */
    public function logs(): array
    {
        $logs = [];
        $rawLogs = $this->environment->logs($this->service, $this->logType);

        foreach ($rawLogs as $log) {
            $text = '';



            $logs[] = [
                'timestamp' => $log['timestamp'],
                'text' => $text,
            ];
        }

        return $logs;
    }

    public function render()
    {
        return view('livewire.log-viewer', [
            'logs' => $this->logs(),
        ]);
    }

    /**
     * Get a text representation of a log, based on the properties it contains.
     *
     * @param array $log
     * @return string
     */
    protected function getTextFromLog($log): string
    {
        if (isset($log['textPayload'])) {
            return $log['textPayload'];
        } elseif (isset($log['jsonPayload'])) {
            return $log['jsonPayload']['message'];
        } elseif (isset($log['httpRequest'])) {
            return sprintf(
                "%s %s %s",
                $log['httpRequest']['requestMethod'],
                $log['httpRequest']['status'],
                $log['httpRequest']['requestUrl']
            );
        }
    }
}
