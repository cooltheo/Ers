<?php
/**
 * @namespace
 */
namespace Ers\Lib;

class iniParser {
	protected $_iniFilename = '';
	protected $_iniParsedArray = array();

   /**
	*  Create a multidimensional array from the INI file
	**/
	public function __construct($filename)
	{
		$this->_iniFilename = $filename;
		$this->_iniParsedArray = parse_ini_file($filename, true);
	}

   /**
	* Returns the complete section
	**/
	public function getSection($key)
	{
		return $this->_iniParsedArray[$key];
	}

	/**
	*  Returns a value of one section back
	**/
	public function getValue($section, $key)
	{
		if(!isset($this->_iniParsedArray[$section])) return false;
		return isset($this->_iniParsedArray[$section][$key]) ? $this->_iniParsedArray[$section][$key] : null;
	}

   /**
	* Returns the value of a section or the whole back section
	**/
	public function get($section, $key=null)
	{
		if(is_null($key)) return $this->getSection($section);
		return $this->getValue($section, $key);
	}

   /**
	* Set a value according to the specified key
	**/
	public function setSection($section, $array)
	{
		if(!is_array($array)) return false;
		return $this->_iniParsedArray[$section] = $array;
	}

	/**
	* Sets a new value in a section
	**/
	public function setValue($section, $key, $value)
	{
		if( $this->_iniParsedArray[$section][$key] = $value ) return true;
	}

	/**
	* Sets a new value in a section or an entire new section
	**/
	public function set($section, $key, $value=null)
	{
		if(is_array($key) && is_null($value)) return $this->setSection($section, $key);
		return $this->setValue($section, $key, $value);
	}

   /**
    * build string
    *
    * @param string
    */
	public function buildString()
	{

        $configString = '';
        foreach ($this->_iniParsedArray as $section => $data) {
            $configString .= "[" . $section . "]\n";
            foreach ($data as $key => $value) {
                $configString .= "$key = $value\n";
            }
            $configString .= "\n";
        }
        return $configString;
	}

   /**
    * setAll
    *
    * @param array $data 数据
    *
    * @return void
    */
	public function setAll(array $data)
	{
        $this->_iniParsedArray = $data;
	}


	/**
	 * getAll
	 *
	 * @param array $data 数据
	 *
	 * @return void
	 */
    public function getAll()
    {
        return $this->_iniParsedArray;
    }


   /**
	* Secures the entire array in the INI file
	**/
	public function save($filename = null)
	{
		if( $filename == null ) $filename = $this->_iniFilename;
		if( is_writeable( $filename ) ) {
			$SFfdescriptor = fopen( $filename, "w" );
		    fwrite( $SFfdescriptor, $this->buildString());
			fclose( $SFfdescriptor );
			return true;
		} else {
			return false;
		}
	}
}
