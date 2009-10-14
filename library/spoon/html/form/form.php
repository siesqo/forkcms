<?php

/**
 * Spoon Library
 *
 * This source file is part of the Spoon Library. More information,
 * documentation and tutorials can be found @ http://www.spoon-library.be
 *
 * @package		html
 * @subpackage	form
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @author 		Tijs Verkoyen <tijs@spoon-library.be>
 * @author		Dave Lens <dave@spoon-library.be>
 * @since		0.1.1
 */


/** Filesystem package */
require_once 'spoon/filesystem/filesystem.php';


/**
 * This exception is used to handle form related exceptions.
 *
 * @package		html
 * @subpackage	form
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @since		0.1.1
 */
class SpoonFormException extends SpoonException {}


/**
 * The class that handles the forms
 *
 * @package		html
 * @subpackage	form
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @since		0.1.1
 */
class SpoonForm
{
	/**
	 * Action the form goes to
	 *
	 * @var	string
	 */
	private $action;


	/**
	 * Form status
	 *
	 * @var	bool
	 */
	private $correct = true;


	/**
	 * Errors (optional)
	 *
	 * @var	string
	 */
	private $errors;


	/**
	 * Allowed field in the $_POST or $_GET array
	 *
	 * @var	array
	 */
	private $fields = array();


	/**
	 * Form method
	 *
	 * @var	string
	 */
	private $method = 'post';


	/**
	 * Name of the form
	 *
	 * @var	string
	 */
	private $name;


	/**
	 * List of added objects
	 *
	 * @var	array
	 */
	private $objects = array();


	/**
	 * Extra parameters for the form tag
	 *
	 * @var	array
	 */
	private $parameters = array();


	/**
	 * Already validated?
	 *
	 * @var	bool
	 */
	private $validated = false;


	/**
	 * Class constructor.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $method
	 */
	public function __construct($name, $action = null, $method = 'post')
	{
		// required field
		$this->setName($name);
		$this->createHiddenField();

		// optional fields
		$this->setAction($action);
		$this->setMethod($method);
	}


	/**
	 * Add one or more objects to the stack
	 *
	 * @return	void
	 * @param	string[optional] $object
	 * @param	string[optional] $objectTwo
	 * @param	string[optional] $objectThree
	 */
	public function add($object = null, $objectTwo = null, $objectThree = null)
	{
		// more than one argument
		if(func_num_args() != 0)
		{
			// iterate arguments
			foreach(func_get_args() as $argument)
			{
				// array of objects
				if(is_array($argument)) foreach($argument as $object) $this->add($object);

				// object
				else
				{
					// not an object
					if(!is_object($argument)) throw new SpoonFormException('The provided argument ('. $argument .') is not an object.');

					// valid object
					$this->objects[$argument->getName()] = $argument;
					$this->objects[$argument->getName()]->setFormName($this->name);
					$this->objects[$argument->getName()]->setMethod($this->method);

					// automagically add enctype if needed & not already added
					if($argument instanceof SpoonFileField && !isset($this->parameters['enctype'])) $this->setParameter('enctype', 'multipart/form-data');
				}
			}
		}
	}


	/**
	 * Adds a single button
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string $value
	 * @param	string[optional] $type
	 * @param	string[optional] $class
	 */
	public function addButton($name, $value, $type = null, $class = 'inputButton')
	{
		$this->add(new SpoonButton($name, $value, $type, $class));
	}


	/**
	 * Adds a single checkbox
	 *
	 * @return	void
	 * @param	string $name
	 * @param	bool[optional] $checked
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function addCheckBox($name, $checked = false, $class = 'inputCheckbox', $classError = 'inputCheckboxError')
	{
		$this->add(new SpoonCheckBox($name, $checked, $class, $classError));
	}


	/**
	 * Adds one or more checkboxes
	 *
	 * @return	void
	 */
	public function addCheckBoxes()
	{
		// loop fields
		foreach(func_get_args() as $argument)
		{
			// not an array
			if(!is_array($argument)) $this->add(new SpoonCheckBox($argument));

			// array
			else
			{
				foreach($argument as $name => $checked) $this->add(new SpoonCheckBox($name, (bool) $checked));
			}
		}
	}


	/**
	 * Adds a single datefield
	 *
	 * @return	void
	 * @param	string $name
	 * @param	int[optional] $name
	 * @param	string[optional] $mask
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function addDateField($name, $value = null, $mask = null, $class = 'inputDatefield', $classError = 'inputDatefieldError')
	{
		$this->add(new SpoonDateField($name, $value, $mask, $class, $classError));
	}


	/**
	 * Adds a single dropdown
	 *
	 * @return	void
	 * @param	string $name
	 * @param	array $values
	 * @param	string[optional] $selected
	 * @param	bool[optional] $multipleSelection
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function addDropDown($name, array $values, $selected = null, $multipleSelection = false, $class = 'inputDropdown', $classError = 'inputDropdownError')
	{
		$this->add(new SpoonDropDown($name, $values, $selected, $multipleSelection, $class, $classError));
		return $this->getField($name);
	}


	/**
	 * Adds an error to the main error stack
	 *
	 * @return	void
	 * @param	string $error
	 */
	public function addError($error)
	{
		$this->errors .= trim((string) $error);
	}


	/**
	 * Adds a single file field
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function addFileField($name, $class = 'inputFilefield', $classError = 'inputFilefieldError')
	{
		$this->add(new SpoonFileField($name, $class, $classError));
		return $this->getField($name);
	}


	/**
	 * Adds one or more file fields
	 *
	 * @return	void
	 */
	public function addFileFields()
	{
		foreach(func_get_args() as $argument) $this->add(new SpoonFileField((string) $argument));
	}


	/**
	 * Adds a single hidden field
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $value
	 */
	public function addHiddenField($name, $value = null)
	{
		$this->add(new SpoonHiddenField($name, $value));
		return $this->getField($name);
	}


	/**
	 * Adds one or more hidden fields
	 *
	 * @return	void
	 */
	public function addHiddenFields()
	{
		// loop fields
		foreach(func_get_args() as $argument)
		{
			// not an array
			if(!is_array($argument)) $this->add(new SpoonHiddenField($argument));

			// array
			else
			{
				foreach($argument as $name => $defaultValue) $this->add(new SpoonHiddenField($name, $defaultValue));
			}
		}
	}


	/**
	 * Adds a single multiple checkbox
	 *
	 * @return	void
	 * @param	string $name
	 * @param	array $values
	 * @param	bool[optional] $checked
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function addMultiCheckBox($name, array $values, $checked = null, $class = 'inputCheckbox', $classError = 'inputCheckboxError')
	{
		$this->add(new SpoonMultiCheckBox($name, $values, $checked, $class, $classError));
		return $this->getField($name);
	}


	/**
	 * Adds a single password field
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $value
	 * @param	int[optional] $maxlength
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 * @param	bool[optional] $HTML
	 */
	public function addPasswordField($name, $value = null, $maxlength = null, $class = 'inputPassword', $classError = 'inputPasswordError', $HTML = false)
	{
		$this->add(new SpoonPasswordField($name, $value, $maxlength, $class, $classError, $HTML));
		return $this->getField($name);
	}


	/**
	 * Adds one or more password fields
	 *
	 * @return	void
	 */
	public function addPasswordFields()
	{
		// loop fields
		foreach(func_get_args() as $argument)
		{
			// not an array
			if(!is_array($argument)) $this->add(new SpoonPasswordField($argument));

			// array
			else
			{
				foreach($argument as $name => $defaultValue) $this->add(new SpoonPasswordField($name, $defaultValue));
			}
		}
	}


	/**
	 * Adds a single radiobutton
	 *
	 * @return	void
	 * @param	string $name
	 * @param	array $values
	 * @param	string[optional] $checked
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function addRadioButton($name, array $values, $checked = null, $class = 'inputRadiobutton', $classError = 'inputRadiobuttonError')
	{
		$this->add(new SpoonRadioButton($name, $values, $checked, $class, $classError));
		return $this->getField($name);
	}


	/**
	 * Adds a single textarea
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $value
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 * @param	bool[optional] $HTML
	 */
	public function addTextArea($name, $value = null, $class = 'inputTextarea', $classError = 'inputTextareaError', $HTML = false)
	{
		$this->add(new SpoonTextArea($name, $value, $class, $classError, $HTML));
		return $this->getField($name);
	}


	/**
	 * Adds one or more textareas
	 *
	 * @return	void
	 */
	public function addTextAreas()
	{
		// loop fields
		foreach(func_get_args() as $argument)
		{
			// not an array
			if(!is_array($argument)) $this->add(new SpoonTextArea($argument));

			// array
			else
			{
				foreach($argument as $name => $defaultValue) $this->add(new SpoonTextArea($name, $defaultValue));
			}
		}
	}


	/**
	 * Adds a single textfield
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $value
	 * @param	int[optional] $maxlength
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 * @param	bool[optional] $HTML
	 */
	public function addTextField($name, $value = null, $maxlength = null, $class = 'inputTextfield', $classError = 'inputTextfieldError', $HTML = false)
	{
		$this->add(new SpoonTextField($name, $value, $maxlength, $class, $classError, $HTML));
		return $this->getField($name);
	}


	/**
	 * Adds one or more textfields
	 *
	 * @return	void
	 */
	public function addTextFields()
	{
		// loop fields
		foreach(func_get_args() as $argument)
		{
			// not an array
			if(!is_array($argument)) $this->add(new SpoonTextField($argument));

			// array
			else
			{
				foreach($argument as $name => $defaultValue) $this->add(new SpoonTextField($name, $defaultValue));
			}
		}
	}


	/**
	 * Adds a single timefield
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $value
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function addTimeField($name, $value = null, $class = 'inputTimefield', $classError = 'inputTimefieldError')
	{
		$this->add(new SpoonTimeField($name, $value, $class, $classError));
		return $this->getField($name);
	}


	/**
	 * Adds one or more timefields
	 *
	 * @return	void
	 */
	public function addTimeFields()
	{
		// loop fields
		foreach(func_get_args() as $argument)
		{
			// not an array
			if(!is_array($argument)) $this->add(new SpoonTimeField($argument));

			// array
			else
			{
				foreach($argument as $name => $defaultValue) $this->add(new SpoonTimeField($name, $defaultValue));
			}
		}
	}


	/**
	 * Loop all the fields and remove the ones that dont need to be in the form
	 *
	 * @return	void
	 */
	public function cleanupFields()
	{
		// create list of fields
		foreach($this->objects as $object)
		{
			// file field should not be added since they are kept within the $_FILES
			if(!($object instanceof SpoonFileField)) $this->fields[] = $object->getName();
		}

		/**
		 * The form key should always automagically be added since the
		 * isSubmitted method counts on this field to check whether or
		 * not the form has been submitted
		 */
		if(!in_array('form', $this->fields)) $this->fields[] = 'form'; // default

		// post method
		if($this->method == 'post')
		{
			// delete unwanted keys
			foreach($_POST as $key => $value) if(!in_array($key, $this->fields)) unset($_POST[$key]);

			// create needed keys
			foreach($this->fields as $field) if(!isset($_POST[$field])) $_POST[$field] = '';
		}

		// get method
		else
		{
			// delete unwanted keys
			foreach($_GET as $key => $value) if(!in_array($key, $this->fields)) unset($_GET[$key]);

			// create needed keys
			foreach($this->fields as $field) if(!isset($_GET[$field])) $_GET[$field] = '';
		}
	}


	/**
	 * Creates a hidden field & adds it to the form
	 *
	 * @return	void
	 */
	private function createHiddenField()
	{
		$this->add(new SpoonHiddenField('form', $this->name));
	}


	/**
	 * Retrieve the action
	 *
	 * @return	string
	 */
	public function getAction()
	{
		return $this->action;
	}


	/**
	 * Retrieve the errors
	 *
	 * @return	string
	 */
	public function getErrors()
	{
		return $this->errors;
	}


	/**
	 * Fetches a field
	 *
	 * @return	SpoonVisualFormElement
	 * @param	string $name
	 */
	public function getField($name)
	{
		// doesn't exist?
		if(!isset($this->objects[(string) $name])) throw new SpoonFormException('The field ('. (string) $name .') does not exist.');

		// all is fine
		return $this->objects[(string) $name];
	}


	/**
	 * Retrieve all fields
	 *
	 * @return	array
	 */
	public function getFields()
	{
		return $this->objects;
	}


	/**
	 * Retrieve the method post/get
	 *
	 * @return	string
	 */
	public function getMethod()
	{
		return $this->method;
	}


	/**
	 * Retrieve the name of this form
	 *
	 * @return	string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * Retrieve the parameters
	 *
	 * @return	array
	 */
	public function getParameters()
	{
		return $this->parameters;
	}


	/**
	 * Retrieve the parameters in html form
	 *
	 * @return	string
	 */
	public function getParametersHTML()
	{
		// start html
		$HTML = '';

		// build & return html
		foreach($this->parameters as $key => $value) $HTML .= ' '. $key .'="'. $value .'"';
		return $HTML;
	}


	/**
	 * Generates an example template, based on the elements already added
	 *
	 * @return	string
	 */
	public function getTemplateExample()
	{
		// start form
		$value = "\n";
		$value .= '{form:'. $this->name ."}\n";

		/**
		 * At first all the hidden fields need to be added to this form, since
		 * they're not shown and are best to be put right beneath the start of the form tag.
		 */
		foreach($this->objects as $object)
		{
			// is a hidden field
			if(($object instanceof SpoonHiddenField) && $object->getName() != 'form')
			{
				$value .= "\t". '{$hid'. SpoonFilter::toCamelCase($object->getName()) ."}\n";
			}
		}

		/**
		 * Add all the objects that are NOT hidden fields. Based on the existance of some methods
		 * errors will or will not be shown.
		 */
		foreach($this->objects as $object)
		{
			// NOT a hidden field
			if(!($object instanceof SpoonHiddenField))
			{
				// buttons
				if($object instanceof SpoonButton)
				{
					$value .= "\t<p>{\$btn". SpoonFilter::toCamelCase($object->getName()) ."}</p>\n";
				}

				// single checkboxes
				elseif($object instanceof SpoonCheckBox)
				{
					$value .= "\t". '<label for="'. $object->getId() .'">'. SpoonFilter::toCamelCase($object->getName()) ."</label>\n";
					$value .= "\t<p>\n";
					$value .= "\t\t{\$chk". SpoonFilter::toCamelCase($object->getName()) ."}\n";
					$value .= "\t\t{\$chk". SpoonFilter::toCamelCase($object->getName()) ."Error}\n";
					$value .= "\t</p>\n";
				}

				// multi checkboxes
				elseif($object instanceof SpoonMultiCheckBox)
				{
					$value .= "\t<p>\n";
					$value .= "\t\t". SpoonFilter::toCamelCase($object->getName()) ."<br />\n";
					$value .= "\t\t{iteration:". $object->getName() ."}\n";
					$value .= "\t\t\t". '<label for="{$'. $object->getName() .'.id}">{$'. $object->getName() .'.chk'. SpoonFilter::toCamelCase($object->getName()) .'} {$'. $object->getName() .'.label}</label>' ."\n";
					$value .= "\t\t{/iteration:". $object->getName() ."}\n";
					$value .= "\t\t". '{$chk'. SpoonFilter::toCamelCase($object->getName()) ."Error}\n";
					$value .= "\t<p>\n";
				}

				// dropdowns
				elseif($object instanceof SpoonDropDown)
				{
					$value .= "\t". '<label for="'. $object->getId() .'">'. SpoonFilter::toCamelCase($object->getName()) ."</label>\n";
					$value .= "\t<p>\n";
					$value .= "\t\t". '{$ddm'. SpoonFilter::toCamelCase($object->getName()) ."}\n";
					$value .= "\t\t". '{$ddm'. SpoonFilter::toCamelCase($object->getName()) ."Error}\n";
					$value .= "\t</p>\n";
				}

				// filefields
				elseif($object instanceof SpoonFileField)
				{
					$value .= "\t". '<label for="'. $object->getId() .'">'. SpoonFilter::toCamelCase($object->getName()) ."</label>\n";
					$value .= "\t<p>\n";
					$value .= "\t\t". '{$file'. SpoonFilter::toCamelCase($object->getName()) ."}\n";
					$value .= "\t\t". '{$file'. SpoonFilter::toCamelCase($object->getName()) ."Error}\n";
					$value .= "\t</p>\n";
				}

				// radiobuttons
				elseif($object instanceof SpoonRadioButton)
				{
					$value .= "\t<p>\n";
					$value .= "\t\t". SpoonFilter::toCamelCase($object->getName()) ."<br />\n";
					$value .= "\t\t{iteration:". $object->getName() ."}\n";
					$value .= "\t\t\t". '<label for="{$'. $object->getName() .'.id}">{$'. $object->getName() .'.rbt'. SpoonFilter::toCamelCase($object->getName()) .'} {$'. $object->getName() .'.label}</label>' ."\n";
					$value .= "\t\t{/iteration:". $object->getName() ."}\n";
					$value .= "\t\t". '{$rbt'. SpoonFilter::toCamelCase($object->getName()) ."Error}\n";
					$value .= "\t<p>\n";
				}

				// textfields
				elseif(($object instanceof SpoonDateField) || ($object instanceof SpoonPasswordField) || ($object instanceof SpoonTextArea) || ($object instanceof SpoonTextField) || ($object instanceof SpoonTimeField))
				{
					$value .= "\t". '<label for="'. $object->getId() .'">'. SpoonFilter::toCamelCase($object->getName()) ."</label>\n";
					$value .= "\t<p>\n";
					$value .= "\t\t". '{$txt'. SpoonFilter::toCamelCase($object->getName()) ."}\n";
					$value .= "\t\t". '{$txt'. SpoonFilter::toCamelCase($object->getName()) ."Error}\n";
					$value .= "\t</p>\n";
				}
			}
		}

		// close form tag
		return $value .'{/form:'. $this->name .'}';
	}


	/**
	 * Fetches all the values for this form as key/value pairs
	 *
	 * @return	array
	 * @param	array[optional] $excluded
	 */
	public function getValues(array $excluded = array())
	{
		// values
		$aValues = array();

		// loop objects
		foreach($this->objects as $object)
		{
			if(method_exists($object, 'getValue') && !in_array($object->getName(), $excluded)) $aValues[$object->getName()] = $object->getValue();
		}

		// return data
		return $aValues;
	}


	/**
	 * Returns the form's status
	 *
	 * @return	bool
	 */
	public function isCorrect()
	{
		// not parsed
		if(!$this->validated) $this->validate();

		// return current status
		return $this->correct;
	}


	/**
	 * Returns whether this form has been submitted
	 *
	 * @return	bool
	 */
	public function isSubmitted()
	{
		// default array
		$aForm = array();

		// post
		if($this->method == 'post' && isset($_POST)) $aForm = $_POST;

		// get
		elseif($this->method == 'get' && isset($_GET)) $aForm = $_GET;

		// name given
		if($this->name != '' && isset($aForm['form']) && $aForm['form'] == $this->name) return true;

		// no name given
		elseif($this->name == '' && $_SERVER['REQUEST_METHOD'] == strtoupper($this->method)) return true;

		// everything else
		return false;
	}


	/**
	 * Parse this form in the given template
	 *
	 * @return	void
	 * @param	SpoonTemplate $template
	 */
	public function parse(SpoonTemplate $template)
	{
		// loop objects
		foreach($this->objects as $name => $object) $object->parse($template);

		// parse form tag
		$template->addForm($this);
	}


	/**
	 * Set the action
	 *
	 * @return	void
	 * @param	string $action
	 */
	public function setAction($action)
	{
		$this->action = (string) $action;
	}


	/**
	 * Sets the correct value
	 *
	 * @return	void
	 * @param	bool[optional] $correct
	 */
	private function setCorrect($correct = true)
	{
		$this->correct = (bool) $correct;
	}


	/**
	 * Set the form method
	 *
	 * @return	void
	 * @param	string[optional] $method
	 */
	public function setMethod($method = 'post')
	{
		$this->method = SpoonFilter::getValue((string) $method, array('get', 'post'), 'post');
	}


	/**
	 * Set the name
	 *
	 * @return	void
	 * @param	string $name
	 */
	private function setName($name)
	{
		$this->name = (string) $name;
	}


	/**
	 * Set a parameter for the form tag
	 *
	 * @return	void
	 * @param	string $key
	 * @param	string $value
	 */
	public function setParameter($key, $value)
	{
		$this->parameters[(string) $key] = (string) $value;
	}


	/**
	 * Set multiple form parameters
	 *
	 * @return	void
	 * @param	array $parameters
	 */
	public function setParameters(array $parameters)
	{
		foreach($parameters as $key => $value) $this->setParameter($key, $value);
	}


	/**
	 * Validates the form (when not wanting to use getcorrect)
	 *
	 * @return	void
	 */
	public function validate()
	{
		// not parsed
        if(!$this->validated)
        {
        	// define errors
        	$errors = '';

			// loop objecjts
			foreach($this->objects as $oElement)
			{
				// check, since some objects don't have this method!
				if(method_exists($oElement, 'getErrors')) $errors .= $oElement->getErrors();
			}

			// affect correct status
			if(trim($errors) != '') $this->correct = false;

            // main form errors?
            if(trim($this->getErrors()) != '') $this->correct = false;

            // update parsed status
            $this->validated = true;
        }
	}
}


/**
 * The base class for every form element
 *
 * @package		html
 * @subpackage	form
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @since		0.1.1
 */
class SpoonFormElement
{
	/**
	 * Custom attributes for this element
	 *
	 * @var	array
	 */
	protected $attributes = array();


	/**
	 * Name of the form this element is a part of
	 *
	 * @var	string
	 */
	protected $formName;


	/**
	 * Html id attribute
	 *
	 * @var	string
	 */
	protected $id;


	/**
	 * Method inherited from the form (post/get)
	 *
	 * @var	string
	 */
	protected $method = 'post';


	/**
	 * HTML name attribute
	 *
	 * @var	string
	 */
	protected $name;


	/**
	 * Reserved attributes. Can not be overwritting using setAttribute(s)
	 *
	 * @var	array
	 */
	protected $reservedAttributes = array('id', 'name', 'value', 'maxlength', 'class', 'style', 'tabindex', 'readonly', 'disabled');


	/**
	 * Retrieves the custom attributes
	 *
	 * @return	array
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}


	/**
	 * Retrieves the custom attributes as HTML
	 *
	 * @return	string
	 * @param	array $variables
	 */
	public function getAttributesHTML(array $variables)
	{
		// init var
		$html = '';

		// loop attributes
		foreach($this->attributes as $key => $value)
		{
			// replace the variables in the output
			$html .= ' '. $key .'="'. str_replace(array_keys($variables), array_values($variables), $value) .'"';
		}

		return $html;
	}


	/**
	 * Retrieves the id attribute
	 *
	 * @return	string
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * Retrieve the form method or the submitted data
	 *
	 * @return	string
	 * @param	bool[optional] $array
	 */
	public function getMethod($array = false)
	{
		if($array) return ($this->method == 'post') ? $_POST : $_GET;
		return $this->method;
	}


	/**
	 * Retrieves the name attribute
	 *
	 * @return	string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * Returns whether this form has been submitted
	 *
	 * @return	bool
	 */
	public function isSubmitted()
	{
		// post/get data
		$data = $this->getMethod(true);

		// name given
		if($this->formName != null && isset($data['form']) && $data['form'] == $this->formName) return true;

		// no name given
		elseif($this->formName == null && $_SERVER['REQUEST_METHOD'] == strtoupper($this->method)) return true;

		// everything else
		else return false;
	}


	/**
	 * Parse the html for the current element
	 *
	 * @return	void
	 * @param	SpoonTemplate[optional] $template
	 */
	public function parse(SpoonTemplate $template = null)
	{
		// filled by subclasses
	}


	/**
	 * Set the name of the form this field is a part of
	 *
	 * @return	void
	 * @param	string $name
	 */
	public function setFormName($name)
	{
		$this->formName = (string) $name;
	}


	/**
	 * Set a custom attribute and its value
	 *
	 * @return	void
	 * @param	string $key
	 * @param	string $value
	 */
	public function setAttribute($key, $value)
	{
		// key is NOT allowed
		if(in_array(strtolower($key), $this->reservedAttributes)) throw new SpoonFormException('The key "'. $key .'" is a reserved attribute an can NOT be overwritten.');

		// set attribute
		$this->attributes[strtolower((string) $key)] = (string) $value;
	}


	/**
	 * Set multiple custom attributes at once
	 *
	 * @return	void
	 * @param	array $attributes
	 */
	public function setAttributes(array $attributes)
	{
		foreach($attributes as $key => $value) $this->setAttribute($key, $value);
	}


	/**
	 * Set the id attribute
	 *
	 * @return	void
	 * @param	string $id
	 */
	public function setId($id)
	{
		$this->id = (string) $id;
	}


	/**
	 * Set the form method
	 *
	 * @return	void
	 * @param	string[optional] $method
	 */
	public function setMethod($method = 'post')
	{
		$this->method = SpoonFilter::getValue($method, array('get', 'post'), 'post');
	}


	/**
	 * Set the name attribute
	 *
	 * @return	void
	 * @param	string $name
	 */
	public function setName($name)
	{
		$this->name = (string) $name;
	}
}


/**
 * The base class for every "visual" form element
 *
 * @package		html
 * @subpackage	form
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @since		0.1.1
 */
class SpoonVisualFormElement extends SpoonFormElement
{
	/**
	 * HTML class attribute
	 *
	 * @var	string
	 */
	protected $class;


	/**
	 * HTML disabled attribute
	 *
	 * @var	bool
	 */
	protected $disabled = false;


	/**
	 * Is it possible to edit this field
	 *
	 * @var	bool
	 */
	protected $readOnly = false;


	/**
	 * HTML style attribute
	 *
	 * @var	string
	 */
	protected $style;


	/**
	 * HTML tabindex attribute
	 *
	 * @var	int
	 */
	protected $tabindex;


	/**
	 * Retrieve the class attribute
	 *
	 * @return	string
	 */
	public function getClass()
	{
		return $this->class;
	}


	/**
	 * Retrieve the disabled status
	 *
	 * @return	bool
	 */
	public function getDisabled()
	{
		return $this->disabled;
	}


	/**
	 * Retrieve the read only status
	 *
	 * @return	bool
	 */
	public function getReadOnly()
	{
		return $this->readOnly;
	}


	/**
	 * Retrieve the style attribute
	 *
	 * @return	string
	 */
	public function getStyle()
	{
		return $this->style;
	}


	/**
	 * Retrieve the tabindex attribute
	 *
	 * @return	int
	 */
	public function getTabIndex()
	{
		return $this->tabindex;
	}


	/**
	 * Set the class attribute
	 *
	 * @return	void
	 * @param	string $class
	 */
	public function setClass($class)
	{
		$this->class = (string) $class;
	}


	/**
	 * Set the disabled attribute
	 *
	 * @return	void
	 * @param	bool[optional] $disabled
	 */
	public function setDisabled($disabled = true)
	{
		$this->disabled = (bool) $disabled;
	}


	/**
	 * Enable/disable the readonly value
	 *
	 * @return	void
	 * @param	bool[optional] $on
	 */
	public function setReadOnly($on = true)
	{
		$this->readOnly = (bool) $on;
	}


	/**
	 * Set the style attribute
	 *
	 * @return	void
	 * @param	string $style
	 */
	public function setStyle($style)
	{
		$this->style = (string) $style;
	}


	/**
	 * Set the tabindex attribute
	 *
	 * @return	void
	 * @param	string $tabindex
	 */
	public function setTabIndex($tabindex)
	{
		$this->tabindex = (int) $tabindex;
	}
}


/**
 * The base class for every text input field
 *
 * @package		html
 * @subpackage	form
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @since		0.1.1
 */
class SpoonInputField extends SpoonVisualFormElement
{
	/**
	 * Class attribute on error
	 *
	 * @var	string
	 */
	protected $classError;


	/**
	 * Errors stack
	 *
	 * @var	string
	 */
	protected $errors;


	/**
	 * Maximum characters
	 *
	 * @var	int
	 */
	protected $maxlength;


	/**
	 * Initial value
	 *
	 * @var	string
	 */
	protected $value;


	/**
	 * Adds an error to the error stack
	 *
	 * @return	void
	 * @param	string $error
	 */
	public function addError($error)
	{
		$this->errors .= (string) $error;
	}


	/**
	 * Retrieves the class based on the errors status
	 *
	 * @return	string
	 */
	public function getClassHTML()
	{
		// default value
		$value = '';

		// has errors
		if($this->errors != '')
		{
			// class & classOnError defined
			if($this->class != '' && $this->classError != '') $value = ' class="'. $this->class .' '. $this->classError .'"';

			// only class defined
			elseif($this->class != '') $value = ' class="'. $this->class .'"';

			// only error defined
			elseif($this->classError != '') $value = ' class="'. $this->classError .'"';
		}

		// no errors
		else
		{
			// class defined
			if($this->class != '') $value = ' class="'. $this->class .'"';
		}

		return $value;
	}


	/**
	 * Retrieve the class on error
	 *
	 * @return	string
	 */
	public function getClassOnError()
	{
		return $this->classError;
	}


	/**
	 * Retrieve the initial value
	 *
	 * @return	string
	 */
	public function getDefaultValue()
	{
		return $this->value;
	}


	/**
	 * Retrieve the errors
	 *
	 * @return	string
	 */
	public function getErrors()
	{
		return $this->errors;
	}


	/**
	 * Retrieve the maxlength attribute
	 *
	 * @return	int
	 */
	public function getMaxlength()
	{
		return $this->maxlength;
	}


	/**
	 * Set the class on error
	 *
	 * @return	void
	 * @param	string $class
	 */
	public function setClassOnError($class)
	{
		$this->classError = (string) $class;
	}


	/**
	 * Overwrites the entire error stack
	 *
	 * @return	void
	 * @param	string $error
	 */
	public function setError($error)
	{
		$this->errors = (string) $error;
	}


	/**
	 * Set the maxlength attribute
	 *
	 * @return	void
	 * @param	int $characters
	 */
	public function setMaxlength($characters)
	{
		$this->maxlength = (int) $characters;
	}
}


/**
 * Creates an html form button
 *
 * @package		html
 * @subpackage	form
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @since		0.1.1
 */
class SpoonButton extends SpoonVisualFormElement
{
	/**
	 * Button type (button, reset or submit)
	 *
	 * @var	string
	 */
	private $type = 'submit';


	/**
	 * Html value attribute
	 *
	 * @var	string
	 */
	private $value;


	/**
	 * Class constructor
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string $value
	 * @param	string[optional] $type
	 * @param	string[optional] $class
	 */
	public function __construct($name, $value, $type = null, $class = 'inputButton')
	{
		// obligated fields
		$this->setId(SpoonFilter::toCamelCase($name, '_', true));
		$this->setName($name);
		$this->setValue($value);

		// custom optional fields
		if($type !== null) $this->setType($type);
		if($class !== null) $this->setClass($class);
	}


	/**
	 * Retrieve the initial value
	 *
	 * @return	string
	 */
	public function getDefaultValue()
	{
		return $this->value;
	}


	/**
	 * Retrieves the button type
	 *
	 * @return	string
	 */
	public function getType()
	{
		return $this->type;
	}


	/**
	 * Retrieves the value attribute
	 *
	 * @return	string
	 */
	public function getValue()
	{
		return $this->value;
	}


	/**
	 * Parse the html for this button
	 *
	 * @return	string
	 * @param	SpoonTemplate[optional] $template
	 */
	public function parse(SpoonTemplate $template = null)
	{
		// name required
		if($this->getName() == '') throw new SpoonFormException('A name is required for a button. Please provide a valid name.');

		// value required
		if($this->getValue() == '') throw new SpoonFormException('A value is required for a button. Please provide a value.');

		// start html generation
		$output = '<input type="'. $this->getType() .'" id="'. $this->getId() .'" name="'. $this->getName() .'" value="'. $this->getValue() .'"';

		// class attribute
		if($this->class !== null) $output .= ' class="'. $this->getClass() .'"';

		// style attribute
		if($this->style !== null) $output .= ' style="'. $this->getStyle() .'"';

		// tabindex attribute
		if($this->tabindex !== null) $output .= ' tabindex="'. $this->getTabIndex() .'"';

		// disabled attribute
		if($this->disabled) $output .= ' disabled="disabled"';

		// custom attributes
		if(count($this->attributes) != 0) $output .= $this->getAttributesHTML(array('[id]' => $this->getId(), '[name]' => $this->getName(), '[value]' => $this->getValue()));

		// close input tag
		$output .= ' />';

		// parse
		if($template !== null) $template->assign('btn'. SpoonFilter::toCamelCase($this->name), $output);

		// cough it up
		return $output;
	}


	/**
	 * Set the button type (button, reset or submit)
	 *
	 * @return	void
	 * @param	string[optional] $type
	 */
	public function setType($type = 'submit')
	{
		$this->type = SpoonFilter::getValue($type,  array('button', 'reset', 'submit'), 'submit');
	}


	/**
	 * Set the value attribute
	 *
	 * @return	void
	 * @param	string $value
	 */
	private function setValue($value)
	{
		$this->value = (string) $value;
	}
}


/**
 * Create an html filefield
 *
 * @package		html
 * @subpackage	form
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @since		0.1.1
 */
class SpoonFileField extends SpoonVisualFormElement
{
	/**
	 * Class attribute on error
	 *
	 * @var	string
	 */
	protected $classError;


	/**
	 * Errors stack
	 *
	 * @var	string
	 */
	protected $errors;


	/**
	 * File extension
	 *
	 * @var	string
	 */
	private $extension;


	/**
	 * Filename (without extension)
	 *
	 * @var	string
	 */
	private $filename;


	/**
	 * Class constructor
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function __construct($name, $class = 'inputFilefield', $classError = 'inputFilefieldError')
	{
		// set name & id
		$this->setName($name);
		$this->setId(SpoonFilter::toCamelCase($name, '_', true));

		// custom optional fields
		if($class !== null) $this->setClass($class);
		if($classError !== null) $this->setClassOnError($classError);
	}


	/**
	 * Adds an error to the error stack
	 *
	 * @return	void
	 * @param	string $error
	 */
	public function addError($error)
	{
		$this->errors .= (string) $error;
	}


	/**
	 * Retrieves the class based on the errors status
	 *
	 * @return	string
	 */
	public function getClassHTML()
	{
		// default value
		$value = '';

		// has errors
		if($this->errors != '')
		{
			// class & classOnError defined
			if($this->class != '' && $this->classError != '') $value = ' class="'. $this->class .' '. $this->classError .'"';

			// only class defined
			elseif($this->class != '') $value = ' class="'. $this->class .'"';

			// only error defined
			elseif($this->classError != '') $value = ' class="'. $this->classError .'"';
		}

		// no errors
		else
		{
			// class defined
			if($this->class != '') $value = ' class="'. $this->class .'"';
		}

		return $value;
	}


	/**
	 * Retrieve the class on error
	 *
	 * @return	string
	 */
	public function getClassOnError()
	{
		return $this->classError;
	}


	/**
	 * Retrieve the errors
	 *
	 * @return	string
	 */
	public function getErrors()
	{
		return $this->errors;
	}


	/**
	 * Retrieve the extension of the uploaded file
	 *
	 * @return	string
	 * @param	bool[optional] $lowercase
	 */
	public function getExtension($lowercase = true)
	{
		return $this->isFilled() ? (SpoonFile::getExtension($_FILES[$this->getName()]['name'], $lowercase)) : '';
	}


	/**
	 * Retrieve the filename of the uploade file
	 *
	 * @return	string
	 * @param	bool[optional] $includeExtension
	 */
	public function getFileName($includeExtension = true)
	{
		if($this->isFilled()) return (!$includeExtension) ? str_replace(SpoonFile::getExtension($_FILES[$this->getName()]['name']), '', $_FILES[$this->getName()]['name']) : $_FILES[$this->getName()]['name'];
		return '';
	}


	/**
	 * Retrieve the filesize of the file in a specified unit
	 *
	 * @return	int
	 * @param	string[optional] $unit
	 * @param	int[optional] $precision
	 */
	public function getFileSize($unit = 'kb', $precision = null)
	{
		if($this->isFilled())
		{
			// redefine unit
			$unit = SpoonFilter::getValue(strtolower($unit), array('b', 'kb', 'mb', 'gb'), 'kb');

			// fetch size
			$size = $_FILES[$this->getName()]['size'];

			// redefine prection
			if($precision !== null) $precision = (int) $precision;

			// bytes
			if($unit == 'b') return $size;

			// kilobytes
			if($unit == 'kb') return round(($size / 1024), $precision);

			// megabytes
			if($unit == 'mb') return round(($size / 1024 / 1024), $precision);

			// gigabytes
			if($unit == 'gb') return round(($size / 1024 / 1024 / 1024), $precision);
		}

		return 0;
	}


	/**
	 * Get the temporary filename
	 *
	 * @return	string
	 */
	public function getTempFileName()
	{
		return $this->isFilled() ? (string) $_FILES[$this->getName()]['tmp_name'] : '';
	}


	/**
	 * Checks if the extension is allowed
	 *
	 * @return	bool
	 * @param	array $extensions
	 * @param	string[optional] $error
	 */
	public function isAllowedExtension(array $extensions, $error = null)
	{
		// file has been uploaded
		if($this->isFilled())
		{
			// search for extension
			$return = in_array(strtolower(SpoonFile::getExtension($_FILES[$this->getName()]['name'])), $extensions);

			// add error if needed
			if(!$return && $error !== null) $this->setError($error);

			// return
			return $return;
		}

		// no file uploaded
		else
		{
			// add error if needed
			if($error !== null) $this->setError($error);

			// return
			return false;
		}
	}


	/**
	 * Checks of the filesize is greater, equal or smaller than the given number + units
	 *
	 * @return	bool
	 * @param	int $size
	 * @param	string[optional] $unit
	 * @param	string[optional] $operator
	 * @param	string[optional] $error
	 */
	public function isFileSize($size, $unit = 'kb', $operator = 'smaller', $error = null)
	{
		// file has been uploaded
		if($this->isFilled())
		{
			// define size
			$actualSize = $this->getFileSize($unit, 0);

			// operator
			$operator = SpoonFilter::getValue(strtolower($operator), array('smaller', 'equal', 'greater'), 'smaller');

			// smaller
			if($operator == 'smaller' && $actualSize < $size) return true;

			// equal
			if($operator == 'equal' && $actualSize == $size) return true;

			// greater
			if($operator == 'greater' && $actualSize == $size) return true;
		}

		// has error
		if($error !== null) $this->setError($error);
		return false;
	}


	/**
	 * Checks for a valid file name (including dots but no slashes and other forbidden characters)
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isFilename($error = null)
	{
		// correct filename
		if($this->isFilled() && SpoonFilter::isFilename($this->getFileName())) return true;

		// has error
		if($error !== null) $this->setError($error);
		return false;
	}


	/**
	 * Checks if this field was submitted & filled
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isFilled($error = null)
	{
		// default error
		$hasError = true;

		// form submitted
		if($this->isSubmitted())
		{
			// submitted, no errors & has a name!
			if(isset($_FILES[$this->getName()]) && $_FILES[$this->getName()]['error'] == 0 && $_FILES[$this->getName()] != '') $hasError = false;
		}

		// has erorr?
		if($hasError)
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Attemps to move the uploaded file to the new location
	 *
	 * @return	bool
	 * @param	string $path
	 * @param	int[optional] $chmod
	 */
	public function moveFile($path, $chmod = 0755)
	{
		// move the file
		$return = @move_uploaded_file($_FILES[$this->getName()]['tmp_name'], (string) $path);

		// chmod file
		SpoonFile::chmod($path, $chmod);

		// return move file status
		return $return;
	}


	/**
	 * Parses the html for this filefield
	 *
	 * @return	string
	 * @param	SpoonTemplate[optional] $template
	 */
	public function parse(SpoonTemplate $template = null)
	{
		// name is required
		if($this->getName() == '') throw new SpoonFormException('A name is required for a file field. Please provide a name.');

		// start html generation
		$output = '<input type="file" id="'. $this->getId() .'" name="'. $this->getName() .'"';

		// class / classOnError
		if($this->getClassHTML() != '') $output .= $this->getClassHTML();

		// style attribute
		if($this->style !== null) $output .= ' style="'. $this->getStyle() .'"';

		// tabindex
		if($this->tabindex !== null) $output .= ' tabindex="'. $this->getTabIndex() .'"';

		// disabled
		if($this->disabled) $output .= ' disabled="disabled"';

		// custom attributes
		if(count($this->attributes) != 0) $output .= $this->getAttributesHTML(array('[id]' => $this->getId(), '[name]' => $this->getName()));

		// end html
		$output .= ' />';

		// parse to template
		if($template !== null)
		{
			$template->assign('file'. SpoonFilter::toCamelCase($this->name), $output);
			$template->assign('file'. SpoonFilter::toCamelCase($this->name) .'Error', ($this->errors!= '') ? '<span class="formError">'. $this->errors .'</span>' : '');
		}

		// cough it up
		return $output;
	}


	/**
	 * Set the class on error
	 *
	 * @return	void
	 * @param	string $class
	 */
	public function setClassOnError($class)
	{
		$this->classError = (string) $class;
	}


	/**
	 * Overwrites the error stack
	 *
	 * @return	void
	 * @param	string $error
	 */
	public function setError($error)
	{
		$this->errors = (string) $error;
	}
}


/**
 * Create an html filefield specific for images
 *
 * @package		html
 * @subpackage	form
 *
 *
 * @author		Tijs Verkoyen <tijs@spoon-library.be>
 * @since		1.1.3
 */
class SpoonImageField extends SpoonFileField
{
	/**
	 * Retrieve the extension of the uploaded file (based on the MIME-type)
	 *
	 * @return	string
	 * @param	bool[optional] $lowercase
	 *
	 */
	public function getExtension($lowercase = true) // @todo tys, $lowercase wordt nergens gebruikt.
	{
		if($this->isSubmitted())
		{
			// get image properties
			$properties = @getimagesize($_FILES[$this->getName()]['tmp_name']);

			// validate properties
			if($properties !== false)
			{
				// get extension
				$extension = image_type_to_extension($properties[2], false);

				// cleanup
				if($extension == 'jpeg') $extension = 'jpg';

				// return
				return $extension;
			}

			// no image
			return '';
		}

		// fallback
		return '';
	}


	/**
	 * Checks if this field was submitted an if it is an image check if the dimensions are ok,
	 * if the submitted file wasn't an image it will return false.
	 *
	 * @return	bool
	 * @param	int $width
	 * @param	int $height
	 * @param	string[optional] $error
	 */
	public function hasMinimumDimensions($width, $height, $error = null)
	{
		// default error
		$hasError = true;

		// form submitted
		if($this->isSubmitted())
		{
			// get image properties
			$properties = @getimagesize($_FILES[$this->getName()]['tmp_name']);

			// valid properties
			if($properties !== false)
			{
				// redefine
				$actualWidth = (int) $properties[0];
				$actualHeight = (int) $properties[1];

				// validate width and height
				if($actualWidth >= $width && $actualHeight >= $height) $hasError = false;
			}
		}

		// has erorr?
		if($hasError)
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks if the mime-type is allowed
	 * @see	http://www.w3schools.com/media/media_mimeref.asp
	 *
	 * @return	bool
	 * @param	array $allowedTypes
	 * @param	string[optional] $error
	 */
	public function isAllowedMimeType(array $allowedTypes, $error = null)
	{
		// file has been uploaded
		if($this->isFilled())
		{
			// get image properties
			$properties = @getimagesize($_FILES[$this->getName()]['tmp_name']);

			// invalid properties
			if($properties === false) $return = false;

			// search for mime-type
			else $return = in_array($properties['mime'], $allowedTypes);

			// add error if needed
			if(!$return && $error !== null) $this->setError($error);

			// return
			return $return;
		}

		// no file uploaded
		else
		{
			// add error if needed
			if($error !== null) $this->setError($error);

			// return
			return false;
		}
	}
}


/**
 * Creates a list of html radiobuttons
 *
 * @package		html
 * @subpackage	form
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @since		0.1.1
 */
class SpoonRadioButton extends SpoonVisualFormElement
{
	/**
	 * Should we allow external data
	 *
	 * @var	bool
	 */
	private $allowExternalData = false;


	/**
	 * Currently checked value
	 *
	 * @var	string
	 */
	private $checked;


	/**
	 * Class attribute on error
	 *
	 * @var	string
	 */
	private $classError;


	/**
	 * Errors stack
	 *
	 * @var	string
	 */
	private $errors;


	/**
	 * List of labels and their values
	 *
	 * @var	string
	 */
	protected $values;


	/**
	 * Class constructor
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string $values
	 * @param	string[optional] $checked
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function __construct($name, array $values, $checked = null, $class = 'inputRadiobutton', $classError = 'inputRadiobuttonError')
	{
		// obligated fields
		$this->setName($name);
		$this->setValues($values);

		// custom optional fields
		if($checked !== null) $this->setChecked($checked);
		if($class !== null) $this->setClass($class);
		if($classError !== null) $this->setClassOnError($classError);
	}


	/**
	 * Adds an error to the error stack
	 *
	 * @return	void
	 * @param	string $error
	 */
	public function addError($error)
	{
		$this->errors .= (string) $error;
	}


	/**
	 * Retrieve the value of the checked item
	 *
	 * @return	bool
	 */
	public function getChecked()
	{
		/**
		 * If we want to retrieve the checked status, we should first
		 * ensure that the value we return is correct, therefor we
		 * check the $_POST/$_GET array for the right value & ajust it if needed.
		 */

		// post/get data
		$data = $this->getMethod(true);

		// form submitted
		if($this->isSubmitted())
		{
			// currently field checked
			if(isset($data[$this->getName()]) && isset($this->values[(string) $data[$this->getName()]]))
			{
				// set this field as checked
				$this->setChecked($data[$this->getName()]);
			}
		}

		return $this->checked;
	}


	/**
	 * Retrieves the class based on the error status
	 *
	 * @return	string
	 */
	public function getClassHTML()
	{
		// default value
		$value = '';

		// has errors
		if($this->errors != '')
		{
			// class & classOnError defined
			if($this->class != '' && $this->classError != '') $value = ' class="'. $this->class .' '. $this->classError .'"';

			// only class defined
			elseif($this->class != '') $value = ' class="'. $this->class .'"';

			// only error defined
			elseif($this->classError != '') $value = ' class="'. $this->classError .'"';
		}

		// no errors
		else
		{
			// class defined
			if($this->class != '') $value = ' class="'. $this->class .'"';
		}

		return $value;
	}


	/**
	 * Retrieve the class on error
	 *
	 * @return	string
	 */
	public function getClassOnError()
	{
		return $this->classError;
	}


	/**
	 * Retrieve the errors
	 *
	 * @return	string
	 */
	public function getErrors()
	{
		return $this->errors;
	}


	/**
	 * This methid should not be used
	 *
	 * @return	void
	 */
	public function getId()
	{
		return;
	}


	/**
	 * Retrieves the initial or submitted value
	 *
	 * @return	string
	 */
	public function getValue()
	{
		// default value (may be null)
		$value = $this->getChecked();

		// post/get data
		$data = $this->getMethod(true);

		// form submitted
		if($this->isSubmitted())
		{
			// submitted by post (may be empty)
			if($this->allowExternalData) $value = $data[$this->getName()];
			elseif(isset($data[$this->getName()]) && isset($this->values[(string) $data[$this->getName()]])) $value = $data[$this->getName()];
		}

		return $value;
	}


	/**
	 * Checks if this field was submitted & filled
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isFilled($error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// correct
		if(isset($data[$this->getName()]) && isset($this->values[$data[$this->getName()]])) return true;

		// oh-oh
		if($error !== null) $this->setError($error);
		return false;
	}


	/**
	 * Parse the html for this button
	 *
	 * @return	array
	 * @param	SpoonTemplate[optional] $template
	 */
	public function parse(SpoonTemplate $template = null)
	{
		// name required
		if($this->getName() == '') throw new SpoonFormException('A name is required for a radiobutton. Please provide a valid name.');

		// loop the values
		foreach($this->values as $value => $label)
		{
			// init vars
			$aElement['id'] = SpoonFilter::toCamelCase($this->getName() .'_'. $value, '_', true);
			$aElement['label'] = $label;
			$aElement['value'] = $value;
			$name = 'rbt'. SpoonFilter::toCamelCase($this->getName());
			$aElement[$name] = '';

			// start html generation
			$aElement[$name] = '<input type="radio" id="'. $aElement['id'] .'" name="'. $this->getName() .'" value="'. $value .'"';

			// class / classOnError
			if($this->getClassHTML() != '') $aElement[$name] .= $this->getClassHTML();

			// style attribute
			if($this->style !== null) $aElement[$name] .= ' style="'. $this->getStyle() .'"';

			// tabindex attribute
			if($this->tabindex !== null) $aElement[$name] .= ' tabindex="'. $this->getTabIndex() .'"';

			// disabled attribute
			if($this->disabled) $aElement[$name] .= ' disabled="disabled"';

			// readonly
			if($this->readOnly) $aElement[$name] .= ' readonly="readonly"';

			// checked
			if($this->getChecked() == $value) $aElement[$name] .= ' checked="checked"';

			// custom attributes
			if(count($this->attributes) != 0) $aElement[$name] .= $this->getAttributesHTML(array('[id]' => $aElement['id'], '[name]' => $this->getName(), '[value]' => $value));

			// close input tag
			$aElement[$name] .= ' />';

			// add radiobutton
			$aRadioButton[] = $aElement;
		}

		// template
		if($template !== null)
		{
			$template->assign($this->getName(), $aRadioButton);
			$template->assign('rbt'. SpoonFilter::toCamelCase($this->getName()) .'Error', ($this->errors!= '') ? '<span class="formError">'. $this->errors .'</span>' : '');
		}

		// cough
		return $aRadioButton;
	}


	/**
	 * Set the checked value
	 *
	 * @return	void
	 * @param	string $checked
	 */
	public function setChecked($checked)
	{
		// doesnt exist
		if(!isset($this->values[(string) $checked])) throw new SpoonFormException('This value "'. (string) $checked .'" is not among the list of values.');

		// exists
		$this->checked = (string) $checked;
	}


	/**
	 * Set the class on error
	 *
	 * @return	void
	 * @param	string $class
	 */
	public function setClassOnError($class)
	{
		$this->classError = (string) $class;
	}


	/**
	 * Overwrites the error stack
	 *
	 * @return	void
	 * @param	string $error
	 */
	public function setError($error)
	{
		$this->errors = (string) $error;
	}


	/**
	 * This method should not be used
	 *
	 * @return	void
	 * @param	string $id
	 */
	public function setId($id)
	{
		throw new SpoonFormException('This method is not to be used with this class.');
	}


	/**
	 * Set the labels and their values
	 *
	 * @return	void
	 * @param	array $values
	 */
	private function setValues(array $values)
	{
		foreach($values as $value => $label) $this->values[(string) $value] = (string) $label;
	}
}


/**
 * Creates an html textfield (date field)
 *
 * @package		html
 * @subpackage	form
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @since		0.1.1
 */
class SpoonDateField extends SpoonInputField
{
	/**
	 * Input mask (every item may only occur once)
	 *
	 * @var	string
	 */
	protected $mask = 'd-m-Y';


	/**
	 * The value needed to base the mask on
	 *
	 * @var	int
	 */
	private $defaultValue;


	/**
	 * Class constructor
	 *
	 * @return	void
	 * @param	string $name
	 * @param	int[optional] $value
	 * @param	string[optional] $mask
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function __construct($name, $value = null, $mask = null, $class = 'inputDatefield', $classError = 'inputDatefieldError')
	{
		// obligated fields
		$this->setId(SpoonFilter::toCamelCase($name, '_', true));
		$this->setName($name);

		/**
		 * The input mask defines the maxlength attribute, therefor
		 * this needs to be set anyhow. The mask needs to be updated
		 * before the value is set, or the old mask (in case it differs)
		 * will automatically be used.
		 */
		$this->setMask(($mask !== null) ? $mask : $this->mask);

		/**
		 * The value will be filled based on the default input mask
		 * if no value has been defined.
		 */
		$this->defaultValue = ($value !== null) ? (int) $value : time();
		$this->setValue($this->defaultValue);


//		if($value !== null) $this->setValue($value);
//		else $this->value = date($this->mask);

		// custom optional fields
		if($class !== null) $this->setClass($class);
		if($classError !== null) $this->setClassOnError($classError);
	}


	/**
	 * Retrieve the initial value
	 *
	 * @return	string
	 */
	public function getDefaultValue()
	{
		return $this->value;
	}


	/**
	 * Retrieve the input mask
	 *
	 * @return	string
	 */
	public function getMask()
	{
		return $this->mask;
	}


	/**
	 * Returns a timestamp based on mask & optional fields
	 *
	 * @return	int
	 * @param	int[optional] $year
	 * @param	int[optional] $month
	 * @param	int[optional] $day
	 * @param	int[optional] $hour
	 * @param	int[optional] $minute
	 * @param	int[optional] $second
	 */
	public function getTimestamp($year = null, $month = null, $day = null, $hour = null, $minute = null, $second = null)
	{
		// field has been filled in
		if($this->isFilled())
		{
			// post/get data
			$data = $this->getMethod(true);

			// valid field
			if($this->isValid())
			{
				// define long mask
				$longMask = str_replace(array('d', 'm', 'y'), array('dd', 'mm', 'yyyy'), $this->mask);

				// year found
				if(strpos($longMask, 'yyyy') !== false)
				{
					// redefine year
					$year = substr($data[$this->getName()], strpos($longMask, 'yyyy'), 4);
				}

				// month found
				if(strpos($longMask, 'mm') !== false)
				{
					// redefine month
					$month = substr($data[$this->getName()], strpos($longMask, 'mm'), 2);
				}

				// day found
				if(strpos($longMask, 'dd') !== false)
				{
					// redefine day
					$day = substr($data[$this->getName()], strpos($longMask, 'dd'), 2);
				}
			}
		}

		// create (default) time
		return mktime($hour, $minute, $second, $month, $day, $year);
	}


	/**
	 * Retrieve the initial or submitted value
	 *
	 * @return	string
	 */
	public function getValue()
	{
		// redefine html & value
		$value = $this->value;

		// added to form
		if($this->isSubmitted())
		{
			// post/get data
			$data = $this->getMethod(true);

			// submitted by post (may be empty)
			if(isset($data[$this->getName()]))
			{
				// value
				$value = $data[$this->getName()];
			}
		}

		return $value;
	}


	/**
	 * Checks if this field has any content (except spaces)
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isFilled($error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// check filled status
		if(!(isset($data[$this->getName()]) && trim($data[$this->getName()]) != ''))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks if this field is correctly submitted
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isValid($error = null)
	{
		// field has been filled in
		if($this->isFilled())
		{
			// post/get data
			$data = $this->getMethod(true);

			// maxlength checks out (needs to be equal)
			if(strlen($data[$this->getName()]) == $this->maxlength)
			{
				// define long mask
				$longMask = str_replace(array('d', 'm', 'y', 'Y'), array('dd', 'mm', 'yy', 'yyyy'), $this->mask);

				// init vars
				$year = (int) date('Y');
				$month = (int) date('m');
				$day = (int) date('d');

				// validate year (yyyy)
				if(strpos($longMask, 'yyyy') !== false)
				{
					// redefine year
					$year = substr($data[$this->getName()], strpos($longMask, 'yyyy'), 4);

					// not an int
					if(!SpoonFilter::isInteger($year)) { $this->setError($error); return false; }

					// invalid year
					if(!checkdate(1, 1, $year)) { $this->setError($error); return false; }
				}

				// validate year (yy)
				if(strpos($longMask, 'yy') !== false && strpos($longMask, 'yyyy') === false)
				{
					// redefine year
					$year = substr($data[$this->getName()], strpos($longMask, 'yy'), 2);

					// not an int
					if(!SpoonFilter::isInteger($year)) { $this->setError($error); return false; }

					// invalid year
					if(!checkdate(1, 1, '19'. $year)) { $this->setError($error); return false; }
				}

				// validate month (mm)
				if(strpos($longMask, 'mm') !== false)
				{
					// redefine month
					$month = substr($data[$this->getName()], strpos($longMask, 'mm'), 2);

					// not an int
					if(!SpoonFilter::isInteger($month)) { $this->setError($error); return false; }

					// invalid month
					if(!checkdate($month, 1, $year)) { $this->setError($error); return false; }
				}

				// validate day (dd)
				if(strpos($longMask, 'dd') !== false)
				{
					// redefine day
					$day = substr($data[$this->getName()], strpos($longMask, 'dd'), 2);

					// not an int
					if(!SpoonFilter::isInteger($day)) { $this->setError($error); return false; }

					// invalid day
					if(!checkdate($month, $day, $year)) { $this->setError($error); return false; }
				}
			}

			// maximum length doesn't check out
			else { $this->setError($error); return false; }
		}

		// not filled out
		else { $this->setError($error); return false; }

		/**
		 * When the code reaches the point, it means no errors have occured
		 * and truth will out!
		 */
		return true;
	}


	/**
	 * Parses the html for this date field
	 *
	 * @return	string
	 * @param	SpoonTemplate[optional] $template
	 */
	public function parse(SpoonTemplate $template = null)
	{
		// name is required
		if($this->getName() == '') throw new SpoonFormException('A name is required for a date field. Please provide a valid name.');

		// start html generation
		$output = '<input type="text" id="' . $this->getId() . '" name="' . $this->getName() .'" value="'. $this->getValue() .'" maxlength="'. $this->maxlength .'"';

		// class / classOnError
		if($this->getClassHTML() != '') $output .= $this->getClassHTML();

		// style attribute
		if($this->style !== null) $output .= ' style="'. $this->getStyle() .'"';

		// tabindex
		if($this->tabindex !== null) $output .= ' tabindex="'. $this->getTabIndex() .'"';

		// readonly
		if($this->readOnly) $output .= ' readonly="readonly"';

		// disabled
		if($this->disabled) $output .= ' disabled="disabled"';

		// custom attributes
		if(count($this->attributes) != 0) $output .= $this->getAttributesHTML(array('[id]' => $this->getId(), '[name]' => $this->getName(), '[value]' => $this->getValue()));

		// end html
		$output .= ' />';

		// template
		if($template !== null)
		{
			$template->assign('txt'. SpoonFilter::toCamelCase($this->name), $output);
			$template->assign('txt'. SpoonFilter::toCamelCase($this->name) .'Error', ($this->errors!= '') ? '<span class="formError">'. $this->errors .'</span>' : '');
		}

		// cough
		return $output;
	}


	/**
	 * Set the input mask
	 *
	 * @return	void
	 * @param	string[optional] $mask
	 */
	public function setMask($mask = null)
	{
		// redefine mask
		$mask = ($mask !== null) ? (string) $mask : $this->mask;

		// allowed characters
		$aCharachters = array('.', '-', '/', 'd', 'm', 'y', 'Y');

		// new mask
		$maskCorrected = '';

		// loop all elements
		for($i = 0; $i < strlen($mask); $i++)
		{
			// char allowed
			if(in_array(substr($mask, $i, 1), $aCharachters)) $maskCorrected .= substr($mask, $i, 1);
		}

		// new mask
		$this->mask = $maskCorrected;

		// define maximum length for this element
		$maskCorrected = str_replace(array('d', 'm', 'y', 'Y'), array('dd', 'mm', 'yy', 'yyyy'), $maskCorrected);

		// update maxium length
		$this->maxlength = strlen($maskCorrected);

		// update value
		if($this->defaultValue !== null) $this->setValue($this->defaultValue);
	}


	/**
	 * This method is overwritten by the setMask, and therefor useless
	 *
	 * @return	void
	 * @param	int $characters
	 */
	public function setMaxlength($characters)
	{
		throw new SpoonFormException('This method is not to be used with the SpoonDateField class. The maxlength is generated automatically based on the input mask.');
	}


	/**
	 * Set the value attribute for this date field
	 *
	 * @return	void
	 * @param	int $value
	 */
	private function setValue($value)
	{
		$this->value = date($this->mask, (int) $value);
	}
}


/**
 * Create an html textfield
 *
 * @package		html
 * @subpackage	form
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @since		0.1.1
 */
class SpoonTextField extends SpoonInputField
{
	/**
	 * Is the content of this field html?
	 *
	 * @var	bool
	 */
	private $isHTML = false;


	/**
	 * Class constructor
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $value
	 * @param	int[optional] $maxlength
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 * @param	bool[optional] $HTML
	 */
	public function __construct($name, $value = null, $maxlength = null, $class = 'inputTextfield', $classError = 'inputTextfieldError', $HTML = false)
	{
		// obligated fields
		$this->setId(SpoonFilter::toCamelCase($name, '_', true));
		$this->setName($name);

		// custom optional fields
		if($value !== null) $this->setValue($value);
		if($maxlength !== null) $this->setMaxlength($maxlength);
		if($class !== null) $this->setClass($class);
		if($classError !== null) $this->setClassOnError($classError);
		$this->isHTML($HTML);
	}


	/**
	 * Retrieve the initial or submitted value
	 *
	 * @return	string
	 * @param	bool[optional] $allowHTML
	 */
	public function getValue($allowHTML = null)
	{
		// redefine html & default value
		$allowHTML = ($allowHTML !== null) ? (bool) $allowHTML : $this->isHTML;
		$value = ($this->isHTML) ? SpoonFilter::htmlentities($this->value) : $this->value;

		// form submitted
		if($this->isSubmitted())
		{
			// post/get data
			$data = $this->getMethod(true);

			// submitted by post (may be empty)
			if(isset($data[$this->getName()]))
			{
				// value
				$value = $data[$this->getName()];

				// maximum length?
				if($this->maxlength != '') mb_substr($value, 0, $this->maxlength, SPOON_CHARSET);

				// html allowed?
				if(!$allowHTML) $value = SpoonFilter::htmlentities($value);
			}
		}

		return $value;
	}


	/**
	 * Checks if this field contains only letters a-z and A-Z
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isAlphabetical($error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isAlphabetical($data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks if this field only contains letters & numbers (without spaces)
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isAlphaNumeric($error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isAlphaNumeric($data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks if the field is between a given minimum and maximum (includes min & max)
	 *
	 * @return	bool
	 * @param	int $minimum
	 * @param	int $maximum
	 * @param	string[optional] $error
	 */
	public function isBetween($minimum, $maximum, $error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isBetween($minimum, $maximum, $data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks this field for a boolean (true/false | 0/1)
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isBool($error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isBool($data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks if this field only contains numbers 0-9
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isDigital($error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isDigital($data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks this field for a valid e-mail address
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isEmail($error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isEmail($data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks for a valid file name (including dots but no slashes and other forbidden characters)
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isFilename($error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isFilename($data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks if this field was submitted & filled
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isFilled($error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!(isset($data[$this->getName()]) && trim($data[$this->getName()]) != ''))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks this field for numbers 0-9 and an optional - (minus) sign (in the beginning only)
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isFloat($error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isFloat($data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks if this field is greater than another value
	 *
	 * @return	bool
	 * @param	int $minimum
	 * @param	string[optional] $error
	 */
	public function isGreaterThan($minimum, $error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isGreaterThan($minimum, $data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Make spoon aware that this field contains html
	 *
	 * @return	void
	 * @param	bool[optional] $on
	 */
	public function isHTML($on = true)
	{
		$this->isHTML = (bool) $on;
	}


	/**
	 * Checks this field for numbers 0-9 and an optional - (minus) sign (in the beginning only)
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isInteger($error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isInteger($data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks if this field is a proper ip address
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isIp($error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isIp($data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks if this field does not exceed the given maximum
	 *
	 * @return	bool
	 * @param	int $maximum
	 * @param	int[optional] $error
	 */
	public function isMaximum($maximum, $error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isMaximum($maximum, $data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks if this field's length is less (or equal) than the given maximum
	 *
	 * @return	bool
	 * @param	int $maximum
	 * @param	string[optional] $error
	 */
	public function isMaximumCharacters($maximum, $error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isMaximumCharacters($maximum, $data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks if this field is at least a given minimum
	 *
	 * @return	bool
	 * @param	int $minimum
	 * @param	string[optional] $error
	 */
	public function isMinimum($minimum, $error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isMinimum($minimum, $data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks if this field's length is more (or equal) than the given minimum
	 *
	 * @return	bool
	 * @param	int $minimum
	 * @param	string[optional] $error
	 */
	public function isMinimumCharacters($minimum, $error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isMinimumCharacters($minimum, $data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Alias for isDigital (Field may only contain numbers 0-9)
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isNumeric($error = null)
	{
		return $this->isDigital($error);
	}


	/**
	 * Checks if the field is smaller than a given maximum
	 *
	 * @return	bool
	 * @param	int $maximum
	 * @param	string[optional] $error
	 */
	public function isSmallerThan($maximum, $error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isSmallerThan($maximum, $data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks if this field contains any string that doesn't have control characters (ASCII 0 - 31) but spaces are allowed
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isString($error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isString($data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks this field for a valid url
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isURL($error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isURL($data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks if the field validates against the regexp
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isValidAgainstRegexp($regexp, $error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isValidAgainstRegexp((string) $regexp, $data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Parses the html for this textfield
	 *
	 * @return	string
	 * @param	SpoonTemplate[optional] $template
	 */
	public function parse(SpoonTemplate $template = null)
	{
		// name is required
		if($this->getName() == '') throw new SpoonFormException('A name is required for a textfield. Please provide a name.');

		// start html generation
		$output = '<input type="text" id="'. $this->id .'" name="'. $this->name .'" value="'. $this->getValue() .'"';

		// maximum number of characters
		if($this->maxlength !== null) $output .= ' maxlength="'. $this->maxlength .'"';

		// class / classOnError
		if($this->getClassHTML() != '') $output .= $this->getClassHTML();

		// style attribute
		if($this->style !== null) $output .= ' style="'. $this->style .'"';

		// tabindex
		if($this->tabindex !== null) $output .= ' tabindex="'. $this->tabindex .'"';

		// readonly
		if($this->readOnly) $output .= ' readonly="readonly"';

		// disabled
		if($this->disabled) $output .= ' disabled="disabled"';

		// custom attributes
		if(count($this->attributes) != 0) $output .= $this->getAttributesHTML(array('[id]' => $this->getId(), '[name]' => $this->getName(), '[value]' => $this->getValue()));

		// end html
		$output .= ' />';

		// template
		if($template !== null)
		{
			$template->assign('txt'. SpoonFilter::toCamelCase($this->name), $output);
			$template->assign('txt'. SpoonFilter::toCamelCase($this->name) .'Error', ($this->errors!= '') ? '<span class="formError">'. $this->errors .'</span>' : '');
		}

		// cough
		return $output;
	}


	/**
	 * Set the initial value
	 *
	 * @return	void
	 * @param	string $value
	 */
	private function setValue($value)
	{
		$this->value = (string) $value;
	}
}


/**
 * Create an html password field
 *
 * @package		html
 * @subpackage	form
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @since		0.1.1
 */
class SpoonPasswordField extends SpoonInputField
{
	/**
	 * Is the content of this field html?
	 *
	 * @var	bool
	 */
	private $isHTML = false;


	/**
	 * Class constructor
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $value
	 * @param	int[optional] $maxlength
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 * @param	bool[optional] $HTML
	 */
	public function __construct($name, $value = null, $maxlength = null, $class = 'inputPassword', $classError = 'inputPasswordError', $HTML = false)
	{
		// obligated fields
		$this->setId(SpoonFilter::toCamelCase($name, '_', true));
		$this->setName($name);

		// custom optional fields
		if($value !== null) $this->setValue($value);
		if($maxlength !== null) $this->setMaxlength($maxlength);
		if($class !== null) $this->setClass($class);
		if($classError !== null) $this->setClassOnError($classError);
		$this->isHTML($HTML);
	}


	/**
	 * Retrieve the initial or submitted value
	 *
	 * @return	string
	 * @param	bool[optional] $allowHTML
	 */
	public function getValue($allowHTML = null)
	{
		// redefine html & default value
		$allowHTML = ($allowHTML !== null) ? (bool) $allowHTML : $this->isHTML;
		$value = ($this->isHTML) ? SpoonFilter::htmlentities($this->value) : $this->value;

		// form submitted
		if($this->isSubmitted())
		{
			// post/get data
			$data = $this->getMethod(true);

			// submitted by post (may be empty)
			if(isset($data[$this->getName()]))
			{
				// value
				$value = $data[$this->getName()];

				// maximum length?
				if($this->maxlength != '') mb_substr($value, 0, $this->maxlength, SPOON_CHARSET);

				// html allowed?
				if(!$allowHTML) $value = SpoonFilter::htmlentities($value);
			}
		}

		return $value;
	}


	/**
	 * Checks if this field contains only letters a-z and A-Z
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isAlphabetical($error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isAlphabetical($data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks if this field only contains letters & numbers (without spaces)
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isAlphaNumeric($error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isAlphaNumeric($data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks if this field was submitted & filled
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isFilled($error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!(isset($data[$this->getName()]) && trim($data[$this->getName()]) != ''))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Make spoon aware that this field contains html
	 *
	 * @return	void
	 * @param	bool[optional] $on
	 */
	public function isHTML($on = true)
	{
		$this->isHTML = (bool) $on;
	}


	/**
	 * Checks if this field's length is less (or equal) than the given maximum
	 *
	 * @return	bool
	 * @param	int $maximum
	 * @param	string[optional] $error
	 */
	public function isMaximumCharacters($maximum, $error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isMaximumCharacters($maximum, $data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks if this field's length is more (or equal) than the given minimum
	 *
	 * @return	bool
	 * @param	int $minimum
	 * @param	string[optional] $error
	 */
	public function isMinimumCharacters($minimum, $error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isMinimumCharacters($minimum, $data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks if the field validates against the regexp
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isValidAgainstRegexp($regexp, $error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isValidAgainstRegexp($regexp, $data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Parses the html for this textfield
	 *
	 * @return	string
	 * @param	SpoonTemplate[optional] $template
	 */
	public function parse(SpoonTemplate $template = null)
	{
		// name is required
		if($this->getName() == '') throw new SpoonFormException('A name is required for a password field. Please provide a name.');

		// start html generation
		$output = '<input type="password" id="'. $this->id .'" name="'. $this->name .'" value="'. $this->getValue() .'"';

		// maximum number of characters
		if($this->maxlength) $output .= ' maxlength="'. $this->maxlength .'"';

		// class / classOnError
		if($this->getClassHTML() != '') $output .= $this->getClassHTML();

		// style attribute
		if($this->style !== null) $output .= ' style="'. $this->style .'"';

		// tabindex
		if($this->tabindex !== null) $output .= ' tabindex="'. $this->tabindex .'"';

		// readonly
		if($this->readOnly) $output .= ' readonly="readonly"';

		// disabled
		if($this->disabled) $output .= ' disabled="disabled"';

		// custom attributes
		if(count($this->attributes) != 0) $output .= $this->getAttributesHTML(array('[id]' => $this->getId(), '[name]' => $this->getName(), '[value]' => $this->getValue()));

		// end html
		$output .= ' />';

		// template
		if($template !== null)
		{
			$template->assign('txt'. SpoonFilter::toCamelCase($this->name), $output);
			$template->assign('txt'. SpoonFilter::toCamelCase($this->name) .'Error', ($this->errors!= '') ? '<span class="formError">'. $this->errors .'</span>' : '');
		}

		// cough
		return $output;
	}


	/**
	 * Set the initial value
	 *
	 * @return	void
	 * @param	string $value
	 */
	private function setValue($value)
	{
		$this->value = (string) $value;
	}
}


/**
 * Create an html textarea
 *
 * @package		html
 * @subpackage	form
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @since		0.1.1
 */
class SpoonTextArea extends SpoonInputField
{
	/**
	 * Number of columns
	 *
	 * @var	int
	 */
	private $cols = 62;


	/**
	 * Is html allowed?
	 *
	 * @var	bool
	 */
	private $isHTML = false;


	/**
	 * Number of rows
	 *
	 * @var	int
	 */
	private $rows = 5;


	/**
	 * Class constructor
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $value
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 * @param	bool[optional] $HTML
	 */
	public function __construct($name, $value = null, $class = 'inputTextarea', $classError = 'inputTextareaError', $HTML = false)
	{
		// obligated fields
		$this->setId(SpoonFilter::toCamelCase($name, '_', true));
		$this->setName($name);

		// custom optional fields
		$this->setValue($value);
		if($class !== null) $this->setClass($class);
		if($classError !== null) $this->setClassOnError($classError);
		$this->isHTML($HTML);
	}


	/**
	 * Retrieve the number of cols
	 *
	 * @return	mixed
	 */
	public function getCols()
	{
		return $this->cols;
	}


	/**
	 * This method is not to be used with this class
	 *
	 * @return	void
	 */
	public function getMaxlength()
	{
		throw new SpoonFormException('This method is not to be used with the SpoonTextArea class.');
	}


	/**
	 * Retrieve the number of rows
	 *
	 * @return	mixed
	 */
	public function getRows()
	{
		return $this->rows;
	}


	/**
	 * Retrieve the initial or submitted value
	 *
	 * @return	string
	 * @param	bool[optional] $allowHTML
	 */
	public function getValue($allowHTML = null)
	{
		// redefine html & default value
		$allowHTML = ($allowHTML !== null) ? (bool) $allowHTML : $this->isHTML;
		$value = ($this->isHTML) ? SpoonFilter::htmlentities($this->value) : $this->value;

		// added to form
		if($this->isSubmitted())
		{
			// post/get data
			$data = $this->getMethod(true);

			// submitted by post (may be empty)
			if(isset($data[$this->getName()]))
			{
				// value
				$value = $data[$this->getName()];

				// html allowed?
				if(!$allowHTML) $value = SpoonFilter::htmlentities($value);
			}
		}

		return $value;
	}


	/**
	 * Checks if this field contains only letters a-z and A-Z
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isAlphabetical($error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isAlphabetical($data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks if this field only contains letters & numbers (without spaces)
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isAlphaNumeric($error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isAlphaNumeric($data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks if this field was submitted & filled
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isFilled($error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!(isset($data[$this->getName()]) && trim($data[$this->getName()]) != ''))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Make spoon aware that this field contains html
	 *
	 * @return	void
	 * @param	bool[optional] $on
	 */
	public function isHTML($on = true)
	{
		$this->isHTML = (bool) $on;
	}


	/**
	 * Checks if this field's length is less (or equal) than the given maximum
	 *
	 * @return	bool
	 * @param	int $maximum
	 * @param	string[optional] $error
	 */
	public function isMaximumCharacters($maximum, $error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isMaximumCharacters($maximum, $data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks if this field's length is more (or equal) than the given minimum
	 *
	 * @return	bool
	 * @param	int $minimum
	 * @param	string[optional] $error
	 */
	public function isMinimumCharacters($minimum, $error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isMinimumCharacters($minimum, $data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks if this field contains any string that doesn't have control characters (ASCII 0 - 31) but spaces are allowed
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isString($error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!SpoonFilter::isString($data[$this->getName()]))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Parses the html for this textarea
	 *
	 * @return	string
	 * @param	SpoonTemplate[optional] $template
	 */
	public function parse(SpoonTemplate $template = null)
	{
		// name is required
		if($this->getName() == '') throw new SpoonFormException('A name is requird for a textarea. Please provide a valid name.');

		// start html generation
		$output = '<textarea id="'. $this->getId() .'" name="'. $this->getName() .'"';

		// class / classOnError
		if($this->getClassHTML() != '') $output .= $this->getClassHTML();

		// style attribute
		if($this->style != '') $output .= ' style="'. $this->getStyle() .'"';

		// tabindex
		if($this->tabindex !== null) $output .= ' tabindex="'. $this->getTabIndex() .'"';

		// disabled
		if($this->disabled) $output .= ' disabled="disabled"';

		// readonly
		if($this->readOnly) $output .= ' readonly="readonly"';

		// rows & columns
		$output .= ' cols="'. $this->getCols() .'"';
		$output .= ' rows="'. $this->getRows() .'"';

		// custom attributes
		if(count($this->attributes) != 0) $output .= $this->getAttributesHTML(array('[id]' => $this->getId(), '[name]' => $this->getName(), '[value]' => $this->getValue()));

		// close first tag
		$output .= '>';

		// add value
		$output .= $this->getValue();

		// end tag
		$output .= '</textarea>';

		// template
		if($template !== null)
		{
			$template->assign('txt'. SpoonFilter::toCamelCase($this->name), $output);
			$template->assign('txt'. SpoonFilter::toCamelCase($this->name) .'Error', ($this->errors!= '') ? '<span class="formError">'. $this->errors .'</span>' : '');
		}

		return $output;
	}


	/**
	 * Set the number of columns
	 *
	 * @return	void
	 * @param	int $cols
	 */
	public function setCols($cols)
	{
		$this->cols = (int) $cols;
	}


	/**
	 * This method is not to be used with this class
	 *
	 * @return	void
	 * @param	int $characters
	 */
	public function setMaxlength($characters)
	{
		throw new SpoonFormException('This method is not to be used with the SpoonTextArea class.');
	}


	/**
	 * Set the number of rows
	 *
	 * @return	void
	 * @param	int $rows
	 */
	public function setRows($rows)
	{
		$this->rows = (int) $rows;
	}


	/**
	 * Set the initial value
	 *
	 * @return	void
	 * @param	string $value
	 */
	private function setValue($value)
	{
		$this->value = (string) $value;
	}
}


/**
 * Creates an html time field
 *
 * @package		html
 * @subpackage	form
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @since		0.1.1
 */
class SpoonTimeField extends SpoonInputField
{
	/**
	 * Class constructor
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $value
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function __construct($name, $value = null, $class = 'inputTimefield', $classError = 'inputTimefieldError')
	{
		// obligated fields
		$this->setId(SpoonFilter::toCamelCase($name, '_', true));
		$this->setName($name);

		/**
		 * If no value has presented, the current time
		 * will be used.
		 */
		if($value !== null) $this->setValue($value);
		else $this->setValue(date('H:i'));

		// custom optional fields
		if($class !== null) $this->setClass($class);
		if($classError !== null) $this->setClassOnError($classError);
	}


	/**
	 * Retrieve the initial or submitted value
	 *
	 * @return	string
	 */
	public function getValue()
	{
		// redefine default value
		$value = $this->value;

		// added to form
		if($this->isSubmitted())
		{
			// post/get data
			$data = $this->getMethod(true);

			// submitted by post (may be empty)
			if(isset($data[$this->getName()]))
			{
				// value
				$value = $data[$this->getName()];
			}
		}

		return $value;
	}


	/**
	 * Checks if this field has any content (except spaces)
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isFilled($error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!(isset($data[$this->getName()]) && trim($data[$this->getName()]) != ''))
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Checks if this field is correctly submitted
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isValid($error = null)
	{
		// field has been filled in
		if($this->isFilled())
		{
			// post/get data
			$data = $this->getMethod(true);

			// new time
			$time = '';

			// allowed characters
			$aCharacters = array(':', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');

			// replace every character if it's not in the list!
			for($i = 0; $i < strlen($data[$this->getName()]); $i++)
			{
				if(in_array(substr($data[$this->getName()], $i, 1), $aCharacters)) $time .= substr($data[$this->getName()], $i, 1);
			}

			// maxlength checks out (needs to be equal)
			if(strlen($time) == 5)
			{
				// define hour & minutes
				$hour = (int) substr($time, 0, 2);
				$minutes = (int) substr($time, 3, 2);

				// validates
				if($hour >= 0 && $hour <= 23 && $minutes >= 0 && $minutes <= 59) return true;
			}
		}

		/**
		 * If the script reaches this point, that means this field has not
		 * been successfully submitted, which results in returning a false
		 * and optionally defining an error.
		 */

		// error defined
		if($error !== null) $this->setError($error);

		// return status
		return false;
	}


	/**
	 * Parses the html for this time field
	 *
	 * @return	string
	 * @param	SpoonTemplate[optional] $template
	 */
	public function parse(SpoonTemplate $template = null)
	{
		// name is required
		if($this->getName() == '') throw new SpoonFormException('A name is required for a time field. Please provide a name.');

		// start html generation
		$output = '<input type="text" id="'. $this->getId() .'" name="'. $this->getName() .'" value="'. $this->getValue() .'" maxlength="5"';

		// class / classOnError
		if($this->getClassHTML() != '') $output .= $this->getClassHTML();

		// style attribute
		if($this->style !== null) $output .= ' style="'. $this->getStyle() .'"';

		// tabindex
		if($this->tabindex !== null) $output .= ' tabindex="'. $this->getTabIndex() .'"';

		// readonly
		if($this->readOnly) $output .= ' readonly="readonly"';

		// disabled
		if($this->disabled) $output .= ' disabled="disabled"';

		// custom attributes
		if(count($this->attributes) != 0) $output .= $this->getAttributesHTML(array('[id]' => $this->getId(), '[name]' => $this->getName(), '[value]' => $this->getValue()));

		// end html
		$output .= ' />';

		// template
		if($template !== null)
		{
			$template->assign('txt'. SpoonFilter::toCamelCase($this->name), $output);
			$template->assign('txt'. SpoonFilter::toCamelCase($this->name) .'Error', ($this->errors!= '') ? '<span class="formError">'. $this->errors .'</span>' : '');
		}

		// cough
		return $output;
	}


	/**
	 * This method is useless, since the maxlength is limited to 5 charachters
	 *
	 * @return	void
	 * @param	int $characters
	 */
	public function setMaxlength($characters)
	{
		throw new SpoonException('This method is not to be used with the SpoonTimeField class. The maxlength setting is automatically generated.');
	}


	/**
	 * Set the value attribute for this time field
	 *
	 * @return	void
	 * @param	string $value
	 */
	public function setValue($value)
	{
		$this->value = (string) $value;
	}
}


/**
 * Generates a single or multiple dropdown menu.
 *
 * @package		html
 * @subpackage	form
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @since		0.1.1
 */
class SpoonDropDown extends SpoonVisualFormElement
{
	/**
	 * Should we allow external data
	 *
	 * @var	bool
	 */
	private $allowExternalData = false;


	/**
	 * Class attribute on error
	 *
	 * @var	string
	 */
	protected $classError;


	/**
	 * Default element on top of the dropdown (value, label)
	 *
	 * @var	array
	 */
	private $defaultElement = array();


	/**
	 * Errors stack
	 *
	 * @var	string
	 */
	protected $errors;


	/**
	 * Contains optgroups
	 *
	 * @var	bool
	 */
	private $optionGroups = false;


	/**
	 * Default selected item(s)
	 *
	 * @var	mixed
	 */
	private $selected;


	/**
	 * Whether you can select multiple elements
	 *
	 * @var	bool
	 */
	private $single = true;


	/**
	 * Number of elements shown at once
	 *
	 * @var	int
	 */
	private $size = 1;


	/**
	 * Initial values
	 *
	 * @var	array
	 */
	protected $values = array();


	/**
	 * Class constructor.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	array $values
	 * @param	mixed[optional] $selected
	 * @param	bool[optional] $multipleSelection
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function __construct($name, array $values, $selected = null, $multipleSelection = false, $class = 'inputDropdown', $classError = 'inputDropdownError')
	{
		// obligates fields
		$this->setId(SpoonFilter::toCamelCase($name, '_', true));
		$this->setName($name);
		$this->setValues($values);

		// custom optional fields
		$this->single = !(bool) $multipleSelection;
		if($selected !== null) $this->setSelected($selected);
		if($class !== null) $this->setClass($class);
		if($classError !== null) $this->setClassOnError($classError);
	}


	/**
	 * Adds an error to the error stack
	 *
	 * @return	void
	 * @param	string $error
	 */
	public function addError($error)
	{
		$this->errors .= (string) $error;
	}


	/**
	 * Retrieves the class based on the errors status
	 *
	 * @return	string
	 */
	public function getClassHTML()
	{
		// default value
		$value = '';

		// has errors
		if($this->errors != '')
		{
			// class & classOnError defined
			if($this->class != '' && $this->classError != '') $value = ' class="'. $this->class .' '. $this->classError .'"';

			// only class defined
			elseif($this->class != '') $value = ' class="'. $this->class .'"';

			// only error defined
			elseif($this->classError != '') $value = ' class="'. $this->classError .'"';
		}

		// no errors
		else
		{
			// class defined
			if($this->class != '') $value = ' class="'. $this->class .'"';
		}

		return $value;
	}


	/**
	 * Retrieve the class on error
	 *
	 * @return	string
	 */
	public function getClassOnError()
	{
		return $this->classError;
	}


	/**
	 * Retrieve the initial value
	 *
	 * @return	string
	 */
	public function getDefaultValue()
	{
		return $this->values;
	}


	/**
	 * Retrieve the errors
	 *
	 * @return	string
	 */
	public function getErrors()
	{
		return $this->errors;
	}


	/**
	 * Retrieves the selected item(s)
	 *
	 * @return	mixed
	 */
	public function getSelected()
	{
		/**
		 * If we want to know what elements are selected, we first need
		 * to make sure that the $_POST/$_GET array is taken into consideration.
		 */

		// form submitted
		if($this->isSubmitted())
		{
			// post/get data
			$data = $this->getMethod(true);

			// multiple
			if(!$this->single)
			{
				// field has been submitted
				if(isset($data[$this->getName()]) && is_array($data[$this->getName()]) && count($data[$this->getName()]) != 0)
				{
					// reset selected
					$this->selected = array();

					// loop elements and add the value to the array
					foreach($data[$this->getName()] as $label => $value) $this->selected[] = $value;
				}
			}

			// single (has been submitted)
			elseif(isset($data[$this->getName()]) && $data[$this->getName()] != '') $this->selected = (string) $data[$this->getName()];
		}

		return $this->selected;
	}


	/**
	 * Retrieve the value(s)
	 *
	 * @return	mixed
	 */
	public function getValue()
	{
		// post/get data
		$data = $this->getMethod(true);

		// default values
		$values = $this->values;

		// submitted field
		if($this->isSubmitted() && isset($data[$this->getName()]))
		{
			// option groups
			if($this->optionGroups) $values = $data[$this->getName()];

			// no option groups
			else
			{
				// multiple selection allowed
				if(!$this->single)
				{
					// reset
					$values = array();

					// loop choices
					foreach((array) $data[$this->getName()] as $value)
					{
						// add if exists in the initial array
						if($this->allowExternalData) $values[] = $value;
						elseif(isset($this->values[$value]) && !in_array($value, $values)) $values[] = $value;
					}
				}

				// ony single selection
				else
				{
					// rest
					$values = null;

					// external data allowed?
					if($this->allowExternalData) $values = $data[$this->getName()];
					elseif(isset($this->values[$data[$this->getName()]])) $values = $data[$this->getName()];
				}
			}
		}

		return $values;
	}


	/**
	 * Checks if this field was submitted & contains one more values
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isFilled($error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// default error
		$hasError = false;

		// value not submitted
		if(!isset($data[$this->getName()])) $hasError = true;

		// value submitted
		else
		{
			// multiple
			if(!$this->single) $hasError = true;

			// single
			elseif(trim($data[$this->getName()]) == '') $hasError = true;
		}

		// has error
		if($hasError)
		{
			if($error !== null) $this->setError($error);
			return false;
		}

		return true;
	}


	/**
	 * Parses the html for this dropdown
	 *
	 * @return	string
	 * @param	SpoonTemplate[optional] $template
	 */
	public function parse(SpoonTemplate $template = null)
	{
		// name is required
		if($this->getName() == '') throw new SpoonFormException('A name is required for a dropdown menu. Please provide a name.');

		// start html generation
		$output = "\r\n" . '<select id="'. $this->getId() .'" name="'. $this->getName();

		// multiple needs []
		if(!$this->single) $output .= '[]';

		// end name tag
		$output .= '"';

		// size (number of elements to be shown)
		if($this->size > 1) $output .= ' size="'. $this->size .'"';

		// multiple
		if(!$this->single) $output .= ' multiple="multiple"';

		// class / classOnError
		if($this->getClassHTML() != '') $output .= $this->getClassHTML();

		// style attribute
		if($this->style !== null) $output .= ' style="'. $this->getStyle() .'"';

		// tabindex
		if($this->tabindex !== null) $output .= ' tabindex="'. $this->getTabIndex() .'"';

		// readonly
		if($this->readOnly) $output .= ' readonly="readonly"';

		// disabled
		if($this->disabled) $output .= ' disabled="disabled"';

		// custom attributes
		if(count($this->attributes) != 0) $output .= $this->getAttributesHTML(array('[id]' => $this->getId(), '[name]' => $this->getName()));

		// end select tag
		$output .= ">\r\n";

		// default element?
		if(count($this->defaultElement) != 0)
		{
			// create option
			$output .= "\t". '<option value="'. $this->defaultElement[1] .'"';

			// multiple
			if(!$this->single)
			{
				// if the value is within the selected items array
				if(is_array($this->getSelected()) && count($this->getSelected()) != 0 && in_array($this->defaultElement[1], $this->getSelected())) $output .= ' selected="selected"';
			}

			// single
			else
			{
				// if the current value is equal to the submitted value
				if($this->defaultElement[1] == $this->getSelected()) $output .= ' selected="selected"';
			}

			// end option
			$output .= '>'. $this->defaultElement[0] ."</option>\r\n";
		}

		// has option groups
		if($this->optionGroups)
		{
			foreach ($this->values as $groupName => $group)
			{
				// create optgroup
				$output .= "\t" .'<optgroup label="'. $groupName .'">'."\n";

				// loop valuesgoo
				foreach ($group as $value => $label)
				{
					// create option
					$output .= "\t\t" . '<option value="'. $value .'"';

					// multiple
					if(!$this->single)
					{
						// if the value is within the selected items array
						if(is_array($this->getSelected()) && count($this->getSelected()) != 0 && in_array($value, $this->getSelected())) $output .= ' selected="selected"';
					}

					// single
					else
					{
						// if the current value is equal to the submitted value
						if($value == $this->getSelected()) $output .= ' selected="selected"';
					}

					// end option
					$output .= ">$label</option>\r\n";
				}

				// end optgroup
				$output .= "\t" .'</optgroup>'."\n";
			}
		}

		// regular dropdown
		else
		{
			// loop values
			foreach ($this->values as $value => $label)
			{
				// create option
				$output .= "\t". '<option value="'. $value .'"';

				// multiple
				if(!$this->single)
				{
					// if the value is within the selected items array
					if(is_array($this->getSelected()) && count($this->getSelected()) != 0 && in_array($value, $this->getSelected())) $output .= ' selected="selected"';
				}

				// single
				else
				{
					// if the current value is equal to the submitted value
					if($this->getSelected() !== null && $value == $this->getSelected()) $output .= ' selected="selected"';
				}

				// end option
				$output .= ">$label</option>\r\n";
			}
		}

		// end html
		$output .= "</select>\r\n";

		// parse to template
		if($template !== null)
		{
			$template->assign('ddm'. SpoonFilter::toCamelCase($this->name), $output);
			$template->assign('ddm'. SpoonFilter::toCamelCase($this->name) .'Error', ($this->errors!= '') ? '<span class="formError">'. $this->errors .'</span>' : '');
		}

		// cough it up
		return $output;
	}


	/**
	 * Should we allow external data to be added
	 *
	 * @return	void
	 * @param	bool[optional] $on
	 */
	public function setAllowExternalData($on = true)
	{
		$this->allowExternalData = (bool) $on;
	}


	/**
	 * Set the class on error
	 *
	 * @return	void
	 * @param	string $class
	 */
	public function setClassOnError($class)
	{
		$this->classError = (string) $class;
	}


	/**
	 * Sets the default element (top of the dropdown)
	 *
	 * @return	void
	 * @param	string $label
	 * @param	string[optional] $value
	 */
	public function setDefaultElement($label, $value = null)
	{
		$this->defaultElement = array((string) $label, (string) $value);
	}


	/**
	 * Overwrites the error stack
	 *
	 * @return	void
	 * @param	string $error
	 */
	public function setError($error)
	{
		$this->errors = (string) $error;
	}


	/**
	 * Whether you can select one or more items
	 *
	 * @return	void
	 * @param	bool[optional] $single
	 */
	public function setSingle($single = true)
	{
		$this->single = (bool) $single;
	}


	/**
	 * The number of elements that are shown at once
	 *
	 * @return	void
	 * @param	int[optional] $size
	 */
	public function setSize($size = 1)
	{
		$this->size = (int) $size;
	}


	/**
	 * Set the default selected item(s)
	 *
	 * @return	void
	 * @param	mixed $selected
	 */
	public function setSelected($selected)
	{
		// an array
		if(is_array($selected))
		{
			// may NOT be single
			if($this->single) throw new SpoonFormException('The "selected" argument must be a string, when you create a "single" dropdown');

			// arguments are fine
			foreach($selected as $item) $this->selected[] = (string) $item;
		}

		// other types
		else
		{
			// single type
			if($this->single) $this->selected = (string) $selected;

			// multiple selections
			else $this->selected[] = (string) $selected;
		}
	}


	/**
	 * Sets the values for this dropdown menu
	 *
	 * @return	void
	 * @param	array $values
	 */
	private function setValues(array $values)
	{
		// has not items
		if(count($values) == 0) throw new SpoonFormException('The array with values contains no items.');

		// check the first element
		foreach($values as $value)
		{
			// dropdownfield with optgroups?
			$this->optionGroups = (is_array($value)) ? true : false;

			// break the loop
			break;
		}

		// has option groups
		if($this->optionGroups)
		{
			// loop each group
			foreach($values as $groupName => $options)
			{
				// loop each option
				foreach($options as $key => $value) $this->values[$groupName][$key] = $value;
			}
		}

		// no option groups
		else
		{
			// has items
			foreach($values as $label => $value) $this->values[$label] = $value;
		}
	}
}


/**
 * Generates a checkbox.
 *
 * @package		html
 * @subpackage	form
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @since		0.1.1
 */
class SpoonCheckBox extends SpoonVisualFormElement
{
	/**
	 * Checked status
	 *
	 * @var	bool
	 */
	private $checked = false;


	/**
	 * Class attribute on error
	 *
	 * @var	string
	 */
	protected $classError;


	/**
	 * Errors stack
	 *
	 * @var	string
	 */
	private $errors;


	/**
	 * Class constructor.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	bool[optional] $checked
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function __construct($name, $checked = false, $class = 'inputCheckbox', $classError = 'inputCheckboxError')
	{
		// name & id
		$this->setName($name);
		$this->setId(SpoonFilter::toCamelCase($name, '_', true));

		// custom optional fields
		$this->setChecked($checked);
		if($class !== null) $this->setClass($class);
		if($classError !== null) $this->setClassOnError($classError);
	}


	/**
	 * Adds an error to the error stack
	 *
	 * @return	void
	 * @param	string $error
	 */
	public function addError($error)
	{
		$this->errors .= (string) $error;
	}


	/**
	 * Returns the checked status for this checkbox
	 *
	 * @return	bool
	 */
	public function getChecked()
	{
		// form submitted
		if($this->isSubmitted())
		{
			// post/get data
			$data = $this->getMethod(true);

			// not checked by default
			$checked = false;

			// single (is checked)
			if(isset($data[$this->getName()]) && $data[$this->getName()] == 'Y') $checked = true;

			// adjust status
			$this->setChecked($checked);
		}

		return $this->checked;
	}


	/**
	 * Retrieves the class based on the errors status
	 *
	 * @return	string
	 */
	public function getClassHTML()
	{
		// default value
		$value = '';

		// has errors
		if($this->errors != '')
		{
			// class & classOnError defined
			if($this->class != '' && $this->classError != '') $value = ' class="'. $this->class .' '. $this->classError .'"';

			// only class defined
			elseif($this->class != '') $value = ' class="'. $this->class .'"';

			// only error defined
			elseif($this->classError != '') $value = ' class="'. $this->classError .'"';
		}

		// no errors
		else
		{
			// class defined
			if($this->class != '') $value = ' class="'. $this->class .'"';
		}

		return $value;
	}


	/**
	 * Retrieve the class on error
	 *
	 * @return	string
	 */
	public function getClassOnError()
	{
		return $this->classError;
	}


	/**
	 * Retrieve the errors
	 *
	 * @return	string
	 */
	public function getErrors()
	{
		return $this->errors;
	}


	/**
	 * Retrieve the value(s)
	 *
	 * @return	mixed
	 */
	public function getValue()
	{
		// default value
		$value = false;

		// submitted by post (may be empty)
		if($this->isSubmitted())
		{
			// post/get data
			$data = $this->getMethod(true);

			// single checkbox
			if(isset($data[$this->getName()]) && $data[$this->getName()] == 'Y') $value = true;
		}

		return $value;
	}


	/**
	 * Is this specific field checked
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isChecked($error = null)
	{
		// checked
		if($this->getChecked()) return true;

		// not checked
		else
		{
			if($error !== null) $this->addError($error);
			return false;
		}
	}


	/**
	 * Checks if this field was submitted & contains one more values
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isFilled($error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// value submitted
		if(isset($data[$this->getName()]) && $data[$this->getName()] == 'Y') return true;

		// nothing submitted
		if($error !== null) $this->addError($error);
		return false;
	}


	/**
	 * Parses the html for this dropdown
	 *
	 * @return	string
	 * @param	SpoonTemplate[optional] $template
	 */
	public function parse(SpoonTemplate $template = null)
	{
		// name required
		if($this->getName() == '') throw new SpoonFormException('A name is required for checkbox. Please provide a name.');

		// start html generation
		$output = '<input type="checkbox" id="'. $this->getId() .'" name="'. $this->getName() .'" value="Y"';

		// class / classOnError
		if($this->getClassHTML() != '') $output .= $this->getClassHTML();

		// style attribute
		if($this->style !== null) $output .= ' style="'. $this->getStyle() .'"';

		// tabindex
		if($this->tabindex !== null) $output .= ' tabindex="'. $this->getTabIndex() .'"';

		// readonly
		if($this->readOnly) $output .= ' readonly="readonly"';

		// disabled
		if($this->disabled) $output .= ' disabled="disabled"';

		// checked or not?
		if($this->getChecked()) $output .= ' checked="checked"';

		// custom attributes
		if(count($this->attributes) != 0) $output .= $this->getAttributesHTML(array('[id]' => $this->getId(), '[name]' => $this->getName()));

		// end input tag
		$output .= ' />';

		// template
		if($template !== null)
		{
			$template->assign('chk'. SpoonFilter::toCamelCase($this->name), $output);
			$template->assign('chk'. SpoonFilter::toCamelCase($this->name) .'Error', ($this->errors!= '') ? '<span class="formError">'. $this->errors .'</span>' : '');
		}

		// cough
		return $output;
	}


	/**
	 * Sets the checked status
	 *
	 * @return	void
	 * @param	bool[optional] $checked
	 */
	public function setChecked($checked = true)
	{
		$this->checked = (bool) $checked;
	}


	/**
	 * Set the class on error
	 *
	 * @return	void
	 * @param	string $class
	 */
	public function setClassOnError($class)
	{
		$this->classError = (string) $class;
	}


	/**
	 * Overwrites the error stack
	 *
	 * @return	void
	 * @param	string $error
	 */
	public function setError($error)
	{
		$this->errors = (string) $error;
	}
}


/**
 * Generates a checkbox.
 *
 * @package		html
 * @subpackage	form
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @since		0.1.1
 */
class SpoonMultiCheckBox extends SpoonVisualFormElement
{
	/**
	 * Should we allow external data
	 *
	 * @var	bool
	 */
	private $allowExternalData = false;


	/**
	 * List of checked values
	 *
	 * @var	array
	 */
	private $checked = array();


	/**
	 * Class attribute on error
	 *
	 * @var	string
	 */
	protected $classError;


	/**
	 * Errors stack
	 *
	 * @var	string
	 */
	private $errors;


	/**
	 * Initial values
	 *
	 * @var	array
	 */
	private $values;


	/**
	 * Class constructor.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	mixed $values
	 * @param	mixed[optional] $checked
	 * @param	string[optional] $class
	 * @param	string[optional] $classError
	 */
	public function __construct($name, array $values, $checked = null, $class = 'inputCheckbox', $classError = 'inputCheckboxError')
	{
		// name & value
		$this->setName($name);
		$this->setValues($values);

		// custom optional fields
		if($checked !== null) $this->setChecked($checked);
		if($class !== null) $this->setClass($class);
		if($classError !== null) $this->setClassOnError($classError);
	}


	/**
	 * Adds an error to the error stack
	 *
	 * @return	void
	 * @param	string $error
	 */
	public function addError($error)
	{
		$this->errors .= (string) $error;
	}


	/**
	 * Retrieves the class based on the errors status
	 *
	 * @return	string
	 */
	public function getClassHTML()
	{
		// default value
		$value = '';

		// has errors
		if($this->errors != '')
		{
			// class & classOnError defined
			if($this->class != '' && $this->classError != '') $value = ' class="'. $this->class .' '. $this->classError .'"';

			// only class defined
			elseif($this->class != '') $value = ' class="'. $this->class .'"';

			// only error defined
			elseif($this->classError != '') $value = ' class="'. $this->classError .'"';
		}

		// no errors
		else
		{
			// class defined
			if($this->class != '') $value = ' class="'. $this->class .'"';
		}

		return $value;
	}


	/**
	 * Retrieve the list of checked boxes
	 *
	 * @return	array
	 */
	public function getChecked()
	{
		// when submitted
		if($this->isSubmitted()) return $this->getValue();

		// default values
		else return $this->checked;
	}


	/**
	 * Retrieve the class on error
	 *
	 * @return	string
	 */
	public function getClassOnError()
	{
		return $this->classError;
	}


	/**
	 * Retrieve the errors
	 *
	 * @return	string
	 */
	public function getErrors()
	{
		return $this->errors;
	}


	/**
	 * This method should not be used
	 *
	 * @return	void
	 */
	public function getId()
	{
		return;
	}


	/**
	 * Retrieve the value(s)
	 *
	 * @return	array
	 */
	public function getValue()
	{
		// default value
		$aValues = array();

		// submitted by post (may be empty)
		if($this->isSubmitted())
		{
			// post/get data
			$data = $this->getMethod(true);

			// exists
			if(isset($data[$this->getName()]) && is_array($data[$this->getName()]))
			{
				// loop values
				foreach($data[$this->getName()] as $item)
				{
					// value exists
					if($this->allowExternalData) $aValues[] = $item;
					elseif(isset($this->values[(string) $item])) $aValues[] = $item;
				}
			}

		}

		return $aValues;
	}


	/**
	 * Checks if this field was submitted & contains one more values
	 *
	 * @return	bool
	 * @param	string[optional] $error
	 */
	public function isFilled($error = null)
	{
		// post/get data
		$data = $this->getMethod(true);

		// value submitted & is an array
		if(isset($data[$this->getName()]) && is_array($data[$this->getName()]))
		{
			// loop the elements until you can find one that is allowed
			foreach($data[$this->getName()] as $value)
			{
				if(isset($this->values[(string) $value])) return true;
			}
		}

		// no values found
		if($error !== null) $this->addError($error);
		return false;
	}


	/**
	 * Parses the html for this dropdown
	 *
	 * @return	string
	 * @param	SpoonTemplate[optional] $template
	 */
	public function parse(SpoonTemplate $template = null)
	{
		// name required
		if($this->getName() == '') throw new SpoonFormException('A name is required for checkbox. Please provide a name.');

		// loop values
		foreach($this->values as $value => $label)
		{
			// init vars
			$aElement['id'] = SpoonFilter::toCamelCase($this->getName() .'_'. $value, '_', true);
			$aElement['label'] = $label;
			$aElement['value'] = $value;
			$name = 'chk'. SpoonFilter::toCamelCase($this->getName());
			$aElement[$name] = '';

			// start html generation
			$aElement[$name] = '<input type="checkbox" id="'. $aElement['id'] .'" name="'. $this->getName() .'[]"';

			// value
			$aElement[$name] .= ' value="'. $value .'"';

			// class / classOnError
			if($this->getClassHTML() != '') $aElement[$name] .= $this->getClassHTML();

			// style attribute
			if($this->style !== null) $aElement[$name] .= ' style="'. $this->getStyle() .'"';

			// tabindex
			if($this->tabindex !== null) $aElement[$name] .= ' tabindex="'. $this->getTabIndex() .'"';

			// readonly
			if($this->readOnly) $aElement[$name] .= ' readonly="readonly"';

			// disabled
			if($this->disabled) $aElement[$name] .= ' disabled="disabled"';

			// custom attributes
			if(count($this->attributes) != 0) $aElement[$name] .= $this->getAttributesHTML(array('[id]' => $aElement['id'], '[name]' => $this->getName(), '[value]' => $value));

			// checked or not?
			if(in_array($aElement['value'], $this->getChecked())) $aElement[$name] .= ' checked="checked"';

			// end input tag
			$aElement[$name] .= ' />';

			// add checkbox
			$aCheckBox[] = $aElement;
		}

		// template
		if($template !== null)
		{
			$template->assign($this->getName(), $aCheckBox);
			$template->assign('chk'. SpoonFilter::toCamelCase($this->getName()) .'Error', ($this->errors!= '') ? '<span class="formError">'. $this->errors .'</span>' : '');
		}

		// cough
		return $aCheckBox;
	}


	/**
	 * Should we allow external data
	 *
	 * @return	void
	 * @param	bool[optional] $on
	 */
	public function setAllowExternalData($on = true)
	{
		$this->allowExternalData = (bool) $on;
	}


	/**
	 * Sets the checked status
	 *
	 * @return	void
	 * @param	mixed $checked
	 */
	public function setChecked($checked)
	{
		// redefine
		$checked = (array) $checked;

		// loop values
		foreach($checked as $value)
		{
			// exists
			if(isset($this->values[(string) $value])) $aChecked[] = $value;
		}

		// set values
		if(isset($aChecked)) $this->checked = $aChecked;
	}


	/**
	 * Set the class on error
	 *
	 * @return	void
	 * @param	string $class
	 */
	public function setClassOnError($class)
	{
		$this->classError = (string) $class;
	}


	/**
	 * Overwrites the error stack
	 *
	 * @return	void
	 * @param	string $error
	 */
	public function setError($error)
	{
		$this->errors = (string) $error;
	}


	/**
	 * This method should not be used
	 *
	 * @return	void
	 * @param	string $id
	 */
	public function setId($id)
	{
		throw new SpoonFormException('This method is not to be used with this class.');
	}


	/**
	 * Set the initial values
	 *
	 * @return	void
	 * @param	mixed $values
	 */
	private function setValues(array $values)
	{
		foreach($values as $value => $label) $this->values[(string) $value] = (string) $label;
	}
}


/**
 * Creates an html hidden field
 *
 * @package		html
 * @subpackage	form
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @since		1.0.0
 */
class SpoonHiddenField extends SpoonFormElement
{
	/**
	 * Value of this hidden field
	 *
	 * @var	string
	 */
	private $value;


	/**
	 * Class constructor.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $value
	 */
	public function __construct($name, $value = null)
	{
		// obligated fields
		$this->setName($name);

		// value
		if($value !== null) $this->setValue($value);
	}


	/**
	 * Retrieve the initial or submitted value
	 *
	 * @return	string
	 */
	public function getValue()
	{
		// redefine default value
		$value = $this->value;

		// added to form
		if($this->isSubmitted())
		{
			// post/get data
			$data = $this->getMethod(true);

			// submitted by post/get (may be empty)
			if(isset($data[$this->getName()]))
			{
				// value
				$value = (string) $data[$this->getName()];
			}
		}

		return $value;
	}


	/**
	 * Checks if this field has any content (except spaces)
	 *
	 * @return	bool
	 */
	public function isFilled()
	{
		// post/get data
		$data = $this->getMethod(true);

		// validate
		if(!(isset($data[$this->getName()]) && trim($data[$this->getName()]) != '')) return false;
		return true;
	}


	/**
	 * Parses the html for this hidden field
	 *
	 * @return	string
	 * @param	SpoonTemplate[optional] $template
	 */
	public function parse(SpoonTemplate $template = null)
	{
		// name is required
		if($this->name == '') throw new SpoonFormException('A name is required for a hidden field. Please provide a name.');

		// start html generation
		$output = '<input type="hidden"';

		// add id?
		if($this->id !== null) $output .= 'id="'. $this->id .'"';

		// add other elements
		$output .= ' name="'. $this->name .'" value="'. $this->getValue() .'"';

		// custom attributes
		if(count($this->attributes) != 0) $output .= $this->getAttributesHTML(array('[id]' => $this->getId(), '[name]' => $this->getName(), '[value]' => $this->getValue()));

		// close input tag
		$output .= ' />';

		// parse hidden field
		if($template !== null) $template->assign('hid'. SpoonFilter::toCamelCase($this->name), $output);

		// cough it up
		return $output;
	}


	/**
	 * Set the value attribute for this hidden field
	 *
	 * @return	void
	 * @param	string $value
	 */
	private function setValue($value)
	{
		$this->value = (string) $value;
	}
}

?>