<?php
/**
 * Controller bake template file
 *
 * Allows templating of Controllers generated from bake.
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

echo "<?php\n";
?>
/**
 * Class Controller
 *
 * Gerado pelo plugin adrianodemoura/Grid2.
 */
<?php
echo "App::uses('{$plugin}AppController', '{$pluginPath}Controller');\n";
?>
/**
 * Mantém o controlador <?php echo $controllerName; ?>
 */
class <?php echo $controllerName; ?>Controller extends <?php echo $plugin; ?>AppController {
	/**
     * Componentes
     *
     * @var     array
     */
    public $components  = ['Grid2.Grid'];

    /**
     * Página inciial do cadastro <?php echo $controllerName; ?>
     *
     * @return 	void
     */
    public function index()
    {
    	$params 					= [];

        $params['arrTitles'][0] 	= 'Listando <?php echo $controllerName; ?>';

        $this->Grid->index($params);
    }

}
