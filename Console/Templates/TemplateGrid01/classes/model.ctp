<?php
/**
 * Model template file.
 *
 * Used by bake to create new Model files.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.Console.Templates.default.classes
 * @since         CakePHP(tm) v 1.3
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

//App::uses('Describe','Grid.Utility');
//$esquema = Describe::get($useTable);

$db 		= ConnectionManager::getDataSource('default');
$describe 	= $db->describe($useTable);
$c 			= 0;
foreach($describe as $_cmp => $_arrProp)
{
	if ($c==1)
	{
		$displayField = $_cmp;
	}
	$c++;
}

echo "<?php\n";
?>
/**
 * Model <?php echo "$name\n"; ?>
 *
 * Gerado pelo plugin adrianodemoura/Grid2.
 *
 * @package 	app.Model
 * @since 		CakePHP(tm) v 2.9.5
 */
<?php
echo "App::uses('{$plugin}AppModel', '{$pluginPath}Model');\n";
?>
/**
 * Mantém a tabela <?php echo $useTable."\n"; ?>
 *
<?php
foreach (array('hasOne', 'belongsTo', 'hasMany', 'hasAndBelongsToMany') as $assocType) {
	if (!empty($associations[$assocType])) {
		foreach ($associations[$assocType] as $relation) {
			echo " * @property {$relation['className']} \${$relation['alias']}\n";
		}
	}
}
?>
 */
class <?php echo $name ?> extends <?php echo $plugin; ?>AppModel {

<?php if ($useDbConfig): ?>
	/**
	 * Use database config
	 *
	 * @var string
	 */
	public $useDbConfig = '<?php echo $useDbConfig; ?>';

<?php endif;

if ($useTable):
	$table = "'$useTable'";
	echo "\t/**\n\t * Use table\n\t *\n\t * @var mixed False or table name\n\t */\n";
	echo "\tpublic \$useTable = $table;\n\n";
endif;

if ($primaryKey): ?>
	/**
	 * Chave primária
	 *
	 * @var 	string
	 */
	public $primaryKey = '<?php echo $primaryKey; ?>';

<?php endif; 

	if ($displayField): ?>
	/**
	 * Campo de referência
	 *
	 * @var 	string
	 */
	public $displayField = '<?php echo $displayField; ?>';
<?php endif; ?>

	/**
	 * Behavior
	 *
	 * @var 	array
	 */
	public $actsAs = ['Grid2.FormatData'];

<?php if (!empty($actsAs)): ?>

	/**
	 * Behaviors
	 *
	 * @var array
	 */
	public $actsAs = array(<?php echo "\n\t"; foreach ($actsAs as $behavior): echo "\t"; var_export($behavior); echo ",\n\t"; endforeach; ?>);

<?php endif;

if (!empty($validate)):
	echo "\t/**\n\t * Regras de validação\n\t *\n\t * @var array\n\t */\n";
	echo "\tpublic \$validate = 
	[\n";
	foreach ($validate as $field => $validations):
		echo "\t\t'$field' \t\t\t\t=> 
		[\n";
		foreach ($validations as $key => $validator):
			echo "\t\t\t'$key' \t\t\t=> 
			[\n";
			echo "\t\t\t\t'rule' \t\t\t=> ['$validator'],\n";
			echo "\t\t\t\t'message' 	=> 'Este campo é de preenchimento obrigatório !!!',\n";
			echo "\t\t\t\t//'allowEmpty'=> false,\n";
			echo "\t\t\t\t//'required' 	=> false,\n";
			echo "\t\t\t\t//'last' 		=> false, // Interromper validação depois destra regra\n";
			echo "\t\t\t\t//'on' 		=> 'create', // Limitar a regra ao criar ou atualizar\n";
			echo "\t\t\t],\n";
		endforeach;
		echo "\t\t],\n";
	endforeach;
	echo "\t];\n";
endif;

foreach ($associations as $assoc):
	if (!empty($assoc)):
?>

	// As associações abaixo foram criadas com todas as chaves possíveis, aquelas que não são necessárias, podem ser removidas.
<?php
		break;
	endif;
endforeach;

foreach (array('hasOne', 'belongsTo') as $assocType):
	if (!empty($associations[$assocType])):
		$typeCount = count($associations[$assocType]);
		echo "\n\t/**\n\t * $assocType associations\n\t *\n\t * @var array\n */";
		echo "\n\tpublic \$$assocType = array(";
		foreach ($associations[$assocType] as $i => $relation):
			$out = "\n\t\t'{$relation['alias']}' => array(\n";
			$out .= "\t\t\t'className' => '{$relation['className']}',\n";
			$out .= "\t\t\t'foreignKey' => '{$relation['foreignKey']}',\n";
			$out .= "\t\t\t'conditions' => '',\n";
			$out .= "\t\t\t'fields' => '',\n";
			$out .= "\t\t\t'order' => ''\n";
			$out .= "\t\t)";
			if ($i + 1 < $typeCount) {
				$out .= ",";
			}
			echo $out;
		endforeach;
		echo "\n\t);\n";
	endif;
endforeach;

if (!empty($associations['hasMany'])):
	$belongsToCount = count($associations['hasMany']);
	echo "\n\t/**\n\t * Associações hasMany (1:n)\n\t *\n\t * @var array\n\t */";
	echo "\n\tpublic \$hasMany = 
	[";
	foreach ($associations['hasMany'] as $i => $relation):
		$out = "\n\t\t'{$relation['alias']}' => 
		[\n";
		$out .= "\t\t\t'className' => '{$relation['className']}',\n";
		$out .= "\t\t\t'foreignKey' => '{$relation['foreignKey']}',\n";
		$out .= "\t\t\t'dependent' => false,\n";
		$out .= "\t\t\t'conditions' => '',\n";
		$out .= "\t\t\t'fields' => '',\n";
		$out .= "\t\t\t'order' => '',\n";
		$out .= "\t\t\t'limit' => '',\n";
		$out .= "\t\t\t'offset' => '',\n";
		$out .= "\t\t\t'exclusive' => '',\n";
		$out .= "\t\t\t'finderQuery' => '',\n";
		$out .= "\t\t\t'counterQuery' => ''\n";
		$out .= "\t\t]";
		if ($i + 1 < $belongsToCount) {
			$out .= ",";
		}
		echo $out;
	endforeach;
	echo "\n\t];\n\n";
endif;

if (!empty($associations['hasAndBelongsToMany'])):
	$habtmCount = count($associations['hasAndBelongsToMany']);
	echo "\n\t/**\n\t * Associações hasAndBelongsToMany (n:n)\n\t *\n\t * @var array\n\t */";
	echo "\n\tpublic \$hasAndBelongsToMany = 
	[\n";
	foreach ($associations['hasAndBelongsToMany'] as $i => $relation):
		$out = "\n\t\t'{$relation['alias']}' => 
		[\n";
		$out .= "\t\t\t'className' => '{$relation['className']}',\n";
		$out .= "\t\t\t'joinTable' => '{$relation['joinTable']}',\n";
		$out .= "\t\t\t'foreignKey' => '{$relation['foreignKey']}',\n";
		$out .= "\t\t\t'associationForeignKey' => '{$relation['associationForeignKey']}',\n";
		$out .= "\t\t\t'unique' => 'keepExisting',\n";
		$out .= "\t\t\t'conditions' => '',\n";
		$out .= "\t\t\t'fields' => '',\n";
		$out .= "\t\t\t'order' => '',\n";
		$out .= "\t\t\t'limit' => '',\n";
		$out .= "\t\t\t'offset' => '',\n";
		$out .= "\t\t\t'finderQuery' => '',\n";
		$out .= "\t\t]";
		if ($i + 1 < $habtmCount) {
			$out .= ",";
		}
		echo $out;
	endforeach;
	echo "\n\t];\n\n";
endif;
?>

}
