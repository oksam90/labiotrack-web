<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Contrôleur de base — hérite de BaseController Laravel et ajoute :
 *  - AuthorizesRequests : fournit $this->authorize(), $this->authorizeForUser()
 *  - ValidatesRequests  : fournit $this->validate() (legacy, préférer $request->validate())
 *
 * SANS ces traits, tout appel à $this->authorize() lève :
 *   "Call to undefined method Controller::authorize()"
 */
abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
