<?php

namespace Redis\RSMQRESTClientBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class RESTClientCommand extends ContainerAwareCommand
{

    private $params;

    protected function configure()
    {
        $this
            ->setName('rsmq:rest')
            ->addArgument('arguments', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->params = $this->getContainer()->getParameter("rsmq_rest_config");

        $name = $input->getArgument('arguments');

        if ($name) {

            switch ($name) {
                case 'start':
                    $this->actionStart();
                    break;

                case 'stop':
                    $this->actionStop();
                    break;

                case 'restart':
                    $this->actionRestart();
                    break;

                default:
                    $response = $this->render_usage();
                    $output->writeln($response);
            }

        } else {

            $response = $this->render_usage();

            $output->writeln($response);

        }
    }

    protected function render_usage()
    {

        $text = <<<EOD
                
    USAGE
      bin/console {$this->getName()} [action] [parameter]

    DESCRIPTION
      This command provides support for managing RSMQ REST API node app.

    EXAMPLES
     * bin/console {$this->getName()} start
       Start RSMQ REST server, check is started

     * bin/console {$this->getName()} stop
       Stop RSMQ REST server

     * bin/console {$this->getName()} restart
       Restart RSMQ REST  server
                        
EOD;

        return $text;


    }

    protected function actionStart()
    {
        if ($this->isInProgress()) {
            printf("Server " . $this->params['process_name'] . " already started\n");
            return true;
        }

        $this->compileServer();

        printf("Starting server\n");

        $serverPath = implode(DIRECTORY_SEPARATOR, array(
            __DIR__,
            '..',
            'Library',
            'js',
            'server.js'
        ));

        $runtime_command = implode(' ', array(
            'pm2',
            'start',
            $serverPath,
            '--name',
            '"' . $this->params['process_name'] . '"',
        ));


        printf("Starting command:\n$runtime_command\n");

        $process = new Process($runtime_command);
        $process->start();
        while ($process->isRunning()) ;
        if ($this->isInProgress()) {
            printf("RSMQ RESTful client successfully started on:\n");
            printf("\t\thost: " . $this->params['host'] . "\n");
            printf("\t\tport: " . $this->params['port'] . "\n");
            return true;
        } else {
            printf("Error: RSMQ RESTful client can not start. Please check app logs.\n");
            return false;
        }
    }

    protected function actionStop()
    {
        $processName = $this->params['process_name'];
        if ($this->isInProgress()) {
            printf("Stopping " . $processName . " server...\n");
            $runtime_command = implode(' ', array(
                'pm2',
                'stop',
                $processName,
            ));
            $process = new Process($runtime_command);
            $process->start();
            while ($process->isRunning()) ;
            if (!$this->isInProgress()) {
                printf("Server " . $processName . " successfully stopped on port: %s \n", $this->params['port']);
                return true;
            }
            printf("Stopping " . $processName . " server error\n");
            return false;
        }
        printf("Server " . $processName . " is not running\n");
        return true;
    }

    protected function actionRestart()
    {
        $processName = $this->params['process_name'];
        printf("Restarting " . $processName . " server...\n");

        $runtime_command = implode(' ', array(
            'pm2',
            'restart',
            $processName,
        ));
        $process = new Process($runtime_command);
        $process->start();
        while ($process->isRunning()) ;
        if ($this->isInProgress()) {
            printf('Server ' . $processName . " restarted successfully.\n");
        } else {
            printf('Can not restart server ' . $processName . ".\n");
        }
    }

    protected function compileServer()
    {

        printf("Compile server...\n");

        $server_js_config = implode(DIRECTORY_SEPARATOR, array(
            __DIR__,
            '..',
            'Library',
            'js',
            'server.config.js.php'
        ));

        if (file_exists($server_js_config)) {
            echo ('Configuration file found "server.config.js.php"') . PHP_EOL;
        }

        ob_start();

        $configs = $this->params;

        include($server_js_config);

        $js = ob_get_clean();

        return file_put_contents(__DIR__ . '/../Library/js/server.config.js', $js);
    }

    /**
     * @return bool
     */
    protected function isInProgress()
    {
        $runtime_command = implode(' ', array(
            'pm2',
            'jlist',
        ));
        $process = new Process($runtime_command);
        $process->start();
        while ($process->isRunning()) ;
        $nodeApps = json_decode($process->getOutput());
        if($nodeApps) {
            foreach ($nodeApps as $app) {
                if ($app->name == $this->params['process_name'] && $app->pm2_env->status == 'online') {
                    return true;
                }
            }
        }
        return false;
    }


}
