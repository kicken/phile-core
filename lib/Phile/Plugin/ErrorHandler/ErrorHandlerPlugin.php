<?php
/**
 * Plugin class
 */

namespace Phile\Plugin\ErrorHandler;

use Phile\Plugin\AbstractPlugin;

/**
 * Class Plugin
 * Default Phile parser plugin for Markdown
 *
 * @author  PhileCMS
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 * @package Phile\Plugin\Phile\ParserMarkdown
 */
class ErrorHandlerPlugin extends AbstractPlugin {
    public function initialize() : void{
        if ($this->core->getSetting('env', 'dev') === 'dev'){
            set_error_handler([$this, 'handleError']);
            set_exception_handler([$this, 'handleException']);
            register_shutdown_function([$this, 'handleShutdown']);
        }
    }

    public function handleError(int $errno, string $errstr, string $errFile, int $errLine) : bool{
        if ((error_reporting() & $errno) == 0){
            return false;
        }

        $backtrace = debug_backtrace();
        $backtrace = array_slice($backtrace, 2);
        $this->displayDeveloperOutput(
            $errno,
            $errstr,
            $errFile,
            $errLine,
            $backtrace
        );

        return true;
    }

    public function handleShutdown(){
        $error = error_get_last();
        if (headers_sent() || $error === null || (error_reporting() & $error['type']) == 0){
            return;
        }

        $this->displayDeveloperOutput(
            $error['type'],
            $error['message'],
            $error['file'],
            $error['line']
        );
    }

    public function handleException(\Throwable $exception){
        $this->displayDeveloperOutput(
            $exception->getCode(),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            null,
            $exception
        );
    }

    public function shortenString($argument) : string{
        if (is_string($argument) && strlen($argument) > 120){
            $argument = substr($argument, 0, 120) . '...';
        }

        return str_replace(["\r", "\n"], ['\r', '\n'], $argument);
    }

    private function displayDeveloperOutput($code, $message, $file, $line, array $backtrace = null, \Throwable $exception = null){
        while (ob_get_level()){
            ob_end_clean();
        }
        header('HTTP/1.1 500 Internal Server Error');
        $fragment = $this->receiveCodeFragment(
            $file,
            $line,
            5,
            5
        );

        $marker = [
            'base_url' => $this->core->getSetting('base_url'),
            'type' => $exception ? 'Exception' : 'Error',
            'exception_message' => htmlspecialchars($message),
            'exception_code' => htmlspecialchars($code),
            'exception_file' => htmlspecialchars($file),
            'exception_line' => htmlspecialchars($line),
            'exception_fragment' => $fragment,
            'exception_class' => '',
            'wiki_link' => ''
        ];

        if ($exception){
            $marker['exception_class'] = $this->linkClass(get_class($exception));
            if ($code > 0){
                $marker['wiki_link'] = $this->tag(
                    'a',
                    'Exception-Wiki',
                    [
                        'href' => 'https://github.com/PhileCMS/Phile/wiki/Exception_' . $code,
                        'target_' => 'blank'
                    ]
                );
            } else {
                $marker['wiki_link'] = '';
            }
            $backtrace = $exception->getTrace();
        }

        if ($backtrace){
            $marker['exception_backtrace'] = $this->createBacktrace($backtrace);
        }

        ob_start();
        extract($marker);
        include __DIR__ . DIRECTORY_SEPARATOR . 'template.php';
        ob_end_flush();
        die();
    }

    private function createBacktrace(array $traces) : string{
        if (!count($traces)){
            return '';
        }
        $backtraceCodes = [];

        foreach ($traces as $index => $step){
            $backtrace = $this->tag('span', count($traces) - $index, ['class' => 'index']);
            $backtrace .= ' ';

            if (isset($step['class'])){
                $class = $this->linkClass($step['class']) . '<span class="divider">::</span>';
                $backtrace .= $class . $this->linkClass($step['class'], $step['function']);
            } else if (isset($step['function'])){
                $backtrace .= $this->tag('span', $step['function'], ['class' => 'function']);
            }

            $arguments = $this->getBacktraceStepArguments($step);
            if ($arguments){
                $backtrace .= $this->tag('span', "($arguments)", ['class' => 'funcArguments']);
            }

            if (isset($step['file'])){
                $backtrace .= $this->receiveCodeFragment($step['file'], $step['line']);
            }

            $backtraceCodes[] = $this->tag('pre', $backtrace, ['class' => 'entry']);
        }

        return implode('', $backtraceCodes);
    }

    private function getBacktraceStepArguments($step) : string{
        if (empty($step['args'])){
            return '';
        }
        $arguments = '';
        foreach ($step['args'] as $argument){
            $arguments .= strlen($arguments) === 0 ? '' : $this->tag('span', ', ', ['class' => 'separator']);
            if (is_object($argument)){
                $class = 'class';
                $content = $this->linkClass(get_class($argument));
            } else {
                $class = 'others';
                $content = gettype($argument);
            }
            $arguments .= $this->tag(
                'span',
                $content,
                [
                    'class' => $class
                    , 'title' => $this->getPrintableValue($argument)
                ]
            );
        }

        return $arguments;
    }

    private function receiveCodeFragment($filename, $lineNumber, int $linesBefore = 3, int $linesAfter = 3) : string{
        if (!file_exists($filename)){
            return '';
        }
        $html = $this->tag('span', $filename . ':<br/>', ['class' => 'filename']);

        $code = file_get_contents($filename);
        $lines = explode("\n", $code);

        $firstLine = $lineNumber - $linesBefore - 1;
        if ($firstLine < 0){
            $firstLine = 0;
        }

        $lastLine = $lineNumber + $linesAfter;
        if ($lastLine > count($lines)){
            $lastLine = count($lines);
        }

        $line = $firstLine;
        $fragment = '';
        while ($line < $lastLine){
            $line++;

            $lineText = htmlspecialchars($lines[$line - 1]);
            $lineText = str_replace("\t", '&nbsp;&nbsp;', $lineText);
            $tmp = sprintf('%05d: %s <br/>', $line, $lineText);

            $class = 'row';
            if ($line === $lineNumber){
                $class .= ' currentRow';
            }
            $fragment .= $this->tag('span', $tmp, ['class' => $class]);
        }


        $html .= $fragment;

        return $this->tag('pre', $html);
    }

    private function linkClass($class, $method = null) : string{
        $title = $method ?: $class;
        if (strpos($class, 'Phile\\') === 0){
            return $title;
        }

        $filename = 'docs/classes/' . str_replace('\\', '.', $class) . '.html';
        if (file_exists($filename)){
            return $title;
        }

        $href = $this->core->getSetting('base_url') . '/' . $filename;
        if ($method){
            $href .= '#method_' . $method;
        }

        return $this->tag('a', $title, ['href' => $href, 'target' => '_blank']);
    }

    private function tag(string $tag, string $content = '', array $attributes = []) : string{
        $html = '<' . $tag;
        foreach ($attributes as $key => $value){
            $html .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }
        $html .= '>' . $content . '</' . $tag . '>';

        return $html;
    }

    private function getPrintableValue($argument) : string{
        if (is_null($argument)){
            return 'null';
        } else if (is_scalar($argument)){
            return '"' . $this->shortenString($argument) . '"';
        } else if (is_array($argument) || $argument instanceof \stdClass){
            $output = [sprintf('%s(%d){', is_array($argument) ? 'array' : 'stdClass', count($argument))];
            foreach ($argument as $key => $item){
                $output[] = sprintf('%s => %s', $this->getPrintableValue($key), $this->getPrintableValue($item));
            }
            $output[] = '}';

            return implode("\r\n", $output);
        } else if (is_object($argument)){
            return get_class($argument);
        } else {
            return '';
        }
    }
}
