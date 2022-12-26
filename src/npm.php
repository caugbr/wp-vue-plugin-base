<?php

trait WpVuePluginBaseNpm {

    // run generic npm commands
    public function runNpm($slug, $command, $message = '') {
        if ($this->wpCli()) {
            $this->check_template($slug);
            $dir = trim(shell_exec("wp plugin path"));
            print "{$dir}/{$slug}/{$this->template->appDir}\n";
            if (file_exists("{$dir}/{$slug}/{$this->template->appDir}")) {
                if (!empty($message)) {
                    $this->line();
                    $this->line($message);
                }
                chdir("{$dir}/{$slug}/{$this->template->appDir}");
                shell_exec("npm {$command}");
            } else {
                $this->line();
                $this->line("The directory 'plugins/{$slug}/{$this->template->appDir}' does not exists");
            }
        }
    }

    // run 'npm install'
    public function install($slug) {
        $this->runNpm($slug, "install", "Installing packages...");
    }

    // run 'npm run serve'
    public function startServer($slug) {
        if (!empty($this->flags['port'])) {
            $port = $this->flags['port'];
        } else {
            $info = $this->get_template_info($slug);
            $port = $info->devPort;
        }
        $comm = 'run serve';
        if ($port !== '8080') {
            $comm .= " -- --port {$port}";
        }
        $this->runNpm($slug, $comm, "The development server will be running here. Do not close this window. Ctrl + C to stop.");
    }

    // run 'npm run build'
    public function build($slug) {
        $this->runNpm($slug, "run build", "Generating the production package...");
    }

}
?>