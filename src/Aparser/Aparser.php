<?php
/**
 * Created by Slava Basko <basko.slava@gmail.com>
 * Date: 3/10/16
 */

namespace Aparser;


use Aparser\Exceptions\CurlResponseException;

class Aparser
{
    /**
     * @var string $password The password for the A-Parser API
     */
    protected $password;

    /**
     * @var string $host The base URL to use for calls to the API
     */
    protected $host;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param string $host
     * @param string $password
     * @param array $options
     */
    public function __construct($host, $password, $options = [])
    {
        $this->setHost($host);
        $this->setPassword($password);
        $this->configure($options);
    }

    /**
     * Adds a new option value with a default value.
     *
     * @param string $name The option name
     * @param mixed $value The default value
     */
    public function addOption($name, $value = null)
    {
        $this->options[$name] = $value;
    }

    /**
     * Changes an option value.
     *
     * @param string $name The option name
     * @param mixed $value The value
     * @throws \InvalidArgumentException
     */
    public function setOption($name, $value)
    {
        if (!in_array($name, array_keys($this->options))) {
            throw new \InvalidArgumentException(sprintf('%s does not support the following option: \'%s\'.', get_class($this), $name));
        }

        $this->options[$name] = $value;
    }

    /**
     * Gets an option value.
     *
     * @param string $name The option name
     * @return mixed The option value
     */
    protected function getOption($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    /**
     * Returns true if the option exists.
     *
     * @param  string $name The option name
     * @return bool true if the option exists, false otherwise
     */
    protected function hasOption($name)
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * Configures the current object.
     * This method allows set options during object creation
     *
     * @param array $options An array of options
     * @see __construct()
     */
    protected function configure($options = [])
    {
        if (!empty($options)) {
            foreach ($options as $name => $value) {
                $this->addOption($name, $value);
            }
        }
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @param string $url
     */
    public function setHost($url)
    {
        $this->host = $url;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    protected function getPassword()
    {
        if (!is_string($this->password) && !strlen($this->password)) {
            throw new \InvalidArgumentException('Current Password is incorrect!');
        }
        return $this->password;
    }

    /**
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function getHost()
    {
        if (!is_string($this->host) && !strlen($this->host)) {
            throw new \InvalidArgumentException('Current URL is incorrect!');
        }
        return $this->host;
    }

    /**
     * The ping method, the server should respond by invoking "pong" on
     * the callback data
     *
     * @return string
     */
    public function ping()
    {
        return $this->makeRequest(__FUNCTION__);
    }

    /**
     * Return total information (pid, version, tasks in queue);
     *
     * @return array
     */
    public function info()
    {
        return $this->makeRequest(__FUNCTION__);
    }

    /**
     * Getting a list of live proxies
     *
     * @return array
     */
    public function getProxies()
    {
        return $this->makeRequest(__FUNCTION__);
    }

    /**
     * @param string $preset
     * @return mixed
     * @throws CurlResponseException
     * @throws \Exception
     */
    public function setProxyCheckerPreset($preset = 'default')
    {
        return $this->makeRequest(__FUNCTION__, [
            'preset' => $preset
        ]);
    }

    /**
     * Single request parsing, you can use any parser and preset. This
     * will generate the strings in accordance with the format of the
     * result set in the preset, as well as the full log parser.
     *
     * @param string $query
     * @param string $parser
     * @param string $preset
     * @param int $rawResults
     * @param array $options
     * @return array
     */
    public function oneRequest($query, $parser, $preset = 'default', $rawResults = 0, $options = [])
    {
        return $this->makeRequest(
            __FUNCTION__,
            [
                'query' => $query,
                'parser' => $parser,
                'preset' => $preset,
                'rawResults' => $rawResults,
                'options' => $options
            ]
        );
    }

    /**
     * Bulk request parsing, you can use any parser and preset, as well
     * as the quantity indicated in the threads to produce parsing.
     * This will generate the strings in accordance with the format of
     * the result set in the preset, as well as the full log parser for
     * each thread.
     *
     * @param array $queries
     * @param string $parser
     * @param string $preset
     * @param int $threads
     * @param int $rawResults
     * @param array $options
     * @return array
     */
    public function bulkRequest($queries, $parser, $preset = 'default', $threads = 5, $rawResults = 0, $options = [])
    {
        return $this->makeRequest(
            __FUNCTION__,
            [
                'queries' => $queries,
                'parser' => $parser,
                'preset' => $preset,
                'threads' => $threads,
                'rawResults' => $rawResults,
                'options' => $options,
            ]
        );
    }

    /**
     * Getting of the parser settings and presets
     *
     * @param $parser
     * @param string $preset
     * @return array
     */
    public function getParserPreset($parser, $preset = 'default')
    {
        return $this->makeRequest(
            __FUNCTION__,
            [
                'parser' => $parser,
                'preset' => $preset,
            ]
        );
    }

    /**
     * Add a task to turn all options are similar to those that are
     * specified in the interface Add Task
     *
     * @param string $configPreset
     * @param string $taskPreset
     * @param string $queriesFrom file|text
     * @param array $queries
     * @param array $options
     * @return string taskUid
     * @throws \InvalidArgumentException
     */
    public function addTask($configPreset, $taskPreset, $queriesFrom, $queries, $options = [])
    {
        $data['configPreset'] = $configPreset ? $configPreset : 'default';

        if($taskPreset) {
            $data['preset'] = $taskPreset;
        } else {
            $data['resultsFileName'] = isset($options['resultsFileName']) ? $options['resultsFileName'] : '$datefile.format().txt';
            $data['parsers']         = isset($options['parsers'])         ? $options['parsers']         : [];
            $data['uniqueQueries']   = isset($options['uniqueQueries'])   ? $options['uniqueQueries']   : 0;
            $data['keepUnique']      = isset($options['keepUnique'])      ? $options['keepUnique']      : 0;
            $data['resultsPrepend']  = isset($options['resultsPrepend'])  ? $options['resultsPrepend']  : '';
            $data['moreOptions']     = isset($options['moreOptions'])     ? $options['moreOptions']     : '';
            $data['resultsUnique']   = isset($options['resultsUnique'])   ? $options['resultsUnique']   : 'no';
            $data['doLog']           = isset($options['doLog'])           ? $options['doLog']           : 'no';
            $data['queryFormat']     = isset($options['queryFormat'])     ? $options['queryFormat']     : '$query';
            $data['resultsSaveTo']   = isset($options['resultsSaveTo'])   ? $options['resultsSaveTo']   : 'file';
            $data['configOverrides'] = isset($options['configOverrides']) ? $options['configOverrides'] : [];
            $data['resultsFormat']   = isset($options['resultsFormat'])   ? $options['resultsFormat']   : '';
            $data['resultsAppend']   = isset($options['resultsAppend'])   ? $options['resultsAppend']   : '';
            $data['queryBuilders']   = isset($options['queryBuilders'])   ? $options['queryBuilders']   : [];
            $data['resultsBuilders'] = isset($options['resultsBuilders']) ? $options['resultsBuilders'] : [];
        }

        switch($queriesFrom){
            case 'file':
                $data['queriesFrom'] = 'file';
                $data['queriesFile'] = isset($options['queriesFile']) ? $options['queriesFile'] : FALSE;
                break;
            case 'text':
                $data['queriesFrom'] = 'text';
                $data['queries'] = $queries ? $queries : [];
                break;
            default:
                throw new \InvalidArgumentException('Argument $queriesFrom is incorrect!');
        }

        return $this->makeRequest(__FUNCTION__, $data);
    }

    /**
     * Getting the status of task by uid
     *
     * @param int $taskUid
     * @return array
     */
    public function getTaskState($taskUid)
    {
        return $this->makeRequest(__FUNCTION__, ['taskUid' => $taskUid]);
    }

    /**
     * Getting configuration task by uid
     *
     * @param int $taskUid
     * @return array
     */
    public function getTaskConf($taskUid)
    {
        return $this->makeRequest(__FUNCTION__, ['taskUid' => $taskUid]);
    }

    /**
     * @param $taskUid
     * @return mixed
     * @throws CurlResponseException
     * @throws \Exception
     */
    public function getTaskResultsFile($taskUid)
    {
        return $this->makeRequest(__FUNCTION__, ['taskUid' => $taskUid]);
    }

    public function deleteTaskResultsFile($taskUid)
    {
        return $this->makeRequest(__FUNCTION__, ['taskUid' => $taskUid]);
    }

    /**
     * @param null $completed
     * @return mixed
     * @throws CurlResponseException
     * @throws \Exception
     */
    public function getTasksList($completed = null)
    {
        if ($completed == 1) {
            return $this->makeRequest(__FUNCTION__);
        }

        return $this->makeRequest(__FUNCTION__, [
            'completed' => $completed
        ]);
    }

    /**
     * Change status of a task by id
     *
     * @param int $taskUid
     * @param string $toStatus starting|pausing|stopping|deleting
     * @return array
     */
    public function changeTaskStatus($taskUid, $toStatus)
    {
        return $this->makeRequest(__FUNCTION__, ['taskUid' => $taskUid, 'toStatus' => $toStatus]);
    }

    /**
     * @param int $taskUid
     * @param string $direction start|end|up|down
     * @return array
     */
    public function moveTask($taskUid, $direction)
    {
        return $this->makeRequest(__FUNCTION__, ['taskUid' => $taskUid, 'direction' => $direction]);
    }

    /**
     * @param string $parser
     * @return mixed
     * @throws CurlResponseException
     * @throws \Exception
     */
    public function getParserInfo($parser = 'default')
    {
        return $this->makeRequest(__FUNCTION__, ['parser' => $parser]);
    }

    /**
     * @param string $action
     * @param array $data
     * @return mixed
     * @throws CurlResponseException
     * @throws \Exception
     */
    protected function makeRequest($action, $data = [])
    {
        $request = [
            'action' => $action,
            'password' => $this->getPassword(),
        ];

        if (!empty($data)) {
            $request['data'] = $data;
        }

        $request_json = json_encode($request);

        $ch = curl_init($this->getHost());

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Length: ' . strlen($request_json)]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/plain; charset=UTF-8']);

        $response = curl_exec($ch);
        curl_close($ch);

        if($response === false) {
            throw new CurlResponseException('Response fail: '. curl_error($ch));
        }

        $response = json_decode($response, true);

        if(!$response['success']) {
            throw new \Exception( 'Response fail: ' . (
                isset($response['msg']) ? $response['msg'] : 'unknow error')
            );
        }

        return isset($response['data']) ? $response['data'] : true;
    }

}