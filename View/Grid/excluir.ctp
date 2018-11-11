<?php
if ($this->layout == 'Grid.ajax')
{
	echo json_encode($retorno);
} else
{
	pr($retorno);
}