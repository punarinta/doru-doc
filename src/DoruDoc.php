<?php

namespace DoruDoc;

class DoruDoc
{
    protected $tpl;
    protected $rootUrl;
    protected $collectionCount = 0;
    protected $procedureCount = 0;
    protected $config = [];

    /**
     * DoruDoc constructor.
     * @param $configFile
     */
    public function __construct($configFile)
    {
        // load the config file
        $this->config = json_decode(file_get_contents($configFile));
        $this->tpl = new \stdClass;
    }

    /**
     * @return string
     */
    public function build()
    {
        $mainHtml = '';

        // fetch templates
        $tplNames = ['footer', 'header', 'layout', 'collection', 'procedure', 'varEnum', 'variable'];

        foreach ($tplNames as $tplName)
        {
            if (isset ($this->config->templates->overwrite) && in_array($tplName, $this->config->templates->overwrite) )
            {
                $this->tpl->{$tplName} = file_get_contents($this->config->templates->dir . '/' . $tplName . '.html');
            }
            else
            {
                $this->tpl->{$tplName} = file_get_contents(__DIR__ . '/../templates/' . $tplName . '.html');
            }
        }

        $outputDir = isset ($this->config->output->dir) ? $this->config->output->dir : getcwd();
        $outputFile = isset ($this->config->output->filename) ? $this->config->output->filename : 'index.html';
        $this->rootUrl = isset ($this->config->input->rootUrl) ? $this->config->input->rootUrl : '/';

        // Get files list

        foreach ($this->config->input->dirs as $directory)
        {
            $files = scandir($directory);
            foreach ($files as $file)
            {
                if (strpos($file, '.php') != false)
                {
                    if (in_array($file, $this->config->exclude))
                    {
                        continue;
                    }
                    $mainHtml .= $this->scanFile($directory . '/' . $file, $directory);
                }
            }
        }

        // copy styles
        if (!file_exists("$outputDir/css"))
        {
            mkdir("$outputDir/css");
        }

        copy(__DIR__ . '/../templates/media/bootstrap.min.css', "$outputDir/css/bootstrap.min.css");
        copy(__DIR__ . '/../templates/media/styles.css', "$outputDir/css/styles.css");
        copy(__DIR__ . '/../templates/media/favicon.ico', "$outputDir/favicon.ico");

        file_put_contents("$outputDir/$outputFile", strtr($this->tpl->layout, array
        (
            '[HEADER]'              => $this->tpl->header,
            '[LIST_COLLECTIONS]'    => $mainHtml,
            '[FOOTER]'              => $this->tpl->footer,
            '[API_ROOT]'            => $this->rootUrl,
        )));

        return '';
    }

    public function getStats()
    {
        return
        [
            'collectionCount' => $this->collectionCount,
            'procedureCount'  => $this->procedureCount,
        ];
    }

    /**
     * @param $filePath
     * @param $dirName
     * @return string
     */
    protected function scanFile($filePath, $dirName)
    {
        $html = '';
        $baseUrl = '';

        echo 'Scanning ' . str_replace($dirName, '', $filePath) . "...\n";

        $php = file_get_contents($filePath);

        if (preg_match("/^.*@doc-api-path (.*)\$/m", $php, $matches))
        {
            $baseUrl = trim($matches[1]);
        }

        $m2 = strpos($php, '{', 0);
        $procedures = [];

        while ($m1 = strpos($php, '/**', $m2))
        {
            $m2 = strpos($php, "()\n", $m1);

            if (!$m2) break;

            $procedures[] = explode("\n", str_replace('*', '', substr($php, $m1 + 1, $m2 - $m1 - 1)));
        }

        foreach ($procedures as $procedure)
        {
            $html .= $this->scanProcedure($procedure, $baseUrl);
        }

        $this->collectionCount++;

        if (!strlen($baseUrl))
        {
            return '';
        }

        // fill the collection and return its markup
        return strtr($this->tpl->collection, array
        (
            '[BASE_URL]'       => $baseUrl,
            '[LIST_ENDPOINTS]' => $html,
        ));
    }

    /**
     * @param $procedure
     * @param $baseUrl
     * @return string
     */
    protected function scanProcedure($procedure, $baseUrl)
    {
        $html = '';
        $procedureName = '';
        $description = '';

        $endLine = end($procedure);

        foreach ($procedure as $docLine)
        {
            // first line is a description
            if (!strlen($description))
            {
                $description = trim($docLine);
            }

            if (preg_match_all("/^.*@doc-var (.*)\$/m", $docLine, $matches))
            {
                foreach ($matches[1] as $variable)
                {
                    $html .= $this->scanVariable($variable);
                }
            }

            if ($docLine == $endLine)
            {
                $endLine = explode(' ', $endLine);
                $procedureName = end($endLine);
            }
        }

        $this->procedureCount++;

        return strtr($this->tpl->procedure, array
        (
            '[NAME]'           => $procedureName,
            '[DESCRIPTION]'    => $description,
            '[PATH]'           => $baseUrl,
            '[HIDE_FORM]'      => strlen($html) ? '' : 'hidden',
            '[LIST_VARIABLES]' => $html,
        ));
    }

    /**
     * @param $line
     * @return string
     */
    protected function scanVariable($line)
    {
        $type = preg_match("/^.*\\((.*)\\).*\$/m", $line, $varData) ? $varData[1] : 'not provided';
        $name = explode(' - ', preg_replace("/\\(.*\\)/", '', $line));

        $description = count($name) > 1 ? trim($name[1]) : '';

        // is this variable required
        $isImportant = (strpos($name[0], '!') !== false);
        $name = str_replace('!', '', $name[0]);

        // extract default value
        if (strpos($type, '=') !== false)
        {
            $type = explode('=', $type);
            $defaultValue = trim(str_replace("'", '', $type[1]));
            $type = trim($type[0]);
        }
        else
        {
            $defaultValue = '';
        }

        $typeF = trim($type);

        $fieldType = 'text';

        $type = explode(':', $typeF)[0];

        if ($type == 'date')    $fieldType = 'date';
        if ($type == 'time')    $fieldType = 'datetime-local';
        if ($type == 'int')     $fieldType = 'number';
        if ($type == 'float')   $fieldType = 'number';

        if (in_array($type, ['bool', 'enum']))
        {
            $options = [];

            if ($type == 'bool')
            {
                $options =
                [
                    "<option value='0'>false</option>",
                    "<option value='1'>true</option>",
                ];
            }
            else
            {
                $enums = explode('|', explode(':', $typeF)[1]);
                foreach ($enums as $enum)
                {
                    $options[] = "<option value='$enum'>$enum</option>";
                }
            }

            return strtr($this->tpl->varEnum, array
            (
                '[NAME]'            => trim($name),
                '[DESCRIPTION]'     => trim($description),
                '[TYPE]'            => $type,
                '[CLASS_REQUIRED]'  => $isImportant ? 'required' : '',
                '[OPTIONS]'         => implode('', $options),
            ));
        }
        else
        {
            return strtr($this->tpl->variable, array
            (
                '[NAME]'            => trim($name),
                '[DESCRIPTION]'     => trim($description),
                '[TYPE]'            => $type,
                '[DEFAULT_VALUE]'   => trim($defaultValue),
                '[NO_INPUT]'        => /*$type == 'array' ? 'hidden' :*/ '',
                '[CLASS_REQUIRED]'  => $isImportant ? 'required' : '',
                '[FIELD_TYPE]'      => $fieldType,
            ));
        }
    }
}