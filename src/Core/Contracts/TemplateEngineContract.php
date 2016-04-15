<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 03/04/16
 * Time: 10:46 AM
 */

namespace Core\Contracts;


interface TemplateEngineContract
{

    public function setCompileDir($dir);

    public function setConfigDir($dir);
    
    public function setCacheDir($dir);
    
    public function setTemplateDir($dir);
    
    public function addTemplateDir($dir);

    public function templateExists($tpl);
    
    public function assign($key, $value);

    public function fetch($template);
    
}