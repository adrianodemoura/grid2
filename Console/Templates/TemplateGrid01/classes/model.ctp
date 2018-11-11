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
 * Gerado pelo plugin Grid
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
	public $actsAs = ['Grid.FormatData'];

<?php if (isset($describe) && !empty($describe)): ?>
	/**
	 * Atributos de cada campo
	 *
	 * @var 	array
	 */
	public $esquema = 
	[
	<?php 
		$l = 1;
		$m = 1;
		echo "\n";

		foreach($describe as $_cmp => $_arrProp)
		{
			$label = str_replace('_', '', ucwords($_cmp, '_'));
			$label = ($_cmp=='id') ? 'Código' : $label;
			$label = str_replace('-', '', ucwords($label, '-'));
			$input = [];
			$type  = isset($_arrProp['type']) ? $_arrProp['type'] : 'text';

			if (isset($_arrProp['default']) && $_arrProp['default'] != 'null')
			{
				$input['default'] = $_arrProp['default'];
			}
			$sort = (isset($_arrProp['key']) || !empty($_arrProp['index']))
				? true
				: false;
			$filter = ($sort===true) ? true : '';
			if ($sort && in_array($type,['string','varchar','varchar2','text']))
			{
				$filter = '%%';
			}

			$input =[];
			if (isset($_arrProp['length']) || !empty($_arrProp['length']))
			{
				$input['maxlength'] = $_arrProp['length'];
			}
			$th = '';
			if (in_array($_cmp,['nome','titulo','title','name']))
			{
				$th = "style='min-width: 300px;'";
			}

			echo "\t\t'$name.$_cmp' => 
		[
			'title' 	=> '$label',
			'alias' 	=> 'mo$m"."c$l',
			'type' 		=> '$type',\n";
			if (strtolower($type=='date'))
			{
				echo "\t\t\t'displayFormat'\t=>'d/m/Y',\n";
				echo "\t\t\t'saveFormat'\t=>'Y-m-d',\n";
			}
			if (strtolower($type=='datetime'))
			{
				echo "\t\t\t'displayFormat'\t=>'d/m/Y H:i:s',\n";
				echo "\t\t\t'displayFormat'\t=>'Y-m-d H:i:s',\n";
			}

			if (!empty($input))
			{
				echo "\t\t\t'input'\t\t=>\n\t\t\t[\n";
				foreach($input as $_tag => $_vlr)
				{
					echo "\t\t\t\t'$_tag' \t=> \"$_vlr\", ";
				};
				echo "\n\t\t\t],\n";
			};

			if (!empty($th))
			{
				echo "\t\t\t'th'\t\t=> \"$th\",\n";
			}

			if ($sort)
			{
				echo "\t\t\t'sort' \t\t=> true,\n";
			}
			if (!empty($filter))
			{
				echo "\t\t\t'filter' \t=> '$filter',\n";
			}

		echo "\t\t],\n";
		$l++;
		}

		if (!empty($associations['hasAndBelongsToMany']))
		{
			foreach($associations['hasAndBelongsToMany'] as $_l => $_arrPropHABTM)
			{
				echo "\t\t'".$_arrPropHABTM['alias'].".count'\t=>\n";
				echo "\t\t[\n";
				echo "\t\t\t'title'\t=> '".$_arrPropHABTM['alias']." Total', \n";
				echo "\t\t\t'alias'\t=> 'mo1c".(count($describe)+1)."', \n";
				echo "\t\t\t'virtual'\t=> true, \n";
				echo "\t\t],\n";

				echo "\t\t'".$_arrPropHABTM['alias'].".describe'\t=>\n";
				echo "\t\t[\n";
				echo "\t\t\t'title'\t=> '".$_arrPropHABTM['alias']."(s)', \n";
				echo "\t\t\t'alias'\t=> 'mo1c".(count($describe)+2)."', \n";
				echo "\t\t\t'virtual'\t=> true, \n";
				echo "\t\t],\n";
			}
		}
	?>
	];

<?php endif;

if (!empty($actsAs)): ?>
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

	/**
	 * Executa código antes de re-configurar o esquema
	 * 
	 * Utilize este método para customizar o esquema.
	 *
	 * @return 	void
	 */
	public function beforeSetEsquema()
	{
	}

}
