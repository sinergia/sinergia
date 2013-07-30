<?php

namespace Sinergia\Sinergia;
/**
 * Esta classe melhora a utilização de ob_start, para renderizar um template para string
 * Class Render
 * @package Sinergia\Common
 */
class Render
{
    public $errors = array();

    /**
     * Wrap $callback inside a safe rendering schema
     * Accept extra parameters that are forwarded to $callback
     * @param $callback
     * @return string
     * @throws \Exception
     */
    public function __invoke($callback)
    {
        // ob_start que trata erro fatal
        Util::ob_start();
        Error::disable( E_NOTICE | E_WARNING );
        set_error_handler(array($this, '_error_handler'));
        $args = func_get_args();
        array_shift($args);

        try {
            call_user_func_array($callback, $args);
        } catch (\Exception $e) {
            // ignora a saída gerada até o momento do erro
            ob_end_clean();
            // volta ao estado anterior, antes de disparar a exceção
            restore_error_handler();
            Error::pop();
            throw $e;
        }
        // mesmo código repetido aqui, pois o PHP não tem "finally"
        restore_error_handler();
        Error::pop();

        return ob_get_clean();
    }

    /**
     * Salva os erros no array $errors.
     * Este método precisa ser público para functionar com o set_error_handler
     * @param $level
     * @param $message
     * @param $file
     * @param $line
     * @return bool
     */
    public function _error_handler($level, $message, $file, $line)
    {
        // ignora erros calados com @
        if (error_reporting() === 0) return false;
        // ignora erros que já serão capturados pelo PHP
        if (error_reporting() & $level) {
            return false;
        }
        $microtime = microtime(true);
        $this->errors[] = compact('level', 'message', 'file', 'line', 'microtime');
    }
}
