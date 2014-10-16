<?php 

return array(
	
	'autoload' => array(
		'FilterModule.php',
		'src/FilterServiceProvider.php',
		'src/facades/Filter.php',
		'src/command/FilterCommand.php',

		// Filter cases
		'src/cases/FilterCaseInterface.php',
		'src/cases/FilterCase.php',
		'src/cases/FilterCaseAttributes.php',
		'src/cases/FilterCaseOptions.php',
		'src/cases/FilterCaseAttributesGroup.php',

		// Filter builders
		'src/builders/FilterBuilderInterface.php',
		'src/builders/FilterBuilder.php',

		// Filter formatters
		'src/formatters/FilterFormatterInterface.php',
		'src/formatters/FilterFormatterAttributes.php',
		'src/formatters/FilterFormatterAllAttributes.php',
		'src/formatters/FilterFormatterFilteredAttributes.php',

		// Filter utils
		'src/utils/StringTemplate.php',

		// Filter managers
		'src/FilterFactory.php',
		'src/FilterManager.php',
		'src/entity/Attributes.php',
		'src/entity/Categories.php',
	)
	
);