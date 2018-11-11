<?php
/**
 * Retorna o valor Id mascarado ou desmascarado.
 *
 * @return 	$id
 */
function getMaskId($vlr=0, $type=0)
{
    $id = (float) $vlr;

    switch($type)
    {
        case 0:
            $id = (($id+651145780) * 82);
            break;
        case 1:
            $id = (($id/82) - 651145780);
    }

    return $id;
}

/**
 * Retorna o Ip Real.
 * - Verifica se o ip é real ou se está passando por um proxy
 *
 * @see     https://css-tricks.com/serious-form-security/
 * @return  string
 */
function getRealIp() 
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) //verifiq o IP de compartilhamento da Internet.
    {
        $ip     = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) //verifica se o IP está passando por um proxy
    {
        $ip     = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else
    {
        $ip     = $_SERVER['REMOTE_ADDR'];
    }

    return $ip;
}

/**
 * Retorna um token
 *
 * @param   string  Chave a ser concatenada no token
 * @return  string
 */
function getToken($chave='token')
{
    return encripta($chave.getRealIp().$_SERVER['HTTP_USER_AGENT'].uniqid(mt_rand(), true));
}

/**
 * retorna a senha super encriptada
 *
 * @param   string  $senha      Senha a ser encriptada
 * @return  string  $codifica   Senha super encriptada
 */
function encripta($senha='')
{
    $salt       = md5(trim($senha).Configure::read("Security.salt"));
    $codifica   = crypt(trim($senha),$salt);
    $codifica   = hash('sha512',$codifica);

    return $codifica; 
}

/**
 * Exibe na tela o backGrace
 *
 * @param   string  $d Mensagem
 * @return  void
 */
function debugol($d='') 
{
    $t = array_reverse(debug_backtrace());
    $m = array();
    $v = get_defined_vars();
    foreach($t as $_l => $_arrProp)
    {
        if (isset($_arrProp['line']))
        {
            $m[$_arrProp['line']] = $_arrProp['file'];
        }
    }
    echo '<pre class="pre-debug redondo5">';
    foreach($m as $_l => $_file) echo $_file.' (linha: '.$_l.')'.'<br />';
    echo print_r($d,true);
    echo '</pre>';
}
/**
 * Retorna a string no formato camelCase
 *
 * @param   string  $str
 * @return  string  $str
 */
function camelCase($str) {
    $i = array("-","_");
    $str = preg_replace('/([a-z])([A-Z])/', "\\1 \\2", $str);
    $str = preg_replace('@[^a-zA-Z0-9\-_ ]+@', '', $str);
    $str = str_replace($i, ' ', $str);
    $str = str_replace(' ', '', ucwords(strtolower($str)));
    $str = strtolower(substr($str,0,1)).substr($str,1);
    return $str;
}

/**
 * Retorna a string no formato camelCase inverso
 *
 * @param   string  $str
 * @return  string  $str
 */
function uncamelCase($str) {
    $str = preg_replace('/([a-z])([A-Z])/', "\\1_\\2", $str);
    $str = strtolower($str);
    return $str;
}

/**
 * Retorna a string $vlr mascarada com $mask
 *
 * @param   string  $vlr    Valor original a ser mascarado
 * @param   string  $mask   Máscara
 * @return  string
 */
function mascarado($vlr='', $mask='')
{
    $mascarado  = '';
    $k          = 0;

    for( $i=0; $i<=strlen($mask)-1; $i++)
    {
        if (in_array($mask[$i],['#','9']))
        {
            if(isset($vlr[$k]))
            {
                $mascarado .= $vlr[$k++];
            }
        } else
        {
            if ( isset($mask[$i]) )
            {
                $mascarado .= $mask[$i];
            }
        }
    }

    return $mascarado;
}

/**
 * Retorna o valor no formato Sql
 *
 * @param   string  $valr   Valor no formato Sql 0,000.00
 * @return  string  $vlr    Valor no formato pt_BR 0.000,00
 */
function getFormatDecimalBrasil($vlr)
{
    $arrVlr = explode('.',$vlr);
    $t      = isset($arrVlr[1]) ? strlen($arrVlr[1]) : 0;
    $vlr    = number_format($vlr,$t,',','.');

    return $vlr;
}

/**
 * Retorna o valor no formato pt_BR
 * 
 * @param   string  $valr   Valor no formato pt_BR 0.000,00
 * @return  string  $vlr    Valor no formato Sql 0,000.00
 */
function getFormatDecimalSql($vlr)
{
    $arrVlr = explode(',',$vlr);
    $t      = isset($arrVlr[1]) ? strlen($arrVlr[1]) : 0;
    $vlr    = number_format($vlr,$t,'.',',');

    return $vlr;
}

/**
 * Retorna uma faixa de número sem repetição
 *
 * @param   integer     $ini    Número incial
 * @param   integer     $fim    Número final
 * @param   integer     $tam    tamanho do array de retorno.
 */
function getFaixa($ini=1, $fim=1000, $tam=10)
{
    $faixa = range($ini, $fim);
    shuffle($faixa);

    return array_slice($faixa, 0, $tam);
}

/**
 * Retorna uma string randomica
 * 
 * @param int       $length         Tamanho da string
 * @param string    $keyspace       caracteres que vão compor a stirng
 * @return string
 */
function getRandomString($length=10, $characters='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
    $charactersLength   = strlen($characters);
    $randomString       = '';
    for ($i = 0; $i < $length; $i++)
    {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomString;
}

/**
 * Escreve um Log no diretório temporário
 *
 * @param   string  $log        Nome do arquivo log.
 * @param   mixed   $conteudo   Conteúdo do logo.
 * @param   string  $tipo       Tipo da escrita, utiliza a+ para continuar a escrita, o padrão é re-escrever o arquivo.
 * @return  void
 */
function gravaLog($nomeLog='log', $conteudo='', $tipo='w')
{
    $fp = fopen(ROOT . DS . APP_DIR . DS. 'tmp'.DS.'logs'.DS.$nomeLog.'.log',$tipo);
    ob_start();
    pr($conteudo);
    $saida = ob_get_clean();
    fwrite($fp, $saida.PHP_EOL);
    fclose($fp);
}