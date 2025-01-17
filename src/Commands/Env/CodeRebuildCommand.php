<?php

namespace Pantheon\Terminus\Commands\Env;

use Pantheon\Terminus\Commands\TerminusCommand;
use Pantheon\Terminus\Commands\WorkflowProcessingTrait;
use Pantheon\Terminus\Exceptions\TerminusException;
use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Site\SiteAwareTrait;

/**
 * Class CodeRebuildCommand.
 *
 * @package Pantheon\Terminus\Commands\Env
 */
class CodeRebuildCommand extends TerminusCommand implements SiteAwareInterface
{
    use SiteAwareTrait;
    use WorkflowProcessingTrait;

    /**
     * Rebuild code for the given environment (only dev and multidev allowed).
     *
     * @authorize
     *
     * @command env:code-rebuild
     * @aliases code-rebuild
     *
     * @param string $site_env Site & environment in the format `site-name.env` (only Dev or Multidev)
     *
     * @throws \Pantheon\Terminus\Exceptions\TerminusException
     *
     * @usage <site>.<env> Sync code into the <site>'s Dev or multidev environment.
     */
    public function rebuild(
        $site_env
    ) {
        $this->requireSiteIsNotFrozen($site_env);
        $site = $this->getSite($site_env);
        $env = $this->getEnv($site_env);

        if ($env->getName() === 'test' || $env->getName() === 'live') {
            throw new TerminusException('Test and live are not valid environments for this command.');
        }

        $params = [
            'converge' => true,
            'build_steps' => [
                'artifact_install' => true,
            ],
        ];

        $workflow = $env->syncCode($params);

        $this->processWorkflow($workflow);
        $this->log()->notice($workflow->getMessage());
    }
}
