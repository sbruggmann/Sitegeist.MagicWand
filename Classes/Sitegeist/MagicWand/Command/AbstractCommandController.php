<?php
namespace Sitegeist\MagicWand\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Sitegeist.MagicWand".   *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Core\Bootstrap;
use TYPO3\Flow\Cli\CommandController;

abstract class AbstractCommandController extends CommandController
{

    const HIDE_RESULT = 1;
    const HIDE_COMMAND = 2;

    /**
     * @Flow\InjectConfiguration(path="persistence.backendOptions", package="TYPO3.Flow")
     * @var array
     */
    protected $databaseConfiguration;

    /**
     * @Flow\Inject
     * @var Bootstrap
     */
    protected $bootstrap;

    /**
     * @var int
     */
    protected $headlineNumber = 0;

    /**
     * @var array
     */
    protected $secrets = [];


    /**
     * @param string $commands
     * @param array $arguments
     * @param array $options
     */
    protected function executeLocalShellCommand($command, $arguments = [], $options = [])
    {
        $customizedCommand = call_user_func_array('sprintf', array_merge([$command], $arguments));
        if (!in_array(self::HIDE_COMMAND, $options)) {
            $this->outputLine($customizedCommand);
        }
        $customizedCommandResult = shell_exec($customizedCommand);
        if (!in_array(self::HIDE_RESULT, $options)) {
            $this->outputLine($customizedCommandResult);
        }
        return $customizedCommandResult;
    }

    /**
     * @param string $commands
     * @param array $arguments
     * @param array $options
     */
    protected function executeLocalShellCommandWithFlowContext($command, $arguments = [], $options = [])
    {
        $flowCommand = sprintf('FLOW_CONTEXT=%s %s', $this->bootstrap->getContext(), $command);
        return $this->executeLocalShellCommand($flowCommand, $arguments, $options);
    }

    /**
     * @param string $commands
     * @param array $arguments
     * @param array $options
     */
    protected function executeLocalFlowCommand($command, $arguments = [], $options = [])
    {
        $flowCommand = sprintf('./flow %s', $command);
        return $this->executeLocalShellCommandWithFlowContext($flowCommand, $arguments, $options);
    }

    /**
     * @param $line
     */
    protected function outputHeadLine($line = '', $arguments = [])
    {
        $this->headlineNumber++;
        $this->outputLine();
        $this->outputLine('<b>' . $this->headlineNumber . '. ' . $line . '</b>', $arguments);
        $this->outputLine();
    }

    /**
     * @param $line
     */
    protected function outputLine($line = '', array $arguments = [])
    {
        $filteredLine = $line;
        foreach ($this->secrets as $secret) {
            $filteredLine = str_replace($secret, '[xxx]', $filteredLine);
        }
        parent::outputLine($filteredLine, $arguments);
    }

    /**
     * @param $secret
     */
    protected function addSecret($secret)
    {
        $this->secrets[] = $secret;
    }
}