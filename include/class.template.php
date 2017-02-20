<?php

require_once ( "fp_constants.php" );

/**
 * Loads the template-system of FPraktikum
 *
 * @author Bastian Krones
 * @date 03.01.2017
 */
class Template
{
    private $templateDir;// Location of the templates' files
    private $templateFile;			// Complete  path to template file
    private $templateName;			// Name of template file
    private $template = "";			// Content of template
    private $languageDir;// Location of all language files
    private $languageFile = "";		// Complete  path to language file
    private $leftDel = "\{#";		// Left Delimiter for default placeholder
    private $rightDel = "#\}";		// Right Delimiter for default placeholder
    private $leftDelF = "\{";		// Left Delimiter for functions
    private $rightDelF = "\}";		// Right Delimiter for functions
    private $leftDelC = "\{\*";		// Left Delimiter for comments; Special characters have to be escaped
    private $rightDelC = "\*\}";	// Right Delimiter for comments
    private $leftDelL = "\{L_";		// Left Delimiter for default placeholder
    private $rightDelL = "\}";		// Right Delimiter for default placeholder

    public function __construct ()
    {
        $this->templateDir = fp_const\FP_DIRECTORY . "templates/";
        $this->languageDir = fp_const\FP_DIRECTORY . "languages/";
    }

    /**
     * Open template file
     *
     * @access	public
     * @param 	string $file filename of template
     * @uses 	$templateName
     * @uses 	$templateDir
     * @uses	$parseFunctions()
     * @return 	boolean
     */
    public function load($template){
        $this->templateName = $template.".tpl";
        $this->templateFile = $this->templateDir.$this->templateName;

        // if filename is delivered, try to open file
        if(!empty($this->templateFile)){
            if(file_exists($this->templateFile)){
                $this->template = file_get_contents($this->templateFile);
            } else {
                return false;
            }

            // Parse function
            $this->parseFunctions();
        }
    }

    /**
     * Replace default placeholder
     *
     * @access	public
     * @param	string $replace		Name of placeholder
     * @param	string $replacement	Replacement of placeholder
     * @uses	$leftDel
     * @uses	$rightDel
     * @uses	$template
     *
     **/
    public function assign($replace, $replacement){
        $this->template = preg_replace("/".$this->leftDel.$replace.$this->rightDel."/", $replacement, $this->template);
    }

    /**
     * Open language file
     *
     * @access	public
     * @param	array $files filename of languageFiles.
     * @uses	$languageFiles
     * @uses	$languageDir
     * @uses	replaceLangVars()
     * @return	array()
     */
    public function loadLanguage($language){
        $this->languageFile = $this->languageDir.$language . ".php";

        if(!file_exists($this->languageFile)){
            return false;
        } else {
            include_once($this->languageFile);
        }

        $this->replaceLangVars($lang);	// Replace language variables

        return $lang;					// return $lang to use $lang in php-code
    }

    /**
     * Replace language variables in template
     *
     * @access  private
     * @param	string $lang 	languagevariable
     * @uses	$template
     */
    private function replaceLangVars(){
        $this->template = preg_replace("/\{L_(.*)\}/isUe","\$lang[strtolower('\\1')]",
            $this->template);
    }

    /**
     * Parsing 'includes' and removes comments
     *
     * @access	private
     * @uses	$leftDelimiterF
     * @uses	$rightDelF
     * @uses 	$leftDelC
     * @uses	$rightDelC
     */
    private function parseFunctions(){
        // Replace 'includes' ({include file="..."})
        while(preg_match(
            "/".$this->leftDelF."include file=\"(.*)\.(.*)\"".$this->rightDelF."/isUe",
            $this->template)){
            $this->template = preg_replace("/".$this->leftDelF."include file=\"(.*)\.(.*)\"".$this->rightDelF."/isUe",
                "file_get_contents(\$this->templateDir.'\\1'.'.'.'\\2'",
                $this->template);
        }

        // Delete comments
        $this->template = preg_replace(
            "/".$this->leftDelC."(.*)".$this->rightDelC."/isUe",
            "",
            $this->template);
    }

    /**
     * Return finished template
     *
     * @access  public
     * @uses	$template
     */
    public function display(){
        return $this->template;
    }
}