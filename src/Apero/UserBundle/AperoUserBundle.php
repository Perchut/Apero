<?php

namespace Apero\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AperoUserBundle extends Bundle
{
	public function getParent()
	{
		return 'FOSUserBundle';
	}
}
